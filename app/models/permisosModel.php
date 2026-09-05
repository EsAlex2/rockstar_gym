<?php

require_once __DIR__ . '/models.php';

/**
 * Class PermisosModel
 * Modelo para la gestión del catálogo de permisos y privilegios del sistema.
 * Extiende de BaseModel.
 */
class PermisosModel extends BaseModel
{
    protected string $table = 'permisos';

    public function __construct(?PDO $pdo = null)
    {
        parent::__construct($pdo);
    }

    /**
     * Obtiene la lista completa de permisos registrados.
     */
    public function obtenerPermisos(): array
    {
        try {
            $sql = "SELECT id, nombre_permiso, descripcion FROM permisos ORDER BY id ASC";
            $resultado = $this->selectAll($sql);
            return empty($resultado) ? ["error" => "No hay permisos registrados"] : $resultado;
        } catch (PDOException $e) {
            return $this->formatError("obtener los permisos registrados", $e);
        }
    }

    /**
     * Busca un permiso por su identificador de nombre único.
     */
    public function obtenerPermisoPorNombre(string $nombre_permiso): array
    {
        try {
            $permisoClean = strtolower(trim($nombre_permiso));

            if (!$this->existsWhere('permisos', 'nombre_permiso = :nombre', [':nombre' => $permisoClean])) {
                return ["error" => "No se encontró registro de ese permiso en nuestra base de datos"];
            }

            $sql = "SELECT id, nombre_permiso, descripcion FROM permisos WHERE nombre_permiso = :nombre LIMIT 1";
            $resultado = $this->selectOne($sql, [':nombre' => $permisoClean]);

            return [
                "success" => true,
                "message" => "Permiso encontrado exitosamente",
                "data"    => $resultado
            ];
        } catch (PDOException $e) {
            return $this->formatError("obtener el permiso {$nombre_permiso}", $e);
        }
    }

    /**
     * Registra un nuevo permiso en el catálogo.
     */
    public function crearPermiso(string $nombre_permiso, string $descripcion): array
    {
        try {
            $permisoClean = strtolower(trim($nombre_permiso));

            if ($this->existsWhere('permisos', 'nombre_permiso = :nombre', [':nombre' => $permisoClean])) {
                return ["error" => "El permiso: {$permisoClean} ya existe en la base de datos"];
            }

            $sql = "INSERT INTO permisos (nombre_permiso, descripcion) VALUES (:nombre, :desc)";
            $this->executeQuery($sql, [
                ':nombre' => $permisoClean,
                ':desc'   => trim($descripcion)
            ]);

            return [
                "success" => true,
                "message" => "Permiso Creado Exitosamente",
                "data"    => [
                    "id_permiso"     => (int)$this->pdo->lastInsertId(),
                    "nombre_permiso" => $permisoClean
                ]
            ];
        } catch (PDOException $e) {
            return $this->formatError("crear los permisos", $e);
        }
    }

    /**
     * Actualiza la información de un permiso.
     */
    public function actualizarPermiso(int $id_permiso, string $nombre_permiso, string $descripcion): array
    {
        try {
            if (!$this->existsWhere('permisos', 'id = :id', [':id' => $id_permiso])) {
                return ["error" => "No se encontró el permiso especificado en la base de datos"];
            }

            $permisoClean = strtolower(trim($nombre_permiso));

            if ($this->existsWhere('permisos', 'nombre_permiso = :nombre AND id != :id', [':nombre' => $permisoClean, ':id' => $id_permiso])) {
                return ["error" => "Ya existe otro permiso registrado con el nombre: {$nombre_permiso}"];
            }

            $sql = "UPDATE permisos SET nombre_permiso = :nombre, descripcion = :desc WHERE id = :id";
            $this->executeQuery($sql, [
                ':nombre' => $permisoClean,
                ':desc'   => trim($descripcion),
                ':id'     => $id_permiso
            ]);

            return ["success" => true, "message" => "Permiso actualizado exitosamente"];
        } catch (PDOException $e) {
            return $this->formatError("actualizar el permiso", $e);
        }
    }

    /**
     * Elimina un permiso del catálogo.
     */
    public function eliminarPermiso(int $id_permiso): array
    {
        try {
            if (!$this->existsWhere('permisos', 'id = :id', [':id' => $id_permiso])) {
                return ["error" => "No se encontró el permiso especificado en la base de datos"];
            }

            $this->executeQuery("DELETE FROM permisos WHERE id = :id", [':id' => $id_permiso]);
            return ["success" => true, "message" => "Permiso eliminado exitosamente"];
        } catch (PDOException $e) {
            return $this->formatError("eliminar el permiso", $e);
        }
    }
}