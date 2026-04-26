<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        @php
            $fallbackTitle = app()->bound('tenant')
                ? get_tenant_option('business_name', get_option('site_title', config('app.name')))
                : get_option('site_title', config('app.name'));
            $fallbackTitle = trim((string) $fallbackTitle);
            $routeName = request()->route()?->getName();
            $routeParts = $routeName ? explode('.', $routeName) : [];
            $ignoredRouteParts = ['admin', 'index', 'list', 'filter', 'store', 'update', 'destroy'];
            $actionRouteParts = ['create', 'edit', 'show', 'view'];
            $routeAction = end($routeParts) ?: null;
            $titleSource = null;

            if (isset($page_title) && trim((string) $page_title) !== '' && trim((string) $page_title) !== '-') {
                $titleSource = trim((string) $page_title);
            }

            if ($titleSource === null && $routeName) {
                $titlePart = null;

                if (in_array($routeAction, $actionRouteParts, true) && count($routeParts) >= 2) {
                    $resourcePart = $routeParts[count($routeParts) - 2];
                    $resourceLabel = \Illuminate\Support\Str::headline(str_replace(['-', '_'], ' ', \Illuminate\Support\Str::singular($resourcePart)));
                    $actionLabel = \Illuminate\Support\Str::headline($routeAction);
                    $titlePart = $actionLabel . ' ' . $resourceLabel;
                } else {
                    $usableParts = array_values(array_filter($routeParts, function ($part) use ($ignoredRouteParts) {
                        return !in_array($part, $ignoredRouteParts, true);
                    }));
                    $titlePart = end($usableParts) ?: null;
                }

                if ($titlePart) {
                    $titleSource = \Illuminate\Support\Str::headline(str_replace(['-', '_'], ' ', $titlePart));
                }
            }

            if ($titleSource === null) {
                $segments = request()->segments();
                $tenantSlug = app()->bound('tenant') ? app('tenant')->slug : null;
                $usableSegments = array_values(array_filter($segments, function ($segment) use ($tenantSlug) {
                    return $segment !== $tenantSlug && !is_numeric($segment);
                }));
                $segmentTitle = end($usableSegments) ?: null;
                $titleSource = $segmentTitle ? \Illuminate\Support\Str::headline(str_replace(['-', '_'], ' ', $segmentTitle)) : null;
            }

            if ($titleSource === null || $titleSource === '' || $titleSource === '-') {
                $titleSource = $fallbackTitle !== '' && $fallbackTitle !== '-' ? $fallbackTitle : config('app.name');
            }

            $resolvedPageTitle = $titleSource;
            $resolvedTitle = $resolvedPageTitle;
        @endphp
        <title>{{ $resolvedTitle }}</title>
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="csrf-token" content="{{ csrf_token() }}">

		<!-- App favicon -->
        <link rel="shortcut icon" href="{{ get_favicon() }}">
		<link href="{{ asset('public/backend/plugins/dropify/css/dropify.min.css') }}" rel="stylesheet">
		<link href="{{ asset('public/backend/plugins/sweet-alert2/css/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
        <link href="{{ asset('public/backend/plugins/animate/animate.css') }}" rel="stylesheet" type="text/css">
		<link href="{{ asset('public/backend/plugins/select2/css/select2.min.css') }}" rel="stylesheet" type="text/css" />
	    <link href="{{ asset('public/backend/plugins/jquery-toast-plugin/jquery.toast.min.css') }}" rel="stylesheet" />
		<link href="{{ asset('public/backend/plugins/daterangepicker/daterangepicker.css') }}" rel="stylesheet" />

		<!-- App Css -->
        <link rel="stylesheet" href="{{ asset('public/backend/plugins/bootstrap/css/bootstrap.min.css') }}">
		<link rel="stylesheet" href="{{ asset('public/backend/assets/css/fontawesome.css') }}">
		<link rel="stylesheet" href="{{ asset('public/backend/assets/css/themify-icons.css') }}">
		<link rel="stylesheet" href="{{ asset('public/backend/plugins/metisMenu/metisMenu.css') }}">

		@if(isset(request()->tenant->id))
			@if(get_tenant_option('backend_direction') == "rtl")
			<link rel="stylesheet" href="{{ asset('public/backend/plugins/bootstrap/css/bootstrap-rtl.min.css') }}">
			@endif
		@else
			@if(get_option('backend_direction') == "rtl")
			<link rel="stylesheet" href="{{ asset('public/backend/plugins/bootstrap/css/bootstrap-rtl.min.css') }}">
			@endif
		@endif

		<!-- Conditionals CSS -->
		@include('layouts.others.import-css')

		<!-- Others css -->
		<link rel="stylesheet" href="{{ asset('public/backend/assets/css/typography.css') }}">
		<link rel="stylesheet" href="{{ asset('public/backend/assets/css/default-css.css') }}">
		<link rel="stylesheet" href="{{ asset('public/backend/assets/css/styles.css') . '?v=' . filemtime(public_path('backend/assets/css/styles.css')) }}">
		<link rel="stylesheet" href="{{ asset('public/backend/assets/css/responsive.css?v=1.0') }}">

		<!-- Dashboard Deposit button: filled by default -->
		<style>.btn-deposit-header { background: #1A8E8F !important; border: 1px solid #1A8E8F !important; color: #fff !important; border-radius: 6px; transition: background 0.2s, color 0.2s; }.btn-deposit-header:hover { background: #157a7b !important; border-color: #157a7b !important; color: #fff !important; }</style>

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

    @php
        $authUser = auth()->user();
        $userType = $authUser?->user_type;
        $isAdminWorkspace = $userType === 'admin';
        $tenantDisplayName = app()->bound('tenant')
            ? app('tenant')->name
            : get_option('site_title', config('app.name'));
        $profileNameParts = [];

        if ($authUser?->user_type === 'customer' && $authUser?->member) {
            $profileNameParts = array_filter([
                trim((string) $authUser->member->first_name),
                trim((string) $authUser->member->last_name),
            ]);
        }

        $profileDisplayName = trim(
            count($profileNameParts) > 0
                ? implode(' ', $profileNameParts)
                : (string) ($authUser?->name ?? '')
        );

        if ($profileDisplayName === '') {
            $profileDisplayName = $tenantDisplayName ?: _lang('User');
        }

        $profileImageFile = $authUser?->profile_picture;

        if (
            ($profileImageFile === null || $profileImageFile === '' || in_array($profileImageFile, ['default.png', 'avatar.png']))
            && $authUser?->user_type === 'customer'
            && $authUser?->member
            && filled($authUser->member->photo)
            && ! in_array($authUser->member->photo, ['default.png', 'avatar.png'])
        ) {
            $profileImageFile = $authUser->member->photo;
        }

        $hasCustomProfileImage = filled($profileImageFile)
            && ! in_array($profileImageFile, ['default.png', 'avatar.png']);

        $profileAvatarUrl = $hasCustomProfileImage ? profile_picture($profileImageFile) : null;

        $profileInitialParts = array_values(array_filter(preg_split('/\s+/', $profileDisplayName)));
        $profileInitials = collect(array_slice($profileInitialParts, 0, 2))
            ->map(function ($part) {
                return strtoupper(substr($part, 0, 1));
            })
            ->implode('');

        if ($profileInitials === '') {
            $profileInitials = 'U';
        }
    @endphp

    <body class="backend-app user-type-{{ $userType ?? 'guest' }} {{ $isAdminWorkspace ? 'admin-shell-v2' : '' }}">
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

		<!-- Secondary Modal -->
		<div id="secondary_modal" class="modal" tabindex="-1" role="dialog">
		    <div class="modal-dialog modal-dialog-centered" role="document">
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

		<!-- Preloader area start -->
		<div id="preloader">
			<div class="lds-spinner"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>
		</div>
		<!-- Preloader area end -->

		@php $user_type = auth()->user()->user_type; @endphp

		<div class="page-container {{ $isAdminWorkspace ? 'admin-shell-container' : '' }}">
		    <!-- sidebar menu area start -->
			<div class="sidebar-menu {{ $isAdminWorkspace ? 'admin-sidebar-v2' : '' }}" style="display: flex; flex-direction: column;">
				<div class="extra-details {{ $isAdminWorkspace ? 'admin-sidebar-brand' : '' }}">
					<a href="{{ $user_type == 'superadmin' ? route('admin.dashboard.index') : route('dashboard.index') }}" class="{{ $isAdminWorkspace ? 'admin-brand-link' : '' }}">
						<img class="sidebar-logo" src="{{ get_logo() }}" alt="logo">
					</a>
				</div>


				<div class="main-menu {{ $isAdminWorkspace ? 'admin-menu-scroll' : '' }}" style="flex: 1; overflow-y: auto;">
					<div class="menu-inner">
						<nav>
							<ul class="metismenu {{ $user_type == 'user' ? 'staff-menu' : '' }}" id="menu">
							@include('layouts.menus.'.Auth::user()->user_type)
							</ul>
						</nav>
					</div>
				</div>

				<!-- Tenant Switcher -->
				@if(auth()->check() && (auth()->user()->user_type == 'admin' || auth()->user()->user_type == 'customer'))
					@php
						$user = auth()->user();
						$mainTenant = \App\Models\Tenant::find($user->tenant_id);
						$memberTenant = null;
						if ($user->user_type == 'customer') {
							$member = \App\Models\Member::where('user_id', $user->id)->first();
							if ($member && $member->member_tenant_id) {
								$memberTenant = \App\Models\Tenant::find($member->member_tenant_id);
							}
						} elseif ($user->user_type == 'admin' && $user->tenant_owner == 1) {
							// For admin users, check if they have a member record with a member tenant
							$member = \App\Models\Member::where('user_id', $user->id)->first();
							if ($member && $member->member_tenant_id) {
								$memberTenant = \App\Models\Tenant::find($member->member_tenant_id);
							}
						}
						// Only get current tenant if it's bound (for tenant routes)
						$currentTenant = app()->bound('tenant') ? app('tenant') : $mainTenant;
						$hasMultipleTenants = ($mainTenant && $memberTenant) || ($user->user_type == 'admin' && $memberTenant);
					@endphp
					@if($hasMultipleTenants && $currentTenant)
						<div class="tenant-switcher {{ $isAdminWorkspace ? 'admin-tenant-switcher' : '' }}">
							<label class="tenant-switcher-label">{{ _lang('Switch Account') }}</label>
							<div class="dropdown">
								<button class="btn btn-sm btn-block text-left tenant-switcher-btn" type="button" id="tenantSwitcher" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
									<span id="current-tenant-name">{{ $currentTenant ? (strlen($currentTenant->name) > 25 ? substr($currentTenant->name, 0, 22) . '...' : $currentTenant->name) : '' }}</span>
									<i class="ti-angle-down float-right" style="margin-top: 2px;"></i>
								</button>
								<div class="dropdown-menu dropdown-menu-right" aria-labelledby="tenantSwitcher" style="min-width: 200px; max-width: 250px;">
									@if($mainTenant)
										<a class="dropdown-item" href="{{ route('switch_tenant') }}?tenant_slug={{ $mainTenant->slug }}">
											<i class="ti-home mr-2"></i>{{ $mainTenant->name }}
											@if($currentTenant && $currentTenant->id == $mainTenant->id)
												<i class="ti-check float-right text-success"></i>
											@endif
										</a>
									@endif
									@if($memberTenant)
										<a class="dropdown-item" href="{{ route('switch_tenant') }}?tenant_slug={{ $memberTenant->slug }}">
											<i class="ti-user mr-2"></i>{{ $memberTenant->name }}
											@if($currentTenant && $currentTenant->id == $memberTenant->id)
												<i class="ti-check float-right text-success"></i>
											@endif
										</a>
									@endif
								</div>
							</div>
						</div>
					@endif
				@endif
			</div>
			<!-- sidebar menu area end -->

			<!-- main content area start -->
			<div class="main-content">
				<!-- header area start -->
				<div class="header-area {{ $isAdminWorkspace ? 'admin-header-v2' : '' }}">
					<div class="row align-items-center">
						<!-- nav and search button -->
						<div class="col-lg-6 col-4 clearfix rtl-2">
							<div class="d-flex align-items-center admin-header-left-cluster">
								<div class="nav-btn float-left">
									<span></span>
									<span></span>
									<span></span>
								</div>
								@if($isAdminWorkspace)
									<div class="admin-navbar-company d-none d-md-flex">
										<span class="admin-navbar-company-name">{{ $tenantDisplayName }}</span>
									</div>
								@endif
							</div>
						</div>

						<!-- profile info & task notification -->
						<div class="col-lg-6 col-8 clearfix rtl-1">
							<ul class="notification-area float-right d-flex align-items-center">
	                            <li class="dropdown d-none d-sm-inline-block">
									<div class="dropdown">
									  <a class="dropdown-toggle d-flex align-items-center admin-utility-pill" type="button" id="selectLanguage" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
										<img class="avatar avatar-xss avatar-circle mr-1" src="{{ get_language() == 'language' ? asset('public/backend/plugins/flag-icon-css/flags/1x1/us.svg') : asset('public/backend/plugins/flag-icon-css/flags/1x1/'.explode('---', get_language())[1].'.svg') }}">
										<span class="d-none d-md-inline-block">{{ explode('---', get_language())[0] }}</span>
										<i class="fas fa-chevron-down ml-2 admin-dropdown-chevron"></i>
									  </a>
									  <div class="dropdown-menu" aria-labelledby="selectLanguage">
										@foreach( get_language_list() as $language )
											<a class="dropdown-item" href="{{ route('switch_language') }}?language={{ $language }}"><img class="avatar avatar-xss avatar-circle mr-1" src="{{ asset('public/backend/plugins/flag-icon-css/flags/1x1/'.explode('---', $language)[1].'.svg') }}"> {{ explode('---', $language)[0] }}</a>
										@endforeach
									  </div>
									</div>
								</li>

								@if(auth()->user()->user_type == 'customer')
									@php $notifications = Auth::user()->member->notifications->take(15); @endphp
									@php $unreadNotification = Auth::user()->member->unreadNotifications(); @endphp
								@else
									@php $notifications = Auth::user()->notifications->take(15); @endphp
									@php $unreadNotification = Auth::user()->unreadNotifications(); @endphp
								@endif

								<li class="dropdown d-none d-sm-inline-block">
									<i class="ti-bell dropdown-toggle" data-toggle="dropdown">
										<span>{{ $unreadNotification->count() }}</span>
									</i>
									<div class="dropdown-menu bell-notify-box notify-box">
										<span class="notify-title text-center">
											@if($unreadNotification->count() > 0)
											{{ _lang('You have').' '.$unreadNotification->count().' '._lang('new notifications') }}
											@else
											{{ _lang("You don't have any new notification") }}
											@endif
										</span>
										<div class="nofity-list">
											@if($notifications->count() == 0)
												<small class="text-center d-block py-2">{{ _lang('No Notification found') }} !</small>
											@endif

											@foreach ($notifications as $notification)
											<a href="{{ route('profile.show_notification', $notification->id) }}" class="d-flex ajax-modal notify-item" data-title="{{ $notification->data['subject'] }}">
												<div class="notify-thumb {{ $notification->read_at == null ? 'unread-thumb' : '' }}"></div>
												<div class="notify-text {{ $notification->read_at == null ? 'font-weight-bold' : '' }}">
													<p><i class="far fa-bell"></i> {{ $notification->data['subject'] }}</p>
													<p><span>{{ $notification->created_at->diffForHumans() }}</span></p>
												</div>
											</a>
											@endforeach
										</div>
									</div>
								</li>

								<li>
									<div class="user-profile dropdown">
										<a class="user-name dropdown-toggle admin-user-trigger" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
											<span class="admin-user-name-text">{{ (app()->bound('tenant') ? app('tenant')->name : null) ?? Auth::user()->name }}</span>
											@if($hasCustomProfileImage)
												<img class="avatar user-thumb admin-user-avatar" id="my-profile-img" src="{{ $profileAvatarUrl }}" alt="{{ $profileDisplayName }}">
											@else
												<span class="admin-user-avatar admin-user-avatar-initials" id="my-profile-img" aria-hidden="true">{{ $profileInitials }}</span>
											@endif
											<i class="fas fa-chevron-down admin-dropdown-chevron"></i>
										</a>
										<div class="dropdown-menu dropdown-menu-right">
											@if(auth()->user()->user_type == 'customer')
											<a class="dropdown-item" href="{{ route('my_wallet.index') }}"><i class="fas fa-wallet text-muted mr-2"></i>{{ _lang('My Wallet') }}</a>
											<a class="dropdown-item" href="{{ route('profile.membership_details') }}"><i class="ti-user text-muted mr-2"></i>{{ _lang('Membership Details') }}</a>
											@endif

											@php $isAadminRoute = auth()->user()->user_type == 'superadmin' ? 'admin.' : ''; @endphp
											<a class="dropdown-item" href="{{ route($isAadminRoute.'profile.edit') }}"><i class="ti-pencil text-muted mr-2"></i>{{ _lang('Profile Settings') }}</a>
											<a class="dropdown-item" href="{{ route($isAadminRoute.'profile.change_password') }}"><i class="ti-exchange-vertical text-muted mr-2"></i></i>{{ _lang('Change Password') }}</a>

											@if(auth()->user()->uses_two_factor_auth == 1)
											<a class="dropdown-item" href="{{ route($isAadminRoute.'profile.disable_2fa') }}"><i class="fas fa-key text-muted mr-2"></i>{{ _lang('Disable 2FA') }}</a>
											@else
											<a class="dropdown-item" href="{{ route($isAadminRoute.'profile.enable_2fa') }}"><i class="fas fa-key text-muted mr-2"></i>{{ _lang('Enable 2FA') }}</a>
											@endif

											@if(auth()->user()->user_type == 'admin')
											<a class="dropdown-item" href="{{ route('settings.index') }}"><i class="ti-settings text-muted mr-2"></i>{{ _lang('System Settings') }}</a>
											@endif

											@if(auth()->user()->user_type == 'admin' && auth()->user()->tenant_owner == 1)
											<a class="dropdown-item" href="{{ route('membership.index') }}"><i class="ti-crown text-muted mr-2"></i>{{ _lang('My Subscription') }}</a>
											@endif

											<div class="dropdown-divider"></div>
											<a class="dropdown-item" href="{{ route('logout') }}"><i class="ti-power-off text-muted mr-2"></i>{{ _lang('Logout') }}</a>
										</div>
									</div>
	                            </li>

	                        </ul>

						</div>
					</div>
				</div><!-- header area end -->

				<!-- Page title area start -->
				@php
					$hasWorkspaceTopTabs = trim($__env->yieldContent('workspace_top_tabs')) !== '';
					$isDashboardPage = Request::is('dashboard') || Request::is('*/dashboard');
				@endphp
				@if($isDashboardPage || $hasWorkspaceTopTabs)
				<div class="page-title-area {{ $isAdminWorkspace ? 'admin-page-title-v2' : '' }}">
					<div class="row align-items-center {{ $isAdminWorkspace ? 'admin-dashboard-top-row' : 'py-3' }}">
						<div class="col-sm-12">
							@if($isAdminWorkspace && $hasWorkspaceTopTabs)
							<div class="admin-dashboard-top-tabs-wrap d-flex align-items-center justify-content-between flex-wrap gap-2">
								@yield('workspace_top_tabs')

								@if(auth()->user()->user_type == 'admin' || auth()->user()->all_branch_access == 1)
								<div class="dropdown admin-dashboard-branch-switcher">
									<a class="dropdown-toggle btn btn-dark btn-xs" type="button" id="dashboardBranchSwitcher" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
										{{ session('branch') =='' ? _lang('All Branch') : session('branch') }}
									</a>
									<div class="dropdown-menu dropdown-menu-right" aria-labelledby="dashboardBranchSwitcher">
										<a class="dropdown-item" href="{{ route('switch_branch') }}">{{ _lang('All Branch') }}</a>
										<a class="dropdown-item" href="{{ route('switch_branch') }}?branch_id=default&branch={{ get_option('default_branch_name', 'Main Branch') }}">{{ get_option('default_branch_name', 'Main Branch') }}</a>
										@foreach( \App\Models\Branch::all() as $branch )
										<a class="dropdown-item" href="{{ route('switch_branch') }}?branch_id={{ $branch->id }}&branch={{ $branch->name }}">{{ $branch->name }}</a>
										@endforeach
									</div>
								</div>
								@endif
							</div>
							@else
							<div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
								<h6 class="mb-0">
									<span id="dashboard-greeting" data-morning="{{ _lang('Good morning') }}" data-afternoon="{{ _lang('Good afternoon') }}" data-evening="{{ _lang('Good evening') }}" data-night="{{ _lang('Good night') }}"></span>{{ auth()->user()->user_type == 'customer' && auth()->user()->member ? ', ' . auth()->user()->member->first_name . ' ' . auth()->user()->member->last_name : (auth()->user()->name ? ', ' . auth()->user()->name : '') }}
								</h6>

								<div class="d-flex align-items-center gap-2">
									@if(auth()->user()->user_type == 'customer')
									@if(Request::is('*dashboard*'))
									<button type="button" class="btn btn-primary btn-sm btn-deposit-header" data-toggle="modal" data-target="#depositManualModal">{{ _lang('Deposit') }}</button>
									@else
									<a href="{{ route('deposit.manual_methods') }}" class="btn btn-primary btn-sm btn-deposit-header">{{ _lang('Deposit') }}</a>
									@endif
									@endif
									@if(auth()->user()->user_type == 'admin' || auth()->user()->all_branch_access == 1)
									<div class="dropdown">
										<a class="dropdown-toggle btn btn-dark btn-xs" type="button" id="selectLanguage" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
											{{ session('branch') =='' ? _lang('All Branch') : session('branch') }}
										</a>
										<div class="dropdown-menu dropdown-menu-right" aria-labelledby="selectLanguage">
											<a class="dropdown-item" href="{{ route('switch_branch') }}">{{ _lang('All Branch') }}</a>
											<a class="dropdown-item" href="{{ route('switch_branch') }}?branch_id=default&branch={{ get_option('default_branch_name', 'Main Branch') }}">{{ get_option('default_branch_name', 'Main Branch') }}</a>
											@foreach( \App\Models\Branch::all() as $branch )
											<a class="dropdown-item" href="{{ route('switch_branch') }}?branch_id={{ $branch->id }}&branch={{ $branch->name }}">{{ $branch->name }}</a>
											@endforeach
										</div>
									</div>
									@endif
								</div>
							</div>
							@endif
						</div>
					</div>
				</div><!-- page title area end -->
				@else
				<div class="page-title-area {{ $isAdminWorkspace ? 'admin-page-title-v2' : '' }}">
					<div class="row align-items-center py-3">
						<div class="col-sm-8">
							<h4 class="mb-1">{{ $resolvedPageTitle }}</h4>
							@include('layouts.others.breadcrumbs')
						</div>
						@if(auth()->user()->user_type == 'admin' || auth()->user()->all_branch_access == 1)
						<div class="col-sm-4 d-flex justify-content-sm-end mt-2 mt-sm-0">
							<div class="dropdown">
								<a class="dropdown-toggle btn btn-dark btn-xs" type="button" id="pageBranchSwitcher" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
									{{ session('branch') =='' ? _lang('All Branch') : session('branch') }}
								</a>
								<div class="dropdown-menu dropdown-menu-right" aria-labelledby="pageBranchSwitcher">
									<a class="dropdown-item" href="{{ route('switch_branch') }}">{{ _lang('All Branch') }}</a>
									<a class="dropdown-item" href="{{ route('switch_branch') }}?branch_id=default&branch={{ get_option('default_branch_name', 'Main Branch') }}">{{ get_option('default_branch_name', 'Main Branch') }}</a>
									@foreach( \App\Models\Branch::all() as $branch )
									<a class="dropdown-item" href="{{ route('switch_branch') }}?branch_id={{ $branch->id }}&branch={{ $branch->name }}">{{ $branch->name }}</a>
									@endforeach
								</div>
							</div>
						</div>
						@endif
					</div>
				</div><!-- page title area end -->
				@endif

				<div class="main-content-inner mt-4 {{ $isAdminWorkspace ? 'admin-main-content-inner' : '' }}">
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

					@yield('content')
				</div><!--End main content Inner-->

			</div><!--End main content-->

		</div><!--End Page Container-->

        <!-- jQuery  -->
		<script src="{{ asset('public/backend/assets/js/vendor/jquery-3.7.1.min.js') }}"></script>
		<script src="{{ asset('public/backend/assets/js/popper.min.js') }}"></script>
		<script src="{{ asset('public/backend/plugins/bootstrap/js/bootstrap.min.js') }}"></script>
		<script src="{{ asset('public/backend/plugins/metisMenu/metisMenu.min.js') }}"></script>
		<script src="{{ asset('public/backend/assets/js/print.js') }}"></script>
		<script src="{{ asset('public/backend/plugins/pace/pace.min.js') }}"></script>
        <script src="{{ asset('public/backend/plugins/moment/moment.js') }}"></script>

		<!-- Conditional JS -->
        @include('layouts.others.import-js')

		<script src="{{ asset('public/backend/plugins/dropify/js/dropify.min.js') }}"></script>
		<script src="{{ asset('public/backend/plugins/sweet-alert2/js/sweetalert2.min.js') }}"></script>
		<script src="{{ asset('public/backend/plugins/select2/js/select2.min.js') }}"></script>
		<script src="{{ asset('public/backend/plugins/parsleyjs/parsley.min.js') }}"></script>
		<script src="{{ asset('public/backend/plugins/jquery-toast-plugin/jquery.toast.min.js') }}"></script>
		<script src="{{ asset('public/backend/plugins/daterangepicker/daterangepicker.js') }}"></script>
		<script src="{{ asset('public/backend/plugins/slimscroll/jquery.slimscroll.min.js') }}"></script>

        <!-- App js -->
        <script src="{{ asset('public/backend/assets/js/scripts.js'). '?v=' . filemtime(public_path('backend/assets/js/scripts.js')) }}"></script>
        <script src="{{ asset('public/backend/assets/js/table-export-totals.js') }}"></script>

		@include('layouts.others.alert')

		<!-- Dashboard greeting by local time -->
		<script>
		(function() {
			var el = document.getElementById('dashboard-greeting');
			if (!el) return;
			var hour = new Date().getHours();
			var key = 'morning';
			if (hour >= 12 && hour < 17) key = 'afternoon';
			else if (hour >= 17 && hour < 21) key = 'evening';
			else if (hour >= 21 || hour < 5) key = 'night';
			var text = el.getAttribute('data-' + key) || ('Good ' + key);
			el.textContent = text;
		})();
		</script>

		<!-- Custom JS -->
		@yield('js-script')
    </body>
</html>
