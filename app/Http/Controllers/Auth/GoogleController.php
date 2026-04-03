<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    // Step 1: Send user to Google's login page
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    // Step 2: Google sends them back here with their info
    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            
            // THE STRICT CHECK: Does this email exist in our users table?
            $user = User::where('email', $googleUser->getEmail())->first();

            if ($user) {
                // Email matches! Log them in safely.
                Auth::login($user);
                
                // Regenerate session for security
                request()->session()->regenerate();

                return redirect()->intended(route('dashboard', absolute: false));
            }

            // If the email is NOT in the database, reject them and send back to login
            return redirect()->route('login')->with('error', 'Akses Ditolak: Email Anda tidak terdaftar di sistem bengkel.');

        } catch (\Exception $e) {
            return redirect()->route('login')->with('error', 'Terjadi kesalahan saat login dengan Google.');
        }
    }
}
