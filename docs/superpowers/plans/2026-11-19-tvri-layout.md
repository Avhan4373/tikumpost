# Layout TVRI News Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Ganti tampilan publik menjadi layout TVRI News (header biru, ticker, hero grid, strip kategori, daftar baris + sidebar) tanpa iklan.

**Architecture:** Markup Blade dirombak di tiga tempat — header global, beranda, detail berita — dengan dua partial baru (`section-heading`, `article-row`). `HomeController` menambah dua variabel (`$ticker`, `$sections`). Tanpa setting admin, tanpa migrasi.

**Tech Stack:** Laravel 12, Blade, Tailwind CSS v4, vanilla JS, PHPUnit/Pest-like feature tests.

**Spec:** `docs/superpowers/specs/2026-11-19-tvri-layout-design.md`

## Global Constraints

- Tanpa dependency baru, tanpa migrasi, tanpa setting `layout_preset`.
- Aksen tetap merah brand; biru hanya header.
- Test lama wajib hijau: `hero-rise`, `hero-zoom`, `media-frame`, `data-fade` tetap ada di beranda; infografis terbit tetap tampil; label menu database tetap tampil.
- Semua halaman publik memakai header yang sama — jangan merusak nav, dropdown, atau panel mobile.
- Tidak commit/deploy sampai diminta.

## Review Focus

- Beranda tanpa artikel / tanpa kategori → tetap 200 dan tidak error (test).
- Ticker dengan 1 judul atau 0 judul → tidak merusak layout (test 0, CSS untuk 1).
- Judul panjang di kartu hero/kategori → terpotong (`line-clamp`), tidak meluber (cek manual).
- Mode gelap: teks putih di bar biru tetap kontras, panel mobile tetap terbaca (cek manual).
- Mobile 390px: hero, strip, dan sidebar menumpuk tanpa scroll horizontal (cek manual).
- Scroll desktop: nav tetap terlihat (bar biru tanpa `site-header-collapse`), hanya strip tanggal yang hilang.

---

### Task 1: Data beranda (`$ticker`, `$sections`)

**Files:**
- Modify: `app/Http/Controllers/Public/HomeController.php`
- Test: `tests/Feature/HomeLayoutTest.php` (create)

**Interfaces:**
- Produces: view `public.home` menerima `$ticker` (`Collection<int, Article>`, maks 5) dan `$sections` (`Collection<int, Category>` dengan relasi `articles`, maks 2 kategori, masing-masing maks 4 artikel).

- [ ] **Step 1: Write failing test**

```php
<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeLayoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_beranda_tetap_200_tanpa_artikel(): void
    {
        $this->get(route('home'))->assertOk();
    }

    public function test_strip_kategori_hanya_menampilkan_kategori_berisi_artikel(): void
    {
        $kosong = Category::factory()->create(['name' => 'Kosong', 'slug' => 'kosong']);
        $isi = Category::factory()->create(['name' => 'Nasional', 'slug' => 'nasional']);

        $article = Article::factory()->create(['category_id' => $isi->id]);

        $response = $this->get(route('home'))->assertOk();

        $response->assertSee('Nasional')
            ->assertSee($article->title)
            ->assertDontSee('data-section-kosong', false);
    }
}
```

Catat: sesuaikan nama kolom wajib `Category`/`Article` dengan factory yang ada di repo (cek `database/factories`).

- [ ] **Step 2: Run test, pastikan gagal**

Run: `php artisan test --filter=HomeLayoutTest`
Expected: FAIL — `sections`/`ticker` belum dikirim / strip kategori belum ada.

- [ ] **Step 3: Implement controller**

Di `index()`, setelah `$infographics`:

```php
$ticker = Article::published()->orderByDesc('published_at')->take(5)->get();

$sections = Category::ordered()
    ->with(['articles' => fn ($q) => $q->published()->with('category')
        ->latest('published_at')->take(4)])
    ->get()
    ->filter(fn ($c) => $c->articles->isNotEmpty())
    ->take(2);
```

Tambahkan keduanya ke `compact()`.

- [ ] **Step 4: Run test**

Run: `php artisan test --filter=HomeLayoutTest`
Expected: PASS (test pertama), test kedua masih menunggu markup strip — boleh tetap FAIL sampai Task 3; kalau begitu, tandai test kedua di Task 3.

- [ ] **Step 5: Commit**

