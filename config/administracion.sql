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
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    act_en TIMESTAMP
);

-- PERMISOS
CREATE TABLE administracion.permisos (
    id SERIAL PRIMARY KEY,
    nombre_permiso VARCHAR(40) UNIQUE NOT NULL,
    descripcion TEXT,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- RELACION DE ROLES CON LOS PERMISOS ASIGNADOS
CREATE TABLE administracion.roles_permisos (
    id_rol INTEGER REFERENCES administracion.roles(id) ON DELETE CASCADE,
    id_permiso INTEGER REFERENCES administracion.permisos(id) ON DELETE CASCADE,
    PRIMARY KEY (id_rol, id_permiso),
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- BANCOS REGISTRADOS EN EL SISTEMA
CREATE TABLE administracion.bancos (
    id SERIAL PRIMARY KEY,
    nombre_banco VARCHAR(50) NOT NULL,
    descripcion TEXT,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP
);

-- SEDES FISICAS DEL GYM
CREATE TABLE administracion.sedes (
    id SERIAL PRIMARY KEY,
    estado VARCHAR(100) NOT NULL,
    municipio VARCHAR(100) NOT NULL,
    sede VARCHAR(100) NOT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP
);

-- HORARIOS DEL GYM
CREATE TABLE administracion.horarios (
    id SERIAL PRIMARY KEY,
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP,
    CONSTRAINT uq_bloque_horario UNIQUE (hora_inicio, hora_fin) 
);

-- GENERO DE LAS PERSONAS
CREATE TABLE administracion.generos (
    id SERIAL PRIMARY KEY,
    descripcion VARCHAR(30) NOT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
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

-- USUARIOS (Logueo al sistema)
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

-- ENTRENADORES
CREATE TABLE administracion.entrenadores (
    id SERIAL PRIMARY KEY,
    id_estatus INTEGER REFERENCES administracion.estatus(id) ON DELETE RESTRICT,
    id_persona INTEGER REFERENCES administracion.personas(id) ON DELETE CASCADE,
    especialidad VARCHAR(100),
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP
);

-- MIEMBROS
CREATE TABLE administracion.clientes (
    id SERIAL PRIMARY KEY,
    id_estatus INTEGER REFERENCES administracion.estatus(id) ON DELETE RESTRICT,
    id_persona INTEGER REFERENCES administracion.personas(id) ON DELETE CASCADE,
    fecha_inscripcion DATE DEFAULT CURRENT_DATE NOT NULL,
    codigo_acceso VARCHAR(50) UNIQUE,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP
);

-- ENTRENAMIENTOS / CLASES
CREATE TABLE administracion.entrenamiento (
    id SERIAL PRIMARY KEY,
    id_estatus INTEGER REFERENCES administracion.estatus(id) ON DELETE RESTRICT,
    id_entrenador INTEGER REFERENCES administracion.entrenadores(id) ON DELETE RESTRICT,
    id_sede INTEGER REFERENCES administracion.sedes(id) ON DELETE RESTRICT,
    nombre_entrenamiento VARCHAR(60) NOT NULL,
    descripcion TEXT,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP
);

CREATE TABLE administracion.entrenamiento_horarios (
    id_entrenamiento INTEGER REFERENCES administracion.entrenamiento(id) ON DELETE CASCADE,
    id_horario INTEGER REFERENCES administracion.horarios(id) ON DELETE RESTRICT,
    dia_semana VARCHAR(15) NOT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_entrenamiento, id_horario, dia_semana),
    CONSTRAINT chk_dias_semana CHECK (dia_semana IN (
        'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado', 'Domingo'
    ))
);

--CATÁLOGO DE PLANES / MEMBRESÍAS
CREATE TABLE administracion.planes (
    id SERIAL PRIMARY KEY,
    id_estatus INTEGER REFERENCES administracion.estatus(id) ON DELETE RESTRICT,
    nombre_plan VARCHAR(50) UNIQUE NOT NULL,
    descripcion TEXT,
    precio NUMERIC(10, 2) NOT NULL,
    duracion_dias INTEGER NOT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP,
    CONSTRAINT chk_precio_positivo CHECK (precio >= 0),
    CONSTRAINT chk_duracion_positiva CHECK (duracion_dias > 0)
);

--MEMBRESÍAS ADQUIRIDAS POR LOS CLIENTES
CREATE TABLE administracion.clientes_planes (
    id SERIAL PRIMARY KEY, 
    id_cliente INTEGER REFERENCES administracion.clientes(id) ON DELETE CASCADE,
    id_plan INTEGER REFERENCES administracion.planes(id) ON DELETE RESTRICT,
    id_estatus INTEGER REFERENCES administracion.estatus(id) ON DELETE RESTRICT,
    fecha_inicio DATE NOT NULL DEFAULT CURRENT_DATE,
    fecha_vencimiento DATE NOT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP,
    CONSTRAINT chk_fechas_coherentes CHECK (fecha_vencimiento >= fecha_inicio)
);

-- PAGOS REGISTRADOS
CREATE TABLE administracion.pagos (
    id SERIAL PRIMARY KEY,    
    id_banco INTEGER REFERENCES administracion.bancos(id) ON DELETE RESTRICT,
    id_cliente INTEGER REFERENCES administracion.clientes(id) ON DELETE RESTRICT,
    id_cliente_plan INTEGER REFERENCES administracion.clientes_planes(id) ON DELETE RESTRICT,
    id_user INTEGER REFERENCES administracion.usuarios(id) ON DELETE RESTRICT, 
    id_estatus INTEGER REFERENCES administracion.estatus(id) ON DELETE RESTRICT, 
    monto NUMERIC(10, 2) NOT NULL, 
    fecha_pago DATE NOT NULL, 
    cod_referencia VARCHAR(40) UNIQUE NOT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- TABLA PARA REGISTRAR LOS LOGS DEL SISTEMA

CREATE TABLE administracion.logs (
    id SERIAL PRIMARY KEY,
    id_usuario INTEGER REFERENCES administracion.usuarios(id) ON DELETE RESTRICT,
    
);

