@extends('layouts.app')

@section('content')
@php
    $userCount = $adminCounts['users'] ?? 0;
    $roleCount = $adminCounts['roles'] ?? 0;
    $currencyCount = $adminCounts['currencies'] ?? 0;
    $templateCount = $adminCounts['templates'] ?? 0;
@endphp
@include('backend.admin.partials.workspace-styles')

@include('backend.admin.partials.page-header', [
    'title' => _lang('Administration Workspace'),
    'subtitle' => _lang('Manage users, permissions, settings, currencies, and templates from one CAVIC administration area.'),
    'badge' => _lang('System Administration'),
    'breadcrumbs' => [
        ['label' => _lang('Dashboard'), 'url' => route('dashboard.index')],
        ['label' => _lang('Administration Workspace'), 'active' => true],
    ],
    'actions' => [
        ['label' => _lang('Open Settings'), 'url' => route('settings.index'), 'class' => 'btn-primary btn-sm'],
    ],
])

<div class="row mb-4">
    <div class="col-md-3 mb-3"><div class="card workspace-stat-card mb-0"><div class="card-body"><div class="stat-label">{{ _lang('Users') }}</div><div class="stat-value">{{ $userCount }}</div><a class="stat-link" href="{{ route('users.index') }}">{{ _lang('Manage users') }}</a></div></div></div>
    <div class="col-md-3 mb-3"><div class="card workspace-stat-card mb-0"><div class="card-body"><div class="stat-label">{{ _lang('Roles') }}</div><div class="stat-value">{{ $roleCount }}</div><a class="stat-link" href="{{ route('roles.index') }}">{{ _lang('Manage roles') }}</a></div></div></div>
    <div class="col-md-3 mb-3"><div class="card workspace-stat-card mb-0"><div class="card-body"><div class="stat-label">{{ _lang('Currencies') }}</div><div class="stat-value">{{ $currencyCount }}</div><a class="stat-link" href="{{ route('currency.index') }}">{{ _lang('Manage currency') }}</a></div></div></div>
    <div class="col-md-3 mb-3"><div class="card workspace-stat-card mb-0"><div class="card-body"><div class="stat-label">{{ _lang('Notification Templates') }}</div><div class="stat-value">{{ $templateCount }}</div><a class="stat-link" href="{{ route('email_templates.index') }}">{{ _lang('Open templates') }}</a></div></div></div>
</div>

