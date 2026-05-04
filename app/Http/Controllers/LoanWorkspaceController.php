<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\CustomField;
use App\Models\Loan;
use App\Models\LoanApproval;
use App\Models\LoanApproverSetting;
use App\Models\LoanProduct;
use App\Models\LoanRepayment;
use App\Services\CollectionFollowUpInsightsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class LoanWorkspaceController extends Controller
{
    public function index(Request $request)
    {
        $today = Carbon::today();
        $tomorrow = Carbon::tomorrow();
        $nextSevenDays = Carbon::today()->addDays(7);
        $followUpsEnabled = Schema::hasTable('loan_collection_follow_ups');

        $repaymentRelations = ['loan.borrower.branch', 'loan.currency'];
        if ($followUpsEnabled) {
            $repaymentRelations[] = 'latestFollowUp.createdBy';
        }

        $followUpInsights = app(CollectionFollowUpInsightsService::class);
        $dateRange = $followUpInsights->resolveRange($request->get('from_date'), $request->get('to_date'), $today);
        $followUpOverview = $followUpInsights->overview($dateRange['start'], $dateRange['end'], 6, 5, 6);

        $pendingLoanQueue = Loan::with(['borrower', 'loan_product', 'currency', 'approvals'])
            ->where('status', 0)
            ->latest('id')
            ->get();

        $pendingLoans = $pendingLoanQueue->take(8)->map(function ($loan) {
            $approvedCount = $loan->approvals->where('status', LoanApproval::STATUS_APPROVED)->count();
            $rejectedApproval = $loan->approvals->firstWhere('status', LoanApproval::STATUS_REJECTED);
            $currentApproval = $loan->approvals->where('status', LoanApproval::STATUS_PENDING)->sortBy('approval_level')->first();

            if ($rejectedApproval) {
                $loan->setAttribute('workspace_stage_label', _lang('Rejected'));
                $loan->setAttribute('workspace_stage_theme', 'critical');
                $loan->setAttribute('workspace_stage_meta', _lang('Stopped at') . ' ' . _lang($rejectedApproval->approval_level_name));
            } elseif ($currentApproval) {
                $loan->setAttribute('workspace_stage_label', _lang($currentApproval->approval_level_name));
                $loan->setAttribute('workspace_stage_theme', $approvedCount > 0 ? 'info' : 'review');
                $loan->setAttribute('workspace_stage_meta', $approvedCount . '/4 ' . _lang('approvals complete'));
            } else {
                $loan->setAttribute('workspace_stage_label', _lang('Queued'));
                $loan->setAttribute('workspace_stage_theme', 'pending');
                $loan->setAttribute('workspace_stage_meta', _lang('Waiting for approval records'));
            }

            return $loan;
        })->values();

        $activeLoans = Loan::with(['borrower', 'loan_product', 'currency'])
            ->where('status', 1)
            ->latest('id')
            ->limit(8)
            ->get();

        $readyForDisbursementCollection = Loan::with(['borrower', 'loan_product', 'currency', 'approvals'])
            ->where('status', 1)
            ->latest('id')
            ->get()
            ->filter(function ($loan) {
                return $loan->isFullyApproved() && $loan->disburseTransaction()->doesntExist();
            })
            ->values();

        $readyForDisbursement = $readyForDisbursementCollection->take(8)->values();

        $approvalBlockers = $pendingLoanQueue->take(8)->map(function ($loan) {
            $approvedCount = $loan->approvals->where('status', LoanApproval::STATUS_APPROVED)->count();
            $rejectedApproval = $loan->approvals->firstWhere('status', LoanApproval::STATUS_REJECTED);
            $currentApproval = $loan->approvals->where('status', LoanApproval::STATUS_PENDING)->sortBy('approval_level')->first();

            if ($rejectedApproval) {
                $blockerLabel = _lang('Rejected at') . ' ' . _lang($rejectedApproval->approval_level_name);
                $blockerTheme = 'critical';
            } elseif ($currentApproval) {
                $blockerLabel = _lang('Waiting for') . ' ' . _lang($currentApproval->approval_level_name);
                $blockerTheme = 'review';
            } else {
                $blockerLabel = _lang('Approval chain incomplete');
                $blockerTheme = 'pending';
            }

            return (object) [
                'id' => $loan->id,
                'loan_id' => $loan->loan_id,
                'borrower_name' => $loan->borrower->name,
                'product_name' => $loan->loan_product->name,
                'amount' => decimalPlace($loan->applied_amount, optional($loan->currency)->name),
                'progress' => $approvedCount . '/4',
                'blocker_label' => $blockerLabel,
                'blocker_theme' => $blockerTheme,
            ];
        })->values();

        $approvalBottlenecks = collect([
            ['level' => LoanApproval::LEVEL_TRUSTEE_1, 'label' => _lang('Trustee 1 Pending')],
            ['level' => LoanApproval::LEVEL_TRUSTEE_2, 'label' => _lang('Trustee 2 Pending')],
            ['level' => LoanApproval::LEVEL_SECRETARY, 'label' => _lang('Secretary Pending')],
            ['level' => LoanApproval::LEVEL_CHAIRMAN, 'label' => _lang('Chairman Pending')],
        ])->map(function ($item) use ($pendingLoanQueue) {
            $count = $pendingLoanQueue->filter(function ($loan) use ($item) {
                $currentApproval = $loan->approvals->where('status', LoanApproval::STATUS_PENDING)->sortBy('approval_level')->first();
                return $currentApproval && (int) $currentApproval->approval_level === (int) $item['level'];
            })->count();

            return (object) [
                'label' => $item['label'],
                'count' => $count,
            ];
        })->values();

        $dueTodayRepayments = LoanRepayment::with($repaymentRelations)
            ->whereDate('repayment_date', $today->toDateString())
            ->where('status', 0)
            ->orderBy('repayment_date', 'asc')
            ->limit(10)
            ->get();

        $upcomingRepayments = LoanRepayment::with($repaymentRelations)
            ->whereBetween('repayment_date', [$tomorrow->toDateString(), $nextSevenDays->toDateString()])
            ->where('status', 0)
            ->orderBy('repayment_date', 'asc')
            ->limit(10)
            ->get();

        $overdueRepayments = LoanRepayment::with($repaymentRelations)
            ->where('repayment_date', '<', $today->toDateString())
            ->where('status', 0)
            ->orderBy('repayment_date', 'asc')
            ->limit(10)
            ->get();

        $overdueRepaymentsForBuckets = LoanRepayment::with($repaymentRelations)
            ->where('repayment_date', '<', $today->toDateString())
            ->where('status', 0)
            ->get();

        $collectionPriorityQueue = $overdueRepaymentsForBuckets->map(function ($repayment) use ($today, $followUpsEnabled) {
            $daysLate = Carbon::parse($repayment->raw_repayment_date)->diffInDays($today);
            $priorityScore = ($daysLate * 10) + (float) $repayment->amount_to_pay;
            $latestFollowUp = $followUpsEnabled ? $repayment->latestFollowUp : null;

            if ($daysLate >= 31) {
                $actionLabel = _lang('Escalate and recover urgently');
                $actionTheme = 'critical';
            } elseif ($daysLate >= 8) {
                $actionLabel = _lang('Branch follow-up required');
                $actionTheme = 'overdue';
            } else {
                $actionLabel = _lang('Contact borrower today');
                $actionTheme = 'review';
            }

            return (object) [
                'repayment_id' => $repayment->id,
                'loan_id' => $repayment->loan->loan_id,
                'loan_route_id' => $repayment->loan_id,
                'borrower_name' => $repayment->loan->borrower->name,
                'branch_name' => optional($repayment->loan->borrower->branch)->name,
                'repayment_date' => $repayment->repayment_date,
                'amount' => decimalPlace($repayment->amount_to_pay, optional($repayment->loan->currency)->name),
                'days_late' => $daysLate,
                'priority_score' => $priorityScore,
                'contact_phone' => trim(($repayment->loan->borrower->country_code ?? '') . ' ' . ($repayment->loan->borrower->mobile ?? '')),
                'last_outcome_label' => optional($latestFollowUp)->outcome_text,
                'last_outcome_theme' => optional($latestFollowUp)->outcome_theme,
                'last_note' => optional($latestFollowUp)->note,
                'action_label' => $actionLabel,
                'action_theme' => $actionTheme,
            ];
        })->sortByDesc('priority_score')->take(8)->values();

        $collectorReadyCallList = collect($dueTodayRepayments->map(function ($repayment) use ($followUpsEnabled) {
            $latestFollowUp = $followUpsEnabled ? $repayment->latestFollowUp : null;
            return (object) [
                'repayment_id' => $repayment->id,
                'loan_id' => $repayment->loan->loan_id,
                'loan_route_id' => $repayment->loan_id,
                'borrower_name' => $repayment->loan->borrower->name,
                'branch_name' => optional($repayment->loan->borrower->branch)->name,
                'contact_phone' => trim(($repayment->loan->borrower->country_code ?? '') . ' ' . ($repayment->loan->borrower->mobile ?? '')),
                'amount' => decimalPlace($repayment->amount_to_pay, optional($repayment->loan->currency)->name),
                'queue_label' => _lang('Due Today'),
                'queue_theme' => 'today',
                'last_outcome_label' => optional($latestFollowUp)->outcome_text,
                'last_outcome_theme' => optional($latestFollowUp)->outcome_theme,
                'next_action' => _lang('Call before close of day'),
                'priority_score' => 200 + (float) $repayment->amount_to_pay,
            ];
        })->all())->merge(
            $overdueRepaymentsForBuckets->map(function ($repayment) use ($today, $followUpsEnabled) {
                $daysLate = Carbon::parse($repayment->raw_repayment_date)->diffInDays($today);
                $latestFollowUp = $followUpsEnabled ? $repayment->latestFollowUp : null;

                return (object) [
                    'repayment_id' => $repayment->id,
                    'loan_id' => $repayment->loan->loan_id,
                    'loan_route_id' => $repayment->loan_id,
                    'borrower_name' => $repayment->loan->borrower->name,
                    'branch_name' => optional($repayment->loan->borrower->branch)->name,
                    'contact_phone' => trim(($repayment->loan->borrower->country_code ?? '') . ' ' . ($repayment->loan->borrower->mobile ?? '')),
                    'amount' => decimalPlace($repayment->amount_to_pay, optional($repayment->loan->currency)->name),
                    'queue_label' => $daysLate >= 31 ? _lang('Critical') : _lang('Overdue'),
                    'queue_theme' => $daysLate >= 31 ? 'critical' : 'overdue',
                    'last_outcome_label' => optional($latestFollowUp)->outcome_text,
                    'last_outcome_theme' => optional($latestFollowUp)->outcome_theme,
                    'next_action' => $daysLate >= 31 ? _lang('Escalate to branch leadership') : _lang('Call borrower and confirm recovery plan'),
                    'priority_score' => ($daysLate >= 31 ? 400 : 300) + (float) $repayment->amount_to_pay,
                ];
            })->all()
        )->sortByDesc('priority_score')->take(8)->values();

        $upcomingReminderQueue = $upcomingRepayments->map(function ($repayment) use ($today, $followUpsEnabled) {
            $daysUntil = $today->diffInDays(Carbon::parse($repayment->raw_repayment_date));
            $latestFollowUp = $followUpsEnabled ? $repayment->latestFollowUp : null;

            return (object) [
                'repayment_id' => $repayment->id,
                'loan_id' => $repayment->loan->loan_id,
                'loan_route_id' => $repayment->loan_id,
                'borrower_name' => $repayment->loan->borrower->name,
                'branch_name' => optional($repayment->loan->borrower->branch)->name,
                'contact_phone' => trim(($repayment->loan->borrower->country_code ?? '') . ' ' . ($repayment->loan->borrower->mobile ?? '')),
                'amount' => decimalPlace($repayment->amount_to_pay, optional($repayment->loan->currency)->name),
                'days_until' => $daysUntil,
                'last_outcome_label' => optional($latestFollowUp)->outcome_text,
                'last_outcome_theme' => optional($latestFollowUp)->outcome_theme,
                'reminder_label' => $daysUntil <= 2 ? _lang('Reminder today') : _lang('Schedule reminder'),
                'reminder_theme' => $daysUntil <= 2 ? 'today' : 'upcoming',
            ];
        })->sortBy('days_until')->take(8)->values();

        $branchCollectionsPressure = Branch::get()->map(function ($branch) use ($today) {
            $dueTodayCount = LoanRepayment::whereDate('repayment_date', $today->toDateString())
                ->where('status', 0)
                ->whereHas('loan.borrower', function ($query) use ($branch) {
                    $query->where('branch_id', $branch->id);
                })->count();

            $overdueCount = LoanRepayment::where('repayment_date', '<', $today->toDateString())
                ->where('status', 0)
                ->whereHas('loan.borrower', function ($query) use ($branch) {
                    $query->where('branch_id', $branch->id);
                })->count();

            $criticalCount = LoanRepayment::where('repayment_date', '<', $today->copy()->subDays(30)->toDateString())
                ->where('status', 0)
                ->whereHas('loan.borrower', function ($query) use ($branch) {
                    $query->where('branch_id', $branch->id);
                })->count();

            return (object) [
                'name' => $branch->name,
                'due_today' => $dueTodayCount,
                'overdue' => $overdueCount,
                'critical' => $criticalCount,
                'pressure_score' => $dueTodayCount + $overdueCount + ($criticalCount * 2),
            ];
        })->sortByDesc('pressure_score')->take(5)->values();

        $collectionsBuckets = collect([
            ['label' => _lang('1-7 Days Late'), 'key' => '1_7', 'theme' => 'warning'],
            ['label' => _lang('8-30 Days Late'), 'key' => '8_30', 'theme' => 'danger'],
            ['label' => _lang('31+ Days Late'), 'key' => '31_plus', 'theme' => 'dark'],
        ])->map(function ($bucket) use ($overdueRepaymentsForBuckets, $today) {
            $items = $overdueRepaymentsForBuckets->filter(function ($repayment) use ($bucket, $today) {
                $daysLate = Carbon::parse($repayment->raw_repayment_date)->diffInDays($today);

                if ($bucket['key'] === '1_7') {
                    return $daysLate >= 1 && $daysLate <= 7;
                }

                if ($bucket['key'] === '8_30') {
                    return $daysLate >= 8 && $daysLate <= 30;
                }

                return $daysLate >= 31;
            });

            return (object) [
                'label' => $bucket['label'],
                'theme' => $bucket['theme'],
                'count' => $items->count(),
                'amount' => (float) $items->sum('amount_to_pay'),
            ];
        })->values();

        $loanProducts = LoanProduct::latest('id')->get();
        $loanCustomFields = CustomField::where('table', 'loans')->orderBy('id', 'asc')->get();
        $loanApproverSettings = LoanApproverSetting::with('approver')
            ->orderBy('approval_level', 'asc')
            ->get();

        foreach (LoanApproverSetting::getApprovalLevels() as $level => $levelName) {
            if (! $loanApproverSettings->where('approval_level', $level)->first()) {
                $setting = new LoanApproverSetting();
                $setting->approval_level = $level;
                $setting->approval_level_name = $levelName;
                $setting->status = 0;
                $setting->tenant_id = request()->tenant->id;
                $setting->save();
                $loanApproverSettings->push($setting->load('approver'));
            }
        }

        $loanApproverSettings = $loanApproverSettings->sortBy('approval_level')->values();

        return view('backend.admin.loan.workspace', [
            'page_title' => _lang('Loans'),
            'loanStats' => [
                'pending' => Loan::where('status', 0)->count(),
                'active' => Loan::where('status', 1)->count(),
                'due_today' => LoanRepayment::whereDate('repayment_date', $today->toDateString())->where('status', 0)->count(),
                'upcoming' => LoanRepayment::whereBetween('repayment_date', [$tomorrow->toDateString(), $nextSevenDays->toDateString()])->where('status', 0)->count(),
                'overdue' => LoanRepayment::where('repayment_date', '<', $today->toDateString())->where('status', 0)->count(),
                'products' => LoanProduct::count(),
                'ready_disbursement' => $readyForDisbursementCollection->count(),
                'critical_collections' => $overdueRepaymentsForBuckets->filter(function ($repayment) use ($today) {
                    return Carbon::parse($repayment->raw_repayment_date)->diffInDays($today) >= 31;
                })->count(),
            ],
            'pendingLoans' => $pendingLoans,
            'activeLoans' => $activeLoans,
            'readyForDisbursement' => $readyForDisbursement,
            'approvalBlockers' => $approvalBlockers,
            'approvalBottlenecks' => $approvalBottlenecks,
            'dueTodayRepayments' => $dueTodayRepayments,
            'upcomingRepayments' => $upcomingRepayments,
            'overdueRepayments' => $overdueRepayments,
            'collectionsBuckets' => $collectionsBuckets,
            'collectionPriorityQueue' => $collectionPriorityQueue,
            'collectorReadyCallList' => $collectorReadyCallList,
            'upcomingReminderQueue' => $upcomingReminderQueue,
            'branchCollectionsPressure' => $branchCollectionsPressure,
            'followUpsEnabled' => $followUpsEnabled,
            'collectionExecutionStats' => $followUpOverview['stats'],
            'promiseFollowUpQueue' => $followUpOverview['promiseFollowUpQueue'],
            'recentResolvedCases' => $followUpOverview['recentResolvedCases'],
            'branchFollowUpPerformance' => $followUpOverview['branchPerformance'],
            'collectorFollowUpPerformance' => $followUpOverview['collectorPerformance'],
            'collectionDateRange' => $dateRange,
            'loanProducts' => $loanProducts,
            'loanApproverSettings' => $loanApproverSettings,
            'loanCustomFields' => $loanCustomFields,
        ]);
    }
}
