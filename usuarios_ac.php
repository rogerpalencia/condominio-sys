<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php';

$userid = $_SESSION['userid'];
require_once 'core/PDO.class.php';
$conn = DB::getInstance();
include 'core/config.php';
include 'core/funciones.php';
require_once 'core/funciones.php';
require_once 'layouts/vars.php';
$func = new Funciones();
include 'core/config.php';
$conn2 = pg_connect("host=$Servidor dbname=$BaseDeDatos user=$Usuario password=$Password");
if (!$conn) {
    die('Conexion Fallida');
    exit();
}





//////////////////comprobacion de seguridad
$userid = $_SESSION['userid'];
$nombre_archivo = basename($_SERVER['PHP_SELF']);
$sql = "SELECT prog.nombre FROM detalles_perfiles AS per INNER JOIN  programas AS prog  ON per.id_programa = prog.id_programa
WHERE prog.accion = 'usuarios.php' and  per.id_perfil= '$userid' ";
$result = pg_query($conn2, $sql);
$num_rows = pg_num_rows($result);

if ($num_rows == 0) {
    echo "<script>window.location.replace('error_grave.php');</script>";

    exit();
}
/////////////////////////


$user_id_cam = $_GET['id'];



$sql = 'SELECT id_perfil, nombre FROM perfiles ORDER BY id_perfil';
$rss = $conn->query($sql);





if ((isset($_GET['id'])) && (intval($_GET['id']) !== 0)) {
    $id_usuario = $_GET['id'];
    $sql = "select * from usuarios where id_usuario = '$id_usuario'";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $row = $stmt->fetch();
    $escritorio = $row['escritorio'] ?? null;
    $nombre = $row['nombre'] ?? null;
    $perfil = $row['perfil'] ?? null;
    $rif = $row['rif'] ?? null;
    $clave = $row['clave'] ?? null;
}
?>

<head>

    <title>Usuarios | SEMAT-PC</title>
    <?php include 'layouts/head.php'; ?>

    <!-- DataTables -->
    <link href="assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/libs/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/libs/choices.js/public/assets/styles/choices.min.css" rel="stylesheet" type="text/css" />

    <!-- Responsive datatable examples -->
    <link href="assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css" rel="stylesheet" type="text/css" />

    <?php include 'layouts/head-style.php'; ?>


    <style>
        table.dataTable tbody th,
        table.dataTable tbody td {
            padding: 0px 0px
        }

        ;

        .centrar-verticalmente {
            display: flex;
            align-items: center;
            margin-top: 10px;
        }
    </style>


</head>



<?php include 'layouts/body.php'; ?>

<!-- Begin page -->
<input type="hidden" name="parserJsn" id="parserJsn" value="<?php echo PARSERJSN; ?>">
<input type="hidden" name="id_usuario_cam" id="id_usuario_cam" value="<?php echo $user_id_cam; ?>">
<input type="hidden" name="id_usuario" id="id_usuario" value="<?php echo $id_usuario; ?>">
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
                            <h4 class="mb-sm-0 font-size-12">Registro de usuarios </h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="javascript: void(0);">Gestión</a></li>
                                    <li class="breadcrumb-item active">Usuarios</li>
                                </ol>
                            </div>


                        </div>
                    </div>
                </div>
                <!-- end page title -->

                <a href="javascript: void(0);" class="btn btn-primary" onclick="regresar()"><i class="bx bx-chevron-left me-1"></i>Ir al Listado</a>

                <div class="card-body">

                    <div class="row">
                        <div class="col-8">
                            <!-- Tabla DataTable aquí -->

                            <div class="card-body">

                                <table id="mytable" class="table w-100 align-middle table-edits">
                                    <thead>
                                        <tr>
                                            <th>Id_Programa</th>
                                            <th>Acción</th>
                                            <th>Programa</th>
                                            <th>Módulo</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>

                        <div class="col-4">
                            <!-- Formulario aquí -->

                            <div>
                                <form id="pristine-valid-common" novalidate method="post">
                                    <input type="hidden" />
                                    <div class="row">
                                        <div class="col-12">

                                        </div>
                                        <div class="col-12">
                                            <div class="form-group mb-2 mt-">
                                                <label>Id de Usuario:</label>
                                                <input type="text" id='nombrecompleto' name='nombrecompleto' style="text-transform: uppercase" required data-pristine-required-message="Indique Nombre" class="form-control" value="<?php echo $nombre; ?>" pattern="[ a-ZA-ZáéíóúÁÉÍÓÚ]" />
                                            </div>
                                        </div>
                                        <div class="row">
                                        <div class="col-md-9">
                                        <div class="form-group mb-2">
    <label for="clave">Clave:</label>
    <div class="input-group">
    <input type="password" id="clave" name="clave" required data-pristine-required-message="Indique la Nueva Clave" class="form-control" pattern="[a-zA-Z0-9@$]{6,8}" />
    <div class="input-group-append">
            <span class="input-group-text w-100" style="height: 100%;">
                <i class="fas fa-eye" id="togglePassword" style="cursor: pointer;"></i>
            </span>
        </div>
    </div>
