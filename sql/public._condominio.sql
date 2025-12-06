/*
 Navicat Premium Data Transfer

 Source Server         : bunkermatic
 Source Server Type    : PostgreSQL
 Source Server Version : 100023 (100023)
 Source Host           : 162.216.113.82:5432
 Source Catalog        : rhodium_txcondominio
 Source Schema         : public

 Target Server Type    : PostgreSQL
 Target Server Version : 100023 (100023)
 File Encoding         : 65001

 Date: 28/08/2025 20:03:57
*/


-- ----------------------------
-- Type structure for estado_movimiento
-- ----------------------------
DROP TYPE IF EXISTS "public"."estado_movimiento";
CREATE TYPE "public"."estado_movimiento" AS ENUM (
  'pendiente',
  'conciliado',
  'cancelado',
  'cerrado',
  'abierto'
);
ALTER TYPE "public"."estado_movimiento" OWNER TO "rhodium_roger";

-- ----------------------------
-- Sequence structure for administradores_id_administrador_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."administradores_id_administrador_seq";
CREATE SEQUENCE "public"."administradores_id_administrador_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 2147483647
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for auditoria_id_auditoria_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."auditoria_id_auditoria_seq";
CREATE SEQUENCE "public"."auditoria_id_auditoria_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 2147483647
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for cierre_contable_id_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."cierre_contable_id_seq";
CREATE SEQUENCE "public"."cierre_contable_id_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 9223372036854775807
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for condominio_id_condominio_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."condominio_id_condominio_seq";
CREATE SEQUENCE "public"."condominio_id_condominio_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 2147483647
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for credito_a_favor_id_credito_a_favor_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."credito_a_favor_id_credito_a_favor_seq";
CREATE SEQUENCE "public"."credito_a_favor_id_credito_a_favor_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 2147483647
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for cuenta_id_cuenta_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."cuenta_id_cuenta_seq";
CREATE SEQUENCE "public"."cuenta_id_cuenta_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 2147483647
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for distribucion_gasto_id_distribucion_gasto_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."distribucion_gasto_id_distribucion_gasto_seq";
CREATE SEQUENCE "public"."distribucion_gasto_id_distribucion_gasto_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 2147483647
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for ejecucion_gasto_id_ejecucion_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."ejecucion_gasto_id_ejecucion_seq";
CREATE SEQUENCE "public"."ejecucion_gasto_id_ejecucion_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 2147483647
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for esquema_pronto_pago_id_esquema_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."esquema_pronto_pago_id_esquema_seq";
CREATE SEQUENCE "public"."esquema_pronto_pago_id_esquema_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 2147483647
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for exoneracion_notificacion_id_exoneracion_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."exoneracion_notificacion_id_exoneracion_seq";
CREATE SEQUENCE "public"."exoneracion_notificacion_id_exoneracion_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 2147483647
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for gasto_id_gasto_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."gasto_id_gasto_seq";
CREATE SEQUENCE "public"."gasto_id_gasto_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 2147483647
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for inmueble_id_inmueble_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."inmueble_id_inmueble_seq";
CREATE SEQUENCE "public"."inmueble_id_inmueble_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 2147483647
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for log_distribucion_id_log_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."log_distribucion_id_log_seq";
CREATE SEQUENCE "public"."log_distribucion_id_log_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 2147483647
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for moneda_id_moneda_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."moneda_id_moneda_seq";
CREATE SEQUENCE "public"."moneda_id_moneda_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 2147483647
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for movimiento_destino_fondos_id_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."movimiento_destino_fondos_id_seq";
CREATE SEQUENCE "public"."movimiento_destino_fondos_id_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 9223372036854775807
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for movimiento_detalle_egreso_id_detalle_egreso_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."movimiento_detalle_egreso_id_detalle_egreso_seq";
CREATE SEQUENCE "public"."movimiento_detalle_egreso_id_detalle_egreso_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 2147483647
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for movimiento_detalle_ingreso_id_detalle_ingreso_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."movimiento_detalle_ingreso_id_detalle_ingreso_seq";
CREATE SEQUENCE "public"."movimiento_detalle_ingreso_id_detalle_ingreso_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 2147483647
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for movimiento_general_id_movimiento_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."movimiento_general_id_movimiento_seq";
CREATE SEQUENCE "public"."movimiento_general_id_movimiento_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 2147483647
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for movimiento_general_id_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."movimiento_general_id_seq";
CREATE SEQUENCE "public"."movimiento_general_id_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 9223372036854775807
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for movimiento_origen_fondos_id_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."movimiento_origen_fondos_id_seq";
CREATE SEQUENCE "public"."movimiento_origen_fondos_id_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 9223372036854775807
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for movimientos_caja_banco_id_movimiento_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."movimientos_caja_banco_id_movimiento_seq";
CREATE SEQUENCE "public"."movimientos_caja_banco_id_movimiento_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 9223372036854775807
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for notificacion_cobro_detalle_id_detalle_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."notificacion_cobro_detalle_id_detalle_seq";
CREATE SEQUENCE "public"."notificacion_cobro_detalle_id_detalle_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 2147483647
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for notificacion_cobro_detalle_master_id_detalle_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."notificacion_cobro_detalle_master_id_detalle_seq";
CREATE SEQUENCE "public"."notificacion_cobro_detalle_master_id_detalle_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 9223372036854775807
START 11
CACHE 1;

-- ----------------------------
-- Sequence structure for notificacion_cobro_detalle_master_id_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."notificacion_cobro_detalle_master_id_seq";
CREATE SEQUENCE "public"."notificacion_cobro_detalle_master_id_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 9223372036854775807
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for notificacion_cobro_inmueble_id_notificacion_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."notificacion_cobro_inmueble_id_notificacion_seq";
CREATE SEQUENCE "public"."notificacion_cobro_inmueble_id_notificacion_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 2147483647
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for notificacion_cobro_master_id_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."notificacion_cobro_master_id_seq";
CREATE SEQUENCE "public"."notificacion_cobro_master_id_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 9223372036854775807
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for notificaciones_cobro_id_notificacion_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."notificaciones_cobro_id_notificacion_seq";
CREATE SEQUENCE "public"."notificaciones_cobro_id_notificacion_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 2147483647
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for pago_id_pago_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."pago_id_pago_seq";
CREATE SEQUENCE "public"."pago_id_pago_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 2147483647
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for plan_cuenta_base_id_plan_base_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."plan_cuenta_base_id_plan_base_seq";
CREATE SEQUENCE "public"."plan_cuenta_base_id_plan_base_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 2147483647
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for plan_cuenta_id_plan_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."plan_cuenta_id_plan_seq";
CREATE SEQUENCE "public"."plan_cuenta_id_plan_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 2147483647
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for propietario_id_propietario_seq1
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."propietario_id_propietario_seq1";
CREATE SEQUENCE "public"."propietario_id_propietario_seq1" 
INCREMENT 1
MINVALUE  1
MAXVALUE 2147483647
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for recibo_cabecera_id_recibo_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."recibo_cabecera_id_recibo_seq";
CREATE SEQUENCE "public"."recibo_cabecera_id_recibo_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 2147483647
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for recibo_cabecera_numero_recibo_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."recibo_cabecera_numero_recibo_seq";
CREATE SEQUENCE "public"."recibo_cabecera_numero_recibo_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 999999
START 226
CACHE 1;

-- ----------------------------
-- Sequence structure for recibo_destino_fondos_id_destino_fondos_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."recibo_destino_fondos_id_destino_fondos_seq";
CREATE SEQUENCE "public"."recibo_destino_fondos_id_destino_fondos_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 2147483647
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for recibo_origen_fondos_id_origen_fondos_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."recibo_origen_fondos_id_origen_fondos_seq";
CREATE SEQUENCE "public"."recibo_origen_fondos_id_origen_fondos_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 2147483647
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for rol_id_rol_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."rol_id_rol_seq";
CREATE SEQUENCE "public"."rol_id_rol_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 2147483647
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for tipo_cambio_id_tipo_cambio_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."tipo_cambio_id_tipo_cambio_seq";
CREATE SEQUENCE "public"."tipo_cambio_id_tipo_cambio_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 2147483647
START 1
CACHE 1;

-- ----------------------------
-- Table structure for administradores
-- ----------------------------
DROP TABLE IF EXISTS "public"."administradores";
CREATE TABLE "public"."administradores" (
  "id_administrador" int4 NOT NULL DEFAULT nextval('administradores_id_administrador_seq'::regclass),
  "id_usuario" int4,
  "id_condominio" int4,
  "id_cargo" int4,
  "fecha_desde" timestamp(6),
  "fecha_hasta" timestamp(6),
  "estatus" bool DEFAULT true
)
;

-- ----------------------------
-- Records of administradores
-- ----------------------------
INSERT INTO "public"."administradores" VALUES (1, 2, 5, NULL, '2025-04-03 08:04:18', NULL, 't');
INSERT INTO "public"."administradores" VALUES (2, 1, 7, NULL, '2025-04-03 14:17:17', NULL, 't');

-- ----------------------------
-- Table structure for auditoria
-- ----------------------------
DROP TABLE IF EXISTS "public"."auditoria";
CREATE TABLE "public"."auditoria" (
  "id_auditoria" int4 NOT NULL DEFAULT nextval('auditoria_id_auditoria_seq'::regclass),
  "tabla_afectada" varchar(255) COLLATE "pg_catalog"."default" NOT NULL,
  "campo_modificado" varchar(255) COLLATE "pg_catalog"."default" NOT NULL,
  "valor_anterior" text COLLATE "pg_catalog"."default",
  "valor_nuevo" text COLLATE "pg_catalog"."default",
  "id_usuario" int4,
  "fecha_modificacion" timestamp(6) DEFAULT CURRENT_TIMESTAMP
)
;

-- ----------------------------
-- Records of auditoria
-- ----------------------------

-- ----------------------------
-- Table structure for cierre_contable
-- ----------------------------
DROP TABLE IF EXISTS "public"."cierre_contable";
CREATE TABLE "public"."cierre_contable" (
  "id_cierre" int4 NOT NULL DEFAULT nextval('cierre_contable_id_seq'::regclass),
  "id_condominio" int4,
  "anio" int4 NOT NULL,
  "mes" int4 NOT NULL,
  "id_moneda" int4 NOT NULL,
  "ingresos" numeric(15,2) NOT NULL DEFAULT 0,
  "egresos" numeric(15,2) NOT NULL DEFAULT 0,
  "saldo_inicial" numeric(15,2) NOT NULL DEFAULT 0,
  "saldo_final" numeric(15,2) NOT NULL DEFAULT 0,
  "estado" "menu_login"."estado_movimiento" NOT NULL DEFAULT 'abierto'::menu_login.estado_movimiento,
  "fecha_cierre" date,
  "fecha_creacion" timestamp(6) DEFAULT CURRENT_TIMESTAMP,
  "fecha_actualizacion" timestamp(6) DEFAULT CURRENT_TIMESTAMP
)
;

-- ----------------------------
-- Records of cierre_contable
-- ----------------------------

-- ----------------------------
-- Table structure for condominio
-- ----------------------------
DROP TABLE IF EXISTS "public"."condominio";
CREATE TABLE "public"."condominio" (
  "id_condominio" int4 NOT NULL DEFAULT nextval('condominio_id_condominio_seq'::regclass),
  "nombre" varchar(255) COLLATE "pg_catalog"."default" NOT NULL,
  "direccion" text COLLATE "pg_catalog"."default" NOT NULL,
  "id_moneda" int4 NOT NULL,
  "esquema_cuota" varchar(20) COLLATE "pg_catalog"."default" NOT NULL,
  "estado" bool DEFAULT true,
  "fecha_creacion" timestamp(6) DEFAULT CURRENT_TIMESTAMP,
  "fecha_actualizacion" timestamp(6) DEFAULT CURRENT_TIMESTAMP,
  "cant_inmuebles" int4,
  "url_logo_izquierda" varchar(255) COLLATE "pg_catalog"."default",
  "url_logo_derecha" varchar(255) COLLATE "pg_catalog"."default",
  "linea_1" varchar(255) COLLATE "pg_catalog"."default",
  "linea_2" varchar(255) COLLATE "pg_catalog"."default",
  "linea_3" varchar(255) COLLATE "pg_catalog"."default"
)
;

-- ----------------------------
-- Records of condominio
-- ----------------------------
INSERT INTO "public"."condominio" VALUES (7, 'EDIF. VILLAS DEL PARQUE', 'CALICANTO', 1, 'alicuota', 't', '2025-04-03 14:16:53.532617', '2025-04-03 14:16:53.532617', 42, 'assets/images/LOGO_SEMATPC_300.fw.png', 'assets/images/LOGO_SEMATPC_300.fw.png', NULL, NULL, NULL);
INSERT INTO "public"."condominio" VALUES (5, 'VILLAS ANTILLANAS', 'ESTE II', 2, 'fija', 't', '2025-04-01 20:54:08.207886', '2025-04-01 20:54:08.207886', 168, 'assets/images/LOGO_SEMATPC_300.fw.png', 'assets/images/LOGO_SEMATPC_300.fw.png', 'JUNTA DE CONDOMINIOS DE LA URB. VILLAS ANTILLANAS', 'CALLE ESTE I PARCELA 56 TUMERO', 'J-123456789-9');

-- ----------------------------
-- Table structure for credito_a_favor
-- ----------------------------
DROP TABLE IF EXISTS "public"."credito_a_favor";
CREATE TABLE "public"."credito_a_favor" (
  "id_credito_a_favor" int4 NOT NULL DEFAULT nextval('credito_a_favor_id_credito_a_favor_seq'::regclass),
  "id_propietario" int4 NOT NULL,
  "id_moneda" int4 NOT NULL,
  "monto" numeric(53,0) NOT NULL,
  "origen" varchar(255) COLLATE "pg_catalog"."default",
  "estado" varchar(50) COLLATE "pg_catalog"."default" DEFAULT 'activo'::character varying,
  "fecha_uso" timestamp(6),
  "fecha_creacion" timestamp(6) DEFAULT CURRENT_TIMESTAMP,
  "fecha_actualizacion" timestamp(6) DEFAULT CURRENT_TIMESTAMP,
  "id_origen" int4,
  "id_inmueble" int4,
  "tipo_operacion" varchar(20) COLLATE "pg_catalog"."default"
)
;

-- ----------------------------
-- Records of credito_a_favor
-- ----------------------------
INSERT INTO "public"."credito_a_favor" VALUES (78, 2, 2, 65, 'Excedente de Pago', 'activo', NULL, '2025-08-24 13:56:53.894655', '2025-08-24 13:56:53.894655', NULL, 7, NULL);

-- ----------------------------
-- Table structure for cuenta
-- ----------------------------
DROP TABLE IF EXISTS "public"."cuenta";
CREATE TABLE "public"."cuenta" (
  "id_cuenta" int4 NOT NULL DEFAULT nextval('cuenta_id_cuenta_seq'::regclass),
  "id_condominio" int4 NOT NULL,
  "nombre" varchar(255) COLLATE "pg_catalog"."default" NOT NULL,
  "tipo" varchar(20) COLLATE "pg_catalog"."default" NOT NULL DEFAULT 'banco'::character varying,
  "id_moneda" int4 NOT NULL,
  "saldo_actual" numeric(15,2) NOT NULL DEFAULT 0,
  "numero_cuenta_cliente" varchar(30) COLLATE "pg_catalog"."default",
  "banco" varchar(100) COLLATE "pg_catalog"."default",
  "estado" bool NOT NULL DEFAULT true,
  "fecha_creacion" timestamp(6) DEFAULT CURRENT_TIMESTAMP,
  "fecha_actualizacion" timestamp(6) DEFAULT CURRENT_TIMESTAMP
)
;

-- ----------------------------
-- Records of cuenta
-- ----------------------------
INSERT INTO "public"."cuenta" VALUES (5, 5, 'DÓLARES EFECTIVO', 'efectivo', 2, 456.00, NULL, NULL, 't', '2025-04-18 08:06:35.851989', '2025-04-18 08:06:35.851989');
INSERT INTO "public"."cuenta" VALUES (2, 5, 'BOLIVARES EFECTIVO', 'efectivo', 1, 35333.00, NULL, NULL, 't', '2025-04-18 08:06:01.827211', '2025-04-18 08:06:01.827211');
INSERT INTO "public"."cuenta" VALUES (1, 5, 'CUENTA BFC', 'banco', 1, 0.00, '12345678912345678910', 'BFC', 't', '2025-04-18 08:05:35.685599', '2025-04-18 08:05:35.685599');
INSERT INTO "public"."cuenta" VALUES (6, 7, 'CUENTA BANESCO', 'banco', 1, 0.00, '00000000000000000012', 'BANESCO', 't', '2025-04-18 08:05:35.685599', '2025-04-18 08:05:35.685599');
INSERT INTO "public"."cuenta" VALUES (8, 7, 'DÓLARES EFECTIVO', 'efectivo', 2, 0.00, NULL, NULL, 't', '2025-04-18 08:06:35.851989', '2025-04-18 08:06:35.851989');
INSERT INTO "public"."cuenta" VALUES (7, 7, 'BOLIVARES EFECTIVO', 'efectivo', 1, 0.00, NULL, NULL, 't', '2025-04-18 08:06:01.827211', '2025-04-18 08:06:01.827211');

-- ----------------------------
-- Table structure for distribucion_gasto
-- ----------------------------
DROP TABLE IF EXISTS "public"."distribucion_gasto";
CREATE TABLE "public"."distribucion_gasto" (
  "id_distribucion_gasto" int4 NOT NULL DEFAULT nextval('distribucion_gasto_id_distribucion_gasto_seq'::regclass),
  "id_gasto" int4 NOT NULL,
  "mes" date NOT NULL,
  "porcentaje" numeric(5,2) NOT NULL,
  "monto" numeric(15,2) NOT NULL,
  "monto_moneda_base" numeric(15,2) NOT NULL DEFAULT 0,
  "estado" varchar(50) COLLATE "pg_catalog"."default" DEFAULT 'pendiente'::character varying,
  "fecha_creacion" timestamp(6) DEFAULT CURRENT_TIMESTAMP,
  "fecha_actualizacion" timestamp(6) DEFAULT CURRENT_TIMESTAMP
)
;

