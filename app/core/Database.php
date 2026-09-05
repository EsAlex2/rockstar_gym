<?php

namespace App\Core;

use App\Interfaces\DatabaseInterface;
use PDO;
use PDOException;

/**
 * Class Database
 * Implementa el patrón Singleton para garantizar una única conexión PDO compartida
 * y optimizar los recursos del servidor web.
 */
class Database implements DatabaseInterface
{
    private static ?Database $instance = null;
    private ?PDO $pdo = null;

    /**
     * Constructor privado para prevenir instanciación externa (Patrón Singleton).
     */
    private function __construct()
    {
        // Cargar variables de configuración si no están definidas
        if (!defined('BD_HOST')) {
            require_once __DIR__ . '/../../config/init.php';
        }

        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            BD_HOST,
            BD_PORT,
            BD_NAME
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ];

        try {
            $this->pdo = new PDO($dsn, BD_USER, BD_PASS, $options);
        } catch (PDOException $e) {
            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                "status"  => "error",
                "message" => "Error de conexión a la base de datos: " . $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    /**
     * Previene la clonación del objeto (Patrón Singleton).
     */
    private function __clone() {}

    /**
     * Previene la deserialización del objeto (Patrón Singleton).
     */
    public function __wakeup()
    {
        throw new \Exception("No se puede deserializar una instancia de Database.");
    }

    /**
     * Obtiene la instancia única de Database.
     * @return Database
     */
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Obtiene la conexión PDO activa.
     * @return PDO
     */
    public function getConnection(): PDO
    {
        return $this->pdo;
    }

    /**
     * Comprueba si la conexión a base de datos está disponible.
     * @return bool
     */
    public function isConnected(): bool
    {
        return $this->pdo !== null;
    }
}
