# Diagnóstico técnico y roadmap SaaS

## 1. Resumen técnico del proyecto
- Plataforma monolítica PHP (scripts en raíz y carpeta `core/`) con base de datos PostgreSQL. Gestiona condominios, empresas y urbanizaciones para presupuestos, notificaciones de cobro, pagos administrativos, movimientos contables y conciliación.
- Multitenant por campos de referencia (`id_condominio`, `id_usuario`, `id_inmueble`) dentro de un único esquema `public`.

## 2. Mapa global del sistema
- **Núcleo PHP**: scripts en la raíz (`guardar_presupuesto.php`, `generar_notificacion.php`, `guardar_pago.php`, `guardar_movimiento_real.php`, `procesar_conciliacion.php`, etc.) y utilidades en `core/` y `maestros/` para catálogos y formularios.
- **Esquemas de BD**:
  - `public`: datos operacionales (condominios, inmuebles, propietarios, notificaciones, recibos, cuentas, movimientos, créditos, plan de cuentas, presupuestos, ejecución de gastos, etc.).
  - `email`: plantillas y trazas de correo.
  - `menu_login`: autenticación, roles, programas y funciones auxiliares para cálculo de montos base y actualización de saldos.
- **Módulos principales**: inmuebles/propietarios, presupuestos y notificaciones, pagos/recibos, movimientos y conciliación, reportes básicos, proveedores (mínimo), email, autenticación/roles.

## 3. Descripción de los esquemas de BD
### 3.1 Esquema `public` (operacional)
- Catálogos: `moneda`, `plan_cuenta`, `plan_cuenta_base`.
- Entidades core: `condominio` (moneda base, esquema de cuota, logos), `inmueble`, `propietario`, `propietario_inmueble`.
- Cobranza: `notificacion_cobro`, `notificacion_cobro_detalle`, `notificacion_cobro_master` y correlativos.
- Presupuesto y distribución: `gasto`, `distribucion_gasto`, `ejecucion_gasto`.
- Pagos/recibos: `recibo_cabecera`, `recibo_origen_fondos`, `recibo_destino_fondos`, `pago`, `credito_a_favor`.
- Cuentas y movimientos: `cuenta`, `movimiento_general`, `movimiento_detalle_ingreso`, `movimiento_detalle_egreso`.
- Control y auditoría: `auditoria`, `cierre_contable`, `rol`, `administradores`.

### 3.2 Esquema `email`
- Tablas para configuración y registro de correos (plantillas, destinatarios, bitácora) que respaldan notificaciones por email.

### 3.3 Esquema `menu_login`
- Autenticación: `usuario`, `tokens`, `sesiones`, `usuario_rol`.
- Autorización y UI: `modulos`, `programas`, `programas_rol` para menú y permisos.
- Funciones clave: triggers `actualizar_saldo_cuenta`, `calcular_monto_base_detalle`, `calcular_monto_base_egreso` y `calcular_monto_base_ingreso` que calculan montos base con tasas y actualizan saldos de cuentas al conciliar.

## 4. Mapa entidad–relación textual
- **Condominio** (moneda base, esquema de cuota) ←→ **Inmueble** (alícuota o cuota fija) ←→ **Propietario** (puede tener varios inmuebles) y **Propietario_inmueble** (asocia con fechas y estados).
- **Plan de cuentas** (`plan_cuenta`, `plan_cuenta_base`) vincula gastos, presupuestos y movimientos.
- **Presupuesto** (`gasto`/`distribucion_gasto`) define montos por concepto y periodo; se refleja en **notificaciones de cobro** (cabecera y detalle) por condominio y por inmueble.
- **Recibos/Pagos** (`recibo_cabecera`, `recibo_origen_fondos`, `recibo_destino_fondos`, `pago`) aplican abonos a notificaciones y gestionan origen de fondos (cuentas, créditos).
- **Cuentas** (`cuenta`) y **movimientos** (`movimiento_general` + detalles de ingreso/egreso) rastrean saldos por banco/efectivo/fondos.
- **Créditos a favor** (`credito_a_favor`) almacenan excedentes o notas de crédito para ser aplicadas a futuros cobros.

## 5. Columnas críticas y trazabilidad
- Estados y fechas: `estado` en notificaciones, cuentas, créditos y movimientos; `fecha_creacion`/`fecha_actualizacion`; `fecha_ejecucion` en egresos.
- Moneda y tasa: `id_moneda` en `condominio`, `cuenta`, `credito_a_favor`, notificaciones y recibos; campos `tasa`, `monto_base` en movimientos/recibos para conversión.
- Identificadores multitenant: `id_condominio`, `id_inmueble`, `id_propietario`, `id_usuario` en tablas operativas.
- Auditoría: tabla `auditoria`, correlativos de recibos/notificaciones, tokens de sesión y función `actualizar_saldo_cuenta` que asegura reversa/ajuste al cambiar estado de movimientos.

