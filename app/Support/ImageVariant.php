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

    /** @return list<array{path: string, width: int, height: int}> */
    public static function ensure(string $path, string $disk = 'public'): array
    {
        $source = self::intrinsic($path, $disk);

        if ($source === null || ! function_exists('imagewebp')) {
            return [];
        }

        [$sourceWidth, $sourceHeight] = $source;
        $variants = [];

        foreach (self::WIDTHS as $width) {
            // Jangan upscale: varian hanya untuk lebar di bawah gambar asli.
            if ($width >= $sourceWidth) {
                continue;
            }

            $target = self::variantPath($path, $width);
            $height = (int) round($sourceHeight * ($width / $sourceWidth));

            if (! Storage::disk($disk)->exists($target)) {
                self::write($path, $target, $width, $height, $disk);
            }

            if (Storage::disk($disk)->exists($target)) {
                $variants[] = ['path' => $target, 'width' => $width, 'height' => $height];
            }
        }

        return $variants;
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
        if (! Storage::disk($disk)->exists($path)) {
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

        if ($source === null) {
            return;
        }

        $canvas = imagecreatetruecolor($width, $height);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        imagecopyresampled($canvas, $source, 0, 0, 0, 0, $width, $height, imagesx($source), imagesy($source));
        imagewebp($canvas, Storage::disk($disk)->path($target), self::QUALITY);
        imagedestroy($canvas);
        imagedestroy($source);
    }

    /** @return \GdImage|null */
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
