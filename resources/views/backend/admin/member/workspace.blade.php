@extends('layouts.app')

@section('workspace_top_tabs')
@include('backend.admin.partials.module-tabs', [
    'variant' => 'top-strip',
    'role' => 'navigation',
    'tabs' => [
        ['label' => _lang('All Members'), 'target' => '#all-members', 'active' => true],
        ['label' => _lang('Onboarding / Requests'), 'target' => '#onboarding'],
        ['label' => _lang('Documents'), 'target' => '#kyc'],
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
    .member-workspace-link-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1rem; }
    .member-workspace-link-card { display: flex; align-items: center; justify-content: space-between; min-height: 86px; padding: 1rem 1.1rem; border: 1px solid var(--cavic-border, #e7e9e4) !important; border-radius: 14px !important; background: var(--cavic-surface, #fff); color: var(--cavic-text, #2e3338); font-weight: 700; transition: border-color .18s ease, background .18s ease, transform .18s ease; }
    .member-workspace-link-card:hover { border-color: rgba(63, 104, 109, .35) !important; background: var(--cavic-primary-soft, #e7f1f0); color: var(--cavic-primary-dark, #32555a); transform: translateY(-1px); }
    .member-workspace-link-card .link-meta { display: block; margin-top: .2rem; color: var(--cavic-text-soft, #6f787f); font-size: .78rem; font-weight: 500; }
    .member-workspace-link-card i { color: var(--cavic-primary, #3f686d); }
    #leaders .table-row-actions > .btn.dropdown-toggle.btn-xs { width: 34px; height: 34px; min-height: 34px; border-radius: 12px; }
    #leaders .table-row-actions .dropdown-menu { min-width: 168px; padding: .25rem; border-radius: 12px; }
    #leaders .table-row-actions .dropdown-item { min-height: 32px; padding: .35rem .55rem; gap: .45rem; border-radius: 8px; font-size: .78rem; line-height: 1.15; }
    #leaders .table-row-actions .dropdown-item i { width: 16px; text-align: center; font-size: .85rem; }
    @media (max-width: 767px) { .member-workspace-link-grid { grid-template-columns: 1fr; } }
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
        ['label' => _lang('Add Member'), 'url' => route('members.create'), 'class' => 'btn-primary btn-sm ajax-modal', 'data_title' => _lang('Add New Member'), 'data_fullscreen' => true],
        ['label' => _lang('Bulk Import'), 'url' => route('members.import'), 'class' => 'btn-outline-primary btn-sm ajax-modal', 'data_title' => _lang('Bulk Import Members'), 'data_fullscreen' => true],
    ],
])

<div class="workspace-first-tab-stats" data-tab="#all-members">
<div class="row mb-4">
    <div class="col-md-3 mb-3"><div class="card workspace-stat-card mb-0"><div class="card-body"><div class="stat-label">{{ _lang('Total Members') }}</div><div class="stat-value">{{ number_format($membersCount) }}</div><span class="text-muted small">{{ _lang('Current workspace list') }}</span></div></div></div>
    <div class="col-md-3 mb-3"><div class="card workspace-stat-card mb-0"><div class="card-body"><div class="stat-label">{{ _lang('Pending Requests') }}</div><div class="stat-value">{{ $memberRequests }}</div><a class="stat-link" href="{{ route('members.pending_requests') }}">{{ _lang('Review onboarding') }}</a></div></div></div>
    <div class="col-md-3 mb-3"><div class="card workspace-stat-card mb-0"><div class="card-body"><div class="stat-label">{{ _lang('Branches') }}</div><div class="stat-value">{{ $branchesCount }}</div><a class="stat-link" href="{{ route('branches.index') }}">{{ _lang('Manage branches') }}</a></div></div></div>
    <div class="col-md-3 mb-3"><div class="card workspace-stat-card mb-0"><div class="card-body"><div class="stat-label">{{ _lang('Active Borrowers') }}</div><div class="stat-value">{{ $activeBorrowersCount }}</div><a class="stat-link" href="{{ route('loans.filter', 'active') }}">{{ _lang('View active loans') }}</a></div></div></div>
</div>
</div>

<div class="card workspace-section-card cavic-datatable-card">
    <div class="card-body tab-content">
        <div class="tab-pane fade show active" id="all-members">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-export dashboard-table-compact workspace-mini-table cavic-data-table mb-3">
                    <thead>
                        <tr>
                            <th>{{ _lang('Member') }}</th>
                            <th>{{ _lang('Member No') }}</th>
                            <th>{{ _lang('Branch') }}</th>
                            <th>{{ _lang('KYC Docs') }}</th>
                            <th>{{ _lang('Status') }}</th>
                            <th data-no-export="1">{{ _lang('Action') }}</th>
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
                                <td>
                                    @include('backend.admin.partials.table-actions', [
                                        'items' => [
                                            ['label' => _lang('Edit'), 'url' => route('members.edit', $member->id), 'icon' => 'ti-pencil-alt', 'class' => 'ajax-modal', 'data_title' => _lang('Edit Member'), 'data_fullscreen' => true],
                                            ['label' => _lang('View'), 'url' => route('members.show', $member->id), 'icon' => 'ti-eye', 'class' => 'ajax-modal', 'data_title' => _lang('Member Details'), 'data_size' => 'lg'],
                                            ['label' => _lang('Documents'), 'url' => route('member_documents.index', $member->id), 'icon' => 'ti-files', 'class' => 'ajax-modal', 'data_title' => _lang('Member Documents'), 'data_fullscreen' => true],
                                            ['label' => _lang('Delete'), 'url' => route('members.destroy', $member->id), 'icon' => 'ti-trash', 'method' => 'delete', 'class' => 'btn-remove'],
                                        ],
                                    ])
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted">{{ _lang('No members found') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="tab-pane fade" id="onboarding">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-export dashboard-table-compact workspace-mini-table cavic-data-table mb-3">
                    <thead>
                        <tr>
                            <th>{{ _lang('Member') }}</th>
                            <th>{{ _lang('Member No') }}</th>
                            <th>{{ _lang('Branch') }}</th>
                            <th>{{ _lang('KYC Docs') }}</th>
                            <th>{{ _lang('Status') }}</th>
                            <th data-no-export="1">{{ _lang('Action') }}</th>
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
                                    @include('backend.admin.partials.table-actions', [
                                        'items' => [
                                            ['label' => _lang('Edit'), 'url' => route('members.edit', $member->id), 'icon' => 'ti-pencil-alt', 'class' => 'ajax-modal', 'data_title' => _lang('Update Member'), 'data_fullscreen' => true],
                                            ['label' => _lang('View'), 'url' => route('members.show', $member->id), 'icon' => 'ti-eye', 'class' => 'ajax-modal', 'data_title' => _lang('Member Details'), 'data_size' => 'lg'],
                                            ['label' => _lang('Documents'), 'url' => route('member_documents.index', $member->id), 'icon' => 'ti-files', 'class' => 'ajax-modal', 'data_title' => _lang('Member Documents'), 'data_fullscreen' => true],
                                            ['label' => _lang('Approve'), 'url' => route('members.accept_request', $member->id), 'icon' => 'ti-check', 'class' => 'ajax-modal', 'data_title' => _lang('Approve Member Request')],
                                            ['label' => _lang('Delete'), 'url' => route('members.destroy', $member->id), 'icon' => 'ti-trash', 'method' => 'delete', 'class' => 'btn-remove'],
                                        ],
                                    ])
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
                    <div class="workspace-section-title">{{ _lang('Members Missing Documents') }}</div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-export dashboard-table-compact workspace-mini-table cavic-data-table mb-3">
                            <thead>
                                <tr>
                                    <th>{{ _lang('Member') }}</th>
                                    <th>{{ _lang('Member No') }}</th>
                                    <th>{{ _lang('Branch') }}</th>
                                    <th>{{ _lang('Status') }}</th>
                                    <th data-no-export="1">{{ _lang('Action') }}</th>
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
                                            @include('backend.admin.partials.table-actions', [
                                                'items' => [
                                                    ['label' => _lang('Edit'), 'url' => route('members.edit', $member->id), 'icon' => 'ti-pencil-alt', 'class' => 'ajax-modal', 'data_title' => _lang('Update Member'), 'data_fullscreen' => true],
                                                    ['label' => _lang('View'), 'url' => route('members.show', $member->id), 'icon' => 'ti-eye', 'class' => 'ajax-modal', 'data_title' => _lang('Member Details'), 'data_size' => 'lg'],
                                                    ['label' => _lang('Documents'), 'url' => route('member_documents.index', $member->id), 'icon' => 'ti-files', 'class' => 'ajax-modal', 'data_title' => _lang('Member Documents'), 'data_fullscreen' => true],
                                                    ['label' => _lang('Delete'), 'url' => route('members.destroy', $member->id), 'icon' => 'ti-trash', 'method' => 'delete', 'class' => 'btn-remove'],
                                                ],
                                            ])
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
                        <table class="table table-bordered table-striped table-export dashboard-table-compact workspace-mini-table cavic-data-table mb-3">
                            <thead>
                                <tr>
                                    <th>{{ _lang('Member') }}</th>
                                    <th>{{ _lang('Branch') }}</th>
                                    <th>{{ _lang('Document') }}</th>
                                    <th data-no-export="1">{{ _lang('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentDocuments as $document)
                                    <tr>
                                        <td>{{ $document->member->name }}</td>
                                        <td>{{ optional($document->member->branch)->name }}</td>
                                        <td>{{ $document->name }}</td>
                                        <td>@include('backend.admin.partials.table-actions', ['items' => [['label' => _lang('Open Member'), 'url' => route('members.show', $document->member_id), 'icon' => 'ti-eye', 'class' => 'ajax-modal', 'data_title' => _lang('Member Details'), 'data_size' => 'lg']]])</td>
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
                <table class="table table-bordered table-striped table-export dashboard-table-compact workspace-mini-table cavic-data-table mb-3">
                    <thead>
                        <tr>
                            <th>{{ _lang('Branch') }}</th>
                            <th>{{ _lang('Active Members') }}</th>
                            <th>{{ _lang('Pending Requests') }}</th>
                            <th>{{ _lang('Missing Documents') }}</th>
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
                <table class="table table-bordered table-striped table-export dashboard-table-compact workspace-mini-table cavic-data-table mb-0" data-export-filename="Member_Leaders">
                    <thead>
                        <tr>
                            <th>{{ _lang('Leader') }}</th>
                            <th>{{ _lang('Branch') }}</th>
                            <th>{{ _lang('Position') }}</th>
                            <th>{{ _lang('Status') }}</th>
                            <th class="text-center" data-no-export="1">{{ _lang('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($leadersPreview as $leader)
                            <tr>
                                <td>
                                    @if($leader->member)
                                        <a href="{{ route('members.show', $leader->member_id) }}" class="ajax-modal-2" data-title="{{ _lang('Member Details') }}" data-size="lg">
                                            {{ $leader->member->name }}
                                        </a>
                                    @else
                                        <span class="text-muted">{{ _lang('Not Assigned') }}</span>
                                    @endif
                                </td>
                                <td>{{ optional(optional($leader->member)->branch)->name }}</td>
                                <td>{{ $leader->position_text }}</td>
                                <td><span class="workspace-status-chip {{ (int) $leader->status === 1 ? 'active' : 'review' }}">{{ (int) $leader->status === 1 ? _lang('Active') : _lang('Inactive') }}</span></td>
                                <td class="text-center">
                                    @include('backend.admin.partials.table-actions', [
                                        'items' => array_filter([
                                            $leader->member ? ['label' => _lang('View Member'), 'url' => route('members.show', $leader->member_id), 'icon' => 'ti-eye', 'class' => 'ajax-modal-2', 'data_title' => _lang('Member Details'), 'data_size' => 'lg'] : null,
                                            ['label' => _lang('Edit Leader'), 'url' => route('leaders.edit', $leader->id), 'icon' => 'ti-pencil-alt', 'class' => 'ajax-modal-2', 'data_title' => _lang('Edit Leader')],
                                            ['label' => _lang('Manage Leaders'), 'url' => route('leaders.index'), 'icon' => 'ti-layout-list-thumb', 'class' => 'ajax-modal-2', 'data_title' => _lang('Leaders Management'), 'data_size' => 'lg'],
                                        ]),
                                    ])
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted">{{ _lang('No leaders found') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex align-items-center justify-content-between flex-wrap">
                <p class="text-muted mb-2 mb-md-0">{{ _lang('Leaders are now grouped under Members for better organizational visibility.') }}</p>
                <a class="btn btn-outline-primary btn-sm ajax-modal-2" href="{{ route('leaders.index') }}" data-title="{{ _lang('Leaders Management') }}" data-size="lg">{{ _lang('Manage Leaders') }}</a>
            </div>
        </div>
        <div class="tab-pane fade" id="setup">
            <div class="member-workspace-link-grid">
                <a class="member-workspace-link-card ajax-modal" href="{{ route('members.import') }}" data-title="{{ _lang('Bulk Import Members') }}" data-fullscreen="true">
                    <span>{{ _lang('Bulk Import Members') }}<span class="link-meta">{{ _lang('Upload member records from XLSX') }}</span></span>
                    <i class="ti-import"></i>
                </a>
                <a class="member-workspace-link-card ajax-modal" href="{{ route('custom_fields.index', ['members']) }}" data-title="{{ _lang('Member Custom Fields') }}">
                    <span>{{ _lang('Custom Fields') }}<span class="link-meta">{{ _lang('Configure extra member profile fields') }}</span></span>
                    <i class="ti-layout-list-thumb"></i>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js-script')
@include('backend.admin.partials.cavic-datatable-standard')
<script>
    (function ($) {
        window.cavicInitStaticDataTables('.cavic-data-table', 'Members_Workspace');
    })(window.jQuery || window.$);
</script>
@endsection

