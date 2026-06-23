<?php 

require_once __DIR__ . '/seeder.php';
require_once __DIR__ . '/../core/conn.php';

class SeederClientesPlanes extends Seeder
{
    public function __construct($pdo)
    {
        parent::__construct($pdo);
    }

    public function runSeeder()
    {
        // Array de asignaciones simuladas de planes a clientes
        // Nota: id_estatus = 1 representa 'Activo' en tu sistema
        $clientesPlanesList = [
            [
                'id_cliente'        => 1,
                'id_plan'           => 1, // Plan Mensual Estándar (ej. 30 días)
                'id_estatus'        => 1,
                'fecha_inicio'      => '2026-06-01',
                'fecha_vencimiento' => '2026-07-01'
            ],
            [
                'id_cliente'        => 2,
                'id_plan'           => 2, // Plan VIP / Trimestral
                'id_estatus'        => 1,
                'fecha_inicio'      => '2026-05-15',
                'fecha_vencimiento' => '2026-08-15'
            ],
            [
                'id_cliente'        => 3,
                'id_plan'           => 1,
                'id_estatus'        => 1,
                'fecha_inicio'      => '2026-06-10',
                'fecha_vencimiento' => '2026-07-10'
            ],
            [
                'id_cliente'        => 4,
                'id_plan'           => 3, // Plan Anual o Semestral
                'id_estatus'        => 1,
                'fecha_inicio' => '2026-01-10',
                'fecha_vencimiento' => '2026-07-10'
            ]
        ];

        // Preparación de la consulta SQL respetando la estructura de MariaDB
        $stmt = $this->pdo->prepare("INSERT INTO clientes_planes 
            (id_cliente, id_plan, id_estatus, fecha_inicio, fecha_vencimiento) 
            VALUES (:id_cliente, :id_plan, :id_estatus, :fecha_inicio, :fecha_vencimiento)");

        foreach ($clientesPlanesList as $cp) {
            $stmt->execute([
                ':id_cliente'        => $cp['id_cliente'],
                ':id_plan'           => $cp['id_plan'],
                ':id_estatus'        => $cp['id_estatus'],
                ':fecha_inicio'      => $cp['fecha_inicio'],
                ':fecha_vencimiento' => $cp['fecha_vencimiento']
            ]);
        }
    }
}