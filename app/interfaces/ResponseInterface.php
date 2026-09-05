<?php

namespace App\Interfaces;

/**
 * Interface ResponseInterface
 * Contrato para objetos de respuesta unificados del sistema.
 */
interface ResponseInterface
{
    /**
     * Determina si la respuesta representa una operación exitosa.
     * @return bool
     */
    public function isSuccess(): bool;

    /**
     * Obtiene el mensaje descriptivo de la respuesta.
     * @return string
     */
    public function getMessage(): string;

    /**
     * Obtiene la carga útil de datos asociada.
     * @return mixed
     */
    public function getData(): mixed;

    /**
     * Convierte la respuesta a un arreglo asociativo estándar.
     * @return array
     */
    public function toArray(): array;

    /**
     * Serializa la respuesta a formato JSON.
     * @return string
     */
    public function toJson(): string;
}
