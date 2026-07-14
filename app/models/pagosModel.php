<?php
require_once __DIR__ . '/models.php';
require_once __DIR__ . '/../core/conn.php';

/* =================================================================================
 * pagosModel.php
 * Modelo para la gestión y auditoría de pagos en el sistema de administración.
 * Por motivos de seguridad contable, este modelo NO permite actualizaciones de montos 
 * ni eliminaciones de registros. Solo inserción, consulta y cambio de estatus.
 * Autor: Alex Madrid
 * ==============================================================================
 */

class pagosModel extends Model
{
    protected $pdo;

    public function __construct($pdo)
    {
        parent::__construct($pdo);
        $this->pdo = $pdo;
    }

    /**
     * Registra un nuevo pago en el sistema.
     * Valida la existencia previa del código de referencia para evitar colisiones.
     * Resuelve el plan (recibido como ID de plan en id_cliente_plan) en la tabla clientes_planes.
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
    ) {
        try {
            if (!$this->pdo) {
                return ["error" => "Error de conexión a la base de datos"];
            }

            // Validar que la fecha de pago no sea futura
            if (strtotime($fecha_pago) > time()) {
                return ["error" => "La fecha de pago no puede ser en el futuro."];
            }

            $refLimpia = strtoupper(trim($cod_referencia));

            // 1. Validar el código de referencia duplicado
            $checkRef = $this->pdo->prepare("SELECT COUNT(*) FROM pagos WHERE cod_referencia = :ref");
            $checkRef->bindParam(':ref', $refLimpia, PDO::PARAM_STR);
            $checkRef->execute();

            if ($checkRef->fetchColumn() > 0) {
                return ["error" => "El código de referencia bancaria '" . $cod_referencia . "' ya fue registrado previamente."];
            }

            // 2. Obtener duración del plan a partir del ID de plan enviado (en id_cliente_plan)
            $stmtPlan = $this->pdo->prepare("SELECT duracion_dias FROM planes WHERE id = :id_plan");
            $stmtPlan->bindParam(':id_plan', $id_cliente_plan, PDO::PARAM_INT);
            $stmtPlan->execute();
            $plan = $stmtPlan->fetch(PDO::FETCH_ASSOC);

            if (!$plan) {
                return ["error" => "El plan seleccionado no existe en el catálogo."];
            }

            $duracion_dias = (int)$plan['duracion_dias'];

            // 3. Buscar si el cliente ya posee este plan activo en clientes_planes
            $stmtCheckCp = $this->pdo->prepare("SELECT id FROM clientes_planes 
                WHERE id_cliente = :id_cliente AND id_plan = :id_plan AND id_estatus = 1 LIMIT 1");
            $stmtCheckCp->bindParam(':id_cliente', $id_cliente, PDO::PARAM_INT);
            $stmtCheckCp->bindParam(':id_plan', $id_cliente_plan, PDO::PARAM_INT);
            $stmtCheckCp->execute();
            $cp = $stmtCheckCp->fetch(PDO::FETCH_ASSOC);

            if ($cp) {
                $resolved_cliente_plan_id = (int)$cp['id'];
            } else {
                // 4. Si no tiene el plan activo, crear uno nuevo
                $fecha_inicio = $fecha_pago;
                $fecha_vencimiento = date('Y-m-d', strtotime($fecha_inicio . ' + ' . $duracion_dias . ' days'));

                $stmtInsertCp = $this->pdo->prepare("INSERT INTO clientes_planes 
                    (id_cliente, id_plan, id_estatus, fecha_inicio, fecha_vencimiento) 
                    VALUES (:id_cliente, :id_plan, 1, :fecha_inicio, :fecha_vencimiento)");
                
                $stmtInsertCp->bindParam(':id_cliente', $id_cliente, PDO::PARAM_INT);
                $stmtInsertCp->bindParam(':id_plan', $id_cliente_plan, PDO::PARAM_INT);
                $stmtInsertCp->bindParam(':fecha_inicio', $fecha_inicio);
                $stmtInsertCp->bindParam(':fecha_vencimiento', $fecha_vencimiento);
                $stmtInsertCp->execute();

                $resolved_cliente_plan_id = (int)$this->pdo->lastInsertId();
            }

            // 5. Registrar el pago asociándolo a la membresía del cliente (resolved_cliente_plan_id)
            $query = $this->pdo->prepare("INSERT INTO pagos 
                (id_banco, id_cliente, id_cliente_plan, id_user, id_estatus, monto, fecha_pago, cod_referencia) 
                VALUES (:id_banco, :id_cliente, :id_cliente_plan, :id_user, :id_estatus, :monto, :fecha_pago, :ref)");

            $query->bindParam(':id_banco', $id_banco, PDO::PARAM_INT);
            $query->bindParam(':id_cliente', $id_cliente, PDO::PARAM_INT);
            $query->bindParam(':id_cliente_plan', $resolved_cliente_plan_id, PDO::PARAM_INT);
            $query->bindParam(':id_user', $id_user, PDO::PARAM_INT);
            $query->bindParam(':id_estatus', $id_estatus, PDO::PARAM_INT);
            $query->bindParam(':monto', $monto);
            $query->bindParam(':fecha_pago', $fecha_pago, PDO::PARAM_STR); 
            $query->bindParam(':ref', $refLimpia, PDO::PARAM_STR);

            $query->execute();

            self::verificarVencimientoMensualidades($this->pdo);

            return [
                "success" => true,
                "message" => "Pago registrado exitosamente de forma segura",
                "data" => [
                    "id_pago" => $this->pdo->lastInsertId(),
                    "monto" => $monto,
                    "cod_referencia" => $refLimpia
                ]
            ];
        } catch (PDOException $e) {
            return ["error" => "Error crítico al registrar el pago: " . $e->getMessage()];
        }
    }

    public function listarPagos()
    {
        try {
            if (!$this->pdo) {
                return ["error" => "Error de conexión a la base de datos"];
            }

            $sql = $this->pdo->prepare("SELECT 
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
                ORDER BY p.creado_en DESC");

            $sql->execute();
            $resultado = $sql->fetchAll(PDO::FETCH_ASSOC);

            return empty($resultado) ? ["error" => "No se registran movimientos de pago en el sistema"] : $resultado;
        } catch (PDOException $e) {
            return ["error" => "Error al obtener el historial de pagos: " . $e->getMessage()];
        }
    }

    public function listarPagosPorCliente(int $id_cliente)
    {
        try {
            if (!$this->pdo) {
                return ["error" => "Error de conexión a la base de datos"];
            }

            $sql = $this->pdo->prepare("SELECT 
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
                ORDER BY p.creado_en DESC");

            $sql->bindParam(':id_cliente', $id_cliente, PDO::PARAM_INT);
            $sql->execute();
            $resultado = $sql->fetchAll(PDO::FETCH_ASSOC);

            return empty($resultado) ? ["error" => "No se registran movimientos de pago para este cliente"] : $resultado;
        } catch (PDOException $e) {
            return ["error" => "Error al obtener el historial de pagos del cliente: " . $e->getMessage()];
        }
    }

    public function buscarPagoPorId(int $id_pago)
    {
        try {
            if (!$this->pdo) {
                return ["error" => "Error de conexión a la base de datos"];
            }

            $query = $this->pdo->prepare("SELECT 
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
                WHERE p.id = :id");
            
            $query->bindParam(':id', $id_pago, PDO::PARAM_INT);
            $query->execute();
            $resultado = $query->fetch(PDO::FETCH_ASSOC);

            if (!$resultado) {
                return ["error" => "El registro de pago solicitado no existe"];
            }

            return [
                "success" => true,
                "message" => "Pago localizado con éxito",
                "data" => [$resultado]
            ];
        } catch (PDOException $e) {
            return ["error" => "Error al buscar el pago por ID: " . $e->getMessage()];
        }
    }

    public function cambiarEstatusPago(int $id_pago, int $nuevo_id_estatus)
    {
        try {
            if (!$this->pdo) {
                return ["error" => "Error de conexión a la base de datos"];
            }

            $checkPago = $this->pdo->prepare("SELECT id_cliente_plan FROM pagos WHERE id = :id");
            $checkPago->bindParam(':id', $id_pago, PDO::PARAM_INT);
            $checkPago->execute();
            $pago = $checkPago->fetch(PDO::FETCH_ASSOC);

            if (!$pago) {
                return ["error" => "El pago que intenta modificar no existe"];
            }

            $id_cliente_plan = $pago['id_cliente_plan'];

            $checkEstatus = $this->pdo->prepare("SELECT COUNT(*) FROM estatus WHERE id = :id_e");
            $checkEstatus->bindParam(':id_e', $nuevo_id_estatus, PDO::PARAM_INT);
            $checkEstatus->execute();

            if ($checkEstatus->fetchColumn() == 0) {
                return ["error" => "El estatus seleccionado no es válido"];
            }

            $query = $this->pdo->prepare("UPDATE pagos SET id_estatus = :id_e WHERE id = :id");
            $query->bindParam(':id', $id_pago, PDO::PARAM_INT);
            $query->bindParam(':id_e', $nuevo_id_estatus, PDO::PARAM_INT);
            $query->execute();

            // Sincronizar el estado del plan/membresía correspondiente:
            // Pago Aprobado (4) -> Membresía Activa (1)
            // Pago Rechazado (5) -> Membresía Inactiva (2)
            // Pago Pendiente (3) -> Membresía Pendiente (3)
            $plan_estatus = 1; 
            if ($nuevo_id_estatus == 5) { 
                $plan_estatus = 2; 
            } elseif ($nuevo_id_estatus == 3) { 
                $plan_estatus = 3; 
            }

            if ($id_cliente_plan) {
                $stmtUpdateCp = $this->pdo->prepare("UPDATE clientes_planes SET id_estatus = :id_estatus WHERE id = :id_cp");
                $stmtUpdateCp->bindParam(':id_estatus', $plan_estatus, PDO::PARAM_INT);
                $stmtUpdateCp->bindParam(':id_cp', $id_cliente_plan, PDO::PARAM_INT);
                $stmtUpdateCp->execute();
            }

            self::verificarVencimientoMensualidades($this->pdo);

            return [
                "success" => true,
                "message" => "El estatus del pago ha sido actualizado correctamente"
            ];
        } catch (PDOException $e) {
            return ["error" => "Error al cambiar el estatus del pago: " . $e->getMessage()];
        }
    }

    public function eliminarPago(int $id_pago)
    {
        try {
            if (!$this->pdo) {
                return ["error" => "Error de conexión a la base de datos"];
            }

            // 1. Obtener el id_cliente_plan del pago antes de borrarlo
            $stmtGetCp = $this->pdo->prepare("SELECT id_cliente_plan FROM pagos WHERE id = :id");
            $stmtGetCp->bindParam(':id', $id_pago, PDO::PARAM_INT);
            $stmtGetCp->execute();
            $id_cliente_plan = $stmtGetCp->fetchColumn();

            // 2. Borrar el pago
            $stmt = $this->pdo->prepare("DELETE FROM pagos WHERE id = :id");
            $stmt->bindParam(':id', $id_pago, PDO::PARAM_INT);
            $stmt->execute();

            // 3. Borrar el cliente_plan asociado si existe
            if ($id_cliente_plan) {
                $stmtDelCp = $this->pdo->prepare("DELETE FROM clientes_planes WHERE id = :id_cp");
                $stmtDelCp->bindParam(':id_cp', $id_cliente_plan, PDO::PARAM_INT);
                $stmtDelCp->execute();
            }

            self::verificarVencimientoMensualidades($this->pdo);

            return ["success" => true, "message" => "El pago y su membresía han sido eliminados correctamente"];
        } catch (PDOException $e) {
            return ["error" => "Error al eliminar el pago: " . $e->getMessage()];
        }
    }

    public function actualizarPago(
        int $id_pago,
        int $id_banco,
        int $id_cliente,
        int $id_plan,
        int $id_estatus,
        float $monto,
        string $fecha_pago,
        string $cod_referencia
    ) {
        try {
            if (!$this->pdo) {
                return ["error" => "Error de conexión a la base de datos"];
            }

            // Validar fecha de pago no sea futura
            if (strtotime($fecha_pago) > time()) {
                return ["error" => "La fecha de pago no puede ser en el futuro."];
            }

            $refLimpia = strtoupper(trim($cod_referencia));

            // Validar que el código de referencia no esté duplicado en otro pago
            $checkRef = $this->pdo->prepare("SELECT COUNT(*) FROM pagos WHERE cod_referencia = :ref AND id != :id");
            $checkRef->bindParam(':ref', $refLimpia, PDO::PARAM_STR);
            $checkRef->bindParam(':id', $id_pago, PDO::PARAM_INT);
            $checkRef->execute();

            if ($checkRef->fetchColumn() > 0) {
                return ["error" => "El código de referencia bancaria '" . $cod_referencia . "' ya está registrado por otro pago."];
            }

            // Obtener datos del plan
            $stmtPlan = $this->pdo->prepare("SELECT duracion_dias FROM planes WHERE id = :id_plan");
            $stmtPlan->bindParam(':id_plan', $id_plan, PDO::PARAM_INT);
            $stmtPlan->execute();
            $plan = $stmtPlan->fetch(PDO::FETCH_ASSOC);

            if (!$plan) {
                return ["error" => "El plan seleccionado no existe en el catálogo."];
            }
            $duracion_dias = (int)$plan['duracion_dias'];

            // Obtener el id_cliente_plan actual del pago
            $stmtGetCp = $this->pdo->prepare("SELECT id_cliente_plan FROM pagos WHERE id = :id_pago");
            $stmtGetCp->bindParam(':id_pago', $id_pago, PDO::PARAM_INT);
            $stmtGetCp->execute();
            $id_cliente_plan = $stmtGetCp->fetchColumn();

            if (!$id_cliente_plan) {
                return ["error" => "No se encontró el registro de membresía asociado a este pago."];
            }

            // Calcular fecha_vencimiento
            $fecha_vencimiento = date('Y-m-d', strtotime($fecha_pago . ' + ' . $duracion_dias . ' days'));
            
            // Mapear estatus del pago al de membresía
            $plan_estatus = 1; 
            if ($id_estatus == 5) { 
                $plan_estatus = 2; 
            } elseif ($id_estatus == 3) { 
                $plan_estatus = 3; 
            }

            // Actualizar clientes_planes
            $stmtUpdateCp = $this->pdo->prepare("UPDATE clientes_planes 
                SET id_cliente = :id_cliente, 
                    id_plan = :id_plan, 
                    id_estatus = :id_estatus, 
                    fecha_inicio = :fecha_inicio, 
                    fecha_vencimiento = :fecha_vencimiento 
                WHERE id = :id_cp");
            $stmtUpdateCp->bindParam(':id_cliente', $id_cliente, PDO::PARAM_INT);
            $stmtUpdateCp->bindParam(':id_plan', $id_plan, PDO::PARAM_INT);
            $stmtUpdateCp->bindParam(':id_estatus', $plan_estatus, PDO::PARAM_INT);
            $stmtUpdateCp->bindParam(':fecha_inicio', $fecha_pago);
            $stmtUpdateCp->bindParam(':fecha_vencimiento', $fecha_vencimiento);
            $stmtUpdateCp->bindParam(':id_cp', $id_cliente_plan, PDO::PARAM_INT);
            $stmtUpdateCp->execute();

            // Actualizar pago
            $query = $this->pdo->prepare("UPDATE pagos 
                SET id_banco = :id_banco, 
                    id_cliente = :id_cliente, 
                    id_estatus = :id_estatus, 
                    monto = :monto, 
                    fecha_pago = :fecha_pago, 
                    cod_referencia = :ref 
                WHERE id = :id_pago");

            $query->bindParam(':id_banco', $id_banco, PDO::PARAM_INT);
            $query->bindParam(':id_cliente', $id_cliente, PDO::PARAM_INT);
            $query->bindParam(':id_estatus', $id_estatus, PDO::PARAM_INT);
            $query->bindParam(':monto', $monto);
            $query->bindParam(':fecha_pago', $fecha_pago, PDO::PARAM_STR); 
            $query->bindParam(':ref', $refLimpia, PDO::PARAM_STR);
            $query->bindParam(':id_pago', $id_pago, PDO::PARAM_INT);
            $query->execute();

            self::verificarVencimientoMensualidades($this->pdo);

            return [
                "success" => true,
                "message" => "Pago y membresía actualizados correctamente."
            ];
        } catch (PDOException $e) {
            return ["error" => "Error al actualizar el pago: " . $e->getMessage()];
        }
    }

    public static function verificarVencimientoMensualidades($pdo) {
        try {
            if (!$pdo) return;

            // 1. Marcar membresías vencidas como Vencido (6)
            $stmt1 = $pdo->prepare("UPDATE clientes_planes SET id_estatus = 6 WHERE fecha_vencimiento < CURRENT_DATE() AND id_estatus = 1");
            $stmt1->execute();

            // 2. Desactivar clientes (id_estatus = 2) que no tengan ningún plan activo vigente
            $stmt2 = $pdo->prepare("UPDATE clientes c 
                SET c.id_estatus = 2 
                WHERE c.id_estatus = 1 
                  AND NOT EXISTS (
                      SELECT 1 FROM clientes_planes cp 
                      WHERE cp.id_cliente = c.id 
                        AND cp.id_estatus = 1 
                        AND cp.fecha_vencimiento >= CURRENT_DATE()
                  )");
            $stmt2->execute();

            // 3. Desactivar usuarios (id_estatus = 2) de esos clientes desactivados
            $stmt3 = $pdo->prepare("UPDATE usuarios u 
                INNER JOIN clientes c ON u.id_persona = c.id_persona 
                SET u.id_estatus = 2 
                WHERE c.id_estatus = 2 AND u.id_estatus = 1");
            $stmt3->execute();

            // 4. Activar clientes (id_estatus = 1) que sí tienen al menos un plan activo vigente
            $stmt4 = $pdo->prepare("UPDATE clientes c 
                SET c.id_estatus = 1 
                WHERE c.id_estatus = 2 
                  AND EXISTS (
                      SELECT 1 FROM clientes_planes cp 
                      WHERE cp.id_cliente = c.id 
                        AND cp.id_estatus = 1 
                        AND cp.fecha_vencimiento >= CURRENT_DATE()
                  )");
            $stmt4->execute();

            // 5. Activar usuarios (id_estatus = 1) de esos clientes activos
            $stmt5 = $pdo->prepare("UPDATE usuarios u 
                INNER JOIN clientes c ON u.id_persona = c.id_persona 
                SET u.id_estatus = 1 
                WHERE c.id_estatus = 1 AND u.id_estatus = 2");
            $stmt5->execute();

        } catch (PDOException $e) {
            // Silently log or ignore
        }
    }
}