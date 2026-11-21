<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /** Sitemap XML untuk mesin pencari. */
    public function index(): Response
    {
        return response()->view('public.sitemap', [
            'articles' => Article::published()->select(['id', 'slug', 'updated_at'])->orderByDesc('published_at')->get(),
            'categories' => Category::ordered()->get(['id', 'slug']),
            'tags' => Tag::orderBy('name')->get(['id', 'slug']),
            'pages' => ['tentang', 'kontak', 'kebijakan-privasi'],
        ])->header('Content-Type', 'application/xml');
    }
}
