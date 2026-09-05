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
require_once __DIR__ . '/../app/controllers/horariosController.php';
require_once __DIR__ . '/../app/controllers/horarioEntrenamientoController.php';


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Normalizar rol a formato Capitalizado para consistencia en la lógica PHP
if (isset($_SESSION['user_role'])) {
    $role_map = [
        'root' => 'Root',
        'administrador' => 'Administrador',
        'entrenador' => 'Entrenador',
        'cliente' => 'Cliente'
    ];
    $raw_role = strtolower(trim($_SESSION['user_role']));
    if (isset($role_map[$raw_role])) {
        $_SESSION['user_role'] = $role_map[$raw_role];
    }
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
$horariosCtrl = new horariosController($db);
$entrenamientoHorariosCtrl = new HorarioEntrenamientoController($db);

// Ejecutar verificación automática de vencimientos de mensualidades
pagosModel::verificarVencimientoMensualidades($db);

// Crear la tabla cliente_entrenamientos si no existe
try {
    if ($db) {
        $db->exec("CREATE TABLE IF NOT EXISTS cliente_entrenamientos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            id_cliente INT NOT NULL,
            id_entrenamiento INT NOT NULL,
            creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT fk_ce_cliente FOREIGN KEY (id_cliente) REFERENCES clientes(id) ON DELETE CASCADE,
            CONSTRAINT fk_ce_entrenamiento FOREIGN KEY (id_entrenamiento) REFERENCES entrenamiento(id) ON DELETE CASCADE,
            CONSTRAINT uq_cliente_entrenamiento UNIQUE (id_cliente, id_entrenamiento)
        ) ENGINE=InnoDB;");
    }
} catch (PDOException $e) {}

// Obtener ID del cliente logueado (si el rol es Cliente)
$logged_client_id = null;
if (isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'Cliente') {
    try {
        if ($db) {
            $stmt = $db->prepare("SELECT id FROM clientes WHERE id_persona = (SELECT id_persona FROM usuarios WHERE id = :uid LIMIT 1) LIMIT 1");
            $stmt->execute([':uid' => $_SESSION['user_id']]);
            $logged_client_id = $stmt->fetchColumn() ?: null;
        }
    } catch (PDOException $e) {
        $logged_client_id = null;
    }
}

// Obtener ID del entrenador logueado (si el rol es Entrenador)
$logged_entrenador_id = null;
$clasesEntrenador = [];
if (isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'Entrenador') {
    try {
        if ($db) {
            $stmt = $db->prepare("SELECT id FROM entrenadores WHERE id_persona = (SELECT id_persona FROM usuarios WHERE id = :uid LIMIT 1) LIMIT 1");
            $stmt->execute([':uid' => $_SESSION['user_id']]);
            $logged_entrenador_id = $stmt->fetchColumn() ?: null;

            if ($logged_entrenador_id !== null) {
                $stmtClases = $db->prepare("SELECT 
                    e.id AS id_entrenamiento,
                    e.nombre_entrenamiento,
                    e.descripcion,
                    s.sede AS Sede
                    FROM entrenamiento e
                    INNER JOIN sedes s ON e.id_sede = s.id
                    WHERE e.id_entrenador = :eid");
                $stmtClases->execute([':eid' => $logged_entrenador_id]);
                $clasesEntrenador = $stmtClases->fetchAll(PDO::FETCH_ASSOC);

                foreach ($clasesEntrenador as &$clase) {
                    $stmtHorarios = $db->prepare("SELECT 
                        eh.dia_semana,
                        h.hora_inicio,
                        h.hora_fin
                        FROM entrenamiento_horarios eh
                        INNER JOIN horarios h ON eh.id_horario = h.id
                        WHERE eh.id_entrenamiento = :id_ent");
                    $stmtHorarios->execute([':id_ent' => $clase['id_entrenamiento']]);
                    $clase['horarios'] = $stmtHorarios->fetchAll(PDO::FETCH_ASSOC);

                    $stmtClientes = $db->prepare("SELECT 
                        c.id,
                        CONCAT(p.primer_nombre, ' ', p.primer_apellido) AS cliente_nombre,
                        p.email,
                        p.telefono
                        FROM cliente_entrenamientos ce
                        INNER JOIN clientes c ON ce.id_cliente = c.id
                        INNER JOIN personas p ON c.id_persona = p.id
                        WHERE ce.id_entrenamiento = :id_ent");
                    $stmtClientes->execute([':id_ent' => $clase['id_entrenamiento']]);
                    $clase['clientes'] = $stmtClientes->fetchAll(PDO::FETCH_ASSOC);
                }
            }
        }
    } catch (PDOException $e) {
        $logged_entrenador_id = null;
    }
}

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
$respuestaPagos = [];
if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'Cliente' && isset($logged_client_id) && $logged_client_id !== null) {
    $respuestaPagos = $pagosCtrl->listarPagosPorCliente($logged_client_id);
} else {
    $respuestaPagos = $pagosCtrl->listarPagos();
}
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
    $estadoExito = $respuestaRoles['status'] ?? $respuestaRoles['success'] ?? false;
    if ($estadoExito && isset($respuestaRoles['data'])) {
        $rolesCrudos = $respuestaRoles['data'];
    } else {
        $rolesCrudos = [];
    }
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

/** --- CARGA DE PERSONAS Y CATÁLOGOS AUXILIARES --- */
$respuestaPersonas = $personasCtrl->listarPersonas();
$listaPersonas = [];
if (is_string($respuestaPersonas)) {
    $respuestaPersonas = json_decode($respuestaPersonas, true);
}
if (is_array($respuestaPersonas)) {
    $listaPersonas = $respuestaPersonas['data'] ?? (isset($respuestaPersonas[0]) ? $respuestaPersonas : []);
}

$listaGeneros = [];
$listaEstatusPersonas = [];
try {
    if ($db) {
        $listaGeneros = $db->query("SELECT id, descripcion FROM generos ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
        $listaEstatusPersonas = $db->query("SELECT id, nombre_estatus FROM estatus WHERE id IN (1, 2) ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    $listaGeneros = [];
    $listaEstatusPersonas = [];
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

// --- CARGA DINÁMICA DE HORARIOS Y ASIGNACIONES ---
$listaHorarios = [];
try {
    $respuestaHorarios = $horariosCtrl->listarHorarios();
    if (is_string($respuestaHorarios)) {
        $respuestaHorarios = json_decode($respuestaHorarios, true);
    }
    if (is_array($respuestaHorarios)) {
        $estadoExito = $respuestaHorarios['status'] ?? $respuestaHorarios['success'] ?? false;
        if ($estadoExito && isset($respuestaHorarios['data'])) {
            $listaHorarios = $respuestaHorarios['data'];
        } else {
            $listaHorarios = [];
        }
    }
} catch (Exception $e) {}

$listaHorariosAsignados = [];
try {
    $respuestaEh = $entrenamientoHorariosCtrl->listarEntrenamientoHorarios();
    if (is_string($respuestaEh)) {
        $respuestaEh = json_decode($respuestaEh, true);
    }
    if (is_array($respuestaEh)) {
        $estadoExito = $respuestaEh['status'] ?? $respuestaEh['success'] ?? false;
        if ($estadoExito && isset($respuestaEh['data'])) {
            $listaHorariosAsignados = $respuestaEh['data'];
        } else {
            $listaHorariosAsignados = [];
        }
    }
} catch (Exception $e) {}

$clienteEntrenamientos = [];
$entrenamientosDisponibles = [];
$listaTodosClienteEntrenamientos = [];

if ($logged_client_id !== null) {
    try {
        $resCliEnt = $entrenamientosCtrl->obtenerClienteEntrenamientos($logged_client_id);
        if (is_string($resCliEnt)) { $resCliEnt = json_decode($resCliEnt, true); }
        if (is_array($resCliEnt)) { $clienteEntrenamientos = $resCliEnt['data'] ?? []; }

        $resCliDisp = $entrenamientosCtrl->obtenerEntrenamientosDisponibles($logged_client_id);
        if (is_string($resCliDisp)) { $resCliDisp = json_decode($resCliDisp, true); }
        if (is_array($resCliDisp)) { $entrenamientosDisponibles = $resCliDisp['data'] ?? []; }
    } catch (Exception $e) {}
}

if ($_SESSION['user_role'] === 'Root' || $_SESSION['user_role'] === 'Administrador') {
    try {
        $resAll = $entrenamientosCtrl->obtenerTodosClienteEntrenamientos();
        if (is_string($resAll)) { $resAll = json_decode($resAll, true); }
        if (is_array($resAll)) { $listaTodosClienteEntrenamientos = $resAll['data'] ?? []; }
    } catch (Exception $e) {}
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



/**---- RUTAS POST/GET: GESTIÓN INTEGRAL DE PERSONAS ------ */
if (isset($_GET['action']) && $_GET['action'] === 'crear_persona' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    $id_genero = isset($_POST['id_genero']) ? (int) $_POST['id_genero'] : 0;
    $cedula_identidad = $_POST['cedula_identidad'] ?? '';
    $primer_nombre = $_POST['primer_nombre'] ?? '';
    $segundo_nombre = !empty(trim($_POST['segundo_nombre'] ?? '')) ? $_POST['segundo_nombre'] : null;
    $primer_apellido = $_POST['primer_apellido'] ?? '';
    $segundo_apellido = !empty(trim($_POST['segundo_apellido'] ?? '')) ? $_POST['segundo_apellido'] : null;
    $fecha_nacimiento = $_POST['fecha_nacimiento'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $email = $_POST['email'] ?? '';
    $direccion_habitacion = $_POST['direccion_habitacion'] ?? '';

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

if (isset($_GET['action']) && $_GET['action'] === 'actualizar_persona' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    $id_persona = isset($_POST['id_persona']) ? (int) $_POST['id_persona'] : 0;
    $id_genero = isset($_POST['id_genero']) ? (int) $_POST['id_genero'] : 0;
    $id_estatus = isset($_POST['id_estatus']) ? (int) $_POST['id_estatus'] : 1;
    $cedula_identidad = $_POST['cedula_identidad'] ?? '';
    $primer_nombre = $_POST['primer_nombre'] ?? '';
    $segundo_nombre = !empty(trim($_POST['segundo_nombre'] ?? '')) ? $_POST['segundo_nombre'] : null;
    $primer_apellido = $_POST['primer_apellido'] ?? '';
    $segundo_apellido = !empty(trim($_POST['segundo_apellido'] ?? '')) ? $_POST['segundo_apellido'] : null;
    $fecha_nacimiento = $_POST['fecha_nacimiento'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $email = $_POST['email'] ?? '';
    $direccion_habitacion = $_POST['direccion_habitacion'] ?? '';

    $respuesta = $personasCtrl->actualizarDatosPersona(
        $id_persona,
        $id_genero,
        $id_estatus,
        $cedula_identidad,
        $primer_nombre,
        $primer_apellido,
        $fecha_nacimiento,
        $email,
        $telefono,
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

if (isset($_GET['action']) && $_GET['action'] === 'eliminar_persona' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    $id_persona = isset($_POST['id_persona']) ? (int) $_POST['id_persona'] : 0;
    $respuesta = $personasCtrl->eliminarPersona($id_persona);

    if (is_string($respuesta)) {
        $respuesta = json_decode($respuesta, true);
    }

    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'cambiar_estatus_persona' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    $id_persona = isset($_POST['id_persona']) ? (int) $_POST['id_persona'] : 0;
    $nuevo_estatus = isset($_POST['nuevo_id_estatus']) ? (int) $_POST['nuevo_id_estatus'] : 1;

    $respuesta = $personasCtrl->cambiarEstatus($id_persona, $nuevo_estatus);

    if (is_string($respuesta)) {
        $respuesta = json_decode($respuesta, true);
    }

    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'obtener_persona') {
    header('Content-Type: application/json; charset=utf-8');

    $id_persona = isset($_REQUEST['id_persona']) ? (int) $_REQUEST['id_persona'] : 0;
    $respuesta = $personasCtrl->obtenerPersona($id_persona);

    if (is_string($respuesta)) {
        $respuesta = json_decode($respuesta, true);
    }

    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
    exit;
}


/**---- RUTAS POST: CREACIÓN DE CLIENTES ------ */
if (isset($_GET['action']) && $_GET['action'] === 'crear_cliente' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    $id_genero        = isset($_POST['id_genero']) ? (int) $_POST['id_genero'] : 0;
    $cedula_identidad = $_POST['cedula_identidad'] ?? '';
    $primer_nombre    = $_POST['primer_nombre'] ?? '';
    $segundo_nombre   = !empty(trim($_POST['segundo_nombre'] ?? '')) ? $_POST['segundo_nombre'] : null;
    $primer_apellido  = $_POST['primer_apellido'] ?? '';
    $segundo_apellido = !empty(trim($_POST['segundo_apellido'] ?? '')) ? $_POST['segundo_apellido'] : null;
    $fecha_nacimiento = $_POST['fecha_nacimiento'] ?? '';
    $telefono         = $_POST['telefono'] ?? '';
    $email            = $_POST['email'] ?? '';
    $direccion        = $_POST['direccion_habitacion'] ?? '';

    $respuesta = $clientesCtrl->crearClienteDirecto(
        $id_genero,
        $cedula_identidad,
        $primer_nombre,
        $segundo_nombre,
        $primer_apellido,
        $segundo_apellido,
        $fecha_nacimiento,
        $telefono,
        $email,
        $direccion
    );

    if (is_string($respuesta)) {
        $respuesta = json_decode($respuesta, true);
    }

    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
    exit;
}

/**---- RUTAS POST: ACTUALIZACIÓN DE CLIENTES ------ */
if (isset($_GET['action']) && $_GET['action'] === 'actualizar_cliente' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    $id_cliente       = isset($_POST['id_cliente']) ? (int) $_POST['id_cliente'] : 0;
    $id_estatus       = isset($_POST['id_estatus']) ? (int) $_POST['id_estatus'] : 0;
    $id_genero        = isset($_POST['id_genero']) ? (int) $_POST['id_genero'] : 0;
    $cedula_identidad = $_POST['cedula_identidad'] ?? '';
    $primer_nombre    = $_POST['primer_nombre'] ?? '';
    $segundo_nombre   = !empty(trim($_POST['segundo_nombre'] ?? '')) ? $_POST['segundo_nombre'] : null;
    $primer_apellido  = $_POST['primer_apellido'] ?? '';
    $segundo_apellido = !empty(trim($_POST['segundo_apellido'] ?? '')) ? $_POST['segundo_apellido'] : null;
    $fecha_nacimiento = $_POST['fecha_nacimiento'] ?? '';
    $telefono         = $_POST['telefono'] ?? '';
    $email            = $_POST['email'] ?? '';
    $direccion        = $_POST['direccion_habitacion'] ?? '';

    $respuesta = $clientesCtrl->actualizarClienteCompleto(
        $id_cliente,
        $id_estatus,
        $id_genero,
        $cedula_identidad,
        $primer_nombre,
        $segundo_nombre,
        $primer_apellido,
        $segundo_apellido,
        $fecha_nacimiento,
        $telefono,
        $email,
        $direccion
    );

    if (is_string($respuesta)) {
        $respuesta = json_decode($respuesta, true);
    }

    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
    exit;
}

/**---- RUTAS POST: ELIMINACIÓN DE CLIENTES ------ */
if (isset($_GET['action']) && $_GET['action'] === 'eliminar_cliente' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    $id_cliente = isset($_POST['id_cliente']) ? (int) $_POST['id_cliente'] : 0;

    $respuesta = $clientesCtrl->eliminarCliente($id_cliente);

    if (is_string($respuesta)) {
        $respuesta = json_decode($respuesta, true);
    }

    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
    exit;
}

// =========================================================================
// 3. ENRUTADOR DE PASARELA API
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

/**---- NUEVA RUTA POST: ACTUALIZAR USUARIO ------ */
if (isset($_GET['action']) && $_GET['action'] === 'actualizar_usuario' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    $id_usuario = isset($_POST['id_usuario']) ? (int) $_POST['id_usuario'] : 0;
    $id_estatus = isset($_POST['id_estatus']) ? (int) $_POST['id_estatus'] : 1; 
    $id_rol = isset($_POST['id_rol']) ? (int) $_POST['id_rol'] : 0;
    $usuario = $_POST['usuario'] ?? '';

    $respuesta = $userCtrl->actualizarUsuarios($id_usuario, $id_estatus, $id_rol, $usuario, $usuario);

    if (is_string($respuesta)) {
        $respuesta = json_decode($respuesta, true);
    }

    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
    exit;
}

/**---- NUEVA RUTA POST: ELIMINAR USUARIO ------ */
if (isset($_GET['action']) && $_GET['action'] === 'eliminar_usuario' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    $id_usuario = isset($_POST['id_usuario']) ? (int) $_POST['id_usuario'] : 0;

    $respuesta = $userCtrl->eliminarUsuario($id_usuario);

    if (is_string($respuesta)) {
        $respuesta = json_decode($respuesta, true);
    }

    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
    exit;
}

/**---- NUEVA RUTA POST: CAMBIAR ESTATUS USUARIO ------ */
if (isset($_GET['action']) && $_GET['action'] === 'cambiar_estatus_usuario' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    $id_usuario = isset($_POST['id_usuario']) ? (int) $_POST['id_usuario'] : 0;
    $nuevo_id_estatus = isset($_POST['nuevo_id_estatus']) ? (int) $_POST['nuevo_id_estatus'] : 0;

    $respuesta = $userCtrl->cambiarEstatusUsuario($id_usuario, $nuevo_id_estatus);

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
        'id' => $_POST['id_entrenamiento'] ?? '',
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

/**---- [NUEVO] RUTA POST: ELIMINAR ENTRENAMIENTO ------ */
if (isset($_GET['action']) && $_GET['action'] === 'eliminar_entrenamiento' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    $id_entrenamiento = isset($_POST['id_entrenamiento']) ? (int) $_POST['id_entrenamiento'] : 0;

    $respuesta = $entrenamientosCtrl->eliminarEntrenamiento($id_entrenamiento);

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

/**---- NUEVA RUTA POST: ELIMINAR ROLES ------ */
if (isset($_GET['action']) && $_GET['action'] === 'eliminar_rol' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    $id_rol = isset($_POST['id_rol']) ? (int) $_POST['id_rol'] : 0;

    $respuesta = $rolesCtrl->eliminarRol($id_rol);

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

/**---- NUEVA RUTA POST: ACTUALIZAR PAGO ------ */
if (isset($_GET['action']) && $_GET['action'] === 'actualizar_pago' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    $id_pago = isset($_POST['id_pago']) ? (int)$_POST['id_pago'] : 0;
    $id_banco = isset($_POST['id_banco']) ? (int)$_POST['id_banco'] : 0;
    $id_cliente = isset($_POST['id_cliente']) ? (int)$_POST['id_cliente'] : 0;
    $id_plan = isset($_POST['id_cliente_plan']) ? (int)$_POST['id_cliente_plan'] : 0;
    $id_estatus = isset($_POST['id_estatus']) ? (int)$_POST['id_estatus'] : 0;
    $monto = isset($_POST['monto']) ? (float)$_POST['monto'] : 0.0;
    $fecha_pago = $_POST['fecha_pago'] ?? '';
    $cod_referencia = $_POST['cod_referencia'] ?? '';

    $respuesta = $pagosCtrl->actualizarPago(
        $id_pago,
        $id_banco,
        $id_cliente,
        $id_plan,
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

/**---- NUEVA RUTA POST: ELIMINAR PAGO ------ */
if (isset($_GET['action']) && $_GET['action'] === 'eliminar_pago' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    $id_pago = isset($_POST['id_pago']) ? (int)$_POST['id_pago'] : 0;

    $respuesta = $pagosCtrl->eliminarPago($id_pago);

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

/**---- NUEVA RUTA POST: ACTUALIZAR PERMISO ------ */
if (isset($_GET['action']) && $_GET['action'] === 'actualizar_permiso' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    $id_permiso = isset($_POST['id_permiso']) ? (int) $_POST['id_permiso'] : 0;
    $permiso = $_POST['nombre_permiso'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';

    $respuesta = $permisosCtrl->actualizarPermiso($id_permiso, $permiso, $descripcion);

    if (is_string($respuesta)) {
        $respuesta = json_decode($respuesta, true);
    }

    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
    exit;
}

/**---- NUEVA RUTA POST: ELIMINAR PERMISO ------ */
if (isset($_GET['action']) && $_GET['action'] === 'eliminar_permiso' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    $id_permiso = isset($_POST['id_permiso']) ? (int) $_POST['id_permiso'] : 0;

    $respuesta = $permisosCtrl->eliminarPermiso($id_permiso);

    if (is_string($respuesta)) {
        $respuesta = json_decode($respuesta, true);
    }

    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
    exit;
}

/**---- NUEVA RUTA POST: INSCRIBIR CLIENTE A ENTRENAMIENTO ------ */
if (isset($_GET['action']) && $_GET['action'] === 'inscribir_cliente_entrenamiento' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    $id_cliente = isset($_POST['id_cliente']) ? (int) $_POST['id_cliente'] : 0;
    $id_entrenamiento = isset($_POST['id_entrenamiento']) ? (int) $_POST['id_entrenamiento'] : 0;

    if ($id_cliente <= 0 && $_SESSION['user_role'] === 'Cliente') {
        $id_cliente = $logged_client_id;
    }

    $respuesta = $entrenamientosCtrl->inscribirCliente($id_cliente, $id_entrenamiento);

    if (is_string($respuesta)) {
        $respuesta = json_decode($respuesta, true);
    }

    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
    exit;
}

/**---- NUEVA RUTA POST: DESINSCRIBIR CLIENTE DE ENTRENAMIENTO ------ */
if (isset($_GET['action']) && $_GET['action'] === 'desinscribir_cliente_entrenamiento' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    $id_cliente = isset($_POST['id_cliente']) ? (int) $_POST['id_cliente'] : 0;
    $id_entrenamiento = isset($_POST['id_entrenamiento']) ? (int) $_POST['id_entrenamiento'] : 0;

    if ($id_cliente <= 0 && $_SESSION['user_role'] === 'Cliente') {
        $id_cliente = $logged_client_id;
    }

    $respuesta = $entrenamientosCtrl->desinscribirCliente($id_cliente, $id_entrenamiento);

    if (is_string($respuesta)) {
        $respuesta = json_decode($respuesta, true);
    }

    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
    exit;
}

/**---- NUEVA RUTA POST: ASIGNAR HORARIO A ENTRENAMIENTO ------ */
if (isset($_GET['action']) && $_GET['action'] === 'asignar_entrenamiento_horario' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    $datos = [
        'id_entrenamiento' => $_POST['id_entrenamiento'] ?? '',
        'id_horario' => $_POST['id_horario'] ?? '',
        'dia_semana' => $_POST['dia_semana'] ?? ''
    ];

    $respuesta = $entrenamientoHorariosCtrl->asignarEntrenamientoHorario($datos);

    if (is_string($respuesta)) {
        $respuesta = json_decode($respuesta, true);
    }

    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
    exit;
}

/**---- NUEVA RUTA POST: DESASIGNAR HORARIO DE ENTRENAMIENTO ------ */
if (isset($_GET['action']) && $_GET['action'] === 'desasignar_entrenamiento_horario' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    $datos = [
        'id_entrenamiento' => $_POST['id_entrenamiento'] ?? '',
        'id_horario' => $_POST['id_horario'] ?? '',
        'dia_semana' => $_POST['dia_semana'] ?? ''
    ];

    $respuesta = $entrenamientoHorariosCtrl->desasignarEntrenamientoHorario($datos);

    if (is_string($respuesta)) {
        $respuesta = json_decode($respuesta, true);
    }

    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
    exit;
}

/**---- NUEVA RUTA POST: CREAR BLOQUE HORARIO ------ */
if (isset($_GET['action']) && $_GET['action'] === 'crear_horario' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    $datos = [
        'hora_inicio' => $_POST['hora_inicio'] ?? '',
        'hora_fin' => $_POST['hora_fin'] ?? ''
    ];

    $respuesta = $horariosCtrl->crearHorario($datos);

    if (is_string($respuesta)) {
        $respuesta = json_decode($respuesta, true);
    }

    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
    exit;
}

/**---- NUEVA RUTA POST: ACTUALIZAR BLOQUE HORARIO ------ */
if (isset($_GET['action']) && $_GET['action'] === 'actualizar_horario' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    $datos = [
        'id' => $_POST['id_horario'] ?? '',
        'hora_inicio' => $_POST['hora_inicio'] ?? '',
        'hora_fin' => $_POST['hora_fin'] ?? ''
    ];

    $respuesta = $horariosCtrl->actualizarHorario($datos);

    if (is_string($respuesta)) {
        $respuesta = json_decode($respuesta, true);
    }

    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
    exit;
}

/**---- NUEVA RUTA POST: ELIMINAR BLOQUE HORARIO ------ */
if (isset($_GET['action']) && $_GET['action'] === 'eliminar_horario' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    $id_horario = isset($_POST['id_horario']) ? (int)$_POST['id_horario'] : 0;

    $respuesta = $horariosCtrl->eliminarHorario($id_horario);

    if (is_string($respuesta)) {
        $respuesta = json_decode($respuesta, true);
    }

    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
    exit;
}