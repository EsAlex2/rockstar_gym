<?php

require_once __DIR__ . '/models.php';

/**
 * Class EntrenamientosModel
 * Modelo para la gestión de clases y entrenamientos, así como inscripciones de clientes.
 * Extiende de BaseModel.
 */
class EntrenamientosModel extends BaseModel
{
    protected string $table = 'entrenamiento';

    public function __construct(?PDO $pdo = null)
    {
        parent::__construct($pdo);
    }

    /**
     * Lista todos los entrenamientos registrados con la información de entrenadores y sedes.
     */
    public function listarEntrenamientos(): array
    {
        try {
            $sql = "SELECT 
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
                    ORDER BY e.id DESC";

            return $this->selectAll($sql);
        } catch (PDOException $e) {
            return $this->formatError("listar los entrenamientos", $e);
        }
    }

    /**
     * Busca entrenamientos por coincidencia de nombre.
     */
    public function listarEntrenamientosPorNombre(string $nombre): array
    {
        try {
            $sql = "SELECT 
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
                    WHERE e.nombre_entrenamiento LIKE :nombre
                    ORDER BY e.id DESC";

            return $this->selectAll($sql, [':nombre' => '%' . trim($nombre) . '%']);
        } catch (PDOException $e) {
            return $this->formatError("buscar entrenamientos por nombre", $e);
        }
    }

    /**
     * Registra un nuevo entrenamiento.
     */
    public function crearEntrenamientos(int $id_entrenador, int $id_sede, string $nombre, string $descripcion): array
    {
        try {
            if (!$this->existsWhere('entrenadores', 'id = :id', [':id' => $id_entrenador])) {
                return ["error" => "No existe registro del entrenador que intenta buscar en nuestra base de datos"];
            }

            if (!$this->existsWhere('sedes', 'id = :id', [':id' => $id_sede])) {
                return ["error" => "No existe registro de la sede que intenta buscar en nuestra base de datos"];
            }

            $sql = "INSERT INTO entrenamiento (id_entrenador, id_sede, nombre_entrenamiento, descripcion, id_estatus) 
                    VALUES (:id_entrenador, :id_sede, :nombre, :descripcion, 1)";

            $this->executeQuery($sql, [
                ':id_entrenador' => $id_entrenador,
                ':id_sede'       => $id_sede,
                ':nombre'        => trim($nombre),
                ':descripcion'   => trim($descripcion)
            ]);

            return [
                "success" => true,
                "message" => "Entrenamiento creado exitosamente",
                "data"    => [
                    "id_entrenamiento" => (int)$this->pdo->lastInsertId()
                ]
            ];
        } catch (PDOException $e) {
            return $this->formatError("crear entrenamiento", $e);
        }
    }

    /**
     * Actualiza la información de un entrenamiento existente.
     */
    public function actualizarEntrenamiento(int $id, int $id_entrenador, int $id_sede, string $nombre, string $descripcion): array
    {
        try {
            if (!$this->existsWhere('entrenamiento', 'id = :id', [':id' => $id])) {
                return ["error" => "No existe registro del entrenamiento que intenta actualizar"];
            }

            if (!$this->existsWhere('entrenadores', 'id = :id', [':id' => $id_entrenador])) {
                return ["error" => "No existe registro del entrenador seleccionado"];
            }

            if (!$this->existsWhere('sedes', 'id = :id', [':id' => $id_sede])) {
                return ["error" => "No existe registro de la sede seleccionada"];
            }

            $sql = "UPDATE entrenamiento 
                    SET id_entrenador = :id_entrenador, id_sede = :id_sede, nombre_entrenamiento = :nombre, descripcion = :descripcion, actualizado_en = NOW()
                    WHERE id = :id";

            $this->executeQuery($sql, [
                ':id_entrenador' => $id_entrenador,
                ':id_sede'       => $id_sede,
                ':nombre'        => trim($nombre),
                ':descripcion'   => trim($descripcion),
                ':id'            => $id
            ]);

            return [
                "success" => true,
                "message" => "Entrenamiento actualizado exitosamente",
                "data"    => [
                    "id"                   => $id,
                    "id_entrenador"        => $id_entrenador,
                    "id_sede"              => $id_sede,
                    "nombre_entrenamiento" => trim($nombre),
                    "descripcion"          => trim($descripcion)
                ]
            ];
        } catch (PDOException $e) {
            return $this->formatError("actualizar el entrenamiento", $e);
        }
    }

    /**
     * Elimina un entrenamiento por su ID.
     */
    public function eliminarEntrenamiento(int $id): array
    {
        try {
            if (!$this->existsWhere('entrenamiento', 'id = :id', [':id' => $id])) {
                return ["error" => "No se encontró el entrenamiento en la base de datos"];
            }

            $this->executeQuery("DELETE FROM entrenamiento WHERE id = :id", [':id' => $id]);
            return ["success" => true, "message" => "Entrenamiento eliminado exitosamente"];
        } catch (PDOException $e) {
            return $this->formatError("eliminar el entrenamiento", $e);
        }
    }

