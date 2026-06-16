<?php
require_once __DIR__ . '/../config/init.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="es" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GymSystem - Iniciar Sesión</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>

<body
    class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen flex flex-col justify-center items-center transition-colors duration-300 relative px-4">

    <button id="theme-toggle"
        class="absolute top-6 right-6 p-2.5 rounded-lg bg-gray-200 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:ring-2 hover:ring-gray-400 dark:hover:ring-gray-600 transition-all duration-200 cursor-pointer"
        aria-label="Cambiar modo de luz">
        <svg id="theme-toggle-light-icon" class="hidden w-5 h-5" fill="currentColor" viewBox="0 0 20 20"
            xmlns="http://www.w3.org/2000/svg">
            <path
                d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 14.05l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zm2.122-10.607a1 1 0 010 1.414l-.707.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM4 11a1 1 0 100-2H3a1 1 0 100 2h1z">
            </path>
        </svg>
        <svg id="theme-toggle-dark-icon" class="hidden w-5 h-5" fill="currentColor" viewBox="0 0 20 20"
            xmlns="http://www.w3.org/2000/svg">
            <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
        </svg>
    </button>

    <div
        class="w-full max-w-md bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 p-8 transition-all duration-300">

        <div class="text-center mb-8">
            <div
                class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-orange-500 text-white font-black text-xl shadow-md shadow-orange-500/20 mb-3">
                G
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                Bienvenido de nuevo
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Gestión de Gimnasios
            </p>
        </div>

        <?php if (isset($_SESSION['login_error'])): ?>
            <div
                class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl mb-4 text-sm dark:bg-red-950/50 dark:text-red-400 dark:border-red-900">
                <?= $_SESSION['login_error'];
                unset($_SESSION['login_error']); ?>
            </div>
        <?php endif; ?>

        <form action="<?= URL_BASE ?>/public/authlogin.php" method="POST" class="space-y-6">

            <div>
                <label for="identity" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Nombre de usuario o Correo
                </label>
                <input type="text" id="identity" name="identity" required
                    placeholder="ej: apellidom123 o user@dominio.com"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all duration-200 text-sm">
            </div>

            <div>
                <div class="flex justify-between items-center mb-2">
                    <label for="password" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        Contraseña
                    </label>
                    <a href="#" class="text-xs font-semibold text-orange-500 hover:text-orange-600 transition-colors">
                        ¿La olvidaste?
                    </a>
                </div>
                <input type="password" id="password" name="password" required placeholder="••••••••"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all duration-200 text-sm">
            </div>

            <div class="flex items-center">
                <input type="checkbox" id="remember" name="remember"
                    class="w-4 h-4 rounded text-orange-500 border-gray-300 focus:ring-orange-500 dark:bg-gray-700 dark:border-gray-600">
                <label for="remember" class="ml-2 text-sm text-gray-600 dark:text-gray-400 selection:bg-transparent">
                    Mantener sesión iniciada
                </label>
            </div>

            <button type="submit"
                class="w-full py-3 px-4 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-semibold text-sm shadow-lg shadow-orange-500/20 hover:shadow-orange-500/30 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 transition-all duration-200 cursor-pointer">
                Iniciar Sesión
            </button>
        </form>

        <div class="mt-8 text-center border-t border-gray-100 dark:border-gray-700 pt-4">
            <p class="text-xs text-gray-400 dark:text-gray-500">
                &copy; 2026 GymSystem. Todos los derechos reservados.
            </p>
        </div>
    </div>

    <script src="<?= URL_BASE ?>/public/js/auth.js"></script>
</body>

</html>