```bash
git add app/Http/Controllers/Public/HomeController.php tests/Feature/HomeLayoutTest.php
git commit -m "Tambah data ticker dan strip kategori untuk beranda"
```

---

### Task 2: Partial `section-heading` + `article-row`

**Files:**
- Create: `resources/views/public/partials/section-heading.blade.php`
- Create: `resources/views/public/partials/article-row.blade.php`

**Interfaces:**
- Produces: `section-heading` membaca `$title` (string), `$subtitle` (string|null), `$href` (string|null), `$linkLabel` (string, default `'Lihat Semua'`).
- Produces: `article-row` membaca `$article` (App\Models\Article), `$excerpt` (bool, default `true`).
- Produces: root `section-heading` punya `data-section-heading`, root `article-row` punya `data-article-row`.

- [ ] **Step 1: Tulis `section-heading.blade.php`**

```blade
@php($linkLabel = $linkLabel ?? 'Lihat Semua')
<div class="mb-5 flex items-end justify-between gap-4" data-section-heading>
    <div class="flex min-w-0 items-center gap-3">
        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-red-600 text-white shadow-sm">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 5.25h16.5M3.75 12h16.5M3.75 18.75h10.5" />
            </svg>
        </span>
        <div class="min-w-0">
            <h2 class="truncate text-lg font-bold tracking-tight text-slate-900 lg:text-xl">{{ $title }}</h2>
            @if (! empty($subtitle))
                <p class="truncate text-xs text-slate-500">{{ $subtitle }}</p>
            @endif
        </div>
    </div>
    @if (! empty($href))
        <a href="{{ $href }}" class="inline-flex shrink-0 items-center gap-1 text-sm font-semibold text-slate-600 transition hover:text-red-600">
            {{ $linkLabel }}
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 12h16m0 0-6-6m6 6-6 6" />
            </svg>
        </a>
    @endif
</div>
```

- [ ] **Step 2: Tulis `article-row.blade.php`**

```blade
@php($excerpt = $excerpt ?? true)
<article class="group flex gap-4 py-4" data-article-row>
    <a href="{{ route('articles.show', $article) }}" class="relative block h-24 w-32 shrink-0 overflow-hidden rounded-xl bg-slate-100 sm:h-28 sm:w-44 {{ $article->featured_image ? 'media-frame' : '' }}">
        @if ($article->featured_image)
            <img src="{{ Storage::disk('public')->url($article->featured_image) }}" alt="" loading="lazy" data-fade
                class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
        @endif
    </a>
    <div class="min-w-0">
        @if ($article->category)
            <a href="{{ route('categories.show', $article->category) }}"
                class="inline-flex rounded bg-slate-100 px-2 py-0.5 text-[11px] font-bold uppercase tracking-wide text-slate-600 transition hover:bg-red-600 hover:text-white">{{ $article->category->name }}</a>
        @endif
        <h3 class="mt-1.5 line-clamp-2 text-base font-bold leading-snug text-slate-900">
            <a href="{{ route('articles.show', $article) }}" class="transition hover:text-red-600">{{ $article->title }}</a>
        </h3>
        @if ($excerpt)
            <p class="mt-1 hidden line-clamp-2 text-sm leading-relaxed text-slate-600 sm:block">{{ $article->excerpt_text }}</p>
        @endif
        <time class="mt-1.5 block text-xs text-slate-400" datetime="{{ optional($article->published_at)->toAtomString() }}">
            {{ optional($article->published_at)->diffForHumans() }}
        </time>
    </div>
</article>
```

- [ ] **Step 3: Commit**

```bash
git add resources/views/public/partials/section-heading.blade.php resources/views/public/partials/article-row.blade.php
git commit -m "Tambah partial judul seksi dan baris artikel"
```

---

### Task 3: Beranda layout TVRI

**Files:**
- Modify: `resources/views/public/home.blade.php` (tulis ulang)
- Test: `tests/Feature/HomeLayoutTest.php`

**Interfaces:**
- Consumes: `$headline` (5), `$latest` (6), `$popular` (5), `$categories`, `$tags`, `$infographics`, `$ticker`, `$sections` dari Task 1; partial Task 2.

- [ ] **Step 1: Tulis test markup beranda**

Tambahkan ke `HomeLayoutTest`:

