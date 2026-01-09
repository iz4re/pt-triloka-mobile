<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        // For admin routes, redirect to admin login
        if ($request->is('admin') || $request->is('admin/*')) {
            return route('admin.login');
        }

        // For API requests, don't redirect (return null for JSON response)
        return $request->expectsJson() ? null : route('admin.login');
    }
}
