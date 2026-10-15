# KabarKita — Portal Berita (Laravel)

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

# 4. Database (SQLite default — file sudah otomatis dibuat)
touch database/database.sqlite
php artisan migrate:fresh --seed

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
| Administrator | `admin@kabarkita.test` | `password` |
| Editor | `editor@kabarkita.test` | `password` |
| Penulis | `penulis@kabarkita.test` | `password` |
| Pembaca | `pembaca@kabarkita.test` | `password` |

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
DB_DATABASE=kabarkita
DB_USERNAME=root
DB_PASSWORD=
```

Lalu jalankan `php artisan migrate:fresh --seed`.

---

## Catatan / TODO

- **Editor artikel** saat ini memakai `<textarea>` yang menerima HTML. Untuk editor WYSIWYG, integrasikan TinyMCE/Trix/CKEditor di `resources/views/admin/articles/form.blade.php`.
- **Newsletter** menyimpan email ke tabel `subscribers`; pengiriman email massal (via queue) belum diimplementasikan.
- Gambar unggahan disimpan di disk `public` (`storage/app/public`), perlu `php artisan storage:link`.
- Belum ada pengujian otomatis (Pest/PHPUnit) — lihat `tests/` untuk mulai menambahkan.
