<?php

namespace Tests\Feature;

use App\Enums\ArticleStatus;
use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnimationTest extends TestCase
{
    use RefreshDatabase;

    public function test_beranda_memuat_kerangka_animasi(): void
    {
        Article::factory()->create([
            'status' => ArticleStatus::Published,
            'published_at' => now()->subHour(),
            'featured_image' => 'articles/hero.jpg',
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee("classList.add('js')", false)   // penanda JS aktif
            ->assertSee('nav-progress')                  // progress bar navigasi
            ->assertSee('data-page', false)              // pembungkus transisi halaman
            ->assertSee('hero-rise', false)              // entrance hero
            ->assertSee('hero-zoom', false)
            ->assertSee('data-reveal', false);           // scroll reveal
    }

    public function test_kartu_berita_dengan_gambar_memuat_skeleton(): void
    {
        // Headline mengambil 5 terbaru, jadi artikel bergambar dibuat paling lama
        // agar masuk ke grid "Berita Terbaru" sebagai kartu.
        Article::factory()->count(5)->create([
            'status' => ArticleStatus::Published,
            'published_at' => now()->subHours(2),
        ]);

        Article::factory()->create([
            'status' => ArticleStatus::Published,
            'published_at' => now()->subDay(),
            'featured_image' => 'articles/cover.jpg',
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('media-frame', false)
            ->assertSee('data-fade', false);
    }
}
