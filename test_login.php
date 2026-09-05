<?php
require_once __DIR__ . '/app/core/conn.php';
require_once __DIR__ . '/app/models/loginModel.php';

try {
    $model = new LoginModel($pdo);
    echo "1. Buscando por 'root':\n";
    $usuario = $model->buscarPorIdentidad('root');
    var_dump($usuario);

    echo "\n2. Verificando password:\n";
    if ($usuario) {
        $hash = $usuario['password_hash'];
        echo "Hash en BD: " . $hash . "\n";
        $verificadoRoot = password_verify('123456789', $hash);
        echo "Verificado con '123456789' (password seeded de Root): " . ($verificadoRoot ? 'TRUE' : 'FALSE') . "\n";
        $verificadoDefault = password_verify('Cliente2026*', $hash);
        echo "Verificado con 'Cliente2026*' (password por defecto UI): " . ($verificadoDefault ? 'TRUE' : 'FALSE') . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
