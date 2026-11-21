<?php

namespace App\Models;

use App\Enums\CommentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = ['article_id', 'user_id', 'parent_id', 'body', 'status', 'author_name', 'author_email'];

    protected function casts(): array
    {
        return ['status' => CommentStatus::class];
    }

    /** Nama penampil: pengguna terdaftar, atau nama tamu. */
    public function authorName(): string
    {
        return $this->user?->name ?? $this->author_name ?? 'Tamu';
    }

    public function initials(): string
    {
        return collect(explode(' ', $this->authorName()))
            ->filter()
            ->map(fn ($part) => mb_substr($part, 0, 1))
            ->take(2)
            ->implode('');
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', CommentStatus::Approved);
    }

    public function scopeRoot(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }
}
