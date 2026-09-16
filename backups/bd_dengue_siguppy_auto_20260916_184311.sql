--
-- PostgreSQL database dump
--

\restrict CY2RSzhMtfS9gIyL8WpYzatg6i65M8imbkdBkSz2pVyM1Q4rfdtPzgXdsb270gl

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
  v_operacion      VARCHAR(20);
  v_momento        VARCHAR(20);
  v_id_usuario     BIGINT;
  v_usuario_texto  VARCHAR(160);
  v_accion_texto   VARCHAR(20);
BEGIN
  -- Traduce la operacion tecnica a una palabra en espanol
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

  -- TG_WHEN identifica si el disparador se ejecuta ANTES o DESPUES
  v_momento := CASE WHEN TG_WHEN = 'BEFORE' THEN 'ANTES' ELSE 'DESPUES' END;

  -- Usuario que inicio sesion (dato quemado, tomado de la conexion actual)
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
  v_operacion     VARCHAR(20);
  v_momento       VARCHAR(20);
  v_id_seg        BIGINT;
  v_id_usuario    BIGINT;
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

  IF TG_OP = 'DELETE' THEN
    v_id_seg     := OLD.id_seguimiento;
    v_id_usuario := OLD.id_usuario;   -- usuario que habia registrado el seguimiento eliminado
  ELSE
    v_id_seg     := NEW.id_seguimiento;
    v_id_usuario := NEW.id_usuario;   -- usuario que inicio sesion y registra/edita el seguimiento
  END IF;

  INSERT INTO auditoria_seguimiento_zoocriadero
    (id_seguimiento, id_usuario, operacion, momento, fecha_evento, hora_evento, fecha_hora_evento, detalle)
  VALUES
    (v_id_seg, v_id_usuario, v_operacion, v_momento, CURRENT_DATE, CURRENT_TIME, CURRENT_TIMESTAMP,
     'El usuario ' || v_accion_texto || ' un seguimiento de zoocriadero (' || v_momento ||
       ' de guardar el cambio)');

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
    nombre character varying(100) NOT NULL,
    id_comuna bigint
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
    id_historial integer NOT NULL,
    fecha_hora timestamp without time zone DEFAULT now() NOT NULL,
    tipo_operacion character varying(20) NOT NULL,
    nombre_archivo character varying(255),
    id_usuario integer,
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

CREATE SEQUENCE public.copia_seguridad_historial_id_historial_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.copia_seguridad_historial_id_historial_seq OWNER TO postgres;

--
-- Name: copia_seguridad_historial_id_historial_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.copia_seguridad_historial_id_historial_seq OWNED BY public.copia_seguridad_historial.id_historial;


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
    observaciones character varying(300) DEFAULT NULL::character varying,
    estado smallint DEFAULT 1 NOT NULL
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
    id_direccion bigint NOT NULL,
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
    creado_en timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
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
-- Name: copia_seguridad_historial id_historial; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.copia_seguridad_historial ALTER COLUMN id_historial SET DEFAULT nextval('public.copia_seguridad_historial_id_historial_seq'::regclass);


--
-- Data for Name: accion_permiso; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.accion_permiso (id_accion_permiso, nombre) FROM stdin;
1	Registrar
2	Consultar
3	Editar
4	Eliminar
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
\.


--
-- Data for Name: actividad_zoocriadero; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.actividad_zoocriadero (id_actividad_zoocriadero, id_seguimiento, id_actividad) FROM stdin;
1	1	1
2	2	10
3	3	7
\.


--
-- Data for Name: auditoria_seguimiento_zoocriadero; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.auditoria_seguimiento_zoocriadero (id_auditoria, id_seguimiento, id_usuario, operacion, momento, fecha_evento, hora_evento, fecha_hora_evento, detalle) FROM stdin;
\.


--
-- Data for Name: auditoria_sistema; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.auditoria_sistema (id_auditoria, tabla_afectada, operacion, momento, id_usuario, usuario_responsable, fecha_evento, hora_evento, fecha_hora_evento, datos_anteriores, datos_nuevos, detalle) FROM stdin;
\.


