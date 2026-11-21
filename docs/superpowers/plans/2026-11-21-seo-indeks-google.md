# SEO Indeks Google Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Membuat `https://tikumpost.id` layak dan cepat terindeks Google: sitemap XML, canonical, robots meta, JSON-LD, judul beranda, Open Graph/Twitter, dan jalur verifikasi Search Console.

**Architecture:** Semua perubahan ada di lapisan view publik + satu controller baru + satu field setelan. Tak ada dependency baru, tak ada perubahan skema DB (field baru memakai tabel `settings` yang sudah ada dan bersifat schema-driven).

**Tech Stack:** Laravel 13, Blade, PHP 8.2+, PHPUnit (`php artisan test`), Laravel Pint, Tailwind (tak disentuh).

**Spec:** `docs/superpowers/specs/2026-11-21-seo-indeks-google-design.md`

## Global Constraints

- Tak menambah dependency Composer/npm apa pun.
- Tak mengubah skema database; setelan baru ikut `app/Support/SiteSettings.php` (schema-driven).
- Tak mengubah tampilan yang terlihat pembaca (judul tab browser boleh berubah).
- Semua teks berbahasa Indonesia.
- Setiap task berakhir dengan `php artisan test` hijau dan satu commit.
- `public/robots.txt` statis (dilayani web server, bukan Laravel) → diverifikasi manual lewat `curl`, bukan PHPUnit.
- Task 2–4 menyentuh `resources/views/layouts/public.blade.php`; task 5 menyentuh `resources/views/public/article.blade.php`. Jalankan berurutan, jangan paralel.
- Gunakan `route()` untuk URL absolut; helper `url()` untuk membungkus `Storage::disk('public')->url(...)` menjadi absolut.

## Review Focus

- **Canonical paginasi.** Halaman `?page=2` harus canonical ke dirinya sendiri, bukan ke halaman 1; kalau keliru, artikel di halaman lanjutan hilang dari indeks. → tes `test_halaman_kedua_punya_canonical_ke_halaman_kedua` (Task 2).
- **JSON-LD ter-escape.** JSON-LD wajib dirender mentah (`{!! !!}`); kalau memakai `{{ }}`, isinya jadi `&quot;` dan rich result mati. → tes `test_beranda_memuat_structured_data_organisasi_dan_website` (Task 4) dan `test_halaman_artikel_memuat_newsarticle_dan_breadcrumb` (Task 5).
- **Judul artikel dengan tanda kutip/`&`.** `"` atau `&` di judul tidak boleh memecah JSON-LD atau XML. → tes `test_newsarticle_tetap_valid_untuk_judul_bertanda_kutip` (Task 5).
- **Sitemap XML valid.** Dokumen harus mulai tepat di `<?xml` tanpa spasi/HTML apa pun sebelumnya. → tes `test_sitemap_dimulai_dengan_deklarasi_xml` (Task 1).
- **og:image tanpa gambar unggulan.** Artikel tanpa `featured_image` tetap harus menghasilkan `og:image` absolut. → tes `test_og_image_absolut_bila_tanpa_gambar_unggulan` (Task 2).
- **Mode perawatan.** Saat `maintenance_mode` aktif, `/sitemap.xml` ikut tidak tersedia dengan status **503** (route berada di grup `EnsureSiteIsLive`, yang mengembalikan `503` + `Retry-After`, bukan `404`). Ini disengaja: crawler tidak menerima sitemap saat situs ditutup, dan `503` adalah sinyal yang benar untuk "sementara tidak tersedia". Diverifikasi tes `test_sitemap_tidak_tersedia_saat_mode_perawatan`. Jangan "memperbaiki" ini tanpa persetujuan.

---

### Task 1: Sitemap XML + robots.txt

