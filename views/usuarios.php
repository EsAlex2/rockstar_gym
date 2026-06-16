<?php
require_once __DIR__ . '/help.php';
// Aquí se asume que tu enrutador ya provee: $listaUsuarios, $listaPersonas y $listaRoles
?>
<!DOCTYPE html>
<html lang="es" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios - <?= SITE_NAME ?></title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>

<body class="bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-100 min-h-screen flex">

    <?php require_once __DIR__ . '/../components/sidebar.php'; ?>

    <main class="flex-1 p-10 overflow-y-auto">
        <header class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Gestión de Usuarios</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Administración de credenciales de acceso,
                    perfiles y asignación de roles al personal.</p>
            </div>
            <div>
                <button onclick="abrirModalCrear()"
                    class="px-5 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-500 rounded-xl shadow-xs transition-all cursor-pointer flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z">
                        </path>
                    </svg>
                    Nuevo Usuario
                </button>
            </div>
        </header>

        <div
            class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/20">
                            <th class="px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Titular /
                                Cédula</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Correo
                                Electronico</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Rol de
                                Sistema</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Estado
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700 text-sm">
                        <?php if (!empty($listaUsuarios)): ?>
                            <?php foreach ($listaUsuarios as $usuario): ?>
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/10 transition-colors">
                                    <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                        <div class="font-semibold">
                                            <?= htmlspecialchars($usuario['primer_nombre'] . ' ' . $usuario['primer_apellido']) ?>
                                        </div>
                                        <div class="text-xs text-gray-400 mt-0.5">
                                            V-<?= htmlspecialchars($usuario['cedula_identidad']) ?></div>
                                    </td>
                                    <td class="px-6 py-4 text-gray-700 dark:text-gray-200 font-medium text-sm">
                                        <?= htmlspecialchars($usuario['email_user']) ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span
                                            class="px-2.5 py-1 rounded-md text-xs font-medium bg-purple-50 dark:bg-purple-950/30 text-purple-700 dark:text-purple-400 border border-purple-100 dark:border-purple-900/30">
                                            <?= htmlspecialchars($usuario['rol']) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php if (strtoupper($usuario['estatus']) === 'ACTIVO'): ?>
                                            <span
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900/30">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                <?= htmlspecialchars($usuario['estatus']) ?>
                                            </span>
                                        <?php else: ?>
                                            <span
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-rose-50 dark:bg-rose-950/30 text-rose-700 dark:text-rose-400 border border-rose-100 dark:border-rose-900/30">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                                <?= htmlspecialchars($usuario['estatus']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="px-6 py-10 text-center text-gray-400 dark:text-gray-500">
                                    No se encontraron usuarios registrados en el sistema.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="modal-usuario"
            class="fixed inset-0 z-50 hidden bg-gray-900/50 dark:bg-gray-950/70 backdrop-blur-xs flex items-center justify-center p-4">
            <div
                class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 w-full max-w-lg overflow-hidden transform transition-all">
                <div
                    class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between bg-gray-50/50 dark:bg-gray-900/20">
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">Registrar Nuevo Usuario</h3>
                    <button type="button" onclick="cerrarModal()"
                        class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div id="toast-container"
                    class="fixed top-5 right-5 z-50 flex flex-col gap-3 pointer-events-none max-w-sm w-full"></div>

                <form id="form-usuario" class="p-6 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Asignar a
                            Persona *</label>
                        <select name="id_persona" id="select-persona" required
                            class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm text-gray-900 dark:text-white focus:outline-hidden focus:border-blue-500">
                            <option value="">-- Seleccione una Persona --</option>
                            <?php if (!empty($listaPersonas)):
                                foreach ($listaPersonas as $p): ?>
                                    <option value="<?= $p['id_persona'] ?>"
                                        data-email="<?= htmlspecialchars($p['email'] ?? '') ?>">
                                        V-<?= $p['cedula_identidad'] ?> - <?= $p['primer_nombre'] ?>
                                        <?= $p['primer_apellido'] ?>
                                    </option>
                                <?php endforeach; endif; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Nombre de
                            Usuario (Login / Correo) *</label>
                        <input type="text" name="usuario" id="input-usuario" required placeholder="correo@ejemplo.com"
                            class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm text-gray-900 dark:text-white focus:outline-hidden focus:border-blue-500">
                    </div>

                    <div>
                        <label
                            class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Contraseña
                            por Defecto</label>
                        <input type="text" name="password" value="Cliente2026*" readonly
                            class="w-full px-4 py-2.5 bg-gray-100 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm text-gray-500 dark:text-gray-400 cursor-not-allowed focus:outline-hidden">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Rol de
                            Acceso *</label>
                        <select name="id_rol" required
                            class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm text-gray-900 dark:text-white focus:outline-hidden focus:border-blue-500">
                            <option value="">-- Seleccione un Rol --</option>
                            <?php if (!empty($listaRoles)):
                                foreach ($listaRoles as $r): ?>
                                    <option value="<?= $r['id'] ?>"><?= $r['nombre_rol'] ?></option>
                                <?php endforeach; endif; ?>
                        </select>
                    </div>

                    <div class="pt-4 border-t border-gray-100 dark:border-gray-700 flex justify-end space-x-3">
                        <button type="button" onclick="cerrarModal()"
                            class="px-4 py-2 border border-gray-200 dark:border-gray-700 text-sm font-semibold rounded-xl text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer">
                            Cancelar
                        </button>
                        <button type="submit"
                            class="px-4 py-2 text-sm font-semibold rounded-xl text-white bg-blue-600 hover:bg-blue-500 shadow-xs cursor-pointer">
                            Guardar Usuario
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script src="../public/js/usuarios.js"></script>
</body>

</html>