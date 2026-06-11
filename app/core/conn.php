<?php 

    require_once __DIR__ . '/../../config/init.php';
    
    $dsn = 'pgsql:host=' . BD_HOST . ';port=' . BD_PORT . ';dbname=' . BD_NAME . ';options=\'--client_encoding=UTF8\'';

    try {
        $pdo = new PDO($dsn, BD_USER, BD_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        //echo "Conexion Existosa a la Base de Datos";
        
    } catch (PDOException $e) {
        
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(["error" => "Error de conexión a la base de datos" . $e->getMessage()]);
        exit;
    }