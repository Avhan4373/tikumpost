# SEO Indeks Google — TikumPost

Tanggal: 2026-11-21. Status: menunggu persetujuan.

## Niat & alasan
Situs `https://tikumpost.id` harus cepat muncul di hasil Google. Pemilik ingin artikel baru cepat terindeks, bukan menunggu crawl otomatis berbulan-bulan.

## Kondisi sekarang (recon produksi)
Sudah baik: `robots.txt` allow semua, meta description + Open Graph dasar, `noindex` hanya di halaman perawatan, judul & deskripsi artikel dinamis dari kolom `meta_title`/`meta_description`.

Bolong:
- `sitemap.xml` → 404.
- `robots.txt` tanpa baris `Sitemap:`.
- Tak ada `<link rel="canonical">` di seluruh halaman.
- Tak ada JSON-LD (`Organization`, `NewsArticle`, `BreadcrumbList`).
- `<title>` beranda = `Beranda · TikumPost` — boros sinyal.
- Tak ada `og:image` / `twitter:card`; `og:type` selalu `website`, bukan `article`.
- Tak ada jalur verifikasi Google Search Console.
- Halaman hasil pencarian internal (`/cari`) ikut terindeks (konten tipis, duplikat).

## Tujuan
1. Mesin pencari bisa menemukan semua URL penting lewat sitemap.
2. Tidak ada duplikat (canonical + noindex halaman tipis).
3. Artikel punya markup `NewsArticle` agar layak rich result.
4. Beranda punya judul/deskripsi yang menyasar nama situs + kata kunci.
5. Pemilik punya jalur verifikasi Search Console tanpa utak-atik file server.

## Non-tujuan
- Fase performa (LCP/Core Web Vitals, cache, optimasi gambar) — fase 2 terpisah.
- RSS feed — Google tidak memakainya untuk indeks. Dilewati.
- Halaman penulis di sitemap — konten tipis (daftar artikel per penulis), dilewati.
- Backlink/otoritas domain — di luar kendali kode.
- Sitemap berita Google (`news sitemap`) — dipertimbangkan nanti bila volume artikel tinggi.

## Anti-tujuan
- Tak mengubah desain tampilan yang terlihat pembaca.
- Tak memblokir crawl yang sudah berjalan (mis. `Disallow` baru yang salah).
- Tak menambah dependency baru.

## Kendala
- Domain baru: indeks cepat tetap butuh Search Console "Minta pengindeksan"; kode hanya membuka jalan.
- `public/robots.txt` statis, jadi URL sitemap ditulis absolut.
- Halaman statis (`tentang`, `kontak`, `kebijakan-privasi`) masih hardcoded di `PageController` — sitemap ikut hardcode slug-nya.
- Server produksi tanpa Node; `public/build` ikut di-commit (tak relevan di sini karena tak menyentuh aset).

## Pendekatan

**A. Teknis + Search Console (dipilih).** Lengkapi sitemap, canonical, JSON-LD, judul beranda, og:image/twitter, noindex halaman tipis, plus satu field token verifikasi Google di Pengaturan. Pemilik verifikasi domain di Search Console, submit sitemap, minta indeks URL utama.
*Trade-off:* butuh satu langkah manual di sisi pemilik (buka Search Console). Ini yang benar-benar memicu indeks cepat.

**B. Search Console saja tanpa ubah kode.** Bisa minta indeks besok, tapi tanpa sitemap/canonical artikel baru tetap lambat ditemukan dan halaman tipis tetap terindeks. Ditolak: menyelesaikan setengah masalah.

**C. A + fase performa.** Menambah audit Lighthouse, cache, optimasi gambar. Ditunda sebagai fase 2 agar diff tetap kecil dan terverifikasi.

## Rancangan (yang sudah diputuskan)

1. **Sitemap.** `GET /sitemap.xml` → `SitemapController`. Isi: beranda (priority 1.0, changefreq daily), artikel `published()` (0.8, `lastmod` = `updated_at`), kategori (0.6), tag (0.5), 3 halaman statis (0.3). Tanpa cache (query murah).
2. **robots.txt.** Tambah baris `Sitemap: https://tikumpost.id/sitemap.xml`.
3. **Canonical.** Di layout: `request()->url()` + `?page=N` bila ada paginasi. Self-referencing.
4. **Robots meta.** Layout: `<meta name="robots" content="@yield('robots', 'index,follow')">`. Halaman pencarian internal (`/cari`) → `noindex,follow`.
5. **JSON-LD.**
   - Beranda: `Organization` + `WebSite`.
   - Artikel: `NewsArticle` (headline, image absolut, datePublished, dateModified, author Person, publisher Organization + logo, articleSection, mainEntityOfPage) + `BreadcrumbList`.
   - Slot `@push('schema')` di layout.
6. **Judul beranda.** Layout dapat section opsional `title_full`; beranda memakai `"<Nama Situs> — <Tagline>"`. Halaman lain tetap `"<Judul> · <Nama Situs>"`.
7. **Open Graph / Twitter.** Layout: `og:type` dapat di-override, `og:image` dapat di-override, tambah `twitter:card` + `twitter:image`. Artikel mengisi `og:image` (featured image absolut), `og:type=article`, `article:published_time`, `article:section`.
8. **Verifikasi Google.** Field baru `google_site_verification` (text) di grup SEO → layout merender `<meta name="google-site-verification" content="...">` bila terisi. Schema admin otomatis menampilkan field-nya.

## Langkah manual pemilik (di luar kode)
1. Buka https://search.google.com/search-console → **Add property** → **URL prefix** → `https://tikumpost.id/`.
2. Pilih metode **HTML tag**, salin token `content="..."`.
3. Tempel ke admin → **Pengaturan → SEO Default → Google Site Verification** → Simpan.
4. Klik **Verify** di Search Console.
5. **Sitemaps** → submit `sitemap.xml`.
6. **URL Inspection** → mintakan pengindeksan untuk beranda + 5–10 artikel utama.
7. Pantau **Pages** (jumlah terindeks) beberapa hari.

## Verifikasi
- `php artisan test` (tes baru `tests/Feature/SeoTest.php`: sitemap 200 + isi URL artikel, canonical ada, `NewsArticle` ada, judul beranda benar, `og:image` absolut, `/cari` noindex, meta verifikasi muncul saat setelan diisi).
- `curl -s https://tikumpost.id/sitemap.xml` → 200, `application/xml`, memuat URL artikel.
- `curl -s https://tikumpost.id/robots.txt` → memuat baris `Sitemap:`.
- Rich Results Test untuk satu URL artikel (manual, oleh pemilik).

## Sisa untuk pelaksana
- Nama view sitemap: `resources/views/public/sitemap.blade.php`.
- Batas jumlah URL dalam satu sitemap: seluruh artikel `published()` (amat besar pun belum jadi masalah di skala ini).
- Penentuan `og:image` bawaan bila artikel tak punya `featured_image`: pakai `site_logo`, fallback favicon.
