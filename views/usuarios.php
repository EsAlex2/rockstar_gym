<?php
require_once __DIR__ . '/help.php';
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
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Administración de credenciales, accesos y perfiles del sistema.</p>
            </div>
            <div>
                <button onclick="abrirModalCrear()" class="px-5 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-500 rounded-xl shadow-xs transition-all cursor-pointer flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Nuevo Usuario
                </button>
            </div>
        </header>

        

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/20">
                            <th class="px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Identidad / Usuario</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Correo Electrónico</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Rol de Acceso</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Estado</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700 text-sm">
                        <?php if (!empty($listaUsuarios)): ?>
                            <?php foreach ($listaUsuarios as $user): ?>
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/10 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="font-semibold text-gray-900 dark:text-white"><?= htmlspecialchars($user['username']) ?></div>
                                        <div class="text-xs text-gray-400 mt-0.5">ID Persona: <?= htmlspecialchars($user['id_persona']) ?></div>
                                    </td>
                                    <td class="px-6 py-4 text-gray-600 dark:text-gray-300">
                                        <?= htmlspecialchars($user['email_user']) ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2.5 py-1 rounded-md text-xs font-medium bg-blue-50 dark:bg-blue-950/30 text-blue-700 dark:text-blue-400 border border-blue-100 dark:border-blue-900/30">
                                            <?= htmlspecialchars($mapaRoles[$user['id_rol']] ?? 'Sin Rol (' . $user['id_rol'] . ')') ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php if ((int)$user['id_estatus'] === 1): ?>
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900/30">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Activo
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-rose-50 dark:bg-rose-950/30 text-rose-700 dark:text-rose-400 border border-rose-100 dark:border-rose-900/30">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Inactivo
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 text-right space-x-2">
                                        <button onclick='abrirModalEditar(<?= json_encode($user) ?>)' class="p-1.5 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg text-gray-500 dark:text-gray-400 hover:text-blue-600 cursor-pointer inline-flex" title="Editar Parámetros">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </button>
                                        <button onclick="abrirModalPassword('<?= $user['username'] ?>', '<?= $user['email_user'] ?>')" class="p-1.5 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg text-gray-500 dark:text-gray-400 hover:text-amber-600 cursor-pointer inline-flex" title="Restablecer Contraseña">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-gray-400 dark:text-gray-500">
                                    No se encontraron cuentas de usuario registradas en el sistema.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="modal-usuario" class="fixed inset-0 z-50 hidden bg-gray-900/50 dark:bg-gray-950/70 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 w-full max-w-md overflow-hidden transform transition-all">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between bg-gray-50/50 dark:bg-gray-900/20">
                    <h3 id="modal-titulo" class="text-base font-bold text-gray-900 dark:text-white">Formulario de Usuario</h3>
                    <button type="button" onclick="cerrarModal()" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div id="toast-container" class="fixed top-5 right-5 z-50 flex flex-col gap-3 pointer-events-none max-w-sm w-full"></div>

                <form id="form-usuario" class="p-6 space-y-4">
                    <input type="hidden" id="input-id-usuario" name="id_usuario" value="">

                    <div id="grupo-persona" class="space-y-3 p-3 bg-gray-50 dark:bg-gray-900/50 rounded-xl border border-gray-100 dark:border-gray-800">
                        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Asociar Persona (Cédula)</label>
                        <div class="flex gap-2">
                            <input type="text" id="buscar-cedula" placeholder="Ej: V-12345678" class="flex-1 px-3 py-2 text-sm bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white focus:outline-hidden focus:border-blue-500">
                            <button type="button" onclick="buscarPersona()" class="px-3 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-lg text-sm font-semibold transition-colors cursor-pointer">Buscar</button>
                        </div>
                        <input type="hidden" id="input-persona" name="id_persona" value="">
                        
                        <div id="info-persona-encontrada" class="hidden p-2.5 bg-emerald-500/10 border border-emerald-500/20 rounded-lg flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <div class="text-xs">
                                <p id="txt-persona-nombre" class="font-semibold text-emerald-800 dark:text-emerald-400"></p>
                                <p id="txt-persona-correo" class="text-gray-400 mt-0.5"></p>
                            </div>
                        </div>
                    </div>

                    <div id="grupo-username" class="hidden">
                        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Nombre de Usuario</label>
                        <input type="text" id="input-username" name="username" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm text-gray-900 dark:text-white focus:outline-hidden focus:border-blue-500">
                    </div>

                    <div id="grupo-email">
                        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Correo Electrónico</label>
                        <input type="email" id="input-email" name="email" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm text-gray-900 dark:text-white focus:outline-hidden focus:border-blue-500">
                    </div>

                    <div id="grupo-rol">
                        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Rol de Acceso</label>
                        <select id="input-rol" name="id_rol" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm text-gray-900 dark:text-white focus:outline-hidden focus:border-blue-500">
                            <option value="">-- Seleccione un Perfil --</option>
                            <?php if (!empty($rolesCrudos)): ?>
                                <?php foreach ($rolesCrudos as $r): 
                                    $idR = $r['id_rol'] ?? $r['id'] ?? null;
                                    $nomR = $r['nombre_rol'] ?? $r['nombre'] ?? 'Rol';
                                ?>
                                    <option value="<?= $idR ?>"><?= htmlspecialchars($nomR) ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div id="grupo-estatus" class="hidden">
                        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Estatus de la Cuenta</label>
                        <select id="input-estatus" name="id_estatus" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm text-gray-900 dark:text-white focus:outline-hidden focus:border-blue-500">
                            <option value="1">Activo</option>
                            <option value="2">Inactivo</option>
                        </select>
                    </div>

                    <div id="grupo-password" class="hidden">
                        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Nueva Contraseña</label>
                        <input type="password" id="input-password" name="password" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm text-gray-900 dark:text-white focus:outline-hidden focus:border-blue-500">
                    </div>

                    <div class="pt-4 border-t border-gray-100 dark:border-gray-700 flex justify-end space-x-3">
                        <button type="button" onclick="cerrarModal()" class="px-4 py-2 border border-gray-200 dark:border-gray-700 text-sm font-semibold rounded-xl text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer">
                            Cancelar
                        </button>
                        <button type="submit" class="px-4 py-2 text-sm font-semibold rounded-xl text-white bg-blue-600 hover:bg-blue-500 shadow-xs cursor-pointer">
                            Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script src="../public/js/usuarios.js"></script>
</body>
</html>