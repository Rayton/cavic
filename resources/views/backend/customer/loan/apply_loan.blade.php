@extends('layouts.app')

@section('content')
<div class="row">
	<div class="{{ $alert_col }}">
		<div class="card">
			<div class="card-header">
				<span class="panel-title">{{ _lang('Apply New Loan') }}</span>
			</div>
			<div class="card-body">
				<form method="post" class="validate" autocomplete="off" action="{{ route('loans.apply_loan') }}" enctype="multipart/form-data">
					@csrf
					<div class="row">

						<div class="col-lg-6">
							<div class="form-group">
								<label class="control-label">{{ _lang('Loan Product') }}</label>
								<select class="form-control auto-select select2" data-selected="{{ request()->product ?? old('loan_product_id') }}" name="loan_product_id" required>
									<option value="">{{ _lang('Select One') }}</option>
									@foreach(\App\Models\LoanProduct::active()->get() as $loanProduct)
									<option value="{{ $loanProduct->id }}" data-penalties="{{ $loanProduct->late_payment_penalties }}" data-loan-id="{{ $loanProduct->loan_id_prefix.$loanProduct->starting_loan_id }}" data-details="{{ $loanProduct }}">{{ $loanProduct->name }}</option>
									@endforeach
								</select>
							</div>
						</div>

						<div class="col-lg-6">
							<div class="form-group">
								<label class="control-label">{{ _lang('Currency') }}</label>
								<select class="form-control auto-select" data-selected="{{ old('currency_id') }}" name="currency_id" required>
									<option value="">{{ _lang('Select One') }}</option>
									@foreach(\App\Models\Currency::where('status', 1)->get() as $currency)
									<option value="{{ $currency->id }}">{{ $currency->full_name }} ({{ $currency->name }})</option>
									@endforeach
								</select>
							</div>
						</div>

						<div class="col-lg-6">
							<div class="form-group">
								<label class="control-label">{{ _lang('First Payment Date') }}</label>
								<input type="text" class="form-control datepicker" name="first_payment_date" value="{{ old('first_payment_date') }}" required>
							</div>
						</div>

						<div class="col-lg-6">
							<div class="form-group">
								<label class="control-label">{{ _lang('Applied Amount') }}</label>
								<input type="text" class="form-control float-field" name="applied_amount" value="{{ old('applied_amount') }}" required>
							</div>
						</div>

						<!--Custom Fields-->
						@if(! $customFields->isEmpty())
							@foreach($customFields as $customField)
							<div class="{{ $customField->field_width }}">
								<div class="form-group">
									<label class="control-label">{{ $customField->field_name }}</label>	
									{!! xss_clean(generate_input_field($customField)) !!}
								</div>
							</div>
							@endforeach
                        @endif

						<div class="col-lg-12">
							<div class="form-group">
								<label class="control-label">{{ _lang('Fee Deduct Account') }}</label>
								<select class="form-control auto-select select2" data-selected="{{ old('debit_account_id') }}" name="debit_account_id" required>
									<option value="">{{ _lang('Select One') }}</option>
									@foreach($accounts as $account)
                                        <option value="{{ $account->id }}">{{ $account->account_number }} ({{ $account->savings_type->name }} - {{ $account->savings_type->currency->name }})</option>
                                    @endforeach
								</select>
							</div>
						</div>

						<div class="col-lg-12">
							<div class="form-group">
								<label class="control-label">{{ _lang('Attachment') }}</label>
								<input type="file" class="file-uploader" name="attachment">
							</div>
						</div>

						<div class="col-lg-12">
							<div class="form-group">
								<label class="control-label">{{ _lang('Description') }}</label>
								<textarea class="form-control" name="description">{{ old('description') }}</textarea>
							</div>
						</div>

						<div class="col-lg-12">
							<div class="form-group">
								<label class="control-label">{{ _lang('Remarks') }}</label>
								<textarea class="form-control" name="remarks">{{ old('remarks') }}</textarea>
							</div>
						</div>

						<div class="col-lg-12">
							<hr>
							<h5>{{ _lang('Select Trustees') }}</h5>
							<p class="text-muted">{{ _lang('Please select two trustees who will review your loan application') }}</p>
						</div>

						<div class="col-lg-6">
							<div class="form-group">
								<label class="control-label">{{ _lang('Trustee 1') }} <span class="text-danger">*</span></label>
								<select class="form-control auto-select select2" data-selected="{{ old('trustee1_member_id') }}" name="trustee1_member_id" required>
									<option value="">{{ _lang('Select Trustee 1') }}</option>
									@foreach(\App\Models\Member::orderBy('first_name', 'asc')->get() as $member)
										@if($member->id != auth()->user()->member->id)
										<option value="{{ $member->id }}">{{ $member->name }} ({{ $member->member_no }})</option>
										@endif
									@endforeach
								</select>
							</div>
						</div>

						<div class="col-lg-6">
							<div class="form-group">
								<label class="control-label">{{ _lang('Trustee 2') }} <span class="text-danger">*</span></label>
								<select class="form-control auto-select select2" data-selected="{{ old('trustee2_member_id') }}" name="trustee2_member_id" required>
									<option value="">{{ _lang('Select Trustee 2') }}</option>
									@foreach(\App\Models\Member::orderBy('first_name', 'asc')->get() as $member)
										@if($member->id != auth()->user()->member->id)
										<option value="{{ $member->id }}">{{ $member->name }} ({{ $member->member_no }})</option>
										@endif
									@endforeach
								</select>
							</div>
						</div>

						<div class="col-lg-12">
							<hr>
							<h5>{{ _lang('Select Leaders') }}</h5>
							<p class="text-muted">{{ _lang('Please select Secretary and Chairman who will review your loan application') }}</p>
						</div>

						<div class="col-lg-6">
							<div class="form-group">
								<label class="control-label">{{ _lang('Secretary') }} <span class="text-danger">*</span></label>
								<select class="form-control auto-select select2" data-selected="{{ old('secretary_leader_id') }}" name="secretary_leader_id" required>
									<option value="">{{ _lang('Select Secretary') }}</option>
									@foreach($secretaries as $secretary)
										@if($secretary->member)
										<option value="{{ $secretary->id }}">{{ $secretary->member->name }} ({{ $secretary->member->member_no }})</option>
										@endif
									@endforeach
								</select>
							</div>
						</div>

						<div class="col-lg-6">
							<div class="form-group">
								<label class="control-label">{{ _lang('Chairman') }} <span class="text-danger">*</span></label>
								<select class="form-control auto-select select2" data-selected="{{ old('chairman_leader_id') }}" name="chairman_leader_id" required>
									<option value="">{{ _lang('Select Chairman') }}</option>
									@foreach($chairmen as $chairman)
										@if($chairman->member)
										<option value="{{ $chairman->id }}">{{ $chairman->member->name }} ({{ $chairman->member->member_no }})</option>
										@endif
									@endforeach
								</select>
							</div>
						</div>

						<div class="col-md-12 mt-2">
							<div class="form-group">
								<button type="submit" class="btn btn-primary"><i class="ti-check-box"></i>&nbsp;{{ _lang('Submit Application') }}</button>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
@endsection
