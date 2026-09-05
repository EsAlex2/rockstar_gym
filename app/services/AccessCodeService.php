<?php

namespace App\Services;

use App\Interfaces\AccessCodeInterface;

/**
 * Class AccessCodeService
 * Servicio encargado de generar códigos de acceso únicos para los clientes del gimnasio.
 * Implementa AccessCodeInterface permitiendo el principio de inversión de dependencias y polimorfismo.
 */
class AccessCodeService implements AccessCodeInterface
{
    /**
     * Genera un código de acceso combinando las iniciales del nombre con dígitos de la cédula.
     * Ejemplo: Alex (A) + Cédula '27391753' (27) + Jonfranc (J) + '391' -> A27J391
     *
     * @param array $persona Datos con ['primer_nombre', 'segundo_nombre', 'cedula_identidad']
     * @return string
     */
    public function generate(array $persona): string
    {
        $primerNombre = strtoupper(trim($persona['primer_nombre'] ?? ''));
        $inicial1     = mb_substr($primerNombre, 0, 1, 'UTF-8') ?: 'X';

        $cedulaLimpia = preg_replace('/[^0-9]/', '', (string)($persona['cedula_identidad'] ?? '00000'));
        $primeros2    = substr($cedulaLimpia, 0, 2) ?: '00';
        $siguientes3  = substr($cedulaLimpia, 2, 3) ?: '000';

        $segundoNombre = strtoupper(trim($persona['segundo_nombre'] ?? ''));
        $inicial2      = !empty($segundoNombre) ? mb_substr($segundoNombre, 0, 1, 'UTF-8') : 'Z';

        return $inicial1 . $primeros2 . $inicial2 . $siguientes3;
    }
}
