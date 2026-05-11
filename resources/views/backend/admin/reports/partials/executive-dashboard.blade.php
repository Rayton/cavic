@php
    $riskTone = $attentionTotal > 0 ? 'review' : 'active';
    $netMovement = $reportHighlights['net_cash_movement'] ?? 0;
    $netTone = $netMovement >= 0 ? 'active' : 'review';
@endphp

<div class="report-exec-toolbar mb-3">
    <div>
        <div class="report-exec-title">{{ _lang('Executive Dashboard') }}</div>
        <div class="report-exec-period">{{ $periodLabel }}</div>
    </div>
    <div class="report-exec-actions">
        <a href="{{ route('reports.cash_in_hand') }}" class="btn btn-outline-primary btn-sm">{{ _lang('Cash') }}</a>
        <a href="{{ route('reports.loan_due_report') }}" class="btn btn-outline-primary btn-sm">{{ _lang('Overdue') }}</a>
        <a href="{{ route('reports.transactions_report') }}" class="btn btn-outline-primary btn-sm">{{ _lang('Transactions') }}</a>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-6 col-xl-3 mb-3">
        <div class="report-metric-card {{ $riskTone }}">
            <div class="report-metric-label">{{ _lang('Items Needing Attention') }}</div>
            <div class="report-metric-value">{{ number_format($attentionTotal) }}</div>
            <div class="report-metric-split">
                <span>{{ _lang('Pending Members') }}: {{ number_format($reportHighlights['pending_members'] ?? 0) }}</span>
                <span>{{ _lang('Pending Loans') }}: {{ number_format($reportHighlights['pending_loans'] ?? 0) }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3 mb-3">
        <div class="report-metric-card">
            <div class="report-metric-label">{{ _lang('Portfolio Outstanding') }}</div>
            <div class="report-metric-value">{{ money_format_2($reportHighlights['portfolio_outstanding'] ?? 0) }}</div>
            <div class="report-metric-split">
                <span>{{ _lang('Active Loans') }}: {{ number_format($reportHighlights['active_loans'] ?? 0) }}</span>
                <span>{{ _lang('Disbursed') }}: {{ money_format_2($reportHighlights['active_portfolio_amount'] ?? 0) }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3 mb-3">
        <div class="report-metric-card {{ $overdueCount > 0 ? 'critical' : 'active' }}">
            <div class="report-metric-label">{{ _lang('Overdue Exposure') }}</div>
            <div class="report-metric-value">{{ money_format_2($reportHighlights['overdue_amount'] ?? 0) }}</div>
            <div class="report-metric-split">
                <span>{{ _lang('Overdue Items') }}: {{ number_format($overdueCount) }}</span>
                <span>{{ _lang('Due Today') }}: {{ number_format($dueTodayCount) }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3 mb-3">
        <div class="report-metric-card {{ $netTone }}">
            <div class="report-metric-label">{{ _lang('Net Movement') }}</div>
            <div class="report-metric-value">{{ money_format_2($netMovement) }}</div>
            <div class="report-metric-split">
                <span>{{ _lang('Credits') }}: {{ money_format_2($reportHighlights['monthly_credits'] ?? 0) }}</span>
                <span>{{ _lang('Debits') }}: {{ money_format_2($reportHighlights['monthly_debits'] ?? 0) }}</span>
            </div>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-lg-6 mb-3">
        <div class="card workspace-section-card h-100 mb-0">
            <div class="card-header">{{ _lang('Cash Movement') }}</div>
            <div class="card-body">
                <div class="report-chart-wrap">
                    <canvas id="reportCashMovementChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6 mb-3">
        <div class="card workspace-section-card h-100 mb-0">
            <div class="card-header">{{ _lang('Portfolio Pressure') }}</div>
            <div class="card-body">
                <div class="report-chart-wrap">
                    <canvas id="reportPortfolioPressureChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-lg-4 mb-3">
        <div class="card workspace-section-card h-100 mb-0">
            <div class="card-header">{{ _lang('Members & Loans') }}</div>
            <div class="card-body p-0">
                <table class="table table-sm report-info-table mb-0">
                    <tbody>
                        <tr><th>{{ _lang('Active Members') }}</th><td>{{ number_format($reportHighlights['active_members'] ?? 0) }}</td></tr>
                        <tr><th>{{ _lang('Pending Members') }}</th><td>{{ number_format($reportHighlights['pending_members'] ?? 0) }}</td></tr>
                        <tr><th>{{ _lang('Active Loans') }}</th><td>{{ number_format($reportHighlights['active_loans'] ?? 0) }}</td></tr>
                        <tr><th>{{ _lang('Pending Loans') }}</th><td>{{ number_format($reportHighlights['pending_loans'] ?? 0) }}</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4 mb-3">
        <div class="card workspace-section-card h-100 mb-0">
            <div class="card-header">{{ _lang('Collections') }}</div>
            <div class="card-body p-0">
                <table class="table table-sm report-info-table mb-0">
                    <tbody>
                        <tr><th>{{ _lang('Due Today Count') }}</th><td>{{ number_format($dueTodayCount) }}</td></tr>
                        <tr><th>{{ _lang('Due Today Amount') }}</th><td>{{ money_format_2($reportHighlights['due_today_amount'] ?? 0) }}</td></tr>
                        <tr><th>{{ _lang('Overdue Count') }}</th><td>{{ number_format($overdueCount) }}</td></tr>
                        <tr><th>{{ _lang('Overdue Amount') }}</th><td>{{ money_format_2($reportHighlights['overdue_amount'] ?? 0) }}</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4 mb-3">
        <div class="card workspace-section-card h-100 mb-0">
            <div class="card-header">{{ _lang('Cash & Reconciliation') }}</div>
            <div class="card-body p-0">
                <table class="table table-sm report-info-table mb-0">
                    <tbody>
                        <tr><th>{{ _lang('Completed Txns') }}</th><td>{{ number_format($reportHighlights['completed_transactions_this_month'] ?? 0) }}</td></tr>
                        <tr><th>{{ _lang('Pending Txns') }}</th><td>{{ number_format($reportHighlights['pending_transactions_this_month'] ?? 0) }}</td></tr>
                        <tr><th>{{ _lang('Expenses') }}</th><td>{{ money_format_2($reportHighlights['expenses_this_month_amount'] ?? 0) }}</td></tr>
                        <tr><th>{{ _lang('Pending Bank Items') }}</th><td>{{ number_format($pendingBankItems) }}</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
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
