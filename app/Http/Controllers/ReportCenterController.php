<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\BankTransaction;
use App\Models\Branch;
use App\Models\Currency;
use App\Models\Expense;
use App\Models\Loan;
use App\Models\LoanPayment;
use App\Models\LoanProduct;
use App\Models\LoanRepayment;
use App\Models\Member;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportCenterController extends Controller
{
    public function index(Request $request)
    {
        $todayCarbon = Carbon::today();
        $today = $todayCarbon->toDateString();
        $monthStart = $todayCarbon->copy()->startOfMonth()->toDateString();
        $monthEnd = $todayCarbon->copy()->endOfMonth()->toDateString();
        $monthLabel = $todayCarbon->format('F Y');

        $reportGroups = [
            'executive' => [
                'title' => _lang('Executive KPIs'),
                'items' => [
                    ['label' => _lang('Cash In Hand'), 'route' => route('reports.cash_in_hand'), 'description' => _lang('Review current liquidity and movement between cash and bank positions.')],
                    ['label' => _lang('Revenue Report'), 'route' => route('reports.revenue_report'), 'description' => _lang('Analyze interest, charges, and fee-driven revenue by period.')],
                ],
            ],
            'portfolio' => [
                'title' => _lang('Loan Portfolio'),
                'items' => [
                    ['label' => _lang('Loan Report'), 'route' => route('reports.loan_report'), 'description' => _lang('Filter disbursed and pipeline loans by date, member, and product.')],
                ],
            ],
            'collections' => [
                'title' => _lang('Loan Repayments Report'),
                'items' => [
                    ['label' => _lang('Loan Repayment Report'), 'route' => route('reports.loan_repayment_report'), 'description' => _lang('Inspect repayment behavior and payment history by loan.')],
                ],
            ],
            'accounts' => [
                'title' => _lang('Accounts'),
                'items' => [
                    ['label' => _lang('Account Statement'), 'route' => route('reports.account_statement'), 'description' => _lang('Open detailed statement movements for an individual account number.')],
                    ['label' => _lang('Account Balance'), 'route' => route('reports.account_balances'), 'description' => _lang('Review balance positions by account type and member.')],
                    ['label' => _lang('Cash In Hand'), 'route' => route('reports.cash_in_hand'), 'description' => _lang('Review current liquidity and movement between cash and bank positions.')],
                ],
            ],
            'transactions' => [
                'title' => _lang('Transactions'),
                'items' => [
                    ['label' => _lang('Transaction Report'), 'route' => route('reports.transactions_report'), 'description' => _lang('Filter cash transactions by date, status, account, and type.')],
                ],
            ],
            'expenses' => [
                'title' => _lang('Expenses'),
                'items' => [
                    ['label' => _lang('Expense Report'), 'route' => route('reports.expense_report'), 'description' => _lang('Monitor expense entries, categories, and branch spending patterns.')],
                ],
            ],
            'banking' => [
                'title' => _lang('Banking'),
                'items' => [
                    ['label' => _lang('Bank Transactions'), 'route' => route('reports.bank_transactions'), 'description' => _lang('Filter reconciliation movement by bank account, type, and status.')],
                    ['label' => _lang('Bank Account Balance'), 'route' => route('reports.bank_balances'), 'description' => _lang('Check current balances across configured bank accounts.')],
                ],
            ],
            'branch_performance' => [
                'title' => _lang('Branch Performance'),
                'items' => [
                    ['label' => _lang('Loan Due Report'), 'route' => route('reports.loan_due_report'), 'description' => _lang('Review overdue loan positions and earliest missed installment dates.')],
                    ['label' => _lang('Expense Report'), 'route' => route('reports.expense_report'), 'description' => _lang('Monitor expense entries, categories, and branch spending patterns.')],
                ],
            ],
            'revenue' => [
                'title' => _lang('Revenue'),
                'items' => [
                    ['label' => _lang('Revenue Report'), 'route' => route('reports.revenue_report'), 'description' => _lang('Cross-check revenue outcomes alongside banking and fee movement.')],
                ],
            ],
        ];

        $activeMembers = Member::count();
        $pendingMembers = Member::withoutGlobalScopes(['status'])->where('status', 0)->count();
        $activeLoans = Loan::where('status', 1)->count();
        $pendingLoans = Loan::where('status', 0)->count();
        $activePortfolioAmount = Loan::where('status', 1)->sum('applied_amount');
        $portfolioOutstanding = Loan::where('status', 1)
            ->selectRaw('SUM(COALESCE(total_payable, applied_amount) - COALESCE(total_paid, 0)) as outstanding')
            ->value('outstanding') ?? 0;

        $overdueRepaymentsQuery = LoanRepayment::whereDate('repayment_date', '<', $today)->where('status', 0);
        $dueTodayQuery = LoanRepayment::whereDate('repayment_date', $today)->where('status', 0);
        $monthlyTransactionsQuery = Transaction::whereDate('trans_date', '>=', $monthStart)->whereDate('trans_date', '<=', $monthEnd);
        $completedMonthlyTransactionsQuery = (clone $monthlyTransactionsQuery)->where('status', 2);
        $monthlyExpensesQuery = Expense::whereDate('expense_date', '>=', $monthStart)->whereDate('expense_date', '<=', $monthEnd);
        $loanReportFilters = [
            'date1' => $request->input('date1', $monthStart),
            'date2' => $request->input('date2', $monthEnd),
            'loan_type' => $request->input('loan_type', ''),
            'status' => $request->input('status', ''),
            'member_no' => $request->input('member_no', ''),
        ];
        $repaymentReportLoanId = $request->input('repayment_loan_id', '');
        $revenueYear = min((int) date('Y'), max(2020, (int) $request->input('revenue_year', date('Y'))));
        $revenueMonth = min(12, max(1, (int) $request->input('revenue_month', date('m'))));
        $activeCurrencyId = Currency::where('status', 1)->where('id', $request->input('revenue_currency_id', base_currency_id()))->value('id') ?? base_currency_id();
        $revenueReportFilters = [
            'year' => $revenueYear,
            'month' => $revenueMonth,
            'currency_id' => $activeCurrencyId,
        ];

        $monthlyCredits = (clone $completedMonthlyTransactionsQuery)->where('dr_cr', 'cr')->sum('amount');
        $monthlyDebits = (clone $completedMonthlyTransactionsQuery)->where('dr_cr', 'dr')->sum('amount');
        $expensesThisMonthAmount = (clone $monthlyExpensesQuery)->sum('amount');
        $pendingBankTransactions = BankTransaction::where('status', 0)->count();
        $overdueRepayments = (clone $overdueRepaymentsQuery)->count();
        $dueToday = (clone $dueTodayQuery)->count();
        $overdueAmount = (clone $overdueRepaymentsQuery)->sum('amount_to_pay');
        $dueTodayAmount = (clone $dueTodayQuery)->sum('amount_to_pay');
        $portfolioPayable = Loan::where('status', 1)->sum('total_payable');
        $portfolioPaid = Loan::where('status', 1)->sum('total_paid');
        $portfolioPayable = $portfolioPayable > 0 ? $portfolioPayable : $activePortfolioAmount;
        $portfolioRepaymentRate = $portfolioPayable > 0 ? round(($portfolioPaid / $portfolioPayable) * 100, 1) : 0;
        $portfolioParRatio = $portfolioOutstanding > 0 ? round(($overdueAmount / $portfolioOutstanding) * 100, 1) : 0;

        $loanStatusSummary = Loan::selectRaw('status, COUNT(*) as total, SUM(applied_amount) as applied_amount, SUM(COALESCE(total_payable, applied_amount) - COALESCE(total_paid, 0)) as outstanding')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        $loanProductPortfolio = Loan::where('status', 1)
            ->selectRaw('loan_product_id, COUNT(*) as active_loans, SUM(applied_amount) as disbursed_amount, SUM(COALESCE(total_payable, applied_amount) - COALESCE(total_paid, 0)) as outstanding_amount')
            ->with('loan_product')
            ->groupBy('loan_product_id')
            ->orderByDesc('outstanding_amount')
            ->take(6)
            ->get();

        $portfolioAgingBuckets = [
            'not_due' => [
                'label' => _lang('Not Due'),
                'count' => LoanRepayment::where('status', 0)->whereDate('repayment_date', '>', $today)->count(),
                'amount' => LoanRepayment::where('status', 0)->whereDate('repayment_date', '>', $today)->sum('amount_to_pay'),
            ],
            'due_today' => [
                'label' => _lang('Due Today'),
                'count' => $dueToday,
                'amount' => $dueTodayAmount,
            ],
            'one_to_thirty' => [
                'label' => _lang('1-30 Days'),
                'count' => LoanRepayment::where('status', 0)->whereDate('repayment_date', '>=', $todayCarbon->copy()->subDays(30)->toDateString())->whereDate('repayment_date', '<', $today)->count(),
                'amount' => LoanRepayment::where('status', 0)->whereDate('repayment_date', '>=', $todayCarbon->copy()->subDays(30)->toDateString())->whereDate('repayment_date', '<', $today)->sum('amount_to_pay'),
            ],
            'thirty_one_to_sixty' => [
                'label' => _lang('31-60 Days'),
                'count' => LoanRepayment::where('status', 0)->whereDate('repayment_date', '>=', $todayCarbon->copy()->subDays(60)->toDateString())->whereDate('repayment_date', '<', $todayCarbon->copy()->subDays(30)->toDateString())->count(),
                'amount' => LoanRepayment::where('status', 0)->whereDate('repayment_date', '>=', $todayCarbon->copy()->subDays(60)->toDateString())->whereDate('repayment_date', '<', $todayCarbon->copy()->subDays(30)->toDateString())->sum('amount_to_pay'),
            ],
            'sixty_one_to_ninety' => [
                'label' => _lang('61-90 Days'),
                'count' => LoanRepayment::where('status', 0)->whereDate('repayment_date', '>=', $todayCarbon->copy()->subDays(90)->toDateString())->whereDate('repayment_date', '<', $todayCarbon->copy()->subDays(60)->toDateString())->count(),
                'amount' => LoanRepayment::where('status', 0)->whereDate('repayment_date', '>=', $todayCarbon->copy()->subDays(90)->toDateString())->whereDate('repayment_date', '<', $todayCarbon->copy()->subDays(60)->toDateString())->sum('amount_to_pay'),
            ],
            'ninety_plus' => [
                'label' => _lang('90+ Days'),
                'count' => LoanRepayment::where('status', 0)->whereDate('repayment_date', '<', $todayCarbon->copy()->subDays(90)->toDateString())->count(),
                'amount' => LoanRepayment::where('status', 0)->whereDate('repayment_date', '<', $todayCarbon->copy()->subDays(90)->toDateString())->sum('amount_to_pay'),
            ],
        ];

        $portfolioWatchlist = LoanRepayment::selectRaw('loan_id, MIN(repayment_date) as earliest_due_date, COUNT(*) as missed_installments, SUM(amount_to_pay) as overdue_amount')
            ->with(['loan.borrower', 'loan.loan_product'])
            ->whereDate('repayment_date', '<', $today)
            ->where('status', 0)
            ->groupBy('loan_id')
            ->orderByDesc('overdue_amount')
            ->take(6)
            ->get();

        $inlineLoanReport = Loan::select('loans.*')
            ->with(['borrower', 'loan_product', 'currency'])
            ->when($loanReportFilters['status'] !== '', function ($query) use ($loanReportFilters) {
                return $query->where('status', $loanReportFilters['status']);
            })
            ->when($loanReportFilters['loan_type'] !== '', function ($query) use ($loanReportFilters) {
                return $query->where('loan_product_id', $loanReportFilters['loan_type']);
            })
            ->when($loanReportFilters['member_no'] !== '', function ($query) use ($loanReportFilters) {
                return $query->whereHas('borrower', function ($memberQuery) use ($loanReportFilters) {
                    return $memberQuery->where('member_no', $loanReportFilters['member_no']);
                });
            })
            ->whereDate('loans.created_at', '>=', $loanReportFilters['date1'])
            ->whereDate('loans.created_at', '<=', $loanReportFilters['date2'])
            ->orderByDesc('loans.id')
            ->get();

        $loanReportProducts = LoanProduct::where('status', 1)->orderBy('name')->get();
        $repaymentReportLoans = Loan::with(['currency', 'borrower'])->orderByDesc('id')->get();
        $inlineLoanRepaymentReport = null;
        $revenueCurrencies = Currency::where('status', 1)->orderBy('name')->get();

        if ($repaymentReportLoanId !== '') {
            $inlineLoanRepaymentReport = Loan::select('loans.*')
                ->with(['borrower', 'loan_product', 'payments', 'currency'])
                ->where('id', $repaymentReportLoanId)
                ->first();
        }

        $transactionRevenue = Transaction::selectRaw("CONCAT('Revenue from ', type), sum(charge) as amount")
            ->whereRaw("YEAR(trans_date) = ? AND MONTH(trans_date) = ?", [$revenueReportFilters['year'], $revenueReportFilters['month']])
            ->where('charge', '>', 0)
            ->where('status', 2)
            ->whereHas('account.savings_type', function ($query) use ($revenueReportFilters) {
                return $query->where('currency_id', $revenueReportFilters['currency_id']);
            })
            ->groupBy('type');

        $maintenanceFee = Transaction::selectRaw("CONCAT('Revenue from ', type), sum(amount) as amount")
            ->whereRaw("YEAR(trans_date) = ? AND MONTH(trans_date) = ?", [$revenueReportFilters['year'], $revenueReportFilters['month']])
            ->where('type', 'Account_Maintenance_Fee')
            ->where('status', 2)
            ->whereHas('account.savings_type', function ($query) use ($revenueReportFilters) {
                return $query->where('currency_id', $revenueReportFilters['currency_id']);
            })
            ->groupBy('type');

        $otherFees = Transaction::join('transaction_categories', function ($join) {
            $join->on('transaction_categories.name', '=', 'transactions.type')
                ->where('transaction_categories.status', '=', 1);
        })
            ->selectRaw("CONCAT('Revenue from ', type), sum(amount) as amount")
            ->whereRaw("YEAR(trans_date) = ? AND MONTH(trans_date) = ?", [$revenueReportFilters['year'], $revenueReportFilters['month']])
            ->where('dr_cr', 'dr')
            ->where('transactions.status', 2)
            ->whereHas('account.savings_type', function ($query) use ($revenueReportFilters) {
                return $query->where('currency_id', $revenueReportFilters['currency_id']);
            })
            ->groupBy('type');

        $inlineRevenueReport = LoanPayment::selectRaw("'Revenue from Loan' as type, sum(interest + late_penalties) as amount")
            ->whereRaw("YEAR(loan_payments.paid_at) = ? AND MONTH(loan_payments.paid_at) = ?", [$revenueReportFilters['year'], $revenueReportFilters['month']])
            ->whereHas('loan', function ($query) use ($revenueReportFilters) {
                return $query->where('currency_id', $revenueReportFilters['currency_id']);
            })
            ->union($transactionRevenue)
            ->union($maintenanceFee)
            ->union($otherFees)
            ->get();

        $reportHighlights = [
            'period_label' => $monthLabel,
            'active_members' => $activeMembers,
            'pending_members' => $pendingMembers,
            'active_loans' => $activeLoans,
            'pending_loans' => $pendingLoans,
            'active_portfolio_amount' => $activePortfolioAmount,
            'portfolio_outstanding' => $portfolioOutstanding,
            'overdue_repayments' => $overdueRepayments,
            'overdue_amount' => $overdueAmount,
            'due_today' => $dueToday,
            'due_today_amount' => $dueTodayAmount,
            'transactions_this_month' => (clone $monthlyTransactionsQuery)->count(),
            'completed_transactions_this_month' => (clone $completedMonthlyTransactionsQuery)->count(),
            'pending_transactions_this_month' => (clone $monthlyTransactionsQuery)->where('status', 0)->count(),
            'monthly_credits' => $monthlyCredits,
            'monthly_debits' => $monthlyDebits,
            'net_cash_movement' => $monthlyCredits - $monthlyDebits - $expensesThisMonthAmount,
            'expenses_this_month' => (clone $monthlyExpensesQuery)->count(),
            'expenses_this_month_amount' => $expensesThisMonthAmount,
            'pending_bank_transactions' => $pendingBankTransactions,
            'bank_accounts' => BankAccount::count(),
            'attention_total' => $overdueRepayments + $dueToday + $pendingBankTransactions + $pendingLoans + $pendingMembers,
        ];

        $executiveCards = [
            [
                'label' => _lang('Member Base'),
                'value' => number_format($activeMembers),
                'meta' => _lang('active members'),
                'detail' => number_format($pendingMembers) . ' ' . _lang('pending onboarding requests need approval or cleanup.'),
                'route' => route('members.workspace'),
                'icon' => 'fas fa-users',
                'tone' => $pendingMembers > 0 ? 'review' : 'active',
            ],
            [
                'label' => _lang('Portfolio Load'),
                'value' => number_format($activeLoans),
                'meta' => _lang('active loans'),
                'detail' => number_format($pendingLoans) . ' ' . _lang('loan applications are still waiting in the pipeline.'),
                'route' => route('loans.workspace'),
                'icon' => 'fas fa-hand-holding-usd',
                'tone' => $pendingLoans > 0 ? 'today' : 'active',
            ],
            [
                'label' => _lang('Collection Pressure'),
                'value' => number_format($overdueRepayments),
                'meta' => _lang('overdue repayments'),
                'detail' => _lang('Estimated overdue exposure') . ': ' . money_format_2($reportHighlights['overdue_amount']),
                'route' => route('reports.loan_due_report'),
                'icon' => 'fas fa-exclamation-triangle',
                'tone' => $overdueRepayments > 0 ? 'critical' : 'active',
            ],
            [
                'label' => _lang('Due Today'),
                'value' => number_format($dueToday),
                'meta' => _lang('repayments scheduled today'),
                'detail' => _lang('Scheduled collection value') . ': ' . money_format_2($reportHighlights['due_today_amount']),
                'route' => route('action_center.index') . '#due-upcoming',
                'icon' => 'fas fa-calendar-day',
                'tone' => $dueToday > 0 ? 'today' : 'active',
            ],
            [
                'label' => _lang('Monthly Movement'),
                'value' => money_format_2($reportHighlights['net_cash_movement']),
                'meta' => $monthLabel,
                'detail' => _lang('Credits') . ': ' . money_format_2($monthlyCredits) . ' · ' . _lang('Debits') . ': ' . money_format_2($monthlyDebits),
                'route' => route('reports.transactions_report'),
                'icon' => 'fas fa-exchange-alt',
                'tone' => $reportHighlights['net_cash_movement'] >= 0 ? 'info' : 'review',
            ],
            [
                'label' => _lang('Reconciliation'),
                'value' => number_format($pendingBankTransactions),
                'meta' => _lang('pending bank items'),
                'detail' => number_format($reportHighlights['bank_accounts']) . ' ' . _lang('bank accounts available for balance review.'),
                'route' => route('reports.bank_transactions'),
                'icon' => 'fas fa-university',
                'tone' => $pendingBankTransactions > 0 ? 'review' : 'active',
            ],
        ];

        $branchReportSnapshot = Branch::get()->map(function ($branch) use ($today) {
            $overdueRepayments = LoanRepayment::withoutGlobalScopes(['borrower_id'])->whereDate('repayment_date', '<', $today)->where('status', 0)->whereHas('loan', function ($query) use ($branch) {
                $query->where('branch_id', $branch->id);
            });
            $pendingMembers = Member::withoutGlobalScopes(['status'])->where('branch_id', $branch->id)->where('status', 0)->count();

            return (object) [
                'name' => $branch->name,
                'active_members' => Member::withoutGlobalScopes(['status'])->where('branch_id', $branch->id)->where('status', 1)->count(),
                'pending_members' => $pendingMembers,
                'active_loans' => Loan::withoutGlobalScopes(['borrower_id'])->where('branch_id', $branch->id)->where('status', 1)->count(),
                'portfolio_amount' => Loan::withoutGlobalScopes(['borrower_id'])->where('branch_id', $branch->id)->where('status', 1)->sum('applied_amount'),
                'overdue_repayments' => (clone $overdueRepayments)->count(),
                'overdue_amount' => (clone $overdueRepayments)->sum('amount_to_pay'),
                'pressure_score' => $pendingMembers + (clone $overdueRepayments)->count(),
            ];
        })->sortByDesc('pressure_score')->take(5)->values();

        return view('backend.admin.reports.index', [
            'page_title' => _lang('Reports'),
            'assets' => ['datatable'],
            'reportGroups' => $reportGroups,
            'reportHighlights' => $reportHighlights,
            'executiveCards' => $executiveCards,
            'branchReportSnapshot' => $branchReportSnapshot,
            'loanStatusSummary' => $loanStatusSummary,
            'loanProductPortfolio' => $loanProductPortfolio,
            'portfolioAgingBuckets' => $portfolioAgingBuckets,
            'portfolioWatchlist' => $portfolioWatchlist,
            'portfolioRepaymentRate' => $portfolioRepaymentRate,
            'portfolioParRatio' => $portfolioParRatio,
            'inlineLoanReport' => $inlineLoanReport,
            'loanReportFilters' => $loanReportFilters,
            'loanReportProducts' => $loanReportProducts,
            'repaymentReportLoans' => $repaymentReportLoans,
            'repaymentReportLoanId' => $repaymentReportLoanId,
            'inlineLoanRepaymentReport' => $inlineLoanRepaymentReport,
            'revenueCurrencies' => $revenueCurrencies,
            'revenueReportFilters' => $revenueReportFilters,
            'inlineRevenueReport' => $inlineRevenueReport,
        ]);
    }
}
