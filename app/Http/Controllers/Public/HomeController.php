<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $headline = Article::published()->with(['author', 'category'])
            ->orderByDesc('is_headline')->orderByDesc('published_at')->take(5)->get();

        $latest = Article::published()->with(['author', 'category'])
            ->orderByDesc('published_at')->skip(5)->take(6)->get();

        $popular = Article::published()->with('category')
            ->orderByDesc('views')->take(5)->get();

        $categories = Category::ordered()->withCount(['articles' => fn ($q) => $q->published()])->get();
        $tags = Tag::withCount('articles')->orderByDesc('articles_count')->take(12)->get();

        return view('public.home', compact('headline', 'latest', 'popular', 'categories', 'tags'));
    }
}
