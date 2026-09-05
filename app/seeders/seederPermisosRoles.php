<?php

require_once __DIR__ . '/../core/conn.php';
require_once __DIR__ . '/seeder.php';

class SeederPermisosRoles extends Seeder
{
    public function __construct($pdo)
    {
        parent::__construct($pdo);
    }

    public function runSeeder(): void
    {
        try {
            $this->pdo->beginTransaction();

            // 1. Definición de la matriz lógica (Nombre del Rol => [Lista de Permisos])
            $asignaciones = [
                'Root' => [
                    'seguridad.configurar',
                    'roles.gestionar',
                    'usuarios.crear',
                    'usuarios.leer',
                    'usuarios.actualizar',
                    'clientes.crear',
                    'clientes.leer',
                    'clientes.actualizar',
                    'planes.gestionar',
                    'pagos.registrar',
                    'pagos.verificar',
                    'pagos.historial',
                    'clases.gestionar',
                    'clases.pasar_asistencia',
                    'clases.ver_horarios',
                    'clases.reservar',
                    'mi_perfil.ver',
                    'mi_perfil.actualizar',
                    'reportes.financieros',
                    'reportes.asistencia'
                ],
                'Administrador' => [
                    'usuarios.leer',
                    'usuarios.actualizar',
                    'clientes.crear',
                    'clientes.leer',
                    'clientes.actualizar',
                    'planes.gestionar',
                    'pagos.registrar',
                    'pagos.verificar',
                    'pagos.historial',
                    'clases.gestionar',
                    'clases.ver_horarios',
                    'mi_perfil.ver',
                    'mi_perfil.actualizar',
                    'reportes.financieros',
                    'reportes.asistencia'
                ],
                'Entrenador' => [
                    'clientes.leer',
                    'clases.pasar_asistencia',
                    'clases.ver_horarios',
                    'mi_perfil.ver',
                    'mi_perfil.actualizar'
                ],
                'Cliente' => [
                    'clases.ver_horarios',
                    'clases.reservar',
                    'mi_perfil.ver',
                    'mi_perfil.actualizar'
                ]
            ];

            // 2. Preparar sentencias
            $stmtRol = $this->pdo->prepare("SELECT id FROM roles WHERE nombre_rol = :nombre");
            $stmtPermiso = $this->pdo->prepare("SELECT id FROM permisos WHERE nombre_permiso = :nombre");
            $stmtInsert = $this->pdo->prepare("INSERT INTO roles_permisos (id_rol, id_permiso) VALUES (:id_rol, :id_permiso)");

            // 3. Procesar las asignaciones
            foreach ($asignaciones as $nombreRol => $listaPermisos) {
                // Obtener ID del rol
                $stmtRol->execute([':nombre' => $nombreRol]);
                $rol = $stmtRol->fetch(PDO::FETCH_ASSOC);

                if ($rol) {
                    foreach ($listaPermisos as $nombrePermiso) {
                        // Obtener ID del permiso
                        $stmtPermiso->execute([':nombre' => $nombrePermiso]);
                        $permiso = $stmtPermiso->fetch(PDO::FETCH_ASSOC);

                        if ($permiso) {
                            $stmtInsert->execute([
                                ':id_rol' => $rol['id'],
                                ':id_permiso' => $permiso['id']
                            ]);
                        }
                    }
                }
            }

            $this->pdo->commit();
        } catch (Exception $e) {
            $this->pdo->rollBack();
            echo "Error: " . $e->getMessage();
        }
    }
}

// Ejecución
// $seeder = new SeederPermisosRoles($pdo);
// $seeder->runSeeder();
