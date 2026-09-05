<?php

require_once __DIR__ . '/models.php';
require_once __DIR__ . '/../services/AccessCodeService.php';
require_once __DIR__ . '/../traits/ValidatorTrait.php';

use App\Services\AccessCodeService;
use App\Traits\ValidatorTrait;

/**
 * Class ClientesModel
 * Modelo para la administración de clientes y membresías.
 * Hereda de BaseModel e implementa transacciones atómicas y generación de credenciales de acceso.
 */
class ClientesModel extends BaseModel
{
    use ValidatorTrait;

    protected string $table = 'clientes';
    private AccessCodeService $accessCodeService;

    public function __construct(?PDO $pdo = null, ?AccessCodeService $accessCodeService = null)
    {
        parent::__construct($pdo);
        $this->accessCodeService = $accessCodeService ?? new AccessCodeService();
    }

    /**
     * Genera el código de acceso para una persona.
     * @param int $id_persona
     * @return array
     */
    public function accessCode(int $id_persona): array
    {
        try {
            $sql = "SELECT primer_nombre, segundo_nombre, cedula_identidad FROM personas WHERE id = :id LIMIT 1";
            $persona = $this->selectOne($sql, [':id' => $id_persona]);

            if (!$persona) {
                return ["error" => "La persona no existe en la base de datos"];
            }

            $codeAccess = $this->accessCodeService->generate($persona);
            return ["success" => true, "codigo_acceso" => $codeAccess];
        } catch (PDOException $e) {
            return $this->formatError("generar código de acceso", $e);
        }
    }

    /**
     * Registra a un cliente a partir de una persona existente y le asigna su código de acceso.
     * @param int $id_persona
     * @return array
     */
    public function crearClientes(int $id_persona): array
    {
        try {
            if (!$this->existsWhere('personas', 'id = :id', [':id' => $id_persona])) {
                return ["error" => "La persona no existe en nuestra base de datos"];
            }

            $accessResult = $this->accessCode($id_persona);
            if (isset($accessResult['error'])) {
                return ["error" => $accessResult['error']];
            }

            $codigoGenerado = $accessResult['codigo_acceso'];

            if ($this->existsWhere('clientes', 'id_persona = :id_p AND codigo_acceso = :code', [':id_p' => $id_persona, ':code' => $codigoGenerado])) {
                return ["error" => "Esta persona ya se encuentra registrada con ese código de acceso"];
            }

            $estatusActivo = 1;
            $fechaHoy = date('Y-m-d');

            $sql = "INSERT INTO clientes (id_estatus, id_persona, codigo_acceso, fecha_inscripcion) 
                    VALUES (:id_estatus, :id_persona, :codigo_acceso, :fecha_inscripcion)";

            $this->executeQuery($sql, [
                ':id_estatus'         => $estatusActivo,
                ':id_persona'         => $id_persona,
                ':codigo_acceso'      => $codigoGenerado,
                ':fecha_inscripcion'  => $fechaHoy
            ]);

            return [
                "success" => true,
                "message" => "Cliente creado exitosamente",
                "data"    => [
                    "id_cliente"           => (int)$this->pdo->lastInsertId(),
                    "codigo_acceso"        => $codigoGenerado,
                    "Fecha de Inscripcion" => $fechaHoy
                ]
            ];
        } catch (PDOException $e) {
            return $this->formatError("crear cliente", $e);
        }
    }

    /**
     * Lista todos los clientes registrados junto con sus datos personales.
     * @return array
     */
    public function listarClientes(): array
    {
        try {
            $sql = "SELECT 
                        a.id, 
                        a.id_estatus,
                        a.id_persona,
                        b.nombre_estatus AS Estatus, 
                        c.primer_nombre, 
                        c.segundo_nombre, 
                        c.primer_apellido, 
                        c.segundo_apellido, 
                        CONCAT(c.primer_nombre, ' ', c.primer_apellido) AS Cliente, 
                        c.cedula_identidad, 
                        c.id_genero, 
                        c.fecha_nacimiento, 
                        c.email, 
                        c.telefono, 
                        c.direccion_habitacion, 
                        a.fecha_inscripcion, 
                        a.codigo_acceso 
                    FROM clientes a
                    INNER JOIN estatus b ON a.id_estatus = b.id
                    INNER JOIN personas c ON a.id_persona = c.id
                    ORDER BY a.id DESC";

            $resultado = $this->selectAll($sql);
            return empty($resultado) ? ["error" => "No hay usuarios registrados"] : $resultado;
        } catch (PDOException $e) {
            return $this->formatError("listar clientes", $e);
        }
    }

