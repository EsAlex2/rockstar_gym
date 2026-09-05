<?php

namespace App\Interfaces;

use PDO;

/**
 * Interface ModelInterface
 * Contrato base que deben satisfacer todos los modelos de acceso a datos.
 */
interface ModelInterface
{
    /**
     * Obtiene el objeto de conexión PDO del modelo.
     * @return PDO
     */
    public function getPdo(): PDO;

    /**
     * Establece el objeto de conexión PDO.
     * @param PDO $pdo
     * @return void
     */
    public function setPdo(PDO $pdo): void;

    /**
     * Retorna el nombre de la tabla principal asociada al modelo si aplica.
     * @return string
     */
    public function getTableName(): string;
}