-- ----------------------------
-- Records of distribucion_gasto
-- ----------------------------

-- ----------------------------
-- Table structure for ejecucion_gasto
-- ----------------------------
DROP TABLE IF EXISTS "public"."ejecucion_gasto";
CREATE TABLE "public"."ejecucion_gasto" (
  "id_ejecucion" int4 NOT NULL DEFAULT nextval('ejecucion_gasto_id_ejecucion_seq'::regclass),
  "id_condominio" int4 NOT NULL,
  "id_plan_cuenta" int4 NOT NULL,
  "anio" int4 NOT NULL,
  "mes" int4 NOT NULL,
  "descripcion" text COLLATE "pg_catalog"."default",
  "monto" float4 NOT NULL,
  "id_cuenta" int4 NOT NULL,
  "fecha_ejecucion" date NOT NULL DEFAULT CURRENT_DATE,
  "fecha_creacion" timestamp(6) DEFAULT CURRENT_TIMESTAMP,
  "tasa" numeric(15,6) NOT NULL DEFAULT 1.0,
  "monto_base" numeric(15,2) NOT NULL DEFAULT 0.0,
  "estado" varchar(20) COLLATE "pg_catalog"."default" NOT NULL DEFAULT 'pendiente'::character varying,
  "id_comprobante_egreso" int4,
  "id_moneda" int4
)
;

-- ----------------------------
-- Records of ejecucion_gasto
-- ----------------------------

-- ----------------------------
-- Table structure for esquema_pronto_pago
-- ----------------------------
DROP TABLE IF EXISTS "public"."esquema_pronto_pago";
CREATE TABLE "public"."esquema_pronto_pago" (
  "id_esquema" int4 NOT NULL DEFAULT nextval('esquema_pronto_pago_id_esquema_seq'::regclass),
  "id_condominio" int4 NOT NULL,
  "descripcion" varchar(255) COLLATE "pg_catalog"."default",
  "valido_desde" date NOT NULL,
  "valido_hasta" date,
  "dia_inicio" int4 NOT NULL,
  "dia_fin" int4 NOT NULL,
  "porcentaje_descuento" numeric(5,2) NOT NULL,
  "aplica_morosos" bool NOT NULL DEFAULT false,
  "estado" bool DEFAULT true,
  "fecha_creacion" timestamp(6) DEFAULT CURRENT_TIMESTAMP,
  "fecha_actualizacion" timestamp(6) DEFAULT CURRENT_TIMESTAMP
)
;

-- ----------------------------
-- Records of esquema_pronto_pago
-- ----------------------------

-- ----------------------------
-- Table structure for exoneracion_notificacion
-- ----------------------------
DROP TABLE IF EXISTS "public"."exoneracion_notificacion";
CREATE TABLE "public"."exoneracion_notificacion" (
  "id_exoneracion" int4 NOT NULL DEFAULT nextval('exoneracion_notificacion_id_exoneracion_seq'::regclass),
  "id_notificacion" int4 NOT NULL,
  "id_responsable_directivo" int4 NOT NULL,
  "numero_acta" varchar(50) COLLATE "pg_catalog"."default",
  "observaciones" text COLLATE "pg_catalog"."default",
  "fecha_exoneracion" date NOT NULL DEFAULT CURRENT_DATE,
  "estado" bool DEFAULT true,
  "fecha_creacion" timestamp(6) DEFAULT CURRENT_TIMESTAMP,
  "fecha_actualizacion" timestamp(6) DEFAULT CURRENT_TIMESTAMP
)
;

-- ----------------------------
-- Records of exoneracion_notificacion
-- ----------------------------

-- ----------------------------
-- Table structure for gasto
-- ----------------------------
DROP TABLE IF EXISTS "public"."gasto";
CREATE TABLE "public"."gasto" (
  "id_gasto" int4 NOT NULL DEFAULT nextval('gasto_id_gasto_seq'::regclass),
  "id_condominio" int4 NOT NULL,
  "nombre" varchar(255) COLLATE "pg_catalog"."default" NOT NULL,
  "descripcion" text COLLATE "pg_catalog"."default",
  "monto" numeric(15,2) NOT NULL,
  "id_moneda" int4 NOT NULL,
  "tipo" varchar(50) COLLATE "pg_catalog"."default" NOT NULL,
  "metodo_distribucion" varchar(50) COLLATE "pg_catalog"."default" NOT NULL,
  "fecha_inicio" date NOT NULL,
  "fecha_fin" date,
  "estado" bool DEFAULT true,
  "fecha_creacion" timestamp(6) DEFAULT CURRENT_TIMESTAMP,
  "fecha_actualizacion" timestamp(6) DEFAULT CURRENT_TIMESTAMP
)
;

-- ----------------------------
-- Records of gasto
-- ----------------------------

-- ----------------------------
-- Table structure for inmueble
-- ----------------------------
DROP TABLE IF EXISTS "public"."inmueble";
CREATE TABLE "public"."inmueble" (
  "id_inmueble" int4 NOT NULL DEFAULT nextval('inmueble_id_inmueble_seq'::regclass),
  "id_condominio" int4 NOT NULL,
  "id_usuario" int4,
  "alicuota" numeric(24,10) NOT NULL DEFAULT 0,
  "estado" bool DEFAULT true,
  "fecha_creacion" timestamp(6) DEFAULT CURRENT_TIMESTAMP,
  "fecha_actualizacion" timestamp(6) DEFAULT CURRENT_TIMESTAMP,
  "correlativo" int2,
  "identificacion" varchar(20) COLLATE "pg_catalog"."default",
  "torre" varchar(255) COLLATE "pg_catalog"."default",
  "piso" varchar(255) COLLATE "pg_catalog"."default",
  "manzana" varchar(255) COLLATE "pg_catalog"."default",
  "calle" varchar(255) COLLATE "pg_catalog"."default",
  "avenida" varchar(255) COLLATE "pg_catalog"."default",
  "tipo" varchar(255) COLLATE "pg_catalog"."default"
)
;

-- ----------------------------
-- Records of inmueble
-- ----------------------------
INSERT INTO "public"."inmueble" VALUES (2, 5, NULL, 0.1300000000, 't', '2025-04-05 19:48:49.311651', '2025-04-06 07:53:32.502494', 3, 'B-1', 'TORRE', '22', '', '', NULL, '1');
INSERT INTO "public"."inmueble" VALUES (1, 5, NULL, 0.1100000000, 't', '2025-04-25 09:53:55.508655', '2025-04-25 09:53:55.508655', 7, 'A-1', '4', '12', '', '', NULL, '1');
INSERT INTO "public"."inmueble" VALUES (6, 5, NULL, 0.1700000000, 't', '2025-06-08 13:55:21.696179', '2025-06-08 13:55:21.696179', 7, 'C-2', '1', '12', '', '', NULL, '1');
INSERT INTO "public"."inmueble" VALUES (3, 5, NULL, 0.0900000000, 't', '2025-04-07 11:36:36.360571', '2025-04-07 11:36:36.360571', 5, 'A-2', '2', '12', '', '', NULL, '1');
INSERT INTO "public"."inmueble" VALUES (5, 5, NULL, 0.0700000000, 't', '2025-04-06 10:34:42.748953', '2025-04-06 12:29:12.171715', 4, 'C-1', '', '', '', '52', NULL, '2');
INSERT INTO "public"."inmueble" VALUES (7, 5, NULL, 0.2900000000, 't', '2025-04-05 17:55:05.635679', '2025-08-23 17:13:40.799032', 1, 'PH', '9', '9', 'A', '9', NULL, '2');
INSERT INTO "public"."inmueble" VALUES (4, 5, NULL, 0.1400000000, 't', '2025-04-06 12:28:52.381952', '2025-04-06 12:28:52.381952', 2, 'B-2', '6', '3', '', '', NULL, '1');

-- ----------------------------
-- Table structure for log_distribucion
-- ----------------------------
DROP TABLE IF EXISTS "public"."log_distribucion";
CREATE TABLE "public"."log_distribucion" (
  "id_log" int4 NOT NULL DEFAULT nextval('log_distribucion_id_log_seq'::regclass),
  "id_notificacion_master" int4 NOT NULL,
  "fecha_distribucion" timestamp(6) NOT NULL,
  "tipo" varchar(10) COLLATE "pg_catalog"."default" NOT NULL
)
;

-- ----------------------------
-- Records of log_distribucion
-- ----------------------------

-- ----------------------------
-- Table structure for moneda
-- ----------------------------
DROP TABLE IF EXISTS "public"."moneda";
CREATE TABLE "public"."moneda" (
  "id_moneda" int4 NOT NULL DEFAULT nextval('moneda_id_moneda_seq'::regclass),
  "codigo" varchar(3) COLLATE "pg_catalog"."default" NOT NULL,
  "nombre" varchar(50) COLLATE "pg_catalog"."default" NOT NULL,
  "simbolo" varchar(10) COLLATE "pg_catalog"."default",
  "fecha_creacion" timestamp(6) DEFAULT CURRENT_TIMESTAMP,
  "fecha_actualizacion" timestamp(6) DEFAULT CURRENT_TIMESTAMP,
  "tipo_moneda" varchar(10) COLLATE "pg_catalog"."default",
  "pais" varchar(50) COLLATE "pg_catalog"."default"
)
;

-- ----------------------------
-- Records of moneda
-- ----------------------------
INSERT INTO "public"."moneda" VALUES (2, 'USD', 'DÓLAR', '$.', '2025-04-01 20:48:38.775514', '2025-04-01 20:48:38.775514', NULL, NULL);
INSERT INTO "public"."moneda" VALUES (1, 'VES', 'BOLIVAR', 'BS.', '2025-04-01 20:48:18.990738', '2025-04-01 20:48:18.990738', NULL, NULL);

-- ----------------------------
-- Table structure for movimiento_detalle_egreso
-- ----------------------------
DROP TABLE IF EXISTS "public"."movimiento_detalle_egreso";
CREATE TABLE "public"."movimiento_detalle_egreso" (
  "id_detalle_egreso" int4 NOT NULL DEFAULT nextval('movimiento_detalle_egreso_id_detalle_egreso_seq'::regclass),
  "id_movimiento_general" int4,
  "id_plan_cuenta" int4,
  "descripcion" varchar(255) COLLATE "pg_catalog"."default",
  "monto_aplicado" numeric(15,2),
  "id_moneda" int4,
  "tasa" numeric(10,6),
  "monto_base" numeric(15,2),
  "id_cuenta" int4,
  "estado" varchar(50) COLLATE "pg_catalog"."default" DEFAULT 'pendiente'::character varying,
  "fecha_aplicacion" date,
  "fecha_creacion" timestamp(6) DEFAULT CURRENT_TIMESTAMP
)
;

-- ----------------------------
-- Records of movimiento_detalle_egreso
-- ----------------------------

-- ----------------------------
-- Table structure for movimiento_detalle_ingreso
-- ----------------------------
DROP TABLE IF EXISTS "public"."movimiento_detalle_ingreso";
CREATE TABLE "public"."movimiento_detalle_ingreso" (
  "id_detalle_ingreso" int4 NOT NULL DEFAULT nextval('movimiento_detalle_ingreso_id_detalle_ingreso_seq'::regclass),
  "id_movimiento_general" int4,
  "id_plan_cuenta" int4,
  "tipo_origen" varchar(50) COLLATE "pg_catalog"."default",
  "monto" numeric(15,2),
  "referencia" varchar(50) COLLATE "pg_catalog"."default",
  "id_moneda" int4,
  "tasa" numeric(10,6),
  "monto_base" numeric(15,2),
  "id_cuenta" int4,
  "estado" varchar(50) COLLATE "pg_catalog"."default" DEFAULT 'pendiente'::character varying,
  "fecha_pago" date,
  "fecha_creacion" timestamp(6) DEFAULT CURRENT_TIMESTAMP
)
;

-- ----------------------------
-- Records of movimiento_detalle_ingreso
-- ----------------------------
INSERT INTO "public"."movimiento_detalle_ingreso" VALUES (47, 47, 64, NULL, 500.00, '', NULL, 1.000000, 500.00, 5, 'pendiente', NULL, '2025-08-24 19:11:01.857317');

-- ----------------------------
-- Table structure for movimiento_general
-- ----------------------------
DROP TABLE IF EXISTS "public"."movimiento_general";
CREATE TABLE "public"."movimiento_general" (
  "id_movimiento" int4 NOT NULL DEFAULT nextval('movimiento_general_id_movimiento_seq'::regclass),
  "id_condominio" int4 NOT NULL,
  "id_cuenta" int4 NOT NULL,
  "descripcion" varchar(255) COLLATE "pg_catalog"."default" NOT NULL,
  "monto_total" numeric(15,2) NOT NULL,
  "id_moneda" int4 NOT NULL,
  "tipo_movimiento" varchar(50) COLLATE "pg_catalog"."default" NOT NULL,
  "estado" varchar(50) COLLATE "pg_catalog"."default" NOT NULL DEFAULT 'pendiente'::character varying,
  "fecha_movimiento" date NOT NULL,
  "fecha_creacion" timestamp(6) DEFAULT CURRENT_TIMESTAMP,
  "mes_contable" int4,
  "anio_contable" int4
)
;

-- ----------------------------
-- Records of movimiento_general
-- ----------------------------
INSERT INTO "public"."movimiento_general" VALUES (47, 5, 5, 'ALQUILER DE FACHADA FIBEX', 500.00, 2, 'ingreso', 'conciliado', '2025-01-23', '2025-08-24 19:10:08.205292', NULL, NULL);

-- ----------------------------
-- Table structure for notificacion_cobro
-- ----------------------------
DROP TABLE IF EXISTS "public"."notificacion_cobro";
CREATE TABLE "public"."notificacion_cobro" (
  "id_notificacion" int4 NOT NULL DEFAULT nextval('notificacion_cobro_inmueble_id_notificacion_seq'::regclass),
  "id_inmueble" int4,
  "anio" int4,
  "mes" int4,
  "fecha_emision" date NOT NULL DEFAULT CURRENT_DATE,
  "fecha_vencimiento" date,
  "monto_total" numeric(15,2) NOT NULL DEFAULT 0,
  "estado" varchar(30) COLLATE "pg_catalog"."default" NOT NULL DEFAULT 'pendiente'::character varying,
  "id_moneda" int4 NOT NULL,
  "monto_x_pagar" numeric(15,2) DEFAULT 0,
  "monto_pagado" numeric(15,2) DEFAULT 0,
  "pronto_pago" numeric(15,2) DEFAULT 0,
  "fecha_creacion" timestamp(6) DEFAULT CURRENT_TIMESTAMP,
  "fecha_actualizacion" timestamp(6) DEFAULT CURRENT_TIMESTAMP,
  "descripcion" varchar(255) COLLATE "pg_catalog"."default",
  "activa" bool DEFAULT false,
  "id_tipo" int2 DEFAULT 1,
  "id_condominio" int4,
  "id_notificacion_master" int4
)
;
COMMENT ON COLUMN "public"."notificacion_cobro"."activa" IS 'true activa, false inactiva default false';
COMMENT ON COLUMN "public"."notificacion_cobro"."id_tipo" IS '1 presupuesto, 2 relacion';

-- ----------------------------
-- Records of notificacion_cobro
-- ----------------------------
INSERT INTO "public"."notificacion_cobro" VALUES (268, 2, 2025, 1, '2025-01-01', '2025-02-01', 176.80, 'pendiente', 2, 176.80, 0.00, 0.00, '2025-08-24 13:50:27.417025', '2025-08-24 13:50:27.417025', 'CUOTA DE CONDOMINIO DE ENERO', 'f', 1, 5, 25);
INSERT INTO "public"."notificacion_cobro" VALUES (269, 1, 2025, 1, '2025-01-01', '2025-02-01', 149.60, 'pendiente', 2, 149.60, 0.00, 0.00, '2025-08-24 13:50:27.417025', '2025-08-24 13:50:27.417025', 'CUOTA DE CONDOMINIO DE ENERO', 'f', 1, 5, 25);
INSERT INTO "public"."notificacion_cobro" VALUES (270, 6, 2025, 1, '2025-01-01', '2025-02-01', 231.20, 'pendiente', 2, 231.20, 0.00, 0.00, '2025-08-24 13:50:27.417025', '2025-08-24 13:50:27.417025', 'CUOTA DE CONDOMINIO DE ENERO', 'f', 1, 5, 25);
INSERT INTO "public"."notificacion_cobro" VALUES (271, 3, 2025, 1, '2025-01-01', '2025-02-01', 122.40, 'pendiente', 2, 122.40, 0.00, 0.00, '2025-08-24 13:50:27.417025', '2025-08-24 13:50:27.417025', 'CUOTA DE CONDOMINIO DE ENERO', 'f', 1, 5, 25);
INSERT INTO "public"."notificacion_cobro" VALUES (272, 5, 2025, 1, '2025-01-01', '2025-02-01', 95.20, 'pendiente', 2, 95.20, 0.00, 0.00, '2025-08-24 13:50:27.417025', '2025-08-24 13:50:27.417025', 'CUOTA DE CONDOMINIO DE ENERO', 'f', 1, 5, 25);
INSERT INTO "public"."notificacion_cobro" VALUES (274, 4, 2025, 1, '2025-01-01', '2025-02-01', 190.40, 'pendiente', 2, 190.40, 0.00, 0.00, '2025-08-24 13:50:27.417025', '2025-08-24 13:50:27.417025', 'CUOTA DE CONDOMINIO DE ENERO', 'f', 1, 5, 25);
INSERT INTO "public"."notificacion_cobro" VALUES (273, 7, 2025, 1, '2025-01-01', '2025-02-01', 394.40, 'pagada', 2, 394.40, 394.40, 0.00, '2025-08-24 13:50:27.417025', '2025-08-24 13:56:53.894655', 'CUOTA DE CONDOMINIO DE ENERO', 'f', 1, 5, 25);
INSERT INTO "public"."notificacion_cobro" VALUES (275, 4, NULL, NULL, '2025-02-24', '2025-08-24', 20.00, 'pendiente', 2, 20.00, 0.00, 0.00, '2025-08-24 13:58:47.306473', '2025-08-24 13:58:47.306473', 'MULTA DE FEBRERO', 'f', 1, 5, NULL);
INSERT INTO "public"."notificacion_cobro" VALUES (276, 2, NULL, NULL, '2025-01-01', '2025-01-01', 15000.00, 'pendiente', 1, 15000.00, 0.00, 0.00, '2025-08-24 14:41:16.294546', '2025-08-24 14:41:16.294546', 'MULTAS POR SUCIOS', 'f', 1, 5, NULL);
INSERT INTO "public"."notificacion_cobro" VALUES (277, 5, NULL, NULL, '2025-01-01', '2025-01-01', 6500.00, 'pendiente', 1, 6500.00, 0.00, 0.00, '2025-08-24 14:52:58.864086', '2025-08-24 14:52:58.864086', 'MULTA POR INMORALIDADES', 'f', 1, 5, NULL);

