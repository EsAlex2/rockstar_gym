<?php

require_once __DIR__ . '/../core/conn.php';
require_once __DIR__ . '/seeder.php';

class SeederEstatus extends Seeder
{
    public function __construct($pdo)
    {
        parent::__construct($pdo);
    }

    public function runSeeder(): void
    {
        $estatusList = [
            [
                'nombre_estatus' => 'Activo',
                'descripcion' => 'La entidad (usuario, persona, plan) se encuentra totalmente operativa y con acceso al sistema.'
            ],
            [
                'nombre_estatus' => 'Inactivo',
                'descripcion' => 'La entidad ha sido deshabilitada temporal o permanentemente, no tiene acceso al sistema.'
            ],
            [
                'nombre_estatus' => 'Pendiente',
                'descripcion' => 'Registro inicial a la espera de una acción de activación, verificación de datos o primer pago.'
            ],
            [
                'nombre_estatus' => 'Aprobado',
                'descripcion' => 'El pago fue verificado exitosamente en la cuenta bancaria por el personal administrativo.'
            ],
            [
                'nombre_estatus' => 'Rechazado',
                'descripcion' => 'El pago fue declinado debido a datos incorrectos, referencia falsa o fondos insuficientes.'
            ],
            [
                'nombre_estatus' => 'Vencido',
                'descripcion' => 'La membresía del cliente ha superado su fecha límite de vigencia; acceso denegado.'
            ],
            [
                'nombre_estatus' => 'Congelado',
                'descripcion' => 'La membresía ha sido pausada temporalmente (por razones médicas o viajes). El tiempo no corre.'
            ]
        ];

        foreach ($estatusList as $estatus) {
            $stmt = $this->pdo->prepare("INSERT INTO estatus (nombre_estatus, descripcion) VALUES (:nombre_estatus, :descripcion)");
            $stmt->execute([
                ':nombre_estatus' => $estatus['nombre_estatus'],
                ':descripcion' => $estatus['descripcion']
            ]);
        }
    }
}
