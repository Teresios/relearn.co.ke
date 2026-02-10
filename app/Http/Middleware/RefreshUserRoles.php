<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RefreshUserRoles
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Refresh the authenticated user's roles/permissions from the database
        if (auth()->check()) {
            auth()->user()->load('roles', 'permissions');
        }

        return $next($request);
    }
}