-- ----------------------------
-- Table structure for notificacion_cobro_detalle
-- ----------------------------
DROP TABLE IF EXISTS "public"."notificacion_cobro_detalle";
CREATE TABLE "public"."notificacion_cobro_detalle" (
  "id_detalle" int8 NOT NULL DEFAULT nextval('notificacion_cobro_detalle_id_detalle_seq'::regclass),
  "id_notificacion" int4 NOT NULL,
  "id_plan_cuenta" int4 NOT NULL,
  "descripcion" text COLLATE "pg_catalog"."default",
  "monto" numeric(15,2) NOT NULL,
  "id_condominio" int4,
  "id_inmueble" int4,
  "anio" int4,
  "mes" int4,
  "estado" varchar(32) COLLATE "pg_catalog"."default",
  "id_tipo" int4,
  "id_detalle_origen" int8
)
;

-- ----------------------------
-- Records of notificacion_cobro_detalle
-- ----------------------------
INSERT INTO "public"."notificacion_cobro_detalle" VALUES (612, 268, 45, 'JARDINERIA', 32.50, 5, 2, 2025, 1, 'pendiente', 1, 56);
INSERT INTO "public"."notificacion_cobro_detalle" VALUES (613, 268, 45, 'SEGURIDAD', 88.40, 5, 2, 2025, 1, 'pendiente', 1, 57);
INSERT INTO "public"."notificacion_cobro_detalle" VALUES (614, 268, 46, 'MANTENIMIENTO', 45.50, 5, 2, 2025, 1, 'pendiente', 1, 58);
INSERT INTO "public"."notificacion_cobro_detalle" VALUES (615, 268, 46, 'COBRANZA', 5.20, 5, 2, 2025, 1, 'pendiente', 1, 59);
INSERT INTO "public"."notificacion_cobro_detalle" VALUES (616, 268, 45, 'SISTEMA', 5.20, 5, 2, 2025, 1, 'pendiente', 1, 60);
INSERT INTO "public"."notificacion_cobro_detalle" VALUES (617, 269, 45, 'JARDINERIA', 27.50, 5, 1, 2025, 1, 'pendiente', 1, 56);
INSERT INTO "public"."notificacion_cobro_detalle" VALUES (618, 269, 45, 'SEGURIDAD', 74.80, 5, 1, 2025, 1, 'pendiente', 1, 57);
INSERT INTO "public"."notificacion_cobro_detalle" VALUES (619, 269, 46, 'MANTENIMIENTO', 38.50, 5, 1, 2025, 1, 'pendiente', 1, 58);
INSERT INTO "public"."notificacion_cobro_detalle" VALUES (620, 269, 46, 'COBRANZA', 4.40, 5, 1, 2025, 1, 'pendiente', 1, 59);
INSERT INTO "public"."notificacion_cobro_detalle" VALUES (621, 269, 45, 'SISTEMA', 4.40, 5, 1, 2025, 1, 'pendiente', 1, 60);
INSERT INTO "public"."notificacion_cobro_detalle" VALUES (622, 270, 45, 'JARDINERIA', 42.50, 5, 6, 2025, 1, 'pendiente', 1, 56);
INSERT INTO "public"."notificacion_cobro_detalle" VALUES (623, 270, 45, 'SEGURIDAD', 115.60, 5, 6, 2025, 1, 'pendiente', 1, 57);
INSERT INTO "public"."notificacion_cobro_detalle" VALUES (624, 270, 46, 'MANTENIMIENTO', 59.50, 5, 6, 2025, 1, 'pendiente', 1, 58);
INSERT INTO "public"."notificacion_cobro_detalle" VALUES (625, 270, 46, 'COBRANZA', 6.80, 5, 6, 2025, 1, 'pendiente', 1, 59);
INSERT INTO "public"."notificacion_cobro_detalle" VALUES (626, 270, 45, 'SISTEMA', 6.80, 5, 6, 2025, 1, 'pendiente', 1, 60);
INSERT INTO "public"."notificacion_cobro_detalle" VALUES (627, 271, 45, 'JARDINERIA', 22.50, 5, 3, 2025, 1, 'pendiente', 1, 56);
INSERT INTO "public"."notificacion_cobro_detalle" VALUES (628, 271, 45, 'SEGURIDAD', 61.20, 5, 3, 2025, 1, 'pendiente', 1, 57);
INSERT INTO "public"."notificacion_cobro_detalle" VALUES (629, 271, 46, 'MANTENIMIENTO', 31.50, 5, 3, 2025, 1, 'pendiente', 1, 58);
INSERT INTO "public"."notificacion_cobro_detalle" VALUES (630, 271, 46, 'COBRANZA', 3.60, 5, 3, 2025, 1, 'pendiente', 1, 59);
INSERT INTO "public"."notificacion_cobro_detalle" VALUES (631, 271, 45, 'SISTEMA', 3.60, 5, 3, 2025, 1, 'pendiente', 1, 60);
INSERT INTO "public"."notificacion_cobro_detalle" VALUES (632, 272, 45, 'JARDINERIA', 17.50, 5, 5, 2025, 1, 'pendiente', 1, 56);
INSERT INTO "public"."notificacion_cobro_detalle" VALUES (633, 272, 45, 'SEGURIDAD', 47.60, 5, 5, 2025, 1, 'pendiente', 1, 57);
INSERT INTO "public"."notificacion_cobro_detalle" VALUES (634, 272, 46, 'MANTENIMIENTO', 24.50, 5, 5, 2025, 1, 'pendiente', 1, 58);
INSERT INTO "public"."notificacion_cobro_detalle" VALUES (635, 272, 46, 'COBRANZA', 2.80, 5, 5, 2025, 1, 'pendiente', 1, 59);
INSERT INTO "public"."notificacion_cobro_detalle" VALUES (636, 272, 45, 'SISTEMA', 2.80, 5, 5, 2025, 1, 'pendiente', 1, 60);
INSERT INTO "public"."notificacion_cobro_detalle" VALUES (637, 273, 45, 'JARDINERIA', 72.50, 5, 7, 2025, 1, 'pendiente', 1, 56);
INSERT INTO "public"."notificacion_cobro_detalle" VALUES (638, 273, 45, 'SEGURIDAD', 197.20, 5, 7, 2025, 1, 'pendiente', 1, 57);
INSERT INTO "public"."notificacion_cobro_detalle" VALUES (639, 273, 46, 'MANTENIMIENTO', 101.50, 5, 7, 2025, 1, 'pendiente', 1, 58);
INSERT INTO "public"."notificacion_cobro_detalle" VALUES (640, 273, 46, 'COBRANZA', 11.60, 5, 7, 2025, 1, 'pendiente', 1, 59);
INSERT INTO "public"."notificacion_cobro_detalle" VALUES (641, 273, 45, 'SISTEMA', 11.60, 5, 7, 2025, 1, 'pendiente', 1, 60);
INSERT INTO "public"."notificacion_cobro_detalle" VALUES (642, 274, 45, 'JARDINERIA', 35.00, 5, 4, 2025, 1, 'pendiente', 1, 56);
INSERT INTO "public"."notificacion_cobro_detalle" VALUES (643, 274, 45, 'SEGURIDAD', 95.20, 5, 4, 2025, 1, 'pendiente', 1, 57);
INSERT INTO "public"."notificacion_cobro_detalle" VALUES (644, 274, 46, 'MANTENIMIENTO', 49.00, 5, 4, 2025, 1, 'pendiente', 1, 58);
INSERT INTO "public"."notificacion_cobro_detalle" VALUES (645, 274, 46, 'COBRANZA', 5.60, 5, 4, 2025, 1, 'pendiente', 1, 59);
INSERT INTO "public"."notificacion_cobro_detalle" VALUES (646, 274, 45, 'SISTEMA', 5.60, 5, 4, 2025, 1, 'pendiente', 1, 60);
INSERT INTO "public"."notificacion_cobro_detalle" VALUES (647, 275, 48, 'MULTA POR REALIZAR ACTOS CONTRARIOS A LA MORAL', 20.00, 5, 4, NULL, NULL, 'pendiente', NULL, NULL);
INSERT INTO "public"."notificacion_cobro_detalle" VALUES (648, 276, 46, 'MULTA POR ACTOS LASIVOS', 15000.00, 5, 2, NULL, NULL, 'pendiente', NULL, NULL);
INSERT INTO "public"."notificacion_cobro_detalle" VALUES (649, 277, 48, 'MULTA POR EXHIBISIONISMO EN EL GRUPO DE WS', 6500.00, 5, 5, NULL, NULL, 'pendiente', NULL, NULL);

-- ----------------------------
-- Table structure for notificacion_cobro_detalle_master
-- ----------------------------
DROP TABLE IF EXISTS "public"."notificacion_cobro_detalle_master";
CREATE TABLE "public"."notificacion_cobro_detalle_master" (
  "id_detalle" int8 NOT NULL DEFAULT nextval('notificacion_cobro_detalle_master_id_detalle_seq'::regclass),
  "id_notificacion_master" int4 NOT NULL,
  "id_plan_cuenta" int4 NOT NULL,
  "descripcion" text COLLATE "pg_catalog"."default",
  "monto" numeric(15,2) NOT NULL,
  "id_condominio" int4,
  "anio" int4,
  "mes" int4,
  "estado" varchar(32) COLLATE "pg_catalog"."default",
  "id_moneda" int4 NOT NULL,
  "tipo_movimiento" varchar(20) COLLATE "pg_catalog"."default" NOT NULL DEFAULT 'ingreso'::character varying
)
;
COMMENT ON COLUMN "public"."notificacion_cobro_detalle_master"."estado" IS 'Estado del concepto (pendiente, aplicado, etc. — libre según negocio)';
COMMENT ON TABLE "public"."notificacion_cobro_detalle_master" IS 'Detalle de conceptos globales de la notificación maestra (sin prorratear)';

-- ----------------------------
-- Records of notificacion_cobro_detalle_master
-- ----------------------------
INSERT INTO "public"."notificacion_cobro_detalle_master" VALUES (56, 25, 45, 'JARDINERIA', 250.00, 5, 2025, 1, 'pendiente', 2, 'ingreso');
INSERT INTO "public"."notificacion_cobro_detalle_master" VALUES (57, 25, 45, 'SEGURIDAD', 680.00, 5, 2025, 1, 'pendiente', 2, 'ingreso');
INSERT INTO "public"."notificacion_cobro_detalle_master" VALUES (58, 25, 46, 'MANTENIMIENTO', 350.00, 5, 2025, 1, 'pendiente', 2, 'ingreso');
INSERT INTO "public"."notificacion_cobro_detalle_master" VALUES (59, 25, 46, 'COBRANZA', 40.00, 5, 2025, 1, 'pendiente', 2, 'ingreso');
INSERT INTO "public"."notificacion_cobro_detalle_master" VALUES (60, 25, 45, 'SISTEMA', 40.00, 5, 2025, 1, 'pendiente', 2, 'ingreso');
INSERT INTO "public"."notificacion_cobro_detalle_master" VALUES (61, 26, 64, 'JARDINERIA', 15.00, 5, 2025, 1, 'pendiente', 2, 'egreso');
INSERT INTO "public"."notificacion_cobro_detalle_master" VALUES (65, 27, 52, 'AGUA', 100.00, 5, 2025, 2, 'pendiente', 2, 'egreso');
INSERT INTO "public"."notificacion_cobro_detalle_master" VALUES (66, 27, 60, 'MANT DE PORTON', 100.00, 5, 2025, 2, 'pendiente', 2, 'egreso');
INSERT INTO "public"."notificacion_cobro_detalle_master" VALUES (67, 27, 65, 'IDB', 100.00, 5, 2025, 2, 'pendiente', 2, 'egreso');
INSERT INTO "public"."notificacion_cobro_detalle_master" VALUES (68, 27, 53, 'LLENADO DE BOMBONAS', 533.00, 5, 2025, 2, 'pendiente', 2, 'egreso');
INSERT INTO "public"."notificacion_cobro_detalle_master" VALUES (69, 28, 45, 'JARDINERIA', 500.00, 5, 2025, 2, 'pendiente', 2, 'ingreso');
INSERT INTO "public"."notificacion_cobro_detalle_master" VALUES (70, 28, 45, 'SISTEMA', 500.00, 5, 2025, 2, 'pendiente', 2, 'ingreso');
INSERT INTO "public"."notificacion_cobro_detalle_master" VALUES (71, 28, 46, 'COBRANZA', 500.00, 5, 2025, 2, 'pendiente', 2, 'ingreso');
INSERT INTO "public"."notificacion_cobro_detalle_master" VALUES (72, 28, 46, 'MANTENIMIENTO', 500.00, 5, 2025, 2, 'pendiente', 2, 'ingreso');

-- ----------------------------
-- Table structure for notificacion_cobro_master
-- ----------------------------
DROP TABLE IF EXISTS "public"."notificacion_cobro_master";
CREATE TABLE "public"."notificacion_cobro_master" (
  "id_notificacion_master" int4 NOT NULL DEFAULT nextval('notificacion_cobro_master_id_seq'::regclass),
  "anio" int4,
  "mes" int4,
  "fecha_emision" date NOT NULL DEFAULT CURRENT_DATE,
  "fecha_vencimiento" date,
  "monto_total" numeric(15,2) NOT NULL DEFAULT 0,
  "estado" varchar(30) COLLATE "pg_catalog"."default" NOT NULL DEFAULT 'pendiente'::character varying,
  "id_moneda" int4 NOT NULL,
  "fecha_creacion" timestamp(6) DEFAULT CURRENT_TIMESTAMP,
  "fecha_actualizacion" timestamp(6) DEFAULT CURRENT_TIMESTAMP,
  "descripcion" varchar(255) COLLATE "pg_catalog"."default",
  "activa" bool DEFAULT false,
  "id_tipo" int2 DEFAULT 1,
  "id_condominio" int4
)
;
COMMENT ON COLUMN "public"."notificacion_cobro_master"."estado" IS 'emitida, pendiente , cerrada';
COMMENT ON COLUMN "public"."notificacion_cobro_master"."activa" IS 'TRUE: notificación maestra vigente para el periodo; solo debe haber una por condominio + periodo';
COMMENT ON COLUMN "public"."notificacion_cobro_master"."id_tipo" IS '1 = Presupuesto de gastos comunes, 2 = Relación de ingresos/egresos';
COMMENT ON TABLE "public"."notificacion_cobro_master" IS 'Cabecera global de notificaciones de cobro (monto total a prorratear)';

-- ----------------------------
-- Records of notificacion_cobro_master
-- ----------------------------
INSERT INTO "public"."notificacion_cobro_master" VALUES (25, 2025, 1, '2025-01-01', '2025-02-01', 1360.00, 'emitida', 2, '2025-08-24 13:50:09.303534', '2025-08-24 13:50:27.417025', 'CUOTA DE CONDOMINIO DE ENERO', 'f', 1, 5);
INSERT INTO "public"."notificacion_cobro_master" VALUES (27, 2025, 2, '2025-08-27', '2025-09-26', -833.00, 'emitida', 2, '2025-08-27 17:59:12.338118', '2025-08-27 18:06:09.778', 'RELACIÓN DE GASTOS DE FEBRERO', 'f', 2, 5);
INSERT INTO "public"."notificacion_cobro_master" VALUES (28, 2025, 2, '2025-08-27', '2025-09-26', 2000.00, 'pendiente', 2, '2025-08-27 18:35:41.409137', '2025-08-27 18:35:41.409137', 'CUOTA DE CONDOMINIO DE FEBRERO', 'f', 1, 5);

-- ----------------------------
-- Table structure for notificaciones_cobro
-- ----------------------------
DROP TABLE IF EXISTS "public"."notificaciones_cobro";
CREATE TABLE "public"."notificaciones_cobro" (
  "id_notificacion" int4 NOT NULL DEFAULT nextval('notificaciones_cobro_id_notificacion_seq'::regclass),
  "id_notificacion_master" int4 NOT NULL,
  "id_inmueble" int4 NOT NULL,
  "monto" numeric(10,2) NOT NULL,
  "estado" varchar(20) COLLATE "pg_catalog"."default" NOT NULL DEFAULT 'pendiente'::character varying,
  "fecha_emision" date NOT NULL,
  "fecha_vencimiento" date NOT NULL
)
;

