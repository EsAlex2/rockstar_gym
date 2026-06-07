<?php
require_once __DIR__ . '/models.php';
require_once __DIR__ . '/../core/conn.php';

/* =================================================================================
 * entrenadorModel.php
 * Modelo para la gestión de entrenadores del gimnasio en el sistema de administración.
 * Proporciona métodos para obtener, crear y actualizar entrenadores.
 * Utiliza PDO para la interacción con la base de datos y maneja errores de conexión y ejecución.
 * Autor: Alex Madrid
 * Fecha: 07/06/2026
 * ==============================================================================
 */

class entrendorModel extends Model
{
    protected $pdo;

    public function __construct($pdo)
    {
        parent::__construct($pdo);
        $this->pdo = $pdo;
    }

    public function crearEntrenadores(int $id_persona, string $especialidad)
    {
        try {
            if (!$this->pdo) {
                return ["error" => "Error de conexión a la base de datos"];
            }

            $checkPerson = $this->pdo->prepare("SELECT COUNT(*) FROM administracion.personas WHERE id = :id_persona");
            $checkPerson->bindParam(':id_persona', $id_persona, PDO::PARAM_INT);
            $checkPerson->execute();

            if ($checkPerson->fetchColumn() == 0) {
                return ["error" => "La persona no existe en el sistema"];
            }

            $query = $this->pdo->prepare("INSERT INTO administracion.entrenadores (id_estatus, id_persona, especialidad) VALUES (:id_estatus, :id_persona, :especialidad)");

            $estatus_default = 1;

            $query->bindParam(':id_estatus', $estatus_default, PDO::PARAM_INT);
            $query->bindParam(':id_persona', $id_persona, PDO::PARAM_INT);
            $query->bindParam(':especialidad', $especialidad);

            $query->execute();

            return [
                "success" => true,
                "message" => "Entrenador creado exitosamente",
                "data" => [
                    "id_persona" => $id_persona,
                    "especialidad" => $especialidad
                ]
            ];
        } catch (PDOException $e) {
            return ["error" => "Error al crear usuario: " . $e->getMessage()];
        }
    }

    public function listarEntrenadores(){
        
    }
}

