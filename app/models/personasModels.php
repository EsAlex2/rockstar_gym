<?php

require_once __DIR__ . '/models.php';
require_once __DIR__ . '/../traits/ValidatorTrait.php';

use App\Traits\ValidatorTrait;

/**
 * Class PersonasModel
 * Modelo para la gestión integral de personas en el sistema.
 * Extiende de BaseModel e implementa métodos para operaciones de datos personales,
 * validación de unicidad e integridad referencial.
 */
class PersonasModel extends BaseModel
{
    use ValidatorTrait;

    protected string $table = 'personas';

    public function __construct(?PDO $pdo = null)
    {
        parent::__construct($pdo);
    }

    /**
     * Obtiene la lista completa de personas con su género, estatus y vinculación de usuario.
     * @return array
     */
    public function obtenerPersonas(): array
    {
        try {
            $sql = "SELECT 
                        a.id AS id_persona, 
                        a.id_genero,
                        a.id_estatus,
                        b.descripcion AS genero, 
                        c.nombre_estatus AS estatus, 
                        a.cedula_identidad, 
                        a.primer_nombre, 
                        a.segundo_nombre, 
                        a.primer_apellido, 
                        a.segundo_apellido, 
                        a.fecha_nacimiento, 
                        a.telefono, 
                        a.email, 
                        a.direccion_habitacion,
                        u.id AS id_usuario,
                        u.email_user,
                        r.nombre_rol AS rol_usuario
                    FROM personas a 
                    INNER JOIN generos b ON a.id_genero = b.id
                    INNER JOIN estatus c ON a.id_estatus = c.id
                    LEFT JOIN usuarios u ON a.id = u.id_persona
                    LEFT JOIN roles r ON u.id_rol = r.id
                    ORDER BY a.id DESC";

            $resultado = $this->selectAll($sql);
            return $resultado ?: [];
        } catch (PDOException $e) {
            return $this->formatError("obtener personas", $e);
        }
    }

    /**
     * Obtiene los datos detallados de una persona por su ID.
     * @param int $id
     * @return array
     */
    public function obtenerPersonaPorId(int $id): array
    {
        try {
            $sql = "SELECT 
                        a.id AS id_persona, 
                        a.id_genero,
                        a.id_estatus,
                        b.descripcion AS genero, 
                        c.nombre_estatus AS estatus, 
                        a.cedula_identidad, 
                        a.primer_nombre, 
                        a.segundo_nombre, 
                        a.primer_apellido, 
                        a.segundo_apellido, 
                        a.fecha_nacimiento, 
                        a.telefono, 
                        a.email, 
                        a.direccion_habitacion,
                        u.id AS id_usuario,
                        u.email_user,
                        r.nombre_rol AS rol_usuario
                    FROM personas a 
                    INNER JOIN generos b ON a.id_genero = b.id
                    INNER JOIN estatus c ON a.id_estatus = c.id
                    LEFT JOIN usuarios u ON a.id = u.id_persona
                    LEFT JOIN roles r ON u.id_rol = r.id
                    WHERE a.id = :id
                    LIMIT 1";

            $resultado = $this->selectOne($sql, [':id' => $id]);
            return $resultado ?? ["error" => "No se encontró la persona solicitada"];
        } catch (PDOException $e) {
            return $this->formatError("obtener persona por ID", $e);
        }
    }

    /**
     * Busca una persona por su cédula de identidad.
     * @param string $cedula
     * @return array
     */
    public function obtenerPersonaPorCedula(string $cedula): array
    {
        try {
            $cedulaLimpia = trim($cedula);

            if (!$this->existsWhere('personas', 'cedula_identidad = :cedula', [':cedula' => $cedulaLimpia])) {
                return ["error" => "No se encontró persona registrada con la cédula: {$cedulaLimpia}"];
            }

            $sql = "SELECT 
                        a.id AS id_persona, 
                        a.id_genero,
                        a.id_estatus,
                        b.descripcion AS genero, 
                        c.nombre_estatus AS estatus, 
                        a.cedula_identidad, 
                        a.primer_nombre, 
                        a.segundo_nombre, 
                        a.primer_apellido, 
                        a.segundo_apellido, 
                        a.fecha_nacimiento, 
                        a.telefono, 
                        a.email, 
                        a.direccion_habitacion,
                        u.id AS id_usuario,
                        u.email_user,
                        r.nombre_rol AS rol_usuario
                    FROM personas a 
                    INNER JOIN generos b ON a.id_genero = b.id
                    INNER JOIN estatus c ON a.id_estatus = c.id
                    LEFT JOIN usuarios u ON a.id = u.id_persona
                    LEFT JOIN roles r ON u.id_rol = r.id
                    WHERE a.cedula_identidad = :cedula
                    LIMIT 1";

            $resultado = $this->selectOne($sql, [':cedula' => $cedulaLimpia]);
            return $resultado ?? ["error" => "No se pudo recuperar la información de la persona"];
        } catch (PDOException $e) {
            return $this->formatError("buscar persona por cédula", $e);
        }
    }

