# Performa & Core Web Vitals — TikumPost

Tanggal: 2026-11-21. Status: menunggu persetujuan.
Pendahulu: `docs/superpowers/specs/2026-11-21-seo-indeks-google-design.md` (fase SEO, sudah ter-deploy).

## Niat & alasan

Setelah SEO teknis beres, fase berikutnya mengejar **Core Web Vitals** supaya halaman terasa cepat bagi pembaca dan sinyal "Page Experience" Google ikut membaik. Pemilik memilih arah **B** (aset + render), menunda **C** (cache HTML/CDN untuk anonim) karena mengubah perilaku.

## Kondisi sekarang (Lighthouse produksi, 21 Nov 2026)

- Beranda: skor **70**, LCP **4,6 s**, TBT 520 ms, CLS **0**, total 868 KiB, server 150 ms.
- Artikel: skor **89**, LCP 2,3 s, TBT 350 ms, CLS 0, total 257 KiB.

Temuan yang menuntun desain:

1. **Gambar tak responsif.** Unggahan disimpan apa adanya (`store('articles','public')`, maks 4 MB) tanpa resize/WebP. Audit `uses-responsive-images` menyebut **431 KiB** terbuang di beranda (infografis 190 KB + 108 KB dipakai kecil). Banyak `<img>` tanpa `width`/`height` (CLS masih 0 karena CSS `aspect-*`, tapi rapuh).
2. **Cache pendek.** `/build` dan `/storage` hanya `max-age=604800` (7 hari, default Hostinger). 23 resource ditandai "short cache" walau namanya sudah ber-hash/acak.
3. **Beban main-thread 5,3 s** di beranda (`style & layout` 1,9 s, `other` 2,4 s) — bukan berat gambar. Sumber: `transition` global pada `a,button,input,select,textarea,summary`; animasi masuk hero (`hero-rise` opacity 0, `hero-zoom` 1,2 s); shimmer `media-frame::after` **infinite** yang hanya berhenti bila JS menandai `is-loaded`, padahal citra non-`data-fade` (hero/kartu) tak pernah ditandai → shimmer jalan terus; ticker berjalan.
4. **`hero-rise`** memulai pada `opacity: 0` → teks hero tertunda 0,7 s + delay, dan tiap `data-reveal` di bawah fold juga `opacity: 0` sampai IntersectionObserver.
5. Font 4 bobot Instrument Sans (woff2 sudah di-preload otomatis oleh plugin Vite; `.woff` hanya fallback di CSS, tak diunduh). Bukan masalah utama.

## Tujuan

1. Lighthouse **≥ 90** di beranda **dan** halaman artikel (mobile, throttling default).
2. **LCP ≤ 2,5 s**, **TBT ≤ 200 ms**, **CLS tetap 0** di kedua halaman.
3. Berkas gambar yang diunduh beranda turun dari 868 KiB ke kisaran **≤ 500 KiB**.
4. Sumber daya statis punya `Cache-Control` panjang (1 tahun, `immutable`).

## Non-tujuan

- Cache HTML/CDN untuk pengunjung anonim (arah C) — ditunda; mengubah session, hitungan "dibaca", dan CSRF komentar.
- Cache sisi server, Redis/Memcached, Horizon/queue.
- Gambar di dalam isi artikel (HTML Quill): berbeda karena bukan field terstruktur. Ditunda; dicatat sebagai sisa.
- Perubahan desain yang terlihat pembaca (warna, tata letak, tipografi).
- Menambah dependency Composer/npm (gd native cukup; Lighthouse hanya alat ukur lokal, bukan dependency repo).

## Kendala

- Server tanpa Node; `public/build` ikut di-commit.
- Docroot Hostinger (`~/domains/tikumpost.id/public_html`) menyimpan **salinan nyata** `.htaccess`, `favicon.ico` (0 byte), `index.php`; hanya `build/` & `storage/` yang symlink. Pola ini sudah dipakai untuk `robots.txt` (kini symlink).
- GD 2.3.3 dengan dukungan WebP di server (terverifikasi lokal: `imagetypes() & IMG_WEBP` benar).
- Halaman publik butuh session (hitungan baca) & CSRF (komentar) → tak bisa di-cache penuh tanpa arah C.
- Lighthouse menganggap `max-age < 30 hari` sebagai "short cache".

