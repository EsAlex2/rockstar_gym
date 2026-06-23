<?php 

require_once __DIR__ . '/seeder.php';
require_once __DIR__ . '/../core/conn.php';

class SeederPlanes extends Seeder
{
    public function __construct($pdo)
    {
        parent::__construct($pdo);
    }

    public function runSeeder()
    {
        // Array de planes basados exactamente en la estructura de tu tabla y lógica de help.php
        $planesList = [
            [
                'id'            => 1, // Coincide con el Plan Mensual Estándar usado en clientes_planes
                'id_estatus'    => 1, // Activo
                'nombre_plan'   => 'Plan Mensual Estándar',
                'descripcion'   => 'Acceso ilimitado a las instalaciones del gimnasio y área de pesas de lunes a viernes.',
                'precio'        => 35.00,
                'duracion_dias' => 30
            ],
            [
                'id'            => 2, // Coincide con el Plan Trimestral / VIP usado en clientes_planes
                'id_estatus'    => 1, // Activo
                'nombre_plan'   => 'Plan VIP Trimestral',
                'descripcion'   => 'Acceso total de lunes a domingo, incluye área de pesas, todas las clases especiales (Yoga, Spinning) y evaluación antropométrica.',
                'precio'        => 50.00,
                'duracion_dias' => 90
            ],
            [
                'id'            => 3, // Coincide con el Plan Semestral / Anual usado en clientes_planes
                'id_estatus'    => 1, // Activo
                'nombre_plan'   => 'Plan Semestral Premium',
                'descripcion'   => 'Acceso completo por 6 meses a todas las sedes del complejo, casillero privado y descuento en tienda de suplementos.',
                'precio'        => 120.00,
                'duracion_dias' => 180
            ]
        ];

        // Preparación de la consulta SQL respetando las restricciones de MariaDB
        $stmt = $this->pdo->prepare("INSERT INTO planes 
            (id, id_estatus, nombre_plan, descripcion, precio, duracion_dias) 
            VALUES (:id, :id_estatus, :nombre_plan, :descripcion, :precio, :duracion_dias)");

        foreach ($planesList as $plan) {
            $stmt->execute([
                ':id'            => $plan['id'],
                ':id_estatus'    => $plan['id_estatus'],
                ':nombre_plan'   => $plan['nombre_plan'],
                ':descripcion'   => $plan['descripcion'],
                ':precio'        => $plan['precio'],
                ':duracion_dias' => $plan['duracion_dias']
            ]);
        }
    }
}
