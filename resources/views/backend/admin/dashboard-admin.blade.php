@extends('layouts.app')

@section('workspace_top_tabs')
@include('backend.admin.partials.module-tabs', [
    'variant' => 'top-strip',
    'role' => 'navigation',
    'tabs' => [
        ['label' => _lang('Overview'), 'target' => '#dashboard-overview', 'active' => true],
        ['label' => _lang('Portfolio Health'), 'target' => '#portfolio-health'],
        ['label' => _lang('Collections Snapshot'), 'target' => '#collections-snapshot'],
        ['label' => _lang('Branch Performance'), 'target' => '#branch-performance'],
    ],
])
@endsection

@section('content')
@php
    $card_currency = $admin_interest_currency ?? '';
    $followUpStats = $collection_execution_stats ?? [];
    $collectionDateRange = $collection_date_range ?? [];
    $recentResolvedCases = collect($recent_resolved_cases ?? []);
    $recentTransactionPreview = collect($recent_transactions ?? [])->take(6);
    $dashboardBaseCurrency = $dashboard_base_currency ?? get_base_currency();
    $executiveMetrics = collect($dashboard_executive_metrics ?? []);
    $branchHotspots = collect($branch_performance ?? [])->sortByDesc('overdue_repayments')->take(5)->values();
    $branchPressureLeaderboard = collect($branch_collections_pressure ?? [])->take(5)->values();
    $branchPressureMax = max(1, (int) $branchPressureLeaderboard->max('pressure_score'));
    $bucketMax = max(1, collect($collection_buckets ?? [])->max('count'));
    $priorityList = [
        ['label' => _lang('Overdue Repayments'), 'value' => $overdue_repayments_count ?? 0, 'href' => route('loans.workspace'), 'theme' => 'critical', 'icon' => 'fas fa-exclamation-triangle', 'note' => _lang('Missed repayments already past schedule')],
        ['label' => _lang('Due Today'), 'value' => $today_due_count ?? 0, 'href' => route('action_center.index'), 'theme' => 'today', 'icon' => 'fas fa-calendar-day', 'note' => _lang('Collections that need same-day action')],
        ['label' => _lang('Finance Exceptions'), 'value' => $finance_exception_count ?? 0, 'href' => route('finance.index'), 'theme' => 'review', 'icon' => 'fas fa-balance-scale', 'note' => _lang('Pending requests and transaction exceptions')],
        ['label' => _lang('Ready for Disbursement'), 'value' => $ready_for_disbursement_count ?? 0, 'href' => route('loans.workspace'), 'theme' => 'active', 'icon' => 'fas fa-check-circle', 'note' => _lang('Approved loans waiting release')],
    ];
    $priorityMax = max(1, collect($priorityList)->max('value'));
    $queueCards = [
        ['label' => _lang('Pending Loans'), 'value' => request_count('pending_loans'), 'href' => route('loans.filter', 'pending'), 'icon' => 'fas fa-hand-holding-usd'],
        ['label' => _lang('Member Requests'), 'value' => request_count('member_requests'), 'href' => route('members.pending_requests'), 'icon' => 'fas fa-user-check'],
        ['label' => _lang('Deposit Requests'), 'value' => request_count('deposit_requests'), 'href' => route('deposit_requests.index'), 'icon' => 'fas fa-arrow-down'],
        ['label' => _lang('Withdraw Requests'), 'value' => request_count('withdraw_requests'), 'href' => route('withdraw_requests.index'), 'icon' => 'fas fa-arrow-up'],
    ];
    $attentionTotal = ($overdue_repayments_count ?? 0) + ($today_due_count ?? 0) + ($finance_exception_count ?? 0);
