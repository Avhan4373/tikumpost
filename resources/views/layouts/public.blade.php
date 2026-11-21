<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="canonical" href="{{ request()->url().(request()->has('page') ? '?page='.request()->integer('page') : '') }}">
    <meta name="robots" content="@yield('robots', 'index,follow')">
    @include('partials.theme-init')
    <title>@yield('title', 'Beranda') &middot; {{ $site['site_name'] ?? config('app.name', 'TikumPost') }}</title>
    <meta name="description" content="@yield('meta_description', $site['meta_description'] ?? '')">
    @if (! empty($site['meta_keywords']))
        <meta name="keywords" content="{{ $site['meta_keywords'] }}">
    @endif
    @if (! empty($site['site_favicon']))
        <link rel="icon" href="{{ Storage::disk('public')->url($site['site_favicon']) }}">
    @endif
    <meta property="og:title" content="@yield('title', $site['site_name'] ?? config('app.name', 'TikumPost'))">
    <meta property="og:site_name" content="{{ $site['site_name'] ?? config('app.name', 'TikumPost') }}">
    <meta property="og:description" content="@yield('meta_description', $site['meta_description'] ?? '')">
    @php($ogImage = ! empty($site['site_logo']) ? url(Storage::disk('public')->url($site['site_logo'])) : url('favicon.ico'))
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:image" content="@yield('og_image', $ogImage)">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('title', $site['site_name'] ?? config('app.name', 'TikumPost'))">
    <meta name="twitter:description" content="@yield('meta_description', $site['meta_description'] ?? '')">
    <meta name="twitter:image" content="@yield('og_image', $ogImage)">
    @stack('meta')
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>document.documentElement.classList.add('js');</script>
</head>
<body id="top" class="flex min-h-screen flex-col text-slate-800 antialiased @yield('body_class')">
    <div class="nav-progress" data-nav-progress aria-hidden="true"></div>

    @include(\App\Support\SiteSettings::layout() === 'klasik' ? 'public.partials.header-klasik' : 'public.partials.header')

    <main class="flex-1" data-page>
        @include('public.partials.flash')
        @yield('content')
    </main>

    @include('public.partials.footer')

    <script>
        // Cadangan: bila app.js gagal dimuat, tampilkan semua konten yang di-reveal.
        window.setTimeout(function () {
            if (document.documentElement.dataset.revealReady) {
                return;
            }

            document.querySelectorAll('[data-reveal]').forEach(function (el) {
                el.removeAttribute('data-reveal');
            });
        }, 2000);
    </script>
</body>
</html>
