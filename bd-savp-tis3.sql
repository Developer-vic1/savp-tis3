--
-- PostgreSQL database dump
--

\restrict NCfdMCGv5ilRpE7reNpbY7e4fXeuRhS18bcSpSRgm9GKYLTFqs0ScGRKRmFlKTY

-- Dumped from database version 18.3
-- Dumped by pg_dump version 18.3

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

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: asignatura; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.asignatura (
    cod_asi character varying(20) NOT NULL,
    nom_asi character varying(150) NOT NULL,
    sig_asi character varying(20),
    hor_asi integer,
    est_asi character varying(20) DEFAULT 'ACTIVO'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.asignatura OWNER TO sail;

--
-- Name: bitacora; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.bitacora (
    cod_bit character varying(20) NOT NULL,
    acc_bit character varying(150) NOT NULL,
    tab_bit character varying(100) NOT NULL,
    reg_bit character varying(50),
    cod_usu character varying(20),
    fec_bit timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    est_bit character varying(20) DEFAULT 'ACTIVO'::character varying NOT NULL
);


ALTER TABLE public.bitacora OWNER TO sail;

--
-- Name: cache; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.cache (
    key character varying(255) NOT NULL,
    value text NOT NULL,
    expiration bigint NOT NULL
);


ALTER TABLE public.cache OWNER TO sail;

--
-- Name: cache_locks; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.cache_locks (
    key character varying(255) NOT NULL,
    owner character varying(255) NOT NULL,
    expiration bigint NOT NULL
);


ALTER TABLE public.cache_locks OWNER TO sail;

--
-- Name: calificacion; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.calificacion (
    cod_cal character varying(20) NOT NULL,
    cod_est character varying(20) NOT NULL,
    cod_asi character varying(20) NOT NULL,
    cod_pev character varying(20) NOT NULL,
    not_cal numeric(5,2) NOT NULL,
    obs_cal character varying(255),
    est_cal character varying(20) DEFAULT 'ACTIVO'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.calificacion OWNER TO sail;

--
-- Name: curso; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.curso (
    cod_cur character varying(20) NOT NULL,
    nom_cur character varying(100) NOT NULL,
    niv_cur character varying(50),
    est_cur character varying(20) DEFAULT 'ACTIVO'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.curso OWNER TO sail;

--
-- Name: docente; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.docente (
    cod_doc character varying(20) NOT NULL,
    cod_pin character varying(20) NOT NULL,
    esp_doc character varying(150),
    est_doc character varying(20) DEFAULT 'ACTIVO'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.docente OWNER TO sail;

--
-- Name: especialidad_tecnica; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.especialidad_tecnica (
    cod_esp character varying(20) NOT NULL,
    nom_esp character varying(150) NOT NULL,
    des_esp character varying(255),
    est_esp character varying(20) DEFAULT 'ACTIVO'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.especialidad_tecnica OWNER TO sail;

--
-- Name: estudiante; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.estudiante (
    cod_est character varying(20) NOT NULL,
    rud_est character varying(20) NOT NULL,
    cod_per character varying(20) NOT NULL,
    cod_tve character varying(20) NOT NULL,
    cod_ipe character varying(20),
    cod_esp character varying(20),
    est_est character varying(20) DEFAULT 'ACTIVO'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.estudiante OWNER TO sail;

--
-- Name: failed_jobs; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.failed_jobs (
    id bigint NOT NULL,
    uuid character varying(255) NOT NULL,
    connection text NOT NULL,
    queue text NOT NULL,
    payload text NOT NULL,
    exception text NOT NULL,
    failed_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


ALTER TABLE public.failed_jobs OWNER TO sail;

--
-- Name: failed_jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: sail
--

CREATE SEQUENCE public.failed_jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.failed_jobs_id_seq OWNER TO sail;

--
-- Name: failed_jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: sail
--

ALTER SEQUENCE public.failed_jobs_id_seq OWNED BY public.failed_jobs.id;


--
-- Name: gestion_academica; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.gestion_academica (
    cod_gea character varying(20) NOT NULL,
    ani_gea integer NOT NULL,
    fii_gea date,
    ffi_gea date,
    est_gea character varying(20) DEFAULT 'ACTIVO'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.gestion_academica OWNER TO sail;

--
-- Name: inscripcion_estudiante; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.inscripcion_estudiante (
    cod_ins character varying(20) NOT NULL,
    cod_est character varying(20) NOT NULL,
    cod_cur character varying(20) NOT NULL,
    cod_par character varying(20) NOT NULL,
    cod_tur character varying(20) NOT NULL,
    cod_gea character varying(20) NOT NULL,
    fei_ins date NOT NULL,
    est_ins character varying(20) DEFAULT 'ACTIVO'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.inscripcion_estudiante OWNER TO sail;

--
-- Name: institucion_procedencia; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.institucion_procedencia (
    cod_ipe character varying(20) NOT NULL,
    nom_ipe character varying(150) NOT NULL,
    tip_ipe character varying(50),
    ciu_ipe character varying(100),
    est_ipe character varying(20) DEFAULT 'ACTIVO'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.institucion_procedencia OWNER TO sail;

--
-- Name: job_batches; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.job_batches (
    id character varying(255) NOT NULL,
    name character varying(255) NOT NULL,
    total_jobs integer NOT NULL,
    pending_jobs integer NOT NULL,
    failed_jobs integer NOT NULL,
    failed_job_ids text NOT NULL,
    options text,
    cancelled_at integer,
    created_at integer NOT NULL,
    finished_at integer
);


ALTER TABLE public.job_batches OWNER TO sail;

--
-- Name: jobs; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.jobs (
    id bigint NOT NULL,
    queue character varying(255) NOT NULL,
    payload text NOT NULL,
    attempts smallint NOT NULL,
    reserved_at integer,
    available_at integer NOT NULL,
    created_at integer NOT NULL
);


ALTER TABLE public.jobs OWNER TO sail;

--
-- Name: jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: sail
--

CREATE SEQUENCE public.jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.jobs_id_seq OWNER TO sail;

--
-- Name: jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: sail
--

ALTER SEQUENCE public.jobs_id_seq OWNED BY public.jobs.id;


--
-- Name: migrations; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.migrations (
    id integer NOT NULL,
    migration character varying(255) NOT NULL,
    batch integer NOT NULL
);


ALTER TABLE public.migrations OWNER TO sail;

--
-- Name: migrations_id_seq; Type: SEQUENCE; Schema: public; Owner: sail
--

CREATE SEQUENCE public.migrations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.migrations_id_seq OWNER TO sail;

--
-- Name: migrations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: sail
--

ALTER SEQUENCE public.migrations_id_seq OWNED BY public.migrations.id;


--
-- Name: model_has_permissions; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.model_has_permissions (
    permission_id bigint NOT NULL,
    model_type character varying(255) NOT NULL,
    cod_usu character varying(20) NOT NULL
);


ALTER TABLE public.model_has_permissions OWNER TO sail;

--
-- Name: model_has_roles; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.model_has_roles (
    role_id bigint NOT NULL,
    model_type character varying(255) NOT NULL,
    cod_usu character varying(20) NOT NULL
);


ALTER TABLE public.model_has_roles OWNER TO sail;

--
-- Name: paralelo; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.paralelo (
    cod_par character varying(20) NOT NULL,
    nom_par character varying(50) NOT NULL,
    cod_cur character varying(20) NOT NULL,
    est_par character varying(20) DEFAULT 'ACTIVO'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.paralelo OWNER TO sail;

--
-- Name: password_reset_tokens; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.password_reset_tokens (
    email character varying(255) NOT NULL,
    token character varying(255) NOT NULL,
    created_at timestamp(0) without time zone
);


ALTER TABLE public.password_reset_tokens OWNER TO sail;

--
-- Name: periodo_evaluacion; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.periodo_evaluacion (
    cod_pev character varying(20) NOT NULL,
    nom_pev character varying(100) NOT NULL,
    ord_pev integer,
    est_pev character varying(20) DEFAULT 'ACTIVO'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.periodo_evaluacion OWNER TO sail;

--
-- Name: permissions; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.permissions (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    guard_name character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.permissions OWNER TO sail;

--
-- Name: permissions_id_seq; Type: SEQUENCE; Schema: public; Owner: sail
--

CREATE SEQUENCE public.permissions_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.permissions_id_seq OWNER TO sail;

--
-- Name: permissions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: sail
--

ALTER SEQUENCE public.permissions_id_seq OWNED BY public.permissions.id;


--
-- Name: persona; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.persona (
    cod_per character varying(20) NOT NULL,
    nom_per character varying(100) NOT NULL,
    ape_pat_per character varying(100) NOT NULL,
    ape_mat_per character varying(100),
    ci_per character varying(20) NOT NULL,
    com_per character varying(20),
    exp_per character varying(20),
    fec_nac_per date,
    gen_per character varying(20),
    tel_per character varying(20),
    ema_per character varying(150),
    dir_per character varying(255),
    fot_per character varying(255),
    est_per boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.persona OWNER TO sail;

--
-- Name: personal_access_tokens; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.personal_access_tokens (
    id bigint NOT NULL,
    tokenable_type character varying(255) NOT NULL,
    tokenable_id bigint NOT NULL,
    name text NOT NULL,
    token character varying(64) NOT NULL,
    abilities text,
    last_used_at timestamp(0) without time zone,
    expires_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.personal_access_tokens OWNER TO sail;

--
-- Name: personal_access_tokens_id_seq; Type: SEQUENCE; Schema: public; Owner: sail
--

CREATE SEQUENCE public.personal_access_tokens_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.personal_access_tokens_id_seq OWNER TO sail;

--
-- Name: personal_access_tokens_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: sail
--

ALTER SEQUENCE public.personal_access_tokens_id_seq OWNED BY public.personal_access_tokens.id;


--
-- Name: personal_institucional; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.personal_institucional (
    cod_pin character varying(20) NOT NULL,
    cod_per character varying(20) NOT NULL,
    car_pin character varying(100) NOT NULL,
    est_pin character varying(20) DEFAULT 'ACTIVO'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.personal_institucional OWNER TO sail;

--
-- Name: plan_asignatura; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.plan_asignatura (
    cod_pas character varying(20) NOT NULL,
    cod_asi character varying(20) NOT NULL,
    cod_doc character varying(20) NOT NULL,
    cod_cur character varying(20) NOT NULL,
    cod_par character varying(20) NOT NULL,
    cod_tur character varying(20) NOT NULL,
    cod_gea character varying(20) NOT NULL,
    hor_pas integer,
    est_pas character varying(20) DEFAULT 'ACTIVO'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.plan_asignatura OWNER TO sail;

--
-- Name: role_has_permissions; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.role_has_permissions (
    permission_id bigint NOT NULL,
    role_id bigint NOT NULL
);


ALTER TABLE public.role_has_permissions OWNER TO sail;

--
-- Name: roles; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.roles (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    guard_name character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.roles OWNER TO sail;

--
-- Name: roles_id_seq; Type: SEQUENCE; Schema: public; Owner: sail
--

CREATE SEQUENCE public.roles_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.roles_id_seq OWNER TO sail;

--
-- Name: roles_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: sail
--

ALTER SEQUENCE public.roles_id_seq OWNED BY public.roles.id;


--
-- Name: secretaria_general; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.secretaria_general (
    cod_sge character varying(20) NOT NULL,
    cod_pin character varying(20) NOT NULL,
    est_sge character varying(20) DEFAULT 'ACTIVO'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.secretaria_general OWNER TO sail;

--
-- Name: sessions; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.sessions (
    id character varying(255) NOT NULL,
    user_id character varying(20),
    ip_address character varying(45),
    user_agent text,
    payload text NOT NULL,
    last_activity integer NOT NULL
);


ALTER TABLE public.sessions OWNER TO sail;

--
-- Name: tipo_vinculacion_estudiante; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.tipo_vinculacion_estudiante (
    cod_tve character varying(20) NOT NULL,
    nom_tve character varying(100) NOT NULL,
    des_tve character varying(255),
    est_tve character varying(20) DEFAULT 'ACTIVO'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.tipo_vinculacion_estudiante OWNER TO sail;

--
-- Name: turno; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.turno (
    cod_tur character varying(20) NOT NULL,
    nom_tur character varying(50) NOT NULL,
    hor_ini_tur character varying(10),
    hor_fin_tur character varying(10),
    est_tur character varying(20) DEFAULT 'ACTIVO'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.turno OWNER TO sail;

--
-- Name: users; Type: TABLE; Schema: public; Owner: sail
--

CREATE TABLE public.users (
    cod_usu character varying(20) NOT NULL,
    cod_per character varying(20) NOT NULL,
    email character varying(255) NOT NULL,
    email_verified_at timestamp(0) without time zone,
    password character varying(255) NOT NULL,
    remember_token character varying(100),
    current_team_id bigint,
    profile_photo_path character varying(2048),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    two_factor_secret text,
    two_factor_recovery_codes text,
    two_factor_confirmed_at timestamp(0) without time zone
);


ALTER TABLE public.users OWNER TO sail;

--
-- Name: failed_jobs id; Type: DEFAULT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.failed_jobs ALTER COLUMN id SET DEFAULT nextval('public.failed_jobs_id_seq'::regclass);


--
-- Name: jobs id; Type: DEFAULT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.jobs ALTER COLUMN id SET DEFAULT nextval('public.jobs_id_seq'::regclass);


--
-- Name: migrations id; Type: DEFAULT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);


--
-- Name: permissions id; Type: DEFAULT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.permissions ALTER COLUMN id SET DEFAULT nextval('public.permissions_id_seq'::regclass);


--
-- Name: personal_access_tokens id; Type: DEFAULT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.personal_access_tokens ALTER COLUMN id SET DEFAULT nextval('public.personal_access_tokens_id_seq'::regclass);


--
-- Name: roles id; Type: DEFAULT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.roles ALTER COLUMN id SET DEFAULT nextval('public.roles_id_seq'::regclass);


--
-- Data for Name: asignatura; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.asignatura (cod_asi, nom_asi, sig_asi, hor_asi, est_asi, created_at, updated_at) FROM stdin;
ASI_0001	Comunicación y Lenguaje	LCO	4	ACTIVO	2026-04-13 17:43:28	2026-04-13 17:43:28
ASI_0002	Matemática	MAT	5	ACTIVO	2026-04-13 17:43:28	2026-04-13 17:43:28
ASI_0003	Ciencias Sociales	CSO	4	ACTIVO	2026-04-13 17:43:28	2026-04-13 17:43:28
ASI_0004	Ciencias Biológicas	CBG	3	ACTIVO	2026-04-13 17:43:28	2026-04-13 17:43:28
ASI_0005	Física	FIS	3	ACTIVO	2026-04-13 17:43:28	2026-04-13 17:43:28
ASI_0006	Química	QMC	3	ACTIVO	2026-04-13 17:43:28	2026-04-13 17:43:28
ASI_0007	Educación Física	EFD	2	ACTIVO	2026-04-13 17:43:28	2026-04-13 17:43:28
ASI_0008	Lengua Extranjera - Inglés	LEX	2	ACTIVO	2026-04-13 17:43:28	2026-04-13 17:43:28
ASI_0009	Valores y Espiritualidades	VER	2	ACTIVO	2026-04-13 17:43:28	2026-04-13 17:43:28
ASI_0010	Cosmovisiones y Filosofía	CFS	2	ACTIVO	2026-04-13 17:43:28	2026-04-13 17:43:28
ASI_0011	Psicología	PSI	2	ACTIVO	2026-04-13 17:43:28	2026-04-13 17:43:28
ASI_0012	Educación Musical	EMU	2	ACTIVO	2026-04-13 17:43:28	2026-04-13 17:43:28
ASI_0013	Artes Plásticas y Visuales	APV	2	ACTIVO	2026-04-13 17:43:28	2026-04-13 17:43:28
ASI_0014	Técnica Tecnología General	TTG	4	ACTIVO	2026-04-13 17:43:28	2026-04-13 17:43:28
\.


--
-- Data for Name: bitacora; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.bitacora (cod_bit, acc_bit, tab_bit, reg_bit, cod_usu, fec_bit, est_bit) FROM stdin;
\.


--
-- Data for Name: cache; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.cache (key, value, expiration) FROM stdin;
laravel-cache-5405e5bff8616b991c40e32b5d0b0b2a:timer	i:1776137458;	1776137458
laravel-cache-5405e5bff8616b991c40e32b5d0b0b2a	i:1;	1776137458
laravel-cache-456c123d93c2497facc24378bb04fc2a:timer	i:1776123717;	1776123717
laravel-cache-victor@gmail.com|172.19.0.1:timer	i:1776123717;	1776123717
laravel-cache-456c123d93c2497facc24378bb04fc2a	i:2;	1776123717
laravel-cache-victor@gmail.com|172.19.0.1	i:2;	1776123717
laravel-cache-35929a77c2ba8968b1757078f6559998:timer	i:1776125214;	1776125214
laravel-cache-35929a77c2ba8968b1757078f6559998	i:1;	1776125214
laravel-cache-carla@gmail.com|172.19.0.1:timer	i:1776125214;	1776125214
laravel-cache-carla@gmail.com|172.19.0.1	i:1;	1776125214
laravel-cache-132d858099c62a3c589f0aecca5f4afd:timer	i:1776125251;	1776125251
laravel-cache-victorasturizaga@gmail.com|172.19.0.1:timer	i:1776125251;	1776125251
laravel-cache-132d858099c62a3c589f0aecca5f4afd	i:2;	1776125251
laravel-cache-victorasturizaga@gmail.com|172.19.0.1	i:2;	1776125251
\.


--
-- Data for Name: cache_locks; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.cache_locks (key, owner, expiration) FROM stdin;
\.


--
-- Data for Name: calificacion; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.calificacion (cod_cal, cod_est, cod_asi, cod_pev, not_cal, obs_cal, est_cal, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: curso; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.curso (cod_cur, nom_cur, niv_cur, est_cur, created_at, updated_at) FROM stdin;
CUR_0001	1ro de Secundaria	Secundaria	ACTIVO	2026-04-13 17:43:28	2026-04-13 17:43:28
CUR_0002	2do de Secundaria	Secundaria	ACTIVO	2026-04-13 17:43:28	2026-04-13 17:43:28
CUR_0003	3ro de Secundaria	Secundaria	ACTIVO	2026-04-13 17:43:28	2026-04-13 17:43:28
CUR_0004	4to de Secundaria	Secundaria	ACTIVO	2026-04-13 17:43:28	2026-04-13 17:43:28
CUR_0005	5to de Secundaria	Secundaria	ACTIVO	2026-04-13 17:43:28	2026-04-13 17:43:28
CUR_0006	6to de Secundaria	Secundaria	ACTIVO	2026-04-13 17:43:28	2026-04-13 17:43:28
\.


--
-- Data for Name: docente; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.docente (cod_doc, cod_pin, esp_doc, est_doc, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: especialidad_tecnica; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.especialidad_tecnica (cod_esp, nom_esp, des_esp, est_esp, created_at, updated_at) FROM stdin;
ESP_0001	Técnica Tecnología General	Formación técnica inicial correspondiente al nivel de transición hacia especialidades.	ACTIVO	2026-04-13 17:43:28	2026-04-13 17:43:28
ESP_0002	Técnica Tecnología Especializada	Formación técnica aplicada con orientación especializada.	ACTIVO	2026-04-13 17:43:28	2026-04-13 17:43:28
ESP_0003	Sistemas Informáticos	Especialidad orientada al desarrollo de competencias en informática y tecnologías digitales.	ACTIVO	2026-04-13 17:43:28	2026-04-13 17:43:28
ESP_0004	Contabilidad	Especialidad orientada a procesos contables, financieros y administrativos.	ACTIVO	2026-04-13 17:43:28	2026-04-13 17:43:28
ESP_0005	Electrónica	Especialidad técnica enfocada en circuitos, dispositivos y sistemas electrónicos.	ACTIVO	2026-04-13 17:43:28	2026-04-13 17:43:28
ESP_0006	Mecánica Industrial	Especialidad técnica enfocada en procesos industriales y mantenimiento mecánico.	ACTIVO	2026-04-13 17:43:28	2026-04-13 17:43:28
ESP_0007	Mecánica Automotriz	Especialidad técnica orientada al diagnóstico y mantenimiento de vehículos.	ACTIVO	2026-04-13 17:43:28	2026-04-13 17:43:28
ESP_0008	Gastronomía	Especialidad técnica enfocada en preparación de alimentos y gestión gastronómica.	ACTIVO	2026-04-13 17:43:28	2026-04-13 17:43:28
ESP_0009	Textiles y Confección	Especialidad técnica enfocada en diseño, patronaje y confección textil.	ACTIVO	2026-04-13 17:43:28	2026-04-13 17:43:28
\.


--
-- Data for Name: estudiante; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.estudiante (cod_est, rud_est, cod_per, cod_tve, cod_ipe, cod_esp, est_est, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: failed_jobs; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.failed_jobs (id, uuid, connection, queue, payload, exception, failed_at) FROM stdin;
\.


--
-- Data for Name: gestion_academica; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.gestion_academica (cod_gea, ani_gea, fii_gea, ffi_gea, est_gea, created_at, updated_at) FROM stdin;
GEA_0001	2023	2023-02-01	2023-11-30	FINALIZADO	2026-04-13 17:43:28	2026-04-13 17:43:28
GEA_0002	2024	2024-02-01	2024-11-30	FINALIZADO	2026-04-13 17:43:28	2026-04-13 17:43:28
GEA_0003	2025	2025-02-01	2025-11-30	FINALIZADO	2026-04-13 17:43:28	2026-04-13 17:43:28
GEA_0004	2026	2026-02-01	2026-11-30	ACTIVO	2026-04-13 17:43:28	2026-04-13 17:43:28
\.


--
-- Data for Name: inscripcion_estudiante; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.inscripcion_estudiante (cod_ins, cod_est, cod_cur, cod_par, cod_tur, cod_gea, fei_ins, est_ins, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: institucion_procedencia; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.institucion_procedencia (cod_ipe, nom_ipe, tip_ipe, ciu_ipe, est_ipe, created_at, updated_at) FROM stdin;
IPE_0001	Unidad Educativa Franz Tamayo N° 3	Pública	La Paz	ACTIVO	2026-04-13 17:43:28	2026-04-13 17:43:28
IPE_0002	Unidad Educativa Sagrado Corazón de Jesús	Convenio	La Paz	ACTIVO	2026-04-13 17:43:28	2026-04-13 17:43:28
IPE_0003	Unidad Educativa Italia	Pública	La Paz	ACTIVO	2026-04-13 17:43:28	2026-04-13 17:43:28
IPE_0004	Unidad Educativa Simón Bolívar	Pública	La Paz	ACTIVO	2026-04-13 17:43:28	2026-04-13 17:43:28
\.


--
-- Data for Name: job_batches; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.job_batches (id, name, total_jobs, pending_jobs, failed_jobs, failed_job_ids, options, cancelled_at, created_at, finished_at) FROM stdin;
\.


--
-- Data for Name: jobs; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.jobs (id, queue, payload, attempts, reserved_at, available_at, created_at) FROM stdin;
\.


--
-- Data for Name: migrations; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.migrations (id, migration, batch) FROM stdin;
1	0001_01_01_000000_create_persona_table	1
2	0001_01_01_000001_create_cache_table	1
3	0001_01_01_000001_create_users_table	1
4	0001_01_01_000002_create_jobs_table	1
5	2026_04_09_025251_add_two_factor_columns_to_users_table	1
6	2026_04_09_025308_create_personal_access_tokens_table	1
7	2026_04_09_025343_create_permission_tables	1
8	2026_04_09_033042_create_personal_institucional_table	1
9	2026_04_09_033043_create_institucion_procedencia_table	1
10	2026_04_09_033043_create_secretaria_general_table	1
11	2026_04_09_033043_create_tipo_vinculacion_estudiante_table	1
12	2026_04_09_033044_create_gestion_academica_table	1
13	2026_04_09_033045_create_curso_table	1
14	2026_04_09_033045_create_paralelo_table	1
15	2026_04_09_033045_create_turno_table	1
16	2026_04_09_033046_create_asignatura_table	1
17	2026_04_09_033046_create_especialidad_tecnica_table	1
18	2026_04_09_033047_create_periodo_evaluacion_table	1
19	2026_04_09_033048_create_bitacora_table	1
20	2026_04_09_033050_create_docente_table	1
21	2026_04_09_033060_create_estudiante_table	1
22	2026_04_09_033070_create_plan_asignatura_table	1
23	2026_04_09_033080_create_inscripcion_estudiante_table	1
24	2026_04_09_033090_create_calificacion_table	1
\.


--
-- Data for Name: model_has_permissions; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.model_has_permissions (permission_id, model_type, cod_usu) FROM stdin;
\.


--
-- Data for Name: model_has_roles; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.model_has_roles (role_id, model_type, cod_usu) FROM stdin;
1	App\\Models\\User	USU_0001
6	App\\Models\\User	USU_0002
3	App\\Models\\User	USU_0003
2	App\\Models\\User	USU_0004
4	App\\Models\\User	USU_0005
4	App\\Models\\User	USU_0006
4	App\\Models\\User	USU_0007
4	App\\Models\\User	USU_0008
4	App\\Models\\User	USU_0009
4	App\\Models\\User	USU_0010
4	App\\Models\\User	USU_0011
4	App\\Models\\User	USU_0012
4	App\\Models\\User	USU_0013
4	App\\Models\\User	USU_0014
4	App\\Models\\User	USU_0015
4	App\\Models\\User	USU_0016
4	App\\Models\\User	USU_0017
4	App\\Models\\User	USU_0018
4	App\\Models\\User	USU_0019
4	App\\Models\\User	USU_0020
\.


--
-- Data for Name: paralelo; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.paralelo (cod_par, nom_par, cod_cur, est_par, created_at, updated_at) FROM stdin;
PAR_0001	A	CUR_0001	ACTIVO	2026-04-13 17:43:28	2026-04-13 17:43:28
PAR_0002	B	CUR_0001	ACTIVO	2026-04-13 17:43:28	2026-04-13 17:43:28
PAR_0003	C	CUR_0001	ACTIVO	2026-04-13 17:43:28	2026-04-13 17:43:28
PAR_0004	D	CUR_0001	ACTIVO	2026-04-13 17:43:28	2026-04-13 17:43:28
PAR_0005	A	CUR_0002	ACTIVO	2026-04-13 17:43:28	2026-04-13 17:43:28
PAR_0006	B	CUR_0002	ACTIVO	2026-04-13 17:43:28	2026-04-13 17:43:28
PAR_0007	C	CUR_0002	ACTIVO	2026-04-13 17:43:28	2026-04-13 17:43:28
PAR_0008	D	CUR_0002	ACTIVO	2026-04-13 17:43:28	2026-04-13 17:43:28
PAR_0009	A	CUR_0003	ACTIVO	2026-04-13 17:43:28	2026-04-13 17:43:28
PAR_0010	B	CUR_0003	ACTIVO	2026-04-13 17:43:28	2026-04-13 17:43:28
PAR_0011	C	CUR_0003	ACTIVO	2026-04-13 17:43:28	2026-04-13 17:43:28
PAR_0012	D	CUR_0003	ACTIVO	2026-04-13 17:43:28	2026-04-13 17:43:28
PAR_0013	A	CUR_0004	ACTIVO	2026-04-13 17:43:28	2026-04-13 17:43:28
PAR_0014	B	CUR_0004	ACTIVO	2026-04-13 17:43:28	2026-04-13 17:43:28
PAR_0015	C	CUR_0004	ACTIVO	2026-04-13 17:43:28	2026-04-13 17:43:28
PAR_0016	D	CUR_0004	ACTIVO	2026-04-13 17:43:28	2026-04-13 17:43:28
PAR_0017	A	CUR_0005	ACTIVO	2026-04-13 17:43:28	2026-04-13 17:43:28
PAR_0018	B	CUR_0005	ACTIVO	2026-04-13 17:43:28	2026-04-13 17:43:28
PAR_0019	C	CUR_0005	ACTIVO	2026-04-13 17:43:28	2026-04-13 17:43:28
PAR_0020	D	CUR_0005	ACTIVO	2026-04-13 17:43:28	2026-04-13 17:43:28
PAR_0021	A	CUR_0006	ACTIVO	2026-04-13 17:43:28	2026-04-13 17:43:28
PAR_0022	B	CUR_0006	ACTIVO	2026-04-13 17:43:28	2026-04-13 17:43:28
PAR_0023	C	CUR_0006	ACTIVO	2026-04-13 17:43:28	2026-04-13 17:43:28
PAR_0024	D	CUR_0006	ACTIVO	2026-04-13 17:43:28	2026-04-13 17:43:28
\.


--
-- Data for Name: password_reset_tokens; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.password_reset_tokens (email, token, created_at) FROM stdin;
\.


--
-- Data for Name: periodo_evaluacion; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.periodo_evaluacion (cod_pev, nom_pev, ord_pev, est_pev, created_at, updated_at) FROM stdin;
PEV_0001	Primer Bimestre	1	ACTIVO	2026-04-13 17:43:28	2026-04-13 17:43:28
PEV_0002	Segundo Bimestre	2	ACTIVO	2026-04-13 17:43:28	2026-04-13 17:43:28
PEV_0003	Tercer Bimestre	3	ACTIVO	2026-04-13 17:43:28	2026-04-13 17:43:28
PEV_0004	Cuarto Bimestre	4	ACTIVO	2026-04-13 17:43:28	2026-04-13 17:43:28
\.


--
-- Data for Name: permissions; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.permissions (id, name, guard_name, created_at, updated_at) FROM stdin;
1	Panel_Administrador	web	2026-04-13 17:43:28	2026-04-13 17:43:28
2	Panel_Director	web	2026-04-13 17:43:28	2026-04-13 17:43:28
3	Panel_Docente	web	2026-04-13 17:43:28	2026-04-13 17:43:28
4	Panel_Estudiante	web	2026-04-13 17:43:28	2026-04-13 17:43:28
5	Panel_Secretaria	web	2026-04-13 17:43:28	2026-04-13 17:43:28
6	Panel_Regente	web	2026-04-13 17:43:28	2026-04-13 17:43:28
7	Gestion_Usuarios	web	2026-04-13 17:43:28	2026-04-13 17:43:28
8	Gestion_Personas	web	2026-04-13 17:43:28	2026-04-13 17:43:28
9	Estudiantes	web	2026-04-13 17:43:28	2026-04-13 17:43:28
10	Docentes	web	2026-04-13 17:43:28	2026-04-13 17:43:28
11	Personal_Institucional	web	2026-04-13 17:43:28	2026-04-13 17:43:28
12	Inscripciones	web	2026-04-13 17:43:28	2026-04-13 17:43:28
13	Cursos	web	2026-04-13 17:43:28	2026-04-13 17:43:28
14	Paralelos	web	2026-04-13 17:43:28	2026-04-13 17:43:28
15	Turnos	web	2026-04-13 17:43:28	2026-04-13 17:43:28
16	Asignaturas	web	2026-04-13 17:43:28	2026-04-13 17:43:28
17	Especialidades_Tecnicas	web	2026-04-13 17:43:28	2026-04-13 17:43:28
18	Calificaciones	web	2026-04-13 17:43:28	2026-04-13 17:43:28
19	Periodo_Evaluacion	web	2026-04-13 17:43:28	2026-04-13 17:43:28
20	Gestion_Academica	web	2026-04-13 17:43:28	2026-04-13 17:43:28
21	Bitacora	web	2026-04-13 17:43:28	2026-04-13 17:43:28
22	Reportes_Academicos	web	2026-04-13 17:43:28	2026-04-13 17:43:28
23	Reportes_Administrativos	web	2026-04-13 17:43:28	2026-04-13 17:43:28
24	Mi_Perfil	web	2026-04-13 17:43:28	2026-04-13 17:43:28
\.


--
-- Data for Name: persona; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.persona (cod_per, nom_per, ape_pat_per, ape_mat_per, ci_per, com_per, exp_per, fec_nac_per, gen_per, tel_per, ema_per, dir_per, fot_per, est_per, created_at, updated_at) FROM stdin;
PER_0001	Victor	Asturizaga	Plata	13381086	\N	LP	2006-06-11	M	75836807	asturizagavictor@gmail.com	Zona Bajo Tejar, La Paz	\N	t	2026-04-13 17:43:28	2026-04-13 17:43:28
PER_0002	María Fernanda	Choque	Condori	7458123	\N	LP	2006-07-25	F	76543210	maria.choque@gmail.com	Zona El Alto, Distrito 3	\N	t	2026-04-13 17:43:28	2026-04-13 17:43:28
PER_0003	Luis Fernando	Rojas	Gutierrez	5987412	\N	LP	1985-11-08	M	70123456	luis.rojas@gmail.com	Zona Sopocachi, La Paz	\N	t	2026-04-13 17:43:28	2026-04-13 17:43:28
PER_0004	Ana Lucía	Vargas	Torrez	6321457	\N	LP	1990-05-19	F	78965412	ana.vargas@gmail.com	Zona Miraflores, La Paz	\N	t	2026-04-13 17:43:28	2026-04-13 17:43:28
PER_0005	Jorge Andrés	Flores	Mendoza	6123789	\N	LP	1978-09-03	M	73456789	jorge.flores@gmail.com	Zona Achumani, La Paz	\N	t	2026-04-13 17:43:28	2026-04-13 17:43:28
PER_0006	Edgar	Rios	Chuquimia	6798859	\N	LP	1978-09-03	M	73456789	edgar.rios@gmail.com	Zona Achumani, La Paz	\N	t	2026-04-13 17:43:28	2026-04-13 17:43:28
PER_0007	Diego Alejandro	Huanca	Mendoza	7021458	\N	LP	2008-04-15	M	72145678	diego.huanca@gmail.com	Zona Villa Adela, El Alto	\N	t	2026-04-13 17:43:28	2026-04-13 17:43:28
PER_0008	Lucía Fernanda	Quispe	Choque	7156324	1A	LP	2009-08-21	F	73219845	lucia.quispe@gmail.com	Ciudad Satélite, El Alto	\N	t	2026-04-13 17:43:28	2026-04-13 17:43:28
PER_0009	Kevin Andrés	Callisaya	Rojas	7245631	\N	LP	2007-11-02	M	74125698	kevin.callisaya@gmail.com	Villa Fátima, La Paz	\N	t	2026-04-13 17:43:28	2026-04-13 17:43:28
PER_0010	Valeria Sofía	Mamani	Flores	7365412	2B	LP	2010-01-30	F	75632147	valeria.mamani@gmail.com	Miraflores, La Paz	\N	t	2026-04-13 17:43:28	2026-04-13 17:43:28
PER_0011	José Luis	Condori	Quispe	7489123	\N	LP	2008-06-10	M	76521478	jose.condori@gmail.com	Alto Lima, El Alto	\N	t	2026-04-13 17:43:28	2026-04-13 17:43:28
PER_0012	Camila Andrea	Vargas	Lopez	7598741	1C	LP	2009-03-18	F	71234598	camila.vargas@gmail.com	Achumani, La Paz	\N	t	2026-04-13 17:43:28	2026-04-13 17:43:28
PER_0013	Fernando David	Rojas	Mamani	7612345	\N	LP	2007-09-27	M	78965432	fernando.rojas@gmail.com	Villa Bolívar, El Alto	\N	t	2026-04-13 17:43:28	2026-04-13 17:43:28
PER_0014	Daniela Paola	Choque	Gutierrez	7721456	3A	LP	2010-12-05	F	73412569	daniela.choque@gmail.com	San Pedro, La Paz	\N	t	2026-04-13 17:43:28	2026-04-13 17:43:28
PER_0015	Miguel Ángel	Flores	Condori	7832145	\N	LP	2008-02-14	M	72198745	miguel.flores@gmail.com	Sopocachi, La Paz	\N	t	2026-04-13 17:43:28	2026-04-13 17:43:28
PER_0016	Paola Jimena	Lopez	Quispe	7945612	2A	LP	2009-07-09	F	76549812	paola.lopez@gmail.com	Villa Copacabana, La Paz	\N	t	2026-04-13 17:43:28	2026-04-13 17:43:28
PER_0017	Luis Eduardo	Mamani	Rojas	8054123	\N	LP	2007-05-11	M	71236985	luis.mamani@gmail.com	El Tejar, La Paz	\N	t	2026-04-13 17:43:28	2026-04-13 17:43:28
PER_0018	Gabriela Nicole	Condori	Flores	8165234	1B	LP	2010-10-22	F	74325698	gabriela.condori@gmail.com	Villa Esperanza, El Alto	\N	t	2026-04-13 17:43:28	2026-04-13 17:43:28
PER_0019	Andrés Felipe	Quispe	Lopez	8276541	\N	LP	2008-01-05	M	75412369	andres.quispe@gmail.com	Alto Obrajes, La Paz	\N	t	2026-04-13 17:43:28	2026-04-13 17:43:28
PER_0020	Natalia Belén	Flores	Mamani	8387452	2C	LP	2009-06-16	F	72145896	natalia.flores@gmail.com	Munaypata, La Paz	\N	t	2026-04-13 17:43:28	2026-04-13 17:43:28
PER_0021	Cristian Daniel	Rojas	Condori	8496123	\N	LP	2007-12-28	M	73658941	cristian.rojas@gmail.com	Villa Dolores, El Alto	\N	t	2026-04-13 17:43:28	2026-04-13 17:43:28
\.


--
-- Data for Name: personal_access_tokens; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.personal_access_tokens (id, tokenable_type, tokenable_id, name, token, abilities, last_used_at, expires_at, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: personal_institucional; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.personal_institucional (cod_pin, cod_per, car_pin, est_pin, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: plan_asignatura; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.plan_asignatura (cod_pas, cod_asi, cod_doc, cod_cur, cod_par, cod_tur, cod_gea, hor_pas, est_pas, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: role_has_permissions; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.role_has_permissions (permission_id, role_id) FROM stdin;
1	1
2	2
3	3
4	4
5	5
6	6
7	1
8	1
8	5
9	1
9	2
9	3
9	5
9	6
10	1
10	2
11	1
11	2
12	1
12	5
13	1
13	2
13	5
13	3
13	6
14	1
14	2
14	5
14	3
14	6
15	1
15	2
15	5
16	1
16	2
16	3
17	1
17	2
17	5
18	1
18	2
18	3
18	4
19	1
19	2
19	3
20	1
20	2
20	5
21	1
22	1
22	2
22	3
22	6
23	1
23	2
23	5
24	1
24	2
24	3
24	4
24	5
24	6
\.


--
-- Data for Name: roles; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.roles (id, name, guard_name, created_at, updated_at) FROM stdin;
1	Administrador	web	2026-04-13 17:43:28	2026-04-13 17:43:28
2	Director	web	2026-04-13 17:43:28	2026-04-13 17:43:28
3	Docente	web	2026-04-13 17:43:28	2026-04-13 17:43:28
4	Estudiante	web	2026-04-13 17:43:28	2026-04-13 17:43:28
5	Secretaria Académica	web	2026-04-13 17:43:28	2026-04-13 17:43:28
6	Regente	web	2026-04-13 17:43:28	2026-04-13 17:43:28
\.


--
-- Data for Name: secretaria_general; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.secretaria_general (cod_sge, cod_pin, est_sge, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: sessions; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.sessions (id, user_id, ip_address, user_agent, payload, last_activity) FROM stdin;
IVbpWASseyWPgEECgal94zFWE1mXrPRhYUFJ2JDf	USU_0001	172.19.0.1	Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:149.0) Gecko/20100101 Firefox/149.0	eyJfdG9rZW4iOiJWbmxHTXFRMDVOSlBqS00wbEtjUEtqYXlYcjRZNkVhTXgyYmswRHNMIiwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cDpcL1wvbG9jYWxob3N0XC9kYXNoYm9hcmQiLCJyb3V0ZSI6ImRhc2hib2FyZCJ9LCJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI6IlVTVV8wMDAxIiwicGFzc3dvcmRfaGFzaF9zYW5jdHVtIjoiY2YwZjAzYzk0NjI3NmM1Y2UyYzg2MjM3OTgwZjkzZTg5YjE1NDU5MmI2NTMzYjI5Mjk3ZDFkZGNlOGVjYTVhMCJ9	1776127806
5yWMQNCDzUNhVC01lourUUmY1v4KJHIxEPCRqe46	USU_0001	172.19.0.1	Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:149.0) Gecko/20100101 Firefox/149.0	eyJfdG9rZW4iOiJnaUNDRWszZklTNDRLalp3OWxMblpmZmlzVFNBaHJDbFVrbFhxS0ZCIiwidXJsIjpbXSwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2xvY2FsaG9zdFwvZGFzaGJvYXJkIiwicm91dGUiOiJkYXNoYm9hcmQifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI6IlVTVV8wMDAxIiwicGFzc3dvcmRfaGFzaF9zYW5jdHVtIjoiY2YwZjAzYzk0NjI3NmM1Y2UyYzg2MjM3OTgwZjkzZTg5YjE1NDU5MmI2NTMzYjI5Mjk3ZDFkZGNlOGVjYTVhMCJ9	1776138373
\.


--
-- Data for Name: tipo_vinculacion_estudiante; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.tipo_vinculacion_estudiante (cod_tve, nom_tve, des_tve, est_tve, created_at, updated_at) FROM stdin;
TVE_0001	Regular	Estudiante que continúa sus estudios dentro de la misma institución.	ACTIVO	2026-04-13 17:43:28	2026-04-13 17:43:28
TVE_0002	Nuevo	Estudiante que ingresa por primera vez a la institución.	ACTIVO	2026-04-13 17:43:28	2026-04-13 17:43:28
TVE_0003	Transferencia	Estudiante que proviene de otra institución educativa.	ACTIVO	2026-04-13 17:43:28	2026-04-13 17:43:28
TVE_0004	Reincorporación	Estudiante que retoma sus estudios después de haber estado inactivo.	ACTIVO	2026-04-13 17:43:28	2026-04-13 17:43:28
\.


--
-- Data for Name: turno; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.turno (cod_tur, nom_tur, hor_ini_tur, hor_fin_tur, est_tur, created_at, updated_at) FROM stdin;
TUR_0001	Mañana	08:00	13:20	ACTIVO	2026-04-13 17:43:28	2026-04-13 17:43:28
TUR_0002	Tarde	14:00	18:00	ACTIVO	2026-04-13 17:43:28	2026-04-13 17:43:28
\.


--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: sail
--

COPY public.users (cod_usu, cod_per, email, email_verified_at, password, remember_token, current_team_id, profile_photo_path, created_at, updated_at, two_factor_secret, two_factor_recovery_codes, two_factor_confirmed_at) FROM stdin;
USU_0002	PER_0002	ana.vargas@gmail.com	2026-04-13 17:43:28	$2y$12$X8BRI/OvlXDTl71cwYIvIOA0HxKC0/SJxpPfQx38er9pxtR/Xryjy	\N	\N	\N	2026-04-13 17:43:28	2026-04-13 17:43:28	\N	\N	\N
USU_0003	PER_0003	luis.rojas@gmail.com	2026-04-13 17:43:28	$2y$12$bs5Ssn0R.9bCirHekK1YAudVihANEelAYQwZBjiJOUctFrnwA0umC	\N	\N	\N	2026-04-13 17:43:28	2026-04-13 17:43:28	\N	\N	\N
USU_0004	PER_0004	edgar.rios@gmail.com	2026-04-13 17:43:28	$2y$12$KnTB7V5D6XEMCDwbB7GQzeRunQsfdDpG77lgNku1eqkN5LcusxY1S	\N	\N	\N	2026-04-13 17:43:28	2026-04-13 17:43:28	\N	\N	\N
USU_0005	PER_0005	maria.choque@gmail.com	2026-04-13 17:43:28	$2y$12$EkWzmTkzfdTkf.fSqpDhVuXRiAvarefWMLFtt9vsBmCLCLo6AcSUW	\N	\N	\N	2026-04-13 17:43:28	2026-04-13 17:43:28	\N	\N	\N
USU_0006	PER_0006	diego.huanca@gmail.com	2026-04-13 17:43:28	$2y$12$akh59LlrDX60Sqo8VQcy0ODKxF8dyV4wq/6gtUoZZrEgDfW8gEJpW	\N	\N	\N	2026-04-13 17:43:28	2026-04-13 17:43:28	\N	\N	\N
USU_0007	PER_0007	lucia.quispe@gmail.com	2026-04-13 17:43:28	$2y$12$i/kEdJXoOx.KdTAd8x629u82udz/w7nec/zQ0Y02EUKEysqVpxNEi	\N	\N	\N	2026-04-13 17:43:28	2026-04-13 17:43:28	\N	\N	\N
USU_0008	PER_0008	kevin.callisaya@gmail.com	2026-04-13 17:43:28	$2y$12$4n3FiCboDYzhh2Jrps/oJ.h6DTwOv55lbutbL7SMTm4ARblWBvMS.	\N	\N	\N	2026-04-13 17:43:28	2026-04-13 17:43:28	\N	\N	\N
USU_0009	PER_0009	valeria.mamani@gmail.com	2026-04-13 17:43:28	$2y$12$OjB.nwiczRWA.vDHJ1zv1O2YoxL5UyntBNeQ77BpmBilsyP6e3evy	\N	\N	\N	2026-04-13 17:43:28	2026-04-13 17:43:28	\N	\N	\N
USU_0010	PER_0010	jose.condori@gmail.com	2026-04-13 17:43:28	$2y$12$4exyHbbnGOaKX1PuTLRyVej62uwGPr7Yombc7N0BnbPkeG9rPmYcC	\N	\N	\N	2026-04-13 17:43:28	2026-04-13 17:43:28	\N	\N	\N
USU_0011	PER_0011	camila.vargas@gmail.com	2026-04-13 17:43:28	$2y$12$UECeGn3QwWWQHTc/6PqEmutHKkIFdwhE4zuB4E7BQ56MRwg8dEq9e	\N	\N	\N	2026-04-13 17:43:28	2026-04-13 17:43:28	\N	\N	\N
USU_0012	PER_0012	fernando.rojas@gmail.com	2026-04-13 17:43:28	$2y$12$UqRuFZyEd1gpt.iIh4F1FO3G1iPzWrXVPNfZJ6JTM7k.dv.BatynS	\N	\N	\N	2026-04-13 17:43:28	2026-04-13 17:43:28	\N	\N	\N
USU_0013	PER_0013	daniela.choque@gmail.com	2026-04-13 17:43:28	$2y$12$NxRel/1o5lY0IA1mHbBCEeB974VqFiY7OsEP1fYOp10ZhbPtlGFEO	\N	\N	\N	2026-04-13 17:43:28	2026-04-13 17:43:28	\N	\N	\N
USU_0014	PER_0014	miguel.flores@gmail.com	2026-04-13 17:43:28	$2y$12$B2shPHxL/2zFeur9xjiWAuyUUU6pTxpQFsioL4ObqRv.mnAFQHREO	\N	\N	\N	2026-04-13 17:43:28	2026-04-13 17:43:28	\N	\N	\N
USU_0015	PER_0015	paola.lopez@gmail.com	2026-04-13 17:43:28	$2y$12$uCOhymUyNmpDEmtW/QR63u7mGplThlAj/GXYDxYe7q5rbDhE7ksye	\N	\N	\N	2026-04-13 17:43:28	2026-04-13 17:43:28	\N	\N	\N
USU_0016	PER_0016	luis.mamani@gmail.com	2026-04-13 17:43:28	$2y$12$7vb1WKsoG78aHGXZ7HdSAOTE9P7wLs1Vpg8zlZxvKw9lRDDTzP21a	\N	\N	\N	2026-04-13 17:43:28	2026-04-13 17:43:28	\N	\N	\N
USU_0017	PER_0017	gabriela.condori@gmail.com	2026-04-13 17:43:28	$2y$12$2ovEpE4mh4c.Zpa7KSfduuFOF6fM7YM3vEkJuu.pqO/OQy7iPFK2q	\N	\N	\N	2026-04-13 17:43:28	2026-04-13 17:43:28	\N	\N	\N
USU_0018	PER_0018	andres.quispe@gmail.com	2026-04-13 17:43:28	$2y$12$gGJ0jrqAUdaikt250c.onerF0JOEW4lJtTGc2.CQbKqM5U4hsUdCe	\N	\N	\N	2026-04-13 17:43:28	2026-04-13 17:43:28	\N	\N	\N
USU_0019	PER_0019	natalia.flores@gmail.com	2026-04-13 17:43:28	$2y$12$nC4ZYibLpmPzCcIDMDKhBuK2D6d7Ipq6hmtDAl3xFbwTI7z2Rby0q	\N	\N	\N	2026-04-13 17:43:28	2026-04-13 17:43:28	\N	\N	\N
USU_0020	PER_0020	cristian.rojas@gmail.com	2026-04-13 17:43:28	$2y$12$Lj9.XXnE9uJ0PT.w4rMZou9Zd4EGVS1koq4dMIoGclujOSaWOGzM2	\N	\N	\N	2026-04-13 17:43:28	2026-04-13 17:43:28	\N	\N	\N
USU_0001	PER_0001	asturizagavictor@gmail.com	2026-04-13 17:43:28	$2y$12$jP7EroXaMybdt.qdP1VkGOt8YwJUs6sAqRSeIxRNoKQCOGfr/ZCgC	luVeMrZgvJ0K33cABf3gIiK2xxVE92NGcbMbcYKup6Gt5uT5uEkBOCCqO2JV	\N	\N	2026-04-13 17:43:28	2026-04-13 18:12:41	\N	\N	\N
\.


--
-- Name: failed_jobs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: sail
--

SELECT pg_catalog.setval('public.failed_jobs_id_seq', 1, false);


--
-- Name: jobs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: sail
--

SELECT pg_catalog.setval('public.jobs_id_seq', 1, false);


--
-- Name: migrations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: sail
--

SELECT pg_catalog.setval('public.migrations_id_seq', 24, true);


--
-- Name: permissions_id_seq; Type: SEQUENCE SET; Schema: public; Owner: sail
--

SELECT pg_catalog.setval('public.permissions_id_seq', 24, true);


--
-- Name: personal_access_tokens_id_seq; Type: SEQUENCE SET; Schema: public; Owner: sail
--

SELECT pg_catalog.setval('public.personal_access_tokens_id_seq', 1, false);


--
-- Name: roles_id_seq; Type: SEQUENCE SET; Schema: public; Owner: sail
--

SELECT pg_catalog.setval('public.roles_id_seq', 6, true);


--
-- Name: asignatura asignatura_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.asignatura
    ADD CONSTRAINT asignatura_pkey PRIMARY KEY (cod_asi);


--
-- Name: bitacora bitacora_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.bitacora
    ADD CONSTRAINT bitacora_pkey PRIMARY KEY (cod_bit);


--
-- Name: cache_locks cache_locks_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.cache_locks
    ADD CONSTRAINT cache_locks_pkey PRIMARY KEY (key);


--
-- Name: cache cache_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.cache
    ADD CONSTRAINT cache_pkey PRIMARY KEY (key);


--
-- Name: calificacion calificacion_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.calificacion
    ADD CONSTRAINT calificacion_pkey PRIMARY KEY (cod_cal);


--
-- Name: curso curso_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.curso
    ADD CONSTRAINT curso_pkey PRIMARY KEY (cod_cur);


--
-- Name: docente docente_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.docente
    ADD CONSTRAINT docente_pkey PRIMARY KEY (cod_doc);


--
-- Name: especialidad_tecnica especialidad_tecnica_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.especialidad_tecnica
    ADD CONSTRAINT especialidad_tecnica_pkey PRIMARY KEY (cod_esp);


--
-- Name: estudiante estudiante_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.estudiante
    ADD CONSTRAINT estudiante_pkey PRIMARY KEY (cod_est);


--
-- Name: estudiante estudiante_rud_est_unique; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.estudiante
    ADD CONSTRAINT estudiante_rud_est_unique UNIQUE (rud_est);


--
-- Name: failed_jobs failed_jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_pkey PRIMARY KEY (id);


--
-- Name: failed_jobs failed_jobs_uuid_unique; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_uuid_unique UNIQUE (uuid);


--
-- Name: gestion_academica gestion_academica_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.gestion_academica
    ADD CONSTRAINT gestion_academica_pkey PRIMARY KEY (cod_gea);


--
-- Name: inscripcion_estudiante inscripcion_estudiante_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.inscripcion_estudiante
    ADD CONSTRAINT inscripcion_estudiante_pkey PRIMARY KEY (cod_ins);


--
-- Name: institucion_procedencia institucion_procedencia_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.institucion_procedencia
    ADD CONSTRAINT institucion_procedencia_pkey PRIMARY KEY (cod_ipe);


--
-- Name: job_batches job_batches_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.job_batches
    ADD CONSTRAINT job_batches_pkey PRIMARY KEY (id);


--
-- Name: jobs jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.jobs
    ADD CONSTRAINT jobs_pkey PRIMARY KEY (id);


--
-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);


--
-- Name: model_has_permissions model_has_permissions_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.model_has_permissions
    ADD CONSTRAINT model_has_permissions_pkey PRIMARY KEY (permission_id, cod_usu, model_type);


--
-- Name: model_has_roles model_has_roles_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.model_has_roles
    ADD CONSTRAINT model_has_roles_pkey PRIMARY KEY (role_id, cod_usu, model_type);


--
-- Name: paralelo paralelo_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.paralelo
    ADD CONSTRAINT paralelo_pkey PRIMARY KEY (cod_par);


--
-- Name: password_reset_tokens password_reset_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.password_reset_tokens
    ADD CONSTRAINT password_reset_tokens_pkey PRIMARY KEY (email);


--
-- Name: periodo_evaluacion periodo_evaluacion_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.periodo_evaluacion
    ADD CONSTRAINT periodo_evaluacion_pkey PRIMARY KEY (cod_pev);


--
-- Name: permissions permissions_name_guard_name_unique; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.permissions
    ADD CONSTRAINT permissions_name_guard_name_unique UNIQUE (name, guard_name);


--
-- Name: permissions permissions_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.permissions
    ADD CONSTRAINT permissions_pkey PRIMARY KEY (id);


--
-- Name: persona persona_ci_per_unique; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.persona
    ADD CONSTRAINT persona_ci_per_unique UNIQUE (ci_per);


--
-- Name: persona persona_ema_per_unique; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.persona
    ADD CONSTRAINT persona_ema_per_unique UNIQUE (ema_per);


--
-- Name: persona persona_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.persona
    ADD CONSTRAINT persona_pkey PRIMARY KEY (cod_per);


--
-- Name: personal_access_tokens personal_access_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.personal_access_tokens
    ADD CONSTRAINT personal_access_tokens_pkey PRIMARY KEY (id);


--
-- Name: personal_access_tokens personal_access_tokens_token_unique; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.personal_access_tokens
    ADD CONSTRAINT personal_access_tokens_token_unique UNIQUE (token);


--
-- Name: personal_institucional personal_institucional_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.personal_institucional
    ADD CONSTRAINT personal_institucional_pkey PRIMARY KEY (cod_pin);


--
-- Name: plan_asignatura plan_asignatura_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.plan_asignatura
    ADD CONSTRAINT plan_asignatura_pkey PRIMARY KEY (cod_pas);


--
-- Name: role_has_permissions role_has_permissions_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.role_has_permissions
    ADD CONSTRAINT role_has_permissions_pkey PRIMARY KEY (permission_id, role_id);


--
-- Name: roles roles_name_guard_name_unique; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_name_guard_name_unique UNIQUE (name, guard_name);


--
-- Name: roles roles_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_pkey PRIMARY KEY (id);


--
-- Name: secretaria_general secretaria_general_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.secretaria_general
    ADD CONSTRAINT secretaria_general_pkey PRIMARY KEY (cod_sge);


--
-- Name: sessions sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.sessions
    ADD CONSTRAINT sessions_pkey PRIMARY KEY (id);


--
-- Name: tipo_vinculacion_estudiante tipo_vinculacion_estudiante_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.tipo_vinculacion_estudiante
    ADD CONSTRAINT tipo_vinculacion_estudiante_pkey PRIMARY KEY (cod_tve);


--
-- Name: turno turno_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.turno
    ADD CONSTRAINT turno_pkey PRIMARY KEY (cod_tur);


--
-- Name: users users_cod_per_unique; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_cod_per_unique UNIQUE (cod_per);


--
-- Name: users users_email_unique; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_unique UNIQUE (email);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (cod_usu);


--
-- Name: cache_expiration_index; Type: INDEX; Schema: public; Owner: sail
--

CREATE INDEX cache_expiration_index ON public.cache USING btree (expiration);


--
-- Name: cache_locks_expiration_index; Type: INDEX; Schema: public; Owner: sail
--

CREATE INDEX cache_locks_expiration_index ON public.cache_locks USING btree (expiration);


--
-- Name: jobs_queue_index; Type: INDEX; Schema: public; Owner: sail
--

CREATE INDEX jobs_queue_index ON public.jobs USING btree (queue);


--
-- Name: model_has_permissions_model_id_model_type_index; Type: INDEX; Schema: public; Owner: sail
--

CREATE INDEX model_has_permissions_model_id_model_type_index ON public.model_has_permissions USING btree (cod_usu, model_type);


--
-- Name: model_has_roles_model_id_model_type_index; Type: INDEX; Schema: public; Owner: sail
--

CREATE INDEX model_has_roles_model_id_model_type_index ON public.model_has_roles USING btree (cod_usu, model_type);


--
-- Name: personal_access_tokens_expires_at_index; Type: INDEX; Schema: public; Owner: sail
--

CREATE INDEX personal_access_tokens_expires_at_index ON public.personal_access_tokens USING btree (expires_at);


--
-- Name: personal_access_tokens_tokenable_type_tokenable_id_index; Type: INDEX; Schema: public; Owner: sail
--

CREATE INDEX personal_access_tokens_tokenable_type_tokenable_id_index ON public.personal_access_tokens USING btree (tokenable_type, tokenable_id);


--
-- Name: sessions_last_activity_index; Type: INDEX; Schema: public; Owner: sail
--

CREATE INDEX sessions_last_activity_index ON public.sessions USING btree (last_activity);


--
-- Name: sessions_user_id_index; Type: INDEX; Schema: public; Owner: sail
--

CREATE INDEX sessions_user_id_index ON public.sessions USING btree (user_id);


--
-- Name: bitacora bitacora_cod_usu_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.bitacora
    ADD CONSTRAINT bitacora_cod_usu_foreign FOREIGN KEY (cod_usu) REFERENCES public.users(cod_usu) ON DELETE SET NULL;


--
-- Name: calificacion calificacion_cod_asi_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.calificacion
    ADD CONSTRAINT calificacion_cod_asi_foreign FOREIGN KEY (cod_asi) REFERENCES public.asignatura(cod_asi);


--
-- Name: calificacion calificacion_cod_est_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.calificacion
    ADD CONSTRAINT calificacion_cod_est_foreign FOREIGN KEY (cod_est) REFERENCES public.estudiante(cod_est) ON DELETE CASCADE;


--
-- Name: calificacion calificacion_cod_pev_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.calificacion
    ADD CONSTRAINT calificacion_cod_pev_foreign FOREIGN KEY (cod_pev) REFERENCES public.periodo_evaluacion(cod_pev);


--
-- Name: docente docente_cod_pin_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.docente
    ADD CONSTRAINT docente_cod_pin_foreign FOREIGN KEY (cod_pin) REFERENCES public.personal_institucional(cod_pin) ON DELETE CASCADE;


--
-- Name: estudiante estudiante_cod_esp_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.estudiante
    ADD CONSTRAINT estudiante_cod_esp_foreign FOREIGN KEY (cod_esp) REFERENCES public.especialidad_tecnica(cod_esp);


--
-- Name: estudiante estudiante_cod_ipe_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.estudiante
    ADD CONSTRAINT estudiante_cod_ipe_foreign FOREIGN KEY (cod_ipe) REFERENCES public.institucion_procedencia(cod_ipe);


--
-- Name: estudiante estudiante_cod_per_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.estudiante
    ADD CONSTRAINT estudiante_cod_per_foreign FOREIGN KEY (cod_per) REFERENCES public.persona(cod_per) ON DELETE CASCADE;


--
-- Name: estudiante estudiante_cod_tve_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.estudiante
    ADD CONSTRAINT estudiante_cod_tve_foreign FOREIGN KEY (cod_tve) REFERENCES public.tipo_vinculacion_estudiante(cod_tve);


--
-- Name: inscripcion_estudiante inscripcion_estudiante_cod_cur_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.inscripcion_estudiante
    ADD CONSTRAINT inscripcion_estudiante_cod_cur_foreign FOREIGN KEY (cod_cur) REFERENCES public.curso(cod_cur);


--
-- Name: inscripcion_estudiante inscripcion_estudiante_cod_est_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.inscripcion_estudiante
    ADD CONSTRAINT inscripcion_estudiante_cod_est_foreign FOREIGN KEY (cod_est) REFERENCES public.estudiante(cod_est) ON DELETE CASCADE;


--
-- Name: inscripcion_estudiante inscripcion_estudiante_cod_gea_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.inscripcion_estudiante
    ADD CONSTRAINT inscripcion_estudiante_cod_gea_foreign FOREIGN KEY (cod_gea) REFERENCES public.gestion_academica(cod_gea);


--
-- Name: inscripcion_estudiante inscripcion_estudiante_cod_par_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.inscripcion_estudiante
    ADD CONSTRAINT inscripcion_estudiante_cod_par_foreign FOREIGN KEY (cod_par) REFERENCES public.paralelo(cod_par);


--
-- Name: inscripcion_estudiante inscripcion_estudiante_cod_tur_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.inscripcion_estudiante
    ADD CONSTRAINT inscripcion_estudiante_cod_tur_foreign FOREIGN KEY (cod_tur) REFERENCES public.turno(cod_tur);


--
-- Name: model_has_permissions model_has_permissions_permission_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.model_has_permissions
    ADD CONSTRAINT model_has_permissions_permission_id_foreign FOREIGN KEY (permission_id) REFERENCES public.permissions(id) ON DELETE CASCADE;


--
-- Name: model_has_roles model_has_roles_role_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.model_has_roles
    ADD CONSTRAINT model_has_roles_role_id_foreign FOREIGN KEY (role_id) REFERENCES public.roles(id) ON DELETE CASCADE;


--
-- Name: paralelo paralelo_cod_cur_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.paralelo
    ADD CONSTRAINT paralelo_cod_cur_foreign FOREIGN KEY (cod_cur) REFERENCES public.curso(cod_cur) ON DELETE CASCADE;


--
-- Name: personal_institucional personal_institucional_cod_per_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.personal_institucional
    ADD CONSTRAINT personal_institucional_cod_per_foreign FOREIGN KEY (cod_per) REFERENCES public.persona(cod_per) ON DELETE CASCADE;


--
-- Name: plan_asignatura plan_asignatura_cod_asi_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.plan_asignatura
    ADD CONSTRAINT plan_asignatura_cod_asi_foreign FOREIGN KEY (cod_asi) REFERENCES public.asignatura(cod_asi);


--
-- Name: plan_asignatura plan_asignatura_cod_cur_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.plan_asignatura
    ADD CONSTRAINT plan_asignatura_cod_cur_foreign FOREIGN KEY (cod_cur) REFERENCES public.curso(cod_cur);


--
-- Name: plan_asignatura plan_asignatura_cod_doc_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.plan_asignatura
    ADD CONSTRAINT plan_asignatura_cod_doc_foreign FOREIGN KEY (cod_doc) REFERENCES public.docente(cod_doc);


--
-- Name: plan_asignatura plan_asignatura_cod_gea_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.plan_asignatura
    ADD CONSTRAINT plan_asignatura_cod_gea_foreign FOREIGN KEY (cod_gea) REFERENCES public.gestion_academica(cod_gea);


--
-- Name: plan_asignatura plan_asignatura_cod_par_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.plan_asignatura
    ADD CONSTRAINT plan_asignatura_cod_par_foreign FOREIGN KEY (cod_par) REFERENCES public.paralelo(cod_par);


--
-- Name: plan_asignatura plan_asignatura_cod_tur_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.plan_asignatura
    ADD CONSTRAINT plan_asignatura_cod_tur_foreign FOREIGN KEY (cod_tur) REFERENCES public.turno(cod_tur);


--
-- Name: role_has_permissions role_has_permissions_permission_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.role_has_permissions
    ADD CONSTRAINT role_has_permissions_permission_id_foreign FOREIGN KEY (permission_id) REFERENCES public.permissions(id) ON DELETE CASCADE;


--
-- Name: role_has_permissions role_has_permissions_role_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.role_has_permissions
    ADD CONSTRAINT role_has_permissions_role_id_foreign FOREIGN KEY (role_id) REFERENCES public.roles(id) ON DELETE CASCADE;


--
-- Name: secretaria_general secretaria_general_cod_pin_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.secretaria_general
    ADD CONSTRAINT secretaria_general_cod_pin_foreign FOREIGN KEY (cod_pin) REFERENCES public.personal_institucional(cod_pin) ON DELETE CASCADE;


--
-- Name: users users_cod_per_foreign; Type: FK CONSTRAINT; Schema: public; Owner: sail
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_cod_per_foreign FOREIGN KEY (cod_per) REFERENCES public.persona(cod_per) ON DELETE CASCADE;


--
-- PostgreSQL database dump complete
--

\unrestrict NCfdMCGv5ilRpE7reNpbY7e4fXeuRhS18bcSpSRgm9GKYLTFqs0ScGRKRmFlKTY

