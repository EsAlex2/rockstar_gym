<?php
require_once __DIR__ . '/app/core/conn.php';
try {
    $sql = "SELECT 
                u.*, 
                p.primer_nombre, 
                p.primer_apellido, 
                p.cedula_identidad,
                r.nombre_rol
            FROM usuarios u
            INNER JOIN personas p ON u.id_persona = p.id
            INNER JOIN roles r ON u.id_rol = r.id";
            
    $stmt = $pdo->query($sql);
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Usuarios en BD:\n";
    print_r($usuarios);
    
    echo "\n--------------------\n";
    
    $identidad = 'root';
    $sql2 = "SELECT 
                u.*, 
                p.primer_nombre, 
                p.primer_apellido, 
                p.cedula_identidad,
                r.nombre_rol
            FROM usuarios u
            INNER JOIN personas p ON u.id_persona = p.id
            INNER JOIN roles r ON u.id_rol = r.id
            WHERE (
                LOWER(u.email_user) = LOWER(:id1)
                OR p.cedula_identidad = :id2
                OR (LOWER(:id3) = 'root' AND LOWER(r.nombre_rol) = 'root')
                OR (LOWER(:id4) = 'admin' AND LOWER(r.nombre_rol) IN ('root', 'administrador'))
            )
            AND (
                u.id_estatus = 1 
                OR u.id_estatus = (SELECT id FROM estatus WHERE LOWER(nombre_estatus) = 'activo' LIMIT 1)
                OR u.id_estatus IS NULL
            )
            LIMIT 1";
    $stmt2 = $pdo->prepare($sql2);
    $stmt2->execute([
        ':id1' => $identidad,
        ':id2' => $identidad,
        ':id3' => $identidad,
        ':id4' => $identidad
    ]);
    $res = $stmt2->fetch(PDO::FETCH_ASSOC);
    echo "\nResultado con 'root':\n";
    var_dump($res);
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