    /**
     * Inscribe a un cliente activo en un entrenamiento.
     */
    public function inscribirClienteEntrenamiento(int $id_cliente, int $id_entrenamiento): array
    {
        try {
            $cliente = $this->selectOne("SELECT id_estatus FROM clientes WHERE id = :id", [':id' => $id_cliente]);
            if (!$cliente || (int)$cliente['id_estatus'] !== 1) {
                return ["error" => "El cliente no cuenta con una membresía activa y vigente en el sistema."];
            }

            $sql = "INSERT INTO cliente_entrenamientos (id_cliente, id_entrenamiento) VALUES (:id_cliente, :id_entrenamiento)";
            $this->executeQuery($sql, [
                ':id_cliente'       => $id_cliente,
                ':id_entrenamiento' => $id_entrenamiento
            ]);

            return ["success" => true, "message" => "Inscripción al entrenamiento realizada exitosamente."];
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                return ["error" => "El cliente ya se encuentra inscrito en este entrenamiento."];
            }
            return $this->formatError("inscribir al cliente", $e);
        }
    }

    /**
     * Desinscribe a un cliente de un entrenamiento.
     */
    public function desinscribirClienteEntrenamiento(int $id_cliente, int $id_entrenamiento): array
    {
        try {
            $this->executeQuery(
                "DELETE FROM cliente_entrenamientos WHERE id_cliente = :cli AND id_entrenamiento = :ent",
                [':cli' => $id_cliente, ':ent' => $id_entrenamiento]
            );

            return ["success" => true, "message" => "Desinscripción completada exitosamente."];
        } catch (PDOException $e) {
            return $this->formatError("desinscribir al cliente", $e);
        }
    }

    /**
     * Lista los entrenamientos a los que un cliente está inscrito con sus horarios vinculados.
     */
    public function listarEntrenamientosDeCliente(int $id_cliente): array
    {
        try {
            $sql = "SELECT 
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
                    WHERE ce.id_cliente = :id_cliente";

            $entrenamientos = $this->selectAll($sql, [':id_cliente' => $id_cliente]);

            foreach ($entrenamientos as &$ent) {
                $sqlHorarios = "SELECT eh.dia_semana, h.hora_inicio, h.hora_fin 
                                FROM entrenamiento_horarios eh
                                INNER JOIN horarios h ON eh.id_horario = h.id
                                WHERE eh.id_entrenamiento = :id";
                $ent['horarios'] = $this->selectAll($sqlHorarios, [':id' => $ent['id_entrenamiento']]);
            }

            return $entrenamientos;
        } catch (PDOException $e) {
            return $this->formatError("listar entrenamientos del cliente", $e);
        }
    }

    /**
     * Lista los entrenamientos en los cuales el cliente aún NO está inscrito.
     */
    public function listarEntrenamientosDisponiblesParaCliente(int $id_cliente): array
    {
        try {
            $sql = "SELECT 
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
                    WHERE e.id NOT IN (
                        SELECT ce.id_entrenamiento FROM cliente_entrenamientos ce WHERE ce.id_cliente = :id_cliente
                    )
                    ORDER BY e.nombre_entrenamiento ASC";

            $entrenamientos = $this->selectAll($sql, [':id_cliente' => $id_cliente]);

            foreach ($entrenamientos as &$ent) {
                $sqlHorarios = "SELECT eh.dia_semana, h.hora_inicio, h.hora_fin 
                                FROM entrenamiento_horarios eh
                                INNER JOIN horarios h ON eh.id_horario = h.id
                                WHERE eh.id_entrenamiento = :id";
                $ent['horarios'] = $this->selectAll($sqlHorarios, [':id' => $ent['id_entrenamiento']]);
            }

            return $entrenamientos;
        } catch (PDOException $e) {
            return $this->formatError("listar entrenamientos disponibles", $e);
        }
    }

    /**
     * Obtiene todas las inscripciones del sistema con datos detallados de cliente y clase.
     */
    public function listarTodosClienteEntrenamientos(): array
    {
        try {
            $sql = "SELECT 
                        ce.id,
                        ce.id_cliente,
                        ce.id_entrenamiento,
                        ce.creado_en,
                        CONCAT(p.primer_nombre, ' ', p.primer_apellido) AS cliente_nombre,
                        p.cedula_identidad AS cliente_cedula,
                        e.nombre_entrenamiento,
                        CONCAT(pe.primer_nombre, ' ', pe.primer_apellido) AS entrenador_nombre,
                        s.sede AS Sede
                    FROM cliente_entrenamientos ce
                    INNER JOIN clientes c ON ce.id_cliente = c.id
                    INNER JOIN personas p ON c.id_persona = p.id
                    INNER JOIN entrenamiento e ON ce.id_entrenamiento = e.id
                    INNER JOIN entrenadores ent ON e.id_entrenador = ent.id
                    INNER JOIN personas pe ON ent.id_persona = pe.id
                    INNER JOIN sedes s ON e.id_sede = s.id
                    ORDER BY ce.creado_en DESC";

            return $this->selectAll($sql);
        } catch (PDOException $e) {
            return $this->formatError("listar inscripciones generales", $e);
        }
    }
}