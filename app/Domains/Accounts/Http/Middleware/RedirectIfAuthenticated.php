<?php

namespace App\Domains\Accounts\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * The `guest` alias: keeps signed-in staff off the sign-in pages.
 *
 * Rather than refusing, it forwards the caller to the dashboard, so hitting
 * the login page a second time in the same browser simply lands where they
 * were already headed.
 */
class RedirectIfAuthenticated
{
    /**
     * Pass guests through; send anyone already holding a session home.
     *
     * With no guard named on the route the default one is consulted, which
     * means a customer-portal session does not count as being signed in here.
     *
     * @param  Request  $request
     * @param  string|null  $guard  guard alias named on the route, if any
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        return Auth::guard($guard)->check()
            ? redirect(RouteServiceProvider::HOME)
            : $next($request);
    }
}
