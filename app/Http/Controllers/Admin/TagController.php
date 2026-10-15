<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TagController extends Controller
{
    public function index(): View
    {
        $tags = Tag::withCount('articles')->orderBy('name')->paginate(30);

        return view('admin.tags.index', compact('tags'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:60'],
        ]);
        Tag::create(['name' => $data['name'], 'slug' => Str::slug($data['name'])]);

        return back()->with('status', 'Tag berhasil ditambahkan.');
    }

    public function update(Request $request, Tag $tag): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:60', Rule::unique('tags', 'name')->ignore($tag->id)],
        ]);
        $tag->update(['name' => $data['name'], 'slug' => Str::slug($data['name'])]);

        return back()->with('status', 'Tag berhasil diperbarui.');
    }

    public function destroy(Tag $tag): RedirectResponse
    {
        $tag->delete();

        return back()->with('status', 'Tag berhasil dihapus.');
    }
}
