# Sembunyikan Halaman Login — desain

## Intent

Halaman login (`/masuk`) saat ini terlihat publik: tombol "Masuk" ada di header dan tamu yang
membuka `/admin` langsung diarahkan ke sana. Tujuannya menyembunyikan pintu masuk agar orang
iseng tidak menemukan/mencoba-coba halaman login, sekaligus membersihkan sisa branding lama
"KabarKita" di halaman login.

## Goals

- Tidak ada lagi tautan "Masuk" yang terlihat di situs publik.
- Login hanya bisa diakses lewat path rahasia yang diatur dari `.env`.
- Tamu yang membuka `/admin` mendapat **404**, bukan redirect ke halaman login.
- Halaman login memakai logo dan nama situs dinamis (TikumPost), tanpa teks "KabarKita".
- Percobaan login dibatasi (throttle) untuk mengurangi brute force.

## Non-goals / anti-goals

- Bukan pengganti keamanan sesungguhnya: path rahasia hanya mengurangi pemindaian otomatis.
- Tidak menambah 2FA, CAPTCHA, atau allowlist IP (bisa menyusul).
- Tidak mengubah alur komentar pembaca (komentar tamu tetap berjalan tanpa login).

## Constraints

- Route name `login` harus dipertahankan karena dipakai form, test, dan middleware auth.
- Path login harus berupa konfigurasi, bukan hardcode, agar bisa diganti per-server.
- Proyek Laravel 13; alias middleware didaftarkan di `bootstrap/app.php`.

## Approaches

- **A. Path rahasia + `/admin` 404 (dipilih).** Semua tautan dihapus, login pindah ke path dari
  `.env`, dan middleware baru membuat `/admin` tampak tidak ada bagi tamu. URL login tidak bocor
  lewat redirect.
- **B. Path rahasia saja.** Lebih sedikit kode, tetapi `/admin` tetap redirect ke halaman login
  sehingga lokasinya mudah ditemukan. Ditolak.
- **C. A + allowlist IP.** Paling ketat, tetapi merepotkan bila IP operator berubah atau lewat
  Cloudflare. Ditolak untuk sekarang.

## Design

1. **Konfigurasi** `config/admin.php`: `'login_path' => env('ADMIN_LOGIN_PATH', 'kelola-tikum-7b3f9c')`.
   Tambahkan `ADMIN_LOGIN_PATH=kelola-tikum-7b3f9c` ke `.env.example` dan `.env` lokal.
2. **Route** (`routes/web.php`): GET/POST login memakai `config('admin.login_path')`, name tetap
   `login`. POST diberi `throttle:5,1`. Path `/masuk` lama otomatis hilang (404).
3. **Penanganan tamu** (`bootstrap/app.php`): `AuthenticationException` untuk request `admin` /
   `admin/*` dirender sebagai **404**, bukan redirect. Pendekatan ini dipakai karena middleware
   `auth` punya prioritas lebih tinggi daripada middleware kustom sehingga tetap redirect lebih
   dulu.
4. **AuthController**: setelah login sukses, staf diarahkan ke `admin.dashboard`
   (`redirect()->intended(...)` tetap dihormati).
5. **View**: hapus blok tautan "Masuk" di `public/partials/header.blade.php` (desktop + mobile)
   dan `header-klasik.blade.php` (desktop + mobile). Halaman login memakai
   `@include('partials.logo')` + nama situs dinamis, serta meta `noindex,nofollow`.
6. **Test**: feature test baru memastikan `/admin` tamu = 404, `/masuk` = 404, path rahasia = 200,
   halaman login tidak memuat "KabarKita", dan login staf diarahkan ke dashboard. Test
   `MenuTest` yang mengharapkan redirect ke `login` disesuaikan menjadi 404.
7. **Deploy**: tambahkan `ADMIN_LOGIN_PATH` ke `.env` server lalu jalankan
   `php artisan config:cache` (sudah termasuk alur `scripts/deploy.sh`).

## Builder notes

- Pastikan path tanpa slash di depan dan tidak bentrok dengan route publik lain.
- Jangan menyembunyikan tombol "Keluar"/"Dashboard" untuk staf yang sudah login.
- `resources/views/welcome.blade.php` tidak dirutekan; tautan login di dalamnya boleh dibiarkan
  atau dibersihkan tanpa efek.
