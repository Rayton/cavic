@extends('layouts.app')

@section('workspace_top_tabs')
@include('backend.admin.partials.module-tabs', [
    'variant' => 'top-strip',
    'role' => 'navigation',
    'tabs' => [
        ['label' => _lang('Executive KPIs'), 'target' => '#executive', 'active' => true],
        ['label' => _lang('Portfolio & Loans'), 'target' => '#portfolio'],
        ['label' => _lang('Accounts'), 'target' => '#accounts'],
        ['label' => _lang('Transactions & Expenses'), 'target' => '#transactions'],
        ['label' => _lang('Banking & Revenue'), 'target' => '#banking'],
    ],
])
@endsection

@section('content')
@php
    $reportCounts = [
        'executive' => count($reportGroups['executive']['items'] ?? []),
        'portfolio' => count($reportGroups['portfolio']['items'] ?? []),
        'accounts' => count($reportGroups['accounts']['items'] ?? []),
        'transactions' => count($reportGroups['transactions']['items'] ?? []),
        'banking' => count($reportGroups['banking']['items'] ?? []),
    ];
@endphp
@include('backend.admin.partials.workspace-styles')

@include('backend.admin.partials.page-header', [
    'title' => _lang('Reports Center'),
    'subtitle' => _lang('Open all major CAVIC reports from one place, grouped by business question instead of scattered menu links.'),
    'badge' => _lang('Centralized Reporting'),
    'breadcrumbs' => [
        ['label' => _lang('Dashboard'), 'url' => route('dashboard.index')],
        ['label' => _lang('Reports Center'), 'active' => true],
    ],
])

<div class="row mb-4">
    <div class="col-md-4 col-xl mb-3">
        <div class="card workspace-stat-card mb-0"><div class="card-body"><div class="stat-label">{{ _lang('Executive Reports') }}</div><div class="stat-value">{{ $reportCounts['executive'] }}</div><div class="text-muted small">{{ _lang('Cash and KPI summaries') }}</div></div></div>
    </div>
    <div class="col-md-4 col-xl mb-3">
        <div class="card workspace-stat-card mb-0"><div class="card-body"><div class="stat-label">{{ _lang('Portfolio Reports') }}</div><div class="stat-value">{{ $reportCounts['portfolio'] }}</div><div class="text-muted small">{{ _lang('Loan, due, and repayment views') }}</div></div></div>
    </div>
    <div class="col-md-4 col-xl mb-3">
        <div class="card workspace-stat-card mb-0"><div class="card-body"><div class="stat-label">{{ _lang('Operational Reports') }}</div><div class="stat-value">{{ $reportCounts['accounts'] + $reportCounts['transactions'] + $reportCounts['banking'] }}</div><div class="text-muted small">{{ _lang('Accounts, transactions, expenses, and banking') }}</div></div></div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-3 mb-3"><div class="card workspace-bucket-card mb-0"><div class="card-body"><div class="bucket-label">{{ _lang('Active Members') }}</div><div class="bucket-value">{{ number_format($reportHighlights['active_members'] ?? 0) }}</div><div class="bucket-meta">{{ _lang('Current member base for reporting') }}</div></div></div></div>
    <div class="col-md-3 mb-3"><div class="card workspace-bucket-card mb-0"><div class="card-body"><div class="bucket-label">{{ _lang('Active Loans') }}</div><div class="bucket-value">{{ number_format($reportHighlights['active_loans'] ?? 0) }}</div><div class="bucket-meta">{{ _lang('Portfolio currently active') }}</div></div></div></div>
    <div class="col-md-3 mb-3"><div class="card workspace-bucket-card mb-0"><div class="card-body"><div class="bucket-label">{{ _lang('Overdue Repayments') }}</div><div class="bucket-value">{{ number_format($reportHighlights['overdue_repayments'] ?? 0) }}</div><div class="bucket-meta">{{ _lang('Use due and collections reports for drill-down') }}</div></div></div></div>
    <div class="col-md-3 mb-3"><div class="card workspace-bucket-card mb-0"><div class="card-body"><div class="bucket-label">{{ _lang('Due Today') }}</div><div class="bucket-value">{{ number_format($reportHighlights['due_today'] ?? 0) }}</div><div class="bucket-meta">{{ _lang('Immediate repayment schedule pressure') }}</div></div></div></div>
    <div class="col-md-3 mb-3"><div class="card workspace-bucket-card mb-0"><div class="card-body"><div class="bucket-label">{{ _lang('Transactions This Month') }}</div><div class="bucket-value">{{ number_format($reportHighlights['transactions_this_month'] ?? 0) }}</div><div class="bucket-meta">{{ _lang('Movement volume for current reporting period') }}</div></div></div></div>
    <div class="col-md-3 mb-3"><div class="card workspace-bucket-card mb-0"><div class="card-body"><div class="bucket-label">{{ _lang('Expenses This Month') }}</div><div class="bucket-value">{{ number_format($reportHighlights['expenses_this_month'] ?? 0) }}</div><div class="bucket-meta">{{ _lang('Operational expense entries this month') }}</div></div></div></div>
    <div class="col-md-3 mb-3"><div class="card workspace-bucket-card mb-0"><div class="card-body"><div class="bucket-label">{{ _lang('Pending Bank Items') }}</div><div class="bucket-value">{{ number_format($reportHighlights['pending_bank_transactions'] ?? 0) }}</div><div class="bucket-meta">{{ _lang('Reconciliation items that still need review') }}</div></div></div></div>
    <div class="col-md-3 mb-3"><div class="card workspace-bucket-card mb-0"><div class="card-body"><div class="bucket-label">{{ _lang('Bank Accounts') }}</div><div class="bucket-value">{{ number_format($reportHighlights['bank_accounts'] ?? 0) }}</div><div class="bucket-meta">{{ _lang('Accounts available for bank reporting') }}</div></div></div></div>
