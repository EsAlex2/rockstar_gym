<?php

require_once __DIR__ . '/../../config/init.php';
require_once __DIR__ . '/../interfaces/DatabaseInterface.php';
require_once __DIR__ . '/Database.php';

use App\Core\Database;

// Instanciación Singleton segura de la conexión PDO para compatibilidad global
$pdo = Database::getInstance()->getConnection();