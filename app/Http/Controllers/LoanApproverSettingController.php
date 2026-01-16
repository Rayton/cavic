<?php

namespace App\Http\Controllers;

use App\Models\LoanApproverSetting;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LoanApproverSettingController extends Controller
{
    public function __construct()
    {
        date_default_timezone_set(get_timezone());
    }

    /**
     * Display a listing of approver settings
     */
    public function index(Request $request, $tenant)
    {
        $assets = ['datatable'];
        $settings = LoanApproverSetting::with('approver')
            ->orderBy('approval_level', 'asc')
            ->get();

        // Ensure all 4 levels exist
        $levels = LoanApproverSetting::getApprovalLevels();
        foreach ($levels as $level => $levelName) {
            $exists = $settings->where('approval_level', $level)->first();
            if (!$exists) {
                $newSetting = new LoanApproverSetting();
                $newSetting->approval_level = $level;
                $newSetting->approval_level_name = $levelName;
                $newSetting->status = 0;
                $newSetting->tenant_id = request()->tenant->id;
                $newSetting->save();
                $settings->push($newSetting);
            }
        }

        return view('backend.admin.loan.approver_settings', compact('settings', 'assets'));
    }

    /**
     * Show the form for creating/editing approver setting
     */
    public function create(Request $request, $tenant, $level = null)
    {
        if ($request->isMethod('post') && $level) {
            $setting = LoanApproverSetting::where('approval_level', $level)
                ->where('tenant_id', request()->tenant->id)
                ->first();

            if (!$setting) {
                $levels = LoanApproverSetting::getApprovalLevels();
                $setting = new LoanApproverSetting();
                $setting->approval_level = $level;
                $setting->approval_level_name = $levels[$level] ?? _lang('Level') . ' ' . $level;
                $setting->tenant_id = request()->tenant->id;
            }

            $members = Member::orderBy('first_name', 'asc')->get();
            return view('backend.admin.loan.modal.approver_setting', compact('setting', 'members', 'level'));
        }

        return back();
    }

    /**
     * Store a newly created approver setting
     */
    public function store(Request $request, $tenant)
    {
        $validator = Validator::make($request->all(), [
            'approval_level' => 'required|integer|in:1,2,3,4',
            'approver_member_id' => 'required|exists:members,id',
            'status' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
            } else {
                return back()->withErrors($validator)->withInput();
            }
        }

        $levels = LoanApproverSetting::getApprovalLevels();
        $setting = LoanApproverSetting::where('approval_level', $request->approval_level)
            ->where('tenant_id', request()->tenant->id)
            ->first();

        if (!$setting) {
            $setting = new LoanApproverSetting();
            $setting->approval_level = $request->approval_level;
            $setting->approval_level_name = $levels[$request->approval_level] ?? _lang('Level') . ' ' . $request->approval_level;
            $setting->tenant_id = request()->tenant->id;
        }

        $setting->approver_member_id = $request->approver_member_id;
        $setting->status = $request->status;
        $setting->save();

        if ($request->ajax()) {
            return response()->json(['result' => 'success', 'action' => 'store', 'message' => _lang('Saved Successfully'), 'data' => $setting]);
        } else {
            return redirect()->route('loan_approver_settings.index', $tenant)->with('success', _lang('Saved Successfully'));
        }
    }

    /**
     * Show the form for editing approver setting
     */
    public function edit(Request $request, $tenant, $id)
    {
        $setting = LoanApproverSetting::findOrFail($id);
        $members = Member::orderBy('first_name', 'asc')->get();
        $level = $setting->approval_level;

        if ($request->ajax()) {
            return view('backend.admin.loan.modal.approver_setting', compact('setting', 'members', 'level'));
        } else {
            return view('backend.admin.loan.approver_setting_edit', compact('setting', 'members', 'level'));
        }
    }

    /**
     * Update approver setting
     */
    public function update(Request $request, $tenant, $id)
    {
        $validator = Validator::make($request->all(), [
            'approver_member_id' => 'required|exists:members,id',
            'status' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
            } else {
                return back()->withErrors($validator)->withInput();
            }
        }

        $setting = LoanApproverSetting::findOrFail($id);
        $setting->approver_member_id = $request->approver_member_id;
        $setting->status = $request->status;
        $setting->save();

        if ($request->ajax()) {
            return response()->json(['result' => 'success', 'action' => 'update', 'message' => _lang('Updated Successfully'), 'data' => $setting]);
        } else {
            return redirect()->route('loan_approver_settings.index', $tenant)->with('success', _lang('Updated Successfully'));
        }
    }

    /**
     * Remove approver setting
     */
    public function destroy($tenant, $id)
    {
        $setting = LoanApproverSetting::findOrFail($id);
        $setting->approver_member_id = null;
        $setting->status = 0;
        $setting->save();

        return redirect()->route('loan_approver_settings.index', $tenant)->with('success', _lang('Removed Successfully'));
    }
}
