<form method="post" class="ajax-screen-submit" autocomplete="off" action="{{ isset($setting->id) ? route('loan_approver_settings.update', ['tenant' => request()->tenant->slug, 'id' => $setting->id]) : route('loan_approver_settings.store', request()->tenant->slug) }}" enctype="multipart/form-data">
	@csrf
	@if(isset($setting->id))
		@method('PUT')
	@endif
	
	<div class="row px-2">
		<div class="col-md-12">
			<div class="form-group">
				<label class="control-label">{{ _lang('Approval Level') }}</label>
				<input type="text" class="form-control" value="{{ _lang($setting->approval_level_name ?? 'Level ' . $level) }}" disabled>
				@if(!isset($setting->id))
					<input type="hidden" name="approval_level" value="{{ $level ?? $setting->approval_level }}">
				@endif
			</div>
		</div>

		<div class="col-md-12">
			<div class="form-group">
				<label class="control-label">{{ _lang('Approver') }} <span class="text-danger">*</span></label>
				<select class="form-control auto-select select2" data-selected="{{ $setting->approver_member_id ?? old('approver_member_id') }}" name="approver_member_id" required>
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
				<select class="form-control auto-select" data-selected="{{ $setting->status ?? old('status', 1) }}" name="status" required>
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
