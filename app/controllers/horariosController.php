<?php

require_once __DIR__ . '/controllers.php';

/**
 * Class HorariosController
 * Controlador para la gestión de horarios y turnos del gimnasio.
 * Extiende de BaseController.
 */
class HorariosController extends BaseController
{
    private HorariosModel $model;

    public function __construct(?PDO $pdo = null)
    {
        parent::__construct($pdo);
        $this->model = $this->cargarModels('HorariosModel');
    }

    /**
     * Obtiene y lista todos los bloques horarios ordenados.
     */
    public function listarHorarios(): string
    {
        $data = $this->model->listarHorarios();
        
        if (isset($data['error'])) {
            return $this->response(false, $data['error']);
        }
        
        return $this->response(true, "Horarios obtenidos exitosamente", $data);
    }

    /**
     * Busca un bloque horario por su ID.
     */
    public function listarHorarioPorId(int $id_horario): string
    {
        if ($id_horario <= 0) {
            return $this->response(false, "El ID del horario debe ser válido.");
        }

        $data = $this->model->buscarHorarioPorId($id_horario);

        if (isset($data['error'])) {
            return $this->response(false, $data['error']);
        }

        $row = $data['data'][0] ?? [];
        $response = [
            "id"             => $row['id'] ?? null,
            "hora_inicio"    => $row['hora_inicio'] ?? null,
            "hora_fin"       => $row['hora_fin'] ?? null,
            "creado_en"      => $row['creado_en'] ?? null,
            "actualizado_en" => $row['actualizado_en'] ?? null
        ];

        return $this->response(true, "Horario Encontrado con Exito", $response);
    }

    /**
     * Registra un nuevo bloque horario.
     */
    public function crearHorario(array $datos): string
    {
        $validationError = $this->validateRequiredFields($datos, ['hora_inicio', 'hora_fin']);
        if ($validationError !== null) {
            return $this->response(false, "Todos los campos son obligatorios");
        }

        $hora_inicio = trim($datos['hora_inicio']);
        $hora_fin    = trim($datos['hora_fin']);

        $request = $this->model->crearHorario($hora_inicio, $hora_fin);

        if (isset($request['error'])) {
            return $this->response(false, $request['error']);
        }

        return $this->response(true, $request['message'] ?? "Horario creado exitosamente", $request['data'] ?? null);
    }

    /**
     * Actualiza un bloque horario existente.
     */
    public function actualizarHorario(array $datos): string
    {
        $validationError = $this->validateRequiredFields($datos, ['id', 'hora_inicio', 'hora_fin']);
        if ($validationError !== null) {
            return $this->response(false, "Todos los campos son obligatorios");
        }

        $id_horario  = (int)$datos['id'];
        $hora_inicio = trim($datos['hora_inicio']);
        $hora_fin    = trim($datos['hora_fin']);

        $request = $this->model->actualizarHorario($id_horario, $hora_inicio, $hora_fin);

        if (isset($request['error'])) {
            return $this->response(false, $request['error']);
        }

        return $this->response(true, $request['message'] ?? "Horario actualizado exitosamente", $request['data'] ?? null);
    }

    /**
     * Elimina un bloque horario.
     */
    public function eliminarHorario(int $id_horario): string
    {
        if ($id_horario <= 0) {
            return $this->response(false, "El ID del horario es obligatorio y debe ser válido");
        }

        $request = $this->model->eliminarHorario($id_horario);

        if (isset($request['error'])) {
            return $this->response(false, $request['error']);
        }

        return $this->response(true, $request['message'] ?? "Horario eliminado exitosamente");
    }
}