    /**
     * Registra una nueva persona en la base de datos previa validación de duplicados.
     */
    public function crearPersona(
        int $genero_id,
        string $cedula,
        string $primer_nombre,
        string $segundo_nombre,
        string $primer_apellido,
        string $segundo_apellido,
        string $fecha_nacimiento,
        string $telefono,
        string $correo_electronico,
        string $direccion
    ): array {
        try {
            $cedulaLimpia  = trim($cedula);
            $emailLimpio   = strtolower(trim($correo_electronico));
            $telefonoLimpio = trim($telefono);

            // Validar unicidad de Cédula, Correo y Teléfono
            if ($this->existsWhere('personas', 'cedula_identidad = :cedula', [':cedula' => $cedulaLimpia])) {
                return ["error" => "Ya existe una persona registrada con la cédula {$cedulaLimpia}"];
            }
            if ($this->existsWhere('personas', 'email = :email', [':email' => $emailLimpio])) {
                return ["error" => "El correo electrónico '{$emailLimpio}' ya está registrado"];
            }
            if ($this->existsWhere('personas', 'telefono = :tlf', [':tlf' => $telefonoLimpio])) {
                return ["error" => "El número telefónico '{$telefonoLimpio}' ya está registrado"];
            }

            $pNombre   = $this->sanitizeText($primer_nombre);
            $sNombre   = !empty(trim($segundo_nombre)) ? $this->sanitizeText($segundo_nombre) : null;
            $pApellido = $this->sanitizeText($primer_apellido);
            $sApellido = !empty(trim($segundo_apellido)) ? $this->sanitizeText($segundo_apellido) : null;
            $estatusId = 1; // Activo por defecto

            $sql = "INSERT INTO personas 
                    (id_genero, id_estatus, cedula_identidad, primer_nombre, segundo_nombre, primer_apellido, segundo_apellido, fecha_nacimiento, email, telefono, direccion_habitacion) 
                    VALUES 
                    (:genero_id, :estatus_id, :cedula, :p_nom, :s_nom, :p_ape, :s_ape, :f_nac, :email, :tlf, :dir)";

            $params = [
                ':genero_id'  => $genero_id,
                ':estatus_id' => $estatusId,
                ':cedula'     => $cedulaLimpia,
                ':p_nom'      => $pNombre,
                ':s_nom'      => $sNombre,
                ':p_ape'      => $pApellido,
                ':s_ape'      => $sApellido,
                ':f_nac'      => trim($fecha_nacimiento),
                ':email'      => $emailLimpio,
                ':tlf'        => $telefonoLimpio,
                ':dir'        => trim($direccion)
            ];

            $this->executeQuery($sql, $params);
            $nuevoId = (int)$this->pdo->lastInsertId();

            return [
                "success" => true,
                "message" => "La persona {$pNombre} {$pApellido} (V-{$cedulaLimpia}) se ha registrado exitosamente",
                "data"    => [
                    "id_persona"       => $nuevoId,
                    "cedula_identidad" => $cedulaLimpia,
                    "nombre_completo"  => "{$pNombre} {$pApellido}",
                    "email"            => $emailLimpio
                ]
            ];
        } catch (PDOException $e) {
            return $this->formatError("crear persona", $e);
        }
    }