**Files:**
- Create: `app/Http/Controllers/Public/SitemapController.php`
- Create: `resources/views/public/sitemap.blade.php`
- Modify: `routes/web.php:22` (sisipkan sebelum route kategori)
- Modify: `public/robots.txt`
- Test: `tests/Feature/SeoTest.php`

**Interfaces:**
- Consumes: `Article::published()` (`app/Models/Article.php:88`), `Category::ordered()` (`app/Models/Category.php:29`), `Tag` (model di `app/Models/Tag.php`), `PageController` slug statis (`tentang`, `kontak`, `kebijakan-privasi`).
- Produces: route bernama `sitemap` → `GET /sitemap.xml`, `SitemapController::index(): \Illuminate\Http\Response`.

- [ ] **Step 1: Tulis tes yang gagal**

Buat `tests/Feature/SeoTest.php`:

```php
<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_sitemap_memuat_artikel_kategori_tag_dan_halaman_statis(): void
    {
        $article = Article::factory()->create();
        $category = Category::factory()->create();
        $tag = Tag::factory()->create();

        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $this->assertStringContainsString('xml', (string) $response->headers->get('Content-Type'));

        $body = $response->getContent();

        $this->assertStringContainsString(route('articles.show', $article), $body);
        $this->assertStringContainsString(route('categories.show', $category), $body);
        $this->assertStringContainsString(route('tags.show', $tag), $body);
        $this->assertStringContainsString(route('pages.show', 'tentang'), $body);
    }

    public function test_sitemap_tidak_memuat_artikel_draft(): void
    {
        $draft = Article::factory()->draft()->create();

        $this->assertStringNotContainsString(
            route('articles.show', $draft),
            $this->get('/sitemap.xml')->getContent()
        );
    }

    public function test_sitemap_dimulai_dengan_deklarasi_xml(): void
    {
        $body = $this->get('/sitemap.xml')->assertOk()->getContent();

        $this->assertStringStartsWith('<?xml', ltrim($body));
    }
}
```

- [ ] **Step 2: Jalankan tes, pastikan gagal**

Run: `php artisan test --filter=SeoTest`
Expected: FAIL — 404 pada `/sitemap.xml`.

- [ ] **Step 3: Buat controller**

`app/Http/Controllers/Public/SitemapController.php`:

```php
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
```

- [ ] **Step 4: Buat view sitemap**

`resources/views/public/sitemap.blade.php`:

```blade
{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc>{{ route('home') }}</loc>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>
    @foreach ($articles as $article)
        <url>
            <loc>{{ route('articles.show', $article) }}</loc>
            <lastmod>{{ $article->updated_at->toAtomString() }}</lastmod>
            <changefreq>weekly</changefreq>
            <priority>0.8</priority>
        </url>
    @endforeach
    @foreach ($categories as $category)
        <url>
            <loc>{{ route('categories.show', $category) }}</loc>
            <changefreq>daily</changefreq>
            <priority>0.6</priority>
        </url>
    @endforeach
    @foreach ($tags as $tag)
        <url>
            <loc>{{ route('tags.show', $tag) }}</loc>
            <changefreq>weekly</changefreq>
            <priority>0.5</priority>
        </url>
    @endforeach
    @foreach ($pages as $slug)
        <url>
            <loc>{{ route('pages.show', $slug) }}</loc>
            <changefreq>monthly</changefreq>
            <priority>0.3</priority>
        </url>
    @endforeach
</urlset>
```

- [ ] **Step 5: Daftarkan route**

Di `routes/web.php`, di dalam grup `EnsureSiteIsLive`, sisipkan sebelum baris `Route::get('/kategori/{category}', ...)`:

```php
    Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
```

Tambahkan `use App\Http\Controllers\Public\SitemapController;` pada blok `use` (urut alfabetis, setelah `SearchController`).

- [ ] **Step 6: Perbarui robots.txt**

`public/robots.txt` menjadi:

```
User-agent: *
Disallow:

Sitemap: https://tikumpost.id/sitemap.xml
```

