<?php
namespace App\Http\Controllers;

use App\Models\BankTransaction;
use App\Models\Branch;
use App\Models\DepositMethod;
use App\Models\DepositRequest;
use App\Models\Expense;
use App\Models\Loan;
use App\Models\LoanPayment;
use App\Models\LoanRepayment;
use App\Models\Member;
use App\Models\SavingsAccount;
use App\Models\Transaction;
use App\Models\WithdrawRequest;
use App\Services\CollectionFollowUpInsightsService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller {
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        date_default_timezone_set(get_timezone());
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request) {
        $user           = auth()->user();
        $user_type      = $user->user_type;
        $date           = date('Y-m-d');
        $data           = [];
        $data['assets'] = ['datatable'];

        if ($user_type == 'customer') {
            $memberId = $user->member->id;
            $data['recent_transactions'] = Transaction::where('member_id', $memberId)
                ->limit('10')
                ->orderBy('trans_date', 'desc')
                ->get();

            $data['loans'] = Loan::withoutGlobalScopes()
                ->where('status', 1)
                ->where('borrower_id', $memberId)
                ->with(['next_payment', 'currency'])
                ->withSum('repaymentTransactions', 'amount')
                ->get();

            // Your next deduction: sum of next payment amounts and earliest next payment date
            $nextTotal = 0;
            $nextDate = null;
            foreach ($data['loans'] as $loan) {
                if ($loan->next_payment && $loan->next_payment->id) {
                    $nextTotal += (float) ($loan->next_payment->amount_to_pay ?? 0);
                    $d = $loan->next_payment->getRawOriginal('repayment_date');
                    if ($d && ($nextDate === null || $d < $nextDate)) {
                        $nextDate = $d;
                    }
                }
            }
            $data['next_deduction_total'] = $nextTotal;
            $data['next_deduction_date'] = $nextDate;

            // Your last loan deduction: single most recent loan repayment (deduction)
            $lastRepayment = Transaction::where('member_id', $memberId)
                ->where('type', 'Loan_Repayment')
                ->orderBy('trans_date', 'desc')
                ->first();
            $data['last_deduction_total'] = $lastRepayment ? (float) $lastRepayment->amount : 0;
            $data['last_deduction_date'] = $lastRepayment ? $lastRepayment->getRawOriginal('trans_date') : null;

            // Your last Contributions: most recent hisa amana (credit to Hisa-type account only)
            $latestHisaContribution = Transaction::where('member_id', $memberId)
                ->where('dr_cr', 'cr')
                ->whereHas('account.savings_type', function ($q) {
                    $q->whereRaw('LOWER(name) LIKE ?', ['%hisa%']);
                })
                ->orderBy('trans_date', 'desc')
                ->first();
            $data['last_contributions_total'] = $latestHisaContribution ? (float) $latestHisaContribution->amount : 0;
            $data['last_contribution_date'] = $latestHisaContribution ? $latestHisaContribution->getRawOriginal('trans_date') : null;

            // Account types (Jamii, Hisa, etc.) – one row per account (individual), exclude Loans/Mkopo/Mikopo
            $accounts = get_account_details($memberId);
            $excludeTypes = ['loans', 'mkopo', 'mikopo'];
            $accountsFiltered = $accounts->filter(function ($a) use ($excludeTypes) {
                $name = strtolower($a->savings_type->name ?? '');
                return ! in_array($name, $excludeTypes);
            });
            $data['account_types_latest'] = [];
            foreach ($accountsFiltered as $account) {
                $typeName = $account->savings_type ? $account->savings_type->name : _lang('Account');
                $currencyName = $account->savings_type && $account->savings_type->currency ? $account->savings_type->currency->name : '';
                $lastDeposit = Transaction::where('member_id', $memberId)
                    ->where('dr_cr', 'cr')
                    ->where('savings_account_id', $account->id)
                    ->orderBy('trans_date', 'desc')
                    ->first();
                $data['account_types_latest'][] = [
                    'name' => $typeName . ' - ' . $account->account_number,
                    'latest_amount' => $lastDeposit ? (float) $lastDeposit->amount : 0,
                    'currency' => $currencyName,
                    'last_contribution_date' => $lastDeposit ? $lastDeposit->getRawOriginal('trans_date') : null,
                ];
            }

            // Total loan balance: sum of (total_payable - repaid) per loan, grouped by currency
            $data['total_loan_balance_by_currency'] = [];
            foreach ($data['loans'] as $loan) {
                $total = (float) ($loan->total_payable ?? $loan->applied_amount ?? 0);
                $repaid = (float) ($loan->repayment_transactions_sum_amount ?? 0);
                $balance = $total - $repaid;
                $currencyName = $loan->currency ? $loan->currency->name : _lang('N/A');
                $data['total_loan_balance_by_currency'][$currencyName] = ($data['total_loan_balance_by_currency'][$currencyName] ?? 0) + $balance;
            }

            // Loans that have an upcoming/due payment (for the Upcoming Loan Payment table)
            $data['upcoming_loans'] = $data['loans']->filter(function ($loan) {
                return $loan->next_payment && $loan->next_payment->id;
            })->values();

            // Next deduction by loan (for card breakdown): loan_id / product name => amount_to_pay
            $data['next_deduction_by_loan'] = [];
            foreach ($data['upcoming_loans'] as $loan) {
                $label = ($loan->loan_product ? $loan->loan_product->name : $loan->loan_id);
                $amt = (float) ($loan->next_payment->amount_to_pay ?? 0);
                $data['next_deduction_by_loan'][$label] = ($data['next_deduction_by_loan'][$label] ?? 0) + $amt;
            }

            // Interest analysis: total interest payable vs paid (for Interest Paid Progress)
            $totalInterestPayable = 0;
            foreach ($data['loans'] as $loan) {
                $totalPayable = (float) ($loan->total_payable ?? $loan->applied_amount ?? 0);
                $principal = (float) ($loan->applied_amount ?? 0);
                $totalInterestPayable += max(0, $totalPayable - $principal);
            }
            $loanIds = $data['loans']->pluck('id')->toArray();
            $totalInterestPaid = $loanIds
                ? (float) LoanPayment::withoutGlobalScopes()->whereIn('loan_id', $loanIds)->sum('interest')
                : 0;
            $data['total_interest_payable'] = $totalInterestPayable;
            $data['total_interest_paid'] = $totalInterestPaid;
            $data['interest_paid_pct'] = $totalInterestPayable > 0
                ? round(($totalInterestPaid / $totalInterestPayable) * 100, 1)
                : 0;

            // For deposit modal: manual methods (with charge limits) and member's savings accounts (match manual_deposit form)
            $data['deposit_methods']   = DepositMethod::with('currency', 'chargeLimits')->where('status', 1)->get();
            $data['deposit_accounts']  = SavingsAccount::with('savings_type', 'savings_type.currency')
                ->where('member_id', $memberId)
                ->get();

            return view("backend.customer.dashboard-$user_type", $data);
        } else {
            $data['recent_transactions'] = Transaction::limit('10')
                ->orderBy('trans_date', 'desc')
                ->get();

            $data['due_repayments'] = LoanRepayment::selectRaw('loan_repayments.loan_id, MAX(repayment_date) as repayment_date, COUNT(id) as total_due_repayment, SUM(principal_amount) as total_due')
                ->with('loan')
                ->whereRaw("repayment_date < '$date'")
                ->where('status', 0)
                ->groupBy('loan_id')
                ->get();

            $data['loan_balances'] = Loan::where('status', 1)
                ->selectRaw('currency_id, SUM(applied_amount) as total_amount, SUM(total_paid) as total_paid')
                ->with('currency')
                ->groupBy('currency_id')
                ->get();

            $data['total_customer'] = Member::count();
            $data['active_borrowers_count'] = Loan::where('status', 1)->distinct('borrower_id')->count('borrower_id');
            $data['pending_deposit_requests_count'] = DepositRequest::where('status', 0)->count();
            $data['pending_withdraw_requests_count'] = WithdrawRequest::where('status', 0)->count();
            $data['pending_finance_requests_count'] = $data['pending_deposit_requests_count'] + $data['pending_withdraw_requests_count'];
            $data['pending_cash_transactions_count'] = Transaction::where('status', 0)->count();
            $data['pending_bank_transactions_count'] = BankTransaction::where('status', 0)->count();
            $data['finance_exception_count'] = $data['pending_finance_requests_count'] + $data['pending_cash_transactions_count'] + $data['pending_bank_transactions_count'];
            $data['today_due_count'] = LoanRepayment::whereDate('repayment_date', $date)
                ->where('status', 0)
                ->count();
            $data['due_this_week_count'] = LoanRepayment::whereBetween('repayment_date', [Carbon::today()->addDay()->toDateString(), Carbon::today()->addDays(7)->toDateString()])
                ->where('status', 0)
                ->count();
            $data['overdue_repayments_count'] = LoanRepayment::whereDate('repayment_date', '<', $date)
                ->where('status', 0)
                ->count();
            $data['today_transactions_count'] = Transaction::whereDate('trans_date', $date)->count();
            $data['today_expenses_count'] = Expense::whereDate('expense_date', $date)->count();
            $readyForDisbursementLoans = Loan::with('currency')
                ->where('status', 1)
                ->whereDoesntHave('disburseTransaction')
                ->whereHas('approvals', function ($query) {
                    $query->where('status', 1);
                }, '>=', 4)
                ->get();
            $data['ready_for_disbursement_count'] = $readyForDisbursementLoans->count();

            $followUpsEnabled = Schema::hasTable('loan_collection_follow_ups');
            $followUpInsights = app(CollectionFollowUpInsightsService::class);
            $collectionDateRange = $followUpInsights->resolveRange($request->get('from_date'), $request->get('to_date'), Carbon::today());
            $followUpOverview = $followUpInsights->overview($collectionDateRange['start'], $collectionDateRange['end'], 6, 5, 6);
            $data['collection_execution_stats'] = $followUpOverview['stats'];
            $data['promise_follow_up_queue'] = $followUpOverview['promiseFollowUpQueue'];
            $data['recent_resolved_cases'] = $followUpOverview['recentResolvedCases'];
            $data['branch_follow_up_performance'] = $followUpOverview['branchPerformance'];
            $data['collector_follow_up_performance'] = $followUpOverview['collectorPerformance'];
            $data['collection_date_range'] = $collectionDateRange;

            $data['exception_cards'] = [
                [
                    'label' => _lang('Overdue Repayments'),
                    'value' => $data['overdue_repayments_count'],
                    'route' => route('loans.workspace'),
                    'icon' => 'fas fa-exclamation-triangle',
                    'theme' => 'danger',
                ],
                [
                    'label' => _lang('Due Today'),
                    'value' => $data['today_due_count'],
                    'route' => route('loans.workspace'),
                    'icon' => 'fas fa-calendar-day',
                    'theme' => 'warning',
                ],
                [
                    'label' => _lang('Ready for Disbursement'),
                    'value' => $data['ready_for_disbursement_count'],
                    'route' => route('loans.workspace'),
                    'icon' => 'fas fa-check-circle',
                    'theme' => 'success',
                ],
                [
                    'label' => _lang('Finance Exceptions'),
                    'value' => $data['finance_exception_count'],
                    'route' => route('finance.index'),
                    'icon' => 'fas fa-balance-scale',
                    'theme' => 'primary',
                ],
            ];

            $repaymentRelations = ['loan.borrower.branch', 'loan.currency'];
            if ($followUpsEnabled) {
                $repaymentRelations[] = 'latestFollowUp.createdBy';
            }

            $overdueRepaymentsForBuckets = LoanRepayment::with($repaymentRelations)
                ->whereDate('repayment_date', '<', $date)
                ->where('status', 0)
                ->get();

            $todayCarbon = Carbon::today();
            $data['collection_buckets'] = [
                [
                    'label' => _lang('1-7 Days Late'),
                    'count' => $overdueRepaymentsForBuckets->filter(function ($repayment) use ($todayCarbon) {
                        $daysLate = Carbon::parse($repayment->getRawOriginal('repayment_date'))->diffInDays($todayCarbon);
                        return $daysLate >= 1 && $daysLate <= 7;
                    })->count(),
                ],
                [
                    'label' => _lang('8-30 Days Late'),
                    'count' => $overdueRepaymentsForBuckets->filter(function ($repayment) use ($todayCarbon) {
                        $daysLate = Carbon::parse($repayment->getRawOriginal('repayment_date'))->diffInDays($todayCarbon);
                        return $daysLate >= 8 && $daysLate <= 30;
                    })->count(),
                ],
                [
                    'label' => _lang('31+ Days Late'),
                    'count' => $overdueRepaymentsForBuckets->filter(function ($repayment) use ($todayCarbon) {
                        $daysLate = Carbon::parse($repayment->getRawOriginal('repayment_date'))->diffInDays($todayCarbon);
                        return $daysLate >= 31;
                    })->count(),
                ],
            ];

            $data['collection_priority_queue'] = $overdueRepaymentsForBuckets->map(function ($repayment) use ($todayCarbon, $followUpsEnabled) {
                $daysLate = Carbon::parse($repayment->getRawOriginal('repayment_date'))->diffInDays($todayCarbon);
                $latestFollowUp = $followUpsEnabled ? $repayment->latestFollowUp : null;

                return (object) [
                    'repayment_id' => $repayment->id,
                    'loan_id' => $repayment->loan->loan_id,
                    'loan_route_id' => $repayment->loan_id,
                    'borrower_name' => $repayment->loan->borrower->name,
                    'branch_name' => optional($repayment->loan->borrower->branch)->name,
                    'contact_phone' => trim(($repayment->loan->borrower->country_code ?? '') . ' ' . ($repayment->loan->borrower->mobile ?? '')),
                    'days_late' => $daysLate,
                    'amount' => decimalPlace($repayment->amount_to_pay, optional($repayment->loan->currency)->name),
                    'last_outcome_label' => optional($latestFollowUp)->outcome_text,
                    'last_outcome_theme' => optional($latestFollowUp)->outcome_theme,
                    'action_label' => $daysLate >= 31 ? _lang('Escalate now') : ($daysLate >= 8 ? _lang('Branch follow-up') : _lang('Contact today')),
                    'action_theme' => $daysLate >= 31 ? 'critical' : ($daysLate >= 8 ? 'overdue' : 'review'),
                ];
            })->sortByDesc('days_late')->take(6)->values();

            $dueTodayRepayments = LoanRepayment::with($repaymentRelations)
                ->whereDate('repayment_date', $date)
                ->where('status', 0)
                ->get();

            $upcomingRepayments = LoanRepayment::with($repaymentRelations)
                ->whereBetween('repayment_date', [Carbon::today()->addDay()->toDateString(), Carbon::today()->addDays(7)->toDateString()])
                ->where('status', 0)
                ->get();

            $data['collection_queue_counts'] = [
                'call_today' => $dueTodayRepayments->count() + $overdueRepaymentsForBuckets->filter(function ($repayment) use ($todayCarbon) {
                    return Carbon::parse($repayment->getRawOriginal('repayment_date'))->diffInDays($todayCarbon) <= 7;
                })->count(),
                'upcoming_reminders' => $upcomingRepayments->count(),
                'critical' => $overdueRepaymentsForBuckets->filter(function ($repayment) use ($todayCarbon) {
                    return Carbon::parse($repayment->getRawOriginal('repayment_date'))->diffInDays($todayCarbon) >= 31;
                })->count(),
            ];

            $data['collector_call_list'] = collect($dueTodayRepayments->map(function ($repayment) use ($followUpsEnabled) {
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
                $overdueRepaymentsForBuckets->map(function ($repayment) use ($todayCarbon, $followUpsEnabled) {
                    $daysLate = Carbon::parse($repayment->getRawOriginal('repayment_date'))->diffInDays($todayCarbon);
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

            $data['upcoming_reminder_queue'] = $upcomingRepayments->map(function ($repayment) use ($followUpsEnabled) {
                $daysUntil = Carbon::today()->diffInDays(Carbon::parse($repayment->getRawOriginal('repayment_date')));
                $latestFollowUp = $followUpsEnabled ? $repayment->latestFollowUp : null;

                return (object) [
                    'repayment_id' => $repayment->id,
                    'loan_id' => $repayment->loan->loan_id,
                    'borrower_name' => $repayment->loan->borrower->name,
                    'branch_name' => optional($repayment->loan->borrower->branch)->name,
                    'days_until' => $daysUntil,
                    'last_outcome_label' => optional($latestFollowUp)->outcome_text,
                    'last_outcome_theme' => optional($latestFollowUp)->outcome_theme,
                    'reminder_label' => $daysUntil <= 2 ? _lang('Reminder today') : _lang('Schedule reminder'),
                    'reminder_theme' => $daysUntil <= 2 ? 'today' : 'upcoming',
                ];
            })->sortBy('days_until')->take(6)->values();

            $baseCurrency = get_base_currency();
            $data['dashboard_base_currency'] = $baseCurrency;

            $activeLoanPortfolio = Loan::with('currency')
                ->where('status', 1)
                ->get();

            $portfolioOutstandingBase = $activeLoanPortfolio->sum(function ($loan) use ($baseCurrency) {
                $outstanding = max(0, (float) ($loan->applied_amount ?? 0) - (float) ($loan->total_paid ?? 0));
                return $this->convertAmountToBaseCurrency($outstanding, optional($loan->currency)->name, $baseCurrency);
            });

            $overdueExposureBase = $overdueRepaymentsForBuckets->sum(function ($repayment) use ($baseCurrency) {
                return $this->convertAmountToBaseCurrency((float) ($repayment->amount_to_pay ?? 0), optional($repayment->loan->currency)->name, $baseCurrency);
            });

            $dueTodayAmountBase = $dueTodayRepayments->sum(function ($repayment) use ($baseCurrency) {
                return $this->convertAmountToBaseCurrency((float) ($repayment->amount_to_pay ?? 0), optional($repayment->loan->currency)->name, $baseCurrency);
            });

            $readyForDisbursementAmountBase = $readyForDisbursementLoans->sum(function ($loan) use ($baseCurrency) {
                return $this->convertAmountToBaseCurrency((float) ($loan->applied_amount ?? 0), optional($loan->currency)->name, $baseCurrency);
            });

            $currentMonthStart = Carbon::today()->startOfMonth();
            $previousMonthStart = $currentMonthStart->copy()->subMonth()->startOfMonth();
            $previousMonthEnd = $currentMonthStart->copy()->subDay()->endOfDay();

            $currentMonthDeposits = Transaction::with('account.savings_type.currency')
                ->where('type', 'Deposit')
                ->where('status', 2)
                ->whereBetween('trans_date', [$currentMonthStart->toDateString(), Carbon::today()->toDateString()])
                ->get();
            $previousMonthDeposits = Transaction::with('account.savings_type.currency')
                ->where('type', 'Deposit')
                ->where('status', 2)
                ->whereBetween('trans_date', [$previousMonthStart->toDateString(), $previousMonthEnd->toDateString()])
                ->get();

            $currentMonthWithdrawals = Transaction::with('account.savings_type.currency')
                ->where('type', 'Withdraw')
                ->where('status', 2)
                ->whereBetween('trans_date', [$currentMonthStart->toDateString(), Carbon::today()->toDateString()])
                ->get();
            $previousMonthWithdrawals = Transaction::with('account.savings_type.currency')
                ->where('type', 'Withdraw')
                ->where('status', 2)
                ->whereBetween('trans_date', [$previousMonthStart->toDateString(), $previousMonthEnd->toDateString()])
                ->get();

            $currentMonthDepositBase = $this->sumTransactionsInBaseCurrency($currentMonthDeposits, $baseCurrency);
            $previousMonthDepositBase = $this->sumTransactionsInBaseCurrency($previousMonthDeposits, $baseCurrency);
            $currentMonthWithdrawBase = $this->sumTransactionsInBaseCurrency($currentMonthWithdrawals, $baseCurrency);
            $previousMonthWithdrawBase = $this->sumTransactionsInBaseCurrency($previousMonthWithdrawals, $baseCurrency);

            $currentMonthExpenseBase = (float) Expense::whereBetween('expense_date', [$currentMonthStart->toDateString(), Carbon::today()->toDateString()])->sum('amount');
            $previousMonthExpenseBase = (float) Expense::whereBetween('expense_date', [$previousMonthStart->toDateString(), $previousMonthEnd->toDateString()])->sum('amount');
            $currentMonthExpenseCount = Expense::whereBetween('expense_date', [$currentMonthStart->toDateString(), Carbon::today()->toDateString()])->count();

            $currentMonthNetFlowBase = $currentMonthDepositBase - $currentMonthWithdrawBase - $currentMonthExpenseBase;
            $previousMonthNetFlowBase = $previousMonthDepositBase - $previousMonthWithdrawBase - $previousMonthExpenseBase;

            $data['dashboard_executive_metrics'] = [
                $this->makeDashboardMetric(_lang('Portfolio Outstanding'), $portfolioOutstandingBase, $baseCurrency, route('loans.filter', 'active'), 'fas fa-wallet', number_format($activeLoanPortfolio->count()) . ' ' . _lang('active loans')),
                $this->makeDashboardMetric(_lang('Overdue Exposure'), $overdueExposureBase, $baseCurrency, route('loans.workspace'), 'fas fa-exclamation-triangle', number_format($data['overdue_repayments_count']) . ' ' . _lang('overdue repayments'), null, false),
                $this->makeDashboardMetric(_lang('Due Today Value'), $dueTodayAmountBase, $baseCurrency, route('action_center.index'), 'fas fa-calendar-day', number_format($data['today_due_count']) . ' ' . _lang('repayments due today'), null, false),
                $this->makeDashboardMetric(_lang('Ready to Disburse'), $readyForDisbursementAmountBase, $baseCurrency, route('loans.workspace'), 'fas fa-check-circle', number_format($data['ready_for_disbursement_count']) . ' ' . _lang('approved loans waiting release'), null, true),
                $this->makeDashboardMetric(_lang('Deposits This Month'), $currentMonthDepositBase, $baseCurrency, route('transactions.index'), 'fas fa-arrow-down', number_format($currentMonthDeposits->count()) . ' ' . _lang('posted deposit transactions'), $this->calculatePercentDelta($currentMonthDepositBase, $previousMonthDepositBase), true),
                $this->makeDashboardMetric(_lang('Withdrawals This Month'), $currentMonthWithdrawBase, $baseCurrency, route('transactions.index'), 'fas fa-arrow-up', number_format($currentMonthWithdrawals->count()) . ' ' . _lang('posted withdraw transactions'), $this->calculatePercentDelta($currentMonthWithdrawBase, $previousMonthWithdrawBase), false),
                $this->makeDashboardMetric(_lang('Expenses This Month'), $currentMonthExpenseBase, $baseCurrency, route('expenses.index'), 'fas fa-receipt', number_format($currentMonthExpenseCount) . ' ' . _lang('expense entries this month'), $this->calculatePercentDelta($currentMonthExpenseBase, $previousMonthExpenseBase), false),
                $this->makeDashboardMetric(_lang('Net Flow This Month'), $currentMonthNetFlowBase, $baseCurrency, route('finance.index'), 'fas fa-chart-line', _lang('Deposits - withdrawals - expenses'), $this->calculatePercentDelta($currentMonthNetFlowBase, $previousMonthNetFlowBase), true),
            ];

            $data['branch_collections_pressure'] = Branch::get()->map(function ($branch) use ($baseCurrency, $dueTodayRepayments, $overdueRepaymentsForBuckets, $todayCarbon) {
                $branchDueToday = $dueTodayRepayments->filter(fn ($repayment) => optional($repayment->loan)->branch_id == $branch->id);
                $branchOverdue = $overdueRepaymentsForBuckets->filter(fn ($repayment) => optional($repayment->loan)->branch_id == $branch->id);
                $branchCritical = $branchOverdue->filter(function ($repayment) use ($todayCarbon) {
                    return Carbon::parse($repayment->getRawOriginal('repayment_date'))->diffInDays($todayCarbon) >= 31;
                });

                $dueTodayAmountBase = $branchDueToday->sum(fn ($repayment) => $this->convertAmountToBaseCurrency((float) ($repayment->amount_to_pay ?? 0), optional($repayment->loan->currency)->name, $baseCurrency));
                $overdueAmountBase = $branchOverdue->sum(fn ($repayment) => $this->convertAmountToBaseCurrency((float) ($repayment->amount_to_pay ?? 0), optional($repayment->loan->currency)->name, $baseCurrency));
                $criticalAmountBase = $branchCritical->sum(fn ($repayment) => $this->convertAmountToBaseCurrency((float) ($repayment->amount_to_pay ?? 0), optional($repayment->loan->currency)->name, $baseCurrency));

                return (object) [
                    'name' => $branch->name,
                    'due_today' => $branchDueToday->count(),
                    'overdue' => $branchOverdue->count(),
                    'critical' => $branchCritical->count(),
                    'due_today_amount_base' => $dueTodayAmountBase,
                    'overdue_amount_base' => $overdueAmountBase,
                    'critical_amount_base' => $criticalAmountBase,
                    'pressure_score' => $branchDueToday->count() + $branchOverdue->count() + ($branchCritical->count() * 2),
                ];
            })->sortByDesc('pressure_score')->take(5)->values();

            $data['branch_performance'] = Branch::get()->map(function ($branch) use ($date, $baseCurrency, $overdueRepaymentsForBuckets) {
                $activeMembers = Member::withoutGlobalScopes(['status'])
                    ->where('branch_id', $branch->id)
                    ->where('status', 1)
                    ->count();

                $pendingMembers = Member::withoutGlobalScopes(['status'])
                    ->where('branch_id', $branch->id)
                    ->where('status', 0)
                    ->count();

                $activeLoans = Loan::withoutGlobalScopes(['borrower_id'])
                    ->where('branch_id', $branch->id)
                    ->where('status', 1)
                    ->count();

                $branchOverdue = $overdueRepaymentsForBuckets->filter(fn ($repayment) => optional($repayment->loan)->branch_id == $branch->id);
                $overdueAmountBase = $branchOverdue->sum(fn ($repayment) => $this->convertAmountToBaseCurrency((float) ($repayment->amount_to_pay ?? 0), optional($repayment->loan->currency)->name, $baseCurrency));

                return (object) [
                    'name' => $branch->name,
                    'active_members' => $activeMembers,
                    'pending_members' => $pendingMembers,
                    'active_loans' => $activeLoans,
                    'overdue_repayments' => $branchOverdue->count(),
                    'overdue_amount_base' => $overdueAmountBase,
                    'pressure_score' => $pendingMembers + $branchOverdue->count(),
                ];
            })->sortByDesc('pressure_score')->take(5)->values();

            // Interest analysis (system-wide): total interest payable vs paid for all active loans
            $activeLoans = Loan::where('status', 1)->get();
            $totalInterestPayable = 0;
            foreach ($activeLoans as $loan) {
                $totalPayable = (float) ($loan->total_payable ?? $loan->applied_amount ?? 0);
                $principal = (float) ($loan->applied_amount ?? 0);
                $totalInterestPayable += max(0, $totalPayable - $principal);
            }
            $activeLoanIds = $activeLoans->pluck('id')->toArray();
            $data['total_interest_payable'] = $totalInterestPayable;
            $data['total_interest_paid'] = $activeLoanIds
                ? (float) LoanPayment::withoutGlobalScopes()->whereIn('loan_id', $activeLoanIds)->sum('interest')
                : 0;
            $data['interest_paid_pct'] = $totalInterestPayable > 0
                ? round(($data['total_interest_paid'] / $totalInterestPayable) * 100, 1)
                : 0;
            $data['admin_interest_currency'] = optional(optional($data['loan_balances']->first())->currency)->name ?? '';

            return view("backend.admin.dashboard-$user_type", $data);
        }
    }

    protected function convertAmountToBaseCurrency(float $amount, ?string $fromCurrency, ?string $baseCurrency): float
    {
        if ($amount == 0) {
            return 0.0;
        }

        $baseCurrency = $baseCurrency ?: get_base_currency();
        $fromCurrency = $fromCurrency ?: $baseCurrency;

        try {
            return (float) convert_currency($fromCurrency, $baseCurrency, $amount);
        } catch (\Throwable $e) {
            return (float) $amount;
        }
    }

    protected function sumTransactionsInBaseCurrency($transactions, ?string $baseCurrency): float
    {
        return collect($transactions)->sum(function ($transaction) use ($baseCurrency) {
            $fromCurrency = optional(optional(optional($transaction->account)->savings_type)->currency)->name ?: $baseCurrency;
            return $this->convertAmountToBaseCurrency((float) ($transaction->amount ?? 0), $fromCurrency, $baseCurrency);
        });
    }

    protected function calculatePercentDelta(float $current, float $previous): float
    {
        if ($previous == 0.0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / abs($previous)) * 100, 1);
    }

    protected function makeDashboardMetric(string $label, float $amount, string $baseCurrency, string $route, string $icon, string $meta, ?float $delta = null, bool $positiveIsGood = true): array
    {
        $deltaTone = 'neutral';
        if ($delta !== null && $delta != 0.0) {
            if ($positiveIsGood) {
                $deltaTone = $delta > 0 ? 'success' : 'danger';
            } else {
                $deltaTone = $delta > 0 ? 'danger' : 'success';
            }
        }

        return [
            'label' => $label,
            'amount' => $amount,
            'formatted_amount' => decimalPlace($amount, $baseCurrency, 0),
            'route' => $route,
            'icon' => $icon,
            'meta' => $meta,
            'delta' => $delta,
            'delta_tone' => $deltaTone,
        ];
    }

    public function dashboard_widget() {
        return redirect()->route('dashboard.index');
    }

    public function json_expense_by_category() {
        $transactions = Expense::selectRaw('expense_category_id, IFNULL(SUM(amount), 0) as amount')
            ->with('expense_category')
            ->whereRaw('MONTH(expense_date) = ?', date('m'))
            ->whereRaw('YEAR(expense_date) = ?', date('Y'))
            ->groupBy('expense_category_id')
            ->get();
        $category = [];
        $colors   = [];
        $amounts  = [];
        $data     = [];

        foreach ($transactions as $transaction) {
            array_push($category, $transaction->expense_category->name);
            array_push($colors, $transaction->expense_category->color);
            array_push($amounts, (double) $transaction->amount);
        }

        echo json_encode(['amounts' => $amounts, 'category' => $category, 'colors' => $colors]);

    }

    public function json_deposit_withdraw_analytics($currency_id) {
        $months       = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
        $transactions = Transaction::whereHas('account.savings_type', function (Builder $query) use ($currency_id) {
            $query->where('currency_id', $currency_id);
        })
            ->selectRaw('MONTH(trans_date) as td, type, IFNULL(SUM(amount), 0) as amount')
            ->whereRaw("(type = 'Deposit' OR type = 'Withdraw') AND status = 2")
            ->whereRaw('YEAR(trans_date) = ?', date('Y'))
            ->groupBy('td', 'type')
            ->get();

        $deposit  = [];
        $withdraw = [];

        foreach ($transactions as $transaction) {
            if ($transaction->type == 'Deposit') {
                $deposit[$transaction->td] = $transaction->amount;
            } else if ($transaction->type == 'Withdraw') {
                $withdraw[$transaction->td] = $transaction->amount;
            }
        }

        echo json_encode(['month' => $months, 'deposit' => $deposit, 'withdraw' => $withdraw]);
    }

    /**
     * JSON chart data for customer dashboard: loan repayment trend and account type contributions.
     * Query params: from_date (Y-m-d), to_date (Y-m-d). Default: last 12 months.
     */
    public function chartData(Request $request)
    {
        $user = auth()->user();
        if (!$user || $user->user_type !== 'customer' || !$user->member) {
            return response()->json(['loan_repayment_trend' => [], 'account_contributions' => []], 403);
        }

        $memberId = $user->member->id;
        $toDate   = $request->has('to_date') ? Carbon::parse($request->to_date) : Carbon::today();
        $fromDate = $request->has('from_date') ? Carbon::parse($request->from_date) : $toDate->copy()->subMonths(11)->startOfMonth();

        if ($fromDate->gt($toDate)) {
            $fromDate = $toDate->copy()->subMonths(11)->startOfMonth();
        }

        $labels = [];
        for ($d = $fromDate->copy(); $d->lte($toDate); $d->addMonth()) {
            $labels[] = $d->format('M Y');
        }
        $monthKeys = [];
        for ($d = $fromDate->copy(); $d->lte($toDate); $d->addMonth()) {
            $monthKeys[] = $d->format('Y-m');
        }

        // Loan repayment trend: per loan, sum amount per month (Transaction type Loan_Repayment, member_id)
        $repayments = Transaction::withoutGlobalScopes()
            ->where('member_id', $memberId)
            ->where('type', 'Loan_Repayment')
            ->whereBetween(DB::raw('DATE(trans_date)'), [$fromDate->format('Y-m-d'), $toDate->format('Y-m-d')])
            ->selectRaw('loan_id, DATE_FORMAT(trans_date, "%Y-%m") as month, SUM(amount) as total')
            ->groupBy('loan_id', 'month')
            ->get();

        $loans = Loan::withoutGlobalScopes()
            ->where('borrower_id', $memberId)
            ->whereIn('id', $repayments->pluck('loan_id')->unique()->filter())
            ->get()
            ->keyBy('id');

        $datasetsLoan = [];
        $colors       = ['#007bff', '#28a745', '#fd7e14', '#6f42c1', '#20c997'];
        foreach ($repayments->groupBy('loan_id') as $loanId => $rows) {
            $loan   = $loans->get($loanId);
            $label  = $loan ? ('Loan ' . $loan->loan_id) : ('Loan ' . $loanId);
            $color  = $colors[count($datasetsLoan) % count($colors)];
            $byMonth = $rows->keyBy('month');
            $data   = [];
            foreach ($monthKeys as $mk) {
                $row = $byMonth->get($mk);
                $data[] = (float) ($row ? $row->total : 0);
            }
            $datasetsLoan[] = ['label' => $label, 'data' => $data, 'borderColor' => $color, 'backgroundColor' => $color, 'fill' => false];
        }

        // Account type contributions: non-loan accounts for member (direct query like test_chart_data.php), sum cr by month
        $accountsQuery = \App\Models\SavingsAccount::withoutGlobalScopes()
            ->where('member_id', $memberId)
            ->with('savings_type');
        if ($tenantId = auth()->user()->tenant_id ?? null) {
            $accountsQuery->where('tenant_id', $tenantId);
        }
        $accounts = $accountsQuery->get();
        $excludeTypes = ['loans', 'mkopo', 'mikopo'];
        $accountsFiltered = $accounts->filter(function ($a) use ($excludeTypes) {
            $name = $a->savings_type ? strtolower(trim($a->savings_type->name ?? '')) : '';
            return ! in_array($name, $excludeTypes);
        });

        $barColors = ['#007bff', '#28a745', '#fd7e14', '#6f42c1', '#17a2b8'];
        $datasetsBar   = [];
        $totalByMonth  = array_fill_keys($monthKeys, 0);
        $tenantId      = auth()->user()->tenant_id ?? null;

        // One dataset per account (individual), not summed by type
        foreach ($accountsFiltered as $account) {
            $typeName = $account->savings_type ? $account->savings_type->name : _lang('Account');
            $label = $typeName . ' - ' . $account->account_number;

            $baseQuery = DB::table('transactions')
                ->where('member_id', $memberId)
                ->where('dr_cr', 'cr')
                ->where('savings_account_id', $account->id)
                ->whereBetween(DB::raw('DATE(trans_date)'), [$fromDate->format('Y-m-d'), $toDate->format('Y-m-d')]);
            if ($tenantId) {
                $baseQuery->where('tenant_id', $tenantId);
            }
            $rows = (clone $baseQuery)
                ->selectRaw('DATE_FORMAT(trans_date, "%Y-%m") as month_key, SUM(amount) as total')
                ->groupBy(DB::raw('DATE_FORMAT(trans_date, "%Y-%m")'))
                ->get();
            $txns = $rows->keyBy('month_key');
            if ($txns->isEmpty()) {
                $rawRows = (clone $baseQuery)->select('trans_date', 'amount')->get();
                $byMonth = [];
                foreach ($rawRows as $r) {
                    if (empty($r->trans_date)) continue;
                    $mk = Carbon::parse($r->trans_date)->format('Y-m');
                    $byMonth[$mk] = ($byMonth[$mk] ?? 0) + (float) $r->amount;
                }
                $txns = collect($byMonth)->map(function ($total, $month_key) {
                    return (object) ['month_key' => $month_key, 'total' => $total];
                })->keyBy('month_key');
            }

            $data = [];
            foreach ($monthKeys as $mk) {
                $row = $txns->get($mk);
                $val = $row ? (float) $row->total : 0;
                $data[] = $val;
                $totalByMonth[$mk] += $val;
            }
            $color = $barColors[count($datasetsBar) % count($barColors)];
            $datasetsBar[] = ['label' => $label, 'data' => $data, 'backgroundColor' => $color];
        }

        $totalData = [];
        foreach ($monthKeys as $mk) {
            $totalData[] = $totalByMonth[$mk] ?? 0;
        }
        $datasetsBar[] = ['label' => _lang('Total'), 'data' => $totalData, 'type' => 'line', 'borderColor' => '#dc3545', 'backgroundColor' => 'transparent', 'fill' => false];

        return response()->json([
            'loan_repayment_trend'   => ['labels' => $labels, 'datasets' => $datasetsLoan],
            'account_contributions'  => ['labels' => $labels, 'datasets' => $datasetsBar],
        ]);
    }

}