```php
public function test_beranda_memakai_markup_tvri(): void
{
    $article = Article::factory()->create(['is_headline' => true]);

    $response = $this->get(route('home'))->assertOk();

    $response->assertSee('data-home="tvri"', false)
        ->assertSee('data-ticker', false)
        ->assertSee('data-hero-lead', false)
        ->assertSee('data-hero-tile', false)
        ->assertSee('data-article-row', false)
        ->assertSee('Berita Terbaru')
        ->assertSee('hero-rise', false)
        ->assertSee('hero-zoom', false);
}

public function test_strip_kategori_tampil_dengan_tautan_lihat_semua(): void
{
    $category = Category::factory()->create(['name' => 'Nasional', 'slug' => 'nasional']);
    Article::factory()->create(['category_id' => $category->id]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('data-home="tvri"', false)
        ->assertSee('Lihat Semua')
        ->assertSee(route('categories.show', $category), false);
}
```

- [ ] **Step 2: Run, pastikan gagal**

Run: `php artisan test --filter=HomeLayoutTest`
Expected: FAIL — `data-home` tidak ditemukan.

- [ ] **Step 3: Tulis ulang `home.blade.php`**

Struktur wajib:

```blade
@extends('layouts.public')

@section('title', 'Beranda')

@section('content')
<div data-home="tvri">
    {{-- Hero: wadah max-w-7xl --}}
    @if ($headline->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 py-6 lg:py-8">
            @php($lead = $headline->first())
            <div class="grid gap-4 lg:grid-cols-3">
                <a data-hero-lead href="{{ route('articles.show', $lead) }}"
                   class="group relative overflow-hidden rounded-2xl bg-slate-900 lg:col-span-2">
                    {{-- gambar h-64 lg:h-[26rem], hero-zoom, media-frame, overlay gradient, chip kategori, h1 hero-rise --}}
                </a>
                <div class="grid grid-cols-2 gap-4">
                    @foreach ($headline->skip(1)->take(4) as $item)
                        <a data-hero-tile href="{{ route('articles.show', $item) }}"
                           class="group relative aspect-[4/3] overflow-hidden rounded-xl bg-slate-900">
                            {{-- gambar/placeholder, overlay, chip kategori kecil, judul line-clamp-2 text-sm text-white --}}
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Strip kategori --}}
    @foreach ($sections as $i => $section)
        <section class="{{ $i % 2 === 0 ? 'bg-slate-50' : 'bg-white' }} py-8">
            <div class="mx-auto max-w-7xl px-4">
                @include('public.partials.section-heading', [
                    'title' => $section->name,
                    'subtitle' => 'Berita terbaru seputar ' . $section->name,
                    'href' => route('categories.show', $section),
                ])
                <div class="grid gap-x-6 md:grid-cols-2">
                    @foreach ($section->articles as $article)
                        <div class="border-b border-slate-200">
                            @include('public.partials.article-row', ['article' => $article])
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endforeach

    {{-- Berita Terbaru + sidebar: container putih --}}
    <section class="mx-auto max-w-7xl px-4 py-10">
        <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_20rem]">
            <div>
                @include('public.partials.section-heading', ['title' => 'Berita Terbaru', 'subtitle' => 'Update berita terkini dari berbagai kategori'])
                <div class="divide-y divide-slate-200">
                    @foreach ($latest as $article)
                        @include('public.partials.article-row', ['article' => $article])
                    @endforeach
                </div>
            </div>
            <aside class="space-y-5">
                {{-- Terpopuler bernomor + thumbnail; Newsletter biru; Kategori; Topik; Infografis (lightbox) --}}
            </aside>
        </div>
    </section>

    {{-- Lightbox infografis: pertahankan markup lama --}}
</div>
@endsection
```

Detail yang tidak boleh hilang: gambar hero memakai `hero-zoom`, `media-frame`, `data-fade`; `h1`/judul memakai `hero-rise`; chip kategori merah; sidebar memuat `Storage::disk('public')->url($infographic->image)` (test infografis); lightbox `data-lightbox`, `data-lightbox-src`, `data-lightbox-image`, `data-lightbox-close` dipertahankan persis.

- [ ] **Step 4: Run test + suite**

Run: `php artisan test --filter=HomeLayoutTest` lalu `php artisan test`
Expected: semua PASS. Jika `AnimationTest`/`InfographicTest` gagal, perbaiki markup (jangan ubah test).

- [ ] **Step 5: Commit**

```bash
git add resources/views/public/home.blade.php tests/Feature/HomeLayoutTest.php
git commit -m "Terapkan layout TVRI pada beranda"
```

---

### Task 4: Header biru + ticker