- [ ] **Step 7: Jalankan tes, pastikan lulus**

Run: `php artisan test --filter=SeoTest`
Expected: PASS (3 tes).

- [ ] **Step 8: Commit**

```bash
git add app/Http/Controllers/Public/SitemapController.php resources/views/public/sitemap.blade.php routes/web.php public/robots.txt tests/Feature/SeoTest.php
git commit -m "feat(seo): sitemap XML dan baris Sitemap di robots.txt"
```

---

### Task 2: Canonical, robots meta, Open Graph/Twitter

**Files:**
- Modify: `resources/views/layouts/public.blade.php:6` (sisipkan canonical + robots meta) dan `:16-19` (blok OG)
- Modify: `resources/views/public/listing.blade.php:1-4`
- Modify: `app/Http/Controllers/Public/SearchController.php:25-30`
- Modify: `resources/views/public/article.blade.php:4` (override og:type/og:image)
- Test: `tests/Feature/SeoTest.php` (tambahkan metode)

**Interfaces:**
- Consumes: `Storage` facade (sudah dipakai di header publik).
- Produces: section Blade `robots` (default `index,follow`), `og_type` (default `website`), `og_image` (default = logo situs absolut, fallback `url('favicon.ico')`). Variabel view opsional `$noindex`.

- [ ] **Step 1: Tulis tes yang gagal**

Tambahkan ke `tests/Feature/SeoTest.php` (sebelum kurung kurawal penutup kelas):

```php
    public function test_halaman_artikel_punya_canonical_diri_sendiri(): void
    {
        $article = Article::factory()->create();

        $this->get(route('articles.show', $article))->assertOk()
            ->assertSee('<link rel="canonical" href="'.route('articles.show', $article).'">', false);
    }

    public function test_halaman_kedua_punya_canonical_ke_halaman_kedua(): void
    {
        $category = Category::factory()->create();
        Article::factory()->count(12)->create(['category_id' => $category->id]);

        $this->get(route('categories.show', ['category' => $category, 'page' => 2]))->assertOk()
            ->assertSee('<link rel="canonical" href="'.route('categories.show', $category).'?page=2">', false);
    }

    public function test_halaman_pencarian_noindex(): void
    {
        $this->get(route('search', ['q' => 'konawe']))->assertOk()
            ->assertSee('<meta name="robots" content="noindex,follow">', false);
    }

    public function test_halaman_artikel_index_follow(): void
    {
        $article = Article::factory()->create();

        $this->get(route('articles.show', $article))->assertOk()
            ->assertSee('<meta name="robots" content="index,follow">', false);
    }

    public function test_og_image_absolut_bila_tanpa_gambar_unggulan(): void
    {
        $article = Article::factory()->create(['featured_image' => null]);

        $html = $this->get(route('articles.show', $article))->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/<meta property="og:image" content="https?:\/\/[^"]+">/', $html);
    }

    public function test_artikel_dengan_gambar_unggulan_memakai_og_type_article(): void
    {
        $article = Article::factory()->create(['featured_image' => 'artikel/foto.jpg']);

        $this->get(route('articles.show', $article))->assertOk()
            ->assertSee('<meta property="og:type" content="article">', false);
    }
```

- [ ] **Step 2: Jalankan tes, pastikan gagal**

Run: `php artisan test --filter=SeoTest`
Expected: FAIL — canonical/robots/og:image belum ada.

- [ ] **Step 3: Perbarui head layout**

Di `resources/views/layouts/public.blade.php`, setelah baris `<meta name="csrf-token" ...>`, sisipkan:

```blade
    <link rel="canonical" href="{{ request()->url().(request()->has('page') ? '?page='.request()->integer('page') : '') }}">
    <meta name="robots" content="@yield('robots', 'index,follow')">
```

Ganti baris `<meta property="og:type" content="website">` dan tambahkan blok berikut (taruh tepat sebelum `@stack('meta')`):

