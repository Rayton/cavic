@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-lg-8 offset-lg-2">
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <span class="panel-title">{{ isset($setting->id) ? _lang('Edit Approver Setting') : _lang('Configure Approver Setting') }}</span>
                <a href="{{ route('loan_approver_settings.index') }}" class="btn btn-outline-primary btn-xs ml-auto">{{ _lang('Back to Approver Settings') }}</a>
            </div>
            <div class="card-body">
                @include('backend.admin.loan.modal.approver_setting')
            </div>
        </div>
    </div>
</div>
@endsection
