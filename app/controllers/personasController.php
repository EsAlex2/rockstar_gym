<?php

require_once __DIR__ . '/../controllers/controllers.php';

/* * personasController.php
 * Autor: Alex Madrid
 * Refactorizado: 15/06/2026
 */

class PersonasController extends Controllers
{
    private $personaModel;

    public function __construct($pdo)
    {
        parent::__construct($pdo);
        $this->personaModel = $this->cargarModels('personasModel');
    }

    /**
     * Lista todas las personas registradas
     */
    public function listarPersonas()
    {
        $data = $this->personaModel->obtenerPersonas();

        if (isset($data['error'])) {
            return $this->response(false, $data['error']);
        }

        return $this->response(true, "Personas obtenidas exitosamente", $data);
    }

    /**
     * Busca una persona por su cédula de identidad
     */
    public function listarPorCedula(string $cedula)
    {
        $data = $this->personaModel->obtenerPersonaPorCedula($cedula);

        if (isset($data['error'])) {
            return $this->response(false, $data['error']);
        }

        $resultado = [
            "estatus" => $data['estatus'] ?? null,
            "cedula" => $data['cedula_identidad'] ?? null,
            "nombre" => ($data['primer_nombre'] ?? '') . ' ' . ($data['primer_apellido'] ?? ''),
            "genero" => $data['genero'] ?? null,
            "telefono" => $data['telefono'] ?? null,
            "correo" => $data['email'] ?? null,
            "direccion" => $data['direccion_habitacion'] ?? null
        ];

        return $this->response(true, "Persona Encontrada con Éxito", $resultado);
    }

    /**
     * Registra una nueva persona utilizando parámetros individuales explicítos
     */
    public function crearNuevaPersona(
        int $id_genero,
        string $cedula_identidad,
        string $primer_nombre,
        string $primer_apellido,
        string $fecha_nacimiento,
        string $telefono,
        string $email,
        string $direccion_habitacion,
        ?string $segundo_nombre = null,
        ?string $segundo_apellido = null
    ) {
        // Validación unificada para los campos estrictamente obligatorios
        if (
            empty($id_genero) || empty(trim($cedula_identidad)) || empty(trim($primer_nombre)) ||
            empty(trim($primer_apellido)) || empty(trim($fecha_nacimiento)) ||
            empty(trim($telefono)) || empty(trim($email)) || empty(trim($direccion_habitacion))
        ) {
            return $this->response(false, "Todos los campos son obligatorios");
        }

        // Sanitización y formateo de datos recibidos
        $cedula = trim($cedula_identidad);
        $nombre1 = trim($primer_nombre);
        $nombre2 = $segundo_nombre !== null ? trim($segundo_nombre) : '';
        $apellido1 = trim($primer_apellido);
        $apellido2 = $segundo_apellido !== null ? trim($segundo_apellido) : '';
        $fecha_nacimiento_trim = trim($fecha_nacimiento);
        $email_trim = trim($email);
        $telefono_trim = trim($telefono);
        $direccion = trim($direccion_habitacion);

        if (!filter_var($email_trim, FILTER_VALIDATE_EMAIL)) {
            return $this->response(false, "El formato del correo electrónico no es válido");
        }

        // Llamada limpia al modelo
        $request = $this->personaModel->crearPersona(
            $id_genero,
            $cedula,
            $nombre1,
            $nombre2,
            $apellido1,
            $apellido2,
            $fecha_nacimiento_trim,
            $email_trim,
            $telefono_trim,
            $direccion
        );

        if (isset($request['error'])) {
            return $this->response(false, $request['error']);
        }

        return $this->response(true, $request['message'] ?? "Persona registrada exitosamente", $request['data'] ?? null);
    }

    /**
     * Actualiza los datos de una persona existente
     */
    public function actualizarDatosPersona(
        int $id_genero,
        int $id_estatus,
        string $cedula_identidad,
        string $primer_nombre,
        string $primer_apellido,
        string $fecha_nacimiento,
        string $email,
        string $telefono,
        string $direccion_habitacion,
        ?string $segundo_nombre = null,
        ?string $segundo_apellido = null
    ) {
        // En tu código original validabas solo 4, pero extraías todos. Mantenemos tu regla estricta de obligatorios:
        if (empty(trim($cedula_identidad)) || empty(trim($primer_nombre)) || empty(trim($primer_apellido)) || empty(trim($email))) {
            return $this->response(false, "Todos los campos son obligatorios, faltan campos requeridos");
        }

        // Sanitización y manejo seguro de opcionales
        $cedula = trim($cedula_identidad);
        $nombre1 = trim($primer_nombre);
        $nombre2 = $segundo_nombre !== null ? trim($segundo_nombre) : '';
        $apellido1 = trim($primer_apellido);
        $apellido2 = $segundo_apellido !== null ? trim($segundo_apellido) : '';
        $email_trim = trim($email);

        // Para los demás campos, si vienen vacíos o no se pasan correctamente, se maneja un fallback seguro
        $fecha_nacimiento_trim = empty(trim($fecha_nacimiento)) ? '' : trim($fecha_nacimiento);
        $telefono_trim = empty(trim($telefono)) ? '' : trim($telefono);
        $direccion = empty(trim($direccion_habitacion)) ? 'Sin especificar' : trim($direccion_habitacion);

        if (!filter_var($email_trim, FILTER_VALIDATE_EMAIL)) {
            return $this->response(false, "El formato del correo electrónico no es válido");
        }

        // Llamada al modelo con parámetros limpios
        $request = $this->personaModel->actualizarPersona(
            $id_genero,
            $id_estatus,
            $cedula,
            $nombre1,
            $nombre2,
            $apellido1,
            $apellido2,
            $fecha_nacimiento_trim,
            $email_trim,
            $telefono_trim,
            $direccion
        );

        if (isset($request['error'])) {
            return $this->response(false, $request['error']);
        }

        // Corregido $request['success'] a un manejo dinámico o fallback seguro por consistencia
        return $this->response(true, $request['success'] ?? $request['message'] ?? "Datos actualizados correctamente", $request['data'] ?? null);
    }
}