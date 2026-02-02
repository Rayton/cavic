/**
 * Table Export & Footer Totals
 * - Adds Export CSV/Excel buttons above tables with class .table-export
 * - Computes footer totals for columns with data-sum="1" on <th> and fills <tfoot .table-totals-row>
 */
(function ($) {
	'use strict';

	function stripHtml(html) {
		var tmp = document.createElement('div');
		tmp.innerHTML = html;
		return (tmp.textContent || tmp.innerText || '').trim();
	}

	function parseNumber(text) {
		if (text == null || text === '') return NaN;
		var s = (typeof text === 'string' ? stripHtml(text) : String(text))
			.replace(/[^\d.-]/g, '');
		return parseFloat(s) || NaN;
	}

	function tableToArray(table, useDataTables) {
		var data = [];
		var $table = $(table);
		var api = useDataTables && $.fn.DataTable && $table.DataTable ? $table.DataTable() : null;

		if (api && api.rows) {
			try {
				// DataTables: get header texts and row data (current page or all if client-side)
				var columns = api.columns().header().toArray();
				// Columns to exclude from export (e.g. Action column with data-no-export="1")
				var excludeIndices = [];
				$(columns).each(function (i) {
					var $th = $(this);
					if ($th.attr('data-no-export') === '1' || $th.hasClass('no-export') || stripHtml(this.innerHTML).toLowerCase() === 'action') {
						excludeIndices.push(i);
					}
				});
				var headerRow = columns.map(function (th, i) {
					return excludeIndices.indexOf(i) >= 0 ? null : stripHtml(th.innerHTML).trim();
				}).filter(function (v) { return v !== null; });
				data.push(headerRow);
				api.rows({ search: 'applied', page: 'current' }).every(function () {
					var row = this.data();
					var arr = Array.isArray(row) ? row : columns.map(function (_, i) {
						var col = api.settings()[0].aoColumns[i];
						var name = col && col.data;
						var val;
						if (typeof name === 'string' && name.indexOf('.') !== -1) {
							var parts = name.split('.');
							var v = row;
							for (var p = 0; p < parts.length && v != null; p++) v = v[parts[p]];
							val = v != null ? v : '';
						} else {
							val = row[name] != null ? row[name] : '';
						}
						return val;
					});
					// Strip HTML from each value (styled amounts, badges, etc.) and exclude no-export columns
					var exportRow = arr.map(function (c, i) {
						if (excludeIndices.indexOf(i) >= 0) return null;
						var raw = c != null ? String(c) : '';
						return stripHtml(raw).trim();
					}).filter(function (v) { return v !== null; });
					data.push(exportRow);
				});
				return data;
			} catch (e) {}
		}

		// Static table: read from DOM (strip HTML, exclude data-no-export columns)
		var $headerCells = $table.find('thead tr:first th');
		var excludeIndices = [];
		$headerCells.each(function (i) {
			if ($(this).attr('data-no-export') === '1' || $(this).hasClass('no-export') || stripHtml($(this).html()).toLowerCase() === 'action') {
				excludeIndices.push(i);
			}
		});
		var headerRow = [];
		$headerCells.each(function (i) {
			if (excludeIndices.indexOf(i) < 0) headerRow.push(stripHtml($(this).html()).trim());
		});
		data.push(headerRow);
		$table.find('tbody tr').each(function () {
			var row = [];
			$(this).find('td').each(function (i) {
				if (excludeIndices.indexOf(i) < 0) row.push(stripHtml($(this).html()).trim());
			});
			if (row.length) data.push(row);
		});
		if (data.length === 1) data.push([]);
		return data;
	}

	function csvEscapeCell(cell) {
		var s = String(cell).replace(/"/g, '""');
		// Prefix values that start with =, +, -, @ or tab so Excel does not treat them as formulas (avoids #NAME? for zero amounts like "+ 0 Tsh0.00")
		if (s.length && /^[=+\-@\t]/.test(s)) s = "'" + s;
		if (/[",\n\r]/.test(s)) return '"' + s + '"';
		return s;
	}

	function exportCsv(table, filename) {
		var data = tableToArray(table, true);
		if (!data.length) return;
		var csv = data.map(function (row) {
			return row.map(csvEscapeCell).join(',');
		}).join('\r\n');
		var blob = new Blob(['\ufeff' + csv], { type: 'text/csv;charset=utf-8' });
		downloadBlob(blob, (filename || 'export') + '.csv');
	}

	function exportExcel(table, filename) {
		// Export as CSV so Excel opens without "format and extension don't match" warning
		var data = tableToArray(table, true);
		if (!data.length) return;
		var csv = data.map(function (row) {
			return row.map(csvEscapeCell).join(',');
		}).join('\r\n');
		var blob = new Blob(['\ufeff' + csv], { type: 'text/csv;charset=utf-8' });
		downloadBlob(blob, (filename || 'export') + '.csv');
	}

	function downloadBlob(blob, filename) {
		var a = document.createElement('a');
		var url = (window.URL || window.webkitURL).createObjectURL(blob);
		a.href = url;
		a.download = filename;
		document.body.appendChild(a);
		a.click();
		document.body.removeChild(a);
		(window.URL || window.webkitURL).revokeObjectURL(url);
	}

	function initExportToolbar() {
		$('table.table-export').each(function () {
			var $table = $(this);
			if ($table.closest('.no-export').length) return;
			if ($table.data('export-toolbar')) return;
			var id = $table.attr('id') || ('tbl-' + Math.random().toString(36).slice(2));
			if (!$table.attr('id')) $table.attr('id', id);
			// Filename: data-export-filename on table, or card title (panel-title/header-title), else 'Table'
			var title = $table.attr('data-export-filename') || $table.closest('.card').find('.panel-title').first().text().trim() || $table.closest('.card').find('.header-title').first().text().trim() || 'Table';
			var filename = (typeof title === 'string' ? title : 'Table').replace(/[^a-z0-9]+/gi, '_').toLowerCase() || 'export';
			var $wrap = $table.closest('.table-responsive').length ? $table.closest('.table-responsive') : $table.parent();
			var $toolbar = $('<div class="table-export-toolbar mb-2 d-flex align-items-center flex-wrap"></div>');
			$toolbar.append(
				'<button type="button" class="btn btn-sm btn-outline-secondary mr-2 table-export-csv"><i class="ti-download mr-1"></i> CSV</button>' +
				'<button type="button" class="btn btn-sm btn-outline-secondary table-export-excel"><i class="ti-files mr-1"></i> Excel</button>'
			);
			$wrap.before($toolbar);
			$toolbar.find('.table-export-csv').on('click', function () { exportCsv($table[0], filename); });
			$toolbar.find('.table-export-excel').on('click', function () { exportExcel($table[0], filename); });
			$table.data('export-toolbar', true);
		});
	}

	function computeFooterTotals() {
		$('table').each(function () {
			var $table = $(this);
			var $tfoot = $table.find('tfoot .table-totals-row');
			if (!$tfoot.length) return;
			var $headerRow = $table.find('thead tr:first');
			var $bodyRows = $table.find('tbody tr').not(function () {
				return $(this).find('td[colspan]').length;
			});
			$headerRow.find('th').each(function (colIndex) {
				var $th = $(this);
				var label = $th.attr('data-total-label');
				var $footCell = $tfoot.find('td').eq(colIndex);
				if (!$footCell.length) return;
				if (label && colIndex === 0) {
					$footCell.html('<strong>' + label + '</strong>');
					return;
				}
				if ($th.attr('data-sum') !== '1' && !$th.hasClass('sum-col')) return;
				var sum = 0;
				$bodyRows.each(function () {
					var $cell = $(this).find('td').eq(colIndex);
					if (!$cell.length) return;
					var val = parseNumber($cell.text());
					if (!isNaN(val)) sum += val;
				});
				$footCell.html('<strong>' + (isNaN(sum) ? '' : sum.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 })) + '</strong>');
				if ($th.hasClass('text-right')) $footCell.addClass('text-right');
			});
		});
	}

	$(document).ready(function () {
		initExportToolbar();
		computeFooterTotals();
		// Re-run toolbar after DataTables and other dynamic inits
		setTimeout(function () {
			initExportToolbar();
			computeFooterTotals();
		}, 500);
	});

	// Re-run totals after DataTables draw (e.g. pagination/filter)
	$(document).on('draw.dt', 'table', function () {
		computeFooterTotals();
	});

	// Expose for manual refresh
	window.TableExportTotals = {
		initToolbars: initExportToolbar,
		computeTotals: computeFooterTotals,
		refresh: function () { initExportToolbar(); computeFooterTotals(); }
	};
})(jQuery);
