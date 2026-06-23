<?php 

require_once __DIR__ . '/seeder.php';
require_once __DIR__ . '/../core/conn.php';

class SeederPagos extends Seeder
{
    public function __construct($pdo)
    {
        parent::__construct($pdo);
    }

    public function runSeeder()
    {
        // Array de datos simulados basados en la estructura de tu base de datos
        $pagosList = [
            [
                'id_banco'        => 1,  // Banco de Venezuela
                'id_cliente'      => 1,  
                'id_cliente_plan' => 2,  
                'id_user'         => 1,  
                'id_estatus'      => 1,  // Activo/Aprobado
                'monto'           => 35.00,
                'fecha_pago'      => '2026-06-20',
                'cod_referencia'  => '0023418905'
            ],
            [
                'id_banco'        => 5,  // Banesco
                'id_cliente'      => 2,  
                'id_cliente_plan' => 2,  
                'id_user'         => 1,  
                'id_estatus'      => 1,  
                'monto'           => 50.00,
                'fecha_pago'      => '2026-06-21',
                'cod_referencia'  => '9948102351'
            ],
            [
                'id_banco'        => 6,  // Banco Mercantil
                'id_cliente'      => 3,  
                'id_cliente_plan' => 3,  
                'id_user'         => 2,  // jose.altuve98@gmail.com
                'id_estatus'      => 1,  
                'monto'           => 35.00,
                'fecha_pago'      => '2026-06-22',
                'cod_referencia'  => '1102485963'
            ],
            [
                'id_banco'        => 1,  // Banco de Venezuela
                'id_cliente'      => 3,  
                'id_cliente_plan' => 4,  
                'id_user'         => 3,  // adri.valen.gp@hotmail.com
                'id_estatus'      => 1,  
                'monto'           => 120.00,
                'fecha_pago'      => '2026-06-23',
                'cod_referencia'  => '0012958304'
            ]
        ];

        // Preparación de la consulta SQL tal como se procesa en el modelo de pagos
        $stmt = $this->pdo->prepare("INSERT INTO pagos 
            (id_banco, id_cliente, id_cliente_plan, id_user, id_estatus, monto, fecha_pago, cod_referencia) 
            VALUES (:id_banco, :id_cliente, :id_cliente_plan, :id_user, :id_estatus, :monto, :fecha_pago, :cod_referencia)");

        foreach ($pagosList as $pago) {
            $stmt->execute([
                ':id_banco'        => $pago['id_banco'],
                ':id_cliente'      => $pago['id_cliente'],
                ':id_cliente_plan' => $pago['id_cliente_plan'],
                ':id_user'         => $pago['id_user'],
                ':id_estatus'      => $pago['id_estatus'],
                ':monto'           => $pago['monto'],
                ':fecha_pago'      => $pago['fecha_pago'],
                ':cod_referencia'  => $pago['cod_referencia']
            ]);
        }
    }
}