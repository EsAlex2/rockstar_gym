<?php
require_once __DIR__ . '/models.php';
require_once __DIR__ . '/../core/conn.php';

/* =================================================================================
 * horariosModel.php
 * Modelo para la gestión de los horarios del gimnasio en el sistema de administración.
 * Proporciona métodos para obtener, crear y actualizar los bloques horarios de las clases.
 * Utiliza PDO para la interacción con la base de datos y maneja errores de conexión y ejecución.
 * Autor: Alex Madrid
 * Fecha: 12/06/2026
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

    /**
     * Registra un nuevo bloque horario en el sistema.
     * Valida la coherencia del rango y evita duplicados según la restricción uq_bloque_horario.
     */
    public function crearHorario(string $hora_inicio, string $hora_fin)
    {
        try {
            if (!$this->pdo) {
                return ["error" => "Error de conexión a la base de datos"];
            }

            // Validación lógica: La hora de inicio debe ser menor que la hora de fin
            if (strtotime($hora_inicio) >= strtotime($hora_fin)) {
                return ["error" => "La hora de inicio no puede ser mayor o igual a la hora de fin"];
            }

            // Evitar duplicados (Restricción UNIQUE uq_bloque_horario)
            $checkDuplicate = $this->pdo->prepare("SELECT COUNT(*) FROM administracion.horarios WHERE hora_inicio = :hora_inicio AND hora_fin = :hora_fin");
            $checkDuplicate->bindParam(':hora_inicio', $hora_inicio, PDO::PARAM_STR);
            $checkDuplicate->bindParam(':hora_fin', $hora_fin, PDO::PARAM_STR);
            $checkDuplicate->execute();

            if ($checkDuplicate->fetchColumn() > 0) {
                return ["error" => "Ya existe un bloque horario registrado de " . $hora_inicio . " a " . $hora_fin];
            }

            $query = $this->pdo->prepare("INSERT INTO administracion.horarios (hora_inicio, hora_fin) VALUES (:hora_inicio, :hora_fin)");
            $query->bindParam(':hora_inicio', $hora_inicio, PDO::PARAM_STR);
            $query->bindParam(':hora_fin', $hora_fin, PDO::PARAM_STR);
            $query->execute();

            return [
                "success" => true,
                "message" => "Horario registrado exitosamente",
                "data" => [
                    "hora_inicio" => $hora_inicio,
                    "hora_fin" => $hora_fin
                ]
            ];
        } catch (PDOException $e) {
            return ["error" => "Error al crear el horario: " . $e->getMessage()];
        }
    }

    /**
     * Lista todos los bloques horarios registrados.
     */
    public function listarHorarios()
    {
        try {
            if (!$this->pdo) {
                return ["error" => "Error de conexión a la base de datos"];
            }

            $sql = $this->pdo->prepare("SELECT id, hora_inicio, hora_fin, creado_en, actualizado_en FROM administracion.horarios ORDER BY hora_inicio ASC");
            $sql->execute();
            $resultado = $sql->fetchAll(PDO::FETCH_ASSOC);

            return empty($resultado) ? ["error" => "No hay horarios registrados"] : $resultado;
        } catch (PDOException $e) {
            return ["error" => "Error al obtener los horarios: " . $e->getMessage()];
        }
    }

    /**
     * Busca un horario específico por su ID.
     */
    public function buscarHorarioPorId(int $id_horario)
    {
        try {
            if (!$this->pdo) {
                return ["error" => "Error de conexión a la base de datos"];
            }

            $checkHorario = $this->pdo->prepare("SELECT id, hora_inicio, hora_fin, creado_en, actualizado_en FROM administracion.horarios WHERE id = :id");
            $checkHorario->bindParam(':id', $id_horario, PDO::PARAM_INT);
            $checkHorario->execute();

            $resultado = $checkHorario->fetch(PDO::FETCH_ASSOC);

            if (!$resultado) {
                return ["error" => "El horario solicitado no existe en la base de datos"];
            }

            return [
                "success" => true,
                "message" => "Horario encontrado exitosamente",
                "data" => [
                    $resultado
                ]
            ];
        } catch (PDOException $e) {
            return ["error" => "Error al buscar el horario: " . $e->getMessage()];
        }
    }

    /**
     * Actualiza un bloque horario existente.
     * Valida que no colisione con rangos idénticos de otros registros e incluye la marca de actualización.
     */
    public function actualizarHorario(int $id_horario, string $hora_inicio, string $hora_fin)
    {
        try {
            if (!$this->pdo) {
                return ["error" => "Error de conexión a la base de datos"];
            }

            $checkExist = $this->pdo->prepare("SELECT COUNT(*) FROM administracion.horarios WHERE id = :id");
            $checkExist->bindParam(':id', $id_horario, PDO::PARAM_INT);
            $checkExist->execute();

            if ($checkExist->fetchColumn() == 0) {
                return ["error" => "El horario que intenta actualizar no existe en la base de datos"];
            }


            if (strtotime($hora_inicio) >= strtotime($hora_fin)) {
                return ["error" => "La hora de inicio no puede ser mayor o igual a la hora de fin"];
            }

            $checkDuplicate = $this->pdo->prepare("SELECT COUNT(*) FROM administracion.horarios WHERE hora_inicio = :hora_inicio AND hora_fin = :hora_fin AND id != :id");
            $checkDuplicate->bindParam(':hora_inicio', $hora_inicio, PDO::PARAM_STR);
            $checkDuplicate->bindParam(':hora_fin', $hora_fin, PDO::PARAM_STR);
            $checkDuplicate->bindParam(':id', $id_horario, PDO::PARAM_INT);
            $checkDuplicate->execute();

            if ($checkDuplicate->fetchColumn() > 0) {
                return ["error" => "No se pudo actualizar. Ya existe otro bloque horario de " . $hora_inicio . " a " . $hora_fin];
            }

            $query = $this->pdo->prepare("UPDATE administracion.horarios 
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
            return ["error" => "Error al actualizar el horario: " . $e->getMessage()];
        }
    }
}
