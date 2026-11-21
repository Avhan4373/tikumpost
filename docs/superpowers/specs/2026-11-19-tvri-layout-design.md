# Layout TVRI News (pengganti semua preset)

Tanggal: 2026-11-19
Status: diimplementasikan (lokal, belum di-commit/deploy)
Ruang lingkup: header situs, beranda, detail berita

## Intent

Pemilik situs ingin tampilan mengikuti tata letak **TVRI News** (tvrinews.com) tanpa
bagian iklan. Preset lama (Klasik/Majalah/Ringkas) dibatalkan; TVRI menjadi satu-satunya
layout situs. Header memakai biru khas TVRI.

Karena hanya ada satu layout, tidak ada setting `layout_preset` dan tidak ada select
admin. Kalau nanti butuh varian kedua, setting ditambah saat itu.

## Yang diadopsi dari TVRI

- Header biru: logo kiri, navigasi horizontal inline, tombol Live merah, search,
  tombol Masuk/Daftar kanan.
- Bar ticker merah "BREAKING NEWS" berjalan.
- Hero: 1 berita utama besar (2/3) + 4 kartu thumbnail (grid 2x2, 1/3), judul overlay
  di atas gambar + chip kategori.
- Judul seksi berikon kotak merah + subjudul + tautan "Lihat Semua →".
- Strip kategori berselang latar putih / abu, kartu horizontal (gambar kiri, isi kanan),
  2 kolom.
- Berita Terbaru: daftar baris horizontal (gambar + judul + ringkasan + waktu) di kiri,
  sidebar kanan berisi Terpopuler bernomor, Newsletter, Kategori, Topik, Infografis.

## Yang TIDAK diadopsi

- Semua iklan: banner kiri/kanan, kotak 300x250, sticky ad, video floating.
- "Berita 30 Detik" dan "Galeri Foto" (tidak ada sumber datanya).
- Warna biru pada elemen lain: aksen situs tetap merah brand. Biru hanya header
  dan blok Newsletter (TVRI memakai kotak newsletter biru gelap).

## Non-goals

- Setting layout/preset di admin, pemilih layout oleh pengunjung.
- Perubahan halaman kategori, pencarian, tag, penulis, halaman statis (hanya ikut
  header baru karena header dipakai global).
- Migrasi database, dependency baru, perubahan skema data.
- Menyalin struktur DOM TVRI apa adanya (Tailwind, bukan markup asli mereka).

## Anti-goals

- Konten hilang di mobile atau teks tidak terbaca di mode gelap.
- Menyalin blok sidebar yang sama tiga kali tanpa partial.
- Menambah query per artikel (N+1).

## Data yang dibutuhkan

`HomeController::index()` (tambahan):

- `$ticker`: 5 judul terbaru (`Article::published()->orderByDesc('published_at')->take(5)->get()`).
- `$sections`: maksimal 2 kategori teratas yang punya artikel, masing-masing 4 artikel
  terbaru, eager load `category`:

```php
$sections = Category::ordered()
    ->with(['articles' => fn ($q) => $q->published()->with('category')
        ->latest('published_at')->take(4)])
    ->get()
    ->filter(fn ($c) => $c->articles->isNotEmpty())
    ->take(2);
```

Data lama tetap dipakai: `$headline` (5), `$latest` (6), `$popular` (5), `$categories`,
`$tags` (12), `$infographics` (4). Tidak ada kolom baru — "breaking" memakai judul terbaru.

`ArticleController` tidak berubah; data sidebar yang ada sudah cukup.

## Desain

### Header (`public/partials/header.blade.php`)

```
┌──────────────────────────── strip tanggal (slate-900) ─────────────────────────┐
├──────────────────────────── bar biru (blue-700) ───────────────────────────────┤
│ [logo]  Beranda  Nasional  …  Tren   (Live)  (cari)  Masuk  Daftar  (tema)     │
├──────────────────────────── ticker merah (hanya beranda) ──────────────────────┤
│ BREAKING NEWS • judul • judul • judul …                                        │
└────────────────────────────────────────────────────────────────────────────────┘
```

