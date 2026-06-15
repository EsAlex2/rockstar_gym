<?php

require_once __DIR__ . '/../controllers/controllers.php';

/* * rolesController.php
 * Autor: Alex Madrid
 * Refactorizado: 15/06/2026
 * */

class rolesController extends Controllers
{
    private $model;

    public function __construct($pdo)
    {
        parent::__construct($pdo);
        // Cargamos el modelo correspondiente
        $this->model = $this->cargarModels('rolesModel');
    }

    /**
     * Lista todos los roles registrados en el sistema
     */
    public function listarRoles()
    {
        $data = $this->model->obtenerRoles();

        // Si el modelo retorna un error o la estructura no es la esperada
        if (isset($data['error'])) {
            return $this->response(false, $data['error']);
        }

        return $this->response(true, "Roles obtenidos exitosamente", $data);
    }

    /**
     * Busca la información de un rol específico mediante su nombre único
     */
    public function listarRolPorNombre(string $nombre)
    {
        $data = $this->model->obtenerRolPorNombre($nombre);

        if (isset($data['error'])) {
            return $this->response(false, $data['error']);
        }

        // Mapeo estructurado siguiendo la consistencia de tus respuestas de usuario
        $response = [
            "id"          => $data['id'] ?? null,
            "nombre_rol"  => $data['nombre_rol'] ?? null,
            "descripcion" => $data['descripcion'] ?? null
        ];

        return $this->response(true, "Rol Encontrado con Éxito", $response);
    }

    /**
     * Registra un nuevo rol con parámetros individuales e independientes
     */
    public function crearNuevoRol(string $nombre_rol, string $descripcion)
    {
        // Validación individualizada y sanitización temprana
        if (empty(trim($nombre_rol)) || empty(trim($descripcion))) {
            return $this->response(false, "Todos los campos son obligatorios");
        }

        $nombre_rol  = trim($nombre_rol);
        $descripcion = trim($descripcion);

        // Llamar al modelo
        $request = $this->model->crearRol($nombre_rol, $descripcion);

        if (isset($request['error'])) {
            return $this->response(false, $request['error']);
        }

        return $this->response(true, $request['message'] ?? "Rol creado con éxito", $request['data'] ?? null);
    }

    /**
     * Actualiza un rol existente con parámetros separados y tipados
     */
    public function actualizarDatosRol(int $id_rol, string $nombre_rol, string $descripcion)
    {
        // Se valida que el ID sea correcto y que las cadenas no estén vacías
        if (empty($id_rol) || empty(trim($nombre_rol)) || empty(trim($descripcion))) {
            return $this->response(false, "Todos los campos son obligatorios");
        }

        $nombre_rol  = trim($nombre_rol);
        $descripcion = trim($descripcion);

        // Enviar la solicitud de actualización al modelo
        $request = $this->model->actualizarRol($id_rol, $nombre_rol, $descripcion);

        if (isset($request['error'])) {
            return $this->response(false, $request['error']);
        }

        return $this->response(true, $request['message'] ?? "Rol actualizado exitosamente", $request['data'] ?? null);
    }
}