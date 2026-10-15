<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Editor = 'editor';
    case Author = 'author';
    case Reader = 'reader';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrator',
            self::Editor => 'Editor',
            self::Author => 'Penulis',
            self::Reader => 'Pembaca',
        };
    }

    public function isStaff(): bool
    {
        return $this !== self::Reader;
    }

    public function canManageUsers(): bool
    {
        return $this === self::Admin;
    }

    public function canPublish(): bool
    {
        return in_array($this, [self::Admin, self::Editor], true);
    }
}
