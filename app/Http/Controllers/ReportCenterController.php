<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\BankTransaction;
use App\Models\Branch;
use App\Models\Expense;
use App\Models\Loan;
use App\Models\LoanRepayment;
use App\Models\Member;
use App\Models\Transaction;
use Carbon\Carbon;

class ReportCenterController extends Controller
{
    public function index()
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
                'title' => _lang('Portfolio & Loans'),
                'items' => [
                    ['label' => _lang('Loan Report'), 'route' => route('reports.loan_report'), 'description' => _lang('Filter disbursed and pipeline loans by date, member, and product.')],
                    ['label' => _lang('Loan Due Report'), 'route' => route('reports.loan_due_report'), 'description' => _lang('Review overdue loan positions and earliest missed installment dates.')],
                    ['label' => _lang('Loan Repayment Report'), 'route' => route('reports.loan_repayment_report'), 'description' => _lang('Inspect repayment behavior and payment history by loan.')],
                ],
            ],
            'accounts' => [
                'title' => _lang('Accounts'),
                'items' => [
                    ['label' => _lang('Account Statement'), 'route' => route('reports.account_statement'), 'description' => _lang('Open detailed statement movements for an individual account number.')],
                    ['label' => _lang('Account Balance'), 'route' => route('reports.account_balances'), 'description' => _lang('Review balance positions by account type and member.')],
                ],
            ],
            'transactions' => [
                'title' => _lang('Transactions & Expenses'),
                'items' => [
                    ['label' => _lang('Transaction Report'), 'route' => route('reports.transactions_report'), 'description' => _lang('Filter cash transactions by date, status, account, and type.')],
                    ['label' => _lang('Expense Report'), 'route' => route('reports.expense_report'), 'description' => _lang('Monitor expense entries, categories, and branch spending patterns.')],
                ],
            ],
            'banking' => [
                'title' => _lang('Banking & Revenue'),
                'items' => [
                    ['label' => _lang('Bank Transactions'), 'route' => route('reports.bank_transactions'), 'description' => _lang('Filter reconciliation movement by bank account, type, and status.')],
                    ['label' => _lang('Bank Account Balance'), 'route' => route('reports.bank_balances'), 'description' => _lang('Check current balances across configured bank accounts.')],
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

        $monthlyCredits = (clone $completedMonthlyTransactionsQuery)->where('dr_cr', 'cr')->sum('amount');
        $monthlyDebits = (clone $completedMonthlyTransactionsQuery)->where('dr_cr', 'dr')->sum('amount');
        $expensesThisMonthAmount = (clone $monthlyExpensesQuery)->sum('amount');
        $pendingBankTransactions = BankTransaction::where('status', 0)->count();
        $overdueRepayments = (clone $overdueRepaymentsQuery)->count();
        $dueToday = (clone $dueTodayQuery)->count();

        $reportHighlights = [
            'period_label' => $monthLabel,
            'active_members' => $activeMembers,
            'pending_members' => $pendingMembers,
            'active_loans' => $activeLoans,
            'pending_loans' => $pendingLoans,
            'active_portfolio_amount' => $activePortfolioAmount,
            'portfolio_outstanding' => $portfolioOutstanding,
            'overdue_repayments' => $overdueRepayments,
            'overdue_amount' => (clone $overdueRepaymentsQuery)->sum('amount_to_pay'),
            'due_today' => $dueToday,
            'due_today_amount' => (clone $dueTodayQuery)->sum('amount_to_pay'),
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
            'reportGroups' => $reportGroups,
            'reportHighlights' => $reportHighlights,
            'executiveCards' => $executiveCards,
            'branchReportSnapshot' => $branchReportSnapshot,
        ]);
    }
}