--
-- Data for Name: barrio; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.barrio (id_barrio, nombre, id_comuna) FROM stdin;
1	Rodrigo Lara Bonilla	11
2	Los Guayacanes	4
3	Puerto Nuevo	3
4	Ciudad Capri	17
5	El Paraíso	22
6	El Retiro	14
7	Julio Rincón	22
8	Pizamos I	12
9	Polvorines	2
10	Chiminangos Segunda Etapa	4
11	Comuneros I	14
12	Compartir	12
13	Departamental	20
14	Calima	5
15	Potrero Grande	12
16	Manuela Beltrán	16
17	El Cedro	18
18	El Morichal	14
19	El Sena	4
20	Lili	17
21	Normandía	1
22	Flora Industrial	5
23	Los Cámbulos	18
24	Juanambú	1
25	Jockey Club	15
26	Charco Azul	11
27	Pasoancho	20
28	Alfonso Bonilla Aragón	16
29	Lleras Camargo	21
30	San Nicolás	10
31	Benjamín Herrera	7
32	Calimio Norte	19
33	Granada	1
34	Aranjuez	13
35	Mariano Ramos	6
36	José Manuel Marroquín I	16
37	Ignacio Rengifo	5
38	Chipichape	1
39	El Cortijo	21
40	Calipso	11
41	San Pedro	10
42	Alfonso López I	3
43	Fátima	5
44	Mario Correa Rengifo	2
45	San Benito	8
46	La Rivera II	19
47	Arboledas	1
48	El Bosque	1
49	Brisas de Mayo	21
50	Los Naranjos	16
51	Patio Bonito	9
52	San Vicente	1
53	Manuel María Buenaventura	13
54	El Porvenir	5
55	Alférez Real	15
56	La Hacienda	17
57	Cristóbal Colón	20
58	Nueva Tequendama	18
59	Nápoles	2
60	La Campiña	1
61	Altos de Menga	1
62	El Poblado I	11
63	Vipasa	1
64	Guayaquil	13
65	Villablanca	11
66	Primitivo Crespo	7
67	El Caney	17
68	Belalcázar	13
69	Las Orquídeas	16
70	Promociones Populares	16
71	Paso del Comercio	19
72	Chapinero	7
73	Los Libertadores	10
74	Versalles	1
75	Evaristo García	5
76	Olaya Herrera	5
77	Puertas del Sol	16
78	Las Quintas de Don Simón	17
79	Pampalinda	18
80	Bretaña	13
81	Simón Bolívar	7
82	Ciudadela Floralia	19
83	Alto Nápoles	2
84	Siloé	21
85	Petecuy II	19
86	Colinas del Sur	2
87	Siete de Agosto	3
88	Tierra Blanca	21
89	Los Robles	11
90	José Holguín Garcés	8
91	Municipal	7
92	Santa Fe	7
93	San Antonio	10
94	Navarro	10
95	Mayapán	17
96	Laureano Gómez	14
97	Bellavista	8
98	El Limonar	17
99	El Vergel	11
100	Industrial	7
101	Metropolitano del Norte	4
102	Sector Altos de Aguacatal	9
103	Las Dalias	12
104	Horizontes	2
105	Veinte de Julio	8
106	El Refugio	18
107	Jorge Isaacs	5
108	Bueno Madrid	5
109	Santa Isabel	20
110	Urbanización Río Lili	15
111	Centenario	1
112	Pizamos III	12
113	Jorge Zawadsky	20
114	Barrio Obrero	13
115	Base Aérea	3
116	Las Delicias	5
117	San Juan Bosco	10
118	Saavedra Galindo	7
119	Alirio Mora Beltrán	16
120	Sucre	13
121	El Poblado II	11
122	Puente del Comercio	19
123	Desepaz	12
124	Santa Rosa	10
125	La Esmeralda	5
126	El Gran Limonar	17
127	Comuneros II	14
128	Eduardo Santos	22
129	Ciudad Los Álamos	19
130	San Pascual	10
131	Bello Horizonte	22
132	Villa Colombia	7
133	Prados del Sur	2
134	Atanasio Girardot	7
135	Villa del Lago	12
136	El Guabal	8
137	Alfonso Barberena	22
138	Ciudad 2000	17
139	La Libertad	8
140	Torres de Comfandi	4
141	Villanueva	8
142	La Floresta	7
143	Ciudadela Comfandi	4
144	Unión de Vivienda Popular	6
145	Brisas del Bosque	14
146	La Ferroviaria	8
147	Guillermo Valencia	5
148	El Dorado	20
149	El Hoyo	10
150	Bosques del Limonar	17
151	Mojica	14
152	Junín	13
153	El Vallado	14
154	Sindical	19
155	La Flora	1
156	Santa Rita	1
157	Las Vegas	17
158	El Calvario	10
159	Brisas del Limonar	6
160	Meléndez	2
161	Fepicol	3
162	La Base	7
163	Salomia	5
164	Bajo Aguacatal	9
165	La Sultana Ladera	21
166	Lleras Restrepo	11
167	Prados del Norte	1
168	Camino Real	18
169	Santa Mónica	1
170	Nueva Floresta	22
171	Villa del Sur	22
172	Doce de Octubre	22
173	Vista Hermosa	9
174	Las Granjas	8
175	Aguacatal	9
176	El Troncal	7
177	El Ingenio	18
178	Ricardo Balcázar	11
179	Ulpiano Lloreda	11
180	Colseguros	20
181	Las Américas	7
182	Valle Grande	12
183	Francisco Eladio Ramírez	2
184	La Alborada	6
185	Pance	15
186	Ciudad Jardín	15
187	Los Alcázares	19
188	San Judas Tadeo II	22
189	Los Andes	4
190	Alfonso López II	3
191	San Fernando Nuevo	18
192	Villa del Prado	4
193	El Lido	18
194	San Cristóbal	20
195	Tequendama	18
196	El Nacional	10
197	Manzanares	5
198	Bolivariano	5
199	Santa Mónica Popular	7
200	San Cayetano	10
201	Cuarto de Legua	18
202	Ciudad Universitaria	17
203	La Selva	20
204	Los Pinos	3
205	Lourdes	2
206	Cañasgordas	15
207	Chiminangos Primera Etapa	4
208	Alfonso López III	3
209	Puerto Mallarino	3
210	Pueblo Joven	21
211	La Isla	5
212	Parque de la Caña	3
213	Alameda	13
214	El Rodeo	22
215	Primero de Mayo	17
216	El Piloto	10
217	Calimio Desepaz	12
218	El Trébol	7
219	Terrón Colorado	9
220	Antonio Nariño	6
221	José Manuel Marroquín II	16
222	San Luis	19
223	Santander	5
224	Belén	21
225	Omar Torrijos	11
226	San Fernando Viejo	18
227	Champagnat	18
228	Pizamos II	12
229	Los Líderes	12
230	San Pedro Claver	8
231	Los Chorros	2
232	Ciudad Campestre	15
233	Olímpico	20
234	El Templete	18
235	Cementerio Carabineros	21
236	Yira Castro	11
237	Paseo de los Almendros	4
238	Ciudadela del Río	12
239	República de Israel	6
240	Petecuy I	19
241	Petecuy III	19
242	Ciudad Córdoba	14
243	La Merced	10
244	Primavera	8
245	Menga	1
246	Jorge Eliécer Gaitán	19
247	Asturias	22
248	Los Guaduales	19
249	San Judas Tadeo I	22
250	Alto Jordán	2
251	Bosque Municipal	18
252	Fenalco Kennedy	8
253	Villa del Sol	4
254	Parcelaciones de Pance	15
255	Maracaibo	8
256	La Rivera I	4
257	Panamericano	20
258	Boyacá	8
259	Berlín	5
260	Caldas	2
261	Buenos Aires	2
262	Remansos de Comfandi	12
263	Miraflores	18
264	La Esperanza	8
265	La Sultana	5
266	Las Acacias	20
267	San Marino	3
\.


