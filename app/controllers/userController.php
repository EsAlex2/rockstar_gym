<?php
require_once __DIR__ . '/../controllers/controllers.php';

/* * userController.php
 * Autor: Alex Madrid
 * Refactorizado: 15/06/2026
 */

class userController extends Controllers
{
    private $model;

    public function __construct($pdo)
    {
        parent::__construct($pdo);
        $this->model = $this->cargarModels('usuariosModels');
    }

    public function listarUsuarios()
    {
        $data = $this->model->obtenerUsuarios();

        if (isset($data['error'])) {
            return $this->response(false, $data['error']);
        }

        return $this->response(true, "Usuarios obtenidos exitosamente", $data);
    }

    /**
     * Registra un usuario procesando un username derivado y un hash seguro por defecto.
     */
    public function crearUsuario(int $id_persona, int $id_rol, string $email)
    {
        if ($id_persona <= 0 || $id_rol <= 0 || empty(trim($email))) {
            return $this->response(false, "Todos los campos son obligatorios para el registro.");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->response(false, "El formato del correo electrónico no es válido.");
        }

        // Dividir el string en el arroba para tomar el prefijo como username inicial limpio
        $username = strtolower(explode('@', $email)[0]);

        // Contraseña por defecto para su primer acceso (el gimnasio puede indicarle esta)
        $passwordProvisional = "Rockstar.2026";
        $passwordHash = password_hash($passwordProvisional, PASSWORD_BCRYPT);

        $request = $this->model->registrarUsuarios($id_persona, $id_rol, $username, $email, $passwordHash);

        if (isset($request['error'])) {
            return $this->response(false, $request['error']);
        }

        return $this->response(true, "Usuario creado exitosamente. Su usuario es: '{$username}' y clave provisional: 'Rockstar.2026'", $request);
    }

    public function listarUsuariosPorNombre(string $user)
    {
        $data = $this->model->obtenerUsuariosPorUsername($user);

        if (isset($data['error']) || !$data) {
            return $this->response(false, $data['error'] ?? "Usuario no encontrado");
        }

        $response = [
            "id" => $data['id'] ?? null,
            "estatus" => $data['id_estatus'] ?? null,
            "persona" => $data['id_persona'] ?? null,
            "rol" => $data['id_rol'] ?? null,
            "username" => $data['username'] ?? null,
            "email" => $data['email_user'] ?? null
        ];

        return $this->response(true, "Usuario encontrado", $response);
    }

    public function actualizarUsuarios(int $id, int $id_estatus, int $id_rol, string $username, string $email)
    {
        if (empty($id) || empty($id_estatus) || empty($id_rol) || empty(trim($username)) || empty(trim($email))) {
            return $this->response(false, "Todos los campos son obligatorios para actualizar");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->response(false, "El formato del correo electrónico no es válido");
        }

        $request = $this->model->actualizarUsername($id, $id_estatus, $id_rol, $username, $email);

        if (isset($request['error'])) {
            return $this->response(false, $request['error']);
        }

        return $this->response(true, $request['message'] ?? "Usuario actualizado exitosamente", $request['data'] ?? null);
    }

    public function cambiarContraseña(string $username, string $email, string $pass)
    {
        if (empty(trim($username)) || empty(trim($email)) || empty(trim($pass))) {
            return $this->response(false, "Todos los campos son obligatorios");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->response(false, "El formato del correo electrónico no es válido");
        }

        $passwordHash = password_hash($pass, PASSWORD_BCRYPT);

        $request = $this->model->cambiarContraseña($username, $email, $passwordHash);

        if (isset($request['error'])) {
            return $this->response(false, $request['error']);
        }

        return $this->response(true, $request['message'] ?? "Contraseña actualizada exitosamente", $request['data'] ?? null);
    }
}