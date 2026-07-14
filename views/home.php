<?php
require_once __DIR__ . '/help.php';

$user_role = $_SESSION['user_role'] ?? 'Invitado';
$user_id = $_SESSION['user_id'] ?? null;
$user_fullname = $_SESSION['user_fullname'] ?? 'Usuario';

// Mapeo de días en español
$dias_ingles = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
$dias_espanol = ['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado', 'Domingo'];
$hoy_espanol = str_replace($dias_ingles, $dias_espanol, date('l'));

// Inicialización de contadores
$contadores = [];

if ($db) {
    try {
        if ($user_role === 'Root') {
            // Clientes
            $contadores['total_clientes'] = $db->query("SELECT COUNT(*) FROM clientes")->fetchColumn() ?: 0;
            $contadores['activos_clientes'] = $db->query("SELECT COUNT(*) FROM clientes WHERE id_estatus = (SELECT id FROM estatus WHERE nombre_estatus = 'Activo' LIMIT 1)")->fetchColumn() ?: 0;
            
            // Entrenadores
            $contadores['total_entrenadores'] = $db->query("SELECT COUNT(*) FROM entrenadores")->fetchColumn() ?: 0;
            $contadores['activos_entrenadores'] = $db->query("SELECT COUNT(*) FROM entrenadores WHERE id_estatus = (SELECT id FROM estatus WHERE nombre_estatus = 'Activo' LIMIT 1)")->fetchColumn() ?: 0;
            
            // Entrenamientos
            $contadores['total_entrenamientos'] = $db->query("SELECT COUNT(*) FROM entrenamiento")->fetchColumn() ?: 0;
            $stmt = $db->prepare("SELECT COUNT(DISTINCT id_entrenamiento) FROM entrenamiento_horarios WHERE dia_semana = :hoy");
            $stmt->execute([':hoy' => $hoy_espanol]);
            $contadores['clases_hoy'] = $stmt->fetchColumn() ?: 0;
            
            // Planes
            $contadores['total_planes'] = $db->query("SELECT COUNT(*) FROM planes")->fetchColumn() ?: 0;
            
            // Pagos
            $contadores['total_ingresos'] = $db->query("SELECT SUM(monto) FROM pagos WHERE id_estatus = (SELECT id FROM estatus WHERE nombre_estatus = 'Aprobado' LIMIT 1)")->fetchColumn() ?: 0.00;
            $contadores['ingresos_mes'] = $db->query("SELECT SUM(monto) FROM pagos WHERE id_estatus = (SELECT id FROM estatus WHERE nombre_estatus = 'Aprobado' LIMIT 1) AND MONTH(fecha_pago) = MONTH(CURRENT_DATE()) AND YEAR(fecha_pago) = YEAR(CURRENT_DATE())")->fetchColumn() ?: 0.00;
            $contadores['pagos_pendientes'] = $db->query("SELECT COUNT(*) FROM pagos WHERE id_estatus = (SELECT id FROM estatus WHERE nombre_estatus = 'Pendiente' LIMIT 1)")->fetchColumn() ?: 0;
            
            // Usuarios
            $contadores['total_usuarios'] = $db->query("SELECT COUNT(*) FROM usuarios")->fetchColumn() ?: 0;
            $contadores['activos_usuarios'] = $db->query("SELECT COUNT(*) FROM usuarios WHERE id_estatus = (SELECT id FROM estatus WHERE nombre_estatus = 'Activo' LIMIT 1)")->fetchColumn() ?: 0;
            
            // Roles
            $contadores['total_roles'] = $db->query("SELECT COUNT(*) FROM roles")->fetchColumn() ?: 0;
            
            // Permisos
            $contadores['total_permisos'] = $db->query("SELECT COUNT(*) FROM permisos")->fetchColumn() ?: 0;
            
            // Personas
            $contadores['total_personas'] = $db->query("SELECT COUNT(*) FROM personas")->fetchColumn() ?: 0;

        } elseif ($user_role === 'Administrador') {
            // Clientes
            $contadores['total_clientes'] = $db->query("SELECT COUNT(*) FROM clientes")->fetchColumn() ?: 0;
            $contadores['activos_clientes'] = $db->query("SELECT COUNT(*) FROM clientes WHERE id_estatus = (SELECT id FROM estatus WHERE nombre_estatus = 'Activo' LIMIT 1)")->fetchColumn() ?: 0;
            
            // Entrenadores
            $contadores['total_entrenadores'] = $db->query("SELECT COUNT(*) FROM entrenadores")->fetchColumn() ?: 0;
            $contadores['activos_entrenadores'] = $db->query("SELECT COUNT(*) FROM entrenadores WHERE id_estatus = (SELECT id FROM estatus WHERE nombre_estatus = 'Activo' LIMIT 1)")->fetchColumn() ?: 0;
            
            // Entrenamientos
            $contadores['total_entrenamientos'] = $db->query("SELECT COUNT(*) FROM entrenamiento")->fetchColumn() ?: 0;
            $stmt = $db->prepare("SELECT COUNT(DISTINCT id_entrenamiento) FROM entrenamiento_horarios WHERE dia_semana = :hoy");
            $stmt->execute([':hoy' => $hoy_espanol]);
            $contadores['clases_hoy'] = $stmt->fetchColumn() ?: 0;
            
            // Planes
            $contadores['total_planes'] = $db->query("SELECT COUNT(*) FROM planes")->fetchColumn() ?: 0;
            
            // Pagos
            $contadores['total_ingresos'] = $db->query("SELECT SUM(monto) FROM pagos WHERE id_estatus = (SELECT id FROM estatus WHERE nombre_estatus = 'Aprobado' LIMIT 1)")->fetchColumn() ?: 0.00;
            $contadores['ingresos_mes'] = $db->query("SELECT SUM(monto) FROM pagos WHERE id_estatus = (SELECT id FROM estatus WHERE nombre_estatus = 'Aprobado' LIMIT 1) AND MONTH(fecha_pago) = MONTH(CURRENT_DATE()) AND YEAR(fecha_pago) = YEAR(CURRENT_DATE())")->fetchColumn() ?: 0.00;
            $contadores['pagos_pendientes'] = $db->query("SELECT COUNT(*) FROM pagos WHERE id_estatus = (SELECT id FROM estatus WHERE nombre_estatus = 'Pendiente' LIMIT 1)")->fetchColumn() ?: 0;

        } elseif ($user_role === 'Entrenador') {
            // Obtener ID Entrenador
            $stmtTrainer = $db->prepare("SELECT id FROM entrenadores WHERE id_persona = (SELECT id_persona FROM usuarios WHERE id = :user_id LIMIT 1)");
            $stmtTrainer->execute([':user_id' => $user_id]);
            $trainer_id = $stmtTrainer->fetchColumn() ?: 0;
            
            $contadores['activos_clientes'] = $db->query("SELECT COUNT(*) FROM clientes WHERE id_estatus = (SELECT id FROM estatus WHERE nombre_estatus = 'Activo' LIMIT 1)")->fetchColumn() ?: 0;
            
            if ($trainer_id) {
                $stmt = $db->prepare("SELECT COUNT(*) FROM entrenamiento WHERE id_entrenador = :entrenador_id");
                $stmt->execute([':entrenador_id' => $trainer_id]);
                $contadores['mis_entrenamientos'] = $stmt->fetchColumn() ?: 0;
                
                $stmt = $db->prepare("SELECT COUNT(DISTINCT eh.id_entrenamiento) FROM entrenamiento_horarios eh INNER JOIN entrenamiento e ON eh.id_entrenamiento = e.id WHERE e.id_entrenador = :entrenador_id AND eh.dia_semana = :hoy");
                $stmt->execute([':entrenador_id' => $trainer_id, ':hoy' => $hoy_espanol]);
                $contadores['mis_clases_hoy'] = $stmt->fetchColumn() ?: 0;
            } else {
                $contadores['mis_entrenamientos'] = 0;
                $contadores['mis_clases_hoy'] = 0;
            }

        } elseif ($user_role === 'Cliente') {
            // Obtener ID Cliente
            $stmtClient = $db->prepare("SELECT id FROM clientes WHERE id_persona = (SELECT id_persona FROM usuarios WHERE id = :user_id LIMIT 1)");
            $stmtClient->execute([':user_id' => $user_id]);
            $cliente_id = $stmtClient->fetchColumn() ?: 0;
            
            if ($cliente_id) {
                // Pagos
                $stmt = $db->prepare("SELECT COUNT(*) FROM pagos WHERE id_cliente = :cliente_id");
                $stmt->execute([':cliente_id' => $cliente_id]);
                $contadores['mis_pagos_total'] = $stmt->fetchColumn() ?: 0;
                
                $stmt = $db->prepare("SELECT SUM(monto) FROM pagos WHERE id_cliente = :cliente_id AND id_estatus = (SELECT id FROM estatus WHERE nombre_estatus = 'Aprobado' LIMIT 1)");
                $stmt->execute([':cliente_id' => $cliente_id]);
                $contadores['mis_pagos_aprobados'] = $stmt->fetchColumn() ?: 0.00;
                
                // Membresía
                $stmt = $db->prepare("SELECT p.nombre_plan, cp.fecha_vencimiento, DATEDIFF(cp.fecha_vencimiento, CURRENT_DATE()) AS dias_restantes FROM clientes_planes cp INNER JOIN planes p ON cp.id_plan = p.id WHERE cp.id_cliente = :cliente_id AND cp.id_estatus = (SELECT id FROM estatus WHERE nombre_estatus = 'Activo' LIMIT 1) ORDER BY cp.fecha_vencimiento DESC LIMIT 1");
                $stmt->execute([':cliente_id' => $cliente_id]);
                $plan_activo = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($plan_activo) {
                    $contadores['mi_plan_nombre'] = $plan_activo['nombre_plan'];
                    $contadores['mi_plan_dias'] = $plan_activo['dias_restantes'];
                } else {
                    $contadores['mi_plan_nombre'] = 'Ninguno';
                    $contadores['mi_plan_dias'] = 0;
                }
            } else {
                $contadores['mis_pagos_total'] = 0;
                $contadores['mis_pagos_aprobados'] = 0.00;
                $contadores['mi_plan_nombre'] = 'Ninguno';
                $contadores['mi_plan_dias'] = 0;
            }
            
            // Clases hoy
            $stmt = $db->prepare("SELECT COUNT(DISTINCT id_entrenamiento) FROM entrenamiento_horarios WHERE dia_semana = :hoy");
            $stmt->execute([':hoy' => $hoy_espanol]);
            $contadores['clases_hoy'] = $stmt->fetchColumn() ?: 0;
            
            // Entrenadores
            $contadores['entrenadores_activos'] = $db->query("SELECT COUNT(*) FROM entrenadores WHERE id_estatus = (SELECT id FROM estatus WHERE nombre_estatus = 'Activo' LIMIT 1)")->fetchColumn() ?: 0;
        }
    } catch (PDOException $e) {
        // Silenciosamente capturar y rellenar con ceros
    }
}
?>
<!DOCTYPE html>
<html lang="es" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?= SITE_NAME ?></title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>