-- ----------------------------
-- Records of notificaciones_cobro
-- ----------------------------

-- ----------------------------
-- Table structure for pago
-- ----------------------------
DROP TABLE IF EXISTS "public"."pago";
CREATE TABLE "public"."pago" (
  "id_pago" int4 NOT NULL DEFAULT nextval('pago_id_pago_seq'::regclass),
  "id_propietario" int4 NOT NULL,
  "total_pagado" numeric(15,2) NOT NULL,
  "id_moneda" int4 NOT NULL,
  "id_tipo_cambio" int4,
  "tasa_cambio" numeric(15,5),
  "metodo_pago" varchar(50) COLLATE "pg_catalog"."default" NOT NULL,
  "estado" varchar(50) COLLATE "pg_catalog"."default" DEFAULT 'en_revision'::character varying,
  "fecha_pago" date NOT NULL,
  "fecha_verificacion" date,
  "id_usuario_verificador" int4,
  "fecha_creacion" timestamp(6) DEFAULT CURRENT_TIMESTAMP,
  "fecha_actualizacion" timestamp(6) DEFAULT CURRENT_TIMESTAMP
)
;

-- ----------------------------
-- Records of pago
-- ----------------------------

-- ----------------------------
-- Table structure for plan_cuenta
-- ----------------------------
DROP TABLE IF EXISTS "public"."plan_cuenta";
CREATE TABLE "public"."plan_cuenta" (
  "id_plan" int4 NOT NULL DEFAULT nextval('plan_cuenta_id_plan_seq'::regclass),
  "id_condominio" int4 NOT NULL,
  "codigo" varchar(20) COLLATE "pg_catalog"."default" NOT NULL,
  "nombre" varchar(255) COLLATE "pg_catalog"."default" NOT NULL,
  "tipo" varchar(20) COLLATE "pg_catalog"."default" NOT NULL,
  "nivel" int4 NOT NULL,
  "codigo_padre" varchar(20) COLLATE "pg_catalog"."default",
  "estado" bool DEFAULT true
)
;

-- ----------------------------
-- Records of plan_cuenta
-- ----------------------------
INSERT INTO "public"."plan_cuenta" VALUES (45, 5, '4', 'Ingresos', 'ingreso', 1, NULL, 't');
INSERT INTO "public"."plan_cuenta" VALUES (46, 5, '4.1', 'Cuotas de Condominio', 'ingreso', 2, '4', 't');
INSERT INTO "public"."plan_cuenta" VALUES (47, 5, '4.2', 'Intereses por Mora', 'ingreso', 2, '4', 't');
INSERT INTO "public"."plan_cuenta" VALUES (48, 5, '4.3', 'Otros Ingresos', 'ingreso', 2, '4', 't');
INSERT INTO "public"."plan_cuenta" VALUES (49, 5, '5', 'Egresos', 'egreso', 1, NULL, 't');
INSERT INTO "public"."plan_cuenta" VALUES (50, 5, '5.1', 'Servicios Básicos', 'egreso', 2, '5', 't');
INSERT INTO "public"."plan_cuenta" VALUES (51, 5, '5.1.1', 'Electricidad', 'egreso', 3, '5.1', 't');
INSERT INTO "public"."plan_cuenta" VALUES (52, 5, '5.1.2', 'Agua', 'egreso', 3, '5.1', 't');
INSERT INTO "public"."plan_cuenta" VALUES (53, 5, '5.1.3', 'Gas', 'egreso', 3, '5.1', 't');
INSERT INTO "public"."plan_cuenta" VALUES (54, 5, '5.2', 'Gastos de Personal', 'egreso', 2, '5', 't');
INSERT INTO "public"."plan_cuenta" VALUES (55, 5, '5.2.1', 'Vigilancia', 'egreso', 3, '5.2', 't');
INSERT INTO "public"."plan_cuenta" VALUES (56, 5, '5.2.2', 'Conserjería', 'egreso', 3, '5.2', 't');
INSERT INTO "public"."plan_cuenta" VALUES (57, 5, '5.2.3', 'Otros Honorarios', 'egreso', 3, '5.2', 't');
INSERT INTO "public"."plan_cuenta" VALUES (58, 5, '5.3', 'Mantenimiento General', 'egreso', 2, '5', 't');
INSERT INTO "public"."plan_cuenta" VALUES (59, 5, '5.3.1', 'Mantenimiento de Ascensores', 'egreso', 3, '5.3', 't');
INSERT INTO "public"."plan_cuenta" VALUES (60, 5, '5.3.2', 'Portones y Cercado', 'egreso', 3, '5.3', 't');
INSERT INTO "public"."plan_cuenta" VALUES (61, 5, '5.3.3', 'Áreas Comunes', 'egreso', 3, '5.3', 't');
INSERT INTO "public"."plan_cuenta" VALUES (62, 5, '5.4', 'Administración y Otros', 'egreso', 2, '5', 't');
INSERT INTO "public"."plan_cuenta" VALUES (63, 5, '5.4.1', 'Papelería y Suministros', 'egreso', 3, '5.4', 't');
INSERT INTO "public"."plan_cuenta" VALUES (64, 5, '5.4.2', 'Honorarios Administrador', 'egreso', 3, '5.4', 't');
INSERT INTO "public"."plan_cuenta" VALUES (65, 5, '5.4.3', 'Gastos Bancarios', 'egreso', 3, '5.4', 't');
INSERT INTO "public"."plan_cuenta" VALUES (66, 5, '5.4.4', 'Software de Gestión', 'egreso', 3, '5.4', 't');
INSERT INTO "public"."plan_cuenta" VALUES (67, 5, '4.4', 'Cuentas por Cobrar - Deuda Migrada', 'ingreso', 2, '4', 't');

-- ----------------------------
-- Table structure for plan_cuenta_base
-- ----------------------------
DROP TABLE IF EXISTS "public"."plan_cuenta_base";
CREATE TABLE "public"."plan_cuenta_base" (
  "id_plan_base" int4 NOT NULL DEFAULT nextval('plan_cuenta_base_id_plan_base_seq'::regclass),
  "codigo" varchar(20) COLLATE "pg_catalog"."default" NOT NULL,
  "nombre" varchar(255) COLLATE "pg_catalog"."default" NOT NULL,
  "tipo" varchar(20) COLLATE "pg_catalog"."default" NOT NULL,
  "nivel" int4 NOT NULL,
  "codigo_padre" varchar(20) COLLATE "pg_catalog"."default",
  "estado" bool DEFAULT true
)
;

-- ----------------------------
-- Records of plan_cuenta_base
-- ----------------------------
INSERT INTO "public"."plan_cuenta_base" VALUES (1, '4', 'Ingresos', 'ingreso', 1, NULL, 't');
INSERT INTO "public"."plan_cuenta_base" VALUES (2, '4.1', 'Cuotas de Condominio', 'ingreso', 2, '4', 't');
INSERT INTO "public"."plan_cuenta_base" VALUES (3, '4.2', 'Intereses por Mora', 'ingreso', 2, '4', 't');
INSERT INTO "public"."plan_cuenta_base" VALUES (4, '4.3', 'Otros Ingresos', 'ingreso', 2, '4', 't');
INSERT INTO "public"."plan_cuenta_base" VALUES (5, '5', 'Egresos', 'egreso', 1, NULL, 't');
INSERT INTO "public"."plan_cuenta_base" VALUES (6, '5.1', 'Servicios Básicos', 'egreso', 2, '5', 't');
INSERT INTO "public"."plan_cuenta_base" VALUES (7, '5.1.1', 'Electricidad', 'egreso', 3, '5.1', 't');
INSERT INTO "public"."plan_cuenta_base" VALUES (8, '5.1.2', 'Agua', 'egreso', 3, '5.1', 't');
INSERT INTO "public"."plan_cuenta_base" VALUES (9, '5.1.3', 'Gas', 'egreso', 3, '5.1', 't');
INSERT INTO "public"."plan_cuenta_base" VALUES (10, '5.2', 'Gastos de Personal', 'egreso', 2, '5', 't');
INSERT INTO "public"."plan_cuenta_base" VALUES (11, '5.2.1', 'Vigilancia', 'egreso', 3, '5.2', 't');
INSERT INTO "public"."plan_cuenta_base" VALUES (12, '5.2.2', 'Conserjería', 'egreso', 3, '5.2', 't');
INSERT INTO "public"."plan_cuenta_base" VALUES (13, '5.2.3', 'Otros Honorarios', 'egreso', 3, '5.2', 't');
INSERT INTO "public"."plan_cuenta_base" VALUES (14, '5.3', 'Mantenimiento General', 'egreso', 2, '5', 't');
INSERT INTO "public"."plan_cuenta_base" VALUES (15, '5.3.1', 'Mantenimiento de Ascensores', 'egreso', 3, '5.3', 't');
INSERT INTO "public"."plan_cuenta_base" VALUES (16, '5.3.2', 'Portones y Cercado', 'egreso', 3, '5.3', 't');
INSERT INTO "public"."plan_cuenta_base" VALUES (17, '5.3.3', 'Áreas Comunes', 'egreso', 3, '5.3', 't');
INSERT INTO "public"."plan_cuenta_base" VALUES (18, '5.4', 'Administración y Otros', 'egreso', 2, '5', 't');
INSERT INTO "public"."plan_cuenta_base" VALUES (19, '5.4.1', 'Papelería y Suministros', 'egreso', 3, '5.4', 't');
INSERT INTO "public"."plan_cuenta_base" VALUES (20, '5.4.2', 'Honorarios Administrador', 'egreso', 3, '5.4', 't');
INSERT INTO "public"."plan_cuenta_base" VALUES (21, '5.4.3', 'Gastos Bancarios', 'egreso', 3, '5.4', 't');
INSERT INTO "public"."plan_cuenta_base" VALUES (22, '5.4.4', 'Software de Gestión', 'egreso', 3, '5.4', 't');
INSERT INTO "public"."plan_cuenta_base" VALUES (23, '4.4', 'Cuentas por Cobrar - Deuda Migrada', 'egreso', 2, '4', 't');

-- ----------------------------
-- Table structure for propietario
-- ----------------------------
DROP TABLE IF EXISTS "public"."propietario";
CREATE TABLE "public"."propietario" (
  "id_propietario" int8 NOT NULL DEFAULT nextval('propietario_id_propietario_seq1'::regclass),
  "nombre1" varchar(60) COLLATE "pg_catalog"."default",
  "nombre2" varchar(255) COLLATE "pg_catalog"."default",
  "apellido1" varchar(255) COLLATE "pg_catalog"."default",
  "apellido2" varchar(255) COLLATE "pg_catalog"."default",
  "t_cedula" varchar(1) COLLATE "pg_catalog"."default",
  "cedula" int8,
  "t_rif" varchar(255) COLLATE "pg_catalog"."default",
  "rif" varchar(255) COLLATE "pg_catalog"."default",
  "celular" varchar(255) COLLATE "pg_catalog"."default",
  "fecha_registro" timestamp(6),
  "tratamiento" varchar(255) COLLATE "pg_catalog"."default",
  "verificado" bool DEFAULT false
)
;

-- ----------------------------
-- Records of propietario
-- ----------------------------
INSERT INTO "public"."propietario" VALUES (1, 'roger', 'e', 'palencia', 'l', 'V', 13271437, 'V', '132714378', '04243572619', NULL, NULL, 'f');
INSERT INTO "public"."propietario" VALUES (2, 'kevin', 'e', 'palencia', 'l', 'V', 132714333, 'V', '1327143322', '042435744444', NULL, NULL, 'f');

