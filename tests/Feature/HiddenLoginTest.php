<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HiddenLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_halaman_admin_tidak_terlihat_bagi_tamu(): void
    {
        $this->get('/admin')->assertNotFound();
    }

    public function test_path_login_lama_sudah_tidak_ada(): void
    {
        $this->get('/masuk')->assertNotFound();
    }

    public function test_login_hanya_bisa_diakses_lewat_path_rahasia(): void
    {
        $this->get(route('login'))->assertOk();

        $this->assertSame(
            '/'.config('admin.login_path'),
            parse_url(route('login'), PHP_URL_PATH),
        );
    }

    public function test_halaman_login_tidak_memuat_branding_lama(): void
    {
        $response = $this->get(route('login'));

        $response->assertDontSee('Kabar<span', false);
        $response->assertSee('TikumPost');
    }

    public function test_beranda_tidak_menampilkan_tautan_masuk(): void
    {
        $this->get('/')->assertOk()->assertDontSee('/'.config('admin.login_path'));
    }

    public function test_staf_diarahkan_ke_dashboard_setelah_login(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
            'password' => 'rahasia-kuat',
        ]);

        $this->post(route('login'), [
            'email' => $admin->email,
            'password' => 'rahasia-kuat',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin);
    }
}
