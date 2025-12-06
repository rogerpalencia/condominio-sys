/*
 Navicat Premium Data Transfer

 Source Server         : bunkermatic
 Source Server Type    : PostgreSQL
 Source Server Version : 100023 (100023)
 Source Host           : 162.216.113.82:5432
 Source Catalog        : rhodium_txcondominio
 Source Schema         : menu_login

 Target Server Type    : PostgreSQL
 Target Server Version : 100023 (100023)
 File Encoding         : 65001

 Date: 28/08/2025 20:03:37
*/


-- ----------------------------
-- Type structure for estado_movimiento
-- ----------------------------
DROP TYPE IF EXISTS "menu_login"."estado_movimiento";
CREATE TYPE "menu_login"."estado_movimiento" AS ENUM (
  'pendiente',
  'conciliado',
  'cancelado',
  'cerrado',
  'abierto'
);
ALTER TYPE "menu_login"."estado_movimiento" OWNER TO "rhodium_roger";

-- ----------------------------
-- Sequence structure for cierre_contable_id_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "menu_login"."cierre_contable_id_seq";
CREATE SEQUENCE "menu_login"."cierre_contable_id_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 9223372036854775807
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for comprobante_egreso_detalle_id_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "menu_login"."comprobante_egreso_detalle_id_seq";
CREATE SEQUENCE "menu_login"."comprobante_egreso_detalle_id_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 9223372036854775807
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for comprobante_egreso_id_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "menu_login"."comprobante_egreso_id_seq";
CREATE SEQUENCE "menu_login"."comprobante_egreso_id_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 9223372036854775807
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for programas_id_programa_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "menu_login"."programas_id_programa_seq";
CREATE SEQUENCE "menu_login"."programas_id_programa_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 2147483647
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for programas_rol_id_programa_rol_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "menu_login"."programas_rol_id_programa_rol_seq";
CREATE SEQUENCE "menu_login"."programas_rol_id_programa_rol_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 2147483647
START 1
CACHE 1;

-- ----------------------------
-- Table structure for modulos
-- ----------------------------
DROP TABLE IF EXISTS "menu_login"."modulos";
CREATE TABLE "menu_login"."modulos" (
  "id_modulo" int4 NOT NULL,
  "nombre" varchar(64) COLLATE "pg_catalog"."default",
  "orden" int2 DEFAULT 0,
  "icono" varchar(132) COLLATE "pg_catalog"."default"
)
;

-- ----------------------------
-- Records of modulos
-- ----------------------------
INSERT INTO "menu_login"."modulos" VALUES (1, 'Registros', 1, 'fas fa-industry');
INSERT INTO "menu_login"."modulos" VALUES (2, 'Actividad Económica', 2, 'fas fa-industry');

-- ----------------------------
-- Table structure for plan_cuenta
-- ----------------------------
DROP TABLE IF EXISTS "menu_login"."plan_cuenta";
CREATE TABLE "menu_login"."plan_cuenta" (
  "id_plan_cuenta" int4 NOT NULL,
  "nombre" text COLLATE "pg_catalog"."default" NOT NULL
)
;

-- ----------------------------
-- Records of plan_cuenta
-- ----------------------------

-- ----------------------------
-- Table structure for programas
-- ----------------------------
DROP TABLE IF EXISTS "menu_login"."programas";
CREATE TABLE "menu_login"."programas" (
  "agregado" timestamp(6) DEFAULT now(),
  "modificado" timestamp(6) DEFAULT now(),
  "id_programa" int4 NOT NULL DEFAULT nextval('"menu_login".programas_id_programa_seq'::regclass),
  "nombre" varchar(64) COLLATE "pg_catalog"."default" NOT NULL,
  "accion" varchar(128) COLLATE "pg_catalog"."default" NOT NULL,
  "orden" int2 DEFAULT 0,
  "id_modulo" int2,
  "grupo" int2
)
;

