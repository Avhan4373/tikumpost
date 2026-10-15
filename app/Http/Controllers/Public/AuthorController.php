<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\View\View;

class AuthorController extends Controller
{
    public function show(User $author): View
    {
        abort_unless($author->isStaff(), 404);

        $articles = Article::published()->with(['author', 'category'])
            ->where('user_id', $author->id)
            ->orderByDesc('published_at')->paginate(9);

        $categories = Category::ordered()
            ->withCount(['articles' => fn ($q) => $q->published()])->get();

        return view('public.listing', [
            'title' => 'Penulis: '.$author->name,
            'description' => $author->bio,
            'author' => $author,
            'articles' => $articles,
            'categories' => $categories,
        ]);
    }
}
