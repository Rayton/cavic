@extends('layouts.app')

@section('content')
<div class="row">
	<div class="col-lg-12">
		<div class="card">
			<div class="card-header d-flex align-items-center">
				<span class="panel-title">{{ _lang('Loan Approver Settings') }}</span>
				<div class="ml-auto">
					<a class="btn btn-primary btn-xs ajax-modal" href="{{ route('loan_approver_settings.create', ['tenant' => request()->tenant->slug, 'level' => 1]) }}" data-title="{{ _lang('Configure Approvers') }}">
						<i class="ti-plus"></i>&nbsp;{{ _lang('Configure Approvers') }}
					</a>
				</div>
			</div>
			<div class="card-body">
				<table class="table table-bordered">
					<thead>
						<tr>
							<th>{{ _lang('Approval Level') }}</th>
							<th>{{ _lang('Approver') }}</th>
							<th>{{ _lang('Status') }}</th>
							<th class="text-center">{{ _lang('Action') }}</th>
						</tr>
					</thead>
					<tbody>
						@foreach($settings as $setting)
						<tr>
							<td>
								<strong>{{ _lang($setting->approval_level_name) }}</strong>
								<br><small class="text-muted">{{ _lang('Level') }} {{ $setting->approval_level }}</small>
							</td>
							<td>
								@if($setting->approver)
									{{ $setting->approver->name }} ({{ $setting->approver->member_no }})
								@else
									<span class="text-muted">{{ _lang('Not Assigned') }}</span>
								@endif
							</td>
							<td>
								@if($setting->status == 1)
									<span class="badge badge-success">{{ _lang('Active') }}</span>
								@else
									<span class="badge badge-secondary">{{ _lang('Inactive') }}</span>
								@endif
							</td>
							<td class="text-center">
								<a href="{{ route('loan_approver_settings.edit', ['tenant' => request()->tenant->slug, 'id' => $setting->id]) }}" 
								   data-title="{{ _lang('Edit Approver Setting') }}" 
								   class="btn btn-primary btn-xs ajax-modal">
									<i class="ti-pencil-alt"></i>&nbsp;{{ _lang('Edit') }}
								</a>
							</td>
						</tr>
						@endforeach
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
@endsection
