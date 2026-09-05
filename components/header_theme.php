<script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
<style type="text/tailwindcss">
    @custom-variant dark (&:where(.dark, .dark *));
</style>
<script>
    (function() {
        const savedTheme = localStorage.getItem('color-theme');
        if (savedTheme === 'light') {
            document.documentElement.classList.remove('dark');
        } else if (savedTheme === 'dark') {
            document.documentElement.classList.add('dark');
        } else if (window.matchMedia && window.matchMedia('(prefers-color-scheme: light)').matches) {
            document.documentElement.classList.remove('dark');
        } else {
            document.documentElement.classList.add('dark');
        }
    })();
</script>
