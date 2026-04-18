@php
    $collectionDateRange = $collectionDateRange ?? [];
    $filterId = $filterId ?? ('collection-date-range-' . uniqid());
    $fromDate = $collectionDateRange['from_date'] ?? request('from_date', now()->toDateString());
    $toDate = $collectionDateRange['to_date'] ?? request('to_date', $fromDate);
    $displayValue = $collectionDateRange['display_value'] ?? ($fromDate . ' - ' . $toDate);
@endphp

<div class="card workspace-page-header-card mb-4">
    <div class="card-body py-3">
        <form method="get" action="{{ url()->current() }}" class="d-flex flex-column flex-lg-row align-items-lg-end justify-content-between">
            <div class="mb-3 mb-lg-0 pr-lg-4">
                <div class="workspace-section-title mb-1">{{ _lang('Collections Analytics Date Range') }}</div>
                <div class="text-muted small">{{ _lang('Filter follow-up execution metrics, promise tracking, and productivity views by date range.') }}</div>
            </div>
            <div class="d-flex flex-column flex-md-row align-items-md-end">
                @foreach(request()->except(['from_date', 'to_date']) as $key => $value)
                    @if(! is_array($value))
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endif
                @endforeach
                <div class="form-group mb-2 mb-md-0 mr-md-2">
                    <label class="small text-muted d-block mb-1">{{ _lang('Date Range') }}</label>
                    <input type="text" id="{{ $filterId }}" class="form-control form-control-sm" value="{{ $displayValue }}" autocomplete="off" style="min-width: 240px;">
                    <input type="hidden" name="from_date" id="{{ $filterId }}-from" value="{{ $fromDate }}">
                    <input type="hidden" name="to_date" id="{{ $filterId }}-to" value="{{ $toDate }}">
                </div>
                <button type="submit" class="btn btn-outline-primary btn-sm mr-md-2 mb-2 mb-md-0">{{ _lang('Apply') }}</button>
                <a href="{{ url()->current() }}" class="btn btn-light btn-sm">{{ _lang('Reset') }}</a>
            </div>
        </form>
    </div>
</div>

<script>
    (function ($) {
        var input = $('#{{ $filterId }}');
        if (!input.length || typeof input.daterangepicker !== 'function') {
            return;
        }

        input.daterangepicker({
            autoUpdateInput: true,
            locale: {
                format: 'YYYY-MM-DD'
            },
            startDate: '{{ $fromDate }}',
            endDate: '{{ $toDate }}',
            ranges: {
                '{{ _lang('Today') }}': [moment(), moment()],
                '{{ _lang('Yesterday') }}': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                '{{ _lang('Last 7 Days') }}': [moment().subtract(6, 'days'), moment()],
                '{{ _lang('Last 30 Days') }}': [moment().subtract(29, 'days'), moment()],
                '{{ _lang('This Month') }}': [moment().startOf('month'), moment().endOf('month')],
                '{{ _lang('Last Month') }}': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            }
        });

        input.on('apply.daterangepicker', function (ev, picker) {
            $('#{{ $filterId }}-from').val(picker.startDate.format('YYYY-MM-DD'));
            $('#{{ $filterId }}-to').val(picker.endDate.format('YYYY-MM-DD'));
            $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
        });
    })(jQuery);
</script>
