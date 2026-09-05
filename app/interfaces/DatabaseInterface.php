<?php

namespace App\Interfaces;

use PDO;

/**
 * Interface DatabaseInterface
 * Define el contrato para la gestión de conexiones a la base de datos.
 */
interface DatabaseInterface
{
    /**
     * Obtiene la instancia única de conexión PDO.
     * @return PDO
     */
    public function getConnection(): PDO;

    /**
     * Ejecuta una verificación del estado de la conexión activa.
     * @return bool
     */
    public function isConnected(): bool;
}
