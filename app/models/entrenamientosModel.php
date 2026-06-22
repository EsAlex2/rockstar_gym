<?php
require_once __DIR__ . '/models.php';
require_once __DIR__ . '/../core/conn.php';

class EntrenamientosModel extends Model
{
    protected $pdo;

    public function __construct($pdo)
    {
        parent::__construct($pdo);
        $this->pdo = $pdo;
    }

    public function listarEntrenamientos()
    {
        try {
            if (!$this->pdo) {
                return ["error" => "Error de conexion en la base de datos"];
            }

            // Preparamos la consulta para la tabla 'entrenamiento'
            $stmt = $this->pdo->prepare("SELECT * FROM entrenamiento");
            $stmt->execute();

            // Retornamos todos los entrenamientos encontrados
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            return ["error" => "Error al listar los entrenamientos: " . $e->getMessage()];
        }
    }

    public function crearEntrenamientos(int $id_entrenador, int $id_sede, string $nombre, string $descripcion)
    {
        try {
            if (!$this->pdo) {
                return ["error" => "Error de conexion en la base de datos"];
            }

            $checkEntrenador = $this->pdo->prepare("SELECT COUNT(*) FROM entrenadores WHERE id = :id_entrenador");
            $checkEntrenador->bindParam(":id_entrenador", $id_entrenador, PDO::PARAM_INT);
            $checkEntrenador->execute();

            if ($checkEntrenador->fetchColumn() == 0) {
                return ["error" => "No existe registro del entrenador que intenta buscar en nuestra base de datos"];
            }

            $checkSede = $this->pdo->prepare("SELECT COUNT(*) FROM sedes WHERE id = :id_sede");
            $checkSede->bindParam(":id_sede", $id_sede, PDO::PARAM_INT);
            $checkSede->execute();

            if ($checkSede->fetchColumn() == 0) {
                return ["error" => "No existe registro de la sede que intenta buscar en nuestra base de datos"];
            }

            $stmt = $this->pdo->prepare("INSERT INTO entrenamiento (id_entrenador, id_sede, nombre_entrenamiento, descripcion, id_estatus) 
                                        VALUES (:id_entrenador, :id_sede, :nombre, :descripcion, 1)");
            $stmt->bindParam(":id_entrenador", $id_entrenador, PDO::PARAM_INT);
            $stmt->bindParam(":id_sede", $id_sede, PDO::PARAM_INT);
            $stmt->bindParam(":nombre", $nombre, PDO::PARAM_STR);
            $stmt->bindParam(":descripcion", $descripcion, PDO::PARAM_STR);
            $stmt->execute();

            return [
                "success" => true,
                "message" => "Entrenamiento creado exitosamente"
            ];
        } catch (PDOException $e) {
            return ["error" => "Error al crear entrenamiento: " . $e->getMessage()];
        }
    }

    public function actualizarEntrenamiento(int $id_entrenador, int $id_sede, string $nombre, string $descripcion)
    {
        try {
            if (!$this->pdo) {
                return ["error" => "Error de conexion en la base de datos"];
            }

            $checkEntrenador = $this->pdo->prepare("SELECT COUNT(*) FROM entrenadores WHERE id = :id_entrenador");
            $checkEntrenador->bindParam(":id_entrenador", $id_entrenador, PDO::PARAM_INT);
            $checkEntrenador->execute();

            if ($checkEntrenador->fetchColumn() == 0) {
                return ["error" => "No existe registro del entrenador que intenta buscar en nuestra base de datos"];
            }

            $checkSede = $this->pdo->prepare("SELECT COUNT(*) FROM sedes WHERE id = :id_sede");
            $checkSede->bindParam(":id_sede", $id_sede, PDO::PARAM_INT);
            $checkSede->execute();

            if ($checkSede->fetchColumn() == 0) {
                return ["error" => "No existe registro de la sede que intenta buscar en nuestra base de datos"];
            }

            // En MySQL 'NOW()' funciona perfectamente para registrar la estampa de tiempo actual
            $update = $this->pdo->prepare("UPDATE entrenamiento 
                SET id_entrenador = :id_entrenador, id_sede = :id_sede, nombre_entrenamiento = :nombre, descripcion = :descripcion, actualizado_en = NOW()");

            $update->bindParam(":id_entrenador", $id_entrenador, PDO::PARAM_INT);
            $update->bindParam(":id_sede", $id_sede, PDO::PARAM_INT);
            $update->bindParam(":nombre", $nombre, PDO::PARAM_STR);
            $update->bindParam(":descripcion", $descripcion, PDO::PARAM_STR);
            $update->execute();

            return [
                "success" => true,
                "message" => "Entrenamiento actualizado exitosamente",
                "data" => [
                    "id_entrenador" => $id_entrenador,
                    "id_sede" => $id_sede,
                    "nombre_entrenamiento" => $nombre,
                    "descripcion" => $descripcion
                ]
            ];
        } catch (PDOException $e) {
            return ["error" => "Error al actualizar el entrenamiento: " . $e->getMessage()];
        }
    }
}