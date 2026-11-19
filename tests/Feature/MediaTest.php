<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Media;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaTest extends TestCase
{
    use RefreshDatabase;

    public function test_editor_wysiwyg_dapat_mengunggah_gambar_lewat_json(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)
            ->postJson(route('admin.media.store'), [
                'file' => UploadedFile::fake()->image('foto-artikel.jpg'),
                'alt_text' => 'Foto artikel',
            ])
            ->assertCreated()
            ->assertJsonStructure(['id', 'url', 'alt_text']);

        $media = Media::firstOrFail();

        $this->assertSame('Foto artikel', $media->alt_text);
        Storage::disk('public')->assertExists($media->path);
    }
}
