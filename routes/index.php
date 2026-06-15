<?php
// public/index.php (Punto de entrada único)
require_once __DIR__ . '/../config/init.php';
require_once __DIR__ . '/../core/conn.php';

$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$base_path = '/'; 
$route = str_replace($base_path, '/', $request_uri);
$route = rtrim($route, '/') ?: '/';


$method = $_SERVER['REQUEST_METHOD'];


switch ($route) {
    case '/':
        if ($method === 'GET') {
            // Cargar directamente la vista del Login (Index)
            require_once __DIR__ . '/../public/index.php';
        }
        break;

    case '/home':
        if ($method === 'POST') {

            require_once __DIR__ . '/../controllers/userController.php';
            
            $controller = new userController($pdo);
            
            // $respuesta = $controller->autenticarUsuario($_POST);

            echo "Ingresaste";
        } else {
            header('HTTP/1.0 405 Method Not Allowed');
            echo "Método no permitido.";
        }
        break;

    default:
        // Si no encuentra la ruta, error 404
        header("HTTP/1.0 404 Not Found");
        echo "404 - Página no encontrada";
        break;
}