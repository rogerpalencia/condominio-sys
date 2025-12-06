/*
 Navicat Premium Data Transfer

 Source Server         : bunkermatic
 Source Server Type    : PostgreSQL
 Source Server Version : 100023 (100023)
 Source Host           : 162.216.113.82:5432
 Source Catalog        : rhodium_txcondominio
 Source Schema         : email

 Target Server Type    : PostgreSQL
 Target Server Version : 100023 (100023)
 File Encoding         : 65001

 Date: 20/09/2025 19:23:10
*/


-- ----------------------------
-- Type structure for estado
-- ----------------------------
DROP TYPE IF EXISTS "email"."estado";
CREATE TYPE "email"."estado" AS ENUM (
  'en_cola',
  'enviando',
  'enviado',
  'fallido',
  'abierto',
  'clic',
  'rebotado',
  'aplazado',
  'cancelado'
);
ALTER TYPE "email"."estado" OWNER TO "postgres";

-- ----------------------------
-- Type structure for tipo_evento
-- ----------------------------
DROP TYPE IF EXISTS "email"."tipo_evento";
CREATE TYPE "email"."tipo_evento" AS ENUM (
  'en_cola',
  'enviando',
  'enviado',
  'abierto',
  'clic',
  'rebotado',
  'aplazado',
  'reintento',
  'error',
  'cancelado'
);
ALTER TYPE "email"."tipo_evento" OWNER TO "postgres";

-- ----------------------------
-- Sequence structure for cola_id_email_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "email"."cola_id_email_seq";
CREATE SEQUENCE "email"."cola_id_email_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 9223372036854775807
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for config_id_email_config_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "email"."config_id_email_config_seq";
CREATE SEQUENCE "email"."config_id_email_config_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 9223372036854775807
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for evento_id_evento_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "email"."evento_id_evento_seq";
CREATE SEQUENCE "email"."evento_id_evento_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 9223372036854775807
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for plantilla_id_plantilla_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "email"."plantilla_id_plantilla_seq";
CREATE SEQUENCE "email"."plantilla_id_plantilla_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 9223372036854775807
START 1
CACHE 1;

-- ----------------------------
-- Table structure for cola
-- ----------------------------
DROP TABLE IF EXISTS "email"."cola";
CREATE TABLE "email"."cola" (
  "id_email" int8 NOT NULL DEFAULT nextval('"email".cola_id_email_seq'::regclass),
  "id_condominio" int8 NOT NULL,
  "id_email_config" int8 NOT NULL,
  "id_plantilla" int8,
  "para_email" text COLLATE "pg_catalog"."default" NOT NULL,
  "para_nombre" text COLLATE "pg_catalog"."default",
  "id_propietario" int8,
  "id_usuario" int4,
  "asunto" text COLLATE "pg_catalog"."default" NOT NULL,
  "cuerpo_html" text COLLATE "pg_catalog"."default" NOT NULL,
  "cuerpo_texto" text COLLATE "pg_catalog"."default",
  "headers_json" jsonb,
  "adjuntos_json" jsonb,
  "tracking_token" uuid NOT NULL DEFAULT (md5(((random())::text || (clock_timestamp())::text)))::uuid,
  "link_token" uuid NOT NULL DEFAULT (md5(((random())::text || (clock_timestamp())::text)))::uuid,
  "link_target" text COLLATE "pg_catalog"."default",
  "link_payload" jsonb,
  "link_expira_at" timestamp(6),
  "estado" "email"."estado" NOT NULL DEFAULT 'en_cola'::email.estado,
  "intentos" int2 NOT NULL DEFAULT 0,
  "ultimo_error" text COLLATE "pg_catalog"."default",
  "message_id" text COLLATE "pg_catalog"."default",
  "enviado_en" timestamp(6),
  "abierto_en" timestamp(6),
  "clic_en" timestamp(6),
  "created_at" timestamp(6) NOT NULL DEFAULT now(),
  "target_tipo" text COLLATE "pg_catalog"."default",
  "target_id" int8
)
;