    /**
     * Actualiza el estatus de un cliente.
     */
    public function actualizarCliente(int $id_cliente, int $id_estatus): array
    {
        try {
            if (!$this->existsWhere('clientes', 'id = :id', [':id' => $id_cliente])) {
                return ["error" => "No se encontró el cliente en la base de datos"];
            }

            $sql = "UPDATE clientes SET id_estatus = :estatus, actualizado_en = NOW() WHERE id = :id";
            $this->executeQuery($sql, [':estatus' => $id_estatus, ':id' => $id_cliente]);

            return ["success" => true, "message" => "Cliente actualizado correctamente"];
        } catch (PDOException $e) {
            return $this->formatError("actualizar cliente", $e);
        }
    }

    /**
     * Elimina a un cliente y sus registros dependientes de forma segura.
     */
    public function eliminarCliente(int $id_cliente): array
    {
        try {
            $personaId = $this->selectOne("SELECT id_persona FROM clientes WHERE id = :id", [':id' => $id_cliente]);

            if ($personaId && isset($personaId['id_persona'])) {
                $this->executeQuery("DELETE FROM personas WHERE id = :id", [':id' => (int)$personaId['id_persona']]);
            } else {
                $this->executeQuery("DELETE FROM clientes WHERE id = :id", [':id' => $id_cliente]);
            }

            return ["success" => true, "message" => "Cliente eliminado correctamente"];
        } catch (PDOException $e) {
            return $this->formatError("eliminar cliente", $e);
        }
    }

    /**
     * Crea un cliente completo en una sola transacción ACID (Persona + Cliente).
     */
    public function crearClienteDirecto(
        int $id_genero,
        string $cedula_identidad,
        string $primer_nombre,
        ?string $segundo_nombre,
        string $primer_apellido,
        ?string $segundo_apellido,
        string $fecha_nacimiento,
        string $telefono,
        string $email,
        string $direccion_habitacion
    ): array {
        return $this->transaction(function (PDO $pdo) use (
            $id_genero,
            $cedula_identidad,
            $primer_nombre,
            $segundo_nombre,
            $primer_apellido,
            $segundo_apellido,
            $fecha_nacimiento,
            $telefono,
            $email,
            $direccion_habitacion
        ) {
            $cedula = trim($cedula_identidad);
            $correo = strtolower(trim($email));
            $tlf    = trim($telefono);

            // Validar unicidad
            if ($this->existsWhere('personas', 'cedula_identidad = :cedula', [':cedula' => $cedula])) {
                throw new PDOException("La persona con cédula {$cedula} ya existe en la base de datos.");
            }
            if ($this->existsWhere('personas', 'email = :email', [':email' => $correo])) {
                throw new PDOException("El correo electrónico {$correo} ya está registrado.");
            }
            if ($this->existsWhere('personas', 'telefono = :tlf', [':tlf' => $tlf])) {
                throw new PDOException("El teléfono {$tlf} ya está registrado.");
            }

            $pNom = $this->sanitizeText($primer_nombre);
            $sNom = $segundo_nombre !== null ? $this->sanitizeText($segundo_nombre) : '';
            $pApe = $this->sanitizeText($primer_apellido);
            $sApe = $segundo_apellido !== null ? $this->sanitizeText($segundo_apellido) : '';

            $sqlPersona = "INSERT INTO personas 
                           (id_genero, id_estatus, cedula_identidad, primer_nombre, segundo_nombre, primer_apellido, segundo_apellido, fecha_nacimiento, email, telefono, direccion_habitacion) 
                           VALUES 
                           (:genero, 1, :cedula, :p_nom, :s_nom, :p_ape, :s_ape, :f_nac, :email, :tlf, :dir)";

            $stmtP = $pdo->prepare($sqlPersona);
            $stmtP->execute([
                ':genero' => $id_genero,
                ':cedula' => $cedula,
                ':p_nom'  => $pNom,
                ':s_nom'  => $sNom,
                ':p_ape'  => $pApe,
                ':s_ape'  => $sApe,
                ':f_nac'  => trim($fecha_nacimiento),
                ':email'  => $correo,
                ':tlf'    => $tlf,
                ':dir'    => trim($direccion_habitacion)
            ]);

            $idPersona = (int)$pdo->lastInsertId();

            $codigoGenerado = $this->accessCodeService->generate([
                'primer_nombre'    => $pNom,
                'segundo_nombre'   => $sNom,
                'cedula_identidad' => $cedula
            ]);

            $fechaHoy = date('Y-m-d');
            $sqlCliente = "INSERT INTO clientes (id_estatus, id_persona, codigo_acceso, fecha_inscripcion) 
                           VALUES (1, :id_persona, :code, :fecha)";

            $stmtC = $pdo->prepare($sqlCliente);
            $stmtC->execute([
                ':id_persona' => $idPersona,
                ':code'       => $codigoGenerado,
                ':fecha'      => $fechaHoy
            ]);

            return [
                "success" => true,
                "message" => "Cliente inscrito exitosamente",
                "data"    => [
                    "id_cliente"        => (int)$pdo->lastInsertId(),
                    "codigo_acceso"     => $codigoGenerado,
                    "fecha_inscripcion" => $fechaHoy
                ]
            ];
        });
    }

