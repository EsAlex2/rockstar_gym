<?php
require_once __DIR__ . '/../config/init.php';
require_once __DIR__ . '/../app/controllers/userController.php';
require_once __DIR__ . '/../app/controllers/rolesController.php';
require_once __DIR__ . '/../app/controllers/personasController.php';
require_once __DIR__ . '/../app/controllers/clientesController.php'; // Controlador de clientes

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: " . URL_BASE . "/public/index.php");
    exit;
}

$db = $pdo ?? null;

// =========================================================================
// 1. INICIALIZACIÓN DE CONTROLADORES
// =========================================================================
$userCtrl = new userController($db);
$rolesCtrl = new rolesController($db);
$personasCtrl = new PersonasController($db);
$clientesCtrl = new ClientesController($db);

// =========================================================================
// 2. CARGA DE DATOS PARA LAS VISTAS (TABLAS Y SELECTS)
// =========================================================================

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
    $rolesCrudos = $respuestaRoles['data'] ?? $respuestaRoles;
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

/** --- CARGA DE PERSONAS --- */
$respuestaPersonas = $personasCtrl->listarPersonas();
$listaPersonas = [];
if (is_string($respuestaPersonas)) { 
    $respuestaPersonas = json_decode($respuestaPersonas, true); 
}
if (is_array($respuestaPersonas)) {
    $listaPersonas = $respuestaPersonas['data'] ?? (isset($respuestaPersonas[0]) ? $respuestaPersonas : []);
}

/** --- CARGA DE CLIENTES --- */
$respuestaClientes = $clientesCtrl->listarClientes();
$listaClientes = [];
if (is_string($respuestaClientes)) { 
    $respuestaClientes = json_decode($respuestaClientes, true); 
}
if (is_array($respuestaClientes)) {
    $listaClientes = $respuestaClientes['data'] ?? (isset($respuestaClientes[0]) ? $respuestaClientes : []);
}


// =========================================================================
// 3. ENRUTADOR DE PASARELA API (RUTAS ASÍNCRONAS AJAX/FETCH)
// =========================================================================

/**---- RUTAS GET: BÚSQUEDA DE PERSONAS ------ */
if (isset($_GET['action']) && $_GET['action'] === 'buscar_persona') {
    header('Content-Type: application/json; charset=utf-8');
    
    $cedula = $_GET['cedula_identidad'] ?? '';

    if (empty(trim($cedula))) {
        echo json_encode(["status" => false, "message" => "El parámetro cédula es requerido."], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $respuesta = $personasCtrl->listarPorCedula($cedula);

    if (is_string($respuesta)) {
        $respuesta = json_decode($respuesta, true);
    }

    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
    exit;
}

/**---- RUTAS POST: CREACIÓN DE PERSONAS ------ */
if (isset($_GET['action']) && $_GET['action'] === 'crear_persona' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');
    
    // Captura segura de datos provenientes del formulario
    $id_genero            = isset($_POST['id_genero']) ? (int)$_POST['id_genero'] : 0;
    $cedula_identidad     = $_POST['cedula_identidad'] ?? '';
    $primer_nombre        = $_POST['primer_nombre'] ?? '';
    $segundo_nombre       = !empty(trim($_POST['segundo_nombre'])) ? $_POST['segundo_nombre'] : null;
    $primer_apellido      = $_POST['primer_apellido'] ?? '';
    $segundo_apellido     = !empty(trim($_POST['segundo_apellido'])) ? $_POST['segundo_apellido'] : null;
    $fecha_nacimiento     = $_POST['fecha_nacimiento'] ?? '';
    $telefono             = $_POST['telefono'] ?? '';
    $email                = $_POST['email'] ?? '';
    $direccion_habitacion = $_POST['direccion_habitacion'] ?? '';

    // Recordatorio: el controlador ya maneja la lógica de validación e inserción
    $respuesta = $personasCtrl->crearNuevaPersona(
        $id_genero,
        $cedula_identidad,
        $primer_nombre,
        $primer_apellido,
        $fecha_nacimiento,
        $telefono,
        $email,
        $direccion_habitacion,
        $segundo_nombre,
        $segundo_apellido
    );

    if (is_string($respuesta)) {
        $respuesta = json_decode($respuesta, true);
    }

    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
    exit; 
}

/**---- RUTAS POST: CREACIÓN DE CLIENTES ------ */
if (isset($_GET['action']) && $_GET['action'] === 'crear_cliente' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');
    
    $id_persona = isset($_POST['id_persona']) ? (int)$_POST['id_persona'] : 0;

    if ($id_persona <= 0) {
        echo json_encode(["status" => false, "message" => "Debe asociar una persona válida mediante la cédula."], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // El modelo de clientes se encarga de autogenerar el código de acceso a partir de los datos de la persona
    $respuesta = $clientesCtrl->crearClientes($id_persona);

    if (is_string($respuesta)) {
        $respuesta = json_decode($respuesta, true);
    }

    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
    exit;
}