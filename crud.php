<?php
@session_start();
include 'layouts/session.php';
include 'layouts/head-main.php';
require_once("core/funciones.php");
require_once("layouts/vars.php");

$tabla = $_GET['tabla'] ?? 'condominios';
if (!$tabla) die("Debe indicar una tabla por GET: ?tabla=nombre");

$func = new Funciones();
$userid = $_SESSION['userid'];

$pdo = new PDO("pgsql:host=localhost;dbname=tu_db", "user", "clave");

// Obtener columnas
$stmt = $pdo->prepare("SELECT column_name FROM information_schema.columns WHERE table_name = :tabla");
$stmt->execute(['tabla' => $tabla]);
$columnas = $stmt->fetchAll(PDO::FETCH_COLUMN);

if (!$columnas) die("Tabla no encontrada o vacía");
?>

<head>
    <title><?= strtoupper($tabla) ?> | <?= NOMBREAPP ?></title>
    <?php include 'layouts/head.php'; ?>
    <link href="assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css" rel="stylesheet" />
    <link href="assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css" rel="stylesheet" />
    <?php include 'layouts/head-style.php'; ?>
</head>

<?php include 'layouts/body.php'; ?>

<div id="layout-wrapper">
    <?php include 'layouts/menu.php'; ?>

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <h4>Editor de tabla: <strong><?= $tabla ?></strong></h4>

                <table id="datatable" class="table table-bordered dt-responsive nowrap w-100">
                    <thead>
                        <tr>
                            <?php foreach ($columnas as $col): ?>
                                <th><?= $col ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                </table>
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
<script src="assets/js/app.js"></script>

<script>
const columnas = <?= json_encode($columnas) ?>;
const tabla = "<?= $tabla ?>";

$(document).ready(function () {
    const table = $('#datatable').DataTable({
        ajax: {
            url: 'get_tabla_data.php',
            type: 'POST',
            data: { tabla }
        },
        columns: columnas.map(c => ({ data: c })),
        createdRow: function (row) {
            $(row).find('td').addClass('editable');
        }
    });

    $('#datatable').on('click', 'td.editable', function () {
        const cell = table.cell(this);
        const col = columnas[cell.index().column];
        const row = table.row(this).data();
        const id = row.id;
        const valor = prompt("Editar " + col, cell.data());
        if (valor !== null) {
            $.post('update_tabla_campo.php', {
                tabla, id, campo: col, valor
            }, function (res) {
                if (res.status === 'ok') {
                    cell.data(valor).draw();
                } else {
                    alert("Error: " + res.error);
                }
            }, 'json');
        }
    });
});
</script>
</body>
</html>
