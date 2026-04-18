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
        $today = Carbon::today()->toDateString();
        $monthStart = Carbon::today()->startOfMonth()->toDateString();
        $monthEnd = Carbon::today()->endOfMonth()->toDateString();

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

        $reportHighlights = [
            'active_members' => Member::count(),
            'active_loans' => Loan::where('status', 1)->count(),
            'overdue_repayments' => LoanRepayment::whereDate('repayment_date', '<', $today)->where('status', 0)->count(),
            'due_today' => LoanRepayment::whereDate('repayment_date', $today)->where('status', 0)->count(),
            'transactions_this_month' => Transaction::whereBetween('trans_date', [$monthStart, $monthEnd])->count(),
            'expenses_this_month' => Expense::whereBetween('expense_date', [$monthStart, $monthEnd])->count(),
            'pending_bank_transactions' => BankTransaction::where('status', 0)->count(),
            'bank_accounts' => BankAccount::count(),
        ];

        $branchReportSnapshot = Branch::get()->map(function ($branch) use ($today) {
            return (object) [
                'name' => $branch->name,
                'active_members' => Member::withoutGlobalScopes(['status'])->where('branch_id', $branch->id)->where('status', 1)->count(),
                'pending_members' => Member::withoutGlobalScopes(['status'])->where('branch_id', $branch->id)->where('status', 0)->count(),
                'active_loans' => Loan::withoutGlobalScopes(['borrower_id'])->where('branch_id', $branch->id)->where('status', 1)->count(),
                'overdue_repayments' => LoanRepayment::withoutGlobalScopes(['borrower_id'])->whereDate('repayment_date', '<', $today)->where('status', 0)->whereHas('loan', function ($query) use ($branch) {
                    $query->where('branch_id', $branch->id);
                })->count(),
                'pressure_score' => Member::withoutGlobalScopes(['status'])->where('branch_id', $branch->id)->where('status', 0)->count() + LoanRepayment::withoutGlobalScopes(['borrower_id'])->whereDate('repayment_date', '<', $today)->where('status', 0)->whereHas('loan', function ($query) use ($branch) {
                    $query->where('branch_id', $branch->id);
                })->count(),
            ];
        })->sortByDesc('pressure_score')->take(5)->values();

        return view('backend.admin.reports.index', [
            'page_title' => _lang('Reports'),
            'reportGroups' => $reportGroups,
            'reportHighlights' => $reportHighlights,
            'branchReportSnapshot' => $branchReportSnapshot,
        ]);
    }
}
