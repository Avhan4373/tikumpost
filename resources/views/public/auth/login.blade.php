@extends('layouts.public')

@section('title', 'Masuk')

@section('content')
    <div class="mx-auto flex max-w-md flex-col px-4 py-14 lg:py-20">
        <a href="{{ route('home') }}" class="mb-8 flex flex-col items-center gap-3 text-center">
            <span class="grid h-12 w-12 place-items-center rounded-2xl bg-gradient-to-br from-red-500 to-red-700 text-xl font-bold text-white shadow-lg shadow-red-600/30">K</span>
            <span class="text-2xl font-bold tracking-tight text-slate-900">Kabar<span class="text-red-600">Kita</span></span>
        </a>

        <div class="rounded-3xl border border-slate-200/80 bg-white p-8 shadow-xl shadow-slate-900/5">
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Masuk</h1>
            <p class="mt-1 text-sm text-slate-500">Selamat datang kembali di KabarKita.</p>

            <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4">
                @csrf
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                        class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm transition focus:border-red-400 focus:outline-none focus:ring-4 focus:ring-red-100">
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Kata Sandi</label>
                    <input type="password" name="password" required
                        class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm transition focus:border-red-400 focus:outline-none focus:ring-4 focus:ring-red-100">
                </div>
                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" name="remember" class="rounded border-slate-300 text-red-600 focus:ring-red-500"> Ingat saya
                </label>
                <button type="submit" class="w-full rounded-xl bg-red-600 py-2.5 text-sm font-semibold text-white transition hover:bg-red-700">Masuk</button>
            </form>
        </div>
    </div>
@endsection