**Files:**
- Modify: `resources/views/public/partials/header.blade.php`
- Test: `tests/Feature/HomeLayoutTest.php`

**Interfaces:**
- Consumes: `$ticker` (opsional, hanya beranda).
- Produces: bar utama `class` memuat `site-header-blue`; ticker punya `data-ticker` dan track `.ticker-track`.

- [ ] **Step 1: Test header**

```php
public function test_header_biru_dan_ticker_breaking_news(): void
{
    Article::factory()->create(['title' => 'Judul Ticker Uji']);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('site-header-blue', false)
        ->assertSee('data-ticker', false)
        ->assertSee('Judul Ticker Uji')
        ->assertSee('BREAKING NEWS');
}

public function test_halaman_lain_tetap_memakai_header_biru(): void
{
    $this->get(route('search'))->assertOk()->assertSee('site-header-blue', false);
}
```

- [ ] **Step 2: Run, pastikan gagal**

Run: `php artisan test --filter=HomeLayoutTest`
Expected: FAIL — `site-header-blue` belum ada.

- [ ] **Step 3: Ubah header**

- Bar utama (baris logo+search+auth) ganti `bg-white/85 border-slate-200` menjadi `site-header-blue bg-blue-700 border-blue-800`; teks nama situs jadi `text-white`; input search `bg-white/15 text-white placeholder-blue-100 border-white/20 focus:border-white focus:ring-white/30`; tombol Masuk/Daftar: Masuk `text-white hover:bg-white/15`, Daftar `bg-white text-blue-700 hover:bg-blue-50`.
- Hapus blok "Navigasi utama (desktop)" terpisah; pindahkan `<nav>` yang sama ke dalam bar biru (setelah logo, sebelum kontrol kanan), dengan `text-sm font-semibold` putih: aktif `text-white` + `nav-link is-active`, non-aktif `text-blue-100 hover:text-white`.
- Bungkus nav: `<div class="hidden min-w-0 flex-1 overflow-x-auto no-scrollbar lg:block"><nav class="flex items-center gap-1 whitespace-nowrap">…</nav></div>`.
- Bar biru **tidak** memakai `site-header-collapse`; hanya strip tanggal atas yang memakainya, supaya nav tidak hilang saat scroll (tidak ada perubahan JS).
- Hapus tautan "Langganan" desktop (blok `hidden items-center gap-2 ... lg:inline-flex`); biarkan versi panel mobile.
- Tombol Live: `<a href="{{ route('reels.index') }}" class="btn-anim hidden items-center gap-2 rounded-full bg-red-600 px-3.5 py-2 text-sm font-semibold text-white hover:bg-red-700 xl:inline-flex">` dengan `<span class="h-2 w-2 rounded-full bg-white"></span> Live`.
- Search desktop jadi `<details class="relative">`: `<summary class="...list-none text-white">` berisi ikon kaca pembesar, panel `<form>` absolute `right-0 top-full mt-2 w-64 rounded-xl bg-white p-2 shadow-xl`, input `text-slate-800`; tidak butuh JS.
- Ticker setelah bar biru, dirender bila `! empty($ticker)`:

```blade
@if (! empty($ticker))
    {{-- ponytail: ticker hanya beranda; kirim $ticker dari controller lain kalau perlu di semua halaman --}}
    <div data-ticker class="overflow-hidden bg-red-600 text-white">
        <div class="mx-auto flex max-w-7xl items-center gap-3 px-4 py-2">
            <span class="shrink-0 rounded bg-white/15 px-2 py-0.5 text-[11px] font-bold uppercase tracking-wide">Breaking News</span>
            <div class="relative flex-1 overflow-hidden">
                <div class="ticker-track flex w-max items-center gap-6 whitespace-nowrap text-sm">
                    @foreach ($ticker->concat($ticker) as $item)
                        <a href="{{ route('articles.show', $item) }}" class="transition hover:underline">{{ $item->title }}</a>
                        <span class="text-white/50">&bull;</span>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endif
```

- Panel mobile: tetap putih, tetapi tombol Masuk/Daftar di panel tidak perlu diubah.
- Pertahankan semua `data-menu-dropdown`, `data-menu-toggle`, `data-mobile-nav-toggle`, `data-mobile-nav`, `id` yang dipakai JS.

- [ ] **Step 4: Run test**

