<?php
namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\AutomaticGateway;
use App\Models\DepositMethod;
use App\Models\DepositRequest;
use App\Models\SavingsAccount;
use App\Models\Transaction;
use App\Notifications\NewDepositRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DepositController extends Controller {

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
    public function manual_methods() {
        $alert_col       = 'col-lg-8 offset-lg-2';
        $deposit_methods = DepositMethod::where('status', 1)->get();
        return view('backend.customer.deposit.manual_methods', compact('deposit_methods', 'alert_col'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function automatic_methods() {
        $alert_col       = 'col-lg-8 offset-lg-2';
        // Get tenant ID safely
        $tenantId = null;
        if (app()->bound('tenant')) {
            $tenantId = app('tenant')->id;
        } elseif (isset(request()->tenant) && request()->tenant) {
            $tenantId = request()->tenant->id;
        }
        
        $deposit_methods = AutomaticGateway::active()
            ->where('tenant_id', $tenantId)
            ->get();
        return view('backend.customer.deposit.automatic_methods', compact('deposit_methods', 'alert_col'));
    }

    public function manual_deposit(Request $request, $teant, $methodId) {
        if ($request->isMethod('get')) {
            $alert_col = 'col-lg-8 offset-lg-2';
            $accounts  = SavingsAccount::with('savings_type')
                ->where('member_id', auth()->user()->member->id)
                ->get();
            $deposit_method = DepositMethod::find($methodId);
            return view('backend.customer.deposit.manual_deposit', compact('deposit_method', 'accounts', 'alert_col'));
        } else if ($request->isMethod('post')) {
            $deposit_method = DepositMethod::find($methodId);
            $isBatch        = $request->has('rows') && is_array($request->rows) && count($request->rows) > 0;

            if ($isBatch) {
                return $this->manual_deposit_batch($request, $methodId, $deposit_method);
            }

            // Single-row submission (existing flow)
            $account = SavingsAccount::where('id', $request->credit_account)
                ->where('member_id', auth()->user()->member->id)
                ->first();
            if (! $account) {
                if ($request->ajax()) {
                    return response()->json(['result' => 'error', 'message' => [_lang('Invalid account')]]);
                }
                return back()->with('error', _lang('Invalid account'))->withInput();
            }
            $accountType = $account->savings_type;

            $validator = Validator::make($request->all(), [
                'requirements.*' => 'required',
                'credit_account' => 'required',
                'amount'         => "required|numeric",
                'user_transaction_id' => 'required|string|max:255',
                'user_reference'      => 'required|string|max:255',
                'attachment'     => 'nullable|mimes:jpeg,JPEG,png,PNG,jpg,doc,pdf,docx|max:4096',
            ]);

            if ($validator->fails()) {
                if ($request->ajax()) {
                    return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
                } else {
                    return back()
                        ->withErrors($validator)
                        ->withInput();
                }
            }

            //Convert account currency to gateway currency
            $convertedAdmount = convert_currency($accountType->currency->name, $deposit_method->currency->name, $request->amount);

            $chargeLimit = $deposit_method->chargeLimits()->where('minimum_amount', '<=', $convertedAdmount)->where('maximum_amount', '>=', $convertedAdmount)->first();

            if ($chargeLimit) {
                $fixedCharge      = $chargeLimit->fixed_charge;
                $percentageCharge = ($convertedAdmount * $chargeLimit->charge_in_percentage) / 100;
                $charge           = $fixedCharge + $percentageCharge;
            } else {
                //Convert minimum amount to selected currency
                $minimumAmount = convert_currency($deposit_method->currency->name, $accountType->currency->name, $deposit_method->chargeLimits()->min('minimum_amount'));
                $maximumAmount = convert_currency($deposit_method->currency->name, $accountType->currency->name, $deposit_method->chargeLimits()->max('maximum_amount'));
                return back()->with('error', _lang('Deposit limit') . ' ' . $minimumAmount . ' ' . $accountType->currency->name . ' -- ' . $maximumAmount . ' ' . $accountType->currency->name)->withInput();
            }

            $attachment = $this->upload_deposit_attachment($request);

            $depositRequest                    = new DepositRequest();
            $depositRequest->member_id         = auth()->user()->member->id;
            $depositRequest->method_id         = $methodId;
            $depositRequest->credit_account_id = $request->credit_account;
            $depositRequest->amount            = $request->amount;
            $depositRequest->converted_amount  = $convertedAdmount + $charge;
            $depositRequest->charge            = $charge;
            $depositRequest->description       = $request->description;
            $depositRequest->requirements      = json_encode($request->requirements ?? []);
            $depositRequest->attachment        = $attachment;
            $depositRequest->user_transaction_id = $request->user_transaction_id;
            $depositRequest->user_reference     = $request->user_reference;
            $depositRequest->save();

            // Reload deposit request with relationships for email notification
            $depositRequest->load(['member', 'method.currency', 'account.savings_type.currency']);

            // Send email notification to member
            try {
                $depositRequest->member->notify(new NewDepositRequest($depositRequest));
            } catch (\Exception $e) {
                \Log::error('Failed to send deposit request notification: ' . $e->getMessage());
            }

            if (! $request->ajax()) {
                return redirect()->route('deposit.manual_methods')->with('success', _lang('Deposit Request submited successfully'));
            } else {
                return response()->json(['result' => 'success', 'action' => 'store', 'message' => _lang('Deposit Request submited successfully'), 'data' => $depositRequest, 'table' => '#unknown_table']);
            }
        }
    }

    /**
     * Batch manual deposit: multiple account rows, one attachment, one Transaction ID / Reference.
     */
    protected function manual_deposit_batch(Request $request, $methodId, $deposit_method) {
        $validator = Validator::make($request->all(), [
            'rows'                  => 'required|array',
            'rows.*.credit_account' => 'required|exists:savings_accounts,id',
            'rows.*.amount'        => 'required|numeric|min:0.01',
            'user_transaction_id'   => 'required|string|max:255',
            'user_reference'        => 'required|string|max:255',
            'requirements.*'       => 'nullable',
            'attachment'           => 'nullable|mimes:jpeg,JPEG,png,PNG,jpg,doc,pdf,docx|max:4096',
        ]);

        if ($validator->fails()) {
            return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
        }

        $memberId = auth()->user()->member->id;
        $rows     = $request->rows;
        $accounts = SavingsAccount::with('savings_type')
            ->whereIn('id', array_column($rows, 'credit_account'))
            ->where('member_id', $memberId)
            ->get()
            ->keyBy('id');

        foreach ($rows as $row) {
            $acc = $accounts->get($row['credit_account'] ?? null);
            if (! $acc) {
                return response()->json(['result' => 'error', 'message' => [_lang('Invalid account in rows')]]);
            }
        }

        $attachment = $this->upload_deposit_attachment($request);
        $groupId    = \Illuminate\Support\Str::uuid()->toString();
        $description = $request->description ?? '';
        $requirements = json_encode($request->requirements ?? []);

        $created = [];
        foreach ($rows as $row) {
            $account     = $accounts->get($row['credit_account']);
            $accountType = $account->savings_type;
            $amount      = (float) $row['amount'];

            $convertedAmount = convert_currency($accountType->currency->name, $deposit_method->currency->name, $amount);
            $chargeLimit     = $deposit_method->chargeLimits()->where('minimum_amount', '<=', $convertedAmount)->where('maximum_amount', '>=', $convertedAmount)->first();

            if ($chargeLimit) {
                $fixedCharge      = $chargeLimit->fixed_charge;
                $percentageCharge = ($convertedAmount * $chargeLimit->charge_in_percentage) / 100;
                $charge           = $fixedCharge + $percentageCharge;
            } else {
                $minimumAmount = convert_currency($deposit_method->currency->name, $accountType->currency->name, $deposit_method->chargeLimits()->min('minimum_amount'));
                $maximumAmount = convert_currency($deposit_method->currency->name, $accountType->currency->name, $deposit_method->chargeLimits()->max('maximum_amount'));
                return response()->json(['result' => 'error', 'message' => [_lang('Deposit limit') . ' ' . $minimumAmount . ' - ' . $maximumAmount . ' ' . $accountType->currency->name]]);
            }

            $dr = new DepositRequest();
            $dr->member_id              = $memberId;
            $dr->method_id              = $methodId;
            $dr->credit_account_id      = $row['credit_account'];
            $dr->amount                 = $amount;
            $dr->converted_amount       = $convertedAmount + $charge;
            $dr->charge                 = $charge;
            $dr->description            = $description;
            $dr->requirements           = $requirements;
            $dr->attachment             = $attachment;
            $dr->user_transaction_id    = $request->user_transaction_id;
            $dr->user_reference         = $request->user_reference;
            $dr->deposit_request_group_id = $groupId;
            $dr->save();
            $created[] = $dr;
        }

        $first = $created[0];
        $first->load(['member', 'method.currency', 'account.savings_type.currency']);
        try {
            $first->member->notify(new NewDepositRequest($first));
        } catch (\Exception $e) {
            \Log::error('Failed to send deposit request notification: ' . $e->getMessage());
        }

        return response()->json([
            'result'  => 'success',
            'action'  => 'store',
            'message' => _lang('Deposit Request submited successfully'),
            'data'    => $first,
            'table'   => '#unknown_table',
        ]);
    }

    /**
     * Upload attachment for manual deposit; returns filename or empty string.
     */
    protected function upload_deposit_attachment(Request $request) {
        $attachment = '';
        if ($request->hasFile('attachment')) {
            $file       = $request->file('attachment');
            $attachment = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientOriginalName());
            $file->move(public_path('uploads/media'), $attachment);
        }
        return $attachment;
    }

    public function automatic_deposit(Request $request, $teant, $methodId) {
        if ($request->isMethod('get')) {
            if ($request->ajax()) {
                $accounts = SavingsAccount::with('savings_type')
                    ->where('member_id', auth()->user()->member->id)
                    ->get();
                $deposit_method = AutomaticGateway::where('id', $methodId)
                    ->active()
                    ->where('tenant_id', $request->tenant->id)
                    ->first();

                if ($deposit_method->is_crypto == 0) {
                    return view('backend.customer.deposit.modal.automatic_deposit', compact('deposit_method', 'accounts'));
                } else {
                    return view('backend.customer.deposit.modal.crypto_deposit', compact('deposit_method', 'accounts'));
                }
            }
            return redirect()->route('deposit.automatic_methods');
        } else if ($request->isMethod('post')) {
            $deposit_method = AutomaticGateway::where('id', $methodId)->where('status', 1)->first();

            $validator = Validator::make($request->all(), [
                'credit_account' => 'required',
                'amount'         => "required|numeric",
            ]);

            if ($validator->fails()) {
                if ($request->ajax()) {
                    return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
                } else {
                    return redirect()->route('deposit.automatic_methods')
                        ->withErrors($validator)
                        ->withInput();
                }
            }

            $member_id = auth()->user()->member->id;
            $account   = SavingsAccount::where('id', $request->credit_account)
                ->where('member_id', $member_id)
                ->first();

            $baseAmount    = convert_currency($account->savings_type->currency->name, get_base_currency(), $request->amount); //Convert account currency to base currency
            $gatewayAmount = convert_currency_2(1, $deposit_method->exchange_rate, $baseAmount);                              //Convert Base currency to gateway currency

            $chargeLimit = $deposit_method->chargeLimits()->where('minimum_amount', '<=', $gatewayAmount)->where('maximum_amount', '>=', $gatewayAmount)->first();

            if ($chargeLimit) {
                $fixedCharge      = $chargeLimit->fixed_charge;
                $percentageCharge = ($gatewayAmount * $chargeLimit->charge_in_percentage) / 100;
                $charge           = $fixedCharge + $percentageCharge;
                $gatewayAmount    = $gatewayAmount + $charge; //Final Amount
            } else {
                //Convert minimum amount to selected currency
                $minimumAmount = $deposit_method->chargeLimits()->min('minimum_amount');
                $maximumAmount = $deposit_method->chargeLimits()->max('maximum_amount');

                $currencyName = $deposit_method->is_crypto == 1 ? get_base_currency() : $deposit_method->currency;

                if ($gatewayAmount < $minimumAmount) {
                    return redirect()->route('deposit.automatic_methods')
                        ->with('error', _lang('The amount must be at least') . ' ' . $minimumAmount . ' ' . $currencyName)
                        ->withInput();
                }

                if ($gatewayAmount > $maximumAmount) {
                    return redirect()->route('deposit.automatic_methods')
                        ->with('error', _lang('The amount may not be greater than') . ' ' . $maximumAmount . ' ' . $currencyName)
                        ->withInput();
                }
            }

            $deposit = Transaction::where('member_id', $member_id)
                ->where('savings_account_id', $request->credit_account)
                ->where('type', 'Deposit')
                ->where('amount', $request->amount)
                ->where('method', $deposit_method->slug)
                ->where('status', 0)
                ->first();

            if (! $deposit) {
                $deposit                     = new Transaction();
                $deposit->trans_date         = now();
                $deposit->member_id          = $member_id;
                $deposit->savings_account_id = $request->credit_account;
                $deposit->charge             = convert_currency_2($deposit_method->exchange_rate, $deposit->account->savings_type->currency->exchange_rate, $charge);
                $deposit->amount             = $request->amount;
                $deposit->gateway_amount     = $gatewayAmount;
                $deposit->dr_cr              = 'cr';
                $deposit->type               = 'Deposit';
                $deposit->method             = $deposit_method->slug;
                $deposit->status             = 0;
                $deposit->description        = _lang('Deposit via') . ' ' . $deposit_method->name;
                $deposit->gateway_id         = $deposit_method->id;
                $deposit->created_user_id    = auth()->id();
                $deposit->branch_id          = auth()->user()->branch_id;

                $deposit->save();
            }

            //Process Via Payment Gateway
            $gateway = '\App\Http\Controllers\Gateway\\' . $deposit_method->slug . '\\ProcessController';

            $data = $gateway::process($deposit);
            $data = json_decode($data);

            if (isset($data->redirect)) {
                return redirect($data->redirect_url);
            }

            if (isset($data->error)) {
                $deposit->delete();
                return redirect()->route('deposit.automatic_methods')
                    ->with('error', $data->error_message);
            }

            $alert_col = 'col-lg-6 offset-lg-3';
            return view($data->view, compact('data', 'deposit', 'gatewayAmount', 'charge', 'alert_col'));
        }

    }

}