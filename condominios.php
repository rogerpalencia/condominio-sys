<?php 
@session_start();
include 'layouts/session.php';
include 'layouts/head-main.php'; 
require_once("core/funciones.php"); 
$func = new Funciones();
require_once("layouts/vars.php"); 
$userid = $_SESSION['userid'];
require_once("core/PDO.class.php") ; 
$conn=  DB::getInstance();
$id_usuario = $_SESSION['userid'] ;

$sql="SELECT
	administradores.id_condominio, 
	condominio.nombre
FROM
	administradores
	INNER JOIN
	condominio
	ON 
		administradores.id_condominio = condominio.id_condominio
WHERE
	id_usuario = :id_usuario AND
	estatus = true ";

$stmt_condo = $conn->prepare($sql);
$stmt_condo->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
$stmt_condo->execute();
$row_condo = $stmt_condo->fetch();

if ($row_condo) {
    error_log("ID Condominio: " . $row_condo['id_condominio']);
    error_log("ID Condominio: " . $row_condo['nombre']);
} else {
    error_log("No se encontraron resultados.");
}


?>

<head>
    <title>Inmuebles | <?php echo NOMBREAPP ?></title>
    <?php include 'layouts/head.php'; ?>
    <link href="assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/libs/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css" rel="stylesheet" type="text/css" />
    <?php include 'layouts/head-style.php'; ?>
</head>

<?php include 'layouts/body.php'; ?>
<input type="hidden" name="parserJsn" id="parserJsn" value="<?php echo PARSERJSN ?>">

<div id="layout-wrapper">
<?php include 'layouts/menu.php'; ?>

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">


 
                    <div class="col-12">
                        <div class="row">

                            <!--h4 class="card-title">Buttons example</h4-->
                            <h1 class="display-6 mb-0">Master de Inmuebles</h1><br>
                            <h4 class="display-8 mb-0"><?php echo $row_condo['nombre'] ?> </h4>

                        </div>
                    </div>
             
               


                        <div class="card-header">
                            <p class="card-title-desc">
                            <button type="button" class="btn btn-primary waves-effect waves-light w-sm " onclick="volver()"title="Ver Contribuyentes"> Volver </button>
                            <button type="button" class="btn btn-success waves-effect waves-light w-sm" onclick="agregarInmueble()" title="Agregar Inmueble">Nuevo Inmueble</button>
                            </p>
                        </div>


               
     







            <div class="row">
                <div class="col-12">
                    <div class="card">

                        <div class="card-body">
                            <table id="tablaInmuebles" class="table table-bordered dt-responsive nowrap w-100">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th># Item</th>
                                        <th>Identificación</th>
                                        <th>Tipo</th>
                                        <th>Alicuota</th>
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
<script src="assets/js/app.js"></script>

<script>
$(document).ready(function() {
    var tabla = $('#tablaInmuebles').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "condominios_data.php",
            "type": "POST"
        },


        "columns": [
            { "data": "id_inmueble", "visible": false },
            { "data": "correlativo" },
            { "data": "identificacion" },
            {
                "data": "tipo_inmueble",
                "render": function(data, type, row) {
                    if (data == 1) {
                        return 'Apartamento';
                    } else if (data == 2) {
                        return 'Casa';
                    } else if (data == 3) {
                        return 'Local';



                    } else {
                        return 'Otro';
                    }
                }
            },
            { "data": "alicuota" },
            
            {
                "data": "id_inmueble",
                "render": function(data, type, row) {
                    return '<button class="btn btn-primary btn-sm" onclick="editarInmueble('+data+')">Editar</button>';
                }
            }
        ],
        "language": {
            "url": "Spanish.json"
        }
    });
});

function agregarInmueble() {
    window.location.href = 'inmueble_form.php';
}

function editarInmueble(id) {
    window.location.href = 'inmueble_form.php?id=' + id;
}
</script>

</body>
</html>


