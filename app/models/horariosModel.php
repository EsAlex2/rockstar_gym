<?php
require_once __DIR__ . '/models.php';
require_once __DIR__ . '/../core/conn.php';

/* =================================================================================
 * horariosModel.php
 * Modelo para la gestión de los horarios del gimnasio en el sistema de administración.
 * Autor: Alex Madrid
 * ==============================================================================
 */

class horariosModel extends Model
{
    protected $pdo;

    public function __construct($pdo)
    {
        parent::__construct($pdo);
        $this->pdo = $pdo;
    }

    public function listarHorarios()
    {
        try {
            if (!$this->pdo) {
                return ["error" => "Error de conexión a la base de datos"];
            }

            $stmt = $this->pdo->prepare("SELECT id, hora_inicio, hora_fin FROM horarios ORDER BY hora_inicio ASC");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ["error" => "Error al obtener horarios: " . $e->getMessage()];
        }
    }

    public function crearHorario(string $hora_inicio, string $hora_fin)
    {
        try {
            if (!$this->pdo) {
                return ["error" => "Error de conexión a la base de datos"];
            }

            if (strtotime($hora_inicio) >= strtotime($hora_fin)) {
                return ["error" => "La hora de inicio no puede ser mayor o igual a la hora de fin"];
            }

            $checkDuplicate = $this->pdo->prepare("SELECT COUNT(*) FROM horarios WHERE hora_inicio = :hora_inicio AND hora_fin = :hora_fin");
            $checkDuplicate->bindParam(':hora_inicio', $hora_inicio, PDO::PARAM_STR);
            $checkDuplicate->bindParam(':hora_fin', $hora_fin, PDO::PARAM_STR);
            $checkDuplicate->execute();

            if ($checkDuplicate->fetchColumn() > 0) {
                return ["error" => "Ya existe ese bloque horario registrado"];
            }

            $query = $this->pdo->prepare("INSERT INTO horarios (hora_inicio, hora_fin) VALUES (:hora_inicio, :hora_fin)");
            $query->bindParam(':hora_inicio', $hora_inicio, PDO::PARAM_STR);
            $query->bindParam(':hora_fin', $hora_fin, PDO::PARAM_STR);
            $query->execute();

            return [
                "success" => true,
                "message" => "Horario creado exitosamente"
            ];
        } catch (PDOException $e) {
            return ["error" => "Error al crear horario: " . $e->getMessage()];
        }
    }

    public function buscarHorarioPorId(int $id_horario)
    {
        try {
            if (!$this->pdo) {
                return ["error" => "Error de conexión a la base de datos"];
            }

            $stmt = $this->pdo->prepare("SELECT id, hora_inicio, hora_fin, creado_en, actualizado_en FROM horarios WHERE id = :id");
            $stmt->bindParam(':id', $id_horario, PDO::PARAM_INT);
            $stmt->execute();
            $res = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$res) {
                return ["error" => "No se encontró el horario"];
            }

            return [
                "success" => true,
                "data" => [$res]
            ];
        } catch (PDOException $e) {
            return ["error" => "Error al buscar horario: " . $e->getMessage()];
        }
    }

    public function actualizarHorario(int $id_horario, string $hora_inicio, string $hora_fin)
    {
        try {
            if (!$this->pdo) {
                return ["error" => "Error de conexión a la base de datos"];
            }

            if (strtotime($hora_inicio) >= strtotime($hora_fin)) {
                return ["error" => "La hora de inicio no puede ser mayor o igual a la hora de fin"];
            }

            $checkDuplicate = $this->pdo->prepare("SELECT COUNT(*) FROM horarios WHERE hora_inicio = :hora_inicio AND hora_fin = :hora_fin AND id != :id");
            $checkDuplicate->bindParam(':hora_inicio', $hora_inicio, PDO::PARAM_STR);
            $checkDuplicate->bindParam(':hora_fin', $hora_fin, PDO::PARAM_STR);
            $checkDuplicate->bindParam(':id', $id_horario, PDO::PARAM_INT);
            $checkDuplicate->execute();

            if ($checkDuplicate->fetchColumn() > 0) {
                return ["error" => "No se pudo actualizar. Ya existe otro bloque horario de " . $hora_inicio . " a " . $hora_fin];
            }

            // 'CURRENT_TIMESTAMP' es plenamente compatible con MySQL/MariaDB
            $query = $this->pdo->prepare("UPDATE horarios 
                SET hora_inicio = :hora_inicio, hora_fin = :hora_fin, actualizado_en = CURRENT_TIMESTAMP 
                WHERE id = :id");

            $query->bindParam(':id', $id_horario, PDO::PARAM_INT);
            $query->bindParam(':hora_inicio', $hora_inicio, PDO::PARAM_STR);
            $query->bindParam(':hora_fin', $hora_fin, PDO::PARAM_STR);
            $query->execute();

            return [
                "success" => true,
                "message" => "Horario actualizado exitosamente",
                "data" => [
                    "id_horario" => $id_horario,
                    "hora_inicio" => $hora_inicio,
                    "hora_fin" => $hora_fin
                ]
            ];
        } catch (PDOException $e) {
            return ["error" => "Error al actualizar horario: " . $e->getMessage()];
        }
    }

    public function eliminarHorario(int $id_horario)
    {
        try {
            if (!$this->pdo) {
                return ["error" => "Error de conexión a la base de datos"];
            }

            $checkStmt = $this->pdo->prepare("SELECT COUNT(*) FROM horarios WHERE id = :id");
            $checkStmt->bindParam(':id', $id_horario, PDO::PARAM_INT);
            $checkStmt->execute();

            if ($checkStmt->fetchColumn() == 0) {
                return ["error" => "No se encontró el horario especificado en la base de datos"];
            }

            $stmt = $this->pdo->prepare("DELETE FROM horarios WHERE id = :id");
            $stmt->bindParam(':id', $id_horario, PDO::PARAM_INT);
            $stmt->execute();

            return ["success" => true, "message" => "Horario eliminado exitosamente"];
        } catch (PDOException $e) {
            return ["error" => "Error al eliminar el horario: " . $e->getMessage()];
        }
    }
}