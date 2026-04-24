/**
 * Table Export & Footer Totals
 * - Computes footer totals for columns with data-sum="1" on <th> and fills <tfoot .table-totals-row>
 */
(function ($) {
	'use strict';

	if (!$) {
		return;
	}

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
		computeFooterTotals();
		// Re-run totals after DataTables and other dynamic inits
		setTimeout(function () {
			computeFooterTotals();
		}, 500);
	});

	// Re-run totals after DataTables draw (e.g. pagination/filter)
	$(document).on('draw.dt', 'table', function () {
		computeFooterTotals();
	});

	// Expose for manual refresh
	window.TableExportTotals = {
		computeTotals: computeFooterTotals,
		refresh: function () { computeFooterTotals(); }
	};
})(window.jQuery || window.$);
