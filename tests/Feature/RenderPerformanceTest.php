<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Support\ImageVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RenderPerformanceTest extends TestCase
{
    use RefreshDatabase;

    private function makeImage(string $path): string
    {
        $full = Storage::disk('public')->path($path);
        @mkdir(dirname($full), 0777, true);
        $image = imagecreatetruecolor(1200, 800);
        imagejpeg($image, $full, 85);
        ImageVariant::ensure($path);

        return $path;
    }

    public function test_beranda_merender_srcset_dan_preload_hero(): void
    {
        Storage::fake('public');
        $path = $this->makeImage('articles/hero.jpg');
        Article::factory()->create(['is_headline' => true, 'featured_image' => $path]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('srcset=', false)
            ->assertSee('as="image"', false)
            ->assertSee('fetchpriority="high"', false)
            ->assertSee('imagesrcset=', false);
    }

    public function test_artikel_merender_srcset_pada_gambar_unggulan(): void
    {
        Storage::fake('public');
        $path = $this->makeImage('articles/utama.jpg');
        $article = Article::factory()->create(['featured_image' => $path]);

        $this->get(route('articles.show', $article))
            ->assertOk()
            ->assertSee('srcset=', false)
            ->assertSee('fetchpriority="high"', false);
    }

    public function test_beranda_tanpa_gambar_hero_tetap_ok(): void
    {
        Storage::fake('public');
        Article::factory()->count(3)->create(['is_headline' => true, 'featured_image' => null]);

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('as="image"', false);
    }
}
