<?php
namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\BankTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator;

class BankAccountController extends Controller {

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
        $bankAccounts = BankAccount::all()->sortByDesc("id");
        return view('backend.admin.bank_account.list', compact('bankAccounts', 'assets'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request) {
        if (! $request->ajax()) {
            return back();
        } else {
            return view('backend.admin.bank_account.modal.create');
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'opening_date'    => 'required',
            'bank_name'       => 'required|max:191',
            'currency_id'     => 'required',
            'account_name'    => 'required|max:100',
            'account_number'  => 'required|max:50',
            'opening_balance' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
            } else {
                return redirect()->route('bank_accounts.create')
                    ->withErrors($validator)
                    ->withInput();
            }
        }

        DB::beginTransaction();

        $bankAccount                   = new BankAccount();
        $bankAccount->opening_date     = $request->input('opening_date');
        $bankAccount->bank_name        = $request->input('bank_name');
        $bankAccount->currency_id = $request->input('currency_id');
        $bankAccount->account_name     = $request->input('account_name');
        $bankAccount->account_number   = $request->input('account_number');
        $bankAccount->opening_balance  = $request->input('opening_balance');
        $bankAccount->description      = $request->input('description');

        $bankAccount->save();

        if ($bankAccount->opening_balance > 0) {
            $banktransaction                  = new BankTransaction();
            $banktransaction->trans_date      = $request->input('opening_date');
            $banktransaction->bank_account_id = $bankAccount->id;
            $banktransaction->amount          = $request->input('opening_balance');
            $banktransaction->type            = 'deposit';
            $banktransaction->dr_cr           = 'cr';
            $banktransaction->description     = 'Opening Balance';
            $banktransaction->created_user_id = auth()->id();

            $banktransaction->save();
        }

        DB::commit();

        if (! $request->ajax()) {
            return redirect()->route('bank_accounts.create')->with('success', _lang('Saved Successfully'));
        } else {
            return response()->json(['result' => 'success', 'action' => 'store', 'message' => _lang('Saved Successfully'), 'data' => $bankAccount, 'table' => '#bank_accounts_table']);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $tenant, $id) {
        $bankAccount = BankAccount::find($id);
        if (! $request->ajax()) {
            return back();
        } else {
            return view('backend.admin.bank_account.modal.view', compact('bankaccount', 'id'));
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request,$tenant, $id) {
        $bankAccount = BankAccount::find($id);
        if (! $request->ajax()) {
            return back();
        } else {
            return view('backend.admin.bank_account.modal.edit', compact('bankaccount', 'id'));
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,$tenant, $id) {
        $validator = Validator::make($request->all(), [
            'bank_name'      => 'required|max:191',
            'account_name'   => 'required|max:100',
            'account_number' => 'required|max:50',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
            } else {
                return redirect()->route('bank_accounts.edit', $id)
                    ->withErrors($validator)
                    ->withInput();
            }
        }

        $bankAccount                 = BankAccount::find($id);
        $bankAccount->bank_name      = $request->input('bank_name');
        $bankAccount->account_name   = $request->input('account_name');
        $bankAccount->account_number = $request->input('account_number');
        $bankAccount->description    = $request->input('description');

        $bankAccount->save();

        if (! $request->ajax()) {
            return redirect()->route('bank_accounts.index')->with('success', _lang('Updated Successfully'));
        } else {
            return response()->json(['result' => 'success', 'action' => 'update', 'message' => _lang('Updated Successfully'), 'data' => $bankAccount, 'table' => '#bank_accounts_table']);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($tenant,$id) {
        $bankAccount = BankAccount::find($id);
        $bankAccount->delete();
        return redirect()->route('bank_accounts.index')->with('success', _lang('Deleted Successfully'));
    }
}