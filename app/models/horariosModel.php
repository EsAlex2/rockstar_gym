<?php

require_once __DIR__ . '/models.php';

/**
 * Class HorariosModel
 * Modelo para la gestión de bloques horarios del gimnasio.
 * Extiende de BaseModel.
 */
class HorariosModel extends BaseModel
{
    protected string $table = 'horarios';

    public function __construct(?PDO $pdo = null)
    {
        parent::__construct($pdo);
    }

    /**
     * Lista todos los bloques horarios ordenados cronológicamente.
     */
    public function listarHorarios(): array
    {
        try {
            $sql = "SELECT id, hora_inicio, hora_fin FROM horarios ORDER BY hora_inicio ASC";
            return $this->selectAll($sql);
        } catch (PDOException $e) {
            return $this->formatError("obtener horarios", $e);
        }
    }

    /**
     * Registra un nuevo bloque horario validando que hora inicio < hora fin.
     */
    public function crearHorario(string $hora_inicio, string $hora_fin): array
    {
        try {
            $inicio = trim($hora_inicio);
            $fin    = trim($hora_fin);

            if (strtotime($inicio) >= strtotime($fin)) {
                return ["error" => "La hora de inicio no puede ser mayor o igual a la hora de fin"];
            }

            if ($this->existsWhere('horarios', 'hora_inicio = :h_ini AND hora_fin = :h_fin', [':h_ini' => $inicio, ':h_fin' => $fin])) {
                return ["error" => "Ya existe ese bloque horario registrado"];
            }

            $sql = "INSERT INTO horarios (hora_inicio, hora_fin) VALUES (:h_ini, :h_fin)";
            $this->executeQuery($sql, [':h_ini' => $inicio, ':h_fin' => $fin]);

            return [
                "success" => true,
                "message" => "Horario creado exitosamente",
                "data"    => [
                    "id_horario"  => (int)$this->pdo->lastInsertId(),
                    "hora_inicio" => $inicio,
                    "hora_fin"    => $fin
                ]
            ];
        } catch (PDOException $e) {
            return $this->formatError("crear horario", $e);
        }
    }

    /**
     * Busca un bloque horario por su ID.
     */
    public function buscarHorarioPorId(int $id_horario): array
    {
        try {
            $sql = "SELECT id, hora_inicio, hora_fin, creado_en, actualizado_en FROM horarios WHERE id = :id LIMIT 1";
            $res = $this->selectOne($sql, [':id' => $id_horario]);

            if (!$res) {
                return ["error" => "No se encontró el horario"];
            }

            return [
                "success" => true,
                "data"    => [$res]
            ];
        } catch (PDOException $e) {
            return $this->formatError("buscar horario", $e);
        }
    }

    /**
     * Actualiza un bloque horario existente.
     */
    public function actualizarHorario(int $id_horario, string $hora_inicio, string $hora_fin): array
    {
        try {
            $inicio = trim($hora_inicio);
            $fin    = trim($hora_fin);

            if (strtotime($inicio) >= strtotime($fin)) {
                return ["error" => "La hora de inicio no puede ser mayor o igual a la hora de fin"];
            }

            if ($this->existsWhere('horarios', 'hora_inicio = :h_ini AND hora_fin = :h_fin AND id != :id', [':h_ini' => $inicio, ':h_fin' => $fin, ':id' => $id_horario])) {
                return ["error" => "No se pudo actualizar. Ya existe otro bloque horario de {$inicio} a {$fin}"];
            }

            $sql = "UPDATE horarios SET hora_inicio = :h_ini, hora_fin = :h_fin, actualizado_en = NOW() WHERE id = :id";
            $this->executeQuery($sql, [
                ':h_ini' => $inicio,
                ':h_fin' => $fin,
                ':id'    => $id_horario
            ]);

            return [
                "success" => true,
                "message" => "Horario actualizado exitosamente",
                "data"    => [
                    "id_horario"  => $id_horario,
                    "hora_inicio" => $inicio,
                    "hora_fin"    => $fin
                ]
            ];
        } catch (PDOException $e) {
            return $this->formatError("actualizar horario", $e);
        }
    }

    /**
     * Elimina un bloque horario.
     */
    public function eliminarHorario(int $id_horario): array
    {
        try {
            if (!$this->existsWhere('horarios', 'id = :id', [':id' => $id_horario])) {
                return ["error" => "No se encontró el horario especificado en la base de datos"];
            }

            $this->executeQuery("DELETE FROM horarios WHERE id = :id", [':id' => $id_horario]);
            return ["success" => true, "message" => "Horario eliminado exitosamente"];
        } catch (PDOException $e) {
            return $this->formatError("eliminar el horario", $e);
        }
    }
}