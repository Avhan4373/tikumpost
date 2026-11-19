@extends('layouts.admin')

@section('title', 'Pengguna')
@section('heading', 'Pengguna')

@section('content')
    @php
        $isEditError = $errors->any() && old('form') === 'edit';
        $isCreateError = $errors->any() && old('form') !== 'edit';
    @endphp

    @if (! auth()->user()->isAdmin())
        <div class="mb-5 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            Hanya Administrator yang dapat menambah atau mengubah pengguna.
        </div>
    @endif

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-4">
            <div>
                <h2 class="font-bold text-slate-900">Daftar Pengguna</h2>
                <p class="text-xs text-slate-500">{{ $users->total() }} pengguna terdaftar</p>
            </div>
            @if (auth()->user()->isAdmin())
                <button type="button" data-modal-open="user-create"
                        class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-700">
                    Tambah Pengguna
                </button>
            @endif
        </div>
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr><th class="px-5 py-3">Nama</th><th class="px-5 py-3">Email</th><th class="px-5 py-3">Peran</th><th class="px-5 py-3">Artikel</th><th class="px-5 py-3 text-right">Aksi</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($users as $user)
                        <tr>
                            <td class="px-5 py-3 font-medium text-slate-800">
                                {{ $user->name }}
                                @if ($user->id === auth()->id())
                                    <span class="ml-1 rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-slate-500">Anda</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-slate-500">{{ $user->email }}</td>
                            <td class="px-5 py-3"><span class="rounded-full bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-600">{{ $user->role?->label() }}</span></td>
                            <td class="px-5 py-3 text-slate-500">{{ $user->articles_count }}</td>
                            <td class="whitespace-nowrap px-5 py-3">
                                @if (auth()->user()->isAdmin())
                                    <div class="flex items-center justify-end gap-2">
                                        <button type="button"
                                                data-modal-open="user-edit"
                                                data-modal-action="{{ route('admin.users.update', $user) }}"
                                                data-modal-data="{{ json_encode([
                                                    'user_id' => $user->id,
                                                    'name' => $user->name,
                                                    'email' => $user->email,
                                                    'role' => $user->role?->value,
                                                ]) }}"
                                                class="rounded border border-slate-300 px-2 py-1 text-xs font-medium text-slate-600 transition hover:bg-slate-100">
                                            Edit
                                        </button>

                                        @if ($user->id !== auth()->id())
                                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Hapus pengguna ini?')">
                                                @csrf @method('DELETE')
                                                <button class="rounded border border-red-300 px-2 py-1 text-xs text-red-600 hover:bg-red-50">Hapus</button>
                                            </form>
                                        @endif
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
            <div class="border-t border-slate-100 px-5 py-3">{{ $users->links() }}</div>
        </div>

        @if (auth()->user()->isAdmin())
            <x-modal id="user-create" title="Tambah Pengguna" description="Buat akun baru untuk admin, editor, penulis, atau pembaca." :auto-open="$isCreateError">
                <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-3">
                    @csrf
                    <input type="hidden" name="form" value="create">
                    <div>
                        <label for="create-name" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Nama</label>
                        <input id="create-name" type="text" name="name" required value="{{ $isCreateError ? old('name') : '' }}"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500">
                        @if ($isCreateError)
                            @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        @endif
                    </div>
                    <div>
                        <label for="create-email" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Email</label>
                        <input id="create-email" type="email" name="email" required value="{{ $isCreateError ? old('email') : '' }}"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500">
                        @if ($isCreateError)
                            @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        @endif
                    </div>
                    <div>
                        <label for="create-role" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Peran</label>
                        <select id="create-role" name="role" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500">
                            @foreach (\App\Enums\UserRole::cases() as $role)
                                <option value="{{ $role->value }}" @selected($isCreateError && old('role') === $role->value)>{{ $role->label() }}</option>
                            @endforeach
                        </select>
                        @if ($isCreateError)
                            @error('role')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        @endif
                    </div>
                    <div>
                        <label for="create-password" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Kata Sandi</label>
                        <input id="create-password" type="password" name="password" required autocomplete="new-password"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500">
                        @if ($isCreateError)
                            @error('password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        @endif
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" data-modal-close class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100">Batal</button>
                        <button class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">Tambah Pengguna</button>
                    </div>
                </form>
            </x-modal>

            <x-modal id="user-edit" title="Edit Pengguna" description="Perbarui data pengguna. Kosongkan kata sandi jika tidak ingin mengubahnya." :auto-open="$isEditError">
                <form method="POST"
                      action="{{ $isEditError && old('user_id') ? route('admin.users.update', old('user_id')) : '#' }}"
                      class="space-y-3">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="form" value="edit">
                    <input type="hidden" name="user_id" value="{{ $isEditError ? old('user_id') : '' }}">

                    <div>
                        <label for="edit-name" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Nama</label>
                        <input id="edit-name" type="text" name="name" required
                               value="{{ $isEditError ? old('name') : '' }}"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500">
                        @if ($isEditError)
                            @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        @endif
                    </div>

                    <div>
                        <label for="edit-email" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Email</label>
                        <input id="edit-email" type="email" name="email" required
                               value="{{ $isEditError ? old('email') : '' }}"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500">
                        @if ($isEditError)
                            @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        @endif
                    </div>

                    <div>
                        <label for="edit-role" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Peran</label>
                        <select id="edit-role" name="role" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500">
                            @foreach (\App\Enums\UserRole::cases() as $role)
                                <option value="{{ $role->value }}" @selected($isEditError && old('role') === $role->value)>{{ $role->label() }}</option>
                            @endforeach
                        </select>
                        @if ($isEditError)
                            @error('role')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        @endif
                    </div>

                    <div>
                        <label for="edit-password" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Kata Sandi Baru</label>
                        <input id="edit-password" type="password" name="password" placeholder="Kosongkan jika tidak diubah" autocomplete="new-password"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500">
                        @if ($isEditError)
                            @error('password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        @endif
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" data-modal-close class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100">Batal</button>
                        <button class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">Simpan Perubahan</button>
                    </div>
                </form>
            </x-modal>
        @endif
@endsection
