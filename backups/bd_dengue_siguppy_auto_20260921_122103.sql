--
-- PostgreSQL database dump
--

\restrict 4hjLLSkiBZsu44buZFxK15dhbc4fbpiJf540VusKxeGrRIRcRBoNvNJUlyfG0XC

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
-- Name: public; Type: SCHEMA; Schema: -; Owner: postgres
--

-- *not* creating schema, since initdb creates it


ALTER SCHEMA public OWNER TO postgres;

--
-- Name: SCHEMA public; Type: COMMENT; Schema: -; Owner: postgres
--

COMMENT ON SCHEMA public IS '';


--
-- Name: fn_auditoria_generica(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.fn_auditoria_generica() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE
    v_id_usuario  bigint;
    v_id_registro bigint;
    v_pk_column   text;
    v_row         jsonb;
    v_accion      text;
    v_detalle     text;
    v_estado_antes text;
    v_estado_despues text;
BEGIN
    BEGIN
        v_id_usuario := NULLIF(current_setting('app.id_usuario', true), '')::bigint;
    EXCEPTION WHEN OTHERS THEN
        v_id_usuario := NULL;
    END;

    IF TG_NARGS > 0 THEN
        v_pk_column := TG_ARGV[0];
    END IF;

    IF TG_OP = 'DELETE' THEN
        v_row := to_jsonb(OLD);
    ELSE
        v_row := to_jsonb(NEW);
    END IF;

    IF v_pk_column IS NOT NULL THEN
        BEGIN
            v_id_registro := (v_row ->> v_pk_column)::bigint;
        EXCEPTION WHEN OTHERS THEN
            v_id_registro := NULL;
        END;
    END IF;

    IF TG_OP = 'DELETE' THEN
        INSERT INTO public.auditoria (modulo, accion, id_registro, id_usuario, datos_anteriores, detalle)
        VALUES (TG_TABLE_NAME, 'DELETE', v_id_registro, v_id_usuario, to_jsonb(OLD),
                'Eliminación de registro en ' || TG_TABLE_NAME);
        RETURN OLD;

    ELSIF TG_OP = 'UPDATE' THEN
        -- Si el único cambio relevante es la columna `estado` (el patrón
        -- 1 = activo / 0 = inhabilitado que usan casi todas las tablas),
        -- se registra como HABILITAR/INHABILITAR en vez de un UPDATE
        -- genérico, para poder filtrar "quién inhabilitó X" directamente.
        v_estado_antes   := to_jsonb(OLD) ->> 'estado';
        v_estado_despues := to_jsonb(NEW) ->> 'estado';

        IF v_estado_antes IS DISTINCT FROM v_estado_despues AND v_estado_despues IS NOT NULL THEN
            IF v_estado_despues = '0' THEN
                v_accion  := 'INHABILITAR';
                v_detalle := 'Inhabilitación de registro en ' || TG_TABLE_NAME;
            ELSE
                v_accion  := 'HABILITAR';
                v_detalle := 'Habilitación de registro en ' || TG_TABLE_NAME;
            END IF;
        ELSE
            v_accion  := 'UPDATE';
            v_detalle := 'Actualización de registro en ' || TG_TABLE_NAME;
        END IF;

        INSERT INTO public.auditoria (modulo, accion, id_registro, id_usuario, datos_anteriores, datos_nuevos, detalle)
        VALUES (TG_TABLE_NAME, v_accion, v_id_registro, v_id_usuario, to_jsonb(OLD), to_jsonb(NEW), v_detalle);
        RETURN NEW;

    ELSIF TG_OP = 'INSERT' THEN
        INSERT INTO public.auditoria (modulo, accion, id_registro, id_usuario, datos_nuevos, detalle)
        VALUES (TG_TABLE_NAME, 'INSERT', v_id_registro, v_id_usuario, to_jsonb(NEW),
                'Creación de registro en ' || TG_TABLE_NAME);
        RETURN NEW;
    END IF;

    RETURN NULL;
END;
$$;


ALTER FUNCTION public.fn_auditoria_generica() OWNER TO postgres;

--
-- Name: fn_auditoria_seguimiento_zoocriadero(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.fn_auditoria_seguimiento_zoocriadero() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
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
$$;


ALTER FUNCTION public.fn_auditoria_seguimiento_zoocriadero() OWNER TO postgres;

--
-- Name: fn_registrar_login(bigint, character varying, boolean, character varying); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.fn_registrar_login(p_id_usuario bigint, p_correo character varying, p_exitoso boolean, p_detalle character varying DEFAULT NULL::character varying) RETURNS void
    LANGUAGE plpgsql
    AS $$
BEGIN
    INSERT INTO public.auditoria (modulo, accion, id_registro, id_usuario, datos_nuevos, detalle)
    VALUES (
        'login',
        CASE WHEN p_exitoso THEN 'LOGIN_EXITOSO' ELSE 'LOGIN_FALLIDO' END,
        p_id_usuario,
        p_id_usuario,
        jsonb_build_object('correo', p_correo, 'exitoso', p_exitoso),
        COALESCE(p_detalle, CASE WHEN p_exitoso THEN 'Inicio de sesión exitoso' ELSE 'Intento de inicio de sesión fallido' END)
    );
END;
$$;


ALTER FUNCTION public.fn_registrar_login(p_id_usuario bigint, p_correo character varying, p_exitoso boolean, p_detalle character varying) OWNER TO postgres;

--
-- Name: fn_validar_seguimiento_deposito(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.fn_validar_seguimiento_deposito() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE
    v_hoy               date := (CURRENT_TIMESTAMP AT TIME ZONE 'America/Bogota')::date;
    v_estado_deposito   smallint;
    v_estado_sitio      smallint;
    v_ambito            character varying;
    v_estado_actividad  smallint;
BEGIN
    IF NEW.fecha > v_hoy THEN
        RAISE EXCEPTION 'La fecha del seguimiento (%) no puede ser futura.', NEW.fecha
            USING ERRCODE = 'check_violation';
    END IF;

    IF TG_OP = 'INSERT' OR NEW.id_deposito IS DISTINCT FROM OLD.id_deposito THEN
        SELECT d.estado, s.estado
          INTO v_estado_deposito, v_estado_sitio
          FROM public.deposito d
          JOIN public.sitio s ON s.id_sitio = d.id_sitio
         WHERE d.id_deposito = NEW.id_deposito;

        IF NOT FOUND THEN
            RAISE EXCEPTION 'El depósito % no existe.', NEW.id_deposito
                USING ERRCODE = 'foreign_key_violation';
        END IF;

        IF v_estado_deposito <> 1 OR v_estado_sitio <> 1 THEN
            RAISE EXCEPTION 'El depósito % (o su sitio) está inhabilitado y no admite nuevos seguimientos.', NEW.id_deposito
                USING ERRCODE = 'check_violation';
        END IF;
    END IF;

    IF TG_OP = 'INSERT' OR NEW.id_actividad IS DISTINCT FROM OLD.id_actividad THEN
        SELECT a.ambito, a.estado
          INTO v_ambito, v_estado_actividad
          FROM public.actividad a
         WHERE a.id_actividad = NEW.id_actividad;

        IF NOT FOUND THEN
            RAISE EXCEPTION 'La actividad % no existe.', NEW.id_actividad
                USING ERRCODE = 'foreign_key_violation';
        END IF;

        IF v_ambito <> 'terreno' THEN
            RAISE EXCEPTION 'La actividad % no es de terreno (ámbito: %).', NEW.id_actividad, v_ambito
                USING ERRCODE = 'check_violation';
        END IF;

        IF v_estado_actividad <> 1 THEN
            RAISE EXCEPTION 'La actividad % está inhabilitada.', NEW.id_actividad
                USING ERRCODE = 'check_violation';
        END IF;
    END IF;

    RETURN NEW;
END;
$$;


ALTER FUNCTION public.fn_validar_seguimiento_deposito() OWNER TO postgres;

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

ALTER TABLE public.accion_permiso ALTER COLUMN id_accion_permiso ADD GENERATED BY DEFAULT AS IDENTITY (
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
    descripcion character varying(200),
    estado smallint DEFAULT 1 NOT NULL
);


ALTER TABLE public.actividad OWNER TO postgres;

--
-- Name: actividad_id_actividad_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

ALTER TABLE public.actividad ALTER COLUMN id_actividad ADD GENERATED BY DEFAULT AS IDENTITY (
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

ALTER TABLE public.actividad_terreno ALTER COLUMN id_actividad_terreno ADD GENERATED BY DEFAULT AS IDENTITY (
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

ALTER TABLE public.actividad_zoocriadero ALTER COLUMN id_actividad_zoocriadero ADD GENERATED BY DEFAULT AS IDENTITY (
    SEQUENCE NAME public.actividad_zoocriadero_id_actividad_zoocriadero_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: auditoria; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.auditoria (
    id_auditoria bigint NOT NULL,
    modulo character varying(60) NOT NULL,
    accion character varying(20) NOT NULL,
    id_registro bigint,
    id_usuario bigint,
    datos_anteriores jsonb,
    datos_nuevos jsonb,
    detalle character varying(300),
    fecha_hora timestamp without time zone DEFAULT now() NOT NULL
);


ALTER TABLE public.auditoria OWNER TO postgres;

--
-- Name: auditoria_id_auditoria_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

ALTER TABLE public.auditoria ALTER COLUMN id_auditoria ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.auditoria_id_auditoria_seq
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
    id_usuario bigint NOT NULL,
    accion character varying(20) NOT NULL,
    fecha_accion date DEFAULT CURRENT_DATE NOT NULL,
    hora_accion time without time zone DEFAULT CURRENT_TIME NOT NULL,
    fecha_hora_registro timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    detalle character varying(300)
);


ALTER TABLE public.auditoria_seguimiento_zoocriadero OWNER TO postgres;

--
-- Name: auditoria_seguimiento_zoocriadero_id_auditoria_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

ALTER TABLE public.auditoria_seguimiento_zoocriadero ALTER COLUMN id_auditoria ADD GENERATED BY DEFAULT AS IDENTITY (
    SEQUENCE NAME public.auditoria_seguimiento_zoocriadero_id_auditoria_seq
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

ALTER TABLE public.barrio ALTER COLUMN id_barrio ADD GENERATED BY DEFAULT AS IDENTITY (
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

ALTER TABLE public.ciudad ALTER COLUMN id_ciudad ADD GENERATED BY DEFAULT AS IDENTITY (
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
    nombre character varying(100) NOT NULL
);


ALTER TABLE public.comuna OWNER TO postgres;

--
-- Name: comuna_id_comuna_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

ALTER TABLE public.comuna ALTER COLUMN id_comuna ADD GENERATED BY DEFAULT AS IDENTITY (
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

ALTER TABLE public.departamento ALTER COLUMN id_departamento ADD GENERATED BY DEFAULT AS IDENTITY (
    SEQUENCE NAME public.departamento_id_departamento_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: deposito; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.deposito (
    id_deposito bigint NOT NULL,
    id_tipo_deposito bigint NOT NULL,
    id_sitio bigint NOT NULL,
    descripcion character varying(200),
    estado smallint DEFAULT 1 NOT NULL
);


ALTER TABLE public.deposito OWNER TO postgres;

--
-- Name: deposito_id_deposito_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

ALTER TABLE public.deposito ALTER COLUMN id_deposito ADD GENERATED BY DEFAULT AS IDENTITY (
    SEQUENCE NAME public.deposito_id_deposito_seq
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
    id_nomenclatura bigint NOT NULL,
    latitud numeric(10,8),
    longitud numeric(11,8)
);


ALTER TABLE public.direccion OWNER TO postgres;

--
-- Name: direccion_id_direccion_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

ALTER TABLE public.direccion ALTER COLUMN id_direccion ADD GENERATED BY DEFAULT AS IDENTITY (
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
    descripcion character varying(200)
);


ALTER TABLE public.modulo OWNER TO postgres;

--
-- Name: modulo_accion_permitida; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.modulo_accion_permitida (
    id_modulo bigint NOT NULL,
    id_accion_permiso bigint NOT NULL
);


ALTER TABLE public.modulo_accion_permitida OWNER TO postgres;

--
-- Name: modulo_id_modulo_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

ALTER TABLE public.modulo ALTER COLUMN id_modulo ADD GENERATED BY DEFAULT AS IDENTITY (
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

ALTER TABLE public.nomenclatura ALTER COLUMN id_nomenclatura ADD GENERATED BY DEFAULT AS IDENTITY (
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
    descripcion character varying(200),
    estado smallint DEFAULT 1 NOT NULL
);


ALTER TABLE public.rol OWNER TO postgres;

--
-- Name: rol_id_rol_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

ALTER TABLE public.rol ALTER COLUMN id_rol ADD GENERATED BY DEFAULT AS IDENTITY (
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
-- Name: seguimiento_deposito; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.seguimiento_deposito (
    id_seguimiento_deposito bigint NOT NULL,
    id_deposito bigint NOT NULL,
    id_usuario bigint NOT NULL,
    id_actividad bigint NOT NULL,
    fecha date NOT NULL,
    presencia_larvas smallint DEFAULT 0 NOT NULL,
    numero_peces_sembrados integer DEFAULT 0 NOT NULL,
    observaciones character varying(300),
    estado smallint DEFAULT 1 NOT NULL,
    creado_en timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    CONSTRAINT seguimiento_deposito_estado_chk CHECK ((estado = ANY (ARRAY[0, 1]))),
    CONSTRAINT seguimiento_deposito_larvas_chk CHECK ((presencia_larvas = ANY (ARRAY[0, 1]))),
    CONSTRAINT seguimiento_deposito_peces_chk CHECK ((numero_peces_sembrados >= 0))
);


ALTER TABLE public.seguimiento_deposito OWNER TO postgres;

--
-- Name: seguimiento_deposito_id_seguimiento_deposito_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

ALTER TABLE public.seguimiento_deposito ALTER COLUMN id_seguimiento_deposito ADD GENERATED BY DEFAULT AS IDENTITY (
    SEQUENCE NAME public.seguimiento_deposito_id_seguimiento_deposito_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


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

ALTER TABLE public.seguimiento_terreno ALTER COLUMN id_seguimiento_terreno ADD GENERATED BY DEFAULT AS IDENTITY (
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
    ph numeric(4,2),
    temperatura numeric(4,2),
    numero_sembrados integer DEFAULT 0,
    numero_nacidos integer DEFAULT 0,
    numero_nacidos_hembra integer DEFAULT 0 NOT NULL,
    numero_nacidos_macho integer DEFAULT 0 NOT NULL,
    numero_muertos integer DEFAULT 0,
    numero_muertos_hembra integer DEFAULT 0 NOT NULL,
    numero_muertos_macho integer DEFAULT 0 NOT NULL,
    observaciones character varying(300),
    estado smallint DEFAULT 1 NOT NULL
);


ALTER TABLE public.seguimiento_zoocriadero OWNER TO postgres;

--
-- Name: seguimiento_zoocriadero_id_seguimiento_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

ALTER TABLE public.seguimiento_zoocriadero ALTER COLUMN id_seguimiento ADD GENERATED BY DEFAULT AS IDENTITY (
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
    id_direccion bigint NOT NULL,
    estado smallint DEFAULT 1 NOT NULL,
    fecha timestamp without time zone DEFAULT CURRENT_TIMESTAMP CONSTRAINT sitio_creado_en_not_null NOT NULL,
    nombre character varying(100) NOT NULL,
    descripcion character varying(200)
);


ALTER TABLE public.sitio OWNER TO postgres;

--
-- Name: sitio_id_sitio_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

ALTER TABLE public.sitio ALTER COLUMN id_sitio ADD GENERATED BY DEFAULT AS IDENTITY (
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

ALTER TABLE public.tanque ALTER COLUMN id_tanque ADD GENERATED BY DEFAULT AS IDENTITY (
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
    sitio character varying(100),
    direccion_sitio character varying(200),
    nombre_lider character varying(120) NOT NULL,
    direccion_lider character varying(200),
    telefono_lider character varying(30),
    correo_lider character varying(120),
    clase_liderazgo character varying(80),
    id_funcionario_ecosalud bigint NOT NULL,
    latitud numeric(10,8),
    longitud numeric(11,8),
    fecha_registro date DEFAULT CURRENT_DATE NOT NULL,
    estado smallint DEFAULT 1 NOT NULL
);


ALTER TABLE public.territorio_priorizado OWNER TO postgres;

--
-- Name: territorio_priorizado_id_territorio_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

ALTER TABLE public.territorio_priorizado ALTER COLUMN id_territorio ADD GENERATED BY DEFAULT AS IDENTITY (
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
    descripcion character varying(200),
    estado smallint DEFAULT 1 NOT NULL
);


ALTER TABLE public.tipo_deposito OWNER TO postgres;

--
-- Name: tipo_deposito_id_tipo_deposito_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

ALTER TABLE public.tipo_deposito ALTER COLUMN id_tipo_deposito ADD GENERATED BY DEFAULT AS IDENTITY (
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

ALTER TABLE public.tipo_documento ALTER COLUMN id_tipodocumento ADD GENERATED BY DEFAULT AS IDENTITY (
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
    descripcion character varying(200),
    estado smallint DEFAULT 1 NOT NULL
);


ALTER TABLE public.tipo_tanque OWNER TO postgres;

--
-- Name: tipo_tanque_id_tipo_tanque_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

ALTER TABLE public.tipo_tanque ALTER COLUMN id_tipo_tanque ADD GENERATED BY DEFAULT AS IDENTITY (
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
    documento character varying(20) NOT NULL,
    intentos_fallidos integer DEFAULT 0 NOT NULL,
    bloqueo_hasta timestamp without time zone,
    token_recuperacion character varying(255) DEFAULT NULL::character varying,
    token_expira timestamp without time zone
);


ALTER TABLE public.usuario OWNER TO postgres;

--
-- Name: usuario_id_usuario_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

ALTER TABLE public.usuario ALTER COLUMN id_usuario ADD GENERATED BY DEFAULT AS IDENTITY (
    SEQUENCE NAME public.usuario_id_usuario_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: vista_auditoria; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.vista_auditoria AS
 SELECT a.id_auditoria,
    a.modulo,
    a.accion,
    a.id_registro,
    a.id_usuario,
    COALESCE((((u.nombre)::text || ' '::text) || (u.apellido)::text), 'Desconocido'::text) AS usuario,
    a.detalle,
    a.datos_anteriores,
    a.datos_nuevos,
    a.fecha_hora
   FROM (public.auditoria a
     LEFT JOIN public.usuario u ON ((u.id_usuario = a.id_usuario)))
  ORDER BY a.fecha_hora DESC;


ALTER VIEW public.vista_auditoria OWNER TO postgres;

--
-- Name: zoocriadero; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.zoocriadero (
    id_zoocriadero bigint NOT NULL,
    nombre character varying(100) NOT NULL,
    direccion character varying(200) NOT NULL,
    comuna character varying(60),
    barrio character varying(60),
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

ALTER TABLE public.zoocriadero ALTER COLUMN id_zoocriadero ADD GENERATED BY DEFAULT AS IDENTITY (
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
1	Ver
2	Crear
3	Editar
5	Exportar
7	Consultar
4	Inhabilitar
\.


--
-- Data for Name: actividad; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.actividad (id_actividad, ambito, nombre, descripcion, estado) FROM stdin;
2	zoocriadero	Medición Parámetros	Control de PH, Temperatura y Oxígeno	1
5	terreno	Inspección Larvaria	Búsqueda de larvas de Aedes aegypti	1
6	terreno	Siembra de Guppies en Campo	Liberación de peces controladores de larvas	1
7	terreno	Capacitación Comunitaria	Educación sobre control de dengue	1
24	zoocriadero	Limpiar tanque	Limpieza general del tanque y retiro de residuos	1
25	zoocriadero	Contar peces	Conteo de peces vivos, nacidos y muertos	1
26	zoocriadero	Equilibrar pH	Ajuste del pH del agua al rango 6.5 - 7.5	1
27	zoocriadero	Medir temperatura	Medición y ajuste de la temperatura del agua	1
28	zoocriadero	Sembrar alevinos	Siembra de alevinos en el tanque	1
29	zoocriadero	Cosechar peces	Extracción de peces para entrega a la comunidad	1
30	zoocriadero	Alimentar peces	Suministro de alimento concentrado	1
31	zoocriadero	Cambiar agua	Recambio parcial o total del agua	1
32	zoocriadero	Limpiar filtro	Limpieza o cambio del sistema de filtrado	1
33	zoocriadero	Retirar peces muertos	Retiro de la mortalidad encontrada en el tanque	1
34	zoocriadero	Revisar oxigenación	Revisión del sistema de aireación del tanque	1
35	zoocriadero	Clasificar por tallas	Separación de los peces según su tamaño	1
36	zoocriadero	Revisar reproducción	Verificación de hembras grávidas y crías	1
37	zoocriadero	Aplicar tratamiento	Tratamiento sanitario a los peces del tanque	1
38	terreno	Inspección	Inspección de depósitos en vivienda	1
39	terreno	Eliminación	Eliminación de criaderos encontrados	1
40	terreno	Larvicida	Aplicación de larvicida en depósitos útiles	1
41	terreno	Entrega de peces	Entrega de guppys a la comunidad	1
42	terreno	Educación	Jornada de educación sanitaria	1
43	terreno	Lavado	Lavado y cepillado de tanques y albercas	1
44	terreno	Tapado	Tapado de depósitos de agua	1
45	terreno	Seguimiento	Visita de seguimiento a vivienda intervenida	1
46	terreno	Censo de criaderos	Registro de depósitos por vivienda	1
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
1	1	30
2	2	30
3	3	30
\.


--
-- Data for Name: auditoria; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.auditoria (id_auditoria, modulo, accion, id_registro, id_usuario, datos_anteriores, datos_nuevos, detalle, fecha_hora) FROM stdin;
1	rol_permiso	DELETE	\N	\N	{"id_rol": 2, "id_modulo": 1, "id_accion_permiso": 1}	\N	Eliminación de registro en rol_permiso	2026-09-20 19:05:55.527268
2	rol_permiso	DELETE	\N	\N	{"id_rol": 2, "id_modulo": 5, "id_accion_permiso": 1}	\N	Eliminación de registro en rol_permiso	2026-09-20 19:05:55.527268
3	rol_permiso	DELETE	\N	\N	{"id_rol": 2, "id_modulo": 17, "id_accion_permiso": 1}	\N	Eliminación de registro en rol_permiso	2026-09-20 19:05:55.527268
4	rol_permiso	DELETE	\N	\N	{"id_rol": 2, "id_modulo": 18, "id_accion_permiso": 1}	\N	Eliminación de registro en rol_permiso	2026-09-20 19:05:55.527268
5	rol_permiso	DELETE	\N	\N	{"id_rol": 2, "id_modulo": 19, "id_accion_permiso": 1}	\N	Eliminación de registro en rol_permiso	2026-09-20 19:05:55.527268
6	rol_permiso	DELETE	\N	\N	{"id_rol": 2, "id_modulo": 21, "id_accion_permiso": 1}	\N	Eliminación de registro en rol_permiso	2026-09-20 19:05:55.527268
7	rol_permiso	DELETE	\N	\N	{"id_rol": 2, "id_modulo": 22, "id_accion_permiso": 1}	\N	Eliminación de registro en rol_permiso	2026-09-20 19:05:55.527268
8	rol_permiso	DELETE	\N	\N	{"id_rol": 2, "id_modulo": 23, "id_accion_permiso": 1}	\N	Eliminación de registro en rol_permiso	2026-09-20 19:05:55.527268
9	rol_permiso	DELETE	\N	\N	{"id_rol": 2, "id_modulo": 19, "id_accion_permiso": 2}	\N	Eliminación de registro en rol_permiso	2026-09-20 19:05:55.527268
10	rol_permiso	DELETE	\N	\N	{"id_rol": 2, "id_modulo": 21, "id_accion_permiso": 2}	\N	Eliminación de registro en rol_permiso	2026-09-20 19:05:55.527268
11	rol_permiso	DELETE	\N	\N	{"id_rol": 2, "id_modulo": 19, "id_accion_permiso": 3}	\N	Eliminación de registro en rol_permiso	2026-09-20 19:05:55.527268
12	rol_permiso	DELETE	\N	\N	{"id_rol": 2, "id_modulo": 23, "id_accion_permiso": 3}	\N	Eliminación de registro en rol_permiso	2026-09-20 19:05:55.527268
13	rol_permiso	DELETE	\N	\N	{"id_rol": 2, "id_modulo": 19, "id_accion_permiso": 4}	\N	Eliminación de registro en rol_permiso	2026-09-20 19:05:55.527268
14	rol_permiso	DELETE	\N	\N	{"id_rol": 2, "id_modulo": 22, "id_accion_permiso": 4}	\N	Eliminación de registro en rol_permiso	2026-09-20 19:05:55.527268
15	rol_permiso	DELETE	\N	\N	{"id_rol": 2, "id_modulo": 23, "id_accion_permiso": 4}	\N	Eliminación de registro en rol_permiso	2026-09-20 19:05:55.527268
16	rol_permiso	DELETE	\N	\N	{"id_rol": 2, "id_modulo": 18, "id_accion_permiso": 5}	\N	Eliminación de registro en rol_permiso	2026-09-20 19:05:55.527268
17	rol_permiso	DELETE	\N	\N	{"id_rol": 2, "id_modulo": 1, "id_accion_permiso": 7}	\N	Eliminación de registro en rol_permiso	2026-09-20 19:05:55.527268
18	rol_permiso	DELETE	\N	\N	{"id_rol": 2, "id_modulo": 19, "id_accion_permiso": 7}	\N	Eliminación de registro en rol_permiso	2026-09-20 19:05:55.527268
19	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 12, "id_accion_permiso": 1}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
20	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 10, "id_accion_permiso": 1}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
21	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 9, "id_accion_permiso": 1}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
22	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 8, "id_accion_permiso": 1}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
23	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 7, "id_accion_permiso": 1}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
24	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 1, "id_accion_permiso": 1}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
25	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 12, "id_accion_permiso": 2}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
26	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 10, "id_accion_permiso": 2}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
27	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 9, "id_accion_permiso": 2}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
28	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 8, "id_accion_permiso": 2}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
29	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 7, "id_accion_permiso": 2}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
30	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 1, "id_accion_permiso": 2}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
31	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 12, "id_accion_permiso": 3}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
32	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 10, "id_accion_permiso": 3}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
33	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 9, "id_accion_permiso": 3}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
34	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 8, "id_accion_permiso": 3}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
35	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 7, "id_accion_permiso": 3}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
36	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 1, "id_accion_permiso": 3}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
37	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 12, "id_accion_permiso": 7}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
38	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 10, "id_accion_permiso": 7}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
39	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 9, "id_accion_permiso": 7}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
40	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 8, "id_accion_permiso": 7}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
41	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 7, "id_accion_permiso": 7}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
42	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 1, "id_accion_permiso": 7}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
43	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 2, "id_modulo": 23, "id_accion_permiso": 1}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
44	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 2, "id_modulo": 22, "id_accion_permiso": 1}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
45	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 2, "id_modulo": 21, "id_accion_permiso": 1}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
46	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 2, "id_modulo": 19, "id_accion_permiso": 1}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
47	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 2, "id_modulo": 11, "id_accion_permiso": 1}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
48	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 2, "id_modulo": 7, "id_accion_permiso": 1}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
49	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 2, "id_modulo": 21, "id_accion_permiso": 2}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
50	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 2, "id_modulo": 19, "id_accion_permiso": 2}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
51	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 2, "id_modulo": 11, "id_accion_permiso": 2}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
52	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 2, "id_modulo": 7, "id_accion_permiso": 2}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
53	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 2, "id_modulo": 23, "id_accion_permiso": 3}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
54	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 2, "id_modulo": 19, "id_accion_permiso": 3}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
55	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 2, "id_modulo": 11, "id_accion_permiso": 3}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
56	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 2, "id_modulo": 7, "id_accion_permiso": 3}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
57	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 2, "id_modulo": 19, "id_accion_permiso": 7}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
58	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 2, "id_modulo": 11, "id_accion_permiso": 7}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
59	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 2, "id_modulo": 7, "id_accion_permiso": 7}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
60	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 2, "id_modulo": 23, "id_accion_permiso": 4}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
61	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 2, "id_modulo": 22, "id_accion_permiso": 4}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
62	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 2, "id_modulo": 19, "id_accion_permiso": 4}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
63	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 2, "id_modulo": 11, "id_accion_permiso": 4}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
64	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 2, "id_modulo": 7, "id_accion_permiso": 4}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
65	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 1, "id_modulo": 18, "id_accion_permiso": 1}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
66	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 1, "id_modulo": 8, "id_accion_permiso": 1}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
67	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 1, "id_modulo": 1, "id_accion_permiso": 1}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
68	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 1, "id_modulo": 8, "id_accion_permiso": 2}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
69	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 1, "id_modulo": 1, "id_accion_permiso": 2}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
70	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 1, "id_modulo": 8, "id_accion_permiso": 3}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
71	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 1, "id_modulo": 1, "id_accion_permiso": 3}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
72	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 1, "id_modulo": 18, "id_accion_permiso": 5}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
73	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 1, "id_modulo": 8, "id_accion_permiso": 7}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
74	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 1, "id_modulo": 1, "id_accion_permiso": 7}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
75	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 1, "id_modulo": 8, "id_accion_permiso": 4}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
76	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 1, "id_modulo": 1, "id_accion_permiso": 4}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
77	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 4, "id_modulo": 23, "id_accion_permiso": 1}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
78	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 4, "id_modulo": 22, "id_accion_permiso": 1}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
79	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 4, "id_modulo": 21, "id_accion_permiso": 1}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
80	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 4, "id_modulo": 20, "id_accion_permiso": 1}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
81	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 4, "id_modulo": 19, "id_accion_permiso": 1}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
82	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 4, "id_modulo": 18, "id_accion_permiso": 1}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
83	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 4, "id_modulo": 17, "id_accion_permiso": 1}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
84	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 4, "id_modulo": 12, "id_accion_permiso": 1}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
85	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 4, "id_modulo": 11, "id_accion_permiso": 1}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
86	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 4, "id_modulo": 10, "id_accion_permiso": 1}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
87	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 4, "id_modulo": 9, "id_accion_permiso": 1}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
88	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 4, "id_modulo": 8, "id_accion_permiso": 1}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
89	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 4, "id_modulo": 7, "id_accion_permiso": 1}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
90	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 4, "id_modulo": 5, "id_accion_permiso": 1}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
91	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 4, "id_modulo": 1, "id_accion_permiso": 1}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
92	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 4, "id_modulo": 21, "id_accion_permiso": 2}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
93	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 4, "id_modulo": 19, "id_accion_permiso": 2}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
94	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 4, "id_modulo": 12, "id_accion_permiso": 2}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
95	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 4, "id_modulo": 11, "id_accion_permiso": 2}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
96	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 4, "id_modulo": 10, "id_accion_permiso": 2}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
97	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 4, "id_modulo": 9, "id_accion_permiso": 2}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
98	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 4, "id_modulo": 8, "id_accion_permiso": 2}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
99	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 4, "id_modulo": 7, "id_accion_permiso": 2}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
100	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 4, "id_modulo": 1, "id_accion_permiso": 2}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
101	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 4, "id_modulo": 23, "id_accion_permiso": 3}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
102	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 4, "id_modulo": 20, "id_accion_permiso": 3}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
103	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 4, "id_modulo": 19, "id_accion_permiso": 3}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
104	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 4, "id_modulo": 12, "id_accion_permiso": 3}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
105	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 4, "id_modulo": 11, "id_accion_permiso": 3}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
106	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 4, "id_modulo": 10, "id_accion_permiso": 3}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
107	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 4, "id_modulo": 9, "id_accion_permiso": 3}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
108	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 4, "id_modulo": 8, "id_accion_permiso": 3}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
109	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 4, "id_modulo": 7, "id_accion_permiso": 3}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
110	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 4, "id_modulo": 1, "id_accion_permiso": 3}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
111	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 4, "id_modulo": 18, "id_accion_permiso": 5}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
112	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 4, "id_modulo": 20, "id_accion_permiso": 7}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
113	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 4, "id_modulo": 19, "id_accion_permiso": 7}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
114	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 4, "id_modulo": 12, "id_accion_permiso": 7}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
115	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 4, "id_modulo": 11, "id_accion_permiso": 7}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
116	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 4, "id_modulo": 10, "id_accion_permiso": 7}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
117	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 4, "id_modulo": 9, "id_accion_permiso": 7}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
118	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 4, "id_modulo": 8, "id_accion_permiso": 7}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
119	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 4, "id_modulo": 7, "id_accion_permiso": 7}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
120	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 4, "id_modulo": 1, "id_accion_permiso": 7}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
121	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 4, "id_modulo": 23, "id_accion_permiso": 4}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
122	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 4, "id_modulo": 22, "id_accion_permiso": 4}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
123	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 4, "id_modulo": 20, "id_accion_permiso": 4}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
124	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 4, "id_modulo": 19, "id_accion_permiso": 4}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
125	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 4, "id_modulo": 12, "id_accion_permiso": 4}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
126	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 4, "id_modulo": 11, "id_accion_permiso": 4}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
127	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 4, "id_modulo": 10, "id_accion_permiso": 4}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
128	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 4, "id_modulo": 9, "id_accion_permiso": 4}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
129	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 4, "id_modulo": 8, "id_accion_permiso": 4}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
130	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 4, "id_modulo": 7, "id_accion_permiso": 4}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
131	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 4, "id_modulo": 1, "id_accion_permiso": 4}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
132	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 4, "id_modulo": 20, "id_accion_permiso": 2}	Creación de registro en rol_permiso	2026-09-20 19:05:55.527268
133	usuario	INSERT	7	\N	\N	{"correo": "auxiliar.temporal@siguppys.local", "estado": 1, "id_rol": 3, "nombre": "Auxiliar", "apellido": "Temporal", "creado_en": "2026-09-20T19:05:55.527268", "documento": "900000001", "contrasena": "Siguppy#Temp2026", "id_usuario": 7, "token_expira": null, "bloqueo_hasta": null, "id_tipodocumento": 1, "intentos_fallidos": 0, "token_recuperacion": null}	Creación de registro en usuario	2026-09-20 19:05:55.527268
134	usuario	INSERT	8	\N	\N	{"correo": "administrador.temporal@siguppys.local", "estado": 1, "id_rol": 2, "nombre": "Administrador", "apellido": "Temporal", "creado_en": "2026-09-20T19:05:55.527268", "documento": "900000002", "contrasena": "Siguppy#Temp2026", "id_usuario": 8, "token_expira": null, "bloqueo_hasta": null, "id_tipodocumento": 1, "intentos_fallidos": 0, "token_recuperacion": null}	Creación de registro en usuario	2026-09-20 19:05:55.527268
135	usuario	INSERT	9	\N	\N	{"correo": "coordinador.temporal@siguppys.local", "estado": 1, "id_rol": 1, "nombre": "Coordinador", "apellido": "Temporal", "creado_en": "2026-09-20T19:05:55.527268", "documento": "900000003", "contrasena": "Siguppy#Temp2026", "id_usuario": 9, "token_expira": null, "bloqueo_hasta": null, "id_tipodocumento": 1, "intentos_fallidos": 0, "token_recuperacion": null}	Creación de registro en usuario	2026-09-20 19:05:55.527268
136	usuario	INSERT	10	\N	\N	{"correo": "superadmin.temporal@siguppys.local", "estado": 1, "id_rol": 4, "nombre": "Super", "apellido": "Administrador Temporal", "creado_en": "2026-09-20T19:05:55.527268", "documento": "900000004", "contrasena": "Siguppy#Temp2026", "id_usuario": 10, "token_expira": null, "bloqueo_hasta": null, "id_tipodocumento": 1, "intentos_fallidos": 0, "token_recuperacion": null}	Creación de registro en usuario	2026-09-20 19:05:55.527268
137	usuario	UPDATE	10	10	{"correo": "superadmin.temporal@siguppys.local", "estado": 1, "id_rol": 4, "nombre": "Super", "apellido": "Administrador Temporal", "creado_en": "2026-09-20T19:05:55.527268", "documento": "900000004", "contrasena": "Siguppy#Temp2026", "id_usuario": 10, "token_expira": null, "bloqueo_hasta": null, "id_tipodocumento": 1, "intentos_fallidos": 0, "token_recuperacion": null}	{"correo": "superadmin.temporal@siguppys.local", "estado": 1, "id_rol": 4, "nombre": "Super", "apellido": "Administrador Temporal", "creado_en": "2026-09-20T19:05:55.527268", "documento": "900000004", "contrasena": "Siguppy#Temp2026", "id_usuario": 10, "token_expira": null, "bloqueo_hasta": null, "id_tipodocumento": 1, "intentos_fallidos": 0, "token_recuperacion": null}	Actualización de registro en usuario	2026-09-20 19:06:19.112315
138	login	LOGIN_EXITOSO	10	10	\N	{"correo": "superadmin.temporal@siguppys.local", "exitoso": true}	Inicio de sesión exitoso	2026-09-20 19:06:19.159369
139	rol	UPDATE	3	\N	{"estado": 1, "id_rol": 3, "nombre_rol": "Auxiliar", "descripcion": "Personal de campo"}	{"estado": 1, "id_rol": 3, "nombre_rol": "Auxiliar", "descripcion": "Personal de campo"}	Actualización de registro en rol	2026-09-20 19:08:08.440103
140	rol_permiso	DELETE	\N	\N	{"id_rol": 3, "id_modulo": 12, "id_accion_permiso": 1}	\N	Eliminación de registro en rol_permiso	2026-09-20 19:08:08.440103
141	rol_permiso	DELETE	\N	\N	{"id_rol": 3, "id_modulo": 10, "id_accion_permiso": 1}	\N	Eliminación de registro en rol_permiso	2026-09-20 19:08:08.440103
142	rol_permiso	DELETE	\N	\N	{"id_rol": 3, "id_modulo": 9, "id_accion_permiso": 1}	\N	Eliminación de registro en rol_permiso	2026-09-20 19:08:08.440103
143	rol_permiso	DELETE	\N	\N	{"id_rol": 3, "id_modulo": 8, "id_accion_permiso": 1}	\N	Eliminación de registro en rol_permiso	2026-09-20 19:08:08.440103
144	rol_permiso	DELETE	\N	\N	{"id_rol": 3, "id_modulo": 7, "id_accion_permiso": 1}	\N	Eliminación de registro en rol_permiso	2026-09-20 19:08:08.440103
145	rol_permiso	DELETE	\N	\N	{"id_rol": 3, "id_modulo": 1, "id_accion_permiso": 1}	\N	Eliminación de registro en rol_permiso	2026-09-20 19:08:08.440103
146	rol_permiso	DELETE	\N	\N	{"id_rol": 3, "id_modulo": 12, "id_accion_permiso": 2}	\N	Eliminación de registro en rol_permiso	2026-09-20 19:08:08.440103
147	rol_permiso	DELETE	\N	\N	{"id_rol": 3, "id_modulo": 10, "id_accion_permiso": 2}	\N	Eliminación de registro en rol_permiso	2026-09-20 19:08:08.440103
148	rol_permiso	DELETE	\N	\N	{"id_rol": 3, "id_modulo": 9, "id_accion_permiso": 2}	\N	Eliminación de registro en rol_permiso	2026-09-20 19:08:08.440103
149	rol_permiso	DELETE	\N	\N	{"id_rol": 3, "id_modulo": 8, "id_accion_permiso": 2}	\N	Eliminación de registro en rol_permiso	2026-09-20 19:08:08.440103
150	rol_permiso	DELETE	\N	\N	{"id_rol": 3, "id_modulo": 7, "id_accion_permiso": 2}	\N	Eliminación de registro en rol_permiso	2026-09-20 19:08:08.440103
151	rol_permiso	DELETE	\N	\N	{"id_rol": 3, "id_modulo": 1, "id_accion_permiso": 2}	\N	Eliminación de registro en rol_permiso	2026-09-20 19:08:08.440103
152	rol_permiso	DELETE	\N	\N	{"id_rol": 3, "id_modulo": 12, "id_accion_permiso": 3}	\N	Eliminación de registro en rol_permiso	2026-09-20 19:08:08.440103
153	rol_permiso	DELETE	\N	\N	{"id_rol": 3, "id_modulo": 10, "id_accion_permiso": 3}	\N	Eliminación de registro en rol_permiso	2026-09-20 19:08:08.440103
154	rol_permiso	DELETE	\N	\N	{"id_rol": 3, "id_modulo": 9, "id_accion_permiso": 3}	\N	Eliminación de registro en rol_permiso	2026-09-20 19:08:08.440103
155	rol_permiso	DELETE	\N	\N	{"id_rol": 3, "id_modulo": 8, "id_accion_permiso": 3}	\N	Eliminación de registro en rol_permiso	2026-09-20 19:08:08.440103
156	rol_permiso	DELETE	\N	\N	{"id_rol": 3, "id_modulo": 7, "id_accion_permiso": 3}	\N	Eliminación de registro en rol_permiso	2026-09-20 19:08:08.440103
157	rol_permiso	DELETE	\N	\N	{"id_rol": 3, "id_modulo": 1, "id_accion_permiso": 3}	\N	Eliminación de registro en rol_permiso	2026-09-20 19:08:08.440103
158	rol_permiso	DELETE	\N	\N	{"id_rol": 3, "id_modulo": 12, "id_accion_permiso": 7}	\N	Eliminación de registro en rol_permiso	2026-09-20 19:08:08.440103
159	rol_permiso	DELETE	\N	\N	{"id_rol": 3, "id_modulo": 10, "id_accion_permiso": 7}	\N	Eliminación de registro en rol_permiso	2026-09-20 19:08:08.440103
160	rol_permiso	DELETE	\N	\N	{"id_rol": 3, "id_modulo": 9, "id_accion_permiso": 7}	\N	Eliminación de registro en rol_permiso	2026-09-20 19:08:08.440103
161	rol_permiso	DELETE	\N	\N	{"id_rol": 3, "id_modulo": 8, "id_accion_permiso": 7}	\N	Eliminación de registro en rol_permiso	2026-09-20 19:08:08.440103
162	rol_permiso	DELETE	\N	\N	{"id_rol": 3, "id_modulo": 7, "id_accion_permiso": 7}	\N	Eliminación de registro en rol_permiso	2026-09-20 19:08:08.440103
163	rol_permiso	DELETE	\N	\N	{"id_rol": 3, "id_modulo": 1, "id_accion_permiso": 7}	\N	Eliminación de registro en rol_permiso	2026-09-20 19:08:08.440103
164	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 1, "id_accion_permiso": 1}	Creación de registro en rol_permiso	2026-09-20 19:08:08.440103
165	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 7, "id_accion_permiso": 1}	Creación de registro en rol_permiso	2026-09-20 19:08:08.440103
166	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 8, "id_accion_permiso": 1}	Creación de registro en rol_permiso	2026-09-20 19:08:08.440103
167	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 9, "id_accion_permiso": 1}	Creación de registro en rol_permiso	2026-09-20 19:08:08.440103
168	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 10, "id_accion_permiso": 1}	Creación de registro en rol_permiso	2026-09-20 19:08:08.440103
169	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 12, "id_accion_permiso": 1}	Creación de registro en rol_permiso	2026-09-20 19:08:08.440103
170	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 20, "id_accion_permiso": 1}	Creación de registro en rol_permiso	2026-09-20 19:08:08.440103
171	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 1, "id_accion_permiso": 2}	Creación de registro en rol_permiso	2026-09-20 19:08:08.440103
172	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 7, "id_accion_permiso": 2}	Creación de registro en rol_permiso	2026-09-20 19:08:08.440103
173	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 8, "id_accion_permiso": 2}	Creación de registro en rol_permiso	2026-09-20 19:08:08.440103
174	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 9, "id_accion_permiso": 2}	Creación de registro en rol_permiso	2026-09-20 19:08:08.440103
175	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 10, "id_accion_permiso": 2}	Creación de registro en rol_permiso	2026-09-20 19:08:08.440103
176	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 12, "id_accion_permiso": 2}	Creación de registro en rol_permiso	2026-09-20 19:08:08.440103
177	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 20, "id_accion_permiso": 2}	Creación de registro en rol_permiso	2026-09-20 19:08:08.440103
178	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 1, "id_accion_permiso": 3}	Creación de registro en rol_permiso	2026-09-20 19:08:08.440103
179	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 7, "id_accion_permiso": 3}	Creación de registro en rol_permiso	2026-09-20 19:08:08.440103
180	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 8, "id_accion_permiso": 3}	Creación de registro en rol_permiso	2026-09-20 19:08:08.440103
181	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 9, "id_accion_permiso": 3}	Creación de registro en rol_permiso	2026-09-20 19:08:08.440103
182	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 10, "id_accion_permiso": 3}	Creación de registro en rol_permiso	2026-09-20 19:08:08.440103
183	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 12, "id_accion_permiso": 3}	Creación de registro en rol_permiso	2026-09-20 19:08:08.440103
184	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 20, "id_accion_permiso": 3}	Creación de registro en rol_permiso	2026-09-20 19:08:08.440103
185	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 20, "id_accion_permiso": 4}	Creación de registro en rol_permiso	2026-09-20 19:08:08.440103
186	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 1, "id_accion_permiso": 7}	Creación de registro en rol_permiso	2026-09-20 19:08:08.440103
187	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 7, "id_accion_permiso": 7}	Creación de registro en rol_permiso	2026-09-20 19:08:08.440103
188	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 8, "id_accion_permiso": 7}	Creación de registro en rol_permiso	2026-09-20 19:08:08.440103
189	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 9, "id_accion_permiso": 7}	Creación de registro en rol_permiso	2026-09-20 19:08:08.440103
190	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 10, "id_accion_permiso": 7}	Creación de registro en rol_permiso	2026-09-20 19:08:08.440103
191	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 12, "id_accion_permiso": 7}	Creación de registro en rol_permiso	2026-09-20 19:08:08.440103
192	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 20, "id_accion_permiso": 7}	Creación de registro en rol_permiso	2026-09-20 19:08:08.440103
230	rol_permiso	DELETE	\N	\N	{"id_rol": 3, "id_modulo": 20, "id_accion_permiso": 1}	\N	Eliminación de registro en rol_permiso	2026-09-20 20:13:13.448567
231	rol_permiso	DELETE	\N	\N	{"id_rol": 3, "id_modulo": 1, "id_accion_permiso": 2}	\N	Eliminación de registro en rol_permiso	2026-09-20 20:13:13.448567
193	usuario	UPDATE	2	10	{"correo": "jaidermontano69@gmail.com", "estado": 1, "id_rol": 2, "nombre": "miguel", "apellido": "tovar", "creado_en": "2026-09-19T10:47:39.645976", "documento": "1109232423", "contrasena": "$2y$10$SlVYZEBG3aDdWZM4JEr1feLl/RTyEtbVeM3meBB6b6sUbr2jm.v.W", "id_usuario": 2, "token_expira": "2026-09-19T23:11:16", "bloqueo_hasta": null, "id_tipodocumento": 1, "intentos_fallidos": 0, "token_recuperacion": "017888"}	{"correo": "jaidermontano69@gmail.com", "estado": 1, "id_rol": 4, "nombre": "miguel", "apellido": "tovar", "creado_en": "2026-09-19T10:47:39.645976", "documento": "1109232423", "contrasena": "$2y$10$SlVYZEBG3aDdWZM4JEr1feLl/RTyEtbVeM3meBB6b6sUbr2jm.v.W", "id_usuario": 2, "token_expira": "2026-09-19T23:11:16", "bloqueo_hasta": null, "id_tipodocumento": 1, "intentos_fallidos": 0, "token_recuperacion": "017888"}	Actualización de registro en usuario	2026-09-20 19:46:16.961297
194	usuario	UPDATE	2	2	{"correo": "jaidermontano69@gmail.com", "estado": 1, "id_rol": 4, "nombre": "miguel", "apellido": "tovar", "creado_en": "2026-09-19T10:47:39.645976", "documento": "1109232423", "contrasena": "$2y$10$SlVYZEBG3aDdWZM4JEr1feLl/RTyEtbVeM3meBB6b6sUbr2jm.v.W", "id_usuario": 2, "token_expira": "2026-09-19T23:11:16", "bloqueo_hasta": null, "id_tipodocumento": 1, "intentos_fallidos": 0, "token_recuperacion": "017888"}	{"correo": "jaidermontano69@gmail.com", "estado": 1, "id_rol": 4, "nombre": "miguel", "apellido": "tovar", "creado_en": "2026-09-19T10:47:39.645976", "documento": "1109232423", "contrasena": "$2y$10$SlVYZEBG3aDdWZM4JEr1feLl/RTyEtbVeM3meBB6b6sUbr2jm.v.W", "id_usuario": 2, "token_expira": "2026-09-19T23:11:16", "bloqueo_hasta": null, "id_tipodocumento": 1, "intentos_fallidos": 0, "token_recuperacion": "017888"}	Actualización de registro en usuario	2026-09-20 19:46:53.213271
195	login	LOGIN_EXITOSO	2	2	\N	{"correo": "jaidermontano69@gmail.com", "exitoso": true}	Inicio de sesión exitoso	2026-09-20 19:46:53.220992
196	modulo_accion_permitida	INSERT	\N	\N	\N	{"id_modulo": 25, "id_accion_permiso": 1}	Creación de registro en modulo_accion_permitida	2026-09-20 19:48:03.545925
197	modulo_accion_permitida	INSERT	\N	\N	\N	{"id_modulo": 25, "id_accion_permiso": 2}	Creación de registro en modulo_accion_permitida	2026-09-20 19:48:03.545925
198	modulo_accion_permitida	INSERT	\N	\N	\N	{"id_modulo": 25, "id_accion_permiso": 3}	Creación de registro en modulo_accion_permitida	2026-09-20 19:48:03.545925
199	modulo_accion_permitida	INSERT	\N	\N	\N	{"id_modulo": 25, "id_accion_permiso": 4}	Creación de registro en modulo_accion_permitida	2026-09-20 19:48:03.545925
200	modulo_accion_permitida	INSERT	\N	\N	\N	{"id_modulo": 25, "id_accion_permiso": 7}	Creación de registro en modulo_accion_permitida	2026-09-20 19:48:03.545925
201	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 4, "id_modulo": 25, "id_accion_permiso": 1}	Creación de registro en rol_permiso	2026-09-20 19:48:03.545925
202	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 4, "id_modulo": 25, "id_accion_permiso": 2}	Creación de registro en rol_permiso	2026-09-20 19:48:03.545925
203	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 4, "id_modulo": 25, "id_accion_permiso": 3}	Creación de registro en rol_permiso	2026-09-20 19:48:03.545925
204	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 4, "id_modulo": 25, "id_accion_permiso": 7}	Creación de registro en rol_permiso	2026-09-20 19:48:03.545925
205	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 4, "id_modulo": 25, "id_accion_permiso": 4}	Creación de registro en rol_permiso	2026-09-20 19:48:03.545925
206	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 25, "id_accion_permiso": 1}	Creación de registro en rol_permiso	2026-09-20 19:48:03.545925
207	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 25, "id_accion_permiso": 2}	Creación de registro en rol_permiso	2026-09-20 19:48:03.545925
208	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 25, "id_accion_permiso": 3}	Creación de registro en rol_permiso	2026-09-20 19:48:03.545925
209	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 25, "id_accion_permiso": 7}	Creación de registro en rol_permiso	2026-09-20 19:48:03.545925
215	usuario	UPDATE	2	2	{"correo": "jaidermontano69@gmail.com", "estado": 1, "id_rol": 4, "nombre": "miguel", "apellido": "tovar", "creado_en": "2026-09-19T10:47:39.645976", "documento": "1109232423", "contrasena": "$2y$10$SlVYZEBG3aDdWZM4JEr1feLl/RTyEtbVeM3meBB6b6sUbr2jm.v.W", "id_usuario": 2, "token_expira": "2026-09-19T23:11:16", "bloqueo_hasta": null, "id_tipodocumento": 1, "intentos_fallidos": 0, "token_recuperacion": "017888"}	{"correo": "jaidermontano69@gmail.com", "estado": 1, "id_rol": 4, "nombre": "miguel", "apellido": "tovar", "creado_en": "2026-09-19T10:47:39.645976", "documento": "1109232423", "contrasena": "$2y$10$SlVYZEBG3aDdWZM4JEr1feLl/RTyEtbVeM3meBB6b6sUbr2jm.v.W", "id_usuario": 2, "token_expira": "2026-09-19T23:11:16", "bloqueo_hasta": null, "id_tipodocumento": 1, "intentos_fallidos": 0, "token_recuperacion": "017888"}	Actualización de registro en usuario	2026-09-20 19:49:03.663079
216	login	LOGIN_EXITOSO	2	2	\N	{"correo": "jaidermontano69@gmail.com", "exitoso": true}	Inicio de sesión exitoso	2026-09-20 19:49:03.697262
217	usuario	UPDATE	8	2	{"correo": "administrador.temporal@siguppys.local", "estado": 1, "id_rol": 2, "nombre": "Administrador", "apellido": "Temporal", "creado_en": "2026-09-20T19:05:55.527268", "documento": "900000002", "contrasena": "Siguppy#Temp2026", "id_usuario": 8, "token_expira": null, "bloqueo_hasta": null, "id_tipodocumento": 1, "intentos_fallidos": 0, "token_recuperacion": null}	{"correo": "adminstradortempral@gmail.com", "estado": 1, "id_rol": 2, "nombre": "Administrador", "apellido": "Temporal", "creado_en": "2026-09-20T19:05:55.527268", "documento": "900000002", "contrasena": "Siguppy#Temp2026", "id_usuario": 8, "token_expira": null, "bloqueo_hasta": null, "id_tipodocumento": 1, "intentos_fallidos": 0, "token_recuperacion": null}	Actualización de registro en usuario	2026-09-20 20:01:06.206464
218	usuario	UPDATE	7	2	{"correo": "auxiliar.temporal@siguppys.local", "estado": 1, "id_rol": 3, "nombre": "Auxiliar", "apellido": "Temporal", "creado_en": "2026-09-20T19:05:55.527268", "documento": "900000001", "contrasena": "Siguppy#Temp2026", "id_usuario": 7, "token_expira": null, "bloqueo_hasta": null, "id_tipodocumento": 1, "intentos_fallidos": 0, "token_recuperacion": null}	{"correo": "auxiliartemporal@gmail.com", "estado": 1, "id_rol": 3, "nombre": "Auxiliar", "apellido": "Temporal", "creado_en": "2026-09-20T19:05:55.527268", "documento": "900000001", "contrasena": "Siguppy#Temp2026", "id_usuario": 7, "token_expira": null, "bloqueo_hasta": null, "id_tipodocumento": 1, "intentos_fallidos": 0, "token_recuperacion": null}	Actualización de registro en usuario	2026-09-20 20:01:24.112513
219	usuario	UPDATE	9	2	{"correo": "coordinador.temporal@siguppys.local", "estado": 1, "id_rol": 1, "nombre": "Coordinador", "apellido": "Temporal", "creado_en": "2026-09-20T19:05:55.527268", "documento": "900000003", "contrasena": "Siguppy#Temp2026", "id_usuario": 9, "token_expira": null, "bloqueo_hasta": null, "id_tipodocumento": 1, "intentos_fallidos": 0, "token_recuperacion": null}	{"correo": "coordinadortemporal@gmail.com", "estado": 1, "id_rol": 1, "nombre": "Coordinador", "apellido": "Temporal", "creado_en": "2026-09-20T19:05:55.527268", "documento": "900000003", "contrasena": "Siguppy#Temp2026", "id_usuario": 9, "token_expira": null, "bloqueo_hasta": null, "id_tipodocumento": 1, "intentos_fallidos": 0, "token_recuperacion": null}	Actualización de registro en usuario	2026-09-20 20:01:39.657453
220	usuario	UPDATE	10	2	{"correo": "superadmin.temporal@siguppys.local", "estado": 1, "id_rol": 4, "nombre": "Super", "apellido": "Administrador Temporal", "creado_en": "2026-09-20T19:05:55.527268", "documento": "900000004", "contrasena": "Siguppy#Temp2026", "id_usuario": 10, "token_expira": null, "bloqueo_hasta": null, "id_tipodocumento": 1, "intentos_fallidos": 0, "token_recuperacion": null}	{"correo": "superadministradortemporal@gmail.com", "estado": 1, "id_rol": 4, "nombre": "Super", "apellido": "Administrador Temporal", "creado_en": "2026-09-20T19:05:55.527268", "documento": "900000004", "contrasena": "Siguppy#Temp2026", "id_usuario": 10, "token_expira": null, "bloqueo_hasta": null, "id_tipodocumento": 1, "intentos_fallidos": 0, "token_recuperacion": null}	Actualización de registro en usuario	2026-09-20 20:01:57.324686
221	usuario	UPDATE	2	2	{"correo": "jaidermontano69@gmail.com", "estado": 1, "id_rol": 4, "nombre": "miguel", "apellido": "tovar", "creado_en": "2026-09-19T10:47:39.645976", "documento": "1109232423", "contrasena": "$2y$10$SlVYZEBG3aDdWZM4JEr1feLl/RTyEtbVeM3meBB6b6sUbr2jm.v.W", "id_usuario": 2, "token_expira": "2026-09-19T23:11:16", "bloqueo_hasta": null, "id_tipodocumento": 1, "intentos_fallidos": 0, "token_recuperacion": "017888"}	{"correo": "migueltovar69@gmail.com", "estado": 1, "id_rol": 2, "nombre": "miguel", "apellido": "tovar", "creado_en": "2026-09-19T10:47:39.645976", "documento": "1109232423", "contrasena": "$2y$10$SlVYZEBG3aDdWZM4JEr1feLl/RTyEtbVeM3meBB6b6sUbr2jm.v.W", "id_usuario": 2, "token_expira": "2026-09-19T23:11:16", "bloqueo_hasta": null, "id_tipodocumento": 1, "intentos_fallidos": 0, "token_recuperacion": "017888"}	Actualización de registro en usuario	2026-09-20 20:08:12.572077
222	usuario	INSERT	11	2	\N	{"correo": "jaidermontano69@gmail.com", "estado": 1, "id_rol": 4, "nombre": "Jaider Alexis", "apellido": "Montaño Mondragon", "creado_en": "2026-09-20T20:08:34.364933", "documento": "1109545511", "contrasena": "$2y$10$A9JiXPBSpciUEFKZnH3yUu1Pzjwe009WeQFjgK376EHbJJSdLZlX6", "id_usuario": 11, "token_expira": null, "bloqueo_hasta": null, "id_tipodocumento": 1, "intentos_fallidos": 0, "token_recuperacion": null}	Creación de registro en usuario	2026-09-20 20:08:34.364933
223	rol	UPDATE	3	\N	{"estado": 1, "id_rol": 3, "nombre_rol": "Auxiliar", "descripcion": "Personal de campo"}	{"estado": 1, "id_rol": 3, "nombre_rol": "Auxiliar", "descripcion": "Personal de campo"}	Actualización de registro en rol	2026-09-20 20:13:13.448567
224	rol_permiso	DELETE	\N	\N	{"id_rol": 3, "id_modulo": 1, "id_accion_permiso": 1}	\N	Eliminación de registro en rol_permiso	2026-09-20 20:13:13.448567
225	rol_permiso	DELETE	\N	\N	{"id_rol": 3, "id_modulo": 7, "id_accion_permiso": 1}	\N	Eliminación de registro en rol_permiso	2026-09-20 20:13:13.448567
226	rol_permiso	DELETE	\N	\N	{"id_rol": 3, "id_modulo": 8, "id_accion_permiso": 1}	\N	Eliminación de registro en rol_permiso	2026-09-20 20:13:13.448567
227	rol_permiso	DELETE	\N	\N	{"id_rol": 3, "id_modulo": 9, "id_accion_permiso": 1}	\N	Eliminación de registro en rol_permiso	2026-09-20 20:13:13.448567
228	rol_permiso	DELETE	\N	\N	{"id_rol": 3, "id_modulo": 10, "id_accion_permiso": 1}	\N	Eliminación de registro en rol_permiso	2026-09-20 20:13:13.448567
229	rol_permiso	DELETE	\N	\N	{"id_rol": 3, "id_modulo": 12, "id_accion_permiso": 1}	\N	Eliminación de registro en rol_permiso	2026-09-20 20:13:13.448567
232	rol_permiso	DELETE	\N	\N	{"id_rol": 3, "id_modulo": 7, "id_accion_permiso": 2}	\N	Eliminación de registro en rol_permiso	2026-09-20 20:13:13.448567
233	rol_permiso	DELETE	\N	\N	{"id_rol": 3, "id_modulo": 8, "id_accion_permiso": 2}	\N	Eliminación de registro en rol_permiso	2026-09-20 20:13:13.448567
234	rol_permiso	DELETE	\N	\N	{"id_rol": 3, "id_modulo": 9, "id_accion_permiso": 2}	\N	Eliminación de registro en rol_permiso	2026-09-20 20:13:13.448567
235	rol_permiso	DELETE	\N	\N	{"id_rol": 3, "id_modulo": 10, "id_accion_permiso": 2}	\N	Eliminación de registro en rol_permiso	2026-09-20 20:13:13.448567
236	rol_permiso	DELETE	\N	\N	{"id_rol": 3, "id_modulo": 12, "id_accion_permiso": 2}	\N	Eliminación de registro en rol_permiso	2026-09-20 20:13:13.448567
237	rol_permiso	DELETE	\N	\N	{"id_rol": 3, "id_modulo": 20, "id_accion_permiso": 2}	\N	Eliminación de registro en rol_permiso	2026-09-20 20:13:13.448567
238	rol_permiso	DELETE	\N	\N	{"id_rol": 3, "id_modulo": 1, "id_accion_permiso": 3}	\N	Eliminación de registro en rol_permiso	2026-09-20 20:13:13.448567
239	rol_permiso	DELETE	\N	\N	{"id_rol": 3, "id_modulo": 7, "id_accion_permiso": 3}	\N	Eliminación de registro en rol_permiso	2026-09-20 20:13:13.448567
240	rol_permiso	DELETE	\N	\N	{"id_rol": 3, "id_modulo": 8, "id_accion_permiso": 3}	\N	Eliminación de registro en rol_permiso	2026-09-20 20:13:13.448567
241	rol_permiso	DELETE	\N	\N	{"id_rol": 3, "id_modulo": 9, "id_accion_permiso": 3}	\N	Eliminación de registro en rol_permiso	2026-09-20 20:13:13.448567
242	rol_permiso	DELETE	\N	\N	{"id_rol": 3, "id_modulo": 10, "id_accion_permiso": 3}	\N	Eliminación de registro en rol_permiso	2026-09-20 20:13:13.448567
243	rol_permiso	DELETE	\N	\N	{"id_rol": 3, "id_modulo": 12, "id_accion_permiso": 3}	\N	Eliminación de registro en rol_permiso	2026-09-20 20:13:13.448567
244	rol_permiso	DELETE	\N	\N	{"id_rol": 3, "id_modulo": 20, "id_accion_permiso": 3}	\N	Eliminación de registro en rol_permiso	2026-09-20 20:13:13.448567
245	rol_permiso	DELETE	\N	\N	{"id_rol": 3, "id_modulo": 20, "id_accion_permiso": 4}	\N	Eliminación de registro en rol_permiso	2026-09-20 20:13:13.448567
246	rol_permiso	DELETE	\N	\N	{"id_rol": 3, "id_modulo": 1, "id_accion_permiso": 7}	\N	Eliminación de registro en rol_permiso	2026-09-20 20:13:13.448567
247	rol_permiso	DELETE	\N	\N	{"id_rol": 3, "id_modulo": 7, "id_accion_permiso": 7}	\N	Eliminación de registro en rol_permiso	2026-09-20 20:13:13.448567
248	rol_permiso	DELETE	\N	\N	{"id_rol": 3, "id_modulo": 8, "id_accion_permiso": 7}	\N	Eliminación de registro en rol_permiso	2026-09-20 20:13:13.448567
249	rol_permiso	DELETE	\N	\N	{"id_rol": 3, "id_modulo": 9, "id_accion_permiso": 7}	\N	Eliminación de registro en rol_permiso	2026-09-20 20:13:13.448567
250	rol_permiso	DELETE	\N	\N	{"id_rol": 3, "id_modulo": 10, "id_accion_permiso": 7}	\N	Eliminación de registro en rol_permiso	2026-09-20 20:13:13.448567
251	rol_permiso	DELETE	\N	\N	{"id_rol": 3, "id_modulo": 12, "id_accion_permiso": 7}	\N	Eliminación de registro en rol_permiso	2026-09-20 20:13:13.448567
252	rol_permiso	DELETE	\N	\N	{"id_rol": 3, "id_modulo": 20, "id_accion_permiso": 7}	\N	Eliminación de registro en rol_permiso	2026-09-20 20:13:13.448567
253	rol_permiso	DELETE	\N	\N	{"id_rol": 3, "id_modulo": 25, "id_accion_permiso": 1}	\N	Eliminación de registro en rol_permiso	2026-09-20 20:13:13.448567
254	rol_permiso	DELETE	\N	\N	{"id_rol": 3, "id_modulo": 25, "id_accion_permiso": 2}	\N	Eliminación de registro en rol_permiso	2026-09-20 20:13:13.448567
255	rol_permiso	DELETE	\N	\N	{"id_rol": 3, "id_modulo": 25, "id_accion_permiso": 3}	\N	Eliminación de registro en rol_permiso	2026-09-20 20:13:13.448567
256	rol_permiso	DELETE	\N	\N	{"id_rol": 3, "id_modulo": 25, "id_accion_permiso": 7}	\N	Eliminación de registro en rol_permiso	2026-09-20 20:13:13.448567
257	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 1, "id_accion_permiso": 1}	Creación de registro en rol_permiso	2026-09-20 20:13:13.448567
258	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 7, "id_accion_permiso": 1}	Creación de registro en rol_permiso	2026-09-20 20:13:13.448567
259	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 8, "id_accion_permiso": 1}	Creación de registro en rol_permiso	2026-09-20 20:13:13.448567
260	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 9, "id_accion_permiso": 1}	Creación de registro en rol_permiso	2026-09-20 20:13:13.448567
261	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 10, "id_accion_permiso": 1}	Creación de registro en rol_permiso	2026-09-20 20:13:13.448567
262	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 12, "id_accion_permiso": 1}	Creación de registro en rol_permiso	2026-09-20 20:13:13.448567
263	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 20, "id_accion_permiso": 1}	Creación de registro en rol_permiso	2026-09-20 20:13:13.448567
264	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 25, "id_accion_permiso": 1}	Creación de registro en rol_permiso	2026-09-20 20:13:13.448567
265	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 1, "id_accion_permiso": 2}	Creación de registro en rol_permiso	2026-09-20 20:13:13.448567
266	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 7, "id_accion_permiso": 2}	Creación de registro en rol_permiso	2026-09-20 20:13:13.448567
267	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 8, "id_accion_permiso": 2}	Creación de registro en rol_permiso	2026-09-20 20:13:13.448567
268	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 9, "id_accion_permiso": 2}	Creación de registro en rol_permiso	2026-09-20 20:13:13.448567
269	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 10, "id_accion_permiso": 2}	Creación de registro en rol_permiso	2026-09-20 20:13:13.448567
270	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 12, "id_accion_permiso": 2}	Creación de registro en rol_permiso	2026-09-20 20:13:13.448567
271	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 20, "id_accion_permiso": 2}	Creación de registro en rol_permiso	2026-09-20 20:13:13.448567
272	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 25, "id_accion_permiso": 2}	Creación de registro en rol_permiso	2026-09-20 20:13:13.448567
273	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 1, "id_accion_permiso": 3}	Creación de registro en rol_permiso	2026-09-20 20:13:13.448567
274	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 7, "id_accion_permiso": 3}	Creación de registro en rol_permiso	2026-09-20 20:13:13.448567
275	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 8, "id_accion_permiso": 3}	Creación de registro en rol_permiso	2026-09-20 20:13:13.448567
276	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 9, "id_accion_permiso": 3}	Creación de registro en rol_permiso	2026-09-20 20:13:13.448567
277	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 10, "id_accion_permiso": 3}	Creación de registro en rol_permiso	2026-09-20 20:13:13.448567
278	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 12, "id_accion_permiso": 3}	Creación de registro en rol_permiso	2026-09-20 20:13:13.448567
279	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 20, "id_accion_permiso": 3}	Creación de registro en rol_permiso	2026-09-20 20:13:13.448567
280	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 25, "id_accion_permiso": 3}	Creación de registro en rol_permiso	2026-09-20 20:13:13.448567
281	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 20, "id_accion_permiso": 4}	Creación de registro en rol_permiso	2026-09-20 20:13:13.448567
282	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 1, "id_accion_permiso": 7}	Creación de registro en rol_permiso	2026-09-20 20:13:13.448567
283	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 7, "id_accion_permiso": 7}	Creación de registro en rol_permiso	2026-09-20 20:13:13.448567
284	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 8, "id_accion_permiso": 7}	Creación de registro en rol_permiso	2026-09-20 20:13:13.448567
285	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 9, "id_accion_permiso": 7}	Creación de registro en rol_permiso	2026-09-20 20:13:13.448567
286	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 10, "id_accion_permiso": 7}	Creación de registro en rol_permiso	2026-09-20 20:13:13.448567
287	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 12, "id_accion_permiso": 7}	Creación de registro en rol_permiso	2026-09-20 20:13:13.448567
288	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 20, "id_accion_permiso": 7}	Creación de registro en rol_permiso	2026-09-20 20:13:13.448567
289	rol_permiso	INSERT	\N	\N	\N	{"id_rol": 3, "id_modulo": 25, "id_accion_permiso": 7}	Creación de registro en rol_permiso	2026-09-20 20:13:13.448567
290	usuario	UPDATE	11	11	{"correo": "jaidermontano69@gmail.com", "estado": 1, "id_rol": 4, "nombre": "Jaider Alexis", "apellido": "Montaño Mondragon", "creado_en": "2026-09-20T20:08:34.364933", "documento": "1109545511", "contrasena": "$2y$10$A9JiXPBSpciUEFKZnH3yUu1Pzjwe009WeQFjgK376EHbJJSdLZlX6", "id_usuario": 11, "token_expira": null, "bloqueo_hasta": null, "id_tipodocumento": 1, "intentos_fallidos": 0, "token_recuperacion": null}	{"correo": "jaidermontano69@gmail.com", "estado": 1, "id_rol": 4, "nombre": "Jaider Alexis", "apellido": "Montaño Mondragon", "creado_en": "2026-09-20T20:08:34.364933", "documento": "1109545511", "contrasena": "$2y$10$A9JiXPBSpciUEFKZnH3yUu1Pzjwe009WeQFjgK376EHbJJSdLZlX6", "id_usuario": 11, "token_expira": null, "bloqueo_hasta": null, "id_tipodocumento": 1, "intentos_fallidos": 0, "token_recuperacion": null}	Actualización de registro en usuario	2026-09-20 21:15:15.340074
291	login	LOGIN_EXITOSO	11	11	\N	{"correo": "jaidermontano69@gmail.com", "exitoso": true}	Inicio de sesión exitoso	2026-09-20 21:15:15.38791
292	usuario	UPDATE	11	11	{"correo": "jaidermontano69@gmail.com", "estado": 1, "id_rol": 4, "nombre": "Jaider Alexis", "apellido": "Montaño Mondragon", "creado_en": "2026-09-20T20:08:34.364933", "documento": "1109545511", "contrasena": "$2y$10$A9JiXPBSpciUEFKZnH3yUu1Pzjwe009WeQFjgK376EHbJJSdLZlX6", "id_usuario": 11, "token_expira": null, "bloqueo_hasta": null, "id_tipodocumento": 1, "intentos_fallidos": 0, "token_recuperacion": null}	{"correo": "jaidermontano69@gmail.com", "estado": 1, "id_rol": 4, "nombre": "Jaider Alexis", "apellido": "Montaño Mondragon", "creado_en": "2026-09-20T20:08:34.364933", "documento": "1109545511", "contrasena": "$2y$10$A9JiXPBSpciUEFKZnH3yUu1Pzjwe009WeQFjgK376EHbJJSdLZlX6", "id_usuario": 11, "token_expira": null, "bloqueo_hasta": null, "id_tipodocumento": 1, "intentos_fallidos": 0, "token_recuperacion": null}	Actualización de registro en usuario	2026-09-20 22:32:58.583943
293	login	LOGIN_EXITOSO	11	11	\N	{"correo": "jaidermontano69@gmail.com", "exitoso": true}	Inicio de sesión exitoso	2026-09-20 22:32:58.634978
294	seguimiento_deposito	INSERT	6	11	\N	{"fecha": "2026-09-20", "estado": 1, "creado_en": "2026-09-20T22:51:27.727257", "id_usuario": 11, "id_deposito": 6, "id_actividad": 38, "observaciones": "Se visualizarón mas larvas de lo normal", "presencia_larvas": 1, "numero_peces_sembrados": 0, "id_seguimiento_deposito": 6}	Creación de registro en seguimiento_deposito	2026-09-20 22:51:27.727257
295	rol	UPDATE	1	11	{"estado": 1, "id_rol": 1, "nombre_rol": "Coordinador", "descripcion": "Coordina control biológico y ecosalud"}	{"estado": 1, "id_rol": 1, "nombre_rol": "Coordinador", "descripcion": "Coordina control biológico"}	Actualización de registro en rol	2026-09-20 22:58:50.498206
296	rol_permiso	DELETE	\N	11	{"id_rol": 1, "id_modulo": 18, "id_accion_permiso": 1}	\N	Eliminación de registro en rol_permiso	2026-09-20 22:58:50.498206
297	rol_permiso	DELETE	\N	11	{"id_rol": 1, "id_modulo": 8, "id_accion_permiso": 1}	\N	Eliminación de registro en rol_permiso	2026-09-20 22:58:50.498206
298	rol_permiso	DELETE	\N	11	{"id_rol": 1, "id_modulo": 1, "id_accion_permiso": 1}	\N	Eliminación de registro en rol_permiso	2026-09-20 22:58:50.498206
299	rol_permiso	DELETE	\N	11	{"id_rol": 1, "id_modulo": 8, "id_accion_permiso": 2}	\N	Eliminación de registro en rol_permiso	2026-09-20 22:58:50.498206
300	rol_permiso	DELETE	\N	11	{"id_rol": 1, "id_modulo": 1, "id_accion_permiso": 2}	\N	Eliminación de registro en rol_permiso	2026-09-20 22:58:50.498206
301	rol_permiso	DELETE	\N	11	{"id_rol": 1, "id_modulo": 8, "id_accion_permiso": 3}	\N	Eliminación de registro en rol_permiso	2026-09-20 22:58:50.498206
302	rol_permiso	DELETE	\N	11	{"id_rol": 1, "id_modulo": 1, "id_accion_permiso": 3}	\N	Eliminación de registro en rol_permiso	2026-09-20 22:58:50.498206
303	rol_permiso	DELETE	\N	11	{"id_rol": 1, "id_modulo": 18, "id_accion_permiso": 5}	\N	Eliminación de registro en rol_permiso	2026-09-20 22:58:50.498206
304	rol_permiso	DELETE	\N	11	{"id_rol": 1, "id_modulo": 8, "id_accion_permiso": 7}	\N	Eliminación de registro en rol_permiso	2026-09-20 22:58:50.498206
305	rol_permiso	DELETE	\N	11	{"id_rol": 1, "id_modulo": 1, "id_accion_permiso": 7}	\N	Eliminación de registro en rol_permiso	2026-09-20 22:58:50.498206
306	rol_permiso	DELETE	\N	11	{"id_rol": 1, "id_modulo": 8, "id_accion_permiso": 4}	\N	Eliminación de registro en rol_permiso	2026-09-20 22:58:50.498206
307	rol_permiso	DELETE	\N	11	{"id_rol": 1, "id_modulo": 1, "id_accion_permiso": 4}	\N	Eliminación de registro en rol_permiso	2026-09-20 22:58:50.498206
308	rol_permiso	INSERT	\N	11	\N	{"id_rol": 1, "id_modulo": 1, "id_accion_permiso": 1}	Creación de registro en rol_permiso	2026-09-20 22:58:50.498206
309	rol_permiso	INSERT	\N	11	\N	{"id_rol": 1, "id_modulo": 8, "id_accion_permiso": 1}	Creación de registro en rol_permiso	2026-09-20 22:58:50.498206
310	rol_permiso	INSERT	\N	11	\N	{"id_rol": 1, "id_modulo": 18, "id_accion_permiso": 1}	Creación de registro en rol_permiso	2026-09-20 22:58:50.498206
311	rol_permiso	INSERT	\N	11	\N	{"id_rol": 1, "id_modulo": 1, "id_accion_permiso": 2}	Creación de registro en rol_permiso	2026-09-20 22:58:50.498206
312	rol_permiso	INSERT	\N	11	\N	{"id_rol": 1, "id_modulo": 8, "id_accion_permiso": 2}	Creación de registro en rol_permiso	2026-09-20 22:58:50.498206
313	rol_permiso	INSERT	\N	11	\N	{"id_rol": 1, "id_modulo": 1, "id_accion_permiso": 3}	Creación de registro en rol_permiso	2026-09-20 22:58:50.498206
314	rol_permiso	INSERT	\N	11	\N	{"id_rol": 1, "id_modulo": 8, "id_accion_permiso": 3}	Creación de registro en rol_permiso	2026-09-20 22:58:50.498206
315	rol_permiso	INSERT	\N	11	\N	{"id_rol": 1, "id_modulo": 1, "id_accion_permiso": 4}	Creación de registro en rol_permiso	2026-09-20 22:58:50.498206
316	rol_permiso	INSERT	\N	11	\N	{"id_rol": 1, "id_modulo": 8, "id_accion_permiso": 4}	Creación de registro en rol_permiso	2026-09-20 22:58:50.498206
317	rol_permiso	INSERT	\N	11	\N	{"id_rol": 1, "id_modulo": 18, "id_accion_permiso": 5}	Creación de registro en rol_permiso	2026-09-20 22:58:50.498206
318	rol_permiso	INSERT	\N	11	\N	{"id_rol": 1, "id_modulo": 1, "id_accion_permiso": 7}	Creación de registro en rol_permiso	2026-09-20 22:58:50.498206
319	rol_permiso	INSERT	\N	11	\N	{"id_rol": 1, "id_modulo": 8, "id_accion_permiso": 7}	Creación de registro en rol_permiso	2026-09-20 22:58:50.498206
320	usuario	UPDATE	11	11	{"correo": "jaidermontano69@gmail.com", "estado": 1, "id_rol": 4, "nombre": "Jaider Alexis", "apellido": "Montaño Mondragon", "creado_en": "2026-09-20T20:08:34.364933", "documento": "1109545511", "contrasena": "$2y$10$A9JiXPBSpciUEFKZnH3yUu1Pzjwe009WeQFjgK376EHbJJSdLZlX6", "id_usuario": 11, "token_expira": null, "bloqueo_hasta": null, "id_tipodocumento": 1, "intentos_fallidos": 0, "token_recuperacion": null}	{"correo": "jaidermontano69@gmail.com", "estado": 1, "id_rol": 4, "nombre": "Jaider Alexis", "apellido": "Montaño Mondragon", "creado_en": "2026-09-20T20:08:34.364933", "documento": "1109545511", "contrasena": "$2y$10$A9JiXPBSpciUEFKZnH3yUu1Pzjwe009WeQFjgK376EHbJJSdLZlX6", "id_usuario": 11, "token_expira": null, "bloqueo_hasta": null, "id_tipodocumento": 1, "intentos_fallidos": 0, "token_recuperacion": null}	Actualización de registro en usuario	2026-09-21 06:10:40.815283
321	login	LOGIN_EXITOSO	11	11	\N	{"correo": "jaidermontano69@gmail.com", "exitoso": true}	Inicio de sesión exitoso	2026-09-21 06:10:40.917799
322	usuario	UPDATE	11	11	{"correo": "jaidermontano69@gmail.com", "estado": 1, "id_rol": 4, "nombre": "Jaider Alexis", "apellido": "Montaño Mondragon", "creado_en": "2026-09-20T20:08:34.364933", "documento": "1109545511", "contrasena": "$2y$10$A9JiXPBSpciUEFKZnH3yUu1Pzjwe009WeQFjgK376EHbJJSdLZlX6", "id_usuario": 11, "token_expira": null, "bloqueo_hasta": null, "id_tipodocumento": 1, "intentos_fallidos": 0, "token_recuperacion": null}	{"correo": "jaidermontano69@gmail.com", "estado": 1, "id_rol": 4, "nombre": "Jaider Alexis", "apellido": "Montaño Mondragon", "creado_en": "2026-09-20T20:08:34.364933", "documento": "1109545511", "contrasena": "$2y$10$A9JiXPBSpciUEFKZnH3yUu1Pzjwe009WeQFjgK376EHbJJSdLZlX6", "id_usuario": 11, "token_expira": null, "bloqueo_hasta": null, "id_tipodocumento": 1, "intentos_fallidos": 0, "token_recuperacion": null}	Actualización de registro en usuario	2026-09-21 06:21:40.402859
323	login	LOGIN_EXITOSO	11	11	\N	{"correo": "jaidermontano69@gmail.com", "exitoso": true}	Inicio de sesión exitoso	2026-09-21 06:21:40.517417
324	usuario	UPDATE	11	\N	{"correo": "jaidermontano69@gmail.com", "estado": 1, "id_rol": 4, "nombre": "Jaider Alexis", "apellido": "Montaño Mondragon", "creado_en": "2026-09-20T20:08:34.364933", "documento": "1109545511", "contrasena": "$2y$10$A9JiXPBSpciUEFKZnH3yUu1Pzjwe009WeQFjgK376EHbJJSdLZlX6", "id_usuario": 11, "token_expira": null, "bloqueo_hasta": null, "id_tipodocumento": 1, "intentos_fallidos": 0, "token_recuperacion": null}	{"correo": "jaidermontano69@gmail.com", "estado": 1, "id_rol": 4, "nombre": "Jaider Alexis", "apellido": "Montaño Mondragon", "creado_en": "2026-09-20T20:08:34.364933", "documento": "1109545511", "contrasena": "$2y$10$A9JiXPBSpciUEFKZnH3yUu1Pzjwe009WeQFjgK376EHbJJSdLZlX6", "id_usuario": 11, "token_expira": "2026-09-21T11:47:35", "bloqueo_hasta": null, "id_tipodocumento": 1, "intentos_fallidos": 0, "token_recuperacion": "782873"}	Actualización de registro en usuario	2026-09-21 06:32:35.900144
325	usuario	UPDATE	11	\N	{"correo": "jaidermontano69@gmail.com", "estado": 1, "id_rol": 4, "nombre": "Jaider Alexis", "apellido": "Montaño Mondragon", "creado_en": "2026-09-20T20:08:34.364933", "documento": "1109545511", "contrasena": "$2y$10$A9JiXPBSpciUEFKZnH3yUu1Pzjwe009WeQFjgK376EHbJJSdLZlX6", "id_usuario": 11, "token_expira": "2026-09-21T11:47:35", "bloqueo_hasta": null, "id_tipodocumento": 1, "intentos_fallidos": 0, "token_recuperacion": "782873"}	{"correo": "jaidermontano69@gmail.com", "estado": 1, "id_rol": 4, "nombre": "Jaider Alexis", "apellido": "Montaño Mondragon", "creado_en": "2026-09-20T20:08:34.364933", "documento": "1109545511", "contrasena": "$2y$10$A9JiXPBSpciUEFKZnH3yUu1Pzjwe009WeQFjgK376EHbJJSdLZlX6", "id_usuario": 11, "token_expira": "2026-09-21T11:49:17", "bloqueo_hasta": null, "id_tipodocumento": 1, "intentos_fallidos": 0, "token_recuperacion": "571404"}	Actualización de registro en usuario	2026-09-21 06:34:17.481274
326	usuario	UPDATE	11	\N	{"correo": "jaidermontano69@gmail.com", "estado": 1, "id_rol": 4, "nombre": "Jaider Alexis", "apellido": "Montaño Mondragon", "creado_en": "2026-09-20T20:08:34.364933", "documento": "1109545511", "contrasena": "$2y$10$A9JiXPBSpciUEFKZnH3yUu1Pzjwe009WeQFjgK376EHbJJSdLZlX6", "id_usuario": 11, "token_expira": "2026-09-21T11:49:17", "bloqueo_hasta": null, "id_tipodocumento": 1, "intentos_fallidos": 0, "token_recuperacion": "571404"}	{"correo": "jaidermontano69@gmail.com", "estado": 1, "id_rol": 4, "nombre": "Jaider Alexis", "apellido": "Montaño Mondragon", "creado_en": "2026-09-20T20:08:34.364933", "documento": "1109545511", "contrasena": "$2y$10$Ws0ZH0JJco6PXBJEz/n.ceXI57rd6n2sKAj.I3EgDoaeY7EHM/x7i", "id_usuario": 11, "token_expira": null, "bloqueo_hasta": null, "id_tipodocumento": 1, "intentos_fallidos": 0, "token_recuperacion": null}	Actualización de registro en usuario	2026-09-21 06:34:43.841215
327	usuario	UPDATE	11	11	{"correo": "jaidermontano69@gmail.com", "estado": 1, "id_rol": 4, "nombre": "Jaider Alexis", "apellido": "Montaño Mondragon", "creado_en": "2026-09-20T20:08:34.364933", "documento": "1109545511", "contrasena": "$2y$10$Ws0ZH0JJco6PXBJEz/n.ceXI57rd6n2sKAj.I3EgDoaeY7EHM/x7i", "id_usuario": 11, "token_expira": null, "bloqueo_hasta": null, "id_tipodocumento": 1, "intentos_fallidos": 0, "token_recuperacion": null}	{"correo": "jaidermontano69@gmail.com", "estado": 1, "id_rol": 4, "nombre": "Jaider Alexis", "apellido": "Montaño Mondragon", "creado_en": "2026-09-20T20:08:34.364933", "documento": "1109545511", "contrasena": "$2y$10$Ws0ZH0JJco6PXBJEz/n.ceXI57rd6n2sKAj.I3EgDoaeY7EHM/x7i", "id_usuario": 11, "token_expira": null, "bloqueo_hasta": null, "id_tipodocumento": 1, "intentos_fallidos": 0, "token_recuperacion": null}	Actualización de registro en usuario	2026-09-21 06:35:15.180061
328	login	LOGIN_EXITOSO	11	11	\N	{"correo": "jaidermontano69@gmail.com", "exitoso": true}	Inicio de sesión exitoso	2026-09-21 06:35:15.235643
329	usuario	UPDATE	11	11	{"correo": "jaidermontano69@gmail.com", "estado": 1, "id_rol": 4, "nombre": "Jaider Alexis", "apellido": "Montaño Mondragon", "creado_en": "2026-09-20T20:08:34.364933", "documento": "1109545511", "contrasena": "$2y$10$Ws0ZH0JJco6PXBJEz/n.ceXI57rd6n2sKAj.I3EgDoaeY7EHM/x7i", "id_usuario": 11, "token_expira": null, "bloqueo_hasta": null, "id_tipodocumento": 1, "intentos_fallidos": 0, "token_recuperacion": null}	{"correo": "jaidermontano69@gmail.com", "estado": 1, "id_rol": 4, "nombre": "Jaider Alexis", "apellido": "Montaño Mondragon", "creado_en": "2026-09-20T20:08:34.364933", "documento": "1109545511", "contrasena": "$2y$10$Ws0ZH0JJco6PXBJEz/n.ceXI57rd6n2sKAj.I3EgDoaeY7EHM/x7i", "id_usuario": 11, "token_expira": null, "bloqueo_hasta": null, "id_tipodocumento": 1, "intentos_fallidos": 0, "token_recuperacion": null}	Actualización de registro en usuario	2026-09-21 06:41:26.813244
330	direccion	INSERT	9	11	\N	{"latitud": null, "longitud": null, "direccion": "Diagonal oeste # 4 -20", "id_barrio": 203, "id_ciudad": 1, "id_comuna": 1, "id_direccion": 9, "id_departamento": 1, "id_nomenclatura": 4}	Creación de registro en direccion	2026-09-21 07:09:39.574229
331	sitio	INSERT	8	11	\N	{"fecha": "2026-09-21T07:09:39.574229", "estado": 1, "nombre": "san lopez", "id_sitio": 8, "descripcion": "Es una información de prueba", "id_direccion": 9}	Creación de registro en sitio	2026-09-21 07:09:39.574229
332	seguimiento_deposito	INSERT	7	11	\N	{"fecha": "2026-09-21", "estado": 1, "creado_en": "2026-09-21T07:12:18.56931", "id_usuario": 11, "id_deposito": 5, "id_actividad": 40, "observaciones": null, "presencia_larvas": 1, "numero_peces_sembrados": 23, "id_seguimiento_deposito": 7}	Creación de registro en seguimiento_deposito	2026-09-21 07:12:18.56931
333	usuario	UPDATE	11	11	{"correo": "jaidermontano69@gmail.com", "estado": 1, "id_rol": 4, "nombre": "Jaider Alexis", "apellido": "Montaño Mondragon", "creado_en": "2026-09-20T20:08:34.364933", "documento": "1109545511", "contrasena": "$2y$10$Ws0ZH0JJco6PXBJEz/n.ceXI57rd6n2sKAj.I3EgDoaeY7EHM/x7i", "id_usuario": 11, "token_expira": null, "bloqueo_hasta": null, "id_tipodocumento": 1, "intentos_fallidos": 0, "token_recuperacion": null}	{"correo": "jaidermontano79@gmail.com", "estado": 1, "id_rol": 4, "nombre": "Jaider Alexis", "apellido": "Montaño Mondragon", "creado_en": "2026-09-20T20:08:34.364933", "documento": "1109545511", "contrasena": "$2y$10$Ws0ZH0JJco6PXBJEz/n.ceXI57rd6n2sKAj.I3EgDoaeY7EHM/x7i", "id_usuario": 11, "token_expira": null, "bloqueo_hasta": null, "id_tipodocumento": 1, "intentos_fallidos": 0, "token_recuperacion": null}	Actualización de registro en usuario	2026-09-21 07:13:40.599347
334	usuario	INSERT	12	11	\N	{"correo": "jaidermontano69@gmail.com", "estado": 1, "id_rol": 4, "nombre": "miguel", "apellido": "tovar", "creado_en": "2026-09-21T07:16:58.88209", "documento": "110954518", "contrasena": "$2y$10$EhBtKOeKj.ESYyC28mI2MOPD2HcIUkIzRkxTBCv3nLEDkpxcCLfpm", "id_usuario": 12, "token_expira": null, "bloqueo_hasta": null, "id_tipodocumento": 1, "intentos_fallidos": 0, "token_recuperacion": null}	Creación de registro en usuario	2026-09-21 07:16:58.88209
\.