-- ----------------------------
-- Records of programas
-- ----------------------------
INSERT INTO "menu_login"."programas" VALUES ('2025-03-30 17:41:43.481778', '2025-03-30 17:41:43.481778', 4, 'Conciliacion de Pagos', 'conciliacion_pagos.php', 3, 1, 1);
INSERT INTO "menu_login"."programas" VALUES ('2025-03-30 17:41:43.481778', '2025-03-30 17:41:43.481778', 3, 'Notificaciones Mestras', 'notif_master.php', 3, 1, 1);
INSERT INTO "menu_login"."programas" VALUES ('2025-03-30 17:41:43.481778', '2025-03-30 17:41:43.481778', 2, 'Inmuebles', 'master_inmuebles.php', 2, 1, 1);

-- ----------------------------
-- Table structure for programas_rol
-- ----------------------------
DROP TABLE IF EXISTS "menu_login"."programas_rol";
CREATE TABLE "menu_login"."programas_rol" (
  "id_programa_rol" int4 NOT NULL DEFAULT nextval('"menu_login".programas_rol_id_programa_rol_seq'::regclass),
  "id_programa" int4 NOT NULL,
  "id_rol" int4 NOT NULL,
  "fecha_asignacion" timestamp(6) DEFAULT CURRENT_TIMESTAMP,
  "estado" bool DEFAULT true
)
;

-- ----------------------------
-- Records of programas_rol
-- ----------------------------
INSERT INTO "menu_login"."programas_rol" VALUES (3, 2, 2, '2025-03-30 18:00:25.242893', 't');
INSERT INTO "menu_login"."programas_rol" VALUES (4, 3, 2, '2025-04-23 20:16:07.896779', 't');
INSERT INTO "menu_login"."programas_rol" VALUES (2, 4, 2, '2025-04-23 20:16:07.896779', 't');

-- ----------------------------
-- Table structure for sesiones
-- ----------------------------
DROP TABLE IF EXISTS "menu_login"."sesiones";
CREATE TABLE "menu_login"."sesiones" (
  "idsesion" int4 NOT NULL,
  "idusuario" int4 NOT NULL,
  "fecha" timestamp(6) NOT NULL,
  "token" text COLLATE "pg_catalog"."default"
)
;

-- ----------------------------
-- Records of sesiones
-- ----------------------------

-- ----------------------------
-- Table structure for tokens
-- ----------------------------
DROP TABLE IF EXISTS "menu_login"."tokens";
CREATE TABLE "menu_login"."tokens" (
  "user_id" int4 NOT NULL,
  "token" varchar(100) COLLATE "pg_catalog"."default" NOT NULL,
  "expires_at" timestamp(6) NOT NULL
)
;

-- ----------------------------
-- Records of tokens
-- ----------------------------
INSERT INTO "menu_login"."tokens" VALUES (1, '52cae1e453391a3892de67889a0938cb7de944c14514a145ba6cdc94c6765217bcb6066d230a4350011b2b05e1ba53b3c2c8', '2025-08-29 00:35:48');
INSERT INTO "menu_login"."tokens" VALUES (2, '6529a84de05d91d6af726d53723425cfc5c02ba77fa61944815da39990cc772df93de18534b33b0c23bb2d3fb5a8bae2dac3', '2025-08-29 00:48:32');

-- ----------------------------
-- Table structure for usuario
-- ----------------------------
DROP TABLE IF EXISTS "menu_login"."usuario";
CREATE TABLE "menu_login"."usuario" (
  "id_usuario" int4 NOT NULL,
  "correo" varchar(255) COLLATE "pg_catalog"."default" NOT NULL,
  "contrasena" varchar(255) COLLATE "pg_catalog"."default" NOT NULL,
  "nombre" varchar(255) COLLATE "pg_catalog"."default" NOT NULL,
  "apellido" varchar(255) COLLATE "pg_catalog"."default" NOT NULL,
  "fecha_nacimiento" date,
  "estado" bool DEFAULT true,
  "fecha_creacion" timestamp(6) DEFAULT CURRENT_TIMESTAMP,
  "fecha_actualizacion" timestamp(6) DEFAULT CURRENT_TIMESTAMP,
  "telefono" varchar(20) COLLATE "pg_catalog"."default",
  "ultimo_login" timestamp(6)
)
;

