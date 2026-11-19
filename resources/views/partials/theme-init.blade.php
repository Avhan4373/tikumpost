{{-- Terapkan tema sebelum render agar tidak berkedip saat halaman dimuat. --}}
@php($defaultTheme = \App\Support\SiteSettings::value('default_theme', 'light') === 'dark' ? 'dark' : 'light')
<script>
    (function () {
        try {
            var stored = localStorage.getItem('theme');
            var dark = (stored || '{{ $defaultTheme }}') === 'dark';

            document.documentElement.classList.toggle('dark', dark);
            document.documentElement.style.colorScheme = dark ? 'dark' : 'light';
        } catch (error) {
            document.documentElement.classList.toggle('dark', {{ $defaultTheme === 'dark' ? 'true' : 'false' }});
            document.documentElement.style.colorScheme = '{{ $defaultTheme }}';
        }
    })();
</script>