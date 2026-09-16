CREATE TABLE IF NOT EXISTS rol (
    id_rol BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    nombre_rol VARCHAR(50) NOT NULL,
    descripcion VARCHAR(200) DEFAULT NULL,
    estado SMALLINT NOT NULL DEFAULT 1
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
    id_comuna BIGINT DEFAULT NULL,
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

-- La relación real que usa el sistema es barrio -> comuna: primero se
-- elige la comuna y el select de barrios se filtra por ella. Se agrega
-- aquí porque la tabla barrio se crea antes que la tabla comuna.
--
-- Se hace con ALTER y no dentro del CREATE porque, si la base ya existía
-- de una ejecución anterior, "CREATE TABLE IF NOT EXISTS" se salta la
-- tabla entera y la columna nueva nunca se crearía.
ALTER TABLE barrio ADD COLUMN IF NOT EXISTS id_comuna BIGINT DEFAULT NULL;

DO $$
BEGIN
  IF NOT EXISTS (
    SELECT 1 FROM pg_constraint WHERE conname = 'fk_barrio_comuna'
  ) THEN
    ALTER TABLE barrio
      ADD CONSTRAINT fk_barrio_comuna
      FOREIGN KEY (id_comuna) REFERENCES comuna (id_comuna);
  END IF;
END$$;

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
CREATE TABLE IF NOT EXISTS "auditoria_seguimiento_zoocriadero" (
  "id_auditoria" BIGINT GENERATED BY DEFAULT AS IDENTITY PRIMARY KEY,
  "id_seguimiento" bigint NOT NULL,
  "id_usuario" bigint NOT NULL,
  "accion" varchar(20) NOT NULL,
  "fecha_accion" date NOT NULL DEFAULT (CURRENT_DATE),
  "hora_accion" time NOT NULL DEFAULT (CURRENT_TIME),
  "fecha_hora_registro" timestamp NOT NULL DEFAULT (CURRENT_TIMESTAMP),
  "detalle" varchar(300)
);

-- Se les pone nombre y se comprueba antes de crearlas: sin nombre,
-- PostgreSQL genera uno automático y al volver a ejecutar el script
-- las agregaría de nuevo (o fallaría).
DO $$
BEGIN
  IF NOT EXISTS (
    SELECT 1 FROM pg_constraint c
    JOIN pg_attribute a ON a.attrelid = c.conrelid AND a.attnum = ANY (c.conkey)
    WHERE c.conrelid = 'auditoria_seguimiento_zoocriadero'::regclass
      AND c.contype = 'f' AND a.attname = 'id_seguimiento'
  ) THEN
    ALTER TABLE "auditoria_seguimiento_zoocriadero"
      ADD CONSTRAINT fk_auditoria_seguimiento
      FOREIGN KEY ("id_seguimiento")
      REFERENCES "seguimiento_zoocriadero" ("id_seguimiento")
      DEFERRABLE INITIALLY IMMEDIATE;
  END IF;

  IF NOT EXISTS (
    SELECT 1 FROM pg_constraint c
    JOIN pg_attribute a ON a.attrelid = c.conrelid AND a.attnum = ANY (c.conkey)
    WHERE c.conrelid = 'auditoria_seguimiento_zoocriadero'::regclass
      AND c.contype = 'f' AND a.attname = 'id_usuario'
  ) THEN
    ALTER TABLE "auditoria_seguimiento_zoocriadero"
      ADD CONSTRAINT fk_auditoria_usuario
      FOREIGN KEY ("id_usuario")
      REFERENCES "usuario" ("id_usuario")
      DEFERRABLE INITIALLY IMMEDIATE;
  END IF;
END$$;

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
-- Se elimina primero por si el script se ejecuta más de una vez.
DROP TRIGGER IF EXISTS trg_auditoria_seguimiento_zoocriadero ON "seguimiento_zoocriadero";

CREATE TRIGGER trg_auditoria_seguimiento_zoocriadero
AFTER INSERT OR UPDATE OR DELETE ON "seguimiento_zoocriadero"
FOR EACH ROW
EXECUTE FUNCTION fn_auditoria_seguimiento_zoocriadero();


-- =========================================================
-- AJUSTE DE COLUMNAS
-- ---------------------------------------------------------
-- "CREATE TABLE IF NOT EXISTS" respeta la tabla que ya existe,
-- así que si la base se creó con una versión anterior del script
-- le faltarían las columnas agregadas después. Estos ALTER las
-- ponen si no están y no hacen nada si ya existen.
-- =========================================================

-- rol.estado: permite inhabilitar un rol en vez de borrarlo
ALTER TABLE rol ADD COLUMN IF NOT EXISTS estado SMALLINT NOT NULL DEFAULT 1;

-- barrio.id_comuna: relación que usan los select encadenados
-- Comuna -> Barrio del formulario de zoocriaderos
ALTER TABLE barrio ADD COLUMN IF NOT EXISTS id_comuna BIGINT DEFAULT NULL;


-- =========================================================
-- DATOS DEL SISTEMA
-- ---------------------------------------------------------
-- Todos los INSERT de esta sección usan "WHERE NOT EXISTS",
-- así que este bloque se puede ejecutar las veces que haga
-- falta sin que se dupliquen los registros.
-- =========================================================

-- ---------------------------------------------------------
-- 1. ACCIONES  (las FILAS del formulario Registro Roles)
-- ---------------------------------------------------------
INSERT INTO accion_permiso (nombre)
SELECT v.nombre FROM (VALUES
  ('Registrar'), ('Consultar'), ('Editar'), ('Eliminar')
) AS v(nombre)
WHERE NOT EXISTS (SELECT 1 FROM accion_permiso a WHERE a.nombre = v.nombre);

-- ---------------------------------------------------------
-- 2. MÓDULOS  (las COLUMNAS del formulario Registro Roles)
-- ---------------------------------------------------------
INSERT INTO modulo (nombre, descripcion)
SELECT v.nombre, v.descripcion FROM (VALUES
  ('Reportes',           'Reportes y gráficos del sistema'),
  ('Zoocriaderos',       'Gestión de zoocriaderos y tanques'),
  ('Terreno',            'Depósitos y actividades de terreno'),
  ('Usuarios',           'Gestión de usuarios del sistema'),
  ('Roles',              'Creación de roles y asignación de permisos'),
  ('Copia de seguridad', 'Respaldo de la base de datos'),
  ('Configuraciones',    'Parámetros generales del sistema')
) AS v(nombre, descripcion)
WHERE NOT EXISTS (SELECT 1 FROM modulo m WHERE m.nombre = v.nombre);

-- ---------------------------------------------------------
-- 3. TIPOS DE DOCUMENTO
-- ---------------------------------------------------------
INSERT INTO tipo_documento (nombre)
SELECT v.nombre FROM (VALUES
  ('Cédula de ciudadanía'),
  ('Cédula de extranjería'),
  ('Tarjeta de identidad'),
  ('Pasaporte'),
  ('Permiso por Protección Temporal')
) AS v(nombre)
WHERE NOT EXISTS (SELECT 1 FROM tipo_documento t WHERE t.nombre = v.nombre);

-- ---------------------------------------------------------
-- 4. ROLES
-- ---------------------------------------------------------
INSERT INTO rol (nombre_rol, descripcion, estado)
SELECT v.nombre_rol, v.descripcion, 1 FROM (VALUES
  ('Super Administrador', 'Administra la base de datos y la configuración del sistema'),
  ('Administrador',       'Director(a) del Grupo ETV'),
  ('Coordinador',         'Coordina control biológico y ecosalud'),
  ('Auxiliar',            'Personal de campo')
) AS v(nombre_rol, descripcion)
WHERE NOT EXISTS (SELECT 1 FROM rol r WHERE LOWER(r.nombre_rol) = LOWER(v.nombre_rol));

-- ---------------------------------------------------------
-- 5. PERMISOS DE LOS ROLES QUE VIENEN POR DEFECTO
--    (los roles nuevos se crean desde Registro Roles)
-- ---------------------------------------------------------

-- Super Administrador: todo sobre todos los módulos
INSERT INTO rol_permiso (id_rol, id_modulo, id_accion_permiso)
SELECT r.id_rol, m.id_modulo, a.id_accion_permiso
FROM rol r CROSS JOIN modulo m CROSS JOIN accion_permiso a
WHERE r.nombre_rol = 'Super Administrador'
  AND NOT EXISTS (
    SELECT 1 FROM rol_permiso rp
    WHERE rp.id_rol = r.id_rol AND rp.id_modulo = m.id_modulo
      AND rp.id_accion_permiso = a.id_accion_permiso);

-- Administrador: todo menos Copia de seguridad
INSERT INTO rol_permiso (id_rol, id_modulo, id_accion_permiso)
SELECT r.id_rol, m.id_modulo, a.id_accion_permiso
FROM rol r CROSS JOIN modulo m CROSS JOIN accion_permiso a
WHERE r.nombre_rol = 'Administrador'
  AND m.nombre <> 'Copia de seguridad'
  AND NOT EXISTS (
    SELECT 1 FROM rol_permiso rp
    WHERE rp.id_rol = r.id_rol AND rp.id_modulo = m.id_modulo
      AND rp.id_accion_permiso = a.id_accion_permiso);

-- Coordinador: opera zoocriaderos y terreno, consulta reportes
INSERT INTO rol_permiso (id_rol, id_modulo, id_accion_permiso)
SELECT r.id_rol, m.id_modulo, a.id_accion_permiso
FROM rol r CROSS JOIN modulo m CROSS JOIN accion_permiso a
WHERE r.nombre_rol = 'Coordinador'
  AND (
       (m.nombre IN ('Zoocriaderos','Terreno') AND a.nombre IN ('Registrar','Consultar','Editar'))
    OR (m.nombre IN ('Reportes','Configuraciones') AND a.nombre = 'Consultar')
  )
  AND NOT EXISTS (
    SELECT 1 FROM rol_permiso rp
    WHERE rp.id_rol = r.id_rol AND rp.id_modulo = m.id_modulo
      AND rp.id_accion_permiso = a.id_accion_permiso);

-- Auxiliar: registra y consulta su trabajo de campo
INSERT INTO rol_permiso (id_rol, id_modulo, id_accion_permiso)
SELECT r.id_rol, m.id_modulo, a.id_accion_permiso
FROM rol r CROSS JOIN modulo m CROSS JOIN accion_permiso a
WHERE r.nombre_rol = 'Auxiliar'
  AND (
       (m.nombre = 'Terreno'      AND a.nombre IN ('Registrar','Consultar'))
    OR (m.nombre = 'Zoocriaderos' AND a.nombre = 'Consultar')
    OR (m.nombre = 'Reportes'     AND a.nombre = 'Consultar')
  )
  AND NOT EXISTS (
    SELECT 1 FROM rol_permiso rp
    WHERE rp.id_rol = r.id_rol AND rp.id_modulo = m.id_modulo
      AND rp.id_accion_permiso = a.id_accion_permiso);

-- ---------------------------------------------------------
-- 6. USUARIOS  (contraseña de todos: demo123)
-- ---------------------------------------------------------
INSERT INTO usuario (id_tipodocumento, id_rol, nombre, apellido, correo, contrasena, estado)
SELECT (SELECT id_tipodocumento FROM tipo_documento WHERE nombre = 'Cédula de ciudadanía'),
       (SELECT id_rol FROM rol WHERE nombre_rol = v.rol),
       v.nombre, v.apellido, v.correo,
       '$2y$10$krffGFo.lHLb7KgLEAGf8ubBH5rWyCkSR/fwkaV/okl0xizVuG04C', 1
FROM (VALUES
  ('Coordinador', 'Luisa Fernanda', 'Ríos',     'luisa.rios@cali.gov.co'),
  ('Auxiliar',    'Carlos Andrés',  'Mosquera', 'carlos.mosquera@cali.gov.co'),
  ('Auxiliar',    'Diana Marcela',  'Ortiz',    'diana.ortiz@cali.gov.co'),
  ('Auxiliar',    'Jhon Édison',    'Valencia', 'jhon.valencia@cali.gov.co'),
  ('Auxiliar',    'María José',     'Perlaza',  'maria.perlaza@cali.gov.co'),
  ('Administrador','Andrés Felipe', 'Caicedo',  'andres.caicedo@cali.gov.co')
) AS v(rol, nombre, apellido, correo)
WHERE NOT EXISTS (SELECT 1 FROM usuario u WHERE LOWER(u.correo) = LOWER(v.correo));

-- ---------------------------------------------------------
-- 7. TIPOS DE TANQUE  (select "Tipo de tanque")
-- ---------------------------------------------------------
INSERT INTO tipo_tanque (nombre, descripcion, estado)
SELECT v.nombre, v.descripcion, 1 FROM (VALUES
  ('Plástico',        'Tanque plástico estándar de 500 a 1000 litros'),
  ('Vidrio',          'Acuario o pecera de vidrio para cría controlada'),
  ('Fibra de vidrio', 'Tanque en fibra de vidrio, resistente a la intemperie'),
  ('Concreto',        'Estanque en concreto construido en sitio'),
  ('Geomembrana',     'Estanque revestido en geomembrana'),
  ('Metálico',        'Tanque metálico con recubrimiento interno'),
  ('Eternit',         'Tanque tipo eternit reutilizado como estanque')
) AS v(nombre, descripcion)
WHERE NOT EXISTS (SELECT 1 FROM tipo_tanque t WHERE t.nombre = v.nombre);

-- ---------------------------------------------------------
-- 8. TIPOS DE DEPÓSITO  (inspecciones de terreno)
-- ---------------------------------------------------------
INSERT INTO tipo_deposito (nombre, descripcion, estado)
SELECT v.nombre, v.descripcion, 1 FROM (VALUES
  ('Tanque bajo',        'Tanque de almacenamiento a nivel de piso'),
  ('Tanque elevado',     'Tanque elevado o de azotea'),
  ('Alberca',            'Alberca o lavadero'),
  ('Llanta',             'Llanta a la intemperie'),
  ('Materas',            'Materas y platos de materas'),
  ('Bebedero de animal', 'Recipiente de agua para mascotas'),
  ('Floreros',           'Floreros de cementerio o de casa'),
  ('Canaleta',           'Canaleta obstruida con agua estancada'),
  ('Inservible',         'Recipiente inservible acumulador de agua'),
  ('Pozo',               'Pozo o aljibe descubierto')
) AS v(nombre, descripcion)
WHERE NOT EXISTS (SELECT 1 FROM tipo_deposito t WHERE t.nombre = v.nombre);

-- ---------------------------------------------------------
-- 9. ACTIVIDADES DE ZOOCRIADERO  (select "Acción")
-- ---------------------------------------------------------
INSERT INTO actividad (ambito, nombre, descripcion, estado)
SELECT 'zoocriadero', v.nombre, v.descripcion, 1 FROM (VALUES
  ('Limpiar tanque',        'Limpieza general del tanque y retiro de residuos'),
  ('Contar peces',          'Conteo de peces vivos, nacidos y muertos'),
  ('Equilibrar pH',         'Ajuste del pH del agua al rango 6.5 - 7.5'),
  ('Medir temperatura',     'Medición y ajuste de la temperatura del agua'),
  ('Sembrar alevinos',      'Siembra de alevinos en el tanque'),
  ('Cosechar peces',        'Extracción de peces para entrega a la comunidad'),
  ('Alimentar peces',       'Suministro de alimento concentrado'),
  ('Cambiar agua',          'Recambio parcial o total del agua'),
  ('Limpiar filtro',        'Limpieza o cambio del sistema de filtrado'),
  ('Retirar peces muertos', 'Retiro de la mortalidad encontrada en el tanque'),
  ('Revisar oxigenación',   'Revisión del sistema de aireación del tanque'),
  ('Clasificar por tallas', 'Separación de los peces según su tamaño'),
  ('Revisar reproducción',  'Verificación de hembras grávidas y crías'),
  ('Aplicar tratamiento',   'Tratamiento sanitario a los peces del tanque')
) AS v(nombre, descripcion)
WHERE NOT EXISTS (
  SELECT 1 FROM actividad a WHERE a.nombre = v.nombre AND a.ambito = 'zoocriadero');

-- ---------------------------------------------------------
-- 10. ACTIVIDADES DE TERRENO
-- ---------------------------------------------------------
INSERT INTO actividad (ambito, nombre, descripcion, estado)
SELECT 'terreno', v.nombre, v.descripcion, 1 FROM (VALUES
  ('Inspección',        'Inspección de depósitos en vivienda'),
  ('Eliminación',       'Eliminación de criaderos encontrados'),
  ('Larvicida',         'Aplicación de larvicida en depósitos útiles'),
  ('Entrega de peces',  'Entrega de guppys a la comunidad'),
  ('Educación',         'Jornada de educación sanitaria'),
  ('Lavado',            'Lavado y cepillado de tanques y albercas'),
  ('Tapado',            'Tapado de depósitos de agua'),
  ('Seguimiento',       'Visita de seguimiento a vivienda intervenida'),
  ('Censo de criaderos','Registro de depósitos por vivienda')
) AS v(nombre, descripcion)
WHERE NOT EXISTS (
  SELECT 1 FROM actividad a WHERE a.nombre = v.nombre AND a.ambito = 'terreno');

-- ---------------------------------------------------------
-- 11. ZOOCRIADEROS
-- ---------------------------------------------------------
INSERT INTO zoocriadero (nombre, direccion, comuna, barrio, id_persona_cargo, latitud, longitud, estado)
SELECT v.nombre, v.direccion, v.comuna, v.barrio,
       (SELECT id_usuario FROM usuario WHERE LOWER(correo) = LOWER(v.correo)),
       v.latitud, v.longitud, v.estado
FROM (VALUES
  ('Zoocriadero Central',    'Calle 13 # 24-05',  'Comuna 11', 'El Guabal',    'luisa.rios@cali.gov.co',      3.42158000::numeric, -76.52050000::numeric, 1),
  ('Zoocriadero Norte',      'Cra 8 # 45-12',     'Comuna 2',  'Granada',   'carlos.mosquera@cali.gov.co', 3.46210000::numeric, -76.53120000::numeric, 1),
  ('Zoocriadero Oriente',    'Calle 70 # 28D-19', 'Comuna 15', 'El Retiro', 'diana.ortiz@cali.gov.co',     3.43980000::numeric, -76.49010000::numeric, 1),
  ('Zoocriadero Ladera',     'Cra 26 # 9-40',     'Comuna 18', 'Meléndez',  'jhon.valencia@cali.gov.co',   3.38720000::numeric, -76.54990000::numeric, 0),
  ('Zoocriadero Aguablanca', 'Cra 31 # 22-71',    'Comuna 15', 'Mojica',    'maria.perlaza@cali.gov.co',   3.41050000::numeric, -76.47330000::numeric, 1)
) AS v(nombre, direccion, comuna, barrio, correo, latitud, longitud, estado)
WHERE NOT EXISTS (SELECT 1 FROM zoocriadero z WHERE z.nombre = v.nombre);

-- ---------------------------------------------------------
-- 12. TANQUES
-- ---------------------------------------------------------
INSERT INTO tanque (id_zoocriadero, id_tipo_tanque, numero_tanque, estado)
SELECT (SELECT id_zoocriadero FROM zoocriadero WHERE nombre = v.zoo),
       (SELECT id_tipo_tanque FROM tipo_tanque WHERE nombre = v.tipo),
       v.numero, 1
FROM (VALUES
  ('Zoocriadero Central',    'Plástico',        1),
  ('Zoocriadero Central',    'Vidrio',          2),
  ('Zoocriadero Central',    'Fibra de vidrio', 3),
  ('Zoocriadero Central',    'Concreto',        4),
  ('Zoocriadero Norte',      'Plástico',        1),
  ('Zoocriadero Norte',      'Geomembrana',     2),
  ('Zoocriadero Norte',      'Vidrio',          3),
  ('Zoocriadero Oriente',    'Plástico',        1),
  ('Zoocriadero Oriente',    'Metálico',        2),
  ('Zoocriadero Ladera',     'Plástico',        1),
  ('Zoocriadero Ladera',     'Concreto',        2),
  ('Zoocriadero Ladera',     'Eternit',         3),
  ('Zoocriadero Aguablanca', 'Plástico',        1),
  ('Zoocriadero Aguablanca', 'Fibra de vidrio', 2),
  ('Zoocriadero Aguablanca', 'Geomembrana',     3)
) AS v(zoo, tipo, numero)
WHERE NOT EXISTS (
  SELECT 1 FROM tanque t
  WHERE t.id_zoocriadero = (SELECT id_zoocriadero FROM zoocriadero WHERE nombre = v.zoo)
    AND t.numero_tanque = v.numero);

-- ---------------------------------------------------------
-- 13. COMUNAS Y BARRIOS DE SANTIAGO DE CALI
--     Alimentan los select "Comuna" y "Barrio" del formulario
--     de zoocriaderos: al elegir la comuna, el select de barrio
--     se filtra por barrio.id_comuna.
--     Es un listado representativo; para agregar más barrios
--     basta con sumar filas a este mismo INSERT.
-- ---------------------------------------------------------
INSERT INTO comuna (nombre)
SELECT v.nombre FROM (VALUES
  ('Comuna 1'),
  ('Comuna 2'),
  ('Comuna 3'),
  ('Comuna 4'),
  ('Comuna 5'),
  ('Comuna 6'),
  ('Comuna 7'),
  ('Comuna 8'),
  ('Comuna 9'),
  ('Comuna 10'),
  ('Comuna 11'),
  ('Comuna 12'),
  ('Comuna 13'),
  ('Comuna 14'),
  ('Comuna 15'),
  ('Comuna 16'),
  ('Comuna 17'),
  ('Comuna 18'),
  ('Comuna 19'),
  ('Comuna 20'),
  ('Comuna 21'),
  ('Comuna 22')
) AS v(nombre)
WHERE NOT EXISTS (SELECT 1 FROM comuna c WHERE c.nombre = v.nombre);

INSERT INTO barrio (id_comuna, nombre)
SELECT (SELECT id_comuna FROM comuna WHERE nombre = v.comuna), v.nombre
FROM (VALUES
  ('Comuna 1', 'Terrón Colorado'),
  ('Comuna 1', 'Vista Hermosa'),
  ('Comuna 1', 'Aguacatal'),
  ('Comuna 1', 'Patio Bonito'),
  ('Comuna 1', 'Bajo Aguacatal'),
  ('Comuna 1', 'Sector Altos de Aguacatal'),
  ('Comuna 2', 'Granada'),
  ('Comuna 2', 'Versalles'),
  ('Comuna 2', 'Centenario'),
  ('Comuna 2', 'Juanambú'),
  ('Comuna 2', 'Santa Mónica'),
  ('Comuna 2', 'Normandía'),
  ('Comuna 2', 'La Flora'),
  ('Comuna 2', 'Vipasa'),
  ('Comuna 2', 'Prados del Norte'),
  ('Comuna 2', 'Chipichape'),
  ('Comuna 2', 'Menga'),
  ('Comuna 2', 'Altos de Menga'),
  ('Comuna 2', 'Santa Rita'),
  ('Comuna 2', 'San Vicente'),
  ('Comuna 2', 'Arboledas'),
  ('Comuna 2', 'El Bosque'),
  ('Comuna 2', 'La Campiña'),
  ('Comuna 3', 'San Antonio'),
  ('Comuna 3', 'El Calvario'),
  ('Comuna 3', 'San Pedro'),
  ('Comuna 3', 'San Juan Bosco'),
  ('Comuna 3', 'La Merced'),
  ('Comuna 3', 'El Nacional'),
  ('Comuna 3', 'San Cayetano'),
  ('Comuna 3', 'Santa Rosa'),
  ('Comuna 3', 'Los Libertadores'),
  ('Comuna 3', 'El Hoyo'),
  ('Comuna 3', 'San Pascual'),
  ('Comuna 3', 'El Piloto'),
  ('Comuna 3', 'San Nicolás'),
  ('Comuna 3', 'Navarro'),
  ('Comuna 4', 'Jorge Isaacs'),
  ('Comuna 4', 'Santander'),
  ('Comuna 4', 'El Porvenir'),
  ('Comuna 4', 'La Isla'),
  ('Comuna 4', 'Berlín'),
  ('Comuna 4', 'Bolivariano'),
  ('Comuna 4', 'La Sultana'),
  ('Comuna 4', 'Manzanares'),
  ('Comuna 4', 'Evaristo García'),
  ('Comuna 4', 'Las Delicias'),
  ('Comuna 4', 'Flora Industrial'),
  ('Comuna 4', 'Olaya Herrera'),
  ('Comuna 4', 'Bueno Madrid'),
  ('Comuna 4', 'Ignacio Rengifo'),
  ('Comuna 4', 'Guillermo Valencia'),
  ('Comuna 4', 'Fátima'),
  ('Comuna 4', 'Salomia'),
  ('Comuna 4', 'Calima'),
  ('Comuna 4', 'La Esmeralda'),
  ('Comuna 5', 'Los Andes'),
  ('Comuna 5', 'Chiminangos Primera Etapa'),
  ('Comuna 5', 'Chiminangos Segunda Etapa'),
  ('Comuna 5', 'Villa del Prado'),
  ('Comuna 5', 'Torres de Comfandi'),
  ('Comuna 5', 'Metropolitano del Norte'),
  ('Comuna 5', 'Paseo de los Almendros'),
  ('Comuna 5', 'Los Guayacanes'),
  ('Comuna 5', 'Villa del Sol'),
  ('Comuna 5', 'El Sena'),
  ('Comuna 5', 'Ciudadela Comfandi'),
  ('Comuna 5', 'La Rivera I'),
  ('Comuna 6', 'Petecuy I'),
  ('Comuna 6', 'Petecuy II'),
  ('Comuna 6', 'Petecuy III'),
  ('Comuna 6', 'San Luis'),
  ('Comuna 6', 'Jorge Eliécer Gaitán'),
  ('Comuna 6', 'Paso del Comercio'),
  ('Comuna 6', 'Los Alcázares'),
  ('Comuna 6', 'Los Guaduales'),
  ('Comuna 6', 'Ciudad Los Álamos'),
  ('Comuna 6', 'Ciudadela Floralia'),
  ('Comuna 6', 'Calimio Norte'),
  ('Comuna 6', 'La Rivera II'),
  ('Comuna 6', 'Puente del Comercio'),
  ('Comuna 6', 'Sindical'),
  ('Comuna 7', 'Alfonso López I'),
  ('Comuna 7', 'Alfonso López II'),
  ('Comuna 7', 'Alfonso López III'),
  ('Comuna 7', 'Puerto Nuevo'),
  ('Comuna 7', 'Puerto Mallarino'),
  ('Comuna 7', 'Base Aérea'),
  ('Comuna 7', 'Fepicol'),
  ('Comuna 7', 'Los Pinos'),
  ('Comuna 7', 'Siete de Agosto'),
  ('Comuna 7', 'Parque de la Caña'),
  ('Comuna 7', 'San Marino'),
  ('Comuna 8', 'Primitivo Crespo'),
  ('Comuna 8', 'Simón Bolívar'),
  ('Comuna 8', 'Saavedra Galindo'),
  ('Comuna 8', 'La Floresta'),
  ('Comuna 8', 'Municipal'),
  ('Comuna 8', 'El Trébol'),
  ('Comuna 8', 'Benjamín Herrera'),
  ('Comuna 8', 'Santa Mónica Popular'),
  ('Comuna 8', 'La Base'),
  ('Comuna 8', 'Las Américas'),
  ('Comuna 8', 'Industrial'),
  ('Comuna 8', 'Chapinero'),
  ('Comuna 8', 'Villa Colombia'),
  ('Comuna 8', 'El Troncal'),
  ('Comuna 8', 'Atanasio Girardot'),
  ('Comuna 8', 'Santa Fe'),
  ('Comuna 9', 'Alameda'),
  ('Comuna 9', 'Bretaña'),
  ('Comuna 9', 'Junín'),
  ('Comuna 9', 'Belalcázar'),
  ('Comuna 9', 'Guayaquil'),
  ('Comuna 9', 'Manuel María Buenaventura'),
  ('Comuna 9', 'Aranjuez'),
  ('Comuna 9', 'Barrio Obrero'),
  ('Comuna 9', 'Sucre'),
  ('Comuna 10', 'El Dorado'),
  ('Comuna 10', 'Panamericano'),
  ('Comuna 10', 'Santa Isabel'),
  ('Comuna 10', 'Cristóbal Colón'),
  ('Comuna 10', 'Departamental'),
  ('Comuna 10', 'Las Acacias'),
  ('Comuna 10', 'La Selva'),
  ('Comuna 10', 'Olímpico'),
  ('Comuna 10', 'San Cristóbal'),
  ('Comuna 10', 'Colseguros'),
  ('Comuna 10', 'Jorge Zawadsky'),
  ('Comuna 10', 'Pasoancho'),
  ('Comuna 11', 'El Guabal'),
  ('Comuna 11', 'La Libertad'),
  ('Comuna 11', 'Villanueva'),
  ('Comuna 11', 'José Holguín Garcés'),
  ('Comuna 11', 'Boyacá'),
  ('Comuna 11', 'San Benito'),
  ('Comuna 11', 'Fenalco Kennedy'),
  ('Comuna 11', 'La Ferroviaria'),
  ('Comuna 11', 'San Pedro Claver'),
  ('Comuna 11', 'Maracaibo'),
  ('Comuna 11', 'Veinte de Julio'),
  ('Comuna 11', 'La Esperanza'),
  ('Comuna 11', 'Las Granjas'),
  ('Comuna 11', 'Bellavista'),
  ('Comuna 11', 'Primavera'),
  ('Comuna 12', 'Asturias'),
  ('Comuna 12', 'Eduardo Santos'),
  ('Comuna 12', 'Doce de Octubre'),
  ('Comuna 12', 'Nueva Floresta'),
  ('Comuna 12', 'Julio Rincón'),
  ('Comuna 12', 'Villa del Sur'),
  ('Comuna 12', 'El Paraíso'),
  ('Comuna 12', 'Bello Horizonte'),
  ('Comuna 12', 'Alfonso Barberena'),
  ('Comuna 12', 'San Judas Tadeo I'),
  ('Comuna 12', 'San Judas Tadeo II'),
  ('Comuna 12', 'El Rodeo'),
  ('Comuna 13', 'El Vergel'),
  ('Comuna 13', 'El Poblado I'),
  ('Comuna 13', 'El Poblado II'),
  ('Comuna 13', 'Ulpiano Lloreda'),
  ('Comuna 13', 'Villablanca'),
  ('Comuna 13', 'Los Robles'),
  ('Comuna 13', 'Calipso'),
  ('Comuna 13', 'Lleras Restrepo'),
  ('Comuna 13', 'Ricardo Balcázar'),
  ('Comuna 13', 'Rodrigo Lara Bonilla'),
  ('Comuna 13', 'Charco Azul'),
  ('Comuna 13', 'Omar Torrijos'),
  ('Comuna 13', 'Yira Castro'),
  ('Comuna 14', 'Alfonso Bonilla Aragón'),
  ('Comuna 14', 'Alirio Mora Beltrán'),
  ('Comuna 14', 'Puertas del Sol'),
  ('Comuna 14', 'Las Orquídeas'),
  ('Comuna 14', 'José Manuel Marroquín I'),
  ('Comuna 14', 'José Manuel Marroquín II'),
  ('Comuna 14', 'Promociones Populares'),
  ('Comuna 14', 'Manuela Beltrán'),
  ('Comuna 14', 'Los Naranjos'),
  ('Comuna 15', 'El Retiro'),
  ('Comuna 15', 'Comuneros I'),
  ('Comuna 15', 'Comuneros II'),
  ('Comuna 15', 'Ciudad Córdoba'),
  ('Comuna 15', 'Mojica'),
  ('Comuna 15', 'Laureano Gómez'),
  ('Comuna 15', 'El Vallado'),
  ('Comuna 15', 'El Morichal'),
  ('Comuna 15', 'Brisas del Bosque'),
  ('Comuna 16', 'Mariano Ramos'),
  ('Comuna 16', 'República de Israel'),
  ('Comuna 16', 'Unión de Vivienda Popular'),
  ('Comuna 16', 'Antonio Nariño'),
  ('Comuna 16', 'Brisas del Limonar'),
  ('Comuna 16', 'La Alborada'),
  ('Comuna 17', 'Ciudad Capri'),
  ('Comuna 17', 'El Limonar'),
  ('Comuna 17', 'El Gran Limonar'),
  ('Comuna 17', 'Ciudad 2000'),
  ('Comuna 17', 'El Caney'),
  ('Comuna 17', 'Bosques del Limonar'),
  ('Comuna 17', 'Las Quintas de Don Simón'),
  ('Comuna 17', 'Primero de Mayo'),
  ('Comuna 17', 'La Hacienda'),
  ('Comuna 17', 'Ciudad Universitaria'),
  ('Comuna 17', 'Mayapán'),
  ('Comuna 17', 'Las Vegas'),
  ('Comuna 17', 'Lili'),
  ('Comuna 18', 'Meléndez'),
  ('Comuna 18', 'Alto Jordán'),
  ('Comuna 18', 'Los Chorros'),
  ('Comuna 18', 'Buenos Aires'),
  ('Comuna 18', 'Caldas'),
  ('Comuna 18', 'Prados del Sur'),
  ('Comuna 18', 'Horizontes'),
  ('Comuna 18', 'Nápoles'),
  ('Comuna 18', 'Polvorines'),
  ('Comuna 18', 'Francisco Eladio Ramírez'),
  ('Comuna 18', 'Lourdes'),
  ('Comuna 18', 'Colinas del Sur'),
  ('Comuna 18', 'Mario Correa Rengifo'),
  ('Comuna 18', 'Alto Nápoles'),
  ('Comuna 19', 'Tequendama'),
  ('Comuna 19', 'El Refugio'),
  ('Comuna 19', 'San Fernando Viejo'),
  ('Comuna 19', 'San Fernando Nuevo'),
  ('Comuna 19', 'Champagnat'),
  ('Comuna 19', 'Miraflores'),
  ('Comuna 19', 'Cuarto de Legua'),
  ('Comuna 19', 'El Cedro'),
  ('Comuna 19', 'Camino Real'),
  ('Comuna 19', 'Los Cámbulos'),
  ('Comuna 19', 'El Lido'),
  ('Comuna 19', 'Pampalinda'),
  ('Comuna 19', 'Bosque Municipal'),
  ('Comuna 19', 'El Ingenio'),
  ('Comuna 19', 'Nueva Tequendama'),
  ('Comuna 19', 'El Templete'),
  ('Comuna 20', 'Siloé'),
  ('Comuna 20', 'Belén'),
  ('Comuna 20', 'Lleras Camargo'),
  ('Comuna 20', 'Tierra Blanca'),
  ('Comuna 20', 'Brisas de Mayo'),
  ('Comuna 20', 'Cementerio Carabineros'),
  ('Comuna 20', 'El Cortijo'),
  ('Comuna 20', 'La Sultana Ladera'),
  ('Comuna 20', 'Pueblo Joven'),
  ('Comuna 21', 'Desepaz'),
  ('Comuna 21', 'Potrero Grande'),
  ('Comuna 21', 'Calimio Desepaz'),
  ('Comuna 21', 'Ciudadela del Río'),
  ('Comuna 21', 'Compartir'),
  ('Comuna 21', 'Las Dalias'),
  ('Comuna 21', 'Los Líderes'),
  ('Comuna 21', 'Pizamos I'),
  ('Comuna 21', 'Pizamos II'),
  ('Comuna 21', 'Pizamos III'),
  ('Comuna 21', 'Valle Grande'),
  ('Comuna 21', 'Villa del Lago'),
  ('Comuna 21', 'Remansos de Comfandi'),
  ('Comuna 22', 'Ciudad Jardín'),
  ('Comuna 22', 'Pance'),
  ('Comuna 22', 'Ciudad Campestre'),
  ('Comuna 22', 'Urbanización Río Lili'),
  ('Comuna 22', 'Jockey Club'),
  ('Comuna 22', 'Cañasgordas'),
  ('Comuna 22', 'Parcelaciones de Pance'),
  ('Comuna 22', 'Alférez Real')
) AS v(comuna, nombre)
WHERE NOT EXISTS (
  SELECT 1 FROM barrio b
  WHERE b.nombre = v.nombre
    AND b.id_comuna = (SELECT id_comuna FROM comuna WHERE nombre = v.comuna));
