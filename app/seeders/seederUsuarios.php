<?php

require_once __DIR__ . '/../core/conn.php';
require_once __DIR__ . '/seeder.php';

class SeederUsuarios extends Seeder
{
    public function __construct($pdo)
    {
        parent::__construct($pdo);
    }

    public function runSeeder(): void
    {
        // Usuarios necesarios para el correcto funcionamiento del sistema
        // Rol 1: Root, Rol 2: Administrador
        $usuarios = [
            [
                'email_persona'  => 'alexmadrid326@gmail.com',
                'cedula'         => '27391753',
                'email_user'     => 'alexmadrid326@gmail.com', 
                'password'       => '123456789', 
                'rol_nombre'     => 'Root',
                'id_rol_default' => 1,
                'id_estatus'     => 1
            ],
            [
                'email_persona'  => 'jose.altuve98@gmail.com',
                'cedula'         => '27790292',
                'email_user'     => 'jose.altuve98@gmail.com', 
                'password'       => '12345678', 
                'rol_nombre'     => 'Administrador',
                'id_rol_default' => 2,
                'id_estatus'     => 1
            ],
            [
                'email_persona'  => 'adri.valen.gp@hotmail.com',
                'cedula'         => '25641157',
                'email_user'     => 'adri.valen.gp@hotmail.com', 
                'password'       => '1234567', 
                'rol_nombre'     => 'Administrador',
                'id_rol_default' => 2,
                'id_estatus'     => 1
            ],
        ];

        foreach ($usuarios as $u) {
            // 1. Resolver id_persona dinámicamente
            $stmtPersona = $this->pdo->prepare("SELECT id FROM personas WHERE email = :email OR cedula_identidad = :cedula LIMIT 1");
            $stmtPersona->execute([':email' => $u['email_persona'], ':cedula' => $u['cedula']]);
            $id_persona = $stmtPersona->fetchColumn();

            if (!$id_persona) {
                continue;
            }

            // 2. Resolver id_rol dinámicamente
            $stmtRol = $this->pdo->prepare("SELECT id FROM roles WHERE LOWER(nombre_rol) = LOWER(:rol) LIMIT 1");
            $stmtRol->execute([':rol' => $u['rol_nombre']]);
            $id_rol = $stmtRol->fetchColumn() ?: $u['id_rol_default'];

            $password_hash = password_hash($u['password'], PASSWORD_BCRYPT);

            // 3. Verificar existencia previa para garantizar idempotencia
            $stmtCheck = $this->pdo->prepare("SELECT id FROM usuarios WHERE email_user = :email OR id_persona = :id_persona LIMIT 1");
            $stmtCheck->execute([':email' => $u['email_user'], ':id_persona' => $id_persona]);
            $existente = $stmtCheck->fetchColumn();

            if ($existente) {
                $stmtUpdate = $this->pdo->prepare("UPDATE usuarios SET password_hash = :hash, id_rol = :id_rol, id_estatus = :id_estatus, email_user = :email WHERE id = :id");
                $stmtUpdate->execute([
                    ':hash'       => $password_hash,
                    ':id_rol'     => $id_rol,
                    ':id_estatus' => $u['id_estatus'],
                    ':email'      => $u['email_user'],
                    ':id'         => $existente
                ]);
            } else {
                $stmtInsert = $this->pdo->prepare("INSERT INTO usuarios (id_persona, email_user, password_hash, id_rol, id_estatus) 
                                                   VALUES (:id_persona, :email_user, :password_hash, :id_rol, :id_estatus)");
                $stmtInsert->execute([
                    ':id_persona'    => $id_persona,
                    ':email_user'    => $u['email_user'],
                    ':password_hash' => $password_hash,
                    ':id_rol'        => $id_rol,
                    ':id_estatus'    => $u['id_estatus']
                ]);
            }
        }
    }
}
