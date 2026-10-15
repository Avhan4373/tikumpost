@extends('layouts.admin')

@section('title', 'Pengguna')
@section('heading', 'Pengguna')

@section('content')
    @if (! auth()->user()->isAdmin())
        <div class="mb-5 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            Hanya Administrator yang dapat menambah atau mengubah pengguna.
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-[1fr_20rem]">
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr><th class="px-5 py-3">Nama</th><th class="px-5 py-3">Email</th><th class="px-5 py-3">Peran</th><th class="px-5 py-3">Artikel</th><th class="px-5 py-3 text-right">Aksi</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($users as $user)
                        <tr>
                            <td class="px-5 py-3 font-medium text-slate-800">{{ $user->name }}</td>
                            <td class="px-5 py-3 text-slate-500">{{ $user->email }}</td>
                            <td class="px-5 py-3"><span class="rounded-full bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-600">{{ $user->role?->label() }}</span></td>
                            <td class="px-5 py-3 text-slate-500">{{ $user->articles_count }}</td>
                            <td class="px-5 py-3">
                                @if (auth()->user()->isAdmin() && $user->id !== auth()->id())
                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Hapus pengguna ini?')" class="flex justify-end">
                                        @csrf @method('DELETE')
                                        <button class="rounded border border-red-300 px-2 py-1 text-xs text-red-600 hover:bg-red-50">Hapus</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="border-t border-slate-100 px-5 py-3">{{ $users->links() }}</div>
        </div>

        @if (auth()->user()->isAdmin())
            <div class="rounded-xl border border-slate-200 bg-white p-5">
                <h2 class="mb-4 font-bold text-slate-900">Pengguna Baru</h2>
                <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-3">
                    @csrf
                    <input type="text" name="name" required placeholder="Nama" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <input type="email" name="email" required placeholder="Email" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <select name="role" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        @foreach (\App\Enums\UserRole::cases() as $role)
                            <option value="{{ $role->value }}">{{ $role->label() }}</option>
                        @endforeach
                    </select>
                    <input type="password" name="password" required placeholder="Kata sandi" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <button class="w-full rounded-lg bg-red-600 py-2.5 text-sm font-semibold text-white hover:bg-red-700">Tambah</button>
                </form>
            </div>
        @endif
    </div>
@endsection
