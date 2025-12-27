<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Models\UserUrlHistory;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandleForgeProfileRedirects
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only handle Forge profile routes
        if (! $request->route()->named('forge.profile')) {
            return $next($request);
        }

        $username = $request->route('username');

        // Check if user exists with current username
        $user = User::where('username', $username)->first();

        if ($user) {
            // Inject user into request for controller
            $request->merge(['user' => $user]);
            return $next($request);
        }

        // Check URL history for redirect
        $redirectTarget = UserUrlHistory::findRedirectTarget($username);

        if ($redirectTarget) {
            return redirect()
                ->route('forge.profile', $redirectTarget->username)
                ->with('info', 'This profile has been moved to a new URL.');
        }

        // No user found and no redirect
        abort(404, 'Forge profile not found');
    }
}
