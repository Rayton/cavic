<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\EmailTemplate;
use App\Models\Role;
use App\Models\User;

class AdministrationHubController extends Controller
{
    public function index()
    {
        return view('backend.admin.administration.index', [
            'page_title' => _lang('Administration'),
            'adminCounts' => [
                'users' => User::count(),
                'roles' => Role::count(),
                'currencies' => Currency::count(),
                'templates' => EmailTemplate::count(),
            ],
            'recentUsers' => User::with(['role', 'branch'])->latest('id')->limit(8)->get(),
            'roleSummary' => Role::withCount('permissions')->latest('id')->limit(8)->get(),
            'recentCurrencies' => Currency::latest('id')->limit(8)->get(),
            'settingsSummary' => [
                'company_name' => get_option('company_name', _lang('Not Set')),
                'timezone' => get_option('timezone', 'UTC'),
                'date_format' => get_date_format(),
                'currency' => get_option('currency', 'USD'),
                'email_verification' => (int) get_option('email_verification', 0) === 1 ? _lang('Enabled') : _lang('Disabled'),
            ],
            'recentTemplates' => EmailTemplate::latest('id')->limit(8)->get(),
        ]);
    }
}
