<?php

require_once __DIR__ . '/../core/conn.php';
require_once __DIR__ . '/seeder.php';
require_once __DIR__ . '/seederSedes.php';
require_once __DIR__ . '/seederRoles.php';
require_once __DIR__ . '/seederPermisos.php';
require_once __DIR__ . '/seederGeneros.php';
require_once __DIR__ . '/seederBancos.php';
require_once __DIR__ . '/seederEstatus.php';


$allSeeders = [
    new seederSedes($pdo),
    new seederRoles($pdo),
    new seederPermisos($pdo),
    new seederGeneros($pdo),
    new seederBancos($pdo),
    new seederEstatus($pdo)
];

foreach ($allSeeders as $seeder) {
    $seeder->runSeeder();
    
    echo "Seeder " . get_class($seeder) . " ejecutado exitosamente. <br>";
}