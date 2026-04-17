<?php

namespace App\Http\Controllers;

use App\Models\DepositRequest;
use App\Models\Loan;
use App\Models\LoanPayment;
use App\Models\LoanRepayment;
use App\Models\Transaction;
use App\Notifications\ApprovedDepositRequest;
use App\Notifications\LoanPaymentReceived;
use App\Notifications\RejectDepositRequest;
use App\Utilities\LoanCalculator as Calculator;
use DataTables;
use DB;
use Exception;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DepositRequestController extends Controller {

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        date_default_timezone_set(get_timezone());
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        $assets = ['datatable'];
        return view('backend.admin.deposit_request.list', compact('assets'));
    }

    public function get_table_data(Request $request) {

        $deposit_requests = DepositRequest::select('deposit_requests.*')
            ->with(['member', 'method', 'account.savings_type', 'account.savings_type.currency'])
            ->orderBy("deposit_requests.id", "desc");

        return Datatables::eloquent($deposit_requests)
            ->filter(function ($query) use ($request) {
                $status = $request->has('status') ? $request->status : 1;
                $query->where('status', $status);
            }, true)
            ->editColumn('member.first_name', function ($deposit_request) {
                return $deposit_request->member->first_name . ' ' . $deposit_request->member->last_name;
            })
            ->editColumn('amount', function ($deposit_request) {
                return decimalPlace($deposit_request->amount, currency($deposit_request->account->savings_type->currency->name));
            })
            ->editColumn('status', function ($deposit_request) {
                return transaction_status($deposit_request->status);
            })
            ->filterColumn('member.first_name', function ($query, $keyword) {
                $query->whereHas('member', function ($query) use ($keyword) {
                    return $query->where("first_name", "like", "{$keyword}%")
                        ->orWhere("last_name", "like", "{$keyword}%");
                });
            }, true)
            ->addColumn('action', function ($deposit_request) {
                $actions = '<div class="dropdown text-center">';
                $actions .= '<button class="btn btn-outline-primary btn-xs dropdown-toggle" type="button" id="dropdownMenuButton' . $deposit_request['id'] . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
                $actions .= _lang('Actions');
                $actions .= '</button>';
                $actions .= '<div class="dropdown-menu" aria-labelledby="dropdownMenuButton' . $deposit_request['id'] . '">';
            
                // Details Button
                $actions .= '<a href="' . route('deposit_requests.show', $deposit_request['id']) . '" class="dropdown-item"><i class="fas fa-eye mr-1"></i>' . _lang('Details') . '</a>';
                if (! empty($deposit_request->attachment)) {
                    $actions .= '<a href="' . route('deposit_requests.download_attachment', $deposit_request['id']) . '" class="dropdown-item"><i class="ti-download mr-1"></i>' . _lang('Download Attachment') . '</a>';
                }
            
                // Approve Button (if status is not 2)
                if ($deposit_request->status != 2) {
                    $actions .= '<a href="' . route('deposit_requests.approve', $deposit_request['id']) . '" class="dropdown-item"><i class="fas fa-check-circle text-success mr-1"></i>' . _lang('Approve') . ' ' . _lang('this') . '</a>';
                }
            
                // Approve group (if this request is in a group and group has pending)
                if ($deposit_request->deposit_request_group_id) {
                    $pendingInGroup = DepositRequest::where('deposit_request_group_id', $deposit_request->deposit_request_group_id)->where('status', '!=', 2)->count();
                    if ($pendingInGroup > 0) {
                        $actions .= '<a href="' . route('deposit_requests.approve_group', $deposit_request->deposit_request_group_id) . '" class="dropdown-item"><i class="fas fa-check-double text-success mr-1"></i>' . _lang('Approve all in group') . '</a>';
                    }
                }
            
                // Reject Button (if status is not 1)
                if ($deposit_request->status != 1) {
                    $actions .= '<a href="' . route('deposit_requests.reject', $deposit_request['id']) . '" class="dropdown-item"><i class="fas fa-times-circle text-danger mr-1"></i>' . _lang('Reject') . '</a>';
                }
            
                // Divider and Delete Button
                $actions .= '<div class="dropdown-divider"></div>';
                $actions .= '<form action="' . route('deposit_requests.destroy', $deposit_request['id']) . '" method="post" class="d-inline">';
                $actions .= csrf_field();
                $actions .= '<input name="_method" type="hidden" value="DELETE">';
                $actions .= '<button class="dropdown-item text-danger btn-remove" type="submit"><i class="fas fa-trash-alt mr-1"></i>' . _lang('Delete') . '</button>';
                $actions .= '</form>';
            
                $actions .= '</div>';
                $actions .= '</div>';
            
                return $actions;
            })
            ->setRowId(function ($deposit_request) {
                return "row_" . $deposit_request->id;
            })
            ->rawColumns(['user.name', 'status', 'action'])
            ->make(true);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $tenant, $id) {
        $depositrequest = DepositRequest::find($id);
        return view('backend.admin.deposit_request.view', compact('depositrequest', 'id'));
    }

    /**
     * Download deposit request attachment.
     *
     * @param  string  $tenant
     * @param  int  $id
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\RedirectResponse
     */
    public function downloadAttachment($tenant, $id): BinaryFileResponse|\Illuminate\Http\RedirectResponse {
        $depositRequest = DepositRequest::find($id);
        if (! $depositRequest || empty($depositRequest->attachment)) {
            return redirect()->route('deposit_requests.index')->with('error', _lang('Attachment not found'));
        }
        $path = public_path('uploads/media/' . $depositRequest->attachment);
        if (! is_file($path)) {
            return redirect()->route('deposit_requests.index')->with('error', _lang('File not found'));
        }
        return response()->download($path, $depositRequest->attachment);
    }

    /**
     * Approve Wire Transfer
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function approve($tenant, $id) {
        $depositRequest = DepositRequest::find($id);
        if (! $depositRequest || $depositRequest->status == 2) {
            return redirect()->route('deposit_requests.index')->with('error', _lang('Request not found or already approved'));
        }
        DB::beginTransaction();
        $this->performApproval($depositRequest);
        DB::commit();
        return redirect()->route('deposit_requests.index')->with('success', _lang('Request Approved'));
    }

    /**
     * Approve all deposit requests in the same group (one submission).
     *
     * @param  string  $groupId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function approveGroup($tenant, $groupId) {
        $requests = DepositRequest::where('deposit_request_group_id', $groupId)->where('status', '!=', 2)->get();
        if ($requests->isEmpty()) {
            return redirect()->route('deposit_requests.index')->with('info', _lang('No pending requests in this group'));
        }
        DB::beginTransaction();
        foreach ($requests as $depositRequest) {
            $this->performApproval($depositRequest);
        }
        DB::commit();
        return redirect()->route('deposit_requests.index')->with('success', _lang('All requests in group approved'));
    }

    /**
     * Perform approval for one deposit request (create transaction, update status).
     * If credit account is loans/mkopo/mikopo, apply deposited amount to member's loans (first loan first, then second, etc.) without exceeding deposit.
     *
     * @param  DepositRequest  $depositRequest
     * @return void
     */
    protected function performApproval(DepositRequest $depositRequest) {
        $transaction                     = new Transaction();
        $transaction->trans_date         = now();
        $transaction->member_id          = $depositRequest->member_id;
        $transaction->savings_account_id = $depositRequest->credit_account_id;
        $transaction->charge             = convert_currency($depositRequest->method->currency->name, $depositRequest->account->savings_type->currency->name, $depositRequest->charge);
        $transaction->amount             = $depositRequest->amount;
        $transaction->dr_cr              = 'cr';
        $transaction->type               = 'Deposit';
        $transaction->method             = $depositRequest->method->name;
        $transaction->status             = 2;
        $transaction->description        = _lang('Deposit Via') . ' ' . $depositRequest->method->name;
        $transaction->created_user_id    = auth()->id();
        $transaction->branch_id          = auth()->user()->branch_id;
        $transaction->save();

        $depositRequest->status         = 2;
        $depositRequest->transaction_id = $transaction->id;
        $depositRequest->save();

        // If deposit is to loans/mkopo/mikopo account, apply payment to member's loans (first loan first, then second; do not exceed deposited amount)
        $accountTypeName = strtolower(trim($depositRequest->account->savings_type->name ?? ''));
        if (in_array($accountTypeName, ['loans', 'mkopo', 'mikopo'])) {
            $this->applyLoanPaymentsFromDeposit($depositRequest, $depositRequest->credit_account_id);
        }

        $transaction->load(['member', 'account.savings_type.currency']);
        try {
            $transaction->member->notify(new ApprovedDepositRequest($transaction));
        } catch (Exception $e) {
            \Log::error('Failed to send deposit approval notification: ' . $e->getMessage());
        }
    }

    /**
     * Apply deposited amount to member's loans: pay first loan (clear it if enough), then second, etc. Total applied does not exceed deposit amount.
     * Uses same logic as portal/loans/payment (Transaction debit, LoanPayment, update Loan/LoanRepayment/schedule).
     *
     * @param  DepositRequest  $depositRequest
     * @param  int  $loanAccountId  Savings account (loans/mkopo) to debit from (already credited by deposit)
     * @return void
     */
    protected function applyLoanPaymentsFromDeposit(DepositRequest $depositRequest, int $loanAccountId): void {
        $memberId     = $depositRequest->member_id;
        $depositAmount = (float) $depositRequest->amount;
        $remaining     = $depositAmount;
        $accountCurrencyId = $depositRequest->account->savings_type->currency_id ?? null;

        $loansQuery = Loan::withoutGlobalScopes()
            ->where('borrower_id', $memberId)
            ->where('status', 1)
            ->whereRaw('total_paid < applied_amount')
            ->with(['loan_product', 'borrower', 'currency'])
            ->orderBy('id');
        if ($accountCurrencyId) {
            $loansQuery->where('currency_id', $accountCurrencyId);
        }
        $loans = $loansQuery->get();

        foreach ($loans as $loan) {
            if ($remaining <= 0) {
                break;
            }
            while ($remaining > 0) {
                $repayment = LoanRepayment::withoutGlobalScopes()
                    ->where('loan_id', $loan->id)
                    ->where('status', 0)
                    ->orderBy('id')
                    ->first();
                if (! $repayment) {
                    break;
                }
                $amountUsed = $this->applyOneLoanRepayment($loan, $repayment, $loanAccountId, $remaining);
                if ($amountUsed <= 0) {
                    break;
                }
                $remaining -= $amountUsed;
                $loan->refresh();
            }
        }
    }

    /**
     * Apply one loan repayment (same logic as Customer\LoanController::loan_payment). Returns amount debited; 0 if not applied (e.g. insufficient remaining).
     *
     * @param  Loan  $loan
     * @param  LoanRepayment  $repayment
     * @param  int  $sourceAccountId
     * @param  float  $maxAmount  Do not debit more than this (deposit remaining)
     * @return float  Amount debited
     */
    protected function applyOneLoanRepayment(Loan $loan, LoanRepayment $repayment, int $sourceAccountId, float $maxAmount): float {
        $today        = \Carbon\Carbon::today();
        $repaymentDate = \Carbon\Carbon::parse($repayment->getRawOriginal('repayment_date'));
        $overdueDays  = $today->gt($repaymentDate) ? $repaymentDate->diffInDays($today) : 0;
        $penaltyPerDay = (float) ($repayment->penalty ?? 0);
        $penalty       = max(0, $overdueDays * $penaltyPerDay);
        $principalAmount = (float) $repayment->principal_amount;
        $interest        = (float) $repayment->interest;
        $amountNeeded    = $principalAmount + $interest + $penalty;

        if ($maxAmount < $amountNeeded) {
            return 0;
        }
        if (get_account_balance($sourceAccountId, $loan->borrower_id) < $amountNeeded) {
            return 0;
        }

        $existingPrincipal = (float) $repayment->principal_amount;

        $debit = new Transaction();
        $debit->trans_date         = now();
        $debit->member_id          = $loan->borrower_id;
        $debit->savings_account_id = $sourceAccountId;
        $debit->amount             = $amountNeeded;
        $debit->dr_cr              = 'dr';
        $debit->type               = 'Loan_Repayment';
        $debit->method             = 'Deposit Approval';
        $debit->status             = 2;
        $debit->note               = _lang('Loan Repayment');
        $debit->description        = _lang('Loan Repayment');
        $debit->created_user_id    = auth()->id();
        $debit->branch_id          = $loan->borrower->branch_id;
        $debit->loan_id            = $loan->id;
        $debit->save();

        $loanpayment = new LoanPayment();
        $loanpayment->loan_id          = $loan->id;
        $loanpayment->paid_at          = date('Y-m-d');
        $loanpayment->late_penalties   = $penalty;
        $loanpayment->interest         = $interest;
        $loanpayment->repayment_amount = $principalAmount + $interest;
        $loanpayment->total_amount    = $loanpayment->repayment_amount + $penalty;
        $loanpayment->remarks          = _lang('Deposit approval');
        $loanpayment->transaction_id   = $debit->id;
        $loanpayment->repayment_id     = $repayment->id;
        $loanpayment->member_id        = $loan->borrower_id;
        $loanpayment->save();

        $loan->total_paid = $loan->total_paid + $principalAmount;
        if ($loan->total_paid >= $loan->applied_amount) {
            $loan->status = 2;
        }
        $loan->save();

        $repayment->principal_amount = $principalAmount;
        $repayment->amount_to_pay    = $principalAmount + $interest;
        $repayment->balance          = $loan->applied_amount - $loan->total_paid;
        $repayment->status           = 1;
        $repayment->save();

        if ($loan->total_paid >= $loan->applied_amount) {
            LoanRepayment::withoutGlobalScopes()->where('loan_id', $loan->id)->where('status', 0)->delete();
        } else {
            if ($repayment->getRawOriginal('principal_amount') != $existingPrincipal) {
                $upcomingRepayments = LoanRepayment::withoutGlobalScopes()
                    ->where('loan_id', $loan->id)
                    ->where('status', 0)
                    ->orderBy('id')
                    ->get();
                if ($upcomingRepayments->isNotEmpty()) {
                    $interestType = $loan->loan_product->interest_type ?? 'flat_rate';
                    $calculator   = new Calculator(
                        $loan->applied_amount - $loan->total_paid,
                        $upcomingRepayments[0]->getRawOriginal('repayment_date'),
                        $loan->loan_product->interest_rate ?? 0,
                        $upcomingRepayments->count(),
                        $loan->loan_product->term_period ?? '+1 month',
                        $loan->late_payment_penalties ?? 0,
                        $loan->applied_amount
                    );
                    if ($interestType == 'flat_rate') {
                        $newRepayments = $calculator->get_flat_rate();
                    } elseif ($interestType == 'fixed_rate') {
                        $newRepayments = $calculator->get_fixed_rate();
                    } elseif ($interestType == 'mortgage') {
                        $newRepayments = $calculator->get_mortgage();
                    } elseif ($interestType == 'one_time') {
                        $newRepayments = $calculator->get_one_time();
                    } elseif ($interestType == 'reducing_amount') {
                        $newRepayments = $calculator->get_reducing_amount();
                    } else {
                        $newRepayments = $calculator->get_flat_rate();
                    }
                    $index = 0;
                    foreach ($newRepayments as $newRepayment) {
                        if (! isset($upcomingRepayments[$index])) {
                            break;
                        }
                        $up = $upcomingRepayments[$index];
                        $up->amount_to_pay    = $newRepayment['amount_to_pay'];
                        $up->penalty          = $newRepayment['penalty'];
                        $up->principal_amount = $newRepayment['principal_amount'];
                        $up->interest         = $newRepayment['interest'];
                        $up->balance          = $newRepayment['balance'];
                        $up->save();
                        $index++;
                    }
                }
            }
        }

        try {
            $loanpayment->load('member');
            $loanpayment->member->notify(new LoanPaymentReceived($loanpayment));
        } catch (Exception $e) {
            // ignore
        }

        return $amountNeeded;
    }

    /**
     * Reject Wire Transfer
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function reject($tenant, $id) {
        DB::beginTransaction();
        $depositRequest = DepositRequest::find($id);

        if ($depositRequest->transaction_id != null) {
            $transaction = Transaction::find($depositRequest->transaction_id);
            $transaction->delete();
        }

        $depositRequest->status         = 1;
        $depositRequest->transaction_id = null;
        $depositRequest->save();

        DB::commit();

        try {
            $depositRequest->member->notify(new RejectDepositRequest($depositRequest));
        } catch (\Exception $e) {}

        return redirect()->route('deposit_requests.index')->with('success', _lang('Request Rejected'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($tenant, $id) {
        $depositRequest = DepositRequest::find($id);
        if ($depositRequest->transaction_id != null) {
            $transaction = Transaction::find($depositRequest->transaction_id);
            if($transaction){
                $transaction->delete();
            } 
        }
        $depositRequest->delete();
        return redirect()->route('deposit_requests.index')->with('success', _lang('Deleted Successfully'));
    }
}