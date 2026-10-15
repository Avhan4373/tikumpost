<footer class="mt-16 border-t border-slate-200 bg-slate-900 text-slate-300">
    <div class="mx-auto grid max-w-7xl gap-10 px-4 py-12 md:grid-cols-3">
        <div>
            <div class="flex items-center gap-2">
                <span class="grid h-9 w-9 place-items-center rounded-lg bg-red-600 font-black text-white">K</span>
                <span class="text-xl font-black text-white">KabarKita</span>
            </div>
            <p class="mt-4 max-w-sm text-sm text-slate-400">
                Portal berita independen yang menyajikan informasi terkini, akurat, dan berimbang untuk pembaca Indonesia.
            </p>
        </div>

        <div>
            <h3 class="text-sm font-semibold uppercase tracking-wide text-white">Navigasi</h3>
            <ul class="mt-4 space-y-2 text-sm">
                <li><a href="{{ route('home') }}" class="hover:text-white">Beranda</a></li>
                <li><a href="{{ route('pages.show', 'tentang') }}" class="hover:text-white">Tentang Kami</a></li>
                <li><a href="{{ route('pages.show', 'kontak') }}" class="hover:text-white">Kontak</a></li>
                <li><a href="{{ route('pages.show', 'kebijakan-privasi') }}" class="hover:text-white">Kebijakan Privasi</a></li>
            </ul>
        </div>

        <div>
            <h3 class="text-sm font-semibold uppercase tracking-wide text-white">Newsletter</h3>
            <p class="mt-4 text-sm text-slate-400">Dapatkan berita terbaru langsung ke email Anda.</p>
            <form method="POST" action="{{ route('newsletter.store') }}" class="mt-4 flex gap-2">
                @csrf
                <input type="email" name="email" required placeholder="Email Anda"
                    class="w-full rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-sm text-white placeholder-slate-500 focus:border-red-500 focus:outline-none">
                <button type="submit" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">Langganan</button>
            </form>
        </div>
    </div>
    <div class="border-t border-slate-800 py-5 text-center text-xs text-slate-500">
        &copy; {{ date('Y') }} KabarKita. Dibangun dengan Laravel.
    </div>
</footer>
