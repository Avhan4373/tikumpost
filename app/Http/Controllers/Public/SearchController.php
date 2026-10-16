<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;
use App\Support\SiteSettings;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q'));

        $articles = Article::published()->with(['author', 'category'])
            ->search($q)
            ->orderByDesc('published_at')->paginate(SiteSettings::perPage())->withQueryString();

        $categories = Category::ordered()
            ->withCount(['articles' => fn ($query) => $query->published()])->get();

        return view('public.listing', [
            'title' => $q !== '' ? 'Hasil pencarian: "'.$q.'"' : 'Pencarian',
            'description' => $articles->total().' artikel ditemukan.',
            'articles' => $articles,
            'categories' => $categories,
        ]);
    }
}
