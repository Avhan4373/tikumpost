# TikumPost — Portal Berita (Laravel)

Website berita berbasis **Laravel 13** dengan dua sisi: **sisi publik** untuk pembaca dan **sisi admin (CMS)** untuk redaksi. Dibangun mengikuti `../Plan.md`.

---

## Fitur

**Publik**
- Beranda dengan headline, berita terbaru, terpopuler, dan topik populer
- Halaman detail artikel (penulis, tanggal, kategori, tag, waktu baca, jumlah dibaca, artikel terkait)
- Halaman kategori, tag, dan profil penulis (dengan paginasi)
- Pencarian artikel (judul + isi + ringkasan)
- Halaman statis (Tentang, Kontak, Kebijakan Privasi)
- Komentar pada artikel (dengan moderasi)
- Registrasi & login pembaca
- Berlangganan newsletter

**Admin / CMS** (`/admin`)
- Dashboard ringkasan statistik
- CRUD Artikel (editor HTML, unggah gambar utama, tag, status, jadwal tayang, headline, SEO)
- CRUD Kategori & Tag
- Manajemen Pengguna & Role (Admin, Editor, Penulis, Pembaca)
- Moderasi Komentar (setujui/tolak/hapus)
- Daftar Pelanggan Newsletter
- Manajer Media (unggah/hapus)

---

## Kebutuhan

- PHP 8.3+ (ekstensi: `pdo_sqlite` atau `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`)
- Composer 2
- Node.js 20+ & npm

---

## Instalasi

```bash
# 1. Dependensi PHP
composer install

# 2. Dependensi frontend
npm install

# 3. Konfigurasi
cp .env.example .env
php artisan key:generate

# 4. Database (MySQL — sesuaikan DB_* di .env lebih dulu)
php artisan migrate --seed

# 5. Symlink penyimpanan (untuk gambar unggahan)
php artisan storage:link

# 6. Build aset
npm run build      # produksi
# atau: npm run dev # pengembangan (hot reload)
```

### Menjalankan

```bash
php artisan serve
# buka http://127.0.0.1:8000
```

---

## Akun Bawaan (hasil seeder)

| Peran | Email | Kata Sandi |
|---|---|---|
| Administrator | `admin@tikumpost.test` | `password` |
| Editor | `editor@tikumpost.test` | `password` |
| Penulis | `penulis@tikumpost.test` | `password` |
| Pembaca | `pembaca@tikumpost.test` | `password` |

> Ganti kata sandi sebelum dipakai di produksi.

---

## Rute Utama

**Publik:** `/` · `/berita/{slug}` · `/kategori/{slug}` · `/tag/{slug}` · `/penulis/{id}` · `/cari?q=` · `/halaman/{slug}` · `/masuk` · `/daftar`

**Admin:** `/admin` · `/admin/articles` · `/admin/categories` · `/admin/tags` · `/admin/comments` · `/admin/users` · `/admin/subscribers` · `/admin/media`

---

## Struktur Penting

```
app/
├── Enums/            # UserRole, ArticleStatus, CommentStatus
├── Http/
│   ├── Controllers/
│   │   ├── Admin/    # Controller CMS
│   │   ├── Auth/     # Login/registrasi pembaca
│   │   └── Public/   # Controller sisi publik
│   ├── Middleware/   # EnsureUserIsStaff, EnsureUserIsAdmin
│   └── Requests/     # Validasi form
├── Models/           # Article, Category, Tag, Comment, Subscriber, Media, User
└── Policies/         # ArticlePolicy, CommentPolicy

resources/views/
├── layouts/          # public.blade.php, admin.blade.php
├── public/           # beranda, artikel, listing, auth, partials
└── admin/            # dashboard, articles, categories, tags, users, comments, ...

routes/
├── web.php           # rute publik + auth (memuat admin.php)
└── admin.php         # rute area admin
```

---

## Menggunakan MySQL

Ubah `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tikumpost
DB_USERNAME=root
DB_PASSWORD=
```

Lalu jalankan `php artisan migrate --seed`.

---

## Deploy ke Hostinger (shared hosting + SSH)

Server Hostinger tidak menyediakan Node.js, jadi aset Vite harus di-build lokal dan ikut di-commit (`public/build`).

1. Build lokal: `npm ci && npm run build`.
2. Commit `public/build` bersama perubahan lain, lalu `git push`.
3. Di server, aplikasi berada di luar `public_html` (mis. `~/tikumpost`):
   ```bash
   cd ~/tikumpost
   git pull
   composer install --no-dev --optimize-autoloader --no-scripts
   php artisan migrate --force
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```
   `--no-scripts` dipakai karena Hostinger memblokir `proc_open`.
4. `public_html` berisi `index.php` (menunjuk absolut ke `~/tikumpost`), plus symlink:
   ```bash
   ln -sfn ~/tikumpost/public/build   ~/domains/DOMAIN/public_html/build
   ln -sfn ~/tikumpost/storage/app/public ~/domains/DOMAIN/public_html/storage
   ```
5. `.env` hanya ada di server (tidak di-commit). Setelah mengubah `.env`, jalankan ulang `php artisan config:cache` agar environment variable dari panel Hostinger tidak menimpa konfigurasi.

---

## Catatan / TODO

- **Editor artikel** memakai editor WYSIWYG Quill (`resources/js/rich-editor.js` + `resources/css/editor.css`) dengan unggah gambar langsung ke Manajer Media. Jalankan `npm run build` setelah mengubah aset editor.
- **Batas unggah** mengikuti `upload_max_filesize`/`post_max_size` PHP. Gambar yang lebih besar dari batas otomatis diperkecil di browser; untuk mengizinkan file mentah lebih besar, naikkan nilainya di `php.ini` lalu restart server.
- **Perlindungan isi berita** (`data-protected-content` di `resources/views/public/article.blade.php`, `resources/js/app.js`, `resources/css/app.css`) menonaktifkan seleksi, salin, potong, klik kanan, dan seret gambar pada isi artikel. Ini penghalang praktis di browser, bukan pengamanan absolut: pengguna teknis tetap bisa membaca HTML lewat DevTools/`curl`.
- **Newsletter** menyimpan email ke tabel `subscribers`; pengiriman email massal (via queue) belum diimplementasikan.
- Gambar unggahan disimpan di disk `public` (`storage/app/public`), perlu `php artisan storage:link`.
- Belum ada pengujian otomatis (Pest/PHPUnit) — lihat `tests/` untuk mulai menambahkan.