## 6. Arquitectura funcional y módulos
- **Inmuebles y propietarios**: altas/bajas y asignación de alícuotas/cuotas (`master_inmuebles.php`, `master_propietarios.php`, `propietario_inmueble_upsert.php`).
- **Presupuestos**: `guardar_presupuesto.php` crea gastos por periodo/plan de cuenta y genera notificaciones de cobro basadas en la distribución.
- **Notificaciones de cobro**: `generar_notificacion.php`, `notificacion_cobro_master_*` administran cabecera/detalle, correlativos y estados.
- **Pagos/recibos**: `guardar_pago.php`, `generar_recibo.php` registran recibos, orígenes de fondos, tasas y aplicación a notificaciones; `aprobar_pago.php` valida/autoriza.
- **Movimientos contables y conciliación**: `guardar_movimiento_real.php`, `conciliacion_pagos.php`, `procesar_conciliacion.php` crean movimientos de ingreso/egreso y marcan conciliación; triggers calculan montos base y actualizan saldos al pasar a estado conciliado.
- **Reportes/indicadores**: scripts en `indicadores*.php`, `notificaciones_pendientes.php`, `cxc_inmueble.php` presentan saldos y pendientes.
- **Proveedores**: base mínima vía plan de cuentas y egresos; no hay módulo completo dedicado.
- **Email**: `guardar_config_email.php`, `probar_config_email.php` para plantillas y envíos.
- **Autenticación/roles**: páginas `auth-*` y tablas `menu_login.usuario`, `usuario_rol`; faltan capacidades explícitas de superadmin global.

## 7. Flujo funcional esperado
1. **Presupuesto**: se definen conceptos (gas, agua, vigilancia) por periodo y plan de cuentas; sirven de base para cargos previstos.
2. **Notificación de cobro**: se generan cargos por condominio y se distribuyen por inmueble según alícuota/cuota fija, informando al propietario su aporte en la moneda base del condominio.
3. **Pago**: el propietario registra un recibo indicando origen de fondos (cuentas bancarias, efectivo, créditos) y aplica montos a notificaciones específicas, convirtiendo siempre a la moneda base con la tasa vigente.
4. **Relación de ingresos y egresos**: consolida lo cobrado y lo ejecutado versus presupuesto, mostrando variaciones por concepto y qué cuentas se afectaron (bancos, caja, fondo de reserva, cuentas por pagar, préstamos de vecinos).
5. **Cuentas contables/bancarias**: cada ingreso/egreso debe generar movimientos vinculados a una cuenta y, al conciliar, impactar `saldo_actual`, permitiendo cuadrar con la relación de ingresos/egresos y con estados de notificación.

## 8. Moneda base, multimoneda y conversiones
- Cada condominio define su **moneda base** en `public.condominio.id_moneda`; todas las deudas y estados de cuenta se expresan en esa moneda.
- El sistema es **multimoneda**: pagos y movimientos pueden estar en otras monedas, pero se convierten a la moneda base usando la tasa registrada; campos `monto_base` y triggers de cálculo se apoyan en `tasa` para guardar equivalentes.
- En esta versión, la moneda base es **invariable** después de crear el condominio. Un cambio de moneda base es una propuesta futura que requerirá reconversión histórica.
- Tablas y campos implicados:
  - `condominio.id_moneda` (moneda base del cliente); `cuenta.id_moneda` y `saldo_actual`; `credito_a_favor.id_moneda`.
  - Notificaciones/recibos guardan montos y tasas; movimientos (`movimiento_general` y detalles) usan `tasa` y `monto_base` para reflejar la equivalencia en moneda base.
  - Implicaciones: notificaciones se generan en moneda base; pagos en moneda distinta se convierten al registrar el recibo; la relación de ingresos/egresos y la conciliación usan montos base para cuadrar con saldos de cuentas.

## 9. Lo que el sistema YA hace bien (en esta versión)
- Gestión de inmuebles y propietarios con asociación por condominio e inmuebles múltiples.
- Generación de presupuestos que alimentan notificaciones de cobro por periodo y concepto.
- Registro de pagos con orígenes de fondos (cuentas, efectivo, créditos) y aplicación a notificaciones respetando saldos.
- Modelo de cuentas bancarias/efectivo y créditos a favor por propietario en múltiples monedas.
- Triggers para cálculo de montos base y ajuste de `saldo_actual` al conciliar movimientos, evitando desbalances al modificar estados.

## 10. Vacíos y debilidades actuales
- **Relación de ingresos y egresos**: falta un reporte consolidado que compare presupuesto vs. ejecutado y amarre con notificaciones y movimientos conciliados.
- **Actualización de saldos**: aunque los triggers ajustan al conciliar, falta garantizar que todo pago/egreso genere su movimiento y que los estados se sincronicen con notificaciones.
- **Estados y amortización de notificaciones**: no siempre se actualiza `monto_pagado` y estado de las notificaciones al registrar/ajustar pagos.
- **Trazabilidad contable y conciliación**: movimientos pueden quedar pendientes; se requiere forzar vínculo a cuentas, validar moneda/tasa y registrar reversas/auditoría en operaciones críticas.
- **Roles y superadmin**: no se observa aún un rol maestro con control global ni flujos de reversión completos.

## 11. Roadmap hacia SaaS (solo a alto nivel)
- **Fase 1**: Consolidar lógica de negocio y consistencia contable: asegurar generación de movimientos para cada pago/egreso, actualización de notificaciones, relación ingresos/egresos confiable, conciliación obligatoria antes de cerrar periodos.
- **Fase 2**: API REST interna y estabilización de módulos para consumo externo; endurecer validaciones y auditoría.
- **Fase 3**: Multitenancy avanzado (políticas/RLS, aislamiento de datos, controles de acceso por condominio) manteniendo esquema único.
- **Fase 4**: Panel de superadmin, reversión de operaciones, métricas globales y posterior habilitación de modelo de negocio SaaS y pagos en línea (futuro).
