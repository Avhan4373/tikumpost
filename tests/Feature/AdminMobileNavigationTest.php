<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminMobileNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_sidebar_admin_bisa_dibuka_di_layar_kecil(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('data-admin-nav-toggle', false)
            ->assertSee('data-admin-nav-backdrop', false)
            ->assertSee('id="admin-sidebar"', false)
            ->assertDontSee('hidden w-64 shrink-0 flex-col bg-slate-900', false);
    }

    public function test_tabel_admin_bisa_di_scroll_horizontal_di_layar_kecil(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)
            ->get(route('admin.articles.index'))
            ->assertOk()
            ->assertSee('overflow-x-auto', false);
    }
}
