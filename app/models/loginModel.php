<?php
// Asegúrate de ajustar la ruta de requerimiento según dónde esté exactamente conn.php
require_once __DIR__ . '/../core/conn.php'; 

class LoginModel {
    private $db;

    public function __construct() {
        global $pdo; // Usamos la instancia PDO creada en conn.php
        $this->db = $pdo;
    }

    public function buscarPorIdentidad($identidad) {
        try {
            $sql = "SELECT u.*, 
                           p.primer_nombre, p.primer_apellido, p.cedula_identidad,
                           r.nombre_rol
                    FROM administracion.usuarios u
                    INNER JOIN administracion.personas p ON u.id_persona = p.id
                    INNER JOIN administracion.roles r ON u.id_rol = r.id
                    WHERE (u.username = :identidad OR u.email_user = :identidad) 
                      AND u.id_estatus = (SELECT id FROM administracion.estatus WHERE nombre_estatus = 'Activo' LIMIT 1)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['identidad' => $identidad]);
            
            return $stmt->fetch(); // Retorna el registro o false si no existe
        } catch (PDOException $e) {
            error_log("Error en UsuariosModel::buscarPorIdentidad -> " . $e->getMessage());
            return false;
        }
    }
}