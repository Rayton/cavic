@php
    $calculatorContext = $calculatorContext ?? 'modal';
    $calculatorTarget = $calculatorTarget ?? '';
@endphp
<div class="loan-calculator-modal-content">
    <div class="alert alert-danger d-none"></div>
    <form
        method="post"
        class="loan-calculator-ajax-form validate"
        autocomplete="off"
        action="{{ route('loans.calculate') }}"
        @if($calculatorTarget) data-target="{{ $calculatorTarget }}" @endif
    >
        @csrf
        <input type="hidden" name="calculator_context" value="{{ $calculatorContext }}">
        <input type="hidden" name="calculator_target" value="{{ $calculatorTarget }}">
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label class="control-label">{{ _lang('Apply Amount') }}</label>
                    <input type="text" class="form-control float-field" name="apply_amount" value="{{ old('apply_amount',$apply_amount) }}" required>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    <label class="control-label">{{ _lang('Interest Rate Per Year') }}</label>
                    <div class="input-group">
                        <input type="text" class="form-control float-field" name="interest_rate" value="{{ old('interest_rate', $interest_rate) }}" required>
                        <div class="input-group-append"><span class="input-group-text">%</span></div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    <label class="control-label">{{ _lang('Interest Type') }}</label>
                    <select class="form-control auto-select" data-loan-calculator-interest-type="1" data-selected="{{ old('interest_type',$interest_type) }}" name="interest_type" required>
                        <option value="">{{ _lang('Select One') }}</option>
                        <option value="flat_rate">{{ _lang('Flat Rate') }}</option>
                        <option value="fixed_rate">{{ _lang('Fixed Rate') }}</option>
                        <option value="mortgage">{{ _lang('Mortgage amortization') }}</option>
                        <option value="reducing_amount">{{ _lang('Reducing Amount') }}</option>
                        <option value="one_time">{{ _lang('One-time payment') }}</option>
                    </select>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    <label class="control-label">{{ _lang('Term') }}</label>
                    <input type="number" class="form-control" data-loan-calculator-term="1" name="term" value="{{ old('term',$term) }}" required>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    <label class="control-label">{{ _lang('Term Period') }}</label>
                    <select class="form-control auto-select" data-loan-calculator-term-period="1" data-selected="{{ old('term_period', $term_period) }}" name="term_period" required>
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

            <div class="col-md-3">
                <div class="form-group">
                    <label class="control-label">{{ _lang('First Payment date') }}</label>
                    <input type="text" class="form-control datepicker" name="first_payment_date" value="{{ old('first_payment_date', $first_payment_date) }}" required>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    <label class="control-label">{{ _lang('Late Payment Penalties') }}</label>
                    <div class="input-group">
                        <input type="text" class="form-control float-field" name="late_payment_penalties" value="{{ old('late_payment_penalties',$late_payment_penalties) }}" required>
                        <div class="input-group-append"><span class="input-group-text">%</span></div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-block" style="margin-top: 27px;">{{ _lang('Calculate') }}</button>
                </div>
            </div>
        </div>
    </form>

    @if(isset($table_data))
        <h5 class="mt-4 text-center"><b>{{ _lang('Payable Amount') }}: {{ decimalPlace($payable_amount) }}</b></h5>

        <div class="table-responsive mt-4">
            <table class="table table-bordered workspace-mini-table">
                <thead>
                    <tr>
                        <th>{{ _lang('Date') }}</th>
                        <th class="text-right">{{ _lang('Principal Amount') }}</th>
                        <th class="text-right">{{ _lang('Interest') }}</th>
                        <th class="text-right">{{ _lang('Penalty') }}</th>
                        <th class="text-right">{{ _lang('Amount to Pay') }}</th>
                        <th class="text-right">{{ _lang('Balance') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($table_data as $td)
                    <tr>
                        <td>{{ date('d/m/Y',strtotime($td['date'])) }}</td>
                        <td class="text-right">{{ decimalPlace($td['principal_amount']) }}</td>
                        <td class="text-right">{{ decimalPlace($td['interest']) }}</td>
                        <td class="text-right">{{ decimalPlace($td['penalty']) }}/ {{ _lang('Day') }}</td>
                        <td class="text-right">{{ decimalPlace($td['amount_to_pay']) }}</td>
                        <td class="text-right">{{ decimalPlace($td['balance']) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
