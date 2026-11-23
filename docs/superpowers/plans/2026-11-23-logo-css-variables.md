# Logo CSS Variables Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Wordmark `tikumpost.id` menjadi SVG inline yang warnanya dikendalikan CSS variables dan mengikuti tema.

**Architecture:** PNG wordmark dijejak menjadi dua grup path (ink + accent), disimpan di partial Blade, dipakai di semua tampilan. Warna didefinisikan di `:root` dan di-override di `.dark` / konteks gelap.

**Tech Stack:** Laravel Blade, Tailwind CSS v4, OpenCV + Pillow (tracking sekali saat build).

**Spec:** `docs/superpowers/specs/2026-11-23-logo-css-variables-design.md`

## Global Constraints

- Tailwind v4: `@custom-variant dark (&:where(.dark, .dark *))`.
- Warna: ink `#0C0C52`, accent `#FFB300`; dark: `#f8fafc` / `#fbbf24`.
- Tidak ada dependency baru; tidak ada file logo baru untuk tiap tema.
- `site_logo` tetap dipakai untuk OG image / schema.org.

## Review Focus

- SVG inline benar-benar mewarisi CSS variable (bukan lewat `<img>`).
- Mode gelap mengubah warna tanpa ganti file.
- Halaman maintenance & sidebar admin (selalu gelap) tetap terbaca.
- SEO meta/JSON-LD tidak berubah.
- Ukuran SVG cukup ringan untuk dipakai dua kali per halaman.

---

### Task 1: Jejak PNG ke path SVG

**Files:**
- Create: `scripts/trace-logo.py`
- Create: `resources/views/partials/logo.blade.php`

**Interfaces:**
- Produces: `@include('partials.logo', ['class' => ...])` → SVG dengan `.logo-ink` dan `.logo-accent`.

- [ ] **Step 1:** Tulis `scripts/trace-logo.py` yang membaca PNG wordmark, memisahkan mask warna ink/accent, menjejak kontur (`RETR_CCOMP` + `approxPolyDP`), dan menulis markup `<path>` ke partial Blade.
- [ ] **Step 2:** Jalankan script, pastikan SVG hanya berisi dua `<path>` dan ukurannya < 80 KB.
- [ ] **Step 3:** Verifikasi SVG dirender benar (buka di browser / cek path count).

### Task 2: Variabel CSS

**Files:**
- Modify: `resources/css/app.css`

- [ ] **Step 1:** Tambah `--logo-ink`, `--logo-accent` di `:root`, override di `.dark`, aturan `.logo-ink`/`.logo-accent` dengan transisi `fill`.
- [ ] **Step 2:** Build CSS (`npm run build`) dan cek tidak ada error.

### Task 3: Pakai partial di semua tampilan

**Files:**
- Modify: `resources/views/public/partials/header.blade.php`
- Modify: `resources/views/public/partials/header-klasik.blade.php`
- Modify: `resources/views/public/partials/footer.blade.php`
- Modify: `resources/views/layouts/admin.blade.php`
- Modify: `resources/views/public/maintenance.blade.php`
- Modify: `app/Support/SiteSettings.php`

- [ ] **Step 1:** Ganti `<img src="site_logo">` dengan `@include('partials.logo', ...)`, hapus cabang fallback inisial.
- [ ] **Step 2:** Tambah override lokal `--logo-ink` untuk sidebar admin & maintenance.
- [ ] **Step 3:** Perjelas teks bantuan field `site_logo` (dipakai untuk SEO/sosial).

### Task 4: Verifikasi

**Files:**
- Create: `tests/Feature/LogoTest.php`

- [ ] **Step 1:** Test: beranda mengandung SVG inline dengan kelas `logo-ink` dan `logo-accent`.
- [ ] **Step 2:** Test: halaman maintenance mengandung SVG dengan override warna terang.
- [ ] **Step 3:** Jalankan `php artisan test` dan `npm run build`.
- [ ] **Step 4:** Commit perubahan.
