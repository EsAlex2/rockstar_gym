<?php
require_once __DIR__ . '/models.php';
require_once __DIR__ . '/../core/conn.php';

/* =================================================================================
 * entrenamientoHorariosModel.php
 * Modelo para la gestión de la relación entre entrenamientos y sus bloques horarios.
 * Permite asignar, listar y desvincular los horarios de las clases de entrenamiento.
 * Utiliza PDO para la interacción con la base de datos y maneja errores de conexión.
 * Autor: Alex Madrid
 * Fecha: 12/06/2026
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
     * Capitaliza el día de la semana para cumplir con el CHECK de la base de datos.
     */
    public function asignarHorarioEntrenamiento(int $id_entrenamiento, int $id_horario, string $dia_semana)
    {
        try {
            if (!$this->pdo) {
                return ["error" => "Error de conexión a la base de datos"];
            }

            $diaFormateado = ucfirst(strtolower(trim($dia_semana)));

            $diasValidos = ['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado', 'Domingo'];
            if (!in_array($diaFormateado, $diasValidos)) {
                return ["error" => "El día de la semana '" . $dia_semana . "' no es válido. Recuerde no usar tildes."];
            }

            $checkEntrenamiento = $this->pdo->prepare("SELECT COUNT(*) FROM administracion.entrenamiento WHERE id = :id_e");
            $checkEntrenamiento->bindParam(':id_e', $id_entrenamiento, PDO::PARAM_INT);
            $checkEntrenamiento->execute();
            if ($checkEntrenamiento->fetchColumn() == 0) {
                return ["error" => "El entrenamiento especificado no existe"];
            }

            $checkHorario = $this->pdo->prepare("SELECT COUNT(*) FROM administracion.horarios WHERE id = :id_h");
            $checkHorario->bindParam(':id_h', $id_horario, PDO::PARAM_INT);
            $checkHorario->execute();
            if ($checkHorario->fetchColumn() == 0) {
                return ["error" => "El bloque de horario especificado no existe"];
            }

            $checkDuplicate = $this->pdo->prepare("SELECT COUNT(*) FROM administracion.entrenamiento_horarios 
                WHERE id_entrenamiento = :id_e AND id_horario = :id_h AND dia_semana = :dia");
            $checkDuplicate->bindParam(':id_e', $id_entrenamiento, PDO::PARAM_INT);
            $checkDuplicate->bindParam(':id_h', $id_horario, PDO::PARAM_INT);
            $checkDuplicate->bindParam(':dia', $diaFormateado, PDO::PARAM_STR);
            $checkDuplicate->execute();

            if ($checkDuplicate->fetchColumn() > 0) {
                return ["error" => "Este entrenamiento ya tiene asignado ese horario para el día " . $diaFormateado];
            }

            $query = $this->pdo->prepare("INSERT INTO administracion.entrenamiento_horarios (id_entrenamiento, id_horario, dia_semana) 
                VALUES (:id_e, :id_h, :dia)");
            $query->bindParam(':id_e', $id_entrenamiento, PDO::PARAM_INT);
            $query->bindParam(':id_h', $id_horario, PDO::PARAM_INT);
            $query->bindParam(':dia', $diaFormateado, PDO::PARAM_STR);
            $query->execute();

            return [
                "success" => true,
                "message" => "Horario asignado al entrenamiento exitosamente",
                "data" => [
                    "id_entrenamiento" => $id_entrenamiento,
                    "id_horario" => $id_horario,
                    "dia_semana" => $diaFormateado
                ]
            ];
        } catch (PDOException $e) {
            return ["error" => "Error al asignar el horario: " . $e->getMessage()];
        }
    }

    /**
     * Lista toda la parrilla de entrenamientos con sus respectivos horarios y días.
     * Utiliza INNER JOINs para resolver los nombres y bloques legibles.
     */
    public function listarEntrenamientosConHorarios()
    {
        try {
            if (!$this->pdo) {
                return ["error" => "Error de conexión a la base de datos"];
            }

            $sql = $this->pdo->prepare("SELECT 
                    eh.id_entrenamiento,
                    e.nombre_entrenamiento AS entrenamiento,
                    h.hora_inicio,
                    h.hora_fin,
                    eh.dia_semana,
                    eh.creado_en
                FROM administracion.entrenamiento_horarios eh
                INNER JOIN administracion.entrenamiento e ON eh.id_entrenamiento = e.id
                INNER JOIN administracion.horarios h ON eh.id_horario = h.id
                ORDER BY 
                    CASE eh.dia_semana
                        WHEN 'Lunes' THEN 1
                        WHEN 'Martes' THEN 2
                        WHEN 'Miercoles' THEN 3
                        WHEN 'Jueves' THEN 4
                        WHEN 'Viernes' THEN 5
                        WHEN 'Sabado' THEN 6
                        WHEN 'Domingo' THEN 7
                    END, h.hora_inicio ASC");
            
            $sql->execute();
            $resultado = $sql->fetchAll(PDO::FETCH_ASSOC);

            return empty($resultado) ? ["error" => "No hay horarios asignados a ningún entrenamiento"] : $resultado;
        } catch (PDOException $e) {
            return ["error" => "Error al obtener el cronograma de entrenamientos: " . $e->getMessage()];
        }
    }

    /**
     * Elimina una asignación específica utilizando su clave primaria compuesta.
     */
    public function eliminarHorarioEntrenamiento(int $id_entrenamiento, int $id_horario, string $dia_semana)
    {
        try {
            if (!$this->pdo) {
                return ["error" => "Error de conexión a la base de datos"];
            }

            $diaFormateado = ucfirst(strtolower(trim($dia_semana)));

            $checkExist = $this->pdo->prepare("SELECT COUNT(*) FROM administracion.entrenamiento_horarios 
                WHERE id_entrenamiento = :id_e AND id_horario = :id_h AND dia_semana = :dia");
            $checkExist->bindParam(':id_e', $id_entrenamiento, PDO::PARAM_INT);
            $checkExist->bindParam(':id_h', $id_horario, PDO::PARAM_INT);
            $checkExist->bindParam(':dia', $diaFormateado, PDO::PARAM_STR);
            $checkExist->execute();

            if ($checkExist->fetchColumn() == 0) {
                return ["error" => "La asignación que intenta eliminar no existe en el sistema"];
            }

            $query = $this->pdo->prepare("DELETE FROM administracion.entrenamiento_horarios 
                WHERE id_entrenamiento = :id_e AND id_horario = :id_h AND dia_semana = :dia");
            $query->bindParam(':id_e', $id_entrenamiento, PDO::PARAM_INT);
            $query->bindParam(':id_h', $id_horario, PDO::PARAM_INT);
            $query->bindParam(':dia', $diaFormateado, PDO::PARAM_STR);
            $query->execute();

            return [
                "success" => true,
                "message" => "Horario desvinculado del entrenamiento con éxito"
            ];
        } catch (PDOException $e) {
            return ["error" => "Error al desvincular el horario: " . $e->getMessage()];
        }
    }
}
