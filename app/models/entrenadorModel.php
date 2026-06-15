<?php
require_once __DIR__ . '/models.php';
require_once __DIR__ . '/../core/conn.php';

/* =================================================================================
 * entrenadorModel.php
 * Modelo para la gestión de entrenadores del gimnasio en el sistema de administración.
 * Proporciona métodos para obtener, crear y actualizar entrenadores.
 * Utiliza PDO para la interacción con la base de datos y maneja errores de conexión y ejecución.
 * Autor: Alex Madrid
 * Fecha: 07/06/2026
 * ==============================================================================
 */

class entrenadorModel extends Model
{
    protected $pdo;

    public function __construct($pdo)
    {
        parent::__construct($pdo);
        $this->pdo = $pdo;
    }

    public function crearEntrenadores(int $id_persona, string $especialidad)
{
    try {
        if (!$this->pdo) {
            return ["error" => "Error de conexión a la base de datos"];
        }
        
        $checkPerson = $this->pdo->prepare("SELECT COUNT(*) FROM administracion.personas WHERE id = :id_persona");
        $checkPerson->bindParam(':id_persona', $id_persona, PDO::PARAM_INT);
        $checkPerson->execute();

        if ($checkPerson->fetchColumn() == 0) {
            return ["error" => "La persona no existe en nuestra base de datos"];
        }

        $mayus = strtoupper($especialidad);

        $checkDuplicate = $this->pdo->prepare("SELECT COUNT(*) FROM administracion.entrenadores WHERE id_persona = :id_persona AND especialidad = :especialidad");
        $checkDuplicate->bindParam(':id_persona', $id_persona, PDO::PARAM_INT);
        $checkDuplicate->bindParam(':especialidad', $especialidad, PDO::PARAM_STR);
        $checkDuplicate->execute();

        if ($checkDuplicate->fetchColumn() > 0) {
            return ["error" => "Esta persona ya se encuentra registrada con la especialidad: " . $especialidad];
        }

        $query = $this->pdo->prepare("INSERT INTO administracion.entrenadores (id_estatus, id_persona, especialidad) VALUES (:id_estatus, :id_persona, :especialidad)");
        $estatus_default = 1;

        $query->bindParam(':id_estatus', $estatus_default, PDO::PARAM_INT);
        $query->bindParam(':id_persona', $id_persona, PDO::PARAM_INT);
        $query->bindParam(':especialidad', $mayus, PDO::PARAM_STR);

        $query->execute();

        return [
            "success" => true,
            "message" => "Especialidad asignada al entrenador exitosamente",
            "data" => [
                "id_persona" => $id_persona,
                "especialidad" => $mayus
            ]
        ];
    } catch (PDOException $e) {
        return ["error" => "Error al crear entrenador: " . $e->getMessage()];
    }
}

    public function listarEntrenadores()
    {
        try {
            if (!$this->pdo) {
                return ["error" => "Error de conexión a la base de datos"];
            }

            $sql = $this->pdo->prepare("SELECT e.nombre_estatus As estatus, p.primer_nombre || ' ' || p.primer_apellido AS persona, especialidad 
            FROM administracion.entrenadores a
            INNER JOIN administracion.estatus e ON a.id_estatus = e.id
            INNER JOIN administracion.personas p ON a.id_persona = p.id");
            $sql->execute();
            $resultado = $sql->fetchAll(PDO::FETCH_ASSOC);

            return empty($resultado) ? ["error" => "No hay usuarios registrados"] : $resultado;
        } catch (PDOException $e) {
            return ["error" => "Error al obtener usuarios: " . $e->getMessage()];
        }
    }

    public function listarPorCedula(string $cedula_identidad)
    {
        try {
            if (!$this->pdo) {
                return ["error" => "Error de conexión a la base de datos"];
            }

            /*
             * validaciones para la busqueda de cada entrenador por cedula
             * 1era validacion: que exista la persona para asociarlo a la tabla de entrenadores
             */

            $checkTraining = $this->pdo->prepare("SELECT COUNT(*) FROM administracion.personas WHERE  cedula_identidad = :cedula");
            $checkTraining->bindParam(':cedula', $cedula_identidad, PDO::PARAM_INT);
            $checkTraining->execute();

            if ($checkTraining->fetchColumn() == 0) {
                return ["error" => "La persona no existe en nuestra base de datos"];
            }

            /**
             * realizamos un INNER JOIN para trer de la tabla de personas la informacion basica del entrenador, ademas su informacion en la 
             * tabla de entrenadores
             */

            $buscarInfo = $this->pdo->prepare("SELECT a.id, b.nombre_estatus AS Estatus, c.cedula_identidad AS Documento_Identidad, c.primer_nombre AS Nombre, c.primer_apellido AS Apellido, a.especialidad
            FROM administracion.entrenadores a
            INNER JOIN administracion.estatus b ON a.id_estatus = b.id
            INNER JOIN administracion.personas c ON a.id_persona = c.id
            WHERE c.cedula_identidad = :cedula");

            $buscarInfo->bindParam(':cedula', $cedula_identidad, PDO::PARAM_STR);
            $buscarInfo->execute();

            $resultado = $buscarInfo->fetch(PDO::FETCH_ASSOC);

            if ($resultado === false) {
                return ["error" => "La persona existe, pero no está registrada como entrenador en el sistema."];
            }

            return [
                "estatus" => true,
                "message" => "Entrenador Encontrado Exitosamente!",
                "data" => [
                    $resultado
                ]
            ];

        } catch (PDOException $e) {
            return ["error" => "Error al buscar la persona " . $e->getMessage()];
        }
    }
}
