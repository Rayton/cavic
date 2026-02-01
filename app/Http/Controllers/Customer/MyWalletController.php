<?php
namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use App\Models\Transaction;
use Illuminate\Http\Request;

class MyWalletController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        date_default_timezone_set(get_timezone());
    }

    /**
     * Display the My Wallet page with loans, account types and transactions.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        $member = auth()->user()->member;
        if (!$member) {
            abort(403, _lang('Membership not found'));
        }

        $assets = ['datatable'];

        $loans = Loan::withoutGlobalScopes()
            ->where('borrower_id', $member->id)
            ->with(['borrower', 'currency', 'loan_product', 'approved_by', 'created_by', 'repayments', 'next_payment', 'approvals.approver'])
            ->withSum('repaymentTransactions', 'amount')
            ->orderBy('loans.id', 'desc')
            ->get();

        $accounts = get_account_details($member->id);

        // Group accounts by savings type (Hisa, Jamii, etc.) - one tab per type (exclude "Loans" - loans are shown in Loans tab)
        $accountTypesAndAccounts = $accounts->groupBy('savings_product_id')->map(function ($accountsOfType, $productId) {
            $first = $accountsOfType->first();
            $typeName = $first && $first->savings_type ? $first->savings_type->name : _lang('Account');
            return [
                'id'   => 'account-' . $productId,
                'name' => $typeName,
                'accounts' => $accountsOfType,
            ];
        })->filter(function ($item) {
            // Do not show "Loans" as its own account tab - loans are implemented independently
            return strtolower($item['name']) !== 'loans';
        })->values()->all();

        $transactions = Transaction::where('member_id', $member->id)
            ->with(['account.savings_type.currency'])
            ->orderBy('trans_date', 'desc')
            ->limit(1000)
            ->get();

        return view('backend.customer.my_wallet.index', compact('loans', 'accountTypesAndAccounts', 'transactions', 'assets'));
    }

    /**
     * Show loan repayment transactions for a loan (modal content).
     *
     * @param string $tenant Tenant slug (from route)
     * @param int $id Loan ID
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\Response
     */
    public function loanRepayments($tenant, $id)
    {
        $member = auth()->user()->member;
        if (!$member) {
            abort(403, _lang('Membership not found'));
        }

        $loan = Loan::withoutGlobalScopes()
            ->where('id', $id)
            ->where('borrower_id', $member->id)
            ->with('currency')
            ->firstOrFail();

        $transactions = Transaction::where('loan_id', $loan->id)
            ->where('type', 'Loan_Repayment')
            ->with(['account.savings_type.currency'])
            ->orderBy('trans_date', 'asc')
            ->get();

        if (request()->ajax()) {
            return view('backend.customer.my_wallet.partials.loan_repayments', compact('loan', 'transactions'));
        }
        return view('backend.customer.my_wallet.partials.loan_repayments', compact('loan', 'transactions'));
    }

    /**
     * Show loan deposit (disbursement) transactions for a loan (modal content).
     *
     * @param string $tenant Tenant slug (from route)
     * @param int $id Loan ID
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\Response
     */
    public function loanDeposits($tenant, $id)
    {
        $member = auth()->user()->member;
        if (!$member) {
            abort(403, _lang('Membership not found'));
        }

        $loan = Loan::withoutGlobalScopes()
            ->where('id', $id)
            ->where('borrower_id', $member->id)
            ->with('currency')
            ->firstOrFail();

        $transactions = Transaction::where('loan_id', $loan->id)
            ->where('type', 'Loan')
            ->with(['account.savings_type.currency'])
            ->orderBy('trans_date', 'desc')
            ->get();

        if (request()->ajax()) {
            return view('backend.customer.my_wallet.partials.loan_deposits', compact('loan', 'transactions'));
        }
        return view('backend.customer.my_wallet.partials.loan_deposits', compact('loan', 'transactions'));
    }
}
