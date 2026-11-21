<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeLayoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_beranda_memakai_markup_tvri(): void
    {
        Article::factory()->count(5)->create(['is_headline' => true, 'featured_image' => 'articles/uji.jpg']);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('data-home="tvri"', false)
            ->assertSee('data-hero-lead', false)
            ->assertSee('data-hero-tile', false)
            ->assertSee('data-article-row', false)
            ->assertSee('data-section-heading', false)
            ->assertSee('Berita Terbaru')
            ->assertSee('hero-rise', false)
            ->assertSee('hero-zoom', false);
    }

    public function test_strip_kategori_tampil_dengan_tautan_lihat_semua(): void
    {
        $category = Category::factory()->create(['name' => 'Nasional', 'slug' => 'nasional']);
        Article::factory()->create(['category_id' => $category->id]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('data-section', false)
            ->assertSee('Lihat Semua')
            ->assertSee(route('categories.show', $category), false);
    }

    public function test_detail_berita_pakai_header_biru_dan_sidebar(): void
    {
        $category = Category::factory()->create();
        $article = Article::factory()->create(['category_id' => $category->id]);
        Article::factory()->create(['category_id' => $category->id]);

        $this->get(route('articles.show', $article))
            ->assertOk()
            ->assertSee('site-header-blue', false)
            ->assertSee('data-article-sidebar', false)
            ->assertSee('Artikel Terkait')
            ->assertDontSee('data-ticker', false);
    }

    public function test_header_biru_dan_ticker_breaking_news(): void
    {
        Article::factory()->create(['title' => 'Judul Ticker Uji']);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('site-header-blue', false)
            ->assertSee('data-ticker', false)
            ->assertSee('Judul Ticker Uji')
            ->assertSee('Breaking News');
    }

    public function test_halaman_lain_tetap_memakai_header_biru_tanpa_ticker(): void
    {
        Article::factory()->create();

        $this->get(route('search'))
            ->assertOk()
            ->assertSee('site-header-blue', false)
            ->assertDontSee('data-ticker', false);
    }

    public function test_beranda_tetap_200_tanpa_artikel(): void
    {
        $this->get(route('home'))->assertOk();
    }

    public function test_strip_kategori_hanya_menampilkan_kategori_berisi_artikel(): void
    {
        Category::factory()->create(['name' => 'Kosong', 'slug' => 'kosong']);
        $isi = Category::factory()->create(['name' => 'Kategori Uji', 'slug' => 'kategori-uji']);

        $article = Article::factory()->create(['category_id' => $isi->id]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Kategori Uji')
            ->assertSee($article->title);
    }
}