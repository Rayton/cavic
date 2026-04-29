@extends('layouts.app')

@section('content')
<div class="row">
	<div class="col-lg-8 offset-lg-2">
		<div class="card cavic-datatable-card">
		    <div class="card-header">
				<span class="header-title">{{ _lang('Branch Details') }}</span>
			</div>
			
			<div class="card-body">
			    <div class="table-responsive">
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
	</div>
</div>
@endsection

