<?php 
require_once __DIR__ . '/../controllers/controllers.php';

/* 
 * userController.php
 * Autor: Alex Madrid
 * Fecha: 06/06/2026
 */

class userController extends Controllers
{
    private $model;

    public function __construct($pdo)
    {
        parent::__construct($pdo);
        $this->model = $this->cargarModels('usuariosModels');
    }

    private function response(bool $success, string $message, $data = null)
    {
        $response = [
            "status" => $success ? "success" : "error",
            "message" => $message
        ];
        
        if ($data !== null) {
            $response["data"] = $data;
        }
        
        return json_encode($response, JSON_UNESCAPED_UNICODE);
    }

    public function listarUsuarios()
    {
        $data = $this->model->obtenerUsuarios();
        
        if (isset($data['error'])) {
            return $this->response(false, $data['error']);
        }
        
        return $this->response(true, "Usuarios obtenidos exitosamente", $data);
    }

    public function listarUsuariosPorNombre(string $user)
    {
        $data = $this->model->obtenerUsuariosPorUsername($user);

        if (isset($data['error'])) {
            return $this->response(false, $data['error']);
        }

        $response = [
            "estatus" => $data['id_estatus'] ?? null,
            "persona" => $data['id_persona'] ?? null,
            "rol" => $data['id_rol'] ?? null,
            "username" => $data['username'] ?? null,
            "correo" => $data['email_user'] ?? null
        ];

        return $this->response(true, "Usuario Encontrado con Exito", $response);
    }

    public function crearUsuarios(array $datos)
    {
        $camposObligatorios = ['id_persona', 'id_rol', 'email_user'];

        foreach ($camposObligatorios as $campo) {
            if (!isset($datos[$campo]) || trim($datos[$campo]) === '') {
                return $this->response(false, "Todos los campos son obligatorios");
            }
        }

        $id_persona = (int)$datos['id_persona'];
        $id_rol = (int)$datos['id_rol'];
        $email_user = trim($datos['email_user']);

        // Validar formato de email
        if (!filter_var($email_user, FILTER_VALIDATE_EMAIL)) {
            return $this->response(false, "El formato del correo electrónico no es válido");
        }

        // Llamar al modelo
        $request = $this->model->crearUsuarios($id_persona, $id_rol, $email_user);

        // El modelo siempre retorna un array con 'success' o 'error'
        if (isset($request['error'])) {
            return $this->response(false, $request['error']);
        }

        return $this->response(true, $request['message'], $request['data'] ?? null);
    }

    public function actualizarUsuarios(array $datos){

        $camposObligatorios = ['id_estatus', 'id_rol', 'username', 'email_user'];
        foreach ($camposObligatorios as $campo) {
            if (!isset($datos[$campo]) || trim($datos[$campo]) === '') {
                return $this->response(false, "Todos los campos son obligatorios");
            }
        }

        /**
         * almacenamos los datos que vienen del model en una variable
         */

        $id_estatus = (int)$datos['id_estatus'];
        $id_rol = (int)$datos['id_rol'];
        $username = trim($datos['username']);
        $email_user = trim($datos['email_user']);

        // Validar formato de email
        if (!filter_var($email_user, FILTER_VALIDATE_EMAIL)) {
            return $this->response(false, "El formato del correo electrónico no es válido");
        }

        $request = $this->model->actualizarUsername($id_estatus, $id_rol, $username, $email_user);

        if (isset($request['error'])) {
            return $this->response(false, $request['error']);
        }

        return $this->response(true, $request['message'], $request['data'] ?? null);
    }

    public function cambiarContraseña(array $datosPass){

        $camposObligatorios = ['username', 'email_user', 'password_hash'];
        foreach ($camposObligatorios as $campo) {
            if (!isset($datosPass[$campo]) || trim($datosPass[$campo]) === '') {
                return $this->response(false, "Todos los campos son obligatorios");
            }
        }

        /**
         * almacenamos los datos que vienen del model en una variable
         */

        $username = trim($datosPass['username']);
        $email = trim($datosPass['email_user']);
        $pass = trim($datosPass['password_hash']);

        // Validar formato de email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->response(false, "El formato del correo electrónico no es válido");
        }

        $request = $this->model->cambiarContraseña($username, $email, $pass);

        if (isset($request['error'])) {
            return $this->response(false, $request['error']);
        }

        return $this->response(true, $request['message'], $request['data'] ?? null);
    }
}

$prueba = new userController($pdo);

// $datos = [
//     "id_estatus" => 1,
//     "id_rol"     => 1, 
//     "username"   => "madrida753",
//     "email_user" => "nuevo.usuario@correo.com"
// ];

// $datosPass = [
//     "username" => "madrida753",
//     "email_user" => "nuevo.usuario@correo.com",
//     "password_hash" => "madrida753"
// ];

echo $prueba->listarUsuariosPorNombre("madrida753");
