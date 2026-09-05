<?php

require_once __DIR__ . '/controllers.php';

/**
 * Class ClientesController
 * Controlador para la gestión de clientes y miembros del gimnasio.
 * Extiende de BaseController.
 */
class ClientesController extends BaseController
{
    private ClientesModel $model;

    public function __construct(?PDO $pdo = null)
    {
        parent::__construct($pdo);
        $this->model = $this->cargarModels('ClientesModel');
    }

    /**
     * Lista todos los clientes registrados.
     */
    public function listarClientes(): string
    {
        $data = $this->model->listarClientes();

        if (isset($data['error'])) {
            return $this->response(false, $data['error']);
        }

        return $this->response(true, "Clientes obtenidos exitosamente", $data);
    }

    /**
     * Registra un cliente a partir del ID de persona.
     */
    public function crearClientes(int $persona): string
    {
        if ($persona <= 0) {
            return $this->response(false, "El ID de la persona es obligatorio y debe ser válido.");
        }

        $request = $this->model->crearClientes($persona);

        if (isset($request['error'])) {
            return $this->response(false, $request['error']);
        }

        return $this->response(true, $request['message'], $request['data'] ?? null);
    }

    /**
     * Actualiza el estado de un cliente.
     */
    public function actualizarCliente(int $id_cliente, int $id_estatus): string
    {
        if ($id_cliente <= 0 || $id_estatus <= 0) {
            return $this->response(false, "El ID del cliente y del estatus son obligatorios");
        }

        $request = $this->model->actualizarCliente($id_cliente, $id_estatus);

        if (isset($request['error'])) {
            return $this->response(false, $request['error']);
        }

        return $this->response(true, $request['message'] ?? "Cliente actualizado correctamente");
    }

    /**
     * Elimina el registro de un cliente.
     */
    public function eliminarCliente(int $id_cliente): string
    {
        if ($id_cliente <= 0) {
            return $this->response(false, "El ID del cliente es obligatorio y debe ser válido");
        }

        $request = $this->model->eliminarCliente($id_cliente);

        if (isset($request['error'])) {
            return $this->response(false, $request['error']);
        }

        return $this->response(true, $request['message'] ?? "Cliente eliminado correctamente");
    }

    /**
     * Crea un cliente y su registro personal en un solo paso.
     */
    public function crearClienteDirecto(
        int $id_genero,
        string $cedula_identidad,
        string $primer_nombre,
        ?string $segundo_nombre,
        string $primer_apellido,
        ?string $segundo_apellido,
        string $fecha_nacimiento,
        string $telefono,
        string $email,
        string $direccion_habitacion
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
            return $this->response(false, "Todos los campos obligatorios (*) son requeridos.");
        }

        $cleanEmail = $this->validateAndSanitizeEmail($email);
        if ($cleanEmail === null) {
            return $this->response(false, "El formato del correo electrónico no es válido.");
        }

        $request = $this->model->crearClienteDirecto(
            $id_genero,
            trim($cedula_identidad),
            trim($primer_nombre),
            $segundo_nombre,
            trim($primer_apellido),
            $segundo_apellido,
            trim($fecha_nacimiento),
            trim($telefono),
            $cleanEmail,
            trim($direccion_habitacion)
        );

        if (isset($request['error'])) {
            return $this->response(false, $request['error']);
        }

        return $this->response(true, $request['message'] ?? "Cliente registrado exitosamente", $request['data'] ?? null);
    }

    /**
     * Actualiza integralmente los datos del cliente y de la persona.
     */
    public function actualizarClienteCompleto(
        int $id_cliente,
        int $id_estatus,
        int $id_genero,
        string $cedula_identidad,
        string $primer_nombre,
        ?string $segundo_nombre,
        string $primer_apellido,
        ?string $segundo_apellido,
        string $fecha_nacimiento,
        string $telefono,
        string $email,
        string $direccion_habitacion
    ): string {
        if ($id_cliente <= 0 || $id_estatus <= 0 || $id_genero <= 0) {
            return $this->response(false, "Parámetros de identificación inválidos.");
        }

        $validationError = $this->validateRequiredFields([
            'cedula_identidad'     => $cedula_identidad,
            'primer_nombre'        => $primer_nombre,
            'primer_apellido'      => $primer_apellido,
            'fecha_nacimiento'     => $fecha_nacimiento,
            'telefono'             => $telefono,
            'email'                => $email,
            'direccion_habitacion' => $direccion_habitacion
        ], ['cedula_identidad', 'primer_nombre', 'primer_apellido', 'fecha_nacimiento', 'telefono', 'email', 'direccion_habitacion']);

        if ($validationError !== null) {
            return $this->response(false, "Todos los campos obligatorios (*) son requeridos.");
        }

        $cleanEmail = $this->validateAndSanitizeEmail($email);
        if ($cleanEmail === null) {
            return $this->response(false, "El formato del correo electrónico no es válido.");
        }

        $request = $this->model->actualizarClienteCompleto(
            $id_cliente,
            $id_estatus,
            $id_genero,
            trim($cedula_identidad),
            trim($primer_nombre),
            $segundo_nombre,
            trim($primer_apellido),
            $segundo_apellido,
            trim($fecha_nacimiento),
            trim($telefono),
            $cleanEmail,
            trim($direccion_habitacion)
        );

        if (isset($request['error'])) {
            return $this->response(false, $request['error']);
        }

        return $this->response(true, $request['message'] ?? "Cliente actualizado exitosamente");
    }
}
