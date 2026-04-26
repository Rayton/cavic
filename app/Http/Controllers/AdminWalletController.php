<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\SavingsAccount;
use App\Models\Transaction;
use App\Models\SavingsProduct;
use App\Models\Loan;
use App\Models\LoanProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class AdminWalletController extends Controller
{
    protected array $monthNameMap = [
        'january'   => 1,
        'jan'       => 1,
        'february'  => 2,
        'feb'       => 2,
        'march'     => 3,
        'mar'       => 3,
        'april'     => 4,
        'apr'       => 4,
        'may'       => 5,
        'june'      => 6,
        'jun'       => 6,
        'july'      => 7,
        'jul'       => 7,
        'august'    => 8,
        'aug'       => 8,
        'september' => 9,
        'sep'       => 9,
        'sept'      => 9,
        'october'   => 10,
        'oct'       => 10,
        'november'  => 11,
        'nov'       => 11,
        'december'  => 12,
        'dec'       => 12,
    ];

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
        $loanWalletSummaries = $this->getLoanWalletSummaries();

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
            'loanWalletSummaries',
            'start',
            'end'
        ));
    }

    /**
     * Download tab-specific import template.
     */
    public function downloadTemplate(Request $request)
    {
        $tab = strtolower((string) $request->query('tab', ''));
        $productId = (int) $request->query('product_id', 0);
        $filename = 'wallets_import_template.xlsx';
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        if ($tab === 'loans') {
            $filename = 'wallets_loans_import_template.xlsx';
            $this->buildLoansTemplateSheet($sheet);
        } elseif ($tab === 'transactions') {
            $filename = 'wallets_transactions_import_template.xlsx';
            $this->buildMonthlyTemplateSheet($sheet, _lang('Transactions'));
        } elseif ($tab === 'account') {
            $product = SavingsProduct::find($productId);
            if (! $product || strtolower($product->name) === 'loans') {
                return back()->with('error', _lang('Invalid savings product selected for template'));
            }

            $filename = 'wallets_' . preg_replace('/[^a-z0-9_-]/i', '_', strtolower($product->name)) . '_import_template.xlsx';
            $this->buildMonthlyTemplateSheet($sheet, $product->name);
        } elseif ($tab === 'contributions') {
            $filename = 'wallets_contributions_import_template.xlsx';
            $this->buildContributionsTemplateSheet($sheet);
        } else {
            return back()->with('error', _lang('Invalid wallet tab selected'));
        }

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Import tab-specific wallet data from XLSX.
     */
    public function import(Request $request)
    {
        @ini_set('max_execution_time', 0);
        @set_time_limit(0);

        $validator = Validator::make($request->all(), [
            'tab' => 'required|in:loans,account,transactions,contributions',
            'product_id' => 'required_if:tab,account|nullable|integer',
            'file' => 'required|mimes:xlsx',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $tab = strtolower((string) $request->input('tab'));
        $product = null;

        if ($tab === 'account') {
            $productId = (int) $request->input('product_id');
            $product = SavingsProduct::find($productId);
            if (! $product || strtolower($product->name) === 'loans') {
                return back()->with('error', _lang('Invalid savings product selected for import'));
            }
        }

        $sheets = Excel::toArray([], $request->file('file'));
        $rows = $sheets[0] ?? [];

        if (count($rows) < 2) {
            return back()->with('error', _lang('Uploaded file does not contain data rows'));
        }

        DB::beginTransaction();
        try {
            $imported = 0;

            if ($tab === 'loans') {
                $imported = $this->importMonthlyMatrixRows($rows, 'loans');
            } elseif ($tab === 'transactions') {
                $imported = $this->importMonthlyMatrixRows($rows, 'transactions');
            } elseif ($tab === 'account') {
                $imported = $this->importMonthlyMatrixRows($rows, 'account', $product);
            } elseif ($tab === 'contributions') {
                $imported = $this->importContributionsRows($rows);
            }

            DB::commit();

            if ($imported === 0) {
                return back()->with('error', _lang('Nothing Imported, check your template data'));
            }

            return back()->with('success', $imported . ' ' . _lang('Transactions Imported Sucessfully'));
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return back()->with('error', _lang('Import failed, please check your template and try again'));
        }
    }

    /**
     * Build loans template with opening fields + month-year columns.
     */
    protected function buildLoansTemplateSheet(Worksheet $sheet): void
    {
        $sheet->setTitle('Loans');

        $headers = array_merge([
            'member_id',
            'member_name',
            'opening_loan_amount',
            'opening_interest_amount',
            'opening_balance',
            'opening_date',
        ], $this->defaultMonthYearLabels());

        foreach ($headers as $index => $header) {
            $sheet->setCellValueByColumnAndRow($index + 1, 1, $header);
        }

        $members = Member::orderBy('id')->get(['id', 'first_name', 'last_name']);
        $row = 2;
        foreach ($members as $member) {
            $sheet->setCellValueByColumnAndRow(1, $row, $member->id);
            $sheet->setCellValueByColumnAndRow(2, $row, trim($member->first_name . ' ' . $member->last_name));
            $row++;
        }

        if ($members->isEmpty()) {
            $sheet->setCellValueByColumnAndRow(1, 2, '');
            $sheet->setCellValueByColumnAndRow(2, 2, 'Sample Member');
        }
    }

    /**
     * Build monthly matrix template used by Loans/Hisa/Jamii/Transactions tabs.
     */
    protected function buildMonthlyTemplateSheet(Worksheet $sheet, string $title): void
    {
        $sheet->setTitle(substr(preg_replace('/[\\:\\\\\\/\\?\\*\\[\\]]/', '_', $title), 0, 31));

        $headers = array_merge(['member_id', 'member_name'], $this->defaultMonthYearLabels());
        foreach ($headers as $index => $header) {
            $col = $index + 1;
            $sheet->setCellValueByColumnAndRow($col, 1, $header);
        }

        $members = Member::orderBy('id')->get(['id', 'first_name', 'last_name']);
        $row = 2;

        foreach ($members as $member) {
            $sheet->setCellValueByColumnAndRow(1, $row, $member->id);
            $sheet->setCellValueByColumnAndRow(2, $row, trim($member->first_name . ' ' . $member->last_name));
            $row++;
        }

        if ($members->isEmpty()) {
            $sheet->setCellValueByColumnAndRow(1, 2, '');
            $sheet->setCellValueByColumnAndRow(2, 2, 'Sample Member');
        }
    }

    /**
     * Build normalized contributions template (one row per member per month).
     */
    protected function buildContributionsTemplateSheet(Worksheet $sheet): void
    {
        $sheet->setTitle('Contributions');

        $products = SavingsProduct::orderBy('id')
            ->get()
            ->filter(function ($product) {
                return strtolower((string) $product->name) !== 'loans';
            })
            ->values();

        $headers = ['member_id', 'member_name'];
        $monthYearLabels = $this->defaultMonthYearLabels();

        foreach ($monthYearLabels as $monthYearLabel) {
            $headers[] = 'loan_repayments ' . $monthYearLabel;
            foreach ($products as $product) {
                $headers[] = $this->normalizeHeader((string) $product->name) . ' ' . $monthYearLabel;
            }
        }

        foreach ($headers as $index => $header) {
            $sheet->setCellValueByColumnAndRow($index + 1, 1, $header);
        }

        $members = Member::orderBy('id')->get(['id', 'first_name', 'last_name']);
        $row = 2;

        foreach ($members as $member) {
            $sheet->setCellValueByColumnAndRow(1, $row, $member->id);
            $sheet->setCellValueByColumnAndRow(2, $row, trim($member->first_name . ' ' . $member->last_name));
            $row++;
        }

        if ($members->isEmpty()) {
            $sheet->setCellValueByColumnAndRow(1, 2, '');
            $sheet->setCellValueByColumnAndRow(2, 2, 'Sample Member');
        }
    }

    /**
     * Import monthly matrix rows from Loans/Hisa/Jamii/Transactions tabs.
     */
    protected function importMonthlyMatrixRows(array $rows, string $tab, ?SavingsProduct $product = null): int
    {
        $headerRow = $rows[0] ?? [];
        $headers = $this->mapHeaders($headerRow);
        $monthYearColumns = $this->getMonthYearColumnsFromHeaderRow($headerRow);
        $count = 0;
        $loansProduct = $tab === 'loans' ? $this->getLoansSavingsProduct() : null;

        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            if ($this->isRowEmpty($row)) {
                continue;
            }

            $member = $this->resolveMemberFromRow($row, $headers);
            if (! $member) {
                continue;
            }

            $linkedLoanId = null;
            $loanAccountId = null;

            if ($tab === 'loans' && $loansProduct) {
                $loanAccount = $this->getOrCreateSavingsAccount($member, $loansProduct);
                $loanAccountId = $loanAccount?->id;

                $openingLoan = $this->parseAmount($this->getValueByHeaders($row, $headers, ['opening_loan_amount', 'opening_loan', 'opening_principal', 'opening_mkopo']));
                $openingInterest = $this->parseAmount($this->getValueByHeaders($row, $headers, ['opening_interest_amount', 'opening_interest', 'opening_riba']));
                $openingBalance = $this->parseAmount($this->getValueByHeaders($row, $headers, ['opening_balance', 'remaining_balance', 'kiasi_baki']));
                $openingDate = $this->getValueByHeaders($row, $headers, ['opening_date', 'loan_opening_date', 'release_date']);

                $fallbackDate = null;
                if (!empty($monthYearColumns)) {
                    $firstMonthMeta = collect($monthYearColumns)->sortBy(function ($meta) {
                        return ((int) $meta['year'] * 100) + (int) $meta['month'];
                    })->first();
                    if ($firstMonthMeta) {
                        $fallbackDate = Carbon::create((int) $firstMonthMeta['year'], (int) $firstMonthMeta['month'], 1)->format('Y-m-d');
                    }
                }

                $loan = $this->createImportedLoanFromOpening(
                    $member,
                    $loansProduct,
                    $loanAccountId,
                    $openingLoan,
                    $openingInterest,
                    $openingBalance,
                    $openingDate,
                    $fallbackDate
                );

                if ($loan) {
                    $linkedLoanId = $loan->id;
                } else {
                    $linkedLoanId = Loan::where('borrower_id', $member->id)->orderByDesc('id')->value('id');
                }
            }

            if (!empty($monthYearColumns)) {
                foreach ($monthYearColumns as $columnIndex => $meta) {
                    $amount = $this->parseAmount($row[$columnIndex] ?? null);
                    if (abs($amount) <= 0) {
                        continue;
                    }

                    if ($tab === 'loans') {
                        if ($this->createImportedTransaction($member, $loanAccountId, $meta['year'], $meta['month'], abs($amount), $amount >= 0 ? 'dr' : 'cr', 'Loan_Repayment', _lang('Wallet loans import'), $linkedLoanId)) {
                            $count++;
                        }
                    } elseif ($tab === 'transactions') {
                        if ($this->createImportedTransaction($member, null, $meta['year'], $meta['month'], abs($amount), $amount >= 0 ? 'cr' : 'dr', 'Wallet_Adjustment', _lang('Wallet transactions import'))) {
                            $count++;
                        }
                    } elseif ($tab === 'account' && $product) {
                        $account = $this->getOrCreateSavingsAccount($member, $product);
                        if (! $account) {
                            continue;
                        }

                        if ($this->createImportedTransaction($member, $account->id, $meta['year'], $meta['month'], abs($amount), $amount >= 0 ? 'cr' : 'dr', 'Deposit', _lang('Wallet account import'))) {
                            $count++;
                        }
                    }
                }
                continue;
            }

            // Backward-compatible legacy format: year + month columns.
            $yearValue = $this->getValueByHeaders($row, $headers, ['year']);
            $year = (int) ($yearValue !== null && $yearValue !== '' ? $yearValue : Carbon::now()->year);
            if ($year < 1900 || $year > 2200) {
                $year = (int) Carbon::now()->year;
            }

            foreach ($this->fullMonthNames() as $monthName) {
                $amountRaw = $this->getValueByHeaders($row, $headers, [$monthName, substr($monthName, 0, 3)]);
                $amount = $this->parseAmount($amountRaw);
                if (abs($amount) <= 0) {
                    continue;
                }

                $month = $this->monthNameMap[$monthName];
                if ($tab === 'loans') {
                    if ($this->createImportedTransaction($member, $loanAccountId, $year, $month, abs($amount), $amount >= 0 ? 'dr' : 'cr', 'Loan_Repayment', _lang('Wallet loans import'), $linkedLoanId)) {
                        $count++;
                    }
                } elseif ($tab === 'transactions') {
                    if ($this->createImportedTransaction($member, null, $year, $month, abs($amount), $amount >= 0 ? 'cr' : 'dr', 'Wallet_Adjustment', _lang('Wallet transactions import'))) {
                        $count++;
                    }
                } elseif ($tab === 'account' && $product) {
                    $account = $this->getOrCreateSavingsAccount($member, $product);
                    if (! $account) {
                        continue;
                    }

                    if ($this->createImportedTransaction($member, $account->id, $year, $month, abs($amount), $amount >= 0 ? 'cr' : 'dr', 'Deposit', _lang('Wallet account import'))) {
                        $count++;
                    }
                }
            }
        }

        return $count;
    }

    /**
     * Import normalized contributions rows.
     */
    protected function importContributionsRows(array $rows): int
    {
        $headerRow = $rows[0] ?? [];
        $headers = $this->mapHeaders($headerRow);
        $products = SavingsProduct::orderBy('id')
            ->get()
            ->filter(function ($product) {
                return strtolower((string) $product->name) !== 'loans';
            })
            ->values();

        $productMap = [];
        foreach ($products as $product) {
            $productMap[$this->normalizeHeader((string) $product->name)] = $product;
        }

        $monthYearColumns = $this->getContributionMonthYearColumnsFromHeaderRow($headerRow, array_keys($productMap));

        $count = 0;

        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            if ($this->isRowEmpty($row)) {
                continue;
            }

            $member = $this->resolveMemberFromRow($row, $headers);
            if (! $member) {
                continue;
            }

            if (!empty($monthYearColumns)) {
                foreach ($monthYearColumns as $columnIndex => $meta) {
                    $amount = $this->parseAmount($row[$columnIndex] ?? null);
                    if (abs($amount) <= 0) {
                        continue;
                    }

                    if ($meta['type'] === 'loan_repayments') {
                        if ($this->createImportedTransaction($member, null, $meta['year'], $meta['month'], abs($amount), $amount >= 0 ? 'dr' : 'cr', 'Loan_Repayment', _lang('Wallet contributions import'))) {
                            $count++;
                        }
                        continue;
                    }

                    $product = $productMap[$meta['type']] ?? null;
                    if (! $product) {
                        continue;
                    }

                    $account = $this->getOrCreateSavingsAccount($member, $product);
                    if (! $account) {
                        continue;
                    }

                    if ($this->createImportedTransaction($member, $account->id, $meta['year'], $meta['month'], abs($amount), $amount >= 0 ? 'cr' : 'dr', 'Deposit', _lang('Wallet contributions import'))) {
                        $count++;
                    }
                }
                continue;
            }

            // Backward-compatible legacy format: one row per month.
            $yearValue = $this->getValueByHeaders($row, $headers, ['year']);
            $year = (int) ($yearValue !== null && $yearValue !== '' ? $yearValue : Carbon::now()->year);
            if ($year < 1900 || $year > 2200) {
                $year = (int) Carbon::now()->year;
            }

            $monthValue = $this->getValueByHeaders($row, $headers, ['month']);
            $month = $this->parseMonth($monthValue);
            if (! $month) {
                $month = 1;
            }

            $loanAmount = $this->parseAmount($this->getValueByHeaders($row, $headers, ['loan_repayments', 'loan_repayment', 'loans']));
            if (abs($loanAmount) > 0) {
                if ($this->createImportedTransaction($member, null, $year, $month, abs($loanAmount), $loanAmount >= 0 ? 'dr' : 'cr', 'Loan_Repayment', _lang('Wallet contributions import'))) {
                    $count++;
                }
            }

            foreach ($products as $product) {
                $headerName = $this->normalizeHeader((string) $product->name);
                $amount = $this->parseAmount($this->getValueByHeaders($row, $headers, [$headerName]));
                if (abs($amount) <= 0) {
                    continue;
                }

                $productKey = $this->normalizeHeader((string) $product->name);
                $productModel = $productMap[$productKey] ?? null;
                if (! $productModel) {
                    continue;
                }

                $account = $this->getOrCreateSavingsAccount($member, $productModel);
                if (! $account) {
                    continue;
                }

                if ($this->createImportedTransaction($member, $account->id, $year, $month, abs($amount), $amount >= 0 ? 'cr' : 'dr', 'Deposit', _lang('Wallet contributions import'))) {
                    $count++;
                }
            }
        }

        return $count;
    }

    /**
     * Create one imported transaction row.
     */
    protected function createImportedTransaction(
        Member $member,
        ?int $savingsAccountId,
        int $year,
        int $month,
        float $amount,
        string $drCr,
        string $type,
        string $description,
        ?int $loanId = null
    ): bool {
        if ($amount <= 0) {
            return false;
        }

        $date = Carbon::create($year, $month, 1, 12, 0, 0)->endOfMonth();

        $transaction = new Transaction();
        $transaction->trans_date = $date->format('Y-m-d H:i:s');
        $transaction->member_id = $member->id;
        $transaction->savings_account_id = $savingsAccountId;
        $transaction->amount = $amount;
        $transaction->dr_cr = $drCr === 'dr' ? 'dr' : 'cr';
        $transaction->type = substr($type, 0, 30);
        $transaction->method = 'Import';
        $transaction->status = 2;
        $transaction->note = _lang('Bulk wallet import');
        $transaction->description = $description;
        $transaction->loan_id = $loanId;
        $transaction->branch_id = $member->branch_id ?? (auth()->check() ? auth()->user()->branch_id : null);
        $transaction->created_user_id = auth()->id();
        $transaction->save();

        return true;
    }

    /**
     * Resolve or create member from template row.
     */
    protected function resolveMemberFromRow(array $row, array $headers): ?Member
    {
        $memberId = $this->getValueByHeaders($row, $headers, ['member_id', 'id']);
        if ($memberId !== null && $memberId !== '' && is_numeric($memberId)) {
            $member = Member::find((int) $memberId);
            if ($member) {
                return $member;
            }
        }

        $memberName = trim((string) $this->getValueByHeaders($row, $headers, ['member_name', 'name']));
        if ($memberName === '') {
            return null;
        }

        $member = $this->findMemberByName($memberName);
        if ($member) {
            return $member;
        }

        return $this->createImportedMember($memberName);
    }

    /**
     * Find member by full/partial name.
     */
    protected function findMemberByName(string $name): ?Member
    {
        $normalized = trim(preg_replace('/\s+/', ' ', $name));
        if ($normalized === '') {
            return null;
        }

        $fullNameMatch = Member::whereRaw("LOWER(CONCAT(first_name, ' ', last_name)) = ?", [mb_strtolower($normalized)])->first();
        if ($fullNameMatch) {
            return $fullNameMatch;
        }

        $parts = preg_split('/\s+/', $normalized, 2);
        $firstName = $parts[0] ?? '';
        $lastName = $parts[1] ?? '';

        if ($firstName === '') {
            return null;
        }

        if ($lastName !== '') {
            $exact = Member::whereRaw('LOWER(first_name) = ?', [mb_strtolower($firstName)])
                ->whereRaw('LOWER(last_name) = ?', [mb_strtolower($lastName)])
                ->first();
            if ($exact) {
                return $exact;
            }
        }

        return Member::whereRaw('LOWER(first_name) = ?', [mb_strtolower($firstName)])->first();
    }

    /**
     * Create member for unmatched import row and auto-create required accounts.
     */
    protected function createImportedMember(string $name): Member
    {
        $parts = preg_split('/\s+/', trim($name), 2);
        $firstName = $parts[0] ?: 'Member';
        $lastName = $parts[1] ?? 'Imported';

        $nextId = ((int) Member::max('id')) + 1;
        $memberNo = 'IMP-' . str_pad((string) $nextId, 6, '0', STR_PAD_LEFT);

        $member = new Member();
        $member->first_name = $firstName;
        $member->last_name = $lastName;
        $member->member_no = $memberNo;
        $member->status = 1;
        $member->branch_id = auth()->check() ? auth()->user()->branch_id : null;
        $member->save();

        $allProducts = SavingsProduct::orderBy('id')->get();
        foreach ($allProducts as $product) {
            $this->getOrCreateSavingsAccount($member, $product);
        }

        return $member;
    }

    /**
     * Get existing member savings account for product, or create one.
     */
    protected function getOrCreateSavingsAccount(Member $member, SavingsProduct $product): ?SavingsAccount
    {
        $account = SavingsAccount::withoutGlobalScopes(['status'])
            ->where('member_id', $member->id)
            ->where('savings_product_id', $product->id)
            ->first();

        if ($account) {
            return $account;
        }

        $product = SavingsProduct::find($product->id);
        if (! $product) {
            return null;
        }

        $nextNumber = (int) ($product->starting_account_number ?? 1);
        if ($nextNumber <= 0) {
            $nextNumber = 1;
        }

        $accountNumber = trim((string) ($product->account_number_prefix ?? '') . $nextNumber);
        if ($accountNumber === '') {
            $accountNumber = 'ACC' . $product->id . '-' . $nextNumber;
        }

        $account = new SavingsAccount();
        $account->account_number = $accountNumber;
        $account->member_id = $member->id;
        $account->savings_product_id = $product->id;
        $account->status = 1;
        $account->opening_balance = 0;
        $account->description = _lang('Auto created by wallet import');
        $account->created_user_id = auth()->id();
        $account->save();

        $product->starting_account_number = $nextNumber + 1;
        $product->save();

        return $account;
    }

    /**
     * Get the savings product used as loans account type.
     */
    protected function getLoansSavingsProduct(): ?SavingsProduct
    {
        $product = SavingsProduct::whereRaw('LOWER(name) = ?', ['loans'])->first();
        if ($product) {
            return $product;
        }

        return SavingsProduct::orderBy('id')->first();
    }

    /**
     * Create imported opening loan (if opening amounts provided).
     */
    protected function createImportedLoanFromOpening(
        Member $member,
        SavingsProduct $loansProduct,
        ?int $loanAccountId,
        float $openingLoanAmount,
        float $openingInterestAmount,
        float $openingBalance,
        $openingDate,
        ?string $fallbackDate = null
    ): ?Loan {
        $principal = $openingLoanAmount > 0 ? $openingLoanAmount : max(0, $openingBalance);
        $interest = max(0, $openingInterestAmount);

        if ($principal <= 0 && $interest <= 0) {
            return null;
        }

        if ($principal <= 0 && $interest > 0) {
            $principal = $interest;
        }

        $totalPayable = $principal + $interest;
        if ($totalPayable <= 0) {
            $totalPayable = $principal;
        }

        $remaining = $openingBalance > 0 ? $openingBalance : $totalPayable;
        $totalPaid = max(0, $principal - $remaining);
        if ($totalPaid > $principal) {
            $totalPaid = $principal;
        }

        $loanProduct = LoanProduct::active()->orderBy('id')->first();
        if (! $loanProduct) {
            $loanProduct = LoanProduct::orderBy('id')->first();
        }
        if (! $loanProduct) {
            return null;
        }

        $resolvedDate = $this->parseImportDate($openingDate, $fallbackDate ?? Carbon::now()->format('Y-m-d'));
        $resolvedDateStr = $resolvedDate ? $resolvedDate->format('Y-m-d') : Carbon::now()->format('Y-m-d');

        $loan = new Loan();
        $loan->loan_id = $this->generateImportedLoanId($loanProduct);
        $loan->loan_product_id = $loanProduct->id;
        $loan->borrower_id = $member->id;
        $loan->debit_account_id = $loanAccountId;
        $loan->first_payment_date = $resolvedDateStr;
        $loan->release_date = $resolvedDateStr;
        $loan->currency_id = $loansProduct->currency_id ?: 1;
        $loan->applied_amount = $principal;
        $loan->total_payable = $totalPayable;
        $loan->total_paid = $totalPaid;
        $loan->late_payment_penalties = 0;
        $loan->description = _lang('Imported opening loan from MIKOPO NA RIBA');
        $loan->remarks = _lang('Opening balance') . ': ' . $remaining . ' | ' . _lang('Opening interest') . ': ' . $interest;
        $loan->status = $remaining <= 0 ? 2 : 1;
        $loan->disburse_method = 'account';
        $loan->approved_date = $resolvedDateStr;
        $loan->approved_user_id = auth()->id();
        $loan->created_user_id = auth()->id();
        $loan->branch_id = $member->branch_id ?? (auth()->check() ? auth()->user()->branch_id : null);
        $loan->save();

        return $loan;
    }

    /**
     * Generate unique loan reference for imported loans.
     */
    protected function generateImportedLoanId(LoanProduct $loanProduct): string
    {
        $base = '';

        if ($loanProduct->starting_loan_id !== null) {
            $base = trim((string) ($loanProduct->loan_id_prefix ?? '') . (string) $loanProduct->starting_loan_id);
            $loanProduct->increment('starting_loan_id');
        } else {
            $base = trim((string) ($loanProduct->loan_id_prefix ?? ''));
            if ($base === '') {
                $base = 'IMP-LOAN';
            }
            $base .= '-' . ((int) Loan::withoutGlobalScopes()->max('id') + 1);
        }

        if ($base === '') {
            $base = 'IMP-LOAN-' . ((int) Loan::withoutGlobalScopes()->max('id') + 1);
        }

        $candidate = $base;
        $counter = 1;
        while (Loan::withoutGlobalScopes()->where('loan_id', $candidate)->exists()) {
            $counter++;
            $candidate = $base . '-' . $counter;
        }

        return $candidate;
    }

    /**
     * Parse import date value.
     */
    protected function parseImportDate($value, ?string $fallback = null): ?Carbon
    {
        if ($value === null || $value === '') {
            return $fallback ? Carbon::parse($fallback) : null;
        }

        try {
            if (is_numeric($value)) {
                // Excel serial date handling via PhpSpreadsheet style fallback.
                return Carbon::create(1899, 12, 30)->addDays((int) $value);
            }

            return Carbon::parse((string) $value);
        } catch (\Throwable $e) {
            if ($fallback) {
                try {
                    return Carbon::parse($fallback);
                } catch (\Throwable $e2) {
                    return null;
                }
            }
            return null;
        }
    }

    /**
     * Normalize heading labels.
     */
    protected function normalizeHeader(string $header): string
    {
        $header = mb_strtolower(trim($header));
        $header = preg_replace('/[^a-z0-9]+/', '_', $header);
        return trim((string) $header, '_');
    }

    /**
     * Convert first row headings into index map.
     */
    protected function mapHeaders(array $row): array
    {
        $map = [];
        foreach ($row as $index => $value) {
            $header = $this->normalizeHeader((string) $value);
            if ($header !== '') {
                $map[$header] = $index;
            }
        }
        return $map;
    }

    /**
     * Read row value using one of possible header names.
     */
    protected function getValueByHeaders(array $row, array $headers, array $possibleHeaders)
    {
        foreach ($possibleHeaders as $header) {
            $normalized = $this->normalizeHeader((string) $header);
            if (array_key_exists($normalized, $headers)) {
                $idx = $headers[$normalized];
                return $row[$idx] ?? null;
            }
        }
        return null;
    }

    /**
     * Parse monetary values from template cells.
     */
    protected function parseAmount($value): float
    {
        if ($value === null || $value === '') {
            return 0;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        $raw = trim((string) $value);
        if ($raw === '') {
            return 0;
        }

        $negative = false;
        if (str_starts_with($raw, '(') && str_ends_with($raw, ')')) {
            $negative = true;
            $raw = trim($raw, '()');
        }

        $raw = str_ireplace(['tsh', 'tzs'], '', $raw);
        $raw = str_replace(',', '', $raw);
        $raw = preg_replace('/[^0-9\\.-]/', '', $raw);

        if ($raw === '' || $raw === '-' || $raw === '.') {
            return 0;
        }

        $number = (float) $raw;
        if ($negative) {
            $number = 0 - abs($number);
        }

        return $number;
    }

    /**
     * Parse month name/index into month number.
     */
    protected function parseMonth($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            $month = (int) $value;
            return ($month >= 1 && $month <= 12) ? $month : null;
        }

        $text = mb_strtolower(trim((string) $value));
        if ($text === '') {
            return null;
        }

        if (preg_match('/^[a-z]+/', $text, $matches)) {
            $text = $matches[0];
        }

        return $this->monthNameMap[$text] ?? null;
    }

    /**
     * Parse month-year text (e.g. Jan-25, January 2025, 2025-01) to year/month.
     */
    protected function parseMonthYearLabel(string $label): ?array
    {
        $text = mb_strtolower(trim($label));
        if ($text === '') {
            return null;
        }

        $text = str_replace(['.', '_', '/', '-'], [' ', ' ', ' ', ' '], $text);
        $text = preg_replace('/\s+/', ' ', $text);

        if (preg_match('/^([a-z]+)\s*(\d{2,4})$/', $text, $matches)) {
            $month = $this->monthNameMap[$matches[1]] ?? null;
            if (! $month) {
                return null;
            }

            $year = (int) $matches[2];
            if ($year < 100) {
                $year += 2000;
            }

            return ['year' => $year, 'month' => $month];
        }

        if (preg_match('/^(\d{4})\s*(\d{1,2})$/', $text, $matches)) {
            $year = (int) $matches[1];
            $month = (int) $matches[2];
            if ($month < 1 || $month > 12) {
                return null;
            }
            return ['year' => $year, 'month' => $month];
        }

        if (preg_match('/^(\d{1,2})\s*(\d{4})$/', $text, $matches)) {
            $month = (int) $matches[1];
            $year = (int) $matches[2];
            if ($month < 1 || $month > 12) {
                return null;
            }
            return ['year' => $year, 'month' => $month];
        }

        return null;
    }

    /**
     * Extract month-year columns from a header row.
     */
    protected function getMonthYearColumnsFromHeaderRow(array $headerRow): array
    {
        $columns = [];
        foreach ($headerRow as $index => $header) {
            $parsed = $this->parseMonthYearLabel((string) $header);
            if ($parsed) {
                $columns[$index] = $parsed;
            }
        }
        return $columns;
    }

    /**
     * Extract contributions month-year columns from headers.
     */
    protected function getContributionMonthYearColumnsFromHeaderRow(array $headerRow, array $productKeys): array
    {
        $aliases = [
            'loan_repayments' => 'loan_repayments',
            'loan_repayment' => 'loan_repayments',
            'loans' => 'loan_repayments',
        ];

        foreach ($productKeys as $key) {
            $aliases[$key] = $key;
        }

        uksort($aliases, function ($a, $b) {
            return strlen($b) <=> strlen($a);
        });

        $columns = [];
        foreach ($headerRow as $index => $header) {
            $normalized = $this->normalizeHeader((string) $header);
            if ($normalized === '') {
                continue;
            }

            foreach ($aliases as $prefix => $resolvedType) {
                if (!str_starts_with($normalized, $prefix . '_')) {
                    continue;
                }

                $suffix = substr($normalized, strlen($prefix) + 1);
                $parsed = $this->parseMonthYearLabel(str_replace('_', ' ', $suffix));
                if (! $parsed) {
                    continue;
                }

                $columns[$index] = [
                    'type' => $resolvedType,
                    'year' => $parsed['year'],
                    'month' => $parsed['month'],
                ];
                break;
            }
        }

        return $columns;
    }

    /**
     * Check whether a row is empty.
     */
    protected function isRowEmpty(array $row): bool
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }
        return true;
    }

    /**
     * Full month names used by templates.
     */
    protected function fullMonthNames(): array
    {
        return [
            'january',
            'february',
            'march',
            'april',
            'may',
            'june',
            'july',
            'august',
            'september',
            'october',
            'november',
            'december',
        ];
    }

    /**
     * Default month-year labels for templates (single row per member).
     */
    protected function defaultMonthYearLabels(): array
    {
        $labels = [];
        $cursor = Carbon::create((int) Carbon::now()->year - 2, 1, 1)->startOfMonth();
        $end = Carbon::create((int) Carbon::now()->year, 12, 1)->endOfMonth();

        while ($cursor->lte($end)) {
            $labels[] = $cursor->format('M-y');
            $cursor->addMonth();
        }

        return $labels;
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
     * Loan summary fields displayed before repayment months on the wallet Loans tab.
     */
    protected function getLoanWalletSummaries(): array
    {
        $loans = Loan::with('loan_product')
            ->where('status', '!=', 3)
            ->orderBy('borrower_id')
            ->orderByDesc('id')
            ->get(['id', 'borrower_id', 'loan_product_id', 'applied_amount', 'total_payable', 'total_paid']);

        $out = [];

        foreach ($loans as $loan) {
            $memberId = (int) $loan->borrower_id;
            if ($memberId <= 0) {
                continue;
            }

            if (!isset($out[$memberId])) {
                $out[$memberId] = [
                    'loan_types' => [],
                    'total_loan_amount' => 0,
                    'interest' => 0,
                    'balance' => 0,
                ];
            }

            $loanType = trim((string) optional($loan->loan_product)->name);
            if ($loanType !== '' && !in_array($loanType, $out[$memberId]['loan_types'], true)) {
                $out[$memberId]['loan_types'][] = $loanType;
            }

            $appliedAmount = (float) ($loan->applied_amount ?? 0);
            $totalPayable = (float) ($loan->total_payable ?? 0);
            $totalPaid = (float) ($loan->total_paid ?? 0);

            $out[$memberId]['total_loan_amount'] += $appliedAmount;
            $out[$memberId]['interest'] += max(0, $totalPayable - $appliedAmount);
            $out[$memberId]['balance'] += max(0, $appliedAmount - $totalPaid);
        }

        foreach ($out as $memberId => $summary) {
            $out[$memberId]['loan_type'] = !empty($summary['loan_types'])
                ? implode(', ', $summary['loan_types'])
                : _lang('N/A');
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
