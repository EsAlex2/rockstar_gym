<?php

require_once __DIR__ . '/../controllers/controllers.php';

/* 
 * personasController.php
 * Autor: Alex Madrid
 * Fecha: 03/06/2026
 */

class PersonasController extends Controllers
{
    private $personaModel;

    public function __construct($pdo)
    {
        parent::__construct($pdo);
        $this->personaModel = $this->cargarModels('personasModel');
    }

    public function listarPersonas()
    {
        $data = $this->personaModel->obtenerPersonas();
        return $data;
    }

    public function listarPorCedula(string $cedula)
    {
        $data = $this->personaModel->obtenerPersonaPorCedula($cedula);

        if (!is_array($data)) {
            return json_encode([
                "error" => "No se pudo procesar la información o la persona no existe.", 
                "detalle" => $data
            ]);
        }

        $resultado = [
            "id"        => $data['id'] ?? null,
            "cedula"    => $data['cedula_identidad'] ?? null,
            "nombre"    => ($data['primer_nombre'] ?? '') . ' ' . ($data['primer_apellido'] ?? ''),
            "genero"    => $data['genero'] ?? null,
            "telefono"  => $data['telefono'] ?? null,
            "correo"    => $data['email'] ?? null,
            "direccion" => $data['direccion_habitacion'] ?? null
        ];

        return json_encode($resultado);
    }

    public function crearNuevaPersona(array $datos)
    {
        $camposObligatorios = [
            'genero_id',
            'cedula',
            'primer_nombre',
            'primer_apellido',
            'fecha_nacimiento',
            'telefono',
            'correo_electronico',
            'direccion'
        ];

        foreach ($camposObligatorios as $campo) {
            if (!isset($datos[$campo]) || trim($datos[$campo]) === '') {
                return json_encode([
                    "status" => "error",
                    "message" => "El campo '{$campo}' es obligatorio para el registro."
                ], JSON_UNESCAPED_UNICODE);
            }
        }

        $segundo_nombre   = $datos['segundo_nombre'] ?? '';
        $segundo_apellido = $datos['segundo_apellido'] ?? '';

        $resultadoModelo = $this->personaModel->crearPersona(
            (int)$datos['genero_id'],
            trim($datos['cedula']),
            trim($datos['primer_nombre']),
            trim($segundo_nombre),
            trim($datos['primer_apellido']),
            trim($segundo_apellido),
            $datos['fecha_nacimiento'],
            trim($datos['telefono']),
            trim($datos['correo_electronico']),
            trim($datos['direccion'])
        );

        if (strpos($resultadoModelo, 'correctamente') !== false) {
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

    public function actualizarDatosPersona(array $datos)
    {

        $segundo_nombre   = $datos['segundo_nombre'] ?? '';
        $segundo_apellido = $datos['segundo_apellido'] ?? '';

        $resultadoModelo = $this->personaModel->actualizarPersona(
            (int)$datos['genero_id'],
            (int)$datos['estatus_id'],
            trim($datos['cedula']),
            trim($datos['primer_nombre']),
            trim($segundo_nombre),
            trim($datos['primer_apellido']),
            trim($segundo_apellido),
            $datos['fecha_nacimiento'],
            trim($datos['telefono']),
            trim($datos['correo_electronico']),
            trim($datos['direccion'])
        );

        $respuestaDecodificada = json_decode($resultadoModelo, true);

        if (is_array($respuestaDecodificada) && isset($respuestaDecodificada['success'])) {
            return json_encode([
                "status" => "success",
                "message" => $respuestaDecodificada['success']
            ], JSON_UNESCAPED_UNICODE);
        } else {
            return json_encode([
                "status" => "error",
                "message" => $resultadoModelo
            ], JSON_UNESCAPED_UNICODE);
        }
    }
}

$prueba = new PersonasController($pdo);

echo $prueba->listarPersonas();