--
-- Data for Name: auditoria_seguimiento_zoocriadero; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.auditoria_seguimiento_zoocriadero (id_auditoria, id_seguimiento, id_usuario, accion, fecha_accion, hora_accion, fecha_hora_registro, detalle) FROM stdin;
\.


--
-- Data for Name: barrio; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.barrio (id_barrio, id_comuna, nombre) FROM stdin;
1	1	Terrón Colorado
2	2	Floralia
3	3	El Poblado II
4	4	El Vergel
5	5	El Retiro
6	6	Desepaz
7	7	Arboledas
8	2	Ciudadela Floralia
9	22	El Paraíso
10	22	Julio Rincón
11	16	Junín
12	13	El Troncal
13	15	San Pascual
14	4	Manuela Beltrán
15	2	Petecuy II
16	13	Las Américas
17	7	Chipichape
18	17	Urbanización Río Lili
19	15	San Juan Bosco
20	12	Unión de Vivienda Popular
21	20	Departamental
22	11	La Esmeralda
23	14	Veinte de Julio
24	8	Francisco Eladio Ramírez
25	10	La Rivera I
26	11	Las Delicias
27	7	Granada
28	18	Lili
29	10	Villa del Sol
30	15	Santa Rosa
31	6	Villa del Lago
32	7	Altos de Menga
33	3	Omar Torrijos
34	7	Vipasa
35	2	Paso del Comercio
36	7	La Campiña
37	9	Puerto Nuevo
38	1	Patio Bonito
39	19	Pampalinda
40	13	Santa Mónica Popular
41	4	Alfonso Bonilla Aragón
42	7	San Vicente
43	11	Jorge Isaacs
44	3	Yira Castro
45	18	Ciudad Capri
46	11	Bueno Madrid
47	10	Paseo de los Almendros
48	12	Brisas del Limonar
49	20	Pasoancho
50	7	El Bosque
51	21	Lleras Camargo
52	18	La Hacienda
53	15	San Antonio
54	15	Navarro
55	8	Meléndez
56	2	Puente del Comercio
57	5	El Vallado
58	5	Mojica
59	21	El Cortijo
60	13	Villa Colombia
61	13	Atanasio Girardot
62	2	Ciudad Los Álamos
63	4	José Manuel Marroquín I
64	13	La Floresta
65	13	La Base
66	14	José Holguín Garcés
67	21	Brisas de Mayo
68	7	Juanambú
69	14	Bellavista
70	9	Alfonso López I
71	7	Normandía
72	11	Olaya Herrera
73	11	Evaristo García
74	20	Cristóbal Colón
75	19	El Refugio
76	12	La Alborada
77	4	Los Naranjos
78	6	Valle Grande
79	10	Chiminangos Primera Etapa
80	15	Los Libertadores
81	8	Prados del Sur
82	5	Brisas del Bosque
83	10	Villa del Prado
84	14	San Benito
85	18	El Limonar
86	9	Siete de Agosto
87	11	El Porvenir
88	8	Caldas
89	8	Buenos Aires
90	19	Los Cámbulos
91	7	Centenario
92	18	Mayapán
93	4	Las Orquídeas
94	4	Promociones Populares
95	10	Los Andes
96	4	Puertas del Sol
97	8	Alto Jordán
98	19	El Cedro
99	18	Las Quintas de Don Simón
100	15	San Pedro
101	21	Siloé
102	6	Los Líderes
103	11	Fátima
104	6	Pizamos II
105	21	Tierra Blanca
106	17	Alférez Real
107	16	Alameda
108	11	Ignacio Rengifo
109	15	San Nicolás
110	12	Antonio Nariño
111	18	El Caney
112	3	Lleras Restrepo
113	5	Ciudad Córdoba
114	6	Calimio Desepaz
115	8	Los Chorros
116	9	Base Aérea
117	19	Nueva Tequendama
118	18	El Gran Limonar
119	20	Santa Isabel
120	3	Ulpiano Lloreda
121	3	Ricardo Balcázar
122	11	Flora Industrial
123	20	Jorge Zawadsky
124	7	Versalles
125	12	República de Israel
126	6	Ciudadela del Río
127	11	Calima
128	2	Calimio Norte
129	6	Remansos de Comfandi
130	8	Lourdes
131	17	Jockey Club
132	1	Sector Altos de Aguacatal
133	22	Eduardo Santos
134	4	Alirio Mora Beltrán
135	2	La Rivera II
136	13	El Trébol
137	10	Ciudadela Comfandi
138	10	Torres de Comfandi
139	11	La Sultana
140	6	Potrero Grande
141	8	Nápoles
142	22	Bello Horizonte
143	19	El Templete
144	14	Primavera
145	22	Alfonso Barberena
146	11	Berlín
147	19	Champagnat
148	2	San Luis
149	19	San Fernando Viejo
150	6	Compartir
151	3	Villablanca
152	6	Pizamos I
153	9	Fepicol
154	13	Benjamín Herrera
155	20	El Dorado
156	1	Vista Hermosa
157	18	Las Vegas
158	1	Aguacatal
159	14	Boyacá
160	15	La Merced
161	14	La Esperanza
162	18	Bosques del Limonar
163	17	Parcelaciones de Pance
164	8	Mario Correa Rengifo
165	3	El Vergel
166	21	La Sultana Ladera
167	14	Fenalco Kennedy
168	19	Cuarto de Legua
169	3	Los Robles
170	18	Ciudad 2000
171	22	Nueva Floresta
172	14	Maracaibo
173	22	Villa del Sur
174	2	Los Guaduales
175	5	El Morichal
176	16	Manuel María Buenaventura
177	19	Miraflores
178	10	Metropolitano del Norte
179	9	Alfonso López II
180	17	Cañasgordas
181	22	Doce de Octubre
182	12	Mariano Ramos
183	19	Bosque Municipal
184	16	Guayaquil
185	11	Santander
186	20	Colseguros
187	16	Aranjuez
188	7	Santa Mónica
189	14	San Pedro Claver
190	7	Prados del Norte
191	22	San Judas Tadeo II
192	11	La Isla
193	2	Petecuy I
194	15	El Piloto
195	2	Petecuy III
196	7	Santa Rita
197	8	Polvorines
198	17	Ciudad Campestre
199	20	San Cristóbal
200	7	La Flora
201	5	Comuneros I
202	2	Jorge Eliécer Gaitán
203	1	Bajo Aguacatal
204	14	Las Granjas
205	7	Menga
206	11	Manzanares
207	11	Bolivariano
208	19	Camino Real
209	2	Sindical
210	15	El Nacional
211	20	La Selva
212	15	San Cayetano
213	3	Rodrigo Lara Bonilla
214	21	Pueblo Joven
215	5	Comuneros II
216	22	El Rodeo
217	17	Pance
218	17	Ciudad Jardín
219	16	Belalcázar
220	18	Primero de Mayo
221	9	Puerto Mallarino
222	9	Alfonso López III
223	21	Belén
224	9	Los Pinos
225	13	Saavedra Galindo
226	3	Charco Azul
227	8	Horizontes
228	20	Olímpico
229	16	Bretaña
230	9	Parque de la Caña
231	21	Cementerio Carabineros
232	4	José Manuel Marroquín II
233	18	Ciudad Universitaria
234	3	Calipso
235	16	Sucre
236	19	Tequendama
237	2	Los Alcázares
238	19	El Lido
239	14	La Ferroviaria
240	6	Pizamos III
241	11	Salomia
242	13	Simón Bolívar
243	19	San Fernando Nuevo
244	13	Primitivo Crespo
245	5	Laureano Gómez
246	8	Colinas del Sur
247	14	El Guabal
248	13	Chapinero
249	9	San Marino
250	6	Las Dalias
251	14	La Libertad
252	8	Alto Nápoles
253	15	El Calvario
254	14	Villanueva
255	10	El Sena
256	22	Asturias
257	22	San Judas Tadeo I
258	13	Municipal
259	19	El Ingenio
260	13	Santa Fe
261	15	El Hoyo
262	20	Panamericano
263	13	Industrial
264	11	Guillermo Valencia
265	3	El Poblado I
266	16	Barrio Obrero
267	10	Los Guayacanes
268	10	Chiminangos Segunda Etapa
269	20	Las Acacias
\.