```blade
    @php($ogImage = ! empty($site['site_logo']) ? url(Storage::disk('public')->url($site['site_logo'])) : url('favicon.ico'))
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:image" content="@yield('og_image', $ogImage)">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('title', $site['site_name'] ?? config('app.name', 'TikumPost'))">
    <meta name="twitter:description" content="@yield('meta_description', $site['meta_description'] ?? '')">
    <meta name="twitter:image" content="@yield('og_image', $ogImage)">
```

Catatan: empat tag OG yang lama (`og:title`, `og:site_name`, `og:description`, `og:type`) tetap ada; hanya baris `og:type` yang diganti agar bisa di-override.

- [ ] **Step 4: Override OG di halaman artikel**

Di `resources/views/public/article.blade.php`, setelah baris `@section('meta_description', ...)`, sisipkan:

```blade

@section('og_type', 'article')
@section('og_image', $article->featured_image ? url(Storage::disk('public')->url($article->featured_image)) : url('favicon.ico'))
```

- [ ] **Step 5: Noindex halaman pencarian**

Di `resources/views/public/listing.blade.php`, setelah baris `@extends('layouts.public')`, sisipkan:

```blade
@if (! empty($noindex))
    @section('robots', 'noindex,follow')
@endif
```

Di `app/Http/Controllers/Public/SearchController.php`, tambahkan `'noindex' => true,` ke dalam array data `view('public.listing', [...])`.

- [ ] **Step 6: Jalankan tes, pastikan lulus**

Run: `php artisan view:clear && php artisan test --filter=SeoTest`
Expected: PASS (9 tes).

- [ ] **Step 7: Commit**

```bash
git add resources/views/layouts/public.blade.php resources/views/public/listing.blade.php resources/views/public/article.blade.php app/Http/Controllers/Public/SearchController.php tests/Feature/SeoTest.php
git commit -m "feat(seo): canonical, robots meta, Open Graph/Twitter, noindex halaman cari"
```

---

### Task 3: Judul beranda

**Files:**
- Modify: `resources/views/layouts/public.blade.php:8`
- Modify: `resources/views/public/home.blade.php:3`
- Modify: `resources/views/public/home-klasik.blade.php:3`
- Test: `tests/Feature/SeoTest.php` (tambahkan metode)

**Interfaces:**
- Consumes: —
- Produces: section Blade opsional `title_full` — bila diisi, layout memakai isinya apa adanya (tanpa akhiran `· Nama Situs`).

- [ ] **Step 1: Tulis tes yang gagal**

Tambahkan ke `tests/Feature/SeoTest.php`:

```php
    public function test_judul_beranda_memuat_nama_situs_dan_tagline(): void
    {
        $html = $this->get(route('home'))->assertOk()->getContent();

        $this->assertStringContainsString(
            '<title>TikumPost — Portal berita terkini, akurat, dan berimbang</title>',
            $html
        );
        $this->assertStringNotContainsString('Beranda &middot;', $html);
    }

    public function test_judul_halaman_artikel_tetap_memakai_akhiran_nama_situs(): void
    {
        $article = Article::factory()->create();

        $html = $this->get(route('articles.show', $article))->assertOk()->getContent();

        $this->assertStringContainsString('&middot; TikumPost</title>', $html);
    }
```

- [ ] **Step 2: Jalankan tes, pastikan gagal**

Run: `php artisan test --filter=SeoTest`
Expected: FAIL — judul beranda masih `Beranda &middot; TikumPost`.

- [ ] **Step 3: Tambah dukungan `title_full` di layout**

Di `resources/views/layouts/public.blade.php`, ganti baris `<title>` menjadi:

```blade
    <title>@hasSection('title_full')@yield('title_full')@else @yield('title', 'Beranda') &middot; {{ $site['site_name'] ?? config('app.name', 'TikumPost') }}@endif</title>
```

