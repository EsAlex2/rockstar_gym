<?php 
require_once __DIR__ . '/../controllers/controllers.php';

/* * horariosController.php
 * Autor: Alex Madrid
 * Fecha: 14/06/2026
 */

class horariosController extends Controllers
{
    private $model;

    public function __construct($pdo)
    {
        parent::__construct($pdo);
        // Cargamos el modelo correspondiente
        $this->model = $this->cargarModels('horariosModel');
    }

    /**
     * Obtiene y lista todos los bloques horarios ordenados cronológicamente
     */
    public function listarHorarios()
    {
        $data = $this->model->listarHorarios();
        
        if (isset($data['error'])) {
            return $this->response(false, $data['error']);
        }
        
        return $this->response(true, "Horarios obtenidos exitosamente", $data);
    }

    /**
     * Busca la información de un bloque horario específico mediante su ID
     */
    public function listarHorarioPorId(int $id_horario)
    {
        $data = $this->model->buscarHorarioPorId($id_horario);

        if (isset($data['error'])) {
            return $this->response(false, $data['error']);
        }

        // Estructuramos la respuesta mapeando los datos tal como lo haces en tu ejemplo
        $response = [
            "id" => $data['data'][0]['id'] ?? null,
            "hora_inicio" => $data['data'][0]['hora_inicio'] ?? null,
            "hora_fin" => $data['data'][0]['hora_fin'] ?? null,
            "creado_en" => $data['data'][0]['creado_en'] ?? null,
            "actualizado_en" => $data['data'][0]['actualizado_en'] ?? null
        ];

        return $this->response(true, "Horario Encontrado con Exito", $response);
    }

    /**
     * Registra un nuevo bloque horario validando los campos requeridos
     */
    public function crearHorario(array $datos)
    {
        $camposObligatorios = ['hora_inicio', 'hora_fin'];

        foreach ($camposObligatorios as $campo) {
            if (!isset($datos[$campo]) || trim($datos[$campo]) === '') {
                return $this->response(false, "Todos los campos son obligatorios");
            }
        }

        $hora_inicio = trim($datos['hora_inicio']);
        $hora_fin = trim($datos['hora_fin']);

        // Llamar al modelo para ejecutar la lógica de negocio y restricciones UNIQUE
        $request = $this->model->crearHorario($hora_inicio, $hora_fin);

        if (isset($request['error'])) {
            return $this->response(false, $request['error']);
        }

        return $this->response(true, $request['message'], $request['data'] ?? null);
    }

    /**
     * Actualiza un bloque horario existente validando su ID y coherencia de datos
     */
    public function actualizarHorario(array $datos)
    {
        $camposObligatorios = ['hora_inicio', 'hora_fin'];

        foreach ($camposObligatorios as $campo) {
            if (!isset($datos[$campo]) || trim($datos[$campo]) === '') {
                return $this->response(false, "Todos los campos son obligatorios");
            }
        }

        $id_horario = (int)$datos['id'];
        $hora_inicio = trim($datos['hora_inicio']);
        $hora_fin = trim($datos['hora_fin']);

        // Llamar al modelo para actualizar el registro
        $request = $this->model->actualizarHorario($id_horario, $hora_inicio, $hora_fin);

        if (isset($request['error'])) {
            return $this->response(false, $request['error']);
        }

        return $this->response(true, $request['message'], $request['data'] ?? null);
    }
}

$pruebas = new horariosController($pdo);

// $datos = ['id' => 1, 'hora_inicio' => '08:00:00', 'hora_fin' => '09:00:00'];

// echo $pruebas->actualizarHorario($datos);