--
-- Data for Name: ciudad; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.ciudad (id_ciudad, id_comuna, nombre) FROM stdin;
1	1	Santiago de Cali
\.


--
-- Data for Name: comuna; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.comuna (id_comuna, nombre) FROM stdin;
1	Comuna 1
2	Comuna 6
3	Comuna 13
4	Comuna 14
5	Comuna 15
6	Comuna 21
7	Comuna 2
8	Comuna 18
9	Comuna 7
10	Comuna 5
11	Comuna 4
12	Comuna 16
13	Comuna 8
14	Comuna 11
15	Comuna 3
16	Comuna 9
17	Comuna 22
18	Comuna 17
19	Comuna 19
20	Comuna 10
21	Comuna 20
22	Comuna 12
\.


--
-- Data for Name: copia_seguridad_historial; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.copia_seguridad_historial (id_historial, fecha_hora, tipo_operacion, nombre_archivo, id_usuario, usuario_nombre, estado, detalle) FROM stdin;
1	2026-09-20 19:59:09.806133	descarga	bd_dengue_siguppy_20260920_195908.sql	2	miguel tovar	exito	\N
2	2026-09-20 19:59:36.249545	automatica	bd_dengue_siguppy_auto_20260921_005934.sql	\N	Sistema (automático)	exito	\N
\.


