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
/* Deposit modal */
#depositManualModal .modal-dialog { max-width: 700px; }
#depositManualModal .deposit-form-panel { display: none; }
#depositManualModal .deposit-form-panel.active { display: block; }
#depositManualModal .account-rows-table { font-size: 0.8125rem; }
#depositManualModal .account-rows-table input.form-control { font-size: 0.8125rem; }
#depositManualModal .deposit-account-row .btn-remove-row { padding: 0.2rem 0.4rem; font-size: 0.75rem; opacity: 0.8; }
#depositManualModal .deposit-account-row .btn-remove-row:hover { opacity: 1; }
#depositManualModal .deposit-add-account-row td { vertical-align: middle; }
/* Attachment styled area */
#depositManualModal .deposit-attachment-wrap {
	border: 2px dashed #dee2e6;
	border-radius: 8px;
	background: #f8f9fa;
	padding: 1rem 1.25rem;
	transition: border-color 0.2s, background 0.2s;
}
#depositManualModal .deposit-attachment-wrap:hover { border-color: #1A8E8F; background: #f0f9f9; }
#depositManualModal .deposit-attachment-wrap.has-file { border-color: #1A8E8F; background: #e8f6f6; }
#depositManualModal .deposit-attachment-wrap .deposit-attachment-input { position: absolute; opacity: 0; width: 100%; height: 100%; left: 0; top: 0; cursor: pointer; }
#depositManualModal .deposit-attachment-wrap .deposit-attachment-label { cursor: pointer; margin: 0; display: flex; align-items: center; gap: 0.5rem; min-height: 2.5rem; }
#depositManualModal .deposit-attachment-wrap .deposit-attachment-icon { font-size: 1.5rem; color: #1A8E8F; }
#depositManualModal .deposit-attachment-wrap .deposit-attachment-hint { font-size: 0.75rem; color: #6c757d; margin-top: 0.25rem; }
</style>
{{-- Deposit modal: Manual Deposit (portal/deposit/manual_deposit/2 flow) --}}
<div class="modal fade" id="depositManualModal" tabindex="-1" role="dialog" aria-labelledby="depositManualModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="depositManualModalLabel">{{ _lang('Manual Deposit') }}</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<label class="control-label">{{ _lang('Select Manual Deposit Method') }}</label>
					<select class="form-control" id="deposit_method_select" data-selected="">
						<option value="">{{ _lang('Select One') }}</option>
						@foreach($deposit_methods ?? [] as $dm)
						<option value="{{ $dm->id }}" data-currency="{{ $dm->currency->name ?? '' }}" data-requirements="{{ $dm->requirements ? json_encode($dm->requirements) : '[]' }}">{{ $dm->name }}</option>
						@endforeach
					</select>
				</div>
				@foreach($deposit_methods ?? [] as $dm)
				<div id="deposit_charge_limits_{{ $dm->id }}" class="deposit-charge-limits-panel mt-2" style="display: none;">
					<div class="table-responsive">
						<table class="table table-bordered table-sm">
							<thead>
								<tr><th colspan="2" class="text-center bg-light">{{ _lang('Limits & Charges') }}</th></tr>
								<tr><th>{{ _lang('Amount Limit') }}</th><th>{{ _lang('Charge') }}</th></tr>
							</thead>
							<tbody>
								@if($dm->chargeLimits && $dm->chargeLimits->count() > 0)
									@foreach($dm->chargeLimits as $cl)
									<tr>
										<td>{{ $dm->currency->name ?? '' }} {{ $cl->minimum_amount }} - {{ $dm->currency->name ?? '' }} {{ $cl->maximum_amount }}</td>
										<td>{{ $cl->fixed_charge ?? 0 }} + {{ $cl->charge_in_percentage ?? 0 }}%</td>
									</tr>
									@endforeach
								@else
									<tr><td colspan="2" class="text-muted">{{ _lang('No limits configured') }}</td></tr>
								@endif
							</tbody>
						</table>
					</div>
				</div>
				@if(!empty($dm->descriptions))
				<div id="deposit_instructions_{{ $dm->id }}" class="deposit-instructions-panel mt-2" style="display: none;">
					<label class="control-label font-weight-bold">{{ _lang('Instructions') }}</label>
					<div class="border rounded p-2 small">{!! xss_clean($dm->descriptions) !!}</div>
				</div>
				@endif
				@endforeach
				<div id="deposit_form_wrapper" class="deposit-form-panel">
					<form id="deposit_manual_form" enctype="multipart/form-data">
						@csrf
						<input type="hidden" name="method_id" id="deposit_method_id" value="">
						<div class="row">
							<div class="col-12">
								<label class="control-label font-weight-bold">{{ _lang('Credit accounts') }}</label>
								<div class="table-responsive">
									<table class="table table-bordered account-rows-table">
										<thead>
											<tr>
												<th>{{ _lang('Account Type') }}</th>
												<th class="text-right">{{ _lang('Deposit Amount') }}</th>
												<th class="text-right">{{ _lang('Converted Amount') }} ({{ _lang('Charge Included') }})</th>
												<th class="text-center" style="width: 90px;">{{ _lang('Action') }}</th>
											</tr>
										</thead>
										<tbody id="deposit_account_tbody">
											<tr class="deposit-add-account-row" id="deposit_add_account_row">
												<td colspan="4" class="bg-light py-2">
													<div class="d-flex align-items-center gap-2 flex-wrap">
														<span class="small font-weight-bold text-muted">{{ _lang('Add account type') }}:</span>
														<select class="form-control form-control-sm" id="deposit_add_account_select" style="max-width: 280px;">
															<option value="">{{ _lang('Select to add back') }}</option>
														</select>
														<button type="button" class="btn btn-sm dashboard-brand-btn" id="deposit_add_account_btn" title="{{ _lang('Add selected account to list') }}"><i class="ti-plus"></i> {{ _lang('Add') }}</button>
													</div>
												</td>
											</tr>
											@foreach($deposit_accounts ?? [] as $acc)
											<tr class="deposit-account-row" data-account-id="{{ $acc->id }}" data-currency="{{ $acc->savings_type->currency->name ?? '' }}" data-account-label="{{ $acc->account_number }} ({{ $acc->savings_type->name ?? '' }} - {{ $acc->savings_type->currency->name ?? '' }})">
												<td class="row-account-label">{{ $acc->account_number }} ({{ $acc->savings_type->name ?? '' }} - {{ $acc->savings_type->currency->name ?? '' }})</td>
												<td class="text-right">
													<div class="input-group input-group-sm">
														<div class="input-group-prepend"><span class="input-group-text row-account-currency">{{ $acc->savings_type->currency->name ?? '' }}</span></div>
														<input type="number" step="any" min="0" class="form-control row-deposit-amount" name="rows[{{ $acc->id }}][amount]" placeholder="0" data-account-id="{{ $acc->id }}">
													</div>
												</td>
												<td class="text-right">
													<div class="input-group input-group-sm">
														<div class="input-group-prepend"><span class="input-group-text row-gateway-currency">-</span></div>
														<input type="text" class="form-control row-converted-amount" readonly placeholder="0" data-account-id="{{ $acc->id }}">
													</div>
												</td>
												<td class="text-center">
													<button type="button" class="btn btn-outline-danger btn-sm btn-remove-row" title="{{ _lang('Remove from list') }}"><i class="ti-trash"></i> {{ _lang('Remove') }}</button>
												</td>
											</tr>
											@endforeach
										</tbody>
										<tfoot>
											<tr class="table-light">
												<td class="font-weight-bold">{{ _lang('Enter total amount') }}</td>
												<td class="text-right">
													<div class="input-group input-group-sm d-inline-flex" style="max-width: 160px;">
														<div class="input-group-prepend"><span class="input-group-text" id="deposit_enter_total_currency">-</span></div>
														<input type="number" step="0.01" min="0" class="form-control" id="deposit_enter_total_amount" placeholder="0.00" title="{{ _lang('Must equal the calculated total below') }}">
													</div>
												</td>
												<td class="text-right font-weight-bold">
													<span class="small text-muted">{{ _lang('Calculated total') }}:</span> <span id="deposit_total_display">0</span> <span id="deposit_total_currency">-</span>
												</td>
												<td></td>
											</tr>
										</tfoot>
									</table>
								</div>
							</div>
						</div>
						<div id="deposit_requirements_container" class="row mt-2"></div>
						<div class="row mt-2">
							<div class="col-md-12">
								<div class="form-group">
									<label class="control-label">{{ _lang('Description') }}</label>
									<textarea class="form-control" name="description" id="deposit_description" rows="2"></textarea>
								</div>
							</div>
							<div class="col-md-12">
								<div class="form-group">
									<label class="control-label">{{ _lang('Attachment') }}</label>
									<div class="deposit-attachment-wrap position-relative" id="deposit_attachment_wrap">
										<input type="file" class="deposit-attachment-input" name="attachment" id="deposit_attachment" accept=".jpeg,.jpg,.png,.doc,.pdf,.docx">
										<label class="deposit-attachment-label mb-0" for="deposit_attachment" id="deposit_attachment_label">
											<span class="deposit-attachment-icon"><i class="ti-upload"></i></span>
											<span class="deposit-attachment-text">{{ _lang('Choose file') }} <span class="text-muted">({{ _lang('optional') }})</span></span>
										</label>
										<div class="deposit-attachment-hint">{{ _lang('Accepted') }}: JPEG, PNG, JPG, DOC, PDF, DOCX (max 4MB)</div>
									</div>
								</div>
							</div>
						</div>
					</form>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">{{ _lang('Close') }}</button>
				<button type="button" class="btn btn-primary dashboard-brand-btn" id="deposit_manual_submit_btn" style="display: none;"><i class="ti-check-box"></i> {{ _lang('Submit') }}</button>
			</div>
		</div>
	</div>
