<?php 
    
require_once __DIR__ . '/seeder.php';
require_once __DIR__ . '/../core/conn.php';

    class SeederRoles extends Seeder
    {
        public function __construct($pdo)
        {
            parent::__construct($pdo);
        }

        public function runSeeder(): void
        {
            $rolesList = [
                ['nombre_rol' => 'Root', 'descripcion' => 'Acceso total a todas las funciones del sistema, incluyendo la gestión de roles, usuarios, planes, pagos y reportes.'],
                ['nombre_rol' => 'Administrador', 'descripcion' => 'Acceso completo a todas las funcionalidades del sistema. Puede gestionar usuarios, planes, pagos y reportes.'],
                ['nombre_rol' => 'Entrenador', 'descripcion' => 'Acceso a funciones relacionadas con la gestión de clases, horarios y seguimiento de clientes. No tiene acceso a funciones administrativas ni financieras.'],
                ['nombre_rol' => 'Cliente', 'descripcion' => 'Acceso a funciones para gestionar su perfil, clases y seguimiento de progreso.']
            ];

            foreach ($rolesList as $rol) {
                $stmt = $this->pdo->prepare("INSERT INTO roles (nombre_rol, descripcion) VALUES (:nombre_rol, :descripcion)");
                $stmt->execute([
                    ':nombre_rol' => $rol['nombre_rol'],
                    ':descripcion' => $rol['descripcion']
                ]);
            }
        }
    }