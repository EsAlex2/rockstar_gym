<?php
require_once __DIR__ . '/models.php';
require_once __DIR__ . '/../core/conn.php';

/* =================================================================================
 * clientesModel.php
 * Modelo para la gestión de clientes del gimnasio en el sistema de administración.
 * Autor: Alex Madrid
 * ==============================================================================
 */

class ClientesModel extends Model
{
    protected $pdo;

    public function __construct($pdo)
    {
        parent::__construct($pdo);
        $this->pdo = $pdo;
    }

    public function accessCode(int $id_persona)
    {

        try {
            if (!$this->pdo) {
                return ["error" => "Error de conexión a la base de datos"];
            }

            $query = $this->pdo->prepare("SELECT * FROM personas WHERE id = :persona_id");
            $query->bindParam(':persona_id', $id_persona, PDO::PARAM_INT);
            $query->execute();
            $persona = $query->fetch(PDO::FETCH_ASSOC);

            if (!$persona) {
                return ["error" => "La persona no existe en la base de datos"];
            }

            $primerNombre = strtoupper(trim($persona['primer_nombre'])); //ubicamos el primer nombre de la persona en la base de datos
            $inicial = mb_substr($primerNombre, 0, 1, 'UTF-8'); //substraemos la inicial del 1er nombre de la persona 

            $cedula_limpia = preg_replace('/[^0-9]/', '', $persona['cedula_identidad']); //limpiamos la cedula en caso de que traiga guion, comas, puntos
            $primeros2 = substr($cedula_limpia, 0, 2); //substraemos los 2 primeros digitos de la cedula
            $priemros3 = substr($cedula_limpia, 2, 3); //substraemos los 3 primeros digitos de la cedula

            $segundoNombre = strtoupper(trim($persona['segundo_nombre'])); //ubicamos el segundo nombre de la persona en la base de datos
            $inicial2 = mb_substr($segundoNombre, 0, 1, 'UTF-8'); //substraemos la inicial del 2do nombre de la persona 

            $base = $inicial . $primeros2 . $inicial2 . $priemros3; //concatenamos y armamos el codigo de acceso del cliente
            $codeAccess = $base;

            return ["success" => true, "codigo_acceso" => $codeAccess];

        } catch (PDOException $e) {
            return ["Error" => "Error al generar codigo de acceso" . $e->getMessage()];
        }
    }

