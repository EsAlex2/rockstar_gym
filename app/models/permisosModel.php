<?php

require_once __DIR__ . '/models.php';
require_once __DIR__ . '/../core/conn.php';

/* =================================================================================
 * permisosModel.php
 * Modelo para la gestión de permisos en el sistema de administración.
 * Autor: Alex Madrid
 * ==============================================================================
 */

class permisosModel extends Model
{
    protected $pdo;
    protected int $id_permiso;
    protected string $nombre_permiso;
    protected string $descripcion;

    protected array $mensajes = [];

    public function __construct($pdo)
    {
        parent::__construct($pdo);
        $this->pdo = $pdo;
    }

    public function obtenerPermisos()
    {
        try {
            if (!$this->pdo) {
                return ["error" => "Error de conexion a la base de datos"];
            }

            // Removido el prefijo 'administracion.'
            $stmt = $this->pdo->prepare("SELECT id, nombre_permiso, descripcion FROM permisos");
            $stmt->execute();
            $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return empty($resultado) ? ["error" => "No hay usuarios registrados"] : $resultado;
        } catch (PDOException $e) {
            return ["error" => "Error inesperado para obtener los permisos registrados" . $e->getMessage()];
        }
    }

    public function obtenerPermisoPorNombre(string $nombre_permiso)
    {
        try {
            if (!$this->pdo) {
                return ["error" => "Error de conexion a la base de datos"];
            }

            // Removido el prefijo 'administracion.'
            $checkStmt = $this->pdo->prepare("SELECT COUNT(*) FROM permisos WHERE nombre_permiso = :nombre_permiso");
            $checkStmt->bindParam(':nombre_permiso', $nombre_permiso);
            $checkStmt->execute();

            if ($checkStmt->fetchColumn() == 0) {
                return ["error" => "No se encontro registro de ese permiso en nuestra base de datos"];
            }

            $stmt = $this->pdo->prepare("SELECT id, nombre_permiso, descripcion FROM permisos WHERE nombre_permiso = :nombre_permiso");
            $stmt->bindParam(':nombre_permiso', $nombre_permiso);
            $stmt->execute();
            return [
                "success" => true,
                "message" => "Pemiso encontrado exitosamente",
                "data" => [
                    "nombre_permiso" => $nombre_permiso
                ]
            ];
        } catch (PDOException $e) {
            return ["error" => "Error inesperado para obtener el permiso {$nombre_permiso}" . $e->getMessage()];
        }
    }

    public function crearPermiso(string $nombre_permiso, string $descripcion)
    {   
        $permiso = strtolower($nombre_permiso);

        try {
            if (!$this->pdo) {
                return ["error" => "Error de conexion a la base de datos"];
            }

            // Removido el prefijo 'administracion.'
            $checkStmt = $this->pdo->prepare("SELECT COUNT(*) FROM permisos WHERE nombre_permiso = :nombre_permiso");
            $checkStmt->bindParam(':nombre_permiso', $permiso);
            $checkStmt->execute();

            if ($checkStmt->fetchColumn() > 0) {
                return ["error" => "El permiso: $permiso ya existe en la base de datos"];
            }

            $stmt = $this->pdo->prepare("INSERT INTO permisos (nombre_permiso, descripcion) VALUES (:nombre_permiso, :descripcion)");
            $stmt->bindParam(':nombre_permiso', $permiso);
            $stmt->bindParam(':descripcion', $descripcion);
            $stmt->execute();
            return [
                "success" => true,
                "message" => "Permiso Creado Exitosamente"
            ];
        } catch (PDOException $e) {
            return ["error" => "Error inesperado para crear los permisos" . $e->getMessage()];
        }
    }

    public function actualizarPermiso(int $id_permiso, string $nombre_permiso, string $descripcion)
    {
        $permiso = strtolower(trim($nombre_permiso));
        $descripcion = trim($descripcion);

        try {
            if (!$this->pdo) {
                return ["error" => "Error de conexion a la base de datos"];
            }

            $checkStmt = $this->pdo->prepare("SELECT COUNT(*) FROM permisos WHERE id = :id");
            $checkStmt->bindParam(':id', $id_permiso, PDO::PARAM_INT);
            $checkStmt->execute();

            if ($checkStmt->fetchColumn() == 0) {
                return ["error" => "No se encontró el permiso especificado en la base de datos"];
            }

            $checkDuplicate = $this->pdo->prepare("SELECT COUNT(*) FROM permisos WHERE nombre_permiso = :nombre AND id != :id");
            $checkDuplicate->bindParam(':nombre', $permiso);
            $checkDuplicate->bindParam(':id', $id_permiso, PDO::PARAM_INT);
            $checkDuplicate->execute();

            if ($checkDuplicate->fetchColumn() > 0) {
                return ["error" => "Ya existe otro permiso registrado con el nombre: " . $nombre_permiso];
            }

            $stmt = $this->pdo->prepare("UPDATE permisos SET nombre_permiso = :nombre_permiso, descripcion = :descripcion WHERE id = :id");
            $stmt->bindParam(':id', $id_permiso, PDO::PARAM_INT);
            $stmt->bindParam(':nombre_permiso', $permiso);
            $stmt->bindParam(':descripcion', $descripcion);
            $stmt->execute();

            return ["success" => true, "message" => "Permiso actualizado exitosamente"];
        } catch (PDOException $e) {
            return ["error" => "Error inesperado al actualizar el permiso: " . $e->getMessage()];
        }
    }

    public function eliminarPermiso(int $id_permiso)
    {
        try {
            if (!$this->pdo) {
                return ["error" => "Error de conexion a la base de datos"];
            }

            $checkStmt = $this->pdo->prepare("SELECT COUNT(*) FROM permisos WHERE id = :id");
            $checkStmt->bindParam(':id', $id_permiso, PDO::PARAM_INT);
            $checkStmt->execute();

            if ($checkStmt->fetchColumn() == 0) {
                return ["error" => "No se encontró el permiso especificado en la base de datos"];
            }

            $stmt = $this->pdo->prepare("DELETE FROM permisos WHERE id = :id");
            $stmt->bindParam(':id', $id_permiso, PDO::PARAM_INT);
            $stmt->execute();

            return ["success" => true, "message" => "Permiso eliminado exitosamente"];
        } catch (PDOException $e) {
            return ["error" => "Error inesperado al eliminar el permiso: " . $e->getMessage()];
        }
    }
}