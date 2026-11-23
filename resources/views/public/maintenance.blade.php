@php
    $siteName = $site['site_name'] ?? config('app.name', 'TikumPost');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sedang Perawatan &middot; {{ $siteName }}</title>
    <meta name="robots" content="noindex">
    @if (! empty($site['site_favicon']))
        <link rel="icon" href="{{ Storage::disk('public')->url($site['site_favicon']) }}">
    @endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="grid min-h-screen place-items-center bg-slate-950 px-6 text-center text-slate-300">
    <div class="max-w-md">
        <div class="mx-auto flex items-center justify-center gap-3">
            @include('partials.logo', ['class' => 'h-12 w-auto', 'invert' => true])
            <span class="text-2xl font-bold tracking-tight text-white">{{ $siteName }}</span>
        </div>

        <h1 class="mt-8 text-3xl font-black tracking-tight text-white">Situs sedang dalam perawatan</h1>
        <p class="mt-4 text-sm leading-relaxed text-slate-400">
            Kami sedang melakukan pemeliharaan singkat untuk meningkatkan layanan.
            Silakan coba beberapa saat lagi.
        </p>

        @if (! empty($site['contact_email']))
            <p class="mt-6 text-sm text-slate-500">
                Butuh bantuan? <a href="mailto:{{ $site['contact_email'] }}" class="font-semibold text-red-400 hover:text-red-300">{{ $site['contact_email'] }}</a>
            </p>
        @endif
    </div>
</body>
</html>
