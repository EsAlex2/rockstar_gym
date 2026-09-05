<?php

require_once __DIR__ . '/controllers.php';

/**
 * Class RolesController
 * Controlador para la gestión de roles de usuarios y niveles de acceso.
 * Extiende de BaseController.
 */
class RolesController extends BaseController
{
    private RolesModel $model;

    public function __construct(?PDO $pdo = null)
    {
        parent::__construct($pdo);
        $this->model = $this->cargarModels('RolesModel');
    }

    /**
     * Lista todos los roles registrados.
     */
    public function listarRoles(): string
    {
        $data = $this->model->obtenerRoles();

        if (isset($data['error'])) {
            return $this->response(false, $data['error']);
        }

        return $this->response(true, "Roles obtenidos exitosamente", $data);
    }

    /**
     * Busca un rol por su nombre.
     */
    public function listarRolPorNombre(string $nombre): string
    {
        $nombreLimpio = trim($nombre);
        if ($nombreLimpio === '') {
            return $this->response(false, "El nombre del rol es requerido.");
        }

        $data = $this->model->obtenerRolPorNombre($nombreLimpio);

        if (isset($data['error'])) {
            return $this->response(false, $data['error']);
        }

        $response = [
            "id"          => $data['id'] ?? null,
            "nombre_rol"  => $data['nombre_rol'] ?? null,
            "descripcion" => $data['descripcion'] ?? null
        ];

        return $this->response(true, "Rol Encontrado con Éxito", $response);
    }

    /**
     * Registra un nuevo rol.
     */
    public function crearNuevoRol(string $nombre_rol, string $descripcion): string
    {
        $nombreLimpio = trim($nombre_rol);
        $descLimpia   = trim($descripcion);

        if ($nombreLimpio === '' || $descLimpia === '') {
            return $this->response(false, "Todos los campos son obligatorios");
        }

        $request = $this->model->crearRol($nombreLimpio, $descLimpia);

        if (isset($request['error'])) {
            return $this->response(false, $request['error']);
        }

        return $this->response(true, $request['message'] ?? "Rol creado con éxito", $request['data'] ?? null);
    }

    /**
     * Actualiza un rol existente.
     */
    public function actualizarDatosRol(int $id_rol, string $nombre_rol, string $descripcion): string
    {
        $nombreLimpio = trim($nombre_rol);
        $descLimpia   = trim($descripcion);

        if ($id_rol <= 0 || $nombreLimpio === '' || $descLimpia === '') {
            return $this->response(false, "Todos los campos son obligatorios y el ID debe ser válido");
        }

        $request = $this->model->actualizarRol($id_rol, $nombreLimpio, $descLimpia);

        if (isset($request['error'])) {
            return $this->response(false, $request['error']);
        }

        return $this->response(true, $request['message'] ?? "Rol actualizado exitosamente", $request['data'] ?? null);
    }

    /**
     * Elimina un rol por su ID.
     */
    public function eliminarRol(int $id_rol): string
    {
        if ($id_rol <= 0) {
            return $this->response(false, "El ID del rol es obligatorio y debe ser válido");
        }

        $request = $this->model->eliminarRol($id_rol);

        if (isset($request['error'])) {
            return $this->response(false, $request['error']);
        }

        return $this->response(true, $request['message'] ?? "Rol eliminado exitosamente");
    }
}