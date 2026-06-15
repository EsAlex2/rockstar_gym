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
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Administración de credenciales, accesos y
                    perfiles del sistema.</p>
            </div>
            <div>
                <button onclick="abrirModalCrear()"
                    class="px-5 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-500 rounded-xl shadow-sm transition-all cursor-pointer">
                    + Nuevo Usuario
                </button>
            </div>
        </header>

        <div
            class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr
                            class="border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 text-gray-400 font-medium uppercase text-xs tracking-wider">
                            <th class="p-4 pl-6">Usuario</th>
                            <th class="p-4">Correo Electrónico</th>
                            <th class="p-4">Rol</th>
                            <th class="p-4">Estatus</th>
                            <th class="p-4 pr-6 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        <?php if (empty($listaUsuarios)): ?>
                            <tr>
                                <td colspan="5" class="p-8 text-center text-gray-400">No hay usuarios registrados en el
                                    sistema.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($listaUsuarios as $user): ?>
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition-all">
                                    <td class="p-4 pl-6 font-semibold text-gray-900 dark:text-white">
                                        <?= htmlspecialchars($user['username'] ?? 'Sin asignar') ?>
                                    </td>
                                    <td class="p-4 text-gray-500 dark:text-gray-400">
                                        <?= htmlspecialchars($user['email_user'] ?? '') ?>
                                    </td>
                                    <td class="p-4">
                                        <span
                                            class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 border border-blue-100 dark:border-blue-900/30">
                                            <?= htmlspecialchars($mapaRoles[$user['id_rol']] ?? 'Usuario Común') ?>
                                        </span>
                                    </td>
                                    <td class="p-4">
                                        <?php if (($user['id_estatus'] ?? 1) == 1): ?>
                                            <span
                                                class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900/30">Activo</span>
                                        <?php else: ?>
                                            <span
                                                class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400">Inactivo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-4 pr-6 text-right space-x-2">
                                        <button onclick='abrirModalEditar(<?= json_encode($user) ?>)'
                                            class="text-blue-500 hover:text-blue-600 dark:hover:text-blue-400 font-medium cursor-pointer">
                                            Editar
                                        </button>
                                        <button
                                            onclick="abrirModalPassword('<?= htmlspecialchars($user['username'] ?? '') ?>', '<?= htmlspecialchars($user['email_user'] ?? '') ?>')"
                                            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 font-medium cursor-pointer">
                                            Seguridad
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <div id="modal-usuario"
        class="fixed inset-0 z-50 hidden bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
        <div
            class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-xl max-w-md w-full overflow-hidden transform transition-all">
            <div
                class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-800/50">
                <h3 id="modal-titulo" class="text-lg font-bold text-gray-900 dark:text-white">Formulario Usuario</h3>
                <button onclick="cerrarModal()"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            <form id="form-usuario" onsubmit="enviarFormulario(event)" class="p-6 space-y-4">
                <input type="hidden" id="input-id-usuario" name="id_usuario">

                <div id="grupo-persona">
                    <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">ID Registro
                        Persona</label>
                    <input type="number" id="input-persona" name="id_persona"
                        class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm text-gray-900 dark:text-white focus:outline-hidden focus:border-blue-500">
                </div>

                <div id="grupo-username" class="hidden">
                    <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Nombre de
                        Usuario</label>
                    <input disabled type="text" id="input-username" name="username"
                        class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm text-gray-900 dark:text-white focus:outline-hidden focus:border-blue-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Correo
                        Electrónico</label>
                    <input type="email" id="input-email" name="email" required
                        class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm text-gray-900 dark:text-white focus:outline-hidden focus:border-blue-500">
                </div>

                <div id="grupo-rol">
                    <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Rol
                        Asignado</label>
                    <select id="input-rol" name="id_rol"
                        class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm text-gray-900 dark:text-white focus:outline-hidden focus:border-blue-500">
                        <?php if (!empty($rolesCrudos) && is_array($rolesCrudos)): ?>
                            <?php foreach ($rolesCrudos as $rol): ?>
                                <?php
                                $idOption = $rol['id_rol'] ?? $rol['id'] ?? '';
                                $nombreOption = $rol['nombre_rol'] ?? 'Sin nombre';
                                ?>
                                <option value="<?= htmlspecialchars($idOption) ?>"><?= htmlspecialchars($nombreOption) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="">No hay roles disponibles en el sistema</option>
                        <?php endif; ?>
                    </select>
                </div>

                <div id="grupo-estatus" class="hidden">
                    <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Estado de
                        Cuenta</label>
                    <select id="input-estatus" name="id_estatus"
                        class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm text-gray-900 dark:text-white focus:outline-hidden focus:border-blue-500">
                        <option value="1">Activo</option>
                        <option value="2">Inactivo</option>
                    </select>
                </div>

                <div id="grupo-password" class="hidden">
                    <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Nueva
                        Contraseña</label>
                    <input type="password" id="input-password" name="password"
                        class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm text-gray-900 dark:text-white focus:outline-hidden focus:border-blue-500">
                </div>

                <div class="pt-4 border-t border-gray-100 dark:border-gray-700 flex justify-end space-x-3">
                    <button type="button" onclick="cerrarModal()"
                        class="px-4 py-2 border border-gray-200 dark:border-gray-700 text-sm font-semibold rounded-xl text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer">
                        Cancelar
                    </button>
                    <button type="submit"
                        class="px-4 py-2 text-sm font-semibold rounded-xl text-white bg-blue-600 hover:bg-blue-500 shadow-sm cursor-pointer">
                        Confirmar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let urlAccion = '';

        function abrirModalCrear() {
            urlAccion = 'procesar_usuarios.php?action=crear';
            document.getElementById('modal-titulo').innerText = 'Registrar Nuevo Usuario';
            document.getElementById('form-usuario').reset();

            document.getElementById('grupo-persona').classList.remove('hidden');
            document.getElementById('grupo-rol').classList.remove('hidden');
            document.getElementById('grupo-username').classList.add('hidden');
            document.getElementById('grupo-estatus').classList.add('hidden');
            document.getElementById('grupo-password').classList.add('hidden');

            document.getElementById('modal-usuario').classList.remove('hidden');
        }

        function abrirModalEditar(user) {
            urlAccion = 'procesar_usuarios.php?action=actualizar';
            document.getElementById('modal-titulo').innerText = 'Modificar Parámetros de Usuario';

            // Rellenamos los campos con los datos del usuario seleccionado
            document.getElementById('input-id-usuario').value = user.id_usuario || user.id || '';
            document.getElementById('input-username').value = user.username || '';
            document.getElementById('input-email').value = user.email_user || '';

            // Vincula el ID del rol al select dinámico
            document.getElementById('input-rol').value = user.id_rol || '';
            document.getElementById('input-estatus').value = user.id_estatus || '1';

            // Manejo de visibilidad de campos
            document.getElementById('grupo-persona').classList.add('hidden');
            document.getElementById('grupo-username').classList.remove('hidden');
            document.getElementById('grupo-rol').classList.remove('hidden');
            document.getElementById('grupo-estatus').classList.remove('hidden');
            document.getElementById('grupo-password').classList.add('hidden');

            document.getElementById('modal-usuario').classList.remove('hidden');
        }

        function abrirModalPassword(username, email) {
            urlAccion = 'procesar_usuarios.php?action=cambiar_pass'; 
            document.getElementById('modal-titulo').innerText = 'Restablecer Seguridad / Contraseña';
            document.getElementById('form-usuario').reset();

            document.getElementById('input-username').value = username;
            document.getElementById('input-email').value = email;

            document.getElementById('grupo-persona').classList.add('hidden');
            document.getElementById('grupo-username').classList.add('hidden');
            document.getElementById('grupo-rol').classList.add('hidden');
            document.getElementById('grupo-estatus').classList.add('hidden');
            document.getElementById('grupo-password').classList.remove('hidden');

            document.getElementById('modal-usuario').classList.remove('hidden');
        }

        function cerrarModal() {
            document.getElementById('modal-usuario').classList.add('hidden');
        }

        function enviarFormulario(e) {
            e.preventDefault();
            const formData = new FormData(document.getElementById('form-usuario'));

            fetch(urlAccion, {
                method: 'POST',
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    // Soportamos estructuras tanto true/false directas como estructuradas {status: true}
                    const esExitoso = (data.status === true || data === true || data.status === "true");
                    const mensaje = data.message || "Operación realizada con éxito.";

                    if (esExitoso) {
                        alert(mensaje);
                        location.reload();
                    } else {
                        alert("Error: " + mensaje);
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert("Hubo un error al procesar la solicitud.");
                });
        }
    </script>
</body>

</html>