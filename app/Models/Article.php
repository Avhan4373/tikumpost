<?php

namespace App\Models;

use App\Enums\ArticleStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'category_id', 'title', 'slug', 'excerpt', 'body',
        'featured_image', 'status', 'is_headline', 'views',
        'meta_title', 'meta_description', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ArticleStatus::class,
            'is_headline' => 'boolean',
            'views' => 'integer',
            'published_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected static function booted(): void
    {
        static::saving(function (Article $article) {
            if (empty($article->slug)) {
                $article->slug = static::uniqueSlug($article->title, $article->id);
            }
        });
    }

    public static function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        // Judul yang seluruhnya simbol (mis. "!!!") menghasilkan slug kosong,
        // yang membuat route binding ke /edit, /show, dan hapus gagal. Pakai
        // fallback "artikel" agar slug tidak pernah kosong.
        $base = Str::limit(Str::slug($title), 200, '');
        $base = $base !== '' ? $base : 'artikel';

        $slug = $base;
        $i = 2;
        while (static::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }

    // ---- Relationships ----
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    // ---- Scopes ----
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', ArticleStatus::Published)
            ->where('published_at', '<=', now());
    }

    public function scopeHeadline(Builder $query): Builder
    {
        return $query->where('is_headline', true);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('title', 'like', "%{$term}%")
                ->orWhere('excerpt', 'like', "%{$term}%")
                ->orWhere('body', 'like', "%{$term}%");
        });
    }

    // ---- Accessors ----
    /**
     * Sebagian artikel hasil impor memakai &nbsp; sebagai pemisah kata,
     * sehingga paragraf tidak bisa turun baris dan meluber di layar kecil.
     * Normalisasi ke spasi biasa saat dibaca (termasuk di form admin).
     */
    protected function body(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value === null
                ? null
                : str_replace(['&nbsp;', '&#160;', "\u{00A0}"], ' ', $value),
        );
    }

    /**
     * Ringkasan tidak lagi diisi manual di admin. Nilai lama (jika ada) tetap
     * dipakai; artikel baru memakai potongan awal isi artikel.
     */
    public function getExcerptTextAttribute(): string
    {
        return $this->excerpt ?: Str::limit(strip_tags($this->body), 160);
    }

    public function getReadingTimeAttribute(): int
    {
        $words = str_word_count(strip_tags($this->body));

        return max(1, (int) ceil($words / 200));
    }
}
