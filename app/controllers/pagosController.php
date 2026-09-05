<?php

require_once __DIR__ . '/controllers.php';

/**
 * Class PagosController
 * Controlador para la gestión de transacciones de pago, auditoría y membresías.
 * Extiende de BaseController.
 */
class PagosController extends BaseController
{
    private PagosModel $model;

    public function __construct(?PDO $pdo = null)
    {
        parent::__construct($pdo);
        $this->model = $this->cargarModels('PagosModel');
    }

    /**
     * Lista todos los pagos registrados.
     */
    public function listarPagos(): string
    {
        $data = $this->model->listarPagos();

        if (isset($data['error'])) {
            return $this->response(false, $data['error']);
        }

        return $this->response(true, "Historial de pagos obtenido exitosamente", $data);
    }

    /**
     * Busca un pago específico por su ID.
     */
    public function buscarPagoPorId(int $id_pago): string
    {
        if ($id_pago <= 0) {
            return $this->response(false, "El ID del pago debe ser válido.");
        }

        $data = $this->model->buscarPagoPorId($id_pago);

        if (isset($data['error'])) {
            return $this->response(false, $data['error']);
        }

        $row = $data['data'][0] ?? [];
        $response = [
            "id"              => $row['id'] ?? null,
            "id_banco"        => $row['id_banco'] ?? null,
            "nombre_banco"    => $row['nombre_banco'] ?? null,
            "id_cliente"      => $row['id_cliente'] ?? null,
            "id_cliente_plan" => $row['id_cliente_plan'] ?? null,
            "id_user"         => $row['id_user'] ?? null,
            "id_estatus"      => $row['id_estatus'] ?? null,
            "nombre_estatus"  => $row['nombre_estatus'] ?? null,
            "monto"           => $row['monto'] ?? null,
            "fecha_pago"      => $row['fecha_pago'] ?? null,
            "cod_referencia"  => $row['cod_referencia'] ?? null,
            "creado_en"       => $row['creado_en'] ?? null
        ];

        return $this->response(true, "Pago localizado con éxito", $response);
    }

    /**
     * Registra un nuevo pago en el sistema.
     */
    public function registrarPago(
        int $id_banco,
        int $id_cliente,
        int $id_cliente_plan,
        int $id_user,
        int $id_estatus,
        float $monto,
        string $fecha_pago,
        string $cod_referencia
    ): string {
        $fechaLimpia = trim($fecha_pago);
        $refLimpia   = trim($cod_referencia);

        if ($fechaLimpia === '' || $refLimpia === '') {
            return $this->response(false, "Todos los campos son obligatorios para registrar el pago");
        }

        if ($monto <= 0) {
            return $this->response(false, "El monto del pago debe ser un valor mayor a cero");
        }

        $request = $this->model->registrarPago(
            $id_banco,
            $id_cliente,
            $id_cliente_plan,
            $id_user,
            $id_estatus,
            $monto,
            $fechaLimpia,
            $refLimpia
        );

        if (isset($request['error'])) {
            return $this->response(false, $request['error']);
        }

        return $this->response(true, $request['message'], $request['data'] ?? null);
    }

    /**
     * Cambia el estatus de un pago (Aprobado, Rechazado, Pendiente).
     */
    public function cambiarEstatusPago(int $id, int $id_estatus): string
    {
        if ($id <= 0 || $id_estatus <= 0) {
            return $this->response(false, "El ID del pago y el nuevo estatus deben ser valores válidos");
        }

        $request = $this->model->cambiarEstatusPago($id, $id_estatus);

        if (isset($request['error'])) {
            return $this->response(false, $request['error']);
        }

        return $this->response(true, $request['message'], $request['data'] ?? null);
    }

    /**
     * Elimina el registro de un pago.
     */
    public function eliminarPago(int $id): string
    {
        if ($id <= 0) {
            return $this->response(false, "El ID del pago es obligatorio y debe ser válido");
        }

        $request = $this->model->eliminarPago($id);

        if (isset($request['error'])) {
            return $this->response(false, $request['error']);
        }

        return $this->response(true, $request['message'] ?? "El pago ha sido eliminado correctamente");
    }

    /**
     * Actualiza un pago existente.
     */
    public function actualizarPago(
        int $id_pago,
        int $id_banco,
        int $id_cliente,
        int $id_plan,
        int $id_estatus,
        float $monto,
        string $fecha_pago,
        string $cod_referencia
    ): string {
        $fechaLimpia = trim($fecha_pago);
        $refLimpia   = trim($cod_referencia);

        if ($fechaLimpia === '' || $refLimpia === '') {
            return $this->response(false, "Todos los campos son obligatorios para actualizar el pago");
        }

        if ($monto <= 0) {
            return $this->response(false, "El monto del pago debe ser un valor mayor a cero");
        }

        $request = $this->model->actualizarPago(
            $id_pago,
            $id_banco,
            $id_cliente,
            $id_plan,
            $id_estatus,
            $monto,
            $fechaLimpia,
            $refLimpia
        );

        if (isset($request['error'])) {
            return $this->response(false, $request['error']);
        }

        return $this->response(true, $request['message']);
    }

    /**
     * Obtiene los pagos realizados por un cliente específico.
     */
    public function listarPagosPorCliente(int $id_cliente): string
    {
        if ($id_cliente <= 0) {
            return $this->response(false, "El ID del cliente debe ser válido.");
        }

        $data = $this->model->listarPagosPorCliente($id_cliente);

        if (isset($data['error'])) {
            return $this->response(false, $data['error']);
        }

        return $this->response(true, "Historial de pagos obtenido exitosamente", $data);
    }
}