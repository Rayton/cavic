@php
    $loanproduct = $loanproduct ?? null;
    $method = $method ?? null;
    $formClass = $formClass ?? 'validate';
    $submitLabel = $submitLabel ?? _lang('Save Changes');
@endphp
<form method="post" class="{{ $formClass }}" autocomplete="off" action="{{ $action }}" enctype="multipart/form-data">
    @csrf
    @if($method)
        <input name="_method" type="hidden" value="{{ $method }}">
    @endif
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <label class="control-label">{{ _lang('Name') }}</label>
                <input type="text" class="form-control" name="name" value="{{ old('name', optional($loanproduct)->name) }}" required>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label class="control-label">{{ _lang('Loan ID Prefix') }}</label>
                <input type="text" class="form-control" name="loan_id_prefix" value="{{ old('loan_id_prefix', optional($loanproduct)->loan_id_prefix) }}">
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label class="control-label">{{ _lang('Starting Loan ID') }}</label>
                <input type="number" class="form-control" name="starting_loan_id" value="{{ old('starting_loan_id', optional($loanproduct)->starting_loan_id) }}" required>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label class="control-label">{{ _lang('Minimum Amount').' '.currency() }}</label>
                <input type="text" class="form-control float-field" name="minimum_amount" value="{{ old('minimum_amount', optional($loanproduct)->minimum_amount) }}" required>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label class="control-label">{{ _lang('Maximum Amount').' '.currency() }}</label>
                <input type="text" class="form-control float-field" name="maximum_amount" value="{{ old('maximum_amount', optional($loanproduct)->maximum_amount) }}" required>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label class="control-label">{{ _lang('Interest Rate Per Year') }} (%)</label>
                <input type="text" class="form-control float-field" name="interest_rate" value="{{ old('interest_rate', optional($loanproduct)->interest_rate) }}" required>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label class="control-label">{{ _lang('Interest Type') }}</label>
                <select class="form-control auto-select" data-selected="{{ old('interest_type', optional($loanproduct)->interest_type ?? 'flat_rate') }}" name="interest_type" required>
                    <option value="flat_rate">{{ _lang('Flat Rate') }}</option>
                    <option value="fixed_rate">{{ _lang('Fixed Rate') }}</option>
                    <option value="mortgage">{{ _lang('Mortgage amortization') }}</option>
                    <option value="reducing_amount">{{ _lang('Reducing Amount') }}</option>
                    <option value="one_time">{{ _lang('One-time payment') }}</option>
                </select>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label class="control-label">{{ _lang('Max Term') }}</label>
                <input type="number" class="form-control" name="term" value="{{ old('term', optional($loanproduct)->term) }}" required>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label class="control-label">{{ _lang('Term Period') }}</label>
                <select class="form-control select2 auto-select" data-selected="{{ old('term_period', optional($loanproduct)->term_period ?? '+1 month') }}" name="term_period" required>
                    <option value="">{{ _lang('Select One') }}</option>
                    <option value="+1 day">{{ _lang('Daily') }}</option>
                    <option value="+3 day">{{ _lang('Every 3 days') }}</option>
                    <option value="+5 day">{{ _lang('Every 5 days') }}</option>
                    <option value="+7 day">{{ _lang('Weekly') }}</option>
                    <option value="+10 day">{{ _lang('Every 10 days') }}</option>
                    <option value="+15 day">{{ _lang('Every 15 days') }}</option>
                    <option value="+21 day">{{ _lang('Every 21 days') }}</option>
                    <option value="+1 month">{{ _lang('Monthly') }}</option>
                    <option value="+2 month">{{ _lang('Every 2 months') }}</option>
                    <option value="+3 month">{{ _lang('Quarterly (Every 3 months)') }}</option>
                    <option value="+4 month">{{ _lang('Every 4 months') }}</option>
                    <option value="+6 month">{{ _lang('Biannually (Every 6 months)') }}</option>
                    <option value="+9 month">{{ _lang('Every 9 months') }}</option>
                    <option value="+1 year">{{ _lang('Yearly') }}</option>
                    <option value="+2 year">{{ _lang('Every 2 years') }}</option>
                    <option value="+3 year">{{ _lang('Every 3 years') }}</option>
                    <option value="+5 year">{{ _lang('Every 5 years') }}</option>
                </select>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label class="control-label">{{ _lang('Late Payment Penalties') }} / {{ _lang('Each Day') }}</label>
                <div class="input-group">
                    <input type="text" class="form-control float-field" name="late_payment_penalties" value="{{ old('late_payment_penalties', optional($loanproduct)->late_payment_penalties) }}" required>
                    <div class="input-group-append">
                        <span class="input-group-text">%</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label class="control-label">{{ _lang('Status') }}</label>
                <select class="form-control auto-select" data-selected="{{ old('status', optional($loanproduct)->status ?? 1) }}" name="status" required>
                    <option value="">{{ _lang('Select One') }}</option>
                    <option value="1">{{ _lang('Active') }}</option>
                    <option value="0">{{ _lang('Deactivate') }}</option>
                </select>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label class="control-label">{{ _lang('Loan Application Fee') }}</label>
                <input type="text" class="form-control float-field" name="loan_application_fee" value="{{ old('loan_application_fee', optional($loanproduct)->loan_application_fee ?? 0) }}" required>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label class="control-label">{{ _lang('Loan Application Fee Type') }}</label>
                <select class="form-control auto-select" data-selected="{{ old('loan_application_fee_type', optional($loanproduct)->loan_application_fee_type ?? 0) }}" name="loan_application_fee_type" required>
                    <option value="0">{{ _lang('Fixed') }}</option>
                    <option value="1">{{ _lang('Percentage') }}</option>
                </select>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label class="control-label">{{ _lang('Loan Processing Fee') }}</label>
                <input type="text" class="form-control float-field" name="loan_processing_fee" value="{{ old('loan_processing_fee', optional($loanproduct)->loan_processing_fee ?? 0) }}" required>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label class="control-label">{{ _lang('Loan Processing Fee Type') }}</label>
                <select class="form-control auto-select" data-selected="{{ old('loan_processing_fee_type', optional($loanproduct)->loan_processing_fee_type ?? 0) }}" name="loan_processing_fee_type" required>
                    <option value="0">{{ _lang('Fixed') }}</option>
                    <option value="1">{{ _lang('Percentage') }}</option>
                </select>
            </div>
        </div>

        <div class="col-md-12">
            <div class="form-group">
                <label class="control-label">{{ _lang('Description') }}</label>
                <textarea class="form-control" name="description">{{ old('description', optional($loanproduct)->description) }}</textarea>
            </div>
        </div>

        <div class="col-md-12 mt-2">
            <div class="form-group mb-0">
                <button type="submit" class="btn btn-primary"><i class="ti-check-box"></i>&nbsp;{{ $submitLabel }}</button>
            </div>
        </div>
    </div>
</form>