-- ----------------------------
-- Records of usuario
-- ----------------------------
INSERT INTO "menu_login"."usuario" VALUES (2, 'rogerpalencia@gmail.com', '7696f7cf58b7ad8f705717775916c420555d8f1a', 'Roger', 'Palencia', '1985-01-01', 't', '2025-03-30 17:14:15.796698', '2025-03-30 17:14:15.796698', NULL, NULL);
INSERT INTO "menu_login"."usuario" VALUES (1, 'fxkevinricardopalencia@gmail.com', '7696f7cf58b7ad8f705717775916c420555d8f1a', 'Kevin', 'Palencia', '1985-01-01', 't', '2025-03-30 17:14:15.796698', '2025-03-30 17:14:15.796698', NULL, NULL);

-- ----------------------------
-- Table structure for usuario_rol
-- ----------------------------
DROP TABLE IF EXISTS "menu_login"."usuario_rol";
CREATE TABLE "menu_login"."usuario_rol" (
  "id_usuario_rol" int4 NOT NULL,
  "id_usuario" int4 NOT NULL,
  "id_rol" int4 NOT NULL,
  "id_condominio" int4 NOT NULL,
  "fecha_asignacion" timestamp(6) DEFAULT CURRENT_TIMESTAMP,
  "fecha_desasignacion" timestamp(6)
)
;

-- ----------------------------
-- Records of usuario_rol
-- ----------------------------
INSERT INTO "menu_login"."usuario_rol" VALUES (1, 2, 2, 1, '2025-03-30 17:50:01.303133', NULL);
INSERT INTO "menu_login"."usuario_rol" VALUES (2, 1, 2, 7, '2025-08-28 19:37:24.636726', NULL);
INSERT INTO "menu_login"."usuario_rol" VALUES (3, 2, 2, 7, '2025-08-28 19:48:23.5713', NULL);

-- ----------------------------
-- Function structure for actualizar_saldo_cuenta
-- ----------------------------
DROP FUNCTION IF EXISTS "menu_login"."actualizar_saldo_cuenta"();
CREATE OR REPLACE FUNCTION "menu_login"."actualizar_saldo_cuenta"()
  RETURNS "pg_catalog"."trigger" AS $BODY$
DECLARE
    v_saldo_actual NUMERIC(15,2);
BEGIN
    IF TG_OP = 'INSERT' THEN
        IF NEW.estado = 'conciliado' THEN
            UPDATE cuenta
            SET saldo_actual = CASE 
                WHEN NEW.tipo_movimiento = 'ingreso' THEN saldo_actual + NEW.monto_base
                WHEN NEW.tipo_movimiento = 'egreso' THEN saldo_actual - NEW.monto_base
            END
            WHERE id_cuenta = NEW.id_cuenta
            RETURNING saldo_actual INTO v_saldo_actual;
        END IF;
    ELSIF TG_OP = 'UPDATE' THEN
        IF OLD.estado != NEW.estado OR OLD.monto_base != NEW.monto_base OR OLD.id_cuenta != NEW.id_cuenta THEN
            -- Reversa del estado anterior si estaba conciliado
            IF OLD.estado = 'conciliado' THEN
                UPDATE cuenta
                SET saldo_actual = CASE 
                    WHEN OLD.tipo_movimiento = 'ingreso' THEN saldo_actual - OLD.monto_base
                    WHEN OLD.tipo_movimiento = 'egreso' THEN saldo_actual + OLD.monto_base
                END
                WHERE id_cuenta = OLD.id_cuenta;
            END IF;
            -- Aplicar nuevo estado si es conciliado (incluyendo cambio de cuenta)
            IF NEW.estado = 'conciliado' THEN
                UPDATE cuenta
                SET saldo_actual = CASE 
                    WHEN NEW.tipo_movimiento = 'ingreso' THEN saldo_actual + NEW.monto_base
                    WHEN NEW.tipo_movimiento = 'egreso' THEN saldo_actual - NEW.monto_base
                END
                WHERE id_cuenta = NEW.id_cuenta
                RETURNING saldo_actual INTO v_saldo_actual;
            END IF;
        END IF;
    END IF;
    IF v_saldo_actual IS NULL AND NEW.estado = 'conciliado' THEN
        RAISE EXCEPTION 'Cuenta no encontrada para id_cuenta = %', NEW.id_cuenta;
    END IF;
    RETURN NEW;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;

