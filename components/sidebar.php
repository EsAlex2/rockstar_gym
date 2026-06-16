<?php
// Aseguramos que las variables de sesión existan por si acaso
$user_role = $_SESSION['user_role'] ?? 'Invitado';
$user_fullname = $_SESSION['user_fullname'] ?? 'Usuario';
?>

<aside id="sidebar"
    class="w-64 min-h-screen bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 flex flex-col justify-between transition-all duration-300 ease-in-out relative group">

    <button id="toggle-sidebar"
        class="absolute -right-3 top-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-400 rounded-full p-1 hover:text-orange-500 shadow-sm transition-transform duration-300 z-50 focus:outline-none">
        <svg id="toggle-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5"
            stroke="currentColor" class="w-4 h-4 transition-transform duration-300">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
        </svg>
    </button>

    <div class="p-4 overflow-x-hidden">
        <div class="flex items-center gap-3 mb-8 px-2">
            <div
                class="flex-shrink-0 inline-flex items-center justify-center w-10 h-10 rounded-xl bg-orange-500 text-white font-black text-lg shadow-md shadow-orange-500/20">
                R
            </div>
            <span
                class="sidebar-text text-xl font-bold tracking-tight text-gray-900 dark:text-white transition-opacity duration-200"><?= SITE_NAME ?></span>
        </div>

        <nav class="space-y-1">
            <a href="home.php"
                class="flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl bg-orange-500 text-white shadow-lg shadow-orange-500/10">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                    stroke="currentColor" class="w-5 h-5 flex-shrink-0">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                </svg>
                <span class="sidebar-text transition-opacity duration-200">Inicio (Dashboard)</span>
            </a>

            <a href="clientes.php"
                class="flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                    stroke="currentColor" class="w-5 h-5 flex-shrink-0">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                </svg>
                <span class="sidebar-text transition-opacity duration-200">Clientes / Miembros</span>
            </a>

            <a href="membresias.php"
                class="flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                    stroke="currentColor" class="w-5 h-5 flex-shrink-0">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                </svg>
                <span class="sidebar-text transition-opacity duration-200">Planes y Membresías</span>
            </a>

            <a href="entrenadores.php"
                class="flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                    stroke="currentColor" class="w-5 h-5 flex-shrink-0">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                </svg>
                <span class="sidebar-text transition-opacity duration-200">Entrenadores</span>
            </a>

            <a href="pagos.php"
                class="flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                    stroke="currentColor" class="w-5 h-5 flex-shrink-0">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5h16.5M4.5 19.5h15M12 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-1.5a2.25 2.25 0 01-2.25-2.25V6.75A2.25 2.25 0 018.75 4.5h1.5a2.25 2.25 0 012.25 2.25z" />
                </svg>
                <span class="sidebar-text transition-opacity duration-200">Control de Pagos</span>
            </a>

            <a href="entrenamientos.php"
                class="flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                    stroke="currentColor" class="w-5 h-5 flex-shrink-0">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="sidebar-text transition-opacity duration-200">Entrenamientos</span>
            </a>
            <a href="usuarios.php"
                class="flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                    stroke="currentColor" class="w-5 h-5 flex-shrink-0">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                </svg>
                <span class="sidebar-text transition-opacity duration-200">Gestion Usuarios</span>
            </a>
            <a href="personas.php"
                class="flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                    stroke="currentColor" class="w-5 h-5 flex-shrink-0">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                </svg>
                <span class="sidebar-text transition-opacity duration-200">Gestion de Personas</span>
            </a>

        </nav>
    </div>

    <div class="p-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 overflow-x-hidden">
        <div class="sidebar-text flex items-center justify-between mb-2 transition-opacity duration-200">
            <div class="truncate">
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider truncate"><?= $user_role ?></p>
                <p class="text-xs font-bold text-gray-700 dark:text-white truncate"><?= $user_fullname ?></p>
            </div>
        </div>
        <a href="<?= URL_BASE ?>/public/logout.php"
            class="block text-center w-full mt-2 py-2 px-3 text-xs font-semibold text-white bg-red-500 hover:bg-red-600 rounded-lg transition-colors truncate">
            <span class="sidebar-text">Cerrar Sesión</span>
            <span class="sidebar-icon hidden justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                    stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                </svg>
            </span>
        </a>
    </div>
</aside>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('toggle-sidebar');
        const toggleIcon = document.getElementById('toggle-icon');
        const texts = document.querySelectorAll('.sidebar-text');
        const logoutIcon = sidebar.querySelector('.sidebar-icon');

        // Función para aplicar los estados visuales
        const setSidebarState = (isCollapsed) => {
            if (isCollapsed) {
                sidebar.classList.replace('w-64', 'w-20');
                toggleIcon.classList.add('rotate-180');
                texts.forEach(el => el.classList.add('opacity-0', 'pointer-events-none', 'hidden'));
                if (logoutIcon) logoutIcon.classList.replace('hidden', 'flex');
            } else {
                sidebar.classList.replace('w-20', 'w-64');
                toggleIcon.classList.remove('rotate-180');
                texts.forEach(el => {
                    el.classList.remove('opacity-0', 'pointer-events-none', 'hidden');
                });
                if (logoutIcon) logoutIcon.classList.replace('flex', 'hidden');
            }
        };

        // Comprobar si ya estaba guardado el estado colapsado en el navegador
        let isCollapsed = localStorage.getItem('sidebar-collapsed') === 'true';
        setSidebarState(isCollapsed);

        toggleBtn.addEventListener('click', () => {
            isCollapsed = !isCollapsed;
            localStorage.setItem('sidebar-collapsed', isCollapsed);
            setSidebarState(isCollapsed);
        });
    });
</script>