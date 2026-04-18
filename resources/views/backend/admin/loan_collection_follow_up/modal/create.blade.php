<div class="px-2">
    <div class="table-responsive mb-4">
        <table class="table table-sm table-bordered mb-0">
            <tr>
                <td>{{ _lang('Loan ID') }}</td>
                <td>{{ $repayment->loan->loan_id }}</td>
            </tr>
            <tr>
                <td>{{ _lang('Borrower') }}</td>
                <td>{{ $repayment->loan->borrower->name }}</td>
            </tr>
            <tr>
                <td>{{ _lang('Branch') }}</td>
                <td>{{ $repayment->loan->borrower->branch->name }}</td>
            </tr>
            <tr>
                <td>{{ _lang('Repayment Date') }}</td>
                <td>{{ $repayment->repayment_date }}</td>
            </tr>
            <tr>
                <td>{{ _lang('Amount') }}</td>
                <td>{{ decimalPlace($repayment->amount_to_pay, optional($repayment->loan->currency)->name) }}</td>
            </tr>
        </table>
    </div>

    <form method="post" class="ajax-submit" autocomplete="off" action="{{ route('loan_collection_follow_ups.store') }}">
        @csrf
        <input type="hidden" name="loan_repayment_id" value="{{ $repayment->id }}">

        <div class="row">
            <div class="col-lg-6">
                <div class="form-group">
                    <label class="control-label">{{ _lang('Outcome') }}</label>
                    <select class="form-control auto-select" data-selected="{{ old('outcome', \App\Models\LoanCollectionFollowUp::OUTCOME_REACHED) }}" name="outcome" id="collection_follow_up_outcome" required>
                        <option value="{{ \App\Models\LoanCollectionFollowUp::OUTCOME_REACHED }}">{{ _lang('Reached') }}</option>
                        <option value="{{ \App\Models\LoanCollectionFollowUp::OUTCOME_UNREACHABLE }}">{{ _lang('Unreachable') }}</option>
                        <option value="{{ \App\Models\LoanCollectionFollowUp::OUTCOME_PROMISED_TO_PAY }}">{{ _lang('Promised to Pay') }}</option>
                        <option value="{{ \App\Models\LoanCollectionFollowUp::OUTCOME_ESCALATED }}">{{ _lang('Escalated') }}</option>
                        <option value="{{ \App\Models\LoanCollectionFollowUp::OUTCOME_REMINDER_SENT }}">{{ _lang('Reminder Sent') }}</option>
                        <option value="{{ \App\Models\LoanCollectionFollowUp::OUTCOME_RESOLVED }}">{{ _lang('Resolved') }}</option>
                    </select>
                </div>
            </div>
            <div class="col-lg-6 d-none" id="promised_payment_date_group">
                <div class="form-group">
                    <label class="control-label">{{ _lang('Promised Payment Date') }}</label>
                    <input type="text" class="form-control datepicker" name="promised_payment_date" value="{{ old('promised_payment_date') }}">
                </div>
            </div>
            <div class="col-lg-6">
                <div class="form-group">
                    <label class="control-label">{{ _lang('Next Action Date') }}</label>
                    <input type="text" class="form-control datepicker" name="next_action_date" value="{{ old('next_action_date') }}">
                </div>
            </div>
            <div class="col-lg-12">
                <div class="form-group">
                    <label class="control-label">{{ _lang('Follow-up Note') }}</label>
                    <textarea class="form-control" name="note" rows="4" required>{{ old('note') }}</textarea>
                </div>
            </div>
            <div class="col-lg-12 mt-2">
                <div class="form-group mb-0">
                    <button type="submit" class="btn btn-primary"><i class="ti-check-box mr-2"></i>{{ _lang('Log Follow-up') }}</button>
                </div>
            </div>
        </div>
    </form>

    <hr>

    <h6 class="mb-3">{{ _lang('Recent Follow-up History') }}</h6>
    <div class="table-responsive">
        <table class="table table-sm table-bordered mb-0">
            <thead>
                <tr>
                    <th>{{ _lang('Date') }}</th>
                    <th>{{ _lang('Outcome') }}</th>
                    <th>{{ _lang('Note') }}</th>
                    <th>{{ _lang('Next Step') }}</th>
                    <th>{{ _lang('By') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentFollowUps as $followUp)
                    <tr>
                        <td>{{ $followUp->created_at }}</td>
                        <td><span class="workspace-status-chip {{ $followUp->outcome_theme }}">{{ $followUp->outcome_text }}</span></td>
                        <td>{{ \Illuminate\Support\Str::limit($followUp->note, 90) }}</td>
                        <td>
                            @if($followUp->promised_payment_date)
                                {{ _lang('Promised') }}: {{ \Carbon\Carbon::parse($followUp->promised_payment_date)->format(get_date_format()) }}
                            @elseif($followUp->next_action_date)
                                {{ \Carbon\Carbon::parse($followUp->next_action_date)->format(get_date_format()) }}
                            @else
                                {{ _lang('N/A') }}
                            @endif
                        </td>
                        <td>{{ $followUp->createdBy->name }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted">{{ _lang('No follow-up history logged yet') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
    $(document).on('change', '#collection_follow_up_outcome', function () {
        if ($(this).val() == '{{ \App\Models\LoanCollectionFollowUp::OUTCOME_PROMISED_TO_PAY }}') {
            $('#promised_payment_date_group').removeClass('d-none');
        } else {
            $('#promised_payment_date_group').addClass('d-none');
            $('#promised_payment_date_group input').val('');
        }
    }).trigger('change');
</script>
