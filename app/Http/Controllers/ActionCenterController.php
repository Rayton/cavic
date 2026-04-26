<?php

namespace App\Http\Controllers;

use App\Models\BankTransaction;
use App\Models\Branch;
use App\Models\DepositRequest;
use App\Models\Loan;
use App\Models\LoanApproval;
use App\Models\LoanRepayment;
use App\Models\Member;
use App\Models\Transaction;
use App\Models\WithdrawRequest;
use App\Services\CollectionFollowUpInsightsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class ActionCenterController extends Controller
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

        $memberRequests = Member::withoutGlobalScopes(['status'])
            ->with('branch')
            ->where('status', 0)
            ->latest('id')
            ->limit(8)
            ->get();

        $pendingLoans = Loan::with(['borrower', 'loan_product', 'currency', 'approvals'])
            ->where('status', 0)
            ->latest('id')
            ->limit(8)
            ->get()
            ->map(function ($loan) {
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

        $depositRequests = DepositRequest::with(['member', 'method', 'account.savings_type.currency'])
            ->where('status', 0)
            ->latest('id')
            ->limit(8)
            ->get();

        $withdrawRequests = WithdrawRequest::with(['member', 'method', 'account.savings_type.currency'])
            ->where('status', 0)
            ->latest('id')
            ->limit(8)
            ->get();

        $dueTodayRepayments = LoanRepayment::with($repaymentRelations)
            ->whereDate('repayment_date', $today->toDateString())
            ->where('status', 0)
            ->orderBy('repayment_date', 'asc')
            ->limit(8)
            ->get();

        $upcomingRepayments = LoanRepayment::with($repaymentRelations)
            ->whereBetween('repayment_date', [$tomorrow->toDateString(), $nextSevenDays->toDateString()])
            ->where('status', 0)
            ->orderBy('repayment_date', 'asc')
            ->limit(8)
            ->get();

        $readyForDisbursementCount = Loan::with(['approvals'])
            ->where('status', 1)
            ->get()
            ->filter(function ($loan) {
                return $loan->isFullyApproved() && $loan->disburseTransaction()->doesntExist();
            })
            ->count();

        $overdueRepayments = LoanRepayment::with($repaymentRelations)
            ->where('repayment_date', '<', $today->toDateString())
            ->where('status', 0)
            ->orderBy('repayment_date', 'asc')
            ->limit(6)
            ->get();

        $overdueRepaymentsForBuckets = LoanRepayment::with($repaymentRelations)
            ->where('repayment_date', '<', $today->toDateString())
            ->where('status', 0)
            ->get();

        $collectionBuckets = collect([
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
            ];
        })->values();

        $collectionPriorityQueue = $overdueRepaymentsForBuckets->map(function ($repayment) use ($today, $followUpsEnabled) {
            $daysLate = Carbon::parse($repayment->raw_repayment_date)->diffInDays($today);
            $latestFollowUp = $followUpsEnabled ? $repayment->latestFollowUp : null;

            return (object) [
                'repayment_id' => $repayment->id,
                'loan_id' => $repayment->loan->loan_id,
                'loan_route_id' => $repayment->loan_id,
                'borrower_name' => $repayment->loan->borrower->name,
                'branch_name' => optional($repayment->loan->borrower->branch)->name,
                'contact_phone' => trim(($repayment->loan->borrower->country_code ?? '') . ' ' . ($repayment->loan->borrower->mobile ?? '')),
                'days_late' => $daysLate,
                'last_outcome_label' => optional($latestFollowUp)->outcome_text,
                'last_outcome_theme' => optional($latestFollowUp)->outcome_theme,
                'action_label' => $daysLate >= 31 ? _lang('Escalate now') : ($daysLate >= 8 ? _lang('Branch follow-up') : _lang('Contact today')),
                'action_theme' => $daysLate >= 31 ? 'critical' : ($daysLate >= 8 ? 'overdue' : 'review'),
                'priority_score' => ($daysLate * 10) + (float) $repayment->amount_to_pay,
            ];
        })->sortByDesc('priority_score')->take(6)->values();

        $collectorReadyCallList = collect($dueTodayRepayments->map(function ($repayment) use ($followUpsEnabled) {
            $latestFollowUp = $followUpsEnabled ? $repayment->latestFollowUp : null;
            return (object) [
                'repayment_id' => $repayment->id,
                'loan_id' => $repayment->loan->loan_id,
                'loan_route_id' => $repayment->loan_id,
                'borrower_name' => $repayment->loan->borrower->name,
                'branch_name' => optional($repayment->loan->borrower->branch)->name,
                'contact_phone' => trim(($repayment->loan->borrower->country_code ?? '') . ' ' . ($repayment->loan->borrower->mobile ?? '')),
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
                    'queue_label' => $daysLate >= 31 ? _lang('Critical') : _lang('Overdue'),
                    'queue_theme' => $daysLate >= 31 ? 'critical' : 'overdue',
                    'last_outcome_label' => optional($latestFollowUp)->outcome_text,
                    'last_outcome_theme' => optional($latestFollowUp)->outcome_theme,
                    'next_action' => $daysLate >= 31 ? _lang('Escalate to branch leadership') : _lang('Call borrower and confirm recovery plan'),
                    'priority_score' => ($daysLate >= 31 ? 400 : 300) + (float) $repayment->amount_to_pay,
                ];
            })->all()
        )->sortByDesc('priority_score')->take(6)->values();

        $upcomingReminderQueue = $upcomingRepayments->map(function ($repayment) use ($today, $followUpsEnabled) {
            $daysUntil = $today->diffInDays(Carbon::parse($repayment->raw_repayment_date));
            $latestFollowUp = $followUpsEnabled ? $repayment->latestFollowUp : null;

            return (object) [
                'repayment_id' => $repayment->id,
                'loan_id' => $repayment->loan->loan_id,
                'borrower_name' => $repayment->loan->borrower->name,
                'branch_name' => optional($repayment->loan->borrower->branch)->name,
                'contact_phone' => trim(($repayment->loan->borrower->country_code ?? '') . ' ' . ($repayment->loan->borrower->mobile ?? '')),
                'days_until' => $daysUntil,
                'last_outcome_label' => optional($latestFollowUp)->outcome_text,
                'last_outcome_theme' => optional($latestFollowUp)->outcome_theme,
                'reminder_label' => $daysUntil <= 2 ? _lang('Reminder today') : _lang('Schedule reminder'),
                'reminder_theme' => $daysUntil <= 2 ? 'today' : 'upcoming',
            ];
        })->sortBy('days_until')->take(6)->values();

        $branchCollectionsPressure = Branch::get()->map(function ($branch) use ($today) {
            $overdueCount = LoanRepayment::where('repayment_date', '<', $today->toDateString())
                ->where('status', 0)
                ->whereHas('loan.borrower', function ($query) use ($branch) {
                    $query->where('branch_id', $branch->id);
                })->count();

            $dueTodayCount = LoanRepayment::whereDate('repayment_date', $today->toDateString())
                ->where('status', 0)
                ->whereHas('loan.borrower', function ($query) use ($branch) {
                    $query->where('branch_id', $branch->id);
                })->count();

            return (object) [
                'name' => $branch->name,
                'overdue' => $overdueCount,
                'due_today' => $dueTodayCount,
                'pressure_score' => $overdueCount + $dueTodayCount,
            ];
        })->sortByDesc('pressure_score')->take(5)->values();

        $pendingBankTransactionsCount = BankTransaction::where('status', 0)->count();
        $pendingCashTransactionsCount = Transaction::where('status', 0)->count();
        $pendingFinanceRequestsCount = DepositRequest::where('status', 0)->count() + WithdrawRequest::where('status', 0)->count();
        $brokenPromiseCount = $followUpOverview['stats']['broken_promises'] ?? 0;

        return view('backend.admin.action_center.index', [
            'page_title' => _lang('Action Center'),
            'assets' => ['datatable'],
            'memberRequests' => $memberRequests,
            'pendingLoans' => $pendingLoans,
            'depositRequests' => $depositRequests,
            'withdrawRequests' => $withdrawRequests,
            'dueTodayRepayments' => $dueTodayRepayments,
            'upcomingRepayments' => $upcomingRepayments,
            'overdueRepayments' => $overdueRepayments,
            'actionStats' => [
                'member_requests' => Member::withoutGlobalScopes(['status'])->where('status', 0)->count(),
                'pending_loans' => Loan::where('status', 0)->count(),
                'deposit_requests' => DepositRequest::where('status', 0)->count(),
                'withdraw_requests' => WithdrawRequest::where('status', 0)->count(),
                'due_today' => LoanRepayment::whereDate('repayment_date', $today->toDateString())->where('status', 0)->count(),
                'upcoming' => LoanRepayment::whereBetween('repayment_date', [$tomorrow->toDateString(), $nextSevenDays->toDateString()])->where('status', 0)->count(),
                'overdue' => LoanRepayment::where('repayment_date', '<', $today->toDateString())->where('status', 0)->count(),
                'critical_collections' => $overdueRepaymentsForBuckets->filter(function ($repayment) use ($today) {
                    return Carbon::parse($repayment->raw_repayment_date)->diffInDays($today) >= 31;
                })->count(),
                'ready_disbursement' => $readyForDisbursementCount,
                'pending_bank_transactions' => $pendingBankTransactionsCount,
                'pending_cash_transactions' => $pendingCashTransactionsCount,
                'pending_finance_requests' => $pendingFinanceRequestsCount,
                'broken_promises' => $brokenPromiseCount,
                'exception_total' => $pendingFinanceRequestsCount + $pendingBankTransactionsCount + $pendingCashTransactionsCount + LoanRepayment::where('repayment_date', '<', $today->toDateString())->where('status', 0)->count() + $readyForDisbursementCount + $brokenPromiseCount,
            ],
            'collectionBuckets' => $collectionBuckets,
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
        ]);
    }
}
