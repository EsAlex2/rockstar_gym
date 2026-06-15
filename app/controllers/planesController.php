<?php
require_once __DIR__ . '/../controllers/controllers.php';

/* * planesController.php
 * Autor: Alex Madrid
 * Refactorizado: 15/06/2026
 */

class planesController extends Controllers
{
    private $model;

    public function __construct($pdo)
    {
        parent::__construct($pdo);
        // Cargamos el modelo correspondiente según tu convención
        $this->model = $this->cargarModels('planModel');
    }

    /**
     * Obtiene y lista todos los planes registrados en el sistema
     */
    public function listarPlanes()
    {
        $data = $this->model->listarPlanes();

        if (isset($data['error'])) {
            return $this->response(false, $data['error']);
        }

        return $this->response(true, "Planes obtenidos exitosamente", $data);
    }

    /**
     * Busca la información detallada de un plan por su nombre
     */
    public function listarPlanPorNombre(string $nombre_plan)
    {
        $data = $this->model->buscarPlanPorNombre($nombre_plan);

        if (isset($data['error'])) {
            return $this->response(false, $data['error']);
        }

        return $this->response(true, "Plan encontrado exitosamente", $data);
    }

    /**
     * Registra un nuevo plan de suscripción en el sistema
     */
    public function crearPlan(string $nombre_plan, float $precio, int $duracion_dias, ?string $descripcion = null)
    {
        // Los campos obligatorios se evalúan directamente controlando tipos vacíos o nulos
        if (empty(trim($nombre_plan)) || !isset($precio) || empty($duracion_dias)) {
            return $this->response(false, "Todos los campos son obligatorios");
        }

        $nombre_plan   = trim($nombre_plan);
        $descripcion   = $descripcion !== null ? trim($descripcion) : '';
        
        // Validación de lógica de negocio basándonos en los CONSTRAINT de la base de datos
        if ($precio < 0) {
            return $this->response(false, "El precio del plan no puede ser un valor negativo");
        }

        if ($duracion_dias <= 0) {
            return $this->response(false, "La duración en días debe ser un número entero mayor a cero");
        }

        // Delegamos al modelo el registro y el chequeo de duplicados por nombre
        $request = $this->model->crearPlan($nombre_plan, $descripcion, $precio, $duracion_dias);

        if (isset($request['error'])) {
            return $this->response(false, $request['error']);
        }

        return $this->response(true, $request['message'] ?? "Plan creado con éxito", $request['data'] ?? null);
    }

    /**
     * Actualiza un plan existente permitiendo cambiar su estado y datos
     */
    public function actualizarPlan(int $id_plan, int $id_estatus, string $nombre_plan, float $precio, int $duracion_dias, ?string $descripcion = null)
    {
        // Se valida la existencia e integridad de los datos primitivos requeridos
        if (empty($id_plan) || empty($id_estatus) || empty(trim($nombre_plan)) || !isset($precio) || empty($duracion_dias)) {
            return $this->response(false, "Todos los campos son obligatorios");
        }

        $nombre_plan   = trim($nombre_plan);
        $descripcion   = $descripcion !== null ? trim($descripcion) : '';

        // Validaciones previas de los CONSTRAINT de la tabla antes de la ejecución SQL
        if ($precio < 0) {
            return $this->response(false, "El precio del plan no puede ser un valor negativo");
        }

        if ($duracion_dias <= 0) {
            return $this->response(false, "La duración en días debe ser un número entero mayor a cero");
        }

        // Llamar al modelo pasando las variables sanitizadas
        $request = $this->model->actualizarPlan($id_plan, $nombre_plan, $descripcion, $precio, $duracion_dias, $id_estatus);

        if (isset($request['error'])) {
            return $this->response(false, $request['error']);
        }

        return $this->response(true, $request['message'] ?? "Plan actualizado exitosamente", $request['data'] ?? null);
    }
}

// --- Zona de Pruebas Adaptada ---
$pruebas = new planesController($pdo);

// Pasamos los parámetros de forma limpia, separada y respetando los tipos nativos
echo $pruebas->actualizarPlan(1, 1, "plan2", 1.0, 10, "plan de pruebas");