<?php
require_once __DIR__ . '/../config/init.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: " . URL_BASE . "/public/index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?= SITE_NAME ?></title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>

<body class="bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-100 min-h-screen flex">

    <?php require_once __DIR__ . '/../components/sidebar.php'; ?>

    <main class="flex-1 p-10 overflow-y-auto">
        <header class="mb-8">
            <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Panel Principal</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Resumen operativo de Rockstar Gym.</p>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div
                class="p-6 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm">
                <p class="text-xs font-medium text-gray-400 uppercase">Clientes Activos</p>
                <p class="text-2xl font-bold mt-2 text-gray-900 dark:text-white">--</p>
            </div>

            <div
                class="p-6 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm">
                <p class="text-xs font-medium text-gray-400 uppercase">Planes Disponibles</p>
                <p class="text-2xl font-bold mt-2 text-gray-900 dark:text-white">--</p>
            </div>

            <div
                class="p-6 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm">
                <p class="text-xs font-medium text-gray-400 uppercase">Ingresos del Mes</p>
                <p class="text-2xl font-bold mt-2 text-emerald-500">Ref. $</p>
            </div>

            <div
                class="p-6 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm">
                <p class="text-xs font-medium text-gray-400 uppercase">Clases Hoy</p>
                <p class="text-2xl font-bold mt-2 text-orange-500">--</p>
            </div>
        </div>
    </main>

</body>

</html>