<?php
require_once __DIR__ . '/../models/loginModel.php';

class loginController {
    private $model;

    public function __construct() {
        $this->model = new LoginModel();
    }

    public function login() {
        // Asegurar que la sesión esté iniciada para guardar errores o datos de sesión
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitización básica de entradas
            $identity = filter_input(INPUT_POST, 'identity', FILTER_SANITIZE_SPECIAL_CHARS);
            $password = $_POST['password'] ?? '';


            if (empty($identity) || empty($password)) {
                $_SESSION['login_error'] = "Por favor, llene todos los campos.";
                header("Location: " . URL_BASE . "/public/index.php");
                exit;
            }

            // Buscar usuario en la base de datos
            $usuario = $this->model->buscarPorIdentidad($identity);

            if ($usuario && password_verify($password, $usuario['password_hash'])) {
                // Autenticación exitosa: Guardamos datos útiles en la sesión
                $_SESSION['user_id'] = $usuario['id'];
                $_SESSION['username'] = $usuario['username'];
                $_SESSION['user_email'] = $usuario['email_user'];
                $_SESSION['user_fullname'] = $usuario['primer_nombre'] . ' ' . $usuario['primer_apellido'];
                $_SESSION['user_role'] = $usuario['nombre_rol'];
                
                // Redirigir al Dashboard principal
                header("Location: " . URL_BASE . "/views/home.php");
                exit;
            } else {
                // Error de credenciales
                $_SESSION['login_error'] = "Usuario, correo o contraseña incorrectos.";
                header("Location: " . URL_BASE . "/public/index.php");
                exit;
            }
        } else {
            // Si intentan entrar por GET de forma inválida
            header("Location: " . URL_BASE . "/public/index.php");
            exit;
        }
    }

    public function logout() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_unset();
        session_destroy();
        header("Location: " . URL_BASE . "/public/index.php");
        exit;
    }
}