<?php

namespace App\Enums;

enum ArticleStatus: string
{
    case Draft = 'draft';
    case Review = 'review';
    case Scheduled = 'scheduled';
    case Published = 'published';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draf',
            self::Review => 'Menunggu Review',
            self::Scheduled => 'Terjadwal',
            self::Published => 'Terbit',
            self::Archived => 'Diarsipkan',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::Draft => 'bg-gray-100 text-gray-700',
            self::Review => 'bg-amber-100 text-amber-700',
            self::Scheduled => 'bg-blue-100 text-blue-700',
            self::Published => 'bg-emerald-100 text-emerald-700',
            self::Archived => 'bg-rose-100 text-rose-700',
        };
    }

    public static function options(): array
    {
        return array_map(
            fn (self $s) => ['value' => $s->value, 'label' => $s->label()],
            self::cases()
        );
    }
}
