--
-- PostgreSQL database dump
--

\restrict urWfRkaES5cyP0h1IgQxDNRL1kZsDOzDfgnOgFezM96AoujtEcJGCTfiT48BekD

-- Dumped from database version 18.4
-- Dumped by pg_dump version 18.4

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- Name: fn_auditoria_general(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.fn_auditoria_general() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE
  v_operacion     VARCHAR(20);
  v_momento       VARCHAR(20);
  v_id_usuario    BIGINT;
  v_usuario_texto VARCHAR(160);
  v_accion_texto  VARCHAR(20);
BEGIN
  IF TG_OP = 'INSERT' THEN
    v_operacion    := 'INSERTAR';
    v_accion_texto := 'registro';
  ELSIF TG_OP = 'UPDATE' THEN
    v_operacion    := 'ACTUALIZAR';
    v_accion_texto := 'actualizo';
  ELSE
    v_operacion    := 'ELIMINAR';
    v_accion_texto := 'elimino';
  END IF;

  v_momento := CASE WHEN TG_WHEN = 'BEFORE' THEN 'ANTES' ELSE 'DESPUES' END;

  v_id_usuario := fn_usuario_actual();

  SELECT nombre || ' ' || apellido INTO v_usuario_texto
    FROM usuario
   WHERE id_usuario = v_id_usuario;

  v_usuario_texto := COALESCE(v_usuario_texto, 'Usuario no identificado');

  INSERT INTO auditoria_sistema (
    tabla_afectada, operacion, momento, id_usuario, usuario_responsable,
    fecha_evento, hora_evento, fecha_hora_evento,
    datos_anteriores, datos_nuevos, detalle
  ) VALUES (
    TG_TABLE_NAME, v_operacion, v_momento, v_id_usuario, v_usuario_texto,
    CURRENT_DATE, CURRENT_TIME, CURRENT_TIMESTAMP,
    CASE WHEN TG_OP IN ('UPDATE', 'DELETE') THEN to_jsonb(OLD) ELSE NULL END,
    CASE WHEN TG_OP IN ('INSERT', 'UPDATE') THEN to_jsonb(NEW) ELSE NULL END,
    'Se ' || v_accion_texto || ' un registro en "' || TG_TABLE_NAME ||
      '" (' || v_momento || ' de guardar el cambio), realizado por ' || v_usuario_texto
  );

  IF TG_OP = 'DELETE' THEN
    RETURN OLD;
  ELSE
    RETURN NEW;
  END IF;
END;
$$;


ALTER FUNCTION public.fn_auditoria_general() OWNER TO postgres;

--
-- Name: fn_auditoria_seguimiento_zoocriadero(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.fn_auditoria_seguimiento_zoocriadero() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE
  v_operacion    VARCHAR(20);
  v_momento      VARCHAR(20);
  v_id_seg       BIGINT;
  v_id_usuario   BIGINT;
  v_accion_texto VARCHAR(20);
BEGIN
  IF TG_OP = 'INSERT' THEN
    v_operacion    := 'INSERTAR';
    v_accion_texto := 'registro';
  ELSIF TG_OP = 'UPDATE' THEN
    v_operacion    := 'ACTUALIZAR';
    v_accion_texto := 'actualizo';
  ELSE
    v_operacion    := 'ELIMINAR';
    v_accion_texto := 'elimino';
  END IF;

  v_momento := CASE WHEN TG_WHEN = 'BEFORE' THEN 'ANTES' ELSE 'DESPUES' END;

  IF TG_OP = 'DELETE' THEN
    v_id_seg     := OLD.id_seguimiento;
    v_id_usuario := OLD.id_usuario;
  ELSE
    v_id_seg     := NEW.id_seguimiento;
    v_id_usuario := NEW.id_usuario;
  END IF;

  -- si la aplicacion informo el usuario de la sesion, ese tiene prioridad
  v_id_usuario := COALESCE(fn_usuario_actual(), v_id_usuario);

  INSERT INTO auditoria_seguimiento_zoocriadero
    (id_seguimiento, id_usuario, operacion, momento,
     fecha_evento, hora_evento, fecha_hora_evento, detalle)
  VALUES
    (v_id_seg, v_id_usuario, v_operacion, v_momento,
     CURRENT_DATE, CURRENT_TIME, CURRENT_TIMESTAMP,
     'El usuario ' || v_accion_texto || ' un seguimiento de zoocriadero (' ||
       v_momento || ' de guardar el cambio)');

  IF TG_OP = 'DELETE' THEN
    RETURN OLD;
  ELSE
    RETURN NEW;
  END IF;
END;
$$;


ALTER FUNCTION public.fn_auditoria_seguimiento_zoocriadero() OWNER TO postgres;

--
-- Name: fn_usuario_actual(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.fn_usuario_actual() RETURNS bigint
    LANGUAGE plpgsql
    AS $$
DECLARE
  v_id_usuario BIGINT;
BEGIN
  v_id_usuario := NULLIF(current_setting('app.usuario_actual', true), '')::BIGINT;
  RETURN v_id_usuario;
EXCEPTION WHEN OTHERS THEN
  RETURN NULL;
END;
$$;


ALTER FUNCTION public.fn_usuario_actual() OWNER TO postgres;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: accion_permiso; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.accion_permiso (
    id_accion_permiso bigint NOT NULL,
    nombre character varying(60) NOT NULL
);


ALTER TABLE public.accion_permiso OWNER TO postgres;

--
-- Name: accion_permiso_id_accion_permiso_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

ALTER TABLE public.accion_permiso ALTER COLUMN id_accion_permiso ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.accion_permiso_id_accion_permiso_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: actividad; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.actividad (
    id_actividad bigint NOT NULL,
    ambito character varying(20) NOT NULL,
    nombre character varying(60) NOT NULL,
    descripcion character varying(200) DEFAULT NULL::character varying,
    estado smallint DEFAULT 1 NOT NULL
);


ALTER TABLE public.actividad OWNER TO postgres;

--
-- Name: actividad_id_actividad_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

ALTER TABLE public.actividad ALTER COLUMN id_actividad ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.actividad_id_actividad_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: actividad_terreno; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.actividad_terreno (
    id_actividad_terreno bigint NOT NULL,
    id_seguimiento_terreno bigint NOT NULL,
    id_actividad bigint NOT NULL
);


ALTER TABLE public.actividad_terreno OWNER TO postgres;

--
-- Name: actividad_terreno_id_actividad_terreno_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

ALTER TABLE public.actividad_terreno ALTER COLUMN id_actividad_terreno ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.actividad_terreno_id_actividad_terreno_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: actividad_zoocriadero; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.actividad_zoocriadero (
    id_actividad_zoocriadero bigint NOT NULL,
    id_seguimiento bigint NOT NULL,
    id_actividad bigint NOT NULL
);


ALTER TABLE public.actividad_zoocriadero OWNER TO postgres;

--
-- Name: actividad_zoocriadero_id_actividad_zoocriadero_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

ALTER TABLE public.actividad_zoocriadero ALTER COLUMN id_actividad_zoocriadero ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.actividad_zoocriadero_id_actividad_zoocriadero_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: auditoria_seguimiento_zoocriadero; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.auditoria_seguimiento_zoocriadero (
    id_auditoria bigint NOT NULL,
    id_seguimiento bigint NOT NULL,
    id_usuario bigint,
    operacion character varying(20) NOT NULL,
    momento character varying(20) NOT NULL,
    fecha_evento date DEFAULT CURRENT_DATE NOT NULL,
    hora_evento time without time zone DEFAULT CURRENT_TIME NOT NULL,
    fecha_hora_evento timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    detalle character varying(300)
);


ALTER TABLE public.auditoria_seguimiento_zoocriadero OWNER TO postgres;

--
-- Name: TABLE auditoria_seguimiento_zoocriadero; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON TABLE public.auditoria_seguimiento_zoocriadero IS 'Historial de quien registro, actualizo o elimino cada seguimiento de zoocriadero, con fecha y hora exactas.';


--
-- Name: auditoria_seguimiento_zoocriadero_id_auditoria_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

ALTER TABLE public.auditoria_seguimiento_zoocriadero ALTER COLUMN id_auditoria ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.auditoria_seguimiento_zoocriadero_id_auditoria_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: auditoria_sistema; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.auditoria_sistema (
    id_auditoria bigint NOT NULL,
    tabla_afectada character varying(60) NOT NULL,
    operacion character varying(20) NOT NULL,
    momento character varying(20) NOT NULL,
    id_usuario bigint,
    usuario_responsable character varying(160),
    fecha_evento date DEFAULT CURRENT_DATE NOT NULL,
    hora_evento time without time zone DEFAULT CURRENT_TIME NOT NULL,
    fecha_hora_evento timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    datos_anteriores jsonb,
    datos_nuevos jsonb,
    detalle character varying(300)
);


ALTER TABLE public.auditoria_sistema OWNER TO postgres;

--
-- Name: TABLE auditoria_sistema; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON TABLE public.auditoria_sistema IS 'Historial general de inserciones, actualizaciones y eliminaciones de las tablas de catalogo y de terreno del sistema.';


--
-- Name: auditoria_sistema_id_auditoria_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

ALTER TABLE public.auditoria_sistema ALTER COLUMN id_auditoria ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.auditoria_sistema_id_auditoria_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: barrio; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.barrio (
    id_barrio bigint NOT NULL,
    id_comuna bigint,
    nombre character varying(100) NOT NULL
);


ALTER TABLE public.barrio OWNER TO postgres;

--
-- Name: barrio_id_barrio_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

ALTER TABLE public.barrio ALTER COLUMN id_barrio ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.barrio_id_barrio_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: ciudad; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.ciudad (
    id_ciudad bigint NOT NULL,
    id_comuna bigint NOT NULL,
    nombre character varying(100) NOT NULL
);


ALTER TABLE public.ciudad OWNER TO postgres;

--
-- Name: ciudad_id_ciudad_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

ALTER TABLE public.ciudad ALTER COLUMN id_ciudad ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.ciudad_id_ciudad_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: comuna; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.comuna (
    id_comuna bigint NOT NULL,
    id_barrio bigint,
    nombre character varying(100) NOT NULL
);


ALTER TABLE public.comuna OWNER TO postgres;

--
-- Name: comuna_id_comuna_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

ALTER TABLE public.comuna ALTER COLUMN id_comuna ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.comuna_id_comuna_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: copia_seguridad_historial; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.copia_seguridad_historial (
    id_historial bigint NOT NULL,
    fecha_hora timestamp without time zone DEFAULT now() NOT NULL,
    tipo_operacion character varying(20) NOT NULL,
    nombre_archivo character varying(255),
    id_usuario bigint,
    usuario_nombre character varying(150) DEFAULT 'Sistema'::character varying NOT NULL,
    estado character varying(10) NOT NULL,
    detalle text,
    CONSTRAINT copia_seguridad_historial_estado_check CHECK (((estado)::text = ANY ((ARRAY['exito'::character varying, 'error'::character varying])::text[]))),
    CONSTRAINT copia_seguridad_historial_tipo_operacion_check CHECK (((tipo_operacion)::text = ANY ((ARRAY['descarga'::character varying, 'restauracion'::character varying, 'automatica'::character varying])::text[])))
);


ALTER TABLE public.copia_seguridad_historial OWNER TO postgres;

--
-- Name: copia_seguridad_historial_id_historial_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

ALTER TABLE public.copia_seguridad_historial ALTER COLUMN id_historial ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.copia_seguridad_historial_id_historial_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: departamento; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.departamento (
    id_departamento bigint NOT NULL,
    id_ciudad bigint NOT NULL,
    nombre character varying(100) NOT NULL
);


ALTER TABLE public.departamento OWNER TO postgres;

--
-- Name: departamento_id_departamento_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

ALTER TABLE public.departamento ALTER COLUMN id_departamento ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.departamento_id_departamento_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: direccion; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.direccion (
    id_direccion bigint NOT NULL,
    id_departamento bigint NOT NULL,
    id_comuna bigint NOT NULL,
    id_ciudad bigint NOT NULL,
    id_barrio bigint NOT NULL,
    direccion character varying(200) NOT NULL,
    id_nomenclatura bigint NOT NULL
);


ALTER TABLE public.direccion OWNER TO postgres;

--
-- Name: direccion_id_direccion_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

ALTER TABLE public.direccion ALTER COLUMN id_direccion ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.direccion_id_direccion_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: modulo; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.modulo (
    id_modulo bigint NOT NULL,
    nombre character varying(60) NOT NULL,
    descripcion character varying(200) DEFAULT NULL::character varying
);


ALTER TABLE public.modulo OWNER TO postgres;

--
-- Name: modulo_id_modulo_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

ALTER TABLE public.modulo ALTER COLUMN id_modulo ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.modulo_id_modulo_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: nomenclatura; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.nomenclatura (
    id_nomenclatura bigint NOT NULL,
    nomenclatura character varying(100) NOT NULL
);


ALTER TABLE public.nomenclatura OWNER TO postgres;

--
-- Name: nomenclatura_id_nomenclatura_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

ALTER TABLE public.nomenclatura ALTER COLUMN id_nomenclatura ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.nomenclatura_id_nomenclatura_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: rol; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.rol (
    id_rol bigint NOT NULL,
    nombre_rol character varying(50) NOT NULL,
    descripcion character varying(200) DEFAULT NULL::character varying,
    estado smallint DEFAULT 1 NOT NULL
);


ALTER TABLE public.rol OWNER TO postgres;

--
-- Name: rol_id_rol_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

ALTER TABLE public.rol ALTER COLUMN id_rol ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.rol_id_rol_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: rol_permiso; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.rol_permiso (
    id_rol bigint NOT NULL,
    id_modulo bigint NOT NULL,
    id_accion_permiso bigint NOT NULL
);


ALTER TABLE public.rol_permiso OWNER TO postgres;

--
-- Name: seguimiento_terreno; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.seguimiento_terreno (
    id_seguimiento_terreno bigint NOT NULL,
    id_sitio bigint NOT NULL,
    id_usuario bigint NOT NULL,
    fecha date NOT NULL,
    estado smallint DEFAULT 1 NOT NULL
);


ALTER TABLE public.seguimiento_terreno OWNER TO postgres;

--
-- Name: seguimiento_terreno_id_seguimiento_terreno_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

ALTER TABLE public.seguimiento_terreno ALTER COLUMN id_seguimiento_terreno ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.seguimiento_terreno_id_seguimiento_terreno_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: seguimiento_zoocriadero; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.seguimiento_zoocriadero (
    id_seguimiento bigint NOT NULL,
    id_zoocriadero bigint NOT NULL,
    id_tanque bigint NOT NULL,
    id_usuario bigint NOT NULL,
    fecha date NOT NULL,
    ph numeric(4,2) DEFAULT NULL::numeric,
    temperatura numeric(4,2) DEFAULT NULL::numeric,
    numero_sembrados integer DEFAULT 0,
    numero_nacidos integer DEFAULT 0,
    numero_muertos integer DEFAULT 0,
    numero_nacidos_hembra integer DEFAULT 0 NOT NULL,
    numero_nacidos_macho integer DEFAULT 0 NOT NULL,
    numero_muertos_hembra integer DEFAULT 0 NOT NULL,
    numero_muertos_macho integer DEFAULT 0 NOT NULL,
    observaciones character varying(300) DEFAULT NULL::character varying,
    estado smallint DEFAULT 1 NOT NULL,
    estado_actividad character varying(20) DEFAULT 'En progreso'::character varying NOT NULL,
    CONSTRAINT seguimiento_zoocriadero_estado_actividad_check CHECK (((estado_actividad)::text = ANY ((ARRAY['Completada'::character varying, 'En progreso'::character varying, 'Retrasada'::character varying])::text[])))
);


ALTER TABLE public.seguimiento_zoocriadero OWNER TO postgres;

--
-- Name: seguimiento_zoocriadero_id_seguimiento_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

ALTER TABLE public.seguimiento_zoocriadero ALTER COLUMN id_seguimiento ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.seguimiento_zoocriadero_id_seguimiento_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: sitio; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.sitio (
    id_sitio bigint NOT NULL,
    id_tipo_deposito bigint NOT NULL,
    id_direccion bigint,
    direccion character varying(200) DEFAULT NULL::character varying,
    comuna character varying(60) DEFAULT NULL::character varying,
    barrio character varying(60) DEFAULT NULL::character varying,
    latitud numeric(10,8) DEFAULT NULL::numeric,
    longitud numeric(11,8) DEFAULT NULL::numeric,
    estado smallint DEFAULT 1 NOT NULL,
    creado_en timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


ALTER TABLE public.sitio OWNER TO postgres;

--
-- Name: sitio_id_sitio_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

ALTER TABLE public.sitio ALTER COLUMN id_sitio ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.sitio_id_sitio_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: tanque; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.tanque (
    id_tanque bigint NOT NULL,
    id_zoocriadero bigint NOT NULL,
    id_tipo_tanque bigint NOT NULL,
    numero_tanque integer NOT NULL,
    estado smallint DEFAULT 1 NOT NULL
);


ALTER TABLE public.tanque OWNER TO postgres;

--
-- Name: tanque_id_tanque_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

ALTER TABLE public.tanque ALTER COLUMN id_tanque ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.tanque_id_tanque_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: territorio_priorizado; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.territorio_priorizado (
    id_territorio bigint NOT NULL,
    comuna character varying(60) NOT NULL,
    barrio character varying(60) NOT NULL,
    sitio character varying(100) DEFAULT NULL::character varying,
    direccion_sitio character varying(200) DEFAULT NULL::character varying,
    nombre_lider character varying(120) NOT NULL,
    direccion_lider character varying(200) DEFAULT NULL::character varying,
    telefono_lider character varying(30) DEFAULT NULL::character varying,
    correo_lider character varying(120) DEFAULT NULL::character varying,
    clase_liderazgo character varying(80) DEFAULT NULL::character varying,
    id_funcionario_ecosalud bigint NOT NULL,
    latitud numeric(10,8) DEFAULT NULL::numeric,
    longitud numeric(11,8) DEFAULT NULL::numeric,
    fecha_registro date DEFAULT CURRENT_DATE NOT NULL,
    estado smallint DEFAULT 1 NOT NULL
);


ALTER TABLE public.territorio_priorizado OWNER TO postgres;

--
-- Name: territorio_priorizado_id_territorio_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

ALTER TABLE public.territorio_priorizado ALTER COLUMN id_territorio ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.territorio_priorizado_id_territorio_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: tipo_deposito; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.tipo_deposito (
    id_tipo_deposito bigint NOT NULL,
    nombre character varying(60) NOT NULL,
    descripcion character varying(200) DEFAULT NULL::character varying,
    estado smallint DEFAULT 1 NOT NULL
);


ALTER TABLE public.tipo_deposito OWNER TO postgres;

--
-- Name: tipo_deposito_id_tipo_deposito_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

ALTER TABLE public.tipo_deposito ALTER COLUMN id_tipo_deposito ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.tipo_deposito_id_tipo_deposito_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: tipo_documento; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.tipo_documento (
    id_tipodocumento bigint NOT NULL,
    nombre character varying(100) NOT NULL
);


ALTER TABLE public.tipo_documento OWNER TO postgres;

--
-- Name: tipo_documento_id_tipodocumento_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

ALTER TABLE public.tipo_documento ALTER COLUMN id_tipodocumento ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.tipo_documento_id_tipodocumento_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: tipo_tanque; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.tipo_tanque (
    id_tipo_tanque bigint NOT NULL,
    nombre character varying(60) NOT NULL,
    descripcion character varying(200) DEFAULT NULL::character varying,
    estado smallint DEFAULT 1 NOT NULL
);


ALTER TABLE public.tipo_tanque OWNER TO postgres;

--
-- Name: tipo_tanque_id_tipo_tanque_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

ALTER TABLE public.tipo_tanque ALTER COLUMN id_tipo_tanque ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.tipo_tanque_id_tipo_tanque_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: usuario; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.usuario (
    id_usuario bigint NOT NULL,
    id_tipodocumento bigint NOT NULL,
    id_rol bigint NOT NULL,
    nombre character varying(80) NOT NULL,
    apellido character varying(80) NOT NULL,
    correo character varying(120) NOT NULL,
    contrasena character varying(255) NOT NULL,
    estado smallint DEFAULT 1 NOT NULL,
    creado_en timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    intentos_fallidos integer DEFAULT 0 NOT NULL,
    bloqueo_hasta timestamp without time zone,
    token_recuperacion character varying(255) DEFAULT NULL::character varying,
    token_expira timestamp without time zone
);


ALTER TABLE public.usuario OWNER TO postgres;

--
-- Name: usuario_id_usuario_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

ALTER TABLE public.usuario ALTER COLUMN id_usuario ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.usuario_id_usuario_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: zoocriadero; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.zoocriadero (
    id_zoocriadero bigint NOT NULL,
    nombre character varying(100) NOT NULL,
    direccion character varying(200) NOT NULL,
    comuna character varying(60) DEFAULT NULL::character varying,
    barrio character varying(60) DEFAULT NULL::character varying,
    id_persona_cargo bigint,
    latitud numeric(10,8) NOT NULL,
    longitud numeric(11,8) NOT NULL,
    estado smallint DEFAULT 1 NOT NULL,
    creado_en timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


ALTER TABLE public.zoocriadero OWNER TO postgres;

--
-- Name: zoocriadero_id_zoocriadero_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

ALTER TABLE public.zoocriadero ALTER COLUMN id_zoocriadero ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.zoocriadero_id_zoocriadero_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Data for Name: accion_permiso; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.accion_permiso (id_accion_permiso, nombre) FROM stdin;
1	Eliminar
2	Registrar
3	Consultar
4	Editar
\.


--
-- Data for Name: actividad; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.actividad (id_actividad, ambito, nombre, descripcion, estado) FROM stdin;
1	zoocriadero	Limpiar tanque	Limpieza general del tanque y retiro de residuos	1
2	zoocriadero	Contar peces	Conteo de peces vivos, nacidos y muertos	1
3	zoocriadero	Equilibrar pH	Ajuste del pH del agua al rango 6.5 - 7.5	1
4	zoocriadero	Medir temperatura	Medición y ajuste de la temperatura del agua	1
5	zoocriadero	Sembrar alevinos	Siembra de alevinos en el tanque	1
6	zoocriadero	Cosechar peces	Extracción de peces para entrega a la comunidad	1
7	zoocriadero	Alimentar peces	Suministro de alimento concentrado	1
8	zoocriadero	Cambiar agua	Recambio parcial o total del agua	1
9	zoocriadero	Limpiar filtro	Limpieza o cambio del sistema de filtrado	1
10	zoocriadero	Retirar peces muertos	Retiro de la mortalidad encontrada en el tanque	1
11	zoocriadero	Revisar oxigenación	Revisión del sistema de aireación del tanque	1
12	zoocriadero	Clasificar por tallas	Separación de los peces según su tamaño	1
13	zoocriadero	Revisar reproducción	Verificación de hembras grávidas y crías	1
14	zoocriadero	Aplicar tratamiento	Tratamiento sanitario a los peces del tanque	1
15	terreno	Inspección	Inspección de depósitos en vivienda	1
16	terreno	Eliminación	Eliminación de criaderos encontrados	1
17	terreno	Larvicida	Aplicación de larvicida en depósitos útiles	1
18	terreno	Entrega de peces	Entrega de guppys a la comunidad	1
19	terreno	Educación	Jornada de educación sanitaria	1
20	terreno	Lavado	Lavado y cepillado de tanques y albercas	1
21	terreno	Tapado	Tapado de depósitos de agua	1
22	terreno	Seguimiento	Visita de seguimiento a vivienda intervenida	1
23	terreno	Censo de criaderos	Registro de depósitos por vivienda	1
\.


--
-- Data for Name: actividad_terreno; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.actividad_terreno (id_actividad_terreno, id_seguimiento_terreno, id_actividad) FROM stdin;
7	7	15
8	8	16
9	9	17
10	10	19
11	11	22
12	12	23
\.


--
-- Data for Name: actividad_zoocriadero; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.actividad_zoocriadero (id_actividad_zoocriadero, id_seguimiento, id_actividad) FROM stdin;
54	1	1
55	2	2
56	3	3
57	4	4
58	5	5
59	6	6
60	7	7
61	8	8
62	9	9
63	10	10
64	11	11
65	12	12
66	13	13
67	14	14
68	15	1
69	16	2
70	17	3
71	18	4
72	19	5
73	20	6
74	21	7
75	22	8
76	23	9
77	24	10
78	25	11
79	26	12
80	27	13
81	28	14
82	29	1
83	30	2
84	31	3
85	32	4
\.


--
-- Data for Name: auditoria_seguimiento_zoocriadero; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.auditoria_seguimiento_zoocriadero (id_auditoria, id_seguimiento, id_usuario, operacion, momento, fecha_evento, hora_evento, fecha_hora_evento, detalle) FROM stdin;
1	1	5	INSERTAR	DESPUES	2026-09-14	18:40:18.247231	2026-09-14 18:40:18.247231	Se registro un nuevo seguimiento de zoocriadero
2	2	5	INSERTAR	DESPUES	2026-09-14	18:40:18.247231	2026-09-14 18:40:18.247231	Se registro un nuevo seguimiento de zoocriadero
3	3	3	INSERTAR	DESPUES	2026-09-14	18:40:18.247231	2026-09-14 18:40:18.247231	Se registro un nuevo seguimiento de zoocriadero
4	4	3	INSERTAR	DESPUES	2026-09-14	18:40:18.247231	2026-09-14 18:40:18.247231	Se registro un nuevo seguimiento de zoocriadero
5	5	4	INSERTAR	DESPUES	2026-09-14	18:40:18.247231	2026-09-14 18:40:18.247231	Se registro un nuevo seguimiento de zoocriadero
6	6	4	INSERTAR	DESPUES	2026-09-14	18:40:18.247231	2026-09-14 18:40:18.247231	Se registro un nuevo seguimiento de zoocriadero
7	7	1	INSERTAR	DESPUES	2026-09-14	18:40:18.247231	2026-09-14 18:40:18.247231	Se registro un nuevo seguimiento de zoocriadero
8	8	1	INSERTAR	DESPUES	2026-09-14	18:40:18.247231	2026-09-14 18:40:18.247231	Se registro un nuevo seguimiento de zoocriadero
9	9	5	INSERTAR	DESPUES	2026-09-14	18:51:37.977149	2026-09-14 18:51:37.977149	Se registro un nuevo seguimiento de zoocriadero
10	10	5	INSERTAR	DESPUES	2026-09-14	18:51:37.977149	2026-09-14 18:51:37.977149	Se registro un nuevo seguimiento de zoocriadero
11	11	3	INSERTAR	DESPUES	2026-09-14	18:51:37.977149	2026-09-14 18:51:37.977149	Se registro un nuevo seguimiento de zoocriadero
12	12	3	INSERTAR	DESPUES	2026-09-14	18:51:37.977149	2026-09-14 18:51:37.977149	Se registro un nuevo seguimiento de zoocriadero
13	13	4	INSERTAR	DESPUES	2026-09-14	18:51:37.977149	2026-09-14 18:51:37.977149	Se registro un nuevo seguimiento de zoocriadero
14	14	4	INSERTAR	DESPUES	2026-09-14	18:51:37.977149	2026-09-14 18:51:37.977149	Se registro un nuevo seguimiento de zoocriadero
15	15	1	INSERTAR	DESPUES	2026-09-14	18:51:37.977149	2026-09-14 18:51:37.977149	Se registro un nuevo seguimiento de zoocriadero
16	16	1	INSERTAR	DESPUES	2026-09-14	18:51:37.977149	2026-09-14 18:51:37.977149	Se registro un nuevo seguimiento de zoocriadero
17	17	5	INSERTAR	DESPUES	2026-09-14	18:51:38.274627	2026-09-14 18:51:38.274627	Se registro un nuevo seguimiento de zoocriadero
18	18	5	INSERTAR	DESPUES	2026-09-14	18:51:38.274627	2026-09-14 18:51:38.274627	Se registro un nuevo seguimiento de zoocriadero
19	19	3	INSERTAR	DESPUES	2026-09-14	18:51:38.274627	2026-09-14 18:51:38.274627	Se registro un nuevo seguimiento de zoocriadero
20	20	3	INSERTAR	DESPUES	2026-09-14	18:51:38.274627	2026-09-14 18:51:38.274627	Se registro un nuevo seguimiento de zoocriadero
21	21	4	INSERTAR	DESPUES	2026-09-14	18:51:38.274627	2026-09-14 18:51:38.274627	Se registro un nuevo seguimiento de zoocriadero
22	22	4	INSERTAR	DESPUES	2026-09-14	18:51:38.274627	2026-09-14 18:51:38.274627	Se registro un nuevo seguimiento de zoocriadero
23	23	1	INSERTAR	DESPUES	2026-09-14	18:51:38.274627	2026-09-14 18:51:38.274627	Se registro un nuevo seguimiento de zoocriadero
24	24	1	INSERTAR	DESPUES	2026-09-14	18:51:38.274627	2026-09-14 18:51:38.274627	Se registro un nuevo seguimiento de zoocriadero
25	25	5	INSERTAR	DESPUES	2026-09-14	18:51:38.472225	2026-09-14 18:51:38.472225	Se registro un nuevo seguimiento de zoocriadero
26	26	5	INSERTAR	DESPUES	2026-09-14	18:51:38.472225	2026-09-14 18:51:38.472225	Se registro un nuevo seguimiento de zoocriadero
27	27	3	INSERTAR	DESPUES	2026-09-14	18:51:38.472225	2026-09-14 18:51:38.472225	Se registro un nuevo seguimiento de zoocriadero
28	28	3	INSERTAR	DESPUES	2026-09-14	18:51:38.472225	2026-09-14 18:51:38.472225	Se registro un nuevo seguimiento de zoocriadero
29	29	4	INSERTAR	DESPUES	2026-09-14	18:51:38.472225	2026-09-14 18:51:38.472225	Se registro un nuevo seguimiento de zoocriadero
30	30	4	INSERTAR	DESPUES	2026-09-14	18:51:38.472225	2026-09-14 18:51:38.472225	Se registro un nuevo seguimiento de zoocriadero
31	31	1	INSERTAR	DESPUES	2026-09-14	18:51:38.472225	2026-09-14 18:51:38.472225	Se registro un nuevo seguimiento de zoocriadero
32	32	1	INSERTAR	DESPUES	2026-09-14	18:51:38.472225	2026-09-14 18:51:38.472225	Se registro un nuevo seguimiento de zoocriadero
233	1	5	ACTUALIZAR	DESPUES	2026-09-15	11:50:14.556232	2026-09-15 11:50:14.556232	Se actualizo un seguimiento de zoocriadero
234	2	5	ACTUALIZAR	DESPUES	2026-09-15	11:50:14.556232	2026-09-15 11:50:14.556232	Se actualizo un seguimiento de zoocriadero
235	3	3	ACTUALIZAR	DESPUES	2026-09-15	11:50:14.556232	2026-09-15 11:50:14.556232	Se actualizo un seguimiento de zoocriadero
236	4	3	ACTUALIZAR	DESPUES	2026-09-15	11:50:14.556232	2026-09-15 11:50:14.556232	Se actualizo un seguimiento de zoocriadero
237	5	4	ACTUALIZAR	DESPUES	2026-09-15	11:50:14.556232	2026-09-15 11:50:14.556232	Se actualizo un seguimiento de zoocriadero
238	6	4	ACTUALIZAR	DESPUES	2026-09-15	11:50:14.556232	2026-09-15 11:50:14.556232	Se actualizo un seguimiento de zoocriadero
239	7	1	ACTUALIZAR	DESPUES	2026-09-15	11:50:14.556232	2026-09-15 11:50:14.556232	Se actualizo un seguimiento de zoocriadero
240	8	1	ACTUALIZAR	DESPUES	2026-09-15	11:50:14.556232	2026-09-15 11:50:14.556232	Se actualizo un seguimiento de zoocriadero
241	9	5	ACTUALIZAR	DESPUES	2026-09-15	11:50:14.556232	2026-09-15 11:50:14.556232	Se actualizo un seguimiento de zoocriadero
242	10	5	ACTUALIZAR	DESPUES	2026-09-15	11:50:14.556232	2026-09-15 11:50:14.556232	Se actualizo un seguimiento de zoocriadero
243	11	3	ACTUALIZAR	DESPUES	2026-09-15	11:50:14.556232	2026-09-15 11:50:14.556232	Se actualizo un seguimiento de zoocriadero
244	12	3	ACTUALIZAR	DESPUES	2026-09-15	11:50:14.556232	2026-09-15 11:50:14.556232	Se actualizo un seguimiento de zoocriadero
245	13	4	ACTUALIZAR	DESPUES	2026-09-15	11:50:14.556232	2026-09-15 11:50:14.556232	Se actualizo un seguimiento de zoocriadero
246	14	4	ACTUALIZAR	DESPUES	2026-09-15	11:50:14.556232	2026-09-15 11:50:14.556232	Se actualizo un seguimiento de zoocriadero
247	15	1	ACTUALIZAR	DESPUES	2026-09-15	11:50:14.556232	2026-09-15 11:50:14.556232	Se actualizo un seguimiento de zoocriadero
248	16	1	ACTUALIZAR	DESPUES	2026-09-15	11:50:14.556232	2026-09-15 11:50:14.556232	Se actualizo un seguimiento de zoocriadero
249	17	5	ACTUALIZAR	DESPUES	2026-09-15	11:50:14.556232	2026-09-15 11:50:14.556232	Se actualizo un seguimiento de zoocriadero
250	18	5	ACTUALIZAR	DESPUES	2026-09-15	11:50:14.556232	2026-09-15 11:50:14.556232	Se actualizo un seguimiento de zoocriadero
251	19	3	ACTUALIZAR	DESPUES	2026-09-15	11:50:14.556232	2026-09-15 11:50:14.556232	Se actualizo un seguimiento de zoocriadero
252	20	3	ACTUALIZAR	DESPUES	2026-09-15	11:50:14.556232	2026-09-15 11:50:14.556232	Se actualizo un seguimiento de zoocriadero
253	21	4	ACTUALIZAR	DESPUES	2026-09-15	11:50:14.556232	2026-09-15 11:50:14.556232	Se actualizo un seguimiento de zoocriadero
254	22	4	ACTUALIZAR	DESPUES	2026-09-15	11:50:14.556232	2026-09-15 11:50:14.556232	Se actualizo un seguimiento de zoocriadero
255	23	1	ACTUALIZAR	DESPUES	2026-09-15	11:50:14.556232	2026-09-15 11:50:14.556232	Se actualizo un seguimiento de zoocriadero
256	24	1	ACTUALIZAR	DESPUES	2026-09-15	11:50:14.556232	2026-09-15 11:50:14.556232	Se actualizo un seguimiento de zoocriadero
257	25	5	ACTUALIZAR	DESPUES	2026-09-15	11:50:14.556232	2026-09-15 11:50:14.556232	Se actualizo un seguimiento de zoocriadero
258	26	5	ACTUALIZAR	DESPUES	2026-09-15	11:50:14.556232	2026-09-15 11:50:14.556232	Se actualizo un seguimiento de zoocriadero
259	27	3	ACTUALIZAR	DESPUES	2026-09-15	11:50:14.556232	2026-09-15 11:50:14.556232	Se actualizo un seguimiento de zoocriadero
260	28	3	ACTUALIZAR	DESPUES	2026-09-15	11:50:14.556232	2026-09-15 11:50:14.556232	Se actualizo un seguimiento de zoocriadero
261	29	4	ACTUALIZAR	DESPUES	2026-09-15	11:50:14.556232	2026-09-15 11:50:14.556232	Se actualizo un seguimiento de zoocriadero
262	30	4	ACTUALIZAR	DESPUES	2026-09-15	11:50:14.556232	2026-09-15 11:50:14.556232	Se actualizo un seguimiento de zoocriadero
263	31	1	ACTUALIZAR	DESPUES	2026-09-15	11:50:14.556232	2026-09-15 11:50:14.556232	Se actualizo un seguimiento de zoocriadero
264	32	1	ACTUALIZAR	DESPUES	2026-09-15	11:50:14.556232	2026-09-15 11:50:14.556232	Se actualizo un seguimiento de zoocriadero
\.


--
-- Data for Name: auditoria_sistema; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.auditoria_sistema (id_auditoria, tabla_afectada, operacion, momento, id_usuario, usuario_responsable, fecha_evento, hora_evento, fecha_hora_evento, datos_anteriores, datos_nuevos, detalle) FROM stdin;
\.


--
-- Data for Name: barrio; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.barrio (id_barrio, id_comuna, nombre) FROM stdin;
1	11	Rodrigo Lara Bonilla
2	4	Los Guayacanes
3	3	Puerto Nuevo
4	17	Ciudad Capri
5	22	El Paraíso
6	14	El Retiro
7	22	Julio Rincón
8	12	Pizamos I
9	2	Polvorines
10	4	Chiminangos Segunda Etapa
11	14	Comuneros I
12	12	Compartir
13	20	Departamental
14	5	Calima
15	12	Potrero Grande
16	16	Manuela Beltrán
17	18	El Cedro
18	14	El Morichal
19	4	El Sena
20	17	Lili
21	1	Normandía
22	5	Flora Industrial
23	18	Los Cámbulos
24	1	Juanambú
25	15	Jockey Club
26	11	Charco Azul
27	20	Pasoancho
28	16	Alfonso Bonilla Aragón
29	21	Lleras Camargo
30	10	San Nicolás
31	7	Benjamín Herrera
32	19	Calimio Norte
33	1	Granada
34	13	Aranjuez
35	6	Mariano Ramos
36	16	José Manuel Marroquín I
37	5	Ignacio Rengifo
38	1	Chipichape
39	21	El Cortijo
40	11	Calipso
41	10	San Pedro
42	3	Alfonso López I
43	5	Fátima
44	2	Mario Correa Rengifo
45	8	San Benito
46	19	La Rivera II
47	1	Arboledas
48	1	El Bosque
49	21	Brisas de Mayo
50	16	Los Naranjos
51	9	Patio Bonito
52	1	San Vicente
53	13	Manuel María Buenaventura
54	5	El Porvenir
55	15	Alférez Real
56	17	La Hacienda
57	20	Cristóbal Colón
58	18	Nueva Tequendama
59	2	Nápoles
60	1	La Campiña
61	1	Altos de Menga
62	11	El Poblado I
63	1	Vipasa
64	13	Guayaquil
65	11	Villablanca
66	7	Primitivo Crespo
67	17	El Caney
68	13	Belalcázar
69	16	Las Orquídeas
70	16	Promociones Populares
71	19	Paso del Comercio
72	7	Chapinero
73	10	Los Libertadores
74	1	Versalles
75	5	Evaristo García
76	5	Olaya Herrera
77	16	Puertas del Sol
78	17	Las Quintas de Don Simón
79	18	Pampalinda
80	13	Bretaña
81	7	Simón Bolívar
82	19	Ciudadela Floralia
83	2	Alto Nápoles
84	21	Siloé
85	19	Petecuy II
86	2	Colinas del Sur
87	3	Siete de Agosto
88	21	Tierra Blanca
89	11	Los Robles
90	8	José Holguín Garcés
91	7	Municipal
92	7	Santa Fe
93	10	San Antonio
94	10	Navarro
95	17	Mayapán
96	14	Laureano Gómez
97	8	Bellavista
98	17	El Limonar
99	11	El Vergel
100	7	Industrial
101	4	Metropolitano del Norte
102	9	Sector Altos de Aguacatal
103	12	Las Dalias
104	2	Horizontes
105	8	Veinte de Julio
106	18	El Refugio
107	5	Jorge Isaacs
108	5	Bueno Madrid
109	20	Santa Isabel
110	15	Urbanización Río Lili
111	1	Centenario
112	12	Pizamos III
113	20	Jorge Zawadsky
114	13	Barrio Obrero
115	3	Base Aérea
116	5	Las Delicias
117	10	San Juan Bosco
118	7	Saavedra Galindo
119	16	Alirio Mora Beltrán
120	13	Sucre
121	11	El Poblado II
122	19	Puente del Comercio
123	12	Desepaz
124	10	Santa Rosa
125	5	La Esmeralda
126	17	El Gran Limonar
127	14	Comuneros II
128	22	Eduardo Santos
129	19	Ciudad Los Álamos
130	10	San Pascual
131	22	Bello Horizonte
132	7	Villa Colombia
133	2	Prados del Sur
134	7	Atanasio Girardot
135	12	Villa del Lago
136	8	El Guabal
137	22	Alfonso Barberena
138	17	Ciudad 2000
139	8	La Libertad
140	4	Torres de Comfandi
141	8	Villanueva
142	7	La Floresta
143	4	Ciudadela Comfandi
144	6	Unión de Vivienda Popular
145	14	Brisas del Bosque
146	8	La Ferroviaria
147	5	Guillermo Valencia
148	20	El Dorado
149	10	El Hoyo
150	17	Bosques del Limonar
151	14	Mojica
152	13	Junín
153	14	El Vallado
154	19	Sindical
155	1	La Flora
156	1	Santa Rita
157	17	Las Vegas
158	10	El Calvario
159	6	Brisas del Limonar
160	2	Meléndez
161	3	Fepicol
162	7	La Base
163	5	Salomia
164	9	Bajo Aguacatal
165	21	La Sultana Ladera
166	11	Lleras Restrepo
167	1	Prados del Norte
168	18	Camino Real
169	1	Santa Mónica
170	22	Nueva Floresta
171	22	Villa del Sur
172	22	Doce de Octubre
173	9	Vista Hermosa
174	8	Las Granjas
175	9	Aguacatal
176	7	El Troncal
177	18	El Ingenio
178	11	Ricardo Balcázar
179	11	Ulpiano Lloreda
180	20	Colseguros
181	7	Las Américas
182	12	Valle Grande
183	2	Francisco Eladio Ramírez
184	6	La Alborada
185	15	Pance
186	15	Ciudad Jardín
187	19	Los Alcázares
188	22	San Judas Tadeo II
189	4	Los Andes
190	3	Alfonso López II
191	18	San Fernando Nuevo
192	4	Villa del Prado
193	18	El Lido
194	20	San Cristóbal
195	18	Tequendama
196	10	El Nacional
197	5	Manzanares
198	5	Bolivariano
199	7	Santa Mónica Popular
200	10	San Cayetano
201	18	Cuarto de Legua
202	17	Ciudad Universitaria
203	20	La Selva
204	3	Los Pinos
205	2	Lourdes
206	15	Cañasgordas
207	4	Chiminangos Primera Etapa
208	3	Alfonso López III
209	3	Puerto Mallarino
210	21	Pueblo Joven
211	5	La Isla
212	3	Parque de la Caña
213	13	Alameda
214	22	El Rodeo
215	17	Primero de Mayo
216	10	El Piloto
217	12	Calimio Desepaz
218	7	El Trébol
219	9	Terrón Colorado
220	6	Antonio Nariño
221	16	José Manuel Marroquín II
222	19	San Luis
223	5	Santander
224	21	Belén
225	11	Omar Torrijos
226	18	San Fernando Viejo
227	18	Champagnat
228	12	Pizamos II
229	12	Los Líderes
230	8	San Pedro Claver
231	2	Los Chorros
232	15	Ciudad Campestre
233	20	Olímpico
234	18	El Templete
235	21	Cementerio Carabineros
236	11	Yira Castro
237	4	Paseo de los Almendros
238	12	Ciudadela del Río
239	6	República de Israel
240	19	Petecuy I
241	19	Petecuy III
242	14	Ciudad Córdoba
243	10	La Merced
244	8	Primavera
245	1	Menga
246	19	Jorge Eliécer Gaitán
247	22	Asturias
248	19	Los Guaduales
249	22	San Judas Tadeo I
250	2	Alto Jordán
251	18	Bosque Municipal
252	8	Fenalco Kennedy
253	4	Villa del Sol
254	15	Parcelaciones de Pance
255	8	Maracaibo
256	4	La Rivera I
257	20	Panamericano
258	8	Boyacá
259	5	Berlín
260	2	Caldas
261	2	Buenos Aires
262	12	Remansos de Comfandi
263	18	Miraflores
264	8	La Esperanza
265	5	La Sultana
266	20	Las Acacias
267	3	San Marino
\.


--
-- Data for Name: ciudad; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.ciudad (id_ciudad, id_comuna, nombre) FROM stdin;
2	1	Cali
\.


--
-- Data for Name: comuna; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.comuna (id_comuna, id_barrio, nombre) FROM stdin;
1	\N	Comuna 2
2	\N	Comuna 18
3	\N	Comuna 7
4	\N	Comuna 5
5	\N	Comuna 4
6	\N	Comuna 16
7	\N	Comuna 8
8	\N	Comuna 11
9	\N	Comuna 1
10	\N	Comuna 3
11	\N	Comuna 13
12	\N	Comuna 21
13	\N	Comuna 9
14	\N	Comuna 15
15	\N	Comuna 22
16	\N	Comuna 14
17	\N	Comuna 17
18	\N	Comuna 19
19	\N	Comuna 6
20	\N	Comuna 10
21	\N	Comuna 20
22	\N	Comuna 12
\.


--
-- Data for Name: copia_seguridad_historial; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.copia_seguridad_historial (id_historial, fecha_hora, tipo_operacion, nombre_archivo, id_usuario, usuario_nombre, estado, detalle) FROM stdin;
\.


--
-- Data for Name: departamento; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.departamento (id_departamento, id_ciudad, nombre) FROM stdin;
2	2	Valle del Cauca
\.


--
-- Data for Name: direccion; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.direccion (id_direccion, id_departamento, id_comuna, id_ciudad, id_barrio, direccion, id_nomenclatura) FROM stdin;
7	2	11	2	1	Cra 31 # 22-71	6
8	2	4	2	2	Calle 13 # 24-05	6
9	2	3	2	3	Cra 26 # 9-40	6
10	2	17	2	4	Cra 8 # 45-12	6
11	2	22	2	5	Calle 70 # 28D-19	6
12	2	14	2	6	Cra 5 # 12-30	6
\.


--
-- Data for Name: modulo; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.modulo (id_modulo, nombre, descripcion) FROM stdin;
1	Terreno	Depósitos y actividades de terreno
2	Usuarios	Gestión de usuarios del sistema
3	Configuraciones	Parámetros generales del sistema
4	Zoocriaderos	Gestión de zoocriaderos y tanques
5	Copia de seguridad	Respaldo de la base de datos
6	Reportes	Reportes y gráficos del sistema
7	Roles	Creación de roles y asignación de permisos
\.


--
-- Data for Name: nomenclatura; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.nomenclatura (id_nomenclatura, nomenclatura) FROM stdin;
6	Calle
7	Carrera
8	Avenida
9	Diagonal
10	Transversal
\.


--
-- Data for Name: rol; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.rol (id_rol, nombre_rol, descripcion, estado) FROM stdin;
1	Coordinador	Coordina control biológico y ecosalud	1
2	Administrador	Director(a) del Grupo ETV	1
3	Auxiliar	Personal de campo	1
4	Super Administrador	Administra la base de datos y la configuración del sistema	1
\.


--
-- Data for Name: rol_permiso; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.rol_permiso (id_rol, id_modulo, id_accion_permiso) FROM stdin;
4	7	4
4	3	4
4	2	1
4	6	3
4	5	4
4	5	1
4	2	4
4	3	1
4	7	1
4	4	2
4	2	3
4	6	1
4	1	2
4	7	3
4	3	3
4	6	4
4	5	3
4	1	1
4	6	2
4	4	3
4	1	4
4	4	4
4	2	2
4	1	3
4	5	2
4	4	1
4	7	2
4	3	2
2	4	4
2	1	3
2	2	2
2	3	2
2	7	2
2	4	1
2	1	1
2	6	2
2	4	3
2	1	4
2	6	1
2	1	2
2	2	3
2	3	3
2	7	3
2	6	4
2	3	4
2	7	4
2	6	3
2	2	1
2	2	4
2	4	2
2	7	1
2	3	1
1	1	3
1	4	4
1	1	4
1	4	3
1	3	3
1	1	2
1	6	3
1	4	2
3	1	2
3	6	3
3	1	3
3	4	3
\.


--
-- Data for Name: seguimiento_terreno; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.seguimiento_terreno (id_seguimiento_terreno, id_sitio, id_usuario, fecha, estado) FROM stdin;
7	7	1	2026-08-05	1
8	8	2	2026-08-12	1
9	9	3	2026-08-19	1
10	10	4	2026-08-26	1
11	11	5	2026-09-02	1
12	12	1	2026-09-09	1
\.


--
-- Data for Name: seguimiento_zoocriadero; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.seguimiento_zoocriadero (id_seguimiento, id_zoocriadero, id_tanque, id_usuario, fecha, ph, temperatura, numero_sembrados, numero_nacidos, numero_muertos, numero_nacidos_hembra, numero_nacidos_macho, numero_muertos_hembra, numero_muertos_macho, observaciones, estado, estado_actividad) FROM stdin;
1	2	1	5	2026-08-03	7.10	26.50	200	15	2	7	8	1	1	Limpieza y conteo de rutina	1	Completada
2	2	2	5	2026-08-10	6.90	27.00	0	0	5	0	0	2	3	pH bajo, se ajustó con cal	1	Completada
3	1	5	3	2026-08-15	7.30	25.80	150	0	0	0	0	0	0	Revisión de rutina, sin novedad	1	En progreso
4	1	7	3	2026-08-20	7.00	26.10	0	0	3	0	0	1	2	Mortalidad detectada, pendiente tratamiento	1	Retrasada
5	4	8	4	2026-08-25	6.80	27.50	220	20	1	10	10	0	1	Cosecha parcial realizada	1	Completada
6	4	9	4	2026-09-01	7.20	26.00	0	0	0	0	0	0	0	Cambio de agua en curso	1	En progreso
7	3	10	1	2026-09-05	6.50	28.00	100	5	7	2	3	3	4	Filtro sucio, requiere visita adicional	1	Retrasada
8	5	13	1	2026-09-08	7.15	26.40	300	30	4	15	15	2	2	Siembra de alevinos nueva	1	Completada
9	2	1	5	2026-08-03	7.10	26.50	200	15	2	7	8	1	1	Limpieza y conteo de rutina	1	Completada
10	2	2	5	2026-08-10	6.90	27.00	0	0	5	0	0	2	3	pH bajo, se ajustó con cal	1	Completada
11	1	5	3	2026-08-15	7.30	25.80	150	0	0	0	0	0	0	Revisión de rutina, sin novedad	1	En progreso
12	1	7	3	2026-08-20	7.00	26.10	0	0	3	0	0	1	2	Mortalidad detectada, pendiente tratamiento	1	Retrasada
13	4	8	4	2026-08-25	6.80	27.50	220	20	1	10	10	0	1	Cosecha parcial realizada	1	Completada
14	4	9	4	2026-09-01	7.20	26.00	0	0	0	0	0	0	0	Cambio de agua en curso	1	En progreso
15	3	10	1	2026-09-05	6.50	28.00	100	5	7	2	3	3	4	Filtro sucio, requiere visita adicional	1	Retrasada
16	5	13	1	2026-09-08	7.15	26.40	300	30	4	15	15	2	2	Siembra de alevinos nueva	1	Completada
17	2	1	5	2026-08-03	7.10	26.50	200	15	2	7	8	1	1	Limpieza y conteo de rutina	1	Completada
18	2	2	5	2026-08-10	6.90	27.00	0	0	5	0	0	2	3	pH bajo, se ajustó con cal	1	Completada
19	1	5	3	2026-08-15	7.30	25.80	150	0	0	0	0	0	0	Revisión de rutina, sin novedad	1	En progreso
20	1	7	3	2026-08-20	7.00	26.10	0	0	3	0	0	1	2	Mortalidad detectada, pendiente tratamiento	1	Retrasada
21	4	8	4	2026-08-25	6.80	27.50	220	20	1	10	10	0	1	Cosecha parcial realizada	1	Completada
22	4	9	4	2026-09-01	7.20	26.00	0	0	0	0	0	0	0	Cambio de agua en curso	1	En progreso
23	3	10	1	2026-09-05	6.50	28.00	100	5	7	2	3	3	4	Filtro sucio, requiere visita adicional	1	Retrasada
24	5	13	1	2026-09-08	7.15	26.40	300	30	4	15	15	2	2	Siembra de alevinos nueva	1	Completada
25	2	1	5	2026-08-03	7.10	26.50	200	15	2	7	8	1	1	Limpieza y conteo de rutina	1	Completada
26	2	2	5	2026-08-10	6.90	27.00	0	0	5	0	0	2	3	pH bajo, se ajustó con cal	1	Completada
27	1	5	3	2026-08-15	7.30	25.80	150	0	0	0	0	0	0	Revisión de rutina, sin novedad	1	En progreso
28	1	7	3	2026-08-20	7.00	26.10	0	0	3	0	0	1	2	Mortalidad detectada, pendiente tratamiento	1	Retrasada
29	4	8	4	2026-08-25	6.80	27.50	220	20	1	10	10	0	1	Cosecha parcial realizada	1	Completada
30	4	9	4	2026-09-01	7.20	26.00	0	0	0	0	0	0	0	Cambio de agua en curso	1	En progreso
31	3	10	1	2026-09-05	6.50	28.00	100	5	7	2	3	3	4	Filtro sucio, requiere visita adicional	1	Retrasada
32	5	13	1	2026-09-08	7.15	26.40	300	30	4	15	15	2	2	Siembra de alevinos nueva	1	Completada
\.


--
-- Data for Name: sitio; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.sitio (id_sitio, id_tipo_deposito, id_direccion, direccion, comuna, barrio, latitud, longitud, estado, creado_en) FROM stdin;
7	1	7	Cra 31 # 22-71	Comuna 13	Rodrigo Lara Bonilla	\N	\N	1	2026-09-14 23:45:45.944848
8	2	8	Calle 13 # 24-05	Comuna 5	Los Guayacanes	\N	\N	1	2026-09-14 23:45:45.944848
9	4	9	Cra 26 # 9-40	Comuna 7	Puerto Nuevo	\N	\N	1	2026-09-14 23:45:45.944848
10	5	10	Cra 8 # 45-12	Comuna 17	Ciudad Capri	\N	\N	1	2026-09-14 23:45:45.944848
11	7	11	Calle 70 # 28D-19	Comuna 12	El Paraíso	\N	\N	0	2026-09-14 23:45:45.944848
12	8	12	Cra 5 # 12-30	Comuna 15	El Retiro	\N	\N	1	2026-09-14 23:45:45.944848
\.


--
-- Data for Name: tanque; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.tanque (id_tanque, id_zoocriadero, id_tipo_tanque, numero_tanque, estado) FROM stdin;
1	2	5	1	1
2	2	2	2	1
3	2	4	3	1
4	2	6	4	1
5	1	5	1	1
6	1	1	2	1
7	1	2	3	1
8	4	5	1	1
9	4	3	2	1
10	3	5	1	1
11	3	6	2	1
12	3	7	3	1
13	5	5	1	1
14	5	4	2	1
15	5	1	3	1
\.


--
-- Data for Name: territorio_priorizado; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.territorio_priorizado (id_territorio, comuna, barrio, sitio, direccion_sitio, nombre_lider, direccion_lider, telefono_lider, correo_lider, clase_liderazgo, id_funcionario_ecosalud, latitud, longitud, fecha_registro, estado) FROM stdin;
\.


--
-- Data for Name: tipo_deposito; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.tipo_deposito (id_tipo_deposito, nombre, descripcion, estado) FROM stdin;
1	Tanque bajo	Tanque de almacenamiento a nivel de piso	1
2	Canaleta	Canaleta obstruida con agua estancada	1
3	Inservible	Recipiente inservible acumulador de agua	1
4	Materas	Materas y platos de materas	1
5	Llanta	Llanta a la intemperie	1
6	Floreros	Floreros de cementerio o de casa	1
7	Bebedero de animal	Recipiente de agua para mascotas	1
8	Tanque elevado	Tanque elevado o de azotea	1
9	Alberca	Alberca o lavadero	1
10	Pozo	Pozo o aljibe descubierto	1
\.


--
-- Data for Name: tipo_documento; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.tipo_documento (id_tipodocumento, nombre) FROM stdin;
1	Permiso por Protección Temporal
2	Cédula de extranjería
3	Cédula de ciudadanía
4	Pasaporte
5	Tarjeta de identidad
\.


--
-- Data for Name: tipo_tanque; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.tipo_tanque (id_tipo_tanque, nombre, descripcion, estado) FROM stdin;
1	Geomembrana	Estanque revestido en geomembrana	1
2	Vidrio	Acuario o pecera de vidrio para cría controlada	1
3	Metálico	Tanque metálico con recubrimiento interno	1
4	Fibra de vidrio	Tanque en fibra de vidrio, resistente a la intemperie	1
5	Plástico	Tanque plástico estándar de 500 a 1000 litros	1
6	Concreto	Estanque en concreto construido en sitio	1
7	Eternit	Tanque tipo eternit reutilizado como estanque	1
\.


--
-- Data for Name: usuario; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.usuario (id_usuario, id_tipodocumento, id_rol, nombre, apellido, correo, contrasena, estado, creado_en, intentos_fallidos, bloqueo_hasta, token_recuperacion, token_expira) FROM stdin;
1	3	3	María José	Perlaza	maria.perlaza@cali.gov.co	$2y$10$krffGFo.lHLb7KgLEAGf8ubBH5rWyCkSR/fwkaV/okl0xizVuG04C	1	2026-09-14 06:42:02.58288	0	\N	\N	\N
2	3	1	Luisa Fernanda	Ríos	luisa.rios@cali.gov.co	$2y$10$krffGFo.lHLb7KgLEAGf8ubBH5rWyCkSR/fwkaV/okl0xizVuG04C	1	2026-09-14 06:42:02.58288	0	\N	\N	\N
3	3	3	Diana Marcela	Ortiz	diana.ortiz@cali.gov.co	$2y$10$krffGFo.lHLb7KgLEAGf8ubBH5rWyCkSR/fwkaV/okl0xizVuG04C	1	2026-09-14 06:42:02.58288	0	\N	\N	\N
4	3	3	Jhon Édison	Valencia	jhon.valencia@cali.gov.co	$2y$10$krffGFo.lHLb7KgLEAGf8ubBH5rWyCkSR/fwkaV/okl0xizVuG04C	1	2026-09-14 06:42:02.58288	0	\N	\N	\N
5	3	3	Carlos Andrés	Mosquera	carlos.mosquera@cali.gov.co	$2y$10$krffGFo.lHLb7KgLEAGf8ubBH5rWyCkSR/fwkaV/okl0xizVuG04C	1	2026-09-14 06:42:02.58288	0	\N	\N	\N
6	3	2	Andrés Felipe	Caicedo	andres.caicedo@cali.gov.co	$2y$10$krffGFo.lHLb7KgLEAGf8ubBH5rWyCkSR/fwkaV/okl0xizVuG04C	1	2026-09-14 06:42:02.58288	0	\N	\N	\N
\.


--
-- Data for Name: zoocriadero; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.zoocriadero (id_zoocriadero, nombre, direccion, comuna, barrio, id_persona_cargo, latitud, longitud, estado, creado_en) FROM stdin;
1	Zoocriadero Norte	Cra 8 # 45-12	Comuna 2	Granada	5	3.46210000	-76.53120000	1	2026-09-14 06:42:02.58288
2	Zoocriadero Central	Calle 13 # 24-05	Comuna 11	El Guabal	2	3.42158000	-76.52050000	1	2026-09-14 06:42:02.58288
3	Zoocriadero Ladera	Cra 26 # 9-40	Comuna 18	Meléndez	4	3.38720000	-76.54990000	0	2026-09-14 06:42:02.58288
4	Zoocriadero Oriente	Calle 70 # 28D-19	Comuna 15	El Retiro	3	3.43980000	-76.49010000	1	2026-09-14 06:42:02.58288
5	Zoocriadero Aguablanca	Cra 31 # 22-71	Comuna 15	Mojica	1	3.41050000	-76.47330000	1	2026-09-14 06:42:02.58288
\.


--
-- Name: accion_permiso_id_accion_permiso_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.accion_permiso_id_accion_permiso_seq', 4, true);


--
-- Name: actividad_id_actividad_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.actividad_id_actividad_seq', 23, true);


--
-- Name: actividad_terreno_id_actividad_terreno_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.actividad_terreno_id_actividad_terreno_seq', 12, true);


--
-- Name: actividad_zoocriadero_id_actividad_zoocriadero_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.actividad_zoocriadero_id_actividad_zoocriadero_seq', 87, true);


--
-- Name: auditoria_seguimiento_zoocriadero_id_auditoria_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.auditoria_seguimiento_zoocriadero_id_auditoria_seq', 280, true);


--
-- Name: auditoria_sistema_id_auditoria_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.auditoria_sistema_id_auditoria_seq', 1, false);


--
-- Name: barrio_id_barrio_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.barrio_id_barrio_seq', 267, true);


--
-- Name: ciudad_id_ciudad_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.ciudad_id_ciudad_seq', 2, true);


--
-- Name: comuna_id_comuna_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.comuna_id_comuna_seq', 22, true);


--
-- Name: copia_seguridad_historial_id_historial_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.copia_seguridad_historial_id_historial_seq', 1, false);


--
-- Name: departamento_id_departamento_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.departamento_id_departamento_seq', 2, true);


--
-- Name: direccion_id_direccion_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.direccion_id_direccion_seq', 12, true);


--
-- Name: modulo_id_modulo_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.modulo_id_modulo_seq', 7, true);


--
-- Name: nomenclatura_id_nomenclatura_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.nomenclatura_id_nomenclatura_seq', 10, true);


--
-- Name: rol_id_rol_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.rol_id_rol_seq', 4, true);


--
-- Name: seguimiento_terreno_id_seguimiento_terreno_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.seguimiento_terreno_id_seguimiento_terreno_seq', 12, true);


--
-- Name: seguimiento_zoocriadero_id_seguimiento_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.seguimiento_zoocriadero_id_seguimiento_seq', 216, true);


--
-- Name: sitio_id_sitio_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.sitio_id_sitio_seq', 12, true);


--
-- Name: tanque_id_tanque_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.tanque_id_tanque_seq', 15, true);


--
-- Name: territorio_priorizado_id_territorio_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.territorio_priorizado_id_territorio_seq', 1, false);


--
-- Name: tipo_deposito_id_tipo_deposito_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.tipo_deposito_id_tipo_deposito_seq', 10, true);


--
-- Name: tipo_documento_id_tipodocumento_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.tipo_documento_id_tipodocumento_seq', 5, true);


--
-- Name: tipo_tanque_id_tipo_tanque_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.tipo_tanque_id_tipo_tanque_seq', 7, true);


--
-- Name: usuario_id_usuario_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.usuario_id_usuario_seq', 6, true);


--
-- Name: zoocriadero_id_zoocriadero_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.zoocriadero_id_zoocriadero_seq', 5, true);


--
-- Name: accion_permiso accion_permiso_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.accion_permiso
    ADD CONSTRAINT accion_permiso_pkey PRIMARY KEY (id_accion_permiso);


--
-- Name: actividad actividad_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.actividad
    ADD CONSTRAINT actividad_pkey PRIMARY KEY (id_actividad);


--
-- Name: actividad_terreno actividad_terreno_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.actividad_terreno
    ADD CONSTRAINT actividad_terreno_pkey PRIMARY KEY (id_actividad_terreno);


--
-- Name: actividad_zoocriadero actividad_zoocriadero_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.actividad_zoocriadero
    ADD CONSTRAINT actividad_zoocriadero_pkey PRIMARY KEY (id_actividad_zoocriadero);


--
-- Name: auditoria_seguimiento_zoocriadero auditoria_seguimiento_zoocriadero_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.auditoria_seguimiento_zoocriadero
    ADD CONSTRAINT auditoria_seguimiento_zoocriadero_pkey PRIMARY KEY (id_auditoria);


--
-- Name: auditoria_sistema auditoria_sistema_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.auditoria_sistema
    ADD CONSTRAINT auditoria_sistema_pkey PRIMARY KEY (id_auditoria);


--
-- Name: barrio barrio_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.barrio
    ADD CONSTRAINT barrio_pkey PRIMARY KEY (id_barrio);


--
-- Name: ciudad ciudad_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ciudad
    ADD CONSTRAINT ciudad_pkey PRIMARY KEY (id_ciudad);


--
-- Name: comuna comuna_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.comuna
    ADD CONSTRAINT comuna_pkey PRIMARY KEY (id_comuna);


--
-- Name: copia_seguridad_historial copia_seguridad_historial_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.copia_seguridad_historial
    ADD CONSTRAINT copia_seguridad_historial_pkey PRIMARY KEY (id_historial);


--
-- Name: departamento departamento_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.departamento
    ADD CONSTRAINT departamento_pkey PRIMARY KEY (id_departamento);


--
-- Name: direccion direccion_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.direccion
    ADD CONSTRAINT direccion_pkey PRIMARY KEY (id_direccion);


--
-- Name: modulo modulo_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.modulo
    ADD CONSTRAINT modulo_pkey PRIMARY KEY (id_modulo);


--
-- Name: nomenclatura nomenclatura_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.nomenclatura
    ADD CONSTRAINT nomenclatura_pkey PRIMARY KEY (id_nomenclatura);


--
-- Name: rol_permiso rol_permiso_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rol_permiso
    ADD CONSTRAINT rol_permiso_pkey PRIMARY KEY (id_rol, id_modulo, id_accion_permiso);


--
-- Name: rol rol_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rol
    ADD CONSTRAINT rol_pkey PRIMARY KEY (id_rol);


--
-- Name: seguimiento_terreno seguimiento_terreno_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.seguimiento_terreno
    ADD CONSTRAINT seguimiento_terreno_pkey PRIMARY KEY (id_seguimiento_terreno);


--
-- Name: seguimiento_zoocriadero seguimiento_zoocriadero_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.seguimiento_zoocriadero
    ADD CONSTRAINT seguimiento_zoocriadero_pkey PRIMARY KEY (id_seguimiento);


--
-- Name: sitio sitio_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sitio
    ADD CONSTRAINT sitio_pkey PRIMARY KEY (id_sitio);


--
-- Name: tanque tanque_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tanque
    ADD CONSTRAINT tanque_pkey PRIMARY KEY (id_tanque);


--
-- Name: territorio_priorizado territorio_priorizado_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.territorio_priorizado
    ADD CONSTRAINT territorio_priorizado_pkey PRIMARY KEY (id_territorio);


--
-- Name: tipo_deposito tipo_deposito_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tipo_deposito
    ADD CONSTRAINT tipo_deposito_pkey PRIMARY KEY (id_tipo_deposito);


--
-- Name: tipo_documento tipo_documento_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tipo_documento
    ADD CONSTRAINT tipo_documento_pkey PRIMARY KEY (id_tipodocumento);


--
-- Name: tipo_tanque tipo_tanque_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tipo_tanque
    ADD CONSTRAINT tipo_tanque_pkey PRIMARY KEY (id_tipo_tanque);


--
-- Name: usuario usuario_correo_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.usuario
    ADD CONSTRAINT usuario_correo_key UNIQUE (correo);


--
-- Name: usuario usuario_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.usuario
    ADD CONSTRAINT usuario_pkey PRIMARY KEY (id_usuario);


--
-- Name: zoocriadero zoocriadero_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.zoocriadero
    ADD CONSTRAINT zoocriadero_pkey PRIMARY KEY (id_zoocriadero);


--
-- Name: idx_barrio_comuna; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_barrio_comuna ON public.barrio USING btree (id_comuna);


--
-- Name: idx_copia_seguridad_fecha; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_copia_seguridad_fecha ON public.copia_seguridad_historial USING btree (fecha_hora DESC);


--
-- Name: idx_seg_ter_fecha; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_seg_ter_fecha ON public.seguimiento_terreno USING btree (fecha);


--
-- Name: idx_seg_zoo_fecha; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_seg_zoo_fecha ON public.seguimiento_zoocriadero USING btree (fecha);


--
-- Name: idx_seg_zoo_tanque; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_seg_zoo_tanque ON public.seguimiento_zoocriadero USING btree (id_tanque);


--
-- Name: idx_tanque_zoo; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_tanque_zoo ON public.tanque USING btree (id_zoocriadero);


--
-- Name: actividad trg_antes_actividad; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_antes_actividad BEFORE INSERT OR DELETE OR UPDATE ON public.actividad FOR EACH ROW EXECUTE FUNCTION public.fn_auditoria_general();


--
-- Name: actividad_terreno trg_antes_actividad_terreno; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_antes_actividad_terreno BEFORE INSERT OR DELETE OR UPDATE ON public.actividad_terreno FOR EACH ROW EXECUTE FUNCTION public.fn_auditoria_general();


--
-- Name: seguimiento_zoocriadero trg_antes_seguimiento_zoocriadero; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_antes_seguimiento_zoocriadero BEFORE INSERT OR DELETE OR UPDATE ON public.seguimiento_zoocriadero FOR EACH ROW EXECUTE FUNCTION public.fn_auditoria_seguimiento_zoocriadero();


--
-- Name: sitio trg_antes_sitio; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_antes_sitio BEFORE INSERT OR DELETE OR UPDATE ON public.sitio FOR EACH ROW EXECUTE FUNCTION public.fn_auditoria_general();


--
-- Name: tipo_deposito trg_antes_tipo_deposito; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_antes_tipo_deposito BEFORE INSERT OR DELETE OR UPDATE ON public.tipo_deposito FOR EACH ROW EXECUTE FUNCTION public.fn_auditoria_general();


--
-- Name: actividad trg_despues_actividad; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_despues_actividad AFTER INSERT OR DELETE OR UPDATE ON public.actividad FOR EACH ROW EXECUTE FUNCTION public.fn_auditoria_general();


--
-- Name: actividad_terreno trg_despues_actividad_terreno; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_despues_actividad_terreno AFTER INSERT OR DELETE OR UPDATE ON public.actividad_terreno FOR EACH ROW EXECUTE FUNCTION public.fn_auditoria_general();


--
-- Name: seguimiento_zoocriadero trg_despues_seguimiento_zoocriadero; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_despues_seguimiento_zoocriadero AFTER INSERT OR DELETE OR UPDATE ON public.seguimiento_zoocriadero FOR EACH ROW EXECUTE FUNCTION public.fn_auditoria_seguimiento_zoocriadero();


--
-- Name: sitio trg_despues_sitio; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_despues_sitio AFTER INSERT OR DELETE OR UPDATE ON public.sitio FOR EACH ROW EXECUTE FUNCTION public.fn_auditoria_general();


--
-- Name: tipo_deposito trg_despues_tipo_deposito; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_despues_tipo_deposito AFTER INSERT OR DELETE OR UPDATE ON public.tipo_deposito FOR EACH ROW EXECUTE FUNCTION public.fn_auditoria_general();


--
-- Name: actividad_terreno fk_act_ter_act; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.actividad_terreno
    ADD CONSTRAINT fk_act_ter_act FOREIGN KEY (id_actividad) REFERENCES public.actividad(id_actividad);


--
-- Name: actividad_terreno fk_act_ter_seg; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.actividad_terreno
    ADD CONSTRAINT fk_act_ter_seg FOREIGN KEY (id_seguimiento_terreno) REFERENCES public.seguimiento_terreno(id_seguimiento_terreno);


--
-- Name: actividad_zoocriadero fk_act_zoo_act; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.actividad_zoocriadero
    ADD CONSTRAINT fk_act_zoo_act FOREIGN KEY (id_actividad) REFERENCES public.actividad(id_actividad);


--
-- Name: actividad_zoocriadero fk_act_zoo_seg; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.actividad_zoocriadero
    ADD CONSTRAINT fk_act_zoo_seg FOREIGN KEY (id_seguimiento) REFERENCES public.seguimiento_zoocriadero(id_seguimiento);


--
-- Name: auditoria_seguimiento_zoocriadero fk_auditoria_seg_usuario; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.auditoria_seguimiento_zoocriadero
    ADD CONSTRAINT fk_auditoria_seg_usuario FOREIGN KEY (id_usuario) REFERENCES public.usuario(id_usuario);


--
-- Name: barrio fk_barrio_comuna; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.barrio
    ADD CONSTRAINT fk_barrio_comuna FOREIGN KEY (id_comuna) REFERENCES public.comuna(id_comuna);


--
-- Name: ciudad fk_ciudad_comuna; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ciudad
    ADD CONSTRAINT fk_ciudad_comuna FOREIGN KEY (id_comuna) REFERENCES public.comuna(id_comuna);


--
-- Name: comuna fk_comuna_barrio; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.comuna
    ADD CONSTRAINT fk_comuna_barrio FOREIGN KEY (id_barrio) REFERENCES public.barrio(id_barrio);


--
-- Name: departamento fk_dep_ciudad; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.departamento
    ADD CONSTRAINT fk_dep_ciudad FOREIGN KEY (id_ciudad) REFERENCES public.ciudad(id_ciudad);


--
-- Name: direccion fk_dir_barrio; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.direccion
    ADD CONSTRAINT fk_dir_barrio FOREIGN KEY (id_barrio) REFERENCES public.barrio(id_barrio);


--
-- Name: direccion fk_dir_ciudad; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.direccion
    ADD CONSTRAINT fk_dir_ciudad FOREIGN KEY (id_ciudad) REFERENCES public.ciudad(id_ciudad);


--
-- Name: direccion fk_dir_comuna; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.direccion
    ADD CONSTRAINT fk_dir_comuna FOREIGN KEY (id_comuna) REFERENCES public.comuna(id_comuna);


--
-- Name: direccion fk_dir_dep; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.direccion
    ADD CONSTRAINT fk_dir_dep FOREIGN KEY (id_departamento) REFERENCES public.departamento(id_departamento);


--
-- Name: direccion fk_dir_nom; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.direccion
    ADD CONSTRAINT fk_dir_nom FOREIGN KEY (id_nomenclatura) REFERENCES public.nomenclatura(id_nomenclatura);


--
-- Name: rol_permiso fk_rp_accion; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rol_permiso
    ADD CONSTRAINT fk_rp_accion FOREIGN KEY (id_accion_permiso) REFERENCES public.accion_permiso(id_accion_permiso);


--
-- Name: rol_permiso fk_rp_modulo; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rol_permiso
    ADD CONSTRAINT fk_rp_modulo FOREIGN KEY (id_modulo) REFERENCES public.modulo(id_modulo);


--
-- Name: rol_permiso fk_rp_rol; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rol_permiso
    ADD CONSTRAINT fk_rp_rol FOREIGN KEY (id_rol) REFERENCES public.rol(id_rol);


--
-- Name: seguimiento_terreno fk_seg_ter_sitio; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.seguimiento_terreno
    ADD CONSTRAINT fk_seg_ter_sitio FOREIGN KEY (id_sitio) REFERENCES public.sitio(id_sitio);


--
-- Name: seguimiento_terreno fk_seg_ter_usuario; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.seguimiento_terreno
    ADD CONSTRAINT fk_seg_ter_usuario FOREIGN KEY (id_usuario) REFERENCES public.usuario(id_usuario);


--
-- Name: seguimiento_zoocriadero fk_seg_zoo_tanque; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.seguimiento_zoocriadero
    ADD CONSTRAINT fk_seg_zoo_tanque FOREIGN KEY (id_tanque) REFERENCES public.tanque(id_tanque);


--
-- Name: seguimiento_zoocriadero fk_seg_zoo_usuario; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.seguimiento_zoocriadero
    ADD CONSTRAINT fk_seg_zoo_usuario FOREIGN KEY (id_usuario) REFERENCES public.usuario(id_usuario);


--
-- Name: seguimiento_zoocriadero fk_seg_zoo_zoocriadero; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.seguimiento_zoocriadero
    ADD CONSTRAINT fk_seg_zoo_zoocriadero FOREIGN KEY (id_zoocriadero) REFERENCES public.zoocriadero(id_zoocriadero);


--
-- Name: sitio fk_sitio_deposito; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sitio
    ADD CONSTRAINT fk_sitio_deposito FOREIGN KEY (id_tipo_deposito) REFERENCES public.tipo_deposito(id_tipo_deposito);


--
-- Name: sitio fk_sitio_direccion; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sitio
    ADD CONSTRAINT fk_sitio_direccion FOREIGN KEY (id_direccion) REFERENCES public.direccion(id_direccion);


--
-- Name: tanque fk_tanque_tipo; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tanque
    ADD CONSTRAINT fk_tanque_tipo FOREIGN KEY (id_tipo_tanque) REFERENCES public.tipo_tanque(id_tipo_tanque);


--
-- Name: tanque fk_tanque_zoocriadero; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tanque
    ADD CONSTRAINT fk_tanque_zoocriadero FOREIGN KEY (id_zoocriadero) REFERENCES public.zoocriadero(id_zoocriadero);


--
-- Name: territorio_priorizado fk_ter_usuario; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.territorio_priorizado
    ADD CONSTRAINT fk_ter_usuario FOREIGN KEY (id_funcionario_ecosalud) REFERENCES public.usuario(id_usuario);


--
-- Name: usuario fk_usuario_rol; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.usuario
    ADD CONSTRAINT fk_usuario_rol FOREIGN KEY (id_rol) REFERENCES public.rol(id_rol);


--
-- Name: usuario fk_usuario_tipodocumento; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.usuario
    ADD CONSTRAINT fk_usuario_tipodocumento FOREIGN KEY (id_tipodocumento) REFERENCES public.tipo_documento(id_tipodocumento);


--
-- Name: zoocriadero fk_zoocriadero_usuario; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.zoocriadero
    ADD CONSTRAINT fk_zoocriadero_usuario FOREIGN KEY (id_persona_cargo) REFERENCES public.usuario(id_usuario);


--
-- PostgreSQL database dump complete
--

\unrestrict urWfRkaES5cyP0h1IgQxDNRL1kZsDOzDfgnOgFezM96AoujtEcJGCTfiT48BekD

