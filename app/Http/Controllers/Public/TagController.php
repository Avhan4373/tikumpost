<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use App\Support\SiteSettings;
use Illuminate\View\View;

class TagController extends Controller
{
    public function show(Tag $tag): View
    {
        $articles = $tag->articles()->published()->with(['author', 'category'])
            ->orderByDesc('published_at')->paginate(SiteSettings::perPage());

        $categories = \App\Models\Category::ordered()
            ->withCount(['articles' => fn ($q) => $q->published()])->get();

        return view('public.listing', [
            'title' => 'Tag: '.$tag->name,
            'description' => null,
            'articles' => $articles,
            'categories' => $categories,
        ]);
    }
}
