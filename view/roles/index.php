<?php   

    require_once __DIR__ . '/../../app/models/rolesModel.php';
    
    // NOTA: Asegúrate de que la variable $pdo ya esté definida/importada aquí
    $rolesModel = new rolesModel($pdo);

    $mensajeRegistro = '';
    $tipoMensaje = ''; // Puede ser 'success' o 'error'

    // 1. Procesamiento del Formulario de Registro (POST)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registrar_rol'])) {
        // Limpiamos los espacios en blanco de los campos recibidos
        $nombre_rol = trim($_POST['nombre_rol'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');

        // Ejecutamos el método de tu rolesModel
        $resultado = $rolesModel->crearRol($nombre_rol, $descripcion);

        // Identificamos si fue exitoso o un error según la respuesta de tu modelo
        if ($resultado === 'Rol creado exitosamente') {
            $mensajeRegistro = $resultado;
            $tipoMensaje = 'success';
        } else {
            $mensajeRegistro = $resultado;
            $tipoMensaje = 'error';
        }
    }

    // 2. Obtenemos la lista de roles (después de registrar si fuera el caso)
    $resultadoRoles = $rolesModel->obtenerRoles();
    $listaRoles = json_decode($resultadoRoles, true);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Roles</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            background-color: #f4f7f6;
            color: #333;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
        }
        .card {
            background-color: #fff;
            padding: 20px;
            border-radius: 6px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        h2, h3 {
            margin-top: 0;
            color: #2c3e50;
        }
        /* Estilos del Formulario */
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .form-group textarea {
            resize: vertical;
            height: 80px;
        }
        .btn {
            background-color: #28a745;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .btn:hover {
            background-color: #218838;
        }
        /* Estilos de la Tabla */
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            border-radius: 6px;
            overflow: hidden;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #007bff;
            color: white;
        }
        tr:hover {
            background-color: #f9f9f9;
        }
        /* Alertas de feedback */
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            font-weight: bold;
        }
        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            color: #721c24;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>

<div class="container">

    <h2>Sistema de Administración de Roles</h2>

    <?php if (!empty($mensajeRegistro)): ?>
        <div class="alert alert-<?php echo $tipoMensaje; ?>">
            <?php echo htmlspecialchars($mensajeRegistro); ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <h3>Registrar Nuevo Rol</h3>
        <form action="" method="POST">
            <div class="form-group">
                <label Lothar for="nombre_rol">Nombre del Rol:</label>
                <input type="text" id="nombre_rol" name="nombre_rol" placeholder="Ej: Administrador, Operador" required>
            </div>
            
            <div class="form-group">
                <label for="descripcion">Descripción:</label>
                <textarea id="descripcion" name="descripcion" placeholder="Escribe una breve descripción del rol..." required></textarea>
            </div>

            <button type="submit" name="registrar_rol" class="btn">Guardar Rol</button>
        </form>
    </div>

    <div class="card">
        <h3>Roles Registrados</h3>
        
        <?php if (is_array($listaRoles)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Nombre del Rol</th>
                        <th>Descripción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($listaRoles) > 0): ?>
                        <?php foreach ($listaRoles as $rol): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($rol['nombre_rol']); ?></strong></td>
                                <td><?php echo htmlspecialchars($rol['descripcion']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="2" style="text-align: center;">No hay roles registrados en este momento.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert alert-error">
                <strong>Error al cargar la tabla:</strong> <?php echo htmlspecialchars($resultadoRoles); ?>
            </div>
        <?php endif; ?>
    </div>

</div>
        
</body>
</html>