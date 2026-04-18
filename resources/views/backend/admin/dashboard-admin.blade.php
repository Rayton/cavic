@extends('layouts.app')

@section('content')
@php
	$card_currency = $admin_interest_currency ?? '';
	$followUpStats = $collection_execution_stats ?? [];
	$collectionDateRange = $collection_date_range ?? [];
	$recentResolvedCases = $recent_resolved_cases ?? collect();
@endphp
@include('backend.admin.partials.workspace-styles')
<style>
.admin-dashboard-card .card-body { padding: 1.25rem; }
.admin-dashboard-card .card-title { font-size: 0.875rem; color: #6c757d; margin-bottom: 0.5rem; font-weight: 500; }
.admin-dashboard-card .card-value { font-size: 1.5rem; font-weight: 700; color: #1A8E8F; margin-bottom: 0.25rem; }
.admin-dashboard-card .card-meta { font-size: 0.8125rem; color: #6c757d; }
.admin-dashboard-card a { text-decoration: none; color: inherit; }
.admin-dashboard-card a:hover { color: inherit; }
.admin-dashboard-card .icon-wrap { width: 48px; height: 48px; min-width: 48px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; flex-shrink: 0; }
/* Override global .dashboard-card i (grey circle) so icon is just the symbol on our colored box */
.admin-dashboard-card .icon-wrap i { background: none !important; border-radius: 0; width: auto !important; height: auto !important; padding: 0 !important; font-size: inherit; display: inline-flex; align-items: center; justify-content: center; color: inherit; }
.interest-progress-circle { width: 140px; height: 140px; margin: 0 auto 1rem; position: relative; }
.interest-progress-circle svg { transform: rotate(-90deg); }
.interest-progress-circle .bg { fill: none; stroke: #e9ecef; stroke-width: 10; }
.interest-progress-circle .fill { fill: none; stroke: #fd7e14; stroke-width: 10; stroke-linecap: round; transition: stroke-dashoffset 0.5s; }
.interest-progress-circle .percent-text { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 1.75rem; font-weight: 700; color: #333; }
.interest-progress-circle .percent-label { position: absolute; top: calc(50% + 1.25rem); left: 50%; transform: translateX(-50%); font-size: 0.75rem; color: #6c757d; }
.dashboard-brand-btn, .admin-dashboard-card .btn-primary, .chart-card-compact .btn-primary { background: transparent !important; border: 1px solid #1A8E8F !important; color: #1A8E8F !important; border-radius: 6px; transition: background 0.2s, color 0.2s; }
.dashboard-brand-btn:hover, .admin-dashboard-card .btn-primary:hover, .chart-card-compact .btn-primary:hover { background: #1A8E8F !important; border-color: #1A8E8F !important; color: #fff !important; }
.main-content-inner .btn-outline-primary { background: transparent !important; border: 1px solid #1A8E8F !important; color: #1A8E8F !important; border-radius: 6px; transition: background 0.2s, color 0.2s; }
.main-content-inner .btn-outline-primary:hover { background: #1A8E8F !important; border-color: #1A8E8F !important; color: #fff !important; }
.chart-card-compact .card-header { padding: 0.5rem 0.75rem; font-size: 0.9rem; }
.chart-card-compact .card-body { padding: 0.5rem 0.75rem; }
.chart-card-compact .chart-wrap { height: 200px; position: relative; }
.chart-card-compact canvas { max-height: 200px; }
.dashboard-table-compact { margin-bottom: 0; font-size: 0.8125rem; }
.dashboard-table-compact thead th { background: #f8f9fa; font-weight: 600; color: #343a40; border-color: #dee2e6; padding: 0.4rem 0.5rem; font-size: 0.75rem; }
.dashboard-table-compact tbody td { padding: 0.4rem 0.5rem; vertical-align: middle; border-color: #dee2e6; }
.dashboard-hero-card { border: 1px solid #eef1f5; border-radius: 12px; }
.dashboard-hero-card .card-body { padding: 1.25rem; }
.dashboard-quick-actions .btn { margin-right: .5rem; margin-bottom: .5rem; }
.dashboard-exception-card { border: 1px solid #eef1f5; border-radius: 12px; height: 100%; }
.dashboard-exception-card .label { font-size: .8rem; color: #6c757d; }
.dashboard-exception-card .value { font-size: 1.35rem; font-weight: 700; color: #1A8E8F; }
.dashboard-exception-card .icon { width: 42px; height: 42px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; color: #fff; }
</style>

@include('backend.admin.partials.collection-date-range-filter', ['collectionDateRange' => $collectionDateRange, 'filterId' => 'dashboard-collection-range'])

<div class="row mb-4">
	<div class="col-lg-8 mb-3 mb-lg-0">
		<div class="card dashboard-hero-card h-100 mb-0">
			<div class="card-body d-md-flex align-items-center justify-content-between">
				<div>
					<h4 class="mb-2">{{ _lang('CAVIC Admin Dashboard') }}</h4>
					<p class="text-muted mb-0">{{ _lang('Track approvals, portfolio pressure, finance movement, and branch workload from one operational dashboard.') }}</p>
				</div>
				<div class="dashboard-quick-actions mt-3 mt-md-0 text-md-right">
					<a href="{{ route('action_center.index') }}" class="btn btn-primary btn-sm">{{ _lang('Open Action Center') }}</a>
					<a href="{{ route('loans.workspace') }}" class="btn btn-outline-primary btn-sm">{{ _lang('Loan Workspace') }}</a>
					<a href="{{ route('finance.index') }}" class="btn btn-outline-primary btn-sm">{{ _lang('Finance Workspace') }}</a>
				</div>
			</div>
		</div>
	</div>
	<div class="col-lg-4">
		<div class="card dashboard-hero-card h-100 mb-0">
			<div class="card-body">
				<div class="d-flex justify-content-between align-items-center mb-2">
					<span class="text-muted small">{{ _lang('Today\'s Priorities') }}</span>
					<a href="{{ route('action_center.index') }}" class="small">{{ _lang('View queue') }}</a>
				</div>
				<ul class="mb-0 pl-3 text-muted small">
					<li>{{ _lang('Due today repayments') }}: <strong>{{ $today_due_count ?? 0 }}</strong></li>
					<li>{{ _lang('Due in the next 7 days') }}: <strong>{{ $due_this_week_count ?? 0 }}</strong></li>
					<li>{{ _lang('Overdue repayments') }}: <strong>{{ $overdue_repayments_count ?? 0 }}</strong></li>
					<li>{{ _lang('Ready for disbursement') }}: <strong>{{ $ready_for_disbursement_count ?? 0 }}</strong></li>
					<li>{{ _lang('Broken promises') }}: <strong>{{ $followUpStats['broken_promises'] ?? 0 }}</strong></li>
					<li>{{ _lang('Recovered in range') }}: <strong>{{ $followUpStats['recovered_in_range'] ?? 0 }}</strong></li>
					<li>{{ _lang('Promise kept') }}: <strong>{{ $followUpStats['promise_kept'] ?? 0 }}</strong></li>
					<li>{{ _lang('Analytics range') }}: <strong>{{ $collectionDateRange['label'] ?? _lang('Today') }}</strong></li>
					<li>{{ _lang('Finance exceptions') }}: <strong>{{ $finance_exception_count ?? 0 }}</strong></li>
				</ul>
			</div>
		</div>
	</div>
</div>

<div class="row mb-4">
	@foreach(($exception_cards ?? []) as $card)
	<div class="col-xl-3 col-md-6 mb-3 mb-xl-0">
		<a href="{{ $card['route'] }}">
			<div class="card dashboard-exception-card mb-0">
				<div class="card-body">
					<div class="d-flex align-items-center justify-content-between">
						<div>
							<div class="label">{{ $card['label'] }}</div>
							<div class="value">{{ number_format($card['value']) }}</div>
						</div>
						<div class="icon bg-{{ $card['theme'] }}">
							<i class="{{ $card['icon'] }}"></i>
						</div>
					</div>
				</div>
			</div>
		</a>
	</div>
	@endforeach
</div>

<div class="row mb-4">
	@foreach(($collection_buckets ?? []) as $bucket)
	<div class="col-md-4 mb-3 mb-md-0">
		<div class="card workspace-bucket-card mb-0 h-100">
			<div class="card-body">
				<div class="bucket-label">{{ $bucket['label'] }}</div>
				<div class="bucket-value">{{ number_format($bucket['count']) }}</div>
				<div class="bucket-meta">{{ _lang('Open repayments in this aging bucket') }}</div>
			</div>
		</div>
	</div>
	@endforeach
</div>

<div class="row mb-4">
	<div class="col-md-4 mb-3 mb-md-0">
		<div class="card workspace-bucket-card mb-0 h-100">
			<div class="card-body">
				<div class="bucket-label">{{ _lang('Call Today Queue') }}</div>
				<div class="bucket-value">{{ $collection_queue_counts['call_today'] ?? 0 }}</div>
				<div class="bucket-meta">{{ _lang('Due-today and near-term overdue cases') }}</div>
			</div>
		</div>
	</div>
	<div class="col-md-4 mb-3 mb-md-0">
		<div class="card workspace-bucket-card mb-0 h-100">
			<div class="card-body">
				<div class="bucket-label">{{ _lang('Upcoming Reminders') }}</div>
				<div class="bucket-value">{{ $collection_queue_counts['upcoming_reminders'] ?? 0 }}</div>
				<div class="bucket-meta">{{ _lang('Repayments needing pre-due reminders') }}</div>
			</div>
		</div>
	</div>
	<div class="col-md-4">
		<div class="card workspace-bucket-card mb-0 h-100">
			<div class="card-body">
				<div class="bucket-label">{{ _lang('Critical Collections') }}</div>
				<div class="bucket-value">{{ $collection_queue_counts['critical'] ?? 0 }}</div>
				<div class="bucket-meta">{{ _lang('31+ day delinquency cases') }}</div>
			</div>
		</div>
	</div>
</div>

<div class="row mb-4">
	<div class="col-md-4 col-xl-2 mb-3"><div class="card workspace-bucket-card mb-0 h-100"><div class="card-body"><div class="bucket-label">{{ _lang('Follow-ups Logged') }}</div><div class="bucket-value">{{ $followUpStats['logged_today'] ?? 0 }}</div><div class="bucket-meta">{{ _lang('Collection notes recorded in selected range') }}</div></div></div></div>
	<div class="col-md-4 col-xl-2 mb-3"><div class="card workspace-bucket-card mb-0 h-100"><div class="card-body"><div class="bucket-label">{{ _lang('Promises in Range') }}</div><div class="bucket-value">{{ $followUpStats['promise_due_today'] ?? 0 }}</div><div class="bucket-meta">{{ _lang('Promised-to-pay cases dated inside selected range') }}</div></div></div></div>
	<div class="col-md-4 col-xl-2 mb-3"><div class="card workspace-bucket-card mb-0 h-100"><div class="card-body"><div class="bucket-label">{{ _lang('Broken Promises') }}</div><div class="bucket-value">{{ $followUpStats['broken_promises'] ?? 0 }}</div><div class="bucket-meta">{{ _lang('Outstanding promised-to-pay cases already past due as of range end') }}</div></div></div></div>
	<div class="col-md-4 col-xl-2 mb-3"><div class="card workspace-bucket-card mb-0 h-100"><div class="card-body"><div class="bucket-label">{{ _lang('Recovered in Range') }}</div><div class="bucket-value">{{ $followUpStats['recovered_in_range'] ?? 0 }}</div><div class="bucket-meta">{{ _lang('Paid repayments that had follow-up activity in selected range') }}</div></div></div></div>
	<div class="col-md-4 col-xl-2 mb-3"><div class="card workspace-bucket-card mb-0 h-100"><div class="card-body"><div class="bucket-label">{{ _lang('Promise Kept') }}</div><div class="bucket-value">{{ $followUpStats['promise_kept'] ?? 0 }}</div><div class="bucket-meta">{{ _lang('Promised-to-pay cases settled on or before committed date') }}</div></div></div></div>
	<div class="col-md-4 col-xl-2 mb-3"><div class="card workspace-bucket-card mb-0 h-100"><div class="card-body"><div class="bucket-label">{{ _lang('Completion Rate') }}</div><div class="bucket-value">{{ $followUpStats['completion_rate'] ?? 0 }}%</div><div class="bucket-meta">{{ _lang('Open due and overdue cases touched in selected range') }}</div></div></div></div>
</div>

{{-- Compact stat cards (existing links preserved) --}}
<div class="row mb-4">
	<div class="col-xl-3 col-md-6">
		<a href="{{ route('members.index') }}">
			<div class="card mb-3 mb-xl-0 dashboard-card admin-dashboard-card h-100">
				<div class="card-body">
					<div class="d-flex align-items-center justify-content-between">
						<div class="flex-grow-1">
							<h5 class="card-title mb-1">{{ _lang('Total Members') }}</h5>
							<div class="card-value">{{ number_format($total_customer ?? 0) }}</div>
						</div>
						<div class="icon-wrap bg-success text-white ml-2">
							<i class="fas fa-users"></i>
						</div>
					</div>
				</div>
			</div>
		</a>
	</div>
	<div class="col-xl-3 col-md-6">
		<a href="{{ route('deposit_requests.index') }}">
			<div class="card mb-3 mb-xl-0 dashboard-card admin-dashboard-card h-100">
				<div class="card-body">
					<div class="d-flex align-items-center justify-content-between">
						<div class="flex-grow-1">
							<h5 class="card-title mb-1">{{ _lang('Deposit Requests') }}</h5>
							<div class="card-value">{{ request_count('deposit_requests') }}</div>
						</div>
						<div class="icon-wrap bg-info text-white ml-2">
							<i class="fas fa-calendar-alt"></i>
						</div>
					</div>
				</div>
			</div>
		</a>
	</div>
	<div class="col-xl-3 col-md-6">
		<a href="{{ route('withdraw_requests.index') }}">
			<div class="card mb-3 mb-xl-0 dashboard-card admin-dashboard-card h-100">
				<div class="card-body">
					<div class="d-flex align-items-center justify-content-between">
						<div class="flex-grow-1">
							<h5 class="card-title mb-1">{{ _lang('Withdraw Requests') }}</h5>
							<div class="card-value">{{ request_count('withdraw_requests') }}</div>
						</div>
						<div class="icon-wrap bg-primary text-white ml-2">
							<i class="fas fa-coins"></i>
						</div>
					</div>
				</div>
			</div>
		</a>
	</div>
	<div class="col-xl-3 col-md-6">
		<a href="{{ route('loans.filter', 'pending') }}">
			<div class="card mb-3 mb-xl-0 dashboard-card admin-dashboard-card h-100">
				<div class="card-body">
					<div class="d-flex align-items-center justify-content-between">
						<div class="flex-grow-1">
							<h5 class="card-title mb-1">{{ _lang('Pending Loans') }}</h5>
							<div class="card-value">{{ request_count('pending_loans') }}</div>
						</div>
						<div class="icon-wrap bg-danger text-white ml-2">
							<i class="fas fa-dollar-sign"></i>
						</div>
					</div>
				</div>
			</div>
		</a>
	</div>
</div>

{{-- Interest analysis row (inspired by members dashboard) --}}
<div class="row mb-4">
	<div class="col-xl-4 col-md-6">
		<div class="card mb-3 mb-xl-0 dashboard-card admin-dashboard-card h-100">
			<div class="card-body text-center">
				<h5 class="card-title">{{ _lang('Interest Paid Progress') }}</h5>
				<div class="interest-progress-circle">
					@php
						$pct = min(100, (float)($interest_paid_pct ?? 0));
						$r = 58;
						$c = 2 * 3.14159 * $r;
						$offset = $c - ($pct / 100) * $c;
					@endphp
					<svg width="140" height="140" viewBox="0 0 140 140">
						<circle class="bg" cx="70" cy="70" r="{{ $r }}" />
						<circle class="fill" cx="70" cy="70" r="{{ $r }}" stroke-dasharray="{{ $c }}" stroke-dashoffset="{{ $offset }}" />
					</svg>
					<span class="percent-text">{{ number_format($pct, 0) }}%</span>
					<span class="percent-label">{{ _lang('Interest Paid') }}</span>
				</div>
				<p class="small text-muted mb-0">
					{{ _lang('Total interest') }}: {{ decimalPlace($total_interest_paid ?? 0, currency($card_currency), 0) }} {{ _lang('of') }} {{ decimalPlace($total_interest_payable ?? 0, currency($card_currency), 0) }}
				</p>
			</div>
		</div>
	</div>
	<div class="col-xl-8 col-md-6">
		<div class="card mb-3 mb-xl-0 dashboard-card admin-dashboard-card h-100">
			<div class="card-body">
				<h5 class="card-title">{{ _lang('Interest Analysis') }}</h5>
				<div class="row">
					<div class="col-sm-6">
						<p class="mb-1"><strong>{{ _lang('Total interest payable') }}</strong></p>
						<p class="card-value mb-2">{{ decimalPlace($total_interest_payable ?? 0, currency($card_currency), 0) }}</p>
						<p class="small text-muted">{{ _lang('Total interest on all active loans (principal + interest - principal)') }}</p>
					</div>
					<div class="col-sm-6">
						<p class="mb-1"><strong>{{ _lang('Total interest paid') }}</strong></p>
						<p class="card-value mb-2">{{ decimalPlace($total_interest_paid ?? 0, currency($card_currency), 0) }}</p>
						<p class="small text-muted">{{ _lang('Interest portion already paid in repayments') }}</p>
					</div>
				</div>
				<div class="mt-2">
					<div class="progress" style="height: 1.5rem;">
						<div class="progress-bar bg-warning" role="progressbar" style="width: {{ min(100, $interest_paid_pct ?? 0) }}%" aria-valuenow="{{ $interest_paid_pct ?? 0 }}" aria-valuemin="0" aria-valuemax="100">{{ number_format($interest_paid_pct ?? 0, 1) }}%</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="row mb-4">
	<div class="col-lg-7 mb-3 mb-lg-0">
		<div class="card h-100 mb-0">
			<div class="card-header py-2 d-flex align-items-center justify-content-between">
				<span>{{ _lang('Branch Performance Snapshot') }}</span>
				<a href="{{ route('members.workspace') }}" class="small">{{ _lang('Open member workspace') }}</a>
			</div>
			<div class="card-body px-0 pt-0">
				<div class="table-responsive">
					<table class="table table-bordered dashboard-table-compact mb-0">
						<thead>
							<tr>
								<th class="pl-3">{{ _lang('Branch') }}</th>
								<th>{{ _lang('Active Members') }}</th>
								<th>{{ _lang('Pending Onboarding') }}</th>
								<th>{{ _lang('Active Loans') }}</th>
								<th class="pr-3">{{ _lang('Overdue Repayments') }}</th>
							</tr>
						</thead>
						<tbody>
							@forelse(($branch_performance ?? collect()) as $branch)
							<tr>
								<td class="pl-3">{{ $branch->name }}</td>
								<td>{{ $branch->active_members }}</td>
								<td>{{ $branch->pending_members }}</td>
								<td>{{ $branch->active_loans }}</td>
								<td class="pr-3">{{ $branch->overdue_repayments }}</td>
							</tr>
							@empty
							<tr><td colspan="5" class="text-center text-muted py-2">{{ _lang('No branch data available') }}</td></tr>
							@endforelse
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
	<div class="col-lg-5">
		<div class="card h-100 mb-0">
			<div class="card-header py-2 d-flex align-items-center justify-content-between">
				<span>{{ _lang('Operational Exceptions') }}</span>
				<a href="{{ route('action_center.index') }}" class="small">{{ _lang('Open action center') }}</a>
			</div>
			<div class="card-body">
				<div class="alert alert-warning small mb-3">
					<strong>{{ _lang('Immediate focus') }}:</strong> {{ _lang('Overdue repayments, due-today schedules, and finance exceptions require active monitoring.') }}
				</div>
				<ul class="list-unstyled small mb-3">
					<li class="mb-2 d-flex justify-content-between"><span>{{ _lang('Overdue repayments') }}</span><strong>{{ $overdue_repayments_count ?? 0 }}</strong></li>
					<li class="mb-2 d-flex justify-content-between"><span>{{ _lang('Due today') }}</span><strong>{{ $today_due_count ?? 0 }}</strong></li>
					<li class="mb-2 d-flex justify-content-between"><span>{{ _lang('Ready for disbursement') }}</span><strong>{{ $ready_for_disbursement_count ?? 0 }}</strong></li>
					<li class="mb-2 d-flex justify-content-between"><span>{{ _lang('Pending finance requests') }}</span><strong>{{ $pending_finance_requests_count ?? 0 }}</strong></li>
					<li class="mb-2 d-flex justify-content-between"><span>{{ _lang('Pending bank transactions') }}</span><strong>{{ $pending_bank_transactions_count ?? 0 }}</strong></li>
					<li class="mb-2 d-flex justify-content-between"><span>{{ _lang('Pending cash transactions') }}</span><strong>{{ $pending_cash_transactions_count ?? 0 }}</strong></li>
					<li class="mb-2 d-flex justify-content-between"><span>{{ _lang('Broken promises') }}</span><strong>{{ $followUpStats['broken_promises'] ?? 0 }}</strong></li>
					<li class="mb-2 d-flex justify-content-between"><span>{{ _lang('Recovered in range') }}</span><strong>{{ $followUpStats['recovered_in_range'] ?? 0 }}</strong></li>
					<li class="mb-2 d-flex justify-content-between"><span>{{ _lang('Promise kept') }}</span><strong>{{ $followUpStats['promise_kept'] ?? 0 }}</strong></li>
					<li class="d-flex justify-content-between"><span>{{ _lang('Today\'s expenses posted') }}</span><strong>{{ $today_expenses_count ?? 0 }}</strong></li>
				</ul>
				<div class="border-top pt-3">
					<div class="text-muted small mb-2">{{ _lang('Collections aging') }}</div>
					@foreach(($collection_buckets ?? []) as $bucket)
						<div class="d-flex justify-content-between small mb-1"><span>{{ $bucket['label'] }}</span><strong>{{ $bucket['count'] }}</strong></div>
					@endforeach
				</div>
			</div>
		</div>
	</div>
</div>

<div class="row mb-4">
	<div class="col-lg-7 mb-3 mb-lg-0">
		<div class="card h-100 mb-0">
			<div class="card-header py-2 d-flex align-items-center justify-content-between">
				<span>{{ _lang('Collector-ready Call List') }}</span>
				<a href="{{ route('loans.workspace') }}" class="small">{{ _lang('Open collections workspace') }}</a>
			</div>
			<div class="card-body px-0 pt-0">
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
	</div>
	<div class="col-lg-5">
		<div class="card mb-3">
			<div class="card-header py-2 d-flex align-items-center justify-content-between">
				<span>{{ _lang('Branch Collections Pressure') }}</span>
				<a href="{{ route('loans.workspace') }}" class="small">{{ _lang('Open loans workspace') }}</a>
			</div>
			<div class="card-body px-0 pt-0">
				<div class="table-responsive">
					<table class="table table-bordered dashboard-table-compact mb-0">
						<thead>
							<tr>
								<th class="pl-3">{{ _lang('Branch') }}</th>
								<th>{{ _lang('Due Today') }}</th>
								<th>{{ _lang('Overdue') }}</th>
								<th class="pr-3">{{ _lang('Critical') }}</th>
							</tr>
						</thead>
						<tbody>
							@forelse(($branch_collections_pressure ?? collect()) as $branch)
							<tr>
								<td class="pl-3">{{ $branch->name }}</td>
								<td>{{ $branch->due_today }}</td>
								<td>{{ $branch->overdue }}</td>
								<td class="pr-3"><span class="workspace-status-chip {{ $branch->critical > 0 ? 'critical' : 'active' }}">{{ $branch->critical }}</span></td>
							</tr>
							@empty
							<tr><td colspan="4" class="text-center text-muted py-2">{{ _lang('No branch collections data available') }}</td></tr>
							@endforelse
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<div class="card h-100 mb-0">
			<div class="card-header py-2 d-flex align-items-center justify-content-between">
				<span>{{ _lang('Upcoming Reminder Queue') }}</span>
				<a href="{{ route('loans.workspace') }}" class="small">{{ _lang('Open due schedule') }}</a>
			</div>
			<div class="card-body px-0 pt-0">
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
		</div>
	</div>
</div>

<div class="row mb-4">
	<div class="col-lg-4 mb-3 mb-lg-0">
		<div class="card h-100 mb-0">
			<div class="card-header py-2 d-flex align-items-center justify-content-between">
				<span>{{ _lang('Promise Follow-up Queue') }}</span>
				<a href="{{ route('loans.workspace') }}" class="small">{{ _lang('Open loans workspace') }}</a>
			</div>
			<div class="card-body px-0 pt-0">
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
								<td class="pr-3"><a class="btn btn-light btn-xs ajax-modal" data-title="{{ _lang('Log Collection Follow-up') }}" href="{{ route('loan_collection_follow_ups.create', $item->repayment_id) }}">{{ _lang('Log') }}</a></td>
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
	<div class="col-lg-4 mb-3 mb-lg-0">
		<div class="card h-100 mb-0">
			<div class="card-header py-2 d-flex align-items-center justify-content-between">
				<span>{{ _lang('Recent Resolutions') }}</span>
				<a href="{{ route('loans.workspace') }}" class="small">{{ _lang('Open loans workspace') }}</a>
			</div>
			<div class="card-body px-0 pt-0">
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
	<div class="col-lg-4">
		<div class="card h-100 mb-0">
			<div class="card-header py-2 d-flex align-items-center justify-content-between">
				<span>{{ _lang('Branch Follow-up Performance') }}</span>
				<a href="{{ route('action_center.index') }}" class="small">{{ _lang('Open action center') }}</a>
			</div>
			<div class="card-body px-0 pt-0">
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
		</div>
	</div>
</div>

<div class="row mb-4">
	<div class="col-lg-12">
		<div class="card h-100 mb-0">
			<div class="card-header py-2 d-flex align-items-center justify-content-between">
				<span>{{ _lang('Collector Follow-up Performance') }}</span>
				<a href="{{ route('action_center.index') }}" class="small">{{ _lang('Open action center') }}</a>
			</div>
			<div class="card-body px-0 pt-0">
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
		</div>
	</div>
</div>

{{-- Compact charts row --}}
<div class="row mb-4 align-items-stretch">
	<div class="col-lg-4 col-md-5 mb-3 mb-lg-0">
		<div class="card h-100 chart-card-compact">
			<div class="card-header d-flex align-items-center">
				<span>{{ _lang('Expense Overview').' - '.date('M Y') }}</span>
			</div>
			<div class="card-body">
				<div class="chart-wrap">
					<canvas id="expenseOverview"></canvas>
				</div>
			</div>
		</div>
	</div>
	<div class="col-lg-8 col-md-7">
		<div class="card h-100 chart-card-compact">
			<div class="card-header d-flex align-items-center flex-wrap">
				<span>{{ _lang('Deposit & Withdraw Analytics').' - '.date('Y') }}</span>
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
</div>

{{-- Active Loan Balances & Due Loan Payments side by side --}}
<div class="row mb-4">
	<div class="col-md-6 mb-3 mb-md-0">
		<div class="card h-100 mb-0">
			<div class="card-header py-2">
				{{ _lang('Active Loan Balances') }}
			</div>
			<div class="card-body px-0 pt-0">
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
								<tr>
									<td colspan="4"><p class="text-center text-muted mb-0 py-2">{{ _lang('No Data Available') }}</p></td>
								</tr>
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
		</div>
	</div>
	<div class="col-md-6">
		<div class="card h-100 mb-0">
			<div class="card-header py-2">
				{{ _lang('Due Loan Payments') }}
			</div>
			<div class="card-body px-0 pt-0">
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
								<tr>
									<td colspan="6"><p class="text-center text-muted mb-0 py-2">{{ _lang('No Data Available') }}</p></td>
								</tr>
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
	</div>
</div>

{{-- Recent Transactions (compact table) --}}
<div class="row">
	<div class="col-lg-12">
		<div class="card mb-3">
			<div class="card-header py-2">
				{{ _lang('Recent Transactions') }}
			</div>
			<div class="card-body px-0 pt-0">
				<div class="table-responsive">
					<table class="table table-bordered table-export dashboard-table-compact">
						<thead>
							<tr>
								<th data-total-label="{{ _lang('Total') }}" class="pl-3">{{ _lang('Date') }}</th>
								<th>{{ _lang('Member') }}</th>
								<th class="text-nowrap">{{ _lang('Account Number') }}</th>
								<th data-sum="1">{{ _lang('Amount') }}</th>
								<th class="text-nowrap">{{ _lang('Debit/Credit') }}</th>
								<th>{{ _lang('Type') }}</th>
								<th>{{ _lang('Status') }}</th>
								<th class="text-center">{{ _lang('Action') }}</th>
							</tr>
						</thead>
						<tbody>
							@if(count($recent_transactions) == 0)
								<tr>
									<td colspan="8"><p class="text-center text-muted mb-0 py-2">{{ _lang('No Data Available') }}</p></td>
								</tr>
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
								<td class="text-center"><a href="{{ route('transactions.show', $transaction->id) }}" target="_blank" class="btn btn-outline-primary btn-xs"><i class="ti-arrow-right"></i>&nbsp;{{ _lang('View') }}</a></td>
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
@endsection
