<div class="action-center-modal-summary">
    <div class="d-flex flex-wrap align-items-start justify-content-between mb-3">
        <div>
            <h5 class="mb-1">{{ $member->name }}</h5>
            <div class="text-muted small">{{ _lang('Member No') }}: {{ $member->member_no ?: _lang('Pending') }}</div>
        </div>
        <span class="workspace-status-chip {{ (int) $member->status === 0 ? 'pending' : 'active' }}">
            {{ (int) $member->status === 0 ? _lang('Pending Approval') : _lang('Active') }}
        </span>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped dashboard-table-compact mb-0">
            <tr><td>{{ _lang('Branch') }}</td><td>{{ optional($member->branch)->name ?? _lang('N/A') }}</td></tr>
            <tr><td>{{ _lang('Email') }}</td><td>{{ $member->email ?: _lang('N/A') }}</td></tr>
            <tr><td>{{ _lang('Mobile') }}</td><td>{{ trim(($member->country_code ?? '') . ' ' . ($member->mobile ?? '')) ?: _lang('N/A') }}</td></tr>
            <tr><td>{{ _lang('Business Name') }}</td><td>{{ $member->business_name ?: _lang('N/A') }}</td></tr>
            <tr><td>{{ _lang('Address') }}</td><td>{{ $member->address ?: _lang('N/A') }}</td></tr>
        </table>
    </div>

    <div class="d-flex flex-wrap justify-content-end mt-4">
        @if((int) $member->status === 0)
            <a href="{{ route('members.accept_request', $member->id) }}" class="btn btn-success btn-sm mr-2 mb-2 ajax-modal" data-title="{{ _lang('Approve Member Request') }}">
                <i class="fas fa-check-circle mr-1"></i>{{ _lang('Approve') }}
            </a>
            <a href="{{ route('members.reject_request', $member->id) }}" class="btn btn-danger btn-sm mr-2 mb-2 ajax-action" data-confirm="{{ _lang('Reject this member request?') }}">
                <i class="fas fa-times-circle mr-1"></i>{{ _lang('Reject') }}
            </a>
        @endif
        <a href="{{ route('members.show', $member->id) }}" class="btn btn-outline-primary btn-sm mb-2">
            <i class="ti-external-link mr-1"></i>{{ _lang('Open Full Member Record') }}
        </a>
    </div>
</div>
