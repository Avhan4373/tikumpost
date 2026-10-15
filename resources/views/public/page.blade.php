@extends('layouts.public')

@section('title', $page['title'])

@section('content')
    <div class="mx-auto max-w-3xl px-4 py-12">
        <h1 class="text-3xl font-black text-slate-900">{{ $page['title'] }}</h1>
        <div class="prose prose-slate mt-6 max-w-none">
            <p>{{ $page['body'] }}</p>
        </div>
    </div>
@endsection
