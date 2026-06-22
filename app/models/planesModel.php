<?php
require_once __DIR__ . '/models.php';
require_once __DIR__ . '/../core/conn.php';

/* =================================================================================
 * planModel.php
 * Modelo para la gestión de los planes del gimnasio en el sistema de administración.
 * Autor: Alex Madrid
 * ==============================================================================
 */

class planModel extends Model
{
    protected $pdo;

    public function __construct($pdo)
    {
        parent::__construct($pdo);
        $this->pdo = $pdo;
    }

    /**
     * Registra un nuevo plan en el sistema.
     */
    public function crearPlan(string $nombre_plan, string $descripcion, float $precio, int $duracion_dias)
    {
        try {
            if (!$this->pdo) {
                return ["error" => "Error de conexión a la base de datos"];
            }

            $mayusNombre = strtoupper($nombre_plan);

            // Removido el prefijo 'administracion.'
            $checkDuplicate = $this->pdo->prepare("SELECT COUNT(*) FROM planes WHERE nombre_plan = :nombre");
            $checkDuplicate->bindParam(':nombre', $mayusNombre, PDO::PARAM_STR);
            $checkDuplicate->execute();

            if ($checkDuplicate->fetchColumn() > 0) {
                return ["error" => "Ya existe un plan registrado con el nombre: " . $nombre_plan];
            }

            // Removido el prefijo 'administracion.'
            $query = $this->pdo->prepare("INSERT INTO planes (id_estatus, nombre_plan, descripcion, precio, duracion_dias) VALUES (:id_estatus, :nombre, :descripcion, :precio, :duracion)");
            $estatus_default = 1;

            $query->bindParam(':id_estatus', $estatus_default, PDO::PARAM_INT);
            $query->bindParam(':nombre', $mayusNombre, PDO::PARAM_STR);
            $query->bindParam(':descripcion', $descripcion, PDO::PARAM_STR);
            $query->bindParam(':precio', $precio);
            $query->bindParam(':duracion', $duracion_dias, PDO::PARAM_INT);

            $query->execute();

            return [
                "success" => true,
                "message" => "Plan registrado exitosamente",
                "data" => [
                    "nombre_plan" => $mayusNombre,
                    "precio" => $precio,
                    "duracion_dias" => $duracion_dias
                ]
            ];
        } catch (PDOException $e) {
            return ["error" => "Error al crear el plan: " . $e->getMessage()];
        }
    }

    /**
     * Lista todos los planes registrados en la base de datos.
     */
    public function listarPlanes()
    {
        try {
            if (!$this->pdo) {
                return ["error" => "Error de conexión a la base de datos"];
            }

            // Removido el prefijo 'administracion.'
            $sql = $this->pdo->prepare("SELECT id, id_estatus, nombre_plan, descripcion, precio, duracion_dias FROM planes");
            $sql->execute();
            $resultado = $sql->fetchAll(PDO::FETCH_ASSOC);

            return empty($resultado) ? ["error" => "No hay planes registrados"] : $resultado;
        } catch (PDOException $e) {
            return ["error" => "Error al obtener los planes: " . $e->getMessage()];
        }
    }

    /**
     * Busca un plan específico por su ID trayendo el nombre del estatus mediante un INNER JOIN.
     */
    public function buscarPlanPorNombre(string $nombre_plan)
    {
        try {
            if (!$this->pdo) {
                return ["error" => "Error de conexión a la base de datos"];
            }

            // 1era Validación: Verificar si el plan existe en la tabla (Removido prefijo)
            $checkPlan = $this->pdo->prepare("SELECT COUNT(*) FROM planes WHERE nombre_plan = :nombre_plan");
            $checkPlan->bindParam(':nombre_plan', $nombre_plan, PDO::PARAM_STR);
            $checkPlan->execute();

            if ($checkPlan->fetchColumn() == 0) {
                return ["error" => "El plan solicitado no existe en la base de datos"];
            }

            // Removido prefijo de esquema 'administracion.' de 'planes' y 'estatus'
            $buscarInfo = $this->pdo->prepare("SELECT a.id, b.nombre_estatus AS Estatus, a.nombre_plan AS Plan, a.descripcion AS Descripcion, a.precio AS Precio, a.duracion_dias AS Duracion
                FROM planes a
                INNER JOIN estatus b ON a.id_estatus = b.id
                WHERE a.nombre_plan = :nombre_plan");

            $buscarInfo->bindParam(':nombre_plan', $nombre_plan, PDO::PARAM_STR);
            $buscarInfo->execute();

            $resultado = $buscarInfo->fetch(PDO::FETCH_ASSOC);

            return [$resultado];
        } catch (PDOException $e) {
            return ["error" => "Error al buscar el plan: " . $e->getMessage()];
        }
    }

    /**
     * Actualiza los datos de un plan existente.
     */
    public function actualizarPlan(int $id_plan, string $nombre_plan, string $descripcion, float $precio, int $duracion_dias, int $id_estatus)
    {
        try {
            if (!$this->pdo) {
                return ["error" => "Error de conexión a la base de datos"];
            }

            // Removido prefijo
            $checkExist = $this->pdo->prepare("SELECT COUNT(*) FROM planes WHERE id = :id");
            $checkExist->bindParam(':id', $id_plan, PDO::PARAM_INT);
            $checkExist->execute();

            if ($checkExist->fetchColumn() == 0) {
                return ["error" => "El plan que intenta actualizar no existe en la base de datos"];
            }

            $mayusNombre = strtoupper($nombre_plan);

            // Removido prefijo
            $checkDuplicate = $this->pdo->prepare("SELECT COUNT(*) FROM planes WHERE nombre_plan = :nombre AND id != :id");
            $checkDuplicate->bindParam(':nombre', $mayusNombre, PDO::PARAM_STR);
            $checkDuplicate->bindParam(':id', $id_plan, PDO::PARAM_INT);
            $checkDuplicate->execute();

            if ($checkDuplicate->fetchColumn() > 0) {
                return ["error" => "No se pudo actualizar. Ya existe otro plan registrado con el nombre: " . $nombre_plan];
            }

            // Removido prefijo
            $query = $this->pdo->prepare("UPDATE planes 
            SET id_estatus = :id_estatus, nombre_plan = :nombre, descripcion = :descripcion, precio = :precio, duracion_dias = :duracion 
            WHERE id = :id");

            $query->bindParam(':id', $id_plan, PDO::PARAM_INT);
            $query->bindParam(':id_estatus', $id_estatus, PDO::PARAM_INT);
            $query->bindParam(':nombre', $mayusNombre, PDO::PARAM_STR);
            $query->bindParam(':descripcion', $descripcion, PDO::PARAM_STR);
            $query->bindParam(':precio', $precio);
            $query->bindParam(':duracion', $duracion_dias, PDO::PARAM_INT);

            $query->execute();

            return [
                "success" => true,
                "message" => "Plan actualizado exitosamente",
                "data" => [
                    "id_plan" => $id_plan,
                    "nombre_plan" => $mayusNombre,
                    "precio" => $precio,
                    "id_estatus" => $id_estatus
                ]
            ];

        } catch (PDOException $e) {
            return ["error" => "Error al actualizar el plan: " . $e->getMessage()];
        }
    }
}