<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Menu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dapat_membuka_halaman_menu(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $root = Menu::create(['label' => 'Profil', 'url' => '/halaman/tentang']);
        Menu::create(['label' => 'Redaksi', 'url' => '/halaman/kontak', 'parent_id' => $root->id]);

        $this->actingAs($admin)->get(route('admin.menus.index'))->assertOk()->assertSee('Profil')->assertSee('Redaksi');
        $this->actingAs($admin)->get(route('admin.menus.create'))->assertOk();
        $this->actingAs($admin)->get(route('admin.menus.edit', $root))->assertOk();
    }

    public function test_urutan_dan_struktur_menu_tersimpan_setelah_drag_drop(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $a = Menu::create(['label' => 'A', 'url' => '/a', 'sort_order' => 0]);
        $b = Menu::create(['label' => 'B', 'url' => '/b', 'sort_order' => 1]);
        $c = Menu::create(['label' => 'C', 'url' => '/c', 'sort_order' => 2]);

        $this->actingAs($admin)->postJson(route('admin.menus.reorder'), [
            'items' => [
                ['id' => $c->id, 'parent_id' => null, 'sort_order' => 0],
                ['id' => $b->id, 'parent_id' => null, 'sort_order' => 1],
                ['id' => $a->id, 'parent_id' => $c->id, 'sort_order' => 0],
            ],
        ])->assertOk();

        $this->assertDatabaseHas('menus', ['id' => $c->id, 'parent_id' => null, 'sort_order' => 0]);
        $this->assertDatabaseHas('menus', ['id' => $b->id, 'parent_id' => null, 'sort_order' => 1]);
        $this->assertDatabaseHas('menus', ['id' => $a->id, 'parent_id' => $c->id, 'sort_order' => 0]);
    }

    public function test_menu_dibatasi_dua_tingkat_saat_drag_drop(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $a = Menu::create(['label' => 'A', 'url' => '/a']);
        $child = Menu::create(['label' => 'Anak', 'url' => '/anak', 'parent_id' => $a->id]);
        $other = Menu::create(['label' => 'Lain', 'url' => '/lain']);

        // "other" dijadikan anak dari "child" (yang merupakan anak) -> harus dipromosikan ke root.
        $this->actingAs($admin)->postJson(route('admin.menus.reorder'), [
            'items' => [
                ['id' => $a->id, 'parent_id' => null, 'sort_order' => 0],
                ['id' => $child->id, 'parent_id' => $a->id, 'sort_order' => 0],
                ['id' => $other->id, 'parent_id' => $child->id, 'sort_order' => 0],
            ],
        ])->assertOk();

        $this->assertDatabaseHas('menus', ['id' => $other->id, 'parent_id' => null]);
    }

    public function test_header_publik_menampilkan_menu_dari_database(): void
    {
        $root = Menu::create(['label' => 'Profil Kami', 'url' => '/halaman/tentang', 'sort_order' => 0]);
        Menu::create(['label' => 'Hubungi Redaksi', 'url' => '/halaman/kontak', 'parent_id' => $root->id, 'sort_order' => 0]);
        Menu::create(['label' => 'Menu Tersembunyi', 'url' => '/rahasia', 'is_active' => false]);

        $response = $this->get(route('home'))
            ->assertOk()
            ->assertSee('Profil Kami')
            ->assertSee('Hubungi Redaksi')
            ->assertDontSee('Menu Tersembunyi');

        // Submenu wajib dirender di nav desktop dan mobile (bukan hanya salah satu),
        // sekaligus memastikan tidak ada sisa Blade mentah pada output.
        $this->assertSame(2, substr_count($response->getContent(), 'data-menu-dropdown'));
        $response->assertDontSee('@endforeach')->assertDontSee('catActive');
    }

    public function test_fallback_menu_bawaan_tidak_mengeluarkan_blade_mentah(): void
    {
        // Tabel menu kosong -> header memakai menu bawaan (Beranda, Tikum Reels).
        $response = $this->get(route('home'))->assertOk();

        $response->assertSee('Beranda')->assertSee('Tikum Reels');
        $response->assertDontSee('@endforeach')->assertDontSee('catActive');
    }

    public function test_reorder_tidak_bisa_diakses_tanpa_login(): void
    {
        $menu = Menu::create(['label' => 'A', 'url' => '/a']);

        $this->post(route('admin.menus.reorder'), [
            'items' => [['id' => $menu->id, 'parent_id' => null, 'sort_order' => 0]],
        ])->assertRedirect(route('login'));
    }
}
