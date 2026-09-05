<?php

require_once __DIR__ . '/../core/conn.php';
require_once __DIR__ . '/../interfaces/SeederInterface.php';

use App\Interfaces\SeederInterface;

/**
 * Class BaseSeeder
 * Clase base para la ejecución polimórfica de sembradores de datos iniciales (Seeders).
 */
abstract class BaseSeeder implements SeederInterface
{
    protected PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        if ($pdo !== null) {
            $this->pdo = $pdo;
        } else {
            global $pdo;
            $this->pdo = $pdo ?? \App\Core\Database::getInstance()->getConnection();
        }
    }

    /**
     * Método polimórfico de ejecución que debe implementar cada seeder hijo.
     */
    abstract public function runSeeder(): void;
}

/**
 * Alias de compatibilidad hacia atrás
 */
class Seeder extends BaseSeeder
{
    public function runSeeder(): void {}
}