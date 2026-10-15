@extends('layouts.public')

@section('title', 'Masuk')

@section('content')
    <div class="mx-auto max-w-md px-4 py-16">
        <div class="rounded-2xl border border-slate-200 p-8 shadow-sm">
            <h1 class="text-2xl font-black text-slate-900">Masuk</h1>
            <p class="mt-1 text-sm text-slate-500">Selamat datang kembali di KabarKita.</p>

            <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4">
                @csrf
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-100">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Kata Sandi</label>
                    <input type="password" name="password" required
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-100">
                </div>
                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" name="remember" class="rounded border-slate-300"> Ingat saya
                </label>
                <button type="submit" class="w-full rounded-lg bg-red-600 py-2.5 text-sm font-semibold text-white hover:bg-red-700">Masuk</button>
            </form>

            <p class="mt-6 text-center text-sm text-slate-600">
                Belum punya akun? <a href="{{ route('register') }}" class="font-semibold text-red-600 hover:underline">Daftar sekarang</a>
            </p>
        </div>
    </div>
@endsection
