<?php

require_once __DIR__ . '/../core/conn.php';
require_once __DIR__ . '/seeder.php';

class seederSedes extends Seeder
{

    public function __construct($pdo)
    {
        parent::__construct($pdo);
    }

    public function runSeeder()
    {
        $sedesList = [
            [
                'estado' => 'Distrito Capital',
                'municipio' => 'Libertador',
                'sede' => 'Rockstar Gym - El Recreo (Sabana Grande)',
            ],
            [
                'estado' => 'Distrito Capital',
                'municipio' => 'Libertador',
                'sede' => 'Rockstar Gym - San Ignacio (La Castellana/Chacao)',
            ],
            [
                'estado' => 'Distrito Capital',
                'municipio' => 'Libertador',
                'sede' => 'Rockstar Gym - Parque Central',
            ],
            [
                'estado' => 'Miranda',
                'municipio' => 'Chacao',
                'sede' => 'Rockstar Gym - CCCT (Chuao)',
            ],
            [
                'estado' => 'Miranda',
                'municipio' => 'Chacao',
                'sede' => 'Rockstar Gym - Altamira (Plaza Francia)',
            ],
            [
                'estado' => 'Miranda',
                'municipio' => 'Baruta',
                'sede' => 'Rockstar Gym - Las Mercedes (Calle París)',
                
            ],
            [
                'estado' => 'Miranda',
                'municipio' => 'Baruta',
                'sede' => 'Rockstar Gym - La Trinidad (Zona Industrial)',
            ],
            [
                'estado' => 'Miranda',
                'municipio' => 'Sucre',
                'sede' => 'Rockstar Gym - Los Ruices (Av. Francisco de Miranda)',
            ],
            [
                'estado' => 'Miranda',
                'municipio' => 'Sucre',
                'sede' => 'Rockstar Gym - CC Líder (La California)',
            ]
        ];

        foreach ($sedesList as $sede) {
            $stmt = $this->pdo->prepare("INSERT INTO administracion.sedes (estado, municipio, sede) VALUES (:estado, :municipio, :sede)");
            $stmt->execute([
                ':estado' => $sede['estado'],
                ':municipio' => $sede['municipio'],
                ':sede' => $sede['sede']
            ]);
        }
    }
}
