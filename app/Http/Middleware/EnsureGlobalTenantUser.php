<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class EnsureGlobalTenantUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Only proceed if tenant is bound (this middleware should only run after tenant middleware)
        if (!app()->bound('tenant')) {
            return $next($request);
        }
        
        $tenant = app('tenant');
        if (Auth::check() && Auth::user()->tenant_id == $tenant->id && Auth::user()->user_type != 'superadmin') {

            if ($tenant->package_id != null && ($tenant->getRawOriginal('valid_to') < date('Y-m-d') || $tenant->getRawOriginal('valid_to') == null)) {
                $package = $tenant->package;

                //Apply Free Package
                if ($package->cost == 0) {
                    $tenant->membership_type   = 'member';
                    $tenant->subscription_date = now();
                    $tenant->valid_to          = update_membership_date($package, $tenant->getRawOriginal('subscription_date'));
                    $tenant->s_email_send_at   = null;
                    $tenant->save();
                }

                //Apply Trial Package
                if ($package->cost > 0 && $package->trial_days > 0 && $tenant->membership_type == '') {
                    $tenant->membership_type   = 'trial';
                    $tenant->subscription_date = now();
                    $tenant->valid_to          = date('Y-m-d', strtotime($tenant->getRawOriginal('subscription_date') . " + $package->trial_days days"));
                    $tenant->save();
                }
            }

            if ($tenant->package_id == null) {
                return redirect()->route('membership.packages')->with('error', _lang("Please choose your subscription plan"));
            }

            if ($tenant->package_id != null && $tenant->getRawOriginal('valid_to') == null) {
                return redirect()->route('membership.payment_gateways')->with('error', _lang("Please make your subscription payment"));
            }

            if ($tenant->package_id != null && $tenant->getRawOriginal('valid_to') < date('Y-m-d')) {
                return response()->view('membership.expired', ['alert_col' => 'col-lg-6 offset-lg-3']);
            }

            return $next($request);
        }
        // If user is logged in but tenant doesn't match, redirect to tenant login
        if (Auth::check()) {
            if (app()->bound('tenant')) {
                $tenant = app('tenant');
                return redirect()->route('tenant.login', ['tenant' => $tenant->slug])->with('error', _lang('You do not have access to this tenant'));
            } else {
                // No tenant bound, redirect to general login
                return redirect()->route('login');
            }
        }
        return redirect()->route('login');
    }
}