## Pendekatan

**A — Aset saja.** Resize + WebP saat unggah, naikkan cache TTL, rapikan font.
*Trade-off:* risiko terendah, tapi tak menyentuh penyebab utama beban main-thread/LCP; perkiraan hanya sampai skor ~85.

**B — A + perbaikan render (dipilih).** Tambah: citra LCP (`fetchpriority=high`, `decoding=async`, dimensi eksplisit, tanpa shimmer), `srcset`/`sizes` + `width`/`height` di semua citra terstruktur, batasi `transition` global, hentikan shimmer saat citra dimuat, jaga elemen di atas fold tak tertahan `opacity: 0`, dan pecah inisialisasi JS yang tak perlu.
*Trade-off:* menyentuh CSS + beberapa view, tapi tak mengubah tata letak/warna; menangkap hampir seluruh perolehan CWV.

**C — B + cache HTML/CDN untuk anonim.** TTFB 150 ms → ~20 ms. *Trade-off:* mengubah session, hitungan "dibaca", CSRF; ditunda sampai B terbukti belum cukup.

Dipilih **B**.

## Rancangan (yang sudah diputuskan)

### 1. Pipeline gambar (gd native, tanpa dependency baru)

- **`App\Support\ImageVariant`** (kelas baru, `app/Support/`):
  - `const WIDTHS = [480, 960, 1440];`
  - `static ensure(string $path): array` — untuk tiap lebar, buat `<base>-<lebar>.webp` bila belum ada (proporsional, WebP kualitas 80), kembalikan daftar `['path' => ..., 'width' => ..., 'height' => ...]` yang benar-benar ada. Bila gd/WebP tak tersedia atau berkas bukan gambar, kembalikan `[]`.
  - `static srcset(string $path): string` — gabungan `url(...) Nw`; kosong bila tak ada varian.
  - `static intrinsic(string $path): ?array` — `[width, height]` asli via `getimagesize()`.
- **Hook unggah**: satu titik, `App\Support\ImageVariant::ensure()` dipanggil setelah menyimpan berkas di tiga tempat — `ArticleController::storeImage()` (`featured_image`), `MediaController`, `InfographicController`. Bila gagal, unggahan tetap sukses (varian hanya optimasi).
- **Backfill**: `php artisan media:variants` — iterasi `Article.featured_image`, `Infographic.image`, `Media` → `ensure()`. Idempoten.
  - **Skala terukur (produksi, 21 Nov 2026):** 10 artikel ber-`featured_image`, 2 infografis, 1 media; `storage/app/public` = 2,4 MB / 18 berkas. Jadi satu lintasan sinkron cukup — **tanpa** `--chunk`, tanpa antrean, tanpa `--dry-run`.
  - **Tempat menjalankan:** `deploy.sh` melakukan `rsync storage/app/public/ → prod` (aditif, tanpa `--delete`), jadi cukup jalankan perintah **secara lokal** sebelum deploy; berkas varian ikut tersalin. Boleh juga dijalankan sekali lagi di prod setelah deploy (idempoten, aman).
- **Komponen Blade** `resources/views/components/img.blade.php` (anonim):
  - Atribut: `path` (relatif disk `public` atau null), `alt`, `sizes`, `class`, `loading` (default `lazy`), `decoding` (default `async`), `fetchpriority`, `width`, `height`.
  - Keluaran: `<img src=... srcset=... sizes=... width=... height=... alt=... class=... loading=... decoding=... [fetchpriority=...]>`.
  - Bila `path` kosong → render placeholder (blok gradien) seperti pola sekarang, agar `<img>` tak pernah tanpa `src`.
  - Bila varian tak ada → `src` = berkas asli, tanpa `srcset` (tetap valid).
