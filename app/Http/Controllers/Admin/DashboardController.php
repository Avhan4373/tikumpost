<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ArticleStatus;
use App\Enums\CommentStatus;
use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Comment;
use App\Models\Subscriber;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'articles' => Article::count(),
            'published' => Article::where('status', ArticleStatus::Published)->count(),
            'drafts' => Article::where('status', ArticleStatus::Draft)->count(),
            'comments' => Comment::count(),
            'pending_comments' => Comment::where('status', CommentStatus::Pending)->count(),
            'subscribers' => Subscriber::count(),
            'users' => User::count(),
        ];

        $recent = Article::with(['author', 'category'])->latest()->take(8)->get();
        $pendingComments = Comment::with(['user', 'article'])
            ->where('status', CommentStatus::Pending)->latest()->take(5)->get();

        return view('admin.dashboard', compact('stats', 'recent', 'pendingComments'));
    }
}