--
-- Data for Name: departamento; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.departamento (id_departamento, id_ciudad, nombre) FROM stdin;
1	1	Valle del Cauca
\.


--
-- Data for Name: deposito; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.deposito (id_deposito, id_tipo_deposito, id_sitio, descripcion, estado) FROM stdin;
1	1	1	Sumidero con agua estancada y presencia de larvas	1
2	2	2	Llantas abandonadas en lote baldío	1
3	1	3	Alcantarilla tapada frente a parque	1
4	3	4	Tanque bajo en vivienda sin tapa	1
5	5	5	Charco permanente por fuga de agua	1
6	2	6	Depósito de caucho en taller mecánico	1
\.


--
-- Data for Name: direccion; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.direccion (id_direccion, id_departamento, id_comuna, id_ciudad, id_barrio, direccion, id_nomenclatura, latitud, longitud) FROM stdin;
1	1	1	1	1	Calle 12 Oeste # 4-20	1	3.45120000	-76.54890000
2	1	2	1	2	Carrera 4N # 72 C-10	2	3.48910000	-76.49820000
3	1	3	1	3	Calle 72 # 28D-19	1	3.42478140	-76.48252102
4	1	4	1	4	Carrera 32 # 48-12	2	3.41820000	-76.51030000
5	1	5	1	5	Calle 52 # 39-05	1	3.41010000	-76.50520000
6	1	6	1	6	Carrera 23 # 120-45	2	3.43500000	-76.47110000
8	1	1	1	158	Calle 12 norte 23 #1 2	1	\N	\N
9	1	1	1	203	Diagonal oeste # 4 -20	4	\N	\N
\.