</div>
                                            </div>





                                            
                                            
                                            <div class="col-md-3 d-flex align-items-end mb-2">
                                                <button type="button" onclick="cambiarClave()" class="btn btn-primary w-100">Cambiar</button>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-9">
                                                <div class="form-group mb-2">
                                                    <label for="escritorio">Escritorio:</label>
                                                    <input type="text" id="escritorio" name="escritorio" pattern="[0-9]*" style="text-transform: uppercase" required data-pristine-required-message="Indique RIF" class="form-control" value="<?php echo $escritorio; ?>" />
                                                </div>
                                            </div>

                                            <div class="col-md-3 d-flex align-items-end mb-2">
                                                <button type="button" onclick="graba_escritorio()" class="btn btn-primary w-100">Cambiar</button>
                                            </div>
                                        </div>



                 
                                    </div>
                                    <!-- end row -->
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- end card -->
</div>










<!-- end row -->
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
<script src="assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
<script src="assets/libs/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js"></script>

<!-- Datatable init js -->
<script src="assets/js/pages/datatables.init.js"></script>

<script src="assets/js/app.js"></script>
<script src="assets/js/funciones.js"></script>


</body>

</html>

<script>
    function regresar() {
        window.location = "usuarios.php";
    }



    $(document).ready(function() {
        var idx = $("#id_usuario").val();

        $('#mytable').DataTable({

            lengthMenu: [
                [50, -1],
                [50, 'Todo'],
            ],
            "columnDefs": [{

                "targets": [0],
                "visible": false,
                "searchable": false,

            }],
            "language": {
                "url": "Spanish.json"
            },
            'processing': true,
            'serverSide': true,
            'serverMethod': 'post',
            'ajax': {
                'url': 'programas_data.php',
                data: {
                    idx: idx

                },

            },

            'columns': [{
                    data: 'id_programa'
                },
                {
                    data: null,
                    render: function(data, type, row) {
                        return '<div class="centrar-verticalmente">' +
                            '<input data-id="' + row.id_programa + '" type="checkbox" id="' + row.id_programa + '" switch="none" class="check-activar"' + row.tildado + '/> ' +
                            '<label for="' + row.id_programa + '" data-on-label="Si" data-off-label="No"></label>' +
                            '</div>';
                    }
                },
                {
                    data: 'nombre'
                },
                {
                    data: 'modulo'
                }
            ],
        });







        // Evento para el cambio de estado del checkbox
        $(document).on('change', '.check-activar', function() {
            var id_programa = $(this).data('id');
            var usuario = $("#id_usuario").val();


            if (this.checked) {
                activar(id_programa, usuario, 'activar');
            } else {
                activar(id_programa, usuario, 'desactivar');
            }
        });




        // Función para activar un programa
        function activar(id_programa, usuario, accion) {
            var usuario = $("#id_usuario_cam").val();

            // Código para activar el programa con el id_programa especificado
            console.log('Se activó el checkbox para el programa con ID: ' + id_programa);

            $.ajax({
                url: "programas_mod.php",
                type: "POST",
                data: {
                    id_programa: id_programa,
                    usuario: usuario,
                    accion: accion

                },
                datatype: 'json',

                success: function(data) {

                    data = JSON.parse(data);
                    if (data.estatus == 1) {

                        tips(data.respuesta);


                    } else {
                        alerta(data.respuesta);
                    }
                },
                error: function(data) {
                    alerta(data.respuesta);
                }
            })
        };

    });




    function graba_escritorio() {
const escritorioInput = document.getElementById('escritorio');
const numero = escritorioInput.value;

const usuarioInput = document.getElementById('id_usuario');
const usuario = usuarioInput.value;


            console.log('se cambio el escritorio');

            $.ajax({
                url: "escritorio_mod.php",
                type: "POST",
                data: {
                    
                    usuario: usuario,
                    numero: numero

                },
                datatype: 'json',

                success: function(data) {

                    data = JSON.parse(data);
                    if (data.estatus == 1) {

                        tips(data.respuesta);


                    } else {
                        alerta(data.respuesta);
                    }
                },
                error: function(data) {
                    alerta(data.respuesta);
                }
            })
        };







    function Grabar_Usuario() {
        var parserJsn = $("#parserJsn").val();
        var nombrecompleto = $("#nombrecompleto").val();
        var nombre = $("#email").val();
        var clave = $("#clave").val();
        var perfil = $("#perfil").val();
        Swal.fire({
            background: 'transparent',
            html: '<img src="./assets/images/loading.svg">',
            allowOutsideClick: false,
            showConfirmButton: false,
        });
        $.ajax({
            type: "post",
            datatype: 'json',
            url: "modulo_mod.php",
            data: {
                nombre: nombre,
                orden: orden,
                estatus: estatus
            },
            success: function(data) {
                if (parserJsn == 1)
                    data = JSON.parse(data);
                if (data.estatus == 1) {
                    tips(data.respuesta);
                } else {
                    alerta(data.respuesta);
                }
            },
            error: function(data) {
                alerta(data.respuesta)
            }
        })
    }

    function regresar() {
        window.location = "usuarios.php";
    }

    $(document).ready(function() {
        $('input[name="rif"]').mask('A-0000000000000');
    });





