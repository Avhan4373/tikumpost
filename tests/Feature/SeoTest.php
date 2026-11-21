<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
