<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class StaffMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // If the user is Staff or Finance, let them through to the Staff Dashboard
        if (Auth::check() && (Auth::user()->role === 'staff' || Auth::user()->role === 'finance')) {
            return $next($request);
        }

        // If the user is an Admin trying to access the Staff Dashboard, 
        // gently redirect them back to their Boss View!
        if (Auth::check() && Auth::user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        return redirect('/');
    }
}