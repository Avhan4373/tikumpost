<?php

namespace App\Policies;

use App\Models\Article;
use App\Models\User;

class ArticlePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff();
    }

    public function view(User $user, Article $article): bool
    {
        return $user->isStaff();
    }

    public function create(User $user): bool
    {
        return $user->isStaff();
    }

    public function update(User $user, Article $article): bool
    {
        if ($user->isAdmin() || $user->role?->value === 'editor') {
            return true;
        }

        return $article->user_id === $user->id;
    }

    public function delete(User $user, Article $article): bool
    {
        return $this->update($user, $article);
    }

    public function publish(User $user): bool
    {
        return $user->role?->canPublish() ?? false;
    }
}
