@once
<style>
    .cavic-datatable-card .table-responsive {
        border: 1px solid var(--cavic-border, #e7e9e4);
        border-radius: 18px;
        background: var(--cavic-surface, #fff);
        overflow-x: auto;
        overflow-y: visible;
    }

    .cavic-datatable-card .cavic-data-table,
    .cavic-datatable-card .dashboard-table-compact {
        min-width: 760px;
    }

    .cavic-datatable-card .dashboard-table-compact thead th,
    .cavic-datatable-card .dashboard-table-compact tbody td {
        padding-top: .52rem;
        padding-bottom: .52rem;
    }

    .cavic-datatable-card .dashboard-table-compact thead th {
        font-size: .74rem;
        white-space: nowrap;
    }

    .cavic-datatable-card .dashboard-table-compact .workspace-status-chip {
        white-space: nowrap;
    }

    .cavic-datatable-card .dashboard-table-compact th:last-child,
    .cavic-datatable-card .dashboard-table-compact td:last-child {
        width: 1%;
        text-align: center;
        white-space: nowrap;
    }

    .cavic-datatable-card .admin-datatable-top {
        margin-bottom: .7rem;
    }

    .cavic-datatable-card .admin-datatable-table-wrap {
        border: 0;
        border-radius: 0;
    }

    .cavic-datatable-card .dashboard-proof-top-center {
        justify-content: center;
    }

    .cavic-datatable-card .dashboard-proof-export-buttons .admin-dt-btn {
        min-height: 30px;
        padding: .32rem .62rem;
    }

    .cavic-datatable-card .dashboard-columns-menu {
        z-index: 1085;
        max-height: min(420px, 70vh);
        overflow-y: auto;
    }
</style>

<script>
    window.cavicDataTableExportColumns = function (idx, data, node) {
        return $(node).attr('data-no-export') !== '1';
    };

    window.cavicSlugTableTitle = function (text, fallback) {
        var clean = $.trim($('<div>').html(text || '').text())
            .replace(/\s+/g, '_')
            .replace(/[^\w]+/g, '_')
            .replace(/^_+|_+$/g, '');

        return clean || fallback;
    };

    window.cavicPrepareEmptyTable = function ($table) {
        var $rows = $table.find('tbody tr');
        var $emptyCell = $rows.length === 1 ? $rows.first().find('td[colspan]').first() : $();

        if ($emptyCell.length) {
            $table.attr('data-empty-message', $.trim($emptyCell.text()));
            $rows.remove();
        }
    };

    window.cavicBuildDataTableToolbar = function (api, $table) {
        var $wrapper = $(api.table().container());
        var $left = $wrapper.find('.admin-datatable-top-left');
        var $right = $wrapper.find('.admin-datatable-top-right');
        var $top = $wrapper.find('.admin-datatable-top');
        var $length = $left.find('.dataTables_length').detach();
        var $search = $right.find('.dataTables_filter').detach();
        var $buttons = $left.find('.dt-buttons').detach();
        var unique = $table.attr('id') || 'cavic-table';

        var $toolbarLeft = $('<div class="dashboard-proof-top-left"></div>');
        var $toolbarCenter = $('<div class="dashboard-proof-top-center"></div>');
        var $toolbarRight = $('<div class="dashboard-proof-top-right"></div>');
        var $columnsDropdown = $(
            '<div class="dropdown dashboard-columns-dropdown">' +
                '<button type="button" class="btn btn-xs admin-dt-btn admin-dt-btn-ghost dashboard-columns-trigger" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">' +
                    '<i class="ti-layout-column2"></i><span>{{ _lang('Columns') }}</span><i class="fas fa-chevron-down dashboard-columns-chevron"></i>' +
                '</button>' +
                '<div class="dropdown-menu dropdown-menu-right dashboard-columns-menu"></div>' +
            '</div>'
        );
        var $columnsMenu = $columnsDropdown.find('.dashboard-columns-menu');

        api.columns().every(function (columnIndex) {
            var column = this;
            var $header = $(column.header());
            var label = $header.text().trim();
            var isLocked = $header.attr('data-no-export') === '1' || label === '{{ _lang('Action') }}';

            if (!label || isLocked) {
                return;
            }

            var itemId = unique + '-column-toggle-' + columnIndex;
            var $item = $(
                '<label class="dropdown-item dashboard-columns-item" for="' + itemId + '">' +
                    '<span class="dashboard-columns-label">' + label + '</span>' +
                    '<input type="checkbox" class="dashboard-columns-checkbox" id="' + itemId + '"' + (column.visible() ? ' checked' : '') + '>' +
                '</label>'
            );

            $item.on('click', function (event) {
                event.stopPropagation();
            });

            $item.find('.dashboard-columns-checkbox').on('change', function () {
                column.visible($(this).is(':checked'));
                api.columns.adjust();
            });

            $columnsMenu.append($item);
        });

        $columnsMenu.on('click', function (event) {
            event.stopPropagation();
        });

        $buttons.addClass('dashboard-proof-export-buttons');
        $toolbarLeft.append($('<div class="dashboard-toolbar-item dashboard-toolbar-item-length"></div>').append($length));
        $toolbarCenter.append($('<div class="dashboard-toolbar-item dashboard-toolbar-item-export"></div>').append($buttons));
        $toolbarRight
            .append($('<div class="dashboard-toolbar-item"></div>').append($columnsDropdown))
            .append($('<div class="dashboard-toolbar-item dashboard-toolbar-item-search"></div>').append($search));

        $top.empty().append($toolbarLeft, $toolbarCenter, $toolbarRight);
        $search.find('input').attr('placeholder', '{{ _lang('Search records') }}');
    };

    window.cavicInitStaticDataTables = function (selector, prefix) {
        if (!$.fn.DataTable || typeof window.cavicAdminDataTable !== 'function') {
            return;
        }

        $(selector).each(function (index) {
            var $table = $(this);

            if ($.fn.DataTable.isDataTable($table)) {
                return;
            }

            var columnCount = $table.find('thead th').length;
            var explicit = $table.data('exportFilename');
            var nearbyTitle = $table.closest('.card, .tab-pane, .col-lg-6, .col-lg-12')
                .find('.panel-title, .workspace-section-title, h6')
                .first()
                .text();
            var exportTitle = explicit || (prefix + '_' + window.cavicSlugTableTitle(nearbyTitle, 'Table_' + (index + 1)));

            $table.attr('id', $table.attr('id') || (prefix.toLowerCase() + '-table-' + index));
            $table.attr('data-export-filename', exportTitle);
            $table.css('min-width', Math.max(760, columnCount * 132) + 'px');
            window.cavicPrepareEmptyTable($table);

            window.cavicAdminDataTable('#' + $table.attr('id'), {
                paging: true,
                searching: true,
                info: true,
                ordering: false,
                lengthChange: true,
                pageLength: 6,
                lengthMenu: [[6, 10, 25, 50, 100], [6, 10, 25, 50, 100]],
                buttons: [
                    {
                        extend: 'pdf',
                        text: '<i class="ti-download"></i><span>{{ _lang('PDF') }}</span>',
                        className: 'btn btn-xs admin-dt-btn admin-dt-btn-ghost',
                        filename: exportTitle,
                        title: exportTitle,
                        exportOptions: { columns: window.cavicDataTableExportColumns }
                    },
                    {
                        extend: 'excel',
                        text: '<i class="ti-download"></i><span>{{ _lang('Excel') }}</span>',
                        className: 'btn btn-xs admin-dt-btn admin-dt-btn-ghost',
                        filename: exportTitle,
                        title: exportTitle,
                        exportOptions: { columns: window.cavicDataTableExportColumns }
                    },
                    {
                        extend: 'csv',
                        text: '<i class="ti-download"></i><span>{{ _lang('CSV') }}</span>',
                        className: 'btn btn-xs admin-dt-btn admin-dt-btn-ghost',
                        filename: exportTitle,
                        exportOptions: { columns: window.cavicDataTableExportColumns }
                    }
                ],
                language: {
                    info: '{{ _lang('Viewing') }} _START_-_END_ {{ _lang('of') }} _TOTAL_',
                    infoEmpty: '{{ _lang('Viewing 0-0 of 0') }}',
                    search: '',
                    searchPlaceholder: '{{ _lang('Search records') }}',
                    lengthMenu: '_MENU_',
                    zeroRecords: '{{ _lang('No matching records found') }}',
                    emptyTable: $table.attr('data-empty-message') || '{{ _lang('No Data Available') }}',
                    paginate: {
                        previous: '<i class="fas fa-angle-left"></i>',
                        next: '<i class="fas fa-angle-right"></i>'
                    }
                },
                initComplete: function () {
                    window.cavicBuildDataTableToolbar(this.api(), $table);
                }
            });
        });
    };

    $(document).on('shown.bs.tab', 'a[data-toggle="tab"]', function () {
        if ($.fn.DataTable) {
            $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
        }
    });
</script>
@endonce
