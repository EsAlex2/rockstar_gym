<?php

require_once __DIR__ . '/controllers.php';

/**
 * Class HorarioEntrenamientoController
 * Controlador para la asignación y gestión de horarios en entrenamientos.
 * Extiende de BaseController.
 */
class HorarioEntrenamientoController extends BaseController
{
    private HorarioEntrenamientoModel $model;

    public function __construct(?PDO $pdo = null)
    {
        parent::__construct($pdo);
        $this->model = $this->cargarModels('HorarioEntrenamientoModel');
    }

    /**
     * Listar todos los horarios asignados a los entrenamientos.
     */
    public function listarEntrenamientoHorarios(): string
    {
        $data = $this->model->listarEntrenamientosConHorarios();
        
        if (isset($data['error'])) {
            return $this->response(false, $data['error']);
        }
        
        return $this->response(true, "Horarios de entrenamiento obtenidos exitosamente", $data);
    }

    /**
     * Asignar un horario y día a un entrenamiento.
     */
    public function asignarEntrenamientoHorario(array $datos): string
    {
        $validationError = $this->validateRequiredFields($datos, ['id_entrenamiento', 'id_horario', 'dia_semana']);
        if ($validationError !== null) {
            return $this->response(false, "Todos los campos son obligatorios");
        }

        $id_entrenamiento = (int)$datos['id_entrenamiento'];
        $id_horario       = (int)$datos['id_horario'];
        $dia_semana       = ucfirst(strtolower(trim($datos['dia_semana'])));

        $diasValidos = ['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado', 'Domingo'];

        if (!in_array($dia_semana, $diasValidos)) {
            return $this->response(false, "El día de la semana no es válido. Debe ser un día entre Lunes y Domingo");
        }

        $request = $this->model->asignarHorarioEntrenamiento($id_entrenamiento, $id_horario, $dia_semana);

        if (isset($request['error'])) {
            return $this->response(false, $request['error']);
        }

        return $this->response(true, $request['message'] ?? "Horario asignado", $request['data'] ?? null);
    }

    /**
     * Eliminar la asignación de un horario a un entrenamiento.
     */
    public function desasignarEntrenamientoHorario(array $datos): string
    {
        $validationError = $this->validateRequiredFields($datos, ['id_entrenamiento', 'id_horario', 'dia_semana']);
        if ($validationError !== null) {
            return $this->response(false, "Todos los campos son obligatorios para eliminar la asignación");
        }

        $id_entrenamiento = (int)$datos['id_entrenamiento'];
        $id_horario       = (int)$datos['id_horario'];
        $dia_semana       = ucfirst(strtolower(trim($datos['dia_semana'])));

        $request = $this->model->desasignarHorarioEntrenamiento($id_entrenamiento, $id_horario, $dia_semana);

        if (isset($request['error'])) {
            return $this->response(false, $request['error']);
        }

        return $this->response(true, $request['message'] ?? "Horario desvinculado exitosamente", $request['data'] ?? null);
    }
}