</div>

<div class="row mb-4">
    <div class="col-lg-7 mb-3 mb-lg-0">
        <div class="card workspace-section-card mb-0 h-100">
            <div class="card-header"><span>{{ _lang('Branch Reporting Snapshot') }}</span></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered workspace-mini-table mb-0">
                        <thead>
                            <tr>
                                <th>{{ _lang('Branch') }}</th>
                                <th>{{ _lang('Active Members') }}</th>
                                <th>{{ _lang('Pending Members') }}</th>
                                <th>{{ _lang('Active Loans') }}</th>
                                <th>{{ _lang('Overdue Repayments') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($branchReportSnapshot as $branch)
                                <tr>
                                    <td>{{ $branch->name }}</td>
                                    <td>{{ $branch->active_members }}</td>
                                    <td>{{ $branch->pending_members }}</td>
                                    <td>{{ $branch->active_loans }}</td>
                                    <td>{{ $branch->overdue_repayments }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted">{{ _lang('No branch reporting data found') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card workspace-section-card mb-0 h-100">
            <div class="card-header"><span>{{ _lang('Recommended Reporting Workflow') }}</span></div>
            <div class="card-body">
                <ol class="text-muted small mb-0 pl-3">
                    <li class="mb-2">{{ _lang('Start with Executive KPIs for liquidity and revenue checks.') }}</li>
                    <li class="mb-2">{{ _lang('Move to Portfolio & Loans for due, overdue, and repayment analysis.') }}</li>
                    <li class="mb-2">{{ _lang('Use Transactions & Expenses to validate operational posting and spending.') }}</li>
                    <li>{{ _lang('Finish with Banking & Revenue to reconcile bank movement and fee outcomes.') }}</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="card workspace-section-card">
    <div class="card-body tab-content">
        <div class="tab-pane fade show active" id="executive">
            <p class="text-muted">{{ _lang('Use these reports for management-level visibility into cash and high-level financial performance.') }}</p>
            <div class="list-group workspace-link-list">
                @foreach(($reportGroups['executive']['items'] ?? []) as $item)
                    <a class="list-group-item list-group-item-action" href="{{ $item['route'] }}">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>{{ $item['label'] }}</span>
                            <i class="ti-arrow-right"></i>
                        </div>
                        <div class="small text-muted mt-1">{{ $item['description'] ?? '' }}</div>
                    </a>
                @endforeach
            </div>
        </div>
        <div class="tab-pane fade" id="portfolio">
            <p class="text-muted">{{ _lang('Track portfolio health, repayments, and due positions.') }}</p>
            <div class="list-group workspace-link-list">
                @foreach(($reportGroups['portfolio']['items'] ?? []) as $item)
                    <a class="list-group-item list-group-item-action" href="{{ $item['route'] }}">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>{{ $item['label'] }}</span>
                            <i class="ti-arrow-right"></i>
                        </div>
                        <div class="small text-muted mt-1">{{ $item['description'] ?? '' }}</div>
                    </a>
                @endforeach
            </div>
        </div>
        <div class="tab-pane fade" id="accounts">
            <p class="text-muted">{{ _lang('Review member account activity, balances, and cash position.') }}</p>
            <div class="list-group workspace-link-list">
                @foreach(($reportGroups['accounts']['items'] ?? []) as $item)
                    <a class="list-group-item list-group-item-action" href="{{ $item['route'] }}">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>{{ $item['label'] }}</span>
                            <i class="ti-arrow-right"></i>
                        </div>
                        <div class="small text-muted mt-1">{{ $item['description'] ?? '' }}</div>
                    </a>
                @endforeach
            </div>
        </div>
        <div class="tab-pane fade" id="transactions">
            <p class="text-muted">{{ _lang('Use operational reports to review transaction and expense movement.') }}</p>
            <div class="list-group workspace-link-list">
                @foreach(($reportGroups['transactions']['items'] ?? []) as $item)
                    <a class="list-group-item list-group-item-action" href="{{ $item['route'] }}">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>{{ $item['label'] }}</span>
                            <i class="ti-arrow-right"></i>
                        </div>
                        <div class="small text-muted mt-1">{{ $item['description'] ?? '' }}</div>
                    </a>
                @endforeach
            </div>
        </div>
        <div class="tab-pane fade" id="banking">
            <p class="text-muted">{{ _lang('Monitor bank movement, balances, and revenue outcomes from the same reporting center.') }}</p>
            <div class="list-group workspace-link-list">
                @foreach(($reportGroups['banking']['items'] ?? []) as $item)
                    <a class="list-group-item list-group-item-action" href="{{ $item['route'] }}">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>{{ $item['label'] }}</span>
                            <i class="ti-arrow-right"></i>
                        </div>
                        <div class="small text-muted mt-1">{{ $item['description'] ?? '' }}</div>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