-- ----------------------------
-- Records of cola
-- ----------------------------

-- ----------------------------
-- Table structure for config
-- ----------------------------
DROP TABLE IF EXISTS "email"."config";
CREATE TABLE "email"."config" (
  "id_email_config" int8 NOT NULL DEFAULT nextval('"email".config_id_email_config_seq'::regclass),
  "id_condominio" int8 NOT NULL,
  "host" text COLLATE "pg_catalog"."default" NOT NULL,
  "puerto" int4 NOT NULL,
  "seguridad" text COLLATE "pg_catalog"."default" NOT NULL,
  "usuario" text COLLATE "pg_catalog"."default" NOT NULL,
  "contrasena_enc" text COLLATE "pg_catalog"."default" NOT NULL,
  "from_email" text COLLATE "pg_catalog"."default" NOT NULL,
  "from_name" text COLLATE "pg_catalog"."default" NOT NULL,
  "reply_to_email" text COLLATE "pg_catalog"."default",
  "reply_to_name" text COLLATE "pg_catalog"."default",
  "rate_limit_por_min" int2 NOT NULL DEFAULT 30,
  "activo" bool NOT NULL DEFAULT true,
  "created_at" timestamp(6) NOT NULL DEFAULT now(),
  "updated_at" timestamp(6) NOT NULL DEFAULT now()
)
;

-- ----------------------------
-- Records of config
-- ----------------------------
INSERT INTO "email"."config" VALUES (1, 7, 'mail.rhodiumdev.com', 465, 'ssl', 'condominios@rhodiumdev.com', 'RnI5Njg5NDY2Kio=', 'condominios@rhodiumdev.com', 'condominios@rhodiumdev.com', NULL, NULL, 30, 't', '2025-09-07 18:11:50.567793', '2025-09-08 20:57:09.613611');
INSERT INTO "email"."config" VALUES (2, 5, 'mail.rhodiumdev.com', 465, 'ssl', 'condominios@rhodiumdev.com', 'RnI5Njg5NDY2Kio=', 'condominios@rhodiumdev.com', 'condominios@rhodiumdev.com', NULL, NULL, 30, 't', '2025-09-07 18:11:50.567793', '2025-09-08 20:57:09.613611');

-- ----------------------------
-- Table structure for evento
-- ----------------------------
DROP TABLE IF EXISTS "email"."evento";
CREATE TABLE "email"."evento" (
  "id_evento" int8 NOT NULL DEFAULT nextval('"email".evento_id_evento_seq'::regclass),
  "id_email" int8 NOT NULL,
  "tipo" "email"."tipo_evento" NOT NULL,
  "ts" timestamp(6) NOT NULL DEFAULT now(),
  "meta_json" jsonb
)
;

-- ----------------------------
-- Records of evento
-- ----------------------------

-- ----------------------------
-- Table structure for plantilla
-- ----------------------------
DROP TABLE IF EXISTS "email"."plantilla";
CREATE TABLE "email"."plantilla" (
  "id_plantilla" int8 NOT NULL DEFAULT nextval('"email".plantilla_id_plantilla_seq'::regclass),
  "id_condominio" int8,
  "clave" text COLLATE "pg_catalog"."default" NOT NULL,
  "asunto" text COLLATE "pg_catalog"."default" NOT NULL,
  "cuerpo_html" text COLLATE "pg_catalog"."default" NOT NULL,
  "cuerpo_texto" text COLLATE "pg_catalog"."default",
  "activo" bool NOT NULL DEFAULT true,
  "created_at" timestamp(6) NOT NULL DEFAULT now(),
  "updated_at" timestamp(6) NOT NULL DEFAULT now()
)
;

-- ----------------------------
-- Records of plantilla
-- ----------------------------

-- ----------------------------
-- Function structure for tg_set_updated_at
-- ----------------------------
DROP FUNCTION IF EXISTS "email"."tg_set_updated_at"();
CREATE OR REPLACE FUNCTION "email"."tg_set_updated_at"()
  RETURNS "pg_catalog"."trigger" AS $BODY$
