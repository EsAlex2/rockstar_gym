<?php

require_once __DIR__ . '/controllers.php';

/**
 * Class PersonasController
 * Controlador para la gestión y flujo de datos de personas.
 * Extiende de BaseController y aplica validaciones estandarizadas,
 * respuestas unificadas y soporte para el padrón de usuarios.
 */
class PersonasController extends BaseController
{
    private PersonasModel $personaModel;

    public function __construct(?PDO $pdo = null)
    {
        parent::__construct($pdo);
        $this->personaModel = $this->cargarModels('PersonasModel');
    }

    /**
     * Lista todas las personas registradas junto a su estado y vinculación de usuario.
     */
    public function listarPersonas(): string
    {
        $data = $this->personaModel->obtenerPersonas();

        if (isset($data['error'])) {
            return $this->response(false, $data['error']);
        }

        return $this->response(true, "Personas obtenidas exitosamente", $data);
    }

    /**
     * Obtiene los datos de una persona por su ID.
     */
    public function obtenerPersona(int $id): string
    {
        if ($id <= 0) {
            return $this->response(false, "El ID de la persona es inválido");
        }

        $data = $this->personaModel->obtenerPersonaPorId($id);

        if (isset($data['error'])) {
            return $this->response(false, $data['error']);
        }

        return $this->response(true, "Persona obtenida exitosamente", $data);
    }

    /**
     * Busca una persona por su cédula de identidad.
     */
    public function listarPorCedula(string $cedula): string
    {
        $cedulaLimpia = trim($cedula);
        if ($cedulaLimpia === '') {
            return $this->response(false, "La cédula de identidad es obligatoria.");
        }

        $data = $this->personaModel->obtenerPersonaPorCedula($cedulaLimpia);

        if (isset($data['error'])) {
            return $this->response(false, $data['error']);
        }

        return $this->response(true, "Persona Encontrada con Éxito", $data);
    }

    /**
     * Alias de búsqueda por cédula para compatibilidad con vistas.
     */
    public function buscarPorCedula(string $cedula): string
    {
        return $this->listarPorCedula($cedula);
    }

    /**
     * Registra una nueva persona con validaciones estrictas.
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
    ): string {
        $validationError = $this->validateRequiredFields([
            'id_genero'            => $id_genero,
            'cedula_identidad'     => $cedula_identidad,
            'primer_nombre'        => $primer_nombre,
            'primer_apellido'      => $primer_apellido,
            'fecha_nacimiento'     => $fecha_nacimiento,
            'telefono'             => $telefono,
            'email'                => $email,
            'direccion_habitacion' => $direccion_habitacion
        ], ['id_genero', 'cedula_identidad', 'primer_nombre', 'primer_apellido', 'fecha_nacimiento', 'telefono', 'email', 'direccion_habitacion']);

        if ($validationError !== null) {
            return $this->response(false, $validationError['message'] ?? "Todos los campos con asterisco son obligatorios.");
        }

        $cleanEmail = $this->validateAndSanitizeEmail($email);
        if ($cleanEmail === null) {
            return $this->response(false, "El formato del correo electrónico ingresado no es válido.");
        }

        $request = $this->personaModel->crearPersona(
            $id_genero,
            trim($cedula_identidad),
            trim($primer_nombre),
            $segundo_nombre !== null ? trim($segundo_nombre) : '',
            trim($primer_apellido),
            $segundo_apellido !== null ? trim($segundo_apellido) : '',
            trim($fecha_nacimiento),
            trim($telefono),
            $cleanEmail,
            trim($direccion_habitacion)
        );

        if (isset($request['error'])) {
            return $this->response(false, $request['error']);
        }

        return $this->response(true, $request['message'] ?? "Persona registrada exitosamente", $request['data'] ?? null);
    }

    /**
     * Actualiza los datos de una persona existente.
     */
    public function actualizarDatosPersona(
        int $id_persona,
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
    ): string {
        if ($id_persona <= 0) {
            return $this->response(false, "El ID de la persona a actualizar es obligatorio.");
        }

        $validationError = $this->validateRequiredFields([
            'id_genero'            => $id_genero,
            'id_estatus'           => $id_estatus,
            'cedula_identidad'     => $cedula_identidad,
            'primer_nombre'        => $primer_nombre,
            'primer_apellido'      => $primer_apellido,
            'fecha_nacimiento'     => $fecha_nacimiento,
            'telefono'             => $telefono,
            'email'                => $email,
            'direccion_habitacion' => $direccion_habitacion
        ], ['id_genero', 'id_estatus', 'cedula_identidad', 'primer_nombre', 'primer_apellido', 'fecha_nacimiento', 'telefono', 'email', 'direccion_habitacion']);

        if ($validationError !== null) {
            return $this->response(false, $validationError['message'] ?? "Faltan campos obligatorios para la actualización.");
        }

        $cleanEmail = $this->validateAndSanitizeEmail($email);
        if ($cleanEmail === null) {
            return $this->response(false, "El formato del correo electrónico ingresado no es válido.");
        }

        $request = $this->personaModel->actualizarPersona(
            $id_persona,
            $id_genero,
            $id_estatus,
            trim($cedula_identidad),
            trim($primer_nombre),
            $segundo_nombre !== null ? trim($segundo_nombre) : '',
            trim($primer_apellido),
            $segundo_apellido !== null ? trim($segundo_apellido) : '',
            trim($fecha_nacimiento),
            trim($telefono),
            $cleanEmail,
            trim($direccion_habitacion)
        );

        if (isset($request['error'])) {
            return $this->response(false, $request['error']);
        }

        return $this->response(true, $request['message'] ?? "Datos actualizados correctamente", $request['data'] ?? null);
    }

    /**
     * Alterna o asigna el estatus de una persona.
     */
    public function cambiarEstatus(int $id_persona, int $nuevo_estatus): string
    {
        if ($id_persona <= 0 || !in_array($nuevo_estatus, [1, 2])) {
            return $this->response(false, "Parámetros inválidos para cambiar el estado.");
        }

        $request = $this->personaModel->cambiarEstatus($id_persona, $nuevo_estatus);

        if (isset($request['error'])) {
            return $this->response(false, $request['error']);
        }

        return $this->response(true, $request['message'] ?? "Estado de la persona actualizado exitosamente.");
    }

    /**
     * Elimina físicamente o inactiva preventivamente a una persona.
     */
    public function eliminarPersona(int $id_persona): string
    {
        if ($id_persona <= 0) {
            return $this->response(false, "El ID de la persona es obligatorio y debe ser válido.");
        }

        $request = $this->personaModel->eliminarPersona($id_persona);

        if (isset($request['error'])) {
            return $this->response(false, $request['error']);
        }

        return $this->response(true, $request['message'] ?? "Operación completada exitosamente.", [
            "inactivated" => $request['inactivated'] ?? false
        ]);
    }

    /**
     * Obtiene los catálogos auxiliares (géneros y estatus).
     */
    public function obtenerCatalogos(): string
    {
        return $this->response(true, "Catálogos obtenidos", [
            'generos' => $this->personaModel->obtenerGeneros(),
            'estatus' => $this->personaModel->obtenerEstatus()
        ]);
    }
}