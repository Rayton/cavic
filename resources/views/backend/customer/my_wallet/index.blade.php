@extends('layouts.app')

@section('content')
<style>
.wallet-action-btns .btn { margin: 0 1px; font-size: 0.75rem; }
</style>
<div class="row">
	<div class="col-lg-12">
		<div class="card">
			<div class="card-header d-flex justify-content-between align-items-center">
				<span class="panel-title">{{ _lang('My Wallet') }}</span>
				<a class="btn btn-primary btn-xs" href="{{ route('loans.apply_loan') }}"><i class="ti-plus"></i>&nbsp;{{ _lang('Request Loan') }}</a>
			</div>

			<div class="card-body">
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
				</ul>

				<div class="tab-content">
					{{-- Loans Tab --}}
					<div class="tab-pane fade show active" id="tab-loans" role="tabpanel">
						<div class="table-responsive">
							<table id="wallet_loans_table" class="table table-bordered wallet-export-table">
								<thead>
									<tr>
										<th>{{ _lang('ID') }}</th>
										<th>{{ _lang('Loan Product') }}</th>
										<th>{{ _lang('Customer') }}</th>
										<th class="text-right">{{ _lang('Amount') }}</th>
										<th class="text-right">{{ _lang('Interest') }}</th>
										<th class="text-right">{{ _lang('Total Loan Amount') }}</th>
										<th class="text-right">{{ _lang('Paid Amount') }}</th>
										<th class="text-right">{{ _lang('Total Amount Balance') }}</th>
										<th>{{ _lang('Release Date') }}</th>
										<th>{{ _lang('Next Payment Date') }}</th>
										<th>{{ _lang('Status') }}</th>
										<th class="text-center">{{ _lang('Action') }}</th>
									</tr>
								</thead>
								<tbody>
									@foreach($loans as $loan)
									@php
										$totalLoanAmount = (float) ($loan->total_payable ?? $loan->applied_amount ?? 0);
										$paidAmount = (float) ($loan->repayment_transactions_sum_amount ?? 0);
										$totalAmountBalance = $totalLoanAmount - $paidAmount;
										$interestRate = $loan->loan_product ? $loan->loan_product->interest_rate : 0;
										$nextPaymentDate = $loan->next_payment && $loan->next_payment->id ? $loan->next_payment->getRawOriginal('repayment_date') : '-';
									@endphp
									<tr>
										<td>{{ $loan->loan_id }}</td>
										<td>{{ $loan->loan_product ? $loan->loan_product->name : '-' }}</td>
										<td>{{ $loan->borrower ? $loan->borrower->first_name . ' ' . $loan->borrower->last_name : '-' }}</td>
										<td class="text-right">{{ decimalPlace($loan->applied_amount, currency($loan->currency ? $loan->currency->name : '')) }}</td>
										<td class="text-right">{{ $interestRate }}%</td>
										<td class="text-right">{{ decimalPlace($totalLoanAmount, currency($loan->currency ? $loan->currency->name : '')) }}</td>
										<td class="text-right">{{ decimalPlace($paidAmount, currency($loan->currency ? $loan->currency->name : '')) }}</td>
										<td class="text-right">{{ decimalPlace($totalAmountBalance, currency($loan->currency ? $loan->currency->name : '')) }}</td>
										<td>{{ $loan->release_date ?? '-' }}</td>
										<td>{{ $nextPaymentDate !== '-' ? \Carbon\Carbon::parse($nextPaymentDate)->format(get_date_format()) : '-' }}</td>
										<td>
											@if($loan->status == 0)
												<a href="#" class="approval-status-link text-decoration-none" data-loan-id="{{ $loan->id }}" data-toggle="modal" data-target="#approvalStatusModal">{!! xss_clean(show_status(_lang('Pending'), 'warning')) !!}</a>
											@elseif($loan->status == 1)
												<a href="#" class="approval-status-link text-decoration-none" data-loan-id="{{ $loan->id }}" data-toggle="modal" data-target="#approvalStatusModal">{!! xss_clean(show_status(_lang('Approved'), 'success')) !!}</a>
											@elseif($loan->status == 2)
												<a href="#" class="approval-status-link text-decoration-none" data-loan-id="{{ $loan->id }}" data-toggle="modal" data-target="#approvalStatusModal">{!! xss_clean(show_status(_lang('Paid'), 'info')) !!}</a>
											@elseif($loan->status == 3)
												<a href="#" class="approval-status-link text-decoration-none" data-loan-id="{{ $loan->id }}" data-toggle="modal" data-target="#approvalStatusModal">{!! xss_clean(show_status(_lang('Cancelled'), 'danger')) !!}</a>
											@else
												-
											@endif
										</td>
										<td class="text-center text-nowrap wallet-action-btns">
											<a href="{{ route('loans.loan_details', $loan->id) }}" class="btn btn-success btn-xs py-0 px-1">{{ _lang('View') }}</a>
											<a href="#" class="btn btn-info btn-xs py-0 px-1 wallet-loan-dialog-link" data-loan-id="{{ $loan->id }}" data-dialog-type="repayments" data-title="{{ _lang('Loan Repayments') }} - {{ $loan->loan_id }}">{{ _lang('Repayments') }}</a>
											<a href="#" class="btn btn-primary btn-xs py-0 px-1 wallet-loan-dialog-link" data-loan-id="{{ $loan->id }}" data-dialog-type="deposits" data-title="{{ _lang('Loan Deposits') }} - {{ $loan->loan_id }}">{{ _lang('Deposits') }}</a>
										</td>
									</tr>
									@endforeach
								</tbody>
							</table>
						</div>
					</div>

					{{-- Account type tabs (Hisa, Jamii, etc.): list by date and amount, no balance --}}
					@foreach($accountTypesAndAccounts as $accountType)
					<div class="tab-pane fade" id="tab-{{ $accountType['id'] }}" role="tabpanel">
						<div class="table-responsive">
							<table id="wallet_table_{{ $accountType['id'] }}" class="table table-bordered wallet-export-table">
								<thead>
									<tr>
										<th>{{ _lang('SN') }}</th>
										<th>{{ _lang('Date') }}</th>
										<th>{{ _lang('Account Number') }}</th>
										<th>{{ _lang('Description') }}</th>
										<th>{{ _lang('DR/CR') }}</th>
										<th class="text-right">{{ _lang('Amount') }}</th>
									</tr>
								</thead>
								<tbody>
									@foreach($accountType['transactions'] ?? [] as $txn)
									@php
										$symbol = $txn->dr_cr == 'dr' ? '-' : '';
										$class  = $txn->dr_cr == 'dr' ? 'text-danger' : 'text-success';
										$currencyName = $txn->account && $txn->account->savings_type && $txn->account->savings_type->currency ? $txn->account->savings_type->currency->name : '';
									@endphp
									<tr>
										<td>{{ $loop->iteration }}</td>
										<td>{{ $txn->trans_date ? \Carbon\Carbon::parse($txn->trans_date)->format(get_date_format()) : '-' }}</td>
										<td>{{ $txn->account ? $txn->account->account_number : '-' }}</td>
										<td>{{ $txn->description ?? '-' }}</td>
										<td>{{ strtoupper($txn->dr_cr ?? '') }}</td>
										<td class="text-right"><span class="{{ $class }}">{{ $symbol }}{{ $symbol ? ' ' : '' }}{{ decimalPlace($txn->amount, currency($currencyName)) }}</span></td>
									</tr>
									@endforeach
								</tbody>
							</table>
						</div>
					</div>
					@endforeach

					{{-- Transactions Tab --}}
					<div class="tab-pane fade" id="tab-transactions" role="tabpanel">
						<div class="table-responsive">
							<table id="wallet_transactions_table" class="table table-bordered wallet-export-table">
								<thead>
									<tr>
										<th>{{ _lang('SN') }}</th>
										<th>{{ _lang('Date') }}</th>
										<th>{{ _lang('Account') }}</th>
										<th>{{ _lang('Description') }}</th>
										<th class="text-right">{{ _lang('Amount') }}</th>
										<th>{{ _lang('DR/CR') }}</th>
										<th>{{ _lang('Type') }}</th>
										<th>{{ _lang('Status') }}</th>
										<th class="text-center">{{ _lang('Details') }}</th>
									</tr>
								</thead>
								<tbody>
									@foreach($transactions as $transaction)
									@php
										$symbol = $transaction->dr_cr == 'dr' ? '-' : '+';
										$class  = $transaction->dr_cr == 'dr' ? 'text-danger' : 'text-success';
										$currencyName = $transaction->account && $transaction->account->savings_type && $transaction->account->savings_type->currency ? $transaction->account->savings_type->currency->name : '';
									@endphp
									<tr>
										<td>{{ $loop->iteration }}</td>
										<td>{{ $transaction->trans_date }}</td>
										<td>{{ $transaction->account ? $transaction->account->account_number . ' - ' . ($transaction->account->savings_type ? $transaction->account->savings_type->name : '') : '-' }}</td>
										<td>{{ $transaction->description ?? '-' }}</td>
										<td class="text-right"><span class="{{ $class }}">{{ $symbol }} {{ decimalPlace($transaction->amount, currency($currencyName)) }}</span></td>
										<td>{{ strtoupper($transaction->dr_cr) }}</td>
										<td>{{ ucwords(str_replace('_', ' ', $transaction->type)) }}</td>
										<td>{!! xss_clean(transaction_status($transaction->status)) !!}</td>
										<td class="text-center"><a href="{{ route('trasnactions.details', $transaction->id) }}" target="_blank" class="btn btn-outline-primary btn-xs">{{ _lang('View') }}</a></td>
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

