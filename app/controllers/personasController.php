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

    public function listarPersonas()
    {
        $data = $this->personaModel->obtenerPersonas();

        if (isset($data['error'])) {
            return $this->response(false, $data['error']);
        }

        return $this->response(true, "Personas obtenidas exitosamente", $data);
    }

    public function listarPorCedula(string $cedula)
    {
        $data = $this->personaModel->obtenerPersonaPorCedula($cedula);

        if (isset($data['error'])) {
            return $this->response(false, $data['error']);
        }

        $resultado = [
            "estatus" => $data['estatus'] ?? null,
            "cedula"    => $data['cedula_identidad'] ?? null,
            "nombre"    => ($data['primer_nombre'] ?? '') . ' ' . ($data['primer_apellido'] ?? ''),
            "genero"    => $data['genero'] ?? null,
            "telefono"  => $data['telefono'] ?? null,
            "correo"    => $data['email'] ?? null,
            "direccion" => $data['direccion_habitacion'] ?? null
        ];

        return $this->response(true, "Persona Encontrada con Exito", $resultado);
    }

    public function crearNuevaPersona(array $datos)
    {
        $camposObligatorios = [
            'id_genero',
            'cedula_identidad',
            'primer_nombre',
            'primer_apellido',
            'fecha_nacimiento',
            'telefono',
            'email',
            'direccion_habitacion'
        ];

        foreach ($camposObligatorios as $campo) {
            if (!isset($datos[$campo]) || trim($datos[$campo]) === '') {
                return $this->response(false, "Todos los campos son obligatorios");
            }
        }

        $segundo_nombre = $datos['segundo_nombre'] ?? '';
        $segundo_apellido = $datos['segundo_apellido'] ?? '';

        $id_genero = (int)$datos['id_genero'];
        $cedula = trim($datos['cedula_identidad']);
        $nombre1 = trim($datos['primer_nombre']);
        $nombre2 = $segundo_nombre;
        $apellido1 = trim($datos['primer_apellido']); 
        $apellido2 = $segundo_apellido;
        $fecha_nacimiento = trim($datos['fecha_nacimiento']);
        $email = trim($datos['email']);
        $telefono = trim($datos['telefono']);
        $direccion = trim($datos['direccion_habitacion']) ?? 'Sin especificar';

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->response(false, "El formato del correo electrónico no es válido");
        }

        $request = $this->personaModel->crearPersona($id_genero, $cedula, $nombre1, $nombre2, $apellido1, $apellido2, $fecha_nacimiento, $email, $telefono, $direccion);

        if (isset($request['error'])) {
            return $this->response(false, $request['error']);
        }

        return $this->response(true, $request['message'], $request['data'] ?? null);
    }

    public function actualizarDatosPersona(array $datos)    
    {
        $camposObligatorios = ['cedula_identidad', 'primer_nombre', 'primer_apellido','email'];

        foreach($camposObligatorios as $campos){
            if (!isset($datos[$campos]) || trim($datos[$campos]) === '') {
                return $this->response(false, "Todos los campos son obligatorios, falta {$campos}");
            }
        }

        $segundo_nombre = $datos['segundo_nombre'] ?? '';
        $segundo_apellido = $datos['segundo_apellido'] ?? '';

        $id_genero = (int)$datos['id_genero'];
        $id_estatus = (int)$datos['id_estatus'];
        $cedula = trim($datos['cedula_identidad']);
        $nombre1 = trim($datos['primer_nombre']);
        $nombre2 = $segundo_nombre;
        $apellido1 = trim($datos['primer_apellido']); 
        $apellido2 = $segundo_apellido;
        $fecha_nacimiento = trim($datos['fecha_nacimiento']);
        $email = trim($datos['email']);
        $telefono = trim($datos['telefono']);
        $direccion = trim($datos['direccion_habitacion']) ?? 'Sin especificar';

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->response(false, "El formato del correo electrónico no es válido");
        }

        $request = $this->personaModel->actualizarPersona($id_genero, $id_estatus, $cedula, $nombre1, $nombre2, $apellido1, $apellido2, $fecha_nacimiento, $email, $telefono, $direccion);

        if(isset($request['error'])){
            return $this->response(false, $request['error']);
        }

        return $this->response(true, $request['success'], $request['data'] ?? null);
    }
}

$pruebas = new PersonasController($pdo);

// $datos = [
//     "id_genero" => 2,
//     "id_estatus" => 1,
//     "cedula_identidad" => "29571480",
//     "primer_nombre" => "adriáaña",
//     "primer_apellido" => "estrada",
//     "fecha_nacimiento" => "13/11/2002",
//     "telefono" => "04127968974",
//     "email" => "aaec1311@gmail.com",
//     "direccion_habitacion" => "la pastora" 
// ];

// echo $pruebas->actualizarDatosPersona($datos);