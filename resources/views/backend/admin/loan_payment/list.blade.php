@extends('layouts.app')

@section('content')
<div class="row">
	<div class="col-lg-12">
		<div class="card">
			<div class="card-header d-flex justify-content-between align-items-center">
				<span class="panel-title">{{ _lang('Loan Repayments') }}</span>
				<a class="btn btn-primary btn-xs float-right" href="{{ route('loan_payments.create') }}"><i class="ti-plus"></i>&nbsp;{{ _lang('Add Repayment') }}</a>
			</div>
			<div class="card-body">
				<table id="loan_payments_table" class="table table-bordered table-export">
					<thead>
						<tr>
							<th data-total-label="{{ _lang('Total') }}">{{ _lang('Loan ID') }}</th>
							<th>{{ _lang('Payment Date') }}</th>
							<th class="text-right" data-sum="1">{{ _lang('Principal Amount') }}</th>
							<th class="text-right" data-sum="1">{{ _lang('Interest') }}</th>
							<th class="text-right" data-sum="1">{{ _lang('Late Penalties') }}</th>
							<th class="text-right" data-sum="1">{{ _lang('Total Amount') }}</th>
							<th class="text-center" data-no-export="1">{{ _lang('Action') }}</th>
						</tr>
					</thead>
					<tbody>
					</tbody>
					<tfoot><tr class="table-totals-row"><td></td><td></td><td class="text-right"></td><td class="text-right"></td><td class="text-right"></td><td class="text-right"></td><td></td></tr></tfoot>
				</table>
			</div>
		</div>
	</div>
</div>
@endsection

@section('js-script')
<script>
$(function() {
	"use strict";

	window.cavicAdminDataTable('#loan_payments_table', {
		processing: true,
		serverSide: true,
		ajax: _tenant_url + '/loan_payments/get_table_data',
		"columns" : [
			{ data : 'loan.loan_id', name : 'loan.loan_id' },
			{ data : 'paid_at', name : 'paid_at' },
			{ data : 'repayment_amount', name : 'repayment_amount' },
			{ data : 'interest', name : 'interest' },
			{ data : 'late_penalties', name : 'late_penalties' },
			{ data : 'total_amount', name : 'total_amount' },
			{ data : "action", name : "action" },
		],
		responsive: true,
		"bStateSave": true,
		"bAutoWidth":false,
		"ordering": false,
		"language": {
		   "decimal":        "",
		   "emptyTable":     "{{ _lang('No Data Found') }}",
		   "info":           "{{ _lang('Showing') }} _START_ {{ _lang('to') }} _END_ {{ _lang('of') }} _TOTAL_ {{ _lang('Entries') }}",
		   "infoEmpty":      "{{ _lang('Showing 0 To 0 Of 0 Entries') }}",
		   "infoFiltered":   "(filtered from _MAX_ total entries)",
		   "infoPostFix":    "",
		   "thousands":      ",",
		   "lengthMenu":     "{{ _lang('Show') }} _MENU_ {{ _lang('Entries') }}",
		   "loadingRecords": "{{ _lang('Loading...') }}",
		   "processing":     "{{ _lang('Processing...') }}",
		   "search":         "{{ _lang('Search') }}",
		   "zeroRecords":    "{{ _lang('No matching records found') }}",
		   "paginate": {
			  "first":      "{{ _lang('First') }}",
			  "last":       "{{ _lang('Last') }}",
			  "previous": "<i class='fas fa-angle-left'></i>",
        	  "next" : "<i class='fas fa-angle-right'></i>",
		  }
		},
		drawCallback: function () {
			$(".dataTables_paginate > .pagination").addClass("pagination-bordered");
			if (typeof TableExportTotals !== 'undefined') TableExportTotals.computeTotals();
		},
		footerCallback: function (row, data, start, end, display) {
			var api = this.api();
			var cols = [2, 3, 4, 5];
			$(api.column(0).footer()).html('<strong>{{ _lang("Total") }}</strong>');
			cols.forEach(function (colIdx) {
				var total = api.column(colIdx, { search: 'applied', page: 'current' }).data().reduce(function (a, b) {
					var n = parseFloat(String(b).replace(/[^\d.-]/g, '')) || 0;
					return a + n;
				}, 0);
				$(api.column(colIdx).footer()).html('<strong>' + (total.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 })) + '</strong>');
			});
		}
	});
});
</script>
@endsection
