<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Subscriber extends Model
{
    use HasFactory;

    protected $fillable = ['email', 'token', 'verified_at'];

    protected function casts(): array
    {
        return ['verified_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::creating(function (Subscriber $subscriber) {
            $subscriber->token ??= Str::random(40);
        });
    }
}
