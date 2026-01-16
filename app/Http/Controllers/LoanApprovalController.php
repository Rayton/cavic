<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\LoanApproval;
use App\Models\Member;
use App\Notifications\LoanApprovalLevelNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoanApprovalController extends Controller
{
    public function __construct()
    {
        date_default_timezone_set(get_timezone());
    }

    /**
     * Display list of loans pending approval for current approver
     */
    public function index(Request $request, $tenant)
    {
        $assets = ['datatable'];
        
        // Get current user's member ID
        $currentMemberId = $this->getCurrentMemberId();
        
        if (!$currentMemberId) {
            return back()->with('error', _lang('You must be a member to approve loans'));
        }

        // Get pending approvals for this approver
        $pendingApprovals = LoanApproval::where('approver_member_id', $currentMemberId)
            ->where('status', LoanApproval::STATUS_PENDING)
            ->with(['loan.borrower', 'loan.currency', 'loan.loan_product', 'loan.approvals'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->filter(function ($approval) {
                // Only show if all previous levels are approved
                return $this->arePreviousLevelsApproved($approval);
            });

        return view('backend.admin.loan.approval_list', compact('pendingApprovals', 'assets'));
    }

    /**
     * Check if all previous approval levels are approved
     */
    private function arePreviousLevelsApproved(LoanApproval $approval)
    {
        $currentLevel = $approval->approval_level;
        
        // Level 1 (Trustee 1) has no previous levels
        if ($currentLevel == LoanApproval::LEVEL_TRUSTEE_1) {
            return true;
        }
        
        // Get all approvals for this loan
        $allApprovals = LoanApproval::where('loan_id', $approval->loan_id)
            ->orderBy('approval_level', 'asc')
            ->get();
        
        // Check if all previous levels are approved
        for ($level = LoanApproval::LEVEL_TRUSTEE_1; $level < $currentLevel; $level++) {
            $previousApproval = $allApprovals->where('approval_level', $level)->first();
            
            if (!$previousApproval || $previousApproval->status != LoanApproval::STATUS_APPROVED) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Get current user's member ID (handles both customer and admin users)
     */
    private function getCurrentMemberId()
    {
        $user = auth()->user();
        
        // For customer users, get directly from relationship
        if ($user->user_type == 'customer') {
            return $user->member->id ?? null;
        }
        // For admin users in member tenant, find the member that belongs to this tenant
        else if ($user->user_type == 'admin' && $user->tenant_owner == 1) {
            $member = Member::where('member_tenant_id', $user->tenant_id)->first();
            return $member->id ?? null;
        }
        
        return null;
    }

    /**
     * Show approval form for a specific loan
     */
    public function show(Request $request, $tenant, $id)
    {
        $approval = LoanApproval::with(['loan.borrower', 'loan.currency', 'loan.loan_product', 'loan.collaterals', 'loan.guarantors', 'loan.approvals'])
            ->findOrFail($id);

        // Check if current user is the approver
        $currentMemberId = $this->getCurrentMemberId();
        if (!$currentMemberId || $approval->approver_member_id != $currentMemberId) {
            abort(403, _lang('You are not authorized to approve this loan'));
        }

        if ($approval->status != LoanApproval::STATUS_PENDING) {
            return back()->with('error', _lang('This approval has already been processed'));
        }

        // Check if previous levels are approved
        if (!$this->arePreviousLevelsApproved($approval)) {
            return back()->with('error', _lang('Cannot approve this loan. Previous approval levels must be completed first.'));
        }

        $alert_col = 'col-lg-8 offset-lg-2';
        $canApprove = $this->arePreviousLevelsApproved($approval);
        return view('backend.admin.loan.approve_level', compact('approval', 'alert_col', 'canApprove'));
    }

    /**
     * Approve a loan at current level
     */
    public function approve(Request $request, $tenant, $id)
    {
        $request->validate([
            'remarks' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();

        try {
            $approval = LoanApproval::with('loan')->findOrFail($id);

            // Check if current user is the approver
            $currentMemberId = $this->getCurrentMemberId();
            if (!$currentMemberId || $approval->approver_member_id != $currentMemberId) {
                abort(403, _lang('You are not authorized to approve this loan'));
            }

            if ($approval->status != LoanApproval::STATUS_PENDING) {
                return back()->with('error', _lang('This approval has already been processed'));
            }

            // Update approval status
            $approval->status = LoanApproval::STATUS_APPROVED;
            $approval->remarks = $request->remarks;
            $approval->approved_at = now();
            $approval->approved_by_user_id = auth()->id();
            $approval->save();

            $loan = $approval->loan;

            // Check if this is the last approval (Chairman - level 4)
            if ($approval->approval_level == LoanApproval::LEVEL_CHAIRMAN) {
                // All approvals complete - loan is ready for final approval
                $loan->status = 0; // Keep as pending until final admin approval
                $loan->save();

                // Notify borrower that all approvals are complete
                try {
                    $loan->borrower->notify(new LoanApprovalLevelNotification($loan, $approval, 'all_approved'));
                } catch (\Exception $e) {}

            } else {
                // Move to next approval level
                $nextLevel = $approval->approval_level + 1;
                $nextApproval = LoanApproval::where('loan_id', $loan->id)
                    ->where('approval_level', $nextLevel)
                    ->first();

                if ($nextApproval) {
                    // Notify next approver
                    try {
                        if ($nextApproval->approver && $nextApproval->approver->id) {
                            // Check if member has email
                            if (!empty($nextApproval->approver->email)) {
                                // Send notification immediately (not queued) to Member
                                $nextApproval->approver->notifyNow(new LoanApprovalLevelNotification($loan, $nextApproval, 'pending'));
                                \Log::info('Notification sent immediately to next approver (Member ID: ' . $nextApproval->approver->id . ', Email: ' . $nextApproval->approver->email . ')');
                            } else {
                                \Log::warning('Next approver (Member ID: ' . $nextApproval->approver->id . ') does not have an email address');
                            }
                        }
                    } catch (\Exception $e) {
                        \Log::error('Failed to notify next approver: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
                    }
                }
            }

            // Notify borrower of approval at this level
            try {
                $loan->borrower->notify(new LoanApprovalLevelNotification($loan, $approval, 'approved'));
            } catch (\Exception $e) {}

            DB::commit();

            return redirect()->route('loan_approvals.index', $tenant)
                ->with('success', _lang('Loan approved successfully at') . ' ' . _lang($approval->approval_level_name));

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', _lang('An error occurred: ') . $e->getMessage());
        }
    }

    /**
     * Reject a loan at current level
     */
    public function reject(Request $request, $tenant, $id)
    {
        $request->validate([
            'remarks' => 'required|string|max:1000',
        ]);

        DB::beginTransaction();

        try {
            $approval = LoanApproval::with(['loan', 'loan.approvals'])->findOrFail($id);

            // Check if current user is the approver
            $currentMemberId = $this->getCurrentMemberId();
            if (!$currentMemberId || $approval->approver_member_id != $currentMemberId) {
                abort(403, _lang('You are not authorized to reject this loan'));
            }

            if ($approval->status != LoanApproval::STATUS_PENDING) {
                return back()->with('error', _lang('This approval has already been processed'));
            }

            // Check if previous levels are approved
            if (!$this->arePreviousLevelsApproved($approval)) {
                return back()->with('error', _lang('Cannot reject this loan. Previous approval levels must be completed first.'));
            }

            // Update approval status
            $approval->status = LoanApproval::STATUS_REJECTED;
            $approval->remarks = $request->remarks;
            $approval->approved_at = now();
            $approval->approved_by_user_id = auth()->id();
            $approval->save();

            $loan = $approval->loan;

            // Reject the loan completely
            $loan->status = 3; // Cancelled
            $loan->save();

            // Reject all remaining pending approvals
            LoanApproval::where('loan_id', $loan->id)
                ->where('status', LoanApproval::STATUS_PENDING)
                ->update([
                    'status' => LoanApproval::STATUS_REJECTED,
                    'remarks' => _lang('Loan rejected at') . ' ' . _lang($approval->approval_level_name),
                    'approved_at' => now(),
                ]);

            // Notify borrower
            try {
                $loan->borrower->notify(new LoanApprovalLevelNotification($loan, $approval, 'rejected'));
            } catch (\Exception $e) {}

            DB::commit();

            return redirect()->route('loan_approvals.index', $tenant)
                ->with('success', _lang('Loan rejected at') . ' ' . _lang($approval->approval_level_name));

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', _lang('An error occurred: ') . $e->getMessage());
        }
    }
}
