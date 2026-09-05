<?php

require_once __DIR__ . '/controllers.php';

/**
 * Class EntrenadoresController
 * Controlador para la gestión de entrenadores y profesores del gimnasio.
 * Extiende de BaseController.
 */
class EntrenadoresController extends BaseController
{
    private EntrenadorModel $model;

    public function __construct(?PDO $pdo = null)
    {
        parent::__construct($pdo);
        $this->model = $this->cargarModels('EntrenadorModel');
    }

    /**
     * Lista todos los entrenadores.
     */
    public function obtenerEntrenadores(): string
    {
        $data = $this->model->listarEntrenadores();

        if (isset($data['error'])) {
            return $this->response(false, $data['error']);
        }

        return $this->response(true, "Entrenadores obtenidos exitosamente", $data);
    }

    /**
     * Busca un entrenador por la cédula de su persona vinculada.
     */
    public function buscarEntrenadorPorCedula(string $cedula_identidad): string
    {
        $cedula = trim($cedula_identidad);

        if ($cedula === '') {
            return $this->response(false, "La cédula de identidad es obligatoria para la búsqueda");
        }

        $data = $this->model->buscarEntrenadorPorCedula($cedula);

        if (isset($data['error'])) {
            return $this->response(false, $data['error']);
        }

        return $this->response(true, $data['message'] ?? "Entrenador encontrado", $data['data'] ?? null);
    }

    /**
     * Registra un nuevo entrenador.
     */
    public function crearEntrenadores(array $datos): string
    {
        $validationError = $this->validateRequiredFields($datos, ['id_persona', 'especialidad']);
        if ($validationError !== null) {
            return $this->response(false, "Todos los campos son obligatorios");
        }

        $id_persona   = (int)$datos['id_persona'];
        $especialidad = trim($datos['especialidad']);

        $request = $this->model->crearEntrenadores($id_persona, $especialidad);

        if (isset($request['error'])) {
            return $this->response(false, $request['error']);
        }

        return $this->response(true, $request['message'] ?? "Entrenador registrado", $request['data'] ?? null);
    }

    /**
     * Actualiza los datos de un entrenador.
     */
    public function actualizarEntrenador(int $id_entrenador, string $especialidad, int $id_estatus): string
    {
        if ($id_entrenador <= 0 || empty(trim($especialidad)) || $id_estatus <= 0) {
            return $this->response(false, "Todos los campos son obligatorios");
        }

        $request = $this->model->actualizarEntrenador($id_entrenador, trim($especialidad), $id_estatus);

        if (isset($request['error'])) {
            return $this->response(false, $request['error']);
        }

        return $this->response(true, $request['message'] ?? "Entrenador actualizado exitosamente");
    }

    /**
     * Elimina el registro de un entrenador.
     */
    public function eliminarEntrenador(int $id_entrenador): string
    {
        if ($id_entrenador <= 0) {
            return $this->response(false, "El ID del entrenador es obligatorio y debe ser válido");
        }

        $request = $this->model->eliminarEntrenador($id_entrenador);

        if (isset($request['error'])) {
            return $this->response(false, $request['error']);
        }

        return $this->response(true, $request['message'] ?? "Entrenador eliminado exitosamente");
    }
}