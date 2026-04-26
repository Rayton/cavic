<div class="action-center-modal-summary">
    <div class="d-flex flex-wrap align-items-start justify-content-between mb-3">
        <div>
            <h5 class="mb-1">{{ $loan->loan_id }}</h5>
            <div class="text-muted small">{{ optional($loan->borrower)->name }} - {{ optional($loan->loan_product)->name }}</div>
        </div>
        @if($loan->status == 0)
            <span class="workspace-status-chip pending">{{ _lang('Pending') }}</span>
        @elseif($loan->status == 1)
            <span class="workspace-status-chip active">{{ _lang('Approved') }}</span>
        @elseif($loan->status == 2)
            <span class="workspace-status-chip info">{{ _lang('Completed') }}</span>
        @else
            <span class="workspace-status-chip critical">{{ _lang('Cancelled') }}</span>
        @endif
    </div>

    <div class="table-responsive mb-3">
        <table class="table table-sm table-bordered mb-0">
            <tr><td>{{ _lang('Borrower') }}</td><td>{{ optional($loan->borrower)->name }}</td></tr>
            <tr><td>{{ _lang('Member No') }}</td><td>{{ optional($loan->borrower)->member_no ?: _lang('N/A') }}</td></tr>
            <tr><td>{{ _lang('Applied Amount') }}</td><td>{{ decimalPlace($loan->applied_amount, optional($loan->currency)->name) }}</td></tr>
            <tr><td>{{ _lang('Release Date') }}</td><td>{{ $loan->release_date ?: _lang('Not set') }}</td></tr>
            <tr><td>{{ _lang('First Payment Date') }}</td><td>{{ $loan->first_payment_date ?: _lang('Not set') }}</td></tr>
            <tr><td>{{ _lang('Approval Progress') }}</td><td>{{ $loan->approvals->where('status', \App\Models\LoanApproval::STATUS_APPROVED)->count() }} / {{ max($loan->approvals->count(), 1) }}</td></tr>
        </table>
    </div>

    @if($repayments->isNotEmpty())
        <h6 class="mb-2">{{ _lang('Next Repayment') }}</h6>
        @php $nextRepayment = $repayments->where('status', 0)->first() ?: $repayments->first(); @endphp
        <div class="table-responsive">
            <table class="table table-sm table-bordered mb-0">
                <tr><td>{{ _lang('Repayment Date') }}</td><td>{{ $nextRepayment->repayment_date }}</td></tr>
                <tr><td>{{ _lang('Amount') }}</td><td>{{ decimalPlace($nextRepayment->amount_to_pay, optional($loan->currency)->name) }}</td></tr>
                <tr><td>{{ _lang('Status') }}</td><td>{!! xss_clean(transaction_status($nextRepayment->status)) !!}</td></tr>
            </table>
        </div>
    @endif

    <div class="d-flex flex-wrap justify-content-end mt-4">
        @if($loan->status == 0)
            <a href="{{ route('loans.approve', $loan->id) }}" class="btn btn-success btn-sm mr-2 mb-2 ajax-modal" data-title="{{ _lang('Confirm Loan Approval') }}">
                <i class="fas fa-check-circle mr-1"></i>{{ _lang('Approve') }}
            </a>
        @endif
        <a href="{{ route('loans.show', $loan->id) }}" class="btn btn-outline-primary btn-sm mb-2">
            <i class="ti-external-link mr-1"></i>{{ _lang('Open Full Loan Record') }}
        </a>
    </div>
</div>
