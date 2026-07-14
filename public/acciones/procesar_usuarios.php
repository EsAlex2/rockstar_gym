<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/init.php';
require_once __DIR__ . '/../app/controllers/userController.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Sesión inválida o expirada."], JSON_UNESCAPED_UNICODE);
    exit;
}

$db = $pdo ?? null;
$userCtrl = new UsuariosController($db);

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'crear':
            $id_persona = isset($_POST['id_persona']) ? (int)$_POST['id_persona'] : 0;
            $id_rol     = isset($_POST['id_rol']) ? (int)$_POST['id_rol'] : 0;
            $email      = isset($_POST['email']) ? trim($_POST['email']) : '';

            if ($id_persona <= 0) {
                echo json_encode(["success" => false, "message" => "Debe buscar y seleccionar una persona por cédula."], JSON_UNESCAPED_UNICODE);
                exit;
            }

            if ($id_rol <= 0) {
                echo json_encode(["success" => false, "message" => "Debe seleccionar un rol para el usuario."], JSON_UNESCAPED_UNICODE);
                exit;
            }

            $respuesta = $userCtrl->crearUsuario($id_persona, $id_rol, $email);

            if (is_string($respuesta)) {
                $respuesta = json_decode($respuesta, true);
            }

            if (isset($respuesta['status'])) {
                $respuesta['success'] = $respuesta['status'];
            }

            echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
            break;

        case 'actualizar':
            $id_usuario = isset($_POST['id_usuario']) ? (int)$_POST['id_usuario'] : 0;
            $id_estatus = isset($_POST['id_estatus']) ? (int)$_POST['id_estatus'] : 1;
            $id_rol     = isset($_POST['id_rol']) ? (int)$_POST['id_rol'] : 0;
            $username   = isset($_POST['username']) ? trim($_POST['username']) : '';
            $email      = isset($_POST['email']) ? trim($_POST['email']) : '';
            $password   = isset($_POST['password']) ? trim($_POST['password']) : '';

            $respuesta = $userCtrl->actualizarUsuarios($id_usuario, $id_estatus, $id_rol, $username, $email, $password);

            if (is_string($respuesta)) {
                $respuesta = json_decode($respuesta, true);
            }

            if (isset($respuesta['status'])) {
                $respuesta['success'] = $respuesta['status'];
            }

            echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
            break;

        case 'cambiar_pass':
            $username = isset($_POST['username']) ? trim($_POST['username']) : '';
            $email    = isset($_POST['email']) ? trim($_POST['email']) : '';
            $password = isset($_POST['password']) ? trim($_POST['password']) : '';

            $respuesta = $userCtrl->cambiarContraseña($username, $email, $password);

            if (is_string($respuesta)) {
                $respuesta = json_decode($respuesta, true);
            }

            if (isset($respuesta['status'])) {
                $respuesta['success'] = $respuesta['status'];
            }

            echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
            break;

        default:
            echo json_encode(["success" => false, "message" => "Acción no permitida o desconocida."], JSON_UNESCAPED_UNICODE);
            break;
    }
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Error interno: " . $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
