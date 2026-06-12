<?php
require_once __DIR__ . '/models.php';
require_once __DIR__ . '/../core/conn.php';

/* =================================================================================
 * clientesPlanesModel.php
 * Modelo para la gestión de las membresías (planes adquiridos) de los clientes.
 * Controla la asignación de planes, cálculo de vencimientos y cambios de estatus.
 * Utiliza PDO para la interacción con la base de datos y maneja errores de ejecución.
 * Autor: Alex Madrid
 * Fecha: 12/06/2026
 * ==============================================================================
 */

class clientesPlanesModel extends Model
{
    protected $pdo;

    public function __construct($pdo)
    {
        parent::__construct($pdo);
        $this->pdo = $pdo;
    }

    public function asignarPlanCliente(int $id_cliente, int $id_plan, ?string $fecha_inicio = null)
    {
        try {
            if (!$this->pdo) {
                return ["error" => "Error de conexión a la base de datos"];
            }

            $checkCliente = $this->pdo->prepare("SELECT COUNT(*) FROM administracion.clientes WHERE id = :id_c");
            $checkCliente->bindParam(':id_c', $id_cliente, PDO::PARAM_INT);
            $checkCliente->execute();
            if ($checkCliente->fetchColumn() == 0) {
                return ["error" => "El cliente especificado no existe en el sistema"];
            }

            $checkPlan = $this->pdo->prepare("SELECT duracion_dias FROM administracion.planes WHERE id = :id_p");
            $checkPlan->bindParam(':id_p', $id_plan, PDO::PARAM_INT);
            $checkPlan->execute();
            $plan = $checkPlan->fetch(PDO::FETCH_ASSOC);

            if (!$plan) {
                return ["error" => "El plan seleccionado no existe en el sistema"];
            }

            $fechaInicioFormateada = ($fecha_inicio) ? $fecha_inicio : date('Y-m-d');

            $duracion = $plan['duracion_dias'];
            $fechaVencimiento = date('Y-m-d', strtotime($fechaInicioFormateada . " + $duracion days"));

            $estatus_default = 1; 

            // Inserción
            $query = $this->pdo->prepare("INSERT INTO administracion.clientes_planes 
                (id_cliente, id_plan, id_estatus, fecha_inicio, fecha_vencimiento) 
                VALUES (:id_c, :id_p, :id_e, :f_inicio, :f_vencimiento)");

            $query->bindParam(':id_c', $id_cliente, PDO::PARAM_INT);
            $query->bindParam(':id_p', $id_plan, PDO::PARAM_INT);
            $query->bindParam(':id_e', $estatus_default, PDO::PARAM_INT);
            $query->bindParam(':f_inicio', $fechaInicioFormateada, PDO::PARAM_STR);
            $query->bindParam(':f_vencimiento', $fechaVencimiento, PDO::PARAM_STR);
            
            $query->execute();

            return [
                "success" => true,
                "message" => "Plan asignado al cliente de manera exitosa",
                "data" => [
                    "id_cliente_plan" => $this->pdo->lastInsertId(),
                    "fecha_inicio" => $fechaInicioFormateada,
                    "fecha_vencimiento" => $fechaVencimiento
                ]
            ];

        } catch (PDOException $e) {
            return ["error" => "Error al asignar el plan al cliente: " . $e->getMessage()];
        }
    }

    public function listarClientesPlanes()
    {
        try {
            if (!$this->pdo) {
                return ["error" => "Error de conexión a la base de datos"];
            }

            $sql = $this->pdo->prepare("SELECT 
                    cp.id,
                    cp.id_cliente,
                    cp.id_plan,
                    p.nombre_plan AS plan,
                    p.precio,
                    cp.id_estatus,
                    e.nombre_estatus AS estatus,
                    cp.fecha_inicio,
                    cp.fecha_vencimiento,
                    cp.creado_en,
                    cp.actualizado_en
                FROM administracion.clientes_planes cp
                INNER JOIN administracion.planes p ON cp.id_plan = p.id
                INNER JOIN administracion.estatus e ON cp.id_estatus = e.id
                ORDER BY cp.creado_en DESC");

            $sql->execute();
            $resultado = $sql->fetchAll(PDO::FETCH_ASSOC);

            return empty($resultado) ? ["error" => "No hay membresías registradas actualmente"] : $resultado;
        } catch (PDOException $e) {
            return ["error" => "Error al obtener el listado de membresías: " . $e->getMessage()];
        }
    }

    public function buscarClientePlanPorId(int $id_cliente_plan)
    {
        try {
            if (!$this->pdo) {
                return ["error" => "Error de conexión a la base de datos"];
            }

            $query = $this->pdo->prepare("SELECT 
                    cp.id, cp.id_cliente, (c.nombre || ' ' || c.apellido) AS cliente, 
                    cp.id_plan, p.nombre_plan AS plan, cp.id_estatus, e.nombre_estatus AS estatus,
                    cp.fecha_inicio, cp.fecha_vencimiento, cp.creado_en, cp.actualizado_en
                FROM administracion.clientes_planes cp
                INNER JOIN administracion.clientes c ON cp.id_cliente = c.id
                INNER JOIN administracion.planes p ON cp.id_plan = p.id
                INNER JOIN administracion.estatus e ON cp.id_estatus = e.id
                WHERE cp.id = :id");

            $query->bindParam(':id', $id_cliente_plan, PDO::PARAM_INT);
            $query->execute();
            $resultado = $query->fetch(PDO::FETCH_ASSOC);

            if (!$resultado) {
                return ["error" => "La asignación de membresía solicitada no existe"];
            }

            return [
                "success" => true,
                "message" => "Membresía localizada exitosamente",
                "data" => [$resultado]
            ];
        } catch (PDOException $e) {
            return ["error" => "Error al buscar la membresía: " . $e->getMessage()];
        }
    }

    public function actualizarClientePlan(int $id_cliente_plan, int $id_estatus, string $fecha_inicio, string $fecha_vencimiento)
    {
        try {
            if (!$this->pdo) {
                return ["error" => "Error de conexión a la base de datos"];
            }

            $checkExist = $this->pdo->prepare("SELECT COUNT(*) FROM administracion.clientes_planes WHERE id = :id");
            $checkExist->bindParam(':id', $id_cliente_plan, PDO::PARAM_INT);
            $checkExist->execute();

            if ($checkExist->fetchColumn() == 0) {
                return ["error" => "La membresía que intenta actualizar no existe"];
            }

            if (strtotime($fecha_inicio) > strtotime($fecha_vencimiento)) {
                return ["error" => "La fecha de vencimiento no puede ser menor a la fecha de inicio"];
            }

            // Actualización
            $query = $this->pdo->prepare("UPDATE administracion.clientes_planes 
                SET id_estatus = :id_e, fecha_inicio = :f_ini, fecha_vencimiento = :f_venc, actualizado_en = CURRENT_TIMESTAMP 
                WHERE id = :id");

            $query->bindParam(':id', $id_cliente_plan, PDO::PARAM_INT);
            $query->bindParam(':id_e', $id_estatus, PDO::PARAM_INT);
            $query->bindParam(':f_ini', $fecha_inicio, PDO::PARAM_STR);
            $query->bindParam(':f_venc', $fecha_vencimiento, PDO::PARAM_STR);

            $query->execute();

            return [
                "success" => true,
                "message" => "Membresía actualizada con éxito",
                "data" => [
                    "id_cliente_plan" => $id_cliente_plan,
                    "id_estatus" => $id_estatus,
                    "fecha_vencimiento" => $fecha_vencimiento
                ]
            ];

        } catch (PDOException $e) {
            return ["error" => "Error al actualizar la membresía: " . $e->getMessage()];
        }
    }
}