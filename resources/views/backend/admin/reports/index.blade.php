@extends('layouts.app')

@section('workspace_top_tabs')
@include('backend.admin.partials.module-tabs', [
    'variant' => 'top-strip',
    'role' => 'navigation',
    'tabs' => [
        ['label' => _lang('Executive KPIs'), 'target' => '#executive', 'active' => true],
        ['label' => _lang('Portfolio'), 'target' => '#portfolio'],
        ['label' => _lang('Collections'), 'target' => '#collections'],
        ['label' => _lang('Accounts'), 'target' => '#accounts'],
        ['label' => _lang('Transactions'), 'target' => '#transactions'],
        ['label' => _lang('Expenses'), 'target' => '#expenses'],
        ['label' => _lang('Banking'), 'target' => '#banking'],
        ['label' => _lang('Branch Performance'), 'target' => '#branch-performance'],
        ['label' => _lang('Revenue'), 'target' => '#revenue'],
    ],
])
@endsection

@section('content')
@php
    $attentionTotal = $reportHighlights['attention_total'] ?? 0;
    $overdueCount = $reportHighlights['overdue_repayments'] ?? 0;
    $dueTodayCount = $reportHighlights['due_today'] ?? 0;
    $pendingBankItems = $reportHighlights['pending_bank_transactions'] ?? 0;
    $periodLabel = $reportHighlights['period_label'] ?? date('F Y');
