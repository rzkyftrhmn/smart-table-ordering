<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureLocationVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! session()->get('location_verified')) {
            return redirect()->route('customer-menu.check-location', [
                'token' => $request->route('token'),
            ]);
        }

        return $next($request);
    }
}