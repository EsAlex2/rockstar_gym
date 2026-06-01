├── app/                  # Código fuente de tu aplicación (Backend)
│   ├── Controllers/      # Lógica de negocio (recibe peticiones y devuelve respuestas)
│   ├── Models/           # Interacción con la base de datos (entidades y consultas)
│   ├── Views/            # (Opcional) Plantillas HTML o de renderizado (ej. Twig/Blade)
│   └── Core/             # Clases base (Conexión a BD, enrutador, helpers)
├── config/               # Configuraciones del sistema (Base de datos, constantes)
├── public/               # Raíz del proyecto accesible desde el navegador
│   ├── css/              # Hojas de estilo
│   ├── js/               # Scripts de frontend
│   ├── img/              # Archivos multimedia
│   └── index.php         # Punto de entrada principal (Front Controller)
├── vendor/               # Dependencias de terceros instaladas por Composer
├── composer.json         # Gestor de dependencias y autoloader (PSR-4)
├── .env                  # Variables de entorno (credenciales, claves secretas)
└── .gitignore            # Archivos y carpetas que Git debe ignorar

	
	Sistema de Gestion para un Gimnasio: "Rock Star GYM"

	Usuarios del sistema: 
	* Root
	* Admin 
	* Entrenador
	* Clientes (usuario web)

	
	Modulos del Sistema: 
	
	1. Modulo de Gestion de Usuarios (CRUD)
	2. Modulo de Gestion de Roles	 (CRUD)
	3. Modulo de Gestion de permisos (CRUD)
	
	======================================

	4. Modulo para la gestion de clientes (CRUD)
	...

	Crear relaciones en las bases de datos, ademas las relaciones de clientes con los entrenadores. 