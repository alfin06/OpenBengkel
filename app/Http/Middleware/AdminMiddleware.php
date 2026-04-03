<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is logged in AND their role is 'admin'
        if (Auth::check() && Auth::user()->isAdmin()) {
            return $next($request); // Let them through!
        }

        // If they are just 'staff', kick them back to their dashboard with an error
        return redirect()->route('dashboard')->with('error', 'Akses Ditolak: Halaman ini khusus untuk Manajemen.');
    }
}
