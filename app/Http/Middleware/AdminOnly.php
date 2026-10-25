<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminOnly
{
    public function handle(Request $request, Closure $next)
    {
        abort_unless(auth()->check() && auth()->user()->role === 'admin', 403, 'Admin only.');
        return $next($request);
    }
}