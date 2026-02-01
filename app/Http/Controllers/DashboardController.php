<?php
namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Loan;
use App\Models\LoanPayment;
use App\Models\LoanRepayment;
use App\Models\Member;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Builder;

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
    public function index() {
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

            // Your last deduction: sum of all loan repayment transactions for the member
            $lastRepayment = Transaction::where('member_id', $memberId)
                ->where('type', 'Loan_Repayment')
                ->orderBy('trans_date', 'desc')
                ->first();
            $data['last_deduction_total'] = (float) Transaction::where('member_id', $memberId)
                ->where('type', 'Loan_Repayment')
                ->sum('amount');
            $data['last_deduction_date'] = $lastRepayment ? $lastRepayment->getRawOriginal('trans_date') : null;

            // Your last Contributions: sum of deposit (cr) transactions, excluding Loans/Mkopo/Mikopo accounts
            $contributionsQuery = Transaction::where('member_id', $memberId)
                ->where('dr_cr', 'cr')
                ->whereHas('account.savings_type', function ($q) {
                    $q->whereRaw('LOWER(name) NOT IN (?, ?, ?)', ['loans', 'mkopo', 'mikopo']);
                });
            $data['last_contributions_total'] = (float) (clone $contributionsQuery)->sum('amount');
            $latestContribution = (clone $contributionsQuery)->orderBy('trans_date', 'desc')->first();
            $data['last_contribution_date'] = $latestContribution ? $latestContribution->getRawOriginal('trans_date') : null;

            // Account types (Jamii, Hisa, etc.) for card table: exclude Loans/Mkopo/Mikopo
            $accounts = get_account_details($memberId);
            $excludeTypes = ['loans', 'mkopo', 'mikopo'];
            $accountsFiltered = $accounts->filter(function ($a) use ($excludeTypes) {
                $name = strtolower($a->savings_type->name ?? '');
                return ! in_array($name, $excludeTypes);
            });
            $data['account_types_latest'] = [];
            foreach ($accountsFiltered->groupBy('savings_product_id') as $productId => $group) {
                $first = $group->first();
                $typeName = $first->savings_type ? $first->savings_type->name : _lang('Account');
                $currencyName = $first->savings_type && $first->savings_type->currency ? $first->savings_type->currency->name : '';
                $balance = $group->sum(function ($a) {
                    return (float) ($a->balance ?? 0);
                });
                $accountIds = $group->pluck('id')->toArray();
                $lastDeposit = Transaction::where('member_id', $memberId)
                    ->where('dr_cr', 'cr')
                    ->whereIn('savings_account_id', $accountIds)
                    ->orderBy('trans_date', 'desc')
                    ->first();
                $data['account_types_latest'][] = [
                    'name' => $typeName,
                    'balance' => $balance,
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

            return view("backend.admin.dashboard-$user_type", $data);
        }
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

}
