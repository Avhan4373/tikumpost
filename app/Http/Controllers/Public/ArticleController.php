<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCommentRequest;
use App\Models\Article;
use App\Models\Comment;
use App\Support\SiteSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function show(Request $request, Article $article): View
    {
        abort_unless(
            $article->status->value === 'published' && $article->published_at <= now(),
            404
        );

        // Hitung 1x per pengunjung (per sesi) supaya refresh/pindah halaman
        // tidak menggelembungkan angka "dibaca".
        $viewed = (array) $request->session()->get('viewed_articles', []);

        if (! in_array($article->id, $viewed, true)) {
            $article->increment('views');
            $request->session()->put('viewed_articles', array_slice([...$viewed, $article->id], -200));
        }

        $article->load(['author', 'category', 'tags']);

        $comments = $article->comments()
            ->approved()->root()
            ->with(['user', 'replies.user'])
            ->latest()->get();

        $related = Article::published()
            ->where('id', '!=', $article->id)
            ->when($article->category_id, fn ($q) => $q->where('category_id', $article->category_id))
            ->with('category')->orderByDesc('published_at')->take(3)->get();

        return view('public.article', [
            'article' => $article,
            'comments' => $comments,
            'related' => $related,
            'commentsEnabled' => (bool) SiteSettings::value('comments_enabled', true),
        ]);
    }

    public function comment(StoreCommentRequest $request, Article $article): RedirectResponse
    {
        abort_unless((bool) SiteSettings::value('comments_enabled', true), 403);

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
