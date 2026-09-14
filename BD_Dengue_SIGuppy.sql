CREATE TABLE IF NOT EXISTS rol (
    id_rol BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    nombre_rol VARCHAR(50) NOT NULL,
    descripcion VARCHAR(200) DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS tipo_documento (
    id_tipodocumento BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL
);

CREATE TABLE IF NOT EXISTS modulo (
    id_modulo BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    nombre VARCHAR(60) NOT NULL,
    descripcion VARCHAR(200) DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS accion_permiso (
    id_accion_permiso BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    nombre VARCHAR(60) NOT NULL
);

CREATE TABLE IF NOT EXISTS tipo_tanque (
    id_tipo_tanque BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    nombre VARCHAR(60) NOT NULL,
    descripcion VARCHAR(200) DEFAULT NULL,
    estado SMALLINT NOT NULL DEFAULT 1
);

CREATE TABLE IF NOT EXISTS tipo_deposito (
    id_tipo_deposito BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    nombre VARCHAR(60) NOT NULL,
    descripcion VARCHAR(200) DEFAULT NULL,
    estado SMALLINT NOT NULL DEFAULT 1
);

CREATE TABLE IF NOT EXISTS actividad (
    id_actividad BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    ambito VARCHAR(20) NOT NULL,
    nombre VARCHAR(60) NOT NULL,
    descripcion VARCHAR(200) DEFAULT NULL,
    estado SMALLINT NOT NULL DEFAULT 1
);

CREATE TABLE IF NOT EXISTS nomenclatura (
    id_nomenclatura BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    nomenclatura VARCHAR(100) NOT NULL
);

CREATE TABLE IF NOT EXISTS barrio (
    id_barrio BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL
);

CREATE TABLE IF NOT EXISTS usuario (
    id_usuario BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_tipodocumento BIGINT NOT NULL,
    id_rol BIGINT NOT NULL,
    nombre VARCHAR(80) NOT NULL,
    apellido VARCHAR(80) NOT NULL,
    correo VARCHAR(120) UNIQUE NOT NULL,
    contrasena VARCHAR(255) NOT NULL,
    estado SMALLINT NOT NULL DEFAULT 1,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_usuario_rol FOREIGN KEY (id_rol) REFERENCES rol (id_rol),
    CONSTRAINT fk_usuario_tipodocumento FOREIGN KEY (id_tipodocumento) REFERENCES tipo_documento (id_tipodocumento)
);

CREATE TABLE IF NOT EXISTS rol_permiso (
    id_rol BIGINT NOT NULL,
    id_modulo BIGINT NOT NULL,
    id_accion_permiso BIGINT NOT NULL,
    PRIMARY KEY (id_rol, id_modulo, id_accion_permiso),
    CONSTRAINT fk_rp_rol FOREIGN KEY (id_rol) REFERENCES rol (id_rol),
    CONSTRAINT fk_rp_modulo FOREIGN KEY (id_modulo) REFERENCES modulo (id_modulo),
    CONSTRAINT fk_rp_accion FOREIGN KEY (id_accion_permiso) REFERENCES accion_permiso (id_accion_permiso)
);

CREATE TABLE IF NOT EXISTS comuna (
    id_comuna BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_barrio BIGINT DEFAULT NULL,
    nombre VARCHAR(100) NOT NULL,
    CONSTRAINT fk_comuna_barrio FOREIGN KEY (id_barrio) REFERENCES barrio (id_barrio)
);

CREATE TABLE IF NOT EXISTS ciudad (
    id_ciudad BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_comuna BIGINT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    CONSTRAINT fk_ciudad_comuna FOREIGN KEY (id_comuna) REFERENCES comuna (id_comuna)
);

CREATE TABLE IF NOT EXISTS departamento (
    id_departamento BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_ciudad BIGINT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    CONSTRAINT fk_dep_ciudad FOREIGN KEY (id_ciudad) REFERENCES ciudad (id_ciudad)
);

CREATE TABLE IF NOT EXISTS direccion (
    id_direccion BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_departamento BIGINT NOT NULL,
    id_comuna BIGINT NOT NULL,
    id_ciudad BIGINT NOT NULL,
    id_barrio BIGINT NOT NULL,
    direccion VARCHAR(200) NOT NULL,
    id_nomenclatura BIGINT NOT NULL,
    CONSTRAINT fk_dir_dep FOREIGN KEY (id_departamento) REFERENCES departamento (id_departamento),
    CONSTRAINT fk_dir_comuna FOREIGN KEY (id_comuna) REFERENCES comuna (id_comuna),
    CONSTRAINT fk_dir_ciudad FOREIGN KEY (id_ciudad) REFERENCES ciudad (id_ciudad),
    CONSTRAINT fk_dir_barrio FOREIGN KEY (id_barrio) REFERENCES barrio (id_barrio),
    CONSTRAINT fk_dir_nom FOREIGN KEY (id_nomenclatura) REFERENCES nomenclatura (id_nomenclatura)
);

CREATE TABLE IF NOT EXISTS zoocriadero (
    id_zoocriadero BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    direccion VARCHAR(200) NOT NULL,
    comuna VARCHAR(60) DEFAULT NULL,
    barrio VARCHAR(60) DEFAULT NULL,
    id_persona_cargo BIGINT DEFAULT NULL,
    latitud NUMERIC(10, 8) NOT NULL,
    longitud NUMERIC(11, 8) NOT NULL,
    estado SMALLINT NOT NULL DEFAULT 1,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_zoocriadero_usuario FOREIGN KEY (id_persona_cargo) REFERENCES usuario (id_usuario)
);

CREATE TABLE IF NOT EXISTS tanque (
    id_tanque BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_zoocriadero BIGINT NOT NULL,
    id_tipo_tanque BIGINT NOT NULL,
    numero_tanque INT NOT NULL,
    estado SMALLINT NOT NULL DEFAULT 1,
    CONSTRAINT fk_tanque_zoocriadero FOREIGN KEY (id_zoocriadero) REFERENCES zoocriadero (id_zoocriadero),
    CONSTRAINT fk_tanque_tipo FOREIGN KEY (id_tipo_tanque) REFERENCES tipo_tanque (id_tipo_tanque)
);

CREATE TABLE IF NOT EXISTS seguimiento_zoocriadero (
    id_seguimiento BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_zoocriadero BIGINT NOT NULL,
    id_tanque BIGINT NOT NULL,
    id_usuario BIGINT NOT NULL,
    fecha DATE NOT NULL,
    ph NUMERIC(4,2) DEFAULT NULL,
    temperatura NUMERIC(4,2) DEFAULT NULL,
    numero_sembrados INT DEFAULT 0,
    numero_nacidos INT DEFAULT 0,
    numero_muertos INT DEFAULT 0,
    observaciones VARCHAR(300) DEFAULT NULL,
    estado SMALLINT NOT NULL DEFAULT 1,
    CONSTRAINT fk_seg_zoo_zoocriadero FOREIGN KEY (id_zoocriadero) REFERENCES zoocriadero (id_zoocriadero),
    CONSTRAINT fk_seg_zoo_tanque FOREIGN KEY (id_tanque) REFERENCES tanque (id_tanque),
    CONSTRAINT fk_seg_zoo_usuario FOREIGN KEY (id_usuario) REFERENCES usuario (id_usuario)
);

CREATE TABLE IF NOT EXISTS actividad_zoocriadero (
    id_actividad_zoocriadero BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_seguimiento BIGINT NOT NULL,
    id_actividad BIGINT NOT NULL,
    CONSTRAINT fk_act_zoo_seg FOREIGN KEY (id_seguimiento) REFERENCES seguimiento_zoocriadero (id_seguimiento),
    CONSTRAINT fk_act_zoo_act FOREIGN KEY (id_actividad) REFERENCES actividad (id_actividad)
);

CREATE TABLE IF NOT EXISTS sitio (
    id_sitio BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_tipo_deposito BIGINT NOT NULL,
    id_direccion BIGINT NOT NULL,
    estado SMALLINT NOT NULL DEFAULT 1,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_sitio_deposito FOREIGN KEY (id_tipo_deposito) REFERENCES tipo_deposito (id_tipo_deposito),
    CONSTRAINT fk_sitio_direccion FOREIGN KEY (id_direccion) REFERENCES direccion (id_direccion)
);

CREATE TABLE IF NOT EXISTS seguimiento_terreno (
    id_seguimiento_terreno BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_sitio BIGINT NOT NULL,
    id_usuario BIGINT NOT NULL,
    fecha DATE NOT NULL,
    estado SMALLINT NOT NULL DEFAULT 1,
    CONSTRAINT fk_seg_ter_sitio FOREIGN KEY (id_sitio) REFERENCES sitio (id_sitio),
    CONSTRAINT fk_seg_ter_usuario FOREIGN KEY (id_usuario) REFERENCES usuario (id_usuario)
);

CREATE TABLE IF NOT EXISTS actividad_terreno (
    id_actividad_terreno BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_seguimiento_terreno BIGINT NOT NULL,
    id_actividad BIGINT NOT NULL,
    CONSTRAINT fk_act_ter_seg FOREIGN KEY (id_seguimiento_terreno) REFERENCES seguimiento_terreno (id_seguimiento_terreno),
    CONSTRAINT fk_act_ter_act FOREIGN KEY (id_actividad) REFERENCES actividad (id_actividad)
);

CREATE TABLE IF NOT EXISTS territorio_priorizado (
    id_territorio BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    comuna VARCHAR(60) NOT NULL,
    barrio VARCHAR(60) NOT NULL,
    sitio VARCHAR(100) DEFAULT NULL,
    direccion_sitio VARCHAR(200) DEFAULT NULL,
    nombre_lider VARCHAR(120) NOT NULL,
    direccion_lider VARCHAR(200) DEFAULT NULL,
    telefono_lider VARCHAR(30) DEFAULT NULL,
    correo_lider VARCHAR(120) DEFAULT NULL,
    clase_liderazgo VARCHAR(80) DEFAULT NULL,
    id_funcionario_ecosalud BIGINT NOT NULL,
    latitud NUMERIC(10, 8) DEFAULT NULL,
    longitud NUMERIC(11, 8) DEFAULT NULL,
    fecha_registro DATE NOT NULL DEFAULT CURRENT_DATE,
    estado SMALLINT NOT NULL DEFAULT 1,
    CONSTRAINT fk_ter_usuario FOREIGN KEY (id_funcionario_ecosalud) REFERENCES usuario (id_usuario)
);


-- =========================================================
-- AUDITORÍA DE SEGUIMIENTO_ZOOCRIADERO
-- Registra automáticamente cada INSERT / UPDATE 
-- realizado sobre la tabla "seguimiento_zoocriadero",
-- guardando usuario, fecha y hora del movimiento.
-- =========================================================

-- 1. TABLA DE AUDITORÍA
CREATE TABLE "auditoria_seguimiento_zoocriadero" (
  "id_auditoria" BIGINT GENERATED BY DEFAULT AS IDENTITY PRIMARY KEY,
  "id_seguimiento" bigint NOT NULL,
  "id_usuario" bigint NOT NULL,
  "accion" varchar(20) NOT NULL,
  "fecha_accion" date NOT NULL DEFAULT (CURRENT_DATE),
  "hora_accion" time NOT NULL DEFAULT (CURRENT_TIME),
  "fecha_hora_registro" timestamp NOT NULL DEFAULT (CURRENT_TIMESTAMP),
  "detalle" varchar(300)
);

ALTER TABLE "auditoria_seguimiento_zoocriadero"
  ADD FOREIGN KEY ("id_seguimiento")
  REFERENCES "seguimiento_zoocriadero" ("id_seguimiento")
  DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE "auditoria_seguimiento_zoocriadero"
  ADD FOREIGN KEY ("id_usuario")
  REFERENCES "usuario" ("id_usuario")
  DEFERRABLE INITIALLY IMMEDIATE;

-- 2. FUNCIÓN DEL DISPARADOR
-- El INSERT hacia la tabla de auditoría queda "quemado" (hardcodeado)
-- dentro de la función; no depende de datos dinámicos externos.
CREATE OR REPLACE FUNCTION fn_auditoria_seguimiento_zoocriadero()
RETURNS TRIGGER AS $$
BEGIN
  IF TG_OP = 'INSERT' THEN
    INSERT INTO "auditoria_seguimiento_zoocriadero"
      ("id_seguimiento", "id_usuario", "accion", "fecha_accion", "hora_accion", "fecha_hora_registro", "detalle")
    VALUES
      (NEW.id_seguimiento, NEW.id_usuario, 'INSERT', CURRENT_DATE, CURRENT_TIME, CURRENT_TIMESTAMP,
       'Se registro un nuevo seguimiento de zoocriadero');
    RETURN NEW;

  ELSIF TG_OP = 'UPDATE' THEN
    INSERT INTO "auditoria_seguimiento_zoocriadero"
      ("id_seguimiento", "id_usuario", "accion", "fecha_accion", "hora_accion", "fecha_hora_registro", "detalle")
    VALUES
      (NEW.id_seguimiento, NEW.id_usuario, 'UPDATE', CURRENT_DATE, CURRENT_TIME, CURRENT_TIMESTAMP,
       'Se actualizo un seguimiento de zoocriadero');
    RETURN NEW;

  ELSIF TG_OP = 'DELETE' THEN
    INSERT INTO "auditoria_seguimiento_zoocriadero"
      ("id_seguimiento", "id_usuario", "accion", "fecha_accion", "hora_accion", "fecha_hora_registro", "detalle")
    VALUES
      (OLD.id_seguimiento, OLD.id_usuario, 'DELETE', CURRENT_DATE, CURRENT_TIME, CURRENT_TIMESTAMP,
       'Se elimino un seguimiento de zoocriadero');
    RETURN OLD;
  END IF;

  RETURN NULL;
END;
$$ LANGUAGE plpgsql;

-- 3. DISPARADOR (TRIGGER)
CREATE TRIGGER trg_auditoria_seguimiento_zoocriadero
AFTER INSERT OR UPDATE OR DELETE ON "seguimiento_zoocriadero"
FOR EACH ROW
EXECUTE FUNCTION fn_auditoria_seguimiento_zoocriadero();


-- =========================================================
-- DATOS INICIALES DE CATÁLOGO
-- Son los registros que alimentan el formulario "Registro Roles":
--   accion_permiso -> las FILAS   (Registrar/Consultar/Editar/Eliminar)
--   modulo         -> las COLUMNAS (los módulos del menú lateral)
-- No modifican la estructura, solo insertan datos.
-- =========================================================

INSERT INTO accion_permiso (nombre) VALUES
  ('Registrar'),
  ('Consultar'),
  ('Editar'),
  ('Eliminar');

INSERT INTO modulo (nombre, descripcion) VALUES
  ('Reportes',           'Reportes y gráficos del sistema'),
  ('Zoocriaderos',       'Gestión de zoocriaderos y tanques'),
  ('Terreno',            'Depósitos y actividades de terreno'),
  ('Usuarios',           'Gestión de usuarios del sistema'),
  ('Roles',              'Creación de roles y asignación de permisos'),
  ('Copia de seguridad', 'Respaldo de la base de datos'),
  ('Configuraciones',    'Parámetros generales del sistema');


-- =========================================================
-- DATOS BASE DEL SISTEMA
-- Registros mínimos para que los módulos de Zoocriaderos y
-- Seguimiento tengan de dónde leer (antes estaban quemados
-- en el JavaScript).
-- =========================================================

INSERT INTO tipo_documento (nombre) VALUES
  ('Cédula de ciudadanía'),
  ('Cédula de extranjería'),
  ('Tarjeta de identidad');

INSERT INTO rol (nombre_rol, descripcion) VALUES
  ('Administrador', 'Director(a) del Grupo ETV'),
  ('Coordinador',   'Coordina control biológico y ecosalud'),
  ('Auxiliar',      'Personal de campo');

-- Contraseña de todos: demo123
INSERT INTO usuario (id_tipodocumento, id_rol, nombre, apellido, correo, contrasena, estado) VALUES
  (1, 2, 'Luisa Fernanda', 'Ríos',     'luisa.rios@cali.gov.co',      '$2y$10$krffGFo.lHLb7KgLEAGf8ubBH5rWyCkSR/fwkaV/okl0xizVuG04C', 1),
  (1, 3, 'Carlos Andrés',  'Mosquera', 'carlos.mosquera@cali.gov.co', '$2y$10$krffGFo.lHLb7KgLEAGf8ubBH5rWyCkSR/fwkaV/okl0xizVuG04C', 1),
  (1, 3, 'Diana Marcela',  'Ortiz',    'diana.ortiz@cali.gov.co',     '$2y$10$krffGFo.lHLb7KgLEAGf8ubBH5rWyCkSR/fwkaV/okl0xizVuG04C', 1),
  (1, 3, 'Jhon Édison',    'Valencia', 'jhon.valencia@cali.gov.co',   '$2y$10$krffGFo.lHLb7KgLEAGf8ubBH5rWyCkSR/fwkaV/okl0xizVuG04C', 1),
  (1, 3, 'María José',     'Perlaza',  'maria.perlaza@cali.gov.co',   '$2y$10$krffGFo.lHLb7KgLEAGf8ubBH5rWyCkSR/fwkaV/okl0xizVuG04C', 1);

INSERT INTO tipo_tanque (nombre, descripcion, estado) VALUES
  ('Plástico',        'Tanque plástico estándar',   1),
  ('Vidrio',          'Acuario / tanque de vidrio', 1),
  ('Fibra de vidrio', 'Tanque en fibra de vidrio',  1),
  ('Concreto',        'Tanque en concreto',         1);

INSERT INTO tipo_deposito (nombre, descripcion, estado) VALUES
  ('Tanque bajo',      'Tanque de almacenamiento a nivel de piso', 1),
  ('Tanque elevado',   'Tanque elevado o de azotea',               1),
  ('Alberca',          'Alberca o lavadero',                       1),
  ('Llanta',           'Llanta a la intemperie',                   1),
  ('Materas',          'Materas y platos de materas',              1),
  ('Inservible',       'Recipiente inservible acumulador de agua',  1);

-- Actividades del ámbito zoocriadero (alimentan el select "Acción")
INSERT INTO actividad (ambito, nombre, descripcion, estado) VALUES
  ('zoocriadero', 'Limpieza',            'Limpieza y mantenimiento del tanque',   1),
  ('zoocriadero', 'Siembra',             'Siembra de alevinos en el tanque',      1),
  ('zoocriadero', 'Cosecha',             'Extracción de peces del tanque',        1),
  ('zoocriadero', 'Alimentación',        'Suministro de alimento',                1),
  ('zoocriadero', 'Control de calidad',  'Medición de pH y temperatura',          1),
  ('zoocriadero', 'Cambio de agua',      'Recambio parcial o total del agua',     1);

-- Actividades del ámbito terreno
INSERT INTO actividad (ambito, nombre, descripcion, estado) VALUES
  ('terreno', 'Inspección',      'Inspección de depósitos en vivienda',    1),
  ('terreno', 'Eliminación',     'Eliminación de criaderos',               1),
  ('terreno', 'Larvicida',       'Aplicación de larvicida',                1),
  ('terreno', 'Entrega de peces','Entrega de guppys a la comunidad',       1),
  ('terreno', 'Educación',       'Jornada de educación sanitaria',         1);

INSERT INTO zoocriadero (nombre, direccion, comuna, barrio, id_persona_cargo, latitud, longitud, estado) VALUES
  ('Zoocriadero Central',     'Calle 13 # 24-05',  'Comuna 10', 'Guabal',   1, 3.42158000, -76.52050000, 1),
  ('Zoocriadero Norte',       'Cra 8 # 45-12',     'Comuna 2',  'Granada',  2, 3.46210000, -76.53120000, 1),
  ('Zoocriadero Oriente',     'Calle 70 # 28D-19', 'Comuna 13', 'El Retiro',3, 3.43980000, -76.49010000, 1),
  ('Zoocriadero Ladera',      'Cra 26 # 9-40',     'Comuna 18', 'Meléndez', 4, 3.38720000, -76.54990000, 0),
  ('Zoocriadero Aguablanca',  'Cra 31 # 22-71',    'Comuna 15', 'Mojica',   5, 3.41050000, -76.47330000, 1);

INSERT INTO tanque (id_zoocriadero, id_tipo_tanque, numero_tanque, estado) VALUES
  (1, 1, 1, 1), (1, 2, 2, 1), (1, 1, 3, 1), (1, 3, 4, 1),
  (2, 1, 1, 1), (2, 3, 2, 1), (2, 2, 3, 1),
  (3, 1, 1, 1), (3, 4, 2, 1),
  (4, 1, 1, 1), (4, 1, 2, 1), (4, 2, 3, 1), (4, 3, 4, 1), (4, 4, 5, 1),
  (5, 1, 1, 1), (5, 2, 2, 1), (5, 3, 3, 1);
