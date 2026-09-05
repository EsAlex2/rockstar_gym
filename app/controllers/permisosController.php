<?php

require_once __DIR__ . '/controllers.php';

/**
 * Class PermisosController
 * Controlador para la gestión de permisos y privilegios del sistema.
 * Extiende de BaseController.
 */
class PermisosController extends BaseController
{
    private PermisosModel $model;

    public function __construct(?PDO $pdo = null)
    {
        parent::__construct($pdo);
        $this->model = $this->cargarModels('PermisosModel');
    }

    /**
     * Lista todos los permisos del sistema.
     */
    public function listarPermisos(): string
    {
        $data = $this->model->obtenerPermisos();
        
        if (isset($data['error'])) {
            return $this->response(false, $data['error']);
        }
        
        return $this->response(true, "Permisos obtenidos exitosamente", $data);
    }

    /**
     * Busca un permiso por su nombre.
     */
    public function listarPermisoPorNombre(string $nombre_permiso): string
    {
        $nombreLimpio = trim($nombre_permiso);
        if ($nombreLimpio === '') {
            return $this->response(false, "El nombre del permiso es requerido.");
        }

        $data = $this->model->obtenerPermisoPorNombre($nombreLimpio);

        if (isset($data['error'])) {
            return $this->response(false, $data['error']);
        }

        $response = [
            "nombre_permiso" => $data['data']['nombre_permiso'] ?? null
        ];

        return $this->response(true, "Permiso Encontrado con Éxito", $response);
    }

    /**
     * Registra un nuevo permiso en el catálogo.
     */
    public function crearPermiso(string $permiso, string $desc): string
    {
        $nombreLimpio = trim($permiso);
        $descLimpia   = trim($desc);

        if ($nombreLimpio === '' || $descLimpia === '') {
            return $this->response(false, "Todos los campos son estrictamente obligatorios");
        }

        $request = $this->model->crearPermiso($nombreLimpio, $descLimpia);

        if (isset($request['error'])) {
            return $this->response(false, $request['error']);
        }

        return $this->response(true, $request['message'] ?? "Permiso Creado", $request['data'] ?? null);
    }

    /**
     * Actualiza un permiso existente.
     */
    public function actualizarPermiso(int $id_permiso, string $nombre_permiso, string $descripcion): string
    {
        $nombreLimpio = trim($nombre_permiso);
        $descLimpia   = trim($descripcion);

        if ($id_permiso <= 0 || $nombreLimpio === '' || $descLimpia === '') {
            return $this->response(false, "Todos los campos son obligatorios");
        }

        $request = $this->model->actualizarPermiso($id_permiso, $nombreLimpio, $descLimpia);

        if (isset($request['error'])) {
            return $this->response(false, $request['error']);
        }

        return $this->response(true, $request['message'] ?? "Permiso actualizado exitosamente");
    }

    /**
     * Elimina un permiso del catálogo.
     */
    public function eliminarPermiso(int $id_permiso): string
    {
        if ($id_permiso <= 0) {
            return $this->response(false, "El ID del permiso es obligatorio y debe ser válido");
        }

        $request = $this->model->eliminarPermiso($id_permiso);

        if (isset($request['error'])) {
            return $this->response(false, $request['error']);
        }

        return $this->response(true, $request['message'] ?? "Permiso eliminado exitosamente");
    }
}