--
-- Data for Name: modulo; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.modulo (id_modulo, nombre, descripcion) FROM stdin;
1	Zoocriaderos	Gestión de tanques, peces y nacimientos
5	Auditoría	Historial de movimientos de usuarios
7	Actividades	CRUD de actividades de terreno
8	Seguimiento de Zoocriadero	Formulario de seguimiento de un zoocriadero
9	Tipo Depósitos	CRUD de tipos de depósito
10	Sitio	Preparado: aún sin Controller/View, tabla "sitio" ya existe en la BD
11	Acciones de Zoocriadero	Registro de acciones sobre tanques de un zoocriadero
12	Depósitos	CRUD de depósitos de terreno
13	Territorio priorizado	Preparado: aún sin Controller/View, tabla "territorio_priorizado" ya existe en la BD
17	Copia de seguridad	Respaldo de la base de datos
18	Reportes	Reportes y gráficos del sistema
19	Gestión de Usuarios	Creación de roles y asignación de permisos
20	Tanque Zoocriadero	Gestión de tanques del zoocriadero
21	Roles y Permisos	Gestión de roles y sus permisos por módulo
22	Consultar Roles	Revisar la información detallada de la funcion de los roles
23	Consultar Usuarios	Consulta de usuarios: ver, editar e inhabilitar
25	Seguimiento de Depósito	Visitas de seguimiento a un depósito y su ubicación en el mapa
\.


