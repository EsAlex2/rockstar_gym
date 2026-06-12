<?php

require_once __DIR__ . '/../controllers/controllers.php';

/* 
 * clientesPlanController.php
 * Autor: Alex Madrid
 * Fecha: 03/06/2026
 */

class ClientesPlanController extends Controllers {

    private $models;

    public function __construct($pdo)
    {
        parent::__construct($pdo);
        $this->models = $this->cargarModels('clientesPlanesModel');
    }

    public function asignarClientePlan(array $data)
    {
        $camposObligatorios = [
            'id_cliente',
            'id_plan',
            ''
        ];
    }
}