@endphp
@include('backend.admin.partials.workspace-styles')
<style>
.report-exec-hero {
    border: 1px solid #dfe8e7;
    border-radius: 8px;
    background: #f8fbfb;
}
.report-exec-hero .card-body { padding: 1.35rem; }
.report-exec-eyebrow {
    color: #3F686D;
    font-size: .74rem;
    font-weight: 700;
    letter-spacing: 0;
    text-transform: uppercase;
}
.report-exec-title {
    color: #243036;
    font-size: 1.35rem;
    font-weight: 700;
    line-height: 1.25;
    margin: .35rem 0 .5rem;
}
.report-exec-copy { color: #66737b; max-width: 720px; }
.report-exec-signal {
    border: 1px solid #e6edec;
    border-radius: 8px;
    background: #fff;
    padding: 1rem;
    height: 100%;
}
.report-exec-signal-label { color: #6f787f; font-size: .78rem; margin-bottom: .3rem; }
.report-exec-signal-value { color: #243036; font-size: 1.55rem; font-weight: 700; line-height: 1.05; }
.report-exec-signal-meta { color: #7b858c; font-size: .78rem; margin-top: .35rem; }
.report-kpi-card {
    display: block;
    height: 100%;
    border: 1px solid #e8eeee;
    border-radius: 8px;
    color: inherit;
    text-decoration: none;
    background: #fff;
    transition: border-color .15s ease, background .15s ease;
}
.report-kpi-card:hover { color: inherit; text-decoration: none; border-color: #cfdedb; background: #fbfdfd; }
.report-kpi-card .card-body { padding: 1rem; }
.report-kpi-top { display: flex; align-items: flex-start; justify-content: space-between; gap: .75rem; margin-bottom: .8rem; }
.report-kpi-icon {
    width: 34px;
    height: 34px;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #eef7f7;
    color: #3F686D;
}
.report-kpi-card.critical .report-kpi-icon { background: #fdecee; color: #b4232f; }
.report-kpi-card.review .report-kpi-icon { background: #fff2e3; color: #a04a00; }
.report-kpi-card.today .report-kpi-icon { background: #fff7db; color: #7a4d00; }
.report-kpi-label { color: #6f787f; font-size: .78rem; margin-bottom: .3rem; }
.report-kpi-value { color: #243036; font-size: 1.45rem; font-weight: 700; line-height: 1.05; }
.report-kpi-meta { color: #5f6b72; font-size: .8rem; margin-top: .35rem; }
.report-kpi-detail { color: #7b858c; font-size: .78rem; margin-top: .7rem; min-height: 36px; }
.report-shortcut-card {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    padding: 1rem 0;
    border-bottom: 1px solid #eef1f5;
    color: inherit;
    text-decoration: none;
}
.report-shortcut-card:last-child { border-bottom: 0; }
.report-shortcut-card:hover { color: inherit; text-decoration: none; }
.report-shortcut-title { color: #243036; font-weight: 700; margin-bottom: .25rem; }
.report-shortcut-copy { color: #6f787f; font-size: .82rem; margin: 0; }
.report-mini-table th { background: #fafbf9; color: #5f6b72; font-size: .76rem; }
.report-mini-table td { vertical-align: middle; }
.report-exec-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
}
.report-exec-toolbar .report-exec-title { margin: 0; }
.report-exec-period { color: #6f787f; font-size: .82rem; }
.report-exec-actions { display: flex; gap: .5rem; flex-wrap: wrap; }
.report-metric-card {
    height: 100%;
    border: 1px solid #e7eeee;
    border-left: 4px solid #3F686D;
    border-radius: 8px;
    background: #fff;
    padding: 1rem;
}
.report-metric-card.active { border-left-color: #16803c; }
.report-metric-card.review { border-left-color: #b45309; }
.report-metric-card.critical { border-left-color: #b4232f; }
.report-metric-label { color: #65717a; font-size: .78rem; font-weight: 700; margin-bottom: .35rem; }
.report-metric-value { color: #202b33; font-size: 1.55rem; font-weight: 800; line-height: 1.05; }
.report-metric-split { display: grid; gap: .25rem; color: #6f787f; font-size: .78rem; margin-top: .7rem; }
.report-info-table th,
.report-info-table td { padding: .68rem .85rem; vertical-align: middle; }
.report-info-table th { color: #65717a; font-weight: 600; width: 58%; }
.report-info-table td { color: #202b33; font-weight: 800; text-align: right; }
.report-chart-wrap {
    height: 240px;
    position: relative;
}
.report-chart-wrap canvas {
    max-height: 240px;
}
.report-action-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: .75rem;
    margin-bottom: 1rem;
}
.report-action-btn {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
    min-height: 48px;
    border: 1px solid #dfe8e7;
    border-radius: 8px;
    background: #fff;
    color: #243036;
    font-weight: 700;
    padding: .75rem .9rem;
    text-decoration: none;
}
.report-action-btn:hover {
    border-color: #3F686D;
    color: #243036;
    text-decoration: none;
}
.report-action-btn i {
    color: #3F686D;
    font-size: .9rem;
}
@media (max-width: 767.98px) {
    .report-exec-title { font-size: 1.15rem; }
    .report-kpi-value { font-size: 1.25rem; }
    .report-metric-value { font-size: 1.3rem; }
    .report-chart-wrap { height: 220px; }
}
</style>

<div class="tab-content reports-tab-content">
        <div class="tab-pane fade show active" id="executive">
            @include('backend.admin.reports.partials.executive-dashboard')

            <div class="workspace-section-title mb-2">{{ _lang('Reports') }}</div>
            <div class="report-action-grid">
                @foreach(($reportGroups['executive']['items'] ?? []) as $item)
                    <a class="report-action-btn ajax-modal" href="{{ $item['route'] }}" data-title="{{ $item['label'] }}" data-fullscreen="true">
                        <span>{{ $item['label'] }}</span>
                        <i class="ti-arrow-right"></i>
                    </a>
                @endforeach
                <a class="report-action-btn ajax-modal" href="{{ route('reports.loan_due_report') }}" data-title="{{ _lang('Loan Due Report') }}" data-fullscreen="true">
                    <span>{{ _lang('Loan Due') }}</span>
                    <i class="ti-arrow-right"></i>
                </a>
                <a class="report-action-btn ajax-modal" href="{{ route('reports.transactions_report') }}" data-title="{{ _lang('Transactions Report') }}" data-fullscreen="true">
                    <span>{{ _lang('Transactions') }}</span>
                    <i class="ti-arrow-right"></i>
                </a>
            </div>
        </div>
        <div class="tab-pane fade" id="portfolio">
            <div class="report-action-grid">
                @foreach(($reportGroups['portfolio']['items'] ?? []) as $item)
                    <a class="report-action-btn ajax-modal" href="{{ $item['route'] }}" data-title="{{ $item['label'] }}" data-fullscreen="true">
                        <span>{{ $item['label'] }}</span>
                        <i class="ti-arrow-right"></i>
                    </a>
                @endforeach
            </div>
        </div>
        <div class="tab-pane fade" id="collections">
            <div class="report-action-grid">
                @foreach(($reportGroups['collections']['items'] ?? []) as $item)
                    <a class="report-action-btn ajax-modal" href="{{ $item['route'] }}" data-title="{{ $item['label'] }}" data-fullscreen="true">
                        <span>{{ $item['label'] }}</span>
                        <i class="ti-arrow-right"></i>
                    </a>
                @endforeach
            </div>
        </div>
        <div class="tab-pane fade" id="accounts">
            <div class="report-action-grid">
                @foreach(($reportGroups['accounts']['items'] ?? []) as $item)
                    <a class="report-action-btn ajax-modal" href="{{ $item['route'] }}" data-title="{{ $item['label'] }}" data-fullscreen="true">
                        <span>{{ $item['label'] }}</span>
                        <i class="ti-arrow-right"></i>
                    </a>
                @endforeach
            </div>
        </div>
        <div class="tab-pane fade" id="transactions">
            <div class="report-action-grid">
                @foreach(($reportGroups['transactions']['items'] ?? []) as $item)
                    <a class="report-action-btn ajax-modal" href="{{ $item['route'] }}" data-title="{{ $item['label'] }}" data-fullscreen="true">
                        <span>{{ $item['label'] }}</span>
                        <i class="ti-arrow-right"></i>
                    </a>
                @endforeach
            </div>
        </div>
        <div class="tab-pane fade" id="expenses">
            <div class="report-action-grid">
                @foreach(($reportGroups['expenses']['items'] ?? []) as $item)
                    <a class="report-action-btn ajax-modal" href="{{ $item['route'] }}" data-title="{{ $item['label'] }}" data-fullscreen="true">
                        <span>{{ $item['label'] }}</span>
                        <i class="ti-arrow-right"></i>
                    </a>
                @endforeach
            </div>
        </div>
        <div class="tab-pane fade" id="banking">
            <div class="report-action-grid">
                @foreach(($reportGroups['banking']['items'] ?? []) as $item)
                    <a class="report-action-btn ajax-modal" href="{{ $item['route'] }}" data-title="{{ $item['label'] }}" data-fullscreen="true">
                        <span>{{ $item['label'] }}</span>
                        <i class="ti-arrow-right"></i>
                    </a>
                @endforeach
            </div>
        </div>
        <div class="tab-pane fade" id="branch-performance">
            <div class="report-action-grid">
                @foreach(($reportGroups['branch_performance']['items'] ?? []) as $item)
                    <a class="report-action-btn ajax-modal" href="{{ $item['route'] }}" data-title="{{ $item['label'] }}" data-fullscreen="true">
                        <span>{{ $item['label'] }}</span>
                        <i class="ti-arrow-right"></i>
                    </a>
                @endforeach
            </div>

            <div class="card workspace-section-card mb-3">
                <div class="card-header">{{ _lang('Branch Performance Snapshot') }}</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered report-mini-table mb-0">
                            <thead>
                                <tr>
                                    <th>{{ _lang('Branch') }}</th>
                                    <th>{{ _lang('Members') }}</th>
                                    <th>{{ _lang('Pending Members') }}</th>
                                    <th>{{ _lang('Active Loans') }}</th>
                                    <th>{{ _lang('Portfolio') }}</th>
                                    <th>{{ _lang('Overdue') }}</th>
                                    <th>{{ _lang('Overdue Amount') }}</th>
                                    <th>{{ _lang('Status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($branchReportSnapshot as $branch)
                                    <tr>
                                        <td>{{ $branch->name }}</td>
                                        <td>{{ number_format($branch->active_members) }}</td>
                                        <td>{{ number_format($branch->pending_members) }}</td>
                                        <td>{{ number_format($branch->active_loans) }}</td>
                                        <td>{{ money_format_2($branch->portfolio_amount ?? 0) }}</td>
                                        <td>{{ number_format($branch->overdue_repayments) }}</td>
                                        <td>{{ money_format_2($branch->overdue_amount ?? 0) }}</td>
                                        <td><span class="workspace-status-chip {{ $branch->pressure_score > 0 ? 'review' : 'active' }}">{{ $branch->pressure_score > 0 ? _lang('Review') : _lang('OK') }}</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="8" class="text-center text-muted">{{ _lang('No branch reporting data found') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="tab-pane fade" id="revenue">
            <div class="report-action-grid">
                @foreach(($reportGroups['revenue']['items'] ?? []) as $item)
                    <a class="report-action-btn ajax-modal" href="{{ $item['route'] }}" data-title="{{ $item['label'] }}" data-fullscreen="true">
                        <span>{{ $item['label'] }}</span>
                        <i class="ti-arrow-right"></i>
                    </a>
                @endforeach
            </div>
        </div>
</div>
@endsection

@section('js-script')
@php
    $cashChartLabels = [_lang('Credits'), _lang('Debits'), _lang('Expenses'), _lang('Net')];
    $cashChartValues = [
        (float) ($reportHighlights['monthly_credits'] ?? 0),
        (float) ($reportHighlights['monthly_debits'] ?? 0),
        (float) ($reportHighlights['expenses_this_month_amount'] ?? 0),
        (float) ($reportHighlights['net_cash_movement'] ?? 0),
    ];
    $pressureChartLabels = [_lang('Active Loans'), _lang('Pending Loans'), _lang('Due Today'), _lang('Overdue'), _lang('Pending Bank')];
    $pressureChartValues = [
        (int) ($reportHighlights['active_loans'] ?? 0),
        (int) ($reportHighlights['pending_loans'] ?? 0),
        (int) ($reportHighlights['due_today'] ?? 0),
        (int) ($reportHighlights['overdue_repayments'] ?? 0),
        (int) ($reportHighlights['pending_bank_transactions'] ?? 0),
    ];
@endphp
<script src="{{ asset('public/backend/plugins/chartJs/chart.min.js') }}"></script>
<script>
(function ($) {
    function initReportModalTables($scope) {
        if (!$.fn.DataTable) return;

        $scope.find('.report-table').each(function () {
            if ($.fn.DataTable.isDataTable(this)) return;

            var headerText = $(this).prev('.report-header').html() || '';
            $(this).DataTable({
                responsive: true,
                bAutoWidth: false,
                ordering: false,
                lengthChange: false,
                dom:
                    "<'row'<'col-sm-12 col-md-6'B><'col-sm-12 col-md-6'f>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                language: {
                    emptyTable: $lang_no_data_found,
                    info: $lang_showing + " _START_ " + $lang_to + " _END_ " + $lang_of + " _TOTAL_ " + $lang_entries,
                    infoEmpty: $lang_showing_0_to_0_of_0_entries,
                    infoFiltered: "(filtered from _MAX_ total entries)",
                    lengthMenu: $lang_show + " _MENU_ " + $lang_entries,
                    loadingRecords: $lang_loading,
                    processing: $lang_processing,
                    search: $lang_search,
                    zeroRecords: $lang_no_matching_records_found,
                    paginate: {
                        first: $lang_first,
                        last: $lang_last,
                        previous: "<i class='fas fa-angle-left'></i>",
                        next: "<i class='fas fa-angle-right'></i>"
                    },
                    buttons: {
                        copy: $lang_copy,
                        excel: $lang_excel,
                        pdf: $lang_pdf,
                        print: $lang_print
                    }
                },
                drawCallback: function () {
                    $(".dataTables_paginate > .pagination").addClass("pagination-bordered");
                },
                buttons: [
                    'copy',
                    'excel',
                    'pdf',
                    {
                        extend: 'print',
                        title: '',
                        customize: function (win) {
                            $(win.document.body)
                                .css('font-size', '10pt')
                                .prepend('<div class="text-center">' + headerText + '</div>');

                            $(win.document.body).find('table')
                                .addClass('compact')
                                .css('font-size', 'inherit');
                        }
                    }
                ]
            });
        });
    }

    function initReportModalContent($scope) {
        if (!$scope.length) return;

        if (typeof init_datepicker === 'function') {
            init_datepicker($scope);
        }

        $scope.find('.auto-select').each(function () {
            $(this).val($(this).data('selected')).trigger('change');
        });

        if ($.fn.select2) {
            $scope.find('select.select2').select2({
                dropdownParent: $scope.closest('.modal-content')
            });
        }

        if ($.fn.parsley) {
            $scope.find('form.validate').parsley();
        }

        initReportModalTables($scope);

        if (window.TableExportTotals) {
            window.TableExportTotals.refresh();
        }
    }

    function hideRouteLoader() {
        if (window.CavicRouteLoader) {
            window.CavicRouteLoader.hide();
        }
    }

    $(document).on('shown.bs.modal', '#main_modal', function () {
        var $shell = $(this).find('.report-modal-shell');
        if ($shell.length) {
            setTimeout(function () {
                initReportModalContent($shell);
            }, 50);
        }
    });

    $(document).on('submit', '#main_modal .report-modal-shell form', function (event) {
        event.preventDefault();
        hideRouteLoader();

        var $form = $(this);
        if ($form.data('submitting')) return;
        if ($.fn.parsley && $form.hasClass('validate') && !$form.parsley().validate()) {
            hideRouteLoader();
            return;
        }

        $form.data('submitting', true);

        $.ajax({
            url: $form.attr('action'),
            type: ($form.attr('method') || 'GET').toUpperCase(),
            data: $form.serialize(),
            beforeSend: function () {
                $("#preloader").css("display", "block");
            },
            success: function (html) {
                var $modal = $('#main_modal');
                $modal.find('.modal-body').html(html);
                $modal.find('.alert-primary, .alert-danger').addClass('d-none');
                initReportModalContent($modal.find('.report-modal-shell'));
            },
            error: function (request) {
                var message = request.responseJSON && request.responseJSON.message
                    ? request.responseJSON.message
                    : @json(_lang('Unable to load report'));
                $('#main_modal .alert-danger').html('<span>' + message + '</span>').removeClass('d-none');
                $('#main_modal .alert-primary').addClass('d-none');
            },
            complete: function () {
                $form.data('submitting', false);
                $("#preloader").css("display", "none");
                hideRouteLoader();
            }
        });
    });
})(jQuery);

(function () {
    if (typeof Chart === 'undefined') return;

    var cashCanvas = document.getElementById('reportCashMovementChart');
    var pressureCanvas = document.getElementById('reportPortfolioPressureChart');

    var cashData = {
        labels: @json($cashChartLabels),
        values: @json($cashChartValues),
    };

    var pressureData = {
        labels: @json($pressureChartLabels),
        values: @json($pressureChartValues),
    };

    if (cashCanvas) {
        new Chart(cashCanvas.getContext('2d'), {
            type: 'bar',
            data: {
                labels: cashData.labels,
                datasets: [{
                    data: cashData.values,
                    backgroundColor: ['#1A8E8F', '#C14953', '#B7791F', '#3F686D'],
                    borderRadius: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });
    }

    if (pressureCanvas) {
        new Chart(pressureCanvas.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: pressureData.labels,
                datasets: [{
                    data: pressureData.values,
                    backgroundColor: ['#1A8E8F', '#B7791F', '#E0B341', '#C14953', '#3F686D'],
                    borderWidth: 0,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '62%',
                plugins: { legend: { position: 'bottom' } }
            }
        });
    }
})();
</script>
@endsection
