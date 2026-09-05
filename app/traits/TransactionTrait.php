<?php

namespace App\Traits;

use PDO;
use Throwable;

/**
 * Trait TransactionTrait
 * Proporciona un ejecutor de transacciones ACID seguro y encapsulado.
 */
trait TransactionTrait
{
    /**
     * Ejecuta una operación atómica dentro de una transacción de base de datos.
     * Si la operación tiene éxito, realiza commit; si ocurre una excepción, hace rollback.
     *
     * @param PDO $pdo Instancia de PDO
     * @param callable $callback Función anónima con la lógica transaccional
     * @return mixed Retorno del callback o array con error
     */
    public function executeTransaction(PDO $pdo, callable $callback): mixed
    {
        try {
            $pdo->beginTransaction();
            $result = $callback($pdo);
            $pdo->commit();
            return $result;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return [
                "error" => "Error en la transacción de base de datos: " . $e->getMessage()
            ];
        }
    }
}
