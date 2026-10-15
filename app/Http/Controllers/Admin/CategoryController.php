<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::withCount('articles')->ordered()->paginate(20);

        return view('admin.categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('admin.categories.form', [
            'category' => new Category(),
            'parents' => Category::ordered()->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);
        Category::create($data);

        return redirect()->route('admin.categories.index')->with('status', 'Kategori berhasil dibuat.');
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.form', [
            'category' => $category,
            'parents' => Category::where('id', '!=', $category->id)->ordered()->get(),
        ]);
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $category->update($this->validateData($request, $category));

        return redirect()->route('admin.categories.index')->with('status', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $category->delete();

        return back()->with('status', 'Kategori berhasil dihapus.');
    }

    private function validateData(Request $request, ?Category $category = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['nullable', 'string', 'max:120', Rule::unique('categories', 'slug')->ignore($category?->id)],
            'description' => ['nullable', 'string', 'max:500'],
            'parent_id' => ['nullable', 'exists:categories,id'],
            'order' => ['nullable', 'integer', 'min:0'],
        ]);

        $data['slug'] = ! empty($data['slug']) ? $data['slug'] : Str::slug($data['name']);
        $data['order'] = $data['order'] ?? 0;

        return $data;
    }
}
