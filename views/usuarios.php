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
    <?php require_once __DIR__ . '/../components/header_theme.php'; ?>
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
                            <?php if ($user_role === 'Root' || $user_role === 'Administrador'): ?>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider text-right">
                                Acciones</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody id="tabla-usuarios-body" class="divide-y divide-gray-100 dark:divide-gray-700 text-sm">
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
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" class="sr-only peer" 
                                                onchange="toggleEstatusUsuario(<?= $usuario['id_usuario'] ?>, this.checked)" 
                                                <?= strtoupper($usuario['estatus']) === 'ACTIVO' ? 'checked' : '' ?>>
                                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-hidden rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-emerald-500"></div>
                                            <span class="ml-3 text-xs font-medium <?= strtoupper($usuario['estatus']) === 'ACTIVO' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' ?>">
                                                <?= htmlspecialchars($usuario['estatus']) ?>
                                            </span>
                                        </label>
                                    </td>
                                    <?php if ($user_role === 'Root' || $user_role === 'Administrador'): ?>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <button onclick='abrirModalEditar(<?= json_encode($usuario) ?>)'
                                                class="p-2 text-gray-400 hover:text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg transition-colors cursor-pointer"
                                                title="Editar Usuario">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z">
                                                    </path>
                                                </svg>
                                            </button>
                                            <button onclick="eliminarUsuario(<?= $usuario['id_usuario'] ?>)"
                                                class="p-2 text-gray-400 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/30 rounded-lg transition-colors cursor-pointer"
                                                title="Eliminar Usuario">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                    </path>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                    <?php endif; ?>
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
                    <h3 id="modal-titulo" class="text-base font-bold text-gray-900 dark:text-white">Registrar Nuevo Usuario</h3>
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
                    <input type="hidden" name="id_usuario" id="id_usuario">
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
                        <label id="label-password"
                            class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Contraseña
                            por Defecto</label>
                        <input type="text" name="password" id="input-password" value="Cliente2026*" readonly
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