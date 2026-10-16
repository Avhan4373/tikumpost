<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Infographic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InfographicTest extends TestCase
{
    use RefreshDatabase;

    /** PNG 1x1 valid (environment uji tanpa ekstensi GD). */
    private function png(): string
    {
        return base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+M8AAAMBAQAY3Y2wAAAAAElFTkSuQmCC');
    }

    public function test_admin_dapat_membuka_daftar_dan_form_infografis(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)->get(route('admin.infographics.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.infographics.create'))->assertOk();
    }

    public function test_admin_dapat_mengunggah_infografis(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)->post(route('admin.infographics.store'), [
            'is_published' => '1',
            'image' => UploadedFile::fake()->createWithContent('info.png', $this->png()),
        ])->assertRedirect(route('admin.infographics.index'))->assertSessionHasNoErrors();

        $infographic = Infographic::firstOrFail();

        $this->assertTrue($infographic->is_published);
        Storage::disk('public')->assertExists($infographic->image);
    }

    public function test_admin_dapat_memperbarui_tanpa_mengganti_gambar(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $original = 'infographics/lama.jpg';
        Storage::disk('public')->put($original, 'x');
        $infographic = Infographic::factory()->create(['image' => $original]);

        $this->actingAs($admin)->put(route('admin.infographics.update', $infographic), [
            'sort_order' => 5,
        ])->assertRedirect(route('admin.infographics.index'))->assertSessionHasNoErrors();

        $infographic->refresh();

        $this->assertSame(5, $infographic->sort_order);
        $this->assertFalse($infographic->is_published); // checkbox tidak dicentang
        $this->assertSame($original, $infographic->image);
    }

    public function test_admin_dapat_menghapus_infografis_beserta_gambarnya(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $path = 'infographics/hapus.jpg';
        Storage::disk('public')->put($path, 'x');
        $infographic = Infographic::factory()->create(['image' => $path]);

        $this->actingAs($admin)->delete(route('admin.infographics.destroy', $infographic))->assertRedirect();

        $this->assertDatabaseMissing('infographics', ['id' => $infographic->id]);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_sidebar_beranda_menampilkan_infografis_tayang_saja(): void
    {
        Infographic::factory()->create(['image' => 'infographics/tayang.jpg']);
        Infographic::factory()->draft()->create(['image' => 'infographics/draf.jpg']);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('infographics/tayang.jpg')
            ->assertDontSee('infographics/draf.jpg');
    }

    public function test_sidebar_beranda_maksimal_empat_infografis(): void
    {
        Infographic::factory()->count(5)->create();

        $response = $this->get(route('home'))->assertOk();

        $this->assertSame(4, substr_count($response->getContent(), 'data-lightbox-src'));
    }

    public function test_pengguna_biasa_tidak_bisa_mengakses_infografis(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('admin.infographics.index'))->assertForbidden();
    }
}
