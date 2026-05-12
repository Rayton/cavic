@extends('layouts.app')

@php
    $activeReportTab = request('tab', 'executive');
@endphp

@section('workspace_top_tabs')
@include('backend.admin.partials.module-tabs', [
    'variant' => 'top-strip',
    'role' => 'navigation',
    'tabs' => [
        ['label' => _lang('Executive KPIs'), 'target' => '#executive', 'active' => $activeReportTab === 'executive'],
        ['label' => _lang('Loan Portfolio'), 'target' => '#portfolio', 'active' => $activeReportTab === 'portfolio'],
        ['label' => _lang('Loan Repayments Report'), 'target' => '#collections', 'active' => $activeReportTab === 'collections'],
        ['label' => _lang('Accounts'), 'target' => '#accounts', 'active' => $activeReportTab === 'accounts'],
        ['label' => _lang('Transactions'), 'target' => '#transactions', 'active' => $activeReportTab === 'transactions'],
        ['label' => _lang('Expenses'), 'target' => '#expenses', 'active' => $activeReportTab === 'expenses'],
        ['label' => _lang('Banking'), 'target' => '#banking', 'active' => $activeReportTab === 'banking'],
        ['label' => _lang('Branch Performance'), 'target' => '#branch-performance', 'active' => $activeReportTab === 'branch-performance'],
        ['label' => _lang('Revenue'), 'target' => '#revenue', 'active' => $activeReportTab === 'revenue'],
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
.report-action-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: .75rem;
    margin-bottom: 1rem;
}
.report-action-btn {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
    min-height: 48px;
    border: 1px solid #dfe8e7;
    border-radius: 8px;
    background: #fff;
    color: #243036;
    font-weight: 700;
    padding: .75rem .9rem;
    text-decoration: none;
}
.report-action-btn:hover {
    border-color: #3F686D;
    color: #243036;
    text-decoration: none;
}
.report-action-btn i {
    color: #3F686D;
    font-size: .9rem;
}
.report-portfolio-meter {
    height: 8px;
    overflow: hidden;
    border-radius: 999px;
    background: #eef2f2;
}
.report-portfolio-meter span {
    display: block;
    height: 100%;
    border-radius: inherit;
    background: #3F686D;
}
.report-portfolio-meter.danger span { background: #b4232f; }
.report-inline-filter {
    border: 1px solid #e7eeee;
    border-radius: 8px;
    background: #fbfdfd;
    padding: 1rem 1rem .25rem;
}
.reports-inline-table-card .table-responsive {
    border-radius: 8px;
}
.reports-inline-table-card .workspace-mini-table td,
.reports-inline-table-card .workspace-mini-table th {
    vertical-align: middle;
}
.reports-inline-table-card .report-header {
    display: none;
}
@media (max-width: 767.98px) {
    .report-exec-title { font-size: 1.15rem; }
    .report-kpi-value { font-size: 1.25rem; }
    .report-metric-value { font-size: 1.3rem; }
    .report-chart-wrap { height: 220px; }
}
</style>

<div class="tab-content reports-tab-content">
        <div class="tab-pane fade {{ $activeReportTab === 'executive' ? 'show active' : '' }}" id="executive">
            @include('backend.admin.reports.partials.executive-dashboard')

            <div class="workspace-section-title mb-2">{{ _lang('Reports') }}</div>
            <div class="report-action-grid">
                @foreach(($reportGroups['executive']['items'] ?? []) as $item)
                    <a class="report-action-btn ajax-modal" href="{{ $item['route'] }}" data-title="{{ $item['label'] }}" data-fullscreen="true">
                        <span>{{ $item['label'] }}</span>
                        <i class="ti-arrow-right"></i>
                    </a>
                @endforeach
                <a class="report-action-btn ajax-modal" href="{{ route('reports.loan_due_report') }}" data-title="{{ _lang('Loan Due Report') }}" data-fullscreen="true">
                    <span>{{ _lang('Loan Due') }}</span>
                    <i class="ti-arrow-right"></i>
                </a>
                <a class="report-action-btn ajax-modal" href="{{ route('reports.transactions_report') }}" data-title="{{ _lang('Transactions Report') }}" data-fullscreen="true">
                    <span>{{ _lang('Transactions') }}</span>
                    <i class="ti-arrow-right"></i>
                </a>
            </div>
        </div>
        <div class="tab-pane fade {{ $activeReportTab === 'portfolio' ? 'show active' : '' }}" id="portfolio">
            <div class="row mb-3">
                <div class="col-md-6 col-xl-3 mb-3">
                    <div class="report-metric-card">
                        <div class="report-metric-label">{{ _lang('Outstanding Portfolio') }}</div>
                        <div class="report-metric-value">{{ money_format_2($reportHighlights['portfolio_outstanding'] ?? 0) }}</div>
                        <div class="report-metric-split">
                            <span>{{ _lang('Active Loans') }}: {{ number_format($reportHighlights['active_loans'] ?? 0) }}</span>
                            <span>{{ _lang('Disbursed') }}: {{ money_format_2($reportHighlights['active_portfolio_amount'] ?? 0) }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3 mb-3">
                    <div class="report-metric-card active">
                        <div class="report-metric-label">{{ _lang('Repayment Rate') }}</div>
                        <div class="report-metric-value">{{ number_format($portfolioRepaymentRate ?? 0, 1) }}%</div>
                        <div class="report-portfolio-meter mt-3"><span style="width: {{ min(100, max(0, $portfolioRepaymentRate ?? 0)) }}%"></span></div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3 mb-3">
                    <div class="report-metric-card {{ ($portfolioParRatio ?? 0) > 0 ? 'critical' : 'active' }}">
                        <div class="report-metric-label">{{ _lang('PAR Exposure') }}</div>
                        <div class="report-metric-value">{{ number_format($portfolioParRatio ?? 0, 1) }}%</div>
                        <div class="report-metric-split">
                            <span>{{ _lang('Overdue') }}: {{ money_format_2($reportHighlights['overdue_amount'] ?? 0) }}</span>
                            <span>{{ _lang('Items') }}: {{ number_format($reportHighlights['overdue_repayments'] ?? 0) }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3 mb-3">
                    <div class="report-metric-card review">
                        <div class="report-metric-label">{{ _lang('Pipeline') }}</div>
                        <div class="report-metric-value">{{ number_format($reportHighlights['pending_loans'] ?? 0) }}</div>
                        <div class="report-metric-split">
                            <span>{{ _lang('Pending approval') }}</span>
                            <span>{{ _lang('Due Today') }}: {{ number_format($reportHighlights['due_today'] ?? 0) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-lg-6 mb-3">
                    <div class="card workspace-section-card h-100 mb-0">
                        <div class="card-header">{{ _lang('Loan Status Mix') }}</div>
                        <div class="card-body">
                            <div class="report-chart-wrap">
                                <canvas id="reportLoanStatusChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 mb-3">
                    <div class="card workspace-section-card h-100 mb-0">
                        <div class="card-header">{{ _lang('Portfolio Aging') }}</div>
                        <div class="card-body">
                            <div class="report-chart-wrap">
                                <canvas id="reportPortfolioAgingChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-lg-6 mb-3">
                    <div class="card workspace-section-card h-100 mb-0">
                        <div class="card-header">{{ _lang('Product Exposure') }}</div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-sm report-mini-table mb-0">
                                    <thead>
                                        <tr>
                                            <th>{{ _lang('Product') }}</th>
                                            <th>{{ _lang('Loans') }}</th>
                                            <th>{{ _lang('Disbursed') }}</th>
                                            <th>{{ _lang('Outstanding') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($loanProductPortfolio as $product)
                                            <tr>
                                                <td>{{ $product->loan_product->name ?? _lang('Unassigned') }}</td>
                                                <td>{{ number_format($product->active_loans) }}</td>
                                                <td>{{ money_format_2($product->disbursed_amount ?? 0) }}</td>
                                                <td>{{ money_format_2($product->outstanding_amount ?? 0) }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="4" class="text-center text-muted">{{ _lang('No active loan portfolio found') }}</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 mb-3">
                    <div class="card workspace-section-card h-100 mb-0">
                        <div class="card-header">{{ _lang('Overdue Watchlist') }}</div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-sm report-mini-table mb-0">
                                    <thead>
                                        <tr>
                                            <th>{{ _lang('Loan') }}</th>
                                            <th>{{ _lang('Borrower') }}</th>
                                            <th>{{ _lang('Missed') }}</th>
                                            <th>{{ _lang('Overdue') }}</th>
                                            <th>{{ _lang('Since') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($portfolioWatchlist as $item)
                                            <tr>
                                                <td>{{ $item->loan->loan_id ?? _lang('N/A') }}</td>
                                                <td>{{ $item->loan->borrower->name ?? _lang('N/A') }}</td>
                                                <td>{{ number_format($item->missed_installments) }}</td>
                                                <td>{{ money_format_2($item->overdue_amount ?? 0) }}</td>
                                                <td>{{ $item->earliest_due_date ? date(get_date_format(), strtotime($item->earliest_due_date)) : '-' }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="5" class="text-center text-muted">{{ _lang('No overdue portfolio items found') }}</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card workspace-section-card cavic-datatable-card reports-inline-table-card mb-3">
                <div class="card-header">{{ _lang('Loan Report') }}</div>
                <div class="card-body">
                    <form class="report-inline-filter validate mb-3" method="get" action="{{ route('reports.index') }}#portfolio" autocomplete="off">
                        <input type="hidden" name="tab" value="portfolio">
                        <div class="row">
                            <div class="col-xl-2 col-lg-4">
                                <div class="form-group">
                                    <label class="control-label">{{ _lang('Start Date') }}</label>
                                    <input type="text" class="form-control datepicker" name="date1" value="{{ $loanReportFilters['date1'] ?? '' }}" readonly required>
                                </div>
                            </div>
                            <div class="col-xl-2 col-lg-4">
                                <div class="form-group">
                                    <label class="control-label">{{ _lang('End Date') }}</label>
                                    <input type="text" class="form-control datepicker" name="date2" value="{{ $loanReportFilters['date2'] ?? '' }}" readonly required>
                                </div>
                            </div>
                            <div class="col-xl-2 col-lg-4">
                                <div class="form-group">
                                    <label class="control-label">{{ _lang('Loan Type') }}</label>
                                    <select class="form-control auto-select" data-selected="{{ $loanReportFilters['loan_type'] ?? '' }}" name="loan_type">
                                        <option value="">{{ _lang('All') }}</option>
                                        @foreach($loanReportProducts as $loanProduct)
                                            <option value="{{ $loanProduct->id }}">{{ $loanProduct->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-xl-2 col-lg-4">
                                <div class="form-group">
                                    <label class="control-label">{{ _lang('Status') }}</label>
                                    <select class="form-control auto-select" data-selected="{{ $loanReportFilters['status'] ?? '' }}" name="status">
                                        <option value="">{{ _lang('All') }}</option>
                                        <option value="0">{{ _lang('Pending') }}</option>
                                        <option value="1">{{ _lang('Approved') }}</option>
                                        <option value="2">{{ _lang('Completed') }}</option>
                                        <option value="3">{{ _lang('Cancelled') }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-xl-2 col-lg-4">
                                <div class="form-group">
                                    <label class="control-label">{{ _lang('Member No') }}</label>
                                    <input type="text" class="form-control" name="member_no" value="{{ $loanReportFilters['member_no'] ?? '' }}">
                                </div>
                            </div>
                            <div class="col-xl-2 col-lg-4">
                                <button type="submit" class="btn btn-outline-primary btn-xs btn-block mt-26"><i class="ti-filter"></i>&nbsp;{{ _lang('Filter') }}</button>
                            </div>
                        </div>
                    </form>

                    @php $date_format = get_date_format(); @endphp
                    <div class="report-header">
                       <img src="{{ get_logo() }}" class="logo"/>
                       <h4>{{ _lang('Loan Report') }}</h4>
                       <h5>{{ date($date_format, strtotime($loanReportFilters['date1'])) }} {{ _lang('to') }} {{ date($date_format, strtotime($loanReportFilters['date2'])) }}</h5>
                    </div>

                    <div class="table-responsive">
                        <table id="reports_inline_loan_report_table" class="table table-bordered table-striped table-export dashboard-table-compact workspace-mini-table cavic-data-table reports-inline-data-table mb-0" data-export-filename="Reports_Loan_Report">
                            <thead>
                                <tr>
                                    <th data-total-label="{{ _lang('Total') }}">{{ _lang('Loan ID') }}</th>
                                    <th>{{ _lang('Member No') }}</th>
                                    <th>{{ _lang('Created') }}</th>
                                    <th>{{ _lang('Loan Product') }}</th>
                                    <th>{{ _lang('Borrower') }}</th>
                                    <th class="text-right" data-sum="1">{{ _lang('Applied Amount') }}</th>
                                    <th class="text-right" data-sum="1">{{ _lang('Due Amount') }}</th>
                                    <th>{{ _lang('Status') }}</th>
                                    <th class="text-center" data-no-export="1">{{ _lang('Details') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($inlineLoanReport as $loan)
                                    @php
                                        $loanCurrency = currency($loan->currency->name);
                                        $dueAmount = ($loan->total_payable ?? $loan->applied_amount) - ($loan->total_paid ?? 0);
                                    @endphp
                                    <tr>
                                        <td>{{ $loan->loan_id }}</td>
                                        <td>{{ $loan->borrower->member_no }}</td>
                                        <td>{{ $loan->created_at }}</td>
                                        <td>{{ $loan->loan_product->name }}</td>
                                        <td>{{ $loan->borrower->name }}<br>{{ $loan->borrower->email }}</td>
                                        <td class="text-right">{{ decimalPlace($loan->applied_amount, $loanCurrency) }}</td>
                                        <td class="text-right">{{ decimalPlace($dueAmount, $loanCurrency) }}</td>
                                        <td>
                                            @if($loan->status == 0)
                                                <span class="workspace-status-chip pending">{{ _lang('Pending') }}</span>
                                            @elseif($loan->status == 1)
                                                <span class="workspace-status-chip active">{{ _lang('Approved') }}</span>
                                            @elseif($loan->status == 2)
                                                <span class="workspace-status-chip active">{{ _lang('Completed') }}</span>
                                            @elseif($loan->status == 3)
                                                <span class="workspace-status-chip critical">{{ _lang('Cancelled') }}</span>
                                            @endif
                                        </td>
                                        <td class="text-center">@include('backend.admin.partials.table-actions', ['items' => [['label' => _lang('View'), 'url' => route('loans.show', $loan->id), 'icon' => 'ti-eye', 'target' => '_blank']]])</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="9" class="text-center text-muted">{{ _lang('No loans found for these filters') }}</td></tr>
                                @endforelse
                            </tbody>
                            <tfoot><tr class="table-totals-row"><td></td><td></td><td></td><td></td><td></td><td class="text-right"></td><td class="text-right"></td><td></td><td></td></tr></tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="tab-pane fade {{ $activeReportTab === 'collections' ? 'show active' : '' }}" id="collections">
            <div class="card workspace-section-card cavic-datatable-card reports-inline-table-card mb-3">
                <div class="card-header">{{ _lang('Loan Repayments Report') }}</div>
                <div class="card-body">
                    <form class="report-inline-filter validate mb-3" method="get" action="{{ route('reports.index') }}#collections" autocomplete="off">
                        <input type="hidden" name="tab" value="collections">
                        <div class="row">
                            <div class="col-xl-4 col-lg-6">
                                <div class="form-group">
                                    <label class="control-label">{{ _lang('Loan ID') }}</label>
                                    <select class="form-control auto-select select2" data-selected="{{ $repaymentReportLoanId ?? '' }}" name="repayment_loan_id" required>
                                        <option value="">{{ _lang('Select One') }}</option>
                                        @foreach($repaymentReportLoans as $loan)
                                            @php
                                                $loanCurrency = currency($loan->currency->name);
                                                $dueAmount = ($loan->applied_amount ?? 0) - ($loan->total_paid ?? 0);
                                            @endphp
                                            <option value="{{ $loan->id }}">{{ $loan->loan_id }} ({{ $loan->borrower->name }}) ({{ _lang('Total Due') }} {{ decimalPlace($dueAmount, $loanCurrency) }})</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-xl-2 col-lg-4">
                                <button type="submit" class="btn btn-outline-primary btn-xs btn-block mt-26"><i class="ti-filter"></i>&nbsp;{{ _lang('Filter') }}</button>
                            </div>
                        </div>
                    </form>

                    @php $date_format = get_date_format(); @endphp
                    <div class="report-header">
                       <img src="{{ get_logo() }}" class="logo"/>
                       <h4>{{ _lang('Loan Repayments Report') }}</h4>

                       @if($inlineLoanRepaymentReport)
                           @php
                               $selectedLoanCurrency = currency($inlineLoanRepaymentReport->currency->name);
                               $selectedLoanDueAmount = ($inlineLoanRepaymentReport->applied_amount ?? 0) - ($inlineLoanRepaymentReport->total_paid ?? 0);
                           @endphp
                           <p>{{ _lang('Loan ID') }}: {{ $inlineLoanRepaymentReport->loan_id }}</p>
                           <p>{{ _lang('Member No') }}: {{ $inlineLoanRepaymentReport->borrower->member_no }}, {{ _lang('Borrower') }}: {{ $inlineLoanRepaymentReport->borrower->name }}</p>
                           <p>{{ _lang('Applied Amount') }}: {{ decimalPlace($inlineLoanRepaymentReport->applied_amount, $selectedLoanCurrency) }}, {{ _lang('Due Amount') }}: {{ decimalPlace($selectedLoanDueAmount, $selectedLoanCurrency) }}</p>
                       @endif
                    </div>

                    <div class="table-responsive">
                        <table id="reports_inline_loan_repayments_table" class="table table-bordered table-striped table-export dashboard-table-compact workspace-mini-table cavic-data-table reports-inline-data-table mb-0" data-export-filename="Reports_Loan_Repayments_Report">
                            <thead>
                                <tr>
                                    <th data-total-label="{{ _lang('Total') }}">{{ _lang('Payment Date') }}</th>
                                    <th>{{ _lang('Loan') }}</th>
                                    <th class="text-right" data-sum="1">{{ _lang('Principal Amount') }}</th>
                                    <th class="text-right" data-sum="1">{{ _lang('Interest') }}</th>
                                    <th class="text-right" data-sum="1">{{ _lang('Late Penalties') }}</th>
                                    <th class="text-right" data-sum="1">{{ _lang('Total Amount') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($inlineLoanRepaymentReport)
                                    @forelse($inlineLoanRepaymentReport->payments as $loanPayment)
                                        <tr>
                                            <td>{{ $loanPayment->paid_at }}</td>
                                            <td>{{ $inlineLoanRepaymentReport->loan_id }}</td>
                                            <td class="text-right">{{ decimalPlace($loanPayment->repayment_amount - $loanPayment->interest, $selectedLoanCurrency) }}</td>
                                            <td class="text-right">{{ decimalPlace($loanPayment->interest, $selectedLoanCurrency) }}</td>
                                            <td class="text-right">{{ decimalPlace($loanPayment->late_penalties, $selectedLoanCurrency) }}</td>
                                            <td class="text-right">{{ decimalPlace($loanPayment->total_amount, $selectedLoanCurrency) }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="6" class="text-center text-muted">{{ _lang('No repayments found for this loan') }}</td></tr>
                                    @endforelse
                                @else
                                    <tr><td colspan="6" class="text-center text-muted">{{ _lang('Select a loan to view repayments') }}</td></tr>
                                @endif
                            </tbody>
                            <tfoot><tr class="table-totals-row"><td></td><td></td><td class="text-right"></td><td class="text-right"></td><td class="text-right"></td><td class="text-right"></td></tr></tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="tab-pane fade {{ $activeReportTab === 'accounts' ? 'show active' : '' }}" id="accounts">
            <div class="report-action-grid">
                @foreach(($reportGroups['accounts']['items'] ?? []) as $item)
                    <a class="report-action-btn ajax-modal" href="{{ $item['route'] }}" data-title="{{ $item['label'] }}" data-fullscreen="true">
                        <span>{{ $item['label'] }}</span>
                        <i class="ti-arrow-right"></i>
                    </a>
                @endforeach
            </div>
        </div>
        <div class="tab-pane fade {{ $activeReportTab === 'transactions' ? 'show active' : '' }}" id="transactions">
            <div class="report-action-grid">
                @foreach(($reportGroups['transactions']['items'] ?? []) as $item)
                    <a class="report-action-btn ajax-modal" href="{{ $item['route'] }}" data-title="{{ $item['label'] }}" data-fullscreen="true">
                        <span>{{ $item['label'] }}</span>
                        <i class="ti-arrow-right"></i>
                    </a>
                @endforeach
            </div>
        </div>
        <div class="tab-pane fade {{ $activeReportTab === 'expenses' ? 'show active' : '' }}" id="expenses">
            <div class="report-action-grid">
                @foreach(($reportGroups['expenses']['items'] ?? []) as $item)
                    <a class="report-action-btn ajax-modal" href="{{ $item['route'] }}" data-title="{{ $item['label'] }}" data-fullscreen="true">
                        <span>{{ $item['label'] }}</span>
                        <i class="ti-arrow-right"></i>
                    </a>
                @endforeach
            </div>
        </div>
        <div class="tab-pane fade {{ $activeReportTab === 'banking' ? 'show active' : '' }}" id="banking">
            <div class="report-action-grid">
                @foreach(($reportGroups['banking']['items'] ?? []) as $item)
                    <a class="report-action-btn ajax-modal" href="{{ $item['route'] }}" data-title="{{ $item['label'] }}" data-fullscreen="true">
                        <span>{{ $item['label'] }}</span>
                        <i class="ti-arrow-right"></i>
                    </a>
                @endforeach
            </div>
        </div>
        <div class="tab-pane fade {{ $activeReportTab === 'branch-performance' ? 'show active' : '' }}" id="branch-performance">
            <div class="report-action-grid">
                @foreach(($reportGroups['branch_performance']['items'] ?? []) as $item)
                    <a class="report-action-btn ajax-modal" href="{{ $item['route'] }}" data-title="{{ $item['label'] }}" data-fullscreen="true">
                        <span>{{ $item['label'] }}</span>
                        <i class="ti-arrow-right"></i>
                    </a>
                @endforeach
            </div>

            <div class="card workspace-section-card mb-3">
                <div class="card-header">{{ _lang('Branch Performance Snapshot') }}</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered report-mini-table mb-0">
                            <thead>
                                <tr>
                                    <th>{{ _lang('Branch') }}</th>
                                    <th>{{ _lang('Members') }}</th>
                                    <th>{{ _lang('Pending Members') }}</th>
                                    <th>{{ _lang('Active Loans') }}</th>
                                    <th>{{ _lang('Portfolio') }}</th>
                                    <th>{{ _lang('Overdue') }}</th>
                                    <th>{{ _lang('Overdue Amount') }}</th>
                                    <th>{{ _lang('Status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($branchReportSnapshot as $branch)
                                    <tr>
                                        <td>{{ $branch->name }}</td>
                                        <td>{{ number_format($branch->active_members) }}</td>
                                        <td>{{ number_format($branch->pending_members) }}</td>
                                        <td>{{ number_format($branch->active_loans) }}</td>
                                        <td>{{ money_format_2($branch->portfolio_amount ?? 0) }}</td>
                                        <td>{{ number_format($branch->overdue_repayments) }}</td>
                                        <td>{{ money_format_2($branch->overdue_amount ?? 0) }}</td>
                                        <td><span class="workspace-status-chip {{ $branch->pressure_score > 0 ? 'review' : 'active' }}">{{ $branch->pressure_score > 0 ? _lang('Review') : _lang('OK') }}</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="8" class="text-center text-muted">{{ _lang('No branch reporting data found') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="tab-pane fade {{ $activeReportTab === 'revenue' ? 'show active' : '' }}" id="revenue">
            <div class="card workspace-section-card cavic-datatable-card reports-inline-table-card mb-3">
                <div class="card-header">{{ _lang('Revenue Report') }}</div>
                <div class="card-body">
                    <form class="report-inline-filter validate mb-3" method="get" action="{{ route('reports.index') }}#revenue" autocomplete="off">
                        <input type="hidden" name="tab" value="revenue">
                        <div class="row">
                            <div class="col-xl-2 col-lg-4">
                                <div class="form-group">
                                    <label class="control-label">{{ _lang('Year') }}</label>
                                    <select class="form-control auto-select" name="revenue_year" data-selected="{{ $revenueReportFilters['year'] ?? date('Y') }}" required>
                                        @for($y = 2020; $y <= date('Y'); $y++)
                                            <option value="{{ $y }}">{{ $y }}</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>

                            <div class="col-xl-2 col-lg-4">
                                <div class="form-group">
                                    <label class="control-label">{{ _lang('Month') }}</label>
                                    <select class="form-control auto-select" name="revenue_month" data-selected="{{ $revenueReportFilters['month'] ?? date('m') }}" required>
                                        @for($i = 1; $i <= 12; $i++)
                                            <option value="{{ $i }}">{{ date('F', mktime(0, 0, 0, $i, 10)) }}</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>

                            <div class="col-xl-3 col-lg-4">
                                <div class="form-group">
                                    <label class="control-label">{{ _lang('Currency') }}</label>
                                    <select class="form-control auto-select" data-selected="{{ $revenueReportFilters['currency_id'] ?? base_currency_id() }}" name="revenue_currency_id" required>
                                        @foreach($revenueCurrencies as $currency)
                                            <option value="{{ $currency->id }}">{{ $currency->full_name }} ({{ $currency->name }})</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-xl-2 col-lg-4">
                                <button type="submit" class="btn btn-outline-primary btn-xs btn-block mt-26"><i class="ti-filter"></i>&nbsp;{{ _lang('Filter') }}</button>
                            </div>
                        </div>
                    </form>

                    <div class="report-header">
                        <h4>{{ _lang('Revenue Report') }} {{ _lang('of') }} {{ date('F', mktime(0, 0, 0, $revenueReportFilters['month'] ?? date('m'), 10)) }} {{ $revenueReportFilters['year'] ?? date('Y') }}</h4>
                    </div>

                    @php
                        $revenueCurrency = currency(get_currency($revenueReportFilters['currency_id'] ?? base_currency_id())->name);
                        $revenueTotal = 0;
                    @endphp
                    <div class="table-responsive">
                        <table id="reports_inline_revenue_table" class="table table-bordered table-striped table-export dashboard-table-compact workspace-mini-table cavic-data-table reports-inline-data-table mb-0" data-export-filename="Reports_Revenue_Report">
                            <thead>
                                <tr>
                                    <th data-total-label="{{ _lang('Total') }}">{{ _lang('Revenue Type') }}</th>
                                    <th class="text-right" data-sum="1">{{ _lang('Amount') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($inlineRevenueReport as $revenue)
                                    @php $revenueTotal += $revenue->amount; @endphp
                                    <tr>
                                        <td>{{ str_replace('_', ' ', $revenue->type) }}</td>
                                        <td class="text-right">{{ decimalPlace($revenue->amount, $revenueCurrency) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="2" class="text-center text-muted">{{ _lang('No revenue found for these filters') }}</td></tr>
                                @endforelse
                            </tbody>
                            <tfoot><tr class="table-totals-row"><td>{{ _lang('Total') }}</td><td class="text-right">{{ decimalPlace($revenueTotal, $revenueCurrency) }}</td></tr></tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
</div>

@include('backend.admin.partials.cavic-datatable-standard')
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
    $loanStatusLabels = [_lang('Pending'), _lang('Active'), _lang('Closed'), _lang('Cancelled')];
    $loanStatusValues = [
        (int) data_get($loanStatusSummary->get(0), 'total', 0),
        (int) data_get($loanStatusSummary->get(1), 'total', 0),
        (int) data_get($loanStatusSummary->get(2), 'total', 0),
        (int) data_get($loanStatusSummary->get(3), 'total', 0),
    ];
    $portfolioAgingLabels = collect($portfolioAgingBuckets ?? [])->pluck('label')->values();
    $portfolioAgingValues = collect($portfolioAgingBuckets ?? [])->pluck('amount')->map(fn ($amount) => (float) $amount)->values();
@endphp
<script src="{{ asset('public/backend/plugins/chartJs/chart.min.js') }}"></script>
<script>
(function ($) {
    if (typeof window.cavicInitStaticDataTables === 'function') {
        window.cavicInitStaticDataTables('.reports-inline-data-table', 'Reports');
    }

    function initReportModalTables($scope) {
        if (!$.fn.DataTable) return;

        $scope.find('.report-table').each(function () {
            if ($.fn.DataTable.isDataTable(this)) return;

            var headerText = $(this).prev('.report-header').html() || '';
            $(this).DataTable({
                responsive: true,
                bAutoWidth: false,
                ordering: false,
                lengthChange: false,
                dom:
                    "<'row'<'col-sm-12 col-md-6'B><'col-sm-12 col-md-6'f>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                language: {
                    emptyTable: $lang_no_data_found,
                    info: $lang_showing + " _START_ " + $lang_to + " _END_ " + $lang_of + " _TOTAL_ " + $lang_entries,
                    infoEmpty: $lang_showing_0_to_0_of_0_entries,
                    infoFiltered: "(filtered from _MAX_ total entries)",
                    lengthMenu: $lang_show + " _MENU_ " + $lang_entries,
                    loadingRecords: $lang_loading,
                    processing: $lang_processing,
                    search: $lang_search,
                    zeroRecords: $lang_no_matching_records_found,
                    paginate: {
                        first: $lang_first,
                        last: $lang_last,
                        previous: "<i class='fas fa-angle-left'></i>",
                        next: "<i class='fas fa-angle-right'></i>"
                    },
                    buttons: {
                        copy: $lang_copy,
                        excel: $lang_excel,
                        pdf: $lang_pdf,
                        print: $lang_print
                    }
                },
                drawCallback: function () {
                    $(".dataTables_paginate > .pagination").addClass("pagination-bordered");
                },
                buttons: [
                    'copy',
                    'excel',
                    'pdf',
                    {
                        extend: 'print',
                        title: '',
                        customize: function (win) {
                            $(win.document.body)
                                .css('font-size', '10pt')
                                .prepend('<div class="text-center">' + headerText + '</div>');

                            $(win.document.body).find('table')
                                .addClass('compact')
                                .css('font-size', 'inherit');
                        }
                    }
                ]
            });
        });
    }

    function initReportModalContent($scope) {
        if (!$scope.length) return;

        if (typeof init_datepicker === 'function') {
            init_datepicker($scope);
        }

        $scope.find('.auto-select').each(function () {
            $(this).val($(this).data('selected')).trigger('change');
        });

        if ($.fn.select2) {
            $scope.find('select.select2').select2({
                dropdownParent: $scope.closest('.modal-content')
            });
        }

        if ($.fn.parsley) {
            $scope.find('form.validate').parsley();
        }

        initReportModalTables($scope);

        if (window.TableExportTotals) {
            window.TableExportTotals.refresh();
        }
    }

    function hideRouteLoader() {
        if (window.CavicRouteLoader) {
            window.CavicRouteLoader.hide();
        }
    }

    $(document).on('shown.bs.modal', '#main_modal', function () {
        var $shell = $(this).find('.report-modal-shell');
        if ($shell.length) {
            setTimeout(function () {
                initReportModalContent($shell);
            }, 50);
        }
    });

    $(document).on('submit', '#main_modal .report-modal-shell form', function (event) {
        event.preventDefault();
        hideRouteLoader();

        var $form = $(this);
        if ($form.data('submitting')) return;
        if ($.fn.parsley && $form.hasClass('validate') && !$form.parsley().validate()) {
            hideRouteLoader();
            return;
        }

        $form.data('submitting', true);

        $.ajax({
            url: $form.attr('action'),
            type: ($form.attr('method') || 'GET').toUpperCase(),
            data: $form.serialize(),
            beforeSend: function () {
                $("#preloader").css("display", "block");
            },
            success: function (html) {
                var $modal = $('#main_modal');
                $modal.find('.modal-body').html(html);
                $modal.find('.alert-primary, .alert-danger').addClass('d-none');
                initReportModalContent($modal.find('.report-modal-shell'));
            },
            error: function (request) {
                var message = request.responseJSON && request.responseJSON.message
                    ? request.responseJSON.message
                    : @json(_lang('Unable to load report'));
                $('#main_modal .alert-danger').html('<span>' + message + '</span>').removeClass('d-none');
                $('#main_modal .alert-primary').addClass('d-none');
            },
            complete: function () {
                $form.data('submitting', false);
                $("#preloader").css("display", "none");
                hideRouteLoader();
            }
        });
    });

    $(document).on('shown.bs.tab', 'a[data-toggle="tab"][href="#portfolio"], a[data-toggle="tab"][href="#collections"], a[data-toggle="tab"][href="#revenue"]', function () {
        if ($.fn.dataTable) {
            $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust().responsive.recalc();
        }
    });
})(jQuery);

(function () {
    if (typeof Chart === 'undefined') return;

    var cashCanvas = document.getElementById('reportCashMovementChart');
    var pressureCanvas = document.getElementById('reportPortfolioPressureChart');
    var loanStatusCanvas = document.getElementById('reportLoanStatusChart');
    var portfolioAgingCanvas = document.getElementById('reportPortfolioAgingChart');

    var cashData = {
        labels: @json($cashChartLabels),
        values: @json($cashChartValues),
    };

    var pressureData = {
        labels: @json($pressureChartLabels),
        values: @json($pressureChartValues),
    };

    var loanStatusData = {
        labels: @json($loanStatusLabels),
        values: @json($loanStatusValues),
    };

    var portfolioAgingData = {
        labels: @json($portfolioAgingLabels),
        values: @json($portfolioAgingValues),
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

    if (loanStatusCanvas) {
        new Chart(loanStatusCanvas.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: loanStatusData.labels,
                datasets: [{
                    data: loanStatusData.values,
                    backgroundColor: ['#B7791F', '#1A8E8F', '#3F686D', '#C14953'],
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

    if (portfolioAgingCanvas) {
        new Chart(portfolioAgingCanvas.getContext('2d'), {
            type: 'bar',
            data: {
                labels: portfolioAgingData.labels,
                datasets: [{
                    data: portfolioAgingData.values,
                    backgroundColor: ['#1A8E8F', '#E0B341', '#B7791F', '#C14953', '#9F1239', '#6B1024'],
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
})();
</script>
@endsection
