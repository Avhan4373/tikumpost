<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;

class CommentPolicy
{
    public function moderate(User $user): bool
    {
        return $user->isStaff();
    }

    public function update(User $user, Comment $comment): bool
    {
        return $user->isStaff();
    }

    public function delete(User $user, Comment $comment): bool
    {
        return $user->isStaff() || $comment->user_id === $user->id;
    }
}