-- ----------------------------
-- Table structure for propietario_inmueble
-- ----------------------------
DROP TABLE IF EXISTS "public"."propietario_inmueble";
CREATE TABLE "public"."propietario_inmueble" (
  "id_propietario" int4 NOT NULL,
  "id_inmueble" int4 NOT NULL,
  "id_usuario" int4 NOT NULL,
  "porcentaje_propiedad" numeric(5,2) NOT NULL DEFAULT 100,
  "fecha_creacion" timestamp(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "fecha_actualizacion" timestamp(6) NOT NULL DEFAULT CURRENT_TIMESTAMP
)
;

-- ----------------------------
-- Records of propietario_inmueble
-- ----------------------------
INSERT INTO "public"."propietario_inmueble" VALUES (1, 3, 1, 100.00, '2025-08-24 13:18:01.039255', '2025-08-24 13:18:01.039255');
INSERT INTO "public"."propietario_inmueble" VALUES (2, 4, 2, 100.00, '2025-08-24 13:18:01.194382', '2025-08-24 13:18:01.194382');
INSERT INTO "public"."propietario_inmueble" VALUES (2, 5, 2, 100.00, '2025-08-24 13:18:01.345639', '2025-08-24 13:18:01.345639');
INSERT INTO "public"."propietario_inmueble" VALUES (2, 6, 2, 100.00, '2025-08-24 13:18:01.782544', '2025-08-24 13:18:01.782544');
INSERT INTO "public"."propietario_inmueble" VALUES (2, 7, 2, 100.00, '2025-08-24 13:18:01.939511', '2025-08-24 13:18:01.939511');
INSERT INTO "public"."propietario_inmueble" VALUES (1, 1, 1, 100.00, '2025-08-24 13:17:40.828475', '2025-08-24 13:18:02.094904');
INSERT INTO "public"."propietario_inmueble" VALUES (1, 2, 1, 100.00, '2025-08-24 13:17:40.986781', '2025-08-24 13:18:02.259648');

-- ----------------------------
-- Table structure for recibo_cabecera
-- ----------------------------
DROP TABLE IF EXISTS "public"."recibo_cabecera";
CREATE TABLE "public"."recibo_cabecera" (
  "id_recibo" int4 NOT NULL DEFAULT nextval('recibo_cabecera_id_recibo_seq'::regclass),
  "id_propietario" int4 NOT NULL,
  "numero_recibo" varchar(50) COLLATE "pg_catalog"."default" NOT NULL DEFAULT nextval('recibo_cabecera_numero_recibo_seq'::regclass),
  "fecha_emision" date NOT NULL,
  "monto_total" numeric(15,2) NOT NULL,
  "monto_descuento_pronto_pago" numeric(15,2) NOT NULL DEFAULT 0,
  "total_pagado" numeric(15,2) NOT NULL,
  "estado" varchar(50) COLLATE "pg_catalog"."default" DEFAULT 'en_revision'::character varying,
  "fecha_creacion" timestamp(6) DEFAULT CURRENT_TIMESTAMP,
  "fecha_actualizacion" timestamp(6) DEFAULT CURRENT_TIMESTAMP,
  "correlativo_condominio" int4 NOT NULL DEFAULT 0,
  "observaciones" varchar(255) COLLATE "pg_catalog"."default",
  "id_usuario" int4,
  "id_condominio" int4,
  "id_inmueble" int4,
  "fecha_anulacion" timestamp(6),
  "fecha_pago" date,
  "metodo_pago" varchar(50) COLLATE "pg_catalog"."default",
  "id_moneda" int4
)
;

-- ----------------------------
-- Records of recibo_cabecera
-- ----------------------------
INSERT INTO "public"."recibo_cabecera" VALUES (245, 2, 'REC-0005-0001', '2025-08-24', 0.00, 0.00, 9.45, 'aprobado', '2025-08-24 13:55:12.616307', '2025-08-24 13:55:38.657842', 1, 'PARCIALMENTE  #273', 2, 5, 7, NULL, NULL, NULL, NULL);
INSERT INTO "public"."recibo_cabecera" VALUES (246, 2, 'REC-0005-0002', '2025-08-24', 0.00, 0.00, 450.00, 'aprobado', '2025-08-24 13:56:29.511005', '2025-08-24 13:56:53.894655', 2, 'PAGA TOTALMENTE NOTIF: #273 DEJANDO EN CREDITO EN USD 55,60', 2, 5, 7, NULL, NULL, NULL, NULL);

-- ----------------------------
-- Table structure for recibo_correlativo
-- ----------------------------
DROP TABLE IF EXISTS "public"."recibo_correlativo";
CREATE TABLE "public"."recibo_correlativo" (
  "id_condominio" int4 NOT NULL,
  "ultimo_correlativo" int4 NOT NULL DEFAULT 0
)
;

-- ----------------------------
-- Records of recibo_correlativo
-- ----------------------------
INSERT INTO "public"."recibo_correlativo" VALUES (5, 19);

-- ----------------------------
-- Table structure for recibo_destino_fondos
-- ----------------------------
DROP TABLE IF EXISTS "public"."recibo_destino_fondos";
CREATE TABLE "public"."recibo_destino_fondos" (
  "id_destino_fondos" int4 NOT NULL DEFAULT nextval('recibo_destino_fondos_id_destino_fondos_seq'::regclass),
  "id_recibo" int4 NOT NULL,
  "id_notificacion" int4 NOT NULL,
  "monto_aplicado" numeric(15,2) NOT NULL,
  "fecha_creacion" timestamp(6) DEFAULT CURRENT_TIMESTAMP,
  "id_moneda" int4,
  "id_moneda_base" int4,
  "tasa" numeric(20,6) DEFAULT 0,
  "monto_base" numeric(20,2),
  "id_cuenta" int4
)
;

-- ----------------------------
-- Records of recibo_destino_fondos
-- ----------------------------
INSERT INTO "public"."recibo_destino_fondos" VALUES (407, 245, 273, 9.45, '2025-08-24 13:55:38.657842', 2, 2, 1.000000, 9.45, NULL);
INSERT INTO "public"."recibo_destino_fondos" VALUES (409, 246, 273, 384.95, '2025-08-24 13:56:53.894655', 2, 2, 1.000000, 384.95, NULL);

-- ----------------------------
-- Table structure for recibo_origen_fondos
-- ----------------------------
DROP TABLE IF EXISTS "public"."recibo_origen_fondos";
CREATE TABLE "public"."recibo_origen_fondos" (
  "id_origen_fondos" int4 NOT NULL DEFAULT nextval('recibo_origen_fondos_id_origen_fondos_seq'::regclass),
  "id_recibo" int4 NOT NULL,
  "tipo_origen" varchar(50) COLLATE "pg_catalog"."default" NOT NULL,
  "monto" numeric(15,2) NOT NULL,
  "referencia" varchar(255) COLLATE "pg_catalog"."default",
  "fecha_creacion" timestamp(6) DEFAULT CURRENT_TIMESTAMP,
  "tasa" numeric(15,6),
  "monto_base" numeric(15,2),
  "id_moneda" int4,
  "id_cuenta" int4,
  "estado" varchar(20) COLLATE "pg_catalog"."default" DEFAULT 'en revisión'::character varying,
  "fecha_actualizacion" timestamp(6),
  "id_moneda_base" int2
)
;

-- ----------------------------
-- Records of recibo_origen_fondos
-- ----------------------------
INSERT INTO "public"."recibo_origen_fondos" VALUES (405, 245, 'efectivo', 1500.00, '', '2025-08-24 13:55:12.616307', 0.006300, 9.45, 1, 2, 'aprobado', '2025-08-24 13:55:38.657842', 2);
INSERT INTO "public"."recibo_origen_fondos" VALUES (406, 246, 'efectivo', 450.00, '', '2025-08-24 13:56:29.511005', 1.000000, 450.00, 2, 5, 'aprobado', '2025-08-24 13:56:53.894655', 2);

-- ----------------------------
-- Table structure for rol
-- ----------------------------
DROP TABLE IF EXISTS "public"."rol";
CREATE TABLE "public"."rol" (
  "id_rol" int4 NOT NULL DEFAULT nextval('rol_id_rol_seq'::regclass),
  "nombre" varchar(100) COLLATE "pg_catalog"."default" NOT NULL,
  "descripcion" text COLLATE "pg_catalog"."default",
  "fecha_creacion" timestamp(6) DEFAULT CURRENT_TIMESTAMP,
  "fecha_actualizacion" timestamp(6) DEFAULT CURRENT_TIMESTAMP
)
;

-- ----------------------------
-- Records of rol
-- ----------------------------
INSERT INTO "public"."rol" VALUES (1, 'Root', 'Super Usuario', '2025-03-30 17:46:49.775298', '2025-03-30 17:46:49.775298');
INSERT INTO "public"."rol" VALUES (2, 'Administrador', 'Usuario Administrador de Condominios', '2025-03-30 17:47:13.327329', '2025-03-30 17:47:13.327329');
INSERT INTO "public"."rol" VALUES (3, 'Cobrador', 'Usuario Cobrador de Condominios', '2025-03-30 17:47:43.465985', '2025-03-30 17:47:43.465985');
INSERT INTO "public"."rol" VALUES (4, 'Contable', 'Usuario Contable de Condominios', '2025-03-30 17:47:59.737039', '2025-03-30 17:47:59.737039');
INSERT INTO "public"."rol" VALUES (5, 'Propietario', 'Usuario Propietario de Inmuebles', '2025-03-30 17:48:16.286148', '2025-03-30 17:48:16.286148');
INSERT INTO "public"."rol" VALUES (6, 'Inquilino', 'Usuario Autorizado o inquilino de inmuebles', '2025-03-30 17:48:43.513546', '2025-03-30 17:48:43.513546');

-- ----------------------------
-- Table structure for tipo_cambio
-- ----------------------------
DROP TABLE IF EXISTS "public"."tipo_cambio";
CREATE TABLE "public"."tipo_cambio" (
  "id_tipo_cambio" int4 NOT NULL DEFAULT nextval('tipo_cambio_id_tipo_cambio_seq'::regclass),
  "id_moneda_origen" int4 NOT NULL,
  "id_moneda_destino" int4 NOT NULL,
  "tasa" numeric(15,5) NOT NULL,
  "fecha_vigencia" timestamp(0) NOT NULL,
  "fecha_creacion" timestamp(6) DEFAULT CURRENT_TIMESTAMP,
  "fecha_actualizacion" timestamp(6) DEFAULT CURRENT_TIMESTAMP
)
;

-- ----------------------------
-- Records of tipo_cambio
-- ----------------------------
INSERT INTO "public"."tipo_cambio" VALUES (2, 2, 1, 160.00000, '2025-04-18 00:00:00', '2025-04-18 16:35:51.277626', '2025-04-18 16:35:51.277626');
INSERT INTO "public"."tipo_cambio" VALUES (1, 1, 2, 0.00625, '2025-04-18 00:00:00', '2025-04-18 17:23:38.986875', '2025-04-18 17:23:38.986875');
INSERT INTO "public"."tipo_cambio" VALUES (1, 1, 2, 0.00625, '2025-04-18 00:00:00', '2025-04-18 16:35:28.722972', '2025-04-18 16:35:28.722972');

-- ----------------------------
-- Function structure for actualizar_campos_recibo_cabecera
-- ----------------------------
DROP FUNCTION IF EXISTS "public"."actualizar_campos_recibo_cabecera"();
CREATE OR REPLACE FUNCTION "public"."actualizar_campos_recibo_cabecera"()
  RETURNS "pg_catalog"."trigger" AS $BODY$
BEGIN
    -- Usar fecha_creacion como fecha_pago (fecha del recibo)
    NEW.fecha_pago = NEW.fecha_creacion::DATE;

    -- Obtener id_moneda y metodo_pago desde recibo_origen_fondos y cuenta
    SELECT c.id_moneda,
           CASE c.tipo
               WHEN 'efectivo' THEN 'efectivo'
               WHEN 'banco' THEN 'transferencia'
               ELSE 'desconocido'
           END
    INTO NEW.id_moneda, NEW.metodo_pago
    FROM recibo_origen_fondos rof
    JOIN cuenta c ON c.id_cuenta = rof.id_cuenta
    WHERE rof.id_recibo = NEW.id_recibo  -- Cambiado de NEW.numero_recibo a NEW.id_recibo
    LIMIT 1;

    -- Si no se encuentra origen, usar valores por defecto
    IF NEW.id_moneda IS NULL THEN
        NEW.id_moneda = (SELECT id_moneda FROM condominio WHERE id_condominio = NEW.id_condominio LIMIT 1);
        NEW.metodo_pago = 'desconocido';
    END IF;

    RETURN NEW;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;

-- ----------------------------
-- Function structure for actualizar_estado_notificacion
-- ----------------------------
DROP FUNCTION IF EXISTS "public"."actualizar_estado_notificacion"();
CREATE OR REPLACE FUNCTION "public"."actualizar_estado_notificacion"()
  RETURNS "pg_catalog"."trigger" AS $BODY$
DECLARE
  total_recibos NUMERIC := 0;
  existe_exo    BOOLEAN := FALSE;
BEGIN
  -- Recalcular monto_x_pagar como antes
  NEW.monto_x_pagar := NEW.monto_total
                       - COALESCE(NEW.pronto_pago, 0)
                       - COALESCE(NEW.monto_pagado, 0);
  IF NEW.monto_x_pagar < 0 THEN
    NEW.monto_x_pagar := 0;
  END IF;

  -- 1) Verificar exoneración activa
  SELECT COUNT(*) > 0
  INTO existe_exo
  FROM exoneracion_notificacion ex
  WHERE ex.id_notificacion = NEW.id_notificacion 
    AND ex.estado IS TRUE;

  IF existe_exo THEN
    NEW.estado := 'exonerada';
    RETURN NEW;
  END IF;

  -- 2) Sumar totales de recibos aprobados vinculados (a través de recibo_destino_fondos)
  SELECT COALESCE(SUM(rd.monto_aplicado),0)
  INTO total_recibos
  FROM recibo_cabecera rc
  JOIN recibo_destino_fondos rd
    ON rd.id_recibo = rc.id_recibo
  WHERE rc.estado = 'aprobado'
    AND rd.id_notificacion = NEW.id_notificacion;

  IF total_recibos >= NEW.monto_total THEN
    NEW.estado := 'pagada';
  ELSIF total_recibos > 0 THEN
    NEW.estado := 'parcialmente_pagada';
  ELSE
    NEW.estado := 'pendiente';
  END IF;

  RETURN NEW;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;

-- ----------------------------
-- Function structure for actualizar_estado_notificaciones_por_recibo
-- ----------------------------
DROP FUNCTION IF EXISTS "public"."actualizar_estado_notificaciones_por_recibo"();
CREATE OR REPLACE FUNCTION "public"."actualizar_estado_notificaciones_por_recibo"()
  RETURNS "pg_catalog"."trigger" AS $BODY$
BEGIN
  WITH pagos_aprobados AS (
    SELECT
      rc.id_inmueble,
      rc.id_condominio,
      SUM(rc.total_pagado) AS total_pagado
    FROM recibo_cabecera rc
    WHERE rc.estado = 'aprobado'
    GROUP BY rc.id_inmueble, rc.id_condominio
  ),
  exoneraciones AS (
    SELECT
      id_notificacion
    FROM exoneracion_notificacion
    WHERE estado IS TRUE
  ),
  estado_nuevo AS (
    SELECT
      nc.id_notificacion,
      CASE
        WHEN ex.id_notificacion IS NOT NULL THEN 'exonerada'
        WHEN COALESCE(pa.total_pagado, 0) >= nc.monto_total THEN 'pagada'
        WHEN COALESCE(pa.total_pagado, 0) > 0 THEN 'parcialmente_pagada'
        ELSE 'pendiente'
      END AS nuevo_estado
    FROM notificacion_cobro nc
    LEFT JOIN pagos_aprobados pa
      ON pa.id_inmueble = nc.id_inmueble
     AND pa.id_condominio = nc.id_condominio
    LEFT JOIN exoneraciones ex
      ON ex.id_notificacion = nc.id_notificacion
  )
  UPDATE notificacion_cobro nc
  SET estado = en.nuevo_estado
  FROM estado_nuevo en
  WHERE nc.id_notificacion = en.id_notificacion;

  RETURN NEW;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;

-- ----------------------------
-- Function structure for actualizar_saldo_cuenta_movimiento
-- ----------------------------
DROP FUNCTION IF EXISTS "public"."actualizar_saldo_cuenta_movimiento"();
CREATE OR REPLACE FUNCTION "public"."actualizar_saldo_cuenta_movimiento"()
  RETURNS "pg_catalog"."trigger" AS $BODY$
BEGIN
    IF NEW.estado = 'aprobado' AND (OLD.estado IS NULL OR OLD.estado IS DISTINCT FROM 'aprobado') THEN
        UPDATE cuenta
        SET saldo_actual = saldo_actual + NEW.monto
        WHERE id_cuenta = NEW.id_cuenta;
    ELSIF OLD.estado = 'aprobado' AND NEW.estado IS DISTINCT FROM 'aprobado' THEN
        UPDATE cuenta
        SET saldo_actual = saldo_actual - OLD.monto
        WHERE id_cuenta = OLD.id_cuenta;
    END IF;
    RETURN NEW;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;

-- ----------------------------
-- Function structure for actualizar_saldo_cuenta_movimiento_destino
-- ----------------------------
DROP FUNCTION IF EXISTS "public"."actualizar_saldo_cuenta_movimiento_destino"();
CREATE OR REPLACE FUNCTION "public"."actualizar_saldo_cuenta_movimiento_destino"()
  RETURNS "pg_catalog"."trigger" AS $BODY$
DECLARE
    v_row_count INT;
BEGIN
    RAISE NOTICE 'Trigger activado - ID Movimiento: %, Monto aplicado: %, Anterior monto: %', NEW.id_movimiento, NEW.monto_aplicado, OLD.monto_aplicado;
    
    IF NEW.monto_aplicado IS NOT NULL AND (TG_OP = 'INSERT' OR (OLD.monto_aplicado IS DISTINCT FROM NEW.monto_aplicado)) THEN
        UPDATE cuenta
        SET saldo_actual = saldo_actual - NEW.monto_aplicado
        WHERE id_cuenta = (SELECT id_cuenta FROM movimiento_general WHERE id_movimiento = NEW.id_movimiento);
        GET DIAGNOSTICS v_row_count = ROW_COUNT;
        IF v_row_count = 0 THEN
            RAISE NOTICE 'No se actualizó ninguna fila en cuenta para movimiento: %', NEW.id_movimiento;
        ELSE
            RAISE NOTICE 'Saldo reducido para movimiento % con monto %', NEW.id_movimiento, NEW.monto_aplicado;
        END IF;
    END IF;
    RETURN NEW;
EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error en trigger: %', SQLERRM;
        RETURN NULL;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;

-- ----------------------------
-- Function structure for actualizar_saldo_cuenta_movimiento_origen
-- ----------------------------
DROP FUNCTION IF EXISTS "public"."actualizar_saldo_cuenta_movimiento_origen"();
CREATE OR REPLACE FUNCTION "public"."actualizar_saldo_cuenta_movimiento_origen"()
  RETURNS "pg_catalog"."trigger" AS $BODY$
DECLARE
    v_row_count INT;
BEGIN
    RAISE NOTICE 'Trigger activado - Nuevo estado: %, ID Cuenta: %, Nuevo monto: %, Anterior estado: %, Anterior monto: %', 
                 NEW.estado, NEW.id_cuenta, NEW.monto, OLD.estado, OLD.monto;
    
    IF NEW.estado = 'aprobado' AND (OLD.estado IS NULL OR OLD.estado IS DISTINCT FROM 'aprobado') THEN
        IF NEW.id_cuenta IS NULL OR NEW.monto IS NULL THEN
            RAISE NOTICE 'Error: id_cuenta o monto son NULL';
        ELSE
            UPDATE cuenta
            SET saldo_actual = saldo_actual + NEW.monto
            WHERE id_cuenta = NEW.id_cuenta;
            GET DIAGNOSTICS v_row_count = ROW_COUNT;
            IF v_row_count = 0 THEN
                RAISE NOTICE 'No se actualizó ninguna fila en cuenta para id_cuenta: %', NEW.id_cuenta;
            ELSE
                RAISE NOTICE 'Saldo actualizado para cuenta % con monto %', NEW.id_cuenta, NEW.monto;
            END IF;
        END IF;
    ELSIF OLD.estado = 'aprobado' AND NEW.estado IS DISTINCT FROM 'aprobado' THEN
        IF OLD.id_cuenta IS NULL OR OLD.monto IS NULL THEN
            RAISE NOTICE 'Error: id_cuenta o monto anteriores son NULL';
        ELSE
            UPDATE cuenta
            SET saldo_actual = saldo_actual - OLD.monto
            WHERE id_cuenta = OLD.id_cuenta;
            GET DIAGNOSTICS v_row_count = ROW_COUNT;
            IF v_row_count = 0 THEN
                RAISE NOTICE 'No se actualizó ninguna fila en cuenta para id_cuenta: %', OLD.id_cuenta;
            ELSE
                RAISE NOTICE 'Saldo revertido para cuenta % con monto %', OLD.id_cuenta, OLD.monto;
            END IF;
        END IF;
    ELSIF NEW.estado = 'aprobado' AND OLD.estado = 'aprobado' AND NEW.monto IS DISTINCT FROM OLD.monto THEN
        IF NEW.id_cuenta IS NULL OR NEW.monto IS NULL OR OLD.monto IS NULL THEN
            RAISE NOTICE 'Error: id_cuenta o montos son NULL';
        ELSE
            UPDATE cuenta
            SET saldo_actual = saldo_actual - OLD.monto + NEW.monto
            WHERE id_cuenta = NEW.id_cuenta;
            GET DIAGNOSTICS v_row_count = ROW_COUNT;
            IF v_row_count = 0 THEN
                RAISE NOTICE 'No se actualizó ninguna fila en cuenta para id_cuenta: %', NEW.id_cuenta;
            ELSE
                RAISE NOTICE 'Saldo ajustado para cuenta % de % a %', NEW.id_cuenta, OLD.monto, NEW.monto;
            END IF;
        END IF;
    END IF;
    RETURN NEW;
EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error en trigger: %', SQLERRM;
        RETURN NULL;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;

-- ----------------------------
-- Function structure for calcular_monto_base_movimiento
-- ----------------------------
DROP FUNCTION IF EXISTS "public"."calcular_monto_base_movimiento"();
CREATE OR REPLACE FUNCTION "public"."calcular_monto_base_movimiento"()
  RETURNS "pg_catalog"."trigger" AS $BODY$
DECLARE
    v_tasa numeric(15,6);
    v_id_moneda_cuenta int4;
    v_id_moneda_movimiento int4;
BEGIN
    -- Obtener la moneda de la cuenta
    SELECT id_moneda INTO v_id_moneda_cuenta
    FROM cuenta
    WHERE id_cuenta = NEW.id_cuenta;

    -- Usar id_moneda del movimiento si existe, de lo contrario la de la cuenta
    v_id_moneda_movimiento := COALESCE(NEW.id_moneda, v_id_moneda_cuenta);

    -- Obtener la tasa más reciente de tipo_cambio
    IF v_id_moneda_cuenta = v_id_moneda_movimiento THEN
        v_tasa := 1.0; -- Misma moneda, no hay conversión
    ELSE
        SELECT tasa INTO v_tasa
        FROM tipo_cambio
        WHERE (id_moneda_origen = v_id_moneda_cuenta AND id_moneda_destino = v_id_moneda_movimiento)
           OR (id_moneda_origen = v_id_moneda_movimiento AND id_moneda_destino = v_id_moneda_cuenta)
           AND fecha_vigencia <= CURRENT_TIMESTAMP
        ORDER BY fecha_actualizacion DESC
        LIMIT 1;

        -- Si no hay tasa directa, invertir la tasa si existe la conversión inversa
        IF v_tasa IS NULL AND EXISTS (
            SELECT 1 FROM tipo_cambio
            WHERE id_moneda_origen = v_id_moneda_movimiento AND id_moneda_destino = v_id_moneda_cuenta
        ) THEN
            SELECT 1.0 / tasa INTO v_tasa
            FROM tipo_cambio
            WHERE id_moneda_origen = v_id_moneda_movimiento AND id_moneda_destino = v_id_moneda_cuenta
            ORDER BY fecha_actualizacion DESC
            LIMIT 1;
        END IF;

        -- Si aún no hay tasa, usar 1.0 como valor por defecto
        IF v_tasa IS NULL THEN
            v_tasa := 1.0;
        END IF;
    END IF;

    -- Calcular monto_base
    NEW.monto_base := NEW.monto * v_tasa;
    NEW.tasa := v_tasa; -- Actualizar la columna tasa

    RETURN NEW;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;

-- ----------------------------
-- Function structure for calcular_monto_x_pagar
-- ----------------------------
DROP FUNCTION IF EXISTS "public"."calcular_monto_x_pagar"();
CREATE OR REPLACE FUNCTION "public"."calcular_monto_x_pagar"()
  RETURNS "pg_catalog"."trigger" AS $BODY$
BEGIN
    NEW.monto_x_pagar := NEW.monto_total - COALESCE(NEW.pronto_pago, 0) - COALESCE(NEW.monto_pagado, 0);
    RETURN NEW;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;

-- ----------------------------
-- Function structure for calcular_tasa_monto_base
-- ----------------------------
DROP FUNCTION IF EXISTS "public"."calcular_tasa_monto_base"();
CREATE OR REPLACE FUNCTION "public"."calcular_tasa_monto_base"()
  RETURNS "pg_catalog"."trigger" AS $BODY$
BEGIN
    IF NEW.tasa IS NULL OR NEW.monto_base IS NULL THEN
        SELECT tc.tasa, NEW.monto * tc.tasa
        INTO NEW.tasa, NEW.monto_base
        FROM tipo_cambio tc
        JOIN cuenta c ON c.id_moneda = tc.id_moneda
        WHERE c.id_cuenta = NEW.id_cuenta
        AND tc.id_moneda = NEW.id_moneda
        AND tc.fecha <= NEW.fecha_registro
        ORDER BY tc.fecha DESC LIMIT 1;
        -- Fallback si no hay tasa
        IF NEW.tasa IS NULL THEN
            NEW.tasa = 1.0;
            NEW.monto_base = NEW.monto;
        END IF;
    END IF;
    RETURN NEW;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;

-- ----------------------------
-- Function structure for generar_notificaciones_desde_maestra
-- ----------------------------
DROP FUNCTION IF EXISTS "public"."generar_notificaciones_desde_maestra"("p_id_master" int4);
CREATE OR REPLACE FUNCTION "public"."generar_notificaciones_desde_maestra"("p_id_master" int4)
  RETURNS "pg_catalog"."void" AS $BODY$
DECLARE
    v_master   RECORD;
    v_inm      RECORD;
    v_notif    int4;
    v_tot      numeric(15,2);
    v_cnt_mon  int;
BEGIN
    /* 1) Obtener cabecera maestra */
    SELECT *
      INTO v_master
      FROM notificacion_cobro_master
     WHERE id_notificacion_master = p_id_master;

    IF NOT FOUND THEN
        RAISE EXCEPTION 'No existe notificación maestra %', p_id_master;
    END IF;

    /* 1.1) Sanidad: no mezclar monedas en la maestra */
    SELECT COUNT(DISTINCT id_moneda)
      INTO v_cnt_mon
      FROM notificacion_cobro_detalle_master
     WHERE id_notificacion_master = p_id_master;

    IF COALESCE(v_cnt_mon,0) > 1 THEN
        RAISE EXCEPTION 'La notificación maestra % contiene conceptos con distintas monedas', p_id_master;
    END IF;

    /* 2) Recorrer inmuebles activos del condominio */
    FOR v_inm IN
        SELECT id_inmueble, alicuota
          FROM inmueble
         WHERE id_condominio = v_master.id_condominio
           AND estado IS TRUE
    LOOP
        /* 2.1) UPSERT cabecera hija
           - NO tocar monto_pagado si ya existe.
           - Se recalcularán totales/estado luego.
        */
        INSERT INTO notificacion_cobro (
            id_inmueble, id_notificacion_master,
            anio, mes, fecha_emision, fecha_vencimiento,
            monto_total, monto_x_pagar, monto_pagado,
            pronto_pago, estado, id_moneda,
            descripcion, activa, id_tipo, id_condominio
        )
        VALUES (
            v_inm.id_inmueble, p_id_master,
            v_master.anio, v_master.mes,
            v_master.fecha_emision, v_master.fecha_vencimiento,
            0, 0, 0,
            0, 'pendiente', v_master.id_moneda,
            v_master.descripcion, FALSE,
            v_master.id_tipo, v_master.id_condominio
        )
        ON CONFLICT (id_inmueble, id_notificacion_master)
        DO UPDATE SET
            anio              = EXCLUDED.anio,
            mes               = EXCLUDED.mes,
            fecha_emision     = EXCLUDED.fecha_emision,
            fecha_vencimiento = EXCLUDED.fecha_vencimiento,
            descripcion       = EXCLUDED.descripcion,
            id_moneda         = EXCLUDED.id_moneda,
            id_tipo           = EXCLUDED.id_tipo,
            id_condominio     = EXCLUDED.id_condominio,
            activa            = FALSE
        RETURNING id_notificacion
          INTO v_notif;

        /* 2.2) Borrar detalles antiguos del inmueble/maestra */
        DELETE FROM notificacion_cobro_detalle
         WHERE id_notificacion = v_notif;

        /* 2.3) Insertar nuevos detalles prorrateados por alícuota */
        INSERT INTO notificacion_cobro_detalle (
            id_notificacion, id_plan_cuenta,
            descripcion, monto,
            id_condominio, id_inmueble,
            anio, mes, estado, id_tipo,
            id_detalle_origen
        )
        SELECT
            v_notif, d.id_plan_cuenta,
            d.descripcion,
            ROUND(d.monto * v_inm.alicuota, 2) AS monto_prorrateado,
            v_master.id_condominio, v_inm.id_inmueble,
            v_master.anio, v_master.mes,
            'pendiente', v_master.id_tipo,
            d.id_detalle
          FROM notificacion_cobro_detalle_master d
         WHERE d.id_notificacion_master = p_id_master;

        /* 2.4) Recalcular totales del encabezado */
        SELECT COALESCE(SUM(monto),0)::numeric(15,2)
          INTO v_tot
          FROM notificacion_cobro_detalle
         WHERE id_notificacion = v_notif;

        /* Si prorrateas pronto pago por inmueble, ajusta aquí:
           p.ej.: monto_x_pagar = GREATEST(v_tot - COALESCE(nc.pronto_pago,0), 0)
        */
        UPDATE notificacion_cobro nc
           SET monto_total        = v_tot,
               monto_x_pagar      = v_tot,
               estado             = CASE
                                      WHEN COALESCE(nc.monto_pagado,0) <= 0 THEN 'pendiente'
                                      WHEN COALESCE(nc.monto_pagado,0) >= v_tot THEN 'pagada'
                                      ELSE 'parcialmente_pagada'
                                    END,
               fecha_actualizacion = CURRENT_TIMESTAMP
         WHERE nc.id_notificacion = v_notif;
    END LOOP;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;

-- ----------------------------
-- Function structure for recalcular_saldo_cuenta
-- ----------------------------
DROP FUNCTION IF EXISTS "public"."recalcular_saldo_cuenta"("p_id_cuenta" int4);
CREATE OR REPLACE FUNCTION "public"."recalcular_saldo_cuenta"("p_id_cuenta" int4)
  RETURNS "pg_catalog"."void" AS $BODY$
DECLARE
    total_ingresos NUMERIC := 0;
    total_egresos  NUMERIC := 0;
BEGIN
    -- Sumar ingresos conciliados
    SELECT COALESCE(SUM(monto), 0)
    INTO total_ingresos
    FROM movimiento_detalle_ingreso
    WHERE id_cuenta = p_id_cuenta
      AND estado = 'conciliado';

    -- Agregar recibos de origen de fondos aprobados o conciliados
    SELECT COALESCE(total_ingresos, 0) + COALESCE(SUM(monto), 0)
    INTO total_ingresos
    FROM recibo_origen_fondos
    WHERE id_cuenta = p_id_cuenta
      AND estado IN ('aprobado', 'conciliado');

    -- Sumar egresos conciliados
    SELECT COALESCE(SUM(monto_aplicado), 0)
    INTO total_egresos
    FROM movimiento_detalle_egreso
    WHERE id_cuenta = p_id_cuenta
      AND estado = 'conciliado';

    -- Actualizar saldo
    UPDATE cuenta
    SET saldo_actual = total_ingresos - total_egresos
    WHERE id_cuenta = p_id_cuenta;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;

-- ----------------------------
-- Function structure for trg_touch_propietario_inmueble
-- ----------------------------
DROP FUNCTION IF EXISTS "public"."trg_touch_propietario_inmueble"();
CREATE OR REPLACE FUNCTION "public"."trg_touch_propietario_inmueble"()
  RETURNS "pg_catalog"."trigger" AS $BODY$
BEGIN
  NEW.fecha_actualizacion := CURRENT_TIMESTAMP;
  RETURN NEW;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;

-- ----------------------------
-- Function structure for update_monto_x_pagar
-- ----------------------------
DROP FUNCTION IF EXISTS "public"."update_monto_x_pagar"();
CREATE OR REPLACE FUNCTION "public"."update_monto_x_pagar"()
  RETURNS "pg_catalog"."trigger" AS $BODY$
BEGIN
    -- Calculate monto_x_pagar: monto_total - monto_pagado - COALESCE(pronto_pago, 0)
    NEW.monto_x_pagar = NEW.monto_total - NEW.monto_pagado - COALESCE(NEW.pronto_pago, 0);
    
    -- Ensure monto_x_pagar is not negative (optional, remove if not needed)
    IF NEW.monto_x_pagar < 0 THEN
        NEW.monto_x_pagar = 0;
    END IF;
    
    RETURN NEW;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;

-- ----------------------------
-- Function structure for update_timestamp
-- ----------------------------
DROP FUNCTION IF EXISTS "public"."update_timestamp"();
CREATE OR REPLACE FUNCTION "public"."update_timestamp"()
  RETURNS "pg_catalog"."trigger" AS $BODY$
BEGIN
  NEW.fecha_actualizacion := CURRENT_TIMESTAMP;
  RETURN NEW;
END$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;

-- ----------------------------
-- Function structure for validar_presupuesto_antes_cierre
-- ----------------------------
DROP FUNCTION IF EXISTS "public"."validar_presupuesto_antes_cierre"();
CREATE OR REPLACE FUNCTION "public"."validar_presupuesto_antes_cierre"()
  RETURNS "pg_catalog"."trigger" AS $BODY$
BEGIN
    IF NEW.estado = 'cerrado' AND OLD.estado != 'cerrado' THEN
        IF NOT EXISTS (
            SELECT 1
            FROM presupuesto_mes pm
            WHERE pm.id_condominio = NEW.id_condominio
            AND pm.anio = NEW.anio
            AND pm.mes = NEW.mes
        ) THEN
            RAISE EXCEPTION 'No existe un presupuesto para el periodo %/% del condominio %', NEW.anio, NEW.mes, NEW.id_condominio;
        END IF;
    END IF;
    RETURN NEW;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "public"."administradores_id_administrador_seq"
OWNED BY "public"."administradores"."id_administrador";
SELECT setval('"public"."administradores_id_administrador_seq"', 2, true);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "public"."auditoria_id_auditoria_seq"
OWNED BY "public"."auditoria"."id_auditoria";
SELECT setval('"public"."auditoria_id_auditoria_seq"', 1, false);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
SELECT setval('"public"."cierre_contable_id_seq"', 1, false);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "public"."condominio_id_condominio_seq"
OWNED BY "public"."condominio"."id_condominio";
SELECT setval('"public"."condominio_id_condominio_seq"', 7, true);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "public"."credito_a_favor_id_credito_a_favor_seq"
OWNED BY "public"."credito_a_favor"."id_credito_a_favor";
SELECT setval('"public"."credito_a_favor_id_credito_a_favor_seq"', 78, true);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "public"."cuenta_id_cuenta_seq"
OWNED BY "public"."cuenta"."id_cuenta";
SELECT setval('"public"."cuenta_id_cuenta_seq"', 1, false);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "public"."distribucion_gasto_id_distribucion_gasto_seq"
OWNED BY "public"."distribucion_gasto"."id_distribucion_gasto";
SELECT setval('"public"."distribucion_gasto_id_distribucion_gasto_seq"', 1, false);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "public"."ejecucion_gasto_id_ejecucion_seq"
OWNED BY "public"."ejecucion_gasto"."id_ejecucion";
SELECT setval('"public"."ejecucion_gasto_id_ejecucion_seq"', 1, true);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "public"."esquema_pronto_pago_id_esquema_seq"
OWNED BY "public"."esquema_pronto_pago"."id_esquema";
SELECT setval('"public"."esquema_pronto_pago_id_esquema_seq"', 1, false);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "public"."exoneracion_notificacion_id_exoneracion_seq"
OWNED BY "public"."exoneracion_notificacion"."id_exoneracion";
SELECT setval('"public"."exoneracion_notificacion_id_exoneracion_seq"', 1, false);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "public"."gasto_id_gasto_seq"
OWNED BY "public"."gasto"."id_gasto";
SELECT setval('"public"."gasto_id_gasto_seq"', 1, false);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "public"."inmueble_id_inmueble_seq"
OWNED BY "public"."inmueble"."id_inmueble";
SELECT setval('"public"."inmueble_id_inmueble_seq"', 25, true);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "public"."log_distribucion_id_log_seq"
OWNED BY "public"."log_distribucion"."id_log";
SELECT setval('"public"."log_distribucion_id_log_seq"', 1, false);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "public"."moneda_id_moneda_seq"
OWNED BY "public"."moneda"."id_moneda";
SELECT setval('"public"."moneda_id_moneda_seq"', 8, true);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
SELECT setval('"public"."movimiento_destino_fondos_id_seq"', 2, true);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "public"."movimiento_detalle_egreso_id_detalle_egreso_seq"
OWNED BY "public"."movimiento_detalle_egreso"."id_detalle_egreso";
SELECT setval('"public"."movimiento_detalle_egreso_id_detalle_egreso_seq"', 17, true);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "public"."movimiento_detalle_ingreso_id_detalle_ingreso_seq"
OWNED BY "public"."movimiento_detalle_ingreso"."id_detalle_ingreso";
SELECT setval('"public"."movimiento_detalle_ingreso_id_detalle_ingreso_seq"', 47, true);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "public"."movimiento_general_id_movimiento_seq"
OWNED BY "public"."movimiento_general"."id_movimiento";
SELECT setval('"public"."movimiento_general_id_movimiento_seq"', 47, true);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
SELECT setval('"public"."movimiento_general_id_seq"', 4, true);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
SELECT setval('"public"."movimiento_origen_fondos_id_seq"', 3, true);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
SELECT setval('"public"."movimientos_caja_banco_id_movimiento_seq"', 2, true);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "public"."notificacion_cobro_detalle_id_detalle_seq"
OWNED BY "public"."notificacion_cobro_detalle"."id_detalle";
SELECT setval('"public"."notificacion_cobro_detalle_id_detalle_seq"', 656, true);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
SELECT setval('"public"."notificacion_cobro_detalle_master_id_detalle_seq"', 72, true);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "public"."notificacion_cobro_detalle_master_id_seq"
OWNED BY "public"."notificacion_cobro_detalle_master"."id_detalle";
SELECT setval('"public"."notificacion_cobro_detalle_master_id_seq"', 1, true);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "public"."notificacion_cobro_inmueble_id_notificacion_seq"
OWNED BY "public"."notificacion_cobro"."id_notificacion";
SELECT setval('"public"."notificacion_cobro_inmueble_id_notificacion_seq"', 284, true);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "public"."notificacion_cobro_master_id_seq"
OWNED BY "public"."notificacion_cobro_master"."id_notificacion_master";
SELECT setval('"public"."notificacion_cobro_master_id_seq"', 28, true);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "public"."notificaciones_cobro_id_notificacion_seq"
OWNED BY "public"."notificaciones_cobro"."id_notificacion";
SELECT setval('"public"."notificaciones_cobro_id_notificacion_seq"', 1, false);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "public"."pago_id_pago_seq"
OWNED BY "public"."pago"."id_pago";
SELECT setval('"public"."pago_id_pago_seq"', 1, false);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "public"."plan_cuenta_base_id_plan_base_seq"
OWNED BY "public"."plan_cuenta_base"."id_plan_base";
SELECT setval('"public"."plan_cuenta_base_id_plan_base_seq"', 22, true);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "public"."plan_cuenta_id_plan_seq"
OWNED BY "public"."plan_cuenta"."id_plan";
SELECT setval('"public"."plan_cuenta_id_plan_seq"', 67, true);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "public"."propietario_id_propietario_seq1"
OWNED BY "public"."propietario"."id_propietario";
SELECT setval('"public"."propietario_id_propietario_seq1"', 1, true);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "public"."recibo_cabecera_id_recibo_seq"
OWNED BY "public"."recibo_cabecera"."id_recibo";
SELECT setval('"public"."recibo_cabecera_id_recibo_seq"', 246, true);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
SELECT setval('"public"."recibo_cabecera_numero_recibo_seq"', 231, true);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "public"."recibo_destino_fondos_id_destino_fondos_seq"
OWNED BY "public"."recibo_destino_fondos"."id_destino_fondos";
SELECT setval('"public"."recibo_destino_fondos_id_destino_fondos_seq"', 409, true);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "public"."recibo_origen_fondos_id_origen_fondos_seq"
OWNED BY "public"."recibo_origen_fondos"."id_origen_fondos";
SELECT setval('"public"."recibo_origen_fondos_id_origen_fondos_seq"', 406, true);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "public"."rol_id_rol_seq"
OWNED BY "public"."rol"."id_rol";
SELECT setval('"public"."rol_id_rol_seq"', 1, true);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
SELECT setval('"public"."tipo_cambio_id_tipo_cambio_seq"', 1, false);

