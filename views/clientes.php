<?php
require_once __DIR__ . '/help.php';
$user_role = $_SESSION['user_role'] ?? 'Invitado';
$roles = ['Root', 'Administrador', 'Entrenador', 'Cliente'];
?>
<!DOCTYPE html>
<html lang="es" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clientes - <?= SITE_NAME ?></title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>

<body class="bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-100 min-h-screen flex">

    <?php require_once __DIR__ . '/../components/sidebar.php'; ?>

    <main class="flex-1 p-10 overflow-y-auto">
        <header class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Gestión de Clientes</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Administración de membresías, códigos de acceso e historial de inscripciones del gimnasio.</p>
            </div>
            <div>
                <!-- Solo los usuarios root, administradores podran crear clientes nuevos -->
                <?php if(in_array($user_role, [$roles[0], $roles[1]])): ?>
                    <button onclick="abrirModalCrear()" class="px-5 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-500 rounded-xl shadow-xs cursor-pointer transition-colors\">
                        Nuevo Cliente
                    </button>
                <?php endif; ?>
            </div>
        </header>


        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/20">
                            <th class="px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider text-left">Código</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider text-left">Cédula</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider text-left">Cliente</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider text-left">Teléfono / Correo</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider text-left">Inscripción</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider text-left">Estado</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-clientes-body" class="divide-y divide-gray-100 dark:divide-gray-700 text-sm">
                        <?php if (!empty($listaClientes)): ?>
                            <?php foreach ($listaClientes as $client): ?>
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/10 transition-colors">
                                    <td class="px-6 py-4">
                                        <span class="font-mono font-bold tracking-wider text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-950/40 px-2.5 py-1 rounded-lg border border-blue-100 dark:border-blue-900/30">
                                            <?= htmlspecialchars($client['codigo_acceso']) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-gray-950 dark:text-gray-100 font-mono text-xs">
                                        <?= htmlspecialchars($client['cedula_identidad']) ?>
                                    </td>
                                    <td class="px-6 py-4 font-semibold text-gray-900 dark:text-white">
                                        <?= htmlspecialchars($client['cliente'] ?? $client['Cliente'] ?? 'Desconocido') ?>
                                    </td>
                                    <td class="px-6 py-4 text-xs">
                                        <div class="text-gray-900 dark:text-gray-200"><?= htmlspecialchars($client['telefono']) ?></div>
                                        <div class="text-gray-400"><?= htmlspecialchars($client['email']) ?></div>
                                    </td>
                                    <td class="px-6 py-4 text-gray-500 dark:text-gray-400 text-xs">
                                        <?= date('d/m/Y', strtotime($client['fecha_inscripcion'])) ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php if (($client['estatus'] ?? $client['Estatus']) === 'Activo'): ?>
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900/30">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Activo
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-rose-50 dark:bg-rose-950/30 text-rose-700 dark:text-rose-400 border border-rose-100 dark:border-rose-900/30">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Inactivo
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <button onclick='abrirModalEditar(<?= json_encode($client, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>)' 
                                                    class="p-1.5 text-blue-600 hover:text-blue-700 hover:bg-blue-50 dark:hover:bg-blue-950/30 rounded-lg transition-colors cursor-pointer" 
                                                    title="Editar">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                                </svg>
                                            </button>
                                            <button onclick="eliminarCliente(<?= $client['id'] ?>)" 
                                                    class="p-1.5 text-rose-600 hover:text-rose-700 hover:bg-rose-50 dark:hover:bg-rose-950/30 rounded-lg transition-colors cursor-pointer" 
                                                    title="Eliminar">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="px-6 py-10 text-center text-gray-400 dark:text-gray-500">
                                    No se encontraron clientes registrados en el sistema.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="modal-cliente" class="fixed inset-0 z-50 hidden bg-gray-900/50 dark:bg-gray-950/70 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 w-full max-w-2xl overflow-hidden transform transition-all">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between bg-gray-50/50 dark:bg-gray-900/20">
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">Inscribir Nuevo Cliente</h3>
                    <button type="button" onclick="cerrarModal()" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div id="toast-container" class="fixed top-5 right-5 z-50 flex flex-col gap-3 pointer-events-none max-w-sm w-full"></div>

                <form id="form-cliente" class="p-6 space-y-4">
                    <input type="hidden" name="id_cliente" id="input-id-cliente" value="">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Cédula de Identidad *</label>
                            <input type="text" name="cedula_identidad" id="input-cedula" required placeholder="Ej: 29571480" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm text-gray-900 dark:text-white focus:outline-hidden focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Género *</label>
                            <select name="id_genero" id="select-genero" required class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm text-gray-900 dark:text-white focus:outline-hidden focus:border-blue-500">
                                <option value="">-- Seleccione --</option>
                                <option value="1">Masculino</option>
                                <option value="2">Femenino</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Primer Nombre *</label>
                            <input type="text" name="primer_nombre" id="input-nombre1" required class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm text-gray-900 dark:text-white focus:outline-hidden focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Segundo Nombre</label>
                            <input type="text" name="segundo_nombre" id="input-nombre2" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm text-gray-900 dark:text-white focus:outline-hidden focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Primer Apellido *</label>
                            <input type="text" name="primer_apellido" id="input-apellido1" required class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm text-gray-900 dark:text-white focus:outline-hidden focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Segundo Apellido</label>
                            <input type="text" name="segundo_apellido" id="input-apellido2" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm text-gray-900 dark:text-white focus:outline-hidden focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Fecha de Nacimiento *</label>
                            <input type="date" name="fecha_nacimiento" id="input-nacimiento" required class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm text-gray-900 dark:text-white focus:outline-hidden focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Teléfono Móvil / Fijo *</label>
                            <input type="tel" name="telefono" id="input-telefono" required placeholder="Ej: 04127698974" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm text-gray-900 dark:text-white focus:outline-hidden focus:border-blue-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Correo Electrónico *</label>
                        <input type="email" name="email" id="input-email" required placeholder="correo@ejemplo.com" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm text-gray-900 dark:text-white focus:outline-hidden focus:border-blue-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Dirección Completa de Habitación *</label>
                        <textarea name="direccion_habitacion" id="input-direccion" rows="2" required class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm text-gray-900 dark:text-white focus:outline-hidden focus:border-blue-500 resize-none"></textarea>
                    </div>

                    <div id="div-estatus" class="hidden">
                        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Estatus del Cliente *</label>
                        <select name="id_estatus" id="select-estatus" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm text-gray-900 dark:text-white focus:outline-hidden focus:border-blue-500">
                            <option value="1">Activo</option>
                            <option value="2">Inactivo</option>
                        </select>
                    </div>

                    <p class="text-xs text-gray-400 italic">El sistema guardará la información y actualizará el perfil del cliente de forma inmediata.</p>

                    <div class="pt-4 border-t border-gray-100 dark:border-gray-700 flex justify-end space-x-3">
                        <button type="button" onclick="cerrarModal()" class="px-4 py-2 border border-gray-200 dark:border-gray-700 text-sm font-semibold rounded-xl text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer">
                            Cancelar
                        </button>
                        <button type="submit" id="btn-submit-cliente" class="px-4 py-2 text-sm font-semibold rounded-xl text-white bg-blue-600 hover:bg-blue-500 shadow-xs cursor-pointer">
                            Inscribir Cliente
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script src="../public/js/clientes.js"></script>
</body>

</html>