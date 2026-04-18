<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\LoanCollectionFollowUp;
use App\Models\LoanRepayment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class CollectionFollowUpInsightsService
{
    public function enabled(): bool
    {
        return Schema::hasTable('loan_collection_follow_ups');
    }

    public function resolveRange(?string $fromDate, ?string $toDate, ?Carbon $fallbackDate = null): array
    {
        $fallbackDate = $fallbackDate ?: Carbon::today();

        try {
            $rangeStart = $fromDate ? Carbon::parse($fromDate)->startOfDay() : $fallbackDate->copy()->startOfDay();
        } catch (\Throwable $e) {
            $rangeStart = $fallbackDate->copy()->startOfDay();
        }

        try {
            $rangeEnd = $toDate ? Carbon::parse($toDate)->endOfDay() : $rangeStart->copy()->endOfDay();
        } catch (\Throwable $e) {
            $rangeEnd = $rangeStart->copy()->endOfDay();
        }

        if ($rangeStart->gt($rangeEnd)) {
            [$rangeStart, $rangeEnd] = [$rangeEnd->copy()->startOfDay(), $rangeStart->copy()->endOfDay()];
        }

        return [
            'start' => $rangeStart,
            'end' => $rangeEnd,
            'from_date' => $rangeStart->toDateString(),
            'to_date' => $rangeEnd->toDateString(),
            'display_value' => $rangeStart->format('Y-m-d') . ' - ' . $rangeEnd->format('Y-m-d'),
            'label' => $rangeStart->isSameDay($rangeEnd)
                ? $rangeStart->format(get_date_format())
                : $rangeStart->format(get_date_format()) . ' - ' . $rangeEnd->format(get_date_format()),
            'is_single_day' => $rangeStart->isSameDay($rangeEnd),
        ];
    }

    public function overview(Carbon $rangeStart, Carbon $rangeEnd, int $queueLimit = 6, int $branchLimit = 5, int $collectorLimit = 5): array
    {
        if (! $this->enabled()) {
            return [
                'enabled' => false,
                'stats' => [
                    'logged_today' => 0,
                    'touched_today' => 0,
                    'promise_due_today' => 0,
                    'broken_promises' => 0,
                    'escalated_today' => 0,
                    'unreachable_today' => 0,
                    'resolved_in_range' => 0,
                    'recovered_in_range' => 0,
                    'promise_kept' => 0,
                    'open_queue' => 0,
                    'completion_rate' => 0,
                ],
                'promiseFollowUpQueue' => collect(),
                'recentResolvedCases' => collect(),
                'branchPerformance' => collect(),
                'collectorPerformance' => collect(),
            ];
        }

        $fromDate = $rangeStart->toDateString();
        $toDate = $rangeEnd->toDateString();

        $repaymentRelations = ['loan.borrower.branch', 'loan.currency', 'latestFollowUp.createdBy', 'payment'];

        $openQueueCount = LoanRepayment::whereDate('repayment_date', '<=', $toDate)
            ->where('status', 0)
            ->count();

        $followUpsInRange = LoanCollectionFollowUp::with(['createdBy', 'repayment.loan.borrower.branch', 'repayment.payment'])
            ->whereBetween('created_at', [$rangeStart->copy()->startOfDay(), $rangeEnd->copy()->endOfDay()])
            ->get();

        $loggedTodayCount = $followUpsInRange->count();
        $touchedTodayCount = $followUpsInRange->pluck('loan_repayment_id')->filter()->unique()->count();
        $escalatedTodayCount = $followUpsInRange->where('outcome', LoanCollectionFollowUp::OUTCOME_ESCALATED)->count();
        $unreachableTodayCount = $followUpsInRange->where('outcome', LoanCollectionFollowUp::OUTCOME_UNREACHABLE)->count();
        $resolvedLoggedCount = $followUpsInRange->where('outcome', LoanCollectionFollowUp::OUTCOME_RESOLVED)->pluck('loan_repayment_id')->filter()->unique()->count();

        $promiseDueTodayCount = LoanRepayment::where('status', 0)
            ->whereHas('latestFollowUp', function ($query) use ($fromDate, $toDate) {
                $query->where('outcome', LoanCollectionFollowUp::OUTCOME_PROMISED_TO_PAY)
                    ->whereBetween('promised_payment_date', [$fromDate, $toDate]);
            })->count();

        $brokenPromiseCount = LoanRepayment::where('status', 0)
            ->whereHas('latestFollowUp', function ($query) use ($toDate) {
                $query->where('outcome', LoanCollectionFollowUp::OUTCOME_PROMISED_TO_PAY)
                    ->whereDate('promised_payment_date', '<', $toDate);
            })->count();

        $paidRepaymentsInRange = LoanRepayment::with($repaymentRelations)
            ->where('status', 1)
            ->whereHas('payment', function ($query) use ($fromDate, $toDate) {
                $query->whereBetween('paid_at', [$fromDate, $toDate]);
            })->whereHas('followUps')
            ->get();

        $promiseKeptCount = $paidRepaymentsInRange->filter(function ($repayment) {
            $latestFollowUp = $repayment->latestFollowUp;
            $paymentDateRaw = optional($repayment->payment)->getRawOriginal('paid_at');

            if (! $latestFollowUp || (int) $latestFollowUp->outcome !== LoanCollectionFollowUp::OUTCOME_PROMISED_TO_PAY || ! $latestFollowUp->promised_payment_date || ! $paymentDateRaw) {
                return false;
            }

            return Carbon::parse($paymentDateRaw)->lte($latestFollowUp->promised_payment_date->copy()->endOfDay());
        })->count();

        $recoveredAfterFollowUpCount = $paidRepaymentsInRange->count();

        $promiseFollowUpQueue = LoanRepayment::with($repaymentRelations)
            ->where('status', 0)
            ->whereHas('latestFollowUp', function ($query) use ($toDate) {
                $query->where('outcome', LoanCollectionFollowUp::OUTCOME_PROMISED_TO_PAY)
                    ->whereDate('promised_payment_date', '<=', $toDate);
            })->get()->map(function ($repayment) use ($rangeEnd) {
                $latestFollowUp = $repayment->latestFollowUp;
                $promisedDate = $latestFollowUp ? $latestFollowUp->promised_payment_date : null;
                $isBroken = $promisedDate ? $promisedDate->lt($rangeEnd->copy()->startOfDay()) : false;

                return (object) [
                    'repayment_id' => $repayment->id,
                    'loan_id' => $repayment->loan->loan_id,
                    'loan_route_id' => $repayment->loan_id,
                    'borrower_name' => $repayment->loan->borrower->name,
                    'branch_name' => optional($repayment->loan->borrower->branch)->name,
                    'contact_phone' => trim(($repayment->loan->borrower->country_code ?? '') . ' ' . ($repayment->loan->borrower->mobile ?? '')),
                    'promised_payment_date' => $promisedDate ? $promisedDate->format(get_date_format()) : _lang('N/A'),
                    'promised_payment_date_raw' => $promisedDate ? $promisedDate->toDateString() : null,
                    'promise_status_label' => $isBroken ? _lang('Broken Promise') : _lang('Promise Due'),
                    'promise_status_theme' => $isBroken ? 'critical' : 'today',
                    'days_past_promise' => $isBroken && $promisedDate ? $promisedDate->diffInDays($rangeEnd->copy()->startOfDay()) : 0,
                    'last_note' => optional($latestFollowUp)->note,
                    'logged_by' => optional(optional($latestFollowUp)->createdBy)->name,
                ];
            })->sortByDesc(function ($item) {
                return ($item->days_past_promise * 1000000) + (strtotime((string) $item->promised_payment_date_raw) ?: 0);
            })->take($queueLimit)->values();

        $recentResolvedCases = $paidRepaymentsInRange->map(function ($repayment) {
            $latestFollowUp = $repayment->latestFollowUp;
            $paymentDateRaw = optional($repayment->payment)->getRawOriginal('paid_at');
            $paymentDate = $paymentDateRaw ? Carbon::parse($paymentDateRaw) : null;
            $promiseKept = false;
            $resolutionLabel = _lang('Recovered After Follow-up');
            $resolutionTheme = 'active';

            if ($latestFollowUp && (int) $latestFollowUp->outcome === LoanCollectionFollowUp::OUTCOME_RESOLVED) {
                $resolutionLabel = _lang('Manually Resolved');
                $resolutionTheme = 'info';
            }

            if ($latestFollowUp && (int) $latestFollowUp->outcome === LoanCollectionFollowUp::OUTCOME_PROMISED_TO_PAY && $latestFollowUp->promised_payment_date && $paymentDate && $paymentDate->lte($latestFollowUp->promised_payment_date->copy()->endOfDay())) {
                $promiseKept = true;
                $resolutionLabel = _lang('Promise Kept');
                $resolutionTheme = 'ready';
            }

            return (object) [
                'repayment_id' => $repayment->id,
                'loan_id' => $repayment->loan->loan_id,
                'loan_route_id' => $repayment->loan_id,
                'borrower_name' => $repayment->loan->borrower->name,
                'branch_name' => optional($repayment->loan->borrower->branch)->name,
                'payment_date' => $paymentDate ? $paymentDate->format(get_date_format()) : _lang('N/A'),
                'payment_date_raw' => $paymentDate ? $paymentDate->toDateString() : null,
                'resolution_label' => $resolutionLabel,
                'resolution_theme' => $resolutionTheme,
                'promise_kept' => $promiseKept,
                'last_outcome_label' => optional($latestFollowUp)->outcome_text,
                'last_outcome_theme' => optional($latestFollowUp)->outcome_theme,
            ];
        })->sortByDesc(function ($item) {
            return strtotime((string) $item->payment_date_raw) ?: 0;
        })->take($queueLimit)->values();

        $branchPerformance = Branch::get()->map(function ($branch) use ($followUpsInRange, $paidRepaymentsInRange, $toDate) {
            $openQueue = LoanRepayment::whereDate('repayment_date', '<=', $toDate)
                ->where('status', 0)
                ->whereHas('loan.borrower', function ($query) use ($branch) {
                    $query->where('branch_id', $branch->id);
                })->count();

            $branchFollowUps = $followUpsInRange->filter(function ($followUp) use ($branch) {
                return (int) optional(optional(optional($followUp->repayment)->loan)->borrower)->branch_id === (int) $branch->id;
            });

            $resolvedInRange = $paidRepaymentsInRange->filter(function ($repayment) use ($branch) {
                return (int) optional($repayment->loan->borrower)->branch_id === (int) $branch->id;
            })->count();

            $touchedToday = $branchFollowUps->pluck('loan_repayment_id')->filter()->unique()->count();
            $promisedToday = $branchFollowUps->where('outcome', LoanCollectionFollowUp::OUTCOME_PROMISED_TO_PAY)->count();
            $escalatedToday = $branchFollowUps->where('outcome', LoanCollectionFollowUp::OUTCOME_ESCALATED)->count();
            $completionRate = $openQueue > 0 ? (int) round(($touchedToday / $openQueue) * 100) : 0;

            return (object) [
                'name' => $branch->name,
                'open_queue' => $openQueue,
                'touched_today' => $touchedToday,
                'promised_today' => $promisedToday,
                'escalated_today' => $escalatedToday,
                'resolved_in_range' => $resolvedInRange,
                'completion_rate' => $completionRate,
                'pressure_score' => $openQueue + ($escalatedToday * 2),
            ];
        })->filter(function ($branch) {
            return $branch->open_queue > 0 || $branch->touched_today > 0 || $branch->escalated_today > 0 || $branch->resolved_in_range > 0;
        })->sortByDesc('pressure_score')->take($branchLimit)->values();

        $collectorPerformance = $followUpsInRange->groupBy('created_user_id')->map(function ($items) {
            $first = $items->first();
            $touchedCount = $items->pluck('loan_repayment_id')->filter()->unique()->count();
            $resolvedCount = $items->filter(function ($followUp) {
                return (int) optional($followUp->repayment)->status === 1 || (int) $followUp->outcome === LoanCollectionFollowUp::OUTCOME_RESOLVED;
            })->pluck('loan_repayment_id')->filter()->unique()->count();

            return (object) [
                'name' => optional($first->createdBy)->name ?? _lang('N/A'),
                'logs_count' => $items->count(),
                'cases_touched' => $touchedCount,
                'promised_count' => $items->where('outcome', LoanCollectionFollowUp::OUTCOME_PROMISED_TO_PAY)->count(),
                'escalated_count' => $items->where('outcome', LoanCollectionFollowUp::OUTCOME_ESCALATED)->count(),
                'unreachable_count' => $items->where('outcome', LoanCollectionFollowUp::OUTCOME_UNREACHABLE)->count(),
                'resolved_count' => $resolvedCount,
                'performance_score' => ($touchedCount * 10) + ($resolvedCount * 8) + $items->count(),
            ];
        })->sortByDesc('performance_score')->take($collectorLimit)->values();

        return [
            'enabled' => true,
            'stats' => [
                'logged_today' => $loggedTodayCount,
                'touched_today' => $touchedTodayCount,
                'promise_due_today' => $promiseDueTodayCount,
                'broken_promises' => $brokenPromiseCount,
                'escalated_today' => $escalatedTodayCount,
                'unreachable_today' => $unreachableTodayCount,
                'resolved_in_range' => max($resolvedLoggedCount, $recoveredAfterFollowUpCount),
                'recovered_in_range' => $recoveredAfterFollowUpCount,
                'promise_kept' => $promiseKeptCount,
                'open_queue' => $openQueueCount,
                'completion_rate' => $openQueueCount > 0 ? (int) round(($touchedTodayCount / $openQueueCount) * 100) : 0,
            ],
            'promiseFollowUpQueue' => $promiseFollowUpQueue,
            'recentResolvedCases' => $recentResolvedCases,
            'branchPerformance' => $branchPerformance,
            'collectorPerformance' => $collectorPerformance,
        ];
    }
}
