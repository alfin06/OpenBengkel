<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">


        @php
            // Support both array and object for $page['props']['shop']
            $shop = $page['props']['shop'] ?? null;
            if (is_object($shop)) {
                $shopName = $shop->shop_name ?? config('app.name', 'OpenBengkel');
            } elseif (is_array($shop)) {
                $shopName = $shop['shop_name'] ?? config('app.name', 'OpenBengkel');
            } else {
                $shopName = config('app.name', 'OpenBengkel');
            }
        @endphp

        <title inertia>{{ $shopName }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />


        @php
            // Access the shared 'shop' prop from HandleInertiaRequests
            if (is_object($shop)) {
                $logo = $shop->logo_path ?? null;
            } elseif (is_array($shop)) {
                $logo = $shop['logo_path'] ?? null;
            } else {
                $logo = null;
            }
            // Fallback sequence: Shop Logo -> Default Logo -> Laravel/Vite Favicon
            $favicon = $logo ?: asset('favicon.ico');
        @endphp

        <link rel="icon" href="{{ $favicon }}?v={{ time() }}">
        
        <link rel="apple-touch-icon" href="{{ $favicon }}?v={{ time() }}">

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
