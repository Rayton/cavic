<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RouteUtilityController extends Controller
{
    public function adminRoot(): RedirectResponse
    {
        return redirect()->route('admin.login');
    }

    public function switchLanguage(Request $request): RedirectResponse
    {
        if ($request->filled('language')) {
            $request->session()->put('language', $request->query('language'));
        }

        return back();
    }

    public function switchBranch(Request $request): RedirectResponse
    {
        if ($request->filled('branch') && $request->filled('branch_id')) {
            $request->session()->put([
                'branch' => $request->query('branch'),
                'branch_id' => $request->query('branch_id'),
            ]);
        } else {
            $request->session()->forget(['branch', 'branch_id']);
        }

        return back();
    }

    public function switchTenant(Request $request): RedirectResponse
    {
        if (! auth()->check() || ! $request->filled('tenant_slug')) {
            return back();
        }

        $currentUser = auth()->user();
        $targetTenant = Tenant::where('slug', $request->query('tenant_slug'))->first();

        if (! $targetTenant) {
            return back()->with('error', _lang('Tenant not found'));
        }

        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect(url('/' . $targetTenant->slug . '/login?email=' . urlencode($currentUser->email)));
    }

    public function installationPlaceholder(): string
    {
        return 'Installation';
    }
}
