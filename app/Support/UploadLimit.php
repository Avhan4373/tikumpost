<?php

namespace App\Support;

/**
 * Batas unggah efektif berdasarkan konfigurasi PHP dan batas aplikasi.
 *
 * PHP menolak file yang melebihi `upload_max_filesize` sebelum Laravel
 * sempat menjalankan validasi, sehingga editor perlu tahu batas nyata ini
 * untuk memperkecil gambar di sisi browser.
 */
class UploadLimit
{
    public const APP_MAX_BYTES = 8 * 1024 * 1024;

    public static function bytes(): int
    {
        $limits = array_filter([
            static::iniBytes((string) ini_get('upload_max_filesize')),
            static::iniBytes((string) ini_get('post_max_size')),
            self::APP_MAX_BYTES,
        ]);

        return $limits === [] ? self::APP_MAX_BYTES : min($limits);
    }

    public static function label(?int $bytes = null): string
    {
        $bytes ??= static::bytes();

        if ($bytes >= 1024 * 1024) {
            $value = $bytes / 1024 / 1024;
            $formatted = fmod($value, 1.0) === 0.0
                ? (string) (int) $value
                : number_format($value, 1, ',', '.');

            return $formatted.' MB';
        }

        return (int) ceil($bytes / 1024).' KB';
    }

    private static function iniBytes(string $value): ?int
    {
        $value = trim($value);

        if ($value === '' || $value === '-1') {
            return null;
        }

        $unit = strtolower(substr($value, -1));
        $number = (float) $value;

        $bytes = match ($unit) {
            'g' => $number * 1024 ** 3,
            'm' => $number * 1024 ** 2,
            'k' => $number * 1024,
            default => $number,
        };

        // 0 berarti tanpa batas di php.ini; abaikan agar tidak jadi nilai minimum.
        return $bytes > 0 ? (int) $bytes : null;
    }
}
