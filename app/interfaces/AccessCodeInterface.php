<?php

namespace App\Interfaces;

/**
 * Interface AccessCodeInterface
 * Contrato para estrategias y servicios de generación de códigos de acceso biométricos/manuales.
 */
interface AccessCodeInterface
{
    /**
     * Genera un código de acceso único a partir de los datos personales.
     * @param array $persona Datos de la persona (primer_nombre, segundo_nombre, cedula_identidad)
     * @return string Código de acceso generado
     */
    public function generate(array $persona): string;
}
