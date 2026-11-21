<?php

namespace Tests\Feature;

use App\Enums\ArticleStatus;
use App\Enums\UserRole;
use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ArticleTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dapat_membuka_daftar_dan_form_artikel(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)->get(route('admin.articles.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.articles.create'))->assertOk();
    }

    public function test_form_artikel_memakai_editor_wysiwyg(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)->get(route('admin.articles.create'))
            ->assertOk()
            ->assertSee('data-rich-editor', false)
            ->assertSee('name="body"', false);
    }

    public function test_admin_dapat_membuat_artikel(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $category = Category::factory()->create();
        $tag = Tag::factory()->create();

        $this->actingAs($admin)->post(route('admin.articles.store'), [
            'title' => 'Judul Artikel Baru',
            'category_id' => $category->id,
            'excerpt' => 'Ringkasan singkat',
            'body' => '<p>Isi artikel</p>',
            'status' => 'published',
            'tags' => [$tag->id],
        ])->assertRedirect(route('admin.articles.index'))->assertSessionHasNoErrors();

        $article = Article::where('title', 'Judul Artikel Baru')->firstOrFail();

        $this->assertSame('judul-artikel-baru', $article->slug);
        $this->assertSame(ArticleStatus::Published, $article->status);
        $this->assertNotNull($article->published_at);
        $this->assertSame([$tag->id], $article->tags->pluck('id')->all());
    }

    public function test_admin_dapat_mengubah_artikel_tanpa_mengubah_slug(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $article = Article::factory()->create(['title' => 'Judul Lama', 'slug' => 'judul-lama']);

        $this->actingAs($admin)->put(route('admin.articles.update', $article), [
            'title' => 'Judul Baru Diubah',
            'body' => '<p>Isi baru</p>',
            'status' => 'draft',
        ])->assertRedirect(route('admin.articles.index'))->assertSessionHasNoErrors();

        $article->refresh();

        $this->assertSame('Judul Baru Diubah', $article->title);
        $this->assertSame('judul-lama', $article->slug);
        $this->assertSame(ArticleStatus::Draft, $article->status);
    }

    public function test_admin_dapat_menghapus_artikel(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $article = Article::factory()->create();

        $this->actingAs($admin)->delete(route('admin.articles.destroy', $article))
            ->assertRedirect();

        $this->assertDatabaseMissing('articles', ['id' => $article->id]);
    }

    public function test_penulis_tidak_bisa_mengubah_artikel_penulis_lain(): void
    {
        $author = User::factory()->create(['role' => UserRole::Author]);
        $other = Article::factory()->create();

        $this->actingAs($author)->get(route('admin.articles.edit', $other))->assertForbidden();
    }

    public function test_status_terbit_selalu_menang_walau_tanggal_masa_depan(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)->post(route('admin.articles.store'), [
            'title' => 'Terbit Menang',
            'body' => '<p>Isi</p>',
            'status' => 'published',
            'published_at' => now()->addDays(3)->format('Y-m-d\TH:i'),
        ])->assertRedirect()->assertSessionHasNoErrors();

        $article = Article::where('title', 'Terbit Menang')->firstOrFail();

        $this->assertSame(ArticleStatus::Published, $article->status);
        $this->assertTrue($article->published_at->lessThanOrEqualTo(now()));
    }

    public function test_status_terjadwal_menyimpan_tanggal_jadwal(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $schedule = now()->addDays(3);

        $this->actingAs($admin)->post(route('admin.articles.store'), [
            'title' => 'Artikel Terjadwal',
            'body' => '<p>Isi</p>',
            'status' => 'scheduled',
            'published_at' => $schedule->format('Y-m-d\TH:i'),
        ])->assertRedirect()->assertSessionHasNoErrors();

        $article = Article::where('title', 'Artikel Terjadwal')->firstOrFail();

        $this->assertSame(ArticleStatus::Scheduled, $article->status);
        $this->assertTrue($article->published_at->isFuture());
    }

    public function test_artikel_terbit_dengan_tanggal_masa_lalu_tetap_terbit(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $article = Article::factory()->create([
            'status' => ArticleStatus::Published,
            'published_at' => now()->subDay(),
        ]);

        // Regresi: input datetime-local memakai pemisah 'T' sehingga perbandingan
        // string membuat artikel terbit ikut berubah jadi "Terjadwal".
        $this->actingAs($admin)->put(route('admin.articles.update', $article), [
            'title' => 'Tetap Terbit',
            'body' => '<p>Isi</p>',
            'status' => 'published',
            'published_at' => now()->subDay()->format('Y-m-d\TH:i'),
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertSame(ArticleStatus::Published, $article->fresh()->status);
    }

    public function test_artikel_baru_terbit_hari_ini_tetap_terbit(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)->post(route('admin.articles.store'), [
            'title' => 'Terbit Hari Ini',
            'body' => '<p>Isi</p>',
            'status' => 'published',
            'published_at' => now()->format('Y-m-d\TH:i'),
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertSame(ArticleStatus::Published, Article::where('title', 'Terbit Hari Ini')->firstOrFail()->status);
    }

    public function test_judul_tanpa_huruf_tetap_mendapat_slug_dan_bisa_diedit(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)->post(route('admin.articles.store'), [
            'title' => '!!! ???',
            'body' => '<p>Isi</p>',
            'status' => 'draft',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $article = Article::latest('id')->firstOrFail();

        $this->assertNotSame('', $article->slug);
        $this->actingAs($admin)->get(route('admin.articles.edit', $article))->assertOk();
    }

    public function test_admin_dapat_mengunggah_gambar_utama(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+M8AAAMBAQAY3Y2wAAAAAElFTkSuQmCC');

        $this->actingAs($admin)->post(route('admin.articles.store'), [
            'title' => 'Artikel Bergambar',
            'body' => '<p>Isi</p>',
            'status' => 'draft',
            'featured_image' => UploadedFile::fake()->createWithContent('cover.png', $png),
        ])->assertRedirect()->assertSessionHasNoErrors();

        $article = Article::where('title', 'Artikel Bergambar')->firstOrFail();

        $this->assertNotNull($article->featured_image);
        Storage::disk('public')->assertExists($article->featured_image);
    }

    public function test_unggah_gambar_unggulan_membuat_varian_webp(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $image = imagecreatetruecolor(800, 600);
        ob_start();
        imagepng($image);
        $png = ob_get_clean();

        $this->actingAs($admin)->post(route('admin.articles.store'), [
            'title' => 'Artikel Varian',
            'body' => '<p>Isi</p>',
            'status' => 'draft',
            'featured_image' => UploadedFile::fake()->createWithContent('cover.png', $png),
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertTrue(
            collect(Storage::disk('public')->files('articles'))->contains(fn ($path) => str_contains($path, '-v1-480.webp')),
        );
    }

    public function test_pengguna_biasa_tidak_bisa_mengakses_artikel(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('admin.articles.index'))->assertForbidden();
    }

    public function test_kunjungan_pertama_menambah_hitungan_dibaca(): void
    {
        $article = Article::factory()->create([
            'status' => ArticleStatus::Published,
            'published_at' => now()->subHour(),
            'views' => 10,
        ]);

        $this->get(route('articles.show', $article))->assertOk();

        $this->assertSame(11, $article->fresh()->views);
    }

    public function test_pengunjung_yang_sama_tidak_menambah_hitungan_lagi(): void
    {
        $article = Article::factory()->create([
            'status' => ArticleStatus::Published,
            'published_at' => now()->subHour(),
            'views' => 10,
        ]);

        // Sesi ini sudah pernah membaca artikel tersebut.
        $this->withSession(['viewed_articles' => [$article->id]])
            ->get(route('articles.show', $article))
            ->assertOk();

        $this->assertSame(10, $article->fresh()->views);
    }

    public function test_pengunjung_lain_tetap_menambah_hitungan(): void
    {
        $article = Article::factory()->create([
            'status' => ArticleStatus::Published,
            'published_at' => now()->subHour(),
            'views' => 10,
        ]);

        // Sesi ini baru membaca artikel lain, bukan artikel ini.
        $this->withSession(['viewed_articles' => [999]])
            ->get(route('articles.show', $article))
            ->assertOk();

        $this->assertSame(11, $article->fresh()->views);
    }

    public function test_daftar_artikel_admin_bisa_diurutkan_berdasarkan_dibaca(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        Article::factory()->create(['title' => 'Artikel Paling Sepi', 'views' => 5]);
        Article::factory()->create(['title' => 'Artikel Paling Ramai', 'views' => 500]);

        $response = $this->actingAs($admin)
            ->get(route('admin.articles.index', ['sort' => 'views', 'dir' => 'desc']))
            ->assertOk()
            ->assertSee('Dibaca');

        $html = $response->getContent();

        $this->assertLessThan(
            strpos($html, 'Artikel Paling Sepi'),
            strpos($html, 'Artikel Paling Ramai'),
            'Artikel dengan views terbanyak harus muncul lebih dulu.'
        );
    }
}