--
-- Data for Name: ciudad; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.ciudad (id_ciudad, id_comuna, nombre) FROM stdin;
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
1	2026-09-14 20:02:44.760604	descarga	bd_dengue_siguppy_20260915_010244.sql	\N	Sistema	exito	\N
2	2026-09-16 13:37:28.1689	descarga	bd_dengue_siguppy_20260916_183727.sql	\N	Sistema	exito	\N
\.


--
-- Data for Name: departamento; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.departamento (id_departamento, id_ciudad, nombre) FROM stdin;
\.


--
-- Data for Name: direccion; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.direccion (id_direccion, id_departamento, id_comuna, id_ciudad, id_barrio, direccion, id_nomenclatura) FROM stdin;
\.


--
-- Data for Name: modulo; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.modulo (id_modulo, nombre, descripcion) FROM stdin;
1	Reportes	Reportes y gráficos del sistema
2	Zoocriaderos	Gestión de zoocriaderos y tanques
3	Terreno	Depósitos y actividades de terreno
4	Usuarios	Gestión de usuarios del sistema
5	Roles	Creación de roles y asignación de permisos
6	Copia de seguridad	Respaldo de la base de datos
7	Configuraciones	Parámetros generales del sistema
\.


--
-- Data for Name: nomenclatura; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.nomenclatura (id_nomenclatura, nomenclatura) FROM stdin;
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
2	5	2
2	3	2
2	7	2
2	4	1
2	1	1
2	4	3
2	1	4
2	1	2
2	2	3
2	3	3
2	7	3
2	5	3
2	3	4
2	7	4
2	5	4
2	2	1
2	2	4
2	5	1
2	4	2
2	7	1
2	3	1
1	2	2
1	3	1
1	3	3
1	7	2
1	3	2
1	2	3
1	1	2
1	2	1
3	1	2
3	3	2
3	3	1
3	2	2
\.


