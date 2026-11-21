<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Infographic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaVariantsCommandTest extends TestCase
{
    use RefreshDatabase;

    private function makeImage(string $path): void
    {
        $full = Storage::disk('public')->path($path);
        @mkdir(dirname($full), 0777, true);
        $image = imagecreatetruecolor(800, 600);
        imagejpeg($image, $full, 85);
    }

    public function test_perintah_membuat_varian_dan_idempoten(): void
    {
        Storage::fake('public');
        $this->makeImage('articles/berita.jpg');
        $this->makeImage('infographics/grafis.jpg');

        Article::factory()->create(['featured_image' => 'articles/berita.jpg']);
        Infographic::factory()->create(['image' => 'infographics/grafis.jpg']);

        $this->artisan('media:variants')->assertSuccessful();
        $afterFirst = count(Storage::disk('public')->allFiles());

        $this->assertTrue(Storage::disk('public')->exists('articles/berita-v1-480.webp'));
        $this->assertTrue(Storage::disk('public')->exists('infographics/grafis-v1-480.webp'));

        $this->artisan('media:variants')->assertSuccessful();

        $this->assertSame($afterFirst, count(Storage::disk('public')->allFiles()));
    }

    public function test_perintah_melewati_baris_tanpa_gambar(): void
    {
        Storage::fake('public');
        Article::factory()->create(['featured_image' => null]);

        $this->artisan('media:variants')->assertSuccessful();
    }
}
