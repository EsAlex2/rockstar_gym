<?php

namespace App\Interfaces;

/**
 * Interface CrudInterface
 * Contrato genérico para modelos que implementan operaciones CRUD (Create, Read, Update, Delete).
 */
interface CrudInterface
{
    /**
     * Obtiene todos los registros del recurso.
     * @return array
     */
    public function all(): array;

    /**
     * Busca un registro por su identificador primario.
     * @param int $id
     * @return array|null
     */
    public function findById(int $id): ?array;

    /**
     * Elimina un registro por su identificador primario.
     * @param int $id
     * @return array
     */
    public function delete(int $id): array;
}
