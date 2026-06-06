<?php

require_once __DIR__ . '/models.php';
require_once __DIR__ . '/../core/conn.php';

/* =================================================================================
    *  rolesModel.php
    *  Modelo para la gestión de roles en el sistema de administración.
    *  Proporciona métodos para obtener, crear y actualizar roles.
    *  Utiliza PDO para la interacción con la base de datos y maneja errores de conexión y ejecución.
    *  Autor: Alex Madrid
    *  Fecha: 03/06/2026
    * ==============================================================================
*/


class rolesModel extends Model
{
    protected $pdo; 
    protected int $id_rol;
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
        'Error de conexion a la base de datos', 
        'Error inesperado para obtener los roles registrados'
        ];

        try {
            if (!$this->pdo) {
                return $this->mensajes[0];
            }

            $stmt = $this->pdo->prepare("SELECT nombre_rol, descripcion FROM administracion.roles");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return $this->mensajes[1] . $e->getMessage();
        }
    }

    public function obtenerRolPorNombre(string $nombre_rol)
    {
        $this->mensajes = [
        'Error de conexion a la base de datos', 
        'No se encontró el rol {nombre_rol} en la base de datos'
        ];

        try {
            if (!$this->pdo) {
                return $this->mensajes[0];
            }

            $checkStmt = $this->pdo->prepare("SELECT COUNT(*) FROM administracion.roles WHERE nombre_rol = :nombre_rol");
            $checkStmt->bindParam(':nombre_rol', $nombre_rol);
            $checkStmt->execute();
            
            if ($checkStmt->fetchColumn() == 0) {
                return str_replace('{nombre_rol}', $nombre_rol, $this->mensajes[1]);
            }

            $stmt = $this->pdo->prepare("SELECT id, nombre_rol, descripcion FROM administracion.roles WHERE nombre_rol = :nombre_rol");
            $stmt->bindParam(':nombre_rol', $nombre_rol);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return str_replace('{nombre_rol}', $nombre_rol, $this->mensajes[1]) . $e->getMessage();
        }
    }

    public function crearRol(string $nombre_rol, string $descripcion)
    {
        $this->mensajes = [
        'Error de conexion a la base de datos', 
        'Error al crear el rol {nombre_rol}', 
        'El nombre del rol ya existe en la base de datos', 
        'El nombre del rol y la descripción son obligatorios', 
        'Rol creado exitosamente'
        ];

        try {
            if (!$this->pdo) {
                return $this->mensajes[0];
            }

            $roles_minusculas = strtolower($nombre_rol);

            if (!isset($roles_minusculas) || empty($roles_minusculas) || !isset($descripcion) || empty($descripcion)) {
                return str_replace('{nombre_rol}', $roles_minusculas, $this->mensajes[3]);
            }

            $checkStmt = $this->pdo->prepare("SELECT COUNT(*) FROM administracion.roles WHERE nombre_rol = :nombre_rol");
            $checkStmt->bindParam(':nombre_rol', $roles_minusculas);
            $checkStmt->execute();
            if ($checkStmt->fetchColumn() > 0) {
                return str_replace('{nombre_rol}', $nombre_rol, $this->mensajes[2]);
            }

            $stmt = $this->pdo->prepare("INSERT INTO administracion.roles (nombre_rol, descripcion) VALUES (:nombre_rol, :descripcion)");
            $stmt->bindParam(':nombre_rol', $roles_minusculas);
            $stmt->bindParam(':descripcion', $descripcion);
            $stmt->execute();
            return $this->mensajes[4];
        } catch (PDOException $e) {
            return str_replace('{nombre_rol}', $nombre_rol, $this->mensajes[1]) . ": " . $e->getMessage();
        }
    }

    public function actualizarRol(int $id_rol, string $nombre_rol, string $descripcion)
    {
        $this->mensajes = [
        'Error de conexion a la base de datos', 
        "El {$nombre_rol} no se en encuentra en la base de datos para actualizarse!",
        'Rol actualizado exitosamente'
        ];
        try {
            if (!$this->pdo) {
                return $this->mensajes[0];
            }

            $rol_lower3 = strtolower($nombre_rol);

            $checkStmt = $this->pdo->prepare("SELECT COUNT(*) FROM administracion.roles WHERE id = :id_rol");
            $checkStmt->bindParam(':id_rol', $id_rol);
            $checkStmt->execute();

            if ($checkStmt->fetchColumn() == 0) {
                return str_replace('{nombre_rol}', $rol_lower3, $this->mensajes[1]);
            }

            $stmt = $this->pdo->prepare("UPDATE administracion.roles 
            SET nombre_rol = :nombre_rol, descripcion = :descripcion, act_en = NOW()
            WHERE id = :id_rol");
            $stmt->bindParam(':id_rol', $id_rol);
            $stmt->bindParam(':nombre_rol', $rol_lower3);
            $stmt->bindParam(':descripcion', $descripcion);
            $stmt->execute();
            return $this->mensajes[2];
        } catch (PDOException $e) {
            return str_replace('{nombre_rol}', $nombre_rol, $this->mensajes[1]) . ": " . $e->getMessage();
        }
    }
}