- [ ] **Step 4: Pakai di beranda**

Di `resources/views/public/home.blade.php` dan `resources/views/public/home-klasik.blade.php`, ganti `@section('title', 'Beranda')` menjadi:

```blade
@section('title_full', ($site['site_name'] ?? 'TikumPost').' — '.($site['site_tagline'] ?? ''))
```

- [ ] **Step 5: Jalankan tes, pastikan lulus**

Run: `php artisan view:clear && php artisan test --filter=SeoTest`
Expected: PASS (11 tes).

- [ ] **Step 6: Commit**

```bash
git add resources/views/layouts/public.blade.php resources/views/public/home.blade.php resources/views/public/home-klasik.blade.php tests/Feature/SeoTest.php
git commit -m "feat(seo): judul beranda memakai nama situs dan tagline"
```

---

### Task 4: JSON-LD Organization + WebSite (beranda)

**Files:**
- Modify: `resources/views/layouts/public.blade.php:20` (setelah `@stack('meta')`)
- Test: `tests/Feature/SeoTest.php` (tambahkan metode)

**Interfaces:**
- Consumes: —
- Produces: stack Blade `schema` (dipakai Task 5 untuk menyuntik JSON-LD per halaman).

- [ ] **Step 1: Tulis tes yang gagal**

Tambahkan ke `tests/Feature/SeoTest.php`:

```php
    public function test_beranda_memuat_structured_data_organisasi_dan_website(): void
    {
        $html = $this->get(route('home'))->assertOk()->getContent();

        $this->assertStringContainsString('"@type":"Organization"', $html);
        $this->assertStringContainsString('"@type":"WebSite"', $html);
    }

    public function test_halaman_artikel_tidak_memuat_organisasi_bawaan(): void
    {
        $article = Article::factory()->create();

        $html = $this->get(route('articles.show', $article))->assertOk()->getContent();

        // Organisasi hanya disuntik di beranda; halaman lain punya schema sendiri.
        $this->assertStringNotContainsString('"@type":"WebSite"', $html);
    }
```

- [ ] **Step 2: Jalankan tes, pastikan gagal**

Run: `php artisan test --filter=SeoTest`
Expected: FAIL — belum ada JSON-LD.

- [ ] **Step 3: Implementasi**

Di `resources/views/layouts/public.blade.php`, tepat setelah `@stack('meta')`, sisipkan:

```blade
    @stack('schema')
    @if (request()->routeIs('home'))
        <script type="application/ld+json">{!! json_encode([
            '@context' => 'https://schema.org',
            '@graph' => [
                array_filter([
                    '@type' => 'Organization',
                    'name' => $site['site_name'] ?? config('app.name', 'TikumPost'),
                    'url' => route('home'),
                    'logo' => ! empty($site['site_logo']) ? url(Storage::disk('public')->url($site['site_logo'])) : null,
                ]),
                [
                    '@type' => 'WebSite',
                    'name' => $site['site_name'] ?? config('app.name', 'TikumPost'),
                    'url' => route('home'),
                ],
            ],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    @endif
```

- [ ] **Step 4: Jalankan tes, pastikan lulus**

Run: `php artisan view:clear && php artisan test --filter=SeoTest`
Expected: PASS (13 tes).

- [ ] **Step 5: Commit**

```bash
git add resources/views/layouts/public.blade.php tests/Feature/SeoTest.php
git commit -m "feat(seo): JSON-LD Organization dan WebSite di beranda"
```

---

### Task 5: JSON-LD NewsArticle + BreadcrumbList (artikel)

**Files:**
- Modify: `resources/views/public/article.blade.php` (setelah baris 4, setelah `@section('og_image', ...)` dari Task 2)
- Test: `tests/Feature/SeoTest.php` (tambahkan metode)

**Interfaces:**
- Consumes: stack `schema` (dari Task 4), `$article->excerpt_text` (`app/Models/Article.php:131`), `$article->author`, `$article->category`.
- Produces: —

