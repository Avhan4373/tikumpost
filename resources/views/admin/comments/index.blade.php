@extends('layouts.admin')

@section('title', 'Komentar')
@section('heading', 'Moderasi Komentar')

@section('content')
    <div class="mb-5 flex gap-2 text-sm">
        @php $current = request('status'); @endphp
        <a href="{{ route('admin.comments.index') }}" class="rounded-full px-3 py-1.5 {{ ! $current ? 'bg-slate-900 text-white' : 'bg-white border border-slate-300' }}">Semua</a>
        @foreach (\App\Enums\CommentStatus::cases() as $status)
            <a href="{{ route('admin.comments.index', ['status' => $status->value]) }}"
               class="rounded-full px-3 py-1.5 {{ $current === $status->value ? 'bg-slate-900 text-white' : 'bg-white border border-slate-300' }}">{{ $status->label() }}</a>
        @endforeach
    </div>

    <div class="space-y-3">
        @forelse ($comments as $comment)
            <div class="rounded-xl border border-slate-200 bg-white p-5">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <span class="font-semibold text-slate-800">{{ $comment->user?->name }}</span>
                        <span class="text-xs text-slate-400">pada</span>
                        <span class="text-xs font-medium text-slate-500">{{ Str::limit($comment->article?->title, 50) }}</span>
                    </div>
                    <span class="rounded-full px-2 py-1 text-xs font-semibold {{ $comment->status->badge() }}">{{ $comment->status->label() }}</span>
                </div>
                <p class="mt-2 text-sm text-slate-700">{{ $comment->body }}</p>
                <div class="mt-3 flex gap-2">
                    @foreach (\App\Enums\CommentStatus::cases() as $status)
                        @if ($status !== $comment->status)
                            <form method="POST" action="{{ route('admin.comments.update', $comment) }}">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="{{ $status->value }}">
                                <button class="rounded border border-slate-300 px-2 py-1 text-xs hover:bg-slate-100">Tandai {{ $status->label() }}</button>
                            </form>
                        @endif
                    @endforeach
                    <form method="POST" action="{{ route('admin.comments.destroy', $comment) }}" onsubmit="return confirm('Hapus komentar ini?')">
                        @csrf @method('DELETE')
                        <button class="rounded border border-red-300 px-2 py-1 text-xs text-red-600 hover:bg-red-50">Hapus</button>
                    </form>
                </div>
            </div>
        @empty
            <p class="rounded-xl border border-slate-200 bg-white px-5 py-10 text-center text-slate-400">Tidak ada komentar.</p>
        @endforelse
    </div>

    <div class="mt-5">{{ $comments->links() }}</div>
@endsection