-- ----------------------------
-- Primary Key structure for table auditoria
-- ----------------------------
ALTER TABLE "public"."auditoria" ADD CONSTRAINT "auditoria_pkey" PRIMARY KEY ("id_auditoria");

-- ----------------------------
-- Indexes structure for table cierre_contable
-- ----------------------------
CREATE INDEX "idx_cierre_periodo" ON "public"."cierre_contable" USING btree (
  "id_condominio" "pg_catalog"."int4_ops" ASC NULLS LAST,
  "anio" "pg_catalog"."int4_ops" ASC NULLS LAST,
  "mes" "pg_catalog"."int4_ops" ASC NULLS LAST
);

-- ----------------------------
-- Uniques structure for table cierre_contable
-- ----------------------------
ALTER TABLE "public"."cierre_contable" ADD CONSTRAINT "unique_cierre" UNIQUE ("id_condominio", "anio", "mes");

-- ----------------------------
-- Checks structure for table cierre_contable
-- ----------------------------
ALTER TABLE "public"."cierre_contable" ADD CONSTRAINT "cierre_contable_anio_check" CHECK ((anio >= 2000));
ALTER TABLE "public"."cierre_contable" ADD CONSTRAINT "cierre_contable_mes_check" CHECK (((mes >= 1) AND (mes <= 12)));

-- ----------------------------
-- Primary Key structure for table cierre_contable
-- ----------------------------
ALTER TABLE "public"."cierre_contable" ADD CONSTRAINT "cierre_contable_pkey" PRIMARY KEY ("id_cierre");

-- ----------------------------
-- Checks structure for table condominio
-- ----------------------------
ALTER TABLE "public"."condominio" ADD CONSTRAINT "condominio_esquema_cuota_check" CHECK (((esquema_cuota)::text = ANY (ARRAY[('fija'::character varying)::text, ('fija_alicuota'::character varying)::text, ('equitativa'::character varying)::text, ('alicuota'::character varying)::text])));

-- ----------------------------
-- Primary Key structure for table condominio
-- ----------------------------
ALTER TABLE "public"."condominio" ADD CONSTRAINT "condominio_pkey" PRIMARY KEY ("id_condominio");

-- ----------------------------
-- Indexes structure for table credito_a_favor
-- ----------------------------
CREATE INDEX "idx_credito_a_favor_propietario_moneda" ON "public"."credito_a_favor" USING btree (
  "id_propietario" "pg_catalog"."int4_ops" ASC NULLS LAST,
  "id_moneda" "pg_catalog"."int4_ops" ASC NULLS LAST,
  "estado" COLLATE "pg_catalog"."default" "pg_catalog"."text_ops" ASC NULLS LAST
);

-- ----------------------------
-- Checks structure for table credito_a_favor
-- ----------------------------
ALTER TABLE "public"."credito_a_favor" ADD CONSTRAINT "credito_a_favor_estado_check" CHECK (((estado)::text = ANY (ARRAY[('activo'::character varying)::text, ('usado'::character varying)::text, ('cancelado'::character varying)::text])));

-- ----------------------------
-- Primary Key structure for table credito_a_favor
-- ----------------------------
ALTER TABLE "public"."credito_a_favor" ADD CONSTRAINT "credito_a_favor_pkey" PRIMARY KEY ("id_credito_a_favor");

-- ----------------------------
-- Checks structure for table cuenta
-- ----------------------------
ALTER TABLE "public"."cuenta" ADD CONSTRAINT "cuenta_tipo_check" CHECK (((tipo)::text = ANY (ARRAY[('banco'::character varying)::text, ('efectivo'::character varying)::text])));

-- ----------------------------
-- Primary Key structure for table cuenta
-- ----------------------------
ALTER TABLE "public"."cuenta" ADD CONSTRAINT "cuenta_pkey" PRIMARY KEY ("id_cuenta");

-- ----------------------------
-- Checks structure for table distribucion_gasto
-- ----------------------------
ALTER TABLE "public"."distribucion_gasto" ADD CONSTRAINT "distribucion_gasto_porcentaje_check" CHECK (((porcentaje > (0)::numeric) AND (porcentaje <= (100)::numeric)));
ALTER TABLE "public"."distribucion_gasto" ADD CONSTRAINT "distribucion_gasto_monto_check" CHECK ((monto >= (0)::numeric));
ALTER TABLE "public"."distribucion_gasto" ADD CONSTRAINT "distribucion_gasto_monto_moneda_base_check" CHECK ((monto_moneda_base >= (0)::numeric));
ALTER TABLE "public"."distribucion_gasto" ADD CONSTRAINT "distribucion_gasto_estado_check" CHECK (((estado)::text = ANY (ARRAY[('pendiente'::character varying)::text, ('aprobado'::character varying)::text, ('cancelado'::character varying)::text])));

-- ----------------------------
-- Primary Key structure for table distribucion_gasto
-- ----------------------------
ALTER TABLE "public"."distribucion_gasto" ADD CONSTRAINT "distribucion_gasto_pkey" PRIMARY KEY ("id_distribucion_gasto");

-- ----------------------------
-- Checks structure for table ejecucion_gasto
-- ----------------------------
ALTER TABLE "public"."ejecucion_gasto" ADD CONSTRAINT "ejecucion_gasto_anio_check" CHECK ((anio >= 2000));
ALTER TABLE "public"."ejecucion_gasto" ADD CONSTRAINT "ejecucion_gasto_mes_check" CHECK (((mes >= 1) AND (mes <= 12)));
ALTER TABLE "public"."ejecucion_gasto" ADD CONSTRAINT "ejecucion_gasto_monto_check" CHECK ((monto >= (0)::double precision));

-- ----------------------------
-- Primary Key structure for table ejecucion_gasto
-- ----------------------------
ALTER TABLE "public"."ejecucion_gasto" ADD CONSTRAINT "ejecucion_gasto_pkey" PRIMARY KEY ("id_ejecucion");

-- ----------------------------
-- Indexes structure for table esquema_pronto_pago
-- ----------------------------
CREATE INDEX "idx_esquema_pronto_pago_condominio" ON "public"."esquema_pronto_pago" USING btree (
  "id_condominio" "pg_catalog"."int4_ops" ASC NULLS LAST,
  "valido_desde" "pg_catalog"."date_ops" ASC NULLS LAST,
  "estado" "pg_catalog"."bool_ops" ASC NULLS LAST
);

-- ----------------------------
-- Uniques structure for table esquema_pronto_pago
-- ----------------------------
ALTER TABLE "public"."esquema_pronto_pago" ADD CONSTRAINT "unique_esquema_rango" UNIQUE ("id_condominio", "valido_desde", "dia_inicio", "dia_fin");

-- ----------------------------
-- Checks structure for table esquema_pronto_pago
-- ----------------------------
ALTER TABLE "public"."esquema_pronto_pago" ADD CONSTRAINT "esquema_pronto_pago_dia_inicio_check" CHECK (((dia_inicio >= 1) AND (dia_inicio <= 31)));
ALTER TABLE "public"."esquema_pronto_pago" ADD CONSTRAINT "esquema_pronto_pago_check" CHECK (((dia_fin >= dia_inicio) AND (dia_fin <= 31)));
ALTER TABLE "public"."esquema_pronto_pago" ADD CONSTRAINT "esquema_pronto_pago_porcentaje_descuento_check" CHECK (((porcentaje_descuento >= (0)::numeric) AND (porcentaje_descuento <= (100)::numeric)));

-- ----------------------------
-- Primary Key structure for table esquema_pronto_pago
-- ----------------------------
ALTER TABLE "public"."esquema_pronto_pago" ADD CONSTRAINT "esquema_pronto_pago_pkey" PRIMARY KEY ("id_esquema");

-- ----------------------------
-- Uniques structure for table exoneracion_notificacion
-- ----------------------------
ALTER TABLE "public"."exoneracion_notificacion" ADD CONSTRAINT "unique_exoneracion_notificacion" UNIQUE ("id_notificacion");

-- ----------------------------
-- Primary Key structure for table exoneracion_notificacion
-- ----------------------------
ALTER TABLE "public"."exoneracion_notificacion" ADD CONSTRAINT "exoneracion_notificacion_pkey" PRIMARY KEY ("id_exoneracion");

-- ----------------------------
-- Checks structure for table gasto
-- ----------------------------
ALTER TABLE "public"."gasto" ADD CONSTRAINT "gasto_monto_check" CHECK ((monto >= (0)::numeric));
ALTER TABLE "public"."gasto" ADD CONSTRAINT "gasto_tipo_check" CHECK (((tipo)::text = ANY (ARRAY[('ordinario'::character varying)::text, ('extraordinario'::character varying)::text])));
ALTER TABLE "public"."gasto" ADD CONSTRAINT "gasto_metodo_distribucion_check" CHECK (((metodo_distribucion)::text = ANY (ARRAY[('fija_alicuota'::character varying)::text, ('fija_equitativa'::character varying)::text, ('variable_alicuota'::character varying)::text, ('variable_equitativa'::character varying)::text])));

-- ----------------------------
-- Primary Key structure for table gasto
-- ----------------------------
ALTER TABLE "public"."gasto" ADD CONSTRAINT "gasto_pkey" PRIMARY KEY ("id_gasto");

-- ----------------------------
-- Primary Key structure for table inmueble
-- ----------------------------
ALTER TABLE "public"."inmueble" ADD CONSTRAINT "inmueble_pkey" PRIMARY KEY ("id_inmueble");

-- ----------------------------
-- Indexes structure for table log_distribucion
-- ----------------------------
CREATE INDEX "idx_log_distribucion_master" ON "public"."log_distribucion" USING btree (
  "id_notificacion_master" "pg_catalog"."int4_ops" ASC NULLS LAST
);

-- ----------------------------
-- Checks structure for table log_distribucion
-- ----------------------------
ALTER TABLE "public"."log_distribucion" ADD CONSTRAINT "chk_tipo" CHECK (((tipo)::text = ANY ((ARRAY['informe'::character varying, 'cobro'::character varying])::text[])));

-- ----------------------------
-- Primary Key structure for table log_distribucion
-- ----------------------------
ALTER TABLE "public"."log_distribucion" ADD CONSTRAINT "log_distribucion_pkey" PRIMARY KEY ("id_log");

-- ----------------------------
-- Uniques structure for table moneda
-- ----------------------------
ALTER TABLE "public"."moneda" ADD CONSTRAINT "moneda_codigo_key" UNIQUE ("codigo");

-- ----------------------------
-- Checks structure for table moneda
-- ----------------------------
ALTER TABLE "public"."moneda" ADD CONSTRAINT "moneda_tipo_moneda_check" CHECK (((tipo_moneda)::text = ANY (ARRAY[('FIAT'::character varying)::text, ('CRYPTO'::character varying)::text])));

-- ----------------------------
-- Primary Key structure for table moneda
-- ----------------------------
ALTER TABLE "public"."moneda" ADD CONSTRAINT "moneda_pkey" PRIMARY KEY ("id_moneda");

-- ----------------------------
-- Checks structure for table movimiento_detalle_egreso
-- ----------------------------
ALTER TABLE "public"."movimiento_detalle_egreso" ADD CONSTRAINT "movimiento_detalle_egreso_estado_check" CHECK (((estado)::text = ANY ((ARRAY['pendiente'::character varying, 'conciliado'::character varying, 'cancelado'::character varying])::text[])));

-- ----------------------------
-- Primary Key structure for table movimiento_detalle_egreso
-- ----------------------------
ALTER TABLE "public"."movimiento_detalle_egreso" ADD CONSTRAINT "movimiento_detalle_egreso_pkey" PRIMARY KEY ("id_detalle_egreso");

-- ----------------------------
-- Checks structure for table movimiento_detalle_ingreso
-- ----------------------------
ALTER TABLE "public"."movimiento_detalle_ingreso" ADD CONSTRAINT "movimiento_detalle_ingreso_estado_check" CHECK (((estado)::text = ANY ((ARRAY['pendiente'::character varying, 'conciliado'::character varying, 'cancelado'::character varying])::text[])));

-- ----------------------------
-- Primary Key structure for table movimiento_detalle_ingreso
-- ----------------------------
ALTER TABLE "public"."movimiento_detalle_ingreso" ADD CONSTRAINT "movimiento_detalle_ingreso_pkey" PRIMARY KEY ("id_detalle_ingreso");

-- ----------------------------
-- Checks structure for table movimiento_general
-- ----------------------------
ALTER TABLE "public"."movimiento_general" ADD CONSTRAINT "movimiento_general_tipo_movimiento_check" CHECK (((tipo_movimiento)::text = ANY ((ARRAY['ingreso'::character varying, 'egreso'::character varying])::text[])));
ALTER TABLE "public"."movimiento_general" ADD CONSTRAINT "movimiento_general_estado_check" CHECK (((estado)::text = ANY ((ARRAY['pendiente'::character varying, 'conciliado'::character varying, 'cancelado'::character varying])::text[])));
ALTER TABLE "public"."movimiento_general" ADD CONSTRAINT "movimiento_general_monto_total_check" CHECK ((monto_total >= (0)::numeric));

-- ----------------------------
-- Primary Key structure for table movimiento_general
-- ----------------------------
ALTER TABLE "public"."movimiento_general" ADD CONSTRAINT "movimiento_general_pkey" PRIMARY KEY ("id_movimiento");

-- ----------------------------
-- Indexes structure for table notificacion_cobro
-- ----------------------------
CREATE INDEX "idx_notificacion_cobro_inmueble" ON "public"."notificacion_cobro" USING btree (
  "id_inmueble" "pg_catalog"."int4_ops" ASC NULLS LAST
);
CREATE INDEX "idx_notificacion_cobro_master" ON "public"."notificacion_cobro" USING btree (
  "id_notificacion_master" "pg_catalog"."int4_ops" ASC NULLS LAST
);
CREATE UNIQUE INDEX "ux_nc_master_emitida" ON "public"."notificacion_cobro" USING btree (
  "id_condominio" "pg_catalog"."int4_ops" ASC NULLS LAST,
  "anio" "pg_catalog"."int4_ops" ASC NULLS LAST,
  "mes" "pg_catalog"."int4_ops" ASC NULLS LAST,
  "id_tipo" "pg_catalog"."int2_ops" ASC NULLS LAST
) WHERE id_notificacion_master IS NOT NULL AND estado::text = 'emitida'::text;

-- ----------------------------
-- Uniques structure for table notificacion_cobro
-- ----------------------------
ALTER TABLE "public"."notificacion_cobro" ADD CONSTRAINT "unique_notificacion_inmueble" UNIQUE ("id_inmueble", "id_notificacion_master");

-- ----------------------------
-- Checks structure for table notificacion_cobro
-- ----------------------------
ALTER TABLE "public"."notificacion_cobro" ADD CONSTRAINT "chk_id_tipo" CHECK ((id_tipo = ANY (ARRAY[1, 2])));
ALTER TABLE "public"."notificacion_cobro" ADD CONSTRAINT "chk_estado" CHECK (((estado)::text = ANY (ARRAY[('pendiente'::character varying)::text, ('pagada'::character varying)::text, ('cancelada'::character varying)::text, ('parcialmente_pagada'::character varying)::text])));

-- ----------------------------
-- Primary Key structure for table notificacion_cobro
-- ----------------------------
ALTER TABLE "public"."notificacion_cobro" ADD CONSTRAINT "notificacion_cobro_inmueble_pkey" PRIMARY KEY ("id_notificacion");

-- ----------------------------
-- Indexes structure for table notificacion_cobro_detalle
-- ----------------------------
CREATE INDEX "idx_notificacion_cobro_detalle_notif" ON "public"."notificacion_cobro_detalle" USING btree (
  "id_notificacion" "pg_catalog"."int4_ops" ASC NULLS LAST
);

-- ----------------------------
-- Checks structure for table notificacion_cobro_detalle
-- ----------------------------
ALTER TABLE "public"."notificacion_cobro_detalle" ADD CONSTRAINT "chk_estado" CHECK (((estado)::text = ANY ((ARRAY['pendiente'::character varying, 'pagada'::character varying, 'cancelada'::character varying])::text[])));
ALTER TABLE "public"."notificacion_cobro_detalle" ADD CONSTRAINT "chk_id_tipo" CHECK ((id_tipo = ANY (ARRAY[1, 2])));

-- ----------------------------
-- Primary Key structure for table notificacion_cobro_detalle
-- ----------------------------
ALTER TABLE "public"."notificacion_cobro_detalle" ADD CONSTRAINT "notificacion_cobro_detalle_pkey" PRIMARY KEY ("id_detalle");

-- ----------------------------
-- Indexes structure for table notificacion_cobro_detalle_master
-- ----------------------------
CREATE INDEX "idx_ncdm_id_master" ON "public"."notificacion_cobro_detalle_master" USING btree (
  "id_notificacion_master" "pg_catalog"."int4_ops" ASC NULLS LAST
);
CREATE INDEX "idx_ncdm_id_moneda" ON "public"."notificacion_cobro_detalle_master" USING btree (
  "id_moneda" "pg_catalog"."int4_ops" ASC NULLS LAST
);

-- ----------------------------
-- Checks structure for table notificacion_cobro_detalle_master
-- ----------------------------
ALTER TABLE "public"."notificacion_cobro_detalle_master" ADD CONSTRAINT "chk_tipo_movimiento" CHECK (((tipo_movimiento)::text = ANY ((ARRAY['ingreso'::character varying, 'egreso'::character varying])::text[])));

