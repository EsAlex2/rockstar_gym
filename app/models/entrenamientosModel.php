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

            $stmt = $this->pdo->prepare("SELECT 
                e.id,
                e.nombre_entrenamiento,
                e.descripcion,
                e.id_entrenador,
                e.id_sede,
                e.id_estatus,
                p.primer_nombre,
                p.primer_apellido,
                s.sede AS Sede,
                es.nombre_estatus AS Estatus
                FROM entrenamiento e
                INNER JOIN entrenadores ent ON e.id_entrenador = ent.id
                INNER JOIN personas p ON ent.id_persona = p.id
                INNER JOIN sedes s ON e.id_sede = s.id
                INNER JOIN estatus es ON e.id_estatus = es.id
                ORDER BY e.id DESC");
            $stmt->execute();

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

    public function actualizarEntrenamiento(int $id, int $id_entrenador, int $id_sede, string $nombre, string $descripcion)
    {
        try {
            if (!$this->pdo) {
                return ["error" => "Error de conexion en la base de datos"];
            }

            // Validar si el entrenamiento existe
            $checkEntrenamiento = $this->pdo->prepare("SELECT COUNT(*) FROM entrenamiento WHERE id = :id");
            $checkEntrenamiento->bindParam(":id", $id, PDO::PARAM_INT);
            $checkEntrenamiento->execute();

            if ($checkEntrenamiento->fetchColumn() == 0) {
                return ["error" => "No existe registro del entrenamiento que intenta actualizar"];
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
                SET id_entrenador = :id_entrenador, id_sede = :id_sede, nombre_entrenamiento = :nombre, descripcion = :descripcion, actualizado_en = NOW()
                WHERE id = :id");

            $update->bindParam(":id", $id, PDO::PARAM_INT);
            $update->bindParam(":id_entrenador", $id_entrenador, PDO::PARAM_INT);
            $update->bindParam(":id_sede", $id_sede, PDO::PARAM_INT);
            $update->bindParam(":nombre", $nombre, PDO::PARAM_STR);
            $update->bindParam(":descripcion", $descripcion, PDO::PARAM_STR);
            $update->execute();

            return [
                "success" => true,
                "message" => "Entrenamiento actualizado exitosamente",
                "data" => [
                    "id" => $id,
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

    public function eliminarEntrenamiento(int $id)
    {
        try {
            if (!$this->pdo) {
                return ["error" => "Error de conexión a la base de datos"];
            }

            $checkStmt = $this->pdo->prepare("SELECT COUNT(*) FROM entrenamiento WHERE id = :id");
            $checkStmt->bindParam(':id', $id, PDO::PARAM_INT);
            $checkStmt->execute();

            if ($checkStmt->fetchColumn() == 0) {
                return ["error" => "No se encontró el entrenamiento en la base de datos"];
            }

            $stmt = $this->pdo->prepare("DELETE FROM entrenamiento WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            return ["success" => true, "message" => "Entrenamiento eliminado exitosamente"];
        } catch (PDOException $e) {
            return ["error" => "Error al eliminar el entrenamiento: " . $e->getMessage()];
        }
    }

    public function inscribirClienteEntrenamiento(int $id_cliente, int $id_entrenamiento)
    {
        try {
            if (!$this->pdo) {
                return ["error" => "Error de conexión a la base de datos"];
            }

            // 1. Validar que el cliente esté activo
            $stmtCli = $this->pdo->prepare("SELECT id_estatus FROM clientes WHERE id = :id_cliente");
            $stmtCli->execute([':id_cliente' => $id_cliente]);
            $cliente_estatus = $stmtCli->fetchColumn();

            if ($cliente_estatus != 1) {
                return ["error" => "El cliente no cuenta con una membresía activa y vigente en el sistema."];
            }

            // 2. Insertar
            $stmt = $this->pdo->prepare("INSERT INTO cliente_entrenamientos (id_cliente, id_entrenamiento) VALUES (:id_cliente, :id_entrenamiento)");
            $stmt->execute([
                ':id_cliente' => $id_cliente,
                ':id_entrenamiento' => $id_entrenamiento
            ]);

            return ["success" => true, "message" => "Inscripción al entrenamiento realizada exitosamente."];
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                return ["error" => "El cliente ya se encuentra inscrito en este entrenamiento."];
            }
            return ["error" => "Error al inscribir al cliente: " . $e->getMessage()];
        }
    }

    public function desinscribirClienteEntrenamiento(int $id_cliente, int $id_entrenamiento)
    {
        try {
            if (!$this->pdo) {
                return ["error" => "Error de conexión a la base de datos"];
            }

            $stmt = $this->pdo->prepare("DELETE FROM cliente_entrenamientos WHERE id_cliente = :id_cliente AND id_entrenamiento = :id_entrenamiento");
            $stmt->execute([
                ':id_cliente' => $id_cliente,
                ':id_entrenamiento' => $id_entrenamiento
            ]);

            return ["success" => true, "message" => "Desinscripción completada exitosamente."];
        } catch (PDOException $e) {
            return ["error" => "Error al desinscribir al cliente: " . $e->getMessage()];
        }
    }

    public function listarEntrenamientosDeCliente(int $id_cliente)
    {
        try {
            if (!$this->pdo) {
                return ["error" => "Error de conexión a la base de datos"];
            }

            $stmt = $this->pdo->prepare("SELECT 
                ce.id_entrenamiento,
                e.nombre_entrenamiento,
                e.descripcion,
                CONCAT(p.primer_nombre, ' ', p.primer_apellido) AS entrenador_nombre,
                ent.especialidad,
                s.sede AS Sede
                FROM cliente_entrenamientos ce
                INNER JOIN entrenamiento e ON ce.id_entrenamiento = e.id
                INNER JOIN entrenadores ent ON e.id_entrenador = ent.id
                INNER JOIN personas p ON ent.id_persona = p.id
                INNER JOIN sedes s ON e.id_sede = s.id
                WHERE ce.id_cliente = :id_cliente");
            $stmt->execute([':id_cliente' => $id_cliente]);
            $entrenamientos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Cargar los horarios asignados para cada entrenamiento
            foreach ($entrenamientos as &$ent) {
                $stmtHorarios = $this->pdo->prepare("SELECT 
                    eh.dia_semana,
                    h.hora_inicio,
                    h.hora_fin
                    FROM entrenamiento_horarios eh
                    INNER JOIN horarios h ON eh.id_horario = h.id
                    WHERE eh.id_entrenamiento = :id_ent");
                $stmtHorarios->execute([':id_ent' => $ent['id_entrenamiento']]);
                $ent['horarios'] = $stmtHorarios->fetchAll(PDO::FETCH_ASSOC);
            }

            return $entrenamientos;
        } catch (PDOException $e) {
            return ["error" => "Error al obtener entrenamientos del cliente: " . $e->getMessage()];
        }
    }

    public function listarEntrenamientosDisponiblesParaCliente(int $id_cliente)
    {
        try {
            if (!$this->pdo) {
                return ["error" => "Error de conexión a la base de datos"];
            }

            $stmt = $this->pdo->prepare("SELECT 
                e.id AS id_entrenamiento,
                e.nombre_entrenamiento,
                e.descripcion,
                CONCAT(p.primer_nombre, ' ', p.primer_apellido) AS entrenador_nombre,
                ent.especialidad,
                s.sede AS Sede
                FROM entrenamiento e
                INNER JOIN entrenadores ent ON e.id_entrenador = ent.id
                INNER JOIN personas p ON ent.id_persona = p.id
                INNER JOIN sedes s ON e.id_sede = s.id
                WHERE e.id_estatus = 1
                  AND e.id NOT IN (SELECT id_entrenamiento FROM cliente_entrenamientos WHERE id_cliente = :id_cliente)");
            $stmt->execute([':id_cliente' => $id_cliente]);
            $entrenamientos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Cargar los horarios asignados para cada entrenamiento
            foreach ($entrenamientos as &$ent) {
                $stmtHorarios = $this->pdo->prepare("SELECT 
                    eh.dia_semana,
                    h.hora_inicio,
                    h.hora_fin
                    FROM entrenamiento_horarios eh
                    INNER JOIN horarios h ON eh.id_horario = h.id
                    WHERE eh.id_entrenamiento = :id_ent");
                $stmtHorarios->execute([':id_ent' => $ent['id_entrenamiento']]);
                $ent['horarios'] = $stmtHorarios->fetchAll(PDO::FETCH_ASSOC);
            }

            return $entrenamientos;
        } catch (PDOException $e) {
            return ["error" => "Error al obtener entrenamientos disponibles: " . $e->getMessage()];
        }
    }

    public function listarTodosClienteEntrenamientos()
    {
        try {
            if (!$this->pdo) {
                return ["error" => "Error de conexión a la base de datos"];
            }

            $stmt = $this->pdo->prepare("SELECT 
                ce.id,
                ce.id_cliente,
                CONCAT(pc.primer_nombre, ' ', pc.primer_apellido) AS cliente_nombre,
                ce.id_entrenamiento,
                e.nombre_entrenamiento,
                ce.creado_en
                FROM cliente_entrenamientos ce
                INNER JOIN clientes c ON ce.id_cliente = c.id
                INNER JOIN personas pc ON c.id_persona = pc.id
                INNER JOIN entrenamiento e ON ce.id_entrenamiento = e.id
                ORDER BY ce.creado_en DESC");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ["error" => "Error al obtener todas las asignaciones: " . $e->getMessage()];
        }
    }
}