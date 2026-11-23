# Logo fleksibel via CSS Variables — desain

## Intent

Logo wordmark `tikumpost.id` yang sekarang (PNG navy + titik oranye) diubah menjadi SVG inline
yang warnanya dikendalikan CSS variables, sehingga saat tema berganti warna logo ikut berubah
tanpa mengganti file logo.

## Goals

- Warna utama logo (`#0C0C52`) dan aksen (`#FFB300`) menjadi CSS variables.
- Mode gelap otomatis memakai warna logo yang lebih terang.
- Tidak ada file logo baru yang perlu diunggah/disalin ketika tema berubah.

## Non-goals / anti-goals

- Tidak menambah preset warna brand baru di halaman Tema (bisa menyusul).
- Tidak mengubah bentuk wordmark/tagline secara visual.
- Tidak mempertahankan unggahan `site_logo` sebagai sumber gambar di header/footer — PNG tidak
  bisa diwarnai ulang oleh CSS.

## Constraints

- CSS custom properties tidak menembus boundary `<img>` / external SVG; hanya SVG inline yang
  terasa fleksibel.
- Proyek memakai Tailwind v4, `@custom-variant dark (&:where(.dark, .dark *))`, dan tema
  disimpan di `localStorage` lewat `partials/theme-init`.
-Alat yang tersedia: OpenCV + Pillow untuk menjejak PNG menjadi path SVG.

## Approaches

- **A. SVG inline + CSS variables (dipilih).** Wordmark dijejak menjadi dua grup path
  (`--logo-ink`, `--logo-accent`) di partial Blade. Paling andal lintas browser, tajam di semua
  ukuran, warna benar-benar bebas diganti dari CSS.
- **B. CSS mask dari file logo.** Upload tetap dipakai, tetapi aksen oranye hilang dan logo
  hanya satu warna. Ditolak.
- **C. Beberapa file logo per tema.** Bertentangan dengan syarat "tanpa ganti file". Ditolak.

## Design

1. **Aset:** jejak PNG `storage/app/public/settings/G2aY7r8QvW9zwSClPdOMEe0gZuChmLcttJhG6Hoc.png`
   (5757×962) dengan threshold alpha, `findContours` + `approxPolyDP`, dipisah menjadi path
   navy (`#0C0C52`) dan oranye (`#FFB300`). Hasil: markup SVG ringkas (tanpa base64 PNG).
2. **Partial baru** `resources/views/partials/logo.blade.php`: `<svg viewBox="0 0 5757 962">`
   dengan `<path class="logo-ink">` dan `<path class="logo-accent">`, `role="img"` +
   `aria-label` dari nama situs, serta class width/height yang bisa dioverride lewat `$class`.
3. **CSS** di `resources/css/app.css`:
   - `:root { --logo-ink: #0c0c52; --logo-accent: #ffb300; }`
   - `.dark { --logo-ink: #f8fafc; --logo-accent: #fbbf24; }`
   - `.logo-ink { fill: var(--logo-ink); } .logo-accent { fill: var(--logo-accent); }`
   - transisi `fill` singkat agar pergantian tema halus.
4. **Pemakaian:** ganti `<img src="site_logo">` dengan `@include('partials.logo')` di:
   - `public/partials/header.blade.php` (tvri),
   - `public/partials/header-klasik.blade.php`,
   - `public/partials/footer.blade.php`,
   - `layouts/admin.blade.php`,
   - `public/maintenance.blade.php` (dengan override `--logo-ink: #f8fafc` karena halaman
     ini selalu gelap dan tidak memuat `theme-init`).
5. **`site_logo` tetap dipertahankan** untuk OG image, schema.org JSON-LD, dan SEO — bukan
   lagi untuk tampilan logo. Teks bantuan field di `SiteSettings` diperjelas.
6. **Fallback:** inline SVG selalu tampil, jadi cabang inisial huruf di header/footer dihapus
   (logo bawaan kini selalu tersedia). Tidak ada field/flag admin baru.

## Builder notes

- Jejak path harus dibersihkan (titik berurutan, tanpa duplikat) supaya ukuran SVG tetap kecil.
- Pastikan `og:image` / JSON-LD masih memakai URL `site_logo` agar tidak merusak SEO.
- Tambahkan test feature kecil yang memastikan SVG inline dan class `logo-ink`/`logo-accent`
  tampil di beranda.
