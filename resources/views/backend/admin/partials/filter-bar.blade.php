@php
    $title = $title ?? _lang('Filters');
    $description = $description ?? null;
    $action = $action ?? url()->current();
    $filterId = $filterId ?? ('workspace-filter-' . uniqid());
    $dateRange = $dateRange ?? null;
    $extraFilters = $extraFilters ?? [];
    $buttons = $buttons ?? [];
    $ignoredKeys = ['from_date', 'to_date'];

    foreach ($extraFilters as $filter) {
        if (! empty($filter['name'])) {
            $ignoredKeys[] = $filter['name'];
        }
    }
@endphp

<div class="card workspace-page-header-card workspace-filter-card mb-4">
    <div class="card-body py-3">
        <form method="get" action="{{ $action }}" class="workspace-filter-form d-flex flex-column flex-xl-row align-items-xl-end justify-content-between">
            <div class="workspace-filter-copy pr-xl-4 mb-3 mb-xl-0">
                <div class="workspace-section-title mb-1">{{ $title }}</div>
                @if($description)
                    <div class="text-muted small">{{ $description }}</div>
                @endif
            </div>

            <div class="workspace-filter-controls d-flex flex-column flex-lg-row align-items-lg-end">
                @foreach(request()->except($ignoredKeys) as $key => $value)
                    @if(! is_array($value))
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endif
                @endforeach

                @if($dateRange)
                    @php
                        $fromDate = $dateRange['from_date'] ?? request('from_date', now()->toDateString());
                        $toDate = $dateRange['to_date'] ?? request('to_date', $fromDate);
                        $fieldLabel = $dateRange['field_label'] ?? _lang('Date Range');
                        $displayValue = $dateRange['label'] ?? ($fromDate . ' - ' . $toDate);
                    @endphp
                    <div class="form-group workspace-date-range-group mb-2 mb-lg-0 mr-lg-2">
                        <label class="workspace-filter-label d-block mb-1" for="{{ $filterId }}">{{ $fieldLabel }}</label>
                        <div class="workspace-date-range-control">
                            <i class="far fa-calendar-alt"></i>
                            <input type="text" id="{{ $filterId }}" class="form-control form-control-sm workspace-date-range-input" value="{{ $displayValue }}" autocomplete="off" readonly>
                        </div>
                        <input type="hidden" name="from_date" id="{{ $filterId }}-from" value="{{ $fromDate }}">
                        <input type="hidden" name="to_date" id="{{ $filterId }}-to" value="{{ $toDate }}">
                    </div>
                @endif

                @foreach($extraFilters as $filter)
                    @if(($filter['type'] ?? 'select') === 'select')
                        <div class="form-group mb-2 mb-lg-0 mr-lg-2">
                            <label class="small text-muted d-block mb-1">{{ $filter['label'] ?? _lang('Filter') }}</label>
                            <select name="{{ $filter['name'] }}" class="form-control form-control-sm {{ $filter['class'] ?? '' }}" @if(! empty($filter['attributes'])) @foreach($filter['attributes'] as $attribute => $attributeValue) {{ $attribute }}="{{ $attributeValue }}" @endforeach @endif>
                                @if(array_key_exists('placeholder', $filter))
                                    <option value="">{{ $filter['placeholder'] }}</option>
                                @endif
                                @foreach(($filter['options'] ?? []) as $value => $label)
                                    <option value="{{ $value }}" {{ (string) ($filter['selected'] ?? request($filter['name'])) === (string) $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                @endforeach

                <button type="submit" class="btn btn-outline-primary btn-sm workspace-filter-btn mr-lg-2 mb-2 mb-lg-0"><i class="ti-check mr-1"></i>{{ _lang('Apply') }}</button>
                <a href="{{ $resetUrl ?? url()->current() }}" class="btn btn-light btn-sm workspace-filter-btn workspace-filter-reset mr-lg-2 mb-2 mb-lg-0"><i class="ti-reload mr-1"></i>{{ _lang('Reset') }}</a>

                @foreach($buttons as $button)
                    <a href="{{ $button['url'] ?? '#' }}" class="btn {{ $button['class'] ?? 'btn-outline-primary btn-sm' }} mr-lg-2 mb-2 mb-lg-0">
                        @if(! empty($button['icon']))
                            <i class="{{ $button['icon'] }} mr-1"></i>
                        @endif
                        {{ $button['label'] ?? _lang('Open') }}
                    </a>
                @endforeach
            </div>
        </form>
    </div>
</div>

@if($dateRange)
<script>
    window.addEventListener('load', function () {
        var $ = window.jQuery;
        if (!$) {
            return;
        }

        var input = $('#{{ $filterId }}');
        if (!input.length || typeof input.daterangepicker !== 'function') {
            return;
        }

        var startDate = moment($('#{{ $filterId }}-from').val(), 'YYYY-MM-DD', true);
        var endDate = moment($('#{{ $filterId }}-to').val(), 'YYYY-MM-DD', true);

        if (!startDate.isValid()) {
            startDate = moment();
        }

        if (!endDate.isValid()) {
            endDate = startDate.clone();
        }

        input.daterangepicker({
            autoUpdateInput: true,
            parentEl: 'body',
            alwaysShowCalendars: true,
            locale: {
                format: 'DD/MM/YYYY',
                separator: ' - ',
                applyLabel: '{{ _lang('Apply') }}',
                cancelLabel: '{{ _lang('Cancel') }}',
                customRangeLabel: '{{ _lang('Custom Range') }}'
            },
            startDate: startDate,
            endDate: endDate,
            ranges: {
                '{{ _lang('Today') }}': [moment(), moment()],
                '{{ _lang('Yesterday') }}': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                '{{ _lang('Last 7 Days') }}': [moment().subtract(6, 'days'), moment()],
                '{{ _lang('Last 30 Days') }}': [moment().subtract(29, 'days'), moment()],
                '{{ _lang('This Month') }}': [moment().startOf('month'), moment().endOf('month')],
                '{{ _lang('Last Month') }}': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            }
        });

        function setDisplay(startDate, endDate) {
            var sameDay = startDate.isSame(endDate, 'day');
            input.val(sameDay ? startDate.format('DD/MM/YYYY') : startDate.format('DD/MM/YYYY') + ' - ' + endDate.format('DD/MM/YYYY'));
        }

        setDisplay(startDate, endDate);

        input.on('show.daterangepicker', function (ev, picker) {
            picker.container.addClass('cavic-date-range-picker');
        });

        input.on('apply.daterangepicker', function (ev, picker) {
            $('#{{ $filterId }}-from').val(picker.startDate.format('YYYY-MM-DD'));
            $('#{{ $filterId }}-to').val(picker.endDate.format('YYYY-MM-DD'));
            setDisplay(picker.startDate, picker.endDate);
        });
    });
</script>
@endif
