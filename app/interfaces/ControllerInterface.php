<?php

namespace App\Interfaces;

use PDO;

/**
 * Interface ControllerInterface
 * Contrato base para los controladores del sistema.
 */
interface ControllerInterface
{
    /**
     * Carga e instancia un modelo por nombre de clase.
     * @param string $model
     * @return object
     */
    public function cargarModels(string $model): object;

    /**
     * Formatea una respuesta estándar para las vistas o clientes API.
     * @param bool $success
     * @param string $message
     * @param mixed $data
     * @return string
     */
    public function response(bool $success, string $message, mixed $data = null): string;
}
