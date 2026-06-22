<?php

namespace App\Http\Middleware;

use App\Support\AuthContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureGuest
{
    public function handle(Request $request, Closure $next): Response
    {
        if (AuthContext::user()) {
            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}
