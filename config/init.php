<?php 

    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    //Constantes para iniciar la base de datos 
    define('BD_HOST', 'localhost');
    define('BD_NAME', 'admin_sql');
    define('BD_USER', 'postgres');
    define('BD_PASS', 'qwerty2801**');
    define('BD_PORT', '5432');
    define('BD', 'pgsql');

    //URL BASE PARA USO GLOBAL EN EL SISTEMA

    define('URL_BASE', 'http://localhost/gym'); 

    //nombre del sitio 
    define('SITE_NAME', 'ROCKSTAR GYM');