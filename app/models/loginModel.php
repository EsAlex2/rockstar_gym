<?php

require_once __DIR__ . '/models.php';
require_once __DIR__ . '/../core/conn.php'; 

/* * LoginModel.php
 * Modelo dedicado exclusivamente a la verificación de identidad y credenciales de acceso.
 * Autor: Alex Madrid (Refactorizado)
 * Fecha: 16/06/2026
 */

class LoginModel extends Model
{
    protected $pdo;

    public function __construct($pdo)
    {
        parent::__construct($pdo);
        $this->pdo = $pdo;
    }

    /**
     * Busca un usuario activo por su username o por su correo electrónico.
     */
    public function buscarPorIdentidad(string $identidad)
    {
        try {
            if (!$this->pdo) {
                return false;
            }

            $sql = "SELECT u.*, 
                           p.primer_nombre, p.primer_apellido, p.cedula_identidad,
                           r.nombre_rol
                    FROM administracion.usuarios u
                    INNER JOIN administracion.personas p ON u.id_persona = p.id
                    INNER JOIN administracion.roles r ON u.id_rol = r.id
                    WHERE (u.email_user = :identidad) 
                      AND u.id_estatus = (SELECT id FROM administracion.estatus WHERE nombre_estatus = 'Activo' LIMIT 1)
                    LIMIT 1";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['identidad' => trim($identidad)]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC); 
        } catch (PDOException $e) {
            error_log("Error crítico en LoginModel::buscarPorIdentidad -> " . $e->getMessage());
            return false;
        }
    }
}