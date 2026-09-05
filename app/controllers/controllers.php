<?php

require_once __DIR__ . '/../interfaces/ControllerInterface.php';
require_once __DIR__ . '/../traits/ValidatorTrait.php';
require_once __DIR__ . '/../core/Response.php';
require_once __DIR__ . '/../models/models.php';
require_once __DIR__ . '/../models/permisosModel.php';
require_once __DIR__ . '/../models/rolesModel.php';
require_once __DIR__ . '/../models/usuariosModel.php';
require_once __DIR__ . '/../models/personasModels.php';
require_once __DIR__ . '/../models/clientesModel.php';
require_once __DIR__ . '/../models/entrenamientosModel.php';
require_once __DIR__ . '/../models/entrenadorModel.php';
require_once __DIR__ . '/../models/horarioEntrenamientoModel.php';
require_once __DIR__ . '/../models/horariosModel.php';
require_once __DIR__ . '/../models/pagosModel.php';
require_once __DIR__ . '/../models/planesModel.php';
require_once __DIR__ . '/../models/loginModel.php';

use App\Interfaces\ControllerInterface;
use App\Traits\ValidatorTrait;
use App\Core\Response;

/**
 * Class BaseController
 * Clase base para todos los controladores del sistema.
 * Proporciona validación, resolución polimórfica de modelos y formateo de respuestas.
 */
abstract class BaseController implements ControllerInterface
{
    use ValidatorTrait;

    protected PDO $pdo;

    /**
     * @param PDO|null $pdo Instancia de base de datos
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
     * Mapeo de nombres canónicos de modelos para permitir resolución polimórfica sin importar el casing.
     * @param string $model
     * @return object
     * @throws Exception
     */
    public function cargarModels(string $model): object
    {
        $aliasMap = [
            'personasmodel'              => 'PersonasModel',
            'personasmodels'             => 'PersonasModel',
            'clientesmodel'              => 'ClientesModel',
            'entrenadormodel'            => 'EntrenadorModel',
            'entrenadoresmodel'          => 'EntrenadorModel',
            'usuariosmodel'              => 'UsuariosModel',
            'pagosmodel'                 => 'PagosModel',
            'planesmodel'                => 'PlanesModel',
            'planmodel'                  => 'PlanesModel',
            'entrenamientosmodel'        => 'EntrenamientosModel',
            'horariosmodel'              => 'HorariosModel',
            'horarioentrenamientomodel'  => 'HorarioEntrenamientoModel',
            'entrenamientohorariosmodel' => 'HorarioEntrenamientoModel',
            'rolesmodel'                 => 'RolesModel',
            'permisosmodel'              => 'PermisosModel',
            'loginmodel'                 => 'LoginModel'
        ];

        $normalized = strtolower(trim($model));
        $targetClass = $aliasMap[$normalized] ?? $model;

        if (class_exists($targetClass)) {
            return new $targetClass($this->pdo);
        }

        if (class_exists($model)) {
            return new $model($this->pdo);
        }

        throw new Exception("El modelo '{$model}' no existe en el sistema.");
    }

    /**
     * Genera una respuesta serializada en JSON estándar para vistas o pasarelas AJAX.
     * @param bool $success
     * @param string $message
     * @param mixed $data
     * @return string
     */
    public function response(bool $success, string $message, mixed $data = null): string
    {
        $responseObj = new Response($success, $message, $data);
        return $responseObj->toJson();
    }

    /**
     * Retorna una instancia directa del Value Object Response.
     * @param bool $success
     * @param string $message
     * @param mixed $data
     * @return Response
     */
    public function makeResponse(bool $success, string $message, mixed $data = null): Response
    {
        return new Response($success, $message, $data);
    }
}

// Alias de compatibilidad hacia atrás
if (!class_exists('Controllers', false)) {
    class Controllers extends BaseController {}
}
