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
        try {
            if (!$this->pdo) {
                return ["error" => "Error de conexion a la base de datos"];
            }

            $stmt = $this->pdo->prepare("SELECT nombre_rol, descripcion FROM administracion.roles");
            $stmt->execute();
            $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return empty($resultado) ? ["error" => "No hay roles registrados"] : $resultado;
        } catch (PDOException $e) {
            return ["error" => "Error inesperado para obtener los roles" . $e->getMessage()];
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
                return ["error" => "Error de conexion a la base de datos"];
            }

            $checkStmt = $this->pdo->prepare("SELECT COUNT(*) FROM administracion.roles WHERE nombre_rol = :nombre_rol");
            $checkStmt->bindParam(':nombre_rol', $nombre_rol);
            $checkStmt->execute();

            if ($checkStmt->fetchColumn() == 0) {
                return ["error" => "No se encontrol el en nuestra base de datos"];
            }

            $stmt = $this->pdo->prepare("SELECT id, nombre_rol, descripcion FROM administracion.roles WHERE nombre_rol = :nombre_rol");
            $stmt->bindParam(':nombre_rol', $nombre_rol);
            $stmt->execute();

            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return empty($resultado) ? ["error" => "No hay registros con ese nombre de usuario {$nombre_rol}"] : $resultado;
        } catch (PDOException $e) {
            return ["error" => "Error inesperado para obtener el rol" . $e->getMessage()];
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
                return ["error" => "Error de conexion a la base de datos"];
            }

            $roles_minusculas = strtolower($nombre_rol);

            $checkStmt = $this->pdo->prepare("SELECT COUNT(*) FROM administracion.roles WHERE nombre_rol = :nombre_rol");
            $checkStmt->bindParam(':nombre_rol', $roles_minusculas);
            $checkStmt->execute();
            if ($checkStmt->fetchColumn() > 0) {
                return ["error" => "El nombre del rol ya existe en la base de datos"];
            }

            $stmt = $this->pdo->prepare("INSERT INTO administracion.roles (nombre_rol, descripcion) VALUES (:nombre_rol, :descripcion)");
            $stmt->bindParam(':nombre_rol', $roles_minusculas);
            $stmt->bindParam(':descripcion', $descripcion);
            $stmt->execute();

            return [
                "success" => true,
                "message" => "Rol creado exitosamente",
                "data" => [
                    "nombre_rol" => $nombre_rol,
                    "descripcion" => $descripcion
                ]
            ];
        } catch (PDOException $e) {
            return ["error" => "Error inesperado para crear roles" . $e->getMessage()];
        }
    }

    public function actualizarRol(int $id_rol, string $nombre_rol, string $descripcion)
    {
        try {
            if (!$this->pdo) {
                return ["error" => "Error de conexion a la base de datos"];
            }

            $rol_lower3 = strtolower($nombre_rol);

            $checkStmt = $this->pdo->prepare("SELECT COUNT(*) FROM administracion.roles WHERE id = :id_rol");
            $checkStmt->bindParam(':id_rol', $id_rol);
            $checkStmt->execute();

            if ($checkStmt->fetchColumn() == 0) {
                return ["error" => "No se encontro el rol especificado en la base de datos"];
            }

            $stmt = $this->pdo->prepare("UPDATE administracion.roles 
            SET nombre_rol = :nombre_rol, descripcion = :descripcion, act_en = NOW()
            WHERE id = :id_rol");
            $stmt->bindParam(':id_rol', $id_rol);
            $stmt->bindParam(':nombre_rol', $rol_lower3);
            $stmt->bindParam(':descripcion', $descripcion);
            $stmt->execute();
            
            return ["success" => true, "message" => "Rol actualizado exitosamente"];
        } catch (PDOException $e) {
            return ["error" => "Error inesperado para actualizar el rol" . $e->getMessage()];
        }
    }
}
