<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Beranda') &middot; {{ config('app.name', 'KabarKita') }}</title>
    <meta name="description" content="@yield('meta_description', 'Portal berita terkini, akurat, dan berimbang untuk pembaca Indonesia.')">
    <meta property="og:title" content="@yield('title', config('app.name', 'KabarKita'))">
    <meta property="og:type" content="website">
    @stack('meta')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col bg-white text-slate-800 antialiased">
    @include('public.partials.header')

    <main class="flex-1">
        @include('public.partials.flash')
        @yield('content')
    </main>

    @include('public.partials.footer')
</body>
</html>
