<table class="table table-bordered mb-0">
    <tr><td>{{ _lang('Member') }}</td><td>{{ $withdrawRequest->member->first_name.' '.$withdrawRequest->member->last_name }}</td></tr>
    <tr><td>{{ _lang('Account Number') }}</td><td>{{ $withdrawRequest->account->account_number }}</td></tr>
    <tr><td>{{ _lang('Withdraw Method') }}</td><td>{{ $withdrawRequest->method->name }}</td></tr>
    <tr><td>{{ _lang('Withdraw Amount via').' '.$withdrawRequest->method->name }} ({{ _lang('Including Charge') }})</td><td>{{ decimalPlace($withdrawRequest->converted_amount, $withdrawRequest->account->savings_type->currency->name) }}</td></tr>
    <tr><td>{{ _lang('Customer Will Receive') }}</td><td>{{ decimalPlace($withdrawRequest->transaction->amount, $withdrawRequest->account->savings_type->currency->name) }}</td></tr>
    <tr><td>{{ _lang('Description') }}</td><td>{{ $withdrawRequest->description }}</td></tr>
    @if($withdrawRequest->requirements)
        @foreach($withdrawRequest->requirements as $key => $value)
        <tr>
            <td><b>{{ ucwords(str_replace('_',' ',$key)) }}</b></td>
            <td>{{ $value }}</td>
        </tr>
        @endforeach
    @endif
    <tr>
        <td>{{ _lang('Attachment') }}</td>
        <td>
            {!! $withdrawRequest->attachment == "" ? '-' : '<a href="'. asset('public/uploads/media/'.$withdrawRequest->attachment) .'" target="_blank" class="btn btn-outline-primary btn-sm">'._lang('View Attachment').'</a>' !!}
        </td>
    </tr>
    <tr><td>{{ _lang('Status') }}</td><td>{!! xss_clean(transaction_status($withdrawRequest->status)) !!}</td></tr>
</table>
