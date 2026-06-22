<?php

require_once __DIR__ . '/models.php';
require_once __DIR__ . '/../core/conn.php';

/* * personasModels.php
 * Modelo para la gestión de personas en el sistema de administración.
 * Autor: Alex Madrid
 */

class personasModel extends Model
{
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

    public function __construct($pdo)
    {
        parent::__construct($pdo);
        $this->pdo = $pdo;
    }

    public function obtenerPersonas()
    {
        try {
            if (!$this->pdo) {
                return ["error" => "Error de conexión a la base de datos"];
            }

            // Removidos los prefijos 'administracion.' de las tres tablas relacionadas
            $stmt = $this->pdo->prepare("SELECT a.id AS id_persona, b.descripcion AS genero, c.nombre_estatus AS estatus, a.cedula_identidad, a.primer_nombre, a.segundo_nombre, a.primer_apellido, a.segundo_apellido, a.fecha_nacimiento, a.telefono, a.email, a.direccion_habitacion 
                FROM personas a 
                INNER JOIN generos b ON a.id_genero = b.id
                INNER JOIN estatus c ON a.id_estatus = c.id");
            $stmt->execute();
            $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return empty($resultado) ? ["error" => "No hay usuarios registrados"] : $resultado;
        } catch (PDOException $e) {
            return ["error" => "Error al obtener usuarios: " . $e->getMessage()];
        }
    }

    public function obtenerPersonaPorCedula(string $cedula)
    {
        try {
            if (!$this->pdo) {
                return ["error" => "Error de conexión a la base de datos"];
            }

            // Removido el prefijo 'administracion.'
            $checkStmt = $this->pdo->prepare("SELECT COUNT(*) FROM personas WHERE cedula_identidad = :cedula");
            $checkStmt->bindParam(':cedula', $cedula);
            $checkStmt->execute();

            if ($checkStmt->fetchColumn() == 0) {
                return ["error" => "No se encontró persona registrada con la cedula: {$cedula}"];
            }

            // Removidos los prefijos 'administracion.'
            $stmt = $this->pdo->prepare("SELECT a.id, b.descripcion AS genero, c.nombre_estatus AS estatus, a.cedula_identidad, a.primer_nombre, a.segundo_nombre, a.primer_apellido, a.segundo_apellido, a.fecha_nacimiento, a.telefono, a.email, a.direccion_habitacion 
                FROM personas a 
                INNER JOIN generos b ON a.id_genero = b.id
                INNER JOIN estatus c ON a.id_estatus = c.id
                WHERE a.cedula_identidad = :cedula");
            $stmt->bindParam(':cedula', $cedula);
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

            return $resultado;
        } catch (PDOException $e) {
            return ["error" => "Error al buscar la persona " . $e->getMessage()];
        }
    }

    public function crearPersona(int $genero_id, string $cedula, string $primer_nombre, string $segundo_nombre, string $primer_apellido, string $segundo_apellido, string $fecha_nacimiento, string $telefono, string $correo_electronico, string $direccion)
    {
        try {
            if (!$this->pdo) {
                return ["error" => "Error de conexión a la base de datos"];
            }

            // Removido el prefijo 'administracion.'
            $checkStmt = $this->pdo->prepare("SELECT COUNT(*) FROM personas WHERE cedula_identidad = :cedula");
            $checkStmt->bindParam(':cedula', $cedula);
            $checkStmt->execute();

            if ($checkStmt->fetchColumn() > 0) {
                return ["error" => "La persona con cédula {$cedula} ya existe en la base de datos"];
            }

            $caracteresEspeciales = ['ñ', 'Ñ', 'á', 'Á', 'é', 'É', 'í', 'Í', 'ó', 'Ó', 'ú', 'Ú'];
            $reemplazos = ['n', 'N', 'a', 'A', 'e', 'E', 'i', 'I', 'o', 'O', 'u', 'U'];
            $datos_personales = [$primer_nombre, $segundo_nombre, $primer_apellido, $segundo_apellido];
            $datos_personales = array_map('trim', $datos_personales);
            $datos_personales = str_replace($caracteresEspeciales, $reemplazos, $datos_personales);

            $estatus_id = 1;

            // Removido el prefijo 'administracion.'
            $stmt = $this->pdo->prepare("INSERT INTO personas (id_genero, id_estatus, cedula_identidad, primer_nombre, segundo_nombre, primer_apellido, segundo_apellido, fecha_nacimiento, email, telefono, direccion_habitacion) VALUES (:genero_id, :estatus_id, :cedula, :primer_nombre, :segundo_nombre, :primer_apellido, :segundo_apellido, :fecha_nacimiento, :telefono, :email, :direccion)");
            $stmt->bindParam(':genero_id', $genero_id);
            $stmt->bindParam(':estatus_id', $estatus_id);
            $stmt->bindParam(':cedula', $cedula);
            $stmt->bindParam(':primer_nombre', $datos_personales[0]);
            $stmt->bindParam(':segundo_nombre', $datos_personales[1]);
            $stmt->bindParam(':primer_apellido', $datos_personales[2]);
            $stmt->bindParam(':segundo_apellido', $datos_personales[3]);
            $stmt->bindParam(':fecha_nacimiento', $fecha_nacimiento);
            $stmt->bindParam(':email', $correo_electronico);
            $stmt->bindParam(':telefono', $telefono);
            $stmt->bindParam(':direccion', $direccion);
            $stmt->execute();

            return ["success" => true, "message" => "La persona con cédula {$cedula} se ha creado correctamente"];
        } catch (PDOException $e) {
            return ["error" => "Error al crear la persona " . $e->getMessage()];
        }
    }

    public function actualizarPersona(int $genero_id, int $estatus_id, string $cedula, string $primer_nombre, string $segundo_nombre, string $primer_apellido, string $segundo_apellido, string $fecha_nacimiento, string $telefono, string $correo_electronico, string $direccion)
    {
        try {
            if (!$this->pdo) {
                return ["error" => "Error de conexión a la base de datos"];
            }

            // Removido el prefijo 'administracion.'
            $checkStmt = $this->pdo->prepare("SELECT COUNT(*) FROM personas WHERE cedula_identidad = :cedula");
            $checkStmt->bindParam(':cedula', $cedula);
            $checkStmt->execute();

            if ($checkStmt->fetchColumn() == 0) {
                return ["error" => "No se encontró esta cedula en la base de datos"];
            }

            $caracteresEspeciales = ['ñ', 'Ñ', 'á', 'Á', 'é', 'É', 'í', 'Í', 'ó', 'Ó', 'ú', 'Ú'];
            $reemplazos = ['n', 'N', 'a', 'A', 'e', 'E', 'i', 'I', 'o', 'O', 'u', 'U'];
            $datos_personales = [$primer_nombre, $segundo_nombre, $primer_apellido, $segundo_apellido];
            $datos_personales = array_map('trim', $datos_personales);
            $datos_personales = str_replace($caracteresEspeciales, $reemplazos, $datos_personales);

            // Removido el prefijo 'administracion.'. La funcion now() es 100% valida en MySQL.
            $stmt = $this->pdo->prepare("UPDATE personas SET id_genero = :genero_id, id_estatus = :estatus_id, primer_nombre = :primer_nombre, segundo_nombre = :segundo_nombre, primer_apellido = :primer_apellido, segundo_apellido = :segundo_apellido, fecha_nacimiento = :fecha_nacimiento, telefono = :telefono, email = :email, direccion_habitacion = :direccion_habitacion, actualizado_en = now() 
            WHERE cedula_identidad = :cedula");
            $stmt->bindParam(':genero_id', $genero_id);
            $stmt->bindParam(':estatus_id', $estatus_id);
            $stmt->bindParam(':cedula', $cedula);
            $stmt->bindParam(':primer_nombre', $datos_personales[0]);
            $stmt->bindParam(':segundo_nombre', $datos_personales[1]);
            $stmt->bindParam(':primer_apellido', $datos_personales[2]);
            $stmt->bindParam(':segundo_apellido', $datos_personales[3]);
            $stmt->bindParam(':fecha_nacimiento', $fecha_nacimiento);
            $stmt->bindParam(':telefono', $telefono);
            $stmt->bindParam(':email', $correo_electronico);
            $stmt->bindParam(':direccion_habitacion', $direccion);

            $stmt->execute();

            return ["success" => "La persona con cedula {$cedula} se ha actualizado correctamente"];
        } catch (PDOException $e) {
            return ["error" => "Error al actualizar la persona " . $e->getMessage()];
        }
    }
}
