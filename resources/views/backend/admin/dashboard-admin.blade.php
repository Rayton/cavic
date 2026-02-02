@extends('layouts.app')

@section('content')
@php $card_currency = $admin_interest_currency ?? ''; @endphp
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
</style>

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