- Nav horizontal pindah ke dalam bar biru (bukan baris putih terpisah).
- Teks nav putih, aktif: garis bawah putih (`nav-link` sudah punya underline).
- Nav dibungkus `no-scrollbar overflow-x-auto` + `whitespace-nowrap`: pada 1024–1280px
  yang tidak muat digeser horizontal, tidak wrap.
- Tombol Live: pill merah `bg-red-600` + titik putih, tautan ke `route('reels.index')`;
  `hidden xl:inline-flex` supaya tidak berebut ruang di desktop sempit.
- Search desktop: `<details>` native (tanpa JS) — ikon di bar biru membuka panel input
  `absolute right-0 top-full`. Versi mobile tetap di panel.
- Masuk/Daftar jadi pill putih (`bg-white text-blue-700`) di bar biru.
- Tautan "Langganan" di bar desktop dihapus (duplikat newsletter di footer/sidebar);
  tetap ada di panel mobile.
- Panel mobile tetap putih, isi menu tidak berubah.
- Dropdown menu tetap berfungsi (`data-menu-dropdown`); jangan menambah dropdown
  "Lainnya" karena `MenuTest` menghitung jumlah dropdown.
- **Sticky collapse:** hanya strip tanggal yang diberi `site-header-collapse`.
  Bar biru TIDAK memakai class itu, jadi nav tetap terlihat saat scroll. Tidak ada
  perubahan `app.js`.
- Ticker hanya dirender bila `$ticker` tersedia (beranda). Beri komentar
  `ponytail:` bahwa halaman lain bisa menyusulkan datanya nanti.
- Logo gambar yang ada tetap dipakai; teks nama situs jadi putih.

Catatan: artikel boleh muncul di hero dan lagi di strip kategori/berita terbaru —
TVRI juga begitu, dan tidak ada query dedupe.

### Beranda (`public/home.blade.php`, tulis ulang)

Struktur halaman berupa seksi full-width dengan isi `max-w-7xl`:

1. **Hero** (container, latar putih)
   `grid gap-4 lg:grid-cols-3`:
   - utama: `lg:col-span-2`, gambar `hero-zoom`, overlay gradient,
     chip kategori merah, `h1` `hero-rise`, ringkasan (desktop), meta.
   - kanan: `grid grid-cols-2 gap-4`, 4 kartu gambar `aspect-[4/3]`, chip kategori
     kecil, judul `line-clamp-2` overlay.
   - Semua gambar tetap memakai `media-frame` + `data-fade` (test animasi).
2. **Strip kategori** (maks 2, latar `bg-slate-50` bergantian)
   `section-heading` + `grid gap-5 md:grid-cols-2` dari partial `article-row`.
3. **Berita Terbaru** (container, latar putih)
   `grid gap-8 lg:grid-cols-[minmax(0,1fr)_20rem]`:
   - kiri: `section-heading`, lalu daftar `divide-y` dari `article-row` (varian baris).
   - kanan: `<aside class="space-y-5">` berisi blok Terpopuler (nomor + thumbnail),
     Newsletter biru, Kategori, Topik Populer, Infografis (dengan lightbox lama).
4. **Lightbox infografis** tetap seperti sekarang.

Mobile: hero menumpuk (1 besar, lalu 2x2), strip 1 kolom, sidebar turun ke bawah.

### Partial baru

- `public/partials/section-heading.blade.php`
  Parameter: `$title`, `$subtitle` (opsional), `$href` + `$linkLabel` (opsional).
  Markup: kotak ikon merah (`rounded-lg bg-red-600` + SVG garis), judul, subjudul,
  tautan kanan. Dipakai strip kategori, Berita Terbaru, Artikel Terkait.