@endphp
@include('backend.admin.partials.workspace-styles')
<style>
.dashboard-command-card {
    background: linear-gradient(135deg, #f8fbfb 0%, #eef6f5 58%, #e7f0ef 100%);
    border: 1px solid #dde9e8;
    border-radius: 22px;
    overflow: hidden;
}
.dashboard-command-card .card-body { padding: 1.65rem; }
.dashboard-command-eyebrow {
    font-size: .72rem;
    text-transform: uppercase;
    letter-spacing: .12em;
    color: #6f787f;
    font-weight: 700;
    margin-bottom: .7rem;
}
.dashboard-command-title {
    font-size: 2.15rem;
    line-height: 1.02;
    color: #243036;
    margin-bottom: .85rem;
    font-weight: 700;
    max-width: 720px;
}
.dashboard-command-subtitle {
    color: #617078;
    font-size: .95rem;
    max-width: 780px;
}
.dashboard-command-meta {
    display: flex;
    flex-wrap: wrap;
    gap: .6rem;
    margin-top: 1rem;
}
.dashboard-command-meta .meta-pill {
    display: inline-flex;
    align-items: center;
    padding: .5rem .8rem;
    border-radius: 999px;
    background: rgba(63, 104, 109, .08);
    color: #32555A;
    font-size: .76rem;
    font-weight: 600;
}
.dashboard-command-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 1rem;
    margin-top: 1.35rem;
}
.dashboard-command-mini {
    background: rgba(255,255,255,.82);
    border: 1px solid #e1eceb;
    border-radius: 18px;
    padding: 1rem;
}
.dashboard-command-mini .mini-label { font-size: .76rem; color: #6f787f; margin-bottom: .35rem; }
.dashboard-command-mini .mini-value { font-size: 1.65rem; line-height: 1.05; font-weight: 700; color: #243036; }
.dashboard-command-mini .mini-meta { font-size: .78rem; color: #7b858c; margin-top: .35rem; }

.dashboard-priority-card,
.dashboard-kpi-card,
.dashboard-panel-card,
.dashboard-detail-card,
.dashboard-chart-card {
    border: 1px solid #e9eeea;
    border-radius: 20px;
    background: #fff;
    overflow: hidden;
}
.dashboard-priority-card .card-body,
.dashboard-panel-card .card-body,
.dashboard-detail-card .card-body,
.dashboard-chart-card .card-body { padding: 1.15rem; }
.dashboard-priority-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
    margin-bottom: 1rem;
}
.dashboard-attention-total {
    font-size: 2.2rem;
    line-height: 1;
    font-weight: 700;
    color: #243036;
}
.dashboard-priority-list { display: flex; flex-direction: column; gap: .85rem; }
.dashboard-priority-item {
    display: block;
    padding: .95rem;
    border: 1px solid #edf0ec;
    border-radius: 16px;
    background: #fff;
    text-decoration: none !important;
}
.dashboard-priority-item:hover {
    border-color: #d6e3e1;
    box-shadow: 0 10px 28px rgba(31,41,55,.04);
}
.dashboard-priority-item .topline {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: .75rem;
    margin-bottom: .35rem;
}
.dashboard-priority-item .priority-label {
    display: inline-flex;
    align-items: center;
    gap: .45rem;
    color: #2e3338;
    font-weight: 600;
    font-size: .9rem;
}
.dashboard-priority-item .priority-value { color: #243036; font-size: 1.2rem; font-weight: 700; }
.dashboard-priority-item .priority-note { color: #7b858c; font-size: .77rem; margin-top: .45rem; }
.dashboard-priority-item .progress {
    height: 7px;
    border-radius: 999px;
    overflow: hidden;
    background: #eef2ef;
}

.dashboard-kpi-card { position: relative; height: 100%; }
.dashboard-kpi-card::before {
    content: '';
    position: absolute;
    inset: 0 auto auto 0;
    width: 100%;
    height: 4px;
    background: linear-gradient(90deg, #3F686D, #80A8A4);
}
.dashboard-kpi-card .card-body { padding: 1.1rem; }
.dashboard-kpi-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: .75rem;
}
.dashboard-kpi-icon {
    width: 42px;
    height: 42px;
    border-radius: 13px;
    background: #eef6f5;
    color: #3F686D;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.dashboard-kpi-label { color: #6f787f; font-size: .82rem; margin-bottom: .3rem; }
.dashboard-kpi-value { color: #243036; font-size: 1.7rem; line-height: 1.02; font-weight: 700; }
.dashboard-kpi-meta { color: #7b858c; font-size: .78rem; margin-top: .45rem; }
.dashboard-kpi-link { color: #3F686D; font-size: .78rem; font-weight: 600; margin-top: .45rem; }
.dashboard-kpi-change {
    display: inline-flex;
    align-items: center;
    gap: .28rem;
    margin-top: .55rem;
    padding: .28rem .5rem;
    border-radius: 999px;
    font-size: .72rem;
    font-weight: 700;
}
.dashboard-kpi-change.success { background: #eaf8f0; color: #15803d; }
.dashboard-kpi-change.danger { background: #fdecee; color: #dc2626; }
.dashboard-kpi-change.neutral { background: #eef6f5; color: #3F686D; }

.dashboard-chart-card .card-header,
.dashboard-panel-card .card-header,
.dashboard-detail-card .card-header {
    background: transparent;
    border-bottom: 1px solid #eef1ec;
    padding: .95rem 1.1rem;
}
.dashboard-chart-card .chart-wrap { height: 260px; position: relative; }
.dashboard-chart-card canvas { max-height: 260px; }

.dashboard-progress-row + .dashboard-progress-row { margin-top: .95rem; }
.dashboard-progress-row .progress-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: .42rem;
    font-size: .84rem;
    color: #5c666d;
}
.dashboard-progress-row .progress {
    height: 8px;
    background: #eef2ef;
    border-radius: 999px;
    overflow: hidden;
}
.dashboard-progress-row .progress-bar { border-radius: 999px; }

.dashboard-queue-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: .85rem;
}
.dashboard-queue-item {
    display: block;
    border: 1px solid #edf0ec;
    border-radius: 16px;
    padding: .95rem;
    background: #fcfdfd;
    text-decoration: none !important;
}
.dashboard-queue-item:hover { background: #f6fbfb; border-color: #dce7e5; }
.dashboard-queue-item .queue-top { display: flex; align-items: center; justify-content: space-between; margin-bottom: .45rem; }
.dashboard-queue-item .queue-label { color: #66737b; font-size: .78rem; }
.dashboard-queue-item .queue-icon { color: #3F686D; }
.dashboard-queue-item .queue-value { color: #243036; font-size: 1.45rem; line-height: 1; font-weight: 700; }
.dashboard-queue-item .queue-link { color: #3F686D; font-size: .77rem; font-weight: 600; margin-top: .45rem; }

.dashboard-activity-list { display: flex; flex-direction: column; gap: .85rem; }
.dashboard-activity-item {
    display: flex;
    align-items: flex-start;
    gap: .8rem;
    padding-bottom: .85rem;
    border-bottom: 1px solid #eef1ec;
}
.dashboard-activity-item:last-child { border-bottom: 0; padding-bottom: 0; }
.dashboard-activity-icon {
    width: 38px;
    height: 38px;
    border-radius: 12px;
    background: #eef6f5;
    color: #3F686D;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.dashboard-activity-title { font-size: .88rem; font-weight: 600; color: #2e3338; margin-bottom: .15rem; }
.dashboard-activity-meta { font-size: .76rem; color: #7b858c; }
.dashboard-activity-amount { font-size: .82rem; font-weight: 700; }

.dashboard-leaderboard-item + .dashboard-leaderboard-item { margin-top: .95rem; }
.dashboard-leaderboard-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
    margin-bottom: .4rem;
}
.dashboard-leaderboard-branch { font-size: .88rem; font-weight: 600; color: #2e3338; }
.dashboard-leaderboard-meta { color: #7b858c; font-size: .76rem; margin-top: .18rem; }
.dashboard-leaderboard-score { font-size: .88rem; font-weight: 700; color: #243036; }

.dashboard-pulse-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: .85rem;
    margin-top: 1rem;
}
.dashboard-pulse-item {
    border: 1px solid #edf0ec;
    border-radius: 16px;
    padding: .9rem;
    background: #fcfdfd;
}
.dashboard-pulse-item .pulse-label { color: #6f787f; font-size: .77rem; margin-bottom: .25rem; }
.dashboard-pulse-item .pulse-value { color: #243036; font-size: 1.3rem; font-weight: 700; line-height: 1.08; }
.dashboard-pulse-item .pulse-meta { color: #87929a; font-size: .72rem; margin-top: .25rem; }

.dashboard-detail-card .nav-link { border-radius: 12px; }
.dashboard-detail-card .table thead th { background: #fafbf9; }
.dashboard-detail-section + .dashboard-detail-section { margin-top: 1.25rem; }
.dashboard-compact-note { color: #7b858c; font-size: .78rem; }

@media (max-width: 1199px) {
    .dashboard-command-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (max-width: 767px) {
    .dashboard-command-title { font-size: 1.6rem; }
    .dashboard-command-grid,
    .dashboard-queue-grid,
    .dashboard-pulse-grid { grid-template-columns: repeat(1, minmax(0, 1fr)); }
}
</style>

@include('backend.admin.partials.quick-actions', [
    'title' => _lang('Operational Quick Actions'),
    'subtitle' => _lang('Keep common admin actions near the dashboard instead of adding more sidebar clutter.'),
    'actions' => [
        ['label' => _lang('Add Member'), 'url' => route('members.create'), 'class' => 'btn-primary btn-sm', 'icon' => 'fas fa-user-plus'],
        ['label' => _lang('New Loan'), 'url' => route('loans.create'), 'class' => 'btn-outline-primary btn-sm', 'icon' => 'fas fa-hand-holding-usd'],
        ['label' => _lang('New Transaction'), 'url' => route('transactions.create'), 'class' => 'btn-outline-primary btn-sm', 'icon' => 'fas fa-exchange-alt'],
        ['label' => _lang('Reports Center'), 'url' => route('reports.index'), 'class' => 'btn-outline-primary btn-sm', 'icon' => 'fas fa-chart-bar'],
    ],
])

@include('backend.admin.partials.collection-date-range-filter', ['collectionDateRange' => $collectionDateRange, 'filterId' => 'dashboard-collection-range'])

<div class="tab-content dashboard-top-tab-content">
    <div class="tab-pane fade show active" id="dashboard-overview">
        <div class="row mb-4">
            <div class="col-xl-8 mb-3 mb-xl-0">
                <div class="card dashboard-command-card h-100 mb-0">
                    <div class="card-body">
                        <div class="dashboard-command-eyebrow">{{ _lang('Operations Command Center') }}</div>
                        <h3 class="dashboard-command-title">{{ _lang('See risk, workload, and movement in one glance.') }}</h3>
                        <p class="dashboard-command-subtitle mb-0">{{ _lang('The dashboard is organized around immediate attention, portfolio pressure, cash movement trends, and branch hotspots so an admin can decide what to do next without reading a wall of tables.') }}</p>

                        <div class="dashboard-command-meta">
                            <span class="meta-pill"><i class="fas fa-calendar-alt mr-2"></i>{{ _lang('Analytics Range') }}: {{ $collectionDateRange['label'] ?? _lang('Today') }}</span>
                            <span class="meta-pill"><i class="fas fa-bolt mr-2"></i>{{ _lang('Call Today Queue') }}: {{ $collection_queue_counts['call_today'] ?? 0 }}</span>
                            <span class="meta-pill"><i class="fas fa-bell mr-2"></i>{{ _lang('Upcoming Reminders') }}: {{ $collection_queue_counts['upcoming_reminders'] ?? 0 }}</span>
                            <span class="meta-pill"><i class="fas fa-radiation-alt mr-2"></i>{{ _lang('Critical Collections') }}: {{ $collection_queue_counts['critical'] ?? 0 }}</span>
                        </div>

                        <div class="dashboard-command-grid">
                            <div class="dashboard-command-mini">
                                <div class="mini-label">{{ _lang('Call Today Queue') }}</div>
                                <div class="mini-value">{{ number_format($collection_queue_counts['call_today'] ?? 0) }}</div>
                                <div class="mini-meta">{{ _lang('Due-today plus near-term overdue cases queued for immediate action') }}</div>
                            </div>
                            <div class="dashboard-command-mini">
                                <div class="mini-label">{{ _lang('Upcoming Reminders') }}</div>
                                <div class="mini-value">{{ number_format($collection_queue_counts['upcoming_reminders'] ?? 0) }}</div>
                                <div class="mini-meta">{{ _lang('Borrowers needing pre-due reminder outreach') }}</div>
                            </div>
                            <div class="dashboard-command-mini">
                                <div class="mini-label">{{ _lang('Finance Exceptions') }}</div>
                                <div class="mini-value">{{ number_format($finance_exception_count ?? 0) }}</div>
                                <div class="mini-meta">{{ _lang('Pending finance requests, cash postings, and bank exceptions') }}</div>
                            </div>
                            <div class="dashboard-command-mini">
                                <div class="mini-label">{{ _lang('Ready for Disbursement') }}</div>
                                <div class="mini-value">{{ number_format($ready_for_disbursement_count ?? 0) }}</div>
                                <div class="mini-meta">{{ _lang('Approved loans waiting release into member accounts') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="card dashboard-priority-card h-100 mb-0">
                    <div class="card-body">
                        <div class="dashboard-priority-header">
                            <div>
                                <div class="text-muted small mb-1">{{ _lang('Needs attention now') }}</div>
                                <div class="dashboard-attention-total">{{ number_format($attentionTotal) }}</div>
                                <div class="dashboard-compact-note mt-1">{{ _lang('Combined immediate pressure across overdue, due-today, and finance exception queues.') }}</div>
                            </div>
                            <a href="{{ route('action_center.index') }}" class="btn btn-outline-primary btn-sm">{{ _lang('Open Queue') }}</a>
                        </div>

                        <div class="dashboard-priority-list">
                            @foreach($priorityList as $item)
                                @php $progressWidth = $priorityMax > 0 ? min(100, round(($item['value'] / $priorityMax) * 100, 0)) : 0; @endphp
                                <a href="{{ $item['href'] }}" class="dashboard-priority-item">
                                    <div class="topline">
                                        <span class="priority-label"><i class="{{ $item['icon'] }}"></i> {{ $item['label'] }}</span>
                                        <span class="priority-value">{{ number_format($item['value']) }}</span>
                                    </div>
                                    <div class="progress mb-2">
                                        <div class="progress-bar bg-{{ $item['theme'] === 'critical' ? 'danger' : ($item['theme'] === 'today' ? 'warning' : ($item['theme'] === 'active' ? 'success' : 'info')) }}" style="width: {{ $progressWidth }}%"></div>
                                    </div>
                                    <div class="priority-note">{{ $item['note'] }}</div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="tab-pane fade" id="portfolio-health">
        <div class="row mb-4">
            @foreach($executiveMetrics as $metric)
                <div class="col-xl-3 col-md-6 mb-3">
                    <a href="{{ $metric['route'] }}" class="text-decoration-none">
                        <div class="card dashboard-kpi-card mb-0">
                            <div class="card-body">
                                <div class="dashboard-kpi-top">
                                    <span class="dashboard-kpi-icon"><i class="{{ $metric['icon'] }}"></i></span>
                                </div>
                                <div class="dashboard-kpi-label">{{ $metric['label'] }}</div>
                                <div class="dashboard-kpi-value">{{ $metric['formatted_amount'] }}</div>
                                <div class="dashboard-kpi-meta">{{ $metric['meta'] }}</div>
                                @if($metric['delta'] !== null)
                                    <div class="dashboard-kpi-change {{ $metric['delta_tone'] }}">
                                        <i class="fas {{ $metric['delta'] > 0 ? 'fa-arrow-up' : ($metric['delta'] < 0 ? 'fa-arrow-down' : 'fa-minus') }}"></i>
                                        <span>{{ number_format(abs($metric['delta']), 1) }}% {{ _lang('vs last month') }}</span>
                                    </div>
                                @endif
                                <div class="dashboard-kpi-link">{{ _lang('Open detail') }}</div>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>

    <div class="tab-pane fade" id="collections-snapshot">
        <div class="row mb-4">
            <div class="col-lg-8 mb-3 mb-lg-0">
                <div class="card dashboard-chart-card h-100 mb-0">
                    <div class="card-header d-flex align-items-center flex-wrap">
                        <span>{{ _lang('Deposit & Withdraw Trend') . ' - ' . date('Y') }}</span>
                        <select class="filter-select ml-auto py-0 auto-select form-control form-control-sm" style="max-width: 140px;" data-selected="{{ base_currency_id() }}">
                            @foreach(\App\Models\Currency::where('status',1)->get() as $currency)
                                <option value="{{ $currency->id }}" data-symbol="{{ currency_symbol($currency->name) }}">{{ $currency->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="card-body">
                        <div class="chart-wrap">
                            <canvas id="transactionAnalysis"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card dashboard-chart-card h-100 mb-0">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <span>{{ _lang('Expense Trend') . ' - ' . date('M Y') }}</span>
                        <a href="{{ route('expenses.index') }}" class="small">{{ _lang('Open expenses') }}</a>
                    </div>
                    <div class="card-body">
                        <div class="chart-wrap">
                            <canvas id="expenseOverview"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="tab-pane fade" id="branch-performance">
        <div class="row mb-4">
            <div class="col-lg-4 mb-3 mb-lg-0">
                <div class="card dashboard-panel-card h-100 mb-0">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <span>{{ _lang('Risk Radar') }}</span>
                        <a href="{{ route('loans.workspace') }}" class="small">{{ _lang('Open loans workspace') }}</a>
                    </div>
                    <div class="card-body">
                        @foreach(($collection_buckets ?? []) as $bucket)
                            <div class="dashboard-progress-row">
                                <div class="progress-meta">
                                    <span>{{ $bucket['label'] }}</span>
                                    <strong>{{ number_format($bucket['count']) }}</strong>
                                </div>
                                <div class="progress"><div class="progress-bar bg-info" style="width: {{ $bucketMax > 0 ? min(100, round(($bucket['count'] / $bucketMax) * 100, 0)) : 0 }}%"></div></div>
                            </div>
                        @endforeach
                        <div class="dashboard-progress-row mt-3 pt-3 border-top">
                            <div class="progress-meta">
                                <span>{{ _lang('Critical Collections') }}</span>
                                <strong>{{ $collection_queue_counts['critical'] ?? 0 }}</strong>
                            </div>
                            <div class="progress"><div class="progress-bar bg-danger" style="width: {{ $priorityMax > 0 ? min(100, round((($collection_queue_counts['critical'] ?? 0) / $priorityMax) * 100, 0)) : 0 }}%"></div></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 mb-3 mb-lg-0">
                <div class="card dashboard-panel-card h-100 mb-0">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <span>{{ _lang('Branch Pressure Leaderboard') }}</span>
                        <a href="{{ route('members.workspace') }}" class="small">{{ _lang('Open members workspace') }}</a>
                    </div>
                    <div class="card-body">
                        @forelse($branchPressureLeaderboard as $branch)
                            <div class="dashboard-leaderboard-item">
                                <div class="dashboard-leaderboard-top">
                                    <div>
                                        <div class="dashboard-leaderboard-branch">{{ $branch->name }}</div>
                                        <div class="dashboard-leaderboard-meta">{{ $branch->due_today }} {{ _lang('due today') }} · {{ $branch->overdue }} {{ _lang('overdue') }} · {{ decimalPlace($branch->overdue_amount_base ?? 0, $dashboardBaseCurrency, 0) }} {{ _lang('overdue value') }}</div>
                                    </div>
                                    <div class="dashboard-leaderboard-score">{{ $branch->pressure_score }}</div>
                                </div>
                                <div class="progress"><div class="progress-bar bg-danger" style="width: {{ $branchPressureMax > 0 ? min(100, round(($branch->pressure_score / $branchPressureMax) * 100, 0)) : 0 }}%"></div></div>
                            </div>
                        @empty
                            @include('backend.admin.partials.empty-state', [
                                'title' => _lang('No branch pressure yet'),
                                'description' => _lang('Once due and overdue pressure is detected by branch, a ranked view will appear here.'),
                                'class' => 'py-4',
                            ])
                        @endforelse
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card dashboard-panel-card h-100 mb-0">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <span>{{ _lang('Recent Activity') }}</span>
                        <a href="{{ route('transactions.index') }}" class="small">{{ _lang('Open transactions') }}</a>
                    </div>
                    <div class="card-body">
                        @if($recentTransactionPreview->count() > 0)
                            <div class="dashboard-activity-list">
                                @foreach($recentTransactionPreview as $transaction)
                                    @php
                                        $symbol = $transaction->dr_cr == 'dr' ? '-' : '+';
                                        $class = $transaction->dr_cr == 'dr' ? 'text-danger' : 'text-success';
                                    @endphp
                                    <div class="dashboard-activity-item">
                                        <div class="dashboard-activity-icon"><i class="fas fa-exchange-alt"></i></div>
                                        <div class="flex-grow-1">
                                            <div class="dashboard-activity-title">{{ $transaction->member->name }}</div>
                                            <div class="dashboard-activity-meta">{{ $transaction->trans_date }} · {{ ucwords(str_replace('_',' ',$transaction->type)) }}</div>
                                        </div>
                                        <div class="dashboard-activity-amount {{ $class }}">{{ $symbol . ' ' . decimalPlace($transaction->amount, currency($transaction->account->savings_type->currency->name)) }}</div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            @include('backend.admin.partials.empty-state', [
                                'title' => _lang('No recent transactions yet'),
                                'description' => _lang('When finance activity is posted, the latest movement will appear here for quick scanning.'),
                                'class' => 'py-4',
                            ])
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card dashboard-detail-card workspace-anchor-offset" id="dashboard-detail-boards">
    <div class="card-header">
        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between">
            <div class="pr-lg-4 mb-3 mb-lg-0">
                <div class="workspace-section-title mb-1">{{ _lang('Detailed Operational Boards') }}</div>
                <p class="text-muted small mb-0">{{ _lang('Deeper queues and registers stay below the glance layer so the dashboard remains readable while detail is still available.') }}</p>
            </div>
            @include('backend.admin.partials.module-tabs', [
                'tabs' => [
                    ['label' => _lang('Collections'), 'target' => '#detail-collections', 'active' => true],
                    ['label' => _lang('Performance'), 'target' => '#detail-performance'],
                    ['label' => _lang('Registers'), 'target' => '#detail-registers'],
                ],
            ])
        </div>
    </div>
    <div class="card-body tab-content">
        <div class="tab-pane fade show active" id="detail-collections">
            <div class="row">
                <div class="col-lg-7 mb-4 mb-lg-0">
                    <div class="dashboard-detail-section">
                        <div class="workspace-section-title">{{ _lang('Collector-ready Call List') }}</div>
                        <div class="table-responsive">
                            <table class="table table-bordered dashboard-table-compact mb-0">
                                <thead>
                                    <tr>
                                        <th class="pl-3">{{ _lang('Loan ID') }}</th>
                                        <th>{{ _lang('Borrower') }}</th>
                                        <th>{{ _lang('Phone') }}</th>
                                        <th>{{ _lang('Queue') }}</th>
                                        <th>{{ _lang('Last Follow-up') }}</th>
                                        <th class="pr-3">{{ _lang('Next Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse(($collector_call_list ?? collect()) as $item)
                                        <tr>
                                            <td class="pl-3">{{ $item->loan_id }}</td>
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
                                            <td class="pr-3">{{ $item->next_action }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="6" class="text-center text-muted py-2">{{ _lang('No collector call items found') }}</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="dashboard-detail-section mb-4">
                        <div class="workspace-section-title">{{ _lang('Upcoming Reminder Queue') }}</div>
                        <div class="table-responsive">
                            <table class="table table-bordered dashboard-table-compact mb-0">
                                <thead>
                                    <tr>
                                        <th class="pl-3">{{ _lang('Loan ID') }}</th>
                                        <th>{{ _lang('Borrower') }}</th>
                                        <th>{{ _lang('Due In') }}</th>
                                        <th>{{ _lang('Last Follow-up') }}</th>
                                        <th class="pr-3">{{ _lang('Reminder') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse(($upcoming_reminder_queue ?? collect()) as $item)
                                        <tr>
                                            <td class="pl-3">{{ $item->loan_id }}</td>
                                            <td>{{ $item->borrower_name }}</td>
                                            <td>{{ $item->days_until }} {{ _lang('days') }}</td>
                                            <td>
                                                @if($item->last_outcome_label)
                                                    <span class="workspace-status-chip {{ $item->last_outcome_theme }}">{{ $item->last_outcome_label }}</span>
                                                @else
                                                    <span class="text-muted small">{{ _lang('No log yet') }}</span>
                                                @endif
                                            </td>
                                            <td class="pr-3"><span class="workspace-status-chip {{ $item->reminder_theme }}">{{ $item->reminder_label }}</span></td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="text-center text-muted py-2">{{ _lang('No reminder items found') }}</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="dashboard-detail-section">
                        <div class="workspace-section-title">{{ _lang('Promise Follow-up Queue') }}</div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-export dashboard-table-compact mb-0" data-export-filename="Dashboard_Promise_Follow_Up_Queue">
                                <thead>
                                    <tr>
                                        <th class="pl-3">{{ _lang('Loan ID') }}</th>
                                        <th>{{ _lang('Borrower') }}</th>
                                        <th>{{ _lang('Promise Date') }}</th>
                                        <th>{{ _lang('Status') }}</th>
                                        <th class="pr-3">{{ _lang('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse(($promise_follow_up_queue ?? collect()) as $item)
                                        <tr>
                                            <td class="pl-3">{{ $item->loan_id }}</td>
                                            <td>{{ $item->borrower_name }}</td>
                                            <td>{{ $item->promised_payment_date }}</td>
                                            <td><span class="workspace-status-chip {{ $item->promise_status_theme }}">{{ $item->promise_status_label }}</span></td>
                                            <td class="pr-3">@include('backend.admin.partials.table-actions', ['items' => [['label' => _lang('Log Follow-up'), 'url' => route('loan_collection_follow_ups.create', $item->repayment_id), 'icon' => 'ti-write', 'class' => 'ajax-modal', 'data_title' => _lang('Log Collection Follow-up')]]])</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="text-center text-muted py-2">{{ _lang('No promise follow-up items found') }}</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="detail-performance">
            <div class="row">
                <div class="col-lg-4 mb-4 mb-lg-0">
                    <div class="workspace-section-title">{{ _lang('Branch Follow-up Performance') }}</div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-export dashboard-table-compact mb-0" data-export-filename="Dashboard_Branch_Follow_Up_Performance">
                            <thead>
                                <tr>
                                    <th class="pl-3">{{ _lang('Branch') }}</th>
                                    <th>{{ _lang('Open Queue') }}</th>
                                    <th>{{ _lang('Touched') }}</th>
                                    <th>{{ _lang('Resolved') }}</th>
                                    <th class="pr-3">{{ _lang('Completion') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse(($branch_follow_up_performance ?? collect()) as $branch)
                                    <tr>
                                        <td class="pl-3">{{ $branch->name }}</td>
                                        <td>{{ $branch->open_queue }}</td>
                                        <td>{{ $branch->touched_today }}</td>
                                        <td>{{ $branch->resolved_in_range }}</td>
                                        <td class="pr-3"><span class="workspace-status-chip {{ $branch->completion_rate >= 70 ? 'active' : ($branch->completion_rate >= 40 ? 'review' : 'critical') }}">{{ $branch->completion_rate }}%</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted py-2">{{ _lang('No branch follow-up data available') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-lg-4 mb-4 mb-lg-0">
                    <div class="workspace-section-title">{{ _lang('Collector Follow-up Performance') }}</div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-export dashboard-table-compact mb-0" data-export-filename="Dashboard_Collector_Follow_Up_Performance">
                            <thead>
                                <tr>
                                    <th class="pl-3">{{ _lang('User') }}</th>
                                    <th>{{ _lang('Logs') }}</th>
                                    <th>{{ _lang('Cases') }}</th>
                                    <th>{{ _lang('Promises') }}</th>
                                    <th>{{ _lang('Resolved') }}</th>
                                    <th class="pr-3">{{ _lang('Escalated') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse(($collector_follow_up_performance ?? collect()) as $collector)
                                    <tr>
                                        <td class="pl-3">{{ $collector->name }}</td>
                                        <td>{{ $collector->logs_count }}</td>
                                        <td>{{ $collector->cases_touched }}</td>
                                        <td>{{ $collector->promised_count }}</td>
                                        <td>{{ $collector->resolved_count }}</td>
                                        <td class="pr-3">{{ $collector->escalated_count }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted py-2">{{ _lang('No collector follow-up data available') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="workspace-section-title">{{ _lang('Recent Resolutions') }}</div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-export dashboard-table-compact mb-0" data-export-filename="Dashboard_Recent_Resolutions">
                            <thead>
                                <tr>
                                    <th class="pl-3">{{ _lang('Loan ID') }}</th>
                                    <th>{{ _lang('Borrower') }}</th>
                                    <th>{{ _lang('Paid On') }}</th>
                                    <th class="pr-3">{{ _lang('Resolution') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentResolvedCases as $item)
                                    <tr>
                                        <td class="pl-3">{{ $item->loan_id }}</td>
                                        <td>{{ $item->borrower_name }}</td>
                                        <td>{{ $item->payment_date }}</td>
                                        <td class="pr-3"><span class="workspace-status-chip {{ $item->resolution_theme }}">{{ $item->resolution_label }}</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted py-2">{{ _lang('No resolved follow-up cases found') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="detail-registers">
            <div class="row">
                <div class="col-md-6 mb-4 mb-md-0">
                    <div class="workspace-section-title">{{ _lang('Active Loan Balances') }}</div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-export dashboard-table-compact">
                            <thead>
                                <tr>
                                    <th data-total-label="{{ _lang('Total') }}" class="text-nowrap pl-3">{{ _lang('Currency') }}</th>
                                    <th class="text-nowrap" data-sum="1">{{ _lang('Applied Amount') }}</th>
                                    <th class="text-nowrap" data-sum="1">{{ _lang('Paid Amount') }}</th>
                                    <th class="text-nowrap" data-sum="1">{{ _lang('Due Amount') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(count($loan_balances) == 0)
                                    <tr><td colspan="4"><p class="text-center text-muted mb-0 py-2">{{ _lang('No Data Available') }}</p></td></tr>
                                @endif
                                @foreach($loan_balances as $loan_balance)
                                    <tr>
                                        <td class="pl-3">{{ $loan_balance->currency->name }}</td>
                                        <td>{{ decimalPlace($loan_balance->total_amount, currency($loan_balance->currency->name)) }}</td>
                                        <td>{{ decimalPlace($loan_balance->total_paid, currency($loan_balance->currency->name)) }}</td>
                                        <td>{{ decimalPlace($loan_balance->total_amount - $loan_balance->total_paid, currency($loan_balance->currency->name)) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot><tr class="table-totals-row"><td></td><td></td><td></td><td></td></tr></tfoot>
                        </table>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="workspace-section-title">{{ _lang('Due Loan Payments') }}</div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-export dashboard-table-compact">
                            <thead>
                                <tr>
                                    <th data-total-label="{{ _lang('Total') }}" class="text-nowrap pl-3">{{ _lang('Loan ID') }}</th>
                                    <th class="text-nowrap">{{ _lang('Member No') }}</th>
                                    <th class="text-nowrap">{{ _lang('Member') }}</th>
                                    <th class="text-nowrap">{{ _lang('Last Payment Date') }}</th>
                                    <th class="text-nowrap">{{ _lang('Due Repayments') }}</th>
                                    <th class="text-nowrap text-right pr-3" data-sum="1">{{ _lang('Total Due') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(count($due_repayments) == 0)
                                    <tr><td colspan="6"><p class="text-center text-muted mb-0 py-2">{{ _lang('No Data Available') }}</p></td></tr>
                                @endif
                                @foreach($due_repayments as $repayment)
                                    <tr>
                                        <td class="pl-3">{{ $repayment->loan->loan_id }}</td>
                                        <td>{{ $repayment->loan->borrower->member_no }}</td>
                                        <td>{{ $repayment->loan->borrower->name }}</td>
                                        <td class="text-nowrap">{{ $repayment->repayment_date }}</td>
                                        <td class="text-nowrap">{{ $repayment->total_due_repayment }}</td>
                                        <td class="text-nowrap text-right pr-3">{{ decimalPlace($repayment->total_due, currency($repayment->loan->currency->name)) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot><tr class="table-totals-row"><td></td><td></td><td></td><td></td><td></td><td class="text-right"></td></tr></tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <div class="workspace-section-title mt-4">{{ _lang('Recent Transactions') }}</div>
            <div class="dashboard-proof-datatable-card">
                <div class="table-responsive">
                <table id="dashboard_recent_transactions_table" class="table table-bordered table-striped table-export dashboard-table-compact" data-export-filename="Dashboard_Recent_Transactions">
                    <thead>
                        <tr>
                            <th data-total-label="{{ _lang('Total') }}" class="pl-3">{{ _lang('Date') }}</th>
                            <th>{{ _lang('Member') }}</th>
                            <th class="text-nowrap">{{ _lang('Account Number') }}</th>
                            <th data-sum="1">{{ _lang('Amount') }}</th>
                            <th class="text-nowrap">{{ _lang('Debit/Credit') }}</th>
                            <th>{{ _lang('Type') }}</th>
                            <th>{{ _lang('Status') }}</th>
                            <th class="text-center" data-no-export="1">{{ _lang('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(count($recent_transactions) == 0)
                            <tr><td colspan="8"><p class="text-center text-muted mb-0 py-2">{{ _lang('No Data Available') }}</p></td></tr>
                        @endif
                        @foreach($recent_transactions as $transaction)
                            @php
                                $symbol = $transaction->dr_cr == 'dr' ? '-' : '+';
                                $class  = $transaction->dr_cr == 'dr' ? 'text-danger' : 'text-success';
                            @endphp
                            <tr>
                                <td class="text-nowrap pl-3">{{ $transaction->trans_date }}</td>
                                <td>{{ $transaction->member->name }}</td>
                                <td>{{ $transaction->account->account_number }}</td>
                                <td><span class="text-nowrap {{ $class }}">{{ $symbol.' '.decimalPlace($transaction->amount, currency($transaction->account->savings_type->currency->name)) }}</span></td>
                                <td>{{ strtoupper($transaction->dr_cr) }}</td>
                                <td>{{ ucwords(str_replace('_',' ',$transaction->type)) }}</td>
                                <td>{!! xss_clean(transaction_status($transaction->status)) !!}</td>
                                <td class="text-center">@include('backend.admin.partials.table-actions', ['items' => [['label' => _lang('View'), 'url' => route('transactions.show', $transaction->id), 'icon' => 'ti-eye', 'target' => '_blank']]])</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot><tr class="table-totals-row"><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr></tfoot>
                </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js-script')
<script src="{{ asset('public/backend/plugins/chartJs/chart.min.js') }}"></script>
<script src="{{ asset('public/backend/assets/js/dashboard.js') }}"></script>
<script>
(function ($) {
    "use strict";

    if (!$) {
        return;
    }

    function findDashboardChart(canvasId) {
        if (typeof Chart === 'undefined') {
            return null;
        }

        var canvas = document.getElementById(canvasId);
        if (!canvas) {
            return null;
        }

        if (typeof Chart.getChart === 'function') {
            return Chart.getChart(canvas);
        }

        if (!Chart.instances) {
            return null;
        }

        for (var key in Chart.instances) {
            if (Object.prototype.hasOwnProperty.call(Chart.instances, key) && Chart.instances[key].canvas === canvas) {
                return Chart.instances[key];
            }
        }

        return null;
    }

    function refreshCollectionsCharts() {
        ['transactionAnalysis', 'expenseOverview'].forEach(function (canvasId) {
            var chart = findDashboardChart(canvasId);

            if (!chart) {
                return;
            }

            chart.resize();
            chart.update('none');
        });
    }

    var $table = $('#dashboard_recent_transactions_table');
    if ($table.length && typeof window.cavicAdminDataTable === 'function') {
        var table = window.cavicAdminDataTable('#dashboard_recent_transactions_table', {
            paging: true,
            searching: true,
            info: true,
            ordering: false,
            lengthChange: true,
            pageLength: 10,
            buttons: [
                {
                    extend: 'pdf',
                    text: '<i class="ti-download"></i><span>{{ _lang('PDF') }}</span>',
                    className: 'btn btn-xs admin-dt-btn admin-dt-btn-ghost',
                    filename: 'Dashboard_Recent_Transactions',
                    title: 'Dashboard Recent Transactions',
                    exportOptions: { columns: ':not([data-no-export="1"])' }
                },
                {
                    extend: 'excel',
                    text: '<i class="ti-download"></i><span>{{ _lang('Excel') }}</span>',
                    className: 'btn btn-xs admin-dt-btn admin-dt-btn-ghost',
                    filename: 'Dashboard_Recent_Transactions',
                    title: 'Dashboard Recent Transactions',
                    exportOptions: { columns: ':not([data-no-export="1"])' }
                }
            ],
            language: {
                info: '{{ _lang('Viewing') }} _START_-_END_ {{ _lang('of') }} _TOTAL_',
                infoEmpty: '{{ _lang('Viewing 0-0 of 0') }}',
                search: '',
                searchPlaceholder: '{{ _lang('Search transactions') }}',
                lengthMenu: '_MENU_',
                zeroRecords: '{{ _lang('No matching transactions found') }}',
                emptyTable: '{{ _lang('No Data Available') }}',
                paginate: {
                    previous: '<i class="fas fa-angle-left"></i>',
                    next: '<i class="fas fa-angle-right"></i>'
                }
            },
            initComplete: function () {
                var api = this.api();
                var $wrapper = $(api.table().container());
                var $left = $wrapper.find('.admin-datatable-top-left');
                var $right = $wrapper.find('.admin-datatable-top-right');
                var $top = $wrapper.find('.admin-datatable-top');
                var $length = $left.find('.dataTables_length').detach();
                var $search = $right.find('.dataTables_filter').detach();
                var $buttons = $left.find('.dt-buttons').detach();

                var $statusFilter = $('<select class="dashboard-proof-filter"><option value="">{{ _lang('Status') }}</option></select>');
                var $typeFilter = $('<select class="dashboard-proof-filter"><option value="">{{ _lang('Type') }}</option></select>');

                api.column(6).data().unique().sort().each(function (value) {
                    var text = $('<div>').html(value).text().trim();
                    if (text) $statusFilter.append('<option value="' + text.replace(/"/g, '&quot;') + '">' + text + '</option>');
                });

                api.column(5).data().unique().sort().each(function (value) {
                    var text = $('<div>').html(value).text().trim();
                    if (text) $typeFilter.append('<option value="' + text.replace(/"/g, '&quot;') + '">' + text + '</option>');
                });

                $statusFilter.on('change', function () {
                    var val = $.fn.dataTable.util.escapeRegex($(this).val());
                    api.column(6).search(val ? val : '', true, false).draw();
                });

                $typeFilter.on('change', function () {
                    var val = $.fn.dataTable.util.escapeRegex($(this).val());
                    api.column(5).search(val ? '^' + val + '$' : '', true, false).draw();
                });

                var $toolbarLeft = $('<div class="dashboard-proof-top-left"></div>');
                var $toolbarCenter = $('<div class="dashboard-proof-top-center"></div>');
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

                api.columns().every(function (index) {
                    var column = this;
                    var $header = $(column.header());
                    var label = $header.text().trim();
                    var isLocked = $header.data('noExport') === 1 || $header.attr('data-no-export') === '1' || label === '{{ _lang('Action') }}';

                    if (!label || isLocked) {
                        return;
                    }

                    var itemId = 'dashboard-column-toggle-' + index;
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

                $buttons.addClass('dashboard-proof-export-buttons');

                $toolbarLeft.append(
                    $('<div class="dashboard-toolbar-item dashboard-toolbar-item-length"></div>').append($length)
                );
                $toolbarCenter.append(
                    $('<div class="dashboard-toolbar-item dashboard-toolbar-item-export"></div>').append($buttons)
                );
                $toolbarRight
                    .append($('<div class="dashboard-toolbar-item"></div>').append($columnsDropdown))
                    .append($('<div class="dashboard-toolbar-item"></div>').append($typeFilter))
                    .append($('<div class="dashboard-toolbar-item"></div>').append($statusFilter))
                    .append($('<div class="dashboard-toolbar-item dashboard-toolbar-item-search"></div>').append($search));

                $top.empty().append($toolbarLeft, $toolbarCenter, $toolbarRight);
                $search.find('input').attr('placeholder', '{{ _lang('Search transactions') }}');
            }
        });
    }

    $(document).on('shown.bs.tab', 'a[data-toggle="tab"][href="#collections-snapshot"]', function () {
        window.setTimeout(refreshCollectionsCharts, 60);
    });

    $(window).on('load', function () {
        if ($('#collections-snapshot').hasClass('active') || $('#collections-snapshot').hasClass('show')) {
            window.setTimeout(refreshCollectionsCharts, 60);
        }
    });
})(window.jQuery || window.$);
</script>
@endsection
