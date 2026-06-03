<?php 

require_once __DIR__ . '/models.php';
require_once __DIR__ . '/../core/conn.php';

/* 
    * personasModels.php
    * Modelo para la gestión de personas en el sistema de administración.
    * Proporciona métodos para obtener, crear y actualizar personas.
    * Utiliza PDO para la interacción con la base de datos y maneja errores de conexión y ejecución.
    * Autor: Alex Madrid
    * Fecha: 03/06/2026
*/

    class personasModel extends Model
    {

        /**
         *
         * Propiedades del modelo de personas, incluyendo información personal y de contacto.
         * Se definen como protegidas para su uso dentro de la clase y sus métodos.
         * Incluyen mensajes de error para la gestión de errores en la conexión a la base de datos y en la ejecución de consultas. 
         */
        protected $pdo;
        protected int $genero_id;
        protected int $estatus_id;
        protected string $cedula;
        protected string $primer_nombre;
        protected string $segundo_nombre;
        protected string $primer_apellido;
        protected string $segundo_apellido;
        protected string $fecha_nacimiento;
        protected string $telefono;
        protected string $correo_electronico;
        protected string $direccion;
        protected array $mensajes = [];

        /**
         * Constructor de la clase personasModel, que inicializa la conexión a la base de datos utilizando PDO.
         * Llama al constructor de la clase padre Model para asegurar la correcta inicialización.
         */

        public function __construct($pdo)
        {
            parent::__construct($pdo);
            $this->pdo = $pdo;
        }

            /**
            * Método para obtener todas las personas registradas en la base de datos.
            * Utiliza una consulta SQL para seleccionar los campos relevantes de la tabla personas.
            * Maneja errores de conexión y ejecución, devolviendo mensajes adecuados en cada caso.
            * Devuelve un JSON con los datos de las personas o un mensaje de error si ocurre algún problema.
            */

        public function obtenerPersonas(){
            $this->mensajes = [
                "Error de conexion a la base de datos",
                "Error inesperado para obtener las personas registradas"
            ];

            try {
                if (!$this->pdo) {
                    return $this->mensajes[0];
                }

                $stmt = $this->pdo->prepare("SELECT b.id, a.id_estatus, a.cedula_identidad, a.primer_nombre, a.segundo_nombre, a.primer_apellido, a.segundo_apellido, a.fecha_nacimiento, a.telefono, a.correo_electronico, a.direccion 
                FROM administracion.personas a 
                INNER JOIN administracion.generos b 
                ON a.id_genero = b.id");
                $stmt->execute();
                return json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            } catch (PDOException $e) {
                return $this->mensajes[1] . $e->getMessage();
            }
        }

        public function obtenerPersonaPorCedula(string $cedula){
            $this->mensajes = [
                "Error de conexion a la base de datos",
                "No se encontró la persona con cédula: {cedula} en la base de datos"
            ];

            try {
                if (!$this->pdo) {
                    return $this->mensajes[0];
                }

                $checkStmt = $this->pdo->prepare("SELECT COUNT(*) FROM administracion.personas WHERE cedula_identidad = :cedula");
                $checkStmt->bindParam(':cedula', $cedula);
                $checkStmt->execute();

                if ($checkStmt->fetchColumn() == 0) {
                    return str_replace("{cedula}", $cedula, $this->mensajes[1]);
                }

                $stmt = $this->pdo->prepare("SELECT b.id, a.id_estatus, a.cedula_identidad, a.primer_nombre, a.segundo_nombre, a.primer_apellido, a.segundo_apellido, a.fecha_nacimiento, a.telefono, a.correo_electronico, a.direccion 
                FROM administracion.personas a 
                INNER JOIN administracion.generos b 
                ON a.id_genero = b.id
                WHERE a.cedula_identidad = :cedula");
                $stmt->bindParam(':cedula', $cedula);
                $stmt->execute();
                return json_encode($stmt->fetch(PDO::FETCH_ASSOC));
            } catch (PDOException $e) {
                return $this->mensajes[1] . $e->getMessage();
            }
        }


        public function crearPersona(int $genero_id, string $cedula, string $primer_nombre, string $segundo_nombre, string $primer_apellido, string $segundo_apellido, string $fecha_nacimiento, string $telefono, string $correo_electronico, string $direccion){
            $this->mensajes = [
                "Error de conexion a la base de datos",
                "Error inesperado para crear la persona",
                "La persona con cédula {cedula} ya existe en la base de datos",
                "La persona con cédula {cedula} se ha creado correctamente"
            ];

            try {
                if (!$this->pdo) {
                    return $this->mensajes[0];
                }

                $fecha_nacimiento = date('Y-m-d', strtotime($fecha_nacimiento));
                
                $checkStmt = $this->pdo->prepare("SELECT COUNT(*) FROM administracion.personas WHERE cedula_identidad = :cedula");
                $checkStmt->bindParam(':cedula', $cedula);
                $checkStmt->execute();

                if ($checkStmt->fetchColumn() > 0) {
                    return str_replace("{cedula}", $cedula, $this->mensajes[2]);
                }

                /*
                *   Para evitar problemas de codificación con caracteres especiales en los nombres y apellidos, 
                *   se realiza un reemplazo de caracteres acentuados y la letra ñ por sus equivalentes sin acentos y sin la letra ñ. 
                *   Esto asegura que los datos se almacenen de manera consistente en la base de datos y evita posibles errores de codificación al momento de insertar o consultar los datos.
                */
                $caracteresEspeciales = ['ñ', 'Ñ', 'á', 'Á', 'é', 'É', 'í', 'Í', 'ó', 'Ó', 'ú', 'Ú'];
                $reemplazos = ['n', 'N', 'a', 'A', 'e', 'E', 'i', 'I', 'o', 'O', 'u', 'U'];
                $datos_personales = [$primer_nombre, $segundo_nombre, $primer_apellido, $segundo_apellido];
                $datos_personales = array_map('trim', $datos_personales);
                $datos_personales = str_replace($caracteresEspeciales, $reemplazos, $datos_personales);

                $stmt = $this->pdo->prepare("INSERT INTO administracion.personas (id_genero, id_estatus, cedula_identidad, primer_nombre, segundo_nombre, primer_apellido, segundo_apellido, fecha_nacimiento, telefono, correo_electronico, direccion) VALUES (:genero_id, :estatus_id, :cedula, :primer_nombre, :segundo_nombre, :primer_apellido, :segundo_apellido, :fecha_nacimiento, :telefono, :correo_electronico, :direccion)");
                $stmt->bindParam(':genero_id', $genero_id);
                $stmt->bindParam(':estatus_id', 2); // el estatus por defecto es 2 porque en el momento del registro de la persona, este mismo estara inactivo, y se activara una vez que se cree el usuario asociado a esta persona
                $stmt->bindParam(':cedula', $cedula);
                $stmt->bindParam(':primer_nombre', $datos_personales[0]);
                $stmt->bindParam(':segundo_nombre', $datos_personales[1]);
                $stmt->bindParam(':primer_apellido', $datos_personales[2]);
                $stmt->bindParam(':segundo_apellido', $datos_personales[3]);
                $stmt->bindParam(':fecha_nacimiento', $fecha_nacimiento);
                $stmt->bindParam(':telefono', $telefono);
                $stmt->bindParam(':correo_electronico', $correo_electronico);
                $stmt->bindParam(':direccion', $direccion);
                $stmt->execute();

                return $this->mensajes[3];
            } catch (PDOException $e) {
                return $this->mensajes[1] . $e->getMessage();
            }
        }


        public function actualizarPersona(int $genero_id, int $estatus_id, string $cedula, string $primer_nombre, string $segundo_nombre, string $primer_apellido, string $segundo_apellido, string $fecha_nacimiento, string $telefono, string $correo_electronico, string $direccion){
            $this->mensajes = [
                "Error de conexion a la base de datos",
                "No se encontró la persona con cédula: {cedula} en la base de datos",
                "Error inesperado para actualizar la persona con cédula {cedula}",
                "La persona con cédula {cedula} se ha actualizado correctamente"
            ];

            try {
                if (!$this->pdo) {
                    return $this->mensajes[0];
                }

                $fecha_nacimiento = date('Y-m-d', strtotime($fecha_nacimiento));
                
                $checkStmt = $this->pdo->prepare("SELECT COUNT(*) FROM administracion.personas WHERE cedula_identidad = :cedula");
                $checkStmt->bindParam(':cedula', $cedula);
                $checkStmt->execute();

                if ($checkStmt->fetchColumn() == 0) {
                    return str_replace("{cedula}", $cedula, $this->mensajes[1]);
                }

                /*
                *   Para evitar problemas de codificación con caracteres especiales en los nombres y apellidos, 
                *   se realiza un reemplazo de caracteres acentuados y la letra ñ por sus equivalentes sin acentos y sin la letra ñ. 
                *   Esto asegura que los datos se almacenen de manera consistente en la base de datos y evita posibles errores de codificación al momento de insertar o consultar los datos.
                */
                $caracteresEspeciales = ['ñ', 'Ñ', 'á', 'Á', 'é', 'É', 'í', 'Í', 'ó', 'Ó', 'ú', 'Ú'];
                $reemplazos = ['n', 'N', 'a', 'A', 'e', 'E', 'i', 'I', 'o', 'O', 'u', 'U'];
                $datos_personales = [$primer_nombre, $segundo_nombre, $primer_apellido, $segundo_apellido];
                $datos_personales = array_map('trim', $datos_personales);
                $datos_personales = str_replace($caracteresEspeciales, $reemplazos, $datos_personales);

                $stmt = $this->pdo->prepare("UPDATE administracion.personas SET id_genero = :genero_id, id_estatus = :estatus_id, primer_nombre = :primer_nombre, segundo_nombre = :segundo_nombre, primer_apellido = :primer_apellido, segundo_apellido = :segundo_apellido, fecha_nacimiento = :fecha_nacimiento, telefono = :telefono, correo_electronico = :correo_electronico, direccion = :direccion WHERE cedula_identidad = :cedula");
                $stmt->bindParam(':genero_id', $genero_id);
                $stmt->bindParam(':estatus_id', $estatus_id);
                $stmt->bindParam(':cedula', $cedula);
                $stmt->bindParam(':primer_nombre', $datos_personales[0]);
                $stmt->bindParam(':segundo_nombre', $datos_personales[1]);
                $stmt->bindParam(':primer_apellido', $datos_personales[2]);
                $stmt->bindParam(':segundo_apellido', $datos_personales[3]);
                $stmt->bindParam(':fecha_nacimiento', $fecha_nacimiento);
                $stmt->bindParam(':telefono', $telefono);
                $stmt->bindParam(':correo_electronico', $correo_electronico);
                $stmt->bindParam(':direccion', $direccion);
                $stmt->execute();

                return str_replace("{cedula}", $cedula, $this->mensajes[3]);
            } catch (PDOException $e) {
                return $this->mensajes[2] . $e->getMessage();
            }
        }
    }