</div>
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
{{-- Deposit modal: method selection, account rows, get_final_amount, submit (portal/deposit/manual_deposit/2) --}}
<script>
(function($) {
	'use strict';
	var depositManualDepositUrl = "{{ route('deposit.manual_deposit', ['id' => '__ID__']) }}";
	var getFinalAmountUrl = "{{ route('transfer.get_final_amount') }}";
	var csrfToken = $('meta[name="csrf-token"]').attr('content');
	var currentMethodId = null;
	var currentMethodCurrency = null;
	var pendingRequests = 0;

	function showDepositFormPanel(show) {
		if (show) {
			$('#deposit_form_wrapper').addClass('active');
			$('#deposit_manual_submit_btn').show();
		} else {
			$('#deposit_form_wrapper').removeClass('active');
			$('#deposit_manual_submit_btn').hide();
		}
	}

	function buildRequirementsContainer(requirements) {
		var container = $('#deposit_requirements_container');
		container.empty();
		if (requirements && (Array.isArray(requirements) ? requirements.length : Object.keys(requirements).length)) {
			var reqs = Array.isArray(requirements) ? requirements : (typeof requirements === 'object' ? Object.values(requirements) : []);
			reqs.forEach(function(label) {
				var key = String(label).replace(/\s+/g, '_');
				container.append(
					'<div class="col-md-6"><div class="form-group">' +
					'<label class="control-label">' + (label || key) + '</label>' +
					'<input type="text" class="form-control" name="requirements[' + key + ']" data-requirement-key="' + key + '" required></div></div>'
				);
			});
		}
	}

	function updateRowGatewayCurrency() {
		$('#deposit_form_wrapper .row-gateway-currency').text(currentMethodCurrency || '-');
		$('#deposit_enter_total_currency').text(currentMethodCurrency || '-');
	}

	function updateTotalDisplay() {
		var total = 0;
		$('#deposit_form_wrapper .row-converted-amount').each(function() {
			var v = parseFloat($(this).val()) || 0;
			total += v;
		});
		$('#deposit_total_display').text(parseFloat(total).toFixed(2));
		$('#deposit_total_currency').text(currentMethodCurrency || '');
	}

	function fetchConvertedAmount(fromCurrency, toCurrency, amount, accountId, methodId, callback) {
		$.ajax({
			method: 'POST',
			url: getFinalAmountUrl,
			data: {
				_token: csrfToken,
				from: fromCurrency,
				to: toCurrency,
				amount: amount,
				type: 'manual_deposit',
				id: methodId
			},
			success: function(data) {
				var res = typeof data === 'string' ? JSON.parse(data) : data;
				if (res && res.result === true) {
					callback(null, parseFloat(res.amount));
				} else {
					callback(res && res.message ? res.message : 'Error');
				}
			},
			error: function() { callback('Request failed'); }
		});
	}

	$(document).on('change', '#deposit_method_select', function() {
		var opt = $(this).find('option:selected');
		var methodId = opt.val();
		if (!methodId) {
			$('#deposit_method_id').val('');
			$('.deposit-charge-limits-panel, .deposit-instructions-panel').hide();
			showDepositFormPanel(false);
			return;
		}
		currentMethodId = methodId;
		currentMethodCurrency = opt.data('currency') || '';
		$('.deposit-charge-limits-panel, .deposit-instructions-panel').hide();
		$('#deposit_charge_limits_' + methodId).show();
		var $inst = $('#deposit_instructions_' + methodId);
		if ($inst.length) $inst.show();
		var requirements = [];
		try {
			requirements = typeof opt.data('requirements') === 'string' ? JSON.parse(opt.data('requirements')) : (opt.data('requirements') || []);
		} catch (e) {}
		$('#deposit_method_id').val(methodId);
		buildRequirementsContainer(requirements);
		updateRowGatewayCurrency();
		$('#deposit_form_wrapper .row-deposit-amount, #deposit_form_wrapper .row-converted-amount').val('');
		$('#deposit_enter_total_amount').val('');
		$('#deposit_form_wrapper .deposit-account-row').show().removeClass('deposit-row-removed');
		$('#deposit_add_account_select').find('option:not(:first)').remove();
		updateTotalDisplay();
		showDepositFormPanel(true);
	});

	$(document).on('click', '#deposit_form_wrapper .btn-remove-row', function() {
		var $row = $(this).closest('tr.deposit-account-row');
		var id = $row.data('account-id');
		var label = $row.data('account-label') || $row.find('.row-account-label').first().text() || ('Account ' + id);
		$row.hide().addClass('deposit-row-removed');
		$row.find('.row-deposit-amount, .row-converted-amount').val('');
		$('#deposit_add_account_select').append($('<option></option>').val(id).text(label));
		updateTotalDisplay();
	});

	function depositAddSelectedAccount() {
		var id = $('#deposit_add_account_select').val();
		if (!id) return;
		var $row = $('#deposit_form_wrapper .deposit-account-row[data-account-id="' + id + '"]');
		if ($row.length) {
			$row.show().removeClass('deposit-row-removed');
			$row.find('.row-deposit-amount, .row-converted-amount').val('');
			$('#deposit_add_account_select').find('option[value="' + id + '"]').remove();
			$('#deposit_add_account_select').val('');
		}
		updateTotalDisplay();
	}

	$(document).on('change', '#deposit_add_account_select', depositAddSelectedAccount);
	$(document).on('click', '#deposit_add_account_btn', depositAddSelectedAccount);

	$(document).on('change', '#deposit_attachment', function() {
		var input = this;
		var wrap = $('#deposit_attachment_wrap');
		var labelText = $('#deposit_attachment_label .deposit-attachment-text');
		if (input.files && input.files.length) {
			wrap.addClass('has-file');
			labelText.text(input.files[0].name);
		} else {
			wrap.removeClass('has-file');
			labelText.html('{{ _lang("Choose file") }} <span class="text-muted">({{ _lang("optional") }})</span>');
		}
	});

	$(document).on('input keyup', '#deposit_form_wrapper .row-deposit-amount', function() {
		var input = $(this);
		var row = input.closest('tr.deposit-account-row');
		var accountCurrency = row.data('currency');
		var amount = parseFloat(input.val()) || 0;
		var convertedInput = row.find('.row-converted-amount');
		if (!currentMethodId || !accountCurrency || !currentMethodCurrency) {
			convertedInput.val('');
			updateTotalDisplay();
			return;
		}
		if (amount <= 0) {
			convertedInput.val('');
			updateTotalDisplay();
			return;
		}
		fetchConvertedAmount(accountCurrency, currentMethodCurrency, amount, row.data('account-id'), currentMethodId, function(err, converted) {
			if (err) {
				convertedInput.val('');
				if (typeof Swal !== 'undefined') Swal.fire('{{ _lang("Alert") }}', err, 'warning');
			} else {
				convertedInput.val(parseFloat(converted).toFixed(2));
			}
			updateTotalDisplay();
		});
	});

	$(document).on('click', '#deposit_manual_submit_btn', function() {
		var methodId = currentMethodId;
		if (!methodId) return;
		var rows = [];
		var calculatedTotal = 0;
		$('#deposit_form_wrapper .deposit-account-row').each(function() {
			var row = $(this);
			var accountId = row.data('account-id');
			var amount = parseFloat(row.find('.row-deposit-amount').val()) || 0;
			var converted = parseFloat(row.find('.row-converted-amount').val()) || 0;
			if (amount > 0) {
				rows.push({ credit_account: accountId, amount: amount });
				calculatedTotal += converted;
			}
		});
		if (rows.length === 0) {
			if (typeof Swal !== 'undefined') Swal.fire('{{ _lang("Alert") }}', '{{ _lang("Please enter at least one deposit amount.") }}', 'warning');
			return;
		}
		var enteredTotal = parseFloat($('#deposit_enter_total_amount').val());
		if (isNaN(enteredTotal)) {
			if (typeof Swal !== 'undefined') Swal.fire('{{ _lang("Alert") }}', '{{ _lang("Please enter the total amount.") }}', 'warning');
			return;
		}
		var tolerance = 0.01;
		if (Math.abs(enteredTotal - calculatedTotal) > tolerance) {
			if (typeof Swal !== 'undefined') Swal.fire('{{ _lang("Alert") }}', '{{ _lang("Entered total amount must equal the calculated total.") }} ({{ _lang("Calculated") }}: ' + parseFloat(calculatedTotal).toFixed(2) + ' ' + (currentMethodCurrency || '') + ')', 'warning');
			return;
		}
		var requirements = {};
		$('#deposit_requirements_container input[name^="requirements["]').each(function() {
			var name = $(this).attr('name');
			var match = name.match(/requirements\[([^\]]+)\]/);
			if (match) requirements[match[1]] = $(this).val();
		});
		var description = $('#deposit_description').val() || '';
		var attachmentInput = document.getElementById('deposit_attachment');
		var attachmentFile = attachmentInput && attachmentInput.files && attachmentInput.files[0] ? attachmentInput.files[0] : null;
		var totalConverted = 0;
		$('#deposit_form_wrapper .row-converted-amount').each(function() { totalConverted += parseFloat($(this).val()) || 0; });
		var submitBtn = $('#deposit_manual_submit_btn');
		submitBtn.prop('disabled', true);
		var url = depositManualDepositUrl.replace('__ID__', methodId);
		var doneCount = 0;
		var hasError = false;
		function onRowDone(err) {
			doneCount++;
			if (err) hasError = true;
			if (doneCount >= rows.length) {
				submitBtn.prop('disabled', false);
				if (hasError) {
					if (typeof Swal !== 'undefined') Swal.fire('{{ _lang("Error") }}', '{{ _lang("One or more deposit requests failed.") }}', 'error');
				} else {
					if (typeof Swal !== 'undefined') Swal.fire('{{ _lang("Success") }}', '{{ _lang("Deposit Request submited successfully") }}', 'success').then(function() {
						$('#depositManualModal').modal('hide');
						$('#deposit_method_select').val('');
						showDepositFormPanel(false);
						$('#deposit_manual_form')[0].reset();
						location.reload();
					});
				}
			}
		}
		rows.forEach(function(r) {
			var fd = new FormData();
			fd.append('_token', csrfToken);
			fd.append('credit_account', r.credit_account);
			fd.append('amount', r.amount);
			fd.append('description', description);
			Object.keys(requirements).forEach(function(k) { fd.append('requirements[' + k + ']', requirements[k]); });
			if (attachmentFile) fd.append('attachment', attachmentFile);
			$.ajax({
				url: url,
				method: 'POST',
				data: fd,
				processData: false,
				contentType: false,
				headers: { 'X-Requested-With': 'XMLHttpRequest' },
				success: function(data) {
					var res = typeof data === 'string' ? (function(){ try { return JSON.parse(data); } catch(e) { return data; } })() : data;
					if (res && res.result === 'success') onRowDone(null);
					else onRowDone(res && res.message ? res.message : 'Error');
				},
				error: function(xhr) {
					var msg = 'Request failed';
					if (xhr.responseJSON && xhr.responseJSON.message) msg = Array.isArray(xhr.responseJSON.message) ? xhr.responseJSON.message.join(' ') : xhr.responseJSON.message;
					onRowDone(msg);
				}
			});
		});
	});

	$('#depositManualModal').on('hidden.bs.modal', function() {
		$('#deposit_method_select').val('');
		showDepositFormPanel(false);
		currentMethodId = null;
		currentMethodCurrency = null;
		$('#deposit_enter_total_amount').val('');
		$('#deposit_attachment_wrap').removeClass('has-file');
		$('#deposit_attachment_label .deposit-attachment-text').html('{{ _lang("Choose file") }} <span class="text-muted">({{ _lang("optional") }})</span>');
		$('#deposit_add_account_select').find('option:not(:first)').remove();
	});
})(jQuery);
</script>
@endsection