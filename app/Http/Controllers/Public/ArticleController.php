<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCommentRequest;
use App\Models\Article;
use App\Models\Comment;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function show(Article $article): View
    {
        abort_unless(
            $article->status->value === 'published' && $article->published_at <= now(),
            404
        );

        $article->increment('views');
        $article->load(['author', 'category', 'tags']);

        $comments = $article->comments()
            ->approved()->root()
            ->with(['user', 'replies.user'])
            ->latest()->get();

        $related = Article::published()
            ->where('id', '!=', $article->id)
            ->when($article->category_id, fn ($q) => $q->where('category_id', $article->category_id))
            ->with('category')->orderByDesc('published_at')->take(3)->get();

        return view('public.article', compact('article', 'comments', 'related'));
    }

    public function comment(StoreCommentRequest $request, Article $article): RedirectResponse
    {
        $isStaff = $request->user()->isStaff();

        Comment::create([
            'article_id' => $article->id,
            'user_id' => $request->user()->id,
            'parent_id' => $request->input('parent_id'),
            'body' => $request->validated('body'),
            'status' => $isStaff ? 'approved' : 'pending',
        ]);

        return back()->with('status', $isStaff
            ? 'Komentar Anda telah ditayangkan.'
            : 'Terima kasih! Komentar Anda menunggu moderasi.');
    }
}
