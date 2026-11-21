<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Article;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThemeTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dapat_membuka_halaman_tema(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)->get(route('admin.theme.edit'))
            ->assertOk()
            ->assertSee('Layout Situs')
            ->assertSee('Tema Warna Default');
    }

    public function test_pengguna_biasa_tidak_bisa_mengakses_tema(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('admin.theme.edit'))->assertForbidden();
        $this->actingAs($user)->put(route('admin.theme.update'), [])->assertForbidden();
    }

    public function test_admin_dapat_menyimpan_tema(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)->put(route('admin.theme.update'), [
            'layout_preset' => 'klasik',
            'default_theme' => 'dark',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertDatabaseHas('settings', ['key' => 'layout_preset', 'value' => 'klasik']);
        $this->assertDatabaseHas('settings', ['key' => 'default_theme', 'value' => 'dark']);
    }

    public function test_layout_menolak_nilai_tidak_dikenal(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)->put(route('admin.theme.update'), [
            'layout_preset' => 'neon',
        ])->assertSessionHasErrors('layout_preset');
    }

    public function test_beranda_default_memakai_layout_tvri(): void
    {
        $this->get(route('home'))->assertOk()
            ->assertSee('data-home="tvri"', false)
            ->assertSee('site-header-blue', false);
    }

    public function test_beranda_memakai_layout_klasik_bila_dipilih(): void
    {
        Article::factory()->create();
        Setting::set('layout_preset', 'klasik');

        $this->get(route('home'))->assertOk()
            ->assertDontSee('data-home="tvri"', false)
            ->assertDontSee('site-header-blue', false)
            ->assertSee('lg:grid-cols-5', false);
    }

    public function test_pengaturan_tidak_lagi_menampilkan_field_tema(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)->get(route('admin.settings.edit'))
            ->assertOk()
            ->assertDontSee('Layout Situs');
    }
}
