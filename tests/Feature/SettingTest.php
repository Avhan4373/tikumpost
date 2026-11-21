<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Article;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dapat_membuka_halaman_pengaturan(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)->get(route('admin.settings.edit'))
            ->assertOk()
            ->assertSee('Identitas & Branding')
            ->assertSee('Media Sosial')
            ->assertSee('Pengaturan Situs');
    }

    public function test_pengguna_biasa_tidak_bisa_mengakses_pengaturan(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('admin.settings.edit'))->assertForbidden();
        $this->actingAs($user)->put(route('admin.settings.update'), [])->assertForbidden();
    }

    public function test_admin_dapat_menyimpan_pengaturan(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)->put(route('admin.settings.update'), [
            'site_name' => 'Kabar Baru',
            'site_tagline' => 'Tagline baru',
            'site_description' => 'Deskripsi baru',
            'meta_description' => 'Meta baru',
            'copyright_text' => 'Hak cipta baru.',
            'posts_per_page' => 12,
            'comments_enabled' => '1',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertDatabaseHas('settings', ['key' => 'site_name', 'value' => 'Kabar Baru']);
        $this->assertDatabaseHas('settings', ['key' => 'posts_per_page', 'value' => '12']);
        $this->assertDatabaseHas('settings', ['key' => 'comments_enabled', 'value' => '1']);
        // Checkbox yang tidak dicentang tetap tersimpan sebagai nonaktif.
        $this->assertDatabaseHas('settings', ['key' => 'maintenance_mode', 'value' => '0']);
    }

    public function test_admin_dapat_mengunggah_logo(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+M8AAAMBAQAY3Y2wAAAAAElFTkSuQmCC');

        $this->actingAs($admin)->put(route('admin.settings.update'), [
            'site_logo' => UploadedFile::fake()->createWithContent('logo.png', $png),
        ])->assertRedirect()->assertSessionHasNoErrors();

        $path = Setting::get('site_logo');

        $this->assertNotNull($path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_header_footer_dan_meta_publik_memakai_pengaturan(): void
    {
        Setting::setMany([
            'site_name' => 'Nama Kustom',
            'footer_text' => 'Deskripsi footer kustom',
            'copyright_text' => 'Hak cipta kustom.',
            'social_instagram' => 'https://instagram.com/kustom',
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Nama Kustom')
            ->assertSee('Deskripsi footer kustom')
            ->assertSee('Hak cipta kustom.')
            ->assertSee('https://instagram.com/kustom');
    }

    public function test_mode_perawatan_menutup_situs_untuk_publik_tetapi_bukan_staf(): void
    {
        Setting::set('maintenance_mode', '1');

        $this->get(route('home'))
            ->assertStatus(503)
            ->assertSee('sedang dalam perawatan', false);

        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)->get(route('home'))->assertOk();
    }

    public function test_komentar_dapat_dinonaktifkan_dari_pengaturan(): void
    {
        $article = Article::factory()->create();

        $this->get(route('articles.show', $article))->assertOk()->assertSee('untuk ikut berkomentar');

        Setting::set('comments_enabled', '0');

        $this->get(route('articles.show', $article))->assertOk()->assertDontSee('untuk ikut berkomentar');
    }

    public function test_tema_default_dapat_diatur_dari_pengaturan(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->get(route('home'))->assertSee("stored || 'light'", false);

        $this->actingAs($admin)->put(route('admin.theme.update'), [
            'default_theme' => 'dark',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertDatabaseHas('settings', ['key' => 'default_theme', 'value' => 'dark']);
        $this->get(route('home'))->assertSee("stored || 'dark'", false);
    }

    public function test_tema_default_menolak_nilai_tidak_dikenal(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)->put(route('admin.theme.update'), [
            'default_theme' => 'neon',
        ])->assertSessionHasErrors('default_theme');
    }
}