<!-- Dialog for Loan Repayments / Loan Deposits -->
<div id="wallet_loan_dialog" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="walletLoanDialogTitle" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="walletLoanDialogTitle"></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true"><i class="ti-close text-danger"></i></span>
				</button>
			</div>
			<div class="modal-body overflow-auto">
				<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x"></i><br>{{ _lang('Loading...') }}</div>
			</div>
		</div>
	</div>
</div>

<!-- Approval Status (trails) Modal -->
<div class="modal fade" id="approvalStatusModal" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">{{ _lang('Loan Approval Status') }}</h5>
				<button type="button" class="close" data-dismiss="modal">
					<span>&times;</span>
				</button>
			</div>
			<div class="modal-body" id="approvalStatusContent">
				<!-- Content loaded by JS -->
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">{{ _lang('Close') }}</button>
			</div>
		</div>
	</div>
</div>
@endsection

@section('js-script')
@php
	$loansApprovalData = $loans->map(function($loan) {
		$currencyName = $loan->currency ? $loan->currency->name : '';
		return [
			'id' => $loan->id,
			'loan_id' => $loan->loan_id,
			'loan_product' => $loan->loan_product ? $loan->loan_product->name : null,
			'currency' => $currencyName,
			'amount_formatted' => decimalPlace($loan->applied_amount ?? 0, currency($currencyName)),
			'approvals' => $loan->approvals->map(function($approval) {
				return [
					'approval_level' => $approval->approval_level,
					'approval_level_name' => $approval->approval_level_name,
					'status' => $approval->status,
					'remarks' => $approval->remarks,
					'approved_at' => $approval->formatted_approved_at,
					'approver' => $approval->approver ? [
						'name' => $approval->approver->first_name . ' ' . $approval->approver->last_name,
						'member_no' => $approval->approver->member_no ?? 'N/A'
					] : null
				];
			})->values()
		];
	})->keyBy('id');
