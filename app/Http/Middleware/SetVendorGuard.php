<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SetVendorGuard
{
    /**
     * Ensure auth resolves against the vendor guard.
     */
    public function handle(Request $request, Closure $next): Response
    {
        Auth::shouldUse('vendor');

        return $next($request);
    }
}
