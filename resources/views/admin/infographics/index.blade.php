@extends('layouts.admin')

@section('title', 'Infografis')
@section('heading', 'Infografis')

@section('content')
    <div class="grid gap-6 lg:grid-cols-[1fr_20rem]">
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-3">Gambar</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Urutan</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($infographics as $infographic)
                        <tr>
                            <td class="px-5 py-3">
                                <img src="{{ Storage::disk('public')->url($infographic->image) }}" alt=""
                                    class="h-14 w-14 rounded-lg border border-slate-200 bg-slate-50 object-cover">
                            </td>
                            <td class="px-5 py-3">
                                @if ($infographic->is_published)
                                    <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">Tayang</span>
                                @else
                                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-500">Draf</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-slate-500">{{ $infographic->sort_order }}</td>
                            <td class="px-5 py-3">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.infographics.edit', $infographic) }}" class="rounded border border-slate-300 px-2 py-1 text-xs hover:bg-slate-100">Edit</a>
                                    <form method="POST" action="{{ route('admin.infographics.destroy', $infographic) }}" onsubmit="return confirm('Hapus infografis ini?')">
                                        @csrf @method('DELETE')
                                        <button class="rounded border border-red-300 px-2 py-1 text-xs text-red-600 hover:bg-red-50">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-5 py-10 text-center text-slate-400">Belum ada infografis. Unggah gambar pertama Anda di panel kanan.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="border-t border-slate-100 px-5 py-3">{{ $infographics->links() }}</div>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <h2 class="mb-4 font-bold text-slate-900">Tambah Infografis</h2>
            <form method="POST" action="{{ route('admin.infographics.store') }}" enctype="multipart/form-data" class="space-y-3">
                @csrf
                <input type="file" name="image" required accept="image/png,image/jpeg,image/webp"
                    class="block w-full cursor-pointer text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm file:font-medium file:text-slate-700 hover:file:bg-slate-200">
                <div class="flex items-center gap-4">
                    <label class="flex items-center gap-2 text-sm text-slate-600">
                        <input type="checkbox" name="is_published" value="1" checked class="rounded border-slate-300 text-red-600"> Tayangkan
                    </label>
                    <input type="number" name="sort_order" value="0" min="0" class="w-24 rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Urutan">
                </div>
                <button class="w-full rounded-lg bg-red-600 py-2.5 text-sm font-semibold text-white hover:bg-red-700">Tambah</button>
                <p class="text-xs text-slate-400">Format PNG/JPG/WebP, maksimal 8 MB. Infografis tampil di sidebar beranda.</p>
            </form>
        </div>
    </div>
@endsection
