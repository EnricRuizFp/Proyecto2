@php
$config = [
'appName' => config('app.name'),
'locale' => $locale = app()->getLocale(),
'locales' => config('app.locales'),
];
@endphp
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="light">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/anchor.svg') }}">
    <link rel="alternate icon" type="image/x-icon" href="{{ asset('images/favicon.ico') }}">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>DE BATTLESHIP</title>
    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <!--<link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">-->

    <!-- IMPORT RUBIK FONT -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Rubik:ital,wght@0,300..900;1,300..900&display=swap" rel="stylesheet">

    <script>
        window.config = @json($config);
    </script>
    <!-- Scripts -->
    {{-- @vite(['resources/sass/app.scss', 'resources/js/app.js'])--}}
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

</head>

<body class="font-sans antialiased" id="app">
    <router-view></router-view>
</body>

</html>