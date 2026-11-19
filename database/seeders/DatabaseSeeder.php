<?php

namespace Database\Seeders;

use App\Enums\ArticleStatus;
use App\Enums\CommentStatus;
use App\Enums\UserRole;
use App\Models\Article;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Subscriber;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ---- Pengguna ----
        $admin = User::create([
            'name' => 'Andi Pratama', 'email' => 'admin@tikumpost.test',
            'password' => Hash::make('password'), 'role' => UserRole::Admin,
            'bio' => 'Pemimpin redaksi TikumPost.',
        ]);
        $editor = User::create([
            'name' => 'Siti Rahayu', 'email' => 'editor@tikumpost.test',
            'password' => Hash::make('password'), 'role' => UserRole::Editor,
            'bio' => 'Editor desk nasional.',
        ]);
        $author = User::create([
            'name' => 'Budi Santoso', 'email' => 'penulis@tikumpost.test',
            'password' => Hash::make('password'), 'role' => UserRole::Author,
            'bio' => 'Jurnalis teknologi dan ekonomi.',
        ]);
        $reader = User::create([
            'name' => 'Dewi Lestari', 'email' => 'pembaca@tikumpost.test',
            'password' => Hash::make('password'), 'role' => UserRole::Reader,
        ]);

        // ---- Kategori ----
        $categoryNames = [
            ['Nasional', 'Berita terkini seputar dalam negeri.'],
            ['Ekonomi', 'Perkembangan ekonomi dan bisnis.'],
            ['Teknologi', 'Inovasi dan kabar dunia teknologi.'],
            ['Olahraga', 'Sepak bola, bulu tangkis, dan lainnya.'],
            ['Hiburan', 'Film, musik, dan selebritas.'],
            ['Internasional', 'Peristiwa dari mancanegara.'],
        ];
        $categories = collect($categoryNames)->map(fn ($c, $i) => Category::create([
            'name' => $c[0], 'slug' => Str::slug($c[0]), 'description' => $c[1], 'order' => $i,
        ]));

        // ---- Tag ----
        $tagNames = ['Pemilu', 'Ekonomi Digital', 'Kecerdasan Buatan', 'Startup', 'Liga Indonesia',
            'Film Indonesia', 'Energi', 'Kesehatan', 'Pendidikan', 'Infrastruktur'];
        $tags = collect($tagNames)->map(fn ($t) => Tag::create(['name' => $t, 'slug' => Str::slug($t)]));

        // ---- Artikel ----
        $articles = [
            ['Pemerintah Percepat Pembangunan Infrastruktur Digital di Daerah 3T', 'Nasional', 0],
            ['Bank Indonesia Pertahankan Suku Bunga Acuan di Tengah Tekanan Global', 'Ekonomi', 0],
            ['Startup Lokal Raih Pendanaan Seri B Senilai Rp500 Miliar', 'Teknologi', 1],
            ['Timnas Indonesia Melaju ke Babak Semifinal Piala AFF', 'Olahraga', 0],
            ['Film Indonesia Cetak Rekor Penonton Sepanjang Masa', 'Hiburan', 0],
            ['KTT ASEAN Bahas Kerja Sama Ekonomi Kawasan', 'Internasional', 0],
            ['Kecerdasan Buatan Ubah Lanskap Dunia Kerja Indonesia', 'Teknologi', 1],
            ['Harga Beras Mulai Turun Jelang Musim Panen Raya', 'Ekonomi', 1],
            ['Program Beasiswa Baru untuk 10 Ribu Mahasiswa Dibuka', 'Nasional', 1],
            ['Liga Indonesia: Persaingan Gelar Juara Semakin Ketat', 'Olahraga', 1],
            ['Transisi Energi Terbarukan Jadi Prioritas Pemerintah', 'Ekonomi', 0],
            ['Grup Musik Tanah Air Gelar Tur Konser Asia Tenggara', 'Hiburan', 1],
        ];

        $authors = [$admin, $editor, $author];

        foreach ($articles as $i => [$title, $cat, $headline]) {
            $article = Article::create([
                'user_id' => $authors[$i % 3]->id,
                'category_id' => $categories->firstWhere('name', $cat)->id,
                'title' => $title,
                'slug' => Str::slug($title),
                'excerpt' => 'Ringkasan singkat: '.$title.'. Baca selengkapnya di TikumPost.',
                'body' => '<p>'.implode('</p><p>', [
                    fake()->paragraph(7),
                    fake()->paragraph(7),
                    fake()->paragraph(6),
                ]).'</p>',
                'status' => ArticleStatus::Published,
                'is_headline' => $headline === 0,
                'views' => rand(200, 9000),
                'published_at' => now()->subDays(count($articles) - $i)->subHours(rand(1, 20)),
                'meta_description' => 'Berita terkini tentang '.$title,
            ]);

            $article->tags()->attach($tags->random(rand(2, 4))->pluck('id'));
        }

        // Beberapa draf
        foreach (['Draf: Analisis Kebijakan Moneter 2026', 'Draf: Review Gadget Terbaru'] as $t) {
            Article::create([
                'user_id' => $author->id, 'category_id' => $categories->random()->id,
                'title' => $t, 'slug' => Str::slug($t), 'body' => '<p>Draf artikel.</p>',
                'status' => ArticleStatus::Draft, 'published_at' => null,
            ]);
        }

        // ---- Komentar ----
        $published = Article::where('status', ArticleStatus::Published)->get();
        foreach ($published->random(6) as $article) {
            Comment::create([
                'article_id' => $article->id, 'user_id' => $reader->id,
                'body' => fake()->sentence(12), 'status' => CommentStatus::Approved,
            ]);
            Comment::create([
                'article_id' => $article->id, 'user_id' => $reader->id,
                'body' => fake()->sentence(10), 'status' => CommentStatus::Pending,
            ]);
        }

        // ---- Pelanggan newsletter ----
        foreach (['pembaca1@mail.test', 'pembaca2@mail.test', 'pembaca3@mail.test'] as $email) {
            Subscriber::create(['email' => $email]);
        }

        $this->command->info('Seed selesai: 4 pengguna, 6 kategori, '.count($tagNames).' tag, '.Article::count().' artikel.');
        $this->command->info('Login admin: admin@tikumpost.test / password');
    }
}
