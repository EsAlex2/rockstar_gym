<?php

require_once __DIR__ . '/../controllers/controllers.php';

/* 
 * rolesController.php
 * Autor: Alex Madrid
 * Fecha: 03/06/2026
 */

class rolesController extends Controllers
{
    private $model;

    public function __construct($pdo)
    {
        parent::__construct($pdo);
        $this->model = $this->cargarModels('rolesModel');
    }

    public function listarRoles()
    {
        $data = $this->model->obtenerRoles();

        if (!is_array($data)) {
            return json_encode([
                "error" => "No se pudo procesar la información o no hay roles cargados"
            ]);
        }

        return json_encode($data);
    }

    public function listarRolPorNombre(string $nombre)
    {
        $data = $this->model->obtenerRolPorNombre($nombre);


        if (!is_array($data)) {
            return json_encode([
                "error" => "No se pudo procesar la información o el rol no existe.",
                "detalle" => $data
            ]);
        }

        $resultado = [
            "id"        => $data['id'] ?? null,
            "nombre_rol"    => $data['nombre_rol'] ?? null,
            "descripcion"    => $data['descripcion'] ?? null
        ];

        return json_encode($resultado);
    }

    public function crearNuevoRol(array $datos)
    {
        $camposObligatorios = ['nombre_rol', 'descripcion'];

        foreach ($camposObligatorios as $campo) {
            if (!isset($datos[$campo]) || trim($datos[$campo]) === '') {
                return json_encode([
                    "status" => "error",
                    "message" => "El campo '{$campo}' es obligatorio para registrar el rol."
                ], JSON_UNESCAPED_UNICODE);
            }
        }

        $resultadoModelo = $this->model->crearRol(
            trim($datos['nombre_rol']),
            trim($datos['descripcion'])
        );

        if (strpos($resultadoModelo, 'exitosamente') !== false) {
            return json_encode([
                "status" => "success",
                "message" => $resultadoModelo
            ], JSON_UNESCAPED_UNICODE);
        } else {
            return json_encode([
                "status" => "error",
                "message" => $resultadoModelo
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    public function actualizarDatosRol(array $datos)
    {
        $camposObligatorios = ['id_rol', 'nombre_rol', 'descripcion'];

        foreach ($camposObligatorios as $campo) {
            if (!isset($datos[$campo]) || trim($datos[$campo]) === '') {
                return json_encode([
                    "status" => "error",
                    "message" => "El campo '{$campo}' es obligatorio para actualizar el rol."
                ], JSON_UNESCAPED_UNICODE);
            }
        }

        $resultadoModelo = $this->model->actualizarRol(
            (int)$datos['id_rol'],
            trim($datos['nombre_rol']),
            trim($datos['descripcion'])
        );

        if (strpos($resultadoModelo, 'exitosamente') !== false) {
            return json_encode([
                "status" => "success",
                "message" => $resultadoModelo
            ], JSON_UNESCAPED_UNICODE);
        } else {
            return json_encode([
                "status" => "error",
                "message" => $resultadoModelo
            ], JSON_UNESCAPED_UNICODE);
        }
    }
}