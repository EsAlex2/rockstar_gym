<?php

require_once __DIR__ . '/../core/conn.php';
require_once __DIR__ . '/seeder.php';

class SeederUsuarios extends Seeder
{
    public function __construct($pdo)
    {
        parent::__construct($pdo);
    }

    public function runSeeder()
    {
        // Usuarios necesarios para el correcto funcionamiento de SeederPagos
        // id_rol = 1 (Root), id_rol = 2 (Administrador)
        // id_estatus = 1 (Activo)
        $usuarios = [
            [
                'id_persona' => 1, 
                'email_user' => 'alexmadrid326@gmail.com', 
                'password_hash' => password_hash('Cliente2026*', PASSWORD_BCRYPT), 
                'id_rol' => 1, 
                'id_estatus' => 1
            ],
            [
                'id_persona' => 3, 
                'email_user' => 'jose.altuve98@gmail.com', 
                'password_hash' => password_hash('Cliente2026*', PASSWORD_BCRYPT), 
                'id_rol' => 2, 
                'id_estatus' => 1
            ],
            [
                'id_persona' => 4, 
                'email_user' => 'adri.valen.gp@hotmail.com', 
                'password_hash' => password_hash('Cliente2026*', PASSWORD_BCRYPT), 
                'id_rol' => 2, 
                'id_estatus' => 1
            ],
        ];

        $stmt = $this->pdo->prepare("INSERT INTO usuarios (id_persona, email_user, password_hash, id_rol, id_estatus) 
                                    VALUES (:id_persona, :email_user, :password_hash, :id_rol, :id_estatus)");

        foreach ($usuarios as $u) {
            $stmt->execute([
                ':id_persona' => $u['id_persona'],
                ':email_user' => $u['email_user'],
                ':password_hash' => $u['password_hash'],
                ':id_rol' => $u['id_rol'],
                ':id_estatus' => $u['id_estatus']
            ]);
        }
    }
}
