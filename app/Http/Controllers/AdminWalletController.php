<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Transaction;
use App\Models\SavingsProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminWalletController extends Controller
{
    public function __construct()
    {
        date_default_timezone_set(get_timezone());
        view()->share('assets', ['datatable']);
    }

    /**
     * Period filter options.
     */
    public static function periodOptions(): array
    {
        return [
            'this_month'    => _lang('This Month'),
            'last_month'    => _lang('Last Month'),
            'this_quarter'  => _lang('This Quarter'),
            'last_quarter' => _lang('Last Quarter'),
            'this_year'     => _lang('This Year'),
            'past_year'     => _lang('Past Year'),
            'custom'        => _lang('Custom Date Range'),
        ];
    }

    /**
     * Resolve start and end date from period and optional custom dates.
     */
    public static function getDateRange(string $period, ?string $date1 = null, ?string $date2 = null): array
    {
        $now = Carbon::now();
        switch ($period) {
            case 'this_month':
                $start = $now->copy()->startOfMonth();
                $end   = $now->copy()->endOfMonth();
                break;
            case 'last_month':
                $start = $now->copy()->subMonth()->startOfMonth();
                $end   = $now->copy()->subMonth()->endOfMonth();
                break;
            case 'this_quarter':
                $start = $now->copy()->startOfQuarter();
                $end   = $now->copy()->endOfQuarter();
                break;
            case 'last_quarter':
                $start = $now->copy()->subQuarter()->startOfQuarter();
                $end   = $now->copy()->subQuarter()->endOfQuarter();
                break;
            case 'this_year':
                $start = $now->copy()->startOfYear();
                $end   = $now->copy()->endOfYear();
                break;
            case 'past_year':
                $start = $now->copy()->subYear()->startOfYear();
                $end   = $now->copy()->subYear()->endOfYear();
                break;
            case 'custom':
                $start = $date1 ? Carbon::parse($date1)->startOfDay() : $now->copy()->startOfYear();
                $end   = $date2 ? Carbon::parse($date2)->endOfDay() : $now->copy()->endOfYear();
                break;
            default:
                $start = $now->copy()->startOfYear();
                $end   = $now->copy()->endOfYear();
        }
        return [$start, $end];
    }

    /**
     * List of [year, month, label] for each month in range (for table columns).
     */
    public static function getMonthsInRange(Carbon $start, Carbon $end): array
    {
        $months = [];
        $cursor = $start->copy()->startOfMonth();
        while ($cursor->lte($end)) {
            $months[] = [
                'year'  => $cursor->year,
                'month' => $cursor->month,
                'label' => $cursor->format('F'), // January, February, ...
            ];
            $cursor->addMonth();
        }
        return $months;
    }

    /**
     * Index: show Wallets page with filters and monthly tables data.
     */
    public function index(Request $request)
    {
        $period = $request->get('period', 'this_year');
        $date1  = $request->get('date1');
        $date2  = $request->get('date2');

        [$start, $end] = self::getDateRange($period, $date1, $date2);
        $startStr = $start->format('Y-m-d H:i:s');
        $endStr   = $end->format('Y-m-d H:i:s');

        $months = self::getMonthsInRange($start, $end);

        // All active members
        $members = Member::orderBy('id')->get(['id', 'first_name', 'last_name']);

        // Account types (savings products) for tabs - exclude "Loans" type by name if any
        $savingsProducts = SavingsProduct::with('currency')
            ->orderBy('id')
            ->get();

        $accountTypesAndAccounts = $savingsProducts->map(function ($product) {
            return [
                'id'   => 'account-' . $product->id,
                'name' => $product->name,
                'product_id' => $product->id,
            ];
        })->filter(function ($item) {
            return strtolower($item['name']) !== 'loans';
        })->values()->all();

        // Loans tab: per member, per month = sum of Loan_Repayment amount
        $loansMonthly = $this->getLoansMonthlyTotals($startStr, $endStr);

        // Per account type: per member, per month = sum of credit (dr_cr='cr') for that savings_product_id
        $accountTypeMonthly = [];
        foreach ($accountTypesAndAccounts as $accountType) {
            $productId = $accountType['product_id'];
            $accountTypeMonthly[$productId] = $this->getAccountTypeMonthlyTotals($productId, $startStr, $endStr);
        }

        // Transactions tab: per member, per month = net (sum cr - sum dr) for all transactions
        $transactionsMonthly = $this->getTransactionsMonthlyTotals($startStr, $endStr);

        return view('backend.admin.wallets.index', compact(
            'period',
            'date1',
            'date2',
            'months',
            'members',
            'accountTypesAndAccounts',
            'loansMonthly',
            'accountTypeMonthly',
            'transactionsMonthly',
            'start',
            'end'
        ));
    }

    /**
     * Loan repayments per member per month (member_id => [ 'Y-m' => sum ]).
     */
    protected function getLoansMonthlyTotals(string $start, string $end): array
    {
        $rows = Transaction::query()
            ->where('type', 'Loan_Repayment')
            ->where('status', 2)
            ->whereBetween(DB::raw('DATE(trans_date)'), [
                \Carbon\Carbon::parse($start)->format('Y-m-d'),
                \Carbon\Carbon::parse($end)->format('Y-m-d'),
            ])
            ->selectRaw('member_id, YEAR(trans_date) as y, MONTH(trans_date) as m, SUM(amount) as total')
            ->groupBy('member_id', 'y', 'm')
            ->get();

        $out = [];
        foreach ($rows as $r) {
            $key = $r->y . '-' . str_pad($r->m, 2, '0', STR_PAD_LEFT);
            if (!isset($out[$r->member_id])) {
                $out[$r->member_id] = [];
            }
            $out[$r->member_id][$key] = (float) $r->total;
        }
        return $out;
    }

    /**
     * Credits per member per month for a given savings product (member_id => [ 'Y-m' => sum ]).
     */
    protected function getAccountTypeMonthlyTotals(int $savingsProductId, string $start, string $end): array
    {
        $startDate = \Carbon\Carbon::parse($start)->format('Y-m-d');
        $endDate   = \Carbon\Carbon::parse($end)->format('Y-m-d');

        $rows = Transaction::query()
            ->join('savings_accounts', 'savings_accounts.id', '=', 'transactions.savings_account_id')
            ->where('savings_accounts.savings_product_id', $savingsProductId)
            ->where('transactions.dr_cr', 'cr')
            ->where('transactions.status', 2)
            ->whereBetween(DB::raw('DATE(transactions.trans_date)'), [$startDate, $endDate])
            ->selectRaw('transactions.member_id, YEAR(transactions.trans_date) as y, MONTH(transactions.trans_date) as m, SUM(transactions.amount) as total')
            ->groupBy('transactions.member_id', 'y', 'm')
            ->get();

        $out = [];
        foreach ($rows as $r) {
            $key = $r->y . '-' . str_pad($r->m, 2, '0', STR_PAD_LEFT);
            if (!isset($out[$r->member_id])) {
                $out[$r->member_id] = [];
            }
            $out[$r->member_id][$key] = (float) $r->total;
        }
        return $out;
    }

    /**
     * Net movement (credits - debits) per member per month (member_id => [ 'Y-m' => net ]).
     */
    protected function getTransactionsMonthlyTotals(string $start, string $end): array
    {
        $startDate = \Carbon\Carbon::parse($start)->format('Y-m-d');
        $endDate   = \Carbon\Carbon::parse($end)->format('Y-m-d');

        $rows = Transaction::query()
            ->where('status', 2)
            ->whereBetween(DB::raw('DATE(trans_date)'), [$startDate, $endDate])
            ->selectRaw("member_id, YEAR(trans_date) as y, MONTH(trans_date) as m, SUM(CASE WHEN dr_cr = 'cr' THEN amount ELSE -amount END) as total")
            ->groupBy('member_id', 'y', 'm')
            ->get();

        $out = [];
        foreach ($rows as $r) {
            $key = $r->y . '-' . str_pad($r->m, 2, '0', STR_PAD_LEFT);
            if (!isset($out[$r->member_id])) {
                $out[$r->member_id] = [];
            }
            $out[$r->member_id][$key] = (float) $r->total;
        }
        return $out;
    }
}
