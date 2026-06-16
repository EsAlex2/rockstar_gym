<?php

require_once __DIR__ . '/../controllers/controllers.php';

/* * usuariosController.php
 * Controlador para gestionar el flujo de datos del módulo de usuarios.
 * Autor: Alex Madrid (Adaptación)
 * Fecha: 16/06/2026
 */

class UsuariosController extends Controllers
{
    private $usuarioModel;

    public function __construct($pdo)
    {
        parent::__construct($pdo);
        $this->usuarioModel = $this->cargarModels('usuariosModel');
    }

    /**
     * Lista todos los usuarios del sistema
     */
    public function listarUsuarios()
    {
        $data = $this->usuarioModel->obtenerUsuarios();

        if (isset($data['error'])) {
            return $this->response(false, $data['error']);
        }

        return $this->response(true, "Usuarios obtenidos exitosamente", $data);
    }

    /**
     * Procesa el registro de un nuevo usuario
     */
    public function crearNuevoUsuario(int $id_persona, string $usuario, int $id_rol)
    {   
        $password = "Cliente2026*";
        // Validación de campos requeridos
        if (empty($id_persona) || empty(trim($usuario)) || empty(trim($password)) || empty($id_rol)) {
            return $this->response(false, "Todos los campos son estrictamente obligatorios");
        }

        // Sanitización básica del username
        $user_trim = strtolower(trim($usuario));

        if (!filter_var($user_trim, FILTER_VALIDATE_EMAIL)) {
            return $this->response(false, "El formato del correo electrónico no es válido");
        }
        
        if (strlen($password) < 8) {
            return $this->response(false, "La contraseña debe tener al menos 15 caracteres");
        }
        
        $passGenerico = $password;

        // Ejecución en el modelo
        $request = $this->usuarioModel->crearUsuario(
            $id_persona,
            $user_trim,
            $passGenerico,
            $id_rol
        );

        if (isset($request['error'])) {
            return $this->response(false, $request['error']);
        }

        return $this->response(true, $request['message'] ?? "Usuario registrado exitosamente");
    }
}

// $pruebas = new UsuariosController($pdo);

// echo $pruebas->crearNuevoUsuario(1, "alexmadrid326@gmail.com", 1);