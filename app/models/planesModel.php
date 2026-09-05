<?php

require_once __DIR__ . '/models.php';

/**
 * Class PlanesModel
 * Modelo para la gestión de los planes y tarifas del gimnasio.
 * Extiende de BaseModel.
 */
class PlanesModel extends BaseModel
{
    protected string $table = 'planes';

    public function __construct(?PDO $pdo = null)
    {
        parent::__construct($pdo);
    }

    /**
     * Registra un nuevo plan de membresía.
     */
    public function crearPlan(string $nombre_plan, string $descripcion, float $precio, int $duracion_dias): array
    {
        try {
            $mayusNombre = strtoupper(trim($nombre_plan));

            if ($this->existsWhere('planes', 'nombre_plan = :nombre', [':nombre' => $mayusNombre])) {
                return ["error" => "Ya existe un plan registrado con el nombre: {$nombre_plan}"];
            }

            $sql = "INSERT INTO planes (id_estatus, nombre_plan, descripcion, precio, duracion_dias) 
                    VALUES (1, :nombre, :descripcion, :precio, :duracion)";

            $this->executeQuery($sql, [
                ':nombre'      => $mayusNombre,
                ':descripcion' => trim($descripcion),
                ':precio'      => $precio,
                ':duracion'    => $duracion_dias
            ]);

            return [
                "success" => true,
                "message" => "Plan registrado exitosamente",
                "data"    => [
                    "id_plan"       => (int)$this->pdo->lastInsertId(),
                    "nombre_plan"   => $mayusNombre,
                    "precio"        => $precio,
                    "duracion_dias" => $duracion_dias
                ]
            ];
        } catch (PDOException $e) {
            return $this->formatError("crear el plan", $e);
        }
    }

    /**
     * Lista todos los planes registrados en el catálogo.
     */
    public function listarPlanes(): array
    {
        try {
            $sql = "SELECT id, id_estatus, nombre_plan, descripcion, precio, duracion_dias FROM planes ORDER BY id ASC";
            $resultado = $this->selectAll($sql);
            return empty($resultado) ? ["error" => "No hay planes registrados"] : $resultado;
        } catch (PDOException $e) {
            return $this->formatError("obtener los planes", $e);
        }
    }

    /**
     * Busca un plan por su nombre.
     */
    public function buscarPlanPorNombre(string $nombre_plan): array
    {
        try {
            $sql = "SELECT a.id, b.nombre_estatus AS Estatus, a.nombre_plan AS Plan, a.descripcion AS Descripcion, a.precio AS Precio, a.duracion_dias AS Duracion
                    FROM planes a
                    INNER JOIN estatus b ON a.id_estatus = b.id
                    WHERE a.nombre_plan = :nombre
                    LIMIT 1";

            $resultado = $this->selectOne($sql, [':nombre' => strtoupper(trim($nombre_plan))]);

            if (!$resultado) {
                return ["error" => "El plan solicitado no existe en la base de datos"];
            }

            return [$resultado];
        } catch (PDOException $e) {
            return $this->formatError("buscar el plan", $e);
        }
    }

    /**
     * Actualiza la información de un plan.
     */
    public function actualizarPlan(int $id_plan, string $nombre_plan, string $descripcion, float $precio, int $duracion_dias, int $id_estatus): array
    {
        try {
            if (!$this->existsWhere('planes', 'id = :id', [':id' => $id_plan])) {
                return ["error" => "El plan que intenta actualizar no existe en la base de datos"];
            }

            $mayusNombre = strtoupper(trim($nombre_plan));

            if ($this->existsWhere('planes', 'nombre_plan = :nombre AND id != :id', [':nombre' => $mayusNombre, ':id' => $id_plan])) {
                return ["error" => "No se pudo actualizar. Ya existe otro plan registrado con el nombre: {$nombre_plan}"];
            }

            $sql = "UPDATE planes 
                    SET id_estatus = :estatus, nombre_plan = :nombre, descripcion = :descripcion, precio = :precio, duracion_dias = :duracion 
                    WHERE id = :id";

            $this->executeQuery($sql, [
                ':estatus'     => $id_estatus,
                ':nombre'      => $mayusNombre,
                ':descripcion' => trim($descripcion),
                ':precio'      => $precio,
                ':duracion'    => $duracion_dias,
                ':id'          => $id_plan
            ]);

            return [
                "success" => true,
                "message" => "Plan actualizado exitosamente",
                "data"    => [
                    "id_plan"     => $id_plan,
                    "nombre_plan" => $mayusNombre,
                    "precio"      => $precio,
                    "id_estatus"  => $id_estatus
                ]
            ];
        } catch (PDOException $e) {
            return $this->formatError("actualizar el plan", $e);
        }
    }

    /**
     * Elimina un plan por su ID.
     */
    public function eliminarPlan(int $id_plan): array
    {
        try {
            if (!$this->existsWhere('planes', 'id = :id', [':id' => $id_plan])) {
                return ["error" => "El plan que intenta eliminar no existe en la base de datos"];
            }

            $this->executeQuery("DELETE FROM planes WHERE id = :id", [':id' => $id_plan]);
            return ["success" => true, "message" => "Plan eliminado exitosamente"];
        } catch (PDOException $e) {
            return $this->formatError("eliminar el plan", $e);
        }
    }
}