-- ----------------------------
-- Primary Key structure for table notificacion_cobro_detalle_master
-- ----------------------------
ALTER TABLE "public"."notificacion_cobro_detalle_master" ADD CONSTRAINT "notificacion_cobro_detalle_copy1_pkey" PRIMARY KEY ("id_detalle");

-- ----------------------------
-- Indexes structure for table notificacion_cobro_master
-- ----------------------------
CREATE UNIQUE INDEX "idx_ncm_condo_periodo_estado" ON "public"."notificacion_cobro_master" USING btree (
  "id_condominio" "pg_catalog"."int4_ops" ASC NULLS LAST,
  "anio" "pg_catalog"."int4_ops" ASC NULLS LAST,
  "mes" "pg_catalog"."int4_ops" ASC NULLS LAST,
  "estado" COLLATE "pg_catalog"."default" "pg_catalog"."text_ops" ASC NULLS LAST,
  "id_tipo" "pg_catalog"."int2_ops" ASC NULLS LAST
);
CREATE UNIQUE INDEX "uq_ncm_activa_periodo" ON "public"."notificacion_cobro_master" USING btree (
  "id_condominio" "pg_catalog"."int4_ops" ASC NULLS LAST,
  "anio" "pg_catalog"."int4_ops" ASC NULLS LAST,
  "mes" "pg_catalog"."int4_ops" ASC NULLS LAST,
  "id_tipo" "pg_catalog"."int2_ops" ASC NULLS LAST
) WHERE activa IS TRUE;

-- ----------------------------
-- Primary Key structure for table notificacion_cobro_master
-- ----------------------------
ALTER TABLE "public"."notificacion_cobro_master" ADD CONSTRAINT "notificacion_cobro_copy1_pkey" PRIMARY KEY ("id_notificacion_master");

-- ----------------------------
-- Indexes structure for table notificaciones_cobro
-- ----------------------------
CREATE INDEX "idx_notificaciones_cobro_inmueble" ON "public"."notificaciones_cobro" USING btree (
  "id_inmueble" "pg_catalog"."int4_ops" ASC NULLS LAST
);
CREATE INDEX "idx_notificaciones_cobro_master" ON "public"."notificaciones_cobro" USING btree (
  "id_notificacion_master" "pg_catalog"."int4_ops" ASC NULLS LAST
);

-- ----------------------------
-- Checks structure for table notificaciones_cobro
-- ----------------------------
ALTER TABLE "public"."notificaciones_cobro" ADD CONSTRAINT "chk_estado" CHECK (((estado)::text = ANY ((ARRAY['pendiente'::character varying, 'pagada'::character varying, 'cancelada'::character varying])::text[])));

-- ----------------------------
-- Primary Key structure for table notificaciones_cobro
-- ----------------------------
ALTER TABLE "public"."notificaciones_cobro" ADD CONSTRAINT "notificaciones_cobro_pkey" PRIMARY KEY ("id_notificacion");

-- ----------------------------
-- Indexes structure for table pago
-- ----------------------------
CREATE INDEX "idx_pago_moneda" ON "public"."pago" USING btree (
  "id_moneda" "pg_catalog"."int4_ops" ASC NULLS LAST
);

-- ----------------------------
-- Primary Key structure for table pago
-- ----------------------------
ALTER TABLE "public"."pago" ADD CONSTRAINT "pago_pkey" PRIMARY KEY ("id_pago");

-- ----------------------------
-- Primary Key structure for table plan_cuenta
-- ----------------------------
ALTER TABLE "public"."plan_cuenta" ADD CONSTRAINT "plan_cuenta_pkey" PRIMARY KEY ("id_plan");

-- ----------------------------
-- Primary Key structure for table plan_cuenta_base
-- ----------------------------
ALTER TABLE "public"."plan_cuenta_base" ADD CONSTRAINT "plan_cuenta_base_pkey" PRIMARY KEY ("id_plan_base");

-- ----------------------------
-- Primary Key structure for table propietario
-- ----------------------------
ALTER TABLE "public"."propietario" ADD CONSTRAINT "propietario_pkey" PRIMARY KEY ("id_propietario");

-- ----------------------------
-- Triggers structure for table propietario_inmueble
-- ----------------------------
CREATE TRIGGER "trg_touch_propietario_inmueble" BEFORE UPDATE ON "public"."propietario_inmueble"
FOR EACH ROW
EXECUTE PROCEDURE "public"."trg_touch_propietario_inmueble"();

-- ----------------------------
-- Primary Key structure for table propietario_inmueble
-- ----------------------------
ALTER TABLE "public"."propietario_inmueble" ADD CONSTRAINT "propietario_inmueble_pkey" PRIMARY KEY ("id_propietario", "id_inmueble");

-- ----------------------------
-- Primary Key structure for table recibo_cabecera
-- ----------------------------
ALTER TABLE "public"."recibo_cabecera" ADD CONSTRAINT "recibo_cabecera_pkey" PRIMARY KEY ("id_recibo");

-- ----------------------------
-- Primary Key structure for table recibo_correlativo
-- ----------------------------
ALTER TABLE "public"."recibo_correlativo" ADD CONSTRAINT "recibo_correlativo_pkey" PRIMARY KEY ("id_condominio");

-- ----------------------------
-- Primary Key structure for table recibo_destino_fondos
-- ----------------------------
ALTER TABLE "public"."recibo_destino_fondos" ADD CONSTRAINT "recibo_destino_fondos_pkey" PRIMARY KEY ("id_destino_fondos");

-- ----------------------------
-- Indexes structure for table recibo_origen_fondos
-- ----------------------------
CREATE INDEX "idx_recibo_origen_fondos_fecha" ON "public"."recibo_origen_fondos" USING btree (
  "fecha_creacion" "pg_catalog"."timestamp_ops" ASC NULLS LAST
);
CREATE INDEX "idx_recibo_origen_fondos_id_cuenta" ON "public"."recibo_origen_fondos" USING btree (
  "id_cuenta" "pg_catalog"."int4_ops" ASC NULLS LAST
);

-- ----------------------------
-- Primary Key structure for table recibo_origen_fondos
-- ----------------------------
ALTER TABLE "public"."recibo_origen_fondos" ADD CONSTRAINT "recibo_origen_fondos_pkey" PRIMARY KEY ("id_origen_fondos");

-- ----------------------------
-- Primary Key structure for table rol
-- ----------------------------
ALTER TABLE "public"."rol" ADD CONSTRAINT "rol_pkey" PRIMARY KEY ("id_rol");

-- ----------------------------
-- Foreign Keys structure for table cierre_contable
-- ----------------------------
ALTER TABLE "public"."cierre_contable" ADD CONSTRAINT "cierre_contable_id_condominio_fkey" FOREIGN KEY ("id_condominio") REFERENCES "public"."condominio" ("id_condominio") ON DELETE NO ACTION ON UPDATE NO ACTION;
ALTER TABLE "public"."cierre_contable" ADD CONSTRAINT "cierre_contable_id_moneda_fkey" FOREIGN KEY ("id_moneda") REFERENCES "public"."moneda" ("id_moneda") ON DELETE NO ACTION ON UPDATE NO ACTION;

-- ----------------------------
-- Foreign Keys structure for table condominio
-- ----------------------------
ALTER TABLE "public"."condominio" ADD CONSTRAINT "condominio_id_moneda_base_fkey" FOREIGN KEY ("id_moneda") REFERENCES "public"."moneda" ("id_moneda") ON DELETE RESTRICT ON UPDATE NO ACTION;

-- ----------------------------
-- Foreign Keys structure for table credito_a_favor
-- ----------------------------
ALTER TABLE "public"."credito_a_favor" ADD CONSTRAINT "credito_a_favor_id_moneda_fkey" FOREIGN KEY ("id_moneda") REFERENCES "public"."moneda" ("id_moneda") ON DELETE RESTRICT ON UPDATE NO ACTION;

-- ----------------------------
-- Foreign Keys structure for table cuenta
-- ----------------------------
ALTER TABLE "public"."cuenta" ADD CONSTRAINT "cuenta_id_condominio_fkey" FOREIGN KEY ("id_condominio") REFERENCES "public"."condominio" ("id_condominio") ON DELETE CASCADE ON UPDATE NO ACTION;
ALTER TABLE "public"."cuenta" ADD CONSTRAINT "cuenta_id_moneda_fkey" FOREIGN KEY ("id_moneda") REFERENCES "public"."moneda" ("id_moneda") ON DELETE RESTRICT ON UPDATE NO ACTION;

-- ----------------------------
-- Foreign Keys structure for table distribucion_gasto
-- ----------------------------
ALTER TABLE "public"."distribucion_gasto" ADD CONSTRAINT "distribucion_gasto_id_gasto_fkey" FOREIGN KEY ("id_gasto") REFERENCES "public"."gasto" ("id_gasto") ON DELETE CASCADE ON UPDATE NO ACTION;

-- ----------------------------
-- Foreign Keys structure for table ejecucion_gasto
-- ----------------------------
ALTER TABLE "public"."ejecucion_gasto" ADD CONSTRAINT "ejecucion_gasto_id_condominio_fkey" FOREIGN KEY ("id_condominio") REFERENCES "public"."condominio" ("id_condominio") ON DELETE CASCADE ON UPDATE NO ACTION;

-- ----------------------------
-- Foreign Keys structure for table esquema_pronto_pago
-- ----------------------------
ALTER TABLE "public"."esquema_pronto_pago" ADD CONSTRAINT "esquema_pronto_pago_id_condominio_fkey" FOREIGN KEY ("id_condominio") REFERENCES "public"."condominio" ("id_condominio") ON DELETE CASCADE ON UPDATE NO ACTION;

-- ----------------------------
-- Foreign Keys structure for table gasto
-- ----------------------------
ALTER TABLE "public"."gasto" ADD CONSTRAINT "gasto_id_condominio_fkey" FOREIGN KEY ("id_condominio") REFERENCES "public"."condominio" ("id_condominio") ON DELETE CASCADE ON UPDATE NO ACTION;
ALTER TABLE "public"."gasto" ADD CONSTRAINT "gasto_id_moneda_fkey" FOREIGN KEY ("id_moneda") REFERENCES "public"."moneda" ("id_moneda") ON DELETE RESTRICT ON UPDATE NO ACTION;

-- ----------------------------
-- Foreign Keys structure for table inmueble
-- ----------------------------
ALTER TABLE "public"."inmueble" ADD CONSTRAINT "inmueble_id_condominio_fkey" FOREIGN KEY ("id_condominio") REFERENCES "public"."condominio" ("id_condominio") ON DELETE CASCADE ON UPDATE NO ACTION;

-- ----------------------------
-- Foreign Keys structure for table log_distribucion
-- ----------------------------
ALTER TABLE "public"."log_distribucion" ADD CONSTRAINT "fk_notificacion_master_log" FOREIGN KEY ("id_notificacion_master") REFERENCES "public"."notificacion_cobro_master" ("id_notificacion_master") ON DELETE CASCADE ON UPDATE NO ACTION;

-- ----------------------------
-- Foreign Keys structure for table movimiento_detalle_egreso
-- ----------------------------
ALTER TABLE "public"."movimiento_detalle_egreso" ADD CONSTRAINT "movimiento_detalle_egreso_id_cuenta_fkey" FOREIGN KEY ("id_cuenta") REFERENCES "public"."cuenta" ("id_cuenta") ON DELETE NO ACTION ON UPDATE NO ACTION;
ALTER TABLE "public"."movimiento_detalle_egreso" ADD CONSTRAINT "movimiento_detalle_egreso_id_moneda_fkey" FOREIGN KEY ("id_moneda") REFERENCES "public"."moneda" ("id_moneda") ON DELETE NO ACTION ON UPDATE NO ACTION;
ALTER TABLE "public"."movimiento_detalle_egreso" ADD CONSTRAINT "movimiento_detalle_egreso_id_movimiento_general_fkey" FOREIGN KEY ("id_movimiento_general") REFERENCES "public"."movimiento_general" ("id_movimiento") ON DELETE NO ACTION ON UPDATE NO ACTION;
ALTER TABLE "public"."movimiento_detalle_egreso" ADD CONSTRAINT "movimiento_detalle_egreso_id_plan_cuenta_fkey" FOREIGN KEY ("id_plan_cuenta") REFERENCES "public"."plan_cuenta" ("id_plan") ON DELETE NO ACTION ON UPDATE NO ACTION;

-- ----------------------------
-- Foreign Keys structure for table movimiento_detalle_ingreso
-- ----------------------------
ALTER TABLE "public"."movimiento_detalle_ingreso" ADD CONSTRAINT "movimiento_detalle_ingreso_id_cuenta_fkey" FOREIGN KEY ("id_cuenta") REFERENCES "public"."cuenta" ("id_cuenta") ON DELETE NO ACTION ON UPDATE NO ACTION;
ALTER TABLE "public"."movimiento_detalle_ingreso" ADD CONSTRAINT "movimiento_detalle_ingreso_id_moneda_fkey" FOREIGN KEY ("id_moneda") REFERENCES "public"."moneda" ("id_moneda") ON DELETE NO ACTION ON UPDATE NO ACTION;
ALTER TABLE "public"."movimiento_detalle_ingreso" ADD CONSTRAINT "movimiento_detalle_ingreso_id_movimiento_general_fkey" FOREIGN KEY ("id_movimiento_general") REFERENCES "public"."movimiento_general" ("id_movimiento") ON DELETE NO ACTION ON UPDATE NO ACTION;
ALTER TABLE "public"."movimiento_detalle_ingreso" ADD CONSTRAINT "movimiento_detalle_ingreso_id_plan_cuenta_fkey" FOREIGN KEY ("id_plan_cuenta") REFERENCES "public"."plan_cuenta" ("id_plan") ON DELETE NO ACTION ON UPDATE NO ACTION;

-- ----------------------------
-- Foreign Keys structure for table movimiento_general
-- ----------------------------
ALTER TABLE "public"."movimiento_general" ADD CONSTRAINT "movimiento_general_id_condominio_fkey" FOREIGN KEY ("id_condominio") REFERENCES "public"."condominio" ("id_condominio") ON DELETE NO ACTION ON UPDATE NO ACTION;
ALTER TABLE "public"."movimiento_general" ADD CONSTRAINT "movimiento_general_id_cuenta_fkey" FOREIGN KEY ("id_cuenta") REFERENCES "public"."cuenta" ("id_cuenta") ON DELETE NO ACTION ON UPDATE NO ACTION;
ALTER TABLE "public"."movimiento_general" ADD CONSTRAINT "movimiento_general_id_moneda_fkey" FOREIGN KEY ("id_moneda") REFERENCES "public"."moneda" ("id_moneda") ON DELETE NO ACTION ON UPDATE NO ACTION;

-- ----------------------------
-- Foreign Keys structure for table notificacion_cobro
-- ----------------------------
ALTER TABLE "public"."notificacion_cobro" ADD CONSTRAINT "fk_condominio" FOREIGN KEY ("id_condominio") REFERENCES "public"."condominio" ("id_condominio") ON DELETE RESTRICT ON UPDATE NO ACTION;
ALTER TABLE "public"."notificacion_cobro" ADD CONSTRAINT "fk_moneda" FOREIGN KEY ("id_moneda") REFERENCES "public"."moneda" ("id_moneda") ON DELETE RESTRICT ON UPDATE NO ACTION;
ALTER TABLE "public"."notificacion_cobro" ADD CONSTRAINT "fk_notif_cobro_master" FOREIGN KEY ("id_notificacion_master") REFERENCES "public"."notificacion_cobro_master" ("id_notificacion_master") ON DELETE SET NULL ON UPDATE NO ACTION DEFERRABLE INITIALLY DEFERRED;

-- ----------------------------
-- Foreign Keys structure for table notificacion_cobro_detalle
-- ----------------------------
ALTER TABLE "public"."notificacion_cobro_detalle" ADD CONSTRAINT "fk_detalle_notif_particular" FOREIGN KEY ("id_notificacion") REFERENCES "public"."notificacion_cobro" ("id_notificacion") ON DELETE CASCADE ON UPDATE NO ACTION DEFERRABLE INITIALLY DEFERRED;

-- ----------------------------
-- Foreign Keys structure for table notificaciones_cobro
-- ----------------------------
ALTER TABLE "public"."notificaciones_cobro" ADD CONSTRAINT "fk_inmueble" FOREIGN KEY ("id_inmueble") REFERENCES "public"."inmueble" ("id_inmueble") ON DELETE RESTRICT ON UPDATE NO ACTION;
ALTER TABLE "public"."notificaciones_cobro" ADD CONSTRAINT "fk_notificacion_master" FOREIGN KEY ("id_notificacion_master") REFERENCES "public"."notificacion_cobro_master" ("id_notificacion_master") ON DELETE CASCADE ON UPDATE NO ACTION;

-- ----------------------------
-- Foreign Keys structure for table propietario_inmueble
-- ----------------------------
ALTER TABLE "public"."propietario_inmueble" ADD CONSTRAINT "propietario_inmueble_id_inmueble_fkey" FOREIGN KEY ("id_inmueble") REFERENCES "public"."inmueble" ("id_inmueble") ON DELETE NO ACTION ON UPDATE NO ACTION;
ALTER TABLE "public"."propietario_inmueble" ADD CONSTRAINT "propietario_inmueble_id_propietario_fkey" FOREIGN KEY ("id_propietario") REFERENCES "public"."propietario" ("id_propietario") ON DELETE NO ACTION ON UPDATE NO ACTION;
ALTER TABLE "public"."propietario_inmueble" ADD CONSTRAINT "propietario_inmueble_id_usuario_fkey" FOREIGN KEY ("id_usuario") REFERENCES "menu_login"."usuario" ("id_usuario") ON DELETE NO ACTION ON UPDATE NO ACTION;