--
-- Data for Name: modulo_accion_permitida; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.modulo_accion_permitida (id_modulo, id_accion_permiso) FROM stdin;
23	1
22	1
21	1
20	1
19	1
18	1
17	1
13	1
12	1
11	1
10	1
9	1
8	1
7	1
5	1
1	1
21	2
19	2
13	2
12	2
11	2
10	2
9	2
8	2
7	2
1	2
23	3
20	3
19	3
13	3
12	3
11	3
10	3
9	3
8	3
7	3
1	3
18	5
20	7
19	7
13	7
12	7
11	7
10	7
9	7
8	7
7	7
1	7
23	4
22	4
20	4
19	4
13	4
12	4
11	4
10	4
9	4
8	4
7	4
1	4
20	2
25	1
25	2
25	3
25	4
25	7
\.


--
-- Data for Name: nomenclatura; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.nomenclatura (id_nomenclatura, nomenclatura) FROM stdin;
1	Calle
2	Carrera
3	Avenida
4	Diagonal
5	Transversal
\.


--
-- Data for Name: rol; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.rol (id_rol, nombre_rol, descripcion, estado) FROM stdin;
4	Super Administrador	Administra la base de datos y la configuración del sistema	1
2	Administrador	Director(a) del Grupo ETV	1
3	Auxiliar	Personal de campo	1
1	Coordinador	Coordina control biológico	1
\.