    /**
     * Actualiza integralmente los datos del cliente y su información personal.
     */
    public function actualizarClienteCompleto(
        int $id_cliente,
        int $id_estatus,
        int $id_genero,
        string $cedula_identidad,
        string $primer_nombre,
        ?string $segundo_nombre,
        string $primer_apellido,
        ?string $segundo_apellido,
        string $fecha_nacimiento,
        string $telefono,
        string $email,
        string $direccion_habitacion
    ): array {
        return $this->transaction(function (PDO $pdo) use (
            $id_cliente,
            $id_estatus,
            $id_genero,
            $cedula_identidad,
            $primer_nombre,
            $segundo_nombre,
            $primer_apellido,
            $segundo_apellido,
            $fecha_nacimiento,
            $telefono,
            $email,
            $direccion_habitacion
        ) {
            $personaData = $this->selectOne("SELECT id_persona FROM clientes WHERE id = :id", [':id' => $id_cliente]);
            if (!$personaData || !isset($personaData['id_persona'])) {
                throw new PDOException("No se encontró la persona vinculada a este cliente.");
            }

            $idPersona = (int)$personaData['id_persona'];
            $cedula    = trim($cedula_identidad);
            $correo    = strtolower(trim($email));
            $tlf       = trim($telefono);

            if ($this->existsWhere('personas', 'cedula_identidad = :cedula AND id != :id', [':cedula' => $cedula, ':id' => $idPersona])) {
                throw new PDOException("La cédula {$cedula} ya pertenece a otra persona registrada.");
            }
            if ($this->existsWhere('personas', 'email = :email AND id != :id', [':email' => $correo, ':id' => $idPersona])) {
                throw new PDOException("El correo electrónico {$correo} ya está asignado a otra persona.");
            }
            if ($this->existsWhere('personas', 'telefono = :tlf AND id != :id', [':tlf' => $tlf, ':id' => $idPersona])) {
                throw new PDOException("El teléfono {$tlf} ya está registrado.");
            }

            $pNom = $this->sanitizeText($primer_nombre);
            $sNom = $segundo_nombre !== null ? $this->sanitizeText($segundo_nombre) : '';
            $pApe = $this->sanitizeText($primer_apellido);
            $sApe = $segundo_apellido !== null ? $this->sanitizeText($segundo_apellido) : '';

            $sqlPersona = "UPDATE personas SET 
                                id_genero = :genero, 
                                cedula_identidad = :cedula, 
                                primer_nombre = :p_nom, 
                                segundo_nombre = :s_nom, 
                                primer_apellido = :p_ape, 
                                segundo_apellido = :s_ape, 
                                fecha_nacimiento = :f_nac, 
                                email = :email, 
                                telefono = :tlf, 
                                direccion_habitacion = :dir, 
                                actualizado_en = NOW() 
                           WHERE id = :id";

            $stmtP = $pdo->prepare($sqlPersona);
            $stmtP->execute([
                ':genero' => $id_genero,
                ':cedula' => $cedula,
                ':p_nom'  => $pNom,
                ':s_nom'  => $sNom,
                ':p_ape'  => $pApe,
                ':s_ape'  => $sApe,
                ':f_nac'  => trim($fecha_nacimiento),
                ':email'  => $correo,
                ':tlf'    => $tlf,
                ':dir'    => trim($direccion_habitacion),
                ':id'     => $idPersona
            ]);

            $sqlCliente = "UPDATE clientes SET id_estatus = :estatus, actualizado_en = NOW() WHERE id = :id";
            $stmtC = $pdo->prepare($sqlCliente);
            $stmtC->execute([':estatus' => $id_estatus, ':id' => $id_cliente]);

            return ["success" => true, "message" => "Cliente y datos personales actualizados exitosamente"];
        });
    }
}