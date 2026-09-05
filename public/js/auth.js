// public/js/auth.js

document.addEventListener('DOMContentLoaded', () => {
    const themeToggleDarkIcon = document.getElementById('theme-toggle-dark-icon');
    const themeToggleLightIcon = document.getElementById('theme-toggle-light-icon');
    const themeToggleBtn = document.getElementById('theme-toggle');

    function syncThemeUI() {
        const isDark = document.documentElement.classList.contains('dark');
        if (isDark) {
            if (themeToggleDarkIcon) themeToggleDarkIcon.classList.add('hidden');
            if (themeToggleLightIcon) themeToggleLightIcon.classList.remove('hidden');
        } else {
            if (themeToggleLightIcon) themeToggleLightIcon.classList.add('hidden');
            if (themeToggleDarkIcon) themeToggleDarkIcon.classList.remove('hidden');
        }
    }

    // Sincronizar iconos según el tema actual en el DOM
    syncThemeUI();

    // Manejo del evento de click para cambiar el tema
    if (themeToggleBtn) {
        themeToggleBtn.addEventListener('click', function () {
            const isDark = document.documentElement.classList.contains('dark');
            if (isDark) {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('color-theme', 'light');
            } else {
                document.documentElement.classList.add('dark');
                localStorage.setItem('color-theme', 'dark');
            }
            syncThemeUI();
        });
    }
});