- [ ] **Step 1: Tulis tes yang gagal**

Tambahkan ke `tests/Feature/SeoTest.php`:

```php
    public function test_halaman_artikel_memuat_newsarticle_dan_breadcrumb(): void
    {
        $article = Article::factory()->create(['featured_image' => 'artikel/foto.jpg']);

        $html = $this->get(route('articles.show', $article))->assertOk()->getContent();

        $this->assertStringContainsString('"@type":"NewsArticle"', $html);
        $this->assertStringContainsString('"@type":"BreadcrumbList"', $html);
        $this->assertStringContainsString('"datePublished"', $html);
    }

    public function test_newsarticle_tetap_valid_untuk_judul_bertanda_kutip(): void
    {
        $article = Article::factory()->create(['title' => 'Diskusi "Tol Laut" & Kuota Sangihe']);

        $html = $this->get(route('articles.show', $article))->assertOk()->getContent();

        $this->assertStringContainsString('"@type":"NewsArticle"', $html);
        $this->assertStringNotContainsString('&quot;@type&quot;', $html);

        // JSON harus tetap bisa di-decode.
        preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $html, $matches);
        $this->assertNotEmpty($matches[1]);

        foreach ($matches[1] as $json) {
            $this->assertIsArray(json_decode($json, true), 'JSON-LD tidak valid: '.$json);
        }
    }
```

- [ ] **Step 2: Jalankan tes, pastikan gagal**

Run: `php artisan test --filter=SeoTest`
Expected: FAIL — belum ada `NewsArticle`.

- [ ] **Step 3: Implementasi**

Di `resources/views/public/article.blade.php`, setelah baris `@section('og_image', ...)`, sisipkan:

```blade

@push('meta')
    <meta property="article:published_time" content="{{ $article->published_at->toAtomString() }}">
    <meta property="article:modified_time" content="{{ $article->updated_at->toAtomString() }}">
    @if ($article->category)
        <meta property="article:section" content="{{ $article->category->name }}">
    @endif
@endpush

@push('schema')
    <script type="application/ld+json">{!! json_encode(array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'NewsArticle',
        'headline' => $article->title,
        'description' => $article->meta_description ?: $article->excerpt_text,
        'image' => [$article->featured_image ? url(Storage::disk('public')->url($article->featured_image)) : url('favicon.ico')],
        'datePublished' => $article->published_at->toAtomString(),
        'dateModified' => $article->updated_at->toAtomString(),
        'articleSection' => $article->category?->name,
        'author' => $article->author ? ['@type' => 'Person', 'name' => $article->author->name] : null,
        'publisher' => [
            '@type' => 'Organization',
            'name' => $site['site_name'] ?? config('app.name', 'TikumPost'),
            'logo' => [
                '@type' => 'ImageObject',
                'url' => ! empty($site['site_logo']) ? url(Storage::disk('public')->url($site['site_logo'])) : url('favicon.ico'),
            ],
        ],
        'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => route('articles.show', $article)],
    ]), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    <script type="application/ld+json">{!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Beranda', 'item' => route('home')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => $article->title, 'item' => route('articles.show', $article)],
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endpush
```

- [ ] **Step 4: Jalankan tes, pastikan lulus**

Run: `php artisan view:clear && php artisan test --filter=SeoTest`
Expected: PASS (15 tes).

- [ ] **Step 5: Commit**

```bash
git add resources/views/public/article.blade.php tests/Feature/SeoTest.php
git commit -m "feat(seo): JSON-LD NewsArticle dan BreadcrumbList di halaman artikel"
```

---

### Task 6: Field verifikasi Google Search Console

**Files:**
- Modify: `app/Support/SiteSettings.php:83-88` (grup `seo`)
- Modify: `resources/views/layouts/public.blade.php` (di antara `@stack('schema')` dan blok JSON-LD)
- Test: `tests/Feature/SeoTest.php` (tambahkan metode)

