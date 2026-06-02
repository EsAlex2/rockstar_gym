<?php

require_once __DIR__ . '/models.php';
require_once __DIR__ . '/../core/conn.php';

class rolesModel extends Model
{

    protected $pdo;
    protected string $nombre_rol;
    protected string $descripcion;
    protected array $mensajes = [];

    public function __construct($pdo)
    {
        parent::__construct($pdo);
        $this->pdo = $pdo;
    }

    public function obtenerRoles()
    {
        $this->mensajes = [
            "error_conexion" => "Error de conexión a la base de datos",
            "error_obtener_roles" => "Error al obtener los roles",
            "error_obtener_rol" => "Error al obtener el rol {nombre_rol}: El rol no existe.",
            "error_crear_rol" => "Error al crear el rol: {nombre_rol}",
            "error_actualizar_rol" => "Error al actualizar el rol: {nombre_rol}",
            "success_crear_rol" => "Rol creado exitosamente",
            "success_actualizar_rol" => "Rol actualizado exitosamente"
        ];
        try {
            if (!$this->pdo) {
                return $this->mensajes["error_conexion"];
            }

            $stmt = $this->pdo->prepare("SELECT nombre_rol, descripcion FROM administracion.roles");
            $stmt->execute();
            return json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {
            return $this->mensajes["error_obtener_roles"] . $e->getMessage();
        }
    }

    public function obtenerRolPorNombre(string $nombre_rol)
    {
        try {
            if (!$this->pdo) {
                return $this->mensajes["error_conexion"];
            }

            $checkStmt = $this->pdo->prepare("SELECT COUNT(*) FROM administracion.roles WHERE nombre_rol = :nombre_rol");
            $checkStmt->bindParam(':nombre_rol', $nombre_rol);
            $checkStmt->execute();
            
            if ($checkStmt->fetchColumn() == 0) {
                return str_replace('{nombre_rol}', $nombre_rol, $this->mensajes["error_obtener_rol"]);
            }

            $stmt = $this->pdo->prepare("SELECT id, nombre_rol, descripcion FROM administracion.roles WHERE nombre_rol = :nombre_rol");
            $stmt->bindParam(':nombre_rol', $nombre_rol);
            $stmt->execute();
            return json_encode($stmt->fetch(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {
            return str_replace('{nombre_rol}', $nombre_rol, $this->mensajes["error_obtener_rol"]) . $e->getMessage();
        }
    }

    public function crearRol(string $nombre_rol, string $descripcion)
    {
        try {
            if (!$this->pdo) {
                return $this->mensajes["error_conexion"];
            }

            if (!isset($nombre_rol) || empty($nombre_rol) || !isset($descripcion) || empty($descripcion)) {
                return str_replace('{nombre_rol}', $nombre_rol, $this->mensajes["error_crear_rol"]) . ": El nombre del rol y la descripción son obligatorios.";
            }

            $checkStmt = $this->pdo->prepare("SELECT COUNT(*) FROM administracion.roles WHERE nombre_rol = :nombre_rol");
            $checkStmt->bindParam(':nombre_rol', $nombre_rol);
            $checkStmt->execute();
            if ($checkStmt->fetchColumn() > 0) {
                return str_replace('{nombre_rol}', $nombre_rol, $this->mensajes["error_crear_rol"]) . ": El nombre del rol ya existe.";
            }

            $stmt = $this->pdo->prepare("INSERT INTO administracion.roles (nombre_rol, descripcion) VALUES (:nombre_rol, :descripcion)");
            $stmt->bindParam(':nombre_rol', $nombre_rol);
            $stmt->bindParam(':descripcion', $descripcion);
            $stmt->execute();
            return $this->mensajes["success_crear_rol"];
        } catch (PDOException $e) {
            return str_replace('{nombre_rol}', $nombre_rol, $this->mensajes["error_crear_rol"]) . ": " . $e->getMessage();
        }
    }

    public function actualizarRol(int $id, string $nombre_rol, string $descripcion)
    {
        try {
            if (!$this->pdo) {
                return $this->mensajes["error_conexion"];
            }

            $stmt = $this->pdo->prepare("UPDATE administracion.roles SET nombre_rol = :nombre_rol, descripcion = :descripcion WHERE id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':nombre_rol', $nombre_rol);
            $stmt->bindParam(':descripcion', $descripcion);
            $stmt->execute();
            return $this->mensajes["success_actualizar_rol"];
        } catch (PDOException $e) {
            return str_replace('{nombre_rol}', $nombre_rol, $this->mensajes["error_actualizar_rol"]) . ": " . $e->getMessage();
        }
    }
}

$rolesModel = new rolesModel($pdo);
echo $rolesModel->obtenerRoles();

echo "<hr>";

echo $rolesModel->obtenerRolPorNombre('Cliete');
