<?php

require_once __DIR__ . '/../controllers/controllers.php';

/* * loginController.php
 * Controlador encargado de procesar las intenciones de inicio y cierre de sesión.
 * Autor: Alex Madrid (Refactorizado)
 * Fecha: 16/06/2026
 */

class LoginController extends Controllers
{
    private $model;

    public function __construct($pdo)
    {
        parent::__construct($pdo);
        $this->model = $this->cargarModels('LoginModel');
    }

    /**
     * Procesa la solicitud POST del formulario de login
     */
    public function login()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitización de la identidad de ingreso
            $identity = filter_input(INPUT_POST, 'identity', FILTER_SANITIZE_SPECIAL_CHARS);
            $password = $_POST['password'] ?? '';

            if (empty($identity) || empty($password)) {
                $_SESSION['login_error'] = "Por favor, llene todos los campos requeridos.";
                header("Location: " . URL_BASE . "/public/index.php");
                exit;
            }

            // Consulta al modelo optimizado
            $usuario = $this->model->buscarPorIdentidad($identity);

            if ($usuario && password_verify($password, $usuario['password_hash'])) {
                
                // MEDIDA DE SEGURIDAD: Previene la fijación de sesiones maliciosas
                session_regenerate_id(true);

                // Mapeo seguro de variables de entorno de sesión
                $_SESSION['user_id']        = $usuario['id'] ?? null;
                $_SESSION['user_email']      = $usuario['email_user'];
                $_SESSION['user_fullname']   = $usuario['primer_nombre'] . ' ' . $usuario['primer_apellido'];
                $_SESSION['user_role']       = $usuario['nombre_rol'];
                // GUARDAR PERMISOS EN SESIÓN
                $_SESSION['user_permissions'] = $this->model->obtenerPermisosPorRol($usuario['id_rol']);
                
                header("Location: " . URL_BASE . "/views/home.php");
                exit;
            } else {
                $_SESSION['login_error'] = "Usuario, correo o contraseña incorrectos.";
                header("Location: " . URL_BASE . "/public/index.php");
                exit;
            }
        } else {
            header("Location: " . URL_BASE . "/public/index.php");
            exit;
        }
    }

    /**
     * Destruye de forma segura los vectores de sesión activos
     */
    public function logout()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_unset();
        session_destroy();
        
        header("Location: " . URL_BASE . "/public/index.php");
        exit;
    }
}