    public function crearClientes(int $id_persona)
    {

        try {
            if (!$this->pdo) {
                return ["error" => "Error de conexión a la base de datos"];
            }

            //Validacion, consulta si existe la persona
            $checkPersona = $this->pdo->prepare("SELECT COUNT(*) FROM personas WHERE id = :persona_id");
            $checkPersona->bindParam(':persona_id', $id_persona, PDO::PARAM_INT);
            $checkPersona->execute();

            if ($checkPersona->fetchColumn() == 0) {
                return ["error" => "La persona no existe en nuestra base de datos"];
            }

            //ubicamos el codigo de accesos con la funcion creada anteriormente
            $accessResult = $this->accessCode($id_persona);

            if (isset($accessResult['error'])) {
                return ["error" => $accessResult['error']];
            }

            $codigoGenerado = $accessResult['codigo_acceso'];

            $checkAccesDuplicate = $this->pdo->prepare("SELECT COUNT(*) FROM clientes WHERE id_persona = :id_persona AND codigo_acceso = :codigo_acceso");
            $checkAccesDuplicate->bindParam(':id_persona', $id_persona, PDO::PARAM_INT);
            $checkAccesDuplicate->bindParam(':codigo_acceso', $codigoGenerado, PDO::PARAM_STR);
            $checkAccesDuplicate->execute();

            if ($checkAccesDuplicate->fetchColumn() > 0) {
                return ["error" => "Esta persona ya se encuentra registrada con ese codigo de acceso"];
            }

            $estatus_activo = 1;
            $fecha_hoy = date('Y-m-d');
            
            $insert = $this->pdo->prepare("INSERT INTO clientes (id_estatus, id_persona, codigo_acceso, fecha_inscripcion)
            VALUES (:id_estatus, :id_persona, :codigo_acceso, :fecha_inscripcion)");

            $insert->bindParam(':id_estatus', $estatus_activo, PDO::PARAM_INT);
            $insert->bindParam(':id_persona', $id_persona, PDO::PARAM_INT);
            $insert->bindParam(':codigo_acceso', $codigoGenerado, PDO::PARAM_STR);
            $insert->bindParam(':fecha_inscripcion', $fecha_hoy);

            $insert->execute();

            return [
                "success" => true,
                "message" => "Cliente creado exitosamente",
                "data" => [
                    "codigo_acceso" => $codigoGenerado, 
                    "Fecha de Inscripcion" => $fecha_hoy
                ]
            ];

        } catch (PDOException $e) {
            return ["error" => "Error al crear el cliente en nuestra base de datos" . $e->getMessage()];
        }
    }



    public function listarClientes()
    {
        try {
            if (!$this->pdo) {
                return ["error" => "Error de conexión a la base de datos"];
            }

            // Seleccionamos también todos los campos básicos de la persona
            $sql = $this->pdo->prepare("SELECT 
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
            INNER JOIN personas c ON a.id_persona = c.id");

            $sql->execute();
            $resultado = $sql->fetchAll(PDO::FETCH_ASSOC);

            return empty($resultado) ? ["error" => "No hay usuarios registrados"] : $resultado;
        } catch (PDOException $e) {
            return ["error" => "Error al listar clientes: " . $e->getMessage()];
        }
    }

    public function actualizarCliente(int $id_cliente, int $id_estatus)
    {
        try {
            if (!$this->pdo) {
                return ["error" => "Error de conexión a la base de datos"];
            }

            $checkStmt = $this->pdo->prepare("SELECT COUNT(*) FROM clientes WHERE id = :id");
            $checkStmt->bindParam(':id', $id_cliente, PDO::PARAM_INT);
            $checkStmt->execute();

            if ($checkStmt->fetchColumn() == 0) {
                return ["error" => "No se encontró el cliente en la base de datos"];
            }

            $stmt = $this->pdo->prepare("UPDATE clientes SET id_estatus = :id_estatus, actualizado_en = NOW() WHERE id = :id");
            $stmt->bindParam(':id', $id_cliente, PDO::PARAM_INT);
            $stmt->bindParam(':id_estatus', $id_estatus, PDO::PARAM_INT);
            $stmt->execute();

            return ["success" => true, "message" => "Cliente actualizado correctamente"];
        } catch (PDOException $e) {
            return ["error" => "Error al actualizar el cliente: " . $e->getMessage()];
        }
    }

    public function eliminarCliente(int $id_cliente)
    {
        try {
            if (!$this->pdo) {
                return ["error" => "Error de conexión a la base de datos"];
            }

            $stmtGet = $this->pdo->prepare("SELECT id_persona FROM clientes WHERE id = :id");
            $stmtGet->bindParam(':id', $id_cliente, PDO::PARAM_INT);
            $stmtGet->execute();
            $id_persona = $stmtGet->fetchColumn();

            if ($id_persona) {
                // Borramos la persona, lo cual causa el borrado en cascada del cliente y sus dependencias
                $stmt = $this->pdo->prepare("DELETE FROM personas WHERE id = :id");
                $stmt->bindParam(':id', $id_persona, PDO::PARAM_INT);
                $stmt->execute();
            } else {
                // Fallback en caso de que no tenga persona asociada
                $stmt = $this->pdo->prepare("DELETE FROM clientes WHERE id = :id");
                $stmt->bindParam(':id', $id_cliente, PDO::PARAM_INT);
                $stmt->execute();
            }

            return ["success" => true, "message" => "Cliente eliminado correctamente"];
        } catch (PDOException $e) {
            return ["error" => "Error al eliminar el cliente: " . $e->getMessage()];
        }
    }

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
    ) {
        try {
            if (!$this->pdo) {
                return ["error" => "Error de conexión a la base de datos"];
            }

            $this->pdo->beginTransaction();

            // 1. Validar que la persona no exista
            $checkPersona = $this->pdo->prepare("SELECT COUNT(*) FROM personas WHERE cedula_identidad = :cedula");
            $checkPersona->execute([':cedula' => trim($cedula_identidad)]);
            if ($checkPersona->fetchColumn() > 0) {
                $this->pdo->rollBack();
                return ["error" => "La persona con cédula " . $cedula_identidad . " ya existe en la base de datos"];
            }

            // 2. Validar duplicado de email/telefono
            $checkEmail = $this->pdo->prepare("SELECT COUNT(*) FROM personas WHERE email = :email");
            $checkEmail->execute([':email' => trim($email)]);
            if ($checkEmail->fetchColumn() > 0) {
                $this->pdo->rollBack();
                return ["error" => "El correo electrónico " . $email . " ya está registrado"];
            }

            $checkTlf = $this->pdo->prepare("SELECT COUNT(*) FROM personas WHERE telefono = :telefono");
            $checkTlf->execute([':telefono' => trim($telefono)]);
            if ($checkTlf->fetchColumn() > 0) {
                $this->pdo->rollBack();
                return ["error" => "El teléfono " . $telefono . " ya está registrado"];
            }

            // 3. Insertar persona
            $caracteresEspeciales = ['ñ', 'Ñ', 'á', 'Á', 'é', 'É', 'í', 'Í', 'ó', 'Ó', 'ú', 'Ú'];
            $reemplazos = ['n', 'N', 'a', 'A', 'e', 'E', 'i', 'I', 'o', 'O', 'u', 'U'];
            
            $segundo_nombre = $segundo_nombre !== null ? trim($segundo_nombre) : '';
            $segundo_apellido = $segundo_apellido !== null ? trim($segundo_apellido) : '';
            
            $datos_personales = [$primer_nombre, $segundo_nombre, $primer_apellido, $segundo_apellido];
            $datos_personales = array_map('trim', $datos_personales);
            $datos_personales = str_replace($caracteresEspeciales, $reemplazos, $datos_personales);

            $estatus_activo = 1;

            $insertPersona = $this->pdo->prepare("INSERT INTO personas (id_genero, id_estatus, cedula_identidad, primer_nombre, segundo_nombre, primer_apellido, segundo_apellido, fecha_nacimiento, email, telefono, direccion_habitacion) 
                VALUES (:genero_id, :estatus_id, :cedula, :primer_nombre, :segundo_nombre, :primer_apellido, :segundo_apellido, :fecha_nacimiento, :email, :telefono, :direccion)");
            
            $insertPersona->execute([
                ':genero_id' => $id_genero,
                ':estatus_id' => $estatus_activo,
                ':cedula' => trim($cedula_identidad),
                ':primer_nombre' => $datos_personales[0],
                ':segundo_nombre' => $datos_personales[1],
                ':primer_apellido' => $datos_personales[2],
                ':segundo_apellido' => $datos_personales[3],
                ':fecha_nacimiento' => trim($fecha_nacimiento),
                ':email' => trim($email),
                ':telefono' => trim($telefono),
                ':direccion' => trim($direccion_habitacion)
            ]);

            $id_persona = (int) $this->pdo->lastInsertId();

            // 4. Crear cliente
            $accessResult = $this->accessCode($id_persona);
            if (isset($accessResult['error'])) {
                $this->pdo->rollBack();
                return ["error" => $accessResult['error']];
            }

            $codigoGenerado = $accessResult['codigo_acceso'];
            $fecha_hoy = date('Y-m-d');

            $insertCliente = $this->pdo->prepare("INSERT INTO clientes (id_estatus, id_persona, codigo_acceso, fecha_inscripcion)
                VALUES (:id_estatus, :id_persona, :codigo_acceso, :fecha_inscripcion)");

            $insertCliente->execute([
                ':id_estatus' => $estatus_activo,
                ':id_persona' => $id_persona,
                ':codigo_acceso' => $codigoGenerado,
                ':fecha_inscripcion' => $fecha_hoy
            ]);

            $this->pdo->commit();

            return [
                "success" => true,
                "message" => "Cliente inscrito exitosamente",
                "data" => [
                    "codigo_acceso" => $codigoGenerado, 
                    "fecha_inscripcion" => $fecha_hoy
                ]
            ];

        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            return ["error" => "Error al registrar el cliente directamente: " . $e->getMessage()];
        }
    }

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
    ) {
        try {
            if (!$this->pdo) {
                return ["error" => "Error de conexión a la base de datos"];
            }

            $this->pdo->beginTransaction();

            // 1. Obtener el id_persona del cliente
            $stmtGet = $this->pdo->prepare("SELECT id_persona FROM clientes WHERE id = :id");
            $stmtGet->execute([':id' => $id_cliente]);
            $id_persona = $stmtGet->fetchColumn();

            if (!$id_persona) {
                $this->pdo->rollBack();
                return ["error" => "No se encontró la persona vinculada a este cliente"];
            }

            // 2. Validar duplicados de cédula, correo y teléfono en otra persona
            $checkPersona = $this->pdo->prepare("SELECT COUNT(*) FROM personas WHERE cedula_identidad = :cedula AND id != :id");
            $checkPersona->execute([':cedula' => trim($cedula_identidad), ':id' => $id_persona]);
            if ($checkPersona->fetchColumn() > 0) {
                $this->pdo->rollBack();
                return ["error" => "La cédula " . $cedula_identidad . " ya está registrada por otra persona"];
            }

            $checkEmail = $this->pdo->prepare("SELECT COUNT(*) FROM personas WHERE email = :email AND id != :id");
            $checkEmail->execute([':email' => trim($email), ':id' => $id_persona]);
            if ($checkEmail->fetchColumn() > 0) {
                $this->pdo->rollBack();
                return ["error" => "El correo electrónico " . $email . " ya está registrado por otra persona"];
            }

            $checkTlf = $this->pdo->prepare("SELECT COUNT(*) FROM personas WHERE telefono = :telefono AND id != :id");
            $checkTlf->execute([':telefono' => trim($telefono), ':id' => $id_persona]);
            if ($checkTlf->fetchColumn() > 0) {
                $this->pdo->rollBack();
                return ["error" => "El teléfono " . $telefono . " ya está registrado por otra persona"];
            }

            // 3. Actualizar la tabla personas
            $caracteresEspeciales = ['ñ', 'Ñ', 'á', 'Á', 'é', 'É', 'í', 'Í', 'ó', 'Ó', 'ú', 'Ú'];
            $reemplazos = ['n', 'N', 'a', 'A', 'e', 'E', 'i', 'I', 'o', 'O', 'u', 'U'];
            
            $segundo_nombre = $segundo_nombre !== null ? trim($segundo_nombre) : '';
            $segundo_apellido = $segundo_apellido !== null ? trim($segundo_apellido) : '';
            
            $datos_personales = [$primer_nombre, $segundo_nombre, $primer_apellido, $segundo_apellido];
            $datos_personales = array_map('trim', $datos_personales);
            $datos_personales = str_replace($caracteresEspeciales, $reemplazos, $datos_personales);

            $updatePersona = $this->pdo->prepare("UPDATE personas SET 
                id_genero = :genero_id, 
                cedula_identidad = :cedula, 
                primer_nombre = :primer_nombre, 
                segundo_nombre = :segundo_nombre, 
                primer_apellido = :primer_apellido, 
                segundo_apellido = :segundo_apellido, 
                fecha_nacimiento = :fecha_nacimiento, 
                email = :email, 
                telefono = :telefono, 
                direccion_habitacion = :direccion, 
                actualizado_en = NOW() 
                WHERE id = :id");
            
            $updatePersona->execute([
                ':id' => $id_persona,
                ':genero_id' => $id_genero,
                ':cedula' => trim($cedula_identidad),
                ':primer_nombre' => $datos_personales[0],
                ':segundo_nombre' => $datos_personales[1],
                ':primer_apellido' => $datos_personales[2],
                ':segundo_apellido' => $datos_personales[3],
                ':fecha_nacimiento' => trim($fecha_nacimiento),
                ':email' => trim($email),
                ':telefono' => trim($telefono),
                ':direccion' => trim($direccion_habitacion)
            ]);

            // 4. Actualizar la tabla clientes
            $updateCliente = $this->pdo->prepare("UPDATE clientes SET id_estatus = :id_estatus, actualizado_en = NOW() WHERE id = :id");
            $updateCliente->execute([
                ':id' => $id_cliente,
                ':id_estatus' => $id_estatus
            ]);

            $this->pdo->commit();

            return ["success" => true, "message" => "Cliente y datos personales actualizados exitosamente"];

        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            return ["error" => "Error al actualizar el cliente: " . $e->getMessage()];
        }
    }
}