<?php

require_once __DIR__ . '/models.php';

/**
 * Class PagosModel
 * Modelo para la auditoría, registro y conciliación de pagos y membresías.
 * Extiende de BaseModel y gestiona la sincronización del ciclo de vida de los planes.
 */
class PagosModel extends BaseModel
{
    protected string $table = 'pagos';

    public function __construct(?PDO $pdo = null)
    {
        parent::__construct($pdo);
    }

    /**
     * Registra un nuevo pago en el sistema vinculándolo a la membresía del cliente.
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
    ): array {
        try {
            if (strtotime($fecha_pago) > time()) {
                return ["error" => "La fecha de pago no puede ser en el futuro."];
            }

            $refLimpia = strtoupper(trim($cod_referencia));

            // Validar unicidad del código de referencia
            if ($this->existsWhere('pagos', 'cod_referencia = :ref', [':ref' => $refLimpia])) {
                return ["error" => "El código de referencia bancaria '{$cod_referencia}' ya fue registrado previamente."];
            }

            // Obtener duración del plan
            $plan = $this->selectOne("SELECT duracion_dias FROM planes WHERE id = :id", [':id' => $id_cliente_plan]);
            if (!$plan) {
                return ["error" => "El plan seleccionado no existe en el catálogo."];
            }

            $duracionDias = (int)$plan['duracion_dias'];

            // Buscar si el cliente ya posee este plan activo
            $cp = $this->selectOne(
                "SELECT id FROM clientes_planes WHERE id_cliente = :cli AND id_plan = :plan AND id_estatus = 1 LIMIT 1",
                [':cli' => $id_cliente, ':plan' => $id_cliente_plan]
            );

            if ($cp) {
                $resolvedClientePlanId = (int)$cp['id'];
            } else {
                $fechaInicio = $fecha_pago;
                $fechaVencimiento = date('Y-m-d', strtotime($fechaInicio . ' + ' . $duracionDias . ' days'));

                $sqlInsertCp = "INSERT INTO clientes_planes (id_cliente, id_plan, id_estatus, fecha_inicio, fecha_vencimiento) 
                                VALUES (:cli, :plan, 1, :f_ini, :f_venc)";
                
                $this->executeQuery($sqlInsertCp, [
                    ':cli'    => $id_cliente,
                    ':plan'   => $id_cliente_plan,
                    ':f_ini'  => $fechaInicio,
                    ':f_venc' => $fechaVencimiento
                ]);

                $resolvedClientePlanId = (int)$this->pdo->lastInsertId();
            }

            $sqlPago = "INSERT INTO pagos (id_banco, id_cliente, id_cliente_plan, id_user, id_estatus, monto, fecha_pago, cod_referencia) 
                        VALUES (:banco, :cli, :cp, :user, :estatus, :monto, :fecha, :ref)";

            $this->executeQuery($sqlPago, [
                ':banco'   => $id_banco,
                ':cli'     => $id_cliente,
                ':cp'      => $resolvedClientePlanId,
                ':user'    => $id_user,
                ':estatus' => $id_estatus,
                ':monto'   => $monto,
                ':fecha'   => $fecha_pago,
                ':ref'     => $refLimpia
            ]);

            self::verificarVencimientoMensualidades($this->pdo);

            return [
                "success" => true,
                "message" => "Pago registrado exitosamente de forma segura",
                "data"    => [
                    "id_pago"        => (int)$this->pdo->lastInsertId(),
                    "monto"          => $monto,
                    "cod_referencia" => $refLimpia
                ]
            ];
        } catch (PDOException $e) {
            return $this->formatError("registrar el pago", $e);
        }
    }

    /**
     * Lista todos los pagos registrados.
     */
    public function listarPagos(): array
    {
        try {
            $sql = "SELECT 
                        p.id,
                        p.id_banco,
                        b.nombre_banco AS banco,
                        p.id_cliente,
                        CONCAT(per.primer_nombre, ' ', per.primer_apellido) AS cliente_nombre,
                        p.id_cliente_plan,
                        cp.id_plan,
                        pl.nombre_plan AS plan_nombre,
                        p.id_estatus,
                        e.nombre_estatus AS estatus,
                        p.monto,
                        p.fecha_pago,
                        p.cod_referencia,
                        p.creado_en
                    FROM pagos p
                    INNER JOIN bancos b ON p.id_banco = b.id
                    INNER JOIN estatus e ON p.id_estatus = e.id
                    INNER JOIN clientes c ON p.id_cliente = c.id
                    INNER JOIN personas per ON c.id_persona = per.id
                    INNER JOIN clientes_planes cp ON p.id_cliente_plan = cp.id
                    INNER JOIN planes pl ON cp.id_plan = pl.id
                    ORDER BY p.creado_en DESC";

            $resultado = $this->selectAll($sql);
            return empty($resultado) ? ["error" => "No se registran movimientos de pago en el sistema"] : $resultado;
        } catch (PDOException $e) {
            return $this->formatError("obtener historial de pagos", $e);
        }
    }