- **Pemakaian**: ganti `<img src="{{ Storage::disk('public')->url(...) }}">` di seluruh `resources/views/public/**` (hero, kartu, baris, sidebar, infografis, logo footer/header). Logo/header boleh tetap `<img>` biasa (kecil) — diputuskan: ikut komponen bila mudah, kalau tidak biarkan (bukan LCP).
- **Citra LCP** (hero beranda + gambar unggulan artikel): `loading="eager"`, `fetchpriority="high"`, dan untuk beranda tambah `<link rel="preload" as="image" imagesrcset=... imagesizes=...>` lewat `@push('meta')` (stack yang benar-benar ada di `<head>` `layouts/public.blade.php`; tak ada `@stack('head')`).
- **Nama berkas varian diberi versi**: `<base>-v1-<lebar>.webp` (konstanta `ImageVariant::VERSION`). Karena cache statis 1 tahun `immutable` dan berkas ditulis ulang di tempat, menaikkan `VERSION` (mis. saat `WIDTHS`/kualitas berubah) memberi URL baru dan menghindari salinan basi. Tanpa ini, perubahan kualitas/lebar akan tak terlihat pembaca selama setahun.

### 2. Cache statis 1 tahun

- Tambah di `public/.htaccess` (di dalam `<IfModule mod_headers.c>`):
  ```
  <FilesMatch "\.(js|css|woff2|woff|jpg|jpeg|png|webp|avif|svg|ico|gif)$">
      Header set Cache-Control "public, max-age=31536000, immutable"
  </FilesMatch>
  ```
- Terapkan ke server dengan menjadikan `.htaccess` docroot **symlink** ke `~/tikumpost/public/.htaccess` (pola sama seperti `robots.txt`), setelah dicadangkan ke `.htaccess.bak`.
- **Sumber `max-age=604800` belum dipastikan** (kemungkinan setelan server LiteSpeed, bukan `.htaccess`). Maka ini langkah **verifikasi-dulu**: pasang aturan, `curl -I`, dan bila header tak berubah pakai **cadangan berurutan**: (a) `.htaccess` terpisah di dalam `public/build/` dan di direktori storage target (`storage/app/public/` — `public/storage` symlink ke sana), (b) `Header always set` alih-alih `Header set`, (c) bila host memang menolak override, **terima cache 7 hari seperti sekarang** — ini bukan penyebab utama skor 70, jangan habiskan waktu di sini.
- Verifikasi: `curl -I` pada satu aset `/build/*.js` dan satu `/storage/*.jpg` → `max-age=31536000`.

### 3. Perbaikan render & LCP (CSS)

- **Shimmer**: hentikan pada semua citra `media-frame` setelah dimuat — di JS, tandai `is-loaded` untuk tiap `img.media-frame` saat `load`/`complete`, bukan hanya `img[data-fade]`. Efek: shimmer tak lagi berjalan terus.
- **`hero-rise`**: hapus ketergantungan `opacity: 0` pada elemen di atas fold. Hero tetap animasi masuk, tapi mulai dari `opacity` tinggi/`transform` saja (mis. `from { transform: translateY(12px) }`) atau `animation-fill-mode: none` tanpa `backwards`, agar cat pertama tak tertunda.
- **`hero-zoom`**: turunkan durasi 1,2 s → 0,6 s atau hapus untuk citra LCP (transform-only, jadi opsional).
- **`transition` global**: sisakan pada `a, button, summary`; buang `input, select, textarea` (dan `[data-reveal]` delay maksimum diturunkan bila perlu).
- **`prefers-reduced-motion`**: perluas agar mematikan `hero-rise`, `hero-zoom`, `data-reveal`, dan shimmer (ticker sudah ada).
- **Ticker**: tetap, tapi pastikan `animation-play-state: paused` saat `prefers-reduced-motion` (sudah) dan tidak memicu reflow (transform-only, sudah).

### 4. JS

- Pertahankan `app.js` sebagai satu entry (TBT didominasi style/layout, bukan eval JS: `scriptEvaluation` 244 ms).
- Tambahkan penanda `is-loaded` untuk `img.media-frame` (poin 3).
- Pastikan `data-reveal`/`data-fade` tak pernah menyembunyikan elemen di atas fold.

## Verifikasi

