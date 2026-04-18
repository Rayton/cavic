@extends('layouts.app')

@section('workspace_top_tabs')
@include('backend.admin.partials.module-tabs', [
    'variant' => 'top-strip',
    'role' => 'navigation',
    'tabs' => [
        ['label' => _lang('All Members'), 'target' => '#all-members', 'active' => true],
        ['label' => _lang('Onboarding / Requests'), 'target' => '#onboarding'],
        ['label' => _lang('KYC & Documents'), 'target' => '#kyc'],
        ['label' => _lang('Branches'), 'target' => '#branches'],
        ['label' => _lang('Leaders'), 'target' => '#leaders'],
        ['label' => _lang('Import & Setup'), 'target' => '#setup'],
    ],
])
@endsection

@section('content')
@php
    $memberRequests = $memberStats['pending'] ?? 0;
    $membersCount = $memberStats['members'] ?? 0;
    $branchesCount = $memberStats['branches'] ?? 0;
    $leadersCount = $memberStats['leaders'] ?? 0;
    $activeBorrowersCount = $memberStats['active_borrowers'] ?? 0;
@endphp
@include('backend.admin.partials.workspace-styles')
<style>
    .workspace-mini-table td, .workspace-mini-table th { vertical-align: middle; }
</style>

@include('backend.admin.partials.page-header', [
    'title' => _lang('Members Workspace'),
    'subtitle' => _lang('Manage member lifecycle, onboarding support, branches, leaders, and member data from one place.'),
    'badge' => _lang('Member Operations'),
    'breadcrumbs' => [
        ['label' => _lang('Dashboard'), 'url' => route('dashboard.index')],
        ['label' => _lang('Members Workspace'), 'active' => true],
    ],
    'actions' => [
        ['label' => _lang('Add Member'), 'url' => route('members.create'), 'class' => 'btn-primary btn-sm'],
        ['label' => _lang('Bulk Import'), 'url' => route('members.import'), 'class' => 'btn-outline-primary btn-sm'],
    ],
])

<div class="row mb-4">
    <div class="col-md-3 mb-3"><div class="card workspace-stat-card mb-0"><div class="card-body"><div class="stat-label">{{ _lang('Total Members') }}</div><div class="stat-value">{{ number_format($membersCount) }}</div><a class="stat-link" href="{{ route('members.index') }}">{{ _lang('Open member list') }}</a></div></div></div>
    <div class="col-md-3 mb-3"><div class="card workspace-stat-card mb-0"><div class="card-body"><div class="stat-label">{{ _lang('Pending Requests') }}</div><div class="stat-value">{{ $memberRequests }}</div><a class="stat-link" href="{{ route('members.pending_requests') }}">{{ _lang('Review onboarding') }}</a></div></div></div>
    <div class="col-md-3 mb-3"><div class="card workspace-stat-card mb-0"><div class="card-body"><div class="stat-label">{{ _lang('Branches') }}</div><div class="stat-value">{{ $branchesCount }}</div><a class="stat-link" href="{{ route('branches.index') }}">{{ _lang('Manage branches') }}</a></div></div></div>
    <div class="col-md-3 mb-3"><div class="card workspace-stat-card mb-0"><div class="card-body"><div class="stat-label">{{ _lang('Active Borrowers') }}</div><div class="stat-value">{{ $activeBorrowersCount }}</div><a class="stat-link" href="{{ route('loans.filter', 'active') }}">{{ _lang('View active loans') }}</a></div></div></div>
</div>

