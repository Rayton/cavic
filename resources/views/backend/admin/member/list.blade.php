@extends('layouts.app')

@section('content')
<div class="row">
	<div class="col-lg-12">
		<div class="card">
		    <div class="card-header d-flex align-items-center">
				<span class="panel-title">{{ _lang('Member List') }}</span>

				<div class="ml-auto">
					<a class="btn btn-dark btn-xs" href="{{ route('members.import') }}"><i class="ti-import mr-1"></i>{{ _lang('Bulk Import') }}</a>
					<a class="btn btn-primary btn-xs" href="{{ route('members.create') }}"><i class="ti-plus mr-1"></i>{{ _lang('Add New') }}</a>
				</div>
			</div>
			<div class="card-body">
				<table id="members_table" class="table table-bordered table-export">
					<thead>
					    <tr>
							<th data-total-label="{{ _lang('Total') }}" class="text-center">{{ _lang('Photo') }}</th>
							<th>{{ _lang('Member No') }}</th>
						    <th>{{ _lang('First Name') }}</th>
							<th>{{ _lang('Last Name') }}</th>
							<th>{{ _lang('Email') }}</th>
							<th>{{ _lang('Branch') }}</th>
							<th class="text-center" data-no-export="1">{{ _lang('Action') }}</th>
					    </tr>
					</thead>
					<tbody>
					</tbody>
					<tfoot><tr class="table-totals-row"><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr></tfoot>
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

	window.cavicAdminDataTable('#members_table', {
		processing: true,
		serverSide: true,
		ajax: _tenant_url + '/members/get_table_data',
		"columns" : [
			{ data : 'photo', name : 'photo' },
			{ data : 'member_no', name : 'member_no' },
			{ data : 'first_name', name : 'first_name' },
			{ data : 'last_name', name : 'last_name' },
			{ data : 'email', name : 'email' },
			{ data : 'branch.name', name : 'branch.name' },
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
		}
	});

})(jQuery);
</script>
@endsection
