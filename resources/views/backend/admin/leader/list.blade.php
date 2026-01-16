@extends('layouts.app')

@section('content')
<div class="row">
	<div class="col-lg-12">
		<div class="card">
			<div class="card-header d-flex align-items-center">
				<span class="panel-title">{{ _lang('Leaders Management') }}</span>
				<div class="ml-auto">
					<a class="btn btn-primary btn-xs ajax-modal" href="{{ route('leaders.create', ['tenant' => request()->tenant->slug, 'position' => 'secretary']) }}" data-title="{{ _lang('Add Secretary') }}">
						<i class="ti-plus"></i>&nbsp;{{ _lang('Add Secretary') }}
					</a>
					<a class="btn btn-primary btn-xs ajax-modal" href="{{ route('leaders.create', ['tenant' => request()->tenant->slug, 'position' => 'chairman']) }}" data-title="{{ _lang('Add Chairman') }}">
						<i class="ti-plus"></i>&nbsp;{{ _lang('Add Chairman') }}
					</a>
				</div>
			</div>
			<div class="card-body">
				<!-- Secretaries Section -->
				<h5 class="mb-3">{{ _lang('Secretaries') }}</h5>
				@if($secretaries->count() > 0)
				<table class="table table-bordered mb-4">
					<thead>
						<tr>
							<th>{{ _lang('Leader') }}</th>
							<th>{{ _lang('Status') }}</th>
							<th class="text-center">{{ _lang('Action') }}</th>
						</tr>
					</thead>
					<tbody>
						@foreach($secretaries as $leader)
						<tr>
							<td>
								@if($leader->member)
									{{ $leader->member->name }} ({{ $leader->member->member_no }})
								@else
									<span class="text-muted">{{ _lang('Not Assigned') }}</span>
								@endif
							</td>
							<td>
								@if($leader->status == 1)
									<span class="badge badge-success">{{ _lang('Active') }}</span>
								@else
									<span class="badge badge-secondary">{{ _lang('Inactive') }}</span>
								@endif
							</td>
							<td class="text-center">
								<a href="{{ route('leaders.edit', ['tenant' => request()->tenant->slug, 'id' => $leader->id]) }}" 
								   data-title="{{ _lang('Edit Leader') }}" 
								   class="btn btn-primary btn-xs ajax-modal">
									<i class="ti-pencil-alt"></i>&nbsp;{{ _lang('Edit') }}
								</a>
								<a href="{{ route('leaders.destroy', ['tenant' => request()->tenant->slug, 'id' => $leader->id]) }}" 
								   class="btn btn-danger btn-xs confirm-alert" 
								   data-message="{{ _lang('Are you sure?') }}">
									<i class="ti-trash"></i>&nbsp;{{ _lang('Delete') }}
								</a>
							</td>
						</tr>
						@endforeach
					</tbody>
				</table>
				@else
				<div class="alert alert-info mb-4">{{ _lang('No secretaries added yet') }}</div>
				@endif

				<!-- Chairmen Section -->
				<h5 class="mb-3">{{ _lang('Chairmen') }}</h5>
				@if($chairmen->count() > 0)
				<table class="table table-bordered">
					<thead>
						<tr>
							<th>{{ _lang('Leader') }}</th>
							<th>{{ _lang('Status') }}</th>
							<th class="text-center">{{ _lang('Action') }}</th>
						</tr>
					</thead>
					<tbody>
						@foreach($chairmen as $leader)
						<tr>
							<td>
								@if($leader->member)
									{{ $leader->member->name }} ({{ $leader->member->member_no }})
								@else
									<span class="text-muted">{{ _lang('Not Assigned') }}</span>
								@endif
							</td>
							<td>
								@if($leader->status == 1)
									<span class="badge badge-success">{{ _lang('Active') }}</span>
								@else
									<span class="badge badge-secondary">{{ _lang('Inactive') }}</span>
								@endif
							</td>
							<td class="text-center">
								<a href="{{ route('leaders.edit', ['tenant' => request()->tenant->slug, 'id' => $leader->id]) }}" 
								   data-title="{{ _lang('Edit Leader') }}" 
								   class="btn btn-primary btn-xs ajax-modal">
									<i class="ti-pencil-alt"></i>&nbsp;{{ _lang('Edit') }}
								</a>
								<a href="{{ route('leaders.destroy', ['tenant' => request()->tenant->slug, 'id' => $leader->id]) }}" 
								   class="btn btn-danger btn-xs confirm-alert" 
								   data-message="{{ _lang('Are you sure?') }}">
									<i class="ti-trash"></i>&nbsp;{{ _lang('Delete') }}
								</a>
							</td>
						</tr>
						@endforeach
					</tbody>
				</table>
				@else
				<div class="alert alert-info">{{ _lang('No chairmen added yet') }}</div>
				@endif
			</div>
		</div>
	</div>
</div>
@endsection