    /**
     * Lista el historial de pagos de un cliente específico.
     */
    public function listarPagosPorCliente(int $id_cliente): array
    {
        try {
            $sql = "SELECT 
                        p.id,
                        p.id_banco,
                        b.nombre_banco AS banco,
                        p.id_cliente,
                        CONCAT(per.primer_nombre, ' ', per.primer_apellido) AS cliente_nombre,
                        p.id_cliente_plan,
                        cp.id_plan,
                        pl.nombre_plan AS plan_nombre,
                        p.id_estatus,
                        e.nombre_estatus AS estatus,
                        p.monto,
                        p.fecha_pago,
                        p.cod_referencia,
                        p.creado_en
                    FROM pagos p
                    INNER JOIN bancos b ON p.id_banco = b.id
                    INNER JOIN estatus e ON p.id_estatus = e.id
                    INNER JOIN clientes c ON p.id_cliente = c.id
                    INNER JOIN personas per ON c.id_persona = per.id
                    INNER JOIN clientes_planes cp ON p.id_cliente_plan = cp.id
                    INNER JOIN planes pl ON cp.id_plan = pl.id
                    WHERE p.id_cliente = :id_cliente
                    ORDER BY p.creado_en DESC";

            $resultado = $this->selectAll($sql, [':id_cliente' => $id_cliente]);
            return empty($resultado) ? ["error" => "No se registran movimientos de pago para este cliente"] : $resultado;
        } catch (PDOException $e) {
            return $this->formatError("obtener historial de pagos del cliente", $e);
        }
    }

    /**
     * Busca un registro de pago por su identificador primario.
     */
    public function buscarPagoPorId(int $id_pago): array
    {
        try {
            $sql = "SELECT 
                        p.id, 
                        p.id_banco, 
                        b.nombre_banco, 
                        p.id_cliente, 
                        p.id_cliente_plan, 
                        cp.id_plan,
                        p.id_user, 
                        p.id_estatus, 
                        e.nombre_estatus, 
                        p.monto, 
                        p.fecha_pago, 
                        p.cod_referencia, 
                        p.creado_en
                    FROM pagos p
                    INNER JOIN bancos b ON p.id_banco = b.id
                    INNER JOIN estatus e ON p.id_estatus = e.id
                    LEFT JOIN clientes_planes cp ON p.id_cliente_plan = cp.id
                    WHERE p.id = :id
                    LIMIT 1";

            $resultado = $this->selectOne($sql, [':id' => $id_pago]);

            if (!$resultado) {
                return ["error" => "El registro de pago solicitado no existe"];
            }

            return [
                "success" => true,
                "message" => "Pago localizado con éxito",
                "data"    => [$resultado]
            ];
        } catch (PDOException $e) {
            return $this->formatError("buscar pago por ID", $e);
        }
    }

    /**
     * Modifica el estado del pago y sincroniza automáticamente la membresía asociada.
     */
    public function cambiarEstatusPago(int $id_pago, int $nuevo_id_estatus): array
    {
        try {
            $pago = $this->selectOne("SELECT id_cliente_plan FROM pagos WHERE id = :id", [':id' => $id_pago]);
            if (!$pago) {
                return ["error" => "El pago que intenta modificar no existe"];
            }

            if (!$this->existsWhere('estatus', 'id = :id', [':id' => $nuevo_id_estatus])) {
                return ["error" => "El estatus seleccionado no es válido"];
            }

            $this->executeQuery("UPDATE pagos SET id_estatus = :estatus WHERE id = :id", [
                ':estatus' => $nuevo_id_estatus,
                ':id'      => $id_pago
            ]);

            $planEstatus = match ($nuevo_id_estatus) {
                5 => 2, // Rechazado -> Inactiva
                3 => 3, // Pendiente -> Pendiente
                default => 1 // Aprobado / Otros -> Activa
            };

            if (!empty($pago['id_cliente_plan'])) {
                $this->executeQuery("UPDATE clientes_planes SET id_estatus = :estatus WHERE id = :id", [
                    ':estatus' => $planEstatus,
                    ':id'      => (int)$pago['id_cliente_plan']
                ]);
            }

            self::verificarVencimientoMensualidades($this->pdo);

            return [
                "success" => true,
                "message" => "El estatus del pago ha sido actualizado correctamente"
            ];
        } catch (PDOException $e) {
            return $this->formatError("cambiar estatus del pago", $e);
        }
    }

