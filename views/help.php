<?php
require_once __DIR__ . '/../config/init.php';
require_once __DIR__ . '/../app/controllers/userController.php';
require_once __DIR__ . '/../app/controllers/rolesController.php';
require_once __DIR__ . '/../app/controllers/personasController.php';
require_once __DIR__ . '/../app/controllers/clientesController.php';
require_once __DIR__ . '/../app/controllers/entrenadoresController.php';
require_once __DIR__ . '/../app/controllers/entrenamientosController.php';
require_once __DIR__ . '/../app/controllers/clientesPlanController.php';

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
$userCtrl = new UsuariosController($db);
$rolesCtrl = new rolesController($db);
$personasCtrl = new PersonasController($db);
$clientesCtrl = new ClientesController($db);
$entrenadoresCtrl = new EntrenadoresController($db);
$entrenamientosCtrl = new EntrenamientosController($db);
$clientesPlanCtrl = new ClientesPlanController($db);

// =========================================================================
// 2. CARGA DE DATOS PARA LAS VISTAS (TABLAS Y SELECTS)
// =========================================================================

/** --- CARGA DINÁMICA DE USUARIOS PARA LA TABLA --- */
$respuestaUsuarios = $userCtrl->listarUsuarios();
$listaUsuarios = []; // Inicializamos vacío por seguridad

if (is_string($respuestaUsuarios)) {
    $respuestaUsuarios = json_decode($respuestaUsuarios, true);
}

if (is_array($respuestaUsuarios)) {
    // Como tu controlador usa $this->response(true, "...", $data), 
    // los registros reales vienen dentro del índice 'data'
    $listaUsuarios = $respuestaUsuarios['data'] ?? [];
}


/** --- CARGA DINAMICA DE ENTRENADORES */
$respuestaEntrenadores = $entrenadoresCtrl->listarEntrenadores();
$listaEntrenadores = [];

if (is_string($respuestaEntrenadores)) {
    $respuestaEntrenadores = json_decode($respuestaEntrenadores, true);
}
if (is_array($respuestaEntrenadores)) {
    $listaEntrenadores = $respuestaEntrenadores['data'] ?? [];
}

/** --- [NUEVO] CARGA DE COMPONENTES DEL MÓDULO DE ENTRENAMIENTOS --- */

// A. Obtener listado general de entrenamientos planeados
$respuestaEntrenamientos = $entrenamientosCtrl->listarEntrenamientos();
$listaEntrenamientos = [];
if (is_string($respuestaEntrenamientos)) {
    $respuestaEntrenamientos = json_decode($respuestaEntrenamientos, true);
}
if (is_array($respuestaEntrenamientos)) {
    $listaEntrenamientos = $respuestaEntrenamientos['data'] ?? $respuestaEntrenamientos;
}

// B. Obtener entrenadores disponibles para el selector modal
$respuestaEntrenadores = $entrenadoresCtrl->listarEntrenadores();
$listaEntrenadores = [];
if (is_string($respuestaEntrenadores)) {
    $respuestaEntrenadores = json_decode($respuestaEntrenadores, true);
}
if (is_array($respuestaEntrenadores)) {
    $listaEntrenadores = $respuestaEntrenadores['data'] ?? $respuestaEntrenadores;
}

