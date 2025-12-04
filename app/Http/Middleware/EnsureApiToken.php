<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiToken
{
    /**
     * Handle an incoming request.
     * Ensures user has a valid API token in session.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Session::has('api_token')) {
            return redirect()->route('login')
                ->with('error', 'Please login to access this page.');
        }

        return $next($request);
    }
}

