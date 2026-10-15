@extends('layouts.admin')

@section('title', 'Pelanggan')
@section('heading', 'Pelanggan Newsletter')

@section('content')
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                <tr><th class="px-5 py-3">Email</th><th class="px-5 py-3">Bergabung</th><th class="px-5 py-3 text-right">Aksi</th></tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($subscribers as $subscriber)
                    <tr>
                        <td class="px-5 py-3 font-medium text-slate-800">{{ $subscriber->email }}</td>
                        <td class="px-5 py-3 text-slate-500">{{ $subscriber->created_at->format('d M Y H:i') }}</td>
                        <td class="px-5 py-3">
                            <form method="POST" action="{{ route('admin.subscribers.destroy', $subscriber) }}" onsubmit="return confirm('Hapus pelanggan ini?')" class="flex justify-end">
                                @csrf @method('DELETE')
                                <button class="rounded border border-red-300 px-2 py-1 text-xs text-red-600 hover:bg-red-50">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="px-5 py-10 text-center text-slate-400">Belum ada pelanggan.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="border-t border-slate-100 px-5 py-3">{{ $subscribers->links() }}</div>
    </div>
@endsection
