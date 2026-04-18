<?php

namespace App\Http\Controllers;

use App\Models\LoanCollectionFollowUp;
use App\Models\LoanRepayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class LoanCollectionFollowUpController extends Controller
{
    public function __construct()
    {
        date_default_timezone_set(get_timezone());
    }

    public function create(Request $request, $tenant, $repaymentId)
    {
        if (! Schema::hasTable('loan_collection_follow_ups')) {
            return response('<div class="alert alert-warning mb-0">' . _lang('Collection follow-up tracking is available after running the latest database migration.') . '</div>');
        }

        $repayment = LoanRepayment::with([
            'loan.borrower.branch',
            'loan.currency',
            'followUps.createdBy',
        ])->findOrFail($repaymentId);

        if (! $request->ajax()) {
            return back();
        }

        $recentFollowUps = $repayment->followUps->sortByDesc('id')->take(8)->values();

        return view('backend.admin.loan_collection_follow_up.modal.create', compact('repayment', 'recentFollowUps'));
    }

    public function store(Request $request, $tenant)
    {
        if (! Schema::hasTable('loan_collection_follow_ups')) {
            return response()->json(['result' => 'error', 'message' => _lang('Collection follow-up tracking is not yet available until the latest migration is applied.')]);
        }

        $validator = Validator::make($request->all(), [
            'loan_repayment_id' => 'required|exists:loan_repayments,id',
            'outcome' => 'required|in:1,2,3,4,5,6',
            'note' => 'required|string|max:2000',
            'next_action_date' => 'nullable|date',
            'promised_payment_date' => 'nullable|date',
        ]);

        $validator->after(function ($validator) use ($request) {
            if ((int) $request->outcome === LoanCollectionFollowUp::OUTCOME_PROMISED_TO_PAY && empty($request->promised_payment_date)) {
                $validator->errors()->add('promised_payment_date', _lang('Promised payment date is required for promised-to-pay follow-up.'));
            }
        });

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
            }

            return back()->withErrors($validator)->withInput();
        }

        $repayment = LoanRepayment::with('loan.borrower')->findOrFail($request->loan_repayment_id);

        $followUp = new LoanCollectionFollowUp();
        $followUp->loan_repayment_id = $repayment->id;
        $followUp->loan_id = $repayment->loan_id;
        $followUp->member_id = optional($repayment->loan)->borrower_id;
        $followUp->outcome = $request->outcome;
        $followUp->note = $request->note;
        $followUp->next_action_date = $request->next_action_date;
        $followUp->promised_payment_date = $request->promised_payment_date;
        $followUp->created_user_id = auth()->id();
        $followUp->save();

        if (! $request->ajax()) {
            return back()->with('success', _lang('Collection follow-up logged successfully'));
        }

        return response()->json([
            'result' => 'success',
            'action' => 'store',
            'message' => _lang('Collection follow-up logged successfully'),
            'data' => $followUp,
        ]);
    }
}
