<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Orchid's own LoginController guards its login routes with Laravel's
 * stock "redirect if authenticated" middleware, but registers it without
 * a guard name — so it always checks the default "web" guard, which this
 * app never authenticates against (everything runs through the "admin"
 * guard instead). The login page therefore never recognized an already
 * logged-in admin and kept showing the login form. This checks the
 * correct guard and redirects to the dashboard when already logged in.
 */
class RedirectIfAdminAuthenticated
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->route()?->getName() === 'platform.login' && Auth::guard('admin')->check()) {
            return redirect()->route(config('platform.index'));
        }

        return $next($request);
    }
}
