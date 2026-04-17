@extends('layouts.app')

@section('content')
<style>
.wallets-month-table { font-size: 0.875rem; }
.wallets-month-table th, .wallets-month-table td { vertical-align: middle !important; }
#custom-date-fields { display: none; }
.wallets-import-tools {
	gap: 10px;
	padding: 10px 12px;
	margin-bottom: 12px;
	border: 1px solid #e4e9f2;
	border-radius: 10px;
	background: linear-gradient(180deg, #fbfcff 0%, #f4f7fc 100%);
}
.wallets-template-btn {
	border-color: #546ea7;
	color: #334f88;
	background: #ffffff;
	font-weight: 500;
}
.wallets-template-btn:hover {
	background: #eef3ff;
	border-color: #4b63a0;
	color: #2f467a;
}
.wallets-import-form {
	gap: 8px;
}
.wallets-file-wrap {
	position: relative;
	min-width: 280px;
	max-width: 420px;
	flex: 1 1 280px;
}
.wallets-import-form .wallets-file-input {
	height: 38px;
	padding: 0 12px;
	border: 1px dashed #9cb0d8;
	border-radius: 8px;
	background: #fff;
	color: #5b6780;
	font-size: 13px;
}
.wallets-import-form .wallets-file-input:focus {
	border-color: #5a7cc4;
	box-shadow: 0 0 0 0.12rem rgba(90, 124, 196, 0.22);
}
.wallets-import-form .wallets-import-btn {
	height: 38px;
	padding: 0 14px;
	font-weight: 500;
}
@media (max-width: 767px) {
	.wallets-file-wrap {
		min-width: 100%;
		max-width: 100%;
	}
	.wallets-import-form {
		width: 100%;
	}
	.wallets-import-form .wallets-import-btn {
		width: 100%;
	}
}
</style>
<div class="row">
	<div class="col-12">
		<div class="card">
			<div class="card-header">
				<span class="panel-title">{{ _lang('Wallets') }}</span>
			</div>

			<div class="card-body">
				{{-- Filters --}}
				<form method="get" action="{{ request()->url() }}" id="wallets-filter-form" class="mb-4">
					<div class="row align-items-end">
						<div class="col-xl-3 col-lg-4">
							<div class="form-group">
								<label class="control-label">{{ _lang('Period') }}</label>
								<select class="form-control" name="period" id="wallets-period">
									@foreach(\App\Http\Controllers\AdminWalletController::periodOptions() as $value => $label)
										<option value="{{ $value }}" {{ ($period ?? '') == $value ? 'selected' : '' }}>{{ $label }}</option>
									@endforeach
								</select>
							</div>
						</div>
						<div id="custom-date-fields" class="col-xl-4 col-lg-6">
							<div class="row">
								<div class="col-6">
									<div class="form-group">
										<label class="control-label">{{ _lang('Start Date') }}</label>
										<input type="text" class="form-control datepicker" name="date1" id="wallets-date1" value="{{ $date1 ?? '' }}" readonly>
									</div>
								</div>
								<div class="col-6">
									<div class="form-group">
										<label class="control-label">{{ _lang('End Date') }}</label>
										<input type="text" class="form-control datepicker" name="date2" id="wallets-date2" value="{{ $date2 ?? '' }}" readonly>
									</div>
								</div>
							</div>
						</div>
						<div class="col-xl-2 col-lg-4">
							<button type="submit" class="btn btn-primary btn-xs"><i class="ti-filter"></i>&nbsp;{{ _lang('Apply') }}</button>
						</div>
					</div>
				</form>

				<ul class="nav nav-tabs nav-tabs-highlight mb-3" role="tablist">
					<li class="nav-item">
						<a class="nav-link active" data-toggle="tab" href="#tab-loans" role="tab">{{ _lang('Loans') }}</a>
					</li>
					@foreach($accountTypesAndAccounts as $accountType)
					<li class="nav-item">
						<a class="nav-link" data-toggle="tab" href="#tab-{{ $accountType['id'] }}" role="tab">{{ $accountType['name'] }}</a>
					</li>
					@endforeach
					<li class="nav-item">
						<a class="nav-link" data-toggle="tab" href="#tab-transactions" role="tab">{{ _lang('Transactions') }}</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" data-toggle="tab" href="#tab-contributions" role="tab">{{ _lang('Monthly Contributions/Deductions') }}</a>
					</li>
				</ul>

				<div class="tab-content">
					{{-- Loans Tab (monthly = loan repayments per member) --}}
					<div class="tab-pane fade show active" id="tab-loans" role="tabpanel">
						<div class="d-flex flex-wrap align-items-center justify-content-between wallets-import-tools">
							<a href="{{ route('wallets.template', ['tab' => 'loans']) }}" class="btn btn-xs wallets-template-btn">
								<i class="ti-download"></i>&nbsp;{{ _lang('Download Import Template') }}
							</a>
							<form method="post" action="{{ route('wallets.import') }}" enctype="multipart/form-data" class="d-flex flex-wrap align-items-center wallets-import-form">
								@csrf
								<input type="hidden" name="tab" value="loans">
								<div class="wallets-file-wrap">
									<input type="file" name="file" class="form-control wallets-file-input" accept=".xlsx" required>
								</div>
								<button type="submit" class="btn btn-primary btn-xs wallets-import-btn"><i class="ti-import"></i>&nbsp;{{ _lang('Import') }}</button>
							</form>
						</div>
						<div class="table-responsive">
							<table id="wallets_table_loans" class="table table-bordered wallets-month-table wallets-export-table" data-export-filename="Wallets_Loans">
								<thead>
									<tr>
										<th>{{ _lang('ID') }}</th>
										<th>{{ _lang('Name') }}</th>
										@foreach($months as $m)
										<th class="text-right">{{ $m['label'] }}</th>
										@endforeach
										<th class="text-right">{{ _lang('Total') }}</th>
									</tr>
								</thead>
								<tbody>
									@foreach($members as $member)
									@php
										$rowTotal = 0;
										$monthKeys = array_map(function($m) { return $m['year'] . '-' . str_pad($m['month'], 2, '0', STR_PAD_LEFT); }, $months);
										$memberData = $loansMonthly[$member->id] ?? [];
									@endphp
									<tr>
										<td>{{ $member->id }}</td>
										<td>{{ $member->first_name }} {{ $member->last_name }}</td>
										@foreach($monthKeys as $key)
											@php $val = $memberData[$key] ?? 0; $rowTotal += $val; @endphp
											<td class="text-right">{{ number_format($val, 0) }}</td>
										@endforeach
										<td class="text-right"><strong>{{ number_format($rowTotal, 0) }}</strong></td>
									</tr>
									@endforeach
								</tbody>
							</table>
						</div>
					</div>

					{{-- Account type tabs (monthly credits per member per product) --}}
					@foreach($accountTypesAndAccounts as $accountType)
					<div class="tab-pane fade" id="tab-{{ $accountType['id'] }}" role="tabpanel">
						<div class="d-flex flex-wrap align-items-center justify-content-between wallets-import-tools">
							<a href="{{ route('wallets.template', ['tab' => 'account', 'product_id' => $accountType['product_id']]) }}" class="btn btn-xs wallets-template-btn">
								<i class="ti-download"></i>&nbsp;{{ _lang('Download Import Template') }}
							</a>
							<form method="post" action="{{ route('wallets.import') }}" enctype="multipart/form-data" class="d-flex flex-wrap align-items-center wallets-import-form">
								@csrf
								<input type="hidden" name="tab" value="account">
								<input type="hidden" name="product_id" value="{{ $accountType['product_id'] }}">
								<div class="wallets-file-wrap">
									<input type="file" name="file" class="form-control wallets-file-input" accept=".xlsx" required>
								</div>
								<button type="submit" class="btn btn-primary btn-xs wallets-import-btn"><i class="ti-import"></i>&nbsp;{{ _lang('Import') }}</button>
							</form>
						</div>
						<div class="table-responsive">
							<table id="wallets_table_{{ $accountType['id'] }}" class="table table-bordered wallets-month-table wallets-export-table" data-export-filename="Wallets_{{ preg_replace('/[^a-zA-Z0-9_-]/', '_', $accountType['name']) }}">
								<thead>
									<tr>
										<th>{{ _lang('ID') }}</th>
										<th>{{ _lang('Name') }}</th>
										@foreach($months as $m)
										<th class="text-right">{{ $m['label'] }}</th>
										@endforeach
										<th class="text-right">{{ _lang('Total') }}</th>
									</tr>
								</thead>
								<tbody>
									@php $productMonthly = $accountTypeMonthly[$accountType['product_id']] ?? []; @endphp
									@foreach($members as $member)
									@php
										$rowTotal = 0;
										$monthKeys = array_map(function($m) { return $m['year'] . '-' . str_pad($m['month'], 2, '0', STR_PAD_LEFT); }, $months);
										$memberData = $productMonthly[$member->id] ?? [];
									@endphp
									<tr>
										<td>{{ $member->id }}</td>
										<td>{{ $member->first_name }} {{ $member->last_name }}</td>
										@foreach($monthKeys as $key)
											@php $val = $memberData[$key] ?? 0; $rowTotal += $val; @endphp
											<td class="text-right">{{ number_format($val, 0) }}</td>
										@endforeach
										<td class="text-right"><strong>{{ number_format($rowTotal, 0) }}</strong></td>
									</tr>
									@endforeach
								</tbody>
							</table>
						</div>
					</div>
					@endforeach

					{{-- Transactions Tab (net movement per member per month) --}}
					<div class="tab-pane fade" id="tab-transactions" role="tabpanel">
						<div class="d-flex flex-wrap align-items-center justify-content-between wallets-import-tools">
							<a href="{{ route('wallets.template', ['tab' => 'transactions']) }}" class="btn btn-xs wallets-template-btn">
								<i class="ti-download"></i>&nbsp;{{ _lang('Download Import Template') }}
							</a>
							<form method="post" action="{{ route('wallets.import') }}" enctype="multipart/form-data" class="d-flex flex-wrap align-items-center wallets-import-form">
								@csrf
								<input type="hidden" name="tab" value="transactions">
								<div class="wallets-file-wrap">
									<input type="file" name="file" class="form-control wallets-file-input" accept=".xlsx" required>
								</div>
								<button type="submit" class="btn btn-primary btn-xs wallets-import-btn"><i class="ti-import"></i>&nbsp;{{ _lang('Import') }}</button>
							</form>
						</div>
						<div class="table-responsive">
							<table id="wallets_table_transactions" class="table table-bordered wallets-month-table wallets-export-table" data-export-filename="Wallets_Transactions">
								<thead>
									<tr>
										<th>{{ _lang('ID') }}</th>
										<th>{{ _lang('Name') }}</th>
										@foreach($months as $m)
										<th class="text-right">{{ $m['label'] }}</th>
										@endforeach
										<th class="text-right">{{ _lang('Total') }}</th>
									</tr>
								</thead>
								<tbody>
									@foreach($members as $member)
									@php
										$rowTotal = 0;
										$monthKeys = array_map(function($m) { return $m['year'] . '-' . str_pad($m['month'], 2, '0', STR_PAD_LEFT); }, $months);
										$memberData = $transactionsMonthly[$member->id] ?? [];
									@endphp
									<tr>
										<td>{{ $member->id }}</td>
										<td>{{ $member->first_name }} {{ $member->last_name }}</td>
										@foreach($monthKeys as $key)
											@php $val = $memberData[$key] ?? 0; $rowTotal += $val; @endphp
											<td class="text-right">{{ number_format($val, 0) }}</td>
										@endforeach
										<td class="text-right"><strong>{{ number_format($rowTotal, 0) }}</strong></td>
									</tr>
									@endforeach
								</tbody>
							</table>
						</div>
					</div>

					{{-- Monthly Contributions/Deductions: one row per member, trans types as columns for the selected period --}}
					<div class="tab-pane fade" id="tab-contributions" role="tabpanel">
						<div class="d-flex flex-wrap align-items-center justify-content-between wallets-import-tools">
							<a href="{{ route('wallets.template', ['tab' => 'contributions']) }}" class="btn btn-xs wallets-template-btn">
								<i class="ti-download"></i>&nbsp;{{ _lang('Download Import Template') }}
							</a>
							<form method="post" action="{{ route('wallets.import') }}" enctype="multipart/form-data" class="d-flex flex-wrap align-items-center wallets-import-form">
								@csrf
								<input type="hidden" name="tab" value="contributions">
								<div class="wallets-file-wrap">
									<input type="file" name="file" class="form-control wallets-file-input" accept=".xlsx" required>
								</div>
								<button type="submit" class="btn btn-primary btn-xs wallets-import-btn"><i class="ti-import"></i>&nbsp;{{ _lang('Import') }}</button>
							</form>
						</div>
						<div class="table-responsive">
							<table id="wallets_table_contributions" class="table table-bordered wallets-month-table wallets-export-table" data-export-filename="Wallets_Monthly_Contributions_Deductions">
								<thead>
									<tr>
										<th>{{ _lang('ID') }}</th>
										<th>{{ _lang('Name') }}</th>
										<th class="text-right">{{ _lang('Loan Repayments') }}</th>
										@foreach($accountTypesAndAccounts as $accountType)
										<th class="text-right">{{ $accountType['name'] }}</th>
										@endforeach
										<th class="text-right">{{ _lang('Total') }}</th>
									</tr>
								</thead>
								<tbody>
									@php
										$monthKeys = array_map(function($m) { return $m['year'] . '-' . str_pad($m['month'], 2, '0', STR_PAD_LEFT); }, $months);
									@endphp
									@foreach($members as $member)
										@php
											$loansData = $loansMonthly[$member->id] ?? [];
											$loansTotal = 0;
											foreach ($monthKeys as $key) { $loansTotal += $loansData[$key] ?? 0; }

											$typeTotals = [];
											foreach ($accountTypesAndAccounts as $accountType) {
												$productMonthly = $accountTypeMonthly[$accountType['product_id']] ?? [];
												$memberData = $productMonthly[$member->id] ?? [];
												$t = 0;
												foreach ($monthKeys as $key) { $t += $memberData[$key] ?? 0; }
												$typeTotals[] = $t;
											}

											$rowTotal = $loansTotal + array_sum($typeTotals);
										@endphp
										<tr>
											<td>{{ $member->id }}</td>
											<td>{{ $member->first_name }} {{ $member->last_name }}</td>
											<td class="text-right">{{ number_format($loansTotal, 0) }}</td>
											@foreach($typeTotals as $t)
											<td class="text-right">{{ number_format($t, 0) }}</td>
											@endforeach
											<td class="text-right"><strong>{{ number_format($rowTotal, 0) }}</strong></td>
										</tr>
									@endforeach
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection

@section('js-script')
<script>
$(document).ready(function() {
	function toggleCustomDates() {
		var isCustom = $('#wallets-period').val() === 'custom';
		$('#custom-date-fields').toggle(isCustom);
		if (isCustom) {
			$('#wallets-date1, #wallets-date2').attr('required', true);
		} else {
			$('#wallets-date1, #wallets-date2').removeAttr('required');
		}
	}
	$('#wallets-period').on('change', toggleCustomDates);
	toggleCustomDates();

	var lang = {
		decimal: "",
		emptyTable: typeof $lang_no_data_found !== 'undefined' ? $lang_no_data_found : "No data available",
		info: (typeof $lang_showing !== 'undefined' ? $lang_showing : "Showing") + " _START_ " + (typeof $lang_to !== 'undefined' ? $lang_to : "to") + " _END_ " + (typeof $lang_of !== 'undefined' ? $lang_of : "of") + " _TOTAL_ " + (typeof $lang_entries !== 'undefined' ? $lang_entries : "entries"),
		infoEmpty: typeof $lang_showing_0_to_0_of_0_entries !== 'undefined' ? $lang_showing_0_to_0_of_0_entries : "Showing 0 to 0 of 0 entries",
		infoFiltered: "(filtered from _MAX_ total entries)",
		lengthMenu: (typeof $lang_show !== 'undefined' ? $lang_show : "Show") + " _MENU_ " + (typeof $lang_entries !== 'undefined' ? $lang_entries : "entries"),
		loadingRecords: typeof $lang_loading !== 'undefined' ? $lang_loading : "Loading...",
		processing: typeof $lang_processing !== 'undefined' ? $lang_processing : "Processing...",
		search: typeof $lang_search !== 'undefined' ? $lang_search : "Search:",
		zeroRecords: typeof $lang_no_matching_records_found !== 'undefined' ? $lang_no_matching_records_found : "No matching records found",
		paginate: {
			first: typeof $lang_first !== 'undefined' ? $lang_first : "First",
			last: typeof $lang_last !== 'undefined' ? $lang_last : "Last",
			previous: "<i class='fas fa-angle-left'></i>",
			next: "<i class='fas fa-angle-right'></i>"
		}
	};
	var btnLang = {
		copy: typeof $lang_copy !== 'undefined' ? $lang_copy : "Copy",
		excel: typeof $lang_excel !== 'undefined' ? $lang_excel : "Excel",
		csv: typeof $lang_csv !== 'undefined' ? $lang_csv : "CSV",
		pdf: typeof $lang_pdf !== 'undefined' ? $lang_pdf : "PDF",
		print: typeof $lang_print !== 'undefined' ? $lang_print : "Print"
	};

	function getExportFilename(baseName) {
		var d = new Date();
		var dateStr = d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
		return (baseName || 'Wallets_Export') + '_' + dateStr;
	}

	function initWalletsDataTable(tableId, exportFilename) {
		if ($.fn.DataTable.isDataTable('#' + tableId)) return;
		var fname = getExportFilename(exportFilename);
		$('#' + tableId).DataTable({
			responsive: true,
			bAutoWidth: false,
			ordering: true,
			lengthChange: true,
			dom: "<'row'<'col-sm-12 col-md-6'B><'col-sm-12 col-md-6'f>>" +
				"<'row'<'col-sm-12'tr>>" +
				"<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
			buttons: [
				{ extend: 'excel', text: btnLang.excel, className: 'btn btn-light btn-xs', filename: fname, title: exportFilename || 'Wallets' },
				{ extend: 'csv', text: btnLang.csv, className: 'btn btn-light btn-xs', filename: fname },
				{ extend: 'pdf', text: btnLang.pdf, className: 'btn btn-light btn-xs', filename: fname, title: exportFilename || 'Wallets' },
				{ extend: 'print', text: btnLang.print, className: 'btn btn-light btn-xs', title: exportFilename || 'Wallets' }
			],
			language: lang,
			drawCallback: function() {
				$(".dataTables_paginate > .pagination").addClass("pagination-bordered");
			}
		});
	}

	if (typeof $.fn.DataTable !== 'undefined' && typeof $.fn.DataTable.Buttons !== 'undefined') {
		$('.wallets-export-table').each(function() {
			var $t = $(this);
			var id = $t.attr('id');
			var exportName = $t.attr('data-export-filename') || '';
			if (id) initWalletsDataTable(id, exportName);
		});
		$('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
			var $pane = $($(e.target).attr('href'));
			$pane.find('.wallets-export-table').each(function() {
				if ($.fn.DataTable.isDataTable('#' + this.id)) {
					$('#' + this.id).DataTable().columns.adjust();
				}
			});
		});
	}
});
</script>
@endsection
