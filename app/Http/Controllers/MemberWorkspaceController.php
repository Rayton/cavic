<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Leader;
use App\Models\Loan;
use App\Models\Member;
use App\Models\MemberDocument;

class MemberWorkspaceController extends Controller
{
    public function index()
    {
        $pendingMembers = Member::withoutGlobalScopes(['status'])
            ->with('branch')
            ->withCount('documents')
            ->where('status', 0)
            ->latest('id')
            ->limit(8)
            ->get();

        $recentMembers = Member::with('branch')
            ->withCount('documents')
            ->latest('id')
            ->limit(8)
            ->get();

        $membersMissingDocuments = Member::withoutGlobalScopes(['status'])
            ->with('branch')
            ->withCount('documents')
            ->doesntHave('documents')
            ->latest('id')
            ->limit(8)
            ->get();

        $recentDocuments = MemberDocument::with(['member.branch'])
            ->latest('id')
            ->limit(8)
            ->get();

        $leadersPreview = Leader::with(['member.branch'])
            ->latest('id')
            ->limit(8)
            ->get();

        $branchSummary = Branch::get()->map(function ($branch) {
            $branch->active_members_count = Member::withoutGlobalScopes(['status'])
                ->where('branch_id', $branch->id)
                ->where('status', 1)
                ->count();
            $branch->pending_members_count = Member::withoutGlobalScopes(['status'])
                ->where('branch_id', $branch->id)
                ->where('status', 0)
                ->count();
            $branch->members_missing_documents_count = Member::withoutGlobalScopes(['status'])
                ->where('branch_id', $branch->id)
                ->whereDoesntHave('documents')
                ->count();
            $branch->active_borrowers_count = Loan::where('status', 1)
                ->whereHas('borrower', function ($query) use ($branch) {
                    $query->withoutGlobalScopes()->where('branch_id', $branch->id);
                })->distinct('borrower_id')->count('borrower_id');
            return $branch;
        });

        return view('backend.admin.member.workspace', [
            'page_title' => _lang('Members'),
            'assets' => ['datatable'],
            'memberStats' => [
                'members' => Member::count(),
                'pending' => Member::withoutGlobalScopes(['status'])->where('status', 0)->count(),
                'branches' => Branch::count(),
                'leaders' => Leader::count(),
                'active_borrowers' => Loan::where('status', 1)->distinct('borrower_id')->count('borrower_id'),
            ],
            'pendingMembers' => $pendingMembers,
            'recentMembers' => $recentMembers,
            'membersMissingDocuments' => $membersMissingDocuments,
            'recentDocuments' => $recentDocuments,
            'leadersPreview' => $leadersPreview,
            'branchSummary' => $branchSummary,
        ]);
    }
}