@endphp
<script>
$(document).ready(function() {
	var loansApprovalData = @json($loansApprovalData);
	// DataTables with export buttons (Excel, CSV, PDF) for each wallet table
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

	function initWalletDataTable(tableId) {
		if ($.fn.DataTable.isDataTable('#' + tableId)) return;
		var table = $('#' + tableId).DataTable({
			responsive: true,
			bAutoWidth: false,
			ordering: true,
			lengthChange: true,
			dom: "<'row'<'col-sm-12 col-md-6'B><'col-sm-12 col-md-6'f>>" +
				"<'row'<'col-sm-12'tr>>" +
				"<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
			buttons: [
				{ extend: 'excel', text: btnLang.excel, className: 'btn btn-light btn-xs' },
				{ extend: 'csv', text: btnLang.csv, className: 'btn btn-light btn-xs' },
				{ extend: 'pdf', text: btnLang.pdf, className: 'btn btn-light btn-xs' },
				{ extend: 'print', text: btnLang.print, className: 'btn btn-light btn-xs' }
			],
			language: lang,
			drawCallback: function() {
				$(".dataTables_paginate > .pagination").addClass("pagination-bordered");
			}
		});
		return table;
	}

	// Initialize all wallet tables (loans, each account type tab, transactions)
	if (typeof $.fn.DataTable.Buttons !== 'undefined') {
		$('.wallet-export-table').each(function() {
			var id = $(this).attr('id');
			if (id) initWalletDataTable(id);
		});

		// Adjust column widths when switching tabs
		$('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
			var $pane = $($(e.target).attr('href'));
			$pane.find('.wallet-export-table').each(function() {
				if ($.fn.DataTable.isDataTable('#' + this.id)) {
					$('#' + this.id).DataTable().columns.adjust();
				}
			});
		});
	} else {
		$('.wallet-export-table').addClass('data-table');
	}

	// View Repayments / View Deposits: build URL from current page location so it matches server (avoids 404 with subdir/tenant)
	var walletDialogLoading = "{{ _lang('Loading...') }}";
	var walletDialogError = "{{ _lang('Error loading content.') }}";
	// Base URL for portal (same as current page) so AJAX hits correct path — e.g. .../tenant/portal when on .../tenant/portal/my_wallet
	function getWalletPortalBase() {
		var path = window.location.pathname || '';
		var i = path.indexOf('/my_wallet');
		if (i !== -1) return window.location.origin + path.substring(0, i);
		i = path.indexOf('/portal');
		if (i !== -1) return window.location.origin + path.substring(0, i + 7);
		return (typeof _tenant_url !== 'undefined' && _tenant_url) ? _tenant_url + '/portal' : (window.location.origin + '/portal');
	}
	$(document).on('click', '.wallet-loan-dialog-link', function(e) {
		e.preventDefault();
		var loanId = $(this).data('loan-id');
		var type = $(this).data('dialog-type'); // 'repayments' or 'deposits'
		if (!loanId || !type) return false;
		var url = getWalletPortalBase() + '/my_wallet/loan/' + loanId + '/' + type;
		var title = $(this).data('title');
		var $dialog = $('#wallet_loan_dialog');
		$dialog.find('#walletLoanDialogTitle').text(title);
		$dialog.find('.modal-body').html('<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x"></i><br>' + walletDialogLoading + '</div>');
		$dialog.modal('show');
		$.ajax({
			url: url,
			type: 'GET',
			headers: { 'X-Requested-With': 'XMLHttpRequest' }
		}).done(function(data) {
			$dialog.find('.modal-body').html(data);
		}).fail(function(xhr) {
			var msg = walletDialogError;
			if (xhr.status) msg += ' (HTTP ' + xhr.status + ')';
			$dialog.find('.modal-body').html('<div class="alert alert-danger">' + msg + '</div>');
		});
		return false;
	});

	// Approval trails modal: show approval status when status badge is clicked
	$(document).on('click', '.approval-status-link', function(e) {
		e.preventDefault();
		var loanId = $(this).data('loan-id');
		var loanData = loansApprovalData[loanId];
		var $content = $('#approvalStatusContent');
		if (!loanData) {
			$content.html('<p class="text-muted">' + (typeof $lang_no_data_found !== 'undefined' ? $lang_no_data_found : 'No data found') + '</p>');
			return;
		}
		var approvals = loanData.approvals || [];
		var html = '<div class="approval-status-container">';
		html += '<div class="row mb-3"><div class="col-md-12">';
		html += '<h6><strong>' + (loanData.loan_id || 'N/A') + '</strong></h6>';
		html += '<p class="text-muted mb-0">' + (loanData.loan_product || 'N/A') + ' - ' + (loanData.currency || 'N/A') + '</p>';
		html += '<p class="mb-0 mt-1"><strong>{{ _lang("Loan Amount") }}:</strong> ' + (loanData.amount_formatted || '-') + '</p>';
		html += '</div></div>';
		html += '<table class="table table-bordered"><thead><tr>';
		html += '<th>{{ _lang("Approval Level") }}</th><th>{{ _lang("Approver") }}</th><th>{{ _lang("Status") }}</th><th>{{ _lang("Date") }}</th><th>{{ _lang("Remarks") }}</th>';
		html += '</tr></thead><tbody>';
		var levels = [
			{ level: 1, name: '{{ _lang("Trustee 1") }}' },
			{ level: 2, name: '{{ _lang("Trustee 2") }}' },
			{ level: 3, name: '{{ _lang("Secretary") }}' },
			{ level: 4, name: '{{ _lang("Chairman") }}' }
		];
		levels.forEach(function(levelInfo) {
			var approval = approvals.find(function(a) { return a.approval_level == levelInfo.level; });
			html += '<tr><td><strong>' + levelInfo.name + '</strong></td>';
			if (approval && approval.approver) {
				html += '<td>' + (approval.approver.name || 'N/A') + ' (' + (approval.approver.member_no || 'N/A') + ')</td>';
			} else {
				html += '<td class="text-muted">{{ _lang("Not Assigned") }}</td>';
			}
			if (approval) {
				if (approval.status == 1) html += '<td><span class="badge badge-success">{{ _lang("Approved") }}</span></td>';
				else if (approval.status == 2) html += '<td><span class="badge badge-danger">{{ _lang("Rejected") }}</span></td>';
				else html += '<td><span class="badge badge-warning">{{ _lang("Pending") }}</span></td>';
				html += '<td>' + (approval.approved_at || '<span class="text-muted">-</span>') + '</td>';
				html += '<td>' + (approval.remarks || '<span class="text-muted">-</span>') + '</td>';
			} else {
				html += '<td><span class="badge badge-secondary">{{ _lang("Not Started") }}</span></td><td class="text-muted">-</td><td class="text-muted">-</td>';
			}
			html += '</tr>';
		});
		html += '</tbody></table>';
		var approvedCount = approvals.filter(function(a) { return a.status == 1; }).length;
		var totalCount = 4;
		var percentage = totalCount ? (approvedCount / totalCount) * 100 : 0;
		html += '<div class="mt-3"><h6>{{ _lang("Approval Progress") }}</h6>';
		html += '<div class="progress" style="height: 30px;"><div class="progress-bar" role="progressbar" style="width: ' + percentage + '%">' + approvedCount + '/' + totalCount + ' {{ _lang("Approved") }}</div></div></div>';
		html += '</div>';
		$content.html(html);
	});
});
</script>
@endsection
