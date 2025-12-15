<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminAccess
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            return redirect()->route('admin.login')->with('error', 'Please login to access admin panel');
        }

        // Check if user is admin
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized access');
        }

        return $next($request);
    }
}
