@extends('layouts.public')

@section('title', 'Daftar')

@section('content')
    <div class="mx-auto max-w-md px-4 py-16">
        <div class="rounded-2xl border border-slate-200 p-8 shadow-sm">
            <h1 class="text-2xl font-black text-slate-900">Buat Akun</h1>
            <p class="mt-1 text-sm text-slate-500">Gratis, dan bisa berkomentar di artikel.</p>

            <form method="POST" action="{{ route('register') }}" class="mt-6 space-y-4">
                @csrf
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Nama</label>
                    <input type="text" name="name" value="{{ old('name') }}" required autofocus
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-100">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-100">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Kata Sandi</label>
                    <input type="password" name="password" required
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-100">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Konfirmasi Kata Sandi</label>
                    <input type="password" name="password_confirmation" required
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-100">
                </div>
                <button type="submit" class="w-full rounded-lg bg-red-600 py-2.5 text-sm font-semibold text-white hover:bg-red-700">Daftar</button>
            </form>

            <p class="mt-6 text-center text-sm text-slate-600">
                Sudah punya akun? <a href="{{ route('login') }}" class="font-semibold text-red-600 hover:underline">Masuk</a>
            </p>
        </div>
    </div>
@endsection
