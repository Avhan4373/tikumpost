<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Category;
use App\Models\Setting;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_sitemap_memuat_artikel_kategori_tag_dan_halaman_statis(): void
    {
        $article = Article::factory()->create();
        $category = Category::factory()->create();
        $tag = Tag::factory()->create();

        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $this->assertStringContainsString('xml', (string) $response->headers->get('Content-Type'));

        $body = $response->getContent();

        $this->assertStringContainsString(route('articles.show', $article), $body);
        $this->assertStringContainsString(route('categories.show', $category), $body);
        $this->assertStringContainsString(route('tags.show', $tag), $body);
        $this->assertStringContainsString(route('pages.show', 'tentang'), $body);
    }

    public function test_sitemap_tidak_memuat_artikel_draft(): void
    {
        $draft = Article::factory()->draft()->create();

        $this->assertStringNotContainsString(
            route('articles.show', $draft),
            $this->get('/sitemap.xml')->getContent()
        );
    }

    public function test_sitemap_dimulai_dengan_deklarasi_xml(): void
    {
        $body = $this->get('/sitemap.xml')->assertOk()->getContent();

        $this->assertStringStartsWith('<?xml', ltrim($body));
        $this->assertNotFalse(simplexml_load_string($body), 'Dokumen sitemap bukan XML yang valid.');
    }

    public function test_halaman_artikel_punya_canonical_diri_sendiri(): void
    {
        $article = Article::factory()->create();

        $this->get(route('articles.show', $article))->assertOk()
            ->assertSee('<link rel="canonical" href="'.route('articles.show', $article).'">', false);
    }

    public function test_halaman_kedua_punya_canonical_ke_halaman_kedua(): void
    {
        $category = Category::factory()->create();
        Article::factory()->count(12)->create(['category_id' => $category->id]);

        $this->get(route('categories.show', ['category' => $category, 'page' => 2]))->assertOk()
            ->assertSee('<link rel="canonical" href="'.route('categories.show', $category).'?page=2">', false);
    }

    public function test_halaman_pencarian_noindex(): void
    {
        $this->get(route('search', ['q' => 'konawe']))->assertOk()
            ->assertSee('<meta name="robots" content="noindex,follow">', false);
    }

    public function test_halaman_artikel_index_follow(): void
    {
        $article = Article::factory()->create();

        $this->get(route('articles.show', $article))->assertOk()
            ->assertSee('<meta name="robots" content="index,follow">', false);
    }

    public function test_og_image_artikel_memakai_gambar_unggulan(): void
    {
        $article = Article::factory()->create(['featured_image' => 'artikel/foto.jpg']);

        $this->get(route('articles.show', $article))->assertOk()
            ->assertSee('<meta property="og:image" content="'.url(Storage::disk('public')->url('artikel/foto.jpg')).'">', false);
    }

    public function test_og_image_bawaan_memakai_favicon_situs_bila_tak_ada_gambar(): void
    {
        Setting::set('site_favicon', 'settings/favicon.jpg');
        $article = Article::factory()->create(['featured_image' => null]);

        $this->get(route('articles.show', $article))->assertOk()
            ->assertSee('<meta property="og:image" content="'.url(Storage::disk('public')->url('settings/favicon.jpg')).'">', false);
    }

    public function test_halaman_artikel_memakai_og_type_article(): void
    {
        $article = Article::factory()->create();

        $this->get(route('articles.show', $article))->assertOk()
            ->assertSee('<meta property="og:type" content="article">', false);
    }

    public function test_judul_beranda_memuat_nama_situs_dan_tagline(): void
    {
        $html = $this->get(route('home'))->assertOk()->getContent();

        $this->assertStringContainsString(
            '<title>TikumPost — Portal berita terkini, akurat, dan berimbang</title>',
            $html
        );
        $this->assertStringNotContainsString('Beranda &middot;', $html);
    }

    public function test_og_title_beranda_memuat_tagline(): void
    {
        $this->get(route('home'))->assertOk()
            ->assertSee('<meta property="og:title" content="TikumPost — Portal berita terkini, akurat, dan berimbang">', false);
    }

    public function test_judul_halaman_artikel_tetap_memakai_akhiran_nama_situs(): void
    {
        $article = Article::factory()->create();

        $html = $this->get(route('articles.show', $article))->assertOk()->getContent();

        $this->assertStringContainsString('&middot; TikumPost</title>', $html);
    }

    public function test_beranda_memuat_structured_data_organisasi_dan_website(): void
    {
        $html = $this->get(route('home'))->assertOk()->getContent();

        $this->assertStringContainsString('"@type":"Organization"', $html);
        $this->assertStringContainsString('"@type":"WebSite"', $html);
    }

    public function test_halaman_artikel_tidak_memuat_organisasi_bawaan(): void
    {
        $article = Article::factory()->create();

        $html = $this->get(route('articles.show', $article))->assertOk()->getContent();

        // Organisasi hanya disuntik di beranda; halaman lain punya schema sendiri.
        $this->assertStringNotContainsString('"@type":"WebSite"', $html);
    }

    public function test_halaman_artikel_memuat_newsarticle_dan_breadcrumb(): void
    {
        $article = Article::factory()->create(['featured_image' => 'artikel/foto.jpg']);

        $html = $this->get(route('articles.show', $article))->assertOk()->getContent();

        $this->assertStringContainsString('"@type":"NewsArticle"', $html);
        $this->assertStringContainsString('"@type":"BreadcrumbList"', $html);
        $this->assertStringContainsString('"datePublished"', $html);
    }

    public function test_newsarticle_tetap_valid_untuk_judul_bertanda_kutip(): void
    {
        $article = Article::factory()->create(['title' => 'Diskusi "Tol Laut" & Kuota Sangihe']);

        $html = $this->get(route('articles.show', $article))->assertOk()->getContent();

        $this->assertStringContainsString('"@type":"NewsArticle"', $html);
        $this->assertStringNotContainsString('&quot;@type&quot;', $html);

        // JSON harus tetap bisa di-decode.
        preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $html, $matches);
        $this->assertNotEmpty($matches[1]);

        foreach ($matches[1] as $json) {
            $this->assertIsArray(json_decode($json, true), 'JSON-LD tidak valid: '.$json);
        }
    }

    public function test_newsarticle_aman_dari_breakout_script(): void
    {
        // Judul dikendalikan penulis; jangan sampai bisa menutup tag <script>.
        $article = Article::factory()->create(['title' => 'Buntut </script><script>alert(1)</script> Kasus']);

        $html = $this->get(route('articles.show', $article))->assertOk()->getContent();

        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);

        preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $html, $matches);
        $this->assertNotEmpty($matches[1]);

        foreach ($matches[1] as $json) {
            $this->assertIsArray(json_decode($json, true), 'JSON-LD rusak/terpotong: '.$json);
        }
    }

    public function test_sitemap_tidak_tersedia_saat_mode_perawatan(): void
    {
        Setting::set('maintenance_mode', '1');

        $this->get('/sitemap.xml')->assertStatus(503);
    }

    public function test_meta_verifikasi_google_muncul_bila_setelan_diisi(): void
    {
        Setting::set('google_site_verification', 'token-uji-123');

        $this->get(route('home'))->assertOk()
            ->assertSee('<meta name="google-site-verification" content="token-uji-123">', false);
    }

    public function test_meta_verifikasi_google_tidak_muncul_bila_kosong(): void
    {
        $this->get(route('home'))->assertOk()
            ->assertDontSee('google-site-verification', false);
    }
}
