<?php
require_once __DIR__ . '/../models/permisosModel.php';
require_once __DIR__ . '/../models/rolesModel.php';
require_once __DIR__ . '/../models/usuariosModel.php';
require_once __DIR__ . '/../models/personasModels.php';
require_once __DIR__ . '/../models/clientesModel.php';
require_once __DIR__ . '/../models/entrenamientosModel.php';
require_once __DIR__ . '/../models/entrenadorModel.php';
require_once __DIR__ . '/../models/horarioEntrenamientoModel.php';
require_once __DIR__ . '/../models/horariosModel.php';
require_once __DIR__ . '/../models/pagosModel.php';
require_once __DIR__ . '/../models/planesModel.php';
require_once __DIR__ . '/../models/loginModel.php';


class Controllers
{
    protected $pdo;
    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function cargarModels(string $model)
    {

        if (class_exists($model)) {
            return new $model($this->pdo);
        }

        throw new Exception("El modelo {$model} no existe.");
    }

    public function response(bool $success, string $message, $data = null)
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