-- ----------------------------
-- Function structure for calcular_monto_base_detalle
-- ----------------------------
DROP FUNCTION IF EXISTS "menu_login"."calcular_monto_base_detalle"();
CREATE OR REPLACE FUNCTION "menu_login"."calcular_monto_base_detalle"()
  RETURNS "pg_catalog"."trigger" AS $BODY$
BEGIN
    NEW.monto_base = NEW.monto * NEW.tasa;
    RETURN NEW;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;

-- ----------------------------
-- Function structure for calcular_monto_base_egreso
-- ----------------------------
DROP FUNCTION IF EXISTS "menu_login"."calcular_monto_base_egreso"();
CREATE OR REPLACE FUNCTION "menu_login"."calcular_monto_base_egreso"()
  RETURNS "pg_catalog"."trigger" AS $BODY$
BEGIN
    NEW.monto_base = NEW.monto_total * NEW.tasa;
    RETURN NEW;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;

-- ----------------------------
-- Function structure for set_monto_base
-- ----------------------------
DROP FUNCTION IF EXISTS "menu_login"."set_monto_base"();
CREATE OR REPLACE FUNCTION "menu_login"."set_monto_base"()
  RETURNS "pg_catalog"."trigger" AS $BODY$
BEGIN
  NEW.monto_base := ROUND(NEW.monto_total * NEW.tasa, 2);        -- cabecera
  RETURN NEW;
EXCEPTION WHEN undefined_column THEN
  -- Estamos en el detalle: usa NEW.monto
  NEW.monto_base := ROUND(NEW.monto * NEW.tasa, 2);
  RETURN NEW;
END$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;

-- ----------------------------
-- Function structure for set_monto_base_cd
-- ----------------------------
DROP FUNCTION IF EXISTS "menu_login"."set_monto_base_cd"();
CREATE OR REPLACE FUNCTION "menu_login"."set_monto_base_cd"()
  RETURNS "pg_catalog"."trigger" AS $BODY$
BEGIN
    NEW.monto_base := ROUND(NEW.monto * NEW.tasa, 2);
    RETURN NEW;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;

-- ----------------------------
-- Function structure for set_monto_base_ce
-- ----------------------------
DROP FUNCTION IF EXISTS "menu_login"."set_monto_base_ce"();
CREATE OR REPLACE FUNCTION "menu_login"."set_monto_base_ce"()
  RETURNS "pg_catalog"."trigger" AS $BODY$
BEGIN
    NEW.monto_base := ROUND(NEW.monto_total * NEW.tasa, 2);
    RETURN NEW;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;

-- ----------------------------
-- Function structure for update_timestamp
-- ----------------------------
DROP FUNCTION IF EXISTS "menu_login"."update_timestamp"();
CREATE OR REPLACE FUNCTION "menu_login"."update_timestamp"()
  RETURNS "pg_catalog"."trigger" AS $BODY$
BEGIN
    NEW.fecha_actualizacion := CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;

-- ----------------------------
-- Function structure for validate_moneda_detalle
-- ----------------------------
DROP FUNCTION IF EXISTS "menu_login"."validate_moneda_detalle"();
CREATE OR REPLACE FUNCTION "menu_login"."validate_moneda_detalle"()
  RETURNS "pg_catalog"."trigger" AS $BODY$
