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
        $this->mensajes = [
            "Error de conexion a la base de datos",
            "Error inesperado al obtener los clientes"
        ];

        try {

            if (!$this->pdo) {
                return $this->mensajes[0];
            }

            $sql = $this->pdo->prepare("SELECT id_estatus, id_persona, id_rol, username, email_user FROM administracion.usuarios");
            $sql->execute();

            return json_encode([
                "success" => $sql->fetchAll(PDO::FETCH_ASSOC)
            ]);

        } catch (PDOException $e) {
            return json_encode([
                "error" => $this->mensajes[1] . $e->getMessage()
            ]);
        }
    }

    public function obtenerUsuriosPorUsername(string $username)
    {
        $this->mensajes = [
            "Error de conexcion a la base de datos",
            "No se encontro usuario asociado con el username: {username} en la base de datos.",
            "Usuario Encontrado exitosamente"
        ];

        try {
            if (!$this->pdo) {
                return $this->mensajes[0];
            }

            $checkUser = $this->pdo->prepare("SELECT COUNT(*) FROM administracion.usuario WHERE username = :username");
            $checkUser->bindParam(':username', $username);
            $checkUser->execute();

            if ($checkUser->fetchColum() == 0) {
                return str_replace("{username}", $username, $this->mensajes[1]);
            }

            $query = $this->pdo->prepare("SELECT id_estatus, id_persona, id_rol, username, email_user 
            FROM administracion.usuarios 
            WHERE username = :username");

            $query->bindParam(':username', $username);
            $query->execute();

            return [
                "message" => $this->mensajes[2],
                "success" => json_encode($query->fetch(PDO::FETCH_ASSOC))
            ];

        } catch (PDOException $e) {
            return ["error" => $this->mensajes[1] . $e->getMessage()];
        }
    }

    /**
     * Genera automáticamente un username único basado en:
     * primer_apellido + inicial_primer_nombre + últimos 3 dígitos de la cédula.
     */
    public function CreacionDeUsername(int $persona_id)
    {

        $this->mensajes= [
            "Error de conexión a la base de datos",
            "No se encontró la persona asociada para generar el username",
            "Error al generar el username: "
        ];

        try {
            if (!$this->pdo) {
                return $this->mensajes[0];
            }

            $query = $this->pdo->prepare("SELECT primer_nombre, primer_apellido, cedula_identidad FROM administracion.personas WHERE id = :persona_id");
            $query->bindParam(':persona_id', $persona_id, PDO::PARAM_INT);
            $query->execute();
            $persona = $query->fetch(PDO::FETCH_ASSOC);

            if (!$persona) {
                return $this->mensajes[1];
            }

            $apellido = strtolower(trim($persona['primer_apellido']));
            $nombre = strtolower(trim($persona['primer_nombre']));

            $inicial = mb_substr($nombre, 0, 1, 'UTF-8');

            $cedula_limpia = preg_replace('/[^0-9]/', '', $persona['cedula_identidad']);
            $ultimos_tres = substr($cedula_limpia, -3);

            $username_base = $apellido . $inicial . $ultimos_tres;
            $username = $username_base;
            $contador = 1;

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
            return ["error" => $this->mensajes[2] . $e->getMessage()];
        }
    }

    public function crearUsuarios(int $persona_id, int $rol_id, string $email, string $password = "Cliente2026**")
    {
        $this->mensajes = [
            "Error de conexion a la base de datos",
            "Error inesperado al crear el usuario {username}",
            "El usuario {username} se ha creado exitosamente"
        ];

        try {
            if (!$this->pdo) {
                return $this->mensajes[0];
            }

            $resultadoUsername = $this->CreacionDeUsername($persona_id);

            if (isset($resultadoUsername['error'])) {
                return $resultadoUsername['error'];
            }

            $username_generado = $resultadoUsername['username'];
            $password_hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $this->pdo->prepare("INSERT INTO administracion.usuarios (id_estatus, id_persona, id_rol, username, email_user, password_hash) VALUES (:id_estatus, :id_persona, :id_rol, :username, :email_user, :password_hash)");

            $estatus_activo = 1;
            $stmt->bindParam(':id_estatus', $estatus_activo);
            $stmt->bindParam(':id_persona', $persona_id);
            $stmt->bindParam(':id_rol', $rol_id);
            $stmt->bindParam(':username', $username_generado);
            $stmt->bindParam(':email_user', $email);
            $stmt->bindParam(':password_hash', $password_hash);

            $stmt->execute();

            return [
                "success" => json_encode(str_replace("{username}", $username_generado, $this->mensajes[2]))
            ];
        } catch (PDOException $e) {
            return json_encode([
                "error" => str_replace("{username}", $username_generado ?? 'desconocido', $this->mensajes[1]) . ": " . $e->getMessage()
            ]);
        }
    }

    public function actualizarUsername(int $estatus_id, int $rol_id, string $username, string $email)
    {
        $this->mensajes = [
            "Error de conexion a la base de datos",
            "No se encontro usuario asociado a ese username: {username}",
            "Error inesperado para actualizar al usuario: {username}", 
            "Se ha actualizado el usuario Exitosamente!"
        ];

        try {

            if(!$this->pdo){
                return $this->mensajes[0];
            }

            $chekUserUpdate = $this->pdo->prepare("SELECT COUNT(*) FROM administracion.usuarios WHERE username = :username");
            $chekUserUpdate->bindParam(':username', $username);
            $chekUserUpdate->execute();

            if($chekUserUpdate->fetchColumn() == 0){
                return str_replace("{username}", $username, $this->mensajes[1]);
            }

            $updateUsers = $this->pdo->prepare("UPDATE administracion.usuarios 
            SET id_estatus = :estatus, id_rol = :rol, username = :username, email_user = :email 
            WHERE username = :username");

            $updateUsers->bindParam(":estatus", $estatus_id);
            $updateUsers->bindParam(":rol", $rol_id);
            $updateUsers->bindParam("username", $username);
            $updateUsers->bindParam(":email", $email);
            $updateUsers->execute();

            return json_encode([
                "success" => $this->mensajes[3]
            ]);
        } catch (PDOException $e){
            return json_encode([
                "error" => $this->mensajes[2] . $e->getMessage()
            ]);
        }
    }
}