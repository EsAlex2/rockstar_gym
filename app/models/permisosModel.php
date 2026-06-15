<?php

require_once __DIR__ . '/models.php';
require_once __DIR__ . '/../core/conn.php';

/* =================================================================================
 *  permisosModel.php
 *  Modelo para la gestión de permisos en el sistema de administración.
 *  Proporciona métodos para obtener, crear y actualizar permisos.
 *  Utiliza PDO para la interacción con la base de datos y maneja errores de conexión y ejecución.
 *  Autor: Alex Madrid
 *  Fecha: 03/06/2026
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

            $stmt = $this->pdo->prepare("SELECT id, nombre_permiso, descripcion FROM administracion.permisos");
            $stmt->execute();

            return empty($stmt) ? ["error" => "No hay usuarios registrados"] : $stmt;
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

            $checkStmt = $this->pdo->prepare("SELECT COUNT(*) FROM administracion.permisos WHERE nombre_permiso = :nombre_permiso");
            $checkStmt->bindParam(':nombre_permiso', $nombre_permiso);
            $checkStmt->execute();

            if ($checkStmt->fetchColumn() == 0) {
                return ["error" => "No se encontro registro de ese permiso en nuestra base de datos"];
            }

            $stmt = $this->pdo->prepare("SELECT id, nombre_permiso, descripcion FROM administracion.permisos WHERE nombre_permiso = :nombre_permiso");
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

            $checkStmt = $this->pdo->prepare("SELECT COUNT(*) FROM administracion.permisos WHERE nombre_permiso = :nombre_permiso");
            $checkStmt->bindParam(':nombre_permiso', $permiso);
            $checkStmt->execute();

            if ($checkStmt->fetchColumn() > 0) {
                return ["error" => "El permiso: $permiso ya existe en la base de datos"];
            }

            $stmt = $this->pdo->prepare("INSERT INTO administracion.permisos (nombre_permiso, descripcion) VALUES (:nombre_permiso, :descripcion)");
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
}
