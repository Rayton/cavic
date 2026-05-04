@extends('layouts.app')

@section('workspace_top_tabs')
@include('backend.admin.partials.module-tabs', [
    'variant' => 'top-strip',
    'role' => 'navigation',
    'tabs' => [
        ['label' => _lang('Member Requests'), 'target' => '#member-requests', 'active' => true],
        ['label' => _lang('Pending Loans'), 'target' => '#pending-loans'],
        ['label' => _lang('Finance Requests'), 'target' => '#finance-requests'],
        ['label' => _lang('Due Today & Upcoming'), 'target' => '#due-upcoming'],
        ['label' => _lang('Exceptions'), 'target' => '#exceptions'],
    ],
])
@endsection

@section('content')
@php
    $memberRequestsCount = $actionStats['member_requests'] ?? request_count('member_requests');
    $pendingLoansCount = $actionStats['pending_loans'] ?? request_count('pending_loans');
    $depositRequestsCount = $actionStats['deposit_requests'] ?? request_count('deposit_requests');
    $withdrawRequestsCount = $actionStats['withdraw_requests'] ?? request_count('withdraw_requests');
    $dueTodayCount = $actionStats['due_today'] ?? 0;
    $upcomingRepaymentsCount = $actionStats['upcoming'] ?? 0;
    $overdueRepaymentsCount = $actionStats['overdue'] ?? 0;
    $readyForDisbursementCount = $actionStats['ready_disbursement'] ?? 0;
    $criticalCollectionsCount = $actionStats['critical_collections'] ?? 0;
    $pendingBankTransactionsCount = $actionStats['pending_bank_transactions'] ?? 0;
    $pendingCashTransactionsCount = $actionStats['pending_cash_transactions'] ?? 0;
    $pendingFinanceRequestsCount = $actionStats['pending_finance_requests'] ?? 0;
    $exceptionCount = $actionStats['exception_total'] ?? 0;
    $brokenPromisesCount = $actionStats['broken_promises'] ?? 0;
    $followUpsLoggedToday = $collectionExecutionStats['logged_today'] ?? 0;
    $promiseDueTodayCount = $collectionExecutionStats['promise_due_today'] ?? 0;
    $resolvedInRangeCount = $collectionExecutionStats['resolved_in_range'] ?? 0;
    $recoveredInRangeCount = $collectionExecutionStats['recovered_in_range'] ?? 0;
    $promiseKeptCount = $collectionExecutionStats['promise_kept'] ?? 0;
    $collectionCompletionRate = $collectionExecutionStats['completion_rate'] ?? 0;