    /**
     * Actualiza los datos personales de un registro existente por su ID.
     */
    public function actualizarPersona(
        int $id_persona,
        int $genero_id,
        int $estatus_id,
        string $cedula,
        string $primer_nombre,
        string $segundo_nombre,
        string $primer_apellido,
        string $segundo_apellido,
        string $fecha_nacimiento,
        string $telefono,
        string $correo_electronico,
        string $direccion
    ): array {
        try {
            if (!$this->existsWhere('personas', 'id = :id', [':id' => $id_persona])) {
                return ["error" => "No se encontró el registro de la persona en la base de datos"];
            }

            $cedulaLimpia   = trim($cedula);
            $emailLimpio    = strtolower(trim($correo_electronico));
            $telefonoLimpio = trim($telefono);

            // Validar colisiones de datos únicos con otros registros
            if ($this->existsWhere('personas', 'cedula_identidad = :cedula AND id != :id', [':cedula' => $cedulaLimpia, ':id' => $id_persona])) {
                return ["error" => "La cédula {$cedulaLimpia} ya pertenece a otra persona registrada"];
            }
            if ($this->existsWhere('personas', 'email = :email AND id != :id', [':email' => $emailLimpio, ':id' => $id_persona])) {
                return ["error" => "El correo electrónico '{$emailLimpio}' ya pertenece a otra persona"];
            }
            if ($this->existsWhere('personas', 'telefono = :tlf AND id != :id', [':tlf' => $telefonoLimpio, ':id' => $id_persona])) {
                return ["error" => "El teléfono '{$telefonoLimpio}' ya pertenece a otra persona"];
            }

            $pNombre   = $this->sanitizeText($primer_nombre);
            $sNombre   = !empty(trim($segundo_nombre)) ? $this->sanitizeText($segundo_nombre) : null;
            $pApellido = $this->sanitizeText($primer_apellido);
            $sApellido = !empty(trim($segundo_apellido)) ? $this->sanitizeText($segundo_apellido) : null;

            $sql = "UPDATE personas SET 
                        id_genero = :genero_id, 
                        id_estatus = :estatus_id, 
                        cedula_identidad = :cedula,
                        primer_nombre = :p_nom, 
                        segundo_nombre = :s_nom, 
                        primer_apellido = :p_ape, 
                        segundo_apellido = :s_ape, 
                        fecha_nacimiento = :f_nac, 
                        telefono = :tlf, 
                        email = :email, 
                        direccion_habitacion = :dir, 
                        actualizado_en = NOW() 
                    WHERE id = :id";

            $params = [
                ':genero_id'  => $genero_id,
                ':estatus_id' => $estatus_id,
                ':cedula'     => $cedulaLimpia,
                ':p_nom'      => $pNombre,
                ':s_nom'      => $sNombre,
                ':p_ape'      => $pApellido,
                ':s_ape'      => $sApellido,
                ':f_nac'      => trim($fecha_nacimiento),
                ':tlf'        => $telefonoLimpio,
                ':email'      => $emailLimpio,
                ':dir'        => trim($direccion),
                ':id'         => $id_persona
            ];

            $this->executeQuery($sql, $params);

            return [
                "success" => true,
                "message" => "Los datos de {$pNombre} {$pApellido} se han actualizado correctamente"
            ];
        } catch (PDOException $e) {
            return $this->formatError("actualizar persona", $e);
        }
    }

    /**
     * Alterna o asigna el estatus de una persona.
     */
    public function cambiarEstatus(int $id, int $nuevoEstatus): array
    {
        try {
            if (!$this->existsWhere('personas', 'id = :id', [':id' => $id])) {
                return ["error" => "No se encontró la persona especificada"];
            }

            $this->executeQuery("UPDATE personas SET id_estatus = :estatus, actualizado_en = NOW() WHERE id = :id", [
                ':estatus' => $nuevoEstatus,
                ':id'      => $id
            ]);

            return [
                "success" => true,
                "message" => "Estado de la persona actualizado exitosamente"
            ];
        } catch (PDOException $e) {
            return $this->formatError("cambiar estatus persona", $e);
        }
    }

    /**
     * Elimina el registro de una persona o lo inactiva preventivamente si posee dependencias.
     */
    public function eliminarPersona(int $id_persona): array
    {
        try {
            if (!$this->existsWhere('personas', 'id = :id', [':id' => $id_persona])) {
                return ["error" => "No se encontró esta persona en la base de datos"];
            }

            // Verificar si tiene dependencias en usuarios, clientes o entrenadores
            $stmtUser = $this->pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE id_persona = :id");
            $stmtUser->execute([':id' => $id_persona]);
            $hasUsers = (int)$stmtUser->fetchColumn();

            $stmtClient = $this->pdo->prepare("SELECT COUNT(*) FROM clientes WHERE id_persona = :id");
            $stmtClient->execute([':id' => $id_persona]);
            $hasClients = (int)$stmtClient->fetchColumn();

            $stmtTrainer = $this->pdo->prepare("SELECT COUNT(*) FROM entrenadores WHERE id_persona = :id");
            $stmtTrainer->execute([':id' => $id_persona]);
            $hasTrainers = (int)$stmtTrainer->fetchColumn();

            if ($hasUsers > 0 || $hasClients > 0 || $hasTrainers > 0) {
                // Inactivación preventiva para preservar la integridad referencial
                $this->executeQuery("UPDATE personas SET id_estatus = 2, actualizado_en = NOW() WHERE id = :id", [':id' => $id_persona]);
                return [
                    "success"     => true,
                    "inactivated" => true,
                    "message"     => "La persona tiene registros vinculados (usuario/cliente/entrenador). Para resguardar el historial, su estado ha sido cambiado a Inactivo."
                ];
            }

            $this->executeQuery("DELETE FROM personas WHERE id = :id", [':id' => $id_persona]);
            return [
                "success"     => true,
                "inactivated" => false,
                "message"     => "Persona eliminada permanentemente del sistema"
            ];
        } catch (PDOException $e) {
            return $this->formatError("eliminar persona", $e);
        }
    }

    /**
     * Obtiene el listado de géneros disponibles.
     */
    public function obtenerGeneros(): array
    {
        try {
            return $this->selectAll("SELECT id, descripcion FROM generos ORDER BY id ASC") ?: [];
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Obtiene los estatus disponibles para personas.
     */
    public function obtenerEstatus(): array
    {
        try {
            return $this->selectAll("SELECT id, nombre_estatus FROM estatus WHERE id IN (1, 2) ORDER BY id ASC") ?: [];
        } catch (PDOException $e) {
            return [];
        }
    }
}
