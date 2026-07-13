<?php

require_once __DIR__ . '/../core/conn.php';
require_once __DIR__ . '/seeder.php';

class SeederClientes extends Seeder
{
    public function __construct($pdo)
    {
        parent::__construct($pdo);
    }

    public function runSeeder()
    {
        // Clientes correspondientes a las personas creadas en SeederPersonas
        // Se calculan los códigos de acceso usando la lógica del modelo:
        // Inicial de primer nombre + 2 primeros dígitos de la cédula + Inicial de segundo nombre + 3er, 4to, 5to dígitos de la cédula
        $clientes = [
            [
                'id_persona' => 1, // Alex Jonfranc Madrid Marin (cédula 27391753)
                'codigo_acceso' => 'A27J391', 
                'fecha_inscripcion' => '2026-01-10'
            ],
            [
                'id_persona' => 2, // Glaivis Alejandra Saavedra Becerra (cédula 28100243)
                'codigo_acceso' => 'G28A100', 
                'fecha_inscripcion' => '2026-01-10'
            ],
            [
                'id_persona' => 3, // Sebastian De Jesus González Rojas (cédula 27790292)
                'codigo_acceso' => 'S27D790', 
                'fecha_inscripcion' => '2026-01-10'
            ],
            [
                'id_persona' => 4, // Christhian Alejandro Rauseo Castillo (cédula 25641157)
                'codigo_acceso' => 'C25A641', 
                'fecha_inscripcion' => '2026-01-10'
            ],
        ];

        $stmt = $this->pdo->prepare("INSERT INTO clientes (id_estatus, id_persona, codigo_acceso, fecha_inscripcion) 
                                    VALUES (1, :id_persona, :codigo_acceso, :fecha_inscripcion)");

        foreach ($clientes as $cliente) {
            $stmt->execute([
                ':id_persona' => $cliente['id_persona'],
                ':codigo_acceso' => $cliente['codigo_acceso'],
                ':fecha_inscripcion' => $cliente['fecha_inscripcion']
            ]);
        }
    }
}
