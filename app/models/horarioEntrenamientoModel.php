<?php
require_once __DIR__ . '/models.php';
require_once __DIR__ . '/../core/conn.php';

/* =================================================================================
 * horarioEntrenamientoModel.php
 * Modelo para la gestión de la relación entre entrenamientos y sus bloques horarios.
 * Permite asignar, listar y desvincular los horarios de las clases de entrenamiento.
 * Autor: Alex Madrid
 * ==============================================================================
 */

class entrenamientoHorariosModel extends Model
{
    protected $pdo;

    public function __construct($pdo)
    {
        parent::__construct($pdo);
        $this->pdo = $pdo;
    }

    /**
     * Asigna un bloque horario y un día de la semana a un entrenamiento específico.
     */
    public function asignarHorarioEntrenamiento(int $id_entrenamiento, int $id_horario, string $dia_semana)
    {
        try {
            if (!$this->pdo) {
                return ["error" => "Error de conexión a la base de datos"];
            }

            $diaFormateado = ucfirst(strtolower(trim($dia_semana)));

            // Validación de existencia previa (sin prefijos de esquema)
            $checkExist = $this->pdo->prepare("SELECT COUNT(*) FROM entrenamiento_horarios 
                WHERE id_entrenamiento = :id_e AND id_horario = :id_h AND dia_semana = :dia");
            $checkExist->bindParam(':id_e', $id_entrenamiento, PDO::PARAM_INT);
            $checkExist->bindParam(':id_h', $id_horario, PDO::PARAM_INT);
            $checkExist->bindParam(':dia', $diaFormateado, PDO::PARAM_STR);
            $checkExist->execute();

            if ($checkExist->fetchColumn() > 0) {
                return ["error" => "Este bloque horario ya está asignado a este entrenamiento para el día " . $diaFormateado];
            }

            $query = $this->pdo->prepare("INSERT INTO entrenamiento_horarios (id_entrenamiento, id_horario, dia_semana) 
                VALUES (:id_e, :id_h, :dia)");
            $query->bindParam(':id_e', $id_entrenamiento, PDO::PARAM_INT);
            $query->bindParam(':id_h', $id_horario, PDO::PARAM_INT);
            $query->bindParam(':dia', $diaFormateado, PDO::PARAM_STR);
            $query->execute();

            return [
                "success" => true,
                "message" => "Horario asignado exitosamente"
            ];
        } catch (PDOException $e) {
            return ["error" => "Error al asignar el horario: " . $e->getMessage()];
        }
    }

    /**
     * Desvincula un bloque horario asignado a un entrenamiento.
     */
    public function desasignarHorarioEntrenamiento(int $id_entrenamiento, int $id_horario, string $dia_semana)
    {
        try {
            if (!$this->pdo) {
                return ["error" => "Error de conexión a la base de datos"];
            }

            $diaFormateado = ucfirst(strtolower(trim($dia_semana)));

            $checkExist = $this->pdo->prepare("SELECT COUNT(*) FROM entrenamiento_horarios 
                WHERE id_entrenamiento = :id_e AND id_horario = :id_h AND dia_semana = :dia");
            $checkExist->bindParam(':id_e', $id_entrenamiento, PDO::PARAM_INT);
            $checkExist->bindParam(':id_h', $id_horario, PDO::PARAM_INT);
            $checkExist->bindParam(':dia', $diaFormateado, PDO::PARAM_STR);
            $checkExist->execute();

            if ($checkExist->fetchColumn() == 0) {
                return ["error" => "La asignación que intenta eliminar no existe en el sistema"];
            }

            $query = $this->pdo->prepare("DELETE FROM entrenamiento_horarios 
                WHERE id_entrenamiento = :id_e AND id_horario = :id_h AND dia_semana = :dia");
            $query->bindParam(':id_e', $id_entrenamiento, PDO::PARAM_INT);
            $query->bindParam(':id_h', $id_horario, PDO::PARAM_INT);
            $query->bindParam(':dia', $diaFormateado, PDO::PARAM_STR);
            $query->execute();

            return [
                "success" => true,
                "message" => "Horario desvinculado exitosamente"
            ];
        } catch (PDOException $e) {
            return ["error" => "Error al eliminar la asignación: " . $e->getMessage()];
        }
    }

    /**
     * Listar todos los horarios asignados a los entrenamientos
     */
    public function listarEntrenamientosConHorarios()
    {
        try {
            if (!$this->pdo) {
                return ["error" => "Error de conexión a la base de datos"];
            }

            $stmt = $this->pdo->prepare("SELECT 
                eh.id_entrenamiento,
                e.nombre_entrenamiento,
                eh.id_horario,
                h.hora_inicio,
                h.hora_fin,
                eh.dia_semana
                FROM entrenamiento_horarios eh
                INNER JOIN entrenamiento e ON eh.id_entrenamiento = e.id
                INNER JOIN horarios h ON eh.id_horario = h.id");
            $stmt->execute();
            $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return empty($resultado) ? ["error" => "No hay horarios asignados a entrenamientos"] : $resultado;
        } catch (PDOException $e) {
            return ["error" => "Error al listar horarios de entrenamientos: " . $e->getMessage()];
        }
    }
}