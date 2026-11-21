@extends('layouts.admin')

@section('title', 'Tema')
@section('heading', 'Tema Situs')

@section('content')
    <form method="POST" action="{{ route('admin.theme.update') }}" class="max-w-2xl space-y-6 pb-20">
        @csrf
        @method('PUT')

        @foreach ($groups as $key => $group)
            <section class="rounded-xl border border-slate-200 bg-white p-6">
                <h2 class="text-base font-bold text-slate-900">{{ $group['label'] }}</h2>
                @if (! empty($group['description']))
                    <p class="mt-1 text-sm text-slate-500">{{ $group['description'] }}</p>
                @endif

                <div class="mt-5 space-y-4">
                    @foreach ($group['fields'] as $field)
                        @include('admin.settings.field', ['field' => $field, 'values' => $values])
                    @endforeach
                </div>
            </section>
        @endforeach

        <div class="sticky bottom-6 z-10 flex items-center gap-3 rounded-xl border border-slate-200 bg-white/95 px-5 py-3 shadow-lg backdrop-blur">
            <span class="mr-auto text-xs text-slate-400">Perubahan berlaku langsung di situs publik.</span>
            <button class="rounded-lg bg-red-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm shadow-red-600/20 transition hover:bg-red-700">
                Simpan Tema
            </button>
        </div>
    </form>
@endsection
