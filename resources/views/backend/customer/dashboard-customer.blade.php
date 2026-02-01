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
.upcoming-payment-table .btn-pay-now { background: #6f42c1; border-color: #6f42c1; color: #fff; padding: 0.25rem 0.5rem; font-size: 0.75rem; border-radius: 4px; }
.upcoming-payment-table .btn-pay-now:hover { background: #5a32a3; border-color: #5a32a3; color: #fff; }
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