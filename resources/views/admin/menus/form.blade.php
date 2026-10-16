@extends('layouts.admin')

@php $isEdit = $menu->exists; @endphp

@section('title', $isEdit ? 'Edit Menu' : 'Menu Baru')
@section('heading', $isEdit ? 'Edit Menu' : 'Menu Baru')

@section('content')
    <form method="POST"
          action="{{ $isEdit ? route('admin.menus.update', $menu) : route('admin.menus.store') }}"
          class="max-w-xl space-y-4 rounded-xl border border-slate-200 bg-white p-6">
        @csrf
        @if ($isEdit) @method('PUT') @endif

        @include('admin.menus.fields', ['menu' => $menu, 'parents' => $parents, 'nextOrder' => $nextOrder])

        <div class="flex gap-2 pt-1">
            <button class="rounded-lg bg-red-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-red-700">Simpan</button>
            <a href="{{ route('admin.menus.index') }}" class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm hover:bg-slate-100">Batal</a>
        </div>
    </form>
@endsection
