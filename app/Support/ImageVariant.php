<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ImageVariant
{
    /** Naikkan bila WIDTHS/kualitas berubah: memberi URL baru agar cache immutable tidak basi. */
    public const VERSION = 'v1';

    public const WIDTHS = [160, 320, 480, 960, 1440];

    public const QUALITY = 80;

    /** Batas piksel sebelum dekode: cegah OOM dari gambar "bom". 40 MP ≈ 160 MB RGBA. */
    public const MAX_PIXELS = 40_000_000;

    /**
     * Varian yang SUDAH ada di disk — read-only, tanpa menulis apa pun.
     * Dipakai jalur render: GET tak boleh membuat berkas.
     *
     * @return list<array{path: string, width: int, height: int}>
     */
    public static function existing(string $path, string $disk = 'public'): array
    {
        $source = self::intrinsic($path, $disk);

        if ($source === null) {
            return [];
        }

        [$sourceWidth, $sourceHeight] = $source;
        $variants = [];

        foreach (self::WIDTHS as $width) {
            if ($width >= $sourceWidth) {
                continue;
            }

            $target = self::variantPath($path, $width);

            if (Storage::disk($disk)->exists($target)) {
                $variants[] = [
                    'path' => $target,
                    'width' => $width,
                    'height' => (int) round($sourceHeight * ($width / $sourceWidth)),
                ];
            }
        }

        return $variants;
    }

    /** @return list<array{path: string, width: int, height: int}> */
    public static function ensure(string $path, string $disk = 'public'): array
    {
        $source = self::intrinsic($path, $disk);

        if ($source === null || ! function_exists('imagewebp') || $source[0] * $source[1] > self::MAX_PIXELS) {
            return [];
        }

        [$sourceWidth, $sourceHeight] = $source;

        foreach (self::WIDTHS as $width) {
            // Jangan upscale: varian hanya untuk lebar di bawah gambar asli.
            if ($width >= $sourceWidth) {
                continue;
            }

            $target = self::variantPath($path, $width);

            if (! Storage::disk($disk)->exists($target)) {
                self::write($path, $target, $width, (int) round($sourceHeight * ($width / $sourceWidth)), $disk);
            }
        }

        return self::existing($path, $disk);
    }

    /** @param list<array{path: string, width: int, height: int}> $variants */
    public static function srcset(array $variants, string $disk = 'public'): string
    {
        return implode(', ', array_map(
            fn (array $variant) => Storage::disk($disk)->url($variant['path']).' '.$variant['width'].'w',
            $variants,
        ));
    }

    /** @return array{0: int, 1: int}|null */
    public static function intrinsic(string $path, string $disk = 'public'): ?array
    {
        // Jalur berkas datang dari kolom DB; tolak apa pun yang bisa keluar dari disk.
        if ($path === '' || str_contains($path, '..') || ! Storage::disk($disk)->exists($path)) {
            return null;
        }

        $info = @getimagesize(Storage::disk($disk)->path($path));

        return $info === false ? null : [(int) $info[0], (int) $info[1]];
    }

    /** Varian hanya optimasi: kegagalan apa pun tak boleh menggagalkan unggahan. */
    public static function ensureQuietly(string $path, string $disk = 'public'): void
    {
        try {
            self::ensure($path, $disk);
        } catch (\Throwable $e) {
            Log::warning('Gagal membuat varian gambar: '.$e->getMessage());
        }
    }

    public static function variantPath(string $path, int $width): string
    {
        $base = preg_replace('/\.[^.]+$/', '', $path);

        return $base.'-'.self::VERSION.'-'.$width.'.webp';
    }

    private static function write(string $path, string $target, int $width, int $height, string $disk): void
    {
        $source = self::createImage(Storage::disk($disk)->path($path));

        if (! $source instanceof \GdImage) {
            return;
        }

        $canvas = imagecreatetruecolor($width, $height);

        if (! $canvas instanceof \GdImage) {
            imagedestroy($source);

            return;
        }

        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        imagecopyresampled($canvas, $source, 0, 0, 0, 0, $width, $height, imagesx($source), imagesy($source));

        // Tulis ke berkas sementara lalu ganti nama: pembaca tak pernah melihat
        // berkas separuh jadi, dan kegagalan tak meninggalkan varian "ada" tapi rusak
        // (cache statis 1 tahun akan mengunci kerusakan itu).
        $finalPath = Storage::disk($disk)->path($target);
        $tempPath = $finalPath.'.tmp.'.bin2hex(random_bytes(4));
        $written = imagewebp($canvas, $tempPath, self::QUALITY);

        imagedestroy($canvas);
        imagedestroy($source);

        if ($written === false || ! is_file($tempPath)) {
            @unlink($tempPath);

            return;
        }

        if (! @rename($tempPath, $finalPath)) {
            @unlink($tempPath);
        }
    }

    /** @return \GdImage|false|null */
    private static function createImage(string $full)
    {
        $info = @getimagesize($full);

        return match ($info['mime'] ?? null) {
            'image/jpeg' => @imagecreatefromjpeg($full),
            'image/png' => @imagecreatefrompng($full),
            'image/webp' => @imagecreatefromwebp($full),
            'image/gif' => @imagecreatefromgif($full),
            default => null,
        };
    }
}