- `public/partials/article-row.blade.php`
  Parameter: `$article`, `$excerpt` (bool, default true).
  Markup: `<article>` flex; gambar `h-24 w-32 sm:h-28 sm:w-44` dengan chip kategori,
  judul `line-clamp-2`, ringkasan `line-clamp-2` (disembunyikan di mobile), waktu
  `diffForHumans`. Dipakai strip kategori dan daftar Berita Terbaru.

`article-card.blade.php` tidak diubah; tetap dipakai "Artikel Terkait".

### Detail berita (`public/article.blade.php`)

- Ganti judul seksi "Artikel Terkait" dengan `section-heading` (ikon + tautan kategori).
- Sisa struktur dan lebar konten dipertahankan.
- Sidebar (`article-sidebar.blade.php`): kotak Newsletter jadi biru gelap
  (`from-blue-700 to-blue-900`) dengan tombol putih; blok lain dirapikan mengikuti
  gaya blok berikon `section-heading` mini.

### CSS (`resources/css/app.css`)

- `.ticker-track`: animasi `translateX(0 → -50%)` linear 40s infinite; isi digandakan
  dua kali; `:hover` pause; `prefers-reduced-motion: reduce` → animasi mati.
- Header biru tidak dipetakan ulang di `.dark`; pastikan teks selalu putih dan
  kontras cukup. Tambah varian gelap untuk bar biru bila perlu (`html.dark .site-header-blue`).

## Testing

`tests/Feature/HomeLayoutTest.php` (baru):

- Beranda memuat `data-ticker`, `data-hero-lead`, `data-hero-tile` (4 kali), dan
  atribut `data-home="tvri"`.
- Judul seksi kategori tampil dan tautan "Lihat Semua" mengarah ke rute kategori.
- Daftar Berita Terbaru memakai `data-article-row`.
- Infografis terbit tetap tampil (sudah dicakup `InfographicTest`).

Test lama wajib hijau, khususnya:
- `AnimationTest`: `hero-rise`, `hero-zoom`, `media-frame`, `data-fade` masih ada.
- `InfographicTest`: gambar infografis beranda.
- `MenuTest`: label menu dari database tetap tampil.

## File yang disentuh

| File | Perubahan |
|---|---|
| `resources/views/public/partials/header.blade.php` | bar biru + nav inline + ticker |
| `resources/views/public/home.blade.php` | tulis ulang layout TVRI |
| `resources/views/public/partials/section-heading.blade.php` | baru |
| `resources/views/public/partials/article-row.blade.php` | baru |
| `resources/views/public/article.blade.php` | judul seksi + tautan kategori |
| `resources/views/public/partials/article-sidebar.blade.php` | newsletter biru, blok berikon |
| `app/Http/Controllers/Public/HomeController.php` | `$ticker`, `$sections` |
| `resources/css/app.css` | animasi ticker, penyesuaian header gelap |
| `tests/Feature/HomeLayoutTest.php` | baru |

## Rollout

- Kerjakan lokal, `php artisan test` hijau, cek manual desktop (1440) + mobile (390).
- `npm run build` lokal untuk aset (server tidak punya Node).
- Tidak commit/deploy sampai diminta.

## Catatan implementasi (2026-11-19)

- Beranda memakai `@section('body_class', 'bg-white')`; strip kategori pertama tetap abu
  agar pola selang-seling TVRI terlihat.
- Pencarian desktop memakai `<details>` native (0 baris JS).
- Tautan "Langganan" desktop dihapus; versi panel mobile tetap.
- Judul blok sidebar tetap memakai garis merah (bukan kotak ikon) agar sama dengan
  sidebar beranda; `section-heading` berikon dipakai untuk judul seksi besar saja.
- Verifikasi: 62 test lulus; desktop 1440 & mobile 390 tanpa scroll horizontal;
  mode gelap header `#1e3a8a`, teks nav putih; ticker beranimasi dan berhenti saat hover.