<div class="card workspace-section-card">
    <div class="card-header">
        @include('backend.admin.partials.module-tabs', [
            'tabs' => [
                ['label' => _lang('Users'), 'target' => '#users', 'active' => true],
                ['label' => _lang('Roles & Permissions'), 'target' => '#roles'],
                ['label' => _lang('Settings'), 'target' => '#settings'],
                ['label' => _lang('Currency'), 'target' => '#currency'],
                ['label' => _lang('Templates'), 'target' => '#templates'],
            ],
        ])
    </div>
    <div class="card-body tab-content">
        <div class="tab-pane fade show active" id="users">
            <div class="table-responsive mb-3">
                <table class="table table-sm table-bordered workspace-mini-table mb-0">
                    <thead>
                        <tr>
                            <th>{{ _lang('User') }}</th>
                            <th>{{ _lang('Email') }}</th>
                            <th>{{ _lang('Role') }}</th>
                            <th>{{ _lang('Branch') }}</th>
                            <th>{{ _lang('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentUsers as $user)
                            <tr>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->role->name }}</td>
                                <td>{{ $user->branch->name }}</td>
                                <td><span class="workspace-status-chip {{ (int) $user->status === 1 ? 'active' : 'review' }}">{{ (int) $user->status === 1 ? _lang('Active') : _lang('Inactive') }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted">{{ _lang('No users found') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <a href="{{ route('users.index') }}" class="btn btn-outline-primary btn-sm">{{ _lang('Manage Users') }}</a>
        </div>
        <div class="tab-pane fade" id="roles">
            <div class="table-responsive mb-3">
                <table class="table table-sm table-bordered workspace-mini-table mb-0">
                    <thead>
                        <tr>
                            <th>{{ _lang('Role') }}</th>
                            <th>{{ _lang('Permissions') }}</th>
                            <th>{{ _lang('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($roleSummary as $role)
                            <tr>
                                <td>{{ $role->name }}</td>
                                <td>{{ $role->permissions_count }}</td>
                                <td><a href="{{ route('roles.index') }}" class="btn btn-light btn-xs">{{ _lang('Open') }}</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-muted">{{ _lang('No roles found') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('roles.index') }}" class="btn btn-outline-primary btn-sm mr-2">{{ _lang('Roles') }}</a>
                <a href="{{ route('permission.index') }}" class="btn btn-outline-primary btn-sm">{{ _lang('Access Control') }}</a>
            </div>
        </div>
        <div class="tab-pane fade" id="settings">
            <div class="table-responsive mb-3">
                <table class="table table-sm table-bordered workspace-mini-table mb-0">
                    <tbody>
                        <tr><th>{{ _lang('Company Name') }}</th><td>{{ $settingsSummary['company_name'] ?? _lang('Not Set') }}</td></tr>
                        <tr><th>{{ _lang('Timezone') }}</th><td>{{ $settingsSummary['timezone'] ?? 'UTC' }}</td></tr>
                        <tr><th>{{ _lang('Date Format') }}</th><td>{{ $settingsSummary['date_format'] ?? 'Y-m-d' }}</td></tr>
                        <tr><th>{{ _lang('Base Currency') }}</th><td>{{ $settingsSummary['currency'] ?? 'USD' }}</td></tr>
                        <tr><th>{{ _lang('Email Verification') }}</th><td>{{ $settingsSummary['email_verification'] ?? _lang('Disabled') }}</td></tr>
                    </tbody>
                </table>
            </div>
            <a href="{{ route('settings.index') }}" class="btn btn-outline-primary btn-sm">{{ _lang('Open Settings') }}</a>
        </div>
        <div class="tab-pane fade" id="currency">
            <div class="table-responsive mb-3">
                <table class="table table-sm table-bordered workspace-mini-table mb-0">
                    <thead>
                        <tr>
                            <th>{{ _lang('Currency') }}</th>
                            <th>{{ _lang('Status') }}</th>
                            <th>{{ _lang('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentCurrencies as $currency)
                            <tr>
                                <td>{{ $currency->name }}</td>
                                <td><span class="workspace-status-chip {{ (int) $currency->status === 1 ? 'active' : 'review' }}">{{ (int) $currency->status === 1 ? _lang('Active') : _lang('Inactive') }}</span></td>
                                <td><a href="{{ route('currency.index') }}" class="btn btn-light btn-xs">{{ _lang('Open') }}</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-muted">{{ _lang('No currencies found') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <a href="{{ route('currency.index') }}" class="btn btn-outline-primary btn-sm">{{ _lang('Currency Management') }}</a>
        </div>
        <div class="tab-pane fade" id="templates">
            <div class="table-responsive mb-3">
                <table class="table table-sm table-bordered workspace-mini-table mb-0">
                    <thead>
                        <tr>
                            <th>{{ _lang('Template') }}</th>
                            <th>{{ _lang('Slug') }}</th>
                            <th>{{ _lang('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentTemplates as $template)
                            <tr>
                                <td>{{ $template->name }}</td>
                                <td>{{ $template->slug }}</td>
                                <td><a href="{{ route('email_templates.index') }}" class="btn btn-light btn-xs">{{ _lang('Open') }}</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-muted">{{ _lang('No templates found') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <a href="{{ route('email_templates.index') }}" class="btn btn-outline-primary btn-sm">{{ _lang('Notification Templates') }}</a>
        </div>
    </div>
</div>
@endsection
