<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        // Fetch the settings once per request
        $settings = \App\Models\Setting::first();

        return array_merge(parent::share($request), [
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'print_id' => fn () => $request->session()->get('print_id'),
                'wa_url'  => fn () => $request->session()->get('wa_url'),
            ],
            'shop' => [
                'shop_name' => $settings->shop_name ?? 'OpenBengkel',
                'shop_address' => $settings->shop_address ?? '',
                'shop_phone' => $settings->shop_phone ?? '',
                'shop_phone2'  => $settings->shop_phone2 ?? '',
                'logo_path' => $settings?->logo_path 
                ? asset('storage/' . $settings->logo_path) 
                : asset('images/default-logo.png'),
                'logo_url' => $settings?->logo_path ? asset('storage/' . $settings->logo_path) : null,
            ],
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'role' => $request->user()->role,
                ] : null,
            ],
        ]);
    }
}
