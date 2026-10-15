<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function show(Category $category): View
    {
        $articles = Article::published()->with(['author', 'category'])
            ->where('category_id', $category->id)
            ->orderByDesc('published_at')->paginate(9);

        $categories = Category::ordered()->withCount(['articles' => fn ($q) => $q->published()])->get();

        return view('public.listing', [
            'title' => 'Kategori: '.$category->name,
            'description' => $category->description,
            'articles' => $articles,
            'categories' => $categories,
        ]);
    }
}