<div class="card workspace-section-card">
    <div class="card-body tab-content">
        <div class="tab-pane fade show active" id="all-members">
            <div class="table-responsive">
                <table class="table table-sm table-bordered workspace-mini-table mb-3">
                    <thead>
                        <tr>
                            <th>{{ _lang('Member') }}</th>
                            <th>{{ _lang('Member No') }}</th>
                            <th>{{ _lang('Branch') }}</th>
                            <th>{{ _lang('KYC Docs') }}</th>
                            <th>{{ _lang('Status') }}</th>
                            <th>{{ _lang('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentMembers as $member)
                            <tr>
                                <td>{{ $member->name }}</td>
                                <td>{{ $member->member_no }}</td>
                                <td>{{ $member->branch->name }}</td>
                                <td>{{ $member->documents_count }}</td>
                                <td><span class="workspace-status-chip active">{{ _lang('Active') }}</span></td>
                                <td><a class="btn btn-light btn-xs" href="{{ route('members.show', $member->id) }}">{{ _lang('View') }}</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted">{{ _lang('No members found') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <a class="btn btn-outline-primary btn-sm" href="{{ route('members.index') }}">{{ _lang('Open Full Member List') }}</a>
        </div>
        <div class="tab-pane fade" id="onboarding">
            <div class="table-responsive">
                <table class="table table-sm table-bordered workspace-mini-table mb-3">
                    <thead>
                        <tr>
                            <th>{{ _lang('Member') }}</th>
                            <th>{{ _lang('Member No') }}</th>
                            <th>{{ _lang('Branch') }}</th>
                            <th>{{ _lang('KYC Docs') }}</th>
                            <th>{{ _lang('Status') }}</th>
                            <th>{{ _lang('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pendingMembers as $member)
                            <tr>
                                <td>{{ $member->name }}</td>
                                <td>{{ $member->member_no }}</td>
                                <td>{{ $member->branch->name }}</td>
                                <td>{{ $member->documents_count }}</td>
                                <td><span class="workspace-status-chip pending">{{ _lang('Pending Approval') }}</span></td>
                                <td>
                                    <a class="btn btn-light btn-xs mr-1" href="{{ route('members.show', $member->id) }}">{{ _lang('Profile') }}</a>
                                    <a class="btn btn-success btn-xs ajax-modal" href="{{ route('members.accept_request', $member->id) }}" data-title="{{ _lang('Approve Member Request') }}">{{ _lang('Approve') }}</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted">{{ _lang('No pending onboarding requests') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <a class="btn btn-outline-primary btn-sm" href="{{ route('members.pending_requests') }}">{{ _lang('Open Full Onboarding Queue') }}</a>
        </div>
        <div class="tab-pane fade" id="kyc">
            <div class="row">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <div class="workspace-section-title">{{ _lang('Members Missing KYC Documents') }}</div>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered workspace-mini-table mb-3">
                            <thead>
                                <tr>
                                    <th>{{ _lang('Member') }}</th>
                                    <th>{{ _lang('Member No') }}</th>
                                    <th>{{ _lang('Branch') }}</th>
                                    <th>{{ _lang('Status') }}</th>
                                    <th>{{ _lang('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($membersMissingDocuments as $member)
                                    <tr>
                                        <td>{{ $member->name }}</td>
                                        <td>{{ $member->member_no }}</td>
                                        <td>{{ $member->branch->name }}</td>
                                        <td><span class="workspace-status-chip review">{{ _lang('Missing Documents') }}</span></td>
                                        <td>
                                            <a class="btn btn-light btn-xs mr-1" href="{{ route('members.show', $member->id) }}">{{ _lang('Profile') }}</a>
                                            <a class="btn btn-outline-primary btn-xs" href="{{ route('member_documents.index', $member->id) }}">{{ _lang('Documents') }}</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted">{{ _lang('No members are currently missing KYC documents') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="workspace-section-title">{{ _lang('Recent KYC Uploads') }}</div>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered workspace-mini-table mb-3">
                            <thead>
                                <tr>
                                    <th>{{ _lang('Member') }}</th>
                                    <th>{{ _lang('Branch') }}</th>
                                    <th>{{ _lang('Document') }}</th>
                                    <th>{{ _lang('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentDocuments as $document)
                                    <tr>
                                        <td>{{ $document->member->name }}</td>
                                        <td>{{ optional($document->member->branch)->name }}</td>
                                        <td>{{ $document->name }}</td>
                                        <td><a class="btn btn-light btn-xs" href="{{ route('members.show', $document->member_id) }}">{{ _lang('Open Member') }}</a></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted">{{ _lang('No recent KYC uploads found') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <a class="btn btn-outline-primary btn-sm" href="{{ route('members.index') }}">{{ _lang('Open Member Profiles') }}</a>
                </div>
            </div>
        </div>
        <div class="tab-pane fade" id="branches">
            <div class="table-responsive">
                <table class="table table-sm table-bordered workspace-mini-table mb-3">
                    <thead>
                        <tr>
                            <th>{{ _lang('Branch') }}</th>
                            <th>{{ _lang('Active Members') }}</th>
                            <th>{{ _lang('Pending Requests') }}</th>
                            <th>{{ _lang('Missing KYC') }}</th>
                            <th>{{ _lang('Active Borrowers') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($branchSummary as $branch)
                            <tr>
                                <td>{{ $branch->name }}</td>
                                <td>{{ $branch->active_members_count }}</td>
                                <td>{{ $branch->pending_members_count }}</td>
                                <td>{{ $branch->members_missing_documents_count }}</td>
                                <td>{{ $branch->active_borrowers_count }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted">{{ _lang('No branches found') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <a class="btn btn-outline-primary btn-sm" href="{{ route('branches.index') }}">{{ _lang('Manage Branches') }}</a>
        </div>
        <div class="tab-pane fade" id="leaders">
            <div class="table-responsive mb-3">
                <table class="table table-sm table-bordered workspace-mini-table mb-0">
                    <thead>
                        <tr>
                            <th>{{ _lang('Leader') }}</th>
                            <th>{{ _lang('Branch') }}</th>
                            <th>{{ _lang('Position') }}</th>
                            <th>{{ _lang('Status') }}</th>
                            <th>{{ _lang('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($leadersPreview as $leader)
                            <tr>
                                <td>{{ optional($leader->member)->name }}</td>
                                <td>{{ optional(optional($leader->member)->branch)->name }}</td>
                                <td>{{ $leader->position_text }}</td>
                                <td><span class="workspace-status-chip {{ (int) $leader->status === 1 ? 'active' : 'review' }}">{{ (int) $leader->status === 1 ? _lang('Active') : _lang('Inactive') }}</span></td>
                                <td><a class="btn btn-light btn-xs" href="{{ route('leaders.index') }}">{{ _lang('Open') }}</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted">{{ _lang('No leaders found') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex align-items-center justify-content-between flex-wrap">
                <p class="text-muted mb-2 mb-md-0">{{ _lang('Leaders are now grouped under Members for better organizational visibility.') }}</p>
                <a class="btn btn-outline-primary btn-sm" href="{{ route('leaders.index') }}">{{ _lang('Manage Leaders') }}</a>
            </div>
        </div>
        <div class="tab-pane fade" id="setup">
            <div class="list-group workspace-link-list">
                <a class="list-group-item list-group-item-action" href="{{ route('members.import') }}">{{ _lang('Bulk Import Members') }}</a>
                <a class="list-group-item list-group-item-action" href="{{ route('custom_fields.index', ['members']) }}">{{ _lang('Custom Fields') }}</a>
            </div>
        </div>
    </div>
</div>
@endsection
