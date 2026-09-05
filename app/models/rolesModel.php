<?php

require_once __DIR__ . '/models.php';

/**
 * Class RolesModel
 * Modelo para la administración de roles de usuario en el sistema.
 * Extiende de BaseModel.
 */
class RolesModel extends BaseModel
{
    protected string $table = 'roles';

    public function __construct(?PDO $pdo = null)
    {
        parent::__construct($pdo);
    }

    /**
     * Obtiene todos los roles registrados en el sistema.
     */
    public function obtenerRoles(): array
    {
        try {
            $sql = "SELECT id, nombre_rol, descripcion FROM roles ORDER BY id ASC";
            $resultado = $this->selectAll($sql);
            return empty($resultado) ? ["error" => "No hay roles registrados"] : $resultado;
        } catch (PDOException $e) {
            return $this->formatError("obtener los roles", $e);
        }
    }

    /**
     * Busca un rol por su nombre único.
     */
    public function obtenerRolPorNombre(string $nombre_rol): array
    {
        try {
            $rolClean = strtolower(trim($nombre_rol));

            if (!$this->existsWhere('roles', 'nombre_rol = :nombre', [':nombre' => $rolClean])) {
                return ["error" => "No se encontró el rol en nuestra base de datos"];
            }

            $sql = "SELECT id, nombre_rol, descripcion FROM roles WHERE nombre_rol = :nombre LIMIT 1";
            $resultado = $this->selectOne($sql, [':nombre' => $rolClean]);

            return $resultado ?? ["error" => "No hay registros con ese nombre de rol {$nombre_rol}"];
        } catch (PDOException $e) {
            return $this->formatError("obtener el rol", $e);
        }
    }

    /**
     * Crea un nuevo rol en el sistema.
     */
    public function crearRol(string $nombre_rol, string $descripcion): array
    {
        try {
            $rolClean = strtolower(trim($nombre_rol));

            if ($this->existsWhere('roles', 'nombre_rol = :nombre', [':nombre' => $rolClean])) {
                return ["error" => "El nombre del rol ya existe en la base de datos"];
            }

            $sql = "INSERT INTO roles (nombre_rol, descripcion) VALUES (:nombre, :desc)";
            $this->executeQuery($sql, [
                ':nombre' => $rolClean,
                ':desc'   => trim($descripcion)
            ]);

            return [
                "success" => true,
                "message" => "Rol creado exitosamente",
                "data"    => [
                    "id_rol"      => (int)$this->pdo->lastInsertId(),
                    "nombre_rol"  => $nombre_rol,
                    "descripcion" => $descripcion
                ]
            ];
        } catch (PDOException $e) {
            return $this->formatError("crear rol", $e);
        }
    }

    /**
     * Actualiza la información de un rol existente.
     */
    public function actualizarRol(int $id_rol, string $nombre_rol, string $descripcion): array
    {
        try {
            if (!$this->existsWhere('roles', 'id = :id', [':id' => $id_rol])) {
                return ["error" => "No se encontró el rol especificado en la base de datos"];
            }

            $rolClean = strtolower(trim($nombre_rol));

            if ($this->existsWhere('roles', 'nombre_rol = :nombre AND id != :id', [':nombre' => $rolClean, ':id' => $id_rol])) {
                return ["error" => "Ya existe otro rol registrado con ese nombre"];
            }

            $sql = "UPDATE roles SET nombre_rol = :nombre, descripcion = :desc, act_en = NOW() WHERE id = :id";
            $this->executeQuery($sql, [
                ':nombre' => $rolClean,
                ':desc'   => trim($descripcion),
                ':id'     => $id_rol
            ]);

            return ["success" => true, "message" => "Rol actualizado exitosamente"];
        } catch (PDOException $e) {
            return $this->formatError("actualizar el rol", $e);
        }
    }

    /**
     * Elimina un rol del sistema.
     */
    public function eliminarRol(int $id_rol): array
    {
        try {
            if (!$this->existsWhere('roles', 'id = :id', [':id' => $id_rol])) {
                return ["error" => "No se encontró el rol especificado en la base de datos"];
            }

            $this->executeQuery("DELETE FROM roles WHERE id = :id", [':id' => $id_rol]);
            return ["success" => true, "message" => "Rol eliminado exitosamente"];
        } catch (PDOException $e) {
            return $this->formatError("eliminar el rol", $e);
        }
    }
}