Run: `php artisan test --filter=HomeLayoutTest` lalu `php artisan test`
Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add resources/views/public/partials/header.blade.php tests/Feature/HomeLayoutTest.php
git commit -m "Header biru dan ticker breaking news ala TVRI"
```

---

### Task 5: Detail berita + sidebar

**Files:**
- Modify: `resources/views/public/article.blade.php`
- Modify: `resources/views/public/partials/article-sidebar.blade.php`
- Test: `tests/Feature/HomeLayoutTest.php`

**Interfaces:**
- Consumes: partial `section-heading` dari Task 2.

- [ ] **Step 1: Test**

```php
public function test_detail_berita_pakai_header_biru_dan_sidebar(): void
{
    $article = Article::factory()->create();

    $this->get(route('articles.show', $article))
        ->assertOk()
        ->assertSee('site-header-blue', false)
        ->assertSee('data-article-sidebar', false)
        ->assertSee('Artikel Terkait')
        ->assertDontSee('data-ticker', false);
}
```

- [ ] **Step 2: Run, pastikan gagal**

Run: `php artisan test --filter=test_detail_berita`
Expected: FAIL — `data-article-sidebar` belum ada.

- [ ] **Step 3: Ubah artikel + sidebar**

- `article-sidebar.blade.php`: root `<aside ... data-article-sidebar>`.
- Ganti newsletter sidebar jadi `bg-gradient-to-br from-blue-700 to-blue-900` + teks putih + tombol `bg-white text-blue-700 hover:bg-blue-50`.
- Ganti header blok sidebar (Terpopuler/Kategori/Topik/Infografis) agar memakai pola ikon kotak merah yang sama dengan `section-heading` (h2 + kotak 8x8 `rounded-lg bg-red-600`), tanpa mengubah data.
- `article.blade.php`: ganti header "Artikel Terkait" menjadi `@include('public.partials.section-heading', ['title' => 'Artikel Terkait', 'subtitle' => 'Bacaan lain dari kategori ini', 'href' => $article->category ? route('categories.show', $article->category) : null])`; grid kartu tetap `sm:grid-cols-2 lg:grid-cols-3` dengan `article-card`.
- Biarkan komentar, share, tags, breadcrumb, progress bar apa adanya.

- [ ] **Step 4: Run test**

Run: `php artisan test`
Expected: PASS semua.

- [ ] **Step 5: Commit**

```bash
git add resources/views/public/article.blade.php resources/views/public/partials/article-sidebar.blade.php tests/Feature/HomeLayoutTest.php
git commit -m "Selaraskan detail berita dan sidebar dengan layout TVRI"
```

---

### Task 6: CSS ticker, build, verifikasi visual

**Files:**
- Modify: `resources/css/app.css`

- [ ] **Step 1: Tambah CSS ticker**

Di `@layer components`, tambahkan:

```css
.ticker-track {
    animation: ticker 40s linear infinite;
}

.ticker-track:hover {
    animation-play-state: paused;
}

@keyframes ticker {
    from { transform: translateX(0); }
    to { transform: translateX(-50%); }
}

@media (prefers-reduced-motion: reduce) {
    .ticker-track { animation: none; }
}
```

Tambah juga varian gelap header bila kontras kurang: `html.dark .site-header-blue { background-color: #1e3a8a; }`.

- [ ] **Step 2: Build aset**

Run: `npm run build`
Expected: build sukses, `public/build/manifest.json` terbarui.

- [ ] **Step 3: Jalankan server + cek manual**

Run: `php artisan serve --host=127.0.0.1 --port=8000`

Cek di 1440px dan 390px via browser-harness:
- hero 1 besar + 4 tile, tidak ada scroll horizontal di 390px;
- ticker berjalan, pause saat hover;
- header biru, nav dropdown desktop tetap terbuka, panel mobile tetap berfungsi;
- beranda tanpa kategori/artikel tidak error;
- detail berita: sidebar Terpopuler, newsletter biru, Artikel Terkait;
- mode gelap: teks header tetap terbaca.

- [ ] **Step 4: Test penuh**

Run: `php artisan test`
Expected: semua test PASS.

- [ ] **Step 5: Commit**

```bash
git add resources/css/app.css public/build
git commit -m "Animasi ticker dan penyesuaian tema gelap header"
```

---

## Catatan eksekusi

- Kerjakan berurutan; Task 3 bergantung pada Task 1 & 2, Task 4–5 pada Task 2.
- Jangan commit/deploy ke server sebelum diminta.
- Sebelum mulai: `git status --short` untuk memastikan tidak menimpa perubahan pengguna.