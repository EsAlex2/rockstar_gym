<?php
ob_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//Constantes para iniciar la base de datos 
// define('BD_HOST', 'localhost');
// define('BD_NAME', 'admin_sql');
// define('BD_USER', 'postgres');
// define('BD_PASS', 'qwerty2801**');
// define('BD_PORT', '5432');
// define('BD', 'pgsql');

define('BD_HOST', 'localhost');
define('BD_NAME', 'rockstar');
define('BD_USER', 'root');
define('BD_PASS', 'admin123');
define('BD_PORT', '3306');
define('BD', 'mysql');


//URL BASE PARA USO GLOBAL EN EL SISTEMA

define('URL_BASE', 'http://localhost/rockstar_gym');

//nombre del sitio 
define('SITE_NAME', 'ROCKSTAR GYM');