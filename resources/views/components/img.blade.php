@props([
    'path',
    'alt' => '',
    'sizes' => null,
    'loading' => 'lazy',
    'decoding' => 'async',
    'fetchpriority' => null,
])

@php
    $disk = 'public';
    // Read-only: varian dibuat saat unggah + perintah `media:variants`, bukan saat render.
    $variants = \App\Support\ImageVariant::existing($path);
    $src = \Illuminate\Support\Facades\Storage::disk($disk)->url($path);
    $srcset = \App\Support\ImageVariant::srcset($variants, $disk) ?: null;
    $intrinsic = \App\Support\ImageVariant::intrinsic($path, $disk);
    $lebar = $intrinsic[0] ?? null;
    $tinggi = $intrinsic[1] ?? null;
@endphp

<img
    src="{{ $src }}"
    @if ($srcset) srcset="{{ $srcset }}" @endif
    @if ($sizes) sizes="{{ $sizes }}" @endif
    @if ($lebar) width="{{ $lebar }}" height="{{ $tinggi }}" @endif
    alt="{{ $alt }}"
    loading="{{ $loading }}"
    decoding="{{ $decoding }}"
    @if ($fetchpriority) fetchpriority="{{ $fetchpriority }}" @endif
    {{ $attributes }}
>
