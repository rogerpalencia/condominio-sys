<?php
/**
 * notif_master.php — Lista, CRUD y envíos de Notificaciones Maestras
 * -------------------------------------------------------------------
 * - Quita botón de eliminar
 * - Botón “Enviar / Reenviar” (lista destinatarios bajo demanda)
 * - Botón “Enviar a…” (correo puntual)
 * - Modal de destinatarios (select all/none, filtro, enviar)
 * - Endpoint interno (?ajax=destinatarios) para listar emails
 * - Conserva tu lógica de alta, edición, clonado y totales
 */

@session_start();
include 'layouts/session.php';
include 'layouts/head-main.php';

require_once 'core/funciones.php';
require_once 'layouts/vars.php';
require_once 'core/PDO.class.php';

if (!isset($_SESSION['userid'])) { header("Location: login.php"); exit; }

/* ============================================================
   0) CONTEXTO BÁSICO
   ============================================================ */

$id_condominio = 0;
if (isset($_POST['id_condominio']) && $_POST['id_condominio'] !== '') {
  $id_condominio = (int)$_POST['id_condominio'];
} elseif (isset($_SESSION['id_condominio'])) {
  $id_condominio = (int)$_SESSION['id_condominio'];
} elseif (isset($_GET['id_condominio'])) {
  $id_condominio = (int)$_GET['id_condominio'];
}
if ($id_condominio <= 0) { die('No hay condominio seleccionado.'); }

$conn = DB::getInstance();

$nombre_condo = 'Condominio';
$id_moneda    = null;
try {
  $st = $conn->prepare('SELECT nombre, id_moneda FROM public.condominio WHERE id_condominio = :c LIMIT 1');
  $st->execute([':c'=>$id_condominio]);
  if ($r = $st->fetch(PDO::FETCH_ASSOC)) {
    $nombre_condo = $r['nombre'];
    $id_moneda    = (int)$r['id_moneda'];
  }
} catch (Throwable $e) {
  // log opcional
}

/* ============================================================
   1) ENDPOINT INTERNO: LISTA DESTINATARIOS (AJAX)
   ============================================================ */
if (isset($_GET['ajax']) && $_GET['ajax'] === 'destinatarios') {
  header('Content-Type: application/json; charset=utf-8');
  try {
    $cid = isset($_GET['id_condominio']) ? (int)$_GET['id_condominio'] : 0;
    if ($cid <= 0) { echo json_encode(['status'=>'error','message'=>'id_condominio inválido']); exit; }

    $q = "
      SELECT
        TRIM(u.correo) AS email,
        TRIM(COALESCE(p.nombre1,'')) || ' ' || TRIM(COALESCE(p.apellido1,'')) AS nombre,
        COALESCE(STRING_AGG(DISTINCT i.identificacion, ' / ' ORDER BY i.identificacion), '') AS inmuebles
      FROM public.inmueble i
      JOIN public.propietario_inmueble pi ON pi.id_inmueble = i.id_inmueble
      JOIN public.propietario p           ON p.id_propietario = pi.id_propietario
      JOIN menu_login.usuario u           ON u.id_usuario = pi.id_usuario
      WHERE i.id_condominio = :c
        AND u.correo IS NOT NULL AND TRIM(u.correo) <> ''
      GROUP BY email, nombre
      ORDER BY email
    ";
    $st = $conn->prepare($q);
    $st->execute([':c'=>$cid]);
    $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    echo json_encode(['status'=>'ok','data'=>$rows], JSON_UNESCAPED_UNICODE);
  } catch (Throwable $e) {
    echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
  }
  exit;
}

/* ============================================================
   2) HELPER: Select de Plan de Cuentas (para alta/edición)
   ============================================================ */
