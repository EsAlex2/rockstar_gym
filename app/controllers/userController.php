<?php

require_once __DIR__ . '/../controllers/controllers.php';

/* * userController.php
 * Autor: Alex Madrid
 * Fecha: 05/06/2026
 */

class UserController extends Controllers
{
    private $model;

    public function __construct($pdo)
    {
        parent::__construct($pdo);
        // Cargamos el modelo de usuarios de forma dinámica
        $this->model = $this->cargarModels('usuariosModels');
    }

    /**
     * Listar todos los usuarios registrados
     */
    public function listarUsuarios()
    {
        $resultadoModelo = $this->model->obtenerUsuarios();

        // El modelo devuelve un JSON, intentamos decodificarlo
        $data = json_decode($resultadoModelo, true);

        if (is_array($data) && isset($data['success'])) {
            return json_encode($data['success'], JSON_UNESCAPED_UNICODE);
        }

        return json_encode([
            "status" => "error",
            "message" => "No se pudieron obtener los usuarios.",
            "detalle" => $resultadoModelo
        ], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Registrar un nuevo usuario en el sistema
     * (Renombrado a crearUsuario para que coincida exactamente con tu prueba de abajo)
     */
    public function crearUsuario(array $datos)
    {
        // 1. Validamos que los campos obligatorios no vengan vacíos
        $camposObligatorios = ['estatus_id', 'rol_id', 'cedula_identidad', 'email'];

        foreach ($camposObligatorios as $campo) {
            if (!isset($datos[$campo]) || trim($datos[$campo]) === '') {
                return json_encode([
                    "status" => "error",
                    "message" => "El campo '{$campo}' es obligatorio para registrar el usuario."
                ], JSON_UNESCAPED_UNICODE);
            }
        }

        // 2. Invocamos al método del modelo pasándole los parámetros requeridos
        $resultadoModelo = $this->model->crearUsuarios(
            (int)$datos['estatus_id'],
            (int)$datos['rol_id'],
            trim($datos['cedula_identidad']),
            trim($datos['email']),
        );

        $mensajeLimpio = trim($resultadoModelo);

        if (stripos($mensajeLimpio, 'creado') !== false) {
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

    /**
     * Actualizar los datos de un usuario existente
     */
    public function actualizarDatosUsuario(array $datos)
    {
        // 1. Validamos los campos requeridos para la actualización
        $camposObligatorios = ['estatus_id', 'rol_id', 'username', 'email'];

        foreach ($camposObligatorios as $campo) {
            if (!isset($datos[$campo]) || trim($datos[$campo]) === '') {
                return json_encode([
                    "status" => "error",
                    "message" => "El campo '{$campo}' es obligatorio para actualizar el usuario."
                ], JSON_UNESCAPED_UNICODE);
            }
        }

        // 2. Invocamos al método del modelo
        $resultadoModelo = $this->model->actualizarUsuario(
            (int)$datos['estatus_id'],
            (int)$datos['rol_id'],
            trim($datos['username']),
            trim($datos['email'])
        );

        // 3. Como este método del modelo SÍ devuelve un JSON, intentamos decodificarlo
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

// --- SECCIÓN DE PRUEBAS LOCALES ---
$pruebaUsuarios = new UserController($pdo);

$nuevoUsuario = [
    'estatus_id'       => 1,
    'rol_id'           => 1,
    'cedula_identidad' => '27391753', 
    'email'            => 'alex@correo.com',
];

//header('Content-Type: application/json; charset=utf-8');
echo $pruebaUsuarios->crearUsuario($nuevoUsuario);