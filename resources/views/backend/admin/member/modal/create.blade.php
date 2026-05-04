<form method="post" class="ajax-screen-submit member-create-modal-form" autocomplete="off" action="{{ route('members.store') }}" enctype="multipart/form-data">
    @csrf
    <div class="member-create-modal">
        <div class="row">
            <div class="col-lg-8">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="control-label">{{ _lang('First Name') }}</label>
                            <input type="text" class="form-control" name="first_name" value="{{ old('first_name') }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="control-label">{{ _lang('Last Name') }}</label>
                            <input type="text" class="form-control" name="last_name" value="{{ old('last_name') }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="control-label">{{ _lang('Business Name') }}</label>
                            <input type="text" class="form-control" name="business_name" value="{{ old('business_name') }}">
                        </div>
                    </div>

                    @if(auth()->user()->user_type == 'admin')
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Branch') }}</label>
                                <select class="form-control select2 auto-select" data-selected="{{ auth()->user()->branch_id }}" name="branch_id">
                                    <option value="">{{ get_tenant_option('default_branch_name', 'Main Branch') }}</option>
                                    @foreach(\App\Models\Branch::all() as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @else
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Branch') }}</label>
                                <select class="form-control auto-select" name="branch_id" data-selected="{{ auth()->user()->branch_id }}" disabled>
                                    <option value="">{{ get_tenant_option('default_branch_name', 'Main Branch') }}</option>
                                    @foreach(\App\Models\Branch::all() as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @endif

                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="control-label">{{ _lang('Email') }}</label>
                            <input type="text" class="form-control" name="email" value="{{ old('email') }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="control-label">{{ _lang('Country Code') }}</label>
                            <select class="form-control select2" name="country_code">
                                <option value="">{{ _lang('Country Code') }}</option>
                                @foreach(get_country_codes() as $key => $value)
                                    <option value="{{ $value['dial_code'] }}" {{ old('country_code') == $value['dial_code'] ? 'selected' : '' }}>{{ $value['country'].' (+'.$value['dial_code'].')' }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="control-label">{{ _lang('Mobile') }}</label>
                            <input type="text" class="form-control" name="mobile" value="{{ old('mobile') }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="control-label">{{ _lang('Gender') }}</label>
                            <select class="form-control auto-select" data-selected="{{ old('gender') }}" name="gender">
                                <option value="">{{ _lang('Select One') }}</option>
                                <option value="male">{{ _lang('Male') }}</option>
                                <option value="female">{{ _lang('Female') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="control-label">{{ _lang('City') }}</label>
                            <input type="text" class="form-control" name="city" value="{{ old('city') }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="control-label">{{ _lang('State') }}</label>
                            <input type="text" class="form-control" name="state" value="{{ old('state') }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="control-label">{{ _lang('Zip') }}</label>
                            <input type="text" class="form-control" name="zip" value="{{ old('zip') }}">
                        </div>
                    </div>

                    @if(! $customFields->isEmpty())
                        @foreach($customFields as $customField)
                            <div class="{{ $customField->field_width }}">
                                <div class="form-group">
                                    <label class="control-label">{{ $customField->field_name }}</label>
                                    {!! xss_clean(generate_input_field($customField)) !!}
                                </div>
                            </div>
                        @endforeach
                    @endif

                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="control-label">{{ _lang('Credit Source') }}</label>
                            <input type="text" class="form-control" name="credit_source" value="{{ old('credit_source') }}">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="control-label">{{ _lang('Address') }}</label>
                            <textarea class="form-control" name="address">{{ old('address') }}</textarea>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="control-label">{{ _lang('Photo') }}</label>
                            <input type="file" class="form-control dropify" name="photo">
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card mb-3">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <span class="header-title">{{ _lang('Member Portal') }}</span>
                        <label class="switch mb-0">
                            <input type="checkbox" class="member-create-client-login" value="1" name="client_login" {{ request()->tenant->package->member_portal != 1 ? 'disabled' : '' }}>
                            <span class="slider"></span>
                        </label>
                    </div>
                    <div class="card-body member-create-client-login-card">
                        @if(request()->tenant->package->member_portal != 1)
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-circle"></i> {{ _lang('Your subscription plan does not include access to the Member Portal.') }}
                            </div>
                        @endif
                        <div class="form-group">
                            <label class="control-label">{{ _lang('Name') }}</label>
                            <input type="text" class="form-control" name="name" value="{{ old('name') }}">
                        </div>
                        <div class="form-group">
                            <label class="control-label">{{ _lang('Email') }}</label>
                            <input type="email" class="form-control" name="login_email" value="{{ old('login_email') }}">
                        </div>
                        <div class="form-group">
                            <label class="control-label">{{ _lang('Password') }}</label>
                            <input type="password" class="form-control" name="password">
                        </div>
                        <div class="form-group mb-0">
                            <label class="control-label">{{ _lang('Status') }}</label>
                            <select class="form-control select2 auto-select" data-selected="{{ old('status') }}" name="status">
                                <option value="">{{ _lang('Select One') }}</option>
                                <option value="1">{{ _lang('Active') }}</option>
                                <option value="0">{{ _lang('In Active') }}</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-right border-top pt-3 mt-2">
            <button type="button" class="btn btn-outline-secondary btn-sm mr-2" data-dismiss="modal">{{ _lang('Cancel') }}</button>
            <button type="submit" class="btn btn-primary btn-sm"><i class="ti-check-box mr-1"></i>{{ _lang('Save Member') }}</button>
        </div>
    </div>
</form>
