<?php
require_once __DIR__ . '/../config/init.php';
require_once __DIR__ . '/../app/controllers/loginController.php';

// Pasamos la instancia global de la base de datos de manera limpia al constructor
$auth = new LoginController($pdo);
$auth->login();