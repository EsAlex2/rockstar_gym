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
        $this->mensajes = [
            'Error de conexion a la base de datos',
            'Error inesperado para obtener los permisos registrados'
        ];

        try {
            if (!$this->pdo) {
                return $this->mensajes[0];
            }

            $stmt = $this->pdo->prepare("SELECT id, nombre_permiso, descripcion FROM administracion.permisos");
            $stmt->execute();
            return json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {
            return $this->mensajes[1] . $e->getMessage();
        }
    }

    public function obtenerPermisoPorNombre(string $nombre_permiso)
    {
        $this->mensajes = [
            'Error de conexion a la base de datos',
            "No se encontró el permiso: {$nombre_permiso} en la base de datos",
            "Error inesperado para obtener el permiso {$nombre_permiso}"
        ];

        try {
            if (!$this->pdo) {
                return $this->mensajes[0];
            }

            $checkStmt = $this->pdo->prepare("SELECT COUNT(*) FROM administracion.permisos WHERE nombre_permiso = :nombre_permiso");
            $checkStmt->bindParam(':nombre_permiso', $nombre_permiso);
            $checkStmt->execute();

            if ($checkStmt->fetchColumn() == 0) {
                return $this->mensajes[1];
            }

            $stmt = $this->pdo->prepare("SELECT id, nombre_permiso, descripcion FROM administracion.permisos WHERE nombre_permiso = :nombre_permiso");
            $stmt->bindParam(':nombre_permiso', $nombre_permiso);
            $stmt->execute();
            return json_encode($stmt->fetch(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {
            return json_encode($this->mensajes[2] . ": " . $e->getMessage());
        }
    }

    public function crearPermiso(string $nombre_permiso, string $descripcion)
    {
        $this->mensajes = [
            "Error de conexion a la base de datos",
            "El permiso ya existe",
            "Error inesperado para crear el permiso {$nombre_permiso}",
            "Permiso creado exitosamente!"
        ];

        try {
            if (!$this->pdo) {
                return $this->mensajes[0];
            }

            $checkStmt = $this->pdo->prepare("SELECT COUNT(*) FROM administracion.permisos WHERE nombre_permiso = :nombre_permiso");
            $checkStmt->bindParam(':nombre_permiso', $nombre_permiso);
            $checkStmt->execute();

            if ($checkStmt->fetchColumn() > 0) {
                return $this->mensajes[1];
            }

            $stmt = $this->pdo->prepare("INSERT INTO administracion.permisos (nombre_permiso, descripcion) VALUES (:nombre_permiso, :descripcion)");
            $stmt->bindParam(':nombre_permiso', $nombre_permiso);
            $stmt->bindParam(':descripcion', $descripcion);
            $stmt->execute();
            return $this->mensajes[3];
        } catch (PDOException $e) {
            return json_encode($this->mensajes[2] . ": " . $e->getMessage());
        }
    }

    public function actualizarPermisos(string $nombre_permiso, string $descripcion)
    {   
        $this->mensajes = [
            "Error de conexion a la base de datos",
            "No se encontró el permiso: {$nombre_permiso} en la base de datos",
            "Error inesperado para actualizar el permiso {$nombre_permiso}",
            "Permiso actualizado exitosamente!"
        ];

        try {
            if (!$this->pdo) {
                return $this->mensajes[0];
            }

            $checkStmt = $this->pdo->prepare("SELECT COUNT(*) FROM administracion.permisos WHERE nombre_permiso = :nombre_permiso");
            $checkStmt->bindParam(':nombre_permiso', $nombre_permiso);
            $checkStmt->execute();

            if ($checkStmt->fetchColumn() == 0) {
                return $this->mensajes[1];
            }

            $stmt = $this->pdo->prepare("UPDATE administracion.permisos SET descripcion = :descripcion WHERE nombre_permiso = :nombre_permiso");
            $stmt->bindParam(':nombre_permiso', $nombre_permiso);
            $stmt->bindParam(':descripcion', $descripcion);
            $stmt->execute();
            return $this->mensajes[3];
        } catch (PDOException $e) {
            return json_encode($this->mensajes[2] . ": " . $e->getMessage());
        }
    }
}

$modelsPermisos = new PermisosModel($pdo);
echo $modelsPermisos->obtenerPermisoPorNombre('usuarios.leer');