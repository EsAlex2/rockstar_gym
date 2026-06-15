<?php
require_once __DIR__ . '/../../config/init.php';
require_once __DIR__ . '/../../controllers/userController.php';

// Asegurar que solo peticiones POST o JSON procesen esta vía
header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificación de seguridad básica
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => false, "message" => "Sesión no autorizada."]);
    exit;
}

// Reemplazar '$pdo' por la variable global o de conexión real que inicializas en init.php
$db = $pdo ?? null; 
if (!$db) {
    echo json_encode(["status" => false, "message" => "Error de conexión a la base de datos."]);
    exit;
}

$controller = new userController($db);
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'crear':
            $id_persona = isset($_POST['id_persona']) ? (int)$_POST['id_persona'] : 0;
            $id_rol = isset($_POST['id_rol']) ? (int)$_POST['id_rol'] : 0;
            $email = isset($_POST['email']) ? trim($_POST['email']) : '';

            $resultado = $controller->crearUsuarios($id_persona, $id_rol, $email);
            echo json_encode($resultado);
            break;

        case 'actualizar':
            $id_usuario = isset($_POST['id_usuario']) ? (int)$_POST['id_usuario'] : 0;
            $id_estatus = isset($_POST['id_estatus']) ? (int)$_POST['id_estatus'] : 0;
            $id_rol = isset($_POST['id_rol']) ? (int)$_POST['id_rol'] : 0;
            $username = isset($_POST['username']) ? trim($_POST['username']) : '';
            $email = isset($_POST['email']) ? trim($_POST['email']) : '';

            $resultado = $controller->actualizarUsuarios($id_usuario, $id_estatus, $id_rol, $username, $email);
            echo json_encode($resultado);
            break;

        case 'cambiar_pass':
            $username = isset($_POST['username']) ? trim($_POST['username']) : '';
            $email = isset($_POST['email']) ? trim($_POST['email']) : '';
            $pass = isset($_POST['password']) ? trim($_POST['password']) : '';

            $resultado = $controller->cambiarContraseña($username, $email, $pass);
            echo json_encode($resultado);
            break;

        default:
            echo json_encode(["status" => false, "message" => "Acción no válida."]);
            break;
    }
} catch (Exception $e) {
    echo json_encode(["status" => false, "message" => "Error del sistema: " . $e->getMessage()]);
}
exit;