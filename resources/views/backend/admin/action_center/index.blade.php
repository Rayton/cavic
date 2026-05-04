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
    .action-exception-hero {
        border: 1px solid #dfe9e8;
        border-radius: 14px;
        background: linear-gradient(135deg, #f8fbfa 0%, #eef7f6 100%);
        padding: 1rem;
        margin-bottom: 1rem;
    }
    .action-exception-hero-title {
        color: #213039;
        font-size: 1.05rem;
        font-weight: 800;
        margin-bottom: .25rem;
    }
    .action-exception-hero-copy {
        color: #66717a;
        font-size: .84rem;
        margin: 0;
    }
    .action-exception-score {
        align-items: center;
        background: #fff;
        border: 1px solid #dce8e7;
        border-radius: 12px;
        display: inline-flex;
        gap: .75rem;
        justify-content: flex-end;
        min-width: 220px;
        padding: .75rem .9rem;
    }
    .action-exception-score-value {
        color: var(--cavic-primary-dark, #32555a);
        font-size: 1.8rem;
        font-weight: 800;
        line-height: 1;
    }
    .action-exception-score-label {
        color: #66717a;
        font-size: .76rem;
        font-weight: 700;
        line-height: 1.25;
        text-transform: uppercase;
    }
    .action-exception-signals {
        display: grid;
        gap: .85rem;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        margin-bottom: 1.1rem;
    }
    .action-signal-card {
        border: 1px solid #e2eceb;
        border-radius: 12px;
        background: #fff;
        min-height: 132px;
        padding: .95rem;
    }
    .action-signal-card.critical { border-color: #f1c7cc; background: #fff8f8; }
    .action-signal-card.warning { border-color: #f3ddb8; background: #fffaf1; }
    .action-signal-card.recovery { border-color: #c9e6d3; background: #f7fcf8; }
    .action-signal-card.info { border-color: #cce3e8; background: #f6fbfc; }
    .action-signal-top {
        align-items: flex-start;
        display: flex;
        gap: .7rem;
        justify-content: space-between;
    }
    .action-signal-icon {
        align-items: center;
        border-radius: 11px;
        display: inline-flex;
        flex: 0 0 36px;
        height: 36px;
        justify-content: center;
        width: 36px;
        background: rgba(63, 104, 109, .1);
        color: var(--cavic-primary-dark, #32555a);
    }
    .action-signal-card.critical .action-signal-icon { background: #f8d7da; color: #721c24; }
    .action-signal-card.warning .action-signal-icon { background: #ffe8bd; color: #8a5300; }
    .action-signal-card.recovery .action-signal-icon { background: #d9f1df; color: #155724; }
    .action-signal-card.info .action-signal-icon { background: #d9eef2; color: #0c5460; }
    .action-signal-value {
        color: #213039;
        font-size: 1.45rem;
        font-weight: 800;
        line-height: 1;
        text-align: right;
    }
    .action-signal-label {
        color: #25333b;
        font-size: .86rem;
        font-weight: 800;
        margin-top: .75rem;
    }
    .action-signal-meta {
        color: #6f787f;
        font-size: .76rem;
        line-height: 1.35;
        margin-top: .25rem;
    }
    .action-aging-board {
        border: 1px solid #e2eceb;
        border-radius: 12px;
        background: #fff;
        padding: 1rem;
        margin-bottom: 1.1rem;
    }
    .action-aging-grid {
        display: grid;
        gap: .75rem;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        margin-top: .75rem;
    }
    .action-aging-item {
        border-radius: 10px;
        background: #f8faf9;
        padding: .8rem;
    }
    .action-aging-top {
        align-items: center;
        display: flex;
        justify-content: space-between;
        gap: .5rem;
        margin-bottom: .55rem;
    }
    .action-aging-label {
        color: #46515a;
        font-size: .78rem;
        font-weight: 800;
    }
    .action-aging-count {
        color: #213039;
        font-weight: 800;
    }
    .action-aging-track {
        background: #e8eeee;
        border-radius: 999px;
        height: 8px;
        overflow: hidden;
    }
    .action-aging-fill {
        background: var(--cavic-primary, #3f686d);
        border-radius: inherit;
        display: block;
        height: 100%;
        min-width: 8px;
    }
    .action-aging-item.warning .action-aging-fill { background: #d99032; }
    .action-aging-item.danger .action-aging-fill { background: #b94a54; }
    .action-aging-item.dark .action-aging-fill { background: #5e343a; }
    .action-pressure-grid {
        display: grid;
        gap: .75rem;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        margin-bottom: 1rem;
    }
    .action-pressure-card {
        align-items: center;
        border: 1px solid #e2eceb;
        border-radius: 12px;
        background: #fff;
        display: flex;
        gap: .75rem;
        min-height: 82px;
        padding: .85rem;
    }
    .action-pressure-dot {
        border-radius: 50%;
        flex: 0 0 12px;
        height: 12px;
        width: 12px;
        background: var(--cavic-primary, #3f686d);
    }
    .action-pressure-card.critical .action-pressure-dot { background: #b94a54; }
    .action-pressure-card.warning .action-pressure-dot { background: #d99032; }
    .action-pressure-card.info .action-pressure-dot { background: #388b9a; }
    .action-pressure-card.success .action-pressure-dot { background: #3f8f5f; }
    .action-pressure-title {
        color: #25333b;
        font-size: .8rem;
        font-weight: 800;
    }
    .action-pressure-meta {
        color: #6f787f;
        font-size: .74rem;
        margin-top: .15rem;
    }
    .action-pressure-value {
        color: #213039;
        font-size: 1.2rem;
        font-weight: 800;
        margin-left: auto;
    }
    .action-member-hero {
        border: 1px solid #dfe9e8;
        border-radius: 14px;
        background: linear-gradient(135deg, #f8fbfa 0%, #edf7f4 100%);
        padding: 1rem;
        margin-bottom: 1rem;
    }
    .action-member-title {
        color: #213039;
        font-size: 1.05rem;
        font-weight: 800;
        margin-bottom: .25rem;
    }
    .action-member-copy {
        color: #66717a;
        font-size: .84rem;
        margin: 0;
        max-width: 720px;
    }
    .action-member-score {
        align-items: center;
        background: #fff;
        border: 1px solid #dce8e7;
        border-radius: 12px;
        display: inline-flex;
        gap: .75rem;
        min-width: 220px;
        padding: .75rem .9rem;
    }
    .action-member-score-value {
        color: var(--cavic-primary-dark, #32555a);
        font-size: 1.8rem;
        font-weight: 800;
        line-height: 1;
    }
    .action-member-score-label {
        color: #66717a;
        font-size: .76rem;
        font-weight: 700;
        line-height: 1.25;
        text-transform: uppercase;
    }
    .action-member-signals {
        display: grid;
        gap: .85rem;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        margin-bottom: 1rem;
    }
    .action-member-signal {
        align-items: center;
        background: #fff;
        border: 1px solid #e2eceb;
        border-radius: 12px;
        display: flex;
        gap: .75rem;
        min-height: 88px;
        padding: .85rem;
    }
    .action-member-signal-icon {
        align-items: center;
        background: rgba(63, 104, 109, .1);
        border-radius: 11px;
        color: var(--cavic-primary-dark, #32555a);
        display: inline-flex;
        flex: 0 0 36px;
        height: 36px;
        justify-content: center;
        width: 36px;
    }
    .action-member-signal.warning .action-member-signal-icon { background: #ffe8bd; color: #8a5300; }
    .action-member-signal.info .action-member-signal-icon { background: #d9eef2; color: #0c5460; }
    .action-member-signal.success .action-member-signal-icon { background: #d9f1df; color: #155724; }
    .action-member-signal-value {
        color: #213039;
        font-size: 1.2rem;
        font-weight: 800;
        line-height: 1;
    }
    .action-member-signal-label {
        color: #25333b;
        font-size: .8rem;
        font-weight: 800;
        margin-top: .2rem;
    }
    .action-member-signal-meta {
        color: #6f787f;
        font-size: .72rem;
        line-height: 1.3;
        margin-top: .15rem;
    }
    .action-member-table-header {
        align-items: center;
        display: flex;
        flex-wrap: wrap;
        gap: .7rem;
        justify-content: space-between;
        margin: .25rem 0 .65rem;
    }
    .action-member-name {
        color: #25333b;
        font-weight: 800;
    }
    .action-member-subline {
        color: #7a848c;
        font-size: .74rem;
        font-weight: 500;
        margin-top: .15rem;
    }
    .action-member-branch-chip {
        background: #eef7f6;
        border: 1px solid #d5e8e6;
        border-radius: 999px;
        color: var(--cavic-primary-dark, #32555a);
        display: inline-flex;
        font-size: .72rem;
        font-weight: 800;
        padding: .22rem .55rem;
    }
    @media (max-width: 1199.98px) {
        .action-exception-signals { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .action-member-signals { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 767.98px) {
        .action-exception-score { justify-content: flex-start; margin-top: .75rem; width: 100%; }
        .action-member-score { justify-content: flex-start; margin-top: .75rem; width: 100%; }
        .action-exception-signals,
        .action-member-signals,
        .action-aging-grid,
        .action-pressure-grid { grid-template-columns: 1fr; }
    }
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

<div class="card workspace-section-card action-center-table-actions dashboard-proof-datatable-card">
    <div class="card-body tab-content">
        <div class="tab-pane fade show active" id="member-requests">
            @php
                $visibleMemberRequests = $memberRequests->count();
                $memberRequestBranches = $memberRequests->map(function ($member) {
                    return optional($member->branch)->name;
                })->filter()->unique()->count();
                $oldestMemberRequest = $memberRequests->sortBy('created_at')->first();
                $oldestMemberWaiting = optional(optional($oldestMemberRequest)->created_at)->diffForHumans() ?? _lang('N/A');
                $financeRequestsCount = $depositRequestsCount + $withdrawRequestsCount;
                $collectionPressureCount = $dueTodayCount + $overdueRepaymentsCount;
            @endphp
            <div class="action-member-hero">
                <div class="d-flex flex-wrap align-items-center justify-content-between">
                    <div>
                        <div class="action-member-title">{{ _lang('Member Onboarding Queue') }}</div>
                        <p class="action-member-copy">{{ _lang('Pending onboarding is shown with the wider approval, finance, and collections load so the day starts with the right priorities.') }}</p>
                    </div>
                    <div class="action-member-score">
                        <div>
                            <div class="action-member-score-label">{{ _lang('Awaiting decision') }}</div>
                            <div class="small text-muted">{{ _lang('Oldest') }}: {{ $oldestMemberWaiting }}</div>
                        </div>
                        <div class="action-member-score-value">{{ $memberRequestsCount }}</div>
                    </div>
                </div>
            </div>
            <div class="action-member-signals">
                <div class="action-member-signal warning">
                    <span class="action-member-signal-icon"><i class="fas fa-user-clock"></i></span>
                    <div>
                        <div class="action-member-signal-value">{{ $memberRequestsCount }}</div>
                        <div class="action-member-signal-label">{{ _lang('Member Requests') }}</div>
                        <div class="action-member-signal-meta">{{ $visibleMemberRequests }} {{ _lang('visible') }} | {{ $memberRequestBranches }} {{ _lang('branches') }}</div>
                    </div>
                </div>
                <div class="action-member-signal info">
                    <span class="action-member-signal-icon"><i class="fas fa-file-signature"></i></span>
                    <div>
                        <div class="action-member-signal-value">{{ $pendingLoansCount }}</div>
                        <div class="action-member-signal-label">{{ _lang('Pending Loans') }}</div>
                        <div class="action-member-signal-meta">{{ _lang('Applications waiting in the loan pipeline') }}</div>
                    </div>
                </div>
                <div class="action-member-signal success">
                    <span class="action-member-signal-icon"><i class="fas fa-wallet"></i></span>
                    <div>
                        <div class="action-member-signal-value">{{ $financeRequestsCount }}</div>
                        <div class="action-member-signal-label">{{ _lang('Finance Requests') }}</div>
                        <div class="action-member-signal-meta">{{ $depositRequestsCount }} {{ _lang('deposits') }} | {{ $withdrawRequestsCount }} {{ _lang('withdraws') }}</div>
                    </div>
                </div>
                <div class="action-member-signal">
                    <span class="action-member-signal-icon"><i class="fas fa-exclamation-triangle"></i></span>
                    <div>
                        <div class="action-member-signal-value">{{ $collectionPressureCount }}</div>
                        <div class="action-member-signal-label">{{ _lang('Collections Pressure') }}</div>
                        <div class="action-member-signal-meta">{{ $dueTodayCount }} {{ _lang('due today') }} | {{ $overdueRepaymentsCount }} {{ _lang('overdue') }} | {{ $criticalCollectionsCount }} {{ _lang('critical') }}</div>
                    </div>
                </div>
            </div>
            <div class="action-member-table-header">
                <div>
                    <div class="workspace-section-title mb-1">{{ _lang('Review Queue') }}</div>
                    <div class="small text-muted">{{ _lang('Approval readiness, contact details, and branch context in one queue.') }}</div>
                </div>
                <span class="workspace-status-chip pending">{{ $memberRequestsCount }} {{ _lang('pending') }}</span>
            </div>
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
                                <td>
                                    <div class="action-member-name">{{ $member->name }}</div>
                                    <div class="action-member-subline">
                                        {{ trim(($member->country_code ?? '') . ' ' . ($member->mobile ?? '')) ?: ($member->email ?? _lang('No contact listed')) }}
                                    </div>
                                </td>
                                <td>{{ trim((string) $member->member_no) !== '' ? $member->member_no : _lang('Unassigned') }}</td>
                                <td><span class="action-member-branch-chip">{{ optional($member->branch)->name ?? _lang('No Branch') }}</span></td>
                                <td>
                                    <span class="workspace-status-chip pending">{{ _lang('Pending Approval') }}</span>
                                    <div class="action-member-subline">{{ optional($member->created_at)->diffForHumans() }}</div>
                                </td>
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
            @php
                $totalAgingBucketCount = max(1, collect($collectionBuckets)->sum('count'));
                $financeExceptionTotal = $pendingFinanceRequestsCount + $pendingBankTransactionsCount + $pendingCashTransactionsCount;
                $followUpActivityTotal = $followUpsLoggedToday + $resolvedInRangeCount + $recoveredInRangeCount;
            @endphp
            <div class="action-exception-hero">
                <div class="d-flex flex-wrap align-items-center justify-content-between">
                    <div>
                        <div class="action-exception-hero-title">{{ _lang('Exception Control Board') }}</div>
                        <p class="action-exception-hero-copy">{{ _lang('Focus first on overdue exposure, broken promises, blocked finance work, and recovery progress for the selected range.') }}</p>
                    </div>
                    <div class="action-exception-score">
                        <div>
                            <div class="action-exception-score-label">{{ _lang('Priority load') }}</div>
                            <div class="small text-muted">{{ $collectionDateRange['label'] ?? _lang('Today') }}</div>
                        </div>
                        <div class="action-exception-score-value">{{ $exceptionCount }}</div>
                    </div>
                </div>
            </div>
            @unless($followUpsEnabled)
                <div class="alert alert-info small">
                    <strong>{{ _lang('Follow-up tracking setup') }}:</strong> {{ _lang('Run the latest migration to unlock saved collection outcomes, broken-promise tracking, and branch completion analytics.') }}
                </div>
            @endunless
            <div class="action-exception-signals">
                <div class="action-signal-card critical">
                    <div class="action-signal-top">
                        <span class="action-signal-icon"><i class="fas fa-exclamation-triangle"></i></span>
                        <span class="action-signal-value">{{ $overdueRepaymentsCount }}</span>
                    </div>
                    <div class="action-signal-label">{{ _lang('Overdue Exposure') }}</div>
                    <div class="action-signal-meta">{{ _lang('Repayments already past due and needing collection action.') }}</div>
                </div>
                <div class="action-signal-card warning">
                    <div class="action-signal-top">
                        <span class="action-signal-icon"><i class="fas fa-calendar-times"></i></span>
                        <span class="action-signal-value">{{ $brokenPromisesCount }}</span>
                    </div>
                    <div class="action-signal-label">{{ _lang('Broken Promises') }}</div>
                    <div class="action-signal-meta">{{ _lang('Promises to pay that are past commitment date and still open.') }}</div>
                </div>
                <div class="action-signal-card info">
                    <div class="action-signal-top">
                        <span class="action-signal-icon"><i class="fas fa-random"></i></span>
                        <span class="action-signal-value">{{ $financeExceptionTotal }}</span>
                    </div>
                    <div class="action-signal-label">{{ _lang('Finance Blockers') }}</div>
                    <div class="action-signal-meta">{{ _lang('Pending finance, bank, and cash items waiting for operational clearing.') }}</div>
                </div>
                <div class="action-signal-card recovery">
                    <div class="action-signal-top">
                        <span class="action-signal-icon"><i class="fas fa-check-circle"></i></span>
                        <span class="action-signal-value">{{ $collectionCompletionRate }}%</span>
                    </div>
                    <div class="action-signal-label">{{ _lang('Follow-up Coverage') }}</div>
                    <div class="action-signal-meta">{{ _lang('Open due and overdue cases touched in the selected range.') }}</div>
                </div>
            </div>

            <div class="action-aging-board">
                <div class="d-flex flex-wrap align-items-center justify-content-between">
                    <div>
                        <div class="workspace-section-title mb-1">{{ _lang('Collections Aging') }}</div>
                        <div class="small text-muted">{{ _lang('Use this to spot whether pressure is fresh, maturing, or already critical.') }}</div>
                    </div>
                    <span class="workspace-status-chip {{ $criticalCollectionsCount > 0 ? 'critical' : 'active' }}">{{ $criticalCollectionsCount }} {{ _lang('critical') }}</span>
                </div>
                <div class="action-aging-grid">
                    @foreach($collectionBuckets as $bucket)
                        @php
                            $bucketPercent = min(100, round(($bucket->count / $totalAgingBucketCount) * 100));
                            $bucketClass = $bucket->theme === 'danger' ? 'danger' : ($bucket->theme === 'dark' ? 'dark' : 'warning');
                        @endphp
                        <div class="action-aging-item {{ $bucketClass }}">
                            <div class="action-aging-top">
                                <span class="action-aging-label">{{ $bucket->label }}</span>
                                <span class="action-aging-count">{{ $bucket->count }}</span>
                            </div>
                            <div class="action-aging-track"><span class="action-aging-fill" style="width: {{ max(4, $bucketPercent) }}%;"></span></div>
                            <div class="small text-muted mt-2">{{ decimalPlace($bucket->amount ?? 0) }}</div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="action-pressure-grid">
                <div class="action-pressure-card critical">
                    <span class="action-pressure-dot"></span>
                    <div>
                        <div class="action-pressure-title">{{ _lang('Ready for Disbursement') }}</div>
                        <div class="action-pressure-meta">{{ _lang('Approved loans waiting for release') }}</div>
                    </div>
                    <div class="action-pressure-value">{{ $readyForDisbursementCount }}</div>
                </div>
                <div class="action-pressure-card warning">
                    <span class="action-pressure-dot"></span>
                    <div>
                        <div class="action-pressure-title">{{ _lang('Pending Finance Requests') }}</div>
                        <div class="action-pressure-meta">{{ _lang('Deposit and withdrawal work still pending') }}</div>
                    </div>
                    <div class="action-pressure-value">{{ $pendingFinanceRequestsCount }}</div>
                </div>
                <div class="action-pressure-card info">
                    <span class="action-pressure-dot"></span>
                    <div>
                        <div class="action-pressure-title">{{ _lang('Transaction Exceptions') }}</div>
                        <div class="action-pressure-meta">{{ _lang('Bank and cash transactions needing attention') }}</div>
                    </div>
                    <div class="action-pressure-value">{{ $pendingBankTransactionsCount + $pendingCashTransactionsCount }}</div>
                </div>
                <div class="action-pressure-card success">
                    <span class="action-pressure-dot"></span>
                    <div>
                        <div class="action-pressure-title">{{ _lang('Recovery Activity') }}</div>
                        <div class="action-pressure-meta">{{ _lang('Follow-ups, recoveries, and resolutions in range') }}</div>
                    </div>
                    <div class="action-pressure-value">{{ $followUpActivityTotal }}</div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-5 mb-4 mb-lg-0">
                    <div class="workspace-section-title">{{ _lang('Branch Collections Pressure') }}</div>
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
                    '<button type="button" class="btn btn-xs admin-dt-btn admin-dt-btn-ghost dashboard-columns-trigger" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">' +
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

