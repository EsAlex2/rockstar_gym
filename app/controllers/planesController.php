<?php

require_once __DIR__ . '/controllers.php';

/**
 * Class PlanesController
 * Controlador para la administración de planes y membresías del gimnasio.
 * Extiende de BaseController.
 */
class PlanesController extends BaseController
{
    private PlanesModel $model;

    public function __construct(?PDO $pdo = null)
    {
        parent::__construct($pdo);
        $this->model = $this->cargarModels('PlanesModel');
    }

    /**
     * Obtiene y lista todos los planes registrados.
     */
    public function listarPlanes(): string
    {
        $data = $this->model->listarPlanes();

        if (isset($data['error'])) {
            return $this->response(false, $data['error']);
        }

        return $this->response(true, "Planes obtenidos exitosamente", $data);
    }

    /**
     * Busca un plan por su nombre.
     */
    public function listarPlanPorNombre(string $nombre_plan): string
    {
        $nombreLimpio = trim($nombre_plan);
        if ($nombreLimpio === '') {
            return $this->response(false, "El nombre del plan es requerido.");
        }

        $data = $this->model->buscarPlanPorNombre($nombreLimpio);

        if (isset($data['error'])) {
            return $this->response(false, $data['error']);
        }

        return $this->response(true, "Plan encontrado exitosamente", $data);
    }

    /**
     * Registra un nuevo plan.
     */
    public function crearPlan(string $nombre_plan, float $precio, int $duracion_dias, ?string $descripcion = null): string
    {
        $nombreLimpio = trim($nombre_plan);

        if ($nombreLimpio === '' || $duracion_dias <= 0) {
            return $this->response(false, "Todos los campos son obligatorios y la duración debe ser mayor a cero");
        }

        if ($precio < 0) {
            return $this->response(false, "El precio del plan no puede ser un valor negativo");
        }

        $request = $this->model->crearPlan($nombreLimpio, $descripcion !== null ? trim($descripcion) : '', $precio, $duracion_dias);

        if (isset($request['error'])) {
            return $this->response(false, $request['error']);
        }

        return $this->response(true, $request['message'] ?? "Plan creado con éxito", $request['data'] ?? null);
    }

    /**
     * Actualiza un plan existente.
     */
    public function actualizarPlan(int $id_plan, int $id_estatus, string $nombre_plan, float $precio, int $duracion_dias, ?string $descripcion = null): string
    {
        $nombreLimpio = trim($nombre_plan);

        if ($id_plan <= 0 || $id_estatus <= 0 || $nombreLimpio === '' || $duracion_dias <= 0) {
            return $this->response(false, "Todos los campos son obligatorios y deben ser válidos");
        }

        if ($precio < 0) {
            return $this->response(false, "El precio del plan no puede ser un valor negativo");
        }

        $request = $this->model->actualizarPlan($id_plan, $nombreLimpio, $descripcion !== null ? trim($descripcion) : '', $precio, $duracion_dias, $id_estatus);

        if (isset($request['error'])) {
            return $this->response(false, $request['error']);
        }

        return $this->response(true, $request['message'] ?? "Plan actualizado exitosamente", $request['data'] ?? null);
    }

    /**
     * Elimina un plan por su ID.
     */
    public function eliminarPlan(int $id_plan): string
    {
        if ($id_plan <= 0) {
            return $this->response(false, "El ID del plan es obligatorio y debe ser válido");
        }

        $request = $this->model->eliminarPlan($id_plan);

        if (isset($request['error'])) {
            return $this->response(false, $request['error']);
        }

        return $this->response(true, $request['message'] ?? "Plan eliminado exitosamente");
    }
}