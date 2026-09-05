<?php
ob_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Constantes de configuración de la Base de Datos
if (!defined('BD_HOST')) {
    define('BD_HOST', '127.0.0.1');
}
if (!defined('BD_NAME')) {
    define('BD_NAME', 'rockstar');
}
if (!defined('BD_USER')) {
    define('BD_USER', 'root');
}
if (!defined('BD_PASS')) {
    define('BD_PASS', '');
}
if (!defined('BD_PORT')) {
    define('BD_PORT', '3306');
}
if (!defined('BD')) {
    define('BD', 'mysql');
}

// URL Base para uso global en el sistema
if (!defined('URL_BASE')) {
    define('URL_BASE', 'http://localhost/rockstar_gym');
}

// Nombre de la aplicación
if (!defined('SITE_NAME')) {
    define('SITE_NAME', 'ROCKSTAR GYM');
}

// ==============================================================================
// AUTOLOADER PSR-4 Y REGISTRO DE CLASES ORIENTADAS A OBJETOS
// ==============================================================================
spl_autoload_register(function ($className) {
    // 1. Mapeo de namespaces PSR-4 App\...
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/../app/';

    $len = strlen($prefix);
    if (strncmp($prefix, $className, $len) === 0) {
        $relativeClass = substr($className, $len);
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }

    // 2. Mapeo específico de clases legacy a archivos físicos
    $classFileMap = [
        'personasmodel'              => __DIR__ . '/../app/models/personasModels.php',
        'personasmodels'             => __DIR__ . '/../app/models/personasModels.php',
        'clientesmodel'              => __DIR__ . '/../app/models/clientesModel.php',
        'entrenadormodel'            => __DIR__ . '/../app/models/entrenadorModel.php',
        'entrenadoresmodel'          => __DIR__ . '/../app/models/entrenadorModel.php',
        'usuariosmodel'              => __DIR__ . '/../app/models/usuariosModel.php',
        'pagosmodel'                 => __DIR__ . '/../app/models/pagosModel.php',
        'planesmodel'                => __DIR__ . '/../app/models/planesModel.php',
        'planmodel'                  => __DIR__ . '/../app/models/planesModel.php',
        'entrenamientosmodel'        => __DIR__ . '/../app/models/entrenamientosModel.php',
        'horariosmodel'              => __DIR__ . '/../app/models/horariosModel.php',
        'horarioentrenamientomodel'  => __DIR__ . '/../app/models/horarioEntrenamientoModel.php',
        'entrenamientohorariosmodel' => __DIR__ . '/../app/models/horarioEntrenamientoModel.php',
        'rolesmodel'                 => __DIR__ . '/../app/models/rolesModel.php',
        'permisosmodel'              => __DIR__ . '/../app/models/permisosModel.php',
        'loginmodel'                 => __DIR__ . '/../app/models/loginModel.php',
        'basemodel'                  => __DIR__ . '/../app/models/models.php',
        'model'                      => __DIR__ . '/../app/models/models.php',
        
        'personascontroller'         => __DIR__ . '/../app/controllers/personasController.php',
        'clientescontroller'         => __DIR__ . '/../app/controllers/clientesController.php',
        'entrenadorescontroller'     => __DIR__ . '/../app/controllers/entrenadoresController.php',
        'usuarioscontroller'         => __DIR__ . '/../app/controllers/userController.php',
        'usercontroller'             => __DIR__ . '/../app/controllers/userController.php',
        'pagoscontroller'            => __DIR__ . '/../app/controllers/pagosController.php',
        'planescontroller'           => __DIR__ . '/../app/controllers/planesController.php',
        'entrenamientoscontroller'   => __DIR__ . '/../app/controllers/entrenamientosController.php',
        'horarioscontroller'         => __DIR__ . '/../app/controllers/horariosController.php',
        'horarioentrenamientocontroller' => __DIR__ . '/../app/controllers/horarioEntrenamientoController.php',
        'entrenamientohorariocontroller' => __DIR__ . '/../app/controllers/horarioEntrenamientoController.php',
        'rolescontroller'            => __DIR__ . '/../app/controllers/rolesController.php',
        'permisoscontroller'         => __DIR__ . '/../app/controllers/permisosController.php',
        'logincontroller'            => __DIR__ . '/../app/controllers/loginController.php',
        'basecontroller'             => __DIR__ . '/../app/controllers/controllers.php',
        'controllers'                => __DIR__ . '/../app/controllers/controllers.php',

        'baseseeder'                 => __DIR__ . '/../app/seeders/seeder.php',
        'seeder'                     => __DIR__ . '/../app/seeders/seeder.php',
    ];

    $lowerClass = strtolower($className);
    if (isset($classFileMap[$lowerClass]) && file_exists($classFileMap[$lowerClass])) {
        require_once $classFileMap[$lowerClass];
        return;
    }

    // 3. Mapeo genérico por directorios
    $directories = [
        __DIR__ . '/../app/interfaces/',
        __DIR__ . '/../app/traits/',
        __DIR__ . '/../app/core/',
        __DIR__ . '/../app/services/',
        __DIR__ . '/../app/models/',
        __DIR__ . '/../app/controllers/',
        __DIR__ . '/../app/seeders/'
    ];

    foreach ($directories as $dir) {
        $possibleFiles = [
            $dir . $className . '.php',
            $dir . lcfirst($className) . '.php',
            $dir . strtolower($className) . '.php',
        ];

        foreach ($possibleFiles as $filePath) {
            if (file_exists($filePath)) {
                require_once $filePath;
                return;
            }
        }
    }
});

// Cargar la conexión global PDO para mantener compatibilidad total con vistas heredadas
require_once __DIR__ . '/../app/core/conn.php';