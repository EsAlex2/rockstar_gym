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

            // Removidos los prefijos 'administracion.' en todas las tablas del JOIN
            $sql = $this->pdo->prepare("SELECT 
                    p.id,
                    b.nombre_banco AS banco,
                    p.id_cliente_plan,
                    e.nombre_estatus AS estatus,
                    p.monto,
                    p.fecha_pago,
                    p.cod_referencia,
                    p.creado_en
                FROM pagos p
                INNER JOIN bancos b ON p.id_banco = b.id
                INNER JOIN estatus e ON p.id_estatus = e.id
                ORDER BY p.creado_en DESC");

            $sql->execute();
            $resultado = $sql->fetchAll(PDO::FETCH_ASSOC);

            return empty($resultado) ? ["error" => "No se registran movimientos de pago en el sistema"] : $resultado;
        } catch (PDOException $e) {
            return ["error" => "Error al obtener el historial de pagos: " . $e->getMessage()];
        }
    }

    public function buscarPagoPorId(int $id_pago)
    {
        try {
            if (!$this->pdo) {
                return ["error" => "Error de conexión a la base de datos"];
            }

            // Removidos los prefijos 'administracion.'
            $query = $this->pdo->prepare("SELECT 
                    p.id, p.id_banco, b.nombre_banco, p.id_cliente, p.id_cliente_plan, 
                    p.id_user, p.id_estatus, e.nombre_estatus, p.monto, p.fecha_pago, p.cod_referencia, p.creado_en
                FROM pagos p
                INNER JOIN bancos b ON p.id_banco = b.id
                INNER JOIN estatus e ON p.id_estatus = e.id
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

            // Removidos los prefijos 'administracion.'
            $checkPago = $this->pdo->prepare("SELECT COUNT(*) FROM pagos WHERE id = :id");
            $checkPago->bindParam(':id', $id_pago, PDO::PARAM_INT);
            $checkPago->execute();

            if ($checkPago->fetchColumn() == 0) {
                return ["error" => "El pago que intenta modificar no existe"];
            }

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

            return [
                "success" => true,
                "message" => "El estatus del pago ha sido actualizado correctamente"
            ];
        } catch (PDOException $e) {
            return ["error" => "Error al cambiar el estatus del pago: " . $e->getMessage()];
        }
    }
}