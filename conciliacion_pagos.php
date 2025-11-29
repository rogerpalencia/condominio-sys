<?php
// conciliacion_pagos.php
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

/* ====== SOLO ESTO ES CRÍTICO: obtener id_condominio de forma robusta ====== */
$id_condominio = 0;
if (isset($_SESSION['id_condominio'])) {
    $id_condominio = (int)$_SESSION['id_condominio'];
} elseif (isset($_POST['id_condominio']) && $_POST['id_condominio'] !== '') {
    $id_condominio = (int)$_POST['id_condominio'];
} elseif (isset($_GET['id_condominio'])) {
    $id_condominio = (int)$_GET['id_condominio'];
}
if ($id_condominio <= 0) {
    die('No se pudo determinar el condominio activo.');
}
/* ========================================================================== */

// (Opcional: mostrar nombre/moneda; NO afecta a la consulta del listado)
$row_condo = ['nombre' => 'Condominio', 'moneda_base' => ''];
try {
    $sql = "SELECT c.nombre, m.codigo AS moneda_base
            FROM condominio c
            LEFT JOIN moneda m ON c.id_moneda = m.id_moneda
            WHERE c.id_condominio = :cid
            LIMIT 1";
    $st = $conn->prepare($sql);
    $st->execute([':cid' => $id_condominio]);
    if ($tmp = $st->fetch(PDO::FETCH_ASSOC)) {
        $row_condo = $tmp;
    }
} catch (Throwable $e) {
    // seguir sin bloquear
}
?>

<head>
    <title>Conciliación de Pagos | <?php echo NOMBREAPP ?></title>
    <?php include 'layouts/head.php'; ?>
    <link href="assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css" rel="stylesheet" />
    <link href="assets/libs/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css" rel="stylesheet" />
    <link href="assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css" rel="stylesheet" />
    <?php include 'layouts/head-style.php'; ?>
</head>

<?php include 'layouts/body.php'; ?>

<!-- Variables ocultas (las usa el JS) -->
<input type="hidden" id="parserJsn" value="<?= PARSERJSN ?>">
<input type="hidden" id="id_condominio_" value="<?= (int)$id_condominio ?>">
<input type="hidden" id="moneda_base_" value="<?= htmlspecialchars($row_condo['moneda_base'] ?? '') ?>">

<div id="layout-wrapper">
    <?php include 'layouts/menu.php'; ?>

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">



  <div class="row align-items-center mb-3">
    <div class="col-12 col-md-6">
                <h1 class="display-6 mb-0">Conciliación de Pagos</h1>
                <p class="text-muted mb-0"><?= htmlspecialchars($row_condo['nombre']) ?></p>
    </div>


    <div class="col-12 col-md-6 text-md-end mt-2 mt-md-0">

      <button class="btn btn-primary" type="button" onclick="window.history.back()">  <i class="mdi mdi-arrow-left"></i> Volver      </button>
    </div>


    
  </div>



          

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <table id="tablaConciliacion" class="table table-bordered dt-responsive nowrap w-100">
                                    <thead>
                                        <tr>
                                            <th>Número Recibo</th>
                                            <th>Fecha Emisión</th>
                                            <th>Responsable</th>
                                            <th>Inmueble</th>
                                            <th>Monto Pagado</th>
                                            <th>Observaciones</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
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
    const monedaBase = $('#moneda_base_').val() || '';
    const id_condominio = $('#id_condominio_').val();
    const id_usuario = <?= isset($_SESSION['userid']) ? (int)$_SESSION['userid'] : 0 ?>;

    // Validaciones básicas
    if (!id_condominio || parseInt(id_condominio, 10) <= 0) {
        alert('No se pudo obtener el ID del condominio. Recarga la página.');
        return;
    }
    if (!id_usuario || id_usuario <= 0) {
        alert('No se pudo obtener el ID del usuario. Recarga la página.');
        return;
    }

    const tabla = $('#tablaConciliacion').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: 'conciliacion_pagos_data.php',
            type: 'POST',
            data: function (d) {
                d.id_condominio = id_condominio; // << SOLO ESTO ES CLAVE
            }
        },
        columns: [
            { data: 'numero_recibo' },
            { data: 'fecha_emision' },
            { data: 'propietario',
              render: function (data, type, row) {
                return `<small>Propietario:</small> &nbsp;<span class="badge bg-info">${row.propietario}</span><br>` +
                       `<small>Usuario:</small> &nbsp;<span class="badge bg-secondary">${row.usuario}</span>`;
              }
            },
            { data: 'inmueble' },
            {
                data: 'monto_total',
                render: function (data) {
                    const num = parseFloat(data || 0);
                    return num.toLocaleString('es', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + (monedaBase ? ' ' + monedaBase : '');
                }
            },
            { data: 'observaciones', defaultContent: '' },
            {
                data: 'id_recibo',
                render: function (data) {
                    const id = parseInt(data, 10);
                    if (!id || isNaN(id)) return '<span class="text-danger">ID inválido</span>';
                    return `<button class="btn btn-sm btn-primary btn-revisar" data-id="${id}"><i class="fas fa-eye"></i> Revisar</button>`;
                }
            }
        ],
        language: { url: "Spanish.json" },
        order: [[0, "desc"]]
    });

    // Abrir modal (tu flujo actual)
    $('#tablaConciliacion tbody').on('click', '.btn-revisar', function () {
        const id_recibo = parseInt($(this).data('id'), 10);
        if (!id_recibo) return;

        $.ajax({
            url: 'modal_conciliacion.php',
            type: 'POST',
            data: { id_recibo: id_recibo, id_condominio: parseInt(id_condominio, 10), id_usuario: id_usuario },
            dataType: 'html',
            cache: false,
            success: function (response) {
                if (!$('#modalConciliacion').length) {
                    $('body').append(response);
                } else {
                    $('#modalConciliacion').replaceWith(response);
                }
                $('#modalConciliacion').modal('show');
            },
            error: function (xhr) {
                alert('No se pudo cargar el modal: ' + (xhr.responseText || ''));
            }
        });
    });
});

function volver() { window.history.back(); }
</script>
