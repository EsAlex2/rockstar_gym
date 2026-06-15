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

        // Retornamos de forma explícita el estado true y los datos del modelo
        return $this->response(true, "Usuarios obtenidos exitosamente", $data);
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
            "correo" => $data['email_user'] ?? null
        ];

        return $this->response(true, "Usuario Encontrado con Éxito", $response);
    }

    public function crearUsuarios(int $id_persona, int $id_rol, string $email)
    {
        if (empty($id_persona) || empty($id_rol) || empty(trim($email))) {
            return $this->response(false, "Todos los campos son obligatorios");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->response(false, "El formato del correo electrónico no es válido");
        }

        $request = $this->model->crearUsuarios($id_persona, $id_rol, $email);

        if (isset($request['error'])) {
            return $this->response(false, $request['error']);
        }

        return $this->response(true, $request['message'] ?? "Usuario creado con éxito", $request['data'] ?? null);
    }

    public function actualizarUsuarios(int $id, int $id_estatus, int $id_rol, string $username, string $email)
    {
        if (empty($id) || empty($id_estatus) || empty($id_rol) || empty(trim($username)) || empty(trim($email))) {
            return $this->response(false, "Todos los campos son obligatorios");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->response(false, "El formato del correo electrónico no es válido");
        }

        // Enviamos las variables reales sanitizadas al modelo
        $request = $this->model->actualizarUsername($id, $id_estatus, $id_rol, $username, $email);

        if (isset($request['error'])) {
            return $this->response(false, $request['error']);
        }

        return $this->response(true, $request['message'] ?? "Usuario actualizado", $request['data'] ?? null);
    }

    public function cambiarContraseña(string $username, string $email, string $pass)
    {
        if (empty(trim($username)) || empty(trim($email)) || empty(trim($pass))) {
            return $this->response(false, "Todos los campos son obligatorios");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->response(false, "El formato del correo electrónico no es válido");
        }

        // Encriptación nativa recomendada antes de impactar el modelo
        $passwordHash = password_hash($pass, PASSWORD_BCRYPT);

        $request = $this->model->cambiarContraseña($username, $email, $passwordHash);

        if (isset($request['error'])) {
            return $this->response(false, $request['error']);
        }

        return $this->response(true, $request['message'] ?? "Contraseña modificada correctamente", $request['data'] ?? null);
    }
}