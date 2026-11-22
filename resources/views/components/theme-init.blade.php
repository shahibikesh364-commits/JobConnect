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
