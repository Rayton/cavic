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
    $attentionTotal = $reportHighlights['attention_total'] ?? 0;
    $overdueCount = $reportHighlights['overdue_repayments'] ?? 0;
    $dueTodayCount = $reportHighlights['due_today'] ?? 0;
    $pendingBankItems = $reportHighlights['pending_bank_transactions'] ?? 0;
    $periodLabel = $reportHighlights['period_label'] ?? date('F Y');
@endphp
@include('backend.admin.partials.workspace-styles')
<style>
.report-exec-hero {
    border: 1px solid #dfe8e7;
    border-radius: 8px;
    background: #f8fbfb;
}
.report-exec-hero .card-body { padding: 1.35rem; }
.report-exec-eyebrow {
    color: #3F686D;
    font-size: .74rem;
    font-weight: 700;
    letter-spacing: 0;
    text-transform: uppercase;
}
.report-exec-title {
    color: #243036;
    font-size: 1.35rem;
    font-weight: 700;
    line-height: 1.25;
    margin: .35rem 0 .5rem;
}
.report-exec-copy { color: #66737b; max-width: 720px; }
.report-exec-signal {
    border: 1px solid #e6edec;
    border-radius: 8px;
    background: #fff;
    padding: 1rem;
    height: 100%;
}
.report-exec-signal-label { color: #6f787f; font-size: .78rem; margin-bottom: .3rem; }
.report-exec-signal-value { color: #243036; font-size: 1.55rem; font-weight: 700; line-height: 1.05; }
.report-exec-signal-meta { color: #7b858c; font-size: .78rem; margin-top: .35rem; }
.report-kpi-card {
    display: block;
    height: 100%;
    border: 1px solid #e8eeee;
    border-radius: 8px;
    color: inherit;
    text-decoration: none;
    background: #fff;
    transition: border-color .15s ease, background .15s ease;
}
.report-kpi-card:hover { color: inherit; text-decoration: none; border-color: #cfdedb; background: #fbfdfd; }
.report-kpi-card .card-body { padding: 1rem; }
.report-kpi-top { display: flex; align-items: flex-start; justify-content: space-between; gap: .75rem; margin-bottom: .8rem; }
.report-kpi-icon {
    width: 34px;
    height: 34px;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #eef7f7;
    color: #3F686D;
}
.report-kpi-card.critical .report-kpi-icon { background: #fdecee; color: #b4232f; }
.report-kpi-card.review .report-kpi-icon { background: #fff2e3; color: #a04a00; }
.report-kpi-card.today .report-kpi-icon { background: #fff7db; color: #7a4d00; }
.report-kpi-label { color: #6f787f; font-size: .78rem; margin-bottom: .3rem; }
.report-kpi-value { color: #243036; font-size: 1.45rem; font-weight: 700; line-height: 1.05; }
.report-kpi-meta { color: #5f6b72; font-size: .8rem; margin-top: .35rem; }
.report-kpi-detail { color: #7b858c; font-size: .78rem; margin-top: .7rem; min-height: 36px; }
.report-shortcut-card {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    padding: 1rem 0;
    border-bottom: 1px solid #eef1f5;
    color: inherit;
    text-decoration: none;
}
.report-shortcut-card:last-child { border-bottom: 0; }
.report-shortcut-card:hover { color: inherit; text-decoration: none; }
.report-shortcut-title { color: #243036; font-weight: 700; margin-bottom: .25rem; }
.report-shortcut-copy { color: #6f787f; font-size: .82rem; margin: 0; }
.report-mini-table th { background: #fafbf9; color: #5f6b72; font-size: .76rem; }
.report-mini-table td { vertical-align: middle; }
.report-exec-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
}
.report-exec-toolbar .report-exec-title { margin: 0; }
.report-exec-period { color: #6f787f; font-size: .82rem; }
.report-exec-actions { display: flex; gap: .5rem; flex-wrap: wrap; }
.report-metric-card {
    height: 100%;
    border: 1px solid #e7eeee;
    border-left: 4px solid #3F686D;
    border-radius: 8px;
    background: #fff;
    padding: 1rem;
}
.report-metric-card.active { border-left-color: #16803c; }
.report-metric-card.review { border-left-color: #b45309; }
.report-metric-card.critical { border-left-color: #b4232f; }
.report-metric-label { color: #65717a; font-size: .78rem; font-weight: 700; margin-bottom: .35rem; }
.report-metric-value { color: #202b33; font-size: 1.55rem; font-weight: 800; line-height: 1.05; }
.report-metric-split { display: grid; gap: .25rem; color: #6f787f; font-size: .78rem; margin-top: .7rem; }
.report-info-table th,
.report-info-table td { padding: .68rem .85rem; vertical-align: middle; }
.report-info-table th { color: #65717a; font-weight: 600; width: 58%; }
.report-info-table td { color: #202b33; font-weight: 800; text-align: right; }
.report-chart-wrap {
    height: 240px;
    position: relative;
}
.report-chart-wrap canvas {
    max-height: 240px;
}
@media (max-width: 767.98px) {
    .report-exec-title { font-size: 1.15rem; }
    .report-kpi-value { font-size: 1.25rem; }
    .report-metric-value { font-size: 1.3rem; }
    .report-chart-wrap { height: 220px; }
}
</style>

<div class="tab-content reports-tab-content">
        <div class="tab-pane fade show active" id="executive">
            @include('backend.admin.reports.partials.executive-dashboard')

            <div class="workspace-section-title mb-2">{{ _lang('Reports') }}</div>
            <div class="row mb-2">
                @foreach(($reportGroups['executive']['items'] ?? []) as $item)
                    <div class="col-md-6 col-xl-3 mb-2">
                        <a class="btn btn-outline-primary btn-block btn-sm" href="{{ $item['route'] }}">{{ $item['label'] }}</a>
                    </div>
                @endforeach
                <div class="col-md-6 col-xl-3 mb-2">
                    <a class="btn btn-outline-primary btn-block btn-sm" href="{{ route('reports.loan_due_report') }}">{{ _lang('Loan Due') }}</a>
                </div>
                <div class="col-md-6 col-xl-3 mb-2">
                    <a class="btn btn-outline-primary btn-block btn-sm" href="{{ route('reports.transactions_report') }}">{{ _lang('Transactions') }}</a>
                </div>
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
@endsection

@section('js-script')
@php
    $cashChartLabels = [_lang('Credits'), _lang('Debits'), _lang('Expenses'), _lang('Net')];
    $cashChartValues = [
        (float) ($reportHighlights['monthly_credits'] ?? 0),
        (float) ($reportHighlights['monthly_debits'] ?? 0),
        (float) ($reportHighlights['expenses_this_month_amount'] ?? 0),
        (float) ($reportHighlights['net_cash_movement'] ?? 0),
    ];
    $pressureChartLabels = [_lang('Active Loans'), _lang('Pending Loans'), _lang('Due Today'), _lang('Overdue'), _lang('Pending Bank')];
    $pressureChartValues = [
        (int) ($reportHighlights['active_loans'] ?? 0),
        (int) ($reportHighlights['pending_loans'] ?? 0),
        (int) ($reportHighlights['due_today'] ?? 0),
        (int) ($reportHighlights['overdue_repayments'] ?? 0),
        (int) ($reportHighlights['pending_bank_transactions'] ?? 0),
    ];
@endphp
<script src="{{ asset('public/backend/plugins/chartJs/chart.min.js') }}"></script>
<script>
(function () {
    if (typeof Chart === 'undefined') return;

    var cashCanvas = document.getElementById('reportCashMovementChart');
    var pressureCanvas = document.getElementById('reportPortfolioPressureChart');

    var cashData = {
        labels: @json($cashChartLabels),
        values: @json($cashChartValues),
    };

    var pressureData = {
        labels: @json($pressureChartLabels),
        values: @json($pressureChartValues),
    };

    if (cashCanvas) {
        new Chart(cashCanvas.getContext('2d'), {
            type: 'bar',
            data: {
                labels: cashData.labels,
                datasets: [{
                    data: cashData.values,
                    backgroundColor: ['#1A8E8F', '#C14953', '#B7791F', '#3F686D'],
                    borderRadius: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });
    }

    if (pressureCanvas) {
        new Chart(pressureCanvas.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: pressureData.labels,
                datasets: [{
                    data: pressureData.values,
                    backgroundColor: ['#1A8E8F', '#B7791F', '#E0B341', '#C14953', '#3F686D'],
                    borderWidth: 0,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '62%',
                plugins: { legend: { position: 'bottom' } }
            }
        });
    }
})();
</script>
@endsection