BEGIN
  NEW.updated_at := NOW();
  RETURN NEW;
END $BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;

-- ----------------------------
-- View structure for v_envios_por_recibo
-- ----------------------------
DROP VIEW IF EXISTS "email"."v_envios_por_recibo";
CREATE VIEW "email"."v_envios_por_recibo" AS  WITH base AS (
         SELECT (cola.link_payload ->> 'id_recibo'::text)::bigint AS id_recibo,
            cola.estado,
            cola.enviado_en,
            cola.abierto_en,
            cola.clic_en,
            cola.created_at
           FROM email.cola
          WHERE COALESCE(cola.link_payload ->> 'tipo'::text, ''::text) = 'recibo_cabecera'::text AND cola.link_payload ? 'id_recibo'::text
        ), cnt AS (
         SELECT base.id_recibo,
            count(*) AS cant_intentos,
            count(base.enviado_en) AS cant_enviados,
            count(base.abierto_en) AS cant_abiertos,
            count(base.clic_en) AS cant_clics
           FROM base
          GROUP BY base.id_recibo
        ), last AS (
         SELECT DISTINCT ON (base.id_recibo) base.id_recibo,
            base.estado AS ultimo_estado,
            COALESCE(base.enviado_en, base.created_at) AS ultima_fecha
           FROM base
          ORDER BY base.id_recibo, (COALESCE(base.enviado_en, base.created_at)) DESC
        )
 SELECT c.id_recibo,
    c.cant_intentos,
    c.cant_enviados,
    c.cant_abiertos,
    c.cant_clics,
    l.ultimo_estado,
    l.ultima_fecha
   FROM cnt c
     LEFT JOIN last l USING (id_recibo);

-- ----------------------------
-- View structure for v_cola_resumen
-- ----------------------------
DROP VIEW IF EXISTS "email"."v_cola_resumen";
CREATE VIEW "email"."v_cola_resumen" AS  SELECT cola.id_condominio,
    cola.estado,
    count(*) AS total
   FROM email.cola
  GROUP BY cola.id_condominio, cola.estado;

-- ----------------------------
-- View structure for v_envios_por_master
-- ----------------------------
DROP VIEW IF EXISTS "email"."v_envios_por_master";
CREATE VIEW "email"."v_envios_por_master" AS  WITH base AS (
         SELECT (cola.link_payload ->> 'id_notificacion_master'::text)::bigint AS id_notificacion_master,
            cola.estado,
            cola.enviado_en,
            cola.abierto_en,
            cola.clic_en,
            cola.created_at
           FROM email.cola
          WHERE COALESCE(cola.link_payload ->> 'tipo'::text, ''::text) = 'notificacion_cobro_master'::text AND cola.link_payload ? 'id_notificacion_master'::text
        ), cnt AS (
         SELECT base.id_notificacion_master,
            count(*) AS cant_intentos,
            count(base.enviado_en) AS cant_enviados,
            count(base.abierto_en) AS cant_abiertos,
            count(base.clic_en) AS cant_clics
           FROM base
          GROUP BY base.id_notificacion_master
        ), last AS (
         SELECT DISTINCT ON (base.id_notificacion_master) base.id_notificacion_master,
            base.estado AS ultimo_estado,
            COALESCE(base.enviado_en, base.created_at) AS ultima_fecha
           FROM base
          ORDER BY base.id_notificacion_master, (COALESCE(base.enviado_en, base.created_at)) DESC
        )
 SELECT c.id_notificacion_master,
    c.cant_intentos,
    c.cant_enviados,
    c.cant_abiertos,
    c.cant_clics,
    l.ultimo_estado,
    l.ultima_fecha
   FROM cnt c
     LEFT JOIN last l USING (id_notificacion_master);

