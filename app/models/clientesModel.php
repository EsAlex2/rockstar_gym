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

            // MySQL utiliza CONCAT() para unir cadenas de texto
            $sql = $this->pdo->prepare("SELECT 
                a.id, 
                b.nombre_estatus AS Estatus, 
                c.primer_nombre, 
                c.primer_apellido, 
                CONCAT(c.primer_nombre, ' ', c.primer_apellido) AS Cliente, 
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
}