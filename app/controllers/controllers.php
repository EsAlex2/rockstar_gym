<?php

require_once __DIR__ . '/../models/permisosModel.php';
require_once __DIR__ . '/../models/rolesModel.php';
require_once __DIR__ . '/../models/usuariosModel.php';
require_once __DIR__ . '/../models/personasModels.php';

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
}
