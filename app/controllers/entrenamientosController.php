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

    /**
     * Obtiene los detalles de un entrenamiento específico a través de su ID.
     * @param int $id
     */
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
     * @param array $datos Debe contener ['id_entrenador', 'id_sede', 'nombre_entrenamiento', 'descripcion']
     */
    public function actualizarEntrenamientos(array $datos)
    {
        $camposObligatorios = ['id_entrenador', 'id_sede', 'nombre_entrenamiento', 'descripcion'];

        foreach ($camposObligatorios as $campo) {
            if (!isset($datos[$campo]) || trim($datos[$campo]) === '') {
                return $this->response(false, "Todos los campos son obligatorios");
            }
        }

        $id_entrenador = (int)$datos['id_entrenador'];
        $id_sede = (int)$datos['id_sede'];
        $nombre = trim($datos['nombre_entrenamiento']);
        $descripcion = trim($datos['descripcion']);

        // Llamada al método de actualización del modelo
        $request = $this->model->actualizarEntrenamientos($id_entrenador, $id_sede, $nombre, $descripcion);

        if (isset($request['error'])) {
            return $this->response(false, $request['error']);
        }

        return $this->response(true, $request['message'], $request['data'] ?? null);
    }
}

$pruebas = new EntrenamientosController($pdo);

echo $pruebas->listarEntrenamientoPorNombre("2do entrenamiento");