<body class="bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-100 min-h-screen flex">

    <?php require_once __DIR__ . '/../components/sidebar.php'; ?>

    <main class="flex-1 p-10 overflow-y-auto">
        <header class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Panel Principal</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Resumen operativo personalizado para el rol: <span class="px-2 py-0.5 rounded-md font-semibold text-xs bg-orange-100 dark:bg-orange-950/40 text-orange-700 dark:text-orange-400"><?= htmlspecialchars($user_role) ?></span></p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 px-4 py-2.5 shadow-xs flex items-center gap-3">
                <div class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></div>
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Usuario activo: <strong><?= htmlspecialchars($user_fullname) ?></strong></span>
            </div>
        </header>

        <!-- DASHBOARD CONTAINER -->
        <div class="space-y-8">
            
            <?php if ($user_role === 'Root'): ?>
                <!-- ============================================== -->
                <!-- CORE STATS FOR ROOT / SUPERADMIN (9 CARDS)     -->
                <!-- ============================================== -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    <!-- CLIENTES CARD -->
                    <div class="p-6 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm relative overflow-hidden group">
                        <div class="absolute top-0 right-0 w-24 h-24 bg-blue-500/10 rounded-bl-full transition-transform group-hover:scale-110"></div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Miembros / Clientes</p>
                        <p class="text-3xl font-black mt-3 text-gray-900 dark:text-white"><?= $contadores['total_clientes'] ?? 0 ?></p>
                        <p class="text-xs text-blue-600 dark:text-blue-400 mt-2 font-medium">Activos: <?= $contadores['activos_clientes'] ?? 0 ?></p>
                    </div>

                    <!-- ENTRENADORES CARD -->
                    <div class="p-6 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm relative overflow-hidden group">
                        <div class="absolute top-0 right-0 w-24 h-24 bg-purple-500/10 rounded-bl-full transition-transform group-hover:scale-110"></div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Instructores / Entrenadores</p>
                        <p class="text-3xl font-black mt-3 text-gray-900 dark:text-white"><?= $contadores['total_entrenadores'] ?? 0 ?></p>
                        <p class="text-xs text-purple-600 dark:text-purple-400 mt-2 font-medium">Activos: <?= $contadores['activos_entrenadores'] ?? 0 ?></p>
                    </div>

                    <!-- ENTRENAMIENTOS CARD -->
                    <div class="p-6 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm relative overflow-hidden group">
                        <div class="absolute top-0 right-0 w-24 h-24 bg-orange-500/10 rounded-bl-full transition-transform group-hover:scale-110"></div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Clases / Entrenamientos</p>
                        <p class="text-3xl font-black mt-3 text-gray-900 dark:text-white"><?= $contadores['total_entrenamientos'] ?? 0 ?></p>
                        <p class="text-xs text-orange-600 dark:text-orange-400 mt-2 font-medium">Programadas Hoy: <?= $contadores['clases_hoy'] ?? 0 ?></p>
                    </div>

                    <!-- PLANES CARD -->
                    <div class="p-6 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm relative overflow-hidden group">
                        <div class="absolute top-0 right-0 w-24 h-24 bg-indigo-500/10 rounded-bl-full transition-transform group-hover:scale-110"></div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Planes de Gimnasio</p>
                        <p class="text-3xl font-black mt-3 text-gray-900 dark:text-white"><?= $contadores['total_planes'] ?? 0 ?></p>
                        <p class="text-xs text-indigo-600 dark:text-indigo-400 mt-2 font-medium">Catálogo completo</p>
                    </div>

                    <!-- INGRESOS TOTALES CARD -->
                    <div class="p-6 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm relative overflow-hidden group">
                        <div class="absolute top-0 right-0 w-24 h-24 bg-emerald-500/10 rounded-bl-full transition-transform group-hover:scale-110"></div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Ingresos del Mes</p>
                        <p class="text-3xl font-black mt-3 text-emerald-500"><?= number_format($contadores['ingresos_mes'] ?? 0.00, 2, ',', '.') ?> Bs.</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2 font-medium">Total histórico: <?= number_format($contadores['total_ingresos'] ?? 0.00, 2, ',', '.') ?> Bs.</p>
                    </div>

                    <!-- PAGOS PENDIENTES CARD -->
                    <div class="p-6 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm relative overflow-hidden group">
                        <div class="absolute top-0 right-0 w-24 h-24 bg-amber-500/10 rounded-bl-full transition-transform group-hover:scale-110"></div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Pagos por Aprobar</p>
                        <p class="text-3xl font-black mt-3 <?= ($contadores['pagos_pendientes'] ?? 0) > 0 ? 'text-amber-500 animate-pulse' : 'text-gray-900 dark:text-white' ?>"><?= $contadores['pagos_pendientes'] ?? 0 ?></p>
                        <p class="text-xs text-amber-600 dark:text-amber-400 mt-2 font-medium">Acción administrativa requerida</p>
                    </div>

                    <!-- USUARIOS CARD -->
                    <div class="p-6 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm relative overflow-hidden group">
                        <div class="absolute top-0 right-0 w-24 h-24 bg-teal-500/10 rounded-bl-full transition-transform group-hover:scale-110"></div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Usuarios del Sistema</p>
                        <p class="text-3xl font-black mt-3 text-gray-900 dark:text-white"><?= $contadores['total_usuarios'] ?? 0 ?></p>
                        <p class="text-xs text-teal-600 dark:text-teal-400 mt-2 font-medium">Operativos Activos: <?= $contadores['activos_usuarios'] ?? 0 ?></p>
                    </div>

                    <!-- ROLES CARD -->
                    <div class="p-6 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm relative overflow-hidden group">
                        <div class="absolute top-0 right-0 w-24 h-24 bg-rose-500/10 rounded-bl-full transition-transform group-hover:scale-110"></div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Roles Registrados</p>
                        <p class="text-3xl font-black mt-3 text-gray-900 dark:text-white"><?= $contadores['total_roles'] ?? 0 ?></p>
                        <p class="text-xs text-rose-600 dark:text-rose-400 mt-2 font-medium">Perfiles de seguridad</p>
                    </div>

                    <!-- PERMISOS CARD -->
                    <div class="p-6 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm relative overflow-hidden group">
                        <div class="absolute top-0 right-0 w-24 h-24 bg-sky-500/10 rounded-bl-full transition-transform group-hover:scale-110"></div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Permisos Totales</p>
                        <p class="text-3xl font-black mt-3 text-gray-900 dark:text-white"><?= $contadores['total_permisos'] ?? 0 ?></p>
                        <p class="text-xs text-sky-600 dark:text-sky-400 mt-2 font-medium">Claves de acceso y acciones</p>
                    </div>

                    <!-- PERSONAS CARD -->
                    <div class="p-6 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm relative overflow-hidden group">
                        <div class="absolute top-0 right-0 w-24 h-24 bg-emerald-500/10 rounded-bl-full transition-transform group-hover:scale-110"></div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Personas Registradas</p>
                        <p class="text-3xl font-black mt-3 text-gray-900 dark:text-white"><?= $contadores['total_personas'] ?? 0 ?></p>
                        <p class="text-xs text-emerald-600 dark:text-emerald-400 mt-2 font-medium">Fichas de identificación</p>
                    </div>
                </div>

            <?php elseif ($user_role === 'Administrador'): ?>
                <!-- ============================================== -->
                <!-- STATS FOR ADMINISTRATOR ROLE (5 CARDS)         -->
                <!-- ============================================== -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-6">
                    <!-- CLIENTES CARD -->
                    <div class="p-6 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm relative overflow-hidden group">
                        <div class="absolute top-0 right-0 w-20 h-20 bg-blue-500/10 rounded-bl-full transition-transform group-hover:scale-110"></div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Miembros / Clientes</p>
                        <p class="text-3xl font-black mt-3 text-gray-900 dark:text-white"><?= $contadores['total_clientes'] ?? 0 ?></p>
                        <p class="text-xs text-blue-600 dark:text-blue-400 mt-2 font-medium">Activos: <?= $contadores['activos_clientes'] ?? 0 ?></p>
                    </div>

                    <!-- ENTRENADORES CARD -->
                    <div class="p-6 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm relative overflow-hidden group">
                        <div class="absolute top-0 right-0 w-20 h-20 bg-purple-500/10 rounded-bl-full transition-transform group-hover:scale-110"></div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Entrenadores</p>
                        <p class="text-3xl font-black mt-3 text-gray-900 dark:text-white"><?= $contadores['total_entrenadores'] ?? 0 ?></p>
                        <p class="text-xs text-purple-600 dark:text-purple-400 mt-2 font-medium">Activos: <?= $contadores['activos_entrenadores'] ?? 0 ?></p>
                    </div>

                    <!-- ENTRENAMIENTOS CARD -->
                    <div class="p-6 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm relative overflow-hidden group">
                        <div class="absolute top-0 right-0 w-20 h-20 bg-orange-500/10 rounded-bl-full transition-transform group-hover:scale-110"></div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Entrenamientos</p>
                        <p class="text-3xl font-black mt-3 text-gray-900 dark:text-white"><?= $contadores['total_entrenamientos'] ?? 0 ?></p>
                        <p class="text-xs text-orange-600 dark:text-orange-400 mt-2 font-medium">Clases de Hoy: <?= $contadores['clases_hoy'] ?? 0 ?></p>
                    </div>

                    <!-- PLANES CARD -->
                    <div class="p-6 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm relative overflow-hidden group">
                        <div class="absolute top-0 right-0 w-20 h-20 bg-indigo-500/10 rounded-bl-full transition-transform group-hover:scale-110"></div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Planes</p>
                        <p class="text-3xl font-black mt-3 text-gray-900 dark:text-white"><?= $contadores['total_planes'] ?? 0 ?></p>
                        <p class="text-xs text-indigo-600 dark:text-indigo-400 mt-2 font-medium">Catálogo activo</p>
                    </div>

                    <!-- PAGOS CARD -->
                    <div class="p-6 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm relative overflow-hidden group">
                        <div class="absolute top-0 right-0 w-20 h-20 bg-emerald-500/10 rounded-bl-full transition-transform group-hover:scale-110"></div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Ingresos del Mes</p>
                        <p class="text-2xl font-black mt-3 text-emerald-500"><?= number_format($contadores['ingresos_mes'] ?? 0.00, 2, ',', '.') ?> Bs.</p>
                        <p class="text-[10px] text-amber-600 dark:text-amber-400 mt-2 font-semibold">Pendientes: <?= $contadores['pagos_pendientes'] ?? 0 ?></p>
                    </div>
                </div>

            <?php elseif ($user_role === 'Entrenador'): ?>
                <!-- ============================================== -->
                <!-- STATS FOR TRAINER / INSTRUCTOR ROLE (3 CARDS)  -->
                <!-- ============================================== -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- CLIENTES GENERAL CARD -->
                    <div class="p-6 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm relative overflow-hidden group">
                        <div class="absolute top-0 right-0 w-24 h-24 bg-blue-500/10 rounded-bl-full transition-transform group-hover:scale-110"></div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Clientes Activos</p>
                        <p class="text-4xl font-black mt-4 text-gray-900 dark:text-white"><?= $contadores['activos_clientes'] ?? 0 ?></p>
                        <p class="text-xs text-blue-600 dark:text-blue-400 mt-3 font-medium">Total de miembros en el gimnasio</p>
                    </div>

                    <!-- MIS ENTRENAMIENTOS CARD -->
                    <div class="p-6 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm relative overflow-hidden group">
                        <div class="absolute top-0 right-0 w-24 h-24 bg-orange-500/10 rounded-bl-full transition-transform group-hover:scale-110"></div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Mis Entrenamientos</p>
                        <p class="text-4xl font-black mt-4 text-gray-900 dark:text-white"><?= $contadores['mis_entrenamientos'] ?? 0 ?></p>
                        <p class="text-xs text-orange-600 dark:text-orange-400 mt-3 font-medium">Clases asignadas bajo mi perfil</p>
                    </div>

                    <!-- MIS CLASES HOY CARD -->
                    <div class="p-6 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm relative overflow-hidden group">
                        <div class="absolute top-0 right-0 w-24 h-24 bg-amber-500/10 rounded-bl-full transition-transform group-hover:scale-110"></div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Mis Clases de Hoy</p>
                        <p class="text-4xl font-black mt-4 text-amber-500"><?= $contadores['mis_clases_hoy'] ?? 0 ?></p>
                        <p class="text-xs text-amber-600 dark:text-amber-400 mt-3 font-medium">Bloques de entrenamiento para hoy <?= htmlspecialchars($hoy_espanol) ?></p>
                    </div>
                </div>

            <?php elseif ($user_role === 'Cliente'): ?>
                <!-- ============================================== -->
                <!-- STATS FOR CUSTOMER / CLIENT ROLE (4 CARDS)     -->
                <!-- ============================================== -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- MI MEMBRESÍA ACTIVA -->
                    <div class="p-6 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm relative overflow-hidden group">
                        <div class="absolute top-0 right-0 w-24 h-24 bg-indigo-500/10 rounded-bl-full transition-transform group-hover:scale-110"></div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Mi Plan Activo</p>
                        <p class="text-2xl font-black mt-3 text-indigo-600 dark:text-indigo-400 truncate"><?= htmlspecialchars($contadores['mi_plan_nombre'] ?? 'Ninguno') ?></p>
                        
                        <?php if (($contadores['mi_plan_dias'] ?? 0) <= 0): ?>
                            <p class="text-xs text-rose-500 mt-2 font-semibold flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span> Sin vigencia / Vencido
                            </p>
                        <?php else: ?>
                            <p class="text-xs text-emerald-500 mt-2 font-semibold flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Vence en <?= $contadores['mi_plan_dias'] ?> días
                            </p>
                        <?php endif; ?>
                    </div>

                    <!-- MIS PAGOS REGISTRADOS -->
                    <div class="p-6 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm relative overflow-hidden group">
                        <div class="absolute top-0 right-0 w-24 h-24 bg-emerald-500/10 rounded-bl-full transition-transform group-hover:scale-110"></div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Pagos Registrados</p>
                        <p class="text-3xl font-black mt-3 text-gray-900 dark:text-white"><?= $contadores['mis_pagos_total'] ?? 0 ?></p>
                        <p class="text-xs text-emerald-600 dark:text-emerald-400 mt-2 font-medium">Abonado total: <?= number_format($contadores['mis_pagos_aprobados'] ?? 0.00, 2, ',', '.') ?> Bs.</p>
                    </div>

                    <!-- CLASES HOY -->
                    <div class="p-6 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm relative overflow-hidden group">
                        <div class="absolute top-0 right-0 w-24 h-24 bg-orange-500/10 rounded-bl-full transition-transform group-hover:scale-110"></div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Clases de Hoy</p>
                        <p class="text-3xl font-black mt-3 text-orange-500"><?= $contadores['clases_hoy'] ?? 0 ?></p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2 font-medium">Entrenamientos para hoy <?= htmlspecialchars($hoy_espanol) ?></p>
                    </div>

                    <!-- ENTRENADORES DISPONIBLES -->
                    <div class="p-6 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm relative overflow-hidden group">
                        <div class="absolute top-0 right-0 w-24 h-24 bg-purple-500/10 rounded-bl-full transition-transform group-hover:scale-110"></div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Entrenadores Activos</p>
                        <p class="text-3xl font-black mt-3 text-gray-900 dark:text-white"><?= $contadores['entrenadores_activos'] ?? 0 ?></p>
                        <p class="text-xs text-purple-600 dark:text-purple-400 mt-2 font-medium">Instructores listos para asesorar</p>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </main>

</body>

</html>