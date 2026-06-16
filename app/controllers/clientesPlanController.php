<?php

require_once __DIR__ . '/../controllers/controllers.php';

/* * clientesPlanController.php
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
            'id_plan'
        ];

        foreach ($camposObligatorios as $campo) {
            if (!isset($data[$campo]) || empty(trim((string)$data[$campo]))) {
                return $this->response(false, "El campo {$campo} es obligatorio.");
            }
        }

        $id_cliente = (int)$data['id_cliente'];
        $id_plan = (int)$data['id_plan'];
        $fecha_inicio = !empty($data['fecha_inicio']) ? $data['fecha_inicio'] : null;

        $resultado = $this->models->asignarPlanCliente($id_cliente, $id_plan, $fecha_inicio);

        if (isset($resultado['error'])) {
            return $this->response(false, $resultado['error']);
        }

        return $this->response(true, $resultado['message'] ?? "Membresía asignada correctamente.", $resultado['data'] ?? []);
    }

    public function listarClientesPlanes()
    {
        $resultado = $this->models->listarClientesPlanes();

        if (isset($resultado['error'])) {
            return $this->response(false, $resultado['error'], []);
        }

        return $this->response(true, "Listado de membresías obtenido.", $resultado);
    }
}