function selectPlanCuentas($conn, $id_condo, $tipo = null) {
  $q = "SELECT id_plan, codigo, nombre, tipo FROM plan_cuenta
        WHERE id_condominio = :c AND estado = TRUE";
  if ($tipo) $q .= " AND tipo = :tipo";
  $q .= " ORDER BY tipo, codigo";

  $st = $conn->prepare($q);
  $params = [':c'=>$id_condo];
  if ($tipo) $params[':tipo'] = $tipo;
  $st->execute($params);

  $html = '<option value="">Seleccione</option>';
  while ($r = $st->fetch(PDO::FETCH_ASSOC)) {
    $tipo_label = ($r['tipo']==='ingreso') ? '[Ingreso]' : '[Egreso]';
    $html .= '<option value="'.(int)$r['id_plan'].'" data-tipo="'.htmlspecialchars($r['tipo']).'">'
          .  htmlspecialchars($r['codigo'].' - '.$r['nombre'].' '.$tipo_label)
          .  '</option>';
  }
  return $html;
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <title>Notif. Maestras | <?= NOMBREAPP ?></title>
  <?php include 'layouts/head.php'; ?>
  <link href="assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css" rel="stylesheet"/>
  <link href="assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css" rel="stylesheet"/>
  <?php include 'layouts/head-style.php'; ?>
  <style>
    .card { border-radius: 8px; }
    .table th, .table td { vertical-align: middle; }
    .badge { font-weight: 500; }
    .btn-group .btn, .d-flex.gap-1 .btn { vertical-align: middle; }
    #tablaDestinatarios tbody tr td { font-size: 13px; }
    .search-mini { max-width: 320px; }
  </style>
</head>

<?php include 'layouts/body.php'; ?>

<input type="hidden" id="id_condominio" value="<?= (int)$id_condominio ?>">

<div id="layout-wrapper">
<?php include 'layouts/menu.php'; ?>

<div class="main-content"><div class="page-content"><div class="container-fluid">

  <!-- Encabezado -->
  <div class="row align-items-center mb-3">
    <div class="col-12 col-md-6">
      <h1 class="display-6 mb-0">Notificaciones Maestras</h1>
      <p class="text-muted mb-0"><?= htmlspecialchars($nombre_condo) ?></p>
    </div>
    <div class="col-12 col-md-6 text-md-end mt-2 mt-md-0">
      <button id="btnNueva" class="btn btn-success me-1" data-bs-toggle="modal" data-bs-target="#modalCargarCobroMaster">
        + Nueva notificación
      </button>
      <button class="btn btn-primary" type="button" onclick="window.history.back()">Volver</button>
    </div>
  </div>

  <!-- Tabla -->
  <div class="card"><div class="card-body">
    <div class="table-responsive">
      <table id="tablaMasters" class="table table-bordered dt-responsive nowrap w-100">
        <thead class="table-light">
          <tr>
            <th>ID</th>
            <th>Año</th>
            <th>Mes</th>
            <th>Emisión</th>
            <th>Descripción</th>
            <th>Moneda</th>
            <th>Total</th>
            <th>Estado</th>
            <th>Tipo</th>
            <th>Acciones</th>
          </tr>
        </thead>
      </table>
    </div>
  </div></div>

</div><?php include 'layouts/footer.php'; ?></div></div>

<?php include 'layouts/right-sidebar.php'; ?>
</div><!-- layout-wrapper -->

<!-- ==========================================================
     Modal NUEVO/EDITAR
     ========================================================== -->
<div class="modal fade" id="modalCargarCobroMaster" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl"><div class="modal-content">
    <form id="formCargarCobroMaster">
      <input type="hidden" name="id_notificacion_master" id="id_notificacion_master">
      <input type="hidden" name="id_condominio" value="<?= (int)$id_condominio ?>">
      <input type="hidden" name="id_moneda" id="id_moneda" value="<?= (int)$id_moneda ?>">

      <div class="modal-header">
        <h5 class="modal-title" id="modalTitle">Nueva Notificación Maestra</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <div class="row g-3">
          <div class="col-md-2">
            <label class="form-label">Año</label>
            <input type="number" name="anio" class="form-control" value="<?= date('Y') ?>" min="2000" max="2100" required>
          </div>
            <div class="col-md-2">
            <label class="form-label">Mes</label>
            <select name="mes" id="mes" class="form-select" required>
              <option value="0">— Extraordinaria —</option>
              <?php foreach([1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',7=>'Julio',8=>'Agosto',9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre'] as $n=>$nom): ?>
                <option value="<?= $n ?>"><?= $nom ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">Emisión</label>
            <input type="date" name="fecha_emision" class="form-control" value="<?= date('Y-m-d') ?>" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">Vencimiento</label>
            <input type="date" name="fecha_vencimiento" class="form-control" value="<?= date('Y-m-d',strtotime('+30 days')) ?>" required>
          </div>


<script>
      // Ajusta emisión/vencimiento cuando se selecciona mes (usa año del input 'anio' o año actual)
      (function(){
        function pad(n){ return String(n).padStart(2,'0'); }
        function setDatesForMonth(){
        var mes = parseInt(document.getElementById('mes').value, 10);
        var anioInput = document.querySelector('input[name="anio"]');
        var anio = anioInput ? parseInt(anioInput.value,10) : (new Date()).getFullYear();
        if (!anio || isNaN(anio)) anio = (new Date()).getFullYear();

        var fe = document.getElementById('fecha_emision');
        var fv = document.getElementById('fecha_vencimiento');
        if (!fe || !fv) return;

        if (mes >= 1 && mes <= 12) {
          var mm = pad(mes);
          fe.value = anio + '-' + mm + '-01';
          fv.value = anio + '-' + mm + '-05';
        } else {
          // Extraordinaria: restaurar valores por defecto (hoy / hoy +30)
          var today = new Date();
          var plus30 = new Date(today);
          plus30.setDate(plus30.getDate() + 30);
          fe.value = today.toISOString().slice(0,10);
          fv.value = plus30.toISOString().slice(0,10);
        }
        }

        // Vincular eventos: cambio de mes y de año
        document.addEventListener('DOMContentLoaded', function(){
        var selMes = document.getElementById('mes');
        if (selMes) selMes.addEventListener('change', setDatesForMonth);
        var anioInput = document.querySelector('input[name="anio"]');
        if (anioInput) anioInput.addEventListener('change', setDatesForMonth);
        // Inicializar al abrir modal (si ya hay valores)
        setDatesForMonth();
        });
      })();
      </script>



          <div class="col-md-12">
            <label class="form-label">Descripción</label>
            <input type="text" name="descripcion_cab" id="descripcion_cab" maxlength="150"
                   class="form-control text-uppercase" required
                   oninput="this.value=this.value.toUpperCase();">
          </div>
          <div class="col-md-4">
            <label class="form-label">Tipo</label>
            <select name="id_tipo" id="id_tipo" class="form-select" required>
              <option value="1" selected>Presupuesto</option>
              <option value="2">Relación Ingr./Egr.</option>
            </select>
          </div>
        </div>

        <hr class="my-3">

        <h5 class="d-flex align-items-center justify-content-between">
          <span>Detalle</span>
          <div class="btn-group">
            <button type="button" class="btn btn-outline-secondary btn-sm" id="btnClonarPresupuesto" onclick="accionClonar()" style="display:none"></button>
            <button type="button" class="btn btn-outline-danger btn-sm" id="btnLimpiarDetalle" onclick="limpiarDetalle()">Limpiar lista</button>
          </div>
        </h5>

        <table class="table table-bordered" id="tablaDetalleMaster">
          <thead>
            <tr>
              <th>Tipo</th>
              <th>Cuenta</th>
              <th>Descripción</th>
              <th>Monto</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>

        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Total</label>
            <input type="text" name="monto_total" class="form-control" readonly>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button class="btn btn-primary" type="submit">Guardar</button>
        <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancelar</button>
      </div>
    </form>
  </div></div>
</div>

<!-- ==========================================================
     Modal DESTINATARIOS
     ========================================================== -->
<div class="modal fade" id="modalDestinatarios" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Enviar / Reenviar por correo</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <div>
            <button id="btnSelTodos"  class="btn btn-sm btn-outline-secondary me-1">Seleccionar todo</button>
            <button id="btnSelNinguno" class="btn btn-sm btn-outline-secondary">Ninguno</button>
          </div>
          <input id="filtroDest" class="form-control form-control-sm search-mini" placeholder="Filtrar por email/nombre/inmueble...">
        </div>
        <div class="table-responsive">
          <table class="table table-sm table-striped" id="tablaDestinatarios">
            <thead>
              <tr>
                <th style="width:36px;"></th>
                <th>Email</th>
                <th>Nombre</th>
                <th>Inmuebles</th>
              </tr>
            </thead>
            <tbody><!-- filas dinámicas --></tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer">
        <button id="btnEnviarSeleccion" class="btn btn-primary">Enviar selección</button>
        <button id="btnEnviarA" class="btn btn-secondary">Enviar a...</button>
      </div>
    </div>
  </div>
</div>

<?php include 'layouts/vendor-scripts.php'; ?>
<script src="assets/libs/jquery/dist/jquery.min.js"></script>
<script src="assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/libs/sweetalert2/dist/sweetalert2.min.js"></script>
<script src="assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
<script src="assets/libs/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js"></script>

<script>
/* ============================================================
   A) Helpers de UI
   ============================================================ */
function estadoBadge(d){
  const s=(d||'').toLowerCase();
  if(s==='emitida')  return '<span class="badge bg-success">Emitida</span>';
  if(s==='pendiente')return '<span class="badge bg-warning text-dark">Pendiente</span>';
  if(s==='cerrada')  return '<span class="badge bg-secondary">Cerrada</span>';
  return d||'';
}
function tipoBadge(row){
  const t = (row.tipo_txt||'').toLowerCase();
  if (t.includes('presupuesto') || row.id_tipo==1) return '<span class="badge bg-primary">Presupuesto</span>';
  if (t.includes('relación') || t.includes('relacion') || row.id_tipo==2) return '<span class="badge bg-info">Relación Ingr./Egr.</span>';
  return (row.tipo_txt||'').toUpperCase();
}
/** Botones por fila:
 *  - Ver
 *  - Enviar / Reenviar (abre modal)
 *  - Enviar a... (correo puntual)
 *  - Aprobar (si Pendiente)
 *  - Sin eliminar (requerido)
 */
function renderBtns(row){
  const id  = row.id_notificacion_master;
  const tip = Number(row.id_tipo);
  const tipoName = (tip === 2) ? 'master_relacion' : 'master_presupuesto';
  const estado = String(row.estado||'').toLowerCase();

  let html = `
    <div class="d-flex flex-wrap gap-1">
      <button class="btn btn-sm btn-secondary ver-notificacion" title="Ver" data-id="${id}">
        <i class="fas fa-eye"></i>
      </button>
  `;



  html += `
      <button class="btn btn-sm btn-outline-primary btn-broadcast" title="Enviar / Reenviar"
              data-id="${id}" data-tipo_name="${tipoName}">
        <i class="fas fa-envelope"></i>
      </button>
      <button class="btn btn-sm btn-outline-secondary btn-enviar-a" title="Enviar a..."
              data-id="${id}" data-tipo_name="${tipoName}">
        <i class="fas fa-paper-plane"></i>
      </button>
  `;
  
  if (estado === 'pendiente') {
    html += `
      <button class="btn btn-sm btn-success aprobar-master" title="Aprobar"
              data-id="${id}" data-tipo="${tip}">
        <i class="fas fa-check"></i>
      </button>
      
            <button class="btn btn-sm btn-outline-warning editar-master" title="Editar"
              data-id="${id}">
        <i class="fas fa-edit"></i>
      </button>
      
      
      
      `;
  }
  html += `</div>`;
  return html;
}

/* ============================================================
   B) Inicialización
   ============================================================ */
$(function(){
  window.currentMasterForDest = 0;
  window.currentMasterTipo    = 'master_relacion';
  window.currentCondoId       = parseInt($('#id_condominio').val(),10) || 0;

  const idCondo = $('#id_condominio').val();

  const tabla = $('#tablaMasters').DataTable({
    processing:true,
    serverSide:true,
    ajax:{
      url:'notif_master_data.php',
      type:'POST',
      data: d => { d.id_condominio = idCondo; }
    },
    columns:[
      { data:'id_notificacion_master' },
      { data:'anio' },
      { data:'mes', render:d=> String(d)==='0' ? '<span class="badge bg-danger">Extraordinaria</span>' : d },
      { data:'fecha_emision' },
      { data:'descripcion', render:d=> String(d||'').toUpperCase() },
      { data:'moneda', defaultContent:'' },
      { data:'monto_total', render: $.fn.dataTable.render.number('.',',',2,'') },
      { data:'estado', render:estadoBadge },
      { data:null, render:(d,t,row)=> tipoBadge(row) },
      { data:null, render:renderBtns, orderable:false, searchable:false }
    ],
    order:[[1,'DESC'],[2,'DESC']],
    language:{ url:'Spanish.json' }
  });

  /* ==========================================================
     C) Acciones: Ver / Aprobar
     ========================================================== */
  $('#tablaMasters').on('click', '.ver-notificacion', function(){
    const id = $(this).data('id');
    window.open('generar_notificacion_master.php?id_notificacion_master=' + id, '_blank');
  });

  $('#tablaMasters').on('click', '.aprobar-master', function(){
    const id   = $(this).data('id');
    const tipo = $(this).data('tipo');
    const msg  = (parseInt(tipo,10)===1)
                  ? 'Aprobar generará notificaciones para cada inmueble según sus alícuotas.'
                  : 'Aprobar marcará la relación como distribuida a los propietarios.';
    Swal.fire({
      title: '¿Aprobar notificación #' + id + '?',
      text: msg, icon:'warning', showCancelButton:true,
      confirmButtonText:'Sí, aprobar', cancelButtonText:'Cancelar'
    }).then(res=>{
      if (!res.isConfirmed) return;
      $.ajax({
        url:'notificacion_cobro_master_aprobar.php',
        type:'POST',
        dataType:'json',
        data:{ id_notificacion_master:id, id_tipo:tipo, id_condominio:window.currentCondoId },
        success:function(r){
          if (r && r.status==='ok'){
            Swal.fire('Aprobado', r.message || 'Operación exitosa', 'success')
              .then(() => { $('#tablaMasters').DataTable().ajax.reload(null, false); });
          } else {
            Swal.fire('Error', (r&&r.message)||'No se pudo aprobar', 'error');
          }
        },
        error:function(xhr){ Swal.fire('Error', xhr.responseText || 'No se pudo aprobar', 'error'); }
      });
    });
  });

  /* ==========================================================
     D) Emails: abrir modal (lista bajo demanda) y Enviar a...
     ========================================================== */
  $('#tablaMasters').on('click', '.btn-broadcast', function(){
    window.currentMasterForDest = parseInt($(this).data('id'), 10) || 0;
    window.currentMasterTipo    = String($(this).data('tipo_name') || 'master_relacion');
    window.currentCondoId       = parseInt($('#id_condominio').val(),10) || 0;

    if (!window.currentMasterForDest || !window.currentCondoId) {
      Swal.fire('Error','No se pudo determinar la master o el condominio.','error');
      return;
    }

    $('#tablaDestinatarios tbody').empty();
    $('#filtroDest').val('');

    $.getJSON('./email/get_destinatarios_master.php', { id_condominio: window.currentCondoId })
      .done(function(r){
        if (!r || r.status!=='ok') {
          Swal.fire('Error', (r && r.message) || 'No se pudieron cargar destinatarios', 'error');
          return;
        }
        const rows = r.data || [];
        if (!rows.length) {
          $('#tablaDestinatarios tbody').html('<tr><td colspan="4" class="text-center text-muted">No hay destinatarios con email.</td></tr>');
        } else {
          const frag = document.createDocumentFragment();
          rows.forEach(item=>{
            const tr = document.createElement('tr');
            tr.innerHTML = `
              <td><input type="checkbox" class="form-check-input chkDest" value="${item.email}"></td>
              <td class="td-email">${item.email}</td>
              <td class="td-nombre">${(item.nombre||'').toUpperCase()}</td>
              <td class="td-inm">${item.inmuebles||''}</td>
            `;
            frag.appendChild(tr);
          });
          $('#tablaDestinatarios tbody')[0].appendChild(frag);
        }
        $('#modalDestinatarios').modal('show');
      })
      .fail(function(){
        Swal.fire('Error', 'No se pudo obtener la lista de destinatarios.', 'error');
      });
  });

  $('#tablaMasters').on('click', '.btn-enviar-a', function(){
    window.currentMasterForDest = parseInt($(this).data('id'), 10) || 0;
    window.currentMasterTipo    = String($(this).data('tipo_name') || 'master_relacion');
    window.currentCondoId       = parseInt($('#id_condominio').val(),10) || 0;

    const idMaster = window.currentMasterForDest;
    const idCondo  = window.currentCondoId;
    const tipoEnvio = window.currentMasterTipo;

    Swal.fire({
      title: 'Enviar a...',
      input: 'email',
      inputPlaceholder: 'ejemplo@correo.com',
      showCancelButton: true,
      confirmButtonText: 'Enviar',
      inputValidator: (value) => {
        if (!value) return 'Debes ingresar un correo';
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) return 'Formato inválido';
      }
    }).then(r => {
      if (!r.isConfirmed || !r.value) return;

      $.ajax({
        url: 'email/enviar_email_master_broadcast.php',
        type: 'POST',
        dataType: 'json',
        data: { tipo: tipoEnvio, id: idMaster, id_condominio: idCondo, emails: r.value },
      })
      .done(function(resp){
        if (resp && resp.status==='ok'){
          Swal.fire('Envío', `Enviados: ${resp.enviados} — Fallidos: ${resp.fallidos}`, 'success');
        } else {
          Swal.fire('Error', (resp&&resp.message)||'No se pudo enviar', 'error');
        }
      })
      .fail(function(xhr){ Swal.fire('Error', xhr.responseText || 'No se pudo contactar el servidor', 'error'); });
    });
  });

  /* ==========================================================
     E) Modal Destinatarios: filtro, selección y envío
     ========================================================== */
  $('#filtroDest').on('input', function(){
    const q = $(this).val().toLowerCase().trim();
    $('#tablaDestinatarios tbody tr').each(function(){
      const txt = $(this).text().toLowerCase();
      $(this).toggle(txt.indexOf(q) !== -1);
    });
  });

  $('#btnSelTodos').on('click', function(e){ e.preventDefault(); $('.chkDest:visible').prop('checked', true); });
  $('#btnSelNinguno').on('click', function(e){ e.preventDefault(); $('.chkDest:visible').prop('checked', false); });

  $('#btnEnviarSeleccion').on('click', function () {
    const $btn = $(this).prop('disabled', true);
    try {
      const emails = $('.chkDest:checked').map(function(){ return String(this.value).trim(); }).get();
      if (!emails.length) { Swal.fire('Aviso','No hay destinatarios seleccionados.','info'); return; }

      const idMaster = parseInt(window.currentMasterForDest, 10) || 0;
      const idCondo  = parseInt(window.currentCondoId, 10) || parseInt($('#id_condominio').val(), 10) || 0;
      const tipoEnvio = String(window.currentMasterTipo || 'master_relacion');

      if (!idMaster || !idCondo) { Swal.fire('Error','Faltan parámetros (ID master o condominio).','error'); return; }

      Swal.fire({
        title: 'Confirmar envío',
        text: `Se enviará a ${emails.length} destinatario(s).`,
        icon: 'question', showCancelButton: true, confirmButtonText: 'Enviar'
      }).then(res => {
        if (!res.isConfirmed) return;

        $.ajax({
          url: 'email/enviar_email_master_broadcast.php',
          type: 'POST',
          dataType: 'json',
          traditional: true,
          data: { tipo: tipoEnvio, id: idMaster, id_condominio: idCondo, emails: emails }
        })
        .done(function(r){
          if (r && r.status==='ok'){
            Swal.fire('Envío masivo', `Enviados: ${r.enviados} — Fallidos: ${r.fallidos}`, 'success');
            $('#modalDestinatarios').modal('hide');
          } else {
            Swal.fire('Error', (r&&r.message)||'No se pudo enviar', 'error');
          }
        })
        .fail(function(xhr){
          Swal.fire('Error', xhr.responseText || 'No se pudo contactar el servidor', 'error');
        })
        .always(function(){ $btn.prop('disabled', false); });
      });
    } finally {
      setTimeout(() => $btn.prop('disabled', false), 400);
    }
  });

  /* ==========================================================
     F) Lógica de alta/edición (tus funciones existentes)
     ========================================================== */

  // Mostrar modal de NUEVO
  $('#modalCargarCobroMaster').on('show.bs.modal', function(e){
    const trigger = e.relatedTarget;
    if (trigger && trigger.id === 'btnNueva') {
      $('#modalTitle').text('Nueva Notificación Maestra');
      $('#formCargarCobroMaster')[0].reset();
      $('#id_notificacion_master').val('');
      $('#tablaDetalleMaster tbody').empty();
      addFilaMaster();
      updateDescripcion();
      toggleBtnClonar();
    }
  });

  // Limpiar backdrop si quedara activo
  $('#modalCargarCobroMaster').on('hidden.bs.modal', function(){
    $('body').removeClass('modal-open');
    $('.modal-backdrop').remove();
    $('#tablaMasters').DataTable().ajax.reload(null,false);
  });

  // Editar (carga por AJAX)
  $('#tablaMasters').on('click', '.editar-master', function(){
    const idCondo = $('#id_condominio').val();
    const id      = $(this).data('id');

    $('#modalTitle').text('Editar Notificación Maestra #' + id);
    $('#formCargarCobroMaster')[0].reset();
    $('#id_notificacion_master').val(id);
    $('#tablaDetalleMaster tbody').empty();
    $('#modalCargarCobroMaster').modal('show');

    $.ajax({
      url: 'notificacion_cobro_master_fetch.php',
      type: 'POST',
      data: { id_notificacion_master:id, id_condominio:idCondo },
      success: function(resp){
        let wrap = null;
        if (typeof resp === 'string') { try { wrap = JSON.parse(resp); } catch(e) {} }
        else wrap = resp;

        if (!wrap || wrap.status!=='ok' || !wrap.data) {
          Swal.fire('Error', (wrap&&wrap.message)||'No se pudo cargar la notificación', 'error');
          return;
        }

        const data = wrap.data;

        $('input[name="anio"]').val(data.anio ?? '');
        $('select[name="mes"]').val(data.mes ?? '0');
        $('input[name="fecha_emision"]').val(data.fecha_emision ?? '');
        $('input[name="fecha_vencimiento"]').val(data.fecha_vencimiento ?? '');
        $('input[name="descripcion_cab"]').val(data.descripcion ?? '');
        $('select[name="id_tipo"]').val(data.id_tipo ?? 1);

        if (typeof data.id_moneda !== 'undefined' && data.id_moneda !== null) {
          $('#id_moneda').val(data.id_moneda);
        }

        const detalles = Array.isArray(data.detalles) ? data.detalles
                        : (Array.isArray(data.detalle) ? data.detalle
                        : (Array.isArray(data.items) ? data.items : []));

        if (detalles.length) {
          detalles.forEach(det => {
            const $tr = $(addFilaMaster(true));
            const tipoFila = (det.tipo_movimiento==='egreso' || det.tipo_movimiento==='ingreso')
                             ? det.tipo_movimiento : 'ingreso';
            const cuentaId = (det.id_plan_cuenta ?? det.cuenta ?? '');
            $tr.find('select[name="tipo_movimiento[]"]').val(tipoFila);
            loadCuentasForRow($tr[0], tipoFila, String(cuentaId));
            $tr.find('input[name="descripcion[]"]').val(det.descripcion ?? '');
            $tr.find('input[name="monto[]"]').val( (det.monto!==undefined && det.monto!==null) ? det.monto : '' );
          });
        } else {
          addFilaMaster();
        }

        calcTotalMaster();
        updateDescripcion();
        toggleBtnClonar();
      },
      error:function(xhr){ Swal.fire('Error', xhr.responseText || 'No se pudo cargar la notificación', 'error'); }
    });
  });

  // ===== Helpers del detalle =====

  window.loadCuentasForRow = function(rowEl, tipo, selectedId){
    const idCondo = $('#id_condominio').val();
    const selectCuenta = rowEl.querySelector('select[name="id_plan_cuenta[]"]');
    $.ajax({
      url:'./get_plan_cuentas.php',
      type:'POST',
      dataType:'json',
      data:{ id_condominio:idCondo, tipo: (tipo==='ingreso' || tipo==='egreso') ? tipo : null },
      success:function(r){
        if (r.status==='ok'){
          selectCuenta.innerHTML = r.options;
          if (selectedId) {
            if (r.options.indexOf(`value="${selectedId}"`) !== -1) {
              selectCuenta.value = selectedId;
            }
          } else {
            selectCuenta.value = '';
          }
          updateTipoMovimiento(selectCuenta);
          calcTotalMaster();
        } else {
          Swal.fire('Error', r.message || 'No se pudieron cargar las cuentas', 'error');
        }
      },
      error:function(){ Swal.fire('Error','No se pudieron cargar las cuentas','error'); }
    });
  };

  window.onTipoMovimientoChange = function(selTipo){
    const row = selTipo.closest('tr');
    const tipo = selTipo.value;
    loadCuentasForRow(row, tipo, null);
  };

  window.updateTipoMovimiento = function(selectCuenta){
    const tipo = selectCuenta.options[selectCuenta.selectedIndex]?.dataset.tipo || 'ingreso';
    const row  = selectCuenta.closest('tr');
    row.querySelector('select[name="tipo_movimiento[]"]').value = tipo;
  };

  window.calcTotalMaster = function(){
    let t = 0;
    document.querySelectorAll('#tablaDetalleMaster tbody tr').forEach(tr=>{
      const montoStr = tr.querySelector('input[name="monto[]"]').value;
      const monto = parseFloat((montoStr||'').toString().replace(',', '.'));
      if (!isNaN(monto)) t += monto;
    });
    document.querySelector('input[name="monto_total"]').value = t.toFixed(2);
  };

  window.updateDescripcion = function(){
    const mes  = parseInt($('#mes').val(),10);
    const tipo = parseInt($('#id_tipo').val(),10) || 1;
    const $d   = $('#descripcion_cab');
    const meses = {1:'ENERO',2:'FEBRERO',3:'MARZO',4:'ABRIL',5:'MAYO',6:'JUNIO',7:'JULIO',8:'AGOSTO',9:'SEPTIEMBRE',10:'OCTUBRE',11:'NOVIEMBRE',12:'DICIEMBRE'};
    if (mes === 0) {
      $d.val( (tipo===1) ? 'CUOTA ESPECIAL PARA ' : 'RELACIÓN ESPECIAL PARA ' ).prop('readonly', false);
    } else if (mes>=1 && mes<=12) {
      if (tipo===1) { $d.val(`CUOTA DE CONDOMINIO DE ${meses[mes]}`).prop('readonly', true); }
      else          { $d.val(`RELACIÓN DE GASTOS DE ${meses[mes]}`).prop('readonly', true); }
    }
  };

  window.toggleBtnClonar = function(){
    const tipo = parseInt($('#id_tipo').val()||'1',10);
    const $btn = $('#btnClonarPresupuesto');
    $btn.show().text( (tipo===1) ? 'Clonar mes anterior' : 'Copiar ítems del Presupuesto' );
  };

  window.accionClonar = function(){
    const tipo = parseInt($('#id_tipo').val()||'1',10);
    if (tipo===1) clonarMesAnterior();
    else          clonarRelacionPeriodo();
  };

  window.limpiarDetalle = function(){
    $('#tablaDetalleMaster tbody').empty();
    calcTotalMaster();
  };

  window.addFilaMaster = function(returnRow=false){
    const trHtml = `
      <tr>
        <td>
          <select name="tipo_movimiento[]" class="form-select" onchange="onTipoMovimientoChange(this)">
            <option value="ingreso">Ingreso</option>
            <option value="egreso">Egreso</option>
          </select>
        </td>
        <td>
          <select name="id_plan_cuenta[]" class="form-select" required
                  onchange="updateTipoMovimiento(this);calcTotalMaster()">
            <?= selectPlanCuentas($conn, $id_condominio, 'ingreso') ?>
          </select>
        </td>
        <td><input type="text" name="descripcion[]" class="form-control text-uppercase"
                   required oninput="this.value=this.value.toUpperCase();"></td>
        <td><input type="number" name="monto[]" class="form-control"
                    step="0.01" required oninput="calcTotalMaster()"></td>
        <td class="text-nowrap">
          <button type="button" class="btn btn-sm btn-success me-1" title="Agregar debajo" onclick="addFilaDespues(this)">+</button>
          <button type="button" class="btn btn-sm btn-danger" title="Eliminar fila"
                  onclick="this.closest('tr').remove();calcTotalMaster()">x</button>
        </td>
      </tr>`;
    if (returnRow){
      const $tr = $(trHtml);
      $('#tablaDetalleMaster tbody').append($tr);
      return $tr;
    } else {
      $('#tablaDetalleMaster tbody').append(trHtml);
      calcTotalMaster();
      return null;
    }
  };

  window.addFilaDespues = function(btn){
    const nueva = document.createElement('tr');
    nueva.innerHTML = `
      <td>
        <select name="tipo_movimiento[]" class="form-select" onchange="onTipoMovimientoChange(this)">
          <option value="ingreso">Ingreso</option>
          <option value="egreso">Egreso</option>
        </select>
      </td>
      <td>
        <select name="id_plan_cuenta[]" class="form-select" required
                onchange="updateTipoMovimiento(this);calcTotalMaster()">
          <?= selectPlanCuentas($conn, $id_condominio, 'ingreso') ?>
        </select>
      </td>
      <td><input type="text" name="descripcion[]" class="form-control text-uppercase"
                 required oninput="this.value=this.value.toUpperCase();"></td>
      <td><input type="number" name="monto[]" class="form-control"
                 step="0.01" required oninput="calcTotalMaster()"></td>
      <td class="text-nowrap">
        <button type="button" class="btn btn-sm btn-success me-1" title="Agregar debajo" onclick="addFilaDespues(this)">+</button>
        <button type="button" class="btn btn-sm btn-danger" title="Eliminar fila"
                onclick="this.closest('tr').remove();calcTotalMaster()">x</button>
      </td>
    `;
    const filaActual = btn.closest('tr');
    filaActual.parentNode.insertBefore(nueva, filaActual.nextSibling);
    calcTotalMaster();
  };

  window.clonarRelacionPeriodo = function(){
    const tipo = parseInt($('#id_tipo').val()||'1',10);
    if (tipo!==2){ Swal.fire('Aviso','Esta función aplica a Relación (tipo 2).','info'); return; }

    const filas = document.querySelectorAll('#tablaDetalleMaster tbody tr');
    const continuar = ejecutarClonadoRelacion;
    if (filas.length>0){
      Swal.fire({
        title:'Reemplazar detalle',
        text:'Se reemplazarán las filas por las del Presupuesto con monto 0. ¿Continuar?',
        icon:'warning', showCancelButton:true,
        confirmButtonText:'Sí, reemplazar', cancelButtonText:'Cancelar'
      }).then(r=>{ if(r.isConfirmed) continuar(); });
    } else continuar();
  };

  function ejecutarClonadoRelacion(){
    const id_condominio = parseInt($('input[name="id_condominio"]').val()||'0',10);
    const anio          = parseInt($('input[name="anio"]').val()||'0',10);
    const mes           = parseInt($('select[name="mes"]').val()||'-1',10);
    if (!id_condominio || !anio || mes<0){ Swal.fire('Error','Faltan datos de período o condominio.','error'); return; }

    $.ajax({
      url:'presupuesto_detalles_por_periodo.php',
      type:'POST', dataType:'json',
      data:{ id_condominio, anio, mes },
      beforeSend: ()=>Swal.showLoading(),
      success:function(resp){
        Swal.close();
        if (resp.status!=='ok'){ Swal.fire('Aviso', resp.message||'No se pudo clonar.','warning'); return; }
        const tbody = document.querySelector('#tablaDetalleMaster tbody');
        tbody.innerHTML = '';

        resp.detalles.forEach(item=>{
          const $tr = $(addFilaMaster(true));
          const tipoFila = (item.tipo_movimiento==='egreso') ? 'egreso' : 'ingreso';
          $tr.find('select[name="tipo_movimiento[]"]').val(tipoFila);
          loadCuentasForRow($tr[0], tipoFila, String(item.id_plan_cuenta));
          $tr.find('input[name="descripcion[]"]').val(item.descripcion||'');
          $tr.find('input[name="monto[]"]').val('0');
        });

        calcTotalMaster();
        Swal.fire('Listo','Se copiaron las partidas del Presupuesto con monto 0.','success');
      },
      error:function(xhr){ Swal.close(); Swal.fire('Error', 'No se pudo clonar: '+(xhr.responseText||''), 'error'); }
    });
  }

  window.clonarMesAnterior = function(){
    const tipo = parseInt($('#id_tipo').val()||'1',10);
    if (tipo!==1){ Swal.fire('Aviso','Esta función aplica a Presupuesto (tipo 1).','info'); return; }
    const anio = parseInt($('input[name="anio"]').val()||'0',10);
    const mes  = parseInt($('select[name="mes"]').val()||'-1',10);
    if (mes<=0){ Swal.fire('Aviso','No hay mes anterior para “Extraordinaria”.','info'); return; }

    let prevMes = mes-1, prevAnio = anio;
    if (prevMes===0){ prevMes=12; prevAnio=anio-1; }

    const filas = document.querySelectorAll('#tablaDetalleMaster tbody tr');
    const continuar = () => ejecutarClonadoMesAnterior(prevAnio, prevMes);
    if (filas.length>0){
      Swal.fire({
        title:'Reemplazar detalle',
        text:`Se reemplazarán las filas por las del Presupuesto de ${prevMes}/${prevAnio}. ¿Continuar?`,
        icon:'warning', showCancelButton:true,
        confirmButtonText:'Sí, reemplazar', cancelButtonText:'Cancelar'
      }).then(r=>{ if(r.isConfirmed) continuar(); });
    } else continuar();
  };

  function ejecutarClonadoMesAnterior(prevAnio, prevMes){
    const id_condominio = parseInt($('input[name="id_condominio"]').val()||'0',10);
    $.ajax({
      url:'presupuesto_detalles_por_periodo.php',
      type:'POST', dataType:'json',
      data:{ id_condominio, anio:prevAnio, mes:prevMes },
      beforeSend: ()=>Swal.showLoading(),
      success:function(resp){
        Swal.close();
        if (resp.status!=='ok'){ Swal.fire('Aviso','No hay Presupuesto del mes anterior.','info'); return; }
        const tbody = document.querySelector('#tablaDetalleMaster tbody');
        tbody.innerHTML = '';

        resp.detalles.forEach(item=>{
          const $tr = $(addFilaMaster(true));
          $tr.find('select[name="tipo_movimiento[]"]').val('ingreso');
          loadCuentasForRow($tr[0], 'ingreso', String(item.id_plan_cuenta));
          $tr.find('input[name="descripcion[]"]').val(item.descripcion||'');
          $tr.find('input[name="monto[]"]').val(
            (item.monto!==undefined && item.monto!==null) ? item.monto : ''
          );
        });

        calcTotalMaster();
        Swal.fire('Listo','Se clonaron las partidas del mes anterior.','success');
      },
      error:function(xhr){ Swal.close(); Swal.fire('Error', 'No se pudo clonar el mes anterior: '+(xhr.responseText||''), 'error'); }
    });
  }

  $('#mes').on('change', updateDescripcion);
  $('#id_tipo').on('change', function(){ updateDescripcion(); toggleBtnClonar(); });

  $('#formCargarCobroMaster').on('submit', function(e){
    e.preventDefault();
    const idNot = $('#id_notificacion_master').val();
    const url   = idNot ? 'notificacion_cobro_master_update.php' : 'notificacion_cobro_master_insert.php';
    const fd    = new FormData(this);

    fd.set('id_condominio', $('input[name="id_condominio"]').val());
    const im = $('input[name="id_moneda"]').val();
    if (im !== '') fd.set('id_moneda', im);

    $.ajax({
      url:url, type:'POST', data:fd, processData:false, contentType:false,
      success:function(resp){
        let r = (typeof resp==='string') ? (function(){ try{return JSON.parse(resp);}catch(e){return null;} })() : resp;
        if (r && r.status==='ok'){
          Swal.fire('Guardado', 'Notificación ' + (idNot ? 'actualizada' : 'registrada') + ' (ID: '+r.id+')', 'success');
          $('#modalCargarCobroMaster').modal('hide');
          $('#tablaMasters').DataTable().ajax.reload(null,false);
        } else {
          Swal.fire('Error', (r&&r.message)||'No se pudo guardar', 'error');
        }
      },
      error:function(xhr){ Swal.fire('Error', xhr.responseText || 'No se pudo guardar', 'error'); }
    });
  });

});
</script>
</body>
</html>
