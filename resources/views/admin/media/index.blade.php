@extends('layouts.admin')

@section('title', 'Media')
@section('heading', 'Manajer Media')

@section('content')
    <div class="mb-6 rounded-xl border border-slate-200 bg-white p-5">
        <h2 class="mb-3 font-bold text-slate-900">Unggah Media</h2>
        <form method="POST" action="{{ route('admin.media.store') }}" enctype="multipart/form-data" class="flex flex-wrap items-end gap-3">
            @csrf
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Berkas</label>
                <input type="file" name="file" required class="text-sm">
                <p class="mt-1 text-xs text-slate-400">
                    JPG, PNG, WebP, atau PDF. Maksimal {{ \App\Support\UploadLimit::label() }}.
                </p>
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Alt text</label>
                <input type="text" name="alt_text" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <button class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">Unggah</button>
        </form>
    </div>

    <div class="grid gap-4 sm:grid-cols-3 lg:grid-cols-5">
        @forelse ($media as $item)
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
                @if (str_starts_with($item->mime_type ?? '', 'image/'))
                    <img src="{{ $item->url }}" alt="{{ $item->alt_text }}" class="h-32 w-full object-cover">
                @else
                    <div class="grid h-32 place-items-center bg-slate-100 text-xs text-slate-500">{{ $item->mime_type }}</div>
                @endif
                <div class="p-3">
                    <p class="truncate text-xs text-slate-600">{{ basename($item->path) }}</p>
                    <form method="POST" action="{{ route('admin.media.destroy', $item) }}" onsubmit="return confirm('Hapus media ini?')" class="mt-2">
                        @csrf @method('DELETE')
                        <button class="w-full rounded border border-red-300 px-2 py-1 text-xs text-red-600 hover:bg-red-50">Hapus</button>
                    </form>
                </div>
            </div>
        @empty
            <p class="col-span-full rounded-xl border border-slate-200 bg-white px-5 py-10 text-center text-slate-400">Belum ada media.</p>
        @endforelse
    </div>

    <div class="mt-5">{{ $media->links() }}</div>
@endsection
