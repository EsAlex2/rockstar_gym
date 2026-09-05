<?php

require_once __DIR__ . '/models.php';

/**
 * Class UsuariosModel
 * Modelo para la gestión de usuarios del sistema, credenciales y autenticación.
 * Extiende de BaseModel.
 */
class UsuariosModel extends BaseModel
{
    protected string $table = 'usuarios';

    public function __construct(?PDO $pdo = null)
    {
        parent::__construct($pdo);
    }

    /**
     * Obtiene la lista completa de usuarios con su rol y datos personales vinculados.
     * @return array
     */
    public function obtenerUsuarios(): array
    {
        try {
            $sql = "SELECT 
                        u.id AS id_usuario, 
                        u.email_user, 
                        r.nombre_rol AS rol, 
                        e.nombre_estatus AS estatus,
                        p.cedula_identidad, 
                        p.primer_nombre, 
                        p.primer_apellido
                    FROM usuarios u
                    INNER JOIN personas p ON u.id_persona = p.id
                    INNER JOIN roles r ON u.id_rol = r.id
                    INNER JOIN estatus e ON u.id_estatus = e.id
                    ORDER BY u.id DESC";

            $resultado = $this->selectAll($sql);
            return empty($resultado) ? ["error" => "No hay usuarios registrados"] : $resultado;
        } catch (PDOException $e) {
            return $this->formatError("obtener usuarios", $e);
        }
    }

    /**
     * Registra un nuevo usuario con contraseña encriptada de forma segura (Bcrypt).
     */
    public function crearUsuario(int $id_persona, string $usuario, string $password, int $id_rol): array
    {
        try {
            $userClean = strtolower(trim($usuario));

            if ($this->existsWhere('usuarios', 'email_user = :email', [':email' => $userClean])) {
                return ["error" => "El correo electrónico '{$userClean}' ya se encuentra registrado"];
            }

            if ($this->existsWhere('usuarios', 'id_persona = :id', [':id' => $id_persona])) {
                return ["error" => "La persona seleccionada ya cuenta con un usuario en el sistema"];
            }

            $passwordHash = password_hash($password, PASSWORD_BCRYPT);
            $idEstatus    = 1;

            $sql = "INSERT INTO usuarios (id_persona, email_user, password_hash, id_rol, id_estatus, creado_en) 
                    VALUES (:id_persona, :email_user, :password, :id_rol, :id_estatus, NOW())";

            $this->executeQuery($sql, [
                ':id_persona'   => $id_persona,
                ':email_user'   => $userClean,
                ':password'     => $passwordHash,
                ':id_rol'       => $id_rol,
                ':id_estatus'   => $idEstatus
            ]);

            return [
                "success" => true,
                "message" => "El usuario '{$userClean}' ha sido creado exitosamente",
                "data"    => [
                    "id_usuario" => (int)$this->pdo->lastInsertId(),
                    "email_user" => $userClean
                ]
            ];
        } catch (PDOException $e) {
            return $this->formatError("crear usuario", $e);
        }
    }

    /**
     * Actualiza la información de un usuario (rol, estatus, correo y opcionalmente contraseña).
     */
    public function actualizarUsuario(int $id_usuario, int $id_estatus, int $id_rol, string $email_user, string $password = ''): array
    {
        try {
            if (!$this->existsWhere('usuarios', 'id = :id', [':id' => $id_usuario])) {
                return ["error" => "No se encontró el usuario en la base de datos"];
            }

            $emailClean = strtolower(trim($email_user));

            if ($this->existsWhere('usuarios', 'email_user = :email AND id != :id', [':email' => $emailClean, ':id' => $id_usuario])) {
                return ["error" => "El correo electrónico '{$emailClean}' ya está registrado por otro usuario"];
            }

            if (!empty($password)) {
                $passwordHash = password_hash($password, PASSWORD_BCRYPT);
                $sql = "UPDATE usuarios SET id_estatus = :estatus, id_rol = :rol, email_user = :email, password_hash = :pass, actualizado_en = NOW() WHERE id = :id";
                $params = [
                    ':estatus' => $id_estatus,
                    ':rol'     => $id_rol,
                    ':email'   => $emailClean,
                    ':pass'    => $passwordHash,
                    ':id'      => $id_usuario
                ];
            } else {
                $sql = "UPDATE usuarios SET id_estatus = :estatus, id_rol = :rol, email_user = :email, actualizado_en = NOW() WHERE id = :id";
                $params = [
                    ':estatus' => $id_estatus,
                    ':rol'     => $id_rol,
                    ':email'   => $emailClean,
                    ':id'      => $id_usuario
                ];
            }

            $this->executeQuery($sql, $params);

            return ["success" => true, "message" => "Usuario actualizado correctamente"];
        } catch (PDOException $e) {
            return $this->formatError("actualizar usuario", $e);
        }
    }

    /**
     * Cambia la contraseña de un usuario mediante su correo.
     */
    public function cambiarPassword(string $email_user, string $password): array
    {
        try {
            $emailClean = strtolower(trim($email_user));

            if (!$this->existsWhere('usuarios', 'email_user = :email', [':email' => $emailClean])) {
                return ["error" => "No se encontró el usuario con ese correo electrónico"];
            }

            $passwordHash = password_hash($password, PASSWORD_BCRYPT);
            $sql = "UPDATE usuarios SET password_hash = :pass, actualizado_en = NOW() WHERE email_user = :email";

            $this->executeQuery($sql, [
                ':pass'  => $passwordHash,
                ':email' => $emailClean
            ]);

            return ["success" => true, "message" => "Contraseña actualizada exitosamente"];
        } catch (PDOException $e) {
            return $this->formatError("cambiar contraseña", $e);
        }
    }

    /**
     * Elimina a un usuario del sistema.
     */
    public function eliminarUsuario(int $id_usuario): array
    {
        try {
            if (!$this->existsWhere('usuarios', 'id = :id', [':id' => $id_usuario])) {
                return ["error" => "No se encontró el usuario en la base de datos"];
            }

            $this->executeQuery("DELETE FROM usuarios WHERE id = :id", [':id' => $id_usuario]);
            return ["success" => true, "message" => "Usuario eliminado correctamente"];
        } catch (PDOException $e) {
            return $this->formatError("eliminar usuario", $e);
        }
    }

    /**
     * Modifica el estado/estatus de un usuario (Activo/Inactivo).
     */
    public function cambiarEstatusUsuario(int $id_usuario, int $nuevo_estatus): array
    {
        try {
            if (!$this->existsWhere('usuarios', 'id = :id', [':id' => $id_usuario])) {
                return ["error" => "No se encontró el usuario en la base de datos"];
            }

            $sql = "UPDATE usuarios SET id_estatus = :estatus, actualizado_en = NOW() WHERE id = :id";
            $this->executeQuery($sql, [
                ':estatus' => $nuevo_estatus,
                ':id'      => $id_usuario
            ]);

            return ["success" => true, "message" => "Estado del usuario actualizado correctamente"];
        } catch (PDOException $e) {
            return $this->formatError("cambiar estatus de usuario", $e);
        }
    }
}