-- CREACIÓN DE LA BASE DE DATOS E INICIALIZACIÓN
CREATE DATABASE IF NOT EXISTS rockstar CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE rockstar;

-- TABLAS PARA LA BASE DE DATOS DE ROCKSTAR GYM --

-- ESTATUS DEL SISTEMA
CREATE TABLE estatus (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_estatus VARCHAR(50) NOT NULL,
    descripcion TEXT,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ROLES
CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_rol VARCHAR(50) UNIQUE NOT NULL,
    descripcion TEXT,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    act_en TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- PERMISOS
CREATE TABLE permisos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_permiso VARCHAR(40) UNIQUE NOT NULL,
    descripcion TEXT,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- RELACION DE ROLES CON LOS PERMISOS ASIGNADOS
CREATE TABLE roles_permisos (
    id_rol INT NOT NULL,
    id_permiso INT NOT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_rol, id_permiso),
    CONSTRAINT fk_roles_permisos_rol FOREIGN KEY (id_rol) 
        REFERENCES roles(id) ON DELETE CASCADE,
    CONSTRAINT fk_roles_permisos_permiso FOREIGN KEY (id_permiso) 
        REFERENCES permisos(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- BANCOS REGISTRADOS EN EL SISTEMA
CREATE TABLE bancos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_banco VARCHAR(50) NOT NULL,
    descripcion TEXT,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- SEDES FISICAS DEL GYM
CREATE TABLE sedes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    estado VARCHAR(100) NOT NULL,
    municipio VARCHAR(100) NOT NULL,
    sede VARCHAR(100) NOT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- HORARIOS DEL GYM
CREATE TABLE horarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT uq_bloque_horario UNIQUE (hora_inicio, hora_fin) 
) ENGINE=InnoDB;

-- GENERO DE LAS PERSONAS
CREATE TABLE generos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    descripcion VARCHAR(30) NOT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- PERSONAS
CREATE TABLE personas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_genero INT,
    id_estatus INT,
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
    actualizado_en TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_personas_genero FOREIGN KEY (id_genero) 
        REFERENCES generos(id) ON DELETE RESTRICT,
    CONSTRAINT fk_personas_estatus FOREIGN KEY (id_estatus) 
        REFERENCES estatus(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- USUARIOS (Logueo al sistema)
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_estatus INT DEFAULT 1,
    id_persona INT UNIQUE,
    id_rol INT,
    email_user VARCHAR(80) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_usuarios_estatus FOREIGN KEY (id_estatus) 
        REFERENCES estatus(id) ON DELETE RESTRICT,
    CONSTRAINT fk_usuarios_persona FOREIGN KEY (id_persona) 
        REFERENCES personas(id) ON DELETE CASCADE,
    CONSTRAINT fk_usuarios_rol FOREIGN KEY (id_rol) 
        REFERENCES roles(id) ON DELETE RESTRICT
) ENGINE=InnoDB;  

-- ENTRENADORES
CREATE TABLE entrenadores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_estatus INT,
    id_persona INT,
    especialidad VARCHAR(100),
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_entrenadores_estatus FOREIGN KEY (id_estatus) 
        REFERENCES estatus(id) ON DELETE RESTRICT,
    CONSTRAINT fk_entrenadores_persona FOREIGN KEY (id_persona) 
        REFERENCES personas(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- MIEMBROS
CREATE TABLE clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_estatus INT,
    id_persona INT,
    fecha_inscripcion DATE DEFAULT (CURRENT_DATE) NOT NULL,
    codigo_acceso VARCHAR(50) UNIQUE,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_clientes_estatus FOREIGN KEY (id_estatus) 
        REFERENCES estatus(id) ON DELETE RESTRICT,
    CONSTRAINT fk_clientes_persona FOREIGN KEY (id_persona) 
        REFERENCES personas(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ENTRENAMIENTOS / CLASES
CREATE TABLE entrenamiento (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_estatus INT,
    id_entrenador INT,
    id_sede INT,
    nombre_entrenamiento VARCHAR(60) NOT NULL,
    descripcion TEXT,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_entrenamiento_estatus FOREIGN KEY (id_estatus) 
        REFERENCES estatus(id) ON DELETE RESTRICT,
    CONSTRAINT fk_entrenamiento_entrenador FOREIGN KEY (id_entrenador) 
        REFERENCES entrenadores(id) ON DELETE RESTRICT,
    CONSTRAINT fk_entrenamiento_sede FOREIGN KEY (id_sede) 
        REFERENCES sedes(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE entrenamiento_horarios (
    id_entrenamiento INT NOT NULL,
    id_horario INT NOT NULL,
    dia_semana VARCHAR(15) NOT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_entrenamiento, id_horario, dia_semana),
    CONSTRAINT fk_eh_entrenamiento FOREIGN KEY (id_entrenamiento) 
        REFERENCES entrenamiento(id) ON DELETE CASCADE,
    CONSTRAINT fk_eh_horario FOREIGN KEY (id_horario) 
        REFERENCES horarios(id) ON DELETE RESTRICT,
    CONSTRAINT chk_dias_semana CHECK (dia_semana IN (
        'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado', 'Domingo'
    ))
) ENGINE=InnoDB;

-- CATÁLOGO DE PLANES / MEMBRESÍAS
CREATE TABLE planes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_estatus INT,
    nombre_plan VARCHAR(50) UNIQUE NOT NULL,
    descripcion TEXT,
    precio DECIMAL(10, 2) NOT NULL,
    duracion_dias INT NOT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_planes_estatus FOREIGN KEY (id_estatus) 
        REFERENCES estatus(id) ON DELETE RESTRICT,
    CONSTRAINT chk_precio_positivo CHECK (precio >= 0),
    CONSTRAINT chk_duracion_positiva CHECK (duracion_dias > 0)
) ENGINE=InnoDB;

-- MEMBRESÍAS ADQUIRIDAS POR LOS CLIENTES
CREATE TABLE clientes_planes (
    id INT AUTO_INCREMENT PRIMARY KEY, 
    id_cliente INT,
    id_plan INT,
    id_estatus INT,
    fecha_inicio DATE DEFAULT (CURRENT_DATE) NOT NULL,
    fecha_vencimiento DATE NOT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_cp_cliente FOREIGN KEY (id_cliente) 
        REFERENCES clientes(id) ON DELETE CASCADE,
    CONSTRAINT fk_cp_plan FOREIGN KEY (id_plan) 
        REFERENCES planes(id) ON DELETE RESTRICT,
    CONSTRAINT fk_cp_estatus FOREIGN KEY (id_estatus) 
        REFERENCES estatus(id) ON DELETE RESTRICT,
    CONSTRAINT chk_fechas_coherentes CHECK (fecha_vencimiento >= fecha_inicio)
) ENGINE=InnoDB;

-- PAGOS REGISTRADOS
CREATE TABLE pagos (
    id INT AUTO_INCREMENT PRIMARY KEY,    
    id_banco INT,
    id_cliente INT,
    id_cliente_plan INT,
    id_user INT,
    id_estatus INT, 
    monto DECIMAL(10, 2) NOT NULL, 
    fecha_pago DATE NOT NULL, 
    cod_referencia VARCHAR(40) UNIQUE NOT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_pagos_banco FOREIGN KEY (id_banco) 
        REFERENCES bancos(id) ON DELETE RESTRICT,
    CONSTRAINT fk_pagos_cliente FOREIGN KEY (id_cliente) 
        REFERENCES clientes(id) ON DELETE RESTRICT,
    CONSTRAINT fk_pagos_cliente_plan FOREIGN KEY (id_cliente_plan) 
        REFERENCES clientes_planes(id) ON DELETE RESTRICT,
    CONSTRAINT fk_pagos_user FOREIGN KEY (id_user) 
        REFERENCES usuarios(id) ON DELETE RESTRICT,
    CONSTRAINT fk_pagos_estatus FOREIGN KEY (id_estatus) 
        REFERENCES estatus(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- TABLA PARA REGISTRAR LOS LOGS DEL SISTEMA (CORREGIDA)
CREATE TABLE logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT,
    accion VARCHAR(255) NOT NULL,
    detalles TEXT,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_logs_usuario FOREIGN KEY (id_usuario) 
        REFERENCES usuarios(id) ON DELETE RESTRICT
) ENGINE=InnoDB;