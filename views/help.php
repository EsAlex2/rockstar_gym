<?php
require_once __DIR__ . '/../config/init.php';
require_once __DIR__ . '/../app/controllers/userController.php';
require_once __DIR__ . '/../app/controllers/rolesController.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: " . URL_BASE . "/public/index.php");
    exit;
}

$db = $pdo ?? null;

// Inicializamos controladores
$userCtrl = new userController($db);
$rolesCtrl = new rolesController($db);

/** --- CARGA DE USUARIOS --- */
$respuestaUsuarios = $userCtrl->listarUsuarios();
$listaUsuarios = [];

if (is_string($respuestaUsuarios)) {
    $respuestaUsuarios = json_decode($respuestaUsuarios, true);
}
if (is_array($respuestaUsuarios)) {
    $listaUsuarios = $respuestaUsuarios['data'] ?? (isset($respuestaUsuarios[0]) ? $respuestaUsuarios : []);
}

/** --- CARGA DINÁMICA DE ROLES --- */
$respuestaRoles = $rolesCtrl->listarRoles();

$rolesCrudos = [];
if (is_string($respuestaRoles)) {
    $respuestaRoles = json_decode($respuestaRoles, true);
}

if (is_array($respuestaRoles)) {
    if (isset($respuestaRoles['data']) && is_array($respuestaRoles['data'])) {
        $rolesCrudos = $respuestaRoles['data'];
    } else {
        $rolesCrudos = $respuestaRoles;
    }
}

$mapaRoles = [];
if (!empty($rolesCrudos) && is_array($rolesCrudos)) {
    foreach ($rolesCrudos as $r) {
        if (is_array($r)) {
            $idClave = $r['id_rol'] ?? $r['id'] ?? array_values($r)[0] ?? null;
            $nombreRol = $r['nombre_rol'] ?? $r['nombre'] ?? $r['nombre_role'] ?? array_values($r)[1] ?? 'Sin especificar';

            if ($idClave !== null) {
                $mapaRoles[$idClave] = $nombreRol;
            }
        }
    }
}
?>