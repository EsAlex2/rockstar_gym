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
    <title>Gestión de Pagos - <?= SITE_NAME ?></title>
    <?php require_once __DIR__ . '/../components/header_theme.php'; ?>
</head>

<body class="bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-100 min-h-screen flex">

    <?php require_once __DIR__ . '/../components/sidebar.php'; ?>

    <main class="flex-1 p-10 overflow-y-auto">
        <header class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Historial de Pagos</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Auditoría, registro de transacciones de clientes y control de estados de cuenta.</p>
            </div>
            <div>
                <?php if(in_array($user_role, [$roles[0], $roles[1], $roles[3]])): ?>
                    <button onclick="abrirModalCrear()"
                        class="px-5 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-500 rounded-xl shadow-xs transition-all cursor-pointer flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Registrar Pago
                    </button>
                <?php endif; ?>
            </div>
        </header>

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <?php if(in_array($user_role, [$roles[0], $roles[1], $roles[3]])): ?>
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/20">
                            <th class="px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">ID / Ref. Bancaria</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Cliente</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Plan Adquirido</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Banco Emisor</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Fecha Pago</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Monto</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Estado</th>
                            <?php if(in_array($user_role, [$roles[0], $roles[1]])): ?>
                                <th class="px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider text-right">Acciones</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    
                    <tbody id="tabla-pagos-body" class="divide-y divide-gray-100 dark:divide-gray-700 text-sm">
                        <?php if (!empty($listaPagos) && !isset($listaPagos['error'])): ?>
                            <?php foreach ($listaPagos as $pago): ?>
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/10 transition-colors">
                                    <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                        <div class="font-semibold">#<?= htmlspecialchars($pago['id']) ?></div>
                                        <div class="text-xs text-blue-600 dark:text-blue-400 mt-0.5 font-mono"><?= htmlspecialchars($pago['cod_referencia']) ?></div>
                                    </td>
                                    <td class="px-6 py-4 text-gray-700 dark:text-gray-200 font-semibold">
                                        <?= htmlspecialchars($pago['cliente_nombre'] ?? 'N/A') ?>
                                    </td>
                                    <td class="px-6 py-4 text-gray-600 dark:text-gray-400 font-medium">
                                        <?= htmlspecialchars($pago['plan_nombre'] ?? 'N/A') ?>
                                    </td>
                                    <td class="px-6 py-4 text-gray-700 dark:text-gray-200 font-medium">
                                        <?= htmlspecialchars($pago['banco']) ?>
                                    </td>
                                    <td class="px-6 py-4 text-gray-600 dark:text-gray-400">
                                        <?= htmlspecialchars(date("d/m/Y", strtotime($pago['fecha_pago']))) ?>
                                    </td>
                                    <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">
                                        <?= number_format($pago['monto'], 2, ',', '.') ?> Bs.
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php 
                                        $estatus = strtoupper($pago['estatus']);
                                        if ($estatus === 'APROBADO' || $estatus === 'CONCILIADO' || $estatus === 'ACTIVO'): ?>
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900/30">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span><?= htmlspecialchars($pago['estatus']) ?>
                                            </span>
                                        <?php elseif ($estatus === 'PENDIENTE'): ?>
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-amber-50 dark:bg-amber-950/30 text-amber-700 dark:text-amber-400 border border-amber-100 dark:border-amber-900/30">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span><?= htmlspecialchars($pago['estatus']) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-rose-50 dark:bg-rose-950/30 text-rose-700 dark:text-rose-400 border border-rose-100 dark:border-rose-900/30">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span><?= htmlspecialchars($pago['estatus']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <?php if(in_array($user_role, [$roles[0], $roles[1]])): ?>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <button onclick='abrirModalEditar(<?= json_encode($pago) ?>)'
                                                class="p-2 text-gray-400 hover:text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg transition-colors cursor-pointer"
                                                title="Editar Pago">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z">
                                                    </path>
                                                </svg>
                                            </button>
                                            <button onclick="eliminarPago(<?= $pago['id'] ?>)"
                                                class="p-2 text-gray-400 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/30 rounded-lg transition-colors cursor-pointer"
                                                title="Eliminar Pago">
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
                                <td colspan="8" class="px-6 py-10 text-center text-gray-400 dark:text-gray-500">
                                    No se registran movimientos de pago en el sistema.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>

        <div id="toast-container" class="fixed top-5 right-5 z-50 flex flex-col gap-3 pointer-events-none max-w-sm w-full"></div>

        <div id="modal-pago" class="fixed inset-0 z-50 hidden bg-gray-900/50 dark:bg-gray-950/70 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 w-full max-w-xl overflow-hidden transform transition-all">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between bg-gray-50/50 dark:bg-gray-900/20">
                    <h3 id="modal-titulo" class="text-base font-bold text-gray-900 dark:text-white">Registrar Transacción de Pago Seguro</h3>
                    <button type="button" onclick="cerrarModal()" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form id="form-pago" class="p-6 space-y-4">
                    <input type="hidden" name="id_pago" id="id_pago">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Cliente *</label>
                            <?php if ($user_role === 'Cliente'): ?>
                                <input type="hidden" name="id_cliente" value="<?= $logged_client_id ?>">
                                <select disabled class="w-full px-4 py-2.5 bg-gray-100 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm text-gray-500 dark:text-gray-400 cursor-not-allowed focus:outline-hidden">
                                    <option selected><?= htmlspecialchars($user_fullname) ?></option>
                                </select>
                            <?php else: ?>
                                <select name="id_cliente" id="select-cliente" required class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm text-gray-900 dark:text-white focus:outline-hidden focus:border-blue-500">
                                    <option value="">-- Seleccione Cliente --</option>
                                    <?php if (!empty($listaClientes)): foreach ($listaClientes as $c): ?>
                                        <option value="<?= $c['id'] ?? $c['id_cliente'] ?>">
                                            <?= htmlspecialchars(($c['primer_nombre'] ?? 'Cliente'). ' ' . ($c['primer_apellido'] ?? '')) ?>
                                        </option>
                                    <?php endforeach; endif; ?>
                                </select>
                            <?php endif; ?>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Plan a Pagar *</label>
                            <select name="id_cliente_plan" id="select-plan" required class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm text-gray-900 dark:text-white focus:outline-hidden focus:border-blue-500">
                                <option value="">-- Seleccione el Plan --</option>
                                <?php if (!empty($listaPlanesDisponibles)): foreach ($listaPlanesDisponibles as $p): ?>
                                    <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nombre_plan']) ?> (<?= number_format($p['precio'], 2) ?> Bs.)</option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Banco Emisor *</label>
                            <select name="id_banco" id="select-banco" required class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm text-gray-900 dark:text-white focus:outline-hidden focus:border-blue-500">
                                <option value="">-- Seleccione Banco --</option>
                                <?php if (!empty($listaBancos)): foreach ($listaBancos as $b): ?>
                                    <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['nombre_banco']) ?></option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Estatus *</label>
                            <?php if ($user_role === 'Cliente'): ?>
                                <input type="hidden" name="id_estatus" value="3">
                                <select disabled class="w-full px-4 py-2.5 bg-gray-100 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm text-gray-500 dark:text-gray-400 cursor-not-allowed focus:outline-hidden">
                                    <option selected>Pendiente</option>
                                </select>
                            <?php else: ?>
                                <select name="id_estatus" id="select-estatus" required class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm text-gray-900 dark:text-white focus:outline-hidden focus:border-blue-500">
                                    <?php if (!empty($listaEstatus)): foreach ($listaEstatus as $e): ?>
                                        <option value="<?= $e['id'] ?>" <?= strtoupper($e['nombre_estatus']) === 'PENDIENTE' ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($e['nombre_estatus']) ?>
                                        </option>
                                    <?php endforeach; endif; ?>
                                </select>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Monto Depósito / Transferencia *</label>
                            <input type="number" step="0.01" name="monto" id="input-monto" required placeholder="0.00" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm text-gray-900 dark:text-white focus:outline-hidden focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Fecha de Operación *</label>
                            <input type="date" name="fecha_pago" id="input-fecha" required class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm text-gray-900 dark:text-white focus:outline-hidden focus:border-blue-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Código de Referencia Bancaria *</label>
                        <input type="text" name="cod_referencia" id="input-referencia" required placeholder="Ej: 48192048210" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm font-mono text-gray-900 dark:text-white focus:outline-hidden focus:border-blue-500">
                    </div>

                    <div class="pt-4 border-t border-gray-100 dark:border-gray-700 flex justify-end space-x-3">
                        <button type="button" onclick="cerrarModal()" class="px-4 py-2 border border-gray-200 dark:border-gray-700 text-sm font-semibold rounded-xl text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer">
                            Cancelar
                        </button>
                        <button type="submit" id="btn-submit" class="px-4 py-2 text-sm font-semibold rounded-xl text-white bg-blue-600 hover:bg-blue-500 shadow-xs cursor-pointer">
                            Guardar Transacción
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </main>

    <script src="../public/js/pagos.js"></script>
</body>
</html>