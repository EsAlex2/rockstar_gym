<?php
require_once __DIR__ . '/../controllers/controllers.php';

/* * planesController.php
 * Autor: Alex Madrid
 * Fecha: 14/06/2026
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
     * Busca la información detallada de un plan por su ID
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
    public function crearPlan(array $datos)
    {
        // Campos obligatorios requeridos según la estructura NOT NULL de la tabla
        $camposObligatorios = ['nombre_plan', 'precio', 'duracion_dias'];

        foreach ($camposObligatorios as $campo) {
            if (!isset($datos[$campo]) || trim((string)$datos[$campo]) === '') {
                return $this->response(false, "Todos los campos son obligatorios");
            }
        }

        $nombre_plan   = trim($datos['nombre_plan']);
        $descripcion   = isset($datos['descripcion']) ? trim($datos['descripcion']) : '';
        $precio        = (float)$datos['precio'];
        $duracion_dias = (int)$datos['duracion_dias'];

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

        return $this->response(true, $request['message'], $request['data'] ?? null);
    }

    /**
     * Actualiza un plan existente permitiendo cambiar su estado y datos
     */
    public function actualizarPlan(array $datos)
    {
        $camposObligatorios = ['id', 'id_estatus', 'nombre_plan', 'precio', 'duracion_dias'];

        foreach ($camposObligatorios as $campo) {
            if (!isset($datos[$campo]) || trim((string)$datos[$campo]) === '') {
                return $this->response(false, "Todos los campos son obligatorios");
            }
        }

        $id_plan       = (int)$datos['id'];
        $id_estatus    = (int)$datos['id_estatus'];
        $nombre_plan   = trim($datos['nombre_plan']);
        $descripcion   = isset($datos['descripcion']) ? trim($datos['descripcion']) : '';
        $precio        = (float)$datos['precio'];
        $duracion_dias = (int)$datos['duracion_dias'];

        // Validaciones previas de los CONSTRAINT de la tabla antes de la ejecución SQL
        if ($precio < 0) {
            return $this->response(false, "El precio del plan no puede ser un valor negativo");
        }

        if ($duracion_dias <= 0) {
            return $this->response(false, "La duración en días debe ser un número entero mayor a cero");
        }

        // Llamar al modelo
        $request = $this->model->actualizarPlan($id_plan, $nombre_plan, $descripcion, $precio, $duracion_dias, $id_estatus);

        if (isset($request['error'])) {
            return $this->response(false, $request['error']);
        }

        return $this->response(true, $request['message'], $request['data'] ?? null);
    }
}

$pruebas = new planesController($pdo);

$datos = [
    "id" => 1,
    "id_estatus" => 1,
    "nombre_plan" => "plan2",
    "descripcion" => "plan de pruebas",
    "precio" => 1.0,
    "duracion_dias" => 10
];

echo $pruebas->actualizarPlan($datos);