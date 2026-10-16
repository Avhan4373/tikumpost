<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reel extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'url', 'is_published', 'sort_order'];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    // ---- Scopes ----
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderByDesc('created_at');
    }

    // ---- Helpers ----
    /**
     * Ekstrak tipe & shortcode dari tautan Instagram.
     * Mendukung /reel/, /reels/, /p/, dan /tv/.
     */
    public function getShortcodeAttribute(): ?string
    {
        if (preg_match('~instagram\.com/(reel|reels|p|tv)/([A-Za-z0-9_-]+)~i', (string) $this->url, $m)) {
            return $m[2];
        }

        return null;
    }

    public function getContentTypeAttribute(): ?string
    {
        if (preg_match('~instagram\.com/(reel|reels|p|tv)~i', (string) $this->url, $m)) {
            return $m[1] === 'reels' ? 'reel' : strtolower($m[1]);
        }

        return null;
    }

    /**
     * Permalink bersih untuk keperluan embed (tanpa query string pelacak).
     */
    public function getPermalinkAttribute(): string
    {
        $shortcode = $this->shortcode;

        if (! $shortcode) {
            return $this->url;
        }

        return 'https://www.instagram.com/'.($this->content_type ?? 'reel').'/'.$shortcode.'/';
    }

    public static function isValidInstagramUrl(?string $url): bool
    {
        return (bool) preg_match('~^https?://(www\.)?instagram\.com/(reel|reels|p|tv)/[A-Za-z0-9_-]+~i', (string) $url);
    }
}
