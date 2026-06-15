<?php 
require_once __DIR__ . '/../controllers/controllers.php';

/* * entrenamientoHorarioController.php
 * Controlador para la gestión de horarios de entrenamiento
 */

class entrenamientoHorarioController extends Controllers
{
    private $model;

    public function __construct($pdo)
    {
        parent::__construct($pdo);
        // Cargamos el modelo correspondiente (siguiendo tu convención de nomenclatura)
        $this->model = $this->cargarModels('entrenamientoHorariosModel');
    }

    /**
     * Listar todos los horarios asignados a los entrenamientos
     */
    public function listarEntrenamientoHorarios()
    {
        $data = $this->model->listarEntrenamientosConHorarios();
        
        if (isset($data['error'])) {
            return $this->response(false, $data['error']);
        }
        
        return $this->response(true, "Horarios de entrenamiento obtenidos exitosamente", $data);
    }

    /**
     * Asignar un horario y día a un entrenamiento
     */
    public function asignarEntrenamientoHorario(array $datos)
    {
        // Validación de campos obligatorios según la estructura de la tabla
        $camposObligatorios = ['id_entrenamiento', 'id_horario', 'dia_semana'];

        foreach ($camposObligatorios as $campo) {
            if (!isset($datos[$campo]) || trim($datos[$campo]) === '') {
                return $this->response(false, "Todos los campos son obligatorios");
            }
        }

        $id_entrenamiento = (int)$datos['id_entrenamiento'];
        $id_horario = (int)$datos['id_horario'];
        $dia_semana = trim($datos['dia_semana']);

        // Validar que el día de la semana sea válido según el CHECK de la base de datos
        $diasValidos = ['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado', 'Domingo'];
        // Capitalizamos la primera letra por si viene en minúsculas
        $dia_semana = ucfirst(strtolower($dia_semana)); 

        if (!in_array($dia_semana, $diasValidos)) {
            return $this->response(false, "El día de la semana no es válido. Debe ser un día entre Lunes y Domingo");
        }

        // Llamar al modelo para insertar
        $request = $this->model->crearEntrenamientoHorario($id_entrenamiento, $id_horario, $dia_semana);

        if (isset($request['error'])) {
            return $this->response(false, $request['error']);
        }

        return $this->response(true, $request['message'], $request['data'] ?? null);
    }

    /**
     * Eliminar la asignación de un horario a un entrenamiento (ON DELETE CASCADE / RESTRICT)
     */
    public function desasignarEntrenamientoHorario(array $datos)
    {
        // Al ser una clave compuesta, necesitamos los 3 parámetros para eliminar la fila exacta
        $camposObligatorios = ['id_entrenamiento', 'id_horario', 'dia_semana'];

        foreach ($camposObligatorios as $campo) {
            if (!isset($datos[$campo]) || trim($datos[$campo]) === '') {
                return $this->response(false, "Todos los campos son obligatorios para eliminar la asignación");
            }
        }

        $id_entrenamiento = (int)$datos['id_entrenamiento'];
        $id_horario = (int)$datos['id_horario'];
        $dia_semana = trim($datos['dia_semana']);

        // Llamar al modelo para eliminar
        $request = $this->model->eliminarEntrenamientoHorario($id_entrenamiento, $id_horario, $dia_semana);

        if (isset($request['error'])) {
            return $this->response(false, $request['error']);
        }

        return $this->response(true, $request['message'], $request['data'] ?? null);
    }
}

$pruebas = new entrenamientoHorarioController($pdo);

echo $pruebas->listarEntrenamientoHorarios();