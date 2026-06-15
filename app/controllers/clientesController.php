<?php
require_once __DIR__ . '/../controllers/controllers.php';

/* * clientesController.php
 * Autor: Alex Madrid
 * Fecha: 14/06/2026
 */

class ClientesController extends Controllers
{
    private $model;

    public function __construct($pdo)
    {
        parent::__construct($pdo);
        // Cargamos el modelo correspondiente a la gestión de clientes
        $this->model = $this->cargarModels('ClientesModel');
    }

    /**
     * Lista todos los clientes registrados con su información básica de la base de datos.
     */
    public function listarClientes()
    {
        $data = $this->model->listarClientes();

        // Manejo de errores devueltos por el modelo (ej: "No hay usuarios registrados" o fallos de PDO)
        if (isset($data['error'])) {
            return $this->response(false, $data['error']);
        }

        return $this->response(true, "Clientes obtenidos exitosamente", $data);
    }

    /**
     * Registra un nuevo cliente y genera automáticamente su código de acceso.
     * * @param array $datos Debe contener ['id_persona']
     */
    public function crearClientes(int $persona)
    {
        $obligatorio = 'id_persona';

        // Validación de campos obligatorios
        if (!isset($obligatorio) || trim($obligatorio) === '') {
            return $this->response(false, "Todos los campos son obligatorios");
        }
        
        $id_persona = (int)$persona;

        // Llamar al método del modelo encargado de la inserción y lógica de código de acceso
        $request = $this->model->crearClientes($id_persona);

        // El modelo siempre retorna un array con 'success' o 'error'
        if (isset($request['error'])) {
            return $this->response(false, $request['error']);
        }

        return $this->response(true, $request['message'], $request['data'] ?? null);
    }
}
