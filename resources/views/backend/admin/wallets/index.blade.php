@extends('layouts.app')

@section('workspace_top_tabs')
@include('backend.admin.partials.module-tabs', [
    'variant' => 'top-strip',
    'role' => 'navigation',
    'tabs' => array_merge(
        [
            ['label' => _lang('Loans'), 'target' => '#tab-loans', 'active' => true],
        ],
        collect($accountTypesAndAccounts)->map(function ($accountType) {
            return ['label' => $accountType['name'], 'target' => '#tab-' . $accountType['id']];
        })->all(),
        [
            ['label' => _lang('Transactions'), 'target' => '#tab-transactions'],
            ['label' => _lang('Monthly Contributions/Deductions'), 'target' => '#tab-contributions'],
        ]
    ),
])
@endsection

@section('content')
<style>
#custom-date-fields { display: none; }
.wallets-console-card {
	border-radius: 20px;
	border: 1px solid var(--cavic-border, #E7E9E4);
	box-shadow: var(--shadow-soft, 0 8px 24px rgba(31, 41, 55, 0.04));
	overflow: visible;
}
.wallets-console-card .card-header {
	padding: 1.05rem 1.25rem;
	background: var(--cavic-surface, #fff);
	border-bottom: 1px solid var(--cavic-border, #E7E9E4);
}
.wallets-page-title {
	display: flex;
	align-items: center;
	gap: 0.75rem;
	margin: 0;
	color: var(--cavic-text, #2E3338);
	font-size: 1.2rem;
	font-weight: 700;
}
.wallets-page-title i {
	width: 36px;
	height: 36px;
	display: inline-flex;
	align-items: center;
	justify-content: center;
	border-radius: 12px;
	background: var(--cavic-primary-soft, #E7F1F0);
	color: var(--cavic-primary, #3F686D);
	font-size: 1rem;
}
.wallets-page-subtitle {
	margin: 0.25rem 0 0 3rem;
	color: var(--cavic-text-soft, #6F787F);
	font-size: 0.82rem;
}
.wallets-filter-panel {
	padding: 1rem;
	margin-bottom: 1rem;
	border: 1px solid var(--cavic-border, #E7E9E4);
	border-radius: 16px;
	background: #fbfcfb;
}
.wallets-filter-panel .form-group {
	margin-bottom: 0;
}
.wallets-filter-panel .control-label {
	color: var(--cavic-text, #2E3338);
	font-size: 0.78rem;
	font-weight: 700;
}
.wallets-filter-panel .form-control {
	min-height: 40px;
	border-radius: 12px;
	border-color: var(--cavic-border, #E7E9E4);
}
.wallets-filter-panel .btn {
	min-height: 40px;
	border-radius: 12px;
	display: inline-flex;
	align-items: center;
	gap: 0.45rem;
}
.wallets-import-tools {
	gap: 0.75rem;
	padding: 0.78rem 0.9rem;
	margin-bottom: 0.9rem;
	border: 1px solid var(--cavic-border, #E7E9E4);
	border-radius: 16px;
	background: #fbfcfb;
}
.wallets-template-btn {
	min-height: 40px;
	border-color: rgba(63, 104, 109, 0.22);
	color: var(--cavic-primary-dark, #32555A);
	background: var(--cavic-surface, #fff);
	font-weight: 700;
	display: inline-flex;
	align-items: center;
	gap: 0.48rem;
}
.wallets-template-btn:hover {
	background: var(--cavic-primary-soft, #E7F1F0);
	border-color: rgba(63, 104, 109, 0.34);
	color: var(--cavic-primary-dark, #32555A);
}
.wallets-import-form {
	gap: 0.75rem;
}
.wallets-file-wrap {
	position: relative;
	min-width: 280px;
	max-width: 420px;
	flex: 1 1 280px;
}
.wallets-import-form .wallets-file-input {
	height: 40px;
	padding: 0;
	border: 1px dashed rgba(63, 104, 109, 0.32);
	border-radius: 12px;
	background: #fff;
	color: var(--cavic-text-soft, #6F787F);
	font-size: 13px;
	font-weight: 600;
	line-height: 40px;
	overflow: hidden;
	cursor: pointer;
}
.wallets-import-form .wallets-file-input:focus {
	border-color: var(--cavic-primary, #3F686D);
	box-shadow: 0 0 0 0.12rem rgba(63, 104, 109, 0.16);
}
.wallets-import-form .wallets-file-input::file-selector-button {
	height: 40px;
	margin: 0 0.75rem 0 0;
	padding: 0 1rem;
	border: 0;
	border-right: 1px solid rgba(63, 104, 109, 0.18);
	background: var(--cavic-primary-soft, #E7F1F0);
	color: var(--cavic-primary-dark, #32555A);
	font-weight: 700;
	cursor: pointer;
	transition: background 0.15s ease, color 0.15s ease;
}
.wallets-import-form .wallets-file-input::-webkit-file-upload-button {
	height: 40px;
	margin: 0 0.75rem 0 0;
	padding: 0 1rem;
	border: 0;
	border-right: 1px solid rgba(63, 104, 109, 0.18);
	background: var(--cavic-primary-soft, #E7F1F0);
	color: var(--cavic-primary-dark, #32555A);
	font-weight: 700;
	cursor: pointer;
	transition: background 0.15s ease, color 0.15s ease;
}
.wallets-import-form .wallets-file-input:hover::file-selector-button,
.wallets-import-form .wallets-file-input:hover::-webkit-file-upload-button {
	background: var(--cavic-primary, #3F686D);
	color: #fff;
}
.wallets-import-form .wallets-import-btn {
	min-height: 40px;
	padding: 0 14px;
	border-radius: 12px;
	font-weight: 700;
	display: inline-flex;
	align-items: center;
	gap: 0.45rem;
}
.wallets-month-table th, .wallets-month-table td {
	vertical-align: middle !important;
}
.wallets-month-table {
	width: 100% !important;
}
.wallets-month-table th {
	position: relative;
}
.wallets-wide-table {
	min-width: 1880px;
}
.wallets-compact-table {
	min-width: 760px;
}
.dashboard-proof-datatable-card .table-responsive {
	overflow-x: auto;
}
.admin-shell-v2 .dashboard-proof-datatable-card .wallets-month-table th:last-child,
.admin-shell-v2 .dashboard-proof-datatable-card .wallets-month-table td:last-child {
	width: auto;
	text-align: right;
}
@media (max-width: 767px) {
	.wallets-page-subtitle {
		margin-left: 0;
	}
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
		<div class="card wallets-console-card">
			<div class="card-header">
				<h1 class="wallets-page-title"><i class="fas fa-wallet"></i><span>{{ _lang('Wallets') }}</span></h1>
				<p class="wallets-page-subtitle">{{ _lang('Review wallet movement by member, month, account type, and import batch.') }}</p>
			</div>

			<div class="card-body">
				{{-- Filters --}}
				<form method="get" action="{{ request()->url() }}" id="wallets-filter-form" class="wallets-filter-panel">
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
							<button type="submit" class="btn btn-primary btn-xs"><i class="ti-filter"></i><span>{{ _lang('Apply') }}</span></button>
						</div>
					</div>
				</form>


				<div class="tab-content">
					{{-- Loans Tab (monthly = loan repayments per member) --}}
					<div class="tab-pane fade show active" id="tab-loans" role="tabpanel">
						<div class="d-flex flex-wrap align-items-center justify-content-between wallets-import-tools">
							<a href="{{ route('wallets.template', ['tab' => 'loans']) }}" class="btn btn-xs wallets-template-btn">
								<i class="ti-download"></i><span>{{ _lang('Download Import Template') }}</span>
							</a>
							<form method="post" action="{{ route('wallets.import') }}" enctype="multipart/form-data" class="d-flex flex-wrap align-items-center wallets-import-form">
								@csrf
								<input type="hidden" name="tab" value="loans">
								<div class="wallets-file-wrap">
									<input type="file" name="file" class="form-control wallets-file-input" accept=".xlsx" required>
								</div>
								<button type="submit" class="btn btn-primary btn-xs wallets-import-btn"><i class="ti-import"></i><span>{{ _lang('Import') }}</span></button>
							</form>
						</div>
						<div class="dashboard-proof-datatable-card">
							<div class="table-responsive">
							<table id="wallets_table_loans" class="table table-bordered table-striped wallets-month-table wallets-wide-table wallets-export-table dashboard-table-compact mb-0" data-export-filename="Wallets_Loans">
								<thead>
									<tr>
										<th>{{ _lang('ID') }}</th>
										<th>{{ _lang('Name') }}</th>
										<th>{{ _lang('Loan Type') }}</th>
										<th class="text-right">{{ _lang('Total Loan Amount') }}</th>
										<th class="text-right">{{ _lang('Interest') }}</th>
										<th class="text-right">{{ _lang('Balance') }}</th>
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
										$loanSummary = $loanWalletSummaries[$member->id] ?? [
											'loan_type' => _lang('N/A'),
											'total_loan_amount' => 0,
											'interest' => 0,
											'balance' => 0,
										];
									@endphp
									<tr>
										<td>{{ $member->id }}</td>
										<td>{{ $member->first_name }} {{ $member->last_name }}</td>
										<td>{{ $loanSummary['loan_type'] }}</td>
										<td class="text-right">{{ number_format($loanSummary['total_loan_amount'], 0) }}</td>
										<td class="text-right">{{ number_format($loanSummary['interest'], 0) }}</td>
										<td class="text-right">{{ number_format($loanSummary['balance'], 0) }}</td>
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
					</div>

					{{-- Account type tabs (monthly credits per member per product) --}}
					@foreach($accountTypesAndAccounts as $accountType)
					<div class="tab-pane fade" id="tab-{{ $accountType['id'] }}" role="tabpanel">
						<div class="d-flex flex-wrap align-items-center justify-content-between wallets-import-tools">
							<a href="{{ route('wallets.template', ['tab' => 'account', 'product_id' => $accountType['product_id']]) }}" class="btn btn-xs wallets-template-btn">
								<i class="ti-download"></i><span>{{ _lang('Download Import Template') }}</span>
							</a>
							<form method="post" action="{{ route('wallets.import') }}" enctype="multipart/form-data" class="d-flex flex-wrap align-items-center wallets-import-form">
								@csrf
								<input type="hidden" name="tab" value="account">
								<input type="hidden" name="product_id" value="{{ $accountType['product_id'] }}">
								<div class="wallets-file-wrap">
									<input type="file" name="file" class="form-control wallets-file-input" accept=".xlsx" required>
								</div>
								<button type="submit" class="btn btn-primary btn-xs wallets-import-btn"><i class="ti-import"></i><span>{{ _lang('Import') }}</span></button>
							</form>
						</div>
						<div class="dashboard-proof-datatable-card">
							<div class="table-responsive">
							<table id="wallets_table_{{ $accountType['id'] }}" class="table table-bordered table-striped wallets-month-table wallets-wide-table wallets-export-table dashboard-table-compact mb-0" data-export-filename="Wallets_{{ preg_replace('/[^a-zA-Z0-9_-]/', '_', $accountType['name']) }}">
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
					</div>
					@endforeach

					{{-- Transactions Tab (net movement per member per month) --}}
					<div class="tab-pane fade" id="tab-transactions" role="tabpanel">
						<div class="d-flex flex-wrap align-items-center justify-content-between wallets-import-tools">
							<a href="{{ route('wallets.template', ['tab' => 'transactions']) }}" class="btn btn-xs wallets-template-btn">
								<i class="ti-download"></i><span>{{ _lang('Download Import Template') }}</span>
							</a>
							<form method="post" action="{{ route('wallets.import') }}" enctype="multipart/form-data" class="d-flex flex-wrap align-items-center wallets-import-form">
								@csrf
								<input type="hidden" name="tab" value="transactions">
								<div class="wallets-file-wrap">
									<input type="file" name="file" class="form-control wallets-file-input" accept=".xlsx" required>
								</div>
								<button type="submit" class="btn btn-primary btn-xs wallets-import-btn"><i class="ti-import"></i><span>{{ _lang('Import') }}</span></button>
							</form>
						</div>
						<div class="dashboard-proof-datatable-card">
							<div class="table-responsive">
							<table id="wallets_table_transactions" class="table table-bordered table-striped wallets-month-table wallets-wide-table wallets-export-table dashboard-table-compact mb-0" data-export-filename="Wallets_Transactions">
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
					</div>

					{{-- Monthly Contributions/Deductions: one row per member, trans types as columns for the selected period --}}
					<div class="tab-pane fade" id="tab-contributions" role="tabpanel">
						<div class="d-flex flex-wrap align-items-center justify-content-between wallets-import-tools">
							<a href="{{ route('wallets.template', ['tab' => 'contributions']) }}" class="btn btn-xs wallets-template-btn">
								<i class="ti-download"></i><span>{{ _lang('Download Import Template') }}</span>
							</a>
							<form method="post" action="{{ route('wallets.import') }}" enctype="multipart/form-data" class="d-flex flex-wrap align-items-center wallets-import-form">
								@csrf
								<input type="hidden" name="tab" value="contributions">
								<div class="wallets-file-wrap">
									<input type="file" name="file" class="form-control wallets-file-input" accept=".xlsx" required>
								</div>
								<button type="submit" class="btn btn-primary btn-xs wallets-import-btn"><i class="ti-import"></i><span>{{ _lang('Import') }}</span></button>
							</form>
						</div>
						<div class="dashboard-proof-datatable-card">
							<div class="table-responsive">
							<table id="wallets_table_contributions" class="table table-bordered table-striped wallets-month-table wallets-compact-table wallets-export-table dashboard-table-compact mb-0" data-export-filename="Wallets_Monthly_Contributions_Deductions">
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
		window.cavicAdminDataTable('#' + tableId, {
			responsive: false,
			bAutoWidth: false,
			ordering: true,
			lengthChange: true,
			pageLength: 10,
			buttons: [
				{ extend: 'excel', text: '<i class="ti-download"></i><span>' + btnLang.excel + '</span>', className: 'btn btn-xs admin-dt-btn admin-dt-btn-ghost', filename: fname, title: exportFilename || 'Wallets' },
				{ extend: 'csv', text: '<i class="ti-download"></i><span>' + btnLang.csv + '</span>', className: 'btn btn-xs admin-dt-btn admin-dt-btn-ghost', filename: fname },
				{ extend: 'pdf', text: '<i class="ti-download"></i><span>' + btnLang.pdf + '</span>', className: 'btn btn-xs admin-dt-btn admin-dt-btn-ghost', filename: fname, title: exportFilename || 'Wallets' },
				{ extend: 'print', text: '<i class="ti-printer"></i><span>' + btnLang.print + '</span>', className: 'btn btn-xs admin-dt-btn admin-dt-btn-ghost', title: exportFilename || 'Wallets' }
			],
			language: $.extend(true, {}, lang, {
				search: '',
				searchPlaceholder: '{{ _lang('Search wallets') }}',
				lengthMenu: '_MENU_',
				info: '{{ _lang('Viewing') }} _START_-_END_ {{ _lang('of') }} _TOTAL_',
				infoEmpty: '{{ _lang('Viewing 0-0 of 0') }}'
			}),
			initComplete: function () {
				var api = this.api();
				var $wrapper = $(api.table().container());
				var $left = $wrapper.find('.admin-datatable-top-left');
				var $right = $wrapper.find('.admin-datatable-top-right');
				var $top = $wrapper.find('.admin-datatable-top');
				var $length = $left.find('.dataTables_length').detach();
				var $search = $right.find('.dataTables_filter').detach();
				var $buttons = $left.find('.dt-buttons').detach();

				var safeTableId = tableId.replace(/[^a-zA-Z0-9_-]/g, '-');
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

				api.columns().every(function (index) {
					var column = this;
					var label = $(column.header()).text().trim();

					if (!label) {
						return;
					}

					var itemId = safeTableId + '-column-toggle-' + index;
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

				$toolbarLeft.append(
					$('<div class="dashboard-toolbar-item dashboard-toolbar-item-length"></div>').append($length)
				);
				$toolbarCenter.append(
					$('<div class="dashboard-toolbar-item dashboard-toolbar-item-export"></div>').append($buttons)
				);
				$toolbarRight
					.append($('<div class="dashboard-toolbar-item"></div>').append($columnsDropdown))
					.append($('<div class="dashboard-toolbar-item dashboard-toolbar-item-search"></div>').append($search));

				$top.empty().append($toolbarLeft, $toolbarCenter, $toolbarRight);
				$search.find('input').attr('placeholder', '{{ _lang('Search wallets') }}');
				api.columns.adjust();
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
