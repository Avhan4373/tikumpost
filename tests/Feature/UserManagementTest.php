<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dapat_melihat_tombol_edit_pengguna(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $target = User::factory()->create(['role' => UserRole::Author]);

        $this->actingAs($admin)->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('data-modal-open="user-edit"', false)
            ->assertSee(route('admin.users.update', $target))
            ->assertSee('Edit Pengguna');
    }

    public function test_admin_dapat_memperbarui_data_pengguna_tanpa_mengubah_kata_sandi(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $target = User::factory()->create([
            'role' => UserRole::Author,
            'password' => Hash::make('rahasia-lama'),
        ]);

        $this->actingAs($admin)->put(route('admin.users.update', $target), [
            'name' => 'Nama Baru',
            'email' => 'baru@tikumpost.test',
            'role' => UserRole::Editor->value,
            'password' => '',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $target->refresh();

        $this->assertSame('Nama Baru', $target->name);
        $this->assertSame('baru@tikumpost.test', $target->email);
        $this->assertSame(UserRole::Editor, $target->role);
        $this->assertTrue(Hash::check('rahasia-lama', $target->password));
    }

    public function test_admin_dapat_mengganti_kata_sandi_pengguna(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $target = User::factory()->create(['role' => UserRole::Reader]);

        $this->actingAs($admin)->put(route('admin.users.update', $target), [
            'name' => $target->name,
            'email' => $target->email,
            'role' => UserRole::Reader->value,
            'password' => 'kata-sandi-baru',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('kata-sandi-baru', $target->refresh()->password));
    }

    public function test_selain_admin_tidak_dapat_mengubah_pengguna(): void
    {
        $editor = User::factory()->create(['role' => UserRole::Editor]);
        $target = User::factory()->create(['role' => UserRole::Author]);

        $this->actingAs($editor)->put(route('admin.users.update', $target), [
            'name' => 'Diubah Editor',
            'email' => 'editor-ubah@tikumpost.test',
            'role' => UserRole::Admin->value,
        ])->assertForbidden();

        $this->assertNotSame('Diubah Editor', $target->refresh()->name);
    }

    public function test_selain_admin_tidak_dapat_menambah_atau_menghapus_pengguna(): void
    {
        $editor = User::factory()->create(['role' => UserRole::Editor]);
        $target = User::factory()->create(['role' => UserRole::Reader]);

        $this->actingAs($editor)->post(route('admin.users.store'), [
            'name' => 'Penyusup',
            'email' => 'penyusup@tikumpost.test',
            'role' => UserRole::Admin->value,
            'password' => 'password-baru',
        ])->assertForbidden();

        $this->actingAs($editor)->delete(route('admin.users.destroy', $target))->assertForbidden();

        $this->assertDatabaseMissing('users', ['email' => 'penyusup@tikumpost.test']);
        $this->assertDatabaseHas('users', ['id' => $target->id]);
    }

    public function test_admin_tidak_dapat_mengubah_peran_akun_sendiri(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)->put(route('admin.users.update', $admin), [
            'name' => $admin->name,
            'email' => $admin->email,
            'role' => UserRole::Editor->value,
        ])->assertSessionHasErrors('role');

        $this->assertSame(UserRole::Admin, $admin->refresh()->role);
    }

    public function test_admin_tidak_dapat_menghapus_akun_sendiri(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)->delete(route('admin.users.destroy', $admin))->assertForbidden();

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }
}