--
-- Data for Name: seguimiento_terreno; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.seguimiento_terreno (id_seguimiento_terreno, id_sitio, id_usuario, fecha, estado) FROM stdin;
\.


--
-- Data for Name: seguimiento_zoocriadero; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.seguimiento_zoocriadero (id_seguimiento, id_zoocriadero, id_tanque, id_usuario, fecha, ph, temperatura, numero_sembrados, numero_nacidos, numero_muertos, observaciones, estado) FROM stdin;
1	3	1	1	2026-09-14	\N	\N	0	10	3	Todo esta bien en el tanque de plástico	1
2	1	13	1	2026-09-14	7.20	\N	0	10	50	\N	1
3	5	9	1	2026-09-14	\N	\N	0	0	0	Todos están vivos y comiendo correctamente sin afectaciones	1
\.


--
-- Data for Name: sitio; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.sitio (id_sitio, id_tipo_deposito, id_direccion, estado, creado_en) FROM stdin;
\.


--
-- Data for Name: tanque; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.tanque (id_tanque, id_zoocriadero, id_tipo_tanque, numero_tanque, estado) FROM stdin;
1	3	5	1	1
2	3	2	2	1
3	3	4	3	1
4	3	6	4	1
5	2	5	1	1
6	2	1	2	1
7	2	2	3	1
8	5	5	1	1
9	5	3	2	1
10	4	5	1	1
11	4	6	2	1
12	4	7	3	1
13	1	5	1	1
14	1	4	2	1
15	1	1	3	1
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
6	Permiso por Protección Temporal
7	Cédula de extranjería
8	Cédula de ciudadanía
9	Pasaporte
10	Tarjeta de identidad
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

