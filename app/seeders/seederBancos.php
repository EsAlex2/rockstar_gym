<?php

require_once __DIR__ . '/../core/conn.php';
require_once __DIR__ . '/seeder.php';

class SeederBancos extends Seeder
{
    public function __construct($pdo)
    {
        parent::__construct($pdo);
    }

    public function runSeeder(): void
    {
        $bancosList = [
            // --- BANCA PÚBLICA Y ESTATAL ---
            [
                'nombre_banco' => 'Banco de Venezuela (BDV)',
                'descripcion' => 'Banca Pública - Entidad financiera con mayor cuota del mercado nacional.',
            ],
            [
                'nombre_banco' => 'Banco del Tesoro',
                'descripcion' => 'Banca Pública - Institución financiera del Estado venezolano.',
            ],
            [
                'nombre_banco' => 'Banco Digital de los Trabajadores (BDT)',
                'descripcion' => 'Banca Pública - Orientado al desarrollo comunal y de la clase obrera.',
            ],
            [
                'nombre_banco' => 'BANFANB',
                'descripcion' => 'Banca Pública - Banco de la Fuerza Armada Nacional Bolivariana.',
            ],

            // --- BANCA PRIVADA ---
            [
                'nombre_banco' => 'Banesco',
                'descripcion' => 'Banca Privada - Una de las principales instituciones financieras privadas del país.',
            ],
            [
                'nombre_banco' => 'Banco Mercantil',
                'descripcion' => 'Banca Privada - Entidad financiera de amplia trayectoria nacional.',
            ],
            [
                'nombre_banco' => 'BBVA Provincial',
                'descripcion' => 'Banca Privada - Parte del grupo internacional BBVA.',
            ],
            [
                'nombre_banco' => 'Banco Nacional de Crédito (BNC)',
                'descripcion' => 'Banca Privada - Institución financiera con fuerte crecimiento corporativo.',
            ],
            [
                'nombre_banco' => 'Bancamiga',
                'descripcion' => 'Banca Privada - Banco universal enfocado en innovación y puntos de venta.',
            ],
            [
                'nombre_banco' => 'Bancaribe',
                'descripcion' => 'Banca Privada - Banco universal con histórico recorrido en el mercado.',
            ],
            [
                'nombre_banco' => 'Banplus',
                'descripcion' => 'Banca Privada - Entidad orientada a soluciones comerciales y corporativas.',
            ],
            [
                'nombre_banco' => 'Banco Exterior',
                'descripcion' => 'Banca Privada - Institución financiera tradicional del mercado venezolano.',
            ],
            [
                'nombre_banco' => 'Banco Fondo Común (BFC)',
                'descripcion' => 'Banca Privada - Entidad bancaria con enfoque en banca de personas.',
            ],
            [
                'nombre_banco' => 'Banco Plaza',
                'descripcion' => 'Banca Privada - Institución financiera de capital privado.',
            ],
            [
                'nombre_banco' => 'Banco Venezolano de Crédito',
                'descripcion' => 'Banca Privada - Una de las instituciones financieras más antiguas y estables.',
            ],
            [
                'nombre_banco' => 'Banco Caroní',
                'descripcion' => 'Banca Privada - Enfocado originalmente en la región Guayana y expandido a nivel nacional.',
            ],
            [
                'nombre_banco' => 'Banco Activo',
                'descripcion' => 'Banca Privada - Orientado a la pequeña y mediana empresa.',
            ],
            [
                'nombre_banco' => 'DELSUR',
                'descripcion' => 'Banca Privada - Banco Universal con base en el desarrollo regional.',
            ],
            [
                'nombre_banco' => 'Banco Sofitasa',
                'descripcion' => 'Banca Privada - Banco universal con fuerte arraigo en la región andina.',
            ]
        ];

        foreach ($bancosList as $banco) {
            
            $stmt = $this->pdo->prepare("INSERT INTO bancos (nombre_banco, descripcion) VALUES (:nombre_banco, :descripcion)");
            $stmt->execute([
                ':nombre_banco' => $banco['nombre_banco'],
                ':descripcion' => $banco['descripcion']
            ]);
        }
    }
}