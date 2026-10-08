<!DOCTYPE html>
<html lang="fa" dir="rtl">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @php
            $siteName = site_setting('site_name', config('app.name'));
            $siteIconPath = site_setting('site_icon');
            $faviconUrl = is_string($siteIconPath) && $siteIconPath !== ''
                ? \App\Support\Storage\PublicStorageUrl::forPath($siteIconPath)
                : asset('favicon.ico');
        @endphp
        <title inertia>{{ $siteName }}</title>
        <link rel="icon" href="{{ $faviconUrl }}">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        @viteReactRefresh
        @vite(['resources/css/app.css', 'resources/js/app.tsx'])
        @inertiaHead
    </head>
    <body class="min-h-screen antialiased">
        @inertia
    </body>
</html>
