@extends('layouts.admin')

@section('title', 'Tikum Reals')
@section('heading', 'Tikum Reals')

@section('content')
    <div class="grid gap-6 lg:grid-cols-[1fr_20rem]">
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-3">Judul</th>
                        <th class="px-5 py-3">URL Instagram</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Urutan</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($reels as $reel)
                        <tr>
                            <td class="px-5 py-3 font-medium text-slate-800">{{ $reel->title }}</td>
                            <td class="px-5 py-3 text-slate-500">
                                <a href="{{ $reel->url }}" target="_blank" rel="noopener" class="inline-block max-w-[16rem] truncate align-bottom hover:text-red-600">{{ $reel->url }}</a>
                            </td>
                            <td class="px-5 py-3">
                                @if ($reel->is_published)
                                    <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">Tayang</span>
                                @else
                                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-500">Draf</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-slate-500">{{ $reel->sort_order }}</td>
                            <td class="px-5 py-3">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.reels.edit', $reel) }}" class="rounded border border-slate-300 px-2 py-1 text-xs hover:bg-slate-100">Edit</a>
                                    <form method="POST" action="{{ route('admin.reels.destroy', $reel) }}" onsubmit="return confirm('Hapus reel ini?')">
                                        @csrf @method('DELETE')
                                        <button class="rounded border border-red-300 px-2 py-1 text-xs text-red-600 hover:bg-red-50">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-10 text-center text-slate-400">Belum ada reel. Tambahkan tautan Instagram pertama Anda.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="border-t border-slate-100 px-5 py-3">{{ $reels->links() }}</div>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <h2 class="mb-4 font-bold text-slate-900">Tambah Reel</h2>
            <form method="POST" action="{{ route('admin.reels.store') }}" class="space-y-3">
                @csrf
                <input type="text" name="title" required placeholder="Judul reel" value="{{ old('title') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <input type="url" name="url" required placeholder="https://www.instagram.com/reel/..." value="{{ old('url') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <div class="flex items-center gap-4">
                    <label class="flex items-center gap-2 text-sm text-slate-600">
                        <input type="checkbox" name="is_published" value="1" checked class="rounded border-slate-300 text-red-600"> Tayangkan
                    </label>
                    <input type="number" name="sort_order" value="0" min="0" class="w-24 rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Urutan">
                </div>
                <button class="w-full rounded-lg bg-red-600 py-2.5 text-sm font-semibold text-white hover:bg-red-700">Tambah</button>
                <p class="text-xs text-slate-400">Tempel tautan Reel/Post Instagram. Preview akan tampil otomatis di halaman Tikum Reals.</p>
            </form>
        </div>
    </div>
@endsection