-- ----------------------------
-- View structure for v_envios_por_notificacion
-- ----------------------------
DROP VIEW IF EXISTS "email"."v_envios_por_notificacion";
CREATE VIEW "email"."v_envios_por_notificacion" AS  WITH base AS (
         SELECT (cola.link_payload ->> 'id_notificacion'::text)::bigint AS id_notificacion,
            cola.estado,
            cola.enviado_en,
            cola.abierto_en,
            cola.clic_en,
            cola.created_at
           FROM email.cola
          WHERE COALESCE(cola.link_payload ->> 'tipo'::text, ''::text) = 'notificacion_cobro'::text AND cola.link_payload ? 'id_notificacion'::text
        ), cnt AS (
         SELECT base.id_notificacion,
            count(*) AS cant_intentos,
            count(base.enviado_en) AS cant_enviados,
            count(base.abierto_en) AS cant_abiertos,
            count(base.clic_en) AS cant_clics
           FROM base
          GROUP BY base.id_notificacion
        ), last AS (
         SELECT DISTINCT ON (base.id_notificacion) base.id_notificacion,
            base.estado AS ultimo_estado,
            COALESCE(base.enviado_en, base.created_at) AS ultima_fecha
           FROM base
          ORDER BY base.id_notificacion, (COALESCE(base.enviado_en, base.created_at)) DESC
        )
 SELECT c.id_notificacion,
    c.cant_intentos,
    c.cant_enviados,
    c.cant_abiertos,
    c.cant_clics,
    l.ultimo_estado,
    l.ultima_fecha
   FROM cnt c
     LEFT JOIN last l USING (id_notificacion);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "email"."cola_id_email_seq"
OWNED BY "email"."cola"."id_email";
SELECT setval('"email"."cola_id_email_seq"', 1, false);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "email"."config_id_email_config_seq"
OWNED BY "email"."config"."id_email_config";
SELECT setval('"email"."config_id_email_config_seq"', 1, true);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "email"."evento_id_evento_seq"
OWNED BY "email"."evento"."id_evento";
SELECT setval('"email"."evento_id_evento_seq"', 1, false);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "email"."plantilla_id_plantilla_seq"
OWNED BY "email"."plantilla"."id_plantilla";
SELECT setval('"email"."plantilla_id_plantilla_seq"', 1, false);

-- ----------------------------
-- Indexes structure for table cola
-- ----------------------------
CREATE INDEX "email_cola_id_notif_idx" ON "email"."cola" USING btree (
  ((link_payload ->> 'id_notificacion'::text)::bigint) "pg_catalog"."int8_ops" ASC NULLS LAST
) WHERE (link_payload ->> 'tipo'::text) = 'notificacion_cobro'::text;
CREATE INDEX "email_cola_id_notif_master_idx" ON "email"."cola" USING btree (
  ((link_payload ->> 'id_notificacion_master'::text)::bigint) "pg_catalog"."int8_ops" ASC NULLS LAST
) WHERE (link_payload ->> 'tipo'::text) = 'notificacion_cobro_master'::text;
CREATE INDEX "email_cola_id_recibo_idx" ON "email"."cola" USING btree (
  ((link_payload ->> 'id_recibo'::text)::bigint) "pg_catalog"."int8_ops" ASC NULLS LAST
) WHERE (link_payload ->> 'tipo'::text) = 'recibo_cabecera'::text;
CREATE INDEX "email_cola_tipo_idx" ON "email"."cola" USING btree (
  (link_payload ->> 'tipo'::text) COLLATE "pg_catalog"."default" "pg_catalog"."text_ops" ASC NULLS LAST
);
CREATE INDEX "idx_email_cola_condo" ON "email"."cola" USING btree (
  "id_condominio" "pg_catalog"."int8_ops" ASC NULLS LAST,
  "created_at" "pg_catalog"."timestamp_ops" ASC NULLS LAST
);
CREATE INDEX "idx_email_cola_estado" ON "email"."cola" USING btree (
  "estado" "pg_catalog"."enum_ops" ASC NULLS LAST,
  "created_at" "pg_catalog"."timestamp_ops" ASC NULLS LAST
);
CREATE INDEX "idx_email_cola_idcondo" ON "email"."cola" USING btree (
  "id_condominio" "pg_catalog"."int8_ops" ASC NULLS LAST
);
CREATE INDEX "idx_email_cola_link_payload" ON "email"."cola" USING gin (
  "link_payload" "pg_catalog"."jsonb_ops"
);
CREATE INDEX "idx_email_cola_target" ON "email"."cola" USING btree (
  "target_tipo" COLLATE "pg_catalog"."default" "pg_catalog"."text_ops" ASC NULLS LAST,
  "target_id" "pg_catalog"."int8_ops" ASC NULLS LAST
);
CREATE UNIQUE INDEX "uq_email_cola_link_token" ON "email"."cola" USING btree (
  "link_token" "pg_catalog"."uuid_ops" ASC NULLS LAST
);
CREATE UNIQUE INDEX "uq_email_cola_tracking_token" ON "email"."cola" USING btree (
  "tracking_token" "pg_catalog"."uuid_ops" ASC NULLS LAST
);
CREATE UNIQUE INDEX "ux_email_cola_link" ON "email"."cola" USING btree (
  "link_token" "pg_catalog"."uuid_ops" ASC NULLS LAST
);
CREATE UNIQUE INDEX "ux_email_cola_tracking" ON "email"."cola" USING btree (
  "tracking_token" "pg_catalog"."uuid_ops" ASC NULLS LAST
);

