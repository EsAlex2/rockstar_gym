<?php

require_once __DIR__ . '/models.php';
require_once __DIR__ . '/../core/conn.php';

class EntrenamientosModel extends Model
{
    protected $pdo;

    public function __construct($pdo)
    {
        parent::__construct($pdo);
        $this->pdo = $pdo;
    }

    public function crearEntrenamientos(int $id_entrenador, int $id_sede, string $nombre, string $descripcion)
    {
        try {
            if (!$this->pdo) {
                return ["error" => "Error de conexion en la base de datos"];
            }

            /**
             * validamos que exista el entrenador
             */
            $checkEntrenador = $this->pdo->prepare("SELECT COUNT(*) FROM administracion.entrenadores WHERE id = :id_entrenador");
            $checkEntrenador->bindParam(":id_entrenador", $id_entrenador, PDO::PARAM_INT);
            $checkEntrenador->execute();

            if ($checkEntrenador->fetchColumn() == 0) {
                return ["error" => "No existe registro del entrenador que intenta buscar en nuestra base de datos"];
            }

            /**
             * validamos que la sede especificada exista en la base de datos
             */
            $checkSede = $this->pdo->prepare("SELECT COUNT(*) FROM administracion.sedes WHERE id = :id_sede");
            $checkSede->bindParam(":id_sede", $id_sede, PDO::PARAM_INT);
            $checkSede->execute();

            if ($checkSede->fetchColumn() == 0) {
                return ["error" => "No existe registro de la sede que intenta buscar en nuestra base de datos"];
            }

            /**
             * preparamos los datos para ingresarlos en la base de datos
             */
            $estatus_activo = 1;

            $insert = $this->pdo->prepare("INSERT INTO administracion.entrenamiento (id_estatus, id_entrenador, id_sede, nombre_entrenamiento, descripcion) 
            VALUES (:id_estatus, :id_entrenador, :id_sede, :nombre_entrenamiento, :descripcion)");

            $insert->bindParam(":id_estatus", $estatus_activo, PDO::PARAM_INT);
            $insert->bindParam(":id_entrenador", $id_entrenador, PDO::PARAM_INT);
            $insert->bindParam(":id_sede", $id_sede, PDO::PARAM_INT);
            $insert->bindParam(":nombre_entrenamiento", $nombre, PDO::PARAM_STR);
            $insert->bindParam(":descripcion", $descripcion, PDO::PARAM_STR);
            $insert->execute();

            return [
                "success" => true,
                "message" => "Entrenamiento creado exitosamente",
                "data" => [
                    "id_estatus" => $estatus_activo,
                    "id_entrenador" => $id_entrenador,
                    "id_sede" => $id_sede,
                    "nombre_entrenamiento" => $nombre,
                    "descripcion" => $descripcion
                ]
            ];

        } catch (PDOException $e) {
            return ["error" => "Error inesperado al crear el entrenamiento: " . $e->getMessage()];
        }
    }

    public function listarEntrenamientos()
    {
        try {
            if (!$this->pdo) {
                return ["error" => "Error de conexion en la base de datos"];
            }

            $lista = $this->pdo->prepare("
                SELECT b.nombre_estatus AS Estatus, a.id_entrenador, p.primer_nombre, p.primer_apellido, d.sede AS Sede, a.nombre_entrenamiento
                FROM administracion.entrenamiento a
                INNER JOIN administracion.estatus b ON a.id_estatus = b.id
                INNER JOIN administracion.entrenadores c ON a.id_entrenador = c.id
                INNER JOIN administracion.personas p ON c.id_persona = p.id
                INNER JOIN administracion.sedes d ON a.id_sede = d.id
            ");

            $lista->execute();
            $resultado = $lista->fetchAll(PDO::FETCH_ASSOC);

            return empty($resultado) ? ["error" => "No hay entrenamientos registrados"] : $resultado;
        } catch (PDOException $e) {
            return ["error" => "Error al obtener listado de entrenamientos " . $e->getMessage()];
        }
    }

}

$pruebas = new EntrenamientosModel($pdo);

echo json_encode($pruebas->listarEntrenamientos(), JSON_UNESCAPED_UNICODE);