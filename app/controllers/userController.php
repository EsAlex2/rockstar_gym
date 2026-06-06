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

    public function listarUsuarios()
    {
        $data = $this->model->obtenerUsuarios();
        
        // Siempre retornar JSON estructurado
        if (isset($data['error'])) {
            return $this->jsonResponse(false, $data['error']);
        }
        
        return $this->jsonResponse(true, "Usuarios obtenidos exitosamente", $data);
    }

    public function listarUsuariosPorNombre(string $user)
    {
        $data = $this->model->obtenerUsuariosPorUsername($user);

        if (isset($data['error'])) {
            return $this->jsonResponse(false, $data['error']);
        }

        $response = [
            "id" => $data['id'] ?? null,
            "estatus" => $data['id_estatus'] ?? null,
            "persona" => $data['id_persona'] ?? null,
            "rol" => $data['id_rol'] ?? null,
            "username" => $data['username'] ?? null,
            "correo" => $data['email_user'] ?? null
        ];

        return $this->jsonResponse(true, "Usuario encontrado", $response);
    }

    public function crearUsuarios(array $datos)
    {
        $camposObligatorios = ['id_persona', 'id_rol', 'email_user'];

        foreach ($camposObligatorios as $campo) {
            if (!isset($datos[$campo]) || trim($datos[$campo]) === '') {
                return $this->jsonResponse(false, "El campo '{$campo}' es obligatorio");
            }
        }

        $id_persona = (int)$datos['id_persona'];
        $id_rol = (int)$datos['id_rol'];
        $email_user = trim($datos['email_user']);

        // Validar formato de email
        if (!filter_var($email_user, FILTER_VALIDATE_EMAIL)) {
            return $this->jsonResponse(false, "El formato del correo electrónico no es válido");
        }

        // Llamar al modelo
        $result = $this->model->crearUsuarios($id_persona, $id_rol, $email_user);

        // El modelo siempre retorna un array con 'success' o 'error'
        if (isset($result['error'])) {
            return $this->jsonResponse(false, $result['error']);
        }

        return $this->jsonResponse(true, $result['message'], $result['data'] ?? null);
    }

    private function jsonResponse(bool $success, string $message, $data = null)
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
}

$prueba = new userController($pdo);


// $datos = [
//     "id_persona" => 1,
//     "id_rol"     => 1, 
//     "email_user" => "nuevo.usuario@correo.com"
// ];

echo $prueba->listarUsuarios();

