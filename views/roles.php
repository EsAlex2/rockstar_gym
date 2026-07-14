<?php
require_once __DIR__ . '/help.php';
// El helper 'help.php' ya provee de forma automática la variable $listaRoles
?>
<!DOCTYPE html>
<html lang="es" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Roles de Sistema - <?= SITE_NAME ?></title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>

<body class="bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-100 min-h-screen flex">

    <?php require_once __DIR__ . '/../components/sidebar.php'; ?>

    <main class="flex-1 p-10 overflow-y-auto">
        <header class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Roles del Sistema</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Configuración de perfiles de acceso y permisos globales para los módulos administrativos.</p>
            </div>
            <div>
                <button onclick="abrirModalCrear()"
                    class="px-5 py-2.5 text-sm font-semibold text-white bg-purple-600 hover:bg-purple-500 rounded-xl shadow-xs transition-all cursor-pointer flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Nuevo Rol
                </button>
            </div>
        </header>

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/20">
                            <th class="px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider w-1/4">Nombre del Rol</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider w-2/4">Descripción de Funciones</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider text-right w-1/4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-roles-body" class="divide-y divide-gray-100 dark:divide-gray-700 text-sm">
                        <?php if (!empty($listaRoles)): ?>
                            <?php foreach ($listaRoles as $rol): ?>
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/10 transition-colors">
                                    <td class="px-6 py-4 font-semibold text-gray-900 dark:text-white">
                                        <span class="px-2.5 py-1 rounded-md text-xs font-semibold bg-purple-50 dark:bg-purple-950/30 text-purple-700 dark:text-purple-400 border border-purple-100 dark:border-purple-900/30">
                                            <?= htmlspecialchars(strtoupper($rol['nombre_rol'])) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-gray-600 dark:text-gray-300 font-medium max-w-xs truncate">
                                        <?= htmlspecialchars($rol['descripcion']) ?>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <button 
                                                onclick="abrirModalEditar(<?= $rol['id'] ?>, '<?=  htmlspecialchars($rol['nombre_rol'], ENT_QUOTES) ?>', '<?= htmlspecialchars($rol['descripcion'], ENT_QUOTES) ?>')"
                                                class="p-2 text-gray-400 hover:text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg transition-colors cursor-pointer"
                                                title="Editar Rol">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                                </svg>
                                            </button>
                                            <button 
                                                onclick="eliminarRol(<?= $rol['id'] ?>)"
                                                class="p-2 text-gray-400 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/30 rounded-lg transition-colors cursor-pointer"
                                                title="Eliminar Rol">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="px-6 py-10 text-center text-gray-400 dark:text-gray-500">
                                    No se encontraron roles registrados en el sistema.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        

        <div id="modal-rol" class="fixed inset-0 z-50 hidden bg-gray-900/50 dark:bg-gray-950/70 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 w-full max-w-md overflow-hidden transform transition-all">
                
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between bg-gray-50/50 dark:bg-gray-900/20">
                    <h3 id="modal-titulo" class="text-base font-bold text-gray-900 dark:text-white">Registrar Nuevo Rol</h3>
                    <button type="button" onclick="cerrarModal()" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form id="form-rol" class="p-6 space-y-4">

                    <div id="toast-container" class="fixed top-5 right-5 z-50 flex flex-col gap-3 pointer-events-none max-w-sm w-full"></div>

                    <input type="hidden" name="id_rol" id="input-id-rol" value="">

                    <div>
                        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Nombre Único del Rol *</label>
                        <input type="text" name="nombre_rol" id="input-nombre-rol" required placeholder="Ej: administrador, entrenador, cliente"
                            class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm text-gray-900 dark:text-white focus:outline-hidden focus:border-purple-500 transition-colors">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Descripción del Rol *</label>
                        <textarea name="descripcion" id="input-descripcion" required rows="3" placeholder="Describe brevemente las responsabilidades o accesos de este rol..."
                            class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm text-gray-900 dark:text-white focus:outline-hidden focus:border-purple-500 transition-colors resize-none"></textarea>
                    </div>

                    <div class="pt-4 border-t border-gray-100 dark:border-gray-700 flex justify-end space-x-3">
                        <button type="button" onclick="cerrarModal()"
                            class="px-4 py-2 border border-gray-200 dark:border-gray-700 text-sm font-semibold rounded-xl text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer">
                            Cancelar
                        </button>
                        <button type="submit" id="btn-guardar"
                            class="px-4 py-2 text-sm font-semibold rounded-xl text-white bg-purple-600 hover:bg-purple-500 shadow-xs cursor-pointer transition-colors">
                            Guardar Rol
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script src="../public/js/roles.js"></script>
</body>

</html>