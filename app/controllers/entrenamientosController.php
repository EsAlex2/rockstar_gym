<?php 
require_once __DIR__ . '/../controllers/controllers.php';

/* * entrenamientosController.php
 * Autor: Alex Madrid
 * Fecha: 14/06/2026
 */

class EntrenamientosController extends Controllers
{
    private $model;

    public function __construct($pdo)
    {
        parent::__construct($pdo);
        // Cargamos el modelo correspondiente a la gestión de entrenamientos o clases
        $this->model = $this->cargarModels('EntrenamientosModel');
    }

    /**
     * Lista todos los entrenamientos registrados con la información cruzada de entrenadores y sedes.
     */
    public function listarEntrenamientos()
    {
        $data = $this->model->listarEntrenamientos();
        
        if (isset($data['error'])) {
            return $this->response(false, $data['error']);
        }
        
        return $this->response(true, "Entrenamientos obtenidos exitosamente", $data);
    }

    public function listarEntrenamientoPorNombre(string $nombre)
    {
        $nombreEntrenamiento = trim($nombre);

        if(!isset($nombreEntrenamiento) || $nombreEntrenamiento === ''){
            return $this->response(false, "Debe ingresar el nombre del entrenamiento para realizar la busqueda");
        }

        $data = $this->model->listarEntrenamientosPorNombre($nombreEntrenamiento);

        if (isset($data['error'])) {
            return $this->response(false, $data['error']);
        }

        return $this->response(true, "Entrenamiento encontrado con éxito", $data);
    }

    /**
     * Registra un nuevo entrenamiento o clase en el sistema.
     * @param array $datos Debe contener ['id_entrenador', 'id_sede', 'nombre_entrenamiento', 'descripcion']
     */
    public function crearEntrenamientos(array $datos)
    {
        $camposObligatorios = ['id_entrenador', 'id_sede', 'nombre_entrenamiento', 'descripcion'];

        // Validación de campos obligatorios
        foreach ($camposObligatorios as $campo) {
            if (!isset($datos[$campo]) || trim($datos[$campo]) === '') {
                return $this->response(false, "Todos los campos son obligatorios");
            }
        }

        $id_entrenador = (int)$datos['id_entrenador'];
        $id_sede = (int)$datos['id_sede'];
        $nombre = trim($datos['nombre_entrenamiento']);
        $descripcion = trim($datos['descripcion']);

        // Llamada al método de inserción del modelo
        $request = $this->model->crearEntrenamientos($id_entrenador, $id_sede, $nombre, $descripcion);

        if (isset($request['error'])) {
            return $this->response(false, $request['error']);
        }

        return $this->response(true, $request['message'], $request['data'] ?? null);
    }

    /**
     * Actualiza los datos de un entrenamiento existente.
     * @param array $datos Debe contener ['id', 'id_entrenador', 'id_sede', 'nombre_entrenamiento', 'descripcion']
     */
    public function actualizarEntrenamientos(array $datos)
    {
        $camposObligatorios = ['id', 'id_entrenador', 'id_sede', 'nombre_entrenamiento', 'descripcion'];

        foreach ($camposObligatorios as $campo) {
            if (!isset($datos[$campo]) || trim((string)$datos[$campo]) === '') {
                return $this->response(false, "Todos los campos son obligatorios");
            }
        }

        $id = (int)$datos['id'];
        $id_entrenador = (int)$datos['id_entrenador'];
        $id_sede = (int)$datos['id_sede'];
        $nombre = trim($datos['nombre_entrenamiento']);
        $descripcion = trim($datos['descripcion']);

        // Llamada al método de actualización del modelo
        $request = $this->model->actualizarEntrenamiento($id, $id_entrenador, $id_sede, $nombre, $descripcion);

        if (isset($request['error'])) {
            return $this->response(false, $request['error']);
        }

        return $this->response(true, $request['message'], $request['data'] ?? null);
    }

    public function eliminarEntrenamiento(int $id)
    {
        if (empty($id) || $id <= 0) {
            return $this->response(false, "El ID del entrenamiento es obligatorio y debe ser válido");
        }

        $request = $this->model->eliminarEntrenamiento($id);

        if (isset($request['error'])) {
            return $this->response(false, $request['error']);
        }

        return $this->response(true, $request['message'] ?? "Entrenamiento eliminado exitosamente");
    }

    public function inscribirCliente(int $id_cliente, int $id_entrenamiento)
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

    public function desinscribirCliente(int $id_cliente, int $id_entrenamiento)
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

    public function obtenerClienteEntrenamientos(int $id_cliente)
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

    public function obtenerEntrenamientosDisponibles(int $id_cliente)
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

    public function obtenerTodosClienteEntrenamientos()
    {
        $data = $this->model->listarTodosClienteEntrenamientos();

        if (isset($data['error'])) {
            return $this->response(false, $data['error']);
        }

        return $this->response(true, "Todas las inscripciones de entrenamientos obtenidas exitosamente", $data);
    }
}

// $pruebas = new EntrenamientosController($pdo);

// $datos = ["id_entrenador" => 1, "id_sede" => 2, "nombre_entrenamiento" => "Entrenamiento 1", "descripcion" => "Entrenamiento de Prueba"];

// echo $pruebas->crearEntrenamientos($datos);

// echo $pruebas->listarEntrenamientoPorNombre("2do entrenamiento");