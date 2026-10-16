@extends('layouts.admin')

@section('title', 'Menu Navigasi')
@section('heading', 'Menu Navigasi')

@section('content')
    <div class="grid gap-6 lg:grid-cols-[1fr_22rem]">
        {{-- Daftar menu (drag & drop) --}}
        <div>
            <div class="mb-3 flex items-center justify-between gap-3">
                <p class="text-sm text-slate-500">
                    Seret ikon <span class="inline-flex items-center rounded bg-slate-200 px-1.5 py-0.5 text-slate-600">⠿</span>
                    untuk mengubah urutan. Tarik ke area submenu sebuah induk untuk menjadikannya submenu.
                </p>
                <span data-menu-save-status class="shrink-0 text-xs font-semibold text-emerald-600 opacity-0 transition">Tersimpan</span>
            </div>

            @if ($menus->isEmpty())
                <p class="rounded-xl border border-dashed border-slate-300 bg-white p-10 text-center text-sm text-slate-500">
                    Belum ada menu. Tambahkan menu pertama di panel kanan.
                </p>
            @else
                <div data-menu-sortable data-reorder-url="{{ route('admin.menus.reorder') }}">
                    <ul data-menu-list class="space-y-2">
                        @foreach ($menus as $menu)
                            <li data-id="{{ $menu->id }}" class="rounded-xl border border-slate-200 bg-white shadow-sm">
                                @include('admin.menus.row', ['menu' => $menu])

                                <ul data-menu-list data-menu-children
                                    class="mr-3 mb-2 ml-10 space-y-2 border-l-2 border-dashed border-slate-200 pl-4 empty:mb-3 empty:min-h-3">
                                    @foreach ($menu->children as $child)
                                        <li data-id="{{ $child->id }}" class="rounded-xl border border-slate-200 bg-slate-50/70">
                                            @include('admin.menus.row', ['menu' => $child])
                                        </li>
                                    @endforeach
                                </ul>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        {{-- Panel kanan: tambah menu & impor --}}
        <div class="space-y-6">
            <div class="rounded-xl border border-slate-200 bg-white p-5">
                <h2 class="mb-4 font-bold text-slate-900">Tambah Menu</h2>
                <form method="POST" action="{{ route('admin.menus.store') }}" class="space-y-3">
                    @csrf
                    @include('admin.menus.fields', ['menu' => new App\Models\Menu(), 'parents' => $parents, 'nextOrder' => $nextOrder])
                    <button class="w-full rounded-lg bg-red-600 py-2.5 text-sm font-semibold text-white hover:bg-red-700">Tambah</button>
                </form>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-5">
                <h2 class="mb-2 font-bold text-slate-900">Impor Kategori</h2>
                <p class="mb-4 text-sm text-slate-500">Tambahkan semua kategori yang belum ada sebagai item menu, sekali klik.</p>
                <form method="POST" action="{{ route('admin.menus.import-categories') }}">
                    @csrf
                    <button class="w-full rounded-lg border border-slate-300 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-100">Impor dari Kategori</button>
                </form>
            </div>
        </div>
    </div>
@endsection
