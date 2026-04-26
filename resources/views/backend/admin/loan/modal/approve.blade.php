<div class="action-center-modal-summary">
    <form method="post" class="ajax-submit" autocomplete="off" action="{{ route('loans.approve', $loan->id) }}">
        @csrf
        <div class="table-responsive mb-3">
            <table class="table table-sm table-bordered mb-0">
                <tr><td>{{ _lang('Loan ID') }}</td><td>{{ $loan->loan_id }}</td></tr>
                <tr><td>{{ _lang('Loan Type') }}</td><td>{{ optional($loan->loan_product)->name }}</td></tr>
                <tr><td>{{ _lang('Borrower') }}</td><td>{{ optional($loan->borrower)->name }}</td></tr>
                <tr><td>{{ _lang('Member No') }}</td><td>{{ optional($loan->borrower)->member_no }}</td></tr>
                <tr><td>{{ _lang('First Payment Date') }}</td><td>{{ $loan->first_payment_date }}</td></tr>
                <tr><td>{{ _lang('Release Date') }}</td><td>{{ $loan->release_date }}</td></tr>
                <tr><td>{{ _lang('Applied Amount') }}</td><td>{{ decimalPlace($loan->applied_amount, currency($loan->currency->name)) }}</td></tr>
                <tr><td>{{ _lang('Late Payment Penalties') }}</td><td>{{ $loan->late_payment_penalties }} %</td></tr>
            </table>
        </div>

        <div class="form-group">
            <label class="control-label">{{ _lang('Credit Account') }}</label>
            <select class="form-control auto-select" data-selected="{{ old('account_id', 'cash') }}" name="account_id" required>
                <option value="cash">{{ _lang('Cash Handover') }}</option>
                @foreach($accounts as $account)
                    <option value="{{ $account->id }}">{{ $account->account_number }} ({{ $account->savings_type->name.' - '.$account->savings_type->currency->name }})</option>
                @endforeach
            </select>
        </div>

        <div class="d-flex flex-wrap justify-content-end mt-4">
            <button type="submit" class="btn btn-success btn-sm mb-2">
                <i class="fas fa-check-circle mr-1"></i>{{ _lang('Confirm Approval') }}
            </button>
        </div>
    </form>
</div>
