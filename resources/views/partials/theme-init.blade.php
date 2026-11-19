{{-- Terapkan tema sebelum render agar tidak berkedip saat halaman dimuat. --}}
<script>
    (function () {
        try {
            var stored = localStorage.getItem('theme');
            var dark = stored ? stored === 'dark' : window.matchMedia('(prefers-color-scheme: dark)').matches;

            document.documentElement.classList.toggle('dark', dark);
            document.documentElement.style.colorScheme = dark ? 'dark' : 'light';
        } catch (error) {
            // localStorage tidak tersedia — biarkan tema terang.
        }
    })();
</script>
