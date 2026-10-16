@extends('layouts.public')

@section('title', $page['title'])

@section('content')
    <div class="mx-auto max-w-3xl px-4 py-10 lg:py-14">
        <nav class="mb-4 text-sm text-slate-500">
            <a href="{{ route('home') }}" class="transition hover:text-red-600">Beranda</a>
            <span class="mx-1 text-slate-300">/</span>
            <span class="text-slate-600">{{ $page['title'] }}</span>
        </nav>
        <h1 class="text-3xl font-bold tracking-tight text-slate-900 lg:text-4xl">{{ $page['title'] }}</h1>
        <div class="article-body mt-6">
            <p>{{ $page['body'] }}</p>
        </div>
    </div>
@endsection
