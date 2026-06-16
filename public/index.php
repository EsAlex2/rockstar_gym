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

<body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen flex flex-col justify-center items-center transition-colors duration-300 relative px-4">

    <button id=\"theme-toggle\" class="absolute top-6 right-6 p-2.5 rounded-lg bg-gray-200 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:ring-2 hover:ring-gray-400 dark:hover:ring-gray-600 transition-all duration-200 cursor-pointer" aria-label="Cambiar modo de luz">
        <svg id="theme-toggle-light-icon" class="hidden w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
            <path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 14.121a1 1 0 011.414 0l.707.707a1 1 0 11-1.414 1.414l-.707-.707a1 1 0 010-1.414zm-.707-7.778a1 1 0 011.414 0l.707.707a1 1 0 11-1.414 1.414l-.707-.707a1 1 0 010-1.414zM4 11a1 1 0 100-2H3a1 1 0 100 2h1z"></path>
        </svg>
        <svg id="theme-toggle-dark-icon" class="hidden w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
            <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
        </svg>
    </button>

    <div class="w-full max-w-md bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-8 shadow-xl">
        <div class="text-center mb-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">¡Bienvenido de nuevo!</h2>
            <p class="text-sm text-gray-400 mt-1">Ingresa tus credenciales de seguridad corporativas</p>
        </div>

        <?php if (isset($_SESSION['login_error'])): ?>
            <div class="mb-4 p-4 text-sm text-rose-700 bg-rose-50/50 border border-rose-100 dark:bg-rose-950/20 dark:text-rose-400 dark:border-rose-900/30 rounded-xl flex items-center gap-2">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <span><?= htmlspecialchars($_SESSION['login_error']); ?></span>
            </div>
            <?php unset($_SESSION['login_error']); // Se limpia inmediatamente ?>
        <?php endif; ?>

        <form action="authlogin.php" method="POST" class="space-y-4">
            <div>
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Correo Electrónico</label>
                <input type="text" name="identity" required autocomplete="username" placeholder="Ej: usuario@correo.com" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-hidden focus:border-orange-500 text-sm transition-colors">
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Contraseña de Acceso</label>
                <input type="password" name="password" required autocomplete="current-password" placeholder="••••••••" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-hidden focus:border-orange-500 text-sm transition-colors">
            </div>

            <div class="flex items-center">
                <input type="checkbox" id="remember" name="remember" class="w-4 h-4 rounded text-orange-500 border-gray-300 focus:ring-orange-500 dark:bg-gray-700 dark:border-gray-600">
                <label for="remember" class="ml-2 text-sm text-gray-600 dark:text-gray-400 cursor-pointer select-none">
                    Mantener sesión iniciada
                </label>
            </div>

            <button type="submit" class="w-full py-3 px-4 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-semibold text-sm shadow-md hover:shadow-lg focus:outline-hidden transition-all cursor-pointer text-center">
                Iniciar Sesión
            </button>
        </form>
    </div>

    <script src="js/auth.js"></script>
</body>

</html>