@php
    $tabId = 'member-modal-' . $member->id;
    $customFieldsData = json_decode($member->custom_fields, true) ?: [];
    $detailRows = [
        _lang('First Name') => $member->first_name,
        _lang('Last Name') => $member->last_name,
        _lang('Business Name') => $member->business_name,
        _lang('Member No') => $member->member_no,
        _lang('Branch') => optional($member->branch)->name,
        _lang('Email') => $member->email,
        _lang('Mobile') => trim(($member->country_code ?? '') . ' ' . ($member->mobile ?? '')),
        _lang('Gender') => $member->gender ? ucwords($member->gender) : null,
        _lang('City') => $member->city,
        _lang('State') => $member->state,
        _lang('Zip') => $member->zip,
        _lang('Address') => $member->address,
        _lang('Credit Source') => $member->credit_source,
    ];
@endphp

<style>
    .member-detail-modal { color: var(--cavic-text, #2e3338); }
    .member-detail-modal .member-modal-header { display: flex; align-items: center; justify-content: space-between; gap: 1rem; margin-bottom: .9rem; }
    .member-detail-modal .member-modal-person { display: flex; align-items: center; gap: .75rem; min-width: 0; }
    .member-detail-modal .member-modal-avatar { width: 48px; height: 48px; border-radius: 14px; object-fit: cover; border: 1px solid var(--cavic-border, #e7e9e4); }
    .member-detail-modal .member-modal-title { font-size: 1rem; font-weight: 800; margin: 0; }
    .member-detail-modal .member-modal-subtitle { color: var(--cavic-text-soft, #6f787f); font-size: .78rem; }
    .member-detail-modal .member-modal-tabs { border-bottom: 1px solid var(--cavic-border, #e7e9e4); gap: .25rem; margin-bottom: .85rem; overflow-x: auto; flex-wrap: nowrap; }
    .member-detail-modal .member-modal-tabs .nav-link { border: 0; border-bottom: 2px solid transparent; color: var(--cavic-text-soft, #6f787f); font-size: .78rem; font-weight: 800; padding: .45rem .65rem; white-space: nowrap; }
    .member-detail-modal .member-modal-tabs .nav-link.active { color: var(--cavic-primary-dark, #32555a); border-bottom-color: var(--cavic-primary, #3f686d); background: transparent; }
    .member-detail-modal .member-compact-card { border: 1px solid var(--cavic-border, #e7e9e4); border-radius: 12px; background: #fff; overflow: hidden; }
    .member-detail-modal .member-compact-table { margin-bottom: 0; font-size: .79rem; }
    .member-detail-modal .member-compact-table th,
    .member-detail-modal .member-compact-table td { padding: .42rem .55rem; vertical-align: middle; }
    .member-detail-modal .member-compact-table th { color: var(--cavic-text-soft, #6f787f); font-weight: 800; background: var(--cavic-surface-muted, #fafaf8); white-space: nowrap; }
    .member-detail-modal .member-compact-table td:first-child { color: var(--cavic-text-soft, #6f787f); font-weight: 800; width: 34%; }
    .member-detail-modal .member-tab-pane-scroll { max-height: 58vh; overflow: auto; padding-right: .15rem; }
    .member-detail-modal .member-modal-actions { display: flex; justify-content: flex-end; flex-wrap: wrap; gap: .45rem; margin-top: .85rem; padding-top: .75rem; border-top: 1px solid var(--cavic-border, #e7e9e4); }
    .member-detail-modal .form-group { margin-bottom: .7rem; }
    .member-detail-modal textarea.form-control { min-height: 92px; }
</style>

<div class="member-detail-modal">
    <div class="member-modal-header">
        <div class="member-modal-person">
            <img src="{{ profile_picture($member->photo) }}" class="member-modal-avatar" alt="{{ $member->name }}">
            <div>
                <h5 class="member-modal-title">{{ $member->name }}</h5>
                <div class="member-modal-subtitle">{{ _lang('Member No') }}: {{ $member->member_no ?: _lang('Pending') }}</div>
            </div>
        </div>
        <span class="workspace-status-chip {{ (int) $member->status === 0 ? 'pending' : 'active' }}">
            {{ (int) $member->status === 0 ? _lang('Pending Approval') : _lang('Active') }}
        </span>
    </div>

    <ul class="nav nav-tabs member-modal-tabs" role="tablist">
        <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#{{ $tabId }}-details">{{ _lang('Details') }}</a></li>
        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#{{ $tabId }}-accounts">{{ _lang('Accounts') }}</a></li>
        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#{{ $tabId }}-transactions">{{ _lang('Transactions') }}</a></li>
        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#{{ $tabId }}-loans">{{ _lang('Loans') }}</a></li>
        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#{{ $tabId }}-documents">{{ _lang('Documents') }}</a></li>
        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#{{ $tabId }}-email">{{ _lang('Email') }}</a></li>
        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#{{ $tabId }}-sms">{{ _lang('SMS') }}</a></li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane fade show active member-tab-pane-scroll" id="{{ $tabId }}-details">
            <div class="member-compact-card">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped member-compact-table">
                        <tbody>
                            @foreach($detailRows as $label => $value)
                                <tr><td>{{ $label }}</td><td>{{ filled($value) ? $value : _lang('N/A') }}</td></tr>
                            @endforeach
                            @if(! $customFields->isEmpty())
                                @foreach($customFields as $customField)
                                    @php
                                        $fieldKey = str_replace(' ', '_', $customField->field_name);
                                        $fieldValue = $customFieldsData[$fieldKey]['field_value'] ?? null;
                                    @endphp
                                    <tr>
                                        <td>{{ $customField->field_name }}</td>
                                        <td>
                                            @if($customField->field_type == 'file')
                                                @if($fieldValue)
                                                    <a href="{{ asset('public/uploads/media/'.$fieldValue) }}" target="_blank" class="btn btn-xs btn-outline-primary"><i class="fas fa-download mr-1"></i>{{ _lang('Download') }}</a>
                                                @else
                                                    {{ _lang('N/A') }}
                                                @endif
                                            @else
                                                {{ filled($fieldValue) ? $fieldValue : _lang('N/A') }}
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="tab-pane fade member-tab-pane-scroll" id="{{ $tabId }}-accounts">
            <div class="member-compact-card">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped member-compact-table">
                        <thead>
                            <tr>
                                <th>{{ _lang('Account Number') }}</th>
                                <th>{{ _lang('Type') }}</th>
                                <th>{{ _lang('Currency') }}</th>
                                <th class="text-right">{{ _lang('Balance') }}</th>
                                <th class="text-right">{{ _lang('Guarantee') }}</th>
                                <th class="text-right">{{ _lang('Current') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($accounts as $account)
                                <tr>
                                    <td>{{ $account->account_number }}</td>
                                    <td>{{ optional($account->savings_type)->name }}</td>
                                    <td>{{ optional(optional($account->savings_type)->currency)->name }}</td>
                                    <td class="text-right">{{ decimalPlace($account->balance, currency(optional(optional($account->savings_type)->currency)->name)) }}</td>
                                    <td class="text-right">{{ decimalPlace($account->blocked_amount, currency(optional(optional($account->savings_type)->currency)->name)) }}</td>
                                    <td class="text-right">{{ decimalPlace($account->balance - $account->blocked_amount, currency(optional(optional($account->savings_type)->currency)->name)) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted">{{ _lang('No accounts found') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="tab-pane fade member-tab-pane-scroll" id="{{ $tabId }}-transactions">
            <div class="member-compact-card">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped member-compact-table">
                        <thead>
                            <tr>
                                <th>{{ _lang('Date') }}</th>
                                <th>{{ _lang('Account') }}</th>
                                <th class="text-right">{{ _lang('Amount') }}</th>
                                <th>{{ _lang('Dr/Cr') }}</th>
                                <th>{{ _lang('Type') }}</th>
                                <th>{{ _lang('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $transaction)
                                @php
                                    $currencyName = optional(optional(optional($transaction->account)->savings_type)->currency)->name;
                                    $symbol = $transaction->dr_cr == 'dr' ? '-' : '+';
                                    $amountClass = $transaction->dr_cr == 'dr' ? 'text-danger' : 'text-success';
                                @endphp
                                <tr>
                                    <td>{{ $transaction->trans_date }}</td>
                                    <td>{{ optional($transaction->account)->account_number ?: _lang('N/A') }}</td>
                                    <td class="text-right {{ $amountClass }}">{{ $symbol }} {{ decimalPlace($transaction->amount, currency_symbol($currencyName)) }}</td>
                                    <td>{{ strtoupper($transaction->dr_cr) }}</td>
                                    <td>{{ str_replace('_', ' ', $transaction->type) }}</td>
                                    <td>{!! xss_clean(transaction_status($transaction->status) ?? _lang('N/A')) !!}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted">{{ _lang('No transactions found') }}</td></tr>
                            @endforelse
                            @if(! empty($hasMoreTransactions))
                                <tr>
                                    <td colspan="6" class="text-center text-muted">
                                        {{ _lang('Showing latest 25 transactions. Open the full member record for complete history.') }}
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="tab-pane fade member-tab-pane-scroll" id="{{ $tabId }}-loans">
            <div class="member-compact-card">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped member-compact-table">
                        <thead>
                            <tr>
                                <th>{{ _lang('Loan ID') }}</th>
                                <th>{{ _lang('Product') }}</th>
                                <th class="text-right">{{ _lang('Applied') }}</th>
                                <th class="text-right">{{ _lang('Paid') }}</th>
                                <th class="text-right">{{ _lang('Due') }}</th>
                                <th>{{ _lang('Release') }}</th>
                                <th>{{ _lang('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($member->loans as $loan)
                                <tr>
                                    <td><a href="{{ route('loans.show', $loan->id) }}" class="ajax-modal" data-title="{{ _lang('Loan Review Summary') }}">{{ $loan->loan_id }}</a></td>
                                    <td>{{ optional($loan->loan_product)->name }}</td>
                                    <td class="text-right">{{ decimalPlace($loan->applied_amount, currency(optional($loan->currency)->name)) }}</td>
                                    <td class="text-right">{{ decimalPlace($loan->total_paid, currency(optional($loan->currency)->name)) }}</td>
                                    <td class="text-right">{{ decimalPlace($loan->applied_amount - $loan->total_paid, currency(optional($loan->currency)->name)) }}</td>
                                    <td>{{ $loan->release_date ?: _lang('N/A') }}</td>
                                    <td>
                                        @if($loan->status == 0)
                                            {!! xss_clean(show_status(_lang('Pending'), 'warning')) !!}
                                        @elseif($loan->status == 1)
                                            {!! xss_clean(show_status(_lang('Approved'), 'success')) !!}
                                        @elseif($loan->status == 2)
                                            {!! xss_clean(show_status(_lang('Completed'), 'info')) !!}
                                        @elseif($loan->status == 3)
                                            {!! xss_clean(show_status(_lang('Cancelled'), 'danger')) !!}
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center text-muted">{{ _lang('No loans found') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="tab-pane fade member-tab-pane-scroll" id="{{ $tabId }}-documents">
            <div class="d-flex justify-content-end mb-2">
                <a class="btn btn-primary btn-xs ajax-modal-2" data-title="{{ _lang('Add New Document') }}" href="{{ route('member_documents.create', $member->id) }}?context=quick_view"><i class="ti-plus mr-1"></i>{{ _lang('Add New') }}</a>
            </div>
            <div class="member-compact-card">
                <div class="table-responsive">
                    <table id="member_documents_modal_table" class="table table-bordered table-striped member-compact-table">
                        <thead>
                            <tr>
                                <th>{{ _lang('Document Name') }}</th>
                                <th>{{ _lang('File') }}</th>
                                <th>{{ _lang('Submitted') }}</th>
                                <th class="text-center">{{ _lang('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($member->documents as $document)
                                @include('backend.admin.member_documents.modal.row', ['memberdocument' => $document, 'context' => 'quick_view'])
                            @empty
                                <tr><td colspan="4" class="text-center text-muted">{{ _lang('No documents found') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="tab-pane fade member-tab-pane-scroll" id="{{ $tabId }}-email">
            <form method="post" class="ajax-submit" data-reload="false" autocomplete="off" action="{{ route('members.send_email') }}" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label class="control-label">{{ _lang('User Email') }}</label>
                    <input type="email" class="form-control" name="user_email" value="{{ $member->email }}" required readonly>
                </div>
                <div class="form-group">
                    <label class="control-label">{{ _lang('Subject') }}</label>
                    <input type="text" class="form-control" name="subject" required>
                </div>
                <div class="form-group">
                    <label class="control-label">{{ _lang('Message') }}</label>
                    <textarea class="form-control" name="message" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary btn-sm"><i class="ti-check-box mr-1"></i>{{ _lang('Send') }}</button>
            </form>
        </div>

        <div class="tab-pane fade member-tab-pane-scroll" id="{{ $tabId }}-sms">
            <form method="post" class="ajax-submit" data-reload="false" autocomplete="off" action="{{ route('members.send_sms') }}" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label class="control-label">{{ _lang('User Mobile') }}</label>
                    <input type="text" class="form-control" name="phone" value="{{ $member->country_code.$member->mobile }}" required readonly>
                </div>
                <div class="form-group">
                    <label class="control-label">{{ _lang('Message') }}</label>
                    <textarea class="form-control" name="message" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary btn-sm"><i class="ti-check-box mr-1"></i>{{ _lang('Send') }}</button>
            </form>
        </div>
    </div>

    <div class="member-modal-actions">
        @if((int) $member->status === 0)
            <a href="{{ route('members.accept_request', $member->id) }}" class="btn btn-success btn-sm ajax-modal-2" data-title="{{ _lang('Approve Member Request') }}">
                <i class="fas fa-check-circle mr-1"></i>{{ _lang('Approve') }}
            </a>
            <a href="{{ route('members.reject_request', $member->id) }}" class="btn btn-danger btn-sm ajax-action" data-confirm="{{ _lang('Reject this member request?') }}">
                <i class="fas fa-times-circle mr-1"></i>{{ _lang('Reject') }}
            </a>
        @endif
        <a href="{{ route('members.edit', $member->id) }}" class="btn btn-outline-primary btn-sm ajax-modal-2" data-title="{{ _lang('Update Member') }}" data-fullscreen="true">
            <i class="ti-pencil-alt mr-1"></i>{{ _lang('Edit Member') }}
        </a>
    </div>
</div>
