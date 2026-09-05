<?php

namespace App\Interfaces;

/**
 * Interface SeederInterface
 * Contrato estandarizado para los sembradores de base de datos.
 */
interface SeederInterface
{
    /**
     * Ejecuta el proceso de siembra de datos.
     * @return void
     */
    public function runSeeder(): void;
}
