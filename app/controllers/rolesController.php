<?php

require_once __DIR__ . '/../controllers/controllers.php';

/* * rolesController.php
 * Autor: Alex Madrid
 * Fecha: 14/06/2026
 * Nota: Refactorizado según el nuevo patrón de respuestas estandarizadas.
 */

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

        return $this->response(true, "Rol Encontrado con Exito", $response);
    }

    /**
     * Registra un nuevo rol validando que los campos requeridos no estén vacíos
     */
    public function crearNuevoRol(array $datos)
    {
        $camposObligatorios = ['nombre_rol', 'descripcion'];

        foreach ($camposObligatorios as $campo) {
            if (!isset($datos[$campo]) || trim($datos[$campo]) === '') {
                return $this->response(false, "Todos los campos son obligatorios");
            }
        }

        $nombre_rol  = trim($datos['nombre_rol']);
        $descripcion = trim($datos['descripcion']);

        // Llamar al modelo (el cual ya pasa a minúsculas y valida duplicados)
        $request = $this->model->crearRol($nombre_rol, $descripcion);

        if (isset($request['error'])) {
            return $this->response(false, $request['error']);
        }

        return $this->response(true, $request['message'], $request['data'] ?? null);
    }

    /**
     * Actualiza un rol existente validando su ID y coherencia de datos
     */
    public function actualizarDatosRol(array $datos)
    {
        $camposObligatorios = ['id_rol', 'nombre_rol', 'descripcion'];

        foreach ($camposObligatorios as $campo) {
            if (!isset($datos[$campo]) || trim($datos[$campo]) === '') {
                return $this->response(false, "Todos los campos son obligatorios");
            }
        }

        $id_rol      = (int)$datos['id_rol'];
        $nombre_rol  = trim($datos['nombre_rol']);
        $descripcion = trim($datos['descripcion']);

        // Enviar la solicitud de actualización al modelo
        $request = $this->model->actualizarRol($id_rol, $nombre_rol, $descripcion);

        if (isset($request['error'])) {
            return $this->response(false, $request['error']);
        }

        // Adaptamos el mensaje de éxito usando el retornado por el modelo o uno genérico estructurado
        $mensajeExito = $request['message'] ?? "Rol actualizado exitosamente";

        return $this->response(true, $mensajeExito, $request['data'] ?? null);
    }
}

$pruebas = new rolesController($pdo);

$datos = ["id_rol" => 8, "nombre_rol" => "rol actualizado", "descripcion" => "probando la actualizacion"];

echo $pruebas->actualizarDatosRol($datos);