--
-- Data for Name: rol_permiso; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.rol_permiso (id_rol, id_modulo, id_accion_permiso) FROM stdin;
2	23	1
2	22	1
2	21	1
2	19	1
2	11	1
2	7	1
2	21	2
2	19	2
2	11	2
2	7	2
2	23	3
2	19	3
2	11	3
2	7	3
2	19	7
2	11	7
2	7	7
2	23	4
2	22	4
2	19	4
2	11	4
2	7	4
4	23	1
4	22	1
4	21	1
4	20	1
4	19	1
4	18	1
4	17	1
4	12	1
4	11	1
4	10	1
4	9	1
4	8	1
4	7	1
4	5	1
4	1	1
4	21	2
4	19	2
4	12	2
4	11	2
4	10	2
4	9	2
4	8	2
4	7	2
4	1	2
4	23	3
4	20	3
4	19	3
4	12	3
4	11	3
4	10	3
4	9	3
4	8	3
4	7	3
4	1	3
4	18	5
4	20	7
4	19	7
4	12	7
4	11	7
4	10	7
4	9	7
4	8	7
4	7	7
4	1	7
4	23	4
4	22	4
4	20	4
4	19	4
4	12	4
4	11	4
4	10	4
4	9	4
4	8	4
4	7	4
4	1	4
4	20	2
4	25	1
4	25	2
4	25	3
4	25	7
4	25	4
3	1	1
3	7	1
3	8	1
3	9	1
3	10	1
3	12	1
3	20	1
3	25	1
3	1	2
3	7	2
3	8	2
3	9	2
3	10	2
3	12	2
3	20	2
3	25	2
3	1	3
3	7	3
3	8	3
3	9	3
3	10	3
3	12	3
3	20	3
3	25	3
3	20	4
3	1	7
3	7	7
3	8	7
3	9	7
3	10	7
3	12	7
3	20	7
3	25	7
1	1	1
1	8	1
1	18	1
1	1	2
1	8	2
1	1	3
1	8	3
1	1	4
1	8	4
1	18	5
1	1	7
1	8	7
\.


--
-- Data for Name: seguimiento_deposito; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.seguimiento_deposito (id_seguimiento_deposito, id_deposito, id_usuario, id_actividad, fecha, presencia_larvas, numero_peces_sembrados, observaciones, estado, creado_en) FROM stdin;
6	6	11	38	2026-09-20	1	0	Se visualizarón mas larvas de lo normal	1	2026-09-20 22:51:27.727257
7	5	11	40	2026-09-21	1	23	\N	1	2026-09-21 07:12:18.56931
\.


--
-- Data for Name: seguimiento_terreno; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.seguimiento_terreno (id_seguimiento_terreno, id_sitio, id_usuario, fecha, estado) FROM stdin;
\.


--
-- Data for Name: seguimiento_zoocriadero; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.seguimiento_zoocriadero (id_seguimiento, id_zoocriadero, id_tanque, id_usuario, fecha, ph, temperatura, numero_sembrados, numero_nacidos, numero_nacidos_hembra, numero_nacidos_macho, numero_muertos, numero_muertos_hembra, numero_muertos_macho, observaciones, estado) FROM stdin;
1	1	1	1	2026-09-18	\N	\N	0	0	0	0	0	0	0	Comieron bien	1
2	1	1	1	2026-09-20	\N	\N	0	0	0	0	0	0	0	comen muy bien	1
3	1	1	1	2026-09-20	\N	\N	0	0	0	0	0	0	0	comen mucho y muy bien	1
\.


--
-- Data for Name: sitio; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.sitio (id_sitio, id_direccion, estado, fecha, nombre, descripcion) FROM stdin;
1	1	1	2026-09-17 17:18:23.754599	Sumidero / Alcantarilla	Depósito de aguas pluviales en vía pública
2	2	1	2026-09-17 17:18:23.754599	Lanta / Neumático desechado	Depósito artificial a la intemperie
3	3	1	2026-09-17 17:18:23.754599	Sumidero / Alcantarilla	Depósito de aguas pluviales en vía pública
4	4	1	2026-09-17 17:18:23.754599	Tanque de Agua Potable Destapado	Depósito doméstico residencial
5	5	1	2026-09-17 17:18:23.754599	Charco / Charca estancada	Acumulación natural en vía pública
6	6	1	2026-09-17 17:18:23.754599	Lanta / Neumático desechado	Depósito artificial a la intemperie
7	8	1	2026-09-20 12:04:22.773839	Cerca	Floreros de cementerio o de casa
8	9	1	2026-09-21 07:09:39.574229	san lopez	Es una información de prueba
\.


--
-- Data for Name: tanque; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.tanque (id_tanque, id_zoocriadero, id_tipo_tanque, numero_tanque, estado) FROM stdin;
1	1	10	1	1
2	2	7	1	1
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
1	Sumidero / Alcantarilla	Depósito de aguas pluviales en vía pública	1
2	Lanta / Neumático desechado	Depósito artificial a la intemperie	1
3	Tanque de Agua Potable Destapado	Depósito doméstico residencial	1
4	Florero / Maceta	Recipiente doméstico menor	1
5	Charco / Charca estancada	Acumulación natural en vía pública	1
6	Tanque bajo	Tanque de almacenamiento a nivel de piso	1
7	Canaleta	Canaleta obstruida con agua estancada	1
8	Inservible	Recipiente inservible acumulador de agua	1
9	Materas	Materas y platos de materas	1
10	Llanta	Llanta a la intemperie	1
11	Floreros	Floreros de cementerio o de casa	1
12	Bebedero de animal	Recipiente de agua para mascotas	1
13	Tanque elevado	Tanque elevado o de azotea	1
14	Alberca	Alberca o lavadero	1
15	Pozo	Pozo o aljibe descubierto	1
\.


--
-- Data for Name: tipo_documento; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.tipo_documento (id_tipodocumento, nombre) FROM stdin;
1	Cédula de Ciudadanía
2	Cédula de Extranjería
3	Tarjeta de Identidad
4	Permiso por Protección Temporal
7	Pasaporte
\.


--
-- Data for Name: tipo_tanque; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.tipo_tanque (id_tipo_tanque, nombre, descripcion, estado) FROM stdin;
1	Tanque de Concreto 500L	Tanque de cría masiva	1
2	Tanque Plástico 250L	Tanque de aclimatación	1
3	Acuario Observación 80L	Monitoreo de alevines	1
4	Tina Fibra 1000L	Tanque de recolección principal	1
5	Geomembrana	Estanque revestido en geomembrana	1
6	Vidrio	Acuario o pecera de vidrio para cría controlada	1
7	Metálico	Tanque metálico con recubrimiento interno	1
8	Fibra de vidrio	Tanque en fibra de vidrio, resistente a la intemperie	1
9	Plástico	Tanque plástico estándar de 500 a 1000 litros	1
10	Concreto	Estanque en concreto construido en sitio	1
11	Eternit	Tanque tipo eternit reutilizado como estanque	1
\.


--
-- Data for Name: usuario; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.usuario (id_usuario, id_tipodocumento, id_rol, nombre, apellido, correo, contrasena, estado, creado_en, documento, intentos_fallidos, bloqueo_hasta, token_recuperacion, token_expira) FROM stdin;
1	1	2	david	gomez	juan@gmail.com	$2y$10$xVZk9DlIgoFE4LfHKwjShuoAS5eEWi9TVLimOklMKTmEvvwfukb2S	1	2026-09-18 21:29:31.045752	1109545513	0	\N	\N	\N
3	1	4	miguel tovar	sol	tovar232@gmail.com	$2y$10$vkcZBDuN66/hpyx60945p.d2LVcx7Y4yqaXCXYmIxPiCZl8GMWVYO	1	2026-09-19 12:37:25.003653	1109541234	0	\N	\N	\N
8	1	2	Administrador	Temporal	adminstradortempral@gmail.com	Siguppy#Temp2026	1	2026-09-20 19:05:55.527268	900000002	0	\N	\N	\N
7	1	3	Auxiliar	Temporal	auxiliartemporal@gmail.com	Siguppy#Temp2026	1	2026-09-20 19:05:55.527268	900000001	0	\N	\N	\N
9	1	1	Coordinador	Temporal	coordinadortemporal@gmail.com	Siguppy#Temp2026	1	2026-09-20 19:05:55.527268	900000003	0	\N	\N	\N
10	1	4	Super	Administrador Temporal	superadministradortemporal@gmail.com	Siguppy#Temp2026	1	2026-09-20 19:05:55.527268	900000004	0	\N	\N	\N
2	1	2	miguel	tovar	migueltovar69@gmail.com	$2y$10$SlVYZEBG3aDdWZM4JEr1feLl/RTyEtbVeM3meBB6b6sUbr2jm.v.W	1	2026-09-19 10:47:39.645976	1109232423	0	\N	017888	2026-09-19 23:11:16
4	1	3	yeisen	arroyo ocho	nicolito@gmail.com	$2y$10$OO/LmIq6RVW2HIP5P4mQIuff3Pxb5bJ9zRWLgQJu4gKXK9XPPGzjK	1	2026-09-20 15:40:07.356712	106565158	0	\N	\N	\N
11	1	4	Jaider Alexis	Montaño Mondragon	jaidermontano79@gmail.com	$2y$10$Ws0ZH0JJco6PXBJEz/n.ceXI57rd6n2sKAj.I3EgDoaeY7EHM/x7i	1	2026-09-20 20:08:34.364933	1109545511	0	\N	\N	\N
12	1	4	miguel	tovar	jaidermontano69@gmail.com	$2y$10$EhBtKOeKj.ESYyC28mI2MOPD2HcIUkIzRkxTBCv3nLEDkpxcCLfpm	1	2026-09-21 07:16:58.88209	110954518	0	\N	\N	\N
\.


--
-- Data for Name: zoocriadero; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.zoocriadero (id_zoocriadero, nombre, direccion, comuna, barrio, id_persona_cargo, latitud, longitud, estado, creado_en) FROM stdin;
1	Zoocriadero Aguablanca	Cra 31 # 22-71	Comuna 6	Ciudadela Floralia	\N	3.49529440	-76.49458530	1	2026-09-18 21:39:27.702052
2	Zoocriadero CDTI	Cra 31 # 22-7123	Comuna 4	Guillermo Valencia	\N	3.49529440	-76.49458530	1	2026-09-18 21:39:28.122364
3	Zoocriadero San Nicolás	Cll 20 # 8-15	Comuna 3	San Nicolás	\N	3.45582560	-76.52281010	1	2026-09-20 11:09:04.860354
4	Zoocriadero Alfonso López	Cra 28 # 45-60	Comuna 7	Alfonso López I	\N	3.46137040	-76.48088930	1	2026-09-20 11:09:59.686689
5	charco azul	Calle 13 # 24-05	Comuna 14	José Manuel Marroquín II	\N	0.00000000	0.00000000	1	2026-09-20 15:43:33.950062
6	7 de agosto	Calle 13 # 24-05	Comuna 15	Comuneros II	\N	0.00000000	0.00000000	1	2026-09-20 16:13:01.35188
\.


--
-- Name: accion_permiso_id_accion_permiso_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.accion_permiso_id_accion_permiso_seq', 7, true);


--
-- Name: actividad_id_actividad_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.actividad_id_actividad_seq', 46, true);


--
-- Name: actividad_terreno_id_actividad_terreno_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.actividad_terreno_id_actividad_terreno_seq', 1, false);


--
-- Name: actividad_zoocriadero_id_actividad_zoocriadero_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.actividad_zoocriadero_id_actividad_zoocriadero_seq', 3, true);


--
-- Name: auditoria_id_auditoria_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.auditoria_id_auditoria_seq', 334, true);


--
-- Name: auditoria_seguimiento_zoocriadero_id_auditoria_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.auditoria_seguimiento_zoocriadero_id_auditoria_seq', 1, false);


--
-- Name: barrio_id_barrio_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.barrio_id_barrio_seq', 269, true);


--
-- Name: ciudad_id_ciudad_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.ciudad_id_ciudad_seq', 1, true);


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

SELECT pg_catalog.setval('public.departamento_id_departamento_seq', 1, true);


--
-- Name: deposito_id_deposito_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.deposito_id_deposito_seq', 6, true);


--
-- Name: direccion_id_direccion_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.direccion_id_direccion_seq', 9, true);


--
-- Name: modulo_id_modulo_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.modulo_id_modulo_seq', 25, true);


--
-- Name: nomenclatura_id_nomenclatura_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.nomenclatura_id_nomenclatura_seq', 5, true);


--
-- Name: rol_id_rol_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.rol_id_rol_seq', 5, true);


--
-- Name: seguimiento_deposito_id_seguimiento_deposito_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.seguimiento_deposito_id_seguimiento_deposito_seq', 7, true);


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

SELECT pg_catalog.setval('public.sitio_id_sitio_seq', 8, true);


--
-- Name: tanque_id_tanque_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.tanque_id_tanque_seq', 2, true);


--
-- Name: territorio_priorizado_id_territorio_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.territorio_priorizado_id_territorio_seq', 1, false);


--
-- Name: tipo_deposito_id_tipo_deposito_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.tipo_deposito_id_tipo_deposito_seq', 15, true);


--
-- Name: tipo_documento_id_tipodocumento_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.tipo_documento_id_tipodocumento_seq', 8, true);


--
-- Name: tipo_tanque_id_tipo_tanque_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.tipo_tanque_id_tipo_tanque_seq', 11, true);


--
-- Name: usuario_id_usuario_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.usuario_id_usuario_seq', 12, true);


--
-- Name: zoocriadero_id_zoocriadero_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.zoocriadero_id_zoocriadero_seq', 6, true);


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
-- Name: auditoria auditoria_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.auditoria
    ADD CONSTRAINT auditoria_pkey PRIMARY KEY (id_auditoria);


--
-- Name: auditoria_seguimiento_zoocriadero auditoria_seguimiento_zoocriadero_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.auditoria_seguimiento_zoocriadero
    ADD CONSTRAINT auditoria_seguimiento_zoocriadero_pkey PRIMARY KEY (id_auditoria);


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
-- Name: deposito deposito_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.deposito
    ADD CONSTRAINT deposito_pkey PRIMARY KEY (id_deposito);


--
-- Name: direccion direccion_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.direccion
    ADD CONSTRAINT direccion_pkey PRIMARY KEY (id_direccion);


--
-- Name: modulo_accion_permitida modulo_accion_permitida_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.modulo_accion_permitida
    ADD CONSTRAINT modulo_accion_permitida_pkey PRIMARY KEY (id_modulo, id_accion_permiso);


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
-- Name: seguimiento_deposito seguimiento_deposito_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.seguimiento_deposito
    ADD CONSTRAINT seguimiento_deposito_pkey PRIMARY KEY (id_seguimiento_deposito);


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
-- Name: usuario usuario_documento_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.usuario
    ADD CONSTRAINT usuario_documento_key UNIQUE (documento);


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
-- Name: idx_auditoria_accion; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_auditoria_accion ON public.auditoria USING btree (accion);