////////password

    const togglePassword = document.querySelector('#togglePassword');
    const password = document.querySelector('#clave');

    togglePassword.addEventListener('click', function (e) {
        // Alternar el tipo de input entre 'password' y 'text'
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);

        // Cambiar el ícono entre ojo abierto y cerrado
        this.classList.toggle('fa-eye-slash');
    });





    // Función para enviar la nueva clave al archivo PHP mediante AJAX
    function cambiarClave() {
    var nuevaClave = document.getElementById('clave').value;
    var id_usuario_cam = document.getElementById('id_usuario_cam').value;

    // Validar que la clave tenga entre 6 y 8 caracteres
    if (nuevaClave.length < 6 || nuevaClave.length > 8) {
        alert("La clave debe tener entre 6 y 8 caracteres.");
        return;
    }

    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'cambio_clave_usuario.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    // Procesar la respuesta del servidor
    xhr.onload = function() {
        if (xhr.status === 200) {
            var response = JSON.parse(xhr.responseText);
            if (response.success) {
                alert("La clave se ha cambiado correctamente.");
            } else {
                alert("Error al cambiar la clave: " + response.message);
            }
        } else {
            alert("Error en la solicitud.");
        }
    };

    // Enviar los datos al servidor
    xhr.send("clave=" + encodeURIComponent(nuevaClave) +   "&id_usuario_cam=" + encodeURIComponent(id_usuario_cam));
}


</script>