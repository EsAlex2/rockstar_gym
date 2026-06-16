<?php
require_once __DIR__ . '/help.php';
?>
<!DOCTYPE html>
<html lang="es" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Membresías - <?= SITE_NAME ?></title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>

<body class="bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-100 min-h-screen flex">

    <?php require_once __DIR__ . '/../components/sidebar.php'; ?>

    <main class="flex-1 p-10 overflow-y-auto">
        <header class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Gestión de Membresías</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Control de asignaciones de planes, fechas de
                    vencimiento y estatus de acceso de los clientes.</p>
            </div>
            <div>
                <button type="button" onclick="abrirModalCrear()"
                    class="px-5 py-2.5 text-sm font-semibold text-white bg-orange-600 hover:bg-orange-500 rounded-xl shadow-xs transition-all cursor-pointer flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Asignar Plan a Cliente
                </button>
            </div>
        </header>

        <div
            class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/20">
                            <th class="px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">ID
                                Membresía</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Cliente /
                                Miembro</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Plan
                                Adquirido</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Vigencia
                                (Inicio / Fin)</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Estado
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700 text-sm">
                        <?php if (!empty($listaMembresias)): ?>
                            <?php foreach ($listaMembresias as $membresia): ?>
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/10 transition-colors">
                                    <td class="px-6 py-4 font-semibold text-gray-900 dark:text-white">
                                        #<?= htmlspecialchars($membresia['id']) ?>
                                    </td>
                                    <td class="px-6 py-4 text-gray-700 dark:text-gray-200 font-medium">
                                        <?= htmlspecialchars($membresia['id_cliente']) ?> - Cliente
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-semibold text-gray-900 dark:text-white">
                                            <?= htmlspecialchars($membresia['plan'] ?? 'Plan Standard') ?></div>
                                        <div class="text-xs text-emerald-500 font-medium mt-0.5">
                                            $<?= htmlspecialchars(number_format($membresia['precio'] ?? 0, 2)) ?></div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-xs text-gray-600 dark:text-gray-300"><strong>Desde:</strong>
                                            <?= htmlspecialchars($membresia['fecha_inicio']) ?></div>
                                        <div class="text-xs text-rose-500 font-medium mt-0.5"><strong>Vence:</strong>
                                            <?= htmlspecialchars($membresia['fecha_vencimiento']) ?></div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php if (isset($membresia['id_estatus']) && $membresia['id_estatus'] == 1): ?>
                                            <span
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900/30">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                <?= htmlspecialchars($membresia['estatus'] ?? 'Activo') ?>
                                            </span>
                                        <?php else: ?>
                                            <span
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-rose-50 dark:bg-rose-950/30 text-rose-700 dark:text-rose-400 border border-rose-100 dark:border-rose-900/30">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                                <?= htmlspecialchars($membresia['estatus'] ?? 'Inactivo') ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-gray-400 dark:text-gray-500">
                                    No se encontraron planes asignados a clientes actualmente.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="modal-membresia"
            class="fixed inset-0 z-50 hidden bg-gray-900/50 dark:bg-gray-950/70 backdrop-blur-xs flex items-center justify-center p-4">
            <div
                class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 w-full max-w-lg overflow-hidden transform transition-all">
                <div
                    class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between bg-gray-50/50 dark:bg-gray-900/20">
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">Asignar Nueva Membresía</h3>
                    <button type="button" onclick="cerrarModal()"
                        class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form id="form-membresia" class="p-6 space-y-4">
                    <div>
                        <label
                            class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Seleccionar
                            Cliente *</label>
                        <select name="id_cliente" required
                            class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm text-gray-900 dark:text-white focus:outline-hidden focus:border-orange-500">
                            <option value="">-- Seleccione un cliente --</option>
                            <?php if (!empty($listaClientes)): ?>
                                <?php foreach ($listaClientes as $cli): ?>
                                    <option value="<?= $cli['id'] ?>"> -
                                        <?= htmlspecialchars($cli['id_cliente'] ?? 'Cliente') ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div>
                        <label
                            class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Seleccionar
                            Plan *</label>
                        <select name="id_plan" required
                            class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm text-gray-900 dark:text-white focus:outline-hidden focus:border-orange-500">
                            <option value="">-- Seleccione un plan --</option>
                            <?php if (!empty($listaPlanesDisponibles)): ?>
                                <?php foreach ($listaPlanesDisponibles as $pl): ?>
                                    <option value="<?= $pl['id'] ?>"><?= htmlspecialchars($pl['nombre_plan']) ?>
                                        ($<?= $pl['precio'] ?>)</option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Fecha de
                            Inicio (Opcional, por defecto hoy)</label>
                        <input type="date" name="fecha_inicio"
                            class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm text-gray-900 dark:text-white focus:outline-hidden focus:border-orange-500">
                    </div>

                    <div class="pt-4 border-t border-gray-100 dark:border-gray-700 flex justify-end space-x-3">
                        <button type="button" onclick="cerrarModal()"
                            class="px-4 py-2 border border-gray-200 dark:border-gray-700 text-sm font-semibold rounded-xl text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer">
                            Cancelar
                        </button>
                        <button type="submit"
                            class="px-4 py-2 text-sm font-semibold rounded-xl text-white bg-orange-600 hover:bg-orange-500 shadow-xs cursor-pointer transition-all">
                            Guardar Membresía
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div id="toast-container" class="fixed top-5 right-5 z-50 flex flex-col gap-3 pointer-events-none max-w-md">
        </div>
    </main>

    <script src="../public/js/membresias.js" defer></script>
</body>

</html>