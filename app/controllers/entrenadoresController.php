<?php 
require_once __DIR__ . '/../controllers/controllers.php';

/* * entrenadoresController.php
 * Autor: Alex Madrid
 * Fecha: 14/06/2026
 */

class EntrenadoresController extends Controllers
{
    private $model;

    public function __construct($pdo)
    {
        parent::__construct($pdo);
        // Cargamos el modelo correspondiente a la gestión de entrenadores
        $this->model = $this->cargarModels('entrenadorModel');
    }

    /**
     * Lista todos los entrenadores registrados en el sistema.
     */
    public function listarEntrenadores()
    {
        $data = $this->model->listarEntrenadores();
        
        if (isset($data['error'])) {
            return $this->response(false, $data['error']);
        }
        
        return $this->response(true, "Entrenadores obtenidos exitosamente", $data);
    }

    /**
     * Busca un entrenador específico por su número de cédula de identidad.
     * @param string $cedula_identidad
     */
    public function buscarEntrenadorPorCedula(string $cedula_identidad)
    {
        // Limpiamos un poco el parámetro por seguridad (removiendo espacios en blanco)
        $cedula = trim($cedula_identidad);

        if ($cedula === '') {
            return $this->response(false, "La cédula de identidad es obligatoria para la búsqueda");
        }

        $data = $this->model->listarPorCedula($cedula);

        if (isset($data['error'])) {
            return $this->response(false, $data['error']);
        }

        // Tu modelo ya estructura el array con 'message' y 'data' en caso de éxito
        return $this->response(true, $data['message'], $data['data'] ?? null);
    }

    /**
     * Registra un nuevo entrenador asignándole una especialidad.
     * @param array $datos Debe contener ['id_persona', 'especialidad']
     */
    public function crearEntrenadores(array $datos)
    {
        $camposObligatorios = ['id_persona', 'especialidad'];

        // Validación de campos requeridos por el modelo y la base de datos
        foreach ($camposObligatorios as $campo) {
            if (!isset($datos[$campo]) || trim($datos[$campo]) === '') {
                return $this->response(false, "Todos los campos son obligatorios");
            }
        }

        $id_persona = (int)$datos['id_persona'];
        $especialidad = trim($datos['especialidad']);

        // Llamada al método del modelo
        $request = $this->model->crearEntrenadores($id_persona, $especialidad);

        if (isset($request['error'])) {
            return $this->response(false, $request['error']);
        }

        return $this->response(true, $request['message'], $request['data'] ?? null);
    }
}

// $pruebas = new EntrenadoresController($pdo);

// $datos = ["id_persona" => 1, "especialidad" => "especialidad1"];

// echo $pruebas->crearEntrenadores($datos);