DECLARE
    moneda_padre INT4;
BEGIN
    SELECT id_moneda INTO moneda_padre
    FROM menu_login.comprobante_egreso
    WHERE id_comprobante = NEW.id_comprobante;

    IF NEW.id_moneda != moneda_padre THEN
        RAISE EXCEPTION 'La moneda del detalle (%), no coincide con la cabecera (%)',
            NEW.id_moneda, moneda_padre;
    END IF;

    RETURN NEW;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
SELECT setval('"menu_login"."cierre_contable_id_seq"', 1, false);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
SELECT setval('"menu_login"."comprobante_egreso_detalle_id_seq"', 1, false);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
SELECT setval('"menu_login"."comprobante_egreso_id_seq"', 1, false);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "menu_login"."programas_id_programa_seq"
OWNED BY "menu_login"."programas"."id_programa";
SELECT setval('"menu_login"."programas_id_programa_seq"', 1, true);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "menu_login"."programas_rol_id_programa_rol_seq"
OWNED BY "menu_login"."programas_rol"."id_programa_rol";
SELECT setval('"menu_login"."programas_rol_id_programa_rol_seq"', 4, true);

-- ----------------------------
-- Primary Key structure for table modulos
-- ----------------------------
ALTER TABLE "menu_login"."modulos" ADD CONSTRAINT "sistemas_pkey" PRIMARY KEY ("id_modulo");

-- ----------------------------
-- Primary Key structure for table plan_cuenta
-- ----------------------------
ALTER TABLE "menu_login"."plan_cuenta" ADD CONSTRAINT "plan_cuenta_pkey" PRIMARY KEY ("id_plan_cuenta");

-- ----------------------------
-- Primary Key structure for table programas
-- ----------------------------
ALTER TABLE "menu_login"."programas" ADD CONSTRAINT "programas_pkey" PRIMARY KEY ("id_programa");

-- ----------------------------
-- Indexes structure for table programas_rol
-- ----------------------------
CREATE INDEX "idx_programas_rol_programa" ON "menu_login"."programas_rol" USING btree (
  "id_programa" "pg_catalog"."int4_ops" ASC NULLS LAST
);
CREATE INDEX "idx_programas_rol_rol" ON "menu_login"."programas_rol" USING btree (
  "id_rol" "pg_catalog"."int4_ops" ASC NULLS LAST
);

-- ----------------------------
-- Primary Key structure for table programas_rol
-- ----------------------------
ALTER TABLE "menu_login"."programas_rol" ADD CONSTRAINT "programas_rol_pkey" PRIMARY KEY ("id_programa_rol");

-- ----------------------------
-- Primary Key structure for table tokens
-- ----------------------------
ALTER TABLE "menu_login"."tokens" ADD CONSTRAINT "tokens_pkey" PRIMARY KEY ("user_id");

-- ----------------------------
-- Uniques structure for table usuario
-- ----------------------------
ALTER TABLE "menu_login"."usuario" ADD CONSTRAINT "usuario_correo_key" UNIQUE ("correo");

-- ----------------------------
-- Primary Key structure for table usuario
-- ----------------------------
ALTER TABLE "menu_login"."usuario" ADD CONSTRAINT "usuario_pkey" PRIMARY KEY ("id_usuario");

-- ----------------------------
-- Primary Key structure for table usuario_rol
-- ----------------------------
ALTER TABLE "menu_login"."usuario_rol" ADD CONSTRAINT "usuario_rol_pkey" PRIMARY KEY ("id_usuario_rol");

-- ----------------------------
-- Foreign Keys structure for table programas_rol
-- ----------------------------
ALTER TABLE "menu_login"."programas_rol" ADD CONSTRAINT "programas_rol_id_programa_fkey" FOREIGN KEY ("id_programa") REFERENCES "menu_login"."programas" ("id_programa") ON DELETE CASCADE ON UPDATE NO ACTION;
