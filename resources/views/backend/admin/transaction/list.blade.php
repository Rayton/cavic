@extends('layouts.app')

@section('content')
<div class="row">
	<div class="col-lg-12">
		<div class="card">
		    <div class="card-header d-flex align-items-center">
				<span class="panel-title">{{ _lang('Transaction History') }}</span>
				<a class="btn btn-primary btn-xs ml-auto" href="{{ route('transactions.create') }}"><i class="ti-plus"></i>&nbsp;{{ _lang('Add New') }}</a>
			</div>
			<div class="card-body">
				<table id="transactions_table" class="table table-bordered table-export">
					<thead>
					    <tr>
						    <th data-total-label="{{ _lang('Total') }}">{{ _lang('Date') }}</th>
							<th>{{ _lang('Member') }}</th>
							<th>{{ _lang('Account Number') }}</th>
							<th class="text-right" data-sum="1">{{ _lang('Amount') }}</th>
							<th>{{ _lang('Debit/Credit') }}</th>
							<th>{{ _lang('Type') }}</th>
							<th>{{ _lang('Status') }}</th>
							<th class="text-center" data-no-export="1">{{ _lang('Action') }}</th>
					    </tr>
					</thead>
					<tbody>
					</tbody>
					<tfoot><tr class="table-totals-row"><td></td><td></td><td></td><td class="text-right"></td><td></td><td></td><td></td><td></td></tr></tfoot>
				</table>
			</div>
		</div>
	</div>
</div>
@endsection

@section('js-script')
<script>
(function ($) {
	"use strict";

	$('#transactions_table').DataTable({
		processing: true,
		serverSide: true,
		ajax: _tenant_url + '/transactions/get_table_data',
		"columns" : [
			{ data : 'trans_date', name : 'trans_date' },
			{ data : 'member.first_name', name : 'member.first_name' },
			{ data : 'account.account_number', name : 'account.account_number', defaultContent: '' },
			{ data : 'amount', name : 'amount' },
			{ data : 'dr_cr', name : 'dr_cr' },
			{ data : 'type', name : 'type' },
			{ data : 'status', name : 'status' },
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
			  "previous": 	"<i class='fas fa-angle-left'></i>",
        	  "next" : 		"<i class='fas fa-angle-right'></i>",
		  }
		},
		drawCallback: function () {
			$(".dataTables_paginate > .pagination").addClass("pagination-bordered");
			if (typeof TableExportTotals !== 'undefined') TableExportTotals.computeTotals();
		},
		footerCallback: function (row, data, start, end, display) {
			var api = this.api();
			var colAmount = 3;
			var total = api.column(colAmount, { search: 'applied', page: 'current' }).data().reduce(function (a, b) {
				var n = parseFloat(String(b).replace(/[^\d.-]/g, '')) || 0;
				return a + n;
			}, 0);
			$(api.column(colAmount).footer()).html('<strong>' + (total.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 })) + '</strong>');
			$(api.column(0).footer()).html('<strong>{{ _lang("Total") }}</strong>');
		}
	});
})(jQuery);
</script>
@endsection
