<?php

require_once __DIR__ . '/../core/conn.php';
require_once __DIR__ . '/seeder.php';

class SeederPersonas extends Seeder
{
    public function __construct($pdo)
    {
        parent::__construct($pdo);
    }

    public function runSeeder()
    {
        $data = [
            [
                'id_genero' => 1, // Masculino
                'id_estatus' => 1, // Activo
                'cedula_identidad' => '27391753',
                'primer_nombre' => 'Alex',
                'segundo_nombre' => 'Jonfranc',
                'primer_apellido' => 'Madrid',
                'segundo_apellido' => 'Marin',
                'fecha_nacimiento' => '1999-01-28',
                'email' => 'alexmadrid326@gmail.com',
                'telefono' => '0412-3345521',
                'direccion_habitacion' => 'Av. Francisco de Miranda, Edif. Centro Seguros La Paz, Piso 4, El Marqués, Caracas.',
            ],
            [
                'id_genero' => 2, // Femenino
                'id_estatus' => 1,
                'cedula_identidad' => '28100243',
                'primer_nombre' => 'Glaivis',
                'segundo_nombre' => 'Alejandra',
                'primer_apellido' => 'Saavedra',
                'segundo_apellido' => 'Becerra',
                'fecha_nacimiento' => '2000-09-07',
                'email' => 'gaby.rodriguez.93@outlook.com',
                'telefono' => '0414-2218899',
                'direccion_habitacion' => 'Residencias El Sol, Torre B, Apto 5-3, Caricuao, Caracas.',
            ],
            [
                'id_genero' => 1,
                'id_estatus' => 1,
                'cedula_identidad' => '27790292',
                'primer_nombre' => 'Sebastian',
                'segundo_nombre' => 'De Jesus',
                'primer_apellido' => 'González',
                'segundo_apellido' => 'Rojas',
                'fecha_nacimiento' => '2001-03-20',
                'email' => 'jose.altuve98@gmail.com',
                'telefono' => '0416-7781122',
                'direccion_habitacion' => 'Barrio José Félix Ribas, Zona 6, Petare, Municipio Sucre.',
            ],
            [
                'id_genero' => 1,
                'id_estatus' => 1,
                'cedula_identidad' => '25641157',
                'primer_nombre' => 'Christhian',
                'segundo_nombre' => 'Alejandro',
                'primer_apellido' => 'Rauseo',
                'segundo_apellido' => 'Castillo',
                'fecha_nacimiento' => '1994-01-14',
                'email' => 'adri.valen.gp@hotmail.com',
                'telefono' => '0424-1156677',
                'direccion_habitacion' => 'Urb. La Urbina, Calle 3, Edif. Altamira, Piso 2, Caracas.',
            ],
            [
                'id_genero' => 1,
                'id_estatus' => 1,
                'cedula_identidad' => '32145639',
                'primer_nombre' => 'Yilbert',
                'segundo_nombre' => 'Javier',
                'primer_apellido' => 'Gonzalez',
                'segundo_apellido' => 'Avilan',
                'fecha_nacimiento' => '2006-01-19',
                'email' => 'lcastillo_75@gmail.com',
                'telefono' => '0412-9954433',
                'direccion_habitacion' => 'Av. Baralt, Esquina de Quinta Crespo, Edif. El Comercio, Piso 1, Caracas.',
            ]
        ];

        foreach ($data as $persona) {
            $query = "INSERT INTO personas (
                        id_genero, id_estatus, cedula_identidad, primer_nombre, segundo_nombre, 
                        primer_apellido, segundo_apellido, fecha_nacimiento, email, telefono, direccion_habitacion
                      ) VALUES (
                        :id_genero, :id_estatus, :cedula_identidad, :primer_nombre, :segundo_nombre, 
                        :primer_apellido, :segundo_apellido, :fecha_nacimiento, :email, :telefono, :direccion_habitacion
                      )";
            
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([
                ':id_genero'          => $persona['id_genero'],
                ':id_estatus'         => $persona['id_estatus'],
                ':cedula_identidad'   => $persona['cedula_identidad'],
                ':primer_nombre'      => $persona['primer_nombre'],
                ':segundo_nombre'     => $persona['segundo_nombre'],
                ':primer_apellido'    => $persona['primer_apellido'],
                ':segundo_apellido'   => $persona['segundo_apellido'],
                ':fecha_nacimiento'   => $persona['fecha_nacimiento'],
                ':email'              => $persona['email'],
                ':telefono'           => $persona['telefono'],
                ':direccion_habitacion'=> $persona['direccion_habitacion']
            ]);
        }
    }
}