-- ----------------------------
-- Checks structure for table cola
-- ----------------------------
ALTER TABLE "email"."cola" ADD CONSTRAINT "cola_intentos_check" CHECK ((intentos >= 0));
ALTER TABLE "email"."cola" ADD CONSTRAINT "cola_target_tipo_check" CHECK ((target_tipo = ANY (ARRAY['notificacion'::text, 'recibo'::text, 'master'::text])));

-- ----------------------------
-- Primary Key structure for table cola
-- ----------------------------
ALTER TABLE "email"."cola" ADD CONSTRAINT "cola_pkey" PRIMARY KEY ("id_email");

-- ----------------------------
-- Indexes structure for table config
-- ----------------------------
CREATE INDEX "idx_email_config_condo_activo" ON "email"."config" USING btree (
  "id_condominio" "pg_catalog"."int8_ops" ASC NULLS LAST,
  "activo" "pg_catalog"."bool_ops" ASC NULLS LAST
);

-- ----------------------------
-- Triggers structure for table config
-- ----------------------------
CREATE TRIGGER "t_upd_email_config" BEFORE UPDATE ON "email"."config"
FOR EACH ROW
EXECUTE PROCEDURE "email"."tg_set_updated_at"();

-- ----------------------------
-- Uniques structure for table config
-- ----------------------------
ALTER TABLE "email"."config" ADD CONSTRAINT "config_id_condominio_from_email_key" UNIQUE ("id_condominio", "from_email");

-- ----------------------------
-- Checks structure for table config
-- ----------------------------
ALTER TABLE "email"."config" ADD CONSTRAINT "config_puerto_check" CHECK (((puerto >= 1) AND (puerto <= 65535)));
ALTER TABLE "email"."config" ADD CONSTRAINT "config_seguridad_check" CHECK ((seguridad = ANY (ARRAY['none'::text, 'tls'::text, 'ssl'::text])));
ALTER TABLE "email"."config" ADD CONSTRAINT "config_rate_limit_por_min_check" CHECK ((rate_limit_por_min >= 0));

-- ----------------------------
-- Primary Key structure for table config
-- ----------------------------
ALTER TABLE "email"."config" ADD CONSTRAINT "config_pkey" PRIMARY KEY ("id_email_config");

-- ----------------------------
-- Indexes structure for table evento
-- ----------------------------
CREATE INDEX "idx_email_evento_email" ON "email"."evento" USING btree (
  "id_email" "pg_catalog"."int8_ops" ASC NULLS LAST
);
CREATE INDEX "idx_email_evento_email_ts" ON "email"."evento" USING btree (
  "id_email" "pg_catalog"."int8_ops" ASC NULLS LAST,
  "ts" "pg_catalog"."timestamp_ops" ASC NULLS LAST
);
CREATE INDEX "idx_email_evento_tipo" ON "email"."evento" USING btree (
  "tipo" "pg_catalog"."enum_ops" ASC NULLS LAST
);