- **Unit/fitur baru** (`tests/Feature/PerformanceTest.php` atau serupa):
  - `ImageVariant::ensure()` membuat varian dengan lebar & format WebP yang benar (fixture gambar kecil yang di-generate via gd di tes).
  - Komponen `img` menghasilkan `srcset`/`sizes`/`width`/`height` yang benar; dan `fetchpriority="high"` hanya untuk gambar LCP.
  - Hook unggah: mengunggah artikel dengan `featured_image` menghasilkan varian.
  - `php artisan media:variants` idempoten (jalan dua kali → jumlah berkas sama).
- **Terukur (manual, oleh pelaksana)**: Lighthouse mobile sebelum/sesudah untuk beranda + satu artikel; target skor ≥ 90, LCP ≤ 2,5 s, TBT ≤ 200 ms, CLS 0. Angka sebelum sudah dicatat di atas.
- **Header**: `curl -I` aset `/build/*` dan `/storage/*` → `max-age=31536000`.
- **Suite**: `php artisan test` hijau tiap task; `vendor/bin/pint --dirty`.

## Keputusan lanjutan (hasil builder-check)

- **Pelaksana & akses prod:** pelaksana punya SSH prod (`.deploy.env`, kunci terpasang; `scripts/deploy.sh` sudah dipakai sepanjang fase SEO). Deploy = `DEPLOY_MESSAGE="..." npm run deploy`: build → commit → push → `git pull --ff-only` di server → `composer install --no-dev --no-scripts` → `migrate --force` → `rsync storage/app/public/` → `optimize:clear`/cache → health check. Karena itu langkah prod (backfill, symlink `.htaccess`) **masuk lingkup** dan dijalankan pelaksana; tak perlu artefak serah-terima manual.
  - **Urutan:** (1) kode + tes hijau + `media:variants` lokal, (2) deploy, (3) symlink `.htaccess` docroot di prod + `curl -I` verifikasi, (4) Lighthouse.
- **Pengukuran:** pelaksana memang bisa mengukur — Chromium lokal (`~/.local/bin/chromium`) + `npx lighthouse@12` terhadap URL **produksi**, dengan batas anggaran data produksi (10 artikel; dataset kecil, jadi TBT/LCP produksi representatif). Angka awal sudah dicatat di bagian "Kondisi sekarang"; angka sesudah diukur dengan cara sama. Bila Lighthouse tak bisa dijalankan, ganti gate numerik dengan pemeriksaan lebih ringan (`curl -I` header + asersi bentuk HTML: `srcset`, `width`/`height`, `fetchpriority`), dan serahkan gate numerik ke pemilik.
- **Tata letak langsung:** `SiteSettings::layout()` = `"tvri"` → halaman hidup = `public.home` (`home.blade.php`, hero ber-`media-frame`). Optimasi difokuskan ke situ. `home-klasik` **tidak** dioptimasi khusus, tapi otomatis ikut membaik karena memakai `partials/` yang sama (kartu, baris, sidebar) dan perbaikan CSS/animasi bersifat global.
- **Batas sesi/CSRF tetap:** `php artisan media:variants` jalan di CLI; tak menyentuh session/CSRF. Klaim "murah, idempoten" terbukti oleh skala di atas (13 berkas).
- **Logo/`favicon`:** logo header/footer berupa SVG → gd mengembalikan `[]` → komponen jatuh ke `<img src>` asli tanpa `srcset`. Tidak perlu perlakuan khusus.

## Sisa untuk pelaksana

- Daftar lebar varian (`[480, 960, 1440]`) boleh disesuaikan bila `sizes` di view menentukan lain. Tambahkan lebar kecil (**160, 320**) karena ada thumbnail 64 px (`h-16 w-14`) dan kartu ~380 px; tanpa itu thumbnail ikut mengunduh varian 480 px.
- Kualitas WebP: 80 (default); boleh diturunkan bila terlihat pecah.
- Gambar di isi artikel (HTML Quill) **di luar lingkup v1** — dicatat untuk fase berikutnya.
- Logo header/footer boleh tetap `<img>` biasa bila memakai komponen menambah kerumitan.
- Cara menerapkan `.htaccess` ke docroot (symlink vs salin) mengikuti pola `robots.txt`.
