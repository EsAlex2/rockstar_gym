<?php

require_once __DIR__ . '/../core/conn.php';
require_once __DIR__ . '/seeder.php';

class seederPermisosRoles extends Seeder
{
    public function __construct($pdo)
    {
        parent::__construct($pdo);
    }

    public function runSeeder()
    {
        try {
            // 1. Iniciamos una transacción para asegurar la integridad de los datos
            $this->pdo->beginTransaction();

            /**
             * 3. Definición de la matriz de permisos
             * Formato: [id_rol, id_permiso]
             * * Roles sugeridos (ajusta según tu DB):
             * 1: ROOT, 2: ADMINISTRADOR, 3: ENTRENADOR, 4: CLIENTE
             */
            $data = [
                // --- ROOT (Acceso Total: 1 al 10) ---
                [1, 1], [1, 2], [1, 3], [1, 4], [1, 5], [1, 6], [1, 7], [1, 8], [1, 9], [1, 10],

                // --- ADMINISTRADOR (Gestión operativa) ---
                [2, 1], [2, 2], [2, 3], [2, 4], [2, 5], [2, 6],

                // --- ENTRENADOR (Gestión de entrenamiento y clientes) ---
                [3, 3], [3, 4], [3, 6], [3, 7],

                // --- CLIENTE (Solo lectura de su información y pagos) ---
                [4, 4], [4, 8]
            ];

            // 4. Inserción preparada
            $stmt = $this->pdo->prepare("INSERT INTO roles_permisos (id_rol, id_permiso) VALUES (:rol, :permiso)");

            foreach ($data as $relacion) {
                $stmt->execute([
                    ':rol'     => $relacion[0],
                    ':permiso' => $relacion[1]
                ]);
            }

            // 5. Confirmamos cambios
            $this->pdo->commit();
            echo "Seeder de Roles y Permisos ejecutado correctamente con " . count($data) . " relaciones.";

        } catch (Exception $e) {
            // Revertimos cambios si hay algún error
            $this->pdo->rollBack();
            echo "Error al ejecutar el seeder: " . $e->getMessage();
        }
    }
}

$pruebas = new seederPermisosRoles($pdo);

$pruebas->runSeeder();