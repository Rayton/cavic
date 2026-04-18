<?php

namespace App\Http\Controllers;

use App\Models\AutomaticGateway;
use App\Models\BankAccount;
use App\Models\BankTransaction;
use App\Models\Branch;
use App\Models\DepositMethod;
use App\Models\DepositRequest;
use App\Models\Expense;
use App\Models\SavingsAccount;
use App\Models\SavingsProduct;
use App\Models\Transaction;
use App\Models\WithdrawMethod;
use App\Models\WithdrawRequest;
use Carbon\Carbon;

class FinanceHubController extends Controller
{
    public function index()
    {
        $today = Carbon::today()->toDateString();

        $latestTransactions = Transaction::with(['member', 'account.savings_type.currency'])
            ->latest('id')
            ->limit(8)
            ->get();

        $pendingCashTransactions = Transaction::with(['member', 'account.savings_type.currency'])
            ->where('status', 0)
            ->latest('id')
            ->limit(6)
            ->get();

        $pendingDeposits = DepositRequest::with(['member', 'method', 'account.savings_type.currency'])
            ->where('status', 0)
            ->latest('id')
            ->limit(6)
            ->get();

        $recentSavingsAccounts = SavingsAccount::with(['member.branch', 'savings_type.currency'])
            ->latest('id')
            ->limit(8)
            ->get();

        $savingsProductSummary = SavingsProduct::withCount('accounts')
            ->with('currency')
            ->latest('id')
            ->limit(6)
            ->get();

        $pendingWithdraws = WithdrawRequest::with(['member', 'method', 'account.savings_type.currency'])
            ->where('status', 0)
            ->latest('id')
            ->limit(6)
            ->get();

        $latestBankTransactions = BankTransaction::with('bank_account.currency')
            ->latest('id')
            ->limit(6)
            ->get();

        $pendingBankTransactions = BankTransaction::with('bank_account.currency')
            ->where('status', 0)
            ->latest('id')
            ->limit(6)
            ->get();

        $financeExceptionCards = [
            [
                'label' => _lang('Pending Deposit Requests'),
                'value' => DepositRequest::where('status', 0)->count(),
                'route' => route('deposit_requests.index'),
                'theme' => 'warning',
                'description' => _lang('Member deposit approvals are waiting'),
            ],
            [
                'label' => _lang('Pending Withdraw Requests'),
                'value' => WithdrawRequest::where('status', 0)->count(),
                'route' => route('withdraw_requests.index'),
                'theme' => 'danger',
                'description' => _lang('Cash-out requests need review'),
            ],
            [
                'label' => _lang('Pending Cash Transactions'),
                'value' => Transaction::where('status', 0)->count(),
                'route' => route('transactions.index'),
                'theme' => 'info',
                'description' => _lang('Posted movements are still not completed'),
            ],
            [
                'label' => _lang('Pending Bank Transactions'),
                'value' => BankTransaction::where('status', 0)->count(),
                'route' => route('bank_transactions.index'),
                'theme' => 'primary',
                'description' => _lang('Bank reconciliation items remain open'),
            ],
        ];

        $methodsSummary = [
            'automatic_gateways' => AutomaticGateway::count(),
            'deposit_methods' => DepositMethod::count(),
            'withdraw_methods' => WithdrawMethod::count(),
            'active_savings_products' => SavingsProduct::active()->count(),
        ];

        $branchFinancePressure = Branch::get()->map(function ($branch) {
            $pendingDepositCount = DepositRequest::where('status', 0)
                ->whereHas('member', function ($query) use ($branch) {
                    $query->withoutGlobalScopes()->where('branch_id', $branch->id);
                })->count();

            $pendingWithdrawCount = WithdrawRequest::where('status', 0)
                ->whereHas('member', function ($query) use ($branch) {
                    $query->withoutGlobalScopes()->where('branch_id', $branch->id);
                })->count();

            $pendingCashTransactionCount = Transaction::where('status', 0)
                ->whereHas('member', function ($query) use ($branch) {
                    $query->withoutGlobalScopes()->where('branch_id', $branch->id);
                })->count();

            return (object) [
                'name' => $branch->name,
                'pending_deposits' => $pendingDepositCount,
                'pending_withdraws' => $pendingWithdrawCount,
                'pending_cash_transactions' => $pendingCashTransactionCount,
                'pressure_score' => $pendingDepositCount + $pendingWithdrawCount + $pendingCashTransactionCount,
            ];
        })->sortByDesc('pressure_score')->take(5)->values();

        return view('backend.admin.finance.index', [
            'page_title' => _lang('Finance'),
            'financeStats' => [
                'deposit_requests' => DepositRequest::where('status', 0)->count(),
                'withdraw_requests' => WithdrawRequest::where('status', 0)->count(),
                'wallet_records' => SavingsAccount::count(),
                'bank_accounts' => BankAccount::count(),
                'today_transactions' => Transaction::whereDate('trans_date', $today)->count(),
                'today_expenses' => Expense::whereDate('expense_date', $today)->count(),
                'pending_cash_transactions' => Transaction::where('status', 0)->count(),
                'pending_bank_transactions' => BankTransaction::where('status', 0)->count(),
            ],
            'latestTransactions' => $latestTransactions,
            'recentSavingsAccounts' => $recentSavingsAccounts,
            'savingsProductSummary' => $savingsProductSummary,
            'pendingCashTransactions' => $pendingCashTransactions,
            'pendingDeposits' => $pendingDeposits,
            'pendingWithdraws' => $pendingWithdraws,
            'latestBankTransactions' => $latestBankTransactions,
            'pendingBankTransactions' => $pendingBankTransactions,
            'financeExceptionCards' => $financeExceptionCards,
            'branchFinancePressure' => $branchFinancePressure,
            'methodsSummary' => $methodsSummary,
        ]);
    }
}
