<?php

require_once __DIR__ . '/models.php';
require_once __DIR__ . '/../core/conn.php';

/* * usuariosModel.php
 * Modelo para la gestión de usuarios y credenciales de acceso al sistema.
 * Autor: Alex Madrid (Adaptación)
 * Fecha: 16/06/2026
 */

class usuariosModel extends Model
{
    protected $pdo;
    protected int $id_persona;
    protected string $usuario;
    protected string $password;
    protected int $id_rol;
    protected int $id_estatus;

    public function __construct($pdo)
    {
        parent::__construct($pdo);
        $this->pdo = $pdo;
    }

    /**
     * Obtiene la lista de todos los usuarios con sus datos de persona y roles emparejados.
     */
    public function obtenerUsuarios()
    {
        try {
            if (!$this->pdo) {
                return ["error" => "Error de conexión a la base de datos"];
            }

            $stmt = $this->pdo->prepare("SELECT 
                u.id AS id_usuario, 
                u.email_user, 
                r.nombre_rol AS rol, 
                e.nombre_estatus AS estatus,
                p.cedula_identidad, 
                p.primer_nombre, 
                p.primer_apellido
                FROM administracion.usuarios u
                INNER JOIN administracion.personas p ON u.id_persona = p.id
                INNER JOIN administracion.roles r ON u.id_rol = r.id
                INNER JOIN administracion.estatus e ON u.id_estatus = e.id
                ORDER BY u.id DESC");
            $stmt->execute();
            $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return empty($resultado) ? ["error" => "No hay usuarios registrados"] : $resultado;
        } catch (PDOException $e) {
            return ["error" => "Error al obtener usuarios: " . $e->getMessage()];
        }
    }

    /**
     * Registra un nuevo usuario en el sistema verificando duplicados de username o persona ya asignada
     */
    public function crearUsuario(int $id_persona, string $usuario, string $password, int $id_rol)
    {
        try {
            if (!$this->pdo) {
                return ["error" => "Error de conexión a la base de datos"];
            }

            // 1. Validar si el nombre de usuario ya existe
            $checkUser = $this->pdo->prepare("SELECT COUNT(*) FROM administracion.usuarios WHERE email_user = :email_user");
            $checkUser->bindParam(':email_user', $usuario);
            $checkUser->execute();
            if ($checkUser->fetchColumn() > 0) {
                return ["error" => "El correo electronico '{$usuario}' ya se encuentra registrado"];
            }

            // 2. Validar si esa persona ya posee un usuario asignado
            $checkPersona = $this->pdo->prepare("SELECT COUNT(*) FROM administracion.usuarios WHERE id_persona = :id_persona");
            $checkPersona->bindParam(':id_persona', $id_persona);
            $checkPersona->execute();
            if ($checkPersona->fetchColumn() > 0) {
                return ["error" => "La persona seleccionada ya cuenta con un usuario en el sistema"];
            }

            // Encriptación segura de la contraseña
            $passwordHash = password_hash($password, PASSWORD_BCRYPT);
            $id_estatus = 1; // Estatus activo por defecto según tu lógica base

            $stmt = $this->pdo->prepare("INSERT INTO administracion.usuarios (id_persona, email_user, password_hash, id_rol, id_estatus, creado_en) 
                VALUES (:id_persona, :email_user, :password, :id_rol, :id_estatus, now())");
            
            $stmt->bindParam(':id_persona', $id_persona);
            $stmt->bindParam(':email_user', $usuario);
            $stmt->bindParam(':password', $passwordHash);
            $stmt->bindParam(':id_rol', $id_rol);
            $stmt->bindParam(':id_estatus', $id_estatus);
            $stmt->execute();

            return ["success" => true, "message" => "El usuario '{$usuario}' ha sido creado exitosamente"];
        } catch (PDOException $e) {
            return ["error" => "Error al crear el usuario: " . $e->getMessage()];
        }
    }
}