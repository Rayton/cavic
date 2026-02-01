@extends('layouts.app')

@section('content')
@php $card_currency = optional(optional($loans->first())->currency)->name ?? ''; @endphp
<style>
.customer-dashboard-card .card-body { padding: 1.25rem; }
.customer-dashboard-card .card-title { font-size: 0.875rem; color: #6c757d; margin-bottom: 0.5rem; font-weight: 500; }
.customer-dashboard-card .card-value { font-size: 1.5rem; font-weight: 700; color: #007bff; margin-bottom: 0.25rem; }
.customer-dashboard-card .card-meta { font-size: 0.8125rem; color: #6c757d; margin-bottom: 0.5rem; }
.customer-dashboard-card .btn-pay-now { margin-top: 0.5rem; }
.customer-dashboard-card .deduction-by-loan { font-size: 0.8125rem; margin-top: 0.75rem; padding-top: 0.75rem; border-top: 1px solid #eee; }
.customer-dashboard-card .deduction-by-loan .item { display: flex; justify-content: space-between; margin-bottom: 0.25rem; }
.interest-progress-circle { width: 140px; height: 140px; margin: 0 auto 1rem; position: relative; }
.interest-progress-circle svg { transform: rotate(-90deg); }
.interest-progress-circle .bg { fill: none; stroke: #e9ecef; stroke-width: 10; }
.interest-progress-circle .fill { fill: none; stroke: #fd7e14; stroke-width: 10; stroke-linecap: round; transition: stroke-dashoffset 0.5s; }
.interest-progress-circle .percent-text { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 1.75rem; font-weight: 700; color: #333; }
.interest-progress-circle .percent-label { position: absolute; top: calc(50% + 1.25rem); left: 50%; transform: translateX(-50%); font-size: 0.75rem; color: #6c757d; }
.upcoming-payment-table { margin-bottom: 0; font-size: 0.8125rem; }
.upcoming-payment-table thead th { background: #f8f9fa; font-weight: 600; color: #343a40; border-color: #dee2e6; padding: 0.4rem 0.5rem; font-size: 0.75rem; }
.upcoming-payment-table tbody td { padding: 0.4rem 0.5rem; vertical-align: middle; border-color: #dee2e6; }
.upcoming-payment-table .badge-upcoming { background: #28a745; color: #fff; padding: 0.2em 0.5em; border-radius: 50px; font-weight: 500; font-size: 0.7rem; }
.upcoming-payment-table .badge-due { background: #dc3545; color: #fff; padding: 0.2em 0.5em; border-radius: 50px; font-weight: 500; font-size: 0.7rem; }
/* Dashboard buttons: outline by default, fill on hover (brand #1A8E8F) */
.upcoming-payment-table .btn-pay-now { background: transparent !important; border: 1px solid #1A8E8F; color: #1A8E8F; padding: 0.25rem 0.5rem; font-size: 0.75rem; border-radius: 6px; transition: background 0.2s, color 0.2s; }
.upcoming-payment-table .btn-pay-now:hover { background: #1A8E8F !important; border-color: #1A8E8F; color: #fff !important; }
.dashboard-brand-btn, .customer-dashboard-card .btn-primary, .chart-card-compact .btn-primary { background: transparent !important; border: 1px solid #1A8E8F !important; color: #1A8E8F !important; border-radius: 6px; transition: background 0.2s, color 0.2s; }
.dashboard-brand-btn:hover, .customer-dashboard-card .btn-primary:hover, .chart-card-compact .btn-primary:hover { background: #1A8E8F !important; border-color: #1A8E8F !important; color: #fff !important; }
/* Recent transactions View button: outline, fill on hover */
.customer-dashboard-card + .row .btn-outline-primary, .main-content-inner .btn-outline-primary { background: transparent !important; border: 1px solid #1A8E8F !important; color: #1A8E8F !important; border-radius: 6px; transition: background 0.2s, color 0.2s; }
.customer-dashboard-card + .row .btn-outline-primary:hover, .main-content-inner .btn-outline-primary:hover { background: #1A8E8F !important; border-color: #1A8E8F !important; color: #fff !important; }
.last-contrib-table { margin-bottom: 0; font-size: 0.8125rem; }
.last-contrib-table thead th { padding: 0.4rem 0.5rem; font-size: 0.75rem; background: #f8f9fa; font-weight: 600; border-color: #dee2e6; }
.last-contrib-table tbody td { padding: 0.35rem 0.5rem; font-size: 0.75rem; border-color: #dee2e6; }
</style>
{{-- Summary cards on top (match shared cards style) --}}
<div class="row mb-4">
	<div class="col-xl-4 col-md-6">
		<div class="card mb-4 dashboard-card customer-dashboard-card h-100">
			<div class="card-body">
				<h5 class="card-title">{{ _lang('Your next deduction') }}</h5>
				<div class="card-value">{{ decimalPlace($next_deduction_total ?? 0, currency($card_currency), 0) }}</div>
				@if($next_deduction_date ?? null)
					<p class="card-meta mb-1">{{ _lang('Scheduled for') }} {{ \Carbon\Carbon::parse($next_deduction_date)->format(get_date_format()) }}</p>
				@endif
				<span class="badge badge-warning mb-2">{{ _lang('Status') }}: {{ _lang('pending withdrawal') }}</span>
				<div class="btn-pay-now">
					<a href="#upcoming-loan-payment" class="btn btn-primary btn-sm">{{ _lang('Pay Now') }}</a>
				</div>
				@if(!empty($next_deduction_by_loan ?? []))
					<div class="deduction-by-loan">
						<strong>{{ _lang('Deduction by loan') }}:</strong>
						@foreach($next_deduction_by_loan ?? [] as $loanName => $amt)
							<div class="item"><span>{{ $loanName }}</span> <span>{{ decimalPlace($amt, currency($card_currency), 0) }}</span></div>
						@endforeach
					</div>
				@endif
			</div>
		</div>
	</div>
	<div class="col-xl-4 col-md-6">
		<div class="card mb-4 dashboard-card customer-dashboard-card h-100">
			<div class="card-body">
				<div class="row">
					<div class="col-6">
						<h5 class="card-title">{{ _lang('Your last deduction') }}</h5>
						<div class="card-value">{{ decimalPlace($last_deduction_total ?? 0, currency($card_currency), 0) }}</div>
						@if($last_deduction_date ?? null)
							<p class="card-meta mb-0">{{ _lang('Posted and applied on') }} {{ \Carbon\Carbon::parse($last_deduction_date)->format(get_date_format()) }}</p>
						@else
							<p class="card-meta mb-0">-</p>
						@endif
					</div>
					<div class="col-6">
						<h5 class="card-title">{{ _lang('Your last Contributions') }}</h5>
						<div class="card-value">{{ decimalPlace($last_contributions_total ?? 0, currency($card_currency), 0) }}</div>
						<p class="card-meta mb-0">{{ _lang('Type') }}: {{ _lang('Deposit') }}</p>
						@if($last_contribution_date ?? null)
							<p class="card-meta mb-0">{{ _lang('Latest') }}: {{ \Carbon\Carbon::parse($last_contribution_date)->format(get_date_format()) }}</p>
						@endif
					</div>
				</div>

				<hr class="my-3 border-secondary">

				<h6 class="mb-1 font-weight-bold" style="font-size: 0.875rem; color: #343a40;">{{ _lang('Latest account types') }}</h6>
				<div class="table-responsive">
					<table class="table table-bordered last-contrib-table">
						<thead>
							<tr>
								<th>{{ _lang('Account Type') }}</th>
								<th class="text-right">{{ _lang('Balance') }}</th>
								<th class="text-nowrap">{{ _lang('Last Contribution') }}</th>
							</tr>
						</thead>
						<tbody>
							@forelse($account_types_latest ?? [] as $row)
								<tr>
									<td>{{ $row['name'] }}</td>
									<td class="text-right">{{ decimalPlace($row['balance'], currency($row['currency']), 0) }}</td>
									<td class="text-nowrap">{{ $row['last_contribution_date'] ? \Carbon\Carbon::parse($row['last_contribution_date'])->format(get_date_format()) : '-' }}</td>
								</tr>
							@empty
								<tr>
									<td colspan="3" class="text-center text-muted py-2" style="font-size: 0.75rem;">{{ _lang('No Data Available') }}</td>
								</tr>
							@endforelse
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
	<div class="col-xl-4 col-md-6" id="upcoming-loan-payment">
		<div class="card mb-4 dashboard-card customer-dashboard-card h-100">
			<div class="card-body">
				<h5 class="card-title">{{ _lang('Total Loan Balance') }}</h5>
				@if(!empty($total_loan_balance_by_currency))
					@foreach($total_loan_balance_by_currency as $currencyName => $balance)
						<div class="card-value">{{ decimalPlace($balance, currency($currencyName), 0) }}</div>
					@endforeach
				@else
					<div class="card-value">{{ decimalPlace(0, currency(''), 0) }}</div>
				@endif
				<h6 class="mt-3 mb-1 font-weight-bold" style="color: #343a40; font-size: 0.875rem;">{{ _lang('Upcoming Loan Payment') }}</h6>
				<div class="table-responsive">
					<table class="table table-bordered upcoming-payment-table">
						<thead>
							<tr>
								<th class="text-nowrap">{{ _lang('Loan ID') }}</th>
								<th class="text-nowrap">{{ _lang('Next Payment Date') }}</th>
								<th>{{ _lang('Status') }}</th>
								<th class="text-nowrap text-right">{{ _lang('Amount to Pay') }}</th>
								<th class="text-center">{{ _lang('Action') }}</th>
							</tr>
						</thead>
						<tbody>
							@if(count($upcoming_loans ?? []) == 0)
								<tr>
									<td colspan="5" class="text-center text-muted py-2" style="font-size: 0.8125rem;">{{ _lang('No Data Available') }}</td>
								</tr>
							@else
								@foreach($upcoming_loans as $loan)
									<tr>
										<td>{{ $loan->loan_id }}</td>
										<td class="text-nowrap">{{ $loan->next_payment->repayment_date }}</td>
										<td>
											@if($loan->next_payment->getRawOriginal('repayment_date') >= date('Y-m-d'))
												<span class="badge-upcoming">{{ _lang('Upcoming') }}</span>
											@else
												<span class="badge-due">{{ _lang('Due') }}</span>
											@endif
										</td>
										<td class="text-nowrap text-right">{{ decimalPlace($loan->next_payment->amount_to_pay, currency($loan->currency->name), 0) }}</td>
										<td class="text-center"><a href="{{ route('loans.loan_payment',$loan->id) }}" class="btn btn-pay-now btn-sm text-nowrap">{{ _lang('Pay Now') }}</a></td>
									</tr>
								@endforeach
							@endif
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>
{{-- Interest analysis row --}}
<div class="row mb-4">
	<div class="col-xl-4 col-md-6">
		<div class="card mb-4 dashboard-card customer-dashboard-card h-100">
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
		<div class="card mb-4 dashboard-card customer-dashboard-card h-100">
			<div class="card-body">
				<h5 class="card-title">{{ _lang('Interest Analysis') }}</h5>
				<div class="row">
					<div class="col-sm-6">
						<p class="mb-1"><strong>{{ _lang('Total interest payable') }}</strong></p>
						<p class="card-value mb-2">{{ decimalPlace($total_interest_payable ?? 0, currency($card_currency), 0) }}</p>
						<p class="small text-muted">{{ _lang('Total interest on all your loans (principal + interest - principal)') }}</p>
					</div>
					<div class="col-sm-6">
						<p class="mb-1"><strong>{{ _lang('Total interest paid') }}</strong></p>
						<p class="card-value mb-2">{{ decimalPlace($total_interest_paid ?? 0, currency($card_currency), 0) }}</p>
						<p class="small text-muted">{{ _lang('Interest portion already paid in your repayments') }}</p>
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

{{-- Compact charts: Loan Repayment Trend + Account Type Contributions (one card per type) --}}
<style>
.chart-card-compact .card-header { padding: 0.5rem 0.75rem; font-size: 0.9rem; }
.chart-card-compact .card-body { padding: 0.5rem 0.75rem; }
.chart-card-compact .chart-wrap { height: 200px; position: relative; }
.chart-card-compact canvas { max-height: 200px; }
</style>
<div class="row mb-3 align-items-stretch">
	<div class="col-lg-6 mb-3 mb-lg-0">
		<div class="card h-100 chart-card-compact">
			<div class="card-header d-flex justify-content-between align-items-center flex-wrap py-2">
				<span>{{ _lang('Loan Repayment Trend') }}</span>
				<div class="d-flex align-items-center flex-wrap gap-1 mt-1 mt-md-0">
					<input type="date" id="chart-loan-from" class="form-control form-control-sm" style="max-width: 120px;">
					<span class="text-muted">–</span>
					<input type="date" id="chart-loan-to" class="form-control form-control-sm" style="max-width: 120px;">
					<button type="button" id="chart-loan-apply" class="btn btn-primary btn-sm dashboard-brand-btn">{{ _lang('Apply') }}</button>
				</div>
			</div>
			<div class="card-body py-2">
				<div class="chart-wrap">
					<canvas id="chartLoanRepayment"></canvas>
				</div>
			</div>
		</div>
	</div>
	<div class="col-lg-6">
		<div class="card h-100 chart-card-compact">
			<div class="card-header d-flex justify-content-between align-items-center flex-wrap py-2">
				<span>{{ _lang('Account Type Contributions') }}</span>
				<div class="d-flex align-items-center flex-wrap gap-1 mt-1 mt-md-0">
					<input type="date" id="chart-contrib-from" class="form-control form-control-sm" style="max-width: 120px;">
					<span class="text-muted">–</span>
					<input type="date" id="chart-contrib-to" class="form-control form-control-sm" style="max-width: 120px;">
					<button type="button" id="chart-contrib-apply" class="btn btn-primary btn-sm dashboard-brand-btn">{{ _lang('Apply') }}</button>
				</div>
			</div>
			<div class="card-body py-2">
				<div class="row g-2" id="account-type-cards">
					{{-- One compact card per type (Hisa, Jamii, ...) rendered by JS --}}
				</div>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-xl-12">
		<div class="card mb-4">
			<div class="card-header">
				<div>{{ _lang('Accounts Overview') }}</div>
			</div>
			<div class="card-body px-0 pt-0">
				<div class="table-responsive">
					<table class="table table-bordered">
						<thead>
							<tr>
								<th class="text-nowrap pl-4">{{ _lang('Account Number') }}</th>
								<th class="text-nowrap">{{ _lang('Account Type') }}</th>
								<th>{{ _lang('Currency') }}</th>
								<th class="text-right">{{ _lang('Balance') }}</th>
								<th class="text-nowrap text-right">{{ _lang('Loan Guarantee') }}</th>
								<th class="text-nowrap text-right pr-4">{{ _lang('Current Balance') }}</th>
							</tr>
						</thead>
						<tbody>
							@foreach(get_account_details(auth()->user()->member->id) as $account)
							<tr>
								<td class="pl-4">{{ $account->account_number }}</td>
								<td class="text-nowrap">{{ $account->savings_type->name }}</td>
								<td>{{ $account->savings_type->currency->name }}</td>
								<td class="text-nowrap text-right">{{ decimalPlace($account->balance, currency($account->savings_type->currency->name), 0) }}</td>
								<td class="text-nowrap text-right">{{ decimalPlace($account->blocked_amount, currency($account->savings_type->currency->name), 0) }}</td>
								<td class="text-nowrap text-right pr-4">{{ decimalPlace($account->balance - $account->blocked_amount, currency($account->savings_type->currency->name), 0) }}</td>
							</tr>
							@endforeach
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-xl-12">
		<div class="card mb-4">
			<div class="card-header">
				{{ _lang('Recent Transactions') }}
			</div>
			<div class="card-body px-0 pt-0">
				<div class="table-responsive">
					<table class="table table-bordered">
						<thead>
							<tr>
								<th class="pl-4">{{ _lang('Date') }}</th>
								<th>{{ _lang('AC Number') }}</th>
								<th class="text-right">{{ _lang('Amount') }}</th>
								<th>{{ _lang('Type') }}</th>
								<th>{{ _lang('Status') }}</th>
								<th class="text-center">{{ _lang('Details') }}</th>
							</tr>
						</thead>
						<tbody>
							@if(count($recent_transactions) == 0)
								<tr>
									<td colspan="7"><p class="text-center">{{ _lang('No Data Available') }}</p></td>
								</tr>
							@endif
							@foreach($recent_transactions as $transaction)
							@php
							$symbol = $transaction->dr_cr == 'dr' ? '-' : '+';
							$class  = $transaction->dr_cr == 'dr' ? 'text-danger' : 'text-success';
							@endphp
							<tr>
								<td class="pl-4">{{ $transaction->trans_date }}</td>
								<td>{{ $transaction->account->account_number }} - {{ $transaction->account->savings_type->name }} ({{ $transaction->account->savings_type->currency->name }})</td>
								<td class="text-right"><span class="{{ $class }}">{{ $symbol.' '.decimalPlace($transaction->amount, currency($transaction->account->savings_type->currency->name), 0) }}</span></td>
								<td>{{ ucwords(str_replace('_',' ',$transaction->type)) }}</td>
								<td>{!! xss_clean(transaction_status($transaction->status)) !!}</td>
								<td class="text-center"><a href="{{ route('trasnactions.details', $transaction->id) }}" target="_blank" class="btn btn-outline-primary btn-xs">{{ _lang('View') }}</a></td>
							</tr>
							@endforeach
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection

@section('js-script')
<script src="{{ asset('public/backend/plugins/chartJs/chart.min.js') }}"></script>
<script>
(function() {
	var chartDataUrl = "{{ route('dashboard.chart_data') }}";
	var defaultTo = new Date();
	var defaultFrom = new Date(defaultTo.getFullYear(), defaultTo.getMonth() - 11, 1);
	var chartLoan = null;
	var chartContribInstances = [];

	var compactOptions = {
		responsive: true,
		maintainAspectRatio: true,
		layout: { padding: { top: 4, bottom: 4, left: 8, right: 8 } },
		plugins: {
			legend: { display: true, position: 'top', labels: { boxWidth: 12, font: { size: 11 } } }
		},
		scales: {
			y: { beginAtZero: true, ticks: { font: { size: 10 }, maxTicksLimit: 6 } },
			x: { ticks: { font: { size: 10 }, maxRotation: 45 } }
		}
	};

	function formatDate(d) {
		return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
	}

	function setDefaultDates() {
		document.getElementById('chart-loan-from').value = formatDate(defaultFrom);
		document.getElementById('chart-loan-to').value = formatDate(defaultTo);
		document.getElementById('chart-contrib-from').value = formatDate(defaultFrom);
		document.getElementById('chart-contrib-to').value = formatDate(defaultTo);
	}

	function fetchChartData(fromDate, toDate, callback) {
		var url = chartDataUrl + '?from_date=' + encodeURIComponent(fromDate) + '&to_date=' + encodeURIComponent(toDate);
		fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
			.then(function(r) { return r.json(); })
			.then(callback)
			.catch(function() { if (typeof callback === 'function') callback({ loan_repayment_trend: { labels: [], datasets: [] }, account_contributions: { labels: [], datasets: [] } }); });
	}

	function renderAccountTypeCards(labels, datasets) {
		var container = document.getElementById('account-type-cards');
		container.innerHTML = '';
		chartContribInstances.forEach(function(c) { if (c) c.destroy(); });
		chartContribInstances = [];
		var typesOnly = (datasets || []).filter(function(d) { return d.type !== 'line' && d.label !== 'Total'; });
		if (typesOnly.length === 0) {
			container.innerHTML = '<div class="col-12"><p class="text-muted small mb-0 py-2">' + (typeof $lang_no_data_found !== 'undefined' ? $lang_no_data_found : 'No data available') + '</p></div>';
			return;
		}
		typesOnly.forEach(function(ds, idx) {
			var col = document.createElement('div');
			col.className = 'col-sm-6';
			var canvasId = 'chart-contrib-' + idx;
			col.innerHTML = '<div class="card mb-3 chart-card-compact"><div class="card-header py-2">' + (ds.label || '') + '</div><div class="card-body py-2"><div class="chart-wrap"><canvas id="' + canvasId + '"></canvas></div></div></div>';
			container.appendChild(col);
			var barColor = ds.backgroundColor || '#1A8E8F';
			var dataArr = Array.isArray(ds.data) ? ds.data.map(function(v) { return Number(v); }) : (labels ? labels.map(function() { return 0; }) : []);
			var ctx = document.getElementById(canvasId).getContext('2d');
			var chart = new Chart(ctx, {
				type: 'bar',
				data: {
					labels: labels || [],
					datasets: [{
						label: ds.label || '',
						data: dataArr,
						backgroundColor: barColor,
						hoverBackgroundColor: barColor
					}]
				},
				options: compactOptions
			});
			chartContribInstances.push(chart);
		});
	}

	function initCharts() {
		setDefaultDates();
		var from = document.getElementById('chart-loan-from').value;
		var to = document.getElementById('chart-loan-to').value;

		fetchChartData(from, to, function(data) {
			var loanCtx = document.getElementById('chartLoanRepayment').getContext('2d');
			if (chartLoan) chartLoan.destroy();
			chartLoan = new Chart(loanCtx, {
				type: 'line',
				data: data.loan_repayment_trend || { labels: [], datasets: [] },
				options: compactOptions
			});

			var contrib = data.account_contributions || { labels: [], datasets: [] };
			renderAccountTypeCards(contrib.labels || [], contrib.datasets || []);
		});
	}

	function applyLoanDates() {
		var from = document.getElementById('chart-loan-from').value;
		var to = document.getElementById('chart-loan-to').value;
		fetchChartData(from, to, function(data) {
			if (chartLoan && data.loan_repayment_trend) {
				chartLoan.data.labels = data.loan_repayment_trend.labels;
				chartLoan.data.datasets = data.loan_repayment_trend.datasets;
				chartLoan.update();
			}
		});
	}

	function applyContribDates() {
		var from = document.getElementById('chart-contrib-from').value;
		var to = document.getElementById('chart-contrib-to').value;
		fetchChartData(from, to, function(data) {
			var contrib = data.account_contributions || { labels: [], datasets: [] };
			renderAccountTypeCards(contrib.labels || [], contrib.datasets || []);
		});
	}

	document.addEventListener('DOMContentLoaded', function() {
		initCharts();
		document.getElementById('chart-loan-apply').addEventListener('click', applyLoanDates);
		document.getElementById('chart-contrib-apply').addEventListener('click', applyContribDates);
	});
})();
</script>
@endsection