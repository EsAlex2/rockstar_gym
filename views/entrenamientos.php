<?php require_once __DIR__ . '/help.php'; 
$user_role = $_SESSION['user_role'] ?? 'Invitado';
$roles = ['Root', 'Administrador', 'Entrenador', 'Cliente'];
?>
<!DOCTYPE html>
<html lang="es" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrenamientos - <?= SITE_NAME ?? 'Gimnasio' ?></title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>

<body class="bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-100 min-h-screen flex">

    <?php require_once __DIR__ . '/../components/sidebar.php'; ?>

    <main class="flex-1 p-10 overflow-y-auto">
        <header class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Gestión de Entrenamientos
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Planificación de clases, asignación de
                    instructores académicos y sedes operativas.</p>
            </div>
            <div>
                <?php if(in_array($user_role, [$roles[0], $roles[1], $roles[2]])): ?>
                    <button onclick="abrirModalCrear()"
                        class="px-5 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-500 rounded-xl shadow-xs transition-all cursor-pointer flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Nuevo Entrenamiento
                    </button>
                <?php endif; ?>
            </div>
        </header>

        <div
            class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/20">
                            <th class="px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                                Entrenamiento / Clase</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                                Instructor Asignado</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Sede
                                Complejo</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Estado
                            </th>
                            <?php if(in_array($user_role, [$roles[0], $roles[1]])): ?>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider text-right">
                                Acciones</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody id="tabla-entrenamientos-body" class="divide-y divide-gray-100 dark:divide-gray-700 text-sm">
                        <?php if (!empty($listaEntrenamientos) && !isset($listaEntrenamientos['error'])): ?>
                            <?php foreach ($listaEntrenamientos as $entrenamiento): ?>
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/10 transition-colors">
                                    <td class="px-6 py-4 font-semibold text-gray-900 dark:text-white">
                                        <?= htmlspecialchars($entrenamiento['nombre_entrenamiento']) ?>
                                        <?php if (!empty($entrenamiento['descripcion'])): ?>
                                            <div class="text-xs text-gray-400 mt-0.5 font-normal truncate max-w-xs">
                                                <?= htmlspecialchars($entrenamiento['descripcion']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 text-gray-700 dark:text-gray-200 font-medium">
                                        <?= htmlspecialchars(($entrenamiento['primer_nombre'] ?? 'N/A') . ' ' . ($entrenamiento['primer_apellido'] ?? '')) ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span
                                            class="px-2.5 py-1 rounded-md text-xs font-medium bg-blue-50 dark:bg-blue-950/30 text-blue-700 dark:text-blue-400 border border-blue-100 dark:border-blue-900/30">
                                            <?= htmlspecialchars($entrenamiento['Sede'] ?? 'No asignada') ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php
                                        $estatus = $entrenamiento['Estatus'] ?? 'Activo';
                                        if (strcasecmp($estatus, 'Activo') === 0):
                                            ?>
                                            <span
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900/30">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Activo
                                            </span>
                                        <?php else: ?>
                                            <span
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-rose-50 dark:bg-rose-950/30 text-rose-700 dark:text-rose-400 border border-rose-100 dark:border-rose-900/30">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Inactivo
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <?php if(in_array($user_role, [$roles[0], $roles[1]])): ?>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <button onclick='abrirModalEditar(<?= json_encode($entrenamiento) ?>)'
                                                class="p-2 text-gray-400 hover:text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg transition-colors cursor-pointer"
                                                title="Editar Entrenamiento">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z">
                                                    </path>
                                                </svg>
                                            </button>
                                            <button onclick="eliminarEntrenamiento(<?= $entrenamiento['id'] ?>)"
                                                class="p-2 text-gray-400 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/30 rounded-lg transition-colors cursor-pointer"
                                                title="Eliminar Entrenamiento">
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
                                <td colspan="5" class="px-6 py-10 text-center text-gray-400 dark:text-gray-500">
                                    No se encontraron entrenamientos bajo el criterio de búsqueda.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="toast-container"
            class="fixed top-5 right-5 z-50 flex flex-col gap-3 pointer-events-none max-w-sm w-full"></div>

        <div id="modal-entrenamiento"
            class="fixed inset-0 z-50 hidden bg-gray-900/50 dark:bg-gray-950/70 backdrop-blur-xs flex items-center justify-center p-4">
            <div
                class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 w-full max-w-xl overflow-hidden transform transition-all">
                <div
                    class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between bg-gray-50/50 dark:bg-gray-900/20">
                    <h3 id="modal-titulo" class="text-base font-bold text-gray-900 dark:text-white">Registrar Nuevo
                        Entrenamiento</h3>
                    <button type="button" onclick="cerrarModal()"
                        class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form id="form-entrenamiento" class="p-6 space-y-4">
                    <input type="hidden" name="id_entrenamiento" id="id_entrenamiento">
                    <div>
                        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Nombre
                            del Entrenamiento *</label>
                        <input type="text" name="nombre_entrenamiento" id="input-nombre" required
                            placeholder="Ej: Spinning Pro / Crossfit Avanzado"
                            class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm text-gray-900 dark:text-white focus:outline-hidden focus:border-blue-500">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">
                                Seleccionar Entrenador *
                            </label>
                            <select name="id_entrenador" id="select-entrenador" required
                                class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm text-gray-900 dark:text-white focus:outline-hidden focus:border-blue-500">
                                <option value="">-- Seleccione un entrenador --</option>
                                <?php if (!empty($listaEntrenadores) && !isset($listaEntrenadores['error'])): ?>
                                    <?php foreach ($listaEntrenadores as $ent): 
                                        $id_ent = $ent['id'] ?? $ent['id_entrenador'] ?? null;
                                        $nombre_ent = ($ent['persona'] ?? (($ent['primer_nombre'] ?? '') . ' ' . ($ent['primer_apellido'] ?? '')));
                                        if ($id_ent !== null):
                                    ?>
                                        <option value="<?= $id_ent ?>"><?= htmlspecialchars(trim($nombre_ent)) ?></option>
                                    <?php endif; endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Sede
                                Complejo *</label>
                            <select name="id_sede" id="select-sede" required
                                class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm text-gray-900 dark:text-white focus:outline-hidden focus:border-blue-500">
                                <option value="">-- Seleccione --</option>
                                <?php foreach ($listaSedes as $sede): ?>
                                    <option value="<?= $sede['id'] ?>"><?= htmlspecialchars($sede['sede']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label
                            class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Descripción
                            del Entrenamiento *</label>
                        <textarea name="descripcion" id="input-descripcion" rows="3" required
                            placeholder="Defina los objetivos tácticos, intensidad y herramientas necesarias para el entrenamiento..."
                            class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm text-gray-900 dark:text-white focus:outline-hidden focus:border-blue-500"></textarea>
                    </div>

                    <div class="pt-4 border-t border-gray-100 dark:border-gray-700 flex justify-end space-x-3">
                        <button type="button" onclick="cerrarModal()"
                            class="px-4 py-2 border border-gray-200 dark:border-gray-700 text-sm font-semibold rounded-xl text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer">
                            Cancelar
                        </button>
                        <button type="submit" id="btn-submit"
                            class="px-4 py-2 text-sm font-semibold rounded-xl text-white bg-blue-600 hover:bg-blue-500 shadow-xs cursor-pointer">
                            Guardar Planificación
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script src="../public/js/entrenamientos.js"></script>
</body>

</html>