--
-- Name: idx_auditoria_fecha; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_auditoria_fecha ON public.auditoria USING btree (fecha_hora);


--
-- Name: idx_auditoria_modulo; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_auditoria_modulo ON public.auditoria USING btree (modulo);


--
-- Name: idx_auditoria_usuario; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_auditoria_usuario ON public.auditoria USING btree (id_usuario);


--
-- Name: idx_copia_seguridad_fecha; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_copia_seguridad_fecha ON public.copia_seguridad_historial USING btree (fecha_hora DESC);


--
-- Name: idx_seguimiento_deposito_deposito_fecha; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_seguimiento_deposito_deposito_fecha ON public.seguimiento_deposito USING btree (id_deposito, fecha DESC);


--
-- Name: uq_tipo_documento_nombre_ci; Type: INDEX; Schema: public; Owner: postgres
--

CREATE UNIQUE INDEX uq_tipo_documento_nombre_ci ON public.tipo_documento USING btree (lower((nombre)::text));


--
-- Name: actividad trg_auditoria_actividad; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_auditoria_actividad AFTER INSERT OR DELETE OR UPDATE ON public.actividad FOR EACH ROW EXECUTE FUNCTION public.fn_auditoria_generica('id_actividad');


--
-- Name: actividad_terreno trg_auditoria_actividad_terreno; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_auditoria_actividad_terreno AFTER INSERT OR DELETE OR UPDATE ON public.actividad_terreno FOR EACH ROW EXECUTE FUNCTION public.fn_auditoria_generica('id_actividad_terreno');


--
-- Name: actividad_zoocriadero trg_auditoria_actividad_zoocriadero; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_auditoria_actividad_zoocriadero AFTER INSERT OR DELETE OR UPDATE ON public.actividad_zoocriadero FOR EACH ROW EXECUTE FUNCTION public.fn_auditoria_generica('id_actividad_zoocriadero');


--
-- Name: deposito trg_auditoria_deposito; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_auditoria_deposito AFTER INSERT OR DELETE OR UPDATE ON public.deposito FOR EACH ROW EXECUTE FUNCTION public.fn_auditoria_generica('id_deposito');


--
-- Name: direccion trg_auditoria_direccion; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_auditoria_direccion AFTER INSERT OR DELETE OR UPDATE ON public.direccion FOR EACH ROW EXECUTE FUNCTION public.fn_auditoria_generica('id_direccion');


--
-- Name: modulo_accion_permitida trg_auditoria_modulo_accion_permitida; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_auditoria_modulo_accion_permitida AFTER INSERT OR DELETE OR UPDATE ON public.modulo_accion_permitida FOR EACH ROW EXECUTE FUNCTION public.fn_auditoria_generica();


--
-- Name: rol trg_auditoria_rol; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_auditoria_rol AFTER INSERT OR DELETE OR UPDATE ON public.rol FOR EACH ROW EXECUTE FUNCTION public.fn_auditoria_generica('id_rol');


--
-- Name: rol_permiso trg_auditoria_rol_permiso; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_auditoria_rol_permiso AFTER INSERT OR DELETE OR UPDATE ON public.rol_permiso FOR EACH ROW EXECUTE FUNCTION public.fn_auditoria_generica();


--
-- Name: seguimiento_deposito trg_auditoria_seguimiento_deposito; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_auditoria_seguimiento_deposito AFTER INSERT OR DELETE OR UPDATE ON public.seguimiento_deposito FOR EACH ROW EXECUTE FUNCTION public.fn_auditoria_generica('id_seguimiento_deposito');


--
-- Name: seguimiento_terreno trg_auditoria_seguimiento_terreno; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_auditoria_seguimiento_terreno AFTER INSERT OR DELETE OR UPDATE ON public.seguimiento_terreno FOR EACH ROW EXECUTE FUNCTION public.fn_auditoria_generica('id_seguimiento_terreno');


--
-- Name: seguimiento_zoocriadero trg_auditoria_seguimiento_zoocriadero; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_auditoria_seguimiento_zoocriadero AFTER INSERT OR DELETE OR UPDATE ON public.seguimiento_zoocriadero FOR EACH ROW EXECUTE FUNCTION public.fn_auditoria_generica('id_seguimiento');


--
-- Name: sitio trg_auditoria_sitio; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_auditoria_sitio AFTER INSERT OR DELETE OR UPDATE ON public.sitio FOR EACH ROW EXECUTE FUNCTION public.fn_auditoria_generica('id_sitio');


--
-- Name: tanque trg_auditoria_tanque; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_auditoria_tanque AFTER INSERT OR DELETE OR UPDATE ON public.tanque FOR EACH ROW EXECUTE FUNCTION public.fn_auditoria_generica('id_tanque');


--
-- Name: territorio_priorizado trg_auditoria_territorio_priorizado; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_auditoria_territorio_priorizado AFTER INSERT OR DELETE OR UPDATE ON public.territorio_priorizado FOR EACH ROW EXECUTE FUNCTION public.fn_auditoria_generica('id_territorio');


--
-- Name: tipo_deposito trg_auditoria_tipo_deposito; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_auditoria_tipo_deposito AFTER INSERT OR DELETE OR UPDATE ON public.tipo_deposito FOR EACH ROW EXECUTE FUNCTION public.fn_auditoria_generica('id_tipo_deposito');


--
-- Name: usuario trg_auditoria_usuario; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_auditoria_usuario AFTER INSERT OR DELETE OR UPDATE ON public.usuario FOR EACH ROW EXECUTE FUNCTION public.fn_auditoria_generica('id_usuario');


--
-- Name: zoocriadero trg_auditoria_zoocriadero; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_auditoria_zoocriadero AFTER INSERT OR DELETE OR UPDATE ON public.zoocriadero FOR EACH ROW EXECUTE FUNCTION public.fn_auditoria_generica('id_zoocriadero');


--
-- Name: seguimiento_deposito trg_validar_seguimiento_deposito; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_validar_seguimiento_deposito BEFORE INSERT OR UPDATE ON public.seguimiento_deposito FOR EACH ROW EXECUTE FUNCTION public.fn_validar_seguimiento_deposito();


--
-- Name: actividad_terreno actividad_terreno_id_actividad_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.actividad_terreno
    ADD CONSTRAINT actividad_terreno_id_actividad_fkey FOREIGN KEY (id_actividad) REFERENCES public.actividad(id_actividad) DEFERRABLE;


--
-- Name: actividad_terreno actividad_terreno_id_seguimiento_terreno_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.actividad_terreno
    ADD CONSTRAINT actividad_terreno_id_seguimiento_terreno_fkey FOREIGN KEY (id_seguimiento_terreno) REFERENCES public.seguimiento_terreno(id_seguimiento_terreno) DEFERRABLE;


--
-- Name: actividad_zoocriadero actividad_zoocriadero_id_actividad_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.actividad_zoocriadero
    ADD CONSTRAINT actividad_zoocriadero_id_actividad_fkey FOREIGN KEY (id_actividad) REFERENCES public.actividad(id_actividad) DEFERRABLE;


--
-- Name: actividad_zoocriadero actividad_zoocriadero_id_seguimiento_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.actividad_zoocriadero
    ADD CONSTRAINT actividad_zoocriadero_id_seguimiento_fkey FOREIGN KEY (id_seguimiento) REFERENCES public.seguimiento_zoocriadero(id_seguimiento) DEFERRABLE;


--
-- Name: auditoria auditoria_id_usuario_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.auditoria
    ADD CONSTRAINT auditoria_id_usuario_fkey FOREIGN KEY (id_usuario) REFERENCES public.usuario(id_usuario);


--
-- Name: auditoria_seguimiento_zoocriadero auditoria_seguimiento_zoocriadero_id_seguimiento_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.auditoria_seguimiento_zoocriadero
    ADD CONSTRAINT auditoria_seguimiento_zoocriadero_id_seguimiento_fkey FOREIGN KEY (id_seguimiento) REFERENCES public.seguimiento_zoocriadero(id_seguimiento) DEFERRABLE;


--
-- Name: auditoria_seguimiento_zoocriadero auditoria_seguimiento_zoocriadero_id_usuario_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.auditoria_seguimiento_zoocriadero
    ADD CONSTRAINT auditoria_seguimiento_zoocriadero_id_usuario_fkey FOREIGN KEY (id_usuario) REFERENCES public.usuario(id_usuario) DEFERRABLE;


--
-- Name: barrio barrio_id_comuna_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.barrio
    ADD CONSTRAINT barrio_id_comuna_fkey FOREIGN KEY (id_comuna) REFERENCES public.comuna(id_comuna) DEFERRABLE;


--
-- Name: ciudad ciudad_id_comuna_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ciudad
    ADD CONSTRAINT ciudad_id_comuna_fkey FOREIGN KEY (id_comuna) REFERENCES public.comuna(id_comuna) DEFERRABLE;


--
-- Name: departamento departamento_id_ciudad_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.departamento
    ADD CONSTRAINT departamento_id_ciudad_fkey FOREIGN KEY (id_ciudad) REFERENCES public.ciudad(id_ciudad) DEFERRABLE;


--
-- Name: deposito deposito_id_sitio_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.deposito
    ADD CONSTRAINT deposito_id_sitio_fkey FOREIGN KEY (id_sitio) REFERENCES public.sitio(id_sitio) DEFERRABLE;


--
-- Name: deposito deposito_id_tipo_deposito_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.deposito
    ADD CONSTRAINT deposito_id_tipo_deposito_fkey FOREIGN KEY (id_tipo_deposito) REFERENCES public.tipo_deposito(id_tipo_deposito) DEFERRABLE;


--
-- Name: direccion direccion_id_barrio_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.direccion
    ADD CONSTRAINT direccion_id_barrio_fkey FOREIGN KEY (id_barrio) REFERENCES public.barrio(id_barrio) DEFERRABLE;


--
-- Name: direccion direccion_id_ciudad_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.direccion
    ADD CONSTRAINT direccion_id_ciudad_fkey FOREIGN KEY (id_ciudad) REFERENCES public.ciudad(id_ciudad) DEFERRABLE;


--
-- Name: direccion direccion_id_comuna_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.direccion
    ADD CONSTRAINT direccion_id_comuna_fkey FOREIGN KEY (id_comuna) REFERENCES public.comuna(id_comuna) DEFERRABLE;


--
-- Name: direccion direccion_id_departamento_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.direccion
    ADD CONSTRAINT direccion_id_departamento_fkey FOREIGN KEY (id_departamento) REFERENCES public.departamento(id_departamento) DEFERRABLE;


--
-- Name: direccion direccion_id_nomenclatura_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.direccion
    ADD CONSTRAINT direccion_id_nomenclatura_fkey FOREIGN KEY (id_nomenclatura) REFERENCES public.nomenclatura(id_nomenclatura) DEFERRABLE;


--
-- Name: barrio fk_barrio_comuna; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.barrio
    ADD CONSTRAINT fk_barrio_comuna FOREIGN KEY (id_comuna) REFERENCES public.comuna(id_comuna);


--
-- Name: modulo_accion_permitida modulo_accion_permitida_id_accion_permiso_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.modulo_accion_permitida
    ADD CONSTRAINT modulo_accion_permitida_id_accion_permiso_fkey FOREIGN KEY (id_accion_permiso) REFERENCES public.accion_permiso(id_accion_permiso) ON DELETE CASCADE;


--
-- Name: modulo_accion_permitida modulo_accion_permitida_id_modulo_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.modulo_accion_permitida
    ADD CONSTRAINT modulo_accion_permitida_id_modulo_fkey FOREIGN KEY (id_modulo) REFERENCES public.modulo(id_modulo) ON DELETE CASCADE;


--
-- Name: rol_permiso rol_permiso_id_accion_permiso_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rol_permiso
    ADD CONSTRAINT rol_permiso_id_accion_permiso_fkey FOREIGN KEY (id_accion_permiso) REFERENCES public.accion_permiso(id_accion_permiso) DEFERRABLE;


--
-- Name: rol_permiso rol_permiso_id_modulo_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rol_permiso
    ADD CONSTRAINT rol_permiso_id_modulo_fkey FOREIGN KEY (id_modulo) REFERENCES public.modulo(id_modulo) DEFERRABLE;


--
-- Name: rol_permiso rol_permiso_id_rol_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rol_permiso
    ADD CONSTRAINT rol_permiso_id_rol_fkey FOREIGN KEY (id_rol) REFERENCES public.rol(id_rol) DEFERRABLE;


--
-- Name: seguimiento_deposito seguimiento_deposito_id_actividad_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.seguimiento_deposito
    ADD CONSTRAINT seguimiento_deposito_id_actividad_fkey FOREIGN KEY (id_actividad) REFERENCES public.actividad(id_actividad) DEFERRABLE;


--
-- Name: seguimiento_deposito seguimiento_deposito_id_deposito_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.seguimiento_deposito
    ADD CONSTRAINT seguimiento_deposito_id_deposito_fkey FOREIGN KEY (id_deposito) REFERENCES public.deposito(id_deposito) DEFERRABLE;


--
-- Name: seguimiento_deposito seguimiento_deposito_id_usuario_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.seguimiento_deposito
    ADD CONSTRAINT seguimiento_deposito_id_usuario_fkey FOREIGN KEY (id_usuario) REFERENCES public.usuario(id_usuario) DEFERRABLE;


--
-- Name: seguimiento_terreno seguimiento_terreno_id_sitio_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.seguimiento_terreno
    ADD CONSTRAINT seguimiento_terreno_id_sitio_fkey FOREIGN KEY (id_sitio) REFERENCES public.sitio(id_sitio) DEFERRABLE;


--
-- Name: seguimiento_terreno seguimiento_terreno_id_usuario_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.seguimiento_terreno
    ADD CONSTRAINT seguimiento_terreno_id_usuario_fkey FOREIGN KEY (id_usuario) REFERENCES public.usuario(id_usuario) DEFERRABLE;


--
-- Name: seguimiento_zoocriadero seguimiento_zoocriadero_id_tanque_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.seguimiento_zoocriadero
    ADD CONSTRAINT seguimiento_zoocriadero_id_tanque_fkey FOREIGN KEY (id_tanque) REFERENCES public.tanque(id_tanque) DEFERRABLE;


--
-- Name: seguimiento_zoocriadero seguimiento_zoocriadero_id_usuario_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.seguimiento_zoocriadero
    ADD CONSTRAINT seguimiento_zoocriadero_id_usuario_fkey FOREIGN KEY (id_usuario) REFERENCES public.usuario(id_usuario) DEFERRABLE;


--
-- Name: seguimiento_zoocriadero seguimiento_zoocriadero_id_zoocriadero_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.seguimiento_zoocriadero
    ADD CONSTRAINT seguimiento_zoocriadero_id_zoocriadero_fkey FOREIGN KEY (id_zoocriadero) REFERENCES public.zoocriadero(id_zoocriadero) DEFERRABLE;


--
-- Name: sitio sitio_id_direccion_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sitio
    ADD CONSTRAINT sitio_id_direccion_fkey FOREIGN KEY (id_direccion) REFERENCES public.direccion(id_direccion) DEFERRABLE;


--
-- Name: tanque tanque_id_tipo_tanque_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tanque
    ADD CONSTRAINT tanque_id_tipo_tanque_fkey FOREIGN KEY (id_tipo_tanque) REFERENCES public.tipo_tanque(id_tipo_tanque) DEFERRABLE;


--
-- Name: tanque tanque_id_zoocriadero_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tanque
    ADD CONSTRAINT tanque_id_zoocriadero_fkey FOREIGN KEY (id_zoocriadero) REFERENCES public.zoocriadero(id_zoocriadero) DEFERRABLE;


--
-- Name: territorio_priorizado territorio_priorizado_id_funcionario_ecosalud_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.territorio_priorizado
    ADD CONSTRAINT territorio_priorizado_id_funcionario_ecosalud_fkey FOREIGN KEY (id_funcionario_ecosalud) REFERENCES public.usuario(id_usuario) DEFERRABLE;


--
-- Name: usuario usuario_id_rol_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.usuario
    ADD CONSTRAINT usuario_id_rol_fkey FOREIGN KEY (id_rol) REFERENCES public.rol(id_rol) DEFERRABLE;


--
-- Name: usuario usuario_id_tipodocumento_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.usuario
    ADD CONSTRAINT usuario_id_tipodocumento_fkey FOREIGN KEY (id_tipodocumento) REFERENCES public.tipo_documento(id_tipodocumento) DEFERRABLE;


--
-- Name: zoocriadero zoocriadero_id_persona_cargo_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.zoocriadero
    ADD CONSTRAINT zoocriadero_id_persona_cargo_fkey FOREIGN KEY (id_persona_cargo) REFERENCES public.usuario(id_usuario) DEFERRABLE;


--
-- Name: SCHEMA public; Type: ACL; Schema: -; Owner: postgres
--

REVOKE USAGE ON SCHEMA public FROM PUBLIC;


--
-- PostgreSQL database dump complete
--

\unrestrict 4hjLLSkiBZsu44buZFxK15dhbc4fbpiJf540VusKxeGrRIRcRBoNvNJUlyfG0XC

