<?php

namespace Tests\Feature;

use App\Support\ImageVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImgComponentTest extends TestCase
{
    use RefreshDatabase;

    private function makeImage(string $path = 'articles/uji.jpg', int $width = 800, int $height = 600): string
    {
        $full = Storage::disk('public')->path($path);
        @mkdir(dirname($full), 0777, true);
        $image = imagecreatetruecolor($width, $height);
        imagejpeg($image, $full, 85);

        return $path;
    }

    private function render(array $data): string
    {
        return view('components.img', $data)->render();
    }

    public function test_menghasilkan_srcset_sizes_dan_dimensi_bila_varian_ada(): void
    {
        Storage::fake('public');
        $path = $this->makeImage();
        ImageVariant::ensure($path);

        $html = $this->render(['path' => $path, 'alt' => 'Judul', 'sizes' => '100vw']);

        $this->assertStringContainsString('srcset="', $html);
        $this->assertStringContainsString(' 480w', $html);
        $this->assertStringContainsString('sizes="100vw"', $html);
        $this->assertStringContainsString('width="800"', $html);
        $this->assertStringContainsString('height="600"', $html);
        $this->assertStringContainsString('loading="lazy"', $html);
        $this->assertStringContainsString('decoding="async"', $html);
    }

    public function test_tanpa_varian_tetap_merender_img_tanpa_srcset(): void
    {
        Storage::fake('public');
        $path = $this->makeImage('articles/kecil.jpg', 100, 80);

        $html = $this->render(['path' => $path, 'alt' => 'Kecil']);

        $this->assertStringContainsString('<img', $html);
        $this->assertStringNotContainsString('srcset', $html);
    }

    public function test_fetchpriority_dan_eager_hanya_bila_diminta(): void
    {
        Storage::fake('public');
        $path = $this->makeImage();

        $html = $this->render(['path' => $path, 'loading' => 'eager', 'fetchpriority' => 'high']);

        $this->assertStringContainsString('fetchpriority="high"', $html);
        $this->assertStringContainsString('loading="eager"', $html);
    }

    public function test_berkas_hilang_tetap_merender_img_tanpa_error(): void
    {
        Storage::fake('public');

        $html = $this->render(['path' => 'articles/hilang.jpg', 'alt' => 'Hilang']);

        $this->assertStringContainsString('<img', $html);
        $this->assertStringNotContainsString('srcset', $html);
    }

    public function test_komponen_tidak_membuat_varian_saat_render(): void
    {
        Storage::fake('public');
        $path = $this->makeImage();

        $html = $this->render(['path' => $path, 'alt' => 'X']);

        $this->assertStringContainsString('<img', $html);
        $this->assertStringNotContainsString('srcset', $html);
        $this->assertSame([], collect(Storage::disk('public')->allFiles())
            ->filter(fn (string $file) => str_contains($file, '-v1-'))
            ->values()
            ->all());
    }
}
