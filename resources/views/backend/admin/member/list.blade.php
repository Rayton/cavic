@extends('layouts.app')

@section('content')
<div class="row">
	<div class="col-lg-12">
		<div class="card cavic-datatable-card dashboard-proof-datatable-card">
		    <div class="card-header d-flex align-items-center">
				<span class="panel-title">{{ _lang('Member List') }}</span>

				<div class="ml-auto">
					<a class="btn btn-dark btn-xs ajax-modal" href="{{ route('members.import') }}" data-title="{{ _lang('Bulk Import Members') }}" data-fullscreen="true"><i class="ti-import mr-1"></i>{{ _lang('Bulk Import') }}</a>
					<a class="btn btn-primary btn-xs ajax-modal" href="{{ route('members.create') }}" data-title="{{ _lang('Add New Member') }}" data-fullscreen="true"><i class="ti-plus mr-1"></i>{{ _lang('Add New') }}</a>
				</div>
			</div>
			<div class="card-body">
				<div class="table-responsive">
				<table id="members_table" class="table table-bordered table-striped table-export dashboard-table-compact">
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
</div>
@endsection

@section('js-script')
@include('backend.admin.partials.cavic-datatable-standard')
<script>
(function ($) {
	"use strict";

	window.cavicAdminDataTable('#members_table', {
		processing: true,
		serverSide: true,
		ajax: _tenant_url + '/members/get_table_data',
		"columns" : [
			{ data : 'photo', name : 'photo', orderable: false, searchable: false },
			{ data : 'member_no', name : 'member_no' },
			{ data : 'first_name', name : 'first_name' },
			{ data : 'last_name', name : 'last_name' },
			{ data : 'email', name : 'email' },
			{ data : 'branch.name', name : 'branch.name' },
			{ data : "action", name : "action", orderable: false, searchable: false },
		],
		responsive: true,
		"bStateSave": true,
		"bAutoWidth":false,
		"ordering": false,
		pageLength: 10,
		lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
		buttons: [
			{
				extend: 'pdf',
				text: '<i class="ti-download"></i><span>{{ _lang('PDF') }}</span>',
				className: 'btn btn-xs admin-dt-btn admin-dt-btn-ghost',
				filename: 'Members_List',
				title: 'Members List',
				exportOptions: { columns: window.cavicDataTableExportColumns }
			},
			{
				extend: 'excel',
				text: '<i class="ti-download"></i><span>{{ _lang('Excel') }}</span>',
				className: 'btn btn-xs admin-dt-btn admin-dt-btn-ghost',
				filename: 'Members_List',
				title: 'Members List',
				exportOptions: { columns: window.cavicDataTableExportColumns }
			},
			{
				extend: 'csv',
				text: '<i class="ti-download"></i><span>{{ _lang('CSV') }}</span>',
				className: 'btn btn-xs admin-dt-btn admin-dt-btn-ghost',
				filename: 'Members_List',
				exportOptions: { columns: window.cavicDataTableExportColumns }
			}
		],
		"language": {
		   "emptyTable":     "{{ _lang('No Data Found') }}",
		   "info":           "{{ _lang('Viewing') }} _START_-_END_ {{ _lang('of') }} _TOTAL_",
		   "infoEmpty":      "{{ _lang('Viewing 0-0 of 0') }}",
		   "lengthMenu":     "_MENU_",
		   "loadingRecords": "{{ _lang('Loading...') }}",
		   "processing":     "{{ _lang('Processing...') }}",
		   "search":         "",
		   "searchPlaceholder": "{{ _lang('Search records') }}",
		   "zeroRecords":    "{{ _lang('No matching records found') }}",
		   "paginate": {
			  "previous": 	"<i class='fas fa-angle-left'></i>",
        	  "next" : 		"<i class='fas fa-angle-right'></i>"
		  }
		},
		drawCallback: function () {
			$(".dataTables_paginate > .pagination").addClass("pagination-bordered");
			if (typeof TableExportTotals !== 'undefined') TableExportTotals.computeTotals();
		},
		initComplete: function () {
			window.cavicBuildDataTableToolbar(this.api(), $('#members_table'));
		}
	});

})(jQuery);
</script>
@endsection
