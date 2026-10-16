<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Menu extends Model
{
    use HasFactory;

    protected $fillable = ['parent_id', 'label', 'url', 'target', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    // ---- Relationships ----
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order')->orderBy('id');
    }

    // ---- Scopes ----
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function scopeRoots(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    // ---- Helpers ----
    public function getIsExternalAttribute(): bool
    {
        return Str::startsWith((string) $this->url, ['http://', 'https://']);
    }

    /**
     * URL siap pakai untuk atribut href. Tautan internal diubah menjadi URL penuh.
     */
    public function getHrefAttribute(): string
    {
        if ($this->url === '' || $this->url === '#') {
            return '#';
        }

        if ($this->is_external || Str::startsWith($this->url, ['mailto:', 'tel:'])) {
            return $this->url;
        }

        return url($this->url);
    }
}
