<?php

require_once __DIR__ . '/controllers.php';

/**
 * Class EntrenamientosController
 * Controlador para la gestión de clases, entrenamientos y asignación de clientes.
 * Extiende de BaseController.
 */
class EntrenamientosController extends BaseController
{
    private EntrenamientosModel $model;

    public function __construct(?PDO $pdo = null)
    {
        parent::__construct($pdo);
        $this->model = $this->cargarModels('EntrenamientosModel');
    }

    /**
     * Lista todos los entrenamientos registrados.
     */
    public function listarEntrenamientos(): string
    {
        $data = $this->model->listarEntrenamientos();
        
        if (isset($data['error'])) {
            return $this->response(false, $data['error']);
        }
        
        return $this->response(true, "Entrenamientos obtenidos exitosamente", $data);
    }

    /**
     * Busca entrenamientos por coincidencia de nombre.
     */
    public function listarEntrenamientoPorNombre(string $nombre): string
    {
        $nombreLimpio = trim($nombre);

        if ($nombreLimpio === '') {
            return $this->response(false, "Debe ingresar el nombre del entrenamiento para realizar la búsqueda");
        }

        $data = $this->model->listarEntrenamientosPorNombre($nombreLimpio);

        if (isset($data['error'])) {
            return $this->response(false, $data['error']);
        }

        return $this->response(true, "Entrenamiento encontrado con éxito", $data);
    }

    /**
     * Registra un nuevo entrenamiento.
     */
    public function crearEntrenamientos(array $datos): string
    {
        $validationError = $this->validateRequiredFields($datos, ['id_entrenador', 'id_sede', 'nombre_entrenamiento', 'descripcion']);
        if ($validationError !== null) {
            return $this->response(false, "Todos los campos son obligatorios");
        }

        $id_entrenador = (int)$datos['id_entrenador'];
        $id_sede       = (int)$datos['id_sede'];
        $nombre        = trim($datos['nombre_entrenamiento']);
        $descripcion   = trim($datos['descripcion']);

        $request = $this->model->crearEntrenamientos($id_entrenador, $id_sede, $nombre, $descripcion);

        if (isset($request['error'])) {
            return $this->response(false, $request['error']);
        }

        return $this->response(true, $request['message'] ?? "Entrenamiento creado con éxito", $request['data'] ?? null);
    }

    /**
     * Actualiza un entrenamiento existente.
     */
    public function actualizarEntrenamientos(array $datos): string
    {
        $validationError = $this->validateRequiredFields($datos, ['id', 'id_entrenador', 'id_sede', 'nombre_entrenamiento', 'descripcion']);
        if ($validationError !== null) {
            return $this->response(false, "Todos los campos son obligatorios");
        }

        $id            = (int)$datos['id'];
        $id_entrenador = (int)$datos['id_entrenador'];
        $id_sede       = (int)$datos['id_sede'];
        $nombre        = trim($datos['nombre_entrenamiento']);
        $descripcion   = trim($datos['descripcion']);

        $request = $this->model->actualizarEntrenamiento($id, $id_entrenador, $id_sede, $nombre, $descripcion);

        if (isset($request['error'])) {
            return $this->response(false, $request['error']);
        }

        return $this->response(true, $request['message'] ?? "Entrenamiento actualizado con éxito", $request['data'] ?? null);
    }

    /**
     * Elimina un entrenamiento por su ID.
     */
    public function eliminarEntrenamiento(int $id): string
    {
        if ($id <= 0) {
            return $this->response(false, "El ID del entrenamiento es obligatorio y debe ser válido");
        }

        $request = $this->model->eliminarEntrenamiento($id);

        if (isset($request['error'])) {
            return $this->response(false, $request['error']);
        }

        return $this->response(true, $request['message'] ?? "Entrenamiento eliminado exitosamente");
    }

    /**
     * Inscribe a un cliente en un entrenamiento.
     */
    public function inscribirCliente(int $id_cliente, int $id_entrenamiento): string
    {
        if ($id_cliente <= 0 || $id_entrenamiento <= 0) {
            return $this->response(false, "El ID del cliente y del entrenamiento son obligatorios");
        }

        $request = $this->model->inscribirClienteEntrenamiento($id_cliente, $id_entrenamiento);

        if (isset($request['error'])) {
            return $this->response(false, $request['error']);
        }

        return $this->response(true, $request['message'] ?? "Cliente inscrito exitosamente");
    }

    /**
     * Desinscribe a un cliente de un entrenamiento.
     */
    public function desinscribirCliente(int $id_cliente, int $id_entrenamiento): string
    {
        if ($id_cliente <= 0 || $id_entrenamiento <= 0) {
            return $this->response(false, "El ID del cliente y del entrenamiento son obligatorios");
        }

        $request = $this->model->desinscribirClienteEntrenamiento($id_cliente, $id_entrenamiento);

        if (isset($request['error'])) {
            return $this->response(false, $request['error']);
        }

        return $this->response(true, $request['message'] ?? "Cliente desinscrito exitosamente");
    }

    /**
     * Obtiene los entrenamientos a los que un cliente está inscrito.
     */
    public function obtenerClienteEntrenamientos(int $id_cliente): string
    {
        if ($id_cliente <= 0) {
            return $this->response(false, "El ID del cliente es obligatorio");
        }

        $data = $this->model->listarEntrenamientosDeCliente($id_cliente);

        if (isset($data['error'])) {
            return $this->response(false, $data['error']);
        }

        return $this->response(true, "Entrenamientos del cliente obtenidos exitosamente", $data);
    }

    /**
     * Obtiene los entrenamientos disponibles para que un cliente se inscriba.
     */
    public function obtenerEntrenamientosDisponibles(int $id_cliente): string
    {
        if ($id_cliente <= 0) {
            return $this->response(false, "El ID del cliente es obligatorio");
        }

        $data = $this->model->listarEntrenamientosDisponiblesParaCliente($id_cliente);

        if (isset($data['error'])) {
            return $this->response(false, $data['error']);
        }

        return $this->response(true, "Entrenamientos disponibles obtenidos exitosamente", $data);
    }

    /**
     * Obtiene el listado de todas las inscripciones del gimnasio.
     */
    public function obtenerTodosClienteEntrenamientos(): string
    {
        $data = $this->model->listarTodosClienteEntrenamientos();

        if (isset($data['error'])) {
            return $this->response(false, $data['error']);
        }

        return $this->response(true, "Todas las inscripciones de entrenamientos obtenidas exitosamente", $data);
    }
}