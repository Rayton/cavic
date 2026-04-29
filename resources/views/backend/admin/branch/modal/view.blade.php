<div class="row px-2">
	<div class="col-lg-12">
		<div class="table-responsive cavic-datatable-card">
		<table class="table table-bordered table-striped dashboard-table-compact mb-0">
			<tr><td>{{ _lang('Name') }}</td><td>{{ $branch->name }}</td></tr>
			<tr><td>{{ _lang('Contact Email') }}</td><td>{{ $branch->contact_email }}</td></tr>
			<tr><td>{{ _lang('Contact Phone') }}</td><td>{{ $branch->contact_phone }}</td></tr>
			<tr><td>{{ _lang('Address') }}</td><td>{{ $branch->address }}</td></tr>
			<tr><td>{{ _lang('Descriptions') }}</td><td>{{ $branch->descriptions }}</td></tr>
		</table>
		</div>
	</div>
</div>