**Interfaces:**
- Consumes: `Setting::set()` (`app/Models/Setting.php`), `SiteSettings::values()`.
- Produces: kunci setelan `google_site_verification`; tag `<meta name="google-site-verification" content="...">` dirender bila terisi.

- [ ] **Step 1: Tulis tes yang gagal**

Tambahkan ke `tests/Feature/SeoTest.php` (tambahkan `use App\Models\Setting;` di blok import):

```php
    public function test_meta_verifikasi_google_muncul_bila_setelan_diisi(): void
    {
        Setting::set('google_site_verification', 'token-uji-123');

        $this->get(route('home'))->assertOk()
            ->assertSee('<meta name="google-site-verification" content="token-uji-123">', false);
    }

    public function test_meta_verifikasi_google_tidak_muncul_bila_kosong(): void
    {
        $this->get(route('home'))->assertOk()
            ->assertDontSee('google-site-verification', false);
    }
```

- [ ] **Step 2: Jalankan tes, pastikan gagal**

Run: `php artisan test --filter=SeoTest`
Expected: FAIL — tag verifikasi belum ada. Perhatikan: tes `test_meta_verifikasi_google_muncul_bila_setelan_diisi` juga akan gagal dulu karena `Setting::set` diabaikan (kunci belum ada di skema).

- [ ] **Step 3: Tambah field ke skema setelan**

Di `app/Support/SiteSettings.php`, pada grup `'seo'` → `'fields'`, setelah field `meta_keywords`, tambahkan:

```php
                    ['key' => 'google_site_verification', 'label' => 'Google Site Verification', 'type' => 'text', 'default' => null, 'help' => 'Tempel hanya nilai token dari Google Search Console (metode "Tag HTML", bagian content="..."). Kosongkan bila belum dipakai.'],
```

- [ ] **Step 4: Render tag di layout**

Di `resources/views/layouts/public.blade.php`, tepat setelah `@stack('schema')`, sisipkan:

```blade
    @if (! empty($site['google_site_verification']))
        <meta name="google-site-verification" content="{{ $site['google_site_verification'] }}">
    @endif
```

- [ ] **Step 5: Jalankan seluruh tes**

Run: `php artisan view:clear && php artisan test`
Expected: PASS — seluruh suite (79 tes lama + 15 tes SEO) hijau.

- [ ] **Step 6: Rapikan gaya kode**

Run: `vendor/bin/pint --dirty`
Expected: hanya memformat file yang berubah; lalu `php artisan test` tetap PASS.

- [ ] **Step 7: Commit**

```bash
git add app/Support/SiteSettings.php resources/views/layouts/public.blade.php tests/Feature/SeoTest.php
git commit -m "feat(seo): setelan token verifikasi Google Search Console"
```

---

## Verifikasi pasca-deploy (manual, dijalankan sekali)

1. `curl -s https://tikumpost.id/sitemap.xml` → 200, header `Content-Type: application/xml`, memuat URL artikel.
2. `curl -s https://tikumpost.id/robots.txt` → memuat baris `Sitemap: https://tikumpost.id/sitemap.xml`.
3. `curl -s https://tikumpost.id/ | grep -o '<title>[^<]*</title>'` → judul memuat nama situs + tagline (tanpa `Beranda ·`).
4. `curl -s "<url-artikel>" | grep -c 'NewsArticle'` → ≥ 1.
5. Rich Results Test (manual, pemilik) untuk satu URL artikel.
6. Search Console: tambah properti **URL prefix** `https://tikumpost.id/` → metode **HTML tag** → tempel token ke admin → Pengaturan → SEO Default → **Google Site Verification** → Simpan → **Verify** → submit `sitemap.xml` → **URL Inspection** → minta pengindeksan beranda + 5–10 artikel utama.
