<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php';

require_once("core/funciones.php");
$func = new Funciones();
require_once("layouts/vars.php");
$userid = $_SESSION['userid'];
?>

<head>

    <title>Registro de Empresas | <?php echo NOMBREAPP ?></title>
    <?php include 'layouts/head.php'; ?>

    <!-- DataTables -->
    <link href="assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/libs/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css" rel="stylesheet" type="text/css" />

    <!-- Responsive datatable examples -->
    <link href="assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css" rel="stylesheet" type="text/css" />

    <?php include 'layouts/head-style.php'; ?>

</head>

<?php include 'layouts/body.php'; ?>

<input type="hidden" name="parserJsn" id="parserJsn" value="<?php echo PARSERJSN ?> ">
<input type="hidden" name="mensaje" id="mensaje" value="<?php echo $mensaje ?> ">

<!-- Begin page -->
<div id="layout-wrapper">

    <?php include 'layouts/menu.php'; ?>

    <!-- ============================================================== -->
    <!-- Start right Content here -->
    <!-- ============================================================== -->
    <div class="main-content">

        <div class="page-content">
            <div class="container-fluid">

                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <!--h4 class="mb-sm-0 font-size-18">DataTables</h4-->

                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="javascript: void(0);">Objeto de contribución</a></li>
                                    <li class="breadcrumb-item active">Empresas</li>
                                </ol>
                            </div>

                        </div>
                    </div>
                </div>
                <!-- end page title -->

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <!--h4 class="card-title">Buttons example</h4-->
                                <p class="card-title-desc"><button type="button" class="btn btn-success waves-effect waves-light w-sm" onclick="nregistro()" title="Agregar Item">Nueva Empresa</button></p>
                            </div>
                            <div class="card-body">
                                <table id="mytable" class="table table-nowrap align-middle table-edits">
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th>Razón Social</th>
                                            <th>Licencia Actividad</th>
                                            <th>RIF</th>
                                            <th style="width:2px;"></th>
                                            <th style="width:2px;"></th>
                                            <th style="width:2px;"></th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                        <!-- end cardaa -->
                    </div> <!-- end col -->
                </div> <!-- end row -->
            </div> <!-- container-fluid -->
        </div>
        <!-- End Page-content -->

        <?php include 'layouts/footer.php'; ?>
    </div>
    <!-- end main content-->

</div>
<!-- END layout-wrapper -->

<!-- Right Sidebar -->
<?php include 'layouts/right-sidebar.php'; ?>
<!-- /Right-bar -->

<!-- JAVASCRIPT -->

<?php include 'layouts/vendor-scripts.php'; ?>

<!-- Required datatable js -->
<script src="assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>
<!-- Buttons examples -->
<script src="assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js"></script>
<script src="assets/libs/datatables.net-buttons-bs4/js/buttons.bootstrap4.min.js"></script>
<script src="assets/libs/jszip/jszip.min.js"></script>
<script src="assets/libs/pdfmake/build/pdfmake.min.js"></script>
<script src="assets/libs/pdfmake/build/vfs_fonts.js"></script>
<script src="assets/libs/datatables.net-buttons/js/buttons.html5.min.js"></script>
<script src="assets/libs/datatables.net-buttons/js/buttons.print.min.js"></script>
<script src="assets/libs/datatables.net-buttons/js/buttons.colVis.min.js"></script>

<!-- Responsive examples -->
<script src="assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
<script src="assets/libs/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js"></script>

<!-- Datatable init js -->
<script src="assets/js/pages/datatables.init.js"></script>

<script src="assets/js/app.js"></script>
<script src="assets/js/funciones.js"></script>

</body>

</html>


<script type="text/javascript">
    $(document).ready(function() {
        $('#mytable').DataTable({
            "columnDefs": [{
                    "targets": [0],
                    "visible": false,
                    "searchable": false
                },
                {
                    "targets": [4],
                    data: 'id_empresa',
                    "render": function(data, type, row, meta) {

                        return '<button type="button" onclick="edit(' + data + ')" class="btn btn-primary btn-sm waves-effect waves-light" title="Editar Item"><i class="fas fa-edit   font-size-8"></i></button>';
                    },
                    "searchable": false
                },
                {
                    "targets": [5],
                    data: 'id_empresa',
                    "render": function(data, type, row, meta) {
                        return '<button type="button" onclick="estimada(' + data + ')" class="btn btn-danger btn-sm waves-effect waves-light" title="Estimada"><i class="fas fa-chart-line font-size-8"></i>Estimada</button>';
                    },
                    "searchable": false
                },
                {
                    "targets": [6],
                    data: 'id_empresa',
                    "render": function(data, type, row, meta) {
                        return '<button type="button" onclick="anular(' + data + ')" class="btn btn-danger btn-sm waves-effect waves-light" title="Anular Item"><i class="fa fa-trash font-size-8"></i></button>';
                    },
                    "searchable": false
                }
            ],
            "language": {
                "url": "Spanish.json"
            },
            'processing': true,
            'serverSide': true,
            'serverMethod': 'post',
            'ajax': {
                'url': 'empresas_data.php'
            },
            'columns': [{
                    data: 'id_empresa'
                },
                {
                    data: 'razon_social'
                },
                {
                    data: 'rif'
                },
                {
                    data: 'licencia_actividad'
                }
            ],
        });
    });


    function nregistro() {
        window.location.href = "empresas_ac.php";
    }

    function edit(id) {
        confirmar('Editar Item ?', "", "empresas_ac.php?id=" + id);
    }

    function anular(id) {
        confirmar('Anular Item ? ', "", "empresas_rem.php?id=" + id);
    }

    function estimada(id) {
        alert("estimada");
        window.location.href = "estimadas/procedimiento_estimada.php?id=" + id;
    }
</script>