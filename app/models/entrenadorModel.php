<?php
require_once __DIR__ . '/models.php';
require_once __DIR__ . '/../core/conn.php';

/* =================================================================================
 * entrenadorModel.php
 * Modelo para la gestión de entrenadores del gimnasio en el sistema de administración.
 * Autor: Alex Madrid
 * ==============================================================================
 */

class entrenadorModel extends Model
{
    protected $pdo;

    public function __construct($pdo)
    {
        parent::__construct($pdo);
        $this->pdo = $pdo;
    }

    public function listarEntrenadores()
    {
        try {
            if (!$this->pdo) {
                return ["error" => "Error de conexión a la base de datos"];
            }

            $stmt = $this->pdo->prepare("SELECT 
            a.id, 
            b.nombre_estatus AS Estatus, 
            c.cedula_identidad AS Documento_Identidad, 
            CONCAT(c.primer_nombre, ' ', c.primer_apellido) AS persona, 
            a.especialidad
        FROM entrenadores a
        INNER JOIN estatus b ON a.id_estatus = b.id
        INNER JOIN personas c ON a.id_persona = c.id");
            $stmt->execute();

            // Retornamos todos los registros encontrados
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            return ["error" => "Error al listar entrenadores: " . $e->getMessage()];
        }
    }


    public function crearEntrenadores(int $id_persona, string $especialidad)
    {
        try {
            if (!$this->pdo) {
                return ["error" => "Error de conexión a la base de datos"];
            }

            $checkPerson = $this->pdo->prepare("SELECT COUNT(*) FROM personas WHERE id = :id_persona");
            $checkPerson->bindParam(':id_persona', $id_persona, PDO::PARAM_INT);
            $checkPerson->execute();

            if ($checkPerson->fetchColumn() == 0) {
                return ["error" => "La persona no existe en nuestra base de datos"];
            }

            $especialidadFormateada = ucwords(strtolower(trim($especialidad)));

            $stmt = $this->pdo->prepare("INSERT INTO entrenadores (id_persona, id_estatus, especialidad) VALUES (:id_persona, 1, :especialidad)");
            $stmt->bindParam(':id_persona', $id_persona, PDO::PARAM_INT);
            $stmt->bindParam(':especialidad', $especialidadFormateada, PDO::PARAM_STR);
            $stmt->execute();

            return [
                "success" => true,
                "message" => "Entrenador registrado exitosamente."
            ];
        } catch (PDOException $e) {
            return ["error" => "Error al crear el entrenador: " . $e->getMessage()];
        }
    }

    public function buscarEntrenadorPorCedula(string $cedula_identidad)
    {
        try {
            if (!$this->pdo) {
                return ["error" => "Error de conexión a la base de datos"];
            }

            $buscarInfo = $this->pdo->prepare("SELECT 
                a.id, 
                b.nombre_estatus AS Estatus, 
                c.cedula_identidad AS Documento_Identidad, 
                c.primer_nombre AS Nombre, 
                c.primer_apellido AS Apellido, 
                a.especialidad
            FROM entrenadores a
            INNER JOIN estatus b ON a.id_estatus = b.id
            INNER JOIN personas c ON a.id_persona = c.id
            WHERE c.cedula_identidad = :cedula");

            $buscarInfo->bindParam(':cedula', $cedula_identidad, PDO::PARAM_STR);
            $buscarInfo->execute();

            $resultado = $buscarInfo->fetch(PDO::FETCH_ASSOC);

            if ($resultado === false) {
                return ["error" => "La persona existe, pero no está registrada como entrenador en el sistema."];
            }

            return [
                "estatus" => true,
                "message" => "Entrenador Encontrado Exitosamente!",
                "data" => [$resultado]
            ];

        } catch (PDOException $e) {
            return ["error" => "Error al buscar entrenador: " . $e->getMessage()];
        }
    }

    public function actualizarEntrenador(int $id_entrenador, string $especialidad, int $id_estatus)
    {
        try {
            if (!$this->pdo) {
                return ["error" => "Error de conexión a la base de datos"];
            }

            $checkStmt = $this->pdo->prepare("SELECT COUNT(*) FROM entrenadores WHERE id = :id");
            $checkStmt->bindParam(':id', $id_entrenador, PDO::PARAM_INT);
            $checkStmt->execute();

            if ($checkStmt->fetchColumn() == 0) {
                return ["error" => "No se encontró el entrenador en la base de datos"];
            }

            $especialidadFormateada = ucwords(strtolower(trim($especialidad)));

            $stmt = $this->pdo->prepare("UPDATE entrenadores SET especialidad = :especialidad, id_estatus = :id_estatus, actualizado_en = NOW() WHERE id = :id");
            $stmt->bindParam(':id', $id_entrenador, PDO::PARAM_INT);
            $stmt->bindParam(':especialidad', $especialidadFormateada, PDO::PARAM_STR);
            $stmt->bindParam(':id_estatus', $id_estatus, PDO::PARAM_INT);
            $stmt->execute();

            return ["success" => true, "message" => "Entrenador actualizado exitosamente"];
        } catch (PDOException $e) {
            return ["error" => "Error al actualizar el entrenador: " . $e->getMessage()];
        }
    }

    public function eliminarEntrenador(int $id_entrenador)
    {
        try {
            if (!$this->pdo) {
                return ["error" => "Error de conexión a la base de datos"];
            }

            $checkStmt = $this->pdo->prepare("SELECT COUNT(*) FROM entrenadores WHERE id = :id");
            $checkStmt->bindParam(':id', $id_entrenador, PDO::PARAM_INT);
            $checkStmt->execute();

            if ($checkStmt->fetchColumn() == 0) {
                return ["error" => "No se encontró el entrenador en la base de datos"];
            }

            $stmt = $this->pdo->prepare("DELETE FROM entrenadores WHERE id = :id");
            $stmt->bindParam(':id', $id_entrenador, PDO::PARAM_INT);
            $stmt->execute();

            return ["success" => true, "message" => "Entrenador eliminado exitosamente"];
        } catch (PDOException $e) {
            return ["error" => "Error al eliminar el entrenador: " . $e->getMessage()];
        }
    }
}