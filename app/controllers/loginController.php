<?php

require_once __DIR__ . '/controllers.php';

/**
 * Class LoginController
 * Controlador para la autenticación, inicio de sesión seguro y cierre de sesiones.
 * Extiende de BaseController.
 */
class LoginController extends BaseController
{
    private LoginModel $model;

    public function __construct(?PDO $pdo = null)
    {
        parent::__construct($pdo);
        $this->model = $this->cargarModels('LoginModel');
    }

    /**
     * Procesa la solicitud POST del formulario de login.
     */
    public function login(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $identity = trim($_POST['identity'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($identity) || empty($password)) {
                $_SESSION['login_error'] = "Por favor, llene todos los campos requeridos.";
                header("Location: " . URL_BASE . "/public/index.php");
                exit;
            }



            $candidatos = $this->model->buscarCandidatosPorIdentidad($identity);

            if (empty($candidatos)) {
                $_SESSION['login_error'] = "Usuario, correo o cédula no registrados.";
                header("Location: " . URL_BASE . "/public/index.php");
                exit;
            }

            $usuarioAutenticado = null;
            $cuentaInactiva = false;

            foreach ($candidatos as $cand) {
                if (password_verify($password, $cand['password_hash'])) {
                    $esActivo = ((int)$cand['id_estatus'] === 1 || strtolower($cand['nombre_estatus'] ?? '') === 'activo');
                    if ($esActivo) {
                        $usuarioAutenticado = $cand;
                        break;
                    } else {
                        $cuentaInactiva = true;
                    }
                }
            }

            if ($usuarioAutenticado) {
                // Previene la fijación de sesiones maliciosas
                session_regenerate_id(true);

                $_SESSION['user_id']        = $usuarioAutenticado['id'] ?? null;
                $_SESSION['user_email']     = $usuarioAutenticado['email_user'];
                $_SESSION['user_fullname']  = trim(($usuarioAutenticado['primer_nombre'] ?? '') . ' ' . ($usuarioAutenticado['primer_apellido'] ?? ''));
                
                $role_map = [
                    'root'          => 'Root',
                    'administrador' => 'Administrador',
                    'entrenador'    => 'Entrenador',
                    'cliente'       => 'Cliente'
                ];
                $rawRole = strtolower(trim($usuarioAutenticado['nombre_rol']));
                $_SESSION['user_role']        = $role_map[$rawRole] ?? $usuarioAutenticado['nombre_rol'];
                $_SESSION['user_permissions'] = $this->model->obtenerPermisosPorRol((int)$usuarioAutenticado['id_rol']);
                
                header("Location: " . URL_BASE . "/views/home.php");
                exit;
            } elseif ($cuentaInactiva) {
                $_SESSION['login_error'] = "Su cuenta de usuario se encuentra inactiva. Comuníquese con la administración.";
                header("Location: " . URL_BASE . "/public/index.php");
                exit;
            } else {
                $_SESSION['login_error'] = "Contraseña incorrecta.";
                header("Location: " . URL_BASE . "/public/index.php");
                exit;
            }
        } else {
            header("Location: " . URL_BASE . "/public/index.php");
            exit;
        }
    }

    /**
     * Destruye de forma segura la sesión activa del usuario.
     */
    public function logout(): void
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