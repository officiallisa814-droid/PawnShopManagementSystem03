<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && !$user->isActive()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->withErrors(['email' => 'គណនីរបស់អ្នកត្រូវបានផ្អាក។ សូមទាក់ទង Admin។']);
        }

        return $next($request);
    }
}