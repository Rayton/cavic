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

@if((int) $withdrawRequest->status === 0)
<div class="d-flex flex-wrap justify-content-end mt-4">
    <a href="{{ route('withdraw_requests.approve', $withdrawRequest->id) }}" class="btn btn-success btn-sm mr-2 mb-2 ajax-action" data-confirm="{{ _lang('Approve this withdraw request?') }}">
        <i class="fas fa-check-circle mr-1"></i>{{ _lang('Approve') }}
    </a>
    <a href="{{ route('withdraw_requests.reject', $withdrawRequest->id) }}" class="btn btn-danger btn-sm mb-2 ajax-action" data-confirm="{{ _lang('Reject this withdraw request?') }}">
        <i class="fas fa-times-circle mr-1"></i>{{ _lang('Reject') }}
    </a>
</div>
@endif
