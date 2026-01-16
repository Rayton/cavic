<?php

namespace App\Http\Controllers;

use App\Models\Leader;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LeaderController extends Controller
{
    public function __construct()
    {
        date_default_timezone_set(get_timezone());
    }

    /**
     * Display a listing of leaders
     */
    public function index(Request $request, $tenant)
    {
        $assets = ['datatable'];
        $leaders = Leader::with('member')
            ->orderBy('position', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        // Group leaders by position
        $secretaries = $leaders->where('position', Leader::POSITION_SECRETARY);
        $chairmen = $leaders->where('position', Leader::POSITION_CHAIRMAN);

        return view('backend.admin.leader.list', compact('leaders', 'secretaries', 'chairmen', 'assets'));
    }

    /**
     * Show the form for creating/editing leader
     */
    public function create(Request $request, $tenant, $position = null)
    {
        if (!$position) {
            return back()->with('error', _lang('Position is required'));
        }

        // Create a new leader instance for the form
        $leader = new Leader();
        $leader->position = $position;
        $leader->tenant_id = request()->tenant->id;
        $leader->status = 1; // Default to active

        $members = Member::orderBy('first_name', 'asc')->get();
        
        if ($request->ajax()) {
            return view('backend.admin.leader.modal.form', compact('leader', 'members', 'position'));
        } else {
            return view('backend.admin.leader.create', compact('leader', 'members', 'position'));
        }
    }

    /**
     * Store a newly created leader
     */
    public function store(Request $request, $tenant)
    {
        $validator = Validator::make($request->all(), [
            'position' => 'required|in:secretary,chairman',
            'member_id' => 'required|exists:members,id',
            'status' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
            } else {
                return back()->withErrors($validator)->withInput();
            }
        }

        // Create a new leader (allowing multiple leaders per position)
        $leader = new Leader();
        $leader->position = $request->position;
        $leader->member_id = $request->member_id;
        $leader->status = $request->status;
        $leader->tenant_id = request()->tenant->id;
        $leader->save();

        if ($request->ajax()) {
            return response()->json(['result' => 'success', 'action' => 'store', 'message' => _lang('Saved Successfully'), 'data' => $leader]);
        } else {
            return redirect()->route('leaders.index', $tenant)->with('success', _lang('Saved Successfully'));
        }
    }

    /**
     * Show the form for editing leader
     */
    public function edit(Request $request, $tenant, $id)
    {
        $leader = Leader::findOrFail($id);
        $members = Member::orderBy('first_name', 'asc')->get();
        $position = $leader->position;

        if ($request->ajax()) {
            return view('backend.admin.leader.modal.form', compact('leader', 'members', 'position'));
        } else {
            return view('backend.admin.leader.edit', compact('leader', 'members', 'position'));
        }
    }

    /**
     * Update leader
     */
    public function update(Request $request, $tenant, $id)
    {
        $validator = Validator::make($request->all(), [
            'member_id' => 'required|exists:members,id',
            'status' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
            } else {
                return back()->withErrors($validator)->withInput();
            }
        }

        $leader = Leader::findOrFail($id);
        $leader->member_id = $request->member_id;
        $leader->status = $request->status;
        $leader->save();

        if ($request->ajax()) {
            return response()->json(['result' => 'success', 'action' => 'update', 'message' => _lang('Updated Successfully'), 'data' => $leader]);
        } else {
            return redirect()->route('leaders.index', $tenant)->with('success', _lang('Updated Successfully'));
        }
    }

    /**
     * Remove leader
     */
    public function destroy($tenant, $id)
    {
        $leader = Leader::findOrFail($id);
        
        // Check if this leader is being used in any loans
        $loansUsingLeader = \App\Models\Loan::where('secretary_leader_id', $id)
            ->orWhere('chairman_leader_id', $id)
            ->count();
        
        if ($loansUsingLeader > 0) {
            return redirect()->route('leaders.index', $tenant)
                ->with('error', _lang('Cannot delete leader. This leader is assigned to') . ' ' . $loansUsingLeader . ' ' . _lang('loan(s)'));
        }
        
        $leader->delete();

        return redirect()->route('leaders.index', $tenant)->with('success', _lang('Deleted Successfully'));
    }
}
