<link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}?v=1">
<meta name="theme-color" content="#5146e5">

{{-- Initialize saved theme before stylesheets load --}}
<script>
    (() => {
        try {
            const savedTheme = localStorage.getItem('jobconnect-theme');

            document.documentElement.dataset.theme =
                savedTheme === 'dark' ? 'dark' : 'light';

        } catch (error) {
            document.documentElement.dataset.theme = 'light';
        }
    })();
</script>
