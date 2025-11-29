<?php 
// master_inmuebles.php  
@session_start();
include 'layouts/session.php';
include 'layouts/head-main.php';
require_once("core/funciones.php");
require_once("layouts/vars.php");
require_once("core/PDO.class.php");

// Verificar si el usuario está autenticado
if (!isset($_SESSION['userid'])) {
    header("Location: login.php");
    exit;
}

$func   = new Funciones();
$conn   = DB::getInstance();
$userid = (int)$_SESSION['userid'];

// ---------------------------------------------
// Resolver id_condominio desde la SESIÓN
// ---------------------------------------------
$id_condominio_sesion = isset($_SESSION['id_condominio']) ? (int)$_SESSION['id_condominio'] : 0;
if ($id_condominio_sesion <= 0) {
    die("No hay condominio seleccionado. Vuelve al panel y selecciona un condominio.");
}

// (Opcional solo para mostrar el nombre en la cabecera; NO afecta al WHERE de la grilla)
$nombre_condo = 'Condominio';
try {
  $st = $conn->prepare("SELECT nombre FROM condominio WHERE id_condominio = :c LIMIT 1");
  $st->execute([':c'=>$id_condominio_sesion]);
  if ($r = $st->fetch(PDO::FETCH_ASSOC)) $nombre_condo = $r['nombre'];
} catch (Throwable $e) {}
// ---------------------------------------------
// Validar acceso (rol ∪ admin) y obtener nombre
// ---------------------------------------------
$sql = "
    SELECT c.id_condominio, c.nombre
    FROM public.condominio c
    WHERE c.id_condominio = :cid
      AND EXISTS (
          SELECT 1 
          FROM \"menu_login\".usuario_rol ur
          WHERE ur.id_usuario = :uid AND ur.id_condominio = c.id_condominio
      )
    UNION
    SELECT c.id_condominio, c.nombre
    FROM public.condominio c
    WHERE c.id_condominio = :cid
      AND EXISTS (
          SELECT 1 
          FROM public.administradores a
          WHERE a.id_usuario = :uid AND a.id_condominio = c.id_condominio AND COALESCE(a.estatus,true)=true
      )
    LIMIT 1
