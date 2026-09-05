<?php
require_once __DIR__ . '/help.php';

// Obtener datos adicionales para los Selects del administrador
$listaEntrenamientos = [];
try {
    $resEnt = $entrenamientosCtrl->listarEntrenamientos();
    if (is_string($resEnt)) { $resEnt = json_decode($resEnt, true); }
    if (is_array($resEnt)) {
        $estadoExito = $resEnt['status'] ?? $resEnt['success'] ?? false;
        if ($estadoExito && isset($resEnt['data'])) {
            $listaEntrenamientos = $resEnt['data'];
        } else {
            $listaEntrenamientos = [];
        }
    }
} catch (Exception $e) {}
?>
<!DOCTYPE html>
<html lang="es" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Horarios - <?= SITE_NAME ?></title>
    <?php require_once __DIR__ . '/../components/header_theme.php'; ?>
</head>

<body class="bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-100 min-h-screen flex">

    <?php require_once __DIR__ . '/../components/sidebar.php'; ?>

    <main class="flex-1 p-10 overflow-y-auto">
        <header class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Horarios y Cronograma</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    <?php if ($_SESSION['user_role'] === 'Cliente'): ?>
                        Consulta tus clases asignadas y explora los horarios disponibles para inscribirte.
                    <?php elseif ($_SESSION['user_role'] === 'Entrenador'): ?>
                        Revisa las clases bajo tu cargo, sus bloques horarios y los alumnos asignados.
                    <?php else: ?>
                        Administra la planificación horaria de los entrenamientos y las asignaciones de clientes.
                    <?php endif; ?>
                </p>
            </div>
            
            <?php if ($_SESSION['user_role'] === 'Root' || $_SESSION['user_role'] === 'Administrador'): ?>
                <div class="flex flex-wrap gap-2">
                    <button onclick="abrirModalAsignarHorario()"
                        class="px-4 py-2.5 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-500 rounded-xl shadow-xs transition-all cursor-pointer flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Asignar Horario
                    </button>
                    <button onclick="abrirModalAsignarCliente()"
                        class="px-4 py-2.5 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-500 rounded-xl shadow-xs transition-all cursor-pointer flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                        </svg>
                        Inscribir Cliente
                    </button>
                    <button onclick="abrirModalCrearBloque()"
                        class="px-4 py-2.5 text-xs font-semibold text-white bg-purple-600 hover:bg-purple-500 rounded-xl shadow-xs transition-all cursor-pointer flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Nuevo Bloque Horario
                    </button>
                </div>
            <?php endif; ?>
        </header>

        <div id="toast-container" class="fixed top-5 right-5 z-50 flex flex-col gap-3 pointer-events-none max-w-sm w-full"></div>

        <?php if ($_SESSION['user_role'] === 'Cliente'): ?>
            <!-- ================= VISTA CLIENTE ================= -->
            <div class="space-y-8">
                <!-- Mis Entrenamientos -->
                <section>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-orange-500"></span>
                        Mi Cronograma (Clases Inscritas)
                    </h2>
                    
                    <?php if (empty($clienteEntrenamientos)): ?>
                        <div class="bg-white dark:bg-gray-800 p-8 rounded-2xl border border-gray-100 dark:border-gray-700 text-center text-gray-500 dark:text-gray-400">
                            No estás inscrito en ningún entrenamiento actualmente. ¡Explora las opciones de abajo para comenzar!
                        </div>
                    <?php else: ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <?php foreach ($clienteEntrenamientos as $ce): ?>
                                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-6 shadow-xs relative overflow-hidden flex flex-col justify-between">
                                    <div class="absolute top-0 left-0 w-1.5 h-full bg-orange-500"></div>
                                    <div>
                                        <h3 class="text-lg font-bold text-gray-900 dark:text-white"><?= htmlspecialchars($ce['nombre_entrenamiento']) ?></h3>
                                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1"><?= htmlspecialchars($ce['Sede']) ?></p>
                                        <p class="text-sm text-gray-600 dark:text-gray-300 mt-3"><?= htmlspecialchars($ce['descripcion']) ?></p>
                                        
                                        <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700 space-y-2">
                                            <div class="flex items-center gap-2 text-xs font-semibold text-gray-500 dark:text-gray-400">
                                                <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                </svg>
                                                <span>Entrenador: <span class="text-gray-800 dark:text-gray-200"><?= htmlspecialchars($ce['entrenador_nombre']) ?> (<?= htmlspecialchars($ce['especialidad']) ?>)</span></span>
                                            </div>
                                            
                                            <div class="space-y-1.5">
                                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Horario Semanal:</p>
                                                <?php if (empty($ce['horarios'])): ?>
                                                    <p class="text-xs text-gray-400 dark:text-gray-500 italic">Sin horarios asignados todavía.</p>
                                                <?php else: ?>
                                                    <?php foreach ($ce['horarios'] as $h): ?>
                                                        <div class="inline-flex items-center gap-1.5 px-2 py-1 rounded bg-orange-50 dark:bg-orange-950/20 text-orange-700 dark:text-orange-400 text-xs font-mono border border-orange-100 dark:border-orange-900/20 mr-1 mb-1">
                                                            <span><?= $h['dia_semana'] ?>:</span>
                                                            <span><?= date('H:i', strtotime($h['hora_inicio'])) ?> - <?= date('H:i', strtotime($h['hora_fin'])) ?></span>
                                                        </div>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-6">
                                        <button onclick="desinscribirEntrenamiento(<?= $ce['id_entrenamiento'] ?>)"
                                            class="w-full py-2 bg-rose-50 dark:bg-rose-950/20 border border-rose-100 dark:border-rose-900/30 text-rose-600 dark:text-rose-400 font-semibold rounded-xl text-xs hover:bg-rose-500 hover:text-white dark:hover:bg-rose-600 cursor-pointer transition-all">
                                            Retirarme del Entrenamiento
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>

                <!-- Clases Disponibles -->
                <section>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                        Clases Disponibles para Inscripción
                    </h2>
                    
                    <?php if (empty($entrenamientosDisponibles)): ?>
                        <div class="bg-white dark:bg-gray-800 p-8 rounded-2xl border border-gray-100 dark:border-gray-700 text-center text-gray-500 dark:text-gray-400">
                            No hay más clases disponibles para inscribirte en este momento.
                        </div>
                    <?php else: ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <?php foreach ($entrenamientosDisponibles as $ed): ?>
                                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-6 shadow-xs relative overflow-hidden flex flex-col justify-between">
                                    <div class="absolute top-0 left-0 w-1.5 h-full bg-blue-500"></div>
                                    <div>
                                        <h3 class="text-lg font-bold text-gray-900 dark:text-white"><?= htmlspecialchars($ed['nombre_entrenamiento']) ?></h3>
                                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1"><?= htmlspecialchars($ed['Sede']) ?></p>
                                        <p class="text-sm text-gray-600 dark:text-gray-300 mt-3"><?= htmlspecialchars($ed['descripcion']) ?></p>
                                        
                                        <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700 space-y-2">
                                            <div class="flex items-center gap-2 text-xs font-semibold text-gray-500 dark:text-gray-400">
                                                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                </svg>
                                                <span>Entrenador: <span class="text-gray-800 dark:text-gray-200"><?= htmlspecialchars($ed['entrenador_nombre']) ?> (<?= htmlspecialchars($ed['especialidad']) ?>)</span></span>
                                            </div>
                                            
                                            <div class="space-y-1.5">
                                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Horario Semanal:</p>
                                                <?php if (empty($ed['horarios'])): ?>
                                                    <p class="text-xs text-gray-400 dark:text-gray-500 italic">Sin horarios asignados todavía.</p>
                                                <?php else: ?>
                                                    <?php foreach ($ed['horarios'] as $h): ?>
                                                        <div class="inline-flex items-center gap-1.5 px-2 py-1 rounded bg-blue-50 dark:bg-blue-950/20 text-blue-700 dark:text-blue-400 text-xs font-mono border border-blue-100 dark:border-blue-900/20 mr-1 mb-1">
                                                            <span><?= $h['dia_semana'] ?>:</span>
                                                            <span><?= date('H:i', strtotime($h['hora_inicio'])) ?> - <?= date('H:i', strtotime($h['hora_fin'])) ?></span>
                                                        </div>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-6">
                                        <button onclick="inscribirEntrenamiento(<?= $ed['id_entrenamiento'] ?>)"
                                            class="w-full py-2 bg-blue-600 text-white font-semibold rounded-xl text-xs hover:bg-blue-500 shadow-xs cursor-pointer transition-all">
                                            Inscribirme en esta clase
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>
            </div>

        <?php elseif ($_SESSION['user_role'] === 'Entrenador'): ?>
            <!-- ================= VISTA ENTRENADOR ================= -->
            <div class="space-y-8">
                <section>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
                        Mi Programación de Clases
                    </h2>
                    
                    <?php if (empty($clasesEntrenador)): ?>
                        <div class="bg-white dark:bg-gray-800 p-8 rounded-2xl border border-gray-100 dark:border-gray-700 text-center text-gray-500 dark:text-gray-400">
                            No tienes entrenamientos registrados o asignados a tu cargo actualmente.
                        </div>
                    <?php else: ?>
                        <div class="space-y-6">
                            <?php foreach ($clasesEntrenador as $ce): ?>
                                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-6 shadow-xs relative overflow-hidden">
                                    <div class="absolute top-0 left-0 w-1.5 h-full bg-indigo-500"></div>
                                    <div class="flex flex-col lg:flex-row justify-between gap-6">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2">
                                                <h3 class="text-xl font-bold text-gray-900 dark:text-white"><?= htmlspecialchars($ce['nombre_entrenamiento']) ?></h3>
                                                <span class="px-2.5 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-indigo-50 dark:bg-indigo-950/30 text-indigo-700 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-900/30">
                                                    <?= htmlspecialchars($ce['Sede']) ?>
                                                </span>
                                            </div>
                                            <p class="text-sm text-gray-600 dark:text-gray-300 mt-2"><?= htmlspecialchars($ce['descripcion']) ?></p>
                                            
                                            <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
                                                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Mis Horarios Asignados:</p>
                                                <?php if (empty($ce['horarios'])): ?>
                                                    <span class="text-xs text-gray-400 dark:text-gray-500 italic">No hay horarios definidos.</span>
                                                <?php else: ?>
                                                    <?php foreach ($ce['horarios'] as $h): ?>
                                                        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-indigo-50 dark:bg-indigo-950/20 text-indigo-700 dark:text-indigo-400 text-xs font-semibold border border-indigo-100 dark:border-indigo-900/20 mr-2 mb-2">
                                                            <span><?= $h['dia_semana'] ?></span>
                                                            <span class="text-indigo-400 dark:text-indigo-600 font-normal">|</span>
                                                            <span class="font-mono"><?= date('H:i', strtotime($h['hora_inicio'])) ?> - <?= date('H:i', strtotime($h['hora_fin'])) ?></span>
                                                        </div>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        
                                        <div class="w-full lg:w-80 border-t lg:border-t-0 lg:border-l border-gray-100 dark:border-gray-700 pt-6 lg:pt-0 lg:pl-6">
                                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Alumnos Inscritos (<?= count($ce['clientes']) ?>)</h4>
                                            <?php if (empty($ce['clientes'])): ?>
                                                <p class="text-xs text-gray-400 dark:text-gray-500 italic">Aún no hay alumnos inscritos en este entrenamiento.</p>
                                            <?php else: ?>
                                                <ul class="space-y-3 max-h-48 overflow-y-auto pr-2">
                                                    <?php foreach ($ce['clientes'] as $cli): ?>
                                                        <li class="flex items-center justify-between text-xs bg-gray-50 dark:bg-gray-900/50 p-2.5 rounded-xl border border-gray-100 dark:border-gray-800">
                                                            <div>
                                                                <p class="font-bold text-gray-800 dark:text-white"><?= htmlspecialchars($cli['cliente_nombre']) ?></p>
                                                                <p class="text-[10px] text-gray-400 dark:text-gray-500 truncate"><?= htmlspecialchars($cli['email']) ?></p>
                                                            </div>
                                                            <a href="tel:<?= $cli['telefono'] ?>" title="Llamar Alumno"
                                                                class="p-1.5 text-gray-400 hover:text-indigo-500 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-850">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.94.725l.548 2.2a1 1 0 01-.321.988l-1.305.98a10.582 10.582 0 004.872 4.872l.98-1.305a1 1 0 01.988-.321l2.2.548a1 1 0 01.725.94V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                                                </svg>
                                                            </a>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>
            </div>

        <?php else: ?>
            <!-- ================= VISTA ADMIN / ROOT ================= -->
            <div class="space-y-8">
                <!-- Pestañas de control -->
                <div class="border-b border-gray-200 dark:border-gray-700 flex gap-6">
                    <button onclick="switchTab('cronogramas')" id="tab-btn-cronogramas"
                        class="pb-4 text-sm font-bold border-b-2 border-blue-600 text-blue-600 dark:text-blue-400 focus:outline-none cursor-pointer">
                        Cronogramas de Clases
                    </button>
                    <button onclick="switchTab('inscripciones')" id="tab-btn-inscripciones"
                        class="pb-4 text-sm font-bold border-b-2 border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 focus:outline-none cursor-pointer">
                        Inscripciones de Clientes
                    </button>
                    <button onclick="switchTab('bloques')" id="tab-btn-bloques"
                        class="pb-4 text-sm font-bold border-b-2 border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 focus:outline-none cursor-pointer">
                        Bloques Horarios Base
                    </button>
                </div>

                <!-- 1. Pestaña Cronogramas (Horarios de Entrenamientos) -->
                <div id="tab-content-cronogramas" class="space-y-4">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-xs overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/20">
                                        <th class="px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Entrenamiento (Clase)</th>
                                        <th class="px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Día Semanal</th>
                                        <th class="px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Horario</th>
                                        <th class="px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider text-right">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="tabla-cronogramas-body" class="divide-y divide-gray-100 dark:divide-gray-700 text-sm">
                                    <?php if (empty($listaHorariosAsignados)): ?>
                                        <tr>
                                            <td colspan="4" class="px-6 py-10 text-center text-gray-400 dark:text-gray-500">
                                                No hay horarios asignados a entrenamientos actualmente.
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($listaHorariosAsignados as $ha): ?>
                                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/10 transition-colors">
                                                <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">
                                                    <?= htmlspecialchars($ha['nombre_entrenamiento']) ?>
                                                </td>
                                                <td class="px-6 py-4 font-medium">
                                                    <span class="px-2.5 py-0.5 rounded bg-blue-50 dark:bg-blue-950/30 text-blue-700 dark:text-blue-400">
                                                        <?= htmlspecialchars($ha['dia_semana']) ?>
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 font-mono text-gray-600 dark:text-gray-300 font-semibold">
                                                    <?= date('H:i', strtotime($ha['hora_inicio'])) ?> - <?= date('H:i', strtotime($ha['hora_fin'])) ?>
                                                </td>
                                                <td class="px-6 py-4 text-right">
                                                    <button 
                                                        onclick="eliminarHorarioEntrenamiento(<?= $ha['id_entrenamiento'] ?>, <?= $ha['id_horario'] ?>, '<?= $ha['dia_semana'] ?>')"
                                                        class="p-2 text-gray-400 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/30 rounded-lg transition-colors cursor-pointer"
                                                        title="Remover Horario">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                        </svg>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- 2. Pestaña Inscripciones de Clientes -->
                <div id="tab-content-inscripciones" class="hidden space-y-4">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-xs overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/20">
                                        <th class="px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Cliente</th>
                                        <th class="px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Entrenamiento Inscrito</th>
                                        <th class="px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Fecha Registro</th>
                                        <th class="px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider text-right">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="tabla-inscripciones-body" class="divide-y divide-gray-100 dark:divide-gray-700 text-sm">
                                    <?php if (empty($listaTodosClienteEntrenamientos)): ?>
                                        <tr>
                                            <td colspan="4" class="px-6 py-10 text-center text-gray-400 dark:text-gray-500">
                                                No hay clientes inscritos en entrenamientos.
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($listaTodosClienteEntrenamientos as $ins): ?>
                                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/10 transition-colors">
                                                <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">
                                                    <?= htmlspecialchars($ins['cliente_nombre']) ?>
                                                </td>
                                                <td class="px-6 py-4 font-medium text-gray-700 dark:text-gray-300">
                                                    <?= htmlspecialchars($ins['nombre_entrenamiento']) ?>
                                                </td>
                                                <td class="px-6 py-4 text-gray-400">
                                                    <?= date('d/m/Y H:i', strtotime($ins['creado_en'])) ?>
                                                </td>
                                                <td class="px-6 py-4 text-right">
                                                    <button 
                                                        onclick="eliminarInscripcion(<?= $ins['id_cliente'] ?>, <?= $ins['id_entrenamiento'] ?>)"
                                                        class="p-2 text-gray-400 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/30 rounded-lg transition-colors cursor-pointer"
                                                        title="Remover Inscripción">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                        </svg>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- 3. Pestaña Bloques Horarios Base -->
                <div id="tab-content-bloques" class="hidden space-y-4">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-xs overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/20">
                                        <th class="px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">ID</th>
                                        <th class="px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Hora Inicio</th>
                                        <th class="px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Hora Fin</th>
                                        <th class="px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider text-right">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="tabla-bloques-body" class="divide-y divide-gray-100 dark:divide-gray-700 text-sm">
                                    <?php if (empty($listaHorarios)): ?>
                                        <tr>
                                            <td colspan="4" class="px-6 py-10 text-center text-gray-400 dark:text-gray-500">
                                                No hay bloques horarios definidos en el catálogo base.
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($listaHorarios as $h): ?>
                                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/10 transition-colors">
                                                <td class="px-6 py-4 font-mono text-gray-400"><?= $h['id'] ?></td>
                                                <td class="px-6 py-4 font-mono font-bold"><?= date('H:i', strtotime($h['hora_inicio'])) ?></td>
                                                <td class="px-6 py-4 font-mono font-bold"><?= date('H:i', strtotime($h['hora_fin'])) ?></td>
                                                <td class="px-6 py-4 text-right">
                                                    <div class="flex items-center justify-end gap-2">
                                                        <button 
                                                            onclick="abrirModalEditarBloque(<?= $h['id'] ?>, '<?= $h['hora_inicio'] ?>', '<?= $h['hora_fin'] ?>')"
                                                            class="p-2 text-gray-400 hover:text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg transition-colors cursor-pointer"
                                                            title="Editar Bloque">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                                            </svg>
                                                        </button>
                                                        <button 
                                                            onclick="eliminarBloqueHorario(<?= $h['id'] ?>)"
                                                            class="p-2 text-gray-400 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/30 rounded-lg transition-colors cursor-pointer"
                                                            title="Eliminar Bloque">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <!-- Modales para Administrador/Root -->
    <?php if ($_SESSION['user_role'] === 'Root' || $_SESSION['user_role'] === 'Administrador'): ?>
        <!-- 1. Modal Asignar Horario a Entrenamiento -->
        <div id="modal-asignar-horario"
            class="fixed inset-0 z-50 hidden bg-gray-900/50 dark:bg-gray-950/70 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 w-full max-w-md overflow-hidden transform transition-all">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between bg-gray-50/50 dark:bg-gray-900/20">
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">Asignar Horario a Entrenamiento</h3>
                    <button type="button" onclick="cerrarModal('modal-asignar-horario')" class="text-gray-400 hover:text-gray-300 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <form id="form-asignar-horario" class="p-6 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Entrenamiento *</label>
                        <select name="id_entrenamiento" required class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm text-gray-900 dark:text-white focus:outline-hidden">
                            <option value="">Selecciona una clase...</option>
                            <?php foreach ($listaEntrenamientos as $e): ?>
                                <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['nombre_entrenamiento']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Bloque Horario *</label>
                        <select name="id_horario" required class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm text-gray-900 dark:text-white focus:outline-hidden">
                            <option value="">Selecciona bloque de horas...</option>
                            <?php foreach ($listaHorarios as $lh): ?>
                                <option value="<?= $lh['id'] ?>"><?= date('H:i', strtotime($lh['hora_inicio'])) ?> - <?= date('H:i', strtotime($lh['hora_fin'])) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Día de la semana *</label>
                        <select name="dia_semana" required class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm text-gray-900 dark:text-white focus:outline-hidden">
                            <option value="">Selecciona un día...</option>
                            <option value="Lunes">Lunes</option>
                            <option value="Martes">Martes</option>
                            <option value="Miercoles">Miércoles</option>
                            <option value="Jueves">Jueves</option>
                            <option value="Viernes">Viernes</option>
                            <option value="Sabado">Sábado</option>
                            <option value="Domingo">Domingo</option>
                        </select>
                    </div>
                    <div class="pt-4 border-t border-gray-100 dark:border-gray-700 flex justify-end space-x-3">
                        <button type="button" onclick="cerrarModal('modal-asignar-horario')"
                            class="px-4 py-2 border border-gray-200 dark:border-gray-700 text-sm font-semibold rounded-xl text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer">
                            Cancelar
                        </button>
                        <button type="submit"
                            class="px-4 py-2 text-sm font-semibold rounded-xl text-white bg-blue-600 hover:bg-blue-500 shadow-xs cursor-pointer">
                            Asignar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 2. Modal Asignar / Inscribir Cliente a Entrenamiento -->
        <div id="modal-asignar-cliente"
            class="fixed inset-0 z-50 hidden bg-gray-900/50 dark:bg-gray-950/70 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 w-full max-w-md overflow-hidden transform transition-all">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between bg-gray-50/50 dark:bg-gray-900/20">
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">Inscribir Cliente en Entrenamiento</h3>
                    <button type="button" onclick="cerrarModal('modal-asignar-cliente')" class="text-gray-400 hover:text-gray-300 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <form id="form-asignar-cliente" class="p-6 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Cliente *</label>
                        <select name="id_cliente" required class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm text-gray-900 dark:text-white focus:outline-hidden">
                            <option value="">Selecciona un cliente...</option>
                            <?php foreach ($listaClientes as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['primer_nombre'] . ' ' . $c['primer_apellido']) ?> (C.I: <?= htmlspecialchars($c['cedula_identidad']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Entrenamiento *</label>
                        <select name="id_entrenamiento" required class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm text-gray-900 dark:text-white focus:outline-hidden">
                            <option value="">Selecciona una clase...</option>
                            <?php foreach ($listaEntrenamientos as $e): ?>
                                <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['nombre_entrenamiento']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="pt-4 border-t border-gray-100 dark:border-gray-700 flex justify-end space-x-3">
                        <button type="button" onclick="cerrarModal('modal-asignar-cliente')"
                            class="px-4 py-2 border border-gray-200 dark:border-gray-700 text-sm font-semibold rounded-xl text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer">
                            Cancelar
                        </button>
                        <button type="submit"
                            class="px-4 py-2 text-sm font-semibold rounded-xl text-white bg-emerald-600 hover:bg-emerald-500 shadow-xs cursor-pointer">
                            Inscribir
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 3. Modal Crear / Editar Bloque Horario Base -->
        <div id="modal-bloque-horario"
            class="fixed inset-0 z-50 hidden bg-gray-900/50 dark:bg-gray-950/70 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 w-full max-w-md overflow-hidden transform transition-all">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between bg-gray-50/50 dark:bg-gray-900/20">
                    <h3 id="modal-bloque-titulo" class="text-base font-bold text-gray-900 dark:text-white">Registrar Nuevo Bloque Horario</h3>
                    <button type="button" onclick="cerrarModal('modal-bloque-horario')" class="text-gray-400 hover:text-gray-300 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <form id="form-bloque-horario" class="p-6 space-y-4">
                    <input type="hidden" name="id_horario" id="input-id-horario" value="">
                    <div>
                        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Hora de Inicio *</label>
                        <input type="time" name="hora_inicio" id="input-hora-inicio" required
                            class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm text-gray-900 dark:text-white focus:outline-hidden">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Hora de Fin *</label>
                        <input type="time" name="hora_fin" id="input-hora-fin" required
                            class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm text-gray-900 dark:text-white focus:outline-hidden">
                    </div>
                    <div class="pt-4 border-t border-gray-100 dark:border-gray-700 flex justify-end space-x-3">
                        <button type="button" onclick="cerrarModal('modal-bloque-horario')"
                            class="px-4 py-2 border border-gray-200 dark:border-gray-700 text-sm font-semibold rounded-xl text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer">
                            Cancelar
                        </button>
                        <button type="submit" id="btn-bloque-guardar"
                            class="px-4 py-2 text-sm font-semibold rounded-xl text-white bg-purple-600 hover:bg-purple-500 shadow-xs cursor-pointer">
                            Guardar Bloque
                        </button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <script src="../public/js/horarios.js"></script>
</body>

</html>
