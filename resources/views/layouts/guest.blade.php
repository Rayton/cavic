<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title>{{ !isset($page_title) ? get_option('site_title', config('app.name')) : $page_title }}</title>
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="csrf-token" content="{{ csrf_token() }}">
        
		<!-- App favicon -->
        <link rel="shortcut icon" href="{{ get_favicon() }}">

		<!-- App Css -->
        <link rel="stylesheet" href="{{ asset('public/backend/plugins/bootstrap/css/bootstrap.min.css') }}">
		<link rel="stylesheet" href="{{ asset('public/backend/assets/css/fontawesome.css') }}">
		<link rel="stylesheet" href="{{ asset('public/backend/assets/css/themify-icons.css') }}">

		@if(isset(request()->tenant->id))
			@if(get_tenant_option('backend_direction') == "rtl")
			<link rel="stylesheet" href="{{ asset('public/backend/plugins/bootstrap/css/bootstrap-rtl.min.css') }}">
			@endif
		@else
			@if(get_option('backend_direction') == "rtl")
			<link rel="stylesheet" href="{{ asset('public/backend/plugins/bootstrap/css/bootstrap-rtl.min.css') }}">
			@endif
		@endif
		
		<!-- Others css -->
		<link rel="stylesheet" href="{{ asset('public/backend/assets/css/typography.css') }}">
		<link rel="stylesheet" href="{{ asset('public/backend/assets/css/default-css.css') }}">
		<link rel="stylesheet" href="{{ asset('public/backend/assets/css/styles.css?v=1.1') }}">
		<link rel="stylesheet" href="{{ asset('public/backend/assets/css/responsive.css?v=1.0') }}">
		
		<!-- Modernizr -->
		<script src="{{ asset('public/backend/assets/js/vendor/modernizr-3.6.0.min.js') }}"></script>     

		@if(isset(request()->tenant->id))
			@if(get_tenant_option('backend_direction') == "rtl")
			<link rel="stylesheet" href="{{ asset('public/backend/assets/css/rtl/style.css?v=1.0') }}">
			@endif
		@else
			@if(get_option('backend_direction') == "rtl")
			<link rel="stylesheet" href="{{ asset('public/backend/assets/css/rtl/style.css?v=1.0') }}">
			@endif
		@endif
		
		@include('layouts.others.languages')	
    </head>

    <body class="guest">  
		<!-- Main Modal -->
		<div id="main_modal" class="modal" tabindex="-1" role="dialog">
		    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
				<div class="modal-content">
				    <div class="modal-header">
						<h5 class="modal-title ml-2"></h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						  <span aria-hidden="true"><i class="ti-close text-danger"></i></span>
						</button>
				    </div>

				    <div class="alert alert-danger d-none mx-4 mt-3 mb-0"></div>
				    <div class="alert alert-primary d-none mx-4 mt-3 mb-0"></div>
				    <div class="modal-body overflow-hidden"></div>

				</div>
		    </div>
		</div>
	     
		<div class="container my-5">						
			<div class="row">
				<div class="{{ isset($alert_col) ? $alert_col : 'col-lg-12' }}">
					<div class="alert alert-success alert-dismissible" id="main_alert" role="alert">
						<button type="button" id="close_alert" class="close">
							<span aria-hidden="true"><i class="far fa-times-circle"></i></span>
						</button>
						<span class="msg"></span>
					</div>		
				</div>
			</div>		
			
			@if(session('login_as_user') == true && session('admin') != null)
			<div class="row">
				<div class="{{ isset($alert_col) ? $alert_col : 'col-lg-12' }}">
					<div class="alert alert-warning" role="alert">
						<span><i class="fas fa-info-circle mr-2"></i>{{ _lang('Back to admin portal?') }} <a href="{{ route('users.back_to_admin') }}">{{ _lang('Click Here') }}</a></span>
					</div>
				</div>
			</div>
			@endif

			@yield('content')
		</div>


        <!-- jQuery  -->
		<script src="{{ asset('public/backend/assets/js/vendor/jquery-3.7.1.min.js') }}"></script>
		<script src="{{ asset('public/backend/assets/js/popper.min.js') }}"></script>
		<script src="{{ asset('public/backend/plugins/bootstrap/js/bootstrap.min.js') }}"></script> 
		<script src="{{ asset('public/backend/assets/js/print.js') }}"></script>
		<script src="{{ asset('public/backend/assets/js/guest.js') }}"></script>

		@include('layouts.others.alert')
		 
		<!-- Custom JS -->
		@yield('js-script')	
    </body>
</html>