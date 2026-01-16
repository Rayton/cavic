@extends('layouts.app')

@section('content')
<div class="row">
	<div class="col-lg-12">
		<div class="card">
			<div class="card-header d-flex align-items-center">
				<span class="panel-title">{{ _lang('Approve Loan Application') }}</span>
			</div>
			<div class="card-body">
				@if($pendingApprovals->count() > 0)
				<table class="table table-bordered data-table">
					<thead>
						<tr>
							<th>{{ _lang('Loan ID') }}</th>
							<th>{{ _lang('Borrower') }}</th>
							<th>{{ _lang('Loan Product') }}</th>
							<th>{{ _lang('Amount') }}</th>
							<th>{{ _lang('Currency') }}</th>
							<th>{{ _lang('Approval Level') }}</th>
							<th>{{ _lang('Applied Date') }}</th>
							<th class="text-center">{{ _lang('Action') }}</th>
						</tr>
					</thead>
					<tbody>
						@foreach($pendingApprovals as $approval)
						@php
							$canApprove = true;
							$currentLevel = $approval->approval_level;
							$allApprovals = $approval->loan->approvals ?? collect();
							
							// Check if all previous levels are approved
							if ($currentLevel > 1) {
								for ($level = 1; $level < $currentLevel; $level++) {
									$previousApproval = $allApprovals->where('approval_level', $level)->first();
									if (!$previousApproval || $previousApproval->status != \App\Models\LoanApproval::STATUS_APPROVED) {
										$canApprove = false;
										break;
									}
								}
							}
						@endphp
						@if($canApprove)
						<tr>
							<td>{{ $approval->loan->loan_id ?? _lang('N/A') }}</td>
							<td>{{ $approval->loan->borrower->name ?? _lang('N/A') }}</td>
							<td>{{ $approval->loan->loan_product->name ?? _lang('N/A') }}</td>
							<td>{{ decimalPlace($approval->loan->applied_amount ?? 0, currency($approval->loan->currency->name ?? 'USD')) }}</td>
							<td>{{ $approval->loan->currency->name ?? _lang('N/A') }}</td>
							<td>
								<span class="badge badge-warning">{{ _lang($approval->approval_level_name) }}</span>
							</td>
							<td>{{ $approval->loan->created_at ?? _lang('N/A') }}</td>
							<td class="text-center">
								<a href="{{ route('loan_approvals.show', ['tenant' => request()->tenant->slug, 'id' => $approval->id]) }}" 
								   class="btn btn-primary btn-xs">
									<i class="ti-eye"></i>&nbsp;{{ _lang('Review & Approve') }}
								</a>
							</td>
						</tr>
						@endif
						@endforeach
					</tbody>
				</table>
				@else
				<div class="alert alert-info">
					{{ _lang('No pending loan approvals at this time') }}
				</div>
				@endif
			</div>
		</div>
	</div>
</div>
@endsection
