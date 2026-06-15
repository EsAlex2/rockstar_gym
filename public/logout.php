<?php
require_once __DIR__ . '/../config/init.php';
require_once __DIR__ . '/../app/controllers/loginController.php';

$auth = new loginController(); 
$auth->logout();