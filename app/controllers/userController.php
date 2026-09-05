<?php

require_once __DIR__ . '/controllers.php';

/**
 * Class UsuariosController
 * Controlador para la gestión de usuarios, credenciales y perfiles de acceso.
 * Extiende de BaseController.
 */
class UsuariosController extends BaseController
{
    private UsuariosModel $usuarioModel;

    public function __construct(?PDO $pdo = null)
    {
        parent::__construct($pdo);
        $this->usuarioModel = $this->cargarModels('UsuariosModel');
    }

    /**
     * Lista todos los usuarios registrados.
     */
    public function listarUsuarios(): string
    {
        $data = $this->usuarioModel->obtenerUsuarios();

        if (isset($data['error'])) {
            return $this->response(false, $data['error']);
        }

        return $this->response(true, "Usuarios obtenidos exitosamente", $data);
    }

    /**
     * Procesa la creación de un nuevo usuario con contraseña por defecto o personalizada.
     */
    public function crearNuevoUsuario(int $id_persona, string $usuario, int $id_rol, string $password = "Cliente2026*"): string
    {   
        if ($id_persona <= 0 || empty(trim($usuario)) || empty(trim($password)) || $id_rol <= 0) {
            return $this->response(false, "Todos los campos son estrictamente obligatorios");
        }

        $cleanEmail = $this->validateAndSanitizeEmail($usuario);
        if ($cleanEmail === null) {
            return $this->response(false, "El formato del correo electrónico no es válido");
        }
        
        if (strlen($password) < 8) {
            return $this->response(false, "La contraseña debe tener al menos 8 caracteres");
        }

        $request = $this->usuarioModel->crearUsuario(
            $id_persona,
            $cleanEmail,
            $password,
            $id_rol
        );

        if (isset($request['error'])) {
            return $this->response(false, $request['error']);
        }

        return $this->response(true, $request['message'] ?? "Usuario registrado exitosamente");
    }

    /**
     * Alias de creación de usuario para pasarelas y formularios.
     */
    public function crearUsuario(int $id_persona, int $id_rol, string $email): string
    {
        return $this->crearNuevoUsuario($id_persona, $email, $id_rol);
    }

    /**
     * Actualiza la información de un usuario.
     */
    public function actualizarUsuarios(int $id_usuario, int $id_estatus, int $id_rol, string $username, string $email, string $password = ''): string
    {
        if ($id_usuario <= 0 || $id_estatus <= 0 || $id_rol <= 0 || empty(trim($email))) {
            return $this->response(false, "Todos los campos son estrictamente obligatorios");
        }

        $cleanEmail = $this->validateAndSanitizeEmail($email);
        if ($cleanEmail === null) {
            return $this->response(false, "El formato del correo electrónico no es válido");
        }

        if ($password !== '' && strlen($password) < 8) {
            return $this->response(false, "La nueva contraseña debe tener al menos 8 caracteres");
        }

        $request = $this->usuarioModel->actualizarUsuario($id_usuario, $id_estatus, $id_rol, $cleanEmail, $password);

        if (isset($request['error'])) {
            return $this->response(false, $request['error']);
        }

        return $this->response(true, $request['message'] ?? "Usuario actualizado exitosamente");
    }

    /**
     * Cambia la contraseña de un usuario mediante su correo electrónico.
     */
    public function cambiarContraseña(string $username, string $email, string $password): string
    {
        $cleanEmail = $this->validateAndSanitizeEmail($email);
        if ($cleanEmail === null || empty(trim($password))) {
            return $this->response(false, "Todos los campos son estrictamente obligatorios");
        }

        if (strlen($password) < 8) {
            return $this->response(false, "La contraseña debe tener al menos 8 caracteres");
        }

        $request = $this->usuarioModel->cambiarPassword($cleanEmail, $password);

        if (isset($request['error'])) {
            return $this->response(false, $request['error']);
        }

        return $this->response(true, $request['message'] ?? "Contraseña actualizada exitosamente");
    }

    /**
     * Elimina el registro de un usuario.
     */
    public function eliminarUsuario(int $id_usuario): string
    {
        if ($id_usuario <= 0) {
            return $this->response(false, "El ID del usuario es obligatorio y debe ser válido");
        }

        $request = $this->usuarioModel->eliminarUsuario($id_usuario);

        if (isset($request['error'])) {
            return $this->response(false, $request['error']);
        }

        return $this->response(true, $request['message'] ?? "Usuario eliminado correctamente");
    }

    /**
     * Cambia el estatus (Activo/Inactivo) de un usuario.
     */
    public function cambiarEstatusUsuario(int $id_usuario, int $nuevo_estatus): string
    {
        if ($id_usuario <= 0 || $nuevo_estatus <= 0) {
            return $this->response(false, "El ID del usuario y el estatus son obligatorios");
        }

        $request = $this->usuarioModel->cambiarEstatusUsuario($id_usuario, $nuevo_estatus);

        if (isset($request['error'])) {
            return $this->response(false, $request['error']);
        }

        return $this->response(true, $request['message'] ?? "Estado del usuario actualizado correctamente");
    }
}