@endphp
@include('backend.admin.partials.workspace-styles')
<style>
    .action-center-table-actions .table-responsive { border: 1px solid var(--cavic-border, #e7e9e4); border-radius: 18px; background: var(--cavic-surface, #fff); overflow-x: auto; overflow-y: visible; }
    .action-center-table-actions .workspace-mini-table { border: 0 !important; border-collapse: separate; border-spacing: 0; color: var(--cavic-text, #2e3338); font-size: .86rem; min-width: 720px; }
    .action-center-table-actions .workspace-mini-table.table-bordered th,
    .action-center-table-actions .workspace-mini-table.table-bordered td { border-left: 0; border-right: 0; }
    .action-center-table-actions .workspace-mini-table th { border-top: 0; border-bottom: 1px solid var(--cavic-border, #e7e9e4); background: var(--cavic-surface-muted, #fafaf8); color: var(--cavic-text-soft, #6f787f); font-size: .74rem; font-weight: 800; padding: .58rem .8rem; vertical-align: middle; white-space: nowrap; }
    .action-center-table-actions .workspace-mini-table td { border-top: 0; border-bottom: 1px solid #eef1ec; background: var(--cavic-surface, #fff); padding: .52rem .8rem; vertical-align: middle; }
    .action-center-table-actions .workspace-mini-table tbody tr:last-child td { border-bottom: 0; }
    .action-center-table-actions .workspace-mini-table tbody tr:hover td { background: #fcfdfb; }
    .action-center-table-actions .workspace-mini-table td:first-child { color: var(--cavic-text, #2e3338); font-weight: 700; }
    .action-center-table-actions .workspace-mini-table td.text-center.text-muted,
    .action-center-table-actions .workspace-mini-table td[colspan] { padding: 1.25rem; color: var(--cavic-text-muted, #9aa2a8) !important; font-weight: 600; }
    .action-center-table-actions .workspace-mini-table .btn { white-space: nowrap; }
    .action-center-table-actions .workspace-mini-table .workspace-status-chip { white-space: nowrap; }
    .action-center-table-actions .workspace-mini-table th:last-child,
    .action-center-table-actions .workspace-mini-table td:last-child { text-align: center; width: 1%; white-space: nowrap; }
    .action-center-link { display: inline-flex; align-items: center; gap: .35rem; font-size: .78rem; font-weight: 700; color: var(--cavic-primary, #1A8E8F); text-decoration: none; }
    .action-center-link:hover { color: #126768; text-decoration: none; }
    .workspace-stat-card .stat-link.action-center-link { margin-top: .35rem; }
    .action-center-local-link { border: 1px solid rgba(26, 142, 143, .28); border-radius: 999px; padding: .3rem .7rem; background: rgba(26, 142, 143, .08); }
    .action-center-local-link:hover { background: rgba(26, 142, 143, .14); }
    .action-center-table-actions .table-row-actions .btn { min-height: 30px; border: 1px solid rgba(63, 104, 109, .28); border-radius: 10px; color: var(--cavic-primary-dark, #32555a); background: var(--cavic-surface, #fff); font-size: .75rem; font-weight: 800; padding: .28rem .7rem; }
    .action-center-table-actions .table-row-actions .btn:hover { background: var(--cavic-primary-soft, #e7f1f0); border-color: var(--cavic-primary, #3f686d); color: var(--cavic-primary-dark, #32555a); }
    .action-center-table-actions .table-row-actions .dropdown-menu { border-color: var(--cavic-border, #e7e9e4); border-radius: 12px; box-shadow: 0 12px 28px rgba(31, 41, 55, .08); padding: .35rem; }
    .action-center-table-actions .dropdown-item { display: flex; align-items: center; gap: .45rem; font-weight: 600; padding: .55rem .85rem; }
    .action-center-table-actions .dropdown-item:hover { background: var(--cavic-primary-soft, #e7f1f0); color: var(--cavic-primary-dark, #32555a); border-radius: 9px; }
    .action-center-table-actions .workspace-section-title,
    .action-center-table-actions h6 { color: var(--cavic-text, #2e3338); font-weight: 800; }
    .action-center-table-actions.dashboard-proof-datatable-card .admin-datatable-top { margin-bottom: .7rem; }
    .action-center-table-actions.dashboard-proof-datatable-card .admin-datatable-table-wrap { border: 0; border-radius: 0; }
    .action-center-table-actions.dashboard-proof-datatable-card .dashboard-proof-top-center { justify-content: center; }
    .action-center-table-actions.dashboard-proof-datatable-card .dashboard-proof-export-buttons .admin-dt-btn { min-height: 30px; padding: .32rem .62rem; }
    .action-center-table-actions.dashboard-proof-datatable-card .dashboard-columns-menu { z-index: 1085; max-height: min(420px, 70vh); overflow-y: auto; }
    .action-center-table-actions .card-body { padding: 1.25rem; }
    .action-center-table-actions .row { row-gap: 1.35rem; }
    .action-center-table-actions .workspace-section-title,
    .action-center-table-actions h6 { margin-bottom: .65rem; }
    .action-center-table-actions .table-responsive {
        border: 0;
        border-radius: 0;
        background: transparent;
        overflow: visible;
    }
    .action-center-table-actions .dataTables_wrapper { max-width: 100%; overflow: visible; }
    .action-center-table-actions .admin-datatable-top {
        display: grid !important;
        grid-template-columns: 86px minmax(0, 1fr) !important;
        grid-template-areas: "length tools" !important;
        align-items: center !important;
        gap: .55rem !important;
        margin: 0 !important;
        padding: .75rem !important;
        border: 1px solid var(--cavic-border, #e7e9e4);
        border-bottom: 0;
        border-radius: 14px 14px 0 0;
        background: #fff;
    }
    .action-center-table-actions .dashboard-proof-top-left {
        grid-area: length;
        min-width: 0;
        width: 86px;
        justify-content: flex-start !important;
    }
    .action-center-table-actions .dashboard-proof-top-center { display: none !important; }
    .action-center-table-actions .dashboard-proof-top-right {
        grid-area: tools;
        display: grid !important;
        grid-template-columns: auto minmax(130px, 1fr);
        align-items: center;
        gap: .55rem;
        width: 100%;
        min-width: 0;
        margin-left: 0 !important;
        justify-content: stretch !important;
    }
    .action-center-table-actions .dashboard-toolbar-item,
    .action-center-table-actions .dashboard-toolbar-item-search,
    .action-center-table-actions .dashboard-proof-top-right .dataTables_filter,
    .action-center-table-actions .dashboard-proof-top-right .dataTables_filter label {
        min-width: 0;
        width: 100%;
    }
    .action-center-table-actions .dashboard-toolbar-item-columns { width: auto; }
    .action-center-table-actions .dashboard-proof-top-left .dataTables_length,
    .action-center-table-actions .dashboard-proof-top-left .dataTables_length label,
    .action-center-table-actions .dashboard-proof-top-left .dataTables_length select {
        width: 100% !important;
        margin: 0 !important;
    }
    .action-center-table-actions .dashboard-proof-top-left .dataTables_length select,
    .action-center-table-actions .dashboard-proof-top-right .dataTables_filter input,
    .action-center-table-actions .dashboard-columns-trigger {
        height: 36px !important;
        min-height: 36px !important;
        border-radius: 10px !important;
    }
    .action-center-table-actions .dashboard-proof-top-right .dataTables_filter input {
        width: 100% !important;
        max-width: 100% !important;
        min-width: 0 !important;
        margin: 0 !important;
    }
    .action-center-table-actions .dashboard-columns-trigger {
        width: auto;
        min-width: 112px;
        padding: .38rem .62rem !important;
        gap: .4rem;
    }
    .action-center-table-actions .admin-datatable-table-wrap {
        border: 1px solid var(--cavic-border, #e7e9e4) !important;
        border-radius: 0 !important;
        overflow-x: auto;
        overflow-y: visible;
    }
    .action-center-table-actions .admin-datatable-table-wrap table.dataTable {
        margin: 0 !important;
    }
    .action-center-table-actions .admin-datatable-bottom {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        gap: .75rem !important;
        padding: .65rem .75rem !important;
        border: 1px solid var(--cavic-border, #e7e9e4);
        border-top: 0;
        border-radius: 0 0 14px 14px;
        background: #fff;
    }
    .action-center-table-actions .dataTables_info {
        color: var(--cavic-text-soft, #6f787f);
        font-size: .78rem;
        padding: 0 !important;
        white-space: normal;
    }
    .action-center-table-actions .dataTables_paginate { flex: 0 0 auto; }
    .action-center-table-actions .dataTables_paginate .pagination { margin: 0; }
    .action-center-table-actions .dataTables_paginate .page-link {
        width: 32px;
        height: 32px;
        min-width: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50% !important;
        font-size: .75rem;
    }
    .action-center-table-actions .workspace-mini-table { min-width: 0 !important; width: 100% !important; }
    .action-center-table-actions .workspace-mini-table th {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 220px;
    }
    .action-center-table-actions .workspace-mini-table td {
        white-space: normal;
        overflow: visible;
        text-overflow: clip;
        max-width: 240px;
        overflow-wrap: anywhere;
    }
    .action-center-table-actions .workspace-mini-table td:first-child,
    .action-center-table-actions .workspace-mini-table th:first-child { max-width: 180px; }
    .action-center-table-actions .workspace-mini-table td[colspan] {
        white-space: normal;
        max-width: none;
    }
    @media (max-width: 1399.98px) {
        .action-center-table-actions .col-lg-5,
        .action-center-table-actions .col-lg-6,
        .action-center-table-actions .col-lg-7 {
            flex: 0 0 100%;
            max-width: 100%;
        }
    }
    @media (max-width: 767.98px) {
        .action-center-table-actions .card-body { padding: .85rem; }
        .action-center-table-actions .admin-datatable-top {
            grid-template-columns: 76px minmax(0, 1fr) !important;
            gap: .45rem !important;
            padding: .6rem !important;
        }
        .action-center-table-actions .dashboard-proof-top-left { width: 76px; }
        .action-center-table-actions .dashboard-proof-top-right {
            grid-template-columns: 1fr;
        }
        .action-center-table-actions .dashboard-columns-trigger {
            justify-content: center;
            width: 100%;
        }
        .action-center-table-actions .admin-datatable-bottom {
            align-items: flex-start !important;
            flex-direction: column !important;
        }
    }
</style>

@include('backend.admin.partials.collection-date-range-filter', ['collectionDateRange' => $collectionDateRange, 'filterId' => 'action-center-collection-range'])

<div class="workspace-first-tab-stats" data-tab="#member-requests">
<div class="row mb-4">
    <div class="col-md-4 col-xl mb-3">
        <div class="card workspace-stat-card mb-0"><div class="card-body"><div class="stat-label">{{ _lang('Member Requests') }}</div><div class="stat-value">{{ $memberRequestsCount }}</div><a class="stat-link action-center-link action-center-tab-link" href="#member-requests"><i class="fas fa-arrow-right"></i>{{ _lang('Open queue') }}</a></div></div>
    </div>
    <div class="col-md-4 col-xl mb-3">
        <div class="card workspace-stat-card mb-0"><div class="card-body"><div class="stat-label">{{ _lang('Pending Loans') }}</div><div class="stat-value">{{ $pendingLoansCount }}</div><a class="stat-link action-center-link action-center-tab-link" href="#pending-loans"><i class="fas fa-arrow-right"></i>{{ _lang('Review applications') }}</a></div></div>
    </div>
    <div class="col-md-4 col-xl mb-3">
        <div class="card workspace-stat-card mb-0"><div class="card-body"><div class="stat-label">{{ _lang('Due Today') }}</div><div class="stat-value">{{ $dueTodayCount }}</div><a class="stat-link action-center-link action-center-tab-link" href="#due-upcoming"><i class="fas fa-arrow-right"></i>{{ _lang('Open today\'s collections') }}</a></div></div>
    </div>
    <div class="col-md-4 col-xl mb-3">
        <div class="card workspace-stat-card mb-0"><div class="card-body"><div class="stat-label">{{ _lang('Deposit Requests') }}</div><div class="stat-value">{{ $depositRequestsCount }}</div><a class="stat-link action-center-link action-center-tab-link" href="#finance-requests"><i class="fas fa-arrow-right"></i>{{ _lang('Open requests') }}</a></div></div>
    </div>
    <div class="col-md-4 col-xl mb-3">
        <div class="card workspace-stat-card mb-0"><div class="card-body"><div class="stat-label">{{ _lang('Withdraw Requests') }}</div><div class="stat-value">{{ $withdrawRequestsCount }}</div><a class="stat-link action-center-link action-center-tab-link" href="#finance-requests"><i class="fas fa-arrow-right"></i>{{ _lang('Open requests') }}</a></div></div>
    </div>
    <div class="col-md-4 col-xl mb-3">
        <div class="card workspace-stat-card mb-0"><div class="card-body"><div class="stat-label">{{ _lang('Overdue Repayments') }}</div><div class="stat-value">{{ $overdueRepaymentsCount }}</div><a class="stat-link action-center-link action-center-tab-link" href="#exceptions"><i class="fas fa-arrow-right"></i>{{ _lang('Review collections') }}</a></div></div>
    </div>
    <div class="col-md-4 col-xl mb-3">
        <div class="card workspace-stat-card mb-0"><div class="card-body"><div class="stat-label">{{ _lang('Critical Collections') }}</div><div class="stat-value">{{ $criticalCollectionsCount }}</div><a class="stat-link action-center-link action-center-tab-link" href="#exceptions"><i class="fas fa-arrow-right"></i>{{ _lang('Escalate critical cases') }}</a></div></div>
    </div>
</div>
</div>

<div class="card workspace-section-card action-center-table-actions dashboard-proof-datatable-card">
    <div class="card-body tab-content">
        <div class="tab-pane fade show active" id="member-requests">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-export dashboard-table-compact workspace-mini-table action-center-data-table mb-3">
                    <thead>
                        <tr>
                            <th>{{ _lang('Member') }}</th>
                            <th>{{ _lang('Member No') }}</th>
                            <th>{{ _lang('Branch') }}</th>
                            <th>{{ _lang('Status') }}</th>
                            <th class="text-center" data-no-export="1">{{ _lang('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($memberRequests as $member)
                            <tr>
                                <td>{{ $member->name }}</td>
                                <td>{{ $member->member_no }}</td>
                                <td>{{ $member->branch->name }}</td>
                                <td><span class="workspace-status-chip pending">{{ _lang('Pending Approval') }}</span></td>
                                <td class="text-center">
                                    @include('backend.admin.partials.table-actions', [
                                        'items' => [
                                            ['label' => _lang('Quick View'), 'url' => route('members.show', $member->id), 'icon' => 'ti-eye', 'class' => 'ajax-modal', 'data_title' => _lang('Member Request Summary'), 'data_size' => 'lg'],
                                            ['label' => _lang('Approve'), 'url' => route('members.accept_request', $member->id), 'icon' => 'ti-check', 'class' => 'ajax-modal', 'data_title' => _lang('Approve Member Request')],
                                            ['label' => _lang('Reject'), 'url' => route('members.reject_request', $member->id), 'icon' => 'ti-close', 'class' => 'ajax-action', 'data_confirm' => _lang('Reject this member request?')],
                                        ],
                                    ])
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted">{{ _lang('No pending member requests') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <a href="#member-requests" class="btn btn-outline-primary btn-sm action-center-tab-link">{{ _lang('Stay In Member Requests Queue') }}</a>
        </div>
        <div class="tab-pane fade" id="pending-loans">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-export dashboard-table-compact workspace-mini-table action-center-data-table mb-3">
                    <thead>
                        <tr>
                            <th>{{ _lang('Loan ID') }}</th>
                            <th>{{ _lang('Borrower') }}</th>
                            <th>{{ _lang('Product') }}</th>
                            <th>{{ _lang('Amount') }}</th>
                            <th>{{ _lang('Stage') }}</th>
                            <th>{{ _lang('Approval Progress') }}</th>
                            <th class="text-center" data-no-export="1">{{ _lang('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pendingLoans as $loan)
                            <tr>
                                <td>{{ $loan->loan_id }}</td>
                                <td>{{ $loan->borrower->name }}</td>
                                <td>{{ $loan->loan_product->name }}</td>
                                <td>{{ decimalPlace($loan->applied_amount, optional($loan->currency)->name) }}</td>
                                <td><span class="workspace-status-chip {{ $loan->workspace_stage_theme ?? 'review' }}">{{ $loan->workspace_stage_label ?? _lang('Under Review') }}</span></td>
                                <td><span class="text-muted small">{{ $loan->workspace_stage_meta ?? _lang('Waiting in queue') }}</span></td>
                                <td class="text-center">@include('backend.admin.partials.table-actions', ['items' => [
                                    ['label' => _lang('Quick Review'), 'url' => route('loans.show', $loan->id), 'icon' => 'ti-eye', 'class' => 'ajax-modal', 'data_title' => _lang('Loan Review Summary')],
                                    ['label' => _lang('Approve'), 'url' => route('loans.approve', $loan->id), 'icon' => 'ti-check', 'class' => 'ajax-modal', 'data_title' => _lang('Confirm Loan Approval')],
                                ]])</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted">{{ _lang('No pending loans') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <a href="#pending-loans" class="btn btn-outline-primary btn-sm action-center-tab-link">{{ _lang('Stay In Pending Loans Queue') }}</a>
        </div>
        <div class="tab-pane fade" id="finance-requests">
            <div class="row">
                <div class="col-lg-6 mb-3 mb-lg-0">
                    <h6>{{ _lang('Deposit Requests') }}</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-export dashboard-table-compact workspace-mini-table action-center-data-table mb-3">
                            <thead><tr><th>{{ _lang('Member') }}</th><th>{{ _lang('Amount') }}</th><th>{{ _lang('Method') }}</th><th>{{ _lang('Status') }}</th><th data-no-export="1">{{ _lang('Action') }}</th></tr></thead>
                            <tbody>
                                @forelse($depositRequests as $request)
                                    <tr>
                                        <td>{{ $request->member->name }}</td>
                                        <td>{{ decimalPlace($request->amount, optional(optional(optional($request->account)->savings_type)->currency)->name) }}</td>
                                        <td>{{ $request->method->name }}</td>
                                        <td><span class="workspace-status-chip pending">{{ _lang('Pending') }}</span></td>
                                        <td>@include('backend.admin.partials.table-actions', ['items' => [['label' => _lang('Quick View'), 'url' => route('deposit_requests.show', $request->id), 'icon' => 'ti-eye', 'class' => 'ajax-modal', 'data_title' => _lang('Deposit Request Details')]]])</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted">{{ _lang('No pending deposit requests') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <a href="#finance-requests" class="btn btn-outline-primary btn-sm action-center-tab-link">{{ _lang('Stay In Deposit Requests') }}</a>
                </div>
                <div class="col-lg-6">
                    <h6>{{ _lang('Withdraw Requests') }}</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-export dashboard-table-compact workspace-mini-table action-center-data-table mb-3">
                            <thead><tr><th>{{ _lang('Member') }}</th><th>{{ _lang('Amount') }}</th><th>{{ _lang('Method') }}</th><th>{{ _lang('Status') }}</th><th data-no-export="1">{{ _lang('Action') }}</th></tr></thead>
                            <tbody>
                                @forelse($withdrawRequests as $request)
                                    <tr>
                                        <td>{{ $request->member->name }}</td>
                                        <td>{{ decimalPlace($request->amount, optional(optional(optional($request->account)->savings_type)->currency)->name) }}</td>
                                        <td>{{ $request->method->name }}</td>
                                        <td><span class="workspace-status-chip pending">{{ _lang('Pending') }}</span></td>
                                        <td>@include('backend.admin.partials.table-actions', ['items' => [['label' => _lang('Quick View'), 'url' => route('withdraw_requests.show', $request->id), 'icon' => 'ti-eye', 'class' => 'ajax-modal', 'data_title' => _lang('Withdraw Request Details')]]])</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted">{{ _lang('No pending withdraw requests') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <a href="#finance-requests" class="btn btn-outline-primary btn-sm action-center-tab-link">{{ _lang('Stay In Withdraw Requests') }}</a>
                </div>
            </div>
        </div>
        <div class="tab-pane fade" id="due-upcoming">
            <div class="row">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <div class="workspace-section-title">{{ _lang('Due Today') }}</div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-export dashboard-table-compact workspace-mini-table action-center-data-table mb-3">
                            <thead>
                                <tr>
                                    <th>{{ _lang('Loan ID') }}</th>
                                    <th>{{ _lang('Borrower') }}</th>
                                    <th>{{ _lang('Repayment Date') }}</th>
                                    <th>{{ _lang('Amount') }}</th>
                                    <th>{{ _lang('Status') }}</th>
                                    <th data-no-export="1">{{ _lang('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($dueTodayRepayments as $repayment)
                                    <tr>
                                        <td>{{ $repayment->loan->loan_id }}</td>
                                        <td>{{ $repayment->loan->borrower->name }}</td>
                                        <td>{{ $repayment->repayment_date }}</td>
                                        <td>{{ decimalPlace($repayment->amount_to_pay, optional($repayment->loan->currency)->name) }}</td>
                                        <td><span class="workspace-status-chip today">{{ _lang('Due Today') }}</span></td>
                                        <td>@include('backend.admin.partials.table-actions', ['items' => [['label' => _lang('Loan Summary'), 'url' => route('loans.show', $repayment->loan_id), 'icon' => 'ti-eye', 'class' => 'ajax-modal', 'data_title' => _lang('Loan Repayment Summary')]]])</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted">{{ _lang('No repayments due today') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="workspace-section-title">{{ _lang('Next 7 Days') }}</div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-export dashboard-table-compact workspace-mini-table action-center-data-table mb-3">
                            <thead>
                                <tr>
                                    <th>{{ _lang('Loan ID') }}</th>
                                    <th>{{ _lang('Borrower') }}</th>
                                    <th>{{ _lang('Repayment Date') }}</th>
                                    <th>{{ _lang('Amount') }}</th>
                                    <th>{{ _lang('Status') }}</th>
                                    <th data-no-export="1">{{ _lang('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($upcomingRepayments as $repayment)
                                    <tr>
                                        <td>{{ $repayment->loan->loan_id }}</td>
                                        <td>{{ $repayment->loan->borrower->name }}</td>
                                        <td>{{ $repayment->repayment_date }}</td>
                                        <td>{{ decimalPlace($repayment->amount_to_pay, optional($repayment->loan->currency)->name) }}</td>
                                        <td><span class="workspace-status-chip upcoming">{{ _lang('Upcoming') }}</span></td>
                                        <td>@include('backend.admin.partials.table-actions', ['items' => [['label' => _lang('Loan Summary'), 'url' => route('loans.show', $repayment->loan_id), 'icon' => 'ti-eye', 'class' => 'ajax-modal', 'data_title' => _lang('Loan Repayment Summary')]]])</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted">{{ _lang('No upcoming repayments in the next 7 days') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-lg-7 mb-4 mb-lg-0">
                    <div class="workspace-section-title">{{ _lang('Collector-ready Call List') }}</div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-export dashboard-table-compact workspace-mini-table action-center-data-table mb-0">
                            <thead>
                                <tr>
                                    <th>{{ _lang('Loan ID') }}</th>
                                    <th>{{ _lang('Borrower') }}</th>
                                    <th>{{ _lang('Phone') }}</th>
                                    <th>{{ _lang('Queue') }}</th>
                                    <th>{{ _lang('Last Follow-up') }}</th>
                                    <th>{{ _lang('Next Action') }}</th>
                                    <th data-no-export="1">{{ _lang('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($collectorReadyCallList as $item)
                                    <tr>
                                        <td>{{ $item->loan_id }}</td>
                                        <td>{{ $item->borrower_name }}</td>
                                        <td>{{ trim($item->contact_phone) != '' ? $item->contact_phone : _lang('N/A') }}</td>
                                        <td><span class="workspace-status-chip {{ $item->queue_theme }}">{{ $item->queue_label }}</span></td>
                                        <td>
                                            @if($item->last_outcome_label)
                                                <span class="workspace-status-chip {{ $item->last_outcome_theme }}">{{ $item->last_outcome_label }}</span>
                                            @else
                                                <span class="text-muted small">{{ _lang('No log yet') }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $item->next_action }}</td>
                                        <td>@include('backend.admin.partials.table-actions', ['items' => [['label' => _lang('Log Follow-up'), 'url' => route('loan_collection_follow_ups.create', $item->repayment_id), 'icon' => 'ti-write', 'class' => 'ajax-modal', 'data_title' => _lang('Log Collection Follow-up')]]])</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="text-center text-muted">{{ _lang('No call list items available') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="workspace-section-title">{{ _lang('Upcoming Reminder Queue') }}</div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-export dashboard-table-compact workspace-mini-table action-center-data-table mb-0">
                            <thead>
                                <tr>
                                    <th>{{ _lang('Loan ID') }}</th>
                                    <th>{{ _lang('Borrower') }}</th>
                                    <th>{{ _lang('Due In') }}</th>
                                    <th>{{ _lang('Reminder') }}</th>
                                    <th>{{ _lang('Last Follow-up') }}</th>
                                    <th data-no-export="1">{{ _lang('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($upcomingReminderQueue as $item)
                                    <tr>
                                        <td>{{ $item->loan_id }}</td>
                                        <td>{{ $item->borrower_name }}</td>
                                        <td>{{ $item->days_until }} {{ _lang('days') }}</td>
                                        <td><span class="workspace-status-chip {{ $item->reminder_theme }}">{{ $item->reminder_label }}</span></td>
                                        <td>
                                            @if($item->last_outcome_label)
                                                <span class="workspace-status-chip {{ $item->last_outcome_theme }}">{{ $item->last_outcome_label }}</span>
                                            @else
                                                <span class="text-muted small">{{ _lang('No log yet') }}</span>
                                            @endif
                                        </td>
                                        <td>@include('backend.admin.partials.table-actions', ['items' => [['label' => _lang('Log Follow-up'), 'url' => route('loan_collection_follow_ups.create', $item->repayment_id), 'icon' => 'ti-write', 'class' => 'ajax-modal', 'data_title' => _lang('Log Collection Follow-up')]]])</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted">{{ _lang('No reminder items available') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <a href="#due-upcoming" class="btn btn-outline-primary btn-sm mt-4 action-center-tab-link">{{ _lang('Stay In Repayment Queue') }}</a>
        </div>
        <div class="tab-pane fade" id="exceptions">
            <div class="alert alert-light border mb-4">
                {{ _lang('Current high-priority exception queue') }}: <strong>{{ $exceptionCount }}</strong>
            </div>
            <div class="alert alert-light small mb-4">
                <strong>{{ _lang('Selected analytics range') }}:</strong> {{ $collectionDateRange['label'] ?? _lang('Today') }}
            </div>
            @unless($followUpsEnabled)
                <div class="alert alert-info small">
                    <strong>{{ _lang('Follow-up tracking setup') }}:</strong> {{ _lang('Run the latest migration to unlock saved collection outcomes, broken-promise tracking, and branch completion analytics.') }}
                </div>
            @endunless
            <div class="row mb-4">
                <div class="col-md-4 col-xl-2 mb-3"><div class="card workspace-bucket-card mb-0 h-100"><div class="card-body"><div class="bucket-label">{{ _lang('Follow-ups Logged') }}</div><div class="bucket-value">{{ $followUpsLoggedToday }}</div><div class="bucket-meta">{{ _lang('Collection notes recorded in selected range') }}</div></div></div></div>
                <div class="col-md-4 col-xl-2 mb-3"><div class="card workspace-bucket-card mb-0 h-100"><div class="card-body"><div class="bucket-label">{{ _lang('Promises in Range') }}</div><div class="bucket-value">{{ $promiseDueTodayCount }}</div><div class="bucket-meta">{{ _lang('Promised-to-pay cases dated inside selected range') }}</div></div></div></div>
                <div class="col-md-4 col-xl-2 mb-3"><div class="card workspace-bucket-card mb-0 h-100"><div class="card-body"><div class="bucket-label">{{ _lang('Broken Promises') }}</div><div class="bucket-value">{{ $brokenPromisesCount }}</div><div class="bucket-meta">{{ _lang('Outstanding promised-to-pay cases already past due as of range end') }}</div></div></div></div>
                <div class="col-md-4 col-xl-2 mb-3"><div class="card workspace-bucket-card mb-0 h-100"><div class="card-body"><div class="bucket-label">{{ _lang('Recovered') }}</div><div class="bucket-value">{{ $recoveredInRangeCount }}</div><div class="bucket-meta">{{ _lang('Paid repayments that had follow-up activity in selected range') }}</div></div></div></div>
                <div class="col-md-4 col-xl-2 mb-3"><div class="card workspace-bucket-card mb-0 h-100"><div class="card-body"><div class="bucket-label">{{ _lang('Promise Kept') }}</div><div class="bucket-value">{{ $promiseKeptCount }}</div><div class="bucket-meta">{{ _lang('Promised-to-pay cases settled on or before committed date') }}</div></div></div></div>
                <div class="col-md-4 col-xl-2 mb-3"><div class="card workspace-bucket-card mb-0 h-100"><div class="card-body"><div class="bucket-label">{{ _lang('Completion Rate') }}</div><div class="bucket-value">{{ $collectionCompletionRate }}%</div><div class="bucket-meta">{{ _lang('Open due and overdue cases touched in selected range') }}</div></div></div></div>
            </div>
            <div class="row mb-4">
                @foreach($collectionBuckets as $bucket)
                    <div class="col-md-4 mb-3">
                        <div class="card workspace-bucket-card mb-0">
                            <div class="card-body">
                                <div class="bucket-label">{{ $bucket->label }}</div>
                                <div class="bucket-value">{{ $bucket->count }}</div>
                                <div class="bucket-meta">{{ _lang('Collections aging bucket') }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="row">
                <div class="col-lg-5 mb-4 mb-lg-0">
                    <ul class="workspace-exception-list">
                        <li><span>{{ _lang('Overdue repayments') }}</span><strong>{{ $overdueRepaymentsCount }}</strong></li>
                        <li><span>{{ _lang('Ready for disbursement') }}</span><strong>{{ $readyForDisbursementCount }}</strong></li>
                        <li><span>{{ _lang('Pending finance requests') }}</span><strong>{{ $pendingFinanceRequestsCount }}</strong></li>
                        <li><span>{{ _lang('Pending bank transactions') }}</span><strong>{{ $pendingBankTransactionsCount }}</strong></li>
                        <li><span>{{ _lang('Pending cash transactions') }}</span><strong>{{ $pendingCashTransactionsCount }}</strong></li>
                        <li><span>{{ _lang('Broken promises') }}</span><strong>{{ $brokenPromisesCount }}</strong></li>
                        <li><span>{{ _lang('Recovered in range') }}</span><strong>{{ $recoveredInRangeCount }}</strong></li>
                        <li><span>{{ _lang('Promise kept') }}</span><strong>{{ $promiseKeptCount }}</strong></li>
                    </ul>
                    <div class="workspace-section-title mt-4">{{ _lang('Branch Collections Pressure') }}</div>
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered table-striped table-export dashboard-table-compact workspace-mini-table action-center-data-table mb-0" data-export-filename="Action_Center_Branch_Collections_Pressure">
                            <thead>
                                <tr>
                                    <th>{{ _lang('Branch') }}</th>
                                    <th>{{ _lang('Due Today') }}</th>
                                    <th>{{ _lang('Overdue') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($branchCollectionsPressure as $branch)
                                    <tr>
                                        <td>{{ $branch->name }}</td>
                                        <td>{{ $branch->due_today }}</td>
                                        <td>{{ $branch->overdue }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center text-muted">{{ _lang('No branch collections pressure found') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="workspace-section-title">{{ _lang('Branch Follow-up Performance') }}</div>
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered table-striped table-export dashboard-table-compact workspace-mini-table action-center-data-table mb-0" data-export-filename="Action_Center_Branch_Follow_Up_Performance">
                            <thead>
                                <tr>
                                    <th>{{ _lang('Branch') }}</th>
                                    <th>{{ _lang('Open Queue') }}</th>
                                    <th>{{ _lang('Touched') }}</th>
                                    <th>{{ _lang('Resolved') }}</th>
                                    <th>{{ _lang('Completion') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($branchFollowUpPerformance as $branch)
                                    <tr>
                                        <td>{{ $branch->name }}</td>
                                        <td>{{ $branch->open_queue }}</td>
                                        <td>{{ $branch->touched_today }}</td>
                                        <td>{{ $branch->resolved_in_range }}</td>
                                        <td><span class="workspace-status-chip {{ $branch->completion_rate >= 70 ? 'active' : ($branch->completion_rate >= 40 ? 'review' : 'critical') }}">{{ $branch->completion_rate }}%</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted">{{ _lang('No branch follow-up performance yet') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="workspace-section-title">{{ _lang('Collector Follow-up Performance') }}</div>
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered table-striped table-export dashboard-table-compact workspace-mini-table action-center-data-table mb-0" data-export-filename="Action_Center_Collector_Follow_Up_Performance">
                            <thead>
                                <tr>
                                    <th>{{ _lang('User') }}</th>
                                    <th>{{ _lang('Logs') }}</th>
                                    <th>{{ _lang('Cases') }}</th>
                                    <th>{{ _lang('Resolved') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($collectorFollowUpPerformance as $collector)
                                    <tr>
                                        <td>{{ $collector->name }}</td>
                                        <td>{{ $collector->logs_count }}</td>
                                        <td>{{ $collector->cases_touched }}</td>
                                        <td>{{ $collector->resolved_count }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted">{{ _lang('No collector follow-up activity yet') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="workspace-section-title">{{ _lang('Recent Resolutions') }}</div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-export dashboard-table-compact workspace-mini-table action-center-data-table mb-0" data-export-filename="Action_Center_Recent_Resolutions">
                            <thead>
                                <tr>
                                    <th>{{ _lang('Loan ID') }}</th>
                                    <th>{{ _lang('Borrower') }}</th>
                                    <th>{{ _lang('Paid On') }}</th>
                                    <th>{{ _lang('Resolution') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentResolvedCases as $item)
                                    <tr>
                                        <td>{{ $item->loan_id }}</td>
                                        <td>{{ $item->borrower_name }}</td>
                                        <td>{{ $item->payment_date }}</td>
                                        <td><span class="workspace-status-chip {{ $item->resolution_theme }}">{{ $item->resolution_label }}</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted">{{ _lang('No resolved follow-up cases found for this range') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-lg-7">
                    <div class="workspace-section-title">{{ _lang('Collections Priority Queue') }}</div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-export dashboard-table-compact workspace-mini-table action-center-data-table mb-0" data-export-filename="Action_Center_Collections_Priority_Queue">
                            <thead>
                                <tr>
                                    <th>{{ _lang('Loan ID') }}</th>
                                    <th>{{ _lang('Borrower') }}</th>
                                    <th>{{ _lang('Branch') }}</th>
                                    <th>{{ _lang('Phone') }}</th>
                                    <th>{{ _lang('Aging') }}</th>
                                    <th>{{ _lang('Last Follow-up') }}</th>
                                    <th>{{ _lang('Next Action') }}</th>
                                    <th data-no-export="1">{{ _lang('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($collectionPriorityQueue as $item)
                                    <tr>
                                        <td>{{ $item->loan_id }}</td>
                                        <td>{{ $item->borrower_name }}</td>
                                        <td>{{ $item->branch_name }}</td>
                                        <td>{{ trim($item->contact_phone) != '' ? $item->contact_phone : _lang('N/A') }}</td>
                                        <td>{{ $item->days_late }} {{ _lang('days late') }}</td>
                                        <td>
                                            @if($item->last_outcome_label)
                                                <span class="workspace-status-chip {{ $item->last_outcome_theme }}">{{ $item->last_outcome_label }}</span>
                                            @else
                                                <span class="text-muted small">{{ _lang('No log yet') }}</span>
                                            @endif
                                        </td>
                                        <td><span class="workspace-status-chip {{ $item->action_theme }}">{{ $item->action_label }}</span></td>
                                        <td>@include('backend.admin.partials.table-actions', ['items' => [['label' => _lang('Log Follow-up'), 'url' => route('loan_collection_follow_ups.create', $item->repayment_id), 'icon' => 'ti-write', 'class' => 'ajax-modal', 'data_title' => _lang('Log Collection Follow-up')]]])</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="8" class="text-center text-muted">{{ _lang('No overdue repayments') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="workspace-section-title mt-4">{{ _lang('Promise Follow-up Queue') }}</div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-export dashboard-table-compact workspace-mini-table action-center-data-table mb-0" data-export-filename="Action_Center_Promise_Follow_Up_Queue">
                            <thead>
                                <tr>
                                    <th>{{ _lang('Loan ID') }}</th>
                                    <th>{{ _lang('Borrower') }}</th>
                                    <th>{{ _lang('Promise Date') }}</th>
                                    <th>{{ _lang('Status') }}</th>
                                    <th data-no-export="1">{{ _lang('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($promiseFollowUpQueue as $item)
                                    <tr>
                                        <td>{{ $item->loan_id }}</td>
                                        <td>{{ $item->borrower_name }}</td>
                                        <td>{{ $item->promised_payment_date }}</td>
                                        <td><span class="workspace-status-chip {{ $item->promise_status_theme }}">{{ $item->promise_status_label }}</span></td>
                                        <td>@include('backend.admin.partials.table-actions', ['items' => [['label' => _lang('Log Follow-up'), 'url' => route('loan_collection_follow_ups.create', $item->repayment_id), 'icon' => 'ti-write', 'class' => 'ajax-modal', 'data_title' => _lang('Log Collection Follow-up')]]])</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted">{{ _lang('No promise follow-up items found') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js-script')
<script>
    (function ($) {
        function slugTableTitle(text, fallback) {
            var clean = $.trim($('<div>').html(text || '').text()).replace(/\s+/g, '_').replace(/[^\w]+/g, '_').replace(/^_+|_+$/g, '');
            return clean ? 'Action_Center_' + clean : fallback;
        }

        function prepareEmptyTable($table) {
            var $rows = $table.find('tbody tr');
            var $emptyCell = $rows.length === 1 ? $rows.first().find('td[colspan]').first() : $();

            if ($emptyCell.length) {
                $table.attr('data-empty-message', $.trim($emptyCell.text()));
                $rows.remove();
            }
        }

        function tableTitle($table, index) {
            var explicit = $table.data('exportFilename');
            if (explicit) {
                return explicit;
            }

            var title = $table.closest('.col-lg-7, .col-lg-6, .col-lg-5, .col-lg-4, .col-md-4, .tab-pane').find('.workspace-section-title, h6').first().text();
            if (!title) {
                title = $table.closest('.tab-pane').attr('id') || 'Queue_' + (index + 1);
            }

            return slugTableTitle(title, 'Action_Center_Table_' + (index + 1));
        }

        function buildActionCenterToolbar(api, $table, exportTitle) {
            var $wrapper = $(api.table().container());
            var $left = $wrapper.find('.admin-datatable-top-left');
            var $right = $wrapper.find('.admin-datatable-top-right');
            var $top = $wrapper.find('.admin-datatable-top');
            var $length = $left.find('.dataTables_length').detach();
            var $search = $right.find('.dataTables_filter').detach();
            var unique = $table.attr('id');

            var $toolbarLeft = $('<div class="dashboard-proof-top-left"></div>');
            var $toolbarRight = $('<div class="dashboard-proof-top-right"></div>');
            var $columnsDropdown = $(
                '<div class="dropdown dashboard-columns-dropdown">' +
                    '<button type="button" class="btn btn-xs admin-dt-btn admin-dt-btn-ghost dashboard-columns-trigger" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">' +
                        '<i class="ti-layout-column2"></i><span>{{ _lang('Columns') }}</span><i class="fas fa-chevron-down dashboard-columns-chevron"></i>' +
                    '</button>' +
                    '<div class="dropdown-menu dropdown-menu-right dashboard-columns-menu"></div>' +
                '</div>'
            );
            var $columnsMenu = $columnsDropdown.find('.dashboard-columns-menu');

            api.columns().every(function (columnIndex) {
                var column = this;
                var $header = $(column.header());
                var label = $header.text().trim();
                var isLocked = $header.attr('data-no-export') === '1' || label === '{{ _lang('Action') }}';

                if (!label || isLocked) {
                    return;
                }

                var itemId = unique + '-column-toggle-' + columnIndex;
                var $item = $(
                    '<label class="dropdown-item dashboard-columns-item" for="' + itemId + '">' +
                        '<span class="dashboard-columns-label">' + label + '</span>' +
                        '<input type="checkbox" class="dashboard-columns-checkbox" id="' + itemId + '"' + (column.visible() ? ' checked' : '') + '>' +
                    '</label>'
                );

                $item.on('click', function (event) {
                    event.stopPropagation();
                });

                $item.find('.dashboard-columns-checkbox').on('change', function () {
                    column.visible($(this).is(':checked'));
                    api.columns.adjust();
                });

                $columnsMenu.append($item);
            });

            $columnsMenu.on('click', function (event) {
                event.stopPropagation();
            });

            $toolbarLeft.append($('<div class="dashboard-toolbar-item dashboard-toolbar-item-length"></div>').append($length));
            $toolbarRight
                .append($('<div class="dashboard-toolbar-item dashboard-toolbar-item-columns"></div>').append($columnsDropdown))
                .append($('<div class="dashboard-toolbar-item dashboard-toolbar-item-search"></div>').append($search));

            $top.empty().append($toolbarLeft, $toolbarRight);
            $search.find('input').attr('placeholder', '{{ _lang('Search records') }}');
        }

        $('.action-center-data-table').each(function (index) {
            var $table = $(this);
            var exportTitle = tableTitle($table, index);
            var columnCount = $table.find('thead th').length;

            $table.attr('id', $table.attr('id') || 'action-center-table-' + index);
            $table.attr('data-export-filename', exportTitle);
            $table.css('min-width', '');
            prepareEmptyTable($table);

            if (typeof window.cavicAdminDataTable === 'function') {
                window.cavicAdminDataTable('#' + $table.attr('id'), {
                    responsive: false,
                    paging: true,
                    searching: true,
                    info: true,
                    ordering: false,
                    lengthChange: true,
                    pageLength: 6,
                    lengthMenu: [[6, 10, 25, 50, 100], [6, 10, 25, 50, 100]],
                    buttons: [],
                    language: {
                        info: '{{ _lang('Viewing') }} _START_-_END_ {{ _lang('of') }} _TOTAL_',
                        infoEmpty: '{{ _lang('Viewing 0-0 of 0') }}',
                        search: '',
                        searchPlaceholder: '{{ _lang('Search records') }}',
                        lengthMenu: '_MENU_',
                        zeroRecords: '{{ _lang('No matching records found') }}',
                        emptyTable: $table.attr('data-empty-message') || '{{ _lang('No Data Available') }}',
                        paginate: {
                            previous: '<i class="fas fa-angle-left"></i>',
                            next: '<i class="fas fa-angle-right"></i>'
                        }
                    },
                    initComplete: function () {
                        buildActionCenterToolbar(this.api(), $table, exportTitle);
                    }
                });
            }
        });

        $(document).on('shown.bs.tab', 'a[data-toggle="tab"]', function () {
            if ($.fn.DataTable) {
                $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
            }
        });
    })(window.jQuery || window.$);

    $(document).on('click', '.action-center-tab-link', function (event) {
        var target = $(this).attr('href');
        if (! target || target.charAt(0) !== '#') {
            return;
        }

        event.preventDefault();
        $('.workspace-module-tabs a[href="' + target + '"], .admin-dashboard-top-tabs a[href="' + target + '"]').first().tab('show');
        var $target = $(target);
        if ($target.length) {
            $('html, body').animate({ scrollTop: $target.closest('.workspace-section-card').offset().top - 90 }, 200);
        }
    });
</script>
@endsection

