<?php

require_once __DIR__ . '/models.php';
require_once __DIR__ . '/../core/conn.php';

class usuariosModels extends Model
{
    protected $pdo;
    protected int $estatus_id;
    protected int $persona_id;
    protected int $rol_id;
    protected string $username;
    protected string $cedula_identidad;
    protected string $email;
    protected string $password;
    protected array $mensajes = [];

    public function __construct($pdo)
    {
        parent::__construct($pdo);
        $this->pdo = $pdo;
    }

    public function obtenerUsuarios()
    {
        try {
            if (!$this->pdo) {
                return ["error" => "Error de conexión a la base de datos"];
            }

            $sql = $this->pdo->prepare("SELECT id_estatus, id_persona, id_rol, username, email_user FROM administracion.usuarios");
            $sql->execute();
            $resultado = $sql->fetchAll(PDO::FETCH_ASSOC);

            return empty($resultado) ? ["error" => "No hay usuarios registrados"] : $resultado;
        } catch (PDOException $e) {
            return ["error" => "Error al obtener usuarios: " . $e->getMessage()];
        }
    }

    public function obtenerUsuariosPorUsername(string $username)
    {
        try {
            if (!$this->pdo) {
                return ["error" => "Error de conexión a la base de datos"];
            }

            $query = $this->pdo->prepare("SELECT id_estatus, id_persona, id_rol, username, email_user 
                FROM administracion.usuarios 
                WHERE username = :username");
            $query->bindParam(':username', $username);
            $query->execute();

            $resultado = $query->fetch(PDO::FETCH_ASSOC);

            return empty($resultado) ? ["error" => "No hay registros con ese nombre de usuario {$username}"] : $resultado;
        } catch (PDOException $e) {
            return ["error" => "Error al buscar usuario: " . $e->getMessage()];
        }
    }

    /**
     * Genera username único basado en: primer_apellido + inicial_primer_nombre + últimos 3 dígitos de la cédula
     */
    public function CreacionDeUsername(int $persona_id)
    {
        try {
            if (!$this->pdo) {
                return ["error" => "Error de conexión a la base de datos"];
            }

            $query = $this->pdo->prepare("SELECT primer_nombre, primer_apellido, cedula_identidad FROM administracion.personas WHERE id = :persona_id");
            $query->bindParam(':persona_id', $persona_id, PDO::PARAM_INT);
            $query->execute();
            $persona = $query->fetch(PDO::FETCH_ASSOC);

            if (!$persona) {
                return ["error" => "La persona no existe en la base de datos"];
            }

            $apellido = strtolower(trim($persona['primer_apellido']));
            $nombre = strtolower(trim($persona['primer_nombre']));
            $inicial = mb_substr($nombre, 0, 1, 'UTF-8');
            $cedula_limpia = preg_replace('/[^0-9]/', '', $persona['cedula_identidad']);
            $ultimos_tres = substr($cedula_limpia, -3);

            $username_base = $apellido . $inicial . $ultimos_tres;
            $username = $username_base;
            $contador = 1;

            // Verificar unicidad del username
            while (true) {
                $checkQuery = $this->pdo->prepare("SELECT COUNT(*) FROM administracion.usuarios WHERE username = :username");
                $checkQuery->bindParam(':username', $username);
                $checkQuery->execute();

                if ($checkQuery->fetchColumn() == 0) {
                    break;
                }

                $username = $username_base . $contador;
                $contador++;
            }

            return ["success" => true, "username" => $username];
        } catch (PDOException $e) {
            return ["error" => "Error al generar username: " . $e->getMessage()];
        }
    }

    /**
     * Verifica si una persona ya tiene un usuario asociado
     */
    public function personaYaTieneUsuario(int $persona_id): bool
    {
        try {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM administracion.usuarios WHERE id_persona = :persona_id");
            $stmt->bindParam(':persona_id', $persona_id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Verifica si el email ya está registrado
     */
    public function emailYaRegistrado(string $email): bool
    {
        try {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM administracion.usuarios WHERE email_user = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function crearUsuarios(int $persona_id, int $rol_id, string $email, string $password = "Cliente2026**")
    {
        try {
            if (!$this->pdo) {
                return ["error" => "Error de conexión a la base de datos"];
            }

            // VALIDACIÓN 1: Verificar que la persona existe
            $checkPersona = $this->pdo->prepare("SELECT COUNT(*) FROM administracion.personas WHERE id = :persona_id");
            $checkPersona->bindParam(':persona_id', $persona_id, PDO::PARAM_INT);
            $checkPersona->execute();

            if ($checkPersona->fetchColumn() == 0) {
                return ["error" => "La persona no existe en nuestra base de datos"];
            }

            if ($this->personaYaTieneUsuario($persona_id)) {
                return ["error" => "La persona ya tiene un usuario registrado"];
            }

            if ($this->emailYaRegistrado($email)) {
                return ["error" => "El correo electrónico ya está registrado por otro usuario"];
            }

            $checkRol = $this->pdo->prepare("SELECT COUNT(*) FROM administracion.roles WHERE id = :rol_id");
            $checkRol->bindParam(':rol_id', $rol_id, PDO::PARAM_INT);
            $checkRol->execute();

            if ($checkRol->fetchColumn() == 0) {
                return ["error" => "El rol no existe en el sistema"];
            }

            $usernameResult = $this->CreacionDeUsername($persona_id);

            if (isset($usernameResult['error'])) {
                return ["error" => $usernameResult['error']];
            }

            $username_generado = $usernameResult['username'];

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return ["error" => "El correo electrónico no tiene un formato válido"];
            }

            $password_hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $this->pdo->prepare("INSERT INTO administracion.usuarios (id_estatus, id_persona, id_rol, username, email_user, password_hash) 
                VALUES (:id_estatus, :id_persona, :id_rol, :username, :email_user, :password_hash)");

            $estatus_activo = 1;
            $stmt->bindParam(':id_estatus', $estatus_activo, PDO::PARAM_INT);
            $stmt->bindParam(':id_persona', $persona_id, PDO::PARAM_INT);
            $stmt->bindParam(':id_rol', $rol_id, PDO::PARAM_INT);
            $stmt->bindParam(':username', $username_generado);
            $stmt->bindParam(':email_user', $email);
            $stmt->bindParam(':password_hash', $password_hash);

            $stmt->execute();

            return [
                "success" => true,
                "message" => "Usuario creado exitosamente",
                "data" => [
                    "id_rol" => $rol_id,
                    "username" => $username_generado,
                    "email" => $email,
                    "password" => $password
                ]
            ];
        } catch (PDOException $e) {
            if ($e->getCode() == 23000 || strpos($e->getMessage(), 'Duplicate entry') !== false) {
                if (strpos($e->getMessage(), 'email_user') !== false) {
                    return ["error" => "El correo electrónico ya está registrado"];
                }
                if (strpos($e->getMessage(), 'username') !== false) {
                    return ["error" => "El nombre de usuario ya existe"];
                }
                if (strpos($e->getMessage(), 'id_persona') !== false) {
                    return ["error" => "La persona ya tiene un usuario asignado"];
                }
            }
            return ["error" => "Error al crear usuario: " . $e->getMessage()];
        }
    }

    public function actualizarUsername(int $id_usuario, int $estatus_id, int $rol_id, string $username, string $email)
    {
        try {
            if (!$this->pdo) {
                return ["error" => "Error de conexión a la base de datos"];
            }

            $checkUser = $this->pdo->prepare("SELECT COUNT(*) FROM administracion.usuarios WHERE id_usuario = :id_usuario");
            $checkUser->bindParam(':id_usuario', $id_usuario);
            $checkUser->execute();

            if ($checkUser->fetchColumn() == 0) {
                return ["error" => "No se encontró usuario con username: {$username}"];
            }

            $checkRoles = $this->pdo->prepare("SELECT COUNT(*) FROM administracion.roles WHERE id = :id_rol");
            $checkRoles->bindParam(':id_rol', $rol_id);
            $checkRoles->execute();

            if ($checkRoles->fetchColumn() == 0) {
                return ["error" => "El rol especificado no existe en la base de datos, contacte a Soporte!"];
            }

            $updateUsers = $this->pdo->prepare("UPDATE administracion.usuarios 
                SET id_estatus = :estatus, id_rol = :rol, email_user = :email, actualizado_en = NOW()
                WHERE id_usuario = :id_usuario");
                   
            $updateUsers->bindParam(":estatus", $estatus_id, PDO::PARAM_INT);
            $updateUsers->bindParam(":rol", $rol_id, PDO::PARAM_INT);
            $updateUsers->bindParam(":email", $email);
            $updateUsers->bindParam(":username", $username);
            $updateUsers->execute();

            return ["success" => true, "message" => "Usuario actualizado exitosamente"];
        } catch (PDOException $e) {
            return ["error" => "Error al actualizar usuario: " . $e->getMessage()];
        }
    }

    public function cambiarContraseña(string $username, string $email, string $password)
    {

        try {
            if (!$this->pdo) {
                return ["error" => "Error de conexión a la base de datos"];
            }

            $checkUser = $this->pdo->prepare("SELECT COUNT(*) FROM administracion.usuarios WHERE username = :username");
            $checkUser->bindParam(':username', $username);
            $checkUser->execute();

            if ($checkUser->fetchColumn() == 0) {
                return ["error" => "No se encontró el nombre de usuario: {$username}"];
            }

            $checkEmail = $this->pdo->prepare("SELECT COUNT(*) FROM administracion.usuarios WHERE email_user = :email");
            $checkEmail->bindParam(':email', $email);
            $checkEmail->execute();

            if ($checkEmail->fetchColumn() == 0) {
                return ["error" => "No se encontró el usuario con el correo: {$email}"];
            }

            $password_hash = password_hash($password, PASSWORD_BCRYPT);

            $updatePassword = $this->pdo->prepare("UPDATE administracion.usuarios 
                SET password_hash = :pass, actualizado_en = NOW()
                WHERE username = :username");

            $updatePassword->bindParam(":username", $username);
            $updatePassword->bindParam(":pass", $password_hash);
            $updatePassword->execute();

            return ["success" => true, "message" => "Contraseña actualizada exitosamente"];

        } catch (PDOException $e) {
            return ["error" => "Error al actualizar usuario: " . $e->getMessage()];
        }
    }
}
