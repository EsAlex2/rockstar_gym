<?php
require_once __DIR__ . '/../controllers/controllers.php';

/* * clientesController.php
 * Autor: Alex Madrid
 * Fecha: 14/06/2026
 */

class ClientesController extends Controllers
{
    private $model;

    public function __construct($pdo)
    {
        parent::__construct($pdo);
        // Cargamos el modelo correspondiente a la gestión de clientes
        $this->model = $this->cargarModels('ClientesModel');
    }

    /**
     * Lista todos los clientes registrados con su información básica de la base de datos.
     */
    public function listarClientes()
    {
        $data = $this->model->listarClientes();

        // Manejo de errores devueltos por el modelo (ej: "No hay usuarios registrados" o fallos de PDO)
        if (isset($data['error'])) {
            return $this->response(false, $data['error']);
        }

        return $this->response(true, "Clientes obtenidos exitosamente", $data);
    }

    /**
     * Registra un nuevo cliente y genera automáticamente su código de acceso.
     * * @param array $datos Debe contener ['id_persona']
     */
    public function crearClientes(int $persona)
    {
        // Validamos que el ID de la persona sea mayor a 0
        if ($persona <= 0) {
            return $this->response(false, "El ID de la persona es obligatorio y debe ser válido.");
        }
        
        $id_persona = (int)$persona;

        // Llamar al método del modelo encargado de la inserción y lógica de código de acceso
        $request = $this->model->crearClientes($id_persona);

        // El modelo siempre retorna un array con 'success' o 'error'
        if (isset($request['error'])) {
            return $this->response(false, $request['error']);
        }

        return $this->response(true, $request['message'], $request['data'] ?? null);
    }

    public function actualizarCliente(int $id_cliente, int $id_estatus)
    {
        if (empty($id_cliente) || $id_cliente <= 0 || empty($id_estatus) || $id_estatus <= 0) {
            return $this->response(false, "El ID del cliente y del estatus son obligatorios");
        }

        $request = $this->model->actualizarCliente($id_cliente, $id_estatus);

        if (isset($request['error'])) {
            return $this->response(false, $request['error']);
        }

        return $this->response(true, $request['message'] ?? "Cliente actualizado correctamente");
    }

    public function eliminarCliente(int $id_cliente)
    {
        if (empty($id_cliente) || $id_cliente <= 0) {
            return $this->response(false, "El ID del cliente es obligatorio y debe ser válido");
        }

        $request = $this->model->eliminarCliente($id_cliente);

        if (isset($request['error'])) {
            return $this->response(false, $request['error']);
        }

        return $this->response(true, $request['message'] ?? "Cliente eliminado correctamente");
    }

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
    ) {
        if (empty($id_genero) || empty(trim($cedula_identidad)) || empty(trim($primer_nombre)) || 
            empty(trim($primer_apellido)) || empty(trim($fecha_nacimiento)) || 
            empty(trim($telefono)) || empty(trim($email)) || empty(trim($direccion_habitacion))) {
            return $this->response(false, "Todos los campos obligatorios (*) son requeridos.");
        }

        $request = $this->model->crearClienteDirecto(
            $id_genero,
            $cedula_identidad,
            $primer_nombre,
            $segundo_nombre,
            $primer_apellido,
            $segundo_apellido,
            $fecha_nacimiento,
            $telefono,
            $email,
            $direccion_habitacion
        );

        if (isset($request['error'])) {
            return $this->response(false, $request['error']);
        }

        return $this->response(true, $request['message'] ?? "Cliente registrado exitosamente", $request['data'] ?? null);
    }

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
    ) {
        if (empty($id_cliente) || empty($id_estatus) || empty($id_genero) || empty(trim($cedula_identidad)) || 
            empty(trim($primer_nombre)) || empty(trim($primer_apellido)) || empty(trim($fecha_nacimiento)) || 
            empty(trim($telefono)) || empty(trim($email)) || empty(trim($direccion_habitacion))) {
            return $this->response(false, "Todos los campos obligatorios (*) son requeridos.");
        }

        $request = $this->model->actualizarClienteCompleto(
            $id_cliente,
            $id_estatus,
            $id_genero,
            $cedula_identidad,
            $primer_nombre,
            $segundo_nombre,
            $primer_apellido,
            $segundo_apellido,
            $fecha_nacimiento,
            $telefono,
            $email,
            $direccion_habitacion
        );

        if (isset($request['error'])) {
            return $this->response(false, $request['error']);
        }

        return $this->response(true, $request['message'] ?? "Cliente actualizado exitosamente");
    }
}

// $pruebas = new ClientesController($pdo);

// echo $pruebas->listarClientes();

