<?php

require_once __DIR__ . '/models.php';

/**
 * Class LoginModel
 * Modelo especializado en la autenticación, resolución de identidad y consulta de permisos de sesión.
 * Extiende de BaseModel.
 */
class LoginModel extends BaseModel
{
    protected string $table = 'usuarios';

    public function __construct(?PDO $pdo = null)
    {
        parent::__construct($pdo);
    }

    /**
     * Busca todos los candidatos de usuario que coincidan con la identidad proporcionada:
     * correo electrónico, prefijo de usuario, cédula de identidad (numérica o con formato) o alias de rol.
     * Incluye el estatus de la cuenta para que el controlador pueda validar e informar al usuario.
     *
     * @param string $identidad
     * @return array
     */
    public function buscarCandidatosPorIdentidad(string $identidad): array
    {
        try {
            $idTrim = trim($identidad);
            if ($idTrim === '') {
                return [];
            }

            $cedulaDigits = preg_replace('/[^0-9]/', '', $idTrim);

            $sql = "SELECT 
                        u.*, 
                        p.primer_nombre, 
                        p.primer_apellido, 
                        p.cedula_identidad,
                        r.nombre_rol,
                        COALESCE(e.nombre_estatus, 'Activo') AS nombre_estatus
                    FROM usuarios u
                    INNER JOIN personas p ON u.id_persona = p.id
                    INNER JOIN roles r ON u.id_rol = r.id
                    LEFT JOIN estatus e ON u.id_estatus = e.id
                    WHERE (
                        LOWER(u.email_user) = LOWER(:id1)
                        OR LOWER(SUBSTRING_INDEX(u.email_user, '@', 1)) = LOWER(:id2)
                        OR p.cedula_identidad = :id3
                        " . ($cedulaDigits !== '' ? "OR p.cedula_identidad = :id_cedula " : "") . "
                        OR (LOWER(:id4) = 'root' AND LOWER(r.nombre_rol) = 'root')
                        OR (LOWER(:id5) = 'admin' AND LOWER(r.nombre_rol) IN ('root', 'administrador'))
                    )
                    ORDER BY (CASE WHEN u.id_estatus = 1 THEN 0 ELSE 1 END), u.id ASC";

            $params = [
                ':id1' => $idTrim,
                ':id2' => $idTrim,
                ':id3' => $idTrim,
                ':id4' => $idTrim,
                ':id5' => $idTrim,
            ];
            if ($cedulaDigits !== '') {
                $params[':id_cedula'] = $cedulaDigits;
            }

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Busca un usuario por su identidad. Devuelve el primer candidato coincidente o false.
     * Mantiene retrocompatibilidad con invocaciones directas.
     *
     * @param string $identidad
     * @return array|false
     */
    public function buscarPorIdentidad(string $identidad): array|false
    {
        $candidatos = $this->buscarCandidatosPorIdentidad($identidad);
        return !empty($candidatos) ? $candidatos[0] : false;
    }

    /**
     * Obtiene el listado plano de permisos asignados al rol del usuario.
     * @param int $id_rol
     * @return array
     */
    public function obtenerPermisosPorRol(int $id_rol): array
    {
        try {
            $sql = "SELECT p.nombre_permiso 
                    FROM permisos p
                    INNER JOIN roles_permisos rp ON p.id = rp.id_permiso
                    WHERE rp.id_rol = :id_rol";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id_rol' => $id_rol]);

            return $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
        } catch (PDOException $e) {
            return [];
        }
    }
}