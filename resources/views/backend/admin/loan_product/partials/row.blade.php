@php
    $termPeriods = [
        '+1 day' => _lang('Day'),
        '+3 day' => _lang('Every 3 days'),
        '+5 day' => _lang('Every 5 days'),
        '+7 day' => _lang('Week'),
        '+10 day' => _lang('Every 10 days'),
        '+15 day' => _lang('Every 15 days'),
        '+21 day' => _lang('Every 21 days'),
        '+1 month' => _lang('Month'),
        '+2 month' => _lang('Every 2 months'),
        '+3 month' => _lang('Quarterly'),
        '+4 month' => _lang('Every 4 months'),
        '+6 month' => _lang('Biannually'),
        '+9 month' => _lang('Every 9 months'),
        '+1 year' => _lang('Year'),
        '+2 year' => _lang('Every 2 years'),
        '+3 year' => _lang('Every 3 years'),
        '+5 year' => _lang('Every 5 years'),
    ];
@endphp
<tr data-id="row_{{ $loanproduct->id }}">
    <td class="name">
        <strong>{{ $loanproduct->name }}</strong>
        @if($loanproduct->loan_id_prefix)
            <div class="small text-muted">{{ _lang('Prefix') }}: {{ $loanproduct->loan_id_prefix }}</div>
        @endif
    </td>
    <td class="minimum_amount">{{ decimalPlace($loanproduct->minimum_amount) }} - {{ decimalPlace($loanproduct->maximum_amount) }}</td>
    <td class="interest_rate">{{ $loanproduct->interest_rate }}%</td>
    <td class="interest_type">{{ ucwords(str_replace('_', ' ', $loanproduct->interest_type)) }}</td>
    <td class="term">{{ $loanproduct->term }} {{ $termPeriods[$loanproduct->term_period] ?? $loanproduct->term_period }}</td>
    <td class="status">{!! xss_clean(status($loanproduct->status)) !!}</td>
    <td class="text-center">
        @include('backend.admin.partials.table-actions', [
            'items' => [
                ['label' => _lang('Edit'), 'url' => route('loan_products.edit', $loanproduct->id), 'icon' => 'ti-pencil-alt', 'class' => 'ajax-modal', 'data_title' => _lang('Update Loan Product'), 'data_size' => 'lg'],
                ['label' => _lang('Delete'), 'url' => route('loan_products.destroy', $loanproduct->id), 'icon' => 'ti-trash', 'method' => 'delete', 'class' => 'btn-remove', 'form_class' => 'ajax-remove'],
            ],
        ])
    </td>
</tr>
