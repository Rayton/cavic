<table class="table table-bordered mb-3">
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
            @if(!empty($depositrequest->attachment))
                <a href="{{ asset('public/uploads/media/'.$depositrequest->attachment) }}" target="_blank" class="btn btn-outline-primary btn-sm mr-1">{{ _lang('View Attachment') }}</a>
                <a href="{{ route('deposit_requests.download_attachment', $depositrequest->id) }}" class="btn btn-outline-secondary btn-sm"><i class="ti-download mr-1"></i>{{ _lang('Download Attachment') }}</a>
            @else
                -
            @endif
        </td>
    </tr>
    <tr><td>{{ _lang('Status') }}</td><td>{!! xss_clean(transaction_status($depositrequest->status)) !!}</td></tr>
</table>
<h5 class="mt-4">{{ _lang('Deposit rows') }} ({{ _lang('this request') }})</h5>
<table class="table table-bordered mb-3">
    <tr><td>{{ _lang('Account Number') }}</td><td>{{ $depositrequest->account->account_number }} ({{ $depositrequest->account->savings_type->name ?? '' }})</td></tr>
    <tr><td>{{ _lang('Deposit Amount via').' '.$depositrequest->method->name }} ({{ _lang('Including Charge') }})</td><td>{{ decimalPlace($depositrequest->converted_amount, $depositrequest->method->currency->name) }}</td></tr>
    <tr><td>{{ _lang('Deposit to Customer Amount') }}</td><td>{{ decimalPlace($depositrequest->amount, $depositrequest->account->savings_type->currency->name) }}</td></tr>
</table>
@if($depositrequest->deposit_request_group_id && $depositrequest->group_requests->count() > 1)
<h5 class="mt-4">{{ _lang('All deposits in this submission') }}</h5>
<p class="text-muted small">{{ _lang('Same Transaction ID, Reference, Description and Attachment for all rows.') }}</p>
<table class="table table-bordered table-sm mb-0">
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
            <td class="text-right">{{ decimalPlace($row->amount, $row->account->savings_type->currency->name) }}</td>
            <td class="text-right">{{ decimalPlace($row->converted_amount, $row->method->currency->name) }}</td>
            <td>{!! xss_clean(transaction_status($row->status)) !!}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

@if((int) $depositrequest->status === 0)
<div class="d-flex flex-wrap justify-content-end mt-4">
    @if($depositrequest->deposit_request_group_id && $depositrequest->group_requests->where('status', '!=', 2)->count() > 1)
        <a href="{{ route('deposit_requests.approve_group', $depositrequest->deposit_request_group_id) }}" class="btn btn-success btn-sm mr-2 mb-2 ajax-action" data-confirm="{{ _lang('Approve all pending deposits in this submission?') }}">
            <i class="fas fa-check-double mr-1"></i>{{ _lang('Approve Group') }}
        </a>
    @endif
    <a href="{{ route('deposit_requests.approve', $depositrequest->id) }}" class="btn btn-success btn-sm mr-2 mb-2 ajax-action" data-confirm="{{ _lang('Approve this deposit request?') }}">
        <i class="fas fa-check-circle mr-1"></i>{{ _lang('Approve') }}
    </a>
    <a href="{{ route('deposit_requests.reject', $depositrequest->id) }}" class="btn btn-danger btn-sm mb-2 ajax-action" data-confirm="{{ _lang('Reject this deposit request?') }}">
        <i class="fas fa-times-circle mr-1"></i>{{ _lang('Reject') }}
    </a>
</div>
@endif