COPY public.usuario (id_usuario, id_tipodocumento, id_rol, nombre, apellido, correo, contrasena, estado, creado_en) FROM stdin;
1	8	3	María José	Perlaza	maria.perlaza@cali.gov.co	$2y$10$krffGFo.lHLb7KgLEAGf8ubBH5rWyCkSR/fwkaV/okl0xizVuG04C	1	2026-09-14 06:26:02.453062
2	8	1	Luisa Fernanda	Ríos	luisa.rios@cali.gov.co	$2y$10$krffGFo.lHLb7KgLEAGf8ubBH5rWyCkSR/fwkaV/okl0xizVuG04C	1	2026-09-14 06:26:02.453062
3	8	3	Diana Marcela	Ortiz	diana.ortiz@cali.gov.co	$2y$10$krffGFo.lHLb7KgLEAGf8ubBH5rWyCkSR/fwkaV/okl0xizVuG04C	1	2026-09-14 06:26:02.453062
4	8	3	Jhon Édison	Valencia	jhon.valencia@cali.gov.co	$2y$10$krffGFo.lHLb7KgLEAGf8ubBH5rWyCkSR/fwkaV/okl0xizVuG04C	1	2026-09-14 06:26:02.453062
5	8	3	Carlos Andrés	Mosquera	carlos.mosquera@cali.gov.co	$2y$10$krffGFo.lHLb7KgLEAGf8ubBH5rWyCkSR/fwkaV/okl0xizVuG04C	1	2026-09-14 06:26:02.453062
6	8	2	Andrés Felipe	Caicedo	andres.caicedo@cali.gov.co	$2y$10$krffGFo.lHLb7KgLEAGf8ubBH5rWyCkSR/fwkaV/okl0xizVuG04C	1	2026-09-14 06:26:02.453062
\.


--
-- Data for Name: zoocriadero; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.zoocriadero (id_zoocriadero, nombre, direccion, comuna, barrio, id_persona_cargo, latitud, longitud, estado, creado_en) FROM stdin;
1	Zoocriadero Aguablanca	Cra 31 # 22-71	Comuna 15	El ingenio	\N	0.00000000	0.00000000	1	2026-09-14 05:41:11.924639
2	Zoocriadero Norte	Cra 8 # 45-12	Comuna 2	Granada	5	3.46210000	-76.53120000	1	2026-09-14 06:26:02.453062
3	Zoocriadero Central	Calle 13 # 24-05	Comuna 11	El Guabal	2	3.42158000	-76.52050000	1	2026-09-14 06:26:02.453062
5	Zoocriadero Oriente	Calle 70 # 28D-19	Comuna 15	El Retiro	3	3.43980000	-76.49010000	1	2026-09-14 06:26:02.453062
4	Zoocriadero Ladera	Cra 26 # 9-40	Comuna 18	Meléndez	4	3.38720000	-76.54990000	0	2026-09-14 06:26:02.453062
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

SELECT pg_catalog.setval('public.actividad_terreno_id_actividad_terreno_seq', 1, false);


--
-- Name: actividad_zoocriadero_id_actividad_zoocriadero_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.actividad_zoocriadero_id_actividad_zoocriadero_seq', 3, true);


--
-- Name: auditoria_seguimiento_zoocriadero_id_auditoria_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.auditoria_seguimiento_zoocriadero_id_auditoria_seq', 1, false);


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

SELECT pg_catalog.setval('public.ciudad_id_ciudad_seq', 1, false);


--
-- Name: comuna_id_comuna_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.comuna_id_comuna_seq', 22, true);


--
-- Name: copia_seguridad_historial_id_historial_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.copia_seguridad_historial_id_historial_seq', 2, true);


--
-- Name: departamento_id_departamento_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.departamento_id_departamento_seq', 1, false);


--
-- Name: direccion_id_direccion_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.direccion_id_direccion_seq', 1, false);


--
-- Name: modulo_id_modulo_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.modulo_id_modulo_seq', 7, true);


--
-- Name: nomenclatura_id_nomenclatura_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.nomenclatura_id_nomenclatura_seq', 1, false);


--
-- Name: rol_id_rol_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.rol_id_rol_seq', 4, true);


--
-- Name: seguimiento_terreno_id_seguimiento_terreno_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.seguimiento_terreno_id_seguimiento_terreno_seq', 1, false);


--
-- Name: seguimiento_zoocriadero_id_seguimiento_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.seguimiento_zoocriadero_id_seguimiento_seq', 3, true);


--
-- Name: sitio_id_sitio_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.sitio_id_sitio_seq', 1, false);


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

SELECT pg_catalog.setval('public.tipo_documento_id_tipodocumento_seq', 10, true);


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
-- Name: idx_copia_seguridad_fecha; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_copia_seguridad_fecha ON public.copia_seguridad_historial USING btree (fecha_hora DESC);


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

\unrestrict CY2RSzhMtfS9gIyL8WpYzatg6i65M8imbkdBkSz2pVyM1Q4rfdtPzgXdsb270gl

