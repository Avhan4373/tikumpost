<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ArticleStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreArticleRequest;
use App\Http\Requests\UpdateArticleRequest;
use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Support\ImageVariant;
use App\Support\UploadLimit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Article::class);

        $sort = $request->query('sort');
        $direction = $request->query('dir') === 'asc' ? 'asc' : 'desc';

        $articles = Article::with(['author', 'category'])
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
            ->when($request->query('q'), fn ($q, $term) => $q->search($term))
            ->when(
                $sort === 'views',
                fn ($q) => $q->orderBy('views', $direction),
                fn ($q) => $q->latest(),
            )
            ->paginate(15)
            ->withQueryString();

        return view('admin.articles.index', compact('articles'));
    }

    public function create(): View
    {
        $this->authorize('create', Article::class);

        return view('admin.articles.form', [
            'article' => new Article(['status' => ArticleStatus::Draft]),
            'categories' => Category::ordered()->get(),
            'tags' => Tag::orderBy('name')->get(),
            'uploadMaxBytes' => UploadLimit::bytes(),
        ]);
    }

    public function store(StoreArticleRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;
        $data['slug'] = Article::uniqueSlug($data['title']);
        $data['is_headline'] = $request->boolean('is_headline');
        $data['featured_image'] = $this->storeImage($request);
        $data = $this->normalizePublish($data);

        $article = Article::create($data);
        $article->tags()->sync($request->input('tags', []));

        return redirect()->route('admin.articles.index')->with('status', 'Artikel berhasil dibuat.');
    }

    public function edit(Article $article): View
    {
        $this->authorize('update', $article);

        return view('admin.articles.form', [
            'article' => $article,
            'categories' => Category::ordered()->get(),
            'tags' => Tag::orderBy('name')->get(),
            'uploadMaxBytes' => UploadLimit::bytes(),
        ]);
    }

    public function update(UpdateArticleRequest $request, Article $article): RedirectResponse
    {
        $this->authorize('update', $article);

        $data = $request->validated();
        $data['is_headline'] = $request->boolean('is_headline');

        if ($request->hasFile('featured_image')) {
            if ($article->featured_image) {
                Storage::disk('public')->delete($article->featured_image);
            }
            $data['featured_image'] = $this->storeImage($request);
        }

        $data = $this->normalizePublish($data, $article);
        $article->update($data);
        $article->tags()->sync($request->input('tags', []));

        return redirect()->route('admin.articles.index')->with('status', 'Artikel berhasil diperbarui.');
    }

    public function destroy(Article $article): RedirectResponse
    {
        $this->authorize('delete', $article);

        if ($article->featured_image) {
            Storage::disk('public')->delete($article->featured_image);
        }
        $article->delete();

        return back()->with('status', 'Artikel berhasil dihapus.');
    }

    private function storeImage(Request $request): ?string
    {
        if (! $request->hasFile('featured_image')) {
            return null;
        }

        $path = $request->file('featured_image')->store('articles', 'public');
        ImageVariant::ensureQuietly($path);

        return $path;
    }

    /**
     * Selaraskan published_at dengan status yang dipilih.
     *
     * Status yang dipilih admin selalu menang: "Terbit" selalu terbit sekarang,
     * "Terjadwal" menyimpan tanggal jadwal. Perbandingan tanggal memakai objek
     * Carbon — membandingkan string "2026-10-16T14:30" dengan now() sebagai teks
     * membuat 'T' > ' ' sehingga "Terbit" salah dianggap terjadwal.
     */
    private function normalizePublish(array $data, ?Article $article = null): array
    {
        $status = $data['status'] instanceof ArticleStatus ? $data['status']->value : $data['status'];

        $publishedAt = filled($data['published_at'] ?? null)
            ? Carbon::parse($data['published_at'])
            : null;

        if ($status === ArticleStatus::Published->value) {
            // "Terbit" selalu menang: terbit sekarang. Tanggal lampau dipertahankan
            // untuk backdate, sedangkan tanggal masa depan diabaikan.
            if (! $publishedAt || $publishedAt->isFuture()) {
                $publishedAt = now();
            }
        } elseif ($status === ArticleStatus::Scheduled->value) {
            // "Terjadwal" butuh tanggal; tanpa tanggal pakai jadwal lama atau sekarang.
            $publishedAt ??= $article?->published_at ?? now();
        }

        if ($publishedAt) {
            $data['published_at'] = $publishedAt;
        }

        return $data;
    }
}
