@extends('layouts.admin')

@section('title', 'Artikel')
@section('heading', 'Kelola Artikel')

@section('content')
    @php
        $sortingByViews = request('sort') === 'views';
        $viewDir = request('dir') === 'asc' ? 'asc' : 'desc';
        $nextViewDir = $sortingByViews && $viewDir === 'desc' ? 'asc' : 'desc';
    @endphp

    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <form method="GET" class="flex flex-wrap gap-2">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari artikel..."
                class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-red-500 focus:outline-none">
            <select name="status" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">Semua status</option>
                @foreach (\App\Enums\ArticleStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
            <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Filter</button>
        </form>
        <a href="{{ route('admin.articles.create') }}" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">+ Artikel Baru</a>
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-5 py-3">Judul</th>
                    <th class="px-5 py-3">Kategori</th>
                    <th class="px-5 py-3">Status</th>
                    <th class="px-5 py-3">Tanggal</th>
                    <th class="px-5 py-3">
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'views', 'dir' => $nextViewDir, 'page' => null]) }}"
                            class="inline-flex items-center gap-1 transition hover:text-slate-700">
                            Dibaca
                            <span class="text-slate-400">{{ $sortingByViews ? ($viewDir === 'asc' ? '↑' : '↓') : '↕' }}</span>
                        </a>
                    </th>
                    <th class="px-5 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($articles as $article)
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-3">
                            <p class="font-medium text-slate-800">{{ Str::limit($article->title, 60) }}</p>
                            <p class="text-xs text-slate-400">{{ $article->author?->name }}</p>
                        </td>
                        <td class="px-5 py-3 text-slate-600">{{ $article->category?->name ?? '-' }}</td>
                        <td class="px-5 py-3">
                            <span class="rounded-full px-2 py-1 text-xs font-semibold {{ $article->status->badge() }}">{{ $article->status->label() }}</span>
                        </td>
                        <td class="px-5 py-3 text-slate-500">{{ optional($article->published_at)->format('d M Y') ?? $article->created_at->format('d M Y') }}</td>
                        <td class="px-5 py-3 text-slate-600">{{ number_format($article->views) }}</td>
                        <td class="px-5 py-3">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('articles.show', $article) }}" target="_blank" class="rounded border border-slate-300 px-2 py-1 text-xs hover:bg-slate-100">Lihat</a>
                                <a href="{{ route('admin.articles.edit', $article) }}" class="rounded border border-slate-300 px-2 py-1 text-xs hover:bg-slate-100">Edit</a>
                                <form method="POST" action="{{ route('admin.articles.destroy', $article) }}" data-confirm="Hapus artikel ini?">
                                    @csrf @method('DELETE')
                                    <button class="rounded border border-red-300 px-2 py-1 text-xs text-red-600 hover:bg-red-50">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-10 text-center text-slate-400">Belum ada artikel.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-5">{{ $articles->links() }}</div>
@endsection