    /**
     * Elimina el pago y su membresía asociada.
     */
    public function eliminarPago(int $id_pago): array
    {
        try {
            $pago = $this->selectOne("SELECT id_cliente_plan FROM pagos WHERE id = :id", [':id' => $id_pago]);
            $idClientePlan = $pago['id_cliente_plan'] ?? null;

            $this->executeQuery("DELETE FROM pagos WHERE id = :id", [':id' => $id_pago]);

            if ($idClientePlan) {
                $this->executeQuery("DELETE FROM clientes_planes WHERE id = :id", [':id' => (int)$idClientePlan]);
            }

            self::verificarVencimientoMensualidades($this->pdo);

            return ["success" => true, "message" => "El pago y su membresía han sido eliminados correctamente"];
        } catch (PDOException $e) {
            return $this->formatError("eliminar el pago", $e);
        }
    }

    /**
     * Actualiza la información de un pago y recalcula la vigencia de la membresía.
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
    ): array {
        try {
            if (strtotime($fecha_pago) > time()) {
                return ["error" => "La fecha de pago no puede ser en el futuro."];
            }

            $refLimpia = strtoupper(trim($cod_referencia));

            if ($this->existsWhere('pagos', 'cod_referencia = :ref AND id != :id', [':ref' => $refLimpia, ':id' => $id_pago])) {
                return ["error" => "El código de referencia bancaria '{$cod_referencia}' ya está registrado por otro pago."];
            }

            $plan = $this->selectOne("SELECT duracion_dias FROM planes WHERE id = :id", [':id' => $id_plan]);
            if (!$plan) {
                return ["error" => "El plan seleccionado no existe en el catálogo."];
            }

            $pago = $this->selectOne("SELECT id_cliente_plan FROM pagos WHERE id = :id", [':id' => $id_pago]);
            $idClientePlan = $pago['id_cliente_plan'] ?? null;

            if (!$idClientePlan) {
                return ["error" => "No se encontró el registro de membresía asociado a este pago."];
            }

            $duracionDias     = (int)$plan['duracion_dias'];
            $fechaVencimiento = date('Y-m-d', strtotime($fecha_pago . ' + ' . $duracionDias . ' days'));
            $planEstatus      = ($id_estatus == 5) ? 2 : (($id_estatus == 3) ? 3 : 1);

            $this->executeQuery(
                "UPDATE clientes_planes SET id_cliente = :cli, id_plan = :plan, id_estatus = :estatus, fecha_inicio = :f_ini, fecha_vencimiento = :f_venc WHERE id = :id",
                [
                    ':cli'    => $id_cliente,
                    ':plan'   => $id_plan,
                    ':estatus'=> $planEstatus,
                    ':f_ini'  => $fecha_pago,
                    ':f_venc' => $fechaVencimiento,
                    ':id'     => $idClientePlan
                ]
            );

            $this->executeQuery(
                "UPDATE pagos SET id_banco = :banco, id_cliente = :cli, id_estatus = :estatus, monto = :monto, fecha_pago = :fecha, cod_referencia = :ref WHERE id = :id",
                [
                    ':banco'   => $id_banco,
                    ':cli'     => $id_cliente,
                    ':estatus' => $id_estatus,
                    ':monto'   => $monto,
                    ':fecha'   => $fecha_pago,
                    ':ref'     => $refLimpia,
                    ':id'      => $id_pago
                ]
            );

            self::verificarVencimientoMensualidades($this->pdo);

            return ["success" => true, "message" => "Pago y membresía actualizados correctamente."];
        } catch (PDOException $e) {
            return $this->formatError("actualizar el pago", $e);
        }
    }

    /**
     * Evalúa y actualiza los estatus de membresías vencidas y la activación/desactivación de clientes y usuarios.
     * @param PDO|null $pdo
     */
    public static function verificarVencimientoMensualidades(?PDO $pdo = null): void
    {
        try {
            if (!$pdo) {
                global $pdo;
                $pdo = $pdo ?? \App\Core\Database::getInstance()->getConnection();
            }

            // 1. Marcar membresías vencidas como Vencido (6)
            $pdo->exec("UPDATE clientes_planes SET id_estatus = 6 WHERE fecha_vencimiento < CURRENT_DATE() AND id_estatus = 1");

            // 2. Desactivar membresía de clientes (id_estatus = 2) que no tengan ningún plan activo vigente
            $pdo->exec("UPDATE clientes c 
                        SET c.id_estatus = 2 
                        WHERE c.id_estatus = 1 
                          AND NOT EXISTS (
                              SELECT 1 FROM clientes_planes cp 
                              WHERE cp.id_cliente = c.id 
                                AND cp.id_estatus = 1 
                                AND cp.fecha_vencimiento >= CURRENT_DATE()
                          )");

            // 3. Reactivar membresía de clientes que tengan al menos un plan activo vigente
            $pdo->exec("UPDATE clientes c 
                        SET c.id_estatus = 1 
                        WHERE c.id_estatus = 2 
                          AND EXISTS (
                              SELECT 1 FROM clientes_planes cp 
                              WHERE cp.id_cliente = c.id 
                                AND cp.id_estatus = 1 
                                AND cp.fecha_vencimiento >= CURRENT_DATE()
                          )");

        } catch (PDOException $e) {
            // Failsafe silencioso
        }
    }
}