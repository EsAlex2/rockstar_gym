
	# Rockstar GYM

Rockstar GYM es un sistema web de gestión para gimnasios desarrollado en PHP con una arquitectura simple tipo MVC, MySQL y una interfaz moderna con Tailwind CSS. El proyecto permite administrar los principales procesos operativos del gimnasio, incluyendo usuarios, roles, permisos, clientes, entrenadores, entrenamientos, horarios, planes, membresías y pagos.

## Características principales

- Autenticación de usuarios con control de acceso por roles.
- Gestión de usuarios, roles y permisos.
- Gestión de clientes y entrenadores.
- Administración de entrenamientos, horarios y planes.
- Registro y seguimiento de pagos y membresías.
- Interfaz web responsive con Tailwind CSS.
- Datos iniciales y seeders para pruebas y desarrollo.

## Usuarios del sistema

El sistema está pensado para trabajar con varios tipos de usuarios, entre ellos:

- Root
- Admin
- Entrenador
- Cliente

## Módulos incluidos

- Gestión de usuarios (CRUD)
- Gestión de roles (CRUD)
- Gestión de permisos (CRUD)
- Gestión de personas
- Gestión de clientes
- Gestión de entrenadores
- Gestión de entrenamientos
- Gestión de horarios
- Gestión de planes y membresías
- Gestión de pagos

## Tecnologías utilizadas

- PHP
- MySQL
- Composer
- JavaScript
- Tailwind CSS
- HTML y CSS

## Estructura del proyecto

- app/controllers: controladores de cada módulo.
- app/models: lógica de acceso a datos.
- app/seeders: datos iniciales y scripts de carga.
- config: configuración general del sistema y conexión a la base de datos.
- public: punto de entrada de la aplicación y archivos públicos.
- views: vistas de cada módulo.
- components: componentes reutilizables de la interfaz.

## Requisitos previos

Antes de instalar el proyecto, asegúrate de tener instalado:

- PHP 8 o superior
- MySQL o MariaDB
- Servidor web como Apache o Nginx
- Composer
- Un navegador moderno

## Instalación

1. Clona este repositorio en tu servidor local:

   ```bash
   git clone <url-del-repositorio>
   cd rockstar_gym
   ```

2. Crea una base de datos en MySQL. El proyecto espera por defecto una base de datos llamada `rockstar`.

3. Importa el archivo SQL ubicado en:

   ```bash
   config/administracion.sql
   ```

4. Configura las credenciales de la base de datos en el archivo:

   ```php
   config/init.php
   ```

   Ajusta los valores de `BD_HOST`, `BD_NAME`, `BD_USER`, `BD_PASS` y `BD_PORT` según tu entorno.

5. Inicia el proyecto desde la carpeta pública o sirve el proyecto desde tu servidor web.

   Ejemplo con PHP integrado:

   ```bash
   php -S localhost:8000 -t public
   ```

6. Abre en tu navegador:

   ```text
   http://localhost:8000
   ```

## Configuración básica

El archivo de configuración principal define valores como:

- `URL_BASE`: ruta base del sistema
- `SITE_NAME`: nombre del sitio
- Credenciales de conexión a MySQL

Si vas a desplegar el proyecto en otra ruta o servidor, recuerda actualizar `URL_BASE`.

## Uso del sistema

- Accede al login desde la interfaz pública.
- Inicia sesión con un usuario registrado.
- Desde el panel principal podrás navegar por los módulos disponibles según el rol asignado.
- Puedes crear, editar, eliminar o consultar registros de cada módulo según los permisos.

## Datos de ejemplo

El proyecto incluye seeders para cargar información inicial y facilitar pruebas. Estos archivos se encuentran en la carpeta `app/seeders`.

## Notas importantes

- El proyecto está orientado a un entorno académico y de práctica.
- Es recomendable mantener la base de datos y la estructura de carpetas consistente al momento de agregar nuevas funcionalidades.
- Si vas a desarrollar nuevas funciones, sigue el patrón existente de controladores y modelos para mantener la organización del código.

## Autor

Proyecto desarrollado para la gestión de un gimnasio con enfoque en administración, control de usuarios y operaciones del negocio.
