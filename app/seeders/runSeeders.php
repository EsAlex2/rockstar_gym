<?php

require_once __DIR__ . '/../core/conn.php';
require_once __DIR__ . '/seeder.php';
require_once __DIR__ . '/seederSedes.php';
require_once __DIR__ . '/seederRoles.php';
require_once __DIR__ . '/seederPermisos.php';
require_once __DIR__ . '/seederGeneros.php';
require_once __DIR__ . '/seederBancos.php';
require_once __DIR__ . '/seederEstatus.php';
require_once __DIR__ . '/seederPermisosRoles.php';
require_once __DIR__ . '/personalGym.php';
require_once __DIR__ . '/seedersPlanes.php';
require_once __DIR__ . '/seederClientes.php';
require_once __DIR__ . '/seederUsuarios.php';
require_once __DIR__ . '/seederClientesPlan.php';
require_once __DIR__ . '/pagosEmulados.php';

$allSeeders = [
    new seederSedes($pdo),
    new seederEstatus($pdo),
    new seederRoles($pdo),
    new seederPermisos($pdo),
    new seederGeneros($pdo),
    new seederBancos($pdo),
    new seederPermisosRoles($pdo),
    new SeederPersonas($pdo),
    new SeederUsuarios($pdo),
    new SeederClientes($pdo),
    new SeederPlanes($pdo),
    new SeederClientesPlanes($pdo),
    new SeederPagos($pdo)
];

foreach ($allSeeders as $seeder) {
    $seeder->runSeeder();
    
    echo "Seeder " . get_class($seeder) . " ejecutado exitosamente. <br>";
}