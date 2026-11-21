<?php

namespace Tests\Unit;

use App\Support\ImageVariant;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageVariantTest extends TestCase
{
    private function makeImage(string $path, int $width, int $height): void
    {
        $full = Storage::disk('public')->path($path);
        @mkdir(dirname($full), 0777, true);
        $image = imagecreatetruecolor($width, $height);
        imagefill($image, 0, 0, imagecolorallocate($image, 200, 30, 30));
        imagejpeg($image, $full, 85);
    }

    public function test_srcset_kosong_untuk_berkas_bukan_gambar(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('media/dokumen.pdf', 'bukan gambar');

        $this->assertSame([], ImageVariant::ensure('media/dokumen.pdf'));
        $this->assertNull(ImageVariant::intrinsic('media/dokumen.pdf'));
        $this->assertSame('', ImageVariant::srcset([]));
    }

    public function test_membuat_varian_webp_tanpa_upscale(): void
    {
        Storage::fake('public');
        $this->makeImage('articles/uji.jpg', 400, 300);

        $variants = ImageVariant::ensure('articles/uji.jpg');

        // 400 px: varian 160 & 320 dibuat; 480/960/1440 dilewati (tak boleh upscale).
        $this->assertSame([160, 320], array_column($variants, 'width'));
        $this->assertSame(240, $variants[1]['height']);
        $this->assertTrue(Storage::disk('public')->exists('articles/uji-v1-160.webp'));
        $this->assertTrue(Storage::disk('public')->exists('articles/uji-v1-320.webp'));
        $this->assertStringContainsString(' 160w', ImageVariant::srcset($variants));
    }

    public function test_perintah_tidak_mengupscale_gambar_kecil(): void
    {
        Storage::fake('public');
        $this->makeImage('articles/mungil.jpg', 200, 150);

        $variants = ImageVariant::ensure('articles/mungil.jpg');

        $this->assertSame([160], array_column($variants, 'width'));
    }

    public function test_intrinsic_mengembalikan_dimensi_asli(): void
    {
        Storage::fake('public');
        $this->makeImage('articles/asli.png', 640, 480);

        $this->assertSame([640, 480], ImageVariant::intrinsic('articles/asli.png'));
    }

    public function test_variant_path_memisahkan_ekstensi_dan_lebar(): void
    {
        $this->assertSame('articles/foto-v1-480.webp', ImageVariant::variantPath('articles/foto.jpg', 480));
    }
}
