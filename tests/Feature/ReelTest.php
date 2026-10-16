<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Reel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReelTest extends TestCase
{
    use RefreshDatabase;

    public function test_halaman_tikum_reals_menampilkan_reel_tayang_saja(): void
    {
        Reel::create([
            'title' => 'Reel Tayang',
            'url' => 'https://www.instagram.com/reel/AAA111aaa/',
            'is_published' => true,
        ]);
        Reel::create([
            'title' => 'Reel Draf',
            'url' => 'https://www.instagram.com/reel/BBB222bbb/',
            'is_published' => false,
        ]);

        $this->get(route('reels.index'))
            ->assertOk()
            ->assertSee('Reel Tayang')
            ->assertDontSee('Reel Draf');
    }

    public function test_admin_dapat_menambah_reel(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)
            ->post(route('admin.reels.store'), [
                'title' => 'Reel Baru',
                'url' => 'https://www.instagram.com/reel/CCC333ccc/',
                'is_published' => '1',
            ])
            ->assertRedirect(route('admin.reels.index'));

        $this->assertDatabaseHas('reels', ['title' => 'Reel Baru', 'is_published' => true]);
    }

    public function test_url_instagram_tidak_valid_ditolak(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)
            ->post(route('admin.reels.store'), [
                'title' => 'Reel Salah',
                'url' => 'https://example.com/reel/xyz',
            ])
            ->assertSessionHasErrors('url');

        $this->assertDatabaseCount('reels', 0);
    }

    public function test_admin_dapat_membuka_daftar_dan_form_reel(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $reel = Reel::create([
            'title' => 'Reel Contoh',
            'url' => 'https://www.instagram.com/reel/DDD444ddd/',
            'is_published' => true,
        ]);

        $this->actingAs($admin)->get(route('admin.reels.index'))->assertOk()->assertSee('Reel Contoh');
        $this->actingAs($admin)->get(route('admin.reels.create'))->assertOk();
        $this->actingAs($admin)->get(route('admin.reels.edit', $reel))->assertOk()->assertSee('Reel Contoh');
    }
}
