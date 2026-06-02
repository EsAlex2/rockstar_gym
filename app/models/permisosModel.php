<?php

require_once __DIR__ . '/models.php';
require_once __DIR__ . '/../core/conn.php';

class permisosModel extends Model
{
    protected $pdo;
    protected string $nombre_permiso;
    protected string $descripcion;

    public function __construct($pdo)
    {
        parent::__construct($pdo);
        $this->pdo = $pdo;
    }

    public function obtenerPermisos()
    {
        try {
            if (!$this->pdo) {
                return json_encode(["error" => "Error de conexión a la base de datos"]);
            }

            $stmt = $this->pdo->prepare("SELECT id, nombre_permiso, descripcion FROM administracion.permisos");
            $stmt->execute();
            return json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {
            return json_encode(["error" => "Error al obtener los permisos: " . $e->getMessage()]);
        }
    }

    public function obtenerPermisoPorNombre(string $nombre_permiso)
    {
        try {
            if (!$this->pdo) {
                return json_encode(["error" => "Error de conexión a la base de datos"]);
            }

            $checkStmt = $this->pdo->prepare("SELECT COUNT(*) FROM administracion.permisos WHERE nombre_permiso = :nombre_permiso");
            $checkStmt->bindParam(':nombre_permiso', $nombre_permiso);
            $checkStmt->execute();
            
            if ($checkStmt->fetchColumn() == 0) {
                return json_encode(["error" => "El permiso no existe"]);
            }

            $stmt = $this->pdo->prepare("SELECT id, nombre_permiso, descripcion FROM administracion.permisos WHERE nombre_permiso = :nombre_permiso");
            $stmt->bindParam(':nombre_permiso', $nombre_permiso);
            $stmt->execute();
            return json_encode($stmt->fetch(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {
            return json_encode(["error" => "Error al obtener el permiso: " . $e->getMessage()]);
        }
    }

    public function crearPermiso(string $nombre_permiso, string $descripcion)
    {
        try {
            if (!$this->pdo) {
                return json_encode(["error" => "Error de conexión a la base de datos"]);
            }

            $checkStmt = $this->pdo->prepare("SELECT COUNT(*) FROM administracion.permisos WHERE nombre_permiso = :nombre_permiso");
            $checkStmt->bindParam(':nombre_permiso', $nombre_permiso);
            $checkStmt->execute();
            
            if ($checkStmt->fetchColumn() > 0) {
                return json_encode(["error" => "El permiso ya existe"]);
            }

            $stmt = $this->pdo->prepare("INSERT INTO administracion.permisos (nombre_permiso, descripcion) VALUES (:nombre_permiso, :descripcion)");
            $stmt->bindParam(':nombre_permiso', $nombre_permiso);
            $stmt->bindParam(':descripcion', $descripcion);
            $stmt->execute();
            return json_encode(["success" => "Permiso creado exitosamente"]);
        } catch (PDOException $e) {
            return json_encode(["error" => "Error al crear el permiso: " . $e->getMessage()]);
        }
    }

    public function actualizarPermisos(string $nombre_permiso, string $descripcion)
    {
        try {
            if (!$this->pdo) {
                return json_encode(["error" => "Error de conexión a la base de datos"]);
            }

            $checkStmt = $this->pdo->prepare("SELECT COUNT(*) FROM administracion.permisos WHERE nombre_permiso = :nombre_permiso");
            $checkStmt->bindParam(':nombre_permiso', $nombre_permiso);
            $checkStmt->execute();
            
            if ($checkStmt->fetchColumn() == 0) {
                return json_encode(["error" => "El permiso no existe"]);
            }

            $stmt = $this->pdo->prepare("UPDATE administracion.permisos SET descripcion = :descripcion WHERE nombre_permiso = :nombre_permiso");
            $stmt->bindParam(':nombre_permiso', $nombre_permiso);
            $stmt->bindParam(':descripcion', $descripcion);
            $stmt->execute();
            return json_encode(["success" => "Permiso actualizado exitosamente"]);
        } catch (PDOException $e) {
            return json_encode(["error" => "Error al actualizar el permiso: " . $e->getMessage()]);
        }
    }
}

$modelsPermisos = new PermisosModel($pdo);
echo $modelsPermisos->obtenerPermisos();