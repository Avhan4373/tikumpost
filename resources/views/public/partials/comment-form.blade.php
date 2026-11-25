@php($parent = $parent ?? null)
@php($compact = $compact ?? false)
<form method="POST" action="{{ route('articles.comment', $article) }}" class="{{ $compact ? 'mt-3 space-y-2' : 'mt-6 space-y-2' }}">
    @csrf
    @if ($parent)
        <input type="hidden" name="parent_id" value="{{ $parent->id }}">
    @endif

    @guest
        <p class="text-xs text-slate-500">Alamat email Anda tidak akan dipublikasikan. Ruas bertanda <span class="font-semibold text-brand-600">*</span> wajib diisi.</p>
    @endguest

    <textarea name="body" rows="{{ $compact ? 2 : 3 }}" required placeholder="{{ $parent ? 'Tulis balasan...*' : 'Tulis komentar Anda...*' }}"
        class="w-full rounded-2xl border border-slate-200 bg-white p-3.5 text-sm transition focus:border-brand-400 focus:outline-none focus:ring-4 focus:ring-brand-100">{{ old('body') }}</textarea>

    @guest
        <div class="grid gap-2 sm:grid-cols-2">
            <input type="text" name="name" value="{{ old('name', request()->cookie('comment_author_name')) }}" required placeholder="Nama*"
                class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm focus:border-brand-400 focus:outline-none focus:ring-4 focus:ring-brand-100">
            <input type="email" name="email" value="{{ old('email', request()->cookie('comment_author_email')) }}" required placeholder="Email*"
                class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm focus:border-brand-400 focus:outline-none focus:ring-4 focus:ring-brand-100">
        </div>
        <label class="flex items-start gap-2 text-xs text-slate-500">
            <input type="checkbox" name="remember" value="1" @checked(old('remember', request()->cookie('comment_author_name')))
                class="mt-0.5 rounded border-slate-300 text-brand-600 focus:ring-brand-200">
            <span>Simpan nama dan email saya di peramban ini untuk komentar berikutnya.</span>
        </label>
    @endguest

    @error('body')
        <p class="text-xs font-medium text-brand-600">{{ $message }}</p>
    @enderror
    @error('name')
        <p class="text-xs font-medium text-brand-600">{{ $message }}</p>
    @enderror
    @error('email')
        <p class="text-xs font-medium text-brand-600">{{ $message }}</p>
    @enderror

    <button type="submit" class="btn-anim {{ $compact ? 'rounded-full bg-slate-900 px-4 py-2 text-xs font-semibold text-white hover:bg-slate-700' : 'rounded-full bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-700' }}">
        {{ $parent ? 'Kirim Balasan' : 'Kirim Komentar' }}
    </button>
</form>
