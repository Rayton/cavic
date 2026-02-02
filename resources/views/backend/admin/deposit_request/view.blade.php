@extends('layouts.app')

@section('content')
<div class="row">
	<div class="col-lg-8 offset-lg-2">
		<div class="card">
		    <div class="card-header d-flex align-items-center">
				<span class="panel-title">{{ _lang('Deposit Request Details') }}</span>
			</div>
			<div class="card-body">
                <table class="table table-bordered">
                    <tr><td>{{ _lang('Member') }}</td><td>{{ $depositrequest->member->first_name.' '.$depositrequest->member->last_name }}</td></tr>
                    <tr><td>{{ _lang('Transaction ID') }}</td><td>{{ $depositrequest->user_transaction_id ?? '-' }}</td></tr>
                    <tr><td>{{ _lang('Reference') }}</td><td>{{ $depositrequest->user_reference ?? '-' }}</td></tr>
                    <tr><td>{{ _lang('Deposit Method') }}</td><td>{{ $depositrequest->method->name }}</td></tr>
                    <tr><td>{{ _lang('Description') }}</td><td>{{ $depositrequest->description ?? '-' }}</td></tr>
                    @if($depositrequest->requirements)
                        @foreach($depositrequest->requirements as $key => $value)
                        <tr>
                            <td><b>{{ ucwords(str_replace('_',' ',$key)) }}</b></td>
                            <td>{{ $value }}</td>
                        </tr>
                        @endforeach
                    @endif
                    <tr>
                        <td>{{ _lang('Attachment') }}</td>
                        <td>
                            {!! $depositrequest->attachment == "" ? '-' : '<a href="'. asset('public/uploads/media/'.$depositrequest->attachment) .'" target="_blank">'._lang('View Attachment').'</a>' !!}
                        </td>
                    </tr>
                    <tr><td>{{ _lang('Status') }}</td><td>{!! xss_clean(transaction_status($depositrequest->status)) !!}</td></tr>
                </table>
                <h5 class="mt-4">{{ _lang('Deposit rows') }} ({{ _lang('this request') }})</h5>
                <table class="table table-bordered">
                    <tr><td>{{ _lang('Account Number') }}</td><td>{{ $depositrequest->account->account_number }} ({{ $depositrequest->account->savings_type->name ?? '' }})</td></tr>
                    <tr><td>{{ _lang('Deposit Amount via').' '.$depositrequest->method->name }} ({{ _lang('Including Charge') }})</td><td>{{ decimalPlace($depositrequest->converted_amount, currency($depositrequest->method->currency->name)) }}</td></tr>
                    <tr><td>{{ _lang('Deposit to Customer Amount') }}</td><td>{{ decimalPlace($depositrequest->amount, currency($depositrequest->account->savings_type->currency->name)) }}</td></tr>
                </table>
                @if($depositrequest->deposit_request_group_id && $depositrequest->group_requests->count() > 1)
                <h5 class="mt-4">{{ _lang('All deposits in this submission') }}</h5>
                <p class="text-muted small">{{ _lang('Same Transaction ID, Reference, Description and Attachment for all rows.') }}</p>
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>{{ _lang('Account') }}</th>
                            <th class="text-right">{{ _lang('Amount') }}</th>
                            <th class="text-right">{{ _lang('Converted') }}</th>
                            <th>{{ _lang('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($depositrequest->group_requests as $row)
                        <tr>
                            <td>{{ $row->account->account_number }} ({{ $row->account->savings_type->name ?? '' }})</td>
                            <td class="text-right">{{ decimalPlace($row->amount, currency($row->account->savings_type->currency->name)) }}</td>
                            <td class="text-right">{{ decimalPlace($row->converted_amount, currency($row->method->currency->name)) }}</td>
                            <td>{!! xss_clean(transaction_status($row->status)) !!}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection