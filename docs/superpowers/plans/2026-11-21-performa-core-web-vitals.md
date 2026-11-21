# Performa & Core Web Vitals Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Naikkan Lighthouse beranda dari 70 → ≥ 90 dan turunkan LCP dari 4,6 s → ≤ 2,5 s dengan gambar responsif (WebP), cache statis panjang, dan perbaikan render.

**Architecture:** Kelas `App\Support\ImageVariant` membuat varian WebP berlebar tetap via GD native (tanpa dependency baru), dipakai oleh komponen Blade anonim `<x-img>` yang merender `srcset`/`sizes`/`width`/`height`. Varian dibuat saat unggah, disisir oleh perintah `media:variants`, dan disajikan dengan `Cache-Control: immutable` dari `public/.htaccess`. Perbaikan render menargetkan `resources/css/app.css` + `resources/js/app.js` (shimmer berjalan terus, hero mulai dari `opacity: 0`, `transition` global terlalu luas).

**Tech Stack:** Laravel (PHP 8.3, GD 2.3.3 + WebP), Tailwind v4 + Vite, PHPUnit, Blade components.

**Spec:** `docs/superpowers/specs/2026-11-21-performa-core-web-vitals-design.md`

## Global Constraints

- Tanpa dependency Composer/npm baru. Tanpa perubahan skema DB. Tanpa perubahan desain yang terlihat pembaca (warna, tata letak, tipografi).
- GD native saja (`imagewebp`); bila `imagewebp` tidak ada → kembalikan `[]`, jangan error.
- Penamaan varian: `<base>-v1-<lebar>.webp` dengan `ImageVariant::VERSION = 'v1'`.
- Lebar varian: `[160, 320, 480, 960, 1440]`. Jangan upscale (`$lebar >= $lebar_asli` → lewati).
- Kualitas WebP: 80.
- Server tanpa Node; `public/build` ikut di-commit. Jangan tambah toolchain.
- Deploy = `DEPLOY_MESSAGE="..." npm run deploy`; **hanya setelah konfirmasi eksplisit pemilik**. Kerja di `master`.
- Setiap task berakhir dengan `php artisan test` hijau + `vendor/bin/pint --dirty` bersih + satu commit.
- Setelah edit Blade: `php artisan view:clear`.
- Skala data produksi (untuk alasan kinerja): 10 artikel ber-`featured_image`, 2 infografis, 1 media, 2,4 MB.

## Review Focus

1. **Berkas non-gambar di unggahan Media.** `MediaController` menerima `pdf`. `ImageVariant::ensure()` harus mengembalikan `[]` tanpa exception; unggahan tetap sukses (`MediaTest` yang ada tetap hijau).
2. **`featured_image` menunjuk berkas hilang.** Komponen harus tetap merender `<img src>` tanpa `srcset`, halaman tetap 200.
3. **Hero tanpa gambar.** Jalur `@else` di `home.blade.php` tetap dipakai: tak ada `<img>` kosong, tak ada tag `preload`, tata letak tidak berubah.
4. **Gambar asli kecil (mis. 300 px).** Tak ada varian upscale; `srcset` tidak memuat lebar ≥ lebar asli; `width`/`height` tetap rasio benar.
5. **`.htaccess` yang salah tidak boleh 500 seluruh situs.** Semua aturan baru di dalam `<IfModule mod_headers.c>`, docroot dicadangkan dulu, rollback = pulihkan cadangan.

---

### Task 1: `ImageVariant` + perintah `media:variants`

**Files:**
- Create: `app/Support/ImageVariant.php`
- Create: `app/Console/Commands/MediaVariants.php`
- Test: `tests/Unit/ImageVariantTest.php`
- Test: `tests/Feature/MediaVariantsCommandTest.php`

**Interfaces:**
- Consumes: tak ada.
- Produces:
  - `ImageVariant::VERSION` (const `'v1'`), `ImageVariant::WIDTHS` (const `list<int>`), `ImageVariant::QUALITY` (const `80`).
  - `ImageVariant::ensure(string $path, string $disk = 'public'): array` → daftar `['path' => string, 'width' => int, 'height' => int]`, hanya varian yang benar-benar ada di disk; `[]` bila bukan gambar/GD tak mampu.
  - `ImageVariant::srcset(array $variants, string $disk = 'public'): string` → `"URL 160w, URL 320w"`; `''` bila kosong.
  - `ImageVariant::intrinsic(string $path, string $disk = 'public'): ?array` → `[width, height]` gambar asli, atau `null`.
  - `ImageVariant::variantPath(string $path, int $width): string` → `articles/foto-v1-480.webp`.

- [ ] **Step 1: Tulis tes gagal `tests/Unit/ImageVariantTest.php`**

```php
<?php

namespace Tests\Unit;

use App\Support\ImageVariant;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageVariantTest extends TestCase
{
    private function makeImage(string $path, int $width, int $height): void
    {
        $full = Storage::disk('public')->path($path);
        @mkdir(dirname($full), 0777, true);
        $image = imagecreatetruecolor($width, $height);
        imagefill($image, 0, 0, imagecolorallocate($image, 200, 30, 30));
        imagejpeg($image, $full, 85);
    }

    public function test_srcset_kosong_untuk_berkas_bukan_gambar(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('media/dokumen.pdf', 'bukan gambar');

        $this->assertSame([], ImageVariant::ensure('media/dokumen.pdf'));
        $this->assertNull(ImageVariant::intrinsic('media/dokumen.pdf'));
        $this->assertSame('', ImageVariant::srcset([]));
    }

    public function test_membuat_varian_webp_tanpa_upscale(): void
    {
        Storage::fake('public');
        $this->makeImage('articles/uji.jpg', 400, 300);

        $variants = ImageVariant::ensure('articles/uji.jpg');

        // 400 px: varian 160 & 320 dibuat; 480/960/1440 dilewati (tak boleh upscale).
        $this->assertSame([160, 320], array_column($variants, 'width'));
        $this->assertSame(240, $variants[1]['height']);
        $this->assertTrue(Storage::disk('public')->exists('articles/uji-v1-160.webp'));
        $this->assertTrue(Storage::disk('public')->exists('articles/uji-v1-320.webp'));
        $this->assertStringContainsString(' 160w', ImageVariant::srcset($variants));
    }

    public function test_perintah_tidak_mengupscale_gambar_kecil(): void
    {
        Storage::fake('public');
        $this->makeImage('articles/mungil.jpg', 200, 150);

        $variants = ImageVariant::ensure('articles/mungil.jpg');

        $this->assertSame([160], array_column($variants, 'width'));
    }

    public function test_intrinsic_mengembalikan_dimensi_asli(): void
    {
        Storage::fake('public');
        $this->makeImage('articles/asli.png', 640, 480);

        $this->assertSame([640, 480], ImageVariant::intrinsic('articles/asli.png'));
    }

    public function test_variant_path_memisahkan_ekstensi_dan_lebar(): void
    {
        $this->assertSame('articles/foto-v1-480.webp', ImageVariant::variantPath('articles/foto.jpg', 480));
    }
}
```

- [ ] **Step 2: Jalankan, pastikan GAGAL**

Run: `php artisan test tests/Unit/ImageVariantTest.php`
Expected: FAIL — `Class "App\Support\ImageVariant" not found`

- [ ] **Step 3: Implementasi `app/Support/ImageVariant.php`**

```php
<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class ImageVariant
{
    /** Naikkan bila WIDTHS/kualitas berubah: memberi URL baru agar cache immutable tidak basi. */
    public const VERSION = 'v1';

    public const WIDTHS = [160, 320, 480, 960, 1440];

    public const QUALITY = 80;

    /** @return list<array{path: string, width: int, height: int}> */
    public static function ensure(string $path, string $disk = 'public'): array
    {
        $source = self::intrinsic($path, $disk);

        if ($source === null || ! function_exists('imagewebp')) {
            return [];
        }

        [$sourceWidth, $sourceHeight] = $source;
        $variants = [];

        foreach (self::WIDTHS as $width) {
            if ($width >= $sourceWidth) {
                continue;
            }

            $target = self::variantPath($path, $width);
            $height = (int) round($sourceHeight * ($width / $sourceWidth));

            if (! Storage::disk($disk)->exists($target)) {
                self::write($path, $target, $width, $height, $disk);
            }

            if (Storage::disk($disk)->exists($target)) {
                $variants[] = ['path' => $target, 'width' => $width, 'height' => $height];
            }
        }

        return $variants;
    }

    /** @param list<array{path: string, width: int, height: int}> $variants */
    public static function srcset(array $variants, string $disk = 'public'): string
    {
        return implode(', ', array_map(
            fn (array $variant) => Storage::disk($disk)->url($variant['path']).' '.$variant['width'].'w',
            $variants,
        ));
    }

    /** @return array{0: int, 1: int}|null */
    public static function intrinsic(string $path, string $disk = 'public'): ?array
    {
        if (! Storage::disk($disk)->exists($path)) {
            return null;
        }

        $info = @getimagesize(Storage::disk($disk)->path($path));

        return $info === false ? null : [(int) $info[0], (int) $info[1]];
    }

    public static function variantPath(string $path, int $width): string
    {
        $base = preg_replace('/\.[^.]+$/', '', $path);

        return $base.'-'.self::VERSION.'-'.$width.'.webp';
    }

    private static function write(string $path, string $target, int $width, int $height, string $disk): void
    {
        $source = self::createImage(Storage::disk($disk)->path($path));

        if ($source === null) {
            return;
        }

        $canvas = imagecreatetruecolor($width, $height);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        imagecopyresampled($canvas, $source, 0, 0, 0, 0, $width, $height, imagesx($source), imagesy($source));
        imagewebp($canvas, Storage::disk($disk)->path($target), self::QUALITY);
        imagedestroy($canvas);
        imagedestroy($source);
    }

    /** @return \GdImage|null */
    private static function createImage(string $full)
    {
        $info = @getimagesize($full);

        return match ($info['mime'] ?? null) {
            'image/jpeg' => @imagecreatefromjpeg($full),
            'image/png' => @imagecreatefrompng($full),
            'image/webp' => @imagecreatefromwebp($full),
            'image/gif' => @imagecreatefromgif($full),
            default => null,
        };
    }
}
```

- [ ] **Step 4: Jalankan, pastikan LULUS**

Run: `php artisan test tests/Unit/ImageVariantTest.php`
Expected: PASS (4 tes)

- [ ] **Step 5: Tulis tes gagal `tests/Feature/MediaVariantsCommandTest.php`**

```php
<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Infographic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaVariantsCommandTest extends TestCase
{
    use RefreshDatabase;

    private function makeImage(string $path): void
    {
        $full = Storage::disk('public')->path($path);
        @mkdir(dirname($full), 0777, true);
        $image = imagecreatetruecolor(800, 600);
        imagejpeg($image, $full, 85);
    }

    public function test_perintah_membuat_varian_dan_idempoten(): void
    {
        Storage::fake('public');
        $this->makeImage('articles/berita.jpg');
        $this->makeImage('infographics/grafis.jpg');

        Article::factory()->create(['featured_image' => 'articles/berita.jpg']);
        Infographic::factory()->create(['image' => 'infographics/grafis.jpg']);

        $this->artisan('media:variants')->assertSuccessful();
        $afterFirst = count(Storage::disk('public')->allFiles());

        $this->assertTrue(Storage::disk('public')->exists('articles/berita-v1-480.webp'));
        $this->assertTrue(Storage::disk('public')->exists('infographics/grafis-v1-480.webp'));

        $this->artisan('media:variants')->assertSuccessful();

        $this->assertSame($afterFirst, count(Storage::disk('public')->allFiles()));
    }

    public function test_perintah_melewati_baris_tanpa_gambar(): void
    {
        Storage::fake('public');
        Article::factory()->create(['featured_image' => null]);

        $this->artisan('media:variants')->assertSuccessful();
    }
}
```

- [ ] **Step 6: Jalankan, pastikan GAGAL**

Run: `php artisan test tests/Feature/MediaVariantsCommandTest.php`
Expected: FAIL — command `media:variants` tidak terdefinisi

- [ ] **Step 7: Implementasi `app/Console/Commands/MediaVariants.php`**

```php
<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Infographic;
use App\Models\Media;
use App\Support\ImageVariant;
use Illuminate\Console\Command;

class MediaVariants extends Command
{
    protected $signature = 'media:variants';

    protected $description = 'Buat varian WebP untuk gambar artikel, infografis, dan media.';

    public function handle(): int
    {
        $sources = collect()
            ->merge(Article::query()->whereNotNull('featured_image')->pluck('featured_image'))
            ->merge(Infographic::query()->pluck('image'))
            ->merge(Media::query()->pluck('path'))
            ->filter()
            ->unique();

        $variants = 0;

        foreach ($sources as $path) {
            $variants += count(ImageVariant::ensure($path));
        }

        $this->info("Selesai: {$sources->count()} gambar diproses, {$variants} varian dibuat/ditemukan.");

        return self::SUCCESS;
    }
}
```

- [ ] **Step 8: Jalankan, pastikan LULUS**

Run: `php artisan test tests/Feature/MediaVariantsCommandTest.php`
Expected: PASS (2 tes)

- [ ] **Step 9: Suite penuh + pint + commit**

```bash
php artisan test
vendor/bin/pint --dirty
git add app/Support/ImageVariant.php app/Console/Commands/MediaVariants.php tests/Unit/ImageVariantTest.php tests/Feature/MediaVariantsCommandTest.php
git commit -m "feat(performa): varian gambar WebP + perintah media:variants"
```

---

### Task 2: Komponen `<x-img>` + hook unggah

**Files:**
- Create: `resources/views/components/img.blade.php`
- Modify: `app/Http/Controllers/Admin/ArticleController.php` (`storeImage()`, baris ~120)
- Modify: `app/Http/Controllers/Admin/InfographicController.php` (setelah `store()` dan setelah `update()` menyimpan `image`)
- Modify: `app/Http/Controllers/Admin/MediaController.php` (setelah `Media::create`, baris ~40)
- Test: `tests/Feature/ImgComponentTest.php`
- Test: `tests/Feature/MediaTest.php` (tambahan kasus PDF — pastikan ada berkas PDF & tes unggah PDF; bila belum ada, tambahkan)

**Interfaces:**
- Consumes: `ImageVariant::ensure()`, `ImageVariant::srcset()`, `ImageVariant::intrinsic()` (Task 1).
- Produces: komponen Blade `x-img` dengan atribut `path` (wajib, relatif disk `public`), `alt`, `sizes`, `loading` (default `lazy`), `decoding` (default `async`), `fetchpriority` (default null), plus passthrough `$attributes` (class dll). Semua view di Task 3 memakai komponen ini.
- Produces: berkas varian dibuat otomatis saat unggah (bukan hanya lewat perintah).

- [ ] **Step 1: Tulis tes gagal `tests/Feature/ImgComponentTest.php`**

```php
<?php

namespace Tests\Feature;

use App\Support\ImageVariant;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImgComponentTest extends TestCase
{
    private function makeImage(string $path = 'articles/uji.jpg'): string
    {
        $full = Storage::disk('public')->path($path);
        @mkdir(dirname($full), 0777, true);
        $image = imagecreatetruecolor(800, 600);
        imagejpeg($image, $full, 85);

        return $path;
    }

    private function render(array $data): string
    {
        return view('components.img', $data)->render();
    }

    public function test_menghasilkan_srcset_sizes_dan_dimensi_bila_varian_ada(): void
    {
        Storage::fake('public');
        $path = $this->makeImage();
        ImageVariant::ensure($path);

        $html = $this->render(['path' => $path, 'alt' => 'Judul', 'sizes' => '100vw']);

        $this->assertStringContainsString('srcset="', $html);
        $this->assertStringContainsString(' 480w', $html);
        $this->assertStringContainsString('sizes="100vw"', $html);
        $this->assertStringContainsString('width="800"', $html);
        $this->assertStringContainsString('height="600"', $html);
        $this->assertStringContainsString('loading="lazy"', $html);
        $this->assertStringContainsString('decoding="async"', $html);
    }

    public function test_tanpa_varian_tetap_merender_img_tanpa_srcset(): void
    {
        Storage::fake('public');
        $path = $this->makeImage('articles/kecil.jpg');

        $html = $this->render(['path' => $path, 'alt' => 'Kecil']);

        $this->assertStringContainsString('<img', $html);
        $this->assertStringNotContainsString('srcset', $html);
    }

    public function test_fetchpriority_dan_eager_hanya_bila_diminta(): void
    {
        Storage::fake('public');
        $path = $this->makeImage();

        $html = $this->render(['path' => $path, 'loading' => 'eager', 'fetchpriority' => 'high']);

        $this->assertStringContainsString('fetchpriority="high"', $html);
        $this->assertStringContainsString('loading="eager"', $html);
    }

    public function test_berkas_hilang_tetap_merender_img_tanpa_error(): void
    {
        Storage::fake('public');

        $html = $this->render(['path' => 'articles/hilang.jpg', 'alt' => 'Hilang']);

        $this->assertStringContainsString('<img', $html);
        $this->assertStringNotContainsString('srcset', $html);
    }
}
```

- [ ] **Step 2: Jalankan, pastikan GAGAL**

Run: `php artisan test tests/Feature/ImgComponentTest.php`
Expected: FAIL — view `components.img` tidak ditemukan

- [ ] **Step 3: Implementasi `resources/views/components/img.blade.php`**

```blade
@props([
    'path',
    'alt' => '',
    'sizes' => null,
    'loading' => 'lazy',
    'decoding' => 'async',
    'fetchpriority' => null,
])

@php
    $disk = 'public';
    $variants = \App\Support\ImageVariant::ensure($path);
    $src = \Illuminate\Support\Facades\Storage::disk($disk)->url($path);
    $srcset = \App\Support\ImageVariant::srcset($variants, $disk) ?: null;
    $dimensi = $variants !== []
        ? ['width' => end($variants)['width'], 'height' => end($variants)['height']]
        : (\App\Support\ImageVariant::intrinsic($path, $disk)
            ? array_combine(['width', 'height'], \App\Support\ImageVariant::intrinsic($path, $disk))
            : []);
@endphp

<img
    src="{{ $src }}"
    @if ($srcset) srcset="{{ $srcset }}" @endif
    @if ($sizes) sizes="{{ $sizes }}" @endif
    @foreach ($dimensi as $nama => $nilai) {{ $nama }}="{{ $nilai }}" @endforeach
    alt="{{ $alt }}"
    loading="{{ $loading }}"
    decoding="{{ $decoding }}"
    @if ($fetchpriority) fetchpriority="{{ $fetchpriority }}" @endif
    {{ $attributes }}
>
```

- [ ] **Step 4: Jalankan, pastikan LULUS**

Run: `php artisan test tests/Feature/ImgComponentTest.php`
Expected: PASS (4 tes)

- [ ] **Step 5: Tulis tes gagal untuk hook unggah di `tests/Feature/ArticleTest.php`**

Tambahkan satu metode (class sudah ada, pakai `Storage::fake('public')` dan helper pembuat gambar yang sama seperti Task 1):

```php
public function test_unggah_gambar_unggulan_membuat_varian_webp(): void
{
    Storage::fake('public');
    $file = UploadedFile::fake()->image('unggulan.jpg', 800, 600);

    $this->actingAs(User::factory()->create())
        ->post(route('admin.articles.store'), [
            'title' => 'Berita Uji',
            'slug' => 'berita-uji',
            'category_id' => Category::factory()->create()->id,
            'excerpt' => 'Ringkasan.',
            'body' => '<p>Isi.</p>',
            'featured_image' => $file,
        ]);

    $this->assertTrue(
        collect(Storage::disk('public')->files('articles'))->contains(fn ($p) => str_contains($p, '-v1-480.webp')),
    );
}
```

Catat: samakan nama field/route dengan yang sudah dipakai `ArticleTest` yang ada, dan pastikan `use Illuminate\Http\UploadedFile;` serta import `User`/`Category` sudah ada di berkas itu (sebagian sudah, karena tes unggah sudah ada). Bila tes unggah sudah ada, cukup tempel `assertTrue(...)` di dalamnya.

- [ ] **Step 6: Jalankan, pastikan GAGAL**

Run: `php artisan test tests/Feature/ArticleTest.php --filter=variansi 2>/dev/null || php artisan test tests/Feature/ArticleTest.php`
Expected: FAIL — tidak ada berkas `-v1-480.webp`

- [ ] **Step 7: Pasang hook di tiga pengunggah**

Di `ArticleController::storeImage()`:
```php
        $path = $request->file('featured_image')->store('articles', 'public');

        try {
            \App\Support\ImageVariant::ensure($path);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Gagal membuat varian gambar: '.$e->getMessage());
        }

        return $path;
```
(Ubah sesuai bentuk asli baris 120; efek sama: kembalikan `$path` setelah `ensure()`.)

Di `InfographicController::store()` dan `update()`, setelah `$data['image'] = $request->file('image')->store('infographics', 'public');` tambahkan blok `try { ImageVariant::ensure($data['image']); } catch (\Throwable $e) { Log::warning(...); }` yang sama.

Di `MediaController::store()`, setelah `Media::create([...])` tambahkan blok yang sama pada `$media->path`. **Jangan** ubah validasi/`mimes` — PDF tetap diterima; `ensure()` mengembalikan `[]` untuk PDF.

- [ ] **Step 8: Jalankan, pastikan LULUS + MediaTest PDF hijau**

Run: `php artisan test tests/Feature/ArticleTest.php tests/Feature/InfographicTest.php tests/Feature/MediaTest.php tests/Feature/ImgComponentTest.php`
Expected: PASS semua. Bila `MediaTest` belum punya kasus PDF, tambahkan satu: unggah `UploadedFile::fake()->create('dokumen.pdf', 10, 'application/pdf')` → 201 + `Storage::disk('public')->assertExists($media->path)`; ini mengunci Review Focus #1.

- [ ] **Step 9: Suite penuh + pint + commit**

```bash
php artisan view:clear
php artisan test
vendor/bin/pint --dirty
git add resources/views/components/img.blade.php app/Http/Controllers/Admin/ArticleController.php app/Http/Controllers/Admin/InfographicController.php app/Http/Controllers/Admin/MediaController.php tests/Feature/ImgComponentTest.php tests/Feature/ArticleTest.php tests/Feature/MediaTest.php
git commit -m "feat(performa): komponen x-img + varian saat unggah"
```

---

### Task 3: Terapkan `<x-img>` di view publik + preload hero

**Files:**
- Modify: `resources/views/public/home.blade.php` (5 `<img>`; tambah `@push('meta')` preload)
- Modify: `resources/views/public/home-klasik.blade.php` (4 `<img>`)
- Modify: `resources/views/public/article.blade.php` (1 `<img>`; baris ~95. **Jangan** sentuh `<img data-lightbox-image src="">` di baris ~209)
- Modify: `resources/views/public/partials/article-card.blade.php` (1)
- Modify: `resources/views/public/partials/article-row.blade.php` (1)
- Modify: `resources/views/public/partials/article-sidebar.blade.php` (2)
- Test: `tests/Feature/RenderPerformanceTest.php`
- Tidak diubah: `partials/header.blade.php`, `partials/header-klasik.blade.php`, `partials/footer.blade.php`, `public/maintenance.blade.php` (logo, bukan LCP)

**Interfaces:**
- Consumes: `<x-img>` (Task 2), `ImageVariant::ensure()`/`srcset()` (Task 1).
- Produces: HTML ber-`srcset`/`width`/`height` di beranda & artikel; `<link rel="preload" as="image">` untuk hero beranda; `loading="eager"` + `fetchpriority="high"` untuk hero beranda dan gambar unggulan artikel.

**Tabel `sizes` per lokasi (pakai apa adanya):**

| Lokasi | `sizes` | `loading`/`fetchpriority` |
|---|---|---|
| `home.blade.php` hero lead (`lg:col-span-2`) | `(min-width: 1024px) 66vw, 100vw` | `eager` / `high` |
| `home.blade.php` hero tile (`aspect-[4/3]`, grid 2 kolom) | `(min-width: 1024px) 33vw, 50vw` | `lazy` |
| `home.blade.php` thumbnail daftar (`h-14 w-16`) | `64px` | `lazy` |
| `home.blade.php` infografis | `(min-width: 768px) 60vw, 100vw` | `lazy` |
| `article.blade.php` gambar unggulan | `(min-width: 1024px) 768px, 100vw` | `eager` / `high` |
| `partials/article-card.blade.php` | `(min-width: 1024px) 33vw, 100vw` | `lazy` |
| `partials/article-row.blade.php` | `(min-width: 768px) 25vw, 40vw` | `lazy` |
| `partials/article-sidebar.blade.php` (2 tempat) | `(min-width: 1024px) 300px, 100vw` | `lazy` |
| `home-klasik.blade.php` (4 tempat) | pakai nilai yang sama dengan lokasi serupa di atas | `lazy` |

- [ ] **Step 1: Tulis tes gagal `tests/Feature/RenderPerformanceTest.php`**

```php
<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Category;
use App\Models\Infographic;
use App\Support\ImageVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RenderPerformanceTest extends TestCase
{
    use RefreshDatabase;

    private function makeImage(string $path): string
    {
        $full = Storage::disk('public')->path($path);
        @mkdir(dirname($full), 0777, true);
        $image = imagecreatetruecolor(1200, 800);
        imagejpeg($image, $full, 85);
        ImageVariant::ensure($path);

        return $path;
    }

    public function test_beranda_merender_srcset_dan_preload_hero(): void
    {
        Storage::fake('public');
        $path = $this->makeImage('articles/hero.jpg');
        Article::factory()->create(['is_headline' => true, 'featured_image' => $path]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('srcset=', false)
            ->assertSee('rel="preload"', false)
            ->assertSee('fetchpriority="high"', false)
            ->assertSee('imagesrcset=', false);
    }

    public function test_artikel_merender_srcset_pada_gambar_unggulan(): void
    {
        Storage::fake('public');
        $path = $this->makeImage('articles/utama.jpg');
        $article = Article::factory()->create([
            'featured_image' => $path,
            'category_id' => Category::factory()->create()->id,
            'published_at' => now(),
        ]);

        $this->get(route('articles.show', $article->slug))
            ->assertOk()
            ->assertSee('srcset=', false)
            ->assertSee('fetchpriority="high"', false);
    }

    public function test_beranda_tanpa_gambar_hero_tetap_ok(): void
    {
        Storage::fake('public');
        Article::factory()->count(3)->create(['is_headline' => true, 'featured_image' => null]);

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('rel="preload"', false);
    }
}
```

Catat: sesuaikan nama route artikel & nama kolom (`is_headline`, `published_at`) dengan yang dipakai `SeoTest`/`HomeLayoutTest` yang sudah ada.

- [ ] **Step 2: Jalankan, pastikan GAGAL**

Run: `php artisan test tests/Feature/RenderPerformanceTest.php`
Expected: FAIL — belum ada `srcset=`/`rel="preload"`

- [ ] **Step 3: Ganti setiap `<img>` jadi `<x-img>` sesuai tabel `sizes`**

Contoh (hero lead di `home.blade.php`):
```blade
<x-img :path="$lead->featured_image" :alt="$lead->title"
    sizes="(min-width: 1024px) 66vw, 100vw" loading="eager" fetchpriority="high"
    class="hero-zoom media-frame h-64 w-full object-cover transition duration-700 group-hover:scale-105 lg:h-[26rem]" />
```
Aturan penggantian untuk semua lokasi: `src="{{ Storage::disk('public')->url($X) }}"` → `:path="$X"`; `alt="..."` tetap; kelas CSS dipindah apa adanya ke `class`; `loading="lazy"` dihapus (default komponen); `sizes` dan `fetchpriority` diisi sesuai tabel.

- [ ] **Step 4: Tambah preload hero di `home.blade.php`**

Tambahkan di bagian atas view (sebelum markup hero, di luar blok lain):
```blade
@push('meta')
    @if (! empty($lead?->featured_image))
        @php($heroSrcset = \App\Support\ImageVariant::srcset(\App\Support\ImageVariant::ensure($lead->featured_image)))
        <link rel="preload" as="image"
            href="{{ Storage::disk('public')->url($lead->featured_image) }}"
            @if ($heroSrcset) imagesrcset="{{ $heroSrcset }}" imagesizes="(min-width: 1024px) 66vw, 100vw" @endif>
    @endif
@endpush
```
(`@stack('meta')` sudah ada di `layouts/public.blade.php` baris 30, di dalam `<head>`.)

- [ ] **Step 5: Jalankan, pastikan LULUS + regresi view hijau**

Run: `php artisan view:clear && php artisan test tests/Feature/RenderPerformanceTest.php tests/Feature/HomeLayoutTest.php tests/Feature/SeoTest.php tests/Feature/ArticleTest.php`
Expected: PASS semua. Bila `HomeLayoutTest`/`SeoTest` gagal karena markup berubah, sesuaikan assert ke bentuk baru (jangan ubah perilaku).

- [ ] **Step 6: Suite penuh + pint + commit**

```bash
php artisan test
vendor/bin/pint --dirty
git add resources/views/public tests/Feature/RenderPerformanceTest.php
git commit -m "feat(performa): pakai x-img di view publik + preload hero"
```

---

### Task 4: Cache statis 1 tahun (`public/.htaccess` + docroot prod)

**Files:**
- Modify: `public/.htaccess`
- Test: tak ada tes otomatis; verifikasi `curl`

**Interfaces:**
- Consumes: tak ada.
- Produces: header `Cache-Control: public, max-age=31536000, immutable` untuk berkas statis ber-ekstensi di daftar.

- [ ] **Step 1: Tambahkan aturan ke `public/.htaccess`**

Sisipkan sebelum `</IfModule>` terakhir (setelah blok rewrite), sebagai blok baru:
```
<IfModule mod_headers.c>
    <FilesMatch "\.(js|css|woff2|woff|jpg|jpeg|png|webp|avif|svg|ico|gif)$">
        Header set Cache-Control "public, max-age=31536000, immutable"
    </FilesMatch>
</IfModule>
```
`.xml` sengaja tidak disertakan (agar `/sitemap.xml` tetap `no-cache`).

- [ ] **Step 2: Pastikan situs lokal tetap 200 (sintaks `.htaccess` tak mematikan app)**

Run: `php artisan serve --port=8123 &` lalu `curl -s -o /dev/null -w "%{http_code}\n" http://127.0.0.1:8123/`
Expected: `200`. (Kalau memakai web server lain, cukup pastikan halaman tetap 200.)

- [ ] **Step 3: Commit (belum deploy)**

```bash
git add public/.htaccess
git commit -m "perf(performa): cache statis 1 tahun immutable"
```

- [ ] **Step 4: Setelah deploy (lihat "Deploy" di bawah), pasang di docroot prod**

```bash
source .deploy.env
ssh -p "${HOSTINGER_PORT:-65002}" "$HOSTINGER_USER@$HOSTINGER_HOST" '
set -e
D=~/domains/tikumpost.id/public_html
[ -f "$D/.htaccess.bak" ] || cp -p "$D/.htaccess" "$D/.htaccess.bak"
ln -sfn ~/tikumpost/public/.htaccess "$D/.htaccess"
ls -l "$D/.htaccess"
'
```
Rollback bila bermasalah: `cp -p ~/domains/tikumpost.id/public_html/.htaccess.bak ~/domains/tikumpost.id/public_html/.htaccess`.

- [ ] **Step 5: Verifikasi header (ini yang menentukan task lulus)**

```bash
JS=$(python3 -c "import json;print(json.load(open('public/build/manifest.json'))['resources/js/app.js']['file'])")
curl -sI "https://tikumpost.id/build/$JS" | grep -i "cache-control"
curl -sI "https://tikumpost.id/storage/articles/20261009220824.jpg" | grep -i "cache-control"
curl -sI "https://tikumpost.id/sitemap.xml" | grep -i "cache-control"
```
Expected: dua baris pertama `max-age=31536000`; baris sitemap tetap `no-cache`.

Bila tetap `max-age=604800`, jalankan **cadangan berurutan** (Review Focus #5: jangan biarkan situs 500):
1. Taruh `.htaccess` sama di dalam `public/build/` dan `storage/app/public/` (lalu deploy/rsync).
2. Ganti `Header set` → `Header always set`.
3. Bila masih ditolak host: catat temuan, **terima 7 hari**, lanjut — ini bukan penyebab utama skor 70.

---

### Task 5: Perbaikan render (shimmer, hero, transisi) + Lighthouse

**Files:**
- Modify: `resources/css/app.css` (baris ~33-46 selector transisi global; ~294-301 animasi hero; ~399-409 `@keyframes hero-rise`)
- Modify: `resources/js/app.js` (baris ~374 selector `img[data-fade]`)
- Test: `tests/Feature/AnimationCssTest.php`

**Interfaces:**
- Consumes: tak ada.
- Produces: shimmer berhenti pada semua `img.media-frame`; hero tak mulai dari `opacity: 0`; `hero-zoom` 0,6 s; transisi global tanpa `input/select/textarea`.

- [ ] **Step 1: Tulis tes gagal `tests/Feature/AnimationCssTest.php`**

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;

class AnimationCssTest extends TestCase
{
    private function css(): string
    {
        return file_get_contents(resource_path('css/app.css'));
    }

    public function test_hero_rise_tidak_mulai_dari_opacity_nol(): void
    {
        preg_match('/@keyframes hero-rise\s*{\s*from\s*{([^}]*)}/s', $this->css(), $m);

        $this->assertNotEmpty($m, 'keyframes hero-rise tidak ditemukan');
        $this->assertStringNotContainsString('opacity', $m[1]);
    }

    public function test_transisi_global_tidak_mencakup_field_form(): void
    {
        preg_match('/^\s*a,\s*button,\s*(.*?)\s*{\s*transition:/ms', $this->css(), $m);

        $this->assertNotEmpty($m, 'blok transisi global tidak ditemukan');
        foreach (['input', 'select', 'textarea'] as $field) {
            $this->assertStringNotContainsString($field, $m[1]);
        }
    }

    public function test_hero_zoom_dipendekkan(): void
    {
        $this->assertStringContainsString('hero-zoom 0.6s', $this->css());
    }
}
```

- [ ] **Step 2: Jalankan, pastikan GAGAL**

Run: `php artisan test tests/Feature/AnimationCssTest.php`
Expected: FAIL (3 tes)

- [ ] **Step 3: Ubah CSS**

1. Selector transisi global (baris ~33): hapus `input,` `select,` `textarea,` → sisakan `a, button, summary`.
2. `@keyframes hero-rise` (baris ~399): hapus `opacity: 0;` dari blok `from` (sisakan `transform: translateY(22px);`) dan dari `to` hapus `opacity: 1;`.
3. `html.js .hero-zoom` (baris ~299): `1.2s` → `0.6s`.

Catatan: `prefers-reduced-motion` **sudah** ditangani aturan global yang ada (`animation-duration: 0.001ms !important; animation-iteration-count: 1 !important`, baris ~488) — jangan tambah blok baru.

- [ ] **Step 4: Ubah JS — hentikan shimmer untuk semua gambar `media-frame`**

Baris ~374, ganti:
```js
    document.querySelectorAll('img[data-fade]').forEach((img) => {
```
menjadi:
```js
    document.querySelectorAll('img[data-fade], img.media-frame').forEach((img) => {
```
(sisa badan loop tak berubah: menambah `is-loaded` ke `img` dan ke `img.closest('.media-frame')`.)

- [ ] **Step 5: Jalankan, pastikan LULUS**

Run: `php artisan test tests/Feature/AnimationCssTest.php tests/Feature/AnimationTest.php`
Expected: PASS

- [ ] **Step 6: Suite penuh + pint + commit**

```bash
php artisan test
vendor/bin/pint --dirty
git add resources/css/app.css resources/js/app.js tests/Feature/AnimationCssTest.php
git commit -m "perf(performa): hentikan shimmer, hero tanpa opacity 0, transisi dipersempit"
```

- [ ] **Step 7: Ukur Lighthouse (bukti angka)**

```bash
npm run build
export CHROME_PATH="$HOME/.local/bin/chromium"
npx --yes lighthouse@12 https://tikumpost.id/ --quiet --only-categories=performance \
  --chrome-flags="--headless=new --no-sandbox --disable-dev-shm-usage" \
  --output=json --output-path=/tmp/opencode/lh-home-after.json
npx --yes lighthouse@12 "https://tikumpost.id/berita/buka-fgd-rdtr-dan-klhs-wabup-konkep-tegaskan-penataan-ruang-harus-berkelanjutan-dan-inklusif" \
  --quiet --only-categories=performance \
  --chrome-flags="--headless=new --no-sandbox --disable-dev-shm-usage" \
  --output=json --output-path=/tmp/opencode/lh-artikel-after.json
```
Expected (target spec): skor ≥ 90 kedua halaman, LCP ≤ 2,5 s, TBT ≤ 200 ms, CLS 0. Baseline sebelum: beranda 70 / 4,6 s; artikel 89 / 2,3 s. Angka sesungguhnya dilaporkan apa adanya ke pemilik (jangan dibulatkan ke atas).

---

## Deploy (setelah konfirmasi eksplisit pemilik)

```bash
php artisan media:variants            # buat varian lokal; ikut ter-rsync
DEPLOY_MESSAGE="perf: Core Web Vitals — gambar responsif, cache, render" npm run deploy
```
Setelah deploy: jalankan Task 4 langkah 4 (symlink docroot `.htaccess`) dan langkah 5 (`curl -I`), lalu Task 5 langkah 7 (Lighthouse). Opsional: `ssh ... php artisan media:variants` sekali lagi di prod (idempoten).