";
$stmt = $conn->prepare($sql);
$stmt->bindValue(':cid', $id_condominio_sesion, PDO::PARAM_INT);
$stmt->bindValue(':uid', $userid, PDO::PARAM_INT);
$stmt->execute();
$row_condo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row_condo) {
    unset($_SESSION['id_condominio']);
    die("No autorizado para el condominio seleccionado. Vuelve al panel y elige otro.");
}
?>
<head>
  <title>Inmuebles | <?php echo NOMBREAPP ?></title>
  <?php include 'layouts/head.php'; ?>
  <link href="assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css" rel="stylesheet" />
  <link href="assets/libs/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css" rel="stylesheet" />
  <link href="assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css" rel="stylesheet" />
  <?php include 'layouts/head-style.php'; ?>
  <style>
    .card { border-radius: 8px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1); }
    .table th, .table td { padding: 0.75rem; vertical-align: middle; font-size: 0.9rem; }
    .btn-action { min-width: 90px; margin-right: 0.25rem; }
    .financial-summary { font-size: 0.85rem; }
    .badge-debt { background-color: #dc3545; margin-right: 0.25rem; }
    .badge-credit { background-color: #28a745; margin-right: 0.25rem; }
    .type-column { font-size: 0.9rem; }
    .type-column small { display: block; color: rgb(78, 84, 90); }
    .fs-label { font-size: .8rem; font-weight: 600; color: #6c757d; }
    .fs-group + .fs-group { margin-top: .25rem; }
    .card-header .row { width: 100%; }
    .identificacion-cell .fw-semibold { font-weight: 600; }
    .identificacion-cell small .badge { vertical-align: middle; cursor: pointer; }
  </style>
</head>

<?php include 'layouts/body.php'; ?>

<!-- Variables ocultas -->
<input type="hidden" id="parserJsn" value="<?= PARSERJSN ?>">
<input type="hidden" id="id_condominio_" value="<?= (int)$row_condo['id_condominio'] ?>">

<div id="layout-wrapper">
  <?php include 'layouts/menu.php'; ?>

  <div class="main-content">
    <div class="page-content">
      <div class="container-fluid">

        <div class="row align-items-center mb-3">
          <div class="col-12 col-md-6">
            <h1 class="display-6 mb-0">Master de Inmuebles</h1>
            <p class="text-muted mb-0"><?= htmlspecialchars($nombre_condo) ?></p>
          </div>
          <div class="col-12 col-md-6 text-md-end mt-2 mt-md-0">
            <button class="btn btn-primary" type="button" onclick="window.history.back()">
              <i class="mdi mdi-arrow-left"></i> Volver
            </button>
            <button type="button" class="btn btn-success me-1" onclick="mostrarModalNuevoInmueble()">
              <i class="mdi mdi-home-plus-outline"></i> Nuevo Inmueble
            </button>
          </div>
        </div>

        <div class="card">
          <div class="card-body">
            <div class="table-responsive">
              <table id="tablaInmuebles" class="table table-striped table-bordered table-hover dt-responsive nowrap w-100">
                <thead class="table-light">
                  <tr>
                    <th>ID</th>
                    <th># Item</th>
                    <th>Identificación</th>
                    <th>Tipo</th>
                    <th>Resumen Financiero</th>
                    <th>Acciones</th>
                  </tr>
                </thead>
              </table>
            </div>
          </div>
        </div>

      </div>
    </div>
    <?php include 'layouts/footer.php'; ?>
  </div>
</div>

<?php include 'layouts/right-sidebar.php'; ?>
<?php include 'layouts/vendor-scripts.php'; ?>

<script src="assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
<script src="assets/libs/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js"></script>
<script src="assets/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/app.js"></script>

<script>
$(document).ready(function () {
  const idCondo = $('#id_condominio_').val();
  let targetInmuebleForOwner = null; // para saber a qué inmueble asociar cuando se pulse "Incluir"

  const tabla = $('#tablaInmuebles').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: 'master_inmuebles_data.php',
      type: 'POST',
      data: function (d) { d.id_condominio = idCondo; }
    },
    columns: [
      { data: 'id_inmueble', visible: false },
      {
        data: null,
        width: "60px",
        render: function (data, type, row, meta) {
          let html = '';
          const rowIndex = meta.row;
          const totalRows = meta.settings.fnRecordsDisplay();
          if (rowIndex > 0) html += `<a href="#" class="move-up me-1" data-id="${row.id_inmueble}" data-correlativo="${row.correlativo}" title="Subir"><i class="fas fa-arrow-up"></i></a>`;
          if (rowIndex < totalRows - 1) html += `<a href="#" class="move-down" data-id="${row.id_inmueble}" data-correlativo="${row.correlativo}" title="Bajar"><i class="fas fa-arrow-down"></i></a>`;
          return html;
        }
      },
      {
        data: null,
        render: function (data, type, row) {
          const ident = row.identificacion || '';
          let ownerHtml = '';
          if (row.propietario && row.propietario.id) {
            const nom = row.propietario.nombre || '';
            ownerHtml = `
              <div class="mt-1">
                <small>
                  Propietario: <span class="fw-semibold">${nom}</span>
                  <a href="#" class="badge bg-secondary ms-2 btn-prop-modificar"
                     data-inmueble="${row.id_inmueble}" data-propietario="${row.propietario.id}">
                    Modificar
                  </a>
                </small>
              </div>`;
          } else {
            ownerHtml = `
              <div class="mt-1">
                <small class="text-muted">No tiene propietario asignado</small>
                <a href="#" class="badge bg-success ms-2 btn-prop-incluir"
                   data-inmueble="${row.id_inmueble}">
                  Incluir
                </a>
              </div>`;
          }
          return `<div class="identificacion-cell"><div class="fw-semibold">${ident}</div>${ownerHtml}</div>`;
        }
      },
      {
        data: 'tipo_inmueble',
        render: function (data, type, row) {
          let tipoText;
          switch (String(data)) {
            case '1': tipoText = 'Apartamento'; break;
            case '2': tipoText = 'Casa'; break;
            case '3': tipoText = 'Local'; break;
            default:  tipoText = 'Otro'; break;
          }
          const alicuota = (row.alicuota !== null && row.alicuota !== undefined && row.alicuota !== '')
                           ? parseFloat(row.alicuota).toFixed(4) : '0.0000';
          return `<div class="type-column"><small>Tipo: ${tipoText}<br>Alicuota: ${alicuota} %</small></div>`;
        }
      },
      {
        data: null,
        render: function (data, type, row) {
          // Prepara arreglos de badges por moneda
          const deudas = [];
          const creditos = [];

          if (row.deudas && row.deudas.length > 0) {
            row.deudas.forEach(d => {
              const pendiente = parseFloat(d.total_pendiente || 0);
              if (pendiente > 0) {
                deudas.push(`<span class="badge badge-debt" data-bs-toggle="tooltip" data-bs-placement="top" title="Deuda pendiente en ${d.moneda}">${d.moneda}: ${pendiente.toFixed(2)}</span>`);
              }
            });
          }
          if (deudas.length === 0) deudas.push('<span class="badge bg-success">Solvente</span>');

          if (row.creditos && row.creditos.length > 0) {
            row.creditos.forEach(c => {
              const totalCred = parseFloat(c.total_credito || 0);
              if (totalCred > 0) {
                creditos.push(`<span class="badge badge-credit" data-bs-toggle="tooltip" data-bs-placement="top" title="Crédito a favor en ${c.moneda}">${c.moneda}: ${totalCred.toFixed(2)}</span>`);
              }
            });
          }
          if (creditos.length === 0) creditos.push('<span class="badge bg-info">Sin créditos</span>');

          return `
            <div class="financial-summary container-fluid px-0">
              <div class="row g-1">
                <div class="col-12 col-md-6">
                  <div class="fs-label mb-1">Deuda:</div>
                  <div class="d-flex flex-column">
                    ${deudas.map(b => `<div class="fs-group">${b}</div>`).join('')}
                  </div>
                </div>
                <div class="col-12 col-md-6">
                  <div class="fs-label mb-1">Crédito:</div>
                  <div class="d-flex flex-column">
                    ${creditos.map(b => `<div class="fs-group">${b}</div>`).join('')}
                  </div>
                </div>
              </div>
            </div>
          `;
        }
      },
      {
        data: 'id_inmueble',
        render: function (data, type, row) {
          const editarBtn      = `<button class="btn btn-sm btn-primary btn-action editar-inmueble" data-id="${row.id_inmueble}" data-identificacion="${row.identificacion}"><i class="fas fa-edit"></i> Editar</button>`;
          const cargarCobroBtn = `<button class="btn btn-sm btn-primary btn-action cargar-cobro" data-id="${row.id_inmueble}" data-identificacion="${row.identificacion}"><i class="fas fa-dollar-sign"></i> Cargar Cobro</button>`;
          const cargarPagoBtn  = `<button class="btn btn-sm btn-primary btn-action cargar-pago" data-id="${row.id_inmueble}" data-identificacion="${row.identificacion}"><i class="fas fa-dollar-sign"></i> Cargar Pago</button>`;
          const cxcBtn         = `<button class="btn btn-sm btn-primary btn-action cxc_inmueble" data-id="${row.id_inmueble}" data-identificacion="${row.identificacion}"><i class="fas fa-file-invoice-dollar"></i> CxC Inmueble</button>`;

          let eliminarBtn = '';
          if (row.puede_borrar === true) {
            eliminarBtn = `<button class="btn btn-danger btn-sm btn-action borrar-inmueble" data-id="${row.id_inmueble}">Borrar<br>Inmueble</button>`;
          } else {
            eliminarBtn = `<span class="text-muted"></span>`;
          }

          return editarBtn + ' ' + cargarCobroBtn + ' ' + cargarPagoBtn + ' ' + cxcBtn + ' ' + eliminarBtn;
        }
      }
    ],
    language: { url: "Spanish.json" },
    order: [[1, "asc"]],
    responsive: true,
    autoWidth: false,
    createdRow: function (row, data, dataIndex) {
      $(row).find('[data-bs-toggle="tooltip"]').tooltip();
    }
  });

  /* ---------- BOTONES Incluir/Modificar Propietario ---------- */
$('#tablaInmuebles').on('click', '.btn-prop-incluir', function(e){
  e.preventDefault();
  const idInmueble = $(this).data('inmueble');
  openPropietarioModalNuevo($('#id_condominio_').val(), { linkInmueble: idInmueble });
});

$('#tablaInmuebles').on('click', '.btn-prop-modificar', function(e){
  e.preventDefault();
  const idProp      = $(this).data('propietario');
  const idInmueble  = $(this).data('inmueble'); // <--- tomar el contexto
  openPropietarioModalEditar(idProp, $('#id_condominio_').val(), { linkInmueble: idInmueble }); // <--- pasarlo al modal
});


  // Cuando el modal guarda, si venía de "Incluir", crear/actualizar asociación propietario_inmueble
  $(document).on('propietario:saved', function(e, payload){
    if (targetInmuebleForOwner) {
      const idProp = payload && payload.id_propietario ? payload.id_propietario : null;
      if (!idProp) {
        Swal.fire('Atención','No se obtuvo el id del propietario creado.','warning');
        return;
      }
      $.post('propietario_inmueble_upsert.php', {
        id_inmueble: targetInmuebleForOwner,
        id_propietario: idProp,
        id_condominio: idCondo
      }, function(resp){
        if (resp && resp.status==='ok'){
          Swal.fire('Vinculado','Propietario asociado al inmueble.','success');
          tabla.ajax.reload(null,false);
        } else {
          Swal.fire('Error', (resp && resp.message) || 'No se pudo asociar', 'error');
        }
      }, 'json').always(function(){ targetInmuebleForOwner = null; });
    } else {
      // Solo se editaron datos del propietario
      tabla.ajax.reload(null,false);
    }
  });

  /* ---------- CARGAR COBRO ---------- */
  $('#tablaInmuebles tbody').on('click', '.cargar-cobro', function () {
    const id = $(this).data('id');
    const identificacion = $(this).data('identificacion');
    $('#id_inmueble_cobro').val(id);
    $('#identificacion_cobro').text(identificacion);
    $('#modalCargarCobro').modal('show');
  });

  /* ---------- CARGAR PAGO ---------- */
  $('#tablaInmuebles tbody').on('click', '.cargar-pago', function () {
    const id = $(this).data('id');
    const identificacion = $(this).data('identificacion');
    const $form = $('<form>', { action: 'cargar_pagos.php', method: 'POST' })
      .append($('<input>', { type: 'hidden', name: 'id_inmueble', value: id }))
      .append($('<input>', { type: 'hidden', name: 'identificacion', value: identificacion }))
      .append($('<input>', { type: 'hidden', name: 'id_condominio', value: idCondo }));
    $('body').append($form);
    $form.trigger('submit');
  });

  /* ---------- REORDENAR ---------- */
  $('#tablaInmuebles tbody').on('click', '.move-up, .move-down', function (e) {
    e.preventDefault();
    const id = $(this).data('id');
    const correlativo = $(this).data('correlativo');
    const direction = $(this).hasClass('move-up') ? 'up' : 'down';
    $.post('reorder_inmueble.php', { id_inmueble:id, correlativo:correlativo, direction:direction, id_condominio:idCondo }, function (response) {
      if (response.status === 'ok') tabla.ajax.reload();
      else alert("Error: " + response.message);
    }, 'json');
  });

  /* ---------- EDITAR INMUEBLE ---------- */
  $('#tablaInmuebles tbody').on('click', '.editar-inmueble', function () {
    const id = $(this).data('id');
    $.post('inmueble_get.php', { id_inmueble: id }, function (data) {
      $('#nuevoInmuebleLabel').text('Editar Datos de Inmueble');
      const form = $('#formNuevoInmueble');
      form.find('input[name=id_condominio]').val(data.id_condominio);
      form.find('input[name=identificacion]').val(data.identificacion);
      form.find('select[name=tipo]').val(data.tipo);
      actualizarCamposPorTipo(data.tipo);
      form.find('input[name=torre]').val(data.torre);
      form.find('input[name=piso]').val(data.piso);
      form.find('input[name=calle]').val(data.calle);
      form.find('input[name=manzana]').val(data.manzana);
      form.find('input[name=alicuota]').val((data.alicuota ?? '').toString().replace('.', ','));
      form.find('input[name=id_inmueble]').remove();
      form.append(`<input type="hidden" name="id_inmueble" value="${data.id_inmueble}">`);
      $('#modalNuevoInmueble').modal('show');
    }, 'json');
  });

  /* ---------- CxC INMUEBLE ---------- */
  $('#tablaInmuebles tbody').on('click', '.cxc_inmueble', function () {
    const id = $(this).data('id');
    const $form = $('<form>', { action: 'cxc_inmueble.php', method: 'POST' })
      .append($('<input>', { type: 'hidden', name: 'id_inmueble', value: id }))
      .append($('<input>', { type: 'hidden', name: 'id_condominio', value: idCondo }));
    $('body').append($form);
    $form.trigger('submit');
  });

  /* ---------- ELIMINAR ---------- */
  $('#tablaInmuebles tbody').on('click', '.borrar-inmueble', function () {
    const id = $(this).data('id');
    Swal.fire({
      title: '¿Estás seguro de Borrar el Registro?',
      text: "No podrás revertir esta acción.",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Sí, eliminar',
      cancelButtonText: 'Cancelar'
    }).then((result) => {
      if (result.isConfirmed) {
        $.post('inmueble_delete.php', { id_inmueble: id }, function (resp) {
          if (resp.status === 'ok') {
            Swal.fire('Eliminado', 'El inmueble ha sido eliminado.', 'success');
            tabla.ajax.reload();
          } else {
            Swal.fire('Error', '❌ Error: ' + resp.message, 'error');
          }
        }, 'json');
      }
    });
  });
});

/* ---------- NUEVO ---------- */
function mostrarModalNuevoInmueble() {
  $('#nuevoInmuebleLabel').text('Nuevo Inmueble');
  $('#formNuevoInmueble')[0].reset();
  actualizarCamposPorTipo($('#tipo').val());
  $('#formNuevoInmueble input[name=id_inmueble]').remove();
  $('#modalNuevoInmueble').modal('show');
}

function volver() { window.history.back(); }
</script>

<?php 
include 'modal_inmueble.php';
include 'modal_cobro.php';
include 'propietario_modal.php'; // modal reutilizable (nuevo/editar propietario)
?>
