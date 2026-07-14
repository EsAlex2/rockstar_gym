<?php

require_once __DIR__ . '/models.php';
require_once __DIR__ . '/../core/conn.php'; 

/* =================================================================================
 * LoginModel.php
 * Modelo dedicado exclusivamente a la verificación de identidad y credenciales de acceso.
 * Autor: Alex Madrid (Refactorizado)
 * ==============================================================================
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

            // Removidos los prefijos 'administracion.' de todas las tablas e id_estatus subquery
            $sql = "SELECT u.*, 
                           p.primer_nombre, p.primer_apellido, p.cedula_identidad,
                           r.nombre_rol
                    FROM usuarios u
                    INNER JOIN personas p ON u.id_persona = p.id
                    INNER JOIN roles r ON u.id_rol = r.id
                    WHERE (u.email_user = :identidad) 
                      AND u.id_estatus = (SELECT id FROM estatus WHERE nombre_estatus = 'Activo' LIMIT 1)
                    LIMIT 1";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['identidad' => trim($identidad)]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC); 
        } catch (PDOException $e) {
            return false;
        }
    }

    public function obtenerPermisosPorRol(int $id_rol) {
    try {
        $sql = "SELECT p.nombre_permiso 
                FROM permisos p
                INNER JOIN roles_permisos rp ON p.id = rp.id_permiso
                WHERE rp.id_rol = :id_rol";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id_rol' => $id_rol]);
        
        // Retorna un array plano de strings (ej: ['usuarios.crear', 'pagos.verificar'])
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        return [];
    }
}
}