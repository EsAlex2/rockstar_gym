<?php 
require_once __DIR__ . '/../controllers/controllers.php';

/* * pagosController.php
 * Autor: Alex Madrid
 * Fecha: 14/06/2026
 */

class pagosController extends Controllers
{
    private $model;

    public function __construct($pdo)
    {
        parent::__construct($pdo);
        // Cargamos el modelo correspondiente
        $this->model = $this->cargarModels('pagosModel');
    }

    /**
     * Lista todos los movimientos de pago registrados en el sistema
     */
    public function listarPagos()
    {
        $data = $this->model->listarPagos();
        
        if (isset($data['error'])) {
            return $this->response(false, $data['error']);
        }
        
        return $this->response(true, "Historial de pagos obtenido exitosamente", $data);
    }

    /**
     * Busca un registro de pago específico mediante su ID
     */
    public function buscarPagoPorId(int $id_pago)
    {
        $data = $this->model->buscarPagoPorId($id_pago);

        if (isset($data['error'])) {
            return $this->response(false, $data['error']);
        }

        // Mapeo estructurado siguiendo la consistencia de tus respuestas previas
        $response = [
            "id" => $data['data'][0]['id'] ?? null,
            "id_banco" => $data['data'][0]['id_banco'] ?? null,
            "nombre_banco" => $data['data'][0]['nombre_banco'] ?? null,
            "id_cliente" => $data['data'][0]['id_cliente'] ?? null,
            "id_cliente_plan" => $data['data'][0]['id_cliente_plan'] ?? null,
            "id_user" => $data['data'][0]['id_user'] ?? null,
            "id_estatus" => $data['data'][0]['id_estatus'] ?? null,
            "nombre_estatus" => $data['data'][0]['nombre_estatus'] ?? null,
            "monto" => $data['data'][0]['monto'] ?? null,
            "fecha_pago" => $data['data'][0]['fecha_pago'] ?? null,
            "cod_referencia" => $data['data'][0]['cod_referencia'] ?? null,
            "creado_en" => $data['data'][0]['creado_en'] ?? null
        ];

        return $this->response(true, "Pago localizado con éxito", $response);
    }

    /**
     * Registra un nuevo pago validando la presencia obligatoria de todos sus campos
     */
    public function registrarPago(array $datos)
    {
        $camposObligatorios = [
            'id_banco', 
            'id_cliente', 
            'id_cliente_plan', 
            'id_user', 
            'id_estatus', 
            'monto', 
            'fecha_pago', 
            'cod_referencia'
        ];

        // Verificación de campos obligatorios
        foreach ($camposObligatorios as $campo) {
            if (!isset($datos[$campo]) || trim((string)$datos[$campo]) === '') {
                return $this->response(false, "Todos los campos son obligatorios para registrar el pago");
            }
        }

        // Castings y sanitización de datos antes de enviar al modelo
        $id_banco        = (int)$datos['id_banco'];
        $id_cliente      = (int)$datos['id_cliente'];
        $id_cliente_plan = (int)$datos['id_cliente_plan'];
        $id_user         = (int)$datos['id_user'];
        $id_estatus      = (int)$datos['id_estatus'];
        $monto           = (float)$datos['monto'];
        $fecha_pago      = trim($datos['fecha_pago']);
        $cod_referencia  = trim($datos['cod_referencia']);

        // Validación adicional del monto
        if ($monto <= 0) {
            return $this->response(false, "El monto del pago debe ser un valor mayor a cero");
        }

        // Llamar al método seguro del modelo
        $request = $this->model->registrarPago(
            $id_banco, 
            $id_cliente, 
            $id_cliente_plan, 
            $id_user, 
            $id_estatus, 
            $monto, 
            $fecha_pago, 
            $cod_referencia
        );

        if (isset($request['error'])) {
            return $this->response(false, $request['error']);
        }

        return $this->response(true, $request['message'], $request['data'] ?? null);
    }

    /**
     * Modifica el estado del pago para flujos de aprobación o rechazo
     */
    public function cambiarEstatusPago(array $datos)
    {
        $camposObligatorios = ['id', 'id_estatus'];

        foreach ($camposObligatorios as $campo) {
            if (!isset($datos[$campo]) || trim((string)$datos[$campo]) === '') {
                return $this->response(false, "El ID del pago y el nuevo estatus son obligatorios");
            }
        }

        $id_pago          = (int)$datos['id'];
        $nuevo_id_estatus = (int)$datos['id_estatus'];

        // Enviar la solicitud de cambio de estado al modelo
        $request = $this->model->cambiarEstatusPago($id_pago, $nuevo_id_estatus);

        if (isset($request['error'])) {
            return $this->response(false, $request['error']);
        }

        return $this->response(true, $request['message'], $request['data'] ?? null);
    }
}