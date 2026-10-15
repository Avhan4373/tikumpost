<?php

namespace App\Enums;

enum CommentStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu',
            self::Approved => 'Disetujui',
            self::Rejected => 'Ditolak',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::Pending => 'bg-amber-100 text-amber-700',
            self::Approved => 'bg-emerald-100 text-emerald-700',
            self::Rejected => 'bg-rose-100 text-rose-700',
        };
    }
}