-- ----------------------------
-- Primary Key structure for table evento
-- ----------------------------
ALTER TABLE "email"."evento" ADD CONSTRAINT "evento_pkey" PRIMARY KEY ("id_evento");

-- ----------------------------
-- Indexes structure for table plantilla
-- ----------------------------
CREATE UNIQUE INDEX "ux_email_plantilla_global" ON "email"."plantilla" USING btree (
  "clave" COLLATE "pg_catalog"."default" "pg_catalog"."text_ops" ASC NULLS LAST
) WHERE id_condominio IS NULL;

-- ----------------------------
-- Triggers structure for table plantilla
-- ----------------------------
CREATE TRIGGER "t_upd_email_plantilla" BEFORE UPDATE ON "email"."plantilla"
FOR EACH ROW
EXECUTE PROCEDURE "email"."tg_set_updated_at"();

-- ----------------------------
-- Uniques structure for table plantilla
-- ----------------------------
ALTER TABLE "email"."plantilla" ADD CONSTRAINT "plantilla_id_condominio_clave_key" UNIQUE ("id_condominio", "clave");

-- ----------------------------
-- Primary Key structure for table plantilla
-- ----------------------------
ALTER TABLE "email"."plantilla" ADD CONSTRAINT "plantilla_pkey" PRIMARY KEY ("id_plantilla");

-- ----------------------------
-- Foreign Keys structure for table cola
-- ----------------------------
ALTER TABLE "email"."cola" ADD CONSTRAINT "cola_id_condominio_fkey" FOREIGN KEY ("id_condominio") REFERENCES "public"."condominio" ("id_condominio") ON DELETE CASCADE ON UPDATE NO ACTION;
ALTER TABLE "email"."cola" ADD CONSTRAINT "cola_id_email_config_fkey" FOREIGN KEY ("id_email_config") REFERENCES "email"."config" ("id_email_config") ON DELETE RESTRICT ON UPDATE NO ACTION;
ALTER TABLE "email"."cola" ADD CONSTRAINT "cola_id_plantilla_fkey" FOREIGN KEY ("id_plantilla") REFERENCES "email"."plantilla" ("id_plantilla") ON DELETE SET NULL ON UPDATE NO ACTION;
ALTER TABLE "email"."cola" ADD CONSTRAINT "cola_id_propietario_fkey" FOREIGN KEY ("id_propietario") REFERENCES "public"."propietario" ("id_propietario") ON DELETE SET NULL ON UPDATE NO ACTION;
ALTER TABLE "email"."cola" ADD CONSTRAINT "cola_id_usuario_fkey" FOREIGN KEY ("id_usuario") REFERENCES "menu_login"."usuario" ("id_usuario") ON DELETE SET NULL ON UPDATE NO ACTION;

-- ----------------------------
-- Foreign Keys structure for table config
-- ----------------------------
ALTER TABLE "email"."config" ADD CONSTRAINT "config_id_condominio_fkey" FOREIGN KEY ("id_condominio") REFERENCES "public"."condominio" ("id_condominio") ON DELETE CASCADE ON UPDATE NO ACTION;

-- ----------------------------
-- Foreign Keys structure for table evento
-- ----------------------------
ALTER TABLE "email"."evento" ADD CONSTRAINT "evento_id_email_fkey" FOREIGN KEY ("id_email") REFERENCES "email"."cola" ("id_email") ON DELETE CASCADE ON UPDATE NO ACTION;

-- ----------------------------
-- Foreign Keys structure for table plantilla
-- ----------------------------
ALTER TABLE "email"."plantilla" ADD CONSTRAINT "plantilla_id_condominio_fkey" FOREIGN KEY ("id_condominio") REFERENCES "public"."condominio" ("id_condominio") ON DELETE CASCADE ON UPDATE NO ACTION;
