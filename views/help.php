<?php
require_once __DIR__ . '/../config/init.php';
require_once __DIR__ . '/../app/controllers/userController.php';
require_once __DIR__ . '/../app/controllers/rolesController.php';
require_once __DIR__ . '/../app/controllers/personasController.php';
require_once __DIR__ . '/../app/controllers/clientesController.php';
require_once __DIR__ . '/../app/controllers/entrenadoresController.php';
require_once __DIR__ . '/../app/controllers/entrenamientosController.php';
require_once __DIR__ . '/../app/controllers/permisosController.php';
require_once __DIR__ . '/../app/controllers/pagosController.php';


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
$permisosCtrl = new permisosController($db);
$pagosCtrl = new pagosController($db);

// =========================================================================
// 2. CARGA DE DATOS PARA LAS VISTAS (TABLAS Y SELECTS)
// =========================================================================

/** --- CARGA DINÁMICA DE PERMISOS PARA LA TABLA --- */
$respuestaPermisos = $permisosCtrl->listarPermisos();
$listaPermisos = [];

if (is_string($respuestaPermisos)) {
    $respuestaPermisos = json_decode($respuestaPermisos, true);
}

if (is_array($respuestaPermisos)) {
    // Los registros del controlador vienen dentro del índice 'data' gracias al método helper response()
    $listaPermisos = $respuestaPermisos['data'] ?? [];
}

// --- CARGA DINÁMICA DE PAGOS PARA LA TABLA ---
$respuestaPagos = $pagosCtrl->listarPagos();
$listaPagos = []; // Inicializamos estrictamente vacío por defecto

if (is_string($respuestaPagos)) {
    $respuestaPagos = json_decode($respuestaPagos, true);
}

if (is_array($respuestaPagos)) {
    // Validamos si la respuesta del controlador fue exitosa mediante 'status' o 'success'
    $exitoPagos = $respuestaPagos['status'] ?? $respuestaPagos['success'] ?? false;

    if ($exitoPagos && isset($respuestaPagos['data'])) {
        $listaPagos = $respuestaPagos['data'];
    } else {
        // Si el controlador reportó un error o no tiene la clave 'data', aseguramos un array vacío
        $listaPagos = isset($respuestaPagos['data']) && is_array($respuestaPagos['data']) ? $respuestaPagos['data'] : [];
    }
}

