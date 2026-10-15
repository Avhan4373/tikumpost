@extends('layouts.admin')

@section('title', 'Dashboard')
@section('heading', 'Dashboard')

@section('content')
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @php
            $cards = [
                ['Total Artikel', $stats['articles'], 'bg-slate-900'],
                ['Terbit', $stats['published'], 'bg-emerald-600'],
                ['Draf', $stats['drafts'], 'bg-amber-500'],
                ['Komentar Menunggu', $stats['pending_comments'], 'bg-red-600'],
                ['Total Komentar', $stats['comments'], 'bg-blue-600'],
                ['Pelanggan', $stats['subscribers'], 'bg-indigo-600'],
                ['Pengguna', $stats['users'], 'bg-slate-600'],
            ];
        @endphp
        @foreach ($cards as [$label, $value, $color])
            <div class="rounded-xl border border-slate-200 bg-white p-5">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-slate-500">{{ $label }}</p>
                    <span class="h-3 w-3 rounded-full {{ $color }}"></span>
                </div>
                <p class="mt-3 text-3xl font-black text-slate-900">{{ number_format($value) }}</p>
            </div>
        @endforeach
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-[1.5fr_1fr]">
        <div class="rounded-xl border border-slate-200 bg-white">
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                <h2 class="font-bold text-slate-900">Artikel Terbaru</h2>
                <a href="{{ route('admin.articles.create') }}" class="rounded-lg bg-red-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-red-700">+ Tulis</a>
            </div>
            <table class="w-full text-sm">
                <tbody class="divide-y divide-slate-100">
                    @forelse ($recent as $article)
                        <tr>
                            <td class="px-5 py-3">
                                <a href="{{ route('admin.articles.edit', $article) }}" class="font-medium text-slate-800 hover:text-red-600">{{ Str::limit($article->title, 60) }}</a>
                                <p class="text-xs text-slate-400">{{ $article->author?->name }} &middot; {{ $article->category?->name ?? 'Tanpa kategori' }}</p>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <span class="rounded-full px-2 py-1 text-xs font-semibold {{ $article->status->badge() }}">{{ $article->status->label() }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr><td class="px-5 py-6 text-center text-slate-400">Belum ada artikel.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white">
            <div class="border-b border-slate-200 px-5 py-4">
                <h2 class="font-bold text-slate-900">Komentar Menunggu Moderasi</h2>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse ($pendingComments as $comment)
                    <div class="px-5 py-3 text-sm">
                        <p class="font-medium text-slate-700">{{ $comment->user?->name }}</p>
                        <p class="text-slate-500">{{ Str::limit($comment->body, 80) }}</p>
                        <a href="{{ route('admin.comments.index') }}" class="text-xs font-semibold text-red-600 hover:underline">Moderasi &rarr;</a>
                    </div>
                @empty
                    <p class="px-5 py-6 text-center text-slate-400">Tidak ada komentar menunggu.</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection
