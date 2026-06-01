<?php

require_once __DIR__ . '/seeder.php';
require_once __DIR__ . '/../core/conn.php';

class SeederPermisos extends Seeder
{
    public function __construct($pdo)
    {
        parent::__construct($pdo);
    }

    public function runSeeder()
    {
        $permisosList = [
            // --- MÓDULO. SEGURIDAD Y CONFIGURACIÓN (Root / Admin) ---
            [
                'nombre_permiso' => 'seguridad.configurar',
                'descripcion' => 'Acceso exclusivo a configuraciones críticas del sistema, variables de entorno y logs (Solo Root).'
            ],
            [
                'nombre_permiso' => 'roles.gestionar',
                'descripcion' => 'Permite crear, editar, eliminar roles y asignarles o quitarles permisos.'
            ],

            // --- MÓDULO. USUARIOS Y PERSONAL (Root / Admin) ---
            [
                'nombre_permiso' => 'usuarios.crear',
                'descripcion' => 'Permite registrar nuevos usuarios (personal del gimnasio) en el sistema.'
            ],
            [
                'nombre_permiso' => 'usuarios.leer',
                'descripcion' => 'Permite ver la lista de usuarios y los detalles de sus cuentas.'
            ],
            [
                'nombre_permiso' => 'usuarios.actualizar',
                'descripcion' => 'Permite modificar datos de usuarios y cambiar sus estatus.'
            ],

            // --- MÓDULO. CLIENTES (Admin / Recepción / Entrenador) ---
            [
                'nombre_permiso' => 'clientes.crear',
                'descripcion' => 'Permite inscribir nuevos clientes y registrar sus datos personales.'
            ],
            [
                'nombre_permiso' => 'clientes.leer',
                'descripcion' => 'Permite ver el listado de clientes, perfiles y estados de sus membresías.'
            ],
            [
                'nombre_permiso' => 'clientes.actualizar',
                'descripcion' => 'Permite editar la información de los clientes o congelar sus pases.'
            ],

            // --- MÓDULO. PLANES Y MEMBRESÍAS (Admin) ---
            [
                'nombre_permiso' => 'planes.gestionar',
                'descripcion' => 'Permite crear, modificar precios, duraciones o deshabilitar planes de entrenamiento.'
            ],

            // --- MÓDULO. FINANZAS Y PAGOS (Admin / Recepción) ---
            [
                'nombre_permiso' => 'pagos.registrar',
                'descripcion' => 'Permite cargar al sistema un nuevo pago emitido por un cliente.'
            ],
            [
                'nombre_permiso' => 'pagos.verificar',
                'descripcion' => 'Permite validar las referencias bancarias y cambiar el estatus del pago a Aprobado o Rechazado.'
            ],
            [
                'nombre_permiso' => 'pagos.historial',
                'descripcion' => 'Permite auditar el histórico de transacciones e ingresos económicos del gimnasio.'
            ],

            // --- MÓDULO. CLASES, HORARIOS Y ENTRENAMIENTOS (Admin / Entrenador / Cliente) ---
            [
                'nombre_permiso' => 'clases.gestionar',
                'descripcion' => 'Permite crear entrenamientos, asignar instructores y definir cronogramas/horarios (Admin).'
            ],
            [
                'nombre_permiso' => 'clases.pasar_asistencia',
                'descripcion' => 'Permite al entrenador registrar qué clientes asistieron a su clase el día de hoy.'
            ],
            [
                'nombre_permiso' => 'clases.ver_horarios',
                'descripcion' => 'Permite consultar la cartelera de clases disponibles, sedes y horas (Todos).'
            ],
            [
                'nombre_permiso' => 'clases.reservar',
                'descripcion' => 'Permite a los clientes agendar o apartar un cupo en una clase específica.'
            ],

            // --- MÓDULO. PERFIL PROPIO (Todos) ---
            [
                'nombre_permiso' => 'mi_perfil.ver',
                'descripcion' => 'Permite a cualquier usuario o cliente logueado visualizar sus propios datos y estatus.'
            ],
            [
                'nombre_permiso' => 'mi_perfil.actualizar',
                'descripcion' => 'Permite cambiar contraseña, foto de perfil o actualizar información básica de contacto propia.'
            ],

            // --- MÓDULO. REPORTES Y ESTADÍSTICAS (Root / Admin) ---
            [
                'nombre_permiso' => 'reportes.financieros',
                'descripcion' => 'Permite generar reportes de ganancias, cierres de caja y proyecciones monetarias.'
            ],
            [
                'nombre_permiso' => 'reportes.asistencia',
                'descripcion' => 'Permite ver estadísticas de afluencia en el gym, horas pico y efectividad de entrenadores.'
            ]
        ];

        foreach ($permisosList as $permiso) {
            $stmt = $this->pdo->prepare("INSERT INTO administracion.permisos (nombre_permiso, descripcion) VALUES (:nombre_permiso, :descripcion)");
            $stmt->execute([
                ':nombre_permiso' => $permiso['nombre_permiso'],
                ':descripcion' => $permiso['descripcion']
            ]);
        }

        echo "Permisos seeders ejecutados correctamente.";
    }
}