// --- CARGA DINÁMICA DE BANCOS ---
$listaBancos = [];
try {
    if ($db) {
        $stmtBancos = $db->query("SELECT id, nombre_banco FROM bancos ORDER BY nombre_banco ASC");
        $listaBancos = $stmtBancos->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) { $listaBancos = []; }

// --- CARGA DINÁMICA DE ESTATUS ---
$listaEstatus = [];
try {
    if ($db) {
        $stmtEstatus = $db->query("SELECT id, nombre_estatus FROM estatus ORDER BY nombre_estatus ASC");
        $listaEstatus = $stmtEstatus->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) { $listaEstatus = []; }

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
$respuestaEntrenadores = $entrenadoresCtrl->obtenerEntrenadores();
$listaEntrenadores = [];

if (is_string($respuestaEntrenadores)) {
    $respuestaEntrenadores = json_decode($respuestaEntrenadores, true);
}
if (is_array($respuestaEntrenadores)) {
    $listaEntrenadores = $respuestaEntrenadores['data'] ?? [];
}

/** --- CARGA DE COMPONENTES DEL MÓDULO DE ENTRENAMIENTOS --- */

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
$respuestaEntrenadores = $entrenadoresCtrl->obtenerEntrenadores();
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
        $stmtSedes = $db->query("SELECT id, sede FROM sedes ORDER BY sede ASC");
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
$listaClientes = []; // Inicializamos vacío por defecto

if (is_string($respuestaClientes)) {
    $respuestaClientes = json_decode($respuestaClientes, true);
}

if (is_array($respuestaClientes)) {
    // Verificamos si la respuesta del controlador fue exitosa (status o success en true)
    $estadoExito = $respuestaClientes['status'] ?? $respuestaClientes['success'] ?? false;
    
    if ($estadoExito && isset($respuestaClientes['data'])) {
        $listaClientes = $respuestaClientes['data'];
    } else {
        // Si el controlador devolvió un error o el formato es una lista directa
        $listaClientes = isset($respuestaClientes['data']) ? $respuestaClientes['data'] : [];
    }
}

// Carga de planes generales de gimnasio para el Selector del Modal
$listaPlanesDisponibles = [];
try {
    if ($db) {
        $stmtPlanes = $db->query("SELECT id, nombre_plan, precio, duracion_dias FROM planes ORDER BY nombre_plan ASC");
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
            $stmt = $db->prepare("SELECT id, primer_nombre, primer_apellido FROM personas WHERE cedula_identidad = :cedula LIMIT 1");
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


/**---- NUEVA RUTA POST: CREACIÓN DE ROLES ------ */
if (isset($_GET['action']) && $_GET['action'] === 'crear_rol' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    $nombre_rol  = $_POST['nombre_rol'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';

    // Ejecutamos el controlador directamente pasándole los parámetros limpios
    $respuesta = $rolesCtrl->crearNuevoRol($nombre_rol, $descripcion);

    if (is_string($respuesta)) {
        $respuesta = json_decode($respuesta, true);
    }

    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
    exit;
}

/**---- NUEVA RUTA POST: ACTUALIZACIÓN DE ROLES ------ */
if (isset($_GET['action']) && $_GET['action'] === 'actualizar_rol' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    $id_rol      = isset($_POST['id_rol']) ? (int) $_POST['id_rol'] : 0;
    $nombre_rol  = $_POST['nombre_rol'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';

    // Invocamos la actualización mapeada de tu rolesController
    $respuesta = $rolesCtrl->actualizarDatosRol($id_rol, $nombre_rol, $descripcion);

    if (is_string($respuesta)) {
        $respuesta = json_decode($respuesta, true);
    }

    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
    exit;
}

/**---- NUEVA RUTA POST: REGISTRAR PAGO ------ */
if (isset($_GET['action']) && $_GET['action'] === 'registrar_pago' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    $id_banco        = isset($_POST['id_banco']) ? (int)$_POST['id_banco'] : 0;
    $id_cliente      = isset($_POST['id_cliente']) ? (int)$_POST['id_cliente'] : 0;
    $id_cliente_plan = isset($_POST['id_cliente_plan']) ? (int)$_POST['id_cliente_plan'] : 0;
    $id_user         = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0; // Se obtiene seguro desde la sesión activa
    $id_estatus      = isset($_POST['id_estatus']) ? (int)$_POST['id_estatus'] : 0;
    $monto           = isset($_POST['monto']) ? (float)$_POST['monto'] : 0.0;
    $fecha_pago      = $_POST['fecha_pago'] ?? '';
    $cod_referencia  = $_POST['cod_referencia'] ?? '';

    $respuesta = $pagosCtrl->registrarPago(
        $id_banco,
        $id_cliente,
        $id_cliente_plan,
        $id_user,
        $id_estatus,
        $monto,
        $fecha_pago,
        $cod_referencia
    );

    if (is_string($respuesta)) {
        $respuesta = json_decode($respuesta, true);
    }

    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
    exit;
}

/**---- NUEVA RUTA POST: CAMBIAR ESTATUS PAGO ------ */
if (isset($_GET['action']) && $_GET['action'] === 'cambiar_estatus_pago' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    $id_pago          = isset($_POST['id_pago']) ? (int)$_POST['id_pago'] : 0;
    $nuevo_id_estatus = isset($_POST['nuevo_id_estatus']) ? (int)$_POST['nuevo_id_estatus'] : 0;

    $respuesta = $pagosCtrl->cambiarEstatusPago($id_pago, $nuevo_id_estatus);

    if (is_string($respuesta)) {
        $respuesta = json_decode($respuesta, true);
    }

    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
    exit;
}

/**---- NUEVA RUTA POST: CREACIÓN DE PERMISOS ------ */
if (isset($_GET['action']) && $_GET['action'] === 'crear_permiso' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    // Captura limpia de los campos del formulario
    $permiso     = $_POST['nombre_permiso'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';

    if (empty(trim($permiso)) || empty(trim($descripcion))) {
        echo json_encode(["status" => false, "message" => "Todos los campos son estrictamente obligatorios."], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Ejecutamos el método del controlador de permisos
    $respuesta = $permisosCtrl->crearPermiso($permiso, $descripcion);

    if (is_string($respuesta)) {
        $respuesta = json_decode($respuesta, true);
    }

    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
    exit;
}