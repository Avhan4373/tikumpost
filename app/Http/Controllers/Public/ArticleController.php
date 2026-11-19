<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCommentRequest;
use App\Models\Article;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Infographic;
use App\Models\Tag;
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

        // Data untuk sidebar kanan halaman detail berita.
        $popular = Article::published()->with('category')
            ->orderByDesc('views')->take(5)->get();

        $categories = Category::ordered()
            ->withCount(['articles' => fn ($q) => $q->published()])
            ->get();

        $tags = Tag::withCount('articles')
            ->orderByDesc('articles_count')->take(12)->get();

        $infographics = Infographic::published()->ordered()->take(2)->get();

        return view('public.article', [
            'article' => $article,
            'comments' => $comments,
            'related' => $related,
            'popular' => $popular,
            'categories' => $categories,
            'tags' => $tags,
            'infographics' => $infographics,
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
