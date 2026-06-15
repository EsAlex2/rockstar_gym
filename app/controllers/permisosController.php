<?php 
require_once __DIR__ . '/../controllers/controllers.php';

/* * permisosController.php
 * Autor: Alex Madrid
 * Fecha: 14/06/2026
 */

class permisosController extends Controllers
{
    private $model;

    public function __construct($pdo)
    {
        parent::__construct($pdo);
        $this->model = $this->cargarModels('permisosModel');
    }

    /**
     * Lista todos los permisos registrados en el sistema
     */
    public function listarPermisos()
    {
        $data = $this->model->obtenerPermisos();
        
        if (isset($data['error'])) {
            return $this->response(false, $data['error']);
        }
        
        return $this->response(true, "Permisos obtenidos exitosamente", $data);
    }

    /**
     * Busca la información de un permiso específico mediante su nombre único
     */
    public function listarPermisoPorNombre(string $nombre_permiso)
    {
        $data = $this->model->obtenerPermisoPorNombre($nombre_permiso);

        if (isset($data['error'])) {
            return $this->response(false, $data['error']);
        }

        $response = [
            "nombre_permiso" => $data['data']['nombre_permiso'] ?? null
        ];

        return $this->response(true, "Permiso Encontrado con Exito", $response);
    }

    /**
     * Crea un nuevo permiso en el sistema validando los campos obligatorios
     */
    public function crearPermiso(array $datos)
    {
        $camposObligatorios = ['nombre_permiso', 'descripcion'];

        foreach ($camposObligatorios as $campo) {
            if (!isset($datos[$campo]) || trim($datos[$campo]) === '') {
                return $this->response(false, "Todos los campos son obligatorios");
            }
        }

        $nombre_permiso = trim($datos['nombre_permiso']);
        // La descripción es opcional en la tabla (puede ser NULL), si no viene guardamos un string vacío o nulo
        $descripcion = isset($datos['descripcion']) ? trim($datos['descripcion']) : '';

        // Llamar al modelo para ejecutar la inserción y las validaciones de duplicados (UNIQUE)
        $request = $this->model->crearPermiso($nombre_permiso, $descripcion);

        if (isset($request['error'])) {
            return $this->response(false, $request['error']);
        }

        return $this->response(true, $request['message'], $request['data'] ?? null);
    }
}

$pruebas = new permisosController($pdo);

$datos = ["nombre_permiso" => "permiso n2", "descripcion" => "permiso de prueba"];

echo $pruebas->crearPermiso($datos);