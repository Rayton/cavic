@extends('layouts.app')

@section('content')
<div class="row">
	<div class="{{ $alert_col }}">
		<div class="card">
			<div class="card-header text-center">
				<span class="panel-title">{{ _lang('Loan Approval') }} - {{ _lang($approval->approval_level_name) }}</span>
			</div>
			<div class="card-body">
				<!-- Loan Details -->
				<div class="row mb-4">
					<div class="col-lg-12">
						<h5>{{ _lang('Loan Details') }}</h5>
						<table class="table table-bordered">
							<tr>
								<td width="30%">{{ _lang("Loan ID") }}</td>
								<td>{{ $approval->loan->loan_id ?? _lang('N/A') }}</td>
							</tr>
							<tr>
								<td>{{ _lang("Loan Product") }}</td>
								<td>{{ $approval->loan->loan_product->name ?? _lang('N/A') }}</td>
							</tr>
							<tr>
								<td>{{ _lang("Borrower") }}</td>
								<td>{{ $approval->loan->borrower->name ?? _lang('N/A') }}</td>
							</tr>
							<tr>
								<td>{{ _lang("Member No") }}</td>
								<td>{{ $approval->loan->borrower->member_no ?? _lang('N/A') }}</td>
							</tr>
							<tr>
								<td>{{ _lang("Applied Amount") }}</td>
								<td>{{ decimalPlace($approval->loan->applied_amount ?? 0, currency($approval->loan->currency->name ?? 'USD')) }}</td>
							</tr>
							<tr>
								<td>{{ _lang("Currency") }}</td>
								<td>{{ $approval->loan->currency->name ?? _lang('N/A') }}</td>
							</tr>
							<tr>
								<td>{{ _lang("First Payment Date") }}</td>
								<td>{{ $approval->loan->first_payment_date ?? _lang('N/A') }}</td>
							</tr>
							<tr>
								<td>{{ _lang("Release Date") }}</td>
								<td>{{ $approval->loan->release_date ?? _lang('N/A') }}</td>
							</tr>
							<tr>
								<td>{{ _lang("Description") }}</td>
								<td>{{ $approval->loan->description ?? _lang('N/A') }}</td>
							</tr>
						</table>
					</div>
				</div>

				<!-- Approval Progress -->
				<div class="row mb-4">
					<div class="col-lg-12">
						<h5>{{ _lang('Approval Progress') }}</h5>
						@php
							$allApprovals = $approval->loan->approvals;
							$progress = $approval->loan->approval_progress;
						@endphp
						<div class="progress mb-2" style="height: 30px;">
							<div class="progress-bar" role="progressbar" style="width: {{ $progress['percentage'] }}%">
								{{ $progress['current'] }}/{{ $progress['total'] }} {{ _lang('Approved') }}
							</div>
						</div>
						<div class="row">
							@foreach($allApprovals as $app)
							<div class="col-md-3 mb-2">
								<div class="card">
									<div class="card-body p-2 text-center">
										<small><strong>{{ _lang($app->approval_level_name) }}</strong></small><br>
										@if($app->status == \App\Models\LoanApproval::STATUS_APPROVED)
											<span class="badge badge-success">{{ _lang('Approved') }}</span>
										@elseif($app->status == \App\Models\LoanApproval::STATUS_REJECTED)
											<span class="badge badge-danger">{{ _lang('Rejected') }}</span>
										@else
											<span class="badge badge-warning">{{ _lang('Pending') }}</span>
										@endif
									</div>
								</div>
							</div>
							@endforeach
						</div>
					</div>
				</div>

				<!-- Check if previous levels are approved -->
				@php
					$canApprove = true;
					$currentLevel = $approval->approval_level;
					$allApprovals = $approval->loan->approvals;
					
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

				@if(!$canApprove)
				<div class="alert alert-warning">
					<i class="fas fa-exclamation-triangle"></i>
					<strong>{{ _lang('Warning') }}:</strong> {{ _lang('This loan cannot be approved yet. All previous approval levels must be completed first.') }}
				</div>
				@endif

				<!-- Approval Form -->
				<form method="post" class="validate" autocomplete="off" action="{{ route('loan_approvals.approve', ['tenant' => request()->tenant->slug, 'id' => $approval->id]) }}">
					@csrf
					<div class="row">
						<div class="col-lg-12">
							<div class="form-group">
								<label class="control-label">{{ _lang('Remarks') }} <span class="text-danger">*</span></label>
								<textarea class="form-control" name="remarks" rows="4" placeholder="{{ _lang('Enter your remarks (optional)') }}" {{ !$canApprove ? 'disabled' : '' }}>{{ old('remarks') }}</textarea>
							</div>
						</div>

						<div class="col-lg-12 mt-3">
							<div class="form-group">
								<button type="submit" class="btn btn-success btn-lg btn-block" {{ !$canApprove ? 'disabled' : '' }}>
									<i class="fas fa-check-circle mr-1"></i>{{ _lang('Approve') }}
								</button>
								<a href="#" class="btn btn-danger btn-lg btn-block mt-2 {{ !$canApprove ? 'disabled' : '' }}" data-toggle="modal" data-target="#rejectModal" {{ !$canApprove ? 'onclick="return false;"' : '' }}>
									<i class="fas fa-times-circle mr-1"></i>{{ _lang('Reject') }}
								</a>
								<a href="{{ route('loan_approvals.index', request()->tenant->slug) }}" class="btn btn-secondary btn-lg btn-block mt-2">
									<i class="fas fa-arrow-left mr-1"></i>{{ _lang('Back') }}
								</a>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" role="dialog">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<form method="post" action="{{ route('loan_approvals.reject', ['tenant' => request()->tenant->slug, 'id' => $approval->id]) }}">
				@csrf
				<div class="modal-header">
					<h5 class="modal-title">{{ _lang('Reject Loan Application') }}</h5>
					<button type="button" class="close" data-dismiss="modal">
						<span>&times;</span>
					</button>
				</div>
				<div class="modal-body">
					@if(!$canApprove)
					<div class="alert alert-warning">
						<i class="fas fa-exclamation-triangle"></i>
						{{ _lang('This loan cannot be rejected yet. All previous approval levels must be completed first.') }}
					</div>
					@endif
					<div class="form-group">
						<label class="control-label">{{ _lang('Rejection Reason') }} <span class="text-danger">*</span></label>
						<textarea class="form-control" name="remarks" rows="4" required placeholder="{{ _lang('Please provide a reason for rejection') }}" {{ !$canApprove ? 'disabled' : '' }}></textarea>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">{{ _lang('Cancel') }}</button>
					<button type="submit" class="btn btn-danger" {{ !$canApprove ? 'disabled' : '' }}>{{ _lang('Reject Loan') }}</button>
				</div>
			</form>
		</div>
	</div>
</div>
@endsection