// C. Obtener sedes directamente por PDO para poblar los complejos del gimnasio
$listaSedes = [];
try {
    if ($db) {
        $stmtSedes = $db->query("SELECT id, sede FROM administracion.sedes ORDER BY sede ASC");
        $listaSedes = $stmtSedes->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    $listaSedes = [];
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

$listaRoles = $rolesCrudos;

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
    // Si viene envuelto en el helper response structure ['data']
    $listaClientes = $respuestaClientes['data'] ?? $respuestaClientes;
}
/** MEMBRESIAS */
$respuestaMembresias = $clientesPlanCtrl->listarClientesPlanes();
$listaMembresias = [];
if (is_string($respuestaMembresias)) {
    $respuestaMembresias = json_decode($respuestaMembresias, true);
}
if (is_array($respuestaMembresias) && isset($respuestaMembresias['status']) && $respuestaMembresias['status'] === true) {
    $listaMembresias = $respuestaMembresias['data'] ?? [];
}

// Carga de planes generales de gimnasio para el Selector del Modal
$listaPlanesDisponibles = [];
try {
    if ($db) {
        $stmtPlanes = $db->query("SELECT id, nombre_plan, precio, duracion_dias FROM administracion.planes ORDER BY nombre_plan ASC");
        $listaPlanesDisponibles = $stmtPlanes->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    $listaPlanesDisponibles = [];
}


// =========================================================================
// 3. ENRUTADOR DE PASARELA API (RUTAS ASÍNCRONAS AJAX/FETCH)
// =========================================================================

/**---- NUEVA RUTA GET: BUSCAR PERSONA POR CÉDULA ------ */
if (isset($_GET['action']) && $_GET['action'] === 'buscar_persona' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    header('Content-Type: application/json; charset=utf-8');
    $cedula = trim($_GET['cedula_identidad'] ?? '');

    if ($cedula === '') {
        echo json_encode(["status" => false, "message" => "La cédula es requerida."], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $respuesta = null;

    // 1. Intentamos usar los métodos estándar de tu controlador de personas si existen
    if (method_exists($personasCtrl, 'buscarPorCedula')) {
        $respuesta = $personasCtrl->buscarPorCedula($cedula);
    } else if (method_exists($personasCtrl, 'buscarPersonaPorCedula')) {
        $respuesta = $personasCtrl->buscarPersonaPorCedula($cedula);
    } else {
        // 2. Solución de respaldo directa usando PDO si los nombres de métodos varían
        try {
            $stmt = $db->prepare("SELECT id, primer_nombre, primer_apellido FROM administracion.personas WHERE cedula_identidad = :cedula LIMIT 1");
            $stmt->bindParam(':cedula', $cedula, PDO::PARAM_STR);
            $stmt->execute();
            $persona = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($persona) {
                $respuesta = ["status" => true, "message" => "Persona encontrada", "data" => $persona];
            } else {
                $respuesta = ["status" => false, "message" => "La persona con cédula $cedula no existe en el sistema."];
            }
        } catch (PDOException $e) {
            $respuesta = ["status" => false, "message" => "Error de base de datos: " . $e->getMessage()];
        }
    }

    // Si el controlador devolvió un string JSON, lo decodificamos antes de imprimirlo de forma unificada
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
    $id_genero = isset($_POST['id_genero']) ? (int) $_POST['id_genero'] : 0;
    $cedula_identidad = $_POST['cedula_identidad'] ?? '';
    $primer_nombre = $_POST['primer_nombre'] ?? '';
    $segundo_nombre = !empty(trim($_POST['segundo_nombre'])) ? $_POST['segundo_nombre'] : null;
    $primer_apellido = $_POST['primer_apellido'] ?? '';
    $segundo_apellido = !empty(trim($_POST['segundo_apellido'])) ? $_POST['segundo_apellido'] : null;
    $fecha_nacimiento = $_POST['fecha_nacimiento'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $email = $_POST['email'] ?? '';
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

    $id_persona = isset($_POST['id_persona']) ? (int) $_POST['id_persona'] : 0;

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

// =========================================================================
// 3. ENRUTADOR DE PASARELA API (AL FINAL DEL ARCHIVO)
// =========================================================================

/**---- NUEVA RUTA POST: CREACIÓN DE USUARIOS ------ */
if (isset($_GET['action']) && $_GET['action'] === 'crear_usuario' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    // Captura de datos del formulario
    $id_persona = isset($_POST['id_persona']) ? (int) $_POST['id_persona'] : 0;
    $usuario = $_POST['usuario'] ?? '';
    $id_rol = isset($_POST['id_rol']) ? (int) $_POST['id_rol'] : 0;

    if ($id_persona <= 0 || empty(trim($usuario)) || $id_rol <= 0) {
        echo json_encode(["status" => false, "message" => "Todos los campos son estrictamente obligatorios."], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Ejecutamos el método del controlador de usuarios
    $respuesta = $userCtrl->crearNuevoUsuario($id_persona, $usuario, $id_rol);

    // Si el controlador devuelve un JSON en string, lo decodificamos para enviarlo limpio
    if (is_string($respuesta)) {
        $respuesta = json_decode($respuesta, true);
    }

    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
    exit;
}

/**---- NUEVA RUTA POST: REGISTRAR / ASIGNAR ENTRENADOR ------ */
if (isset($_GET['action']) && $_GET['action'] === 'crear_entrenador' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    // Estructuramos el array esperado por EntrenadoresController::crearEntrenadores
    $datosEntrenador = [
        'id_persona' => $_POST['id_persona'] ?? '',
        'especialidad' => $_POST['especialidad'] ?? ''
    ];

    $respuesta = $entrenadoresCtrl->crearEntrenadores($datosEntrenador);

    if (is_string($respuesta)) {
        $respuesta = json_decode($respuesta, true);
    }

    // Tu controlador retorna la respuesta estructurada mediante $this->response()
    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
    exit;
}

/**---- [NUEVO] RUTA POST: REGISTRAR NUEVO ENTRENAMIENTO ------ */
if (isset($_GET['action']) && $_GET['action'] === 'crear_entrenamiento' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    $datos = [
        'id_entrenador' => $_POST['id_entrenador'] ?? '',
        'id_sede' => $_POST['id_sede'] ?? '',
        'nombre_entrenamiento' => $_POST['nombre_entrenamiento'] ?? '',
        'descripcion' => $_POST['descripcion'] ?? ''
    ];

    $respuesta = $entrenamientosCtrl->crearEntrenamientos($datos);

    if (is_string($respuesta)) {
        $respuesta = json_decode($respuesta, true);
    }

    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
    exit;
}

/**---- [NUEVO] RUTA POST: ACTUALIZAR ENTRENAMIENTO EXISTENTE ------ */
if (isset($_GET['action']) && $_GET['action'] === 'actualizar_entrenamiento' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    $datos = [
        'id_entrenador' => $_POST['id_entrenador'] ?? '',
        'id_sede' => $_POST['id_sede'] ?? '',
        'nombre_entrenamiento' => $_POST['nombre_entrenamiento'] ?? '',
        'descripcion' => $_POST['descripcion'] ?? ''
    ];

    $respuesta = $entrenamientosCtrl->actualizarEntrenamientos($datos);

    if (is_string($respuesta)) {
        $respuesta = json_decode($respuesta, true);
    }

    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'asignar_membresia' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    $datos = [
        'id_cliente' => $_POST['id_cliente'] ?? '',
        'id_plan' => $_POST['id_plan'] ?? '',
        'fecha_inicio' => $_POST['fecha_inicio'] ?? ''
    ];

    $respuesta = $clientesPlanCtrl->asignarClientePlan($datos);

    if (is_string($respuesta)) {
        $respuesta = json_decode($respuesta, true);
    }

    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
    exit;
}