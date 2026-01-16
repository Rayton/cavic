<form method="post" class="ajax-screen-submit" autocomplete="off" action="{{ isset($leader->id) ? route('leaders.update', ['tenant' => request()->tenant->slug, 'id' => $leader->id]) : route('leaders.store', request()->tenant->slug) }}" enctype="multipart/form-data">
	@csrf
	@if(isset($leader->id))
		@method('PUT')
	@endif
	
	<div class="row px-2">
		<div class="col-md-12">
			<div class="form-group">
				<label class="control-label">{{ _lang('Position') }}</label>
				<input type="text" class="form-control" value="{{ _lang(ucfirst($position ?? $leader->position)) }}" disabled>
				@if(!isset($leader->id))
					<input type="hidden" name="position" value="{{ $position ?? $leader->position }}">
				@endif
			</div>
		</div>

		<div class="col-md-12">
			<div class="form-group">
				<label class="control-label">{{ _lang('Leader') }} <span class="text-danger">*</span></label>
				<select class="form-control auto-select select2" data-selected="{{ $leader->member_id ?? old('member_id') }}" name="member_id" required>
					<option value="">{{ _lang('Select Member') }}</option>
					@foreach($members as $member)
					<option value="{{ $member->id }}">{{ $member->name }} ({{ $member->member_no }})</option>
					@endforeach
				</select>
			</div>
		</div>

		<div class="col-md-12">
			<div class="form-group">
				<label class="control-label">{{ _lang('Status') }} <span class="text-danger">*</span></label>
				<select class="form-control auto-select" data-selected="{{ $leader->status ?? old('status', 1) }}" name="status" required>
					<option value="1">{{ _lang('Active') }}</option>
					<option value="0">{{ _lang('Inactive') }}</option>
				</select>
			</div>
		</div>

		<div class="col-md-12 mt-2">
			<div class="form-group">
				<button type="submit" class="btn btn-primary btn-block"><i class="ti-check-box mr-2"></i>{{ _lang('Save') }}</button>
			</div>
		</div>
	</div>
</form>
