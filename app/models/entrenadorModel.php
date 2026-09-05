<?php

require_once __DIR__ . '/models.php';

/**
 * Class EntrenadorModel
 * Modelo para la gestión de entrenadores del gimnasio.
 * Extiende de BaseModel implementando herencia y consultas tipadas.
 */
class EntrenadorModel extends BaseModel
{
    protected string $table = 'entrenadores';

    public function __construct(?PDO $pdo = null)
    {
        parent::__construct($pdo);
    }

    /**
     * Lista todos los entrenadores con su información personal y estatus.
     * @return array
     */
    public function listarEntrenadores(): array
    {
        try {
            $sql = "SELECT 
                        a.id, 
                        b.nombre_estatus AS Estatus, 
                        c.cedula_identidad AS Documento_Identidad, 
                        CONCAT(c.primer_nombre, ' ', c.primer_apellido) AS persona, 
                        a.especialidad
                    FROM entrenadores a
                    INNER JOIN estatus b ON a.id_estatus = b.id
                    INNER JOIN personas c ON a.id_persona = c.id
                    ORDER BY a.id DESC";

            return $this->selectAll($sql);
        } catch (PDOException $e) {
            return $this->formatError("listar entrenadores", $e);
        }
    }

    /**
     * Registra un nuevo entrenador asignándole su especialidad.
     */
    public function crearEntrenadores(int $id_persona, string $especialidad): array
    {
        try {
            if (!$this->existsWhere('personas', 'id = :id', [':id' => $id_persona])) {
                return ["error" => "La persona no existe en nuestra base de datos"];
            }

            if ($this->existsWhere('entrenadores', 'id_persona = :id', [':id' => $id_persona])) {
                return ["error" => "La persona seleccionada ya está registrada como entrenador"];
            }

            $espFormateada = ucwords(strtolower(trim($especialidad)));
            $sql = "INSERT INTO entrenadores (id_persona, id_estatus, especialidad) VALUES (:id_persona, 1, :esp)";

            $this->executeQuery($sql, [
                ':id_persona' => $id_persona,
                ':esp'        => $espFormateada
            ]);

            return [
                "success" => true,
                "message" => "Entrenador registrado exitosamente.",
                "data"    => [
                    "id_entrenador" => (int)$this->pdo->lastInsertId(),
                    "especialidad"  => $espFormateada
                ]
            ];
        } catch (PDOException $e) {
            return $this->formatError("crear entrenador", $e);
        }
    }

    /**
     * Busca los datos de un entrenador por la cédula de su persona asociada.
     */
    public function buscarEntrenadorPorCedula(string $cedula_identidad): array
    {
        try {
            $sql = "SELECT 
                        a.id, 
                        b.nombre_estatus AS Estatus, 
                        c.cedula_identidad AS Documento_Identidad, 
                        c.primer_nombre AS Nombre, 
                        c.primer_apellido AS Apellido, 
                        a.especialidad
                    FROM entrenadores a
                    INNER JOIN estatus b ON a.id_estatus = b.id
                    INNER JOIN personas c ON a.id_persona = c.id
                    WHERE c.cedula_identidad = :cedula
                    LIMIT 1";

            $resultado = $this->selectOne($sql, [':cedula' => trim($cedula_identidad)]);

            if ($resultado === null) {
                return ["error" => "La persona existe, pero no está registrada como entrenador en el sistema."];
            }

            return [
                "estatus" => true,
                "message" => "Entrenador Encontrado Exitosamente!",
                "data"    => [$resultado]
            ];
        } catch (PDOException $e) {
            return $this->formatError("buscar entrenador", $e);
        }
    }

    /**
     * Actualiza la especialidad y el estado del entrenador.
     */
    public function actualizarEntrenador(int $id_entrenador, string $especialidad, int $id_estatus): array
    {
        try {
            if (!$this->existsWhere('entrenadores', 'id = :id', [':id' => $id_entrenador])) {
                return ["error" => "No se encontró el entrenador en la base de datos"];
            }

            $espFormateada = ucwords(strtolower(trim($especialidad)));
            $sql = "UPDATE entrenadores SET especialidad = :esp, id_estatus = :estatus, actualizado_en = NOW() WHERE id = :id";

            $this->executeQuery($sql, [
                ':esp'     => $espFormateada,
                ':estatus' => $id_estatus,
                ':id'      => $id_entrenador
            ]);

            return ["success" => true, "message" => "Entrenador actualizado exitosamente"];
        } catch (PDOException $e) {
            return $this->formatError("actualizar entrenador", $e);
        }
    }

    /**
     * Elimina el registro de un entrenador.
     */
    public function eliminarEntrenador(int $id_entrenador): array
    {
        try {
            if (!$this->existsWhere('entrenadores', 'id = :id', [':id' => $id_entrenador])) {
                return ["error" => "No se encontró el entrenador en la base de datos"];
            }

            $this->executeQuery("DELETE FROM entrenadores WHERE id = :id", [':id' => $id_entrenador]);
            return ["success" => true, "message" => "Entrenador eliminado exitosamente"];
        } catch (PDOException $e) {
            return $this->formatError("eliminar entrenador", $e);
        }
    }
}