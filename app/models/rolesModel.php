<?php

require_once __DIR__ . '/models.php';
require_once __DIR__ . '/../core/conn.php';

class rolesModel extends Model
{

    protected $pdo;
    protected string $nombre_rol;
    protected string $descripcion;

    public function __construct($pdo)
    {
        parent::__construct($pdo);
        $this->pdo = $pdo;
    }

    public function obtenerRoles()
    {
        try {
            if (!$this->pdo) {
                return json_encode(["error" => "Error de conexión a la base de datos"]);
            }

            $stmt = $this->pdo->prepare("SELECT nombre_rol, descripcion FROM administracion.roles");
            $stmt->execute();
            return json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {
            return json_encode(["error" => "Error al obtener los roles: " . $e->getMessage()]);
        }
    }

    public function obtenerRolPorNombre(string $nombre_rol)
    {
        try {
            if (!$this->pdo) {
                return json_encode(["error" => "Error de conexión a la base de datos"]);
            }

            $checkStmt = $this->pdo->prepare("SELECT COUNT(*) FROM administracion.roles WHERE nombre_rol = :nombre_rol");
            $checkStmt->bindParam(':nombre_rol', $nombre_rol);
            $checkStmt->execute();
            
            if ($checkStmt->fetchColumn() == 0) {
                return json_encode(["error" => "El rol no existe"]);
            }

            $stmt = $this->pdo->prepare("SELECT id, nombre_rol, descripcion FROM administracion.roles WHERE nombre_rol = :nombre_rol");
            $stmt->bindParam(':nombre_rol', $nombre_rol);
            $stmt->execute();
            return json_encode($stmt->fetch(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {
            return json_encode(["error" => "Error al obtener el rol: " . $e->getMessage()]);
        }
    }

    public function crearRol(string $nombre_rol, string $descripcion)
    {
        try {
            if (!$this->pdo) {
                return json_encode(["error" => "Error de conexión a la base de datos"]);
            }

            if (!isset($nombre_rol) || empty($nombre_rol) || !isset($descripcion) || empty($descripcion)) {
                return json_encode(["error" => "El nombre del rol y la descripción no pueden estar vacíos"]);
            }

            $checkStmt = $this->pdo->prepare("SELECT COUNT(*) FROM administracion.roles WHERE nombre_rol = :nombre_rol");
            $checkStmt->bindParam(':nombre_rol', $nombre_rol);
            $checkStmt->execute();
            if ($checkStmt->fetchColumn() > 0) {
                return json_encode(["error" => "El rol ya existe"]);
            }

            $stmt = $this->pdo->prepare("INSERT INTO administracion.roles (nombre_rol, descripcion) VALUES (:nombre_rol, :descripcion)");
            $stmt->bindParam(':nombre_rol', $nombre_rol);
            $stmt->bindParam(':descripcion', $descripcion);
            $stmt->execute();
            return json_encode(["success" => "Rol creado exitosamente"]);
        } catch (PDOException $e) {
            return json_encode(["error" => "Error al crear el rol: " . $e->getMessage()]);
        }
    }

    public function actualizarRol(int $id, string $nombre_rol, string $descripcion)
    {
        try {
            if (!$this->pdo) {
                return json_encode(["error" => "Error de conexión a la base de datos"]);
            }

            $stmt = $this->pdo->prepare("UPDATE administracion.roles SET nombre_rol = :nombre_rol, descripcion = :descripcion WHERE id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':nombre_rol', $nombre_rol);
            $stmt->bindParam(':descripcion', $descripcion);
            $stmt->execute();
            return json_encode(["success" => "Rol actualizado exitosamente"]);
        } catch (PDOException $e) {
            return json_encode(["error" => "Error al actualizar el rol: " . $e->getMessage()]);
        }
    }
}

$rolesModel = new rolesModel($pdo);
echo $rolesModel->obtenerRoles();

echo "<hr>";

echo $rolesModel->obtenerRolPorNombre('Administrado');
