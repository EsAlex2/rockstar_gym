<?php

require_once __DIR__ . '/models.php';

/**
 * Class HorarioEntrenamientoModel
 * Modelo para gestionar la asignación de bloques horarios y días a los entrenamientos.
 * Extiende de BaseModel.
 */
class HorarioEntrenamientoModel extends BaseModel
{
    protected string $table = 'entrenamiento_horarios';

    public function __construct(?PDO $pdo = null)
    {
        parent::__construct($pdo);
    }

    /**
     * Asigna un bloque horario y un día a una clase de entrenamiento.
     */
    public function asignarHorarioEntrenamiento(int $id_entrenamiento, int $id_horario, string $dia_semana): array
    {
        try {
            $diaFormateado = ucfirst(strtolower(trim($dia_semana)));

            if ($this->existsWhere(
                'entrenamiento_horarios',
                'id_entrenamiento = :id_e AND id_horario = :id_h AND dia_semana = :dia',
                [':id_e' => $id_entrenamiento, ':id_h' => $id_horario, ':dia' => $diaFormateado]
            )) {
                return ["error" => "Este bloque horario ya está asignado a este entrenamiento para el día {$diaFormateado}"];
            }

            $sql = "INSERT INTO entrenamiento_horarios (id_entrenamiento, id_horario, dia_semana) 
                    VALUES (:id_e, :id_h, :dia)";

            $this->executeQuery($sql, [
                ':id_e' => $id_entrenamiento,
                ':id_h' => $id_horario,
                ':dia'  => $diaFormateado
            ]);

            return [
                "success" => true,
                "message" => "Horario asignado exitosamente"
            ];
        } catch (PDOException $e) {
            return $this->formatError("asignar el horario", $e);
        }
    }

    /**
     * Desvincula un bloque horario asignado a un entrenamiento.
     */
    public function desasignarHorarioEntrenamiento(int $id_entrenamiento, int $id_horario, string $dia_semana): array
    {
        try {
            $diaFormateado = ucfirst(strtolower(trim($dia_semana)));

            if (!$this->existsWhere(
                'entrenamiento_horarios',
                'id_entrenamiento = :id_e AND id_horario = :id_h AND dia_semana = :dia',
                [':id_e' => $id_entrenamiento, ':id_h' => $id_horario, ':dia' => $diaFormateado]
            )) {
                return ["error" => "La asignación que intenta eliminar no existe en el sistema"];
            }

            $sql = "DELETE FROM entrenamiento_horarios WHERE id_entrenamiento = :id_e AND id_horario = :id_h AND dia_semana = :dia";
            $this->executeQuery($sql, [
                ':id_e' => $id_entrenamiento,
                ':id_h' => $id_horario,
                ':dia'  => $diaFormateado
            ]);

            return [
                "success" => true,
                "message" => "Horario desvinculado exitosamente"
            ];
        } catch (PDOException $e) {
            return $this->formatError("eliminar la asignación de horario", $e);
        }
    }

    /**
     * Lista todos los horarios asignados a entrenamientos.
     */
    public function listarEntrenamientosConHorarios(): array
    {
        try {
            $sql = "SELECT 
                        eh.id_entrenamiento,
                        e.nombre_entrenamiento,
                        eh.id_horario,
                        h.hora_inicio,
                        h.hora_fin,
                        eh.dia_semana
                    FROM entrenamiento_horarios eh
                    INNER JOIN entrenamiento e ON eh.id_entrenamiento = e.id
                    INNER JOIN horarios h ON eh.id_horario = h.id
                    ORDER BY eh.id_entrenamiento ASC, h.hora_inicio ASC";

            $resultado = $this->selectAll($sql);
            return empty($resultado) ? ["error" => "No hay horarios asignados a entrenamientos"] : $resultado;
        } catch (PDOException $e) {
            return $this->formatError("listar horarios de entrenamientos", $e);
        }
    }
}