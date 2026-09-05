<?php

require_once __DIR__ . '/../core/conn.php';
require_once __DIR__ . '/../interfaces/ModelInterface.php';
require_once __DIR__ . '/../interfaces/CrudInterface.php';
require_once __DIR__ . '/../traits/TransactionTrait.php';

use App\Interfaces\ModelInterface;
use App\Interfaces\CrudInterface;
use App\Traits\TransactionTrait;

/**
 * Class BaseModel
 * Clase base abstracta que encapsula las operaciones fundamentales de acceso a datos,
 * consultas preparadas, transacciones y control de errores.
 */
abstract class BaseModel implements ModelInterface, CrudInterface
{
    use TransactionTrait;

    protected PDO $pdo;
    protected string $table = '';

    /**
     * @param PDO|null $pdo Instancia de conexión; si es null, recurre a la instancia global
     */
    public function __construct(?PDO $pdo = null)
    {
        if ($pdo !== null) {
            $this->pdo = $pdo;
        } else {
            global $pdo;
            $this->pdo = $pdo ?? \App\Core\Database::getInstance()->getConnection();
        }
    }

    /**
     * Obtiene el objeto de conexión PDO.
     * @return PDO
     */
    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    /**
     * Asigna un objeto PDO al modelo.
     * @param PDO $pdo
     */
    public function setPdo(PDO $pdo): void
    {
        $this->pdo = $pdo;
    }

    /**
     * Retorna el nombre de la tabla principal asociada al modelo.
     * @return string
     */
    public function getTableName(): string
    {
        return $this->table;
    }

    /**
     * Ejecuta una consulta SELECT y retorna una única fila asociativa o null.
     * @param string $sql
     * @param array $params
     * @return array|null
     */
    protected function selectOne(string $sql, array $params = []): ?array
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result !== false ? $result : null;
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * Ejecuta una consulta SELECT y retorna todas las filas asociadas.
     * @param string $sql
     * @param array $params
     * @return array
     */
    protected function selectAll(string $sql, array $params = []): array
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Ejecuta una sentencia INSERT/UPDATE/DELETE.
     * @param string $sql
     * @param array $params
     * @return bool
     */
    protected function executeQuery(string $sql, array $params = []): bool
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Verifica la existencia de registros bajo una condición SQL.
     * @param string $table
     * @param string $condition Ejemplo: "cedula_identidad = :cedula"
     * @param array $params
     * @return bool
     */
    public function existsWhere(string $table, string $condition, array $params = []): bool
    {
        try {
            $sql = "SELECT COUNT(*) FROM {$table} WHERE {$condition}";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return (int)$stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Cuenta registros bajo una condición SQL dada.
     * @param string $table
     * @param string $condition
     * @param array $params
     * @return int
     */
    public function countWhere(string $table, string $condition = '1=1', array $params = []): int
    {
        try {
            $sql = "SELECT COUNT(*) FROM {$table} WHERE {$condition}";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }

    /**
     * Ejecuta una función callback dentro de una transacción segura.
     * @param callable $callback
     * @return mixed
     */
    public function transaction(callable $callback): mixed
    {
        return $this->executeTransaction($this->pdo, $callback);
    }

    /**
     * Helper para formatear mensajes de error consistentes.
     * @param string $context
     * @param Throwable $e
     * @return array
     */
    protected function formatError(string $context, Throwable $e): array
    {
        return [
            "error" => "Error en {$context}: " . $e->getMessage()
        ];
    }

    /**
     * Implementación por defecto de CrudInterface::all
     * @return array
     */
    public function all(): array
    {
        if (empty($this->table)) {
            return [];
        }
        return $this->selectAll("SELECT * FROM {$this->table} ORDER BY id DESC");
    }

    /**
     * Implementación por defecto de CrudInterface::findById
     * @param int $id
     * @return array|null
     */
    public function findById(int $id): ?array
    {
        if (empty($this->table)) {
            return null;
        }
        return $this->selectOne("SELECT * FROM {$this->table} WHERE id = :id", [':id' => $id]);
    }

    /**
     * Implementación por defecto de CrudInterface::delete
     * @param int $id
     * @return array
     */
    public function delete(int $id): array
    {
        if (empty($this->table)) {
            return ["error" => "No se ha configurado la tabla del modelo."];
        }

        try {
            $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE id = :id");
            $stmt->execute([':id' => $id]);
            return ["success" => true, "message" => "Registro eliminado correctamente."];
        } catch (PDOException $e) {
            return $this->formatError("eliminar registro", $e);
        }
    }
}

// Alias de compatibilidad hacia atrás
if (!class_exists('Model', false)) {
    class Model extends BaseModel {}
}