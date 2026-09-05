<?php

require_once __DIR__ . '/../core/conn.php';
require_once __DIR__ . '/seeder.php';

class seederGeneros extends Seeder
{
    public function __construct($pdo)
    {
        parent::__construct($pdo);
    }

    public function runSeeder(): void
    {
        $generosList = [
            ['descripcion' => 'Masculino'],
            ['descripcion' => 'Femenino'],
            ['descripcion' => 'Otro']
        ];

        foreach ($generosList as $genero) {
            $stmt = $this->pdo->prepare("INSERT INTO generos (descripcion) VALUES (:descripcion)");
            $stmt->execute([
                ':descripcion' => $genero['descripcion']
            ]);
        }
    }
}
