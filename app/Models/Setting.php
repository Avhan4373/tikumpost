<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    /** @var array<string, string|null>|null */
    private static ?array $memo = null;

    /**
     * Semua setting mentah (key => value), di-cache selama satu request.
     *
     * @return array<string, string|null>
     */
    public static function map(): array
    {
        if (self::$memo === null) {
            self::$memo = static::query()->pluck('value', 'key')->all();
        }

        return self::$memo;
    }

    /** Nilai mentah tanpa fallback default. */
    public static function raw(string $key): ?string
    {
        return static::map()[$key] ?? null;
    }

    /** Nilai setting; pakai $default bila kosong atau belum ada. */
    public static function get(string $key, ?string $default = null): ?string
    {
        $value = static::raw($key);

        return ($value === null || $value === '') ? $default : $value;
    }

    public static function set(string $key, ?string $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        static::flush();
    }

    /**
     * @param  array<string, string|null>  $values
     */
    public static function setMany(array $values): void
    {
        foreach ($values as $key => $value) {
            static::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        static::flush();
    }

    /** Kosongkan cache in-memory (dipakai antar request/test). */
    public static function flush(): void
    {
        self::$memo = null;
    }
}
