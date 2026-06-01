-- TABLAS PARA LA BASE DE DATOS DE ROCKSTAR GYM --

-- ESTATUS DEL SISTEMA
	CREATE TABLE administracion.estatus (
		id SERIAL PRIMARY KEY,
		nombre_estatus VARCHAR(50) NOT NULL,
		descripcion TEXT,
		creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
	);

-- ROLES
	CREATE TABLE administracion.roles (
		id SERIAL PRIMARY KEY,
		nombre_rol VARCHAR(50) UNIQUE NOT NULL,
		descripcion TEXT,
		creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
	);

-- PERMISOS
	CREATE TABLE administracion.permisos (
		id SERIAL PRIMARY KEY,
		nombre_permiso VARCHAR(40) NOT NULL,
		descripcion TEXT,
		creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
	);


-- GENERO DE LAS PERSONAS
	CREATE TABLE administracion.generos (
		id SERIAL PRIMARY KEY,
		descripcion TEXT,
		creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
	);

-- PERFILES (RELACION DE ROLES CON LOS PERMISOS ASIGNADOS AL ROL)
	CREATE TABLE administracion.roles_permisos (
    id_rol INTEGER REFERENCES administracion.roles(id) ON DELETE CASCADE,
    id_permiso INTEGER REFERENCES administracion.permisos(id) ON DELETE CASCADE,
    PRIMARY KEY (id_rol, id_permiso),
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- SEDES FISICAS DEL GYM
	CREATE TABLE administracion.sedes (
		id SERIAL PRIMARY KEY,
		estado VARCHAR(100) NOT NULL,
		municipio VARCHAR(100) NOT NULL,
		sede VARCHAR(50) NOT NULL,
		creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    	actualizado_en TIMESTAMP
	);

-- PERSONAS
	CREATE TABLE administracion.personas (
    id SERIAL PRIMARY KEY,
    id_genero INTEGER REFERENCES administracion.generos(id) ON DELETE RESTRICT,
    id_estatus INTEGER REFERENCES administracion.estatus(id) ON DELETE RESTRICT,
    cedula_identidad VARCHAR(30) UNIQUE NOT NULL,
    primer_nombre VARCHAR(50) NOT NULL,
    segundo_nombre VARCHAR(50), 
    primer_apellido VARCHAR(50) NOT NULL,
    segundo_apellido VARCHAR(50), 
    fecha_nacimiento DATE NOT NULL,
    email VARCHAR(80) UNIQUE NOT NULL, 
    telefono VARCHAR(30) UNIQUE NOT NULL,
    direccion_habitacion TEXT,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP
);

-- USUARIOS
	CREATE TABLE administracion.usuarios (
    id SERIAL PRIMARY KEY,
    id_estatus INTEGER REFERENCES administracion.estatus(id) ON DELETE RESTRICT,
    id_persona INTEGER REFERENCES administracion.personas(id) ON DELETE CASCADE,
    id_rol INTEGER REFERENCES administracion.roles(id) ON DELETE RESTRICT,
    username VARCHAR(40) UNIQUE NOT NULL,
    email_user VARCHAR(80) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP
);

