<footer class="mt-16 border-t border-slate-800 bg-slate-900 text-slate-300">
    <div class="mx-auto grid max-w-7xl gap-10 px-4 py-14 md:grid-cols-2 lg:grid-cols-4">
        @php
            $siteName = $site['site_name'] ?? config('app.name', 'TikumPost');
            $socials = [
                ['key' => 'social_facebook', 'label' => 'Facebook', 'path' => 'M22 12a10 10 0 1 0-11.56 9.88v-6.99H7.9V12h2.54V9.8c0-2.5 1.49-3.89 3.77-3.89 1.09 0 2.24.2 2.24.2v2.46h-1.26c-1.24 0-1.63.77-1.63 1.56V12h2.78l-.44 2.89h-2.34v6.99A10 10 0 0 0 22 12Z'],
                ['key' => 'social_twitter', 'label' => 'X', 'path' => 'M18.244 2.25h3.308l-7.227 8.26 8.502 11.24h-6.657l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231 5.45-6.231Zm-1.161 17.52h1.833L7.084 4.126H5.117L17.083 19.77Z'],
                ['key' => 'social_instagram', 'label' => 'Instagram', 'path' => 'M12 2.16c3.2 0 3.58.01 4.85.07 1.17.05 1.8.25 2.23.41.56.22.96.48 1.38.9.42.42.68.82.9 1.38.16.42.36 1.06.41 2.23.06 1.27.07 1.65.07 4.85s-.01 3.58-.07 4.85c-.05 1.17-.25 1.8-.41 2.23-.22.56-.48.96-.9 1.38-.42.42-.82.68-1.38.9-.42.16-1.06.36-2.23.41-1.27.06-1.65.07-4.85.07s-3.58-.01-4.85-.07c-1.17-.05-1.8-.25-2.23-.41a3.7 3.7 0 0 1-1.38-.9 3.7 3.7 0 0 1-.9-1.38c-.16-.42-.36-1.06-.41-2.23C2.17 15.58 2.16 15.2 2.16 12s.01-3.58.07-4.85c.05-1.17.25-1.8.41-2.23.22-.56.48-.96.9-1.38.42-.42.82-.68 1.38-.9.42-.16 1.06-.36 2.23-.41C8.42 2.17 8.8 2.16 12 2.16Zm0 3.68a6.16 6.16 0 1 0 0 12.32 6.16 6.16 0 0 0 0-12.32Zm0 10.16a4 4 0 1 1 0-8 4 4 0 0 1 0 8Zm6.4-10.4a1.44 1.44 0 1 1-2.88 0 1.44 1.44 0 0 1 2.88 0Z'],
                ['key' => 'social_youtube', 'label' => 'YouTube', 'path' => 'M23.5 6.2a3 3 0 0 0-2.12-2.12C19.5 3.55 12 3.55 12 3.55s-7.5 0-9.38.53A3 3 0 0 0 .5 6.2 31.3 31.3 0 0 0 0 12a31.3 31.3 0 0 0 .5 5.8 3 3 0 0 0 2.12 2.12c1.88.53 9.38.53 9.38.53s7.5 0 9.38-.53a3 3 0 0 0 2.12-2.12A31.3 31.3 0 0 0 24 12a31.3 31.3 0 0 0-.5-5.8ZM9.55 15.57V8.43L15.82 12l-6.27 3.57Z'],
                ['key' => 'social_tiktok', 'label' => 'TikTok', 'path' => 'M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z'],
            ];
            $activeSocials = collect($socials)->filter(fn ($social) => ! empty($site[$social['key']]));
        @endphp
        <div class="lg:col-span-1">
            <div class="flex items-center gap-2.5">
                @include('partials.logo', ['class' => 'h-10 w-auto max-w-[9rem]', 'invert' => true])
                <span class="text-xl font-bold tracking-tight text-white">{{ $siteName }}</span>
            </div>
            <p class="mt-4 max-w-sm text-sm leading-relaxed text-slate-400">
                {{ $site['footer_text'] ?? $site['site_description'] ?? '' }}
            </p>

            @if (! empty($site['contact_email']) || ! empty($site['contact_phone']) || ! empty($site['contact_address']))
                <ul class="mt-4 space-y-1.5 text-sm text-slate-400">
                    @if (! empty($site['contact_email']))
                        <li><a href="mailto:{{ $site['contact_email'] }}" class="transition hover:text-white">{{ $site['contact_email'] }}</a></li>
                    @endif
                    @if (! empty($site['contact_phone']))
                        <li>{{ $site['contact_phone'] }}</li>
                    @endif
                    @if (! empty($site['contact_address']))
                        <li>{{ $site['contact_address'] }}</li>
                    @endif
                </ul>
            @endif

            @if ($activeSocials->isNotEmpty())
                <div class="mt-5 flex items-center gap-2">
                    @foreach ($activeSocials as $social)
                        <a href="{{ $site[$social['key']] }}" target="_blank" rel="noopener" aria-label="{{ $social['label'] }}"
                            class="grid h-9 w-9 place-items-center rounded-full bg-slate-800 text-slate-300 transition hover:bg-red-600 hover:text-white">
                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="{{ $social['path'] }}"/></svg>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        <div>
            <h3 class="text-sm font-bold uppercase tracking-wide text-white">Navigasi</h3>
            <ul class="mt-4 space-y-2.5 text-sm">
                <li><a href="{{ route('home') }}" class="text-slate-400 transition hover:text-white">Beranda</a></li>
                <li><a href="{{ route('reels.index') }}" class="text-slate-400 transition hover:text-white">Tikum Reels</a></li>
                <li><a href="{{ route('pages.show', 'tentang') }}" class="text-slate-400 transition hover:text-white">Tentang Kami</a></li>
                <li><a href="{{ route('pages.show', 'kontak') }}" class="text-slate-400 transition hover:text-white">Kontak</a></li>
                <li><a href="{{ route('pages.show', 'kebijakan-privasi') }}" class="text-slate-400 transition hover:text-white">Kebijakan Privasi</a></li>
            </ul>
        </div>

        <div>
            <h3 class="text-sm font-bold uppercase tracking-wide text-white">Kategori</h3>
            <ul class="mt-4 space-y-2.5 text-sm">
                @forelse (($navCategories ?? collect())->take(5) as $cat)
                    <li><a href="{{ route('categories.show', $cat) }}" class="text-slate-400 transition hover:text-white">{{ $cat->name }}</a></li>
                @empty
                    <li class="text-slate-500">Belum ada kategori.</li>
                @endforelse
            </ul>
        </div>

        <div id="newsletter" class="scroll-mt-28">
            <h3 class="text-sm font-bold uppercase tracking-wide text-white">Newsletter</h3>
            <p class="mt-4 text-sm leading-relaxed text-slate-400">Dapatkan berita terbaru langsung ke email Anda.</p>
            <form method="POST" action="{{ route('newsletter.store') }}" class="mt-4 space-y-2">
                @csrf
                <input type="email" name="email" required placeholder="Email Anda"
                    class="w-full rounded-xl border border-slate-700 bg-slate-800 px-3.5 py-2.5 text-sm text-white placeholder-slate-500 transition focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-500/30">
                <button type="submit" class="btn-anim w-full rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-red-700">Langganan</button>
            </form>
        </div>
    </div>

    <div class="border-t border-slate-800">
        <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-3 px-4 py-5 text-xs text-slate-500 sm:flex-row">
            <p>&copy; {{ date('Y') }} {{ $site['copyright_text'] ?? 'TikumPost. Seluruh hak cipta dilindungi.' }}</p>
            <a href="#top" class="inline-flex items-center gap-1.5 rounded-full bg-slate-800 px-3 py-1.5 font-medium text-slate-300 transition hover:bg-slate-700 hover:text-white">
                Kembali ke atas
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19V5m0 0-6 6m6-6 6 6"/></svg>
            </a>
        </div>
    </div>
</footer>
