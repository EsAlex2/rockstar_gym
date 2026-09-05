<?php
require_once __DIR__ . '/help.php';

$user_role = $_SESSION['user_role'] ?? 'Invitado';

// Métricas de resumen
$totalPersonas = count($listaPersonas);
$activasPersonas = 0;
$conUsuario = 0;
$sinUsuario = 0;

foreach ($listaPersonas as $p) {
    if (($p['estatus'] ?? '') === 'Activo') {
        $activasPersonas++;
    }
    if (!empty($p['id_usuario'])) {
        $conUsuario++;
    } else {
        $sinUsuario++;
    }
}
?>
<!DOCTYPE html>
<html lang="es" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personas - <?= SITE_NAME ?></title>
    <?php require_once __DIR__ . '/../components/header_theme.php'; ?>
</head>

<body class="bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-100 min-h-screen flex">

    <?php require_once __DIR__ . '/../components/sidebar.php'; ?>

    <main class="flex-1 p-6 md:p-10 overflow-y-auto">
        <!-- HEADER PRINCIPAL -->
        <header class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Gestión de Personas</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Padrón central de datos personales, información de contacto y vinculación con cuentas de usuario del sistema.
                </p>
            </div>
            <div>
                <button onclick="abrirModalCrear()" class="px-5 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-500 active:bg-blue-700 rounded-xl shadow-md hover:shadow-lg transition-all cursor-pointer flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>Nueva Persona</span>
                </button>
            </div>
        </header>

        <!-- CONTENEDOR TOAST PARA NOTIFICACIONES -->
        <div id="toast-container" class="fixed top-5 right-5 z-50 flex flex-col gap-3 pointer-events-none max-w-sm w-full"></div>

        <!-- TARJETAS DE MÉTRICAS (KPIs) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
            <!-- Total Personas -->
            <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-xs flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-400">Total Personas</span>
                    <h3 id="kpi-total" class="text-2xl font-bold text-gray-900 dark:text-white"><?= $totalPersonas ?></h3>
                </div>
            </div>

            <!-- Personas Activas -->
            <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-xs flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-400">Activas</span>
                    <h3 id="kpi-activas" class="text-2xl font-bold text-gray-900 dark:text-white"><?= $activasPersonas ?></h3>
                </div>
            </div>

            <!-- Con Usuario -->
            <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-xs flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-purple-50 dark:bg-purple-950/40 text-purple-600 dark:text-purple-400 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-400">Con Usuario</span>
                    <h3 id="kpi-con-usuario" class="text-2xl font-bold text-gray-900 dark:text-white"><?= $conUsuario ?></h3>
                </div>
            </div>

            <!-- Sin Usuario (Pendientes) -->
            <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-xs flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                    </svg>
                </div>
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-400">Sin Usuario</span>
                    <h3 id="kpi-sin-usuario" class="text-2xl font-bold text-gray-900 dark:text-white"><?= $sinUsuario ?></h3>
                </div>
            </div>
        </div>

        <!-- BARRA DE BÚSQUEDA Y FILTROS -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-4 mb-6 shadow-xs flex flex-col md:flex-row gap-4 items-center justify-between">
            <div class="relative w-full md:w-96">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <input type="text" id="filtro-busqueda" placeholder="Buscar por cédula, nombre, correo o teléfono..." 
                    class="w-full pl-10 pr-4 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-hidden focus:border-blue-500 transition-colors">
            </div>

            <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
                <!-- Filtro Estado -->
                <select id="filtro-estatus" class="px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-xs font-medium text-gray-700 dark:text-gray-300 focus:outline-hidden focus:border-blue-500">
                    <option value="">Todos los Estados</option>
                    <option value="Activo">Solo Activos</option>
                    <option value="Inactivo">Solo Inactivos</option>
                </select>

                <!-- Filtro Usuario -->
                <select id="filtro-usuario" class="px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-xs font-medium text-gray-700 dark:text-gray-300 focus:outline-hidden focus:border-blue-500">
                    <option value="">Todas las Personas</option>
                    <option value="con_usuario">Con Usuario Asignado</option>
                    <option value="sin_usuario">Sin Cuenta de Usuario</option>
                </select>

                <!-- Filtro Género -->
                <select id="filtro-genero" class="px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-xs font-medium text-gray-700 dark:text-gray-300 focus:outline-hidden focus:border-blue-500">
                    <option value="">Todos los Géneros</option>
                    <?php if (!empty($listaGeneros)): ?>
                        <?php foreach ($listaGeneros as $g): ?>
                            <option value="<?= htmlspecialchars($g['descripcion']) ?>"><?= htmlspecialchars($g['descripcion']) ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>

                <!-- Botón Limpiar Filtros -->
                <button id="btn-limpiar-filtros" type="button" class="px-3 py-2 text-xs font-medium text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl transition-colors cursor-pointer" title="Restablecer filtros">
                    Limpiar
                </button>
            </div>
        </div>

        <!-- TABLA PRINCIPAL DE PERSONAS -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/20">
                            <th class="px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Cédula / Identidad</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Nombre Completo</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Contacto</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Género</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Estado</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Cuenta de Usuario</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-personas-body" class="divide-y divide-gray-100 dark:divide-gray-700 text-sm">
                        <?php if (!empty($listaPersonas)): ?>
                            <?php foreach ($listaPersonas as $persona): ?>
                                <?php 
                                    $nombreCompleto = trim(
                                        $persona['primer_nombre'] . ' ' . 
                                        ($persona['segundo_nombre'] ?? '') . ' ' . 
                                        $persona['primer_apellido'] . ' ' . 
                                        ($persona['segundo_apellido'] ?? '')
                                    );
                                    $tieneUsuario = !empty($persona['id_usuario']);
                                    $isActivo = strtolower($persona['estatus'] ?? '') === 'activo';
                                ?>
                                <tr class="fila-persona hover:bg-gray-50/50 dark:hover:bg-gray-900/10 transition-colors"
                                    data-cedula="<?= strtolower($persona['cedula_identidad']) ?>"
                                    data-nombre="<?= strtolower($nombreCompleto) ?>"
                                    data-email="<?= strtolower($persona['email'] ?? '') ?>"
                                    data-telefono="<?= strtolower($persona['telefono'] ?? '') ?>"
                                    data-estatus="<?= $persona['estatus'] ?>"
                                    data-usuario="<?= $tieneUsuario ? 'con_usuario' : 'sin_usuario' ?>"
                                    data-genero="<?= $persona['genero'] ?>">
                                    
                                    <!-- Cédula -->
                                    <td class="px-6 py-4 font-semibold text-gray-900 dark:text-white whitespace-nowrap">
                                        <span class="font-mono bg-gray-100 dark:bg-gray-700/60 px-2.5 py-1 rounded-lg text-xs font-bold text-gray-800 dark:text-gray-200">
                                            V-<?= htmlspecialchars($persona['cedula_identidad']) ?>
                                        </span>
                                    </td>

                                    <!-- Nombre Completo -->
                                    <td class="px-6 py-4">
                                        <div class="font-semibold text-gray-900 dark:text-white">
                                            <?= htmlspecialchars($persona['primer_nombre'] . ' ' . $persona['primer_apellido']) ?>
                                        </div>
                                        <?php if (!empty($persona['segundo_nombre']) || !empty($persona['segundo_apellido'])): ?>
                                            <div class="text-xs text-gray-400">
                                                <?= htmlspecialchars(trim(($persona['segundo_nombre'] ?? '') . ' ' . ($persona['segundo_apellido'] ?? ''))) ?>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($persona['direccion_habitacion'])): ?>
                                            <div class="text-[11px] text-gray-400/80 truncate max-w-xs mt-0.5" title="<?= htmlspecialchars($persona['direccion_habitacion']) ?>">
                                                📍 <?= htmlspecialchars($persona['direccion_habitacion']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Contacto -->
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-1.5 text-gray-700 dark:text-gray-300 font-medium">
                                            <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                            </svg>
                                            <span class="truncate max-w-[180px]" title="<?= htmlspecialchars($persona['email']) ?>"><?= htmlspecialchars($persona['email']) ?></span>
                                        </div>
                                        <div class="flex items-center gap-1.5 text-xs text-gray-400 mt-1">
                                            <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                            </svg>
                                            <span><?= htmlspecialchars($persona['telefono']) ?></span>
                                        </div>
                                    </td>

                                    <!-- Género -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2.5 py-1 rounded-md text-xs font-medium bg-blue-50 dark:bg-blue-950/30 text-blue-700 dark:text-blue-400 border border-blue-100 dark:border-blue-900/30">
                                            <?= htmlspecialchars($persona['genero']) ?>
                                        </span>
                                    </td>

                                    <!-- Estado (Switch interactivo) -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" class="sr-only peer" 
                                                onchange="toggleEstatusPersona(<?= $persona['id_persona'] ?>, this.checked)" 
                                                <?= $isActivo ? 'checked' : '' ?>>
                                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-hidden rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-emerald-500"></div>
                                            <span class="ml-2.5 text-xs font-medium <?= $isActivo ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' ?>">
                                                <?= htmlspecialchars($persona['estatus']) ?>
                                            </span>
                                        </label>
                                    </td>

                                    <!-- Cuenta de Usuario -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if ($tieneUsuario): ?>
                                            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-purple-50 dark:bg-purple-950/30 text-purple-700 dark:text-purple-300 border border-purple-100 dark:border-purple-900/30"
                                                 title="Usuario: <?= htmlspecialchars($persona['email_user'] ?? '') ?>">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                </svg>
                                                <span><?= htmlspecialchars($persona['rol_usuario'] ?? 'Usuario') ?></span>
                                            </div>
                                        <?php else: ?>
                                            <a href="usuarios.php?persona_id=<?= $persona['id_persona'] ?>" 
                                               class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-semibold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-950/40 hover:bg-blue-100 dark:hover:bg-blue-900/60 border border-blue-200 dark:border-blue-800 transition-colors"
                                               title="Crear cuenta de usuario para <?= htmlspecialchars($nombreCompleto) ?>">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                                                </svg>
                                                <span>+ Crear Usuario</span>
                                            </a>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Acciones -->
                                    <td class="px-6 py-4 text-right whitespace-nowrap">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <button onclick='abrirModalEditar(<?= htmlspecialchars(json_encode($persona), ENT_QUOTES, 'UTF-8') ?>)'
                                                class="p-2 text-gray-400 hover:text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg transition-colors cursor-pointer"
                                                title="Editar Datos de Persona">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z">
                                                    </path>
                                                </svg>
                                            </button>
                                            <button onclick="confirmarEliminarPersona(<?= $persona['id_persona'] ?>, '<?= htmlspecialchars(addslashes($nombreCompleto)) ?>', <?= $tieneUsuario ? 'true' : 'false' ?>)"
                                                class="p-2 text-gray-400 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/30 rounded-lg transition-colors cursor-pointer"
                                                title="Eliminar o Inactivar Persona">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                    </path>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr id="sin-registros-row">
                                <td colspan="7" class="px-6 py-12 text-center text-gray-400 dark:text-gray-500">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <svg class="w-8 h-8 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        <span>No se encontraron personas registradas en el sistema.</span>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- MODAL FORMULARIO (CREAR / EDITAR PERSONA) -->
        <div id="modal-persona" class="fixed inset-0 z-50 hidden bg-gray-900/50 dark:bg-gray-950/70 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 w-full max-w-2xl overflow-hidden transform transition-all">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between bg-gray-50/50 dark:bg-gray-900/20">
                    <h3 id="modal-persona-titulo" class="text-base font-bold text-gray-900 dark:text-white">Registrar Nueva Persona</h3>
                    <button type="button" onclick="cerrarModal()" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form id="form-persona" class="p-6 space-y-4">
                    <input type="hidden" name="id_persona" id="input-id-persona" value="">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Cédula -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Cédula de Identidad *</label>
                            <input type="text" name="cedula_identidad" id="input-cedula" required placeholder="Ej: 27391753" 
                                class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm text-gray-900 dark:text-white focus:outline-hidden focus:border-blue-500 transition-colors">
                        </div>

                        <!-- Género -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Género *</label>
                            <select name="id_genero" id="select-genero" required 
                                class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm text-gray-900 dark:text-white focus:outline-hidden focus:border-blue-500 transition-colors">
                                <option value="">-- Seleccione --</option>
                                <?php if (!empty($listaGeneros)): ?>
                                    <?php foreach ($listaGeneros as $g): ?>
                                        <option value="<?= $g['id'] ?>"><?= htmlspecialchars($g['descripcion']) ?></option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="1">Masculino</option>
                                    <option value="2">Femenino</option>
                                    <option value="3">Otro</option>
                                <?php endif; ?>
                            </select>
                        </div>

                        <!-- Primer Nombre -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Primer Nombre *</label>
                            <input type="text" name="primer_nombre" id="input-primer-nombre" required placeholder="Ej: Carlos"
                                class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm text-gray-900 dark:text-white focus:outline-hidden focus:border-blue-500 transition-colors">
                        </div>

                        <!-- Segundo Nombre -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Segundo Nombre</label>
                            <input type="text" name="segundo_nombre" id="input-segundo-nombre" placeholder="Opcional"
                                class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm text-gray-900 dark:text-white focus:outline-hidden focus:border-blue-500 transition-colors">
                        </div>

                        <!-- Primer Apellido -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Primer Apellido *</label>
                            <input type="text" name="primer_apellido" id="input-primer-apellido" required placeholder="Ej: Pérez"
                                class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm text-gray-900 dark:text-white focus:outline-hidden focus:border-blue-500 transition-colors">
                        </div>

                        <!-- Segundo Apellido -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Segundo Apellido</label>
                            <input type="text" name="segundo_apellido" id="input-segundo-apellido" placeholder="Opcional"
                                class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm text-gray-900 dark:text-white focus:outline-hidden focus:border-blue-500 transition-colors">
                        </div>

                        <!-- Fecha de Nacimiento -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Fecha de Nacimiento *</label>
                            <input type="date" name="fecha_nacimiento" id="input-fecha-nacimiento" required max="<?= date('Y-m-d') ?>"
                                class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm text-gray-900 dark:text-white focus:outline-hidden focus:border-blue-500 transition-colors">
                        </div>

                        <!-- Teléfono -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Teléfono Móvil / Fijo *</label>
                            <input type="tel" name="telefono" id="input-telefono" required placeholder="Ej: 0412-1234567"
                                class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm text-gray-900 dark:text-white focus:outline-hidden focus:border-blue-500 transition-colors">
                        </div>
                    </div>

                    <!-- Correo Electrónico -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Correo Electrónico *</label>
                        <input type="email" name="email" id="input-email" required placeholder="correo@ejemplo.com"
                            class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm text-gray-900 dark:text-white focus:outline-hidden focus:border-blue-500 transition-colors">
                    </div>

                    <!-- Dirección -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Dirección de Habitación *</label>
                        <textarea name="direccion_habitacion" id="input-direccion" rows="2" required placeholder="Av., Calle, Edificio / Casa, Ciudad..."
                            class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm text-gray-900 dark:text-white focus:outline-hidden focus:border-blue-500 transition-colors"></textarea>
                    </div>

                    <!-- Estado (Solo visible en modo edición) -->
                    <div id="campo-estatus-container" class="hidden">
                        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Estado del Registro</label>
                        <select name="id_estatus" id="select-estatus" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm text-gray-900 dark:text-white focus:outline-hidden focus:border-blue-500 transition-colors">
                            <option value="1">Activo</option>
                            <option value="2">Inactivo</option>
                        </select>
                    </div>

                    <div class="pt-4 border-t border-gray-100 dark:border-gray-700 flex justify-end space-x-3">
                        <button type="button" onclick="cerrarModal()" class="px-4 py-2 border border-gray-200 dark:border-gray-700 text-sm font-semibold rounded-xl text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer">
                            Cancelar
                        </button>
                        <button type="submit" id="btn-submit-persona" class="px-5 py-2 text-sm font-semibold rounded-xl text-white bg-blue-600 hover:bg-blue-500 shadow-md hover:shadow-lg transition-all cursor-pointer">
                            Guardar Registro
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL CONFIRMACIÓN DE ELIMINACIÓN / INACTIVACIÓN -->
        <div id="modal-eliminar" class="fixed inset-0 z-50 hidden bg-gray-900/50 dark:bg-gray-950/70 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 w-full max-w-md overflow-hidden transform transition-all p-6 text-center">
                <div class="w-12 h-12 rounded-full bg-rose-100 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">¿Confirmar Acción?</h3>
                <p id="eliminar-mensaje-texto" class="text-sm text-gray-500 dark:text-gray-400 mb-6">
                    ¿Estás seguro de que deseas eliminar este registro?
                </p>
                <div class="flex items-center justify-center gap-3">
                    <button type="button" onclick="cerrarModalEliminar()" class="px-4 py-2.5 border border-gray-200 dark:border-gray-700 text-sm font-semibold rounded-xl text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer">
                        Cancelar
                    </button>
                    <button type="button" id="btn-confirmar-eliminar" class="px-5 py-2.5 text-sm font-semibold rounded-xl text-white bg-rose-600 hover:bg-rose-500 shadow-md hover:shadow-lg transition-all cursor-pointer">
                        Confirmar
                    </button>
                </div>
            </div>
        </div>
    </main>

    <script src="../public/js/personas.js"></script>
</body>

</html>