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
        $permiso_lower = strtolower($nombre_permiso);

        $this->mensajes = [
            "Error de conexion a la base de datos",
            "El permiso {$permiso_lower} ya existe",
            "Error inesperado para crear el permiso {$permiso_lower}",
            "Permiso creado exitosamente!"
        ];

        try {
            if (!$this->pdo) {
                return $this->mensajes[0];
            }

            $checkStmt = $this->pdo->prepare("SELECT COUNT(*) FROM administracion.permisos WHERE nombre_permiso = :nombre_permiso");
            $checkStmt->bindParam(':nombre_permiso', $permiso_lower);
            $checkStmt->execute();

            if ($checkStmt->fetchColumn() > 0) {
                return $this->mensajes[1];
            }

            $stmt = $this->pdo->prepare("INSERT INTO administracion.permisos (nombre_permiso, descripcion) VALUES (:nombre_permiso, :descripcion)");
            $stmt->bindParam(':nombre_permiso', $permiso_lower);
            $stmt->bindParam(':descripcion', $descripcion);
            $stmt->execute();
            return json_encode([
                "message" => $this->mensajes[3]
            ]);
        } catch (PDOException $e) {
            return json_encode($this->mensajes[2] . ": " . $e->getMessage());
        }
    }

    public function actualizarPermisos(int $id_permiso, string $nombre_permiso, string $descripcion)
    {   
        $permiso_lower = strtolower($nombre_permiso);

        $this->mensajes = [
            "Error de conexion a la base de datos",
            "No se encontró el permiso: {$permiso_lower} en la base de datos",
            "Error inesperado para actualizar el permiso {$permiso_lower}",
            "Permiso actualizado exitosamente!"
        ];

        try {
            if (!$this->pdo) {
                return $this->mensajes[0];
            }

            $checkStmt = $this->pdo->prepare("SELECT COUNT(*) FROM administracion.permisos WHERE id = :id_permiso");
            $checkStmt->bindParam(':id_permiso', $id_permiso);
            $checkStmt->execute();

            if ($checkStmt->fetchColumn() == 0) {
                return $this->mensajes[1];
            }

            $stmt = $this->pdo->prepare("UPDATE administracion.permisos SET nombre_permiso = :nombre_permiso, descripcion = :descripcion WHERE id = :id_permiso");
            $stmt->bindParam(':id_permiso', $id_permiso);
            $stmt->bindParam(':nombre_permiso', $permiso_lower);
            $stmt->bindParam(':descripcion', $descripcion);
            $stmt->execute();
            return $this->mensajes[3];
        } catch (PDOException $e) {
            return json_encode($this->mensajes[2] . ": " . $e->getMessage());
        }
    }
}

// $modelsPermisos = new PermisosModel($pdo);
// echo $modelsPermisos->obtenerPermisos();

// echo "<hr>";

// echo $modelsPermisos->obtenerPermisoPorNombre("mi_perfil.ver");

// echo "<hr>";

// echo $modelsPermisos->crearPermiso("seguridad.root", "adad");

// echo "<hr>";

// echo $modelsPermisos->actualizarPermisos(21, "SEGURIñAD.ROOT", "TOOOOT");