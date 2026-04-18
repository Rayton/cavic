@extends('layouts.app')

@section('content')
<div class="row">
	<div class="col-lg-8 offset-lg-2">
		<div class="card">
		    <div class="card-header d-flex align-items-center">
				<span class="panel-title">{{ _lang('Withdraw Request Details') }}</span>       
			</div>
			<div class="card-body">
                @include('backend.admin.withdraw_request.partials.details')
            </div>
        </div>
    </div>
</div>
@endsection