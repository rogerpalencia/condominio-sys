<?php 
require_once 'layouts/session.php'; 
require_once 'layouts/head-main.php';
require_once 'core/config.php';
require_once 'core/funciones.php';
require_once 'layouts/vars.php';

$func = new Funciones();
$conn = pg_connect("host=$Servidor dbname=$BaseDeDatos user=$Usuario password=$Password");

$userid = $_SESSION['userid'];

// Comprobación de seguridad
$nombre_archivo = basename($_SERVER['PHP_SELF']);
$sql = "SELECT prog.nombre FROM detalles_perfiles AS per 
        INNER JOIN programas AS prog ON per.id_programa = prog.id_programa
        WHERE prog.accion = '$nombre_archivo' AND per.id_perfil = '$userid'";
$result = pg_query($conn, $sql);
$num_rows = pg_num_rows($result);

if ($num_rows == 0) {
    echo "<script>window.location.replace('error550.php');</script>";
    exit();
}
?>

<head>
    <title>Registro de Usuarios | <?php echo NOMBREAPP; ?></title>
    <?php include 'layouts/head.php'; ?>

    <!-- DataTables CSS -->
    <link href="assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/libs/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css" rel="stylesheet"
        type="text/css" />
    <link href="assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css" rel="stylesheet"
        type="text/css" />
    <link href="assets/libs/choices.js/public/assets/styles/choices.min.css" rel="stylesheet" type="text/css" />
    <!-- Bootstrap Inputmask JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.5/jquery.inputmask.min.js"></script>

    <?php include 'layouts/head-style.php'; ?>

    <style>
    table.dataTable tbody th,
    table.dataTable tbody td {
        padding: 5px 5px;
    }

    .centrar-verticalmente {
        display: flex;
        align-items: center;
        margin-top: 10px;
    }
    </style>
</head>

<?php include 'layouts/body.php'; ?>

<input type="hidden" name="parserJsn" id="parserJsn" value="<?php echo PARSERJSN; ?>">
<input type="hidden" name="mensaje" id="mensaje" value="">

<!-- Begin page -->
<div id="layout-wrapper">
    <?php include 'layouts/menu.php'; ?>

    <!-- Start right Content here -->
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">

                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
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

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <p class="card-title-desc">



                                <div class="row d-flex align-items-end">
    <div class="col-3 ">
        <div class="form-group ">
            <label for="email">Crear Nuevo Usuario:</label>
            <input type="email" id="email" name="email"
                   style="text-transform: lowercase" required
                   data-pristine-required-message="Indique email" class="form-control"
                   data-inputmask="'alias': 'email'"
                   pattern="^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$"
                   title="Debe ser un correo válido (ejemplo: usuario@dominio.com)" />
            <div class="invalid-feedback">
                Por favor, ingrese un correo electrónico válido.
            </div>
        </div>
    </div>

    <div class="col-2 d-flex align-items-end ">
        <button type="button" onclick="nuevo_usuario()" class="btn btn-primary w-100">+ Crear Nuevo</button>
    </div>
</div>









                                </p>
                            </div>
                            <div class="card-body">
                                <table id="mytable" class="table table-nowrap align-middle table-edits">
                                    <thead>
                                        <tr>
                                            <th>Código</th>
                                            <th>Usuario</th>
                                            <th>Nombres y Apellidos</th>
                                            <th>Cédula</th>

                                            <th>Gerencia</th>
                                            <th>Cargo</th>
                                            <th>Activo</th>
                                            <th>Funciones</th>
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

<!-- DataTables JS -->
<script src="assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
<script src="assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js"></script>
<script src="assets/libs/datatables.net-buttons-bs4/js/buttons.bootstrap4.min.js"></script>

<script src="assets/js/pages/datatables.init.js"></script>
<script src="assets/js/app.js"></script>
<script src="assets/js/funciones.js"></script>

<script type="text/javascript">
$(document).ready(function() {
    var table = $('#mytable').DataTable({
        lengthMenu: [
            [50, -1],
            [50, 'Todo'],
        ],
        columnDefs: [{
            targets: [0],
            visible: false,
            searchable: false
        }],
        language: {
            url: "Spanish.json"
        },
        processing: true,
        serverSide: true,
        serverMethod: 'post',
        ajax: {
            url: 'usuarios_data.php'
        },
        columns: [{
                data: 'id_usuario',
                title: 'Código'
            },
            {
                data: 'nombre',
                title: 'Usuario'
            },
            {
                data: 'nombres_y_apellidos',
                title: 'Nombres y Apellidos'
            },
            {
                data: 'cedula',
                title: 'Cédula'
            },

            {
                data: 'gerencia',
                title: 'Gerencia'
            },
            {
                data: 'cargo',
                title: 'Cargo'
            },

            // Columna con el checkbox para 'activo'
            {
                data: 'activo',
                title: 'Activo',
                render: function(data, type, row) {
                    let checked = data === 's' ? 'checked' : '';
                    return '<div >' +
                        '<input data-id="' + row.id_usuario +
                        '" type="checkbox"  class="check-activar form-check-input" ' + checked +
                        '/> ' +
                        '<label for="' + row.id_usuario +
                        '" data-on-label="Si" data-off-label="No"></label>' +
                        '</div>';
                },
                orderable: false,
                searchable: false
            },

            // Columna 'Funciones'
            {
                data: 'id_usuario',
                title: 'Funciones',
                render: function(data, type, row, meta) {
                    return '<button type="button" onclick="editar(' + data +
                        ')" class="btn-sm btn-primary waves-effect waves-light w-sm" title="Editar Item"><i class="edit-icon fas fa-edit"></i> Programas</button>';
                },
                searchable: false,
                orderable: false
            }
        ],
    });

    // Evento para manejar el cambio en el checkbox de 'activo'
    $(document).on('change', '.check-activar', function() {
        var id_usuario = $(this).data('id');
        var nuevo_valor = $(this).is(':checked') ? 's' : 'n';

        $.ajax({
            url: 'update_usuario.php',
            method: 'POST',
            data: {
                id_usuario: id_usuario,
                campo_modificado: 'activo',
                nuevo_valor: nuevo_valor
            },
            dataType: 'json',
            success: function(response) {
                if (!response.success) {
                    alert('Error al actualizar el estado activo: ' + (response.error ||
                        'Desconocido'));
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Ocurrió un error al enviar los datos: ' + textStatus);
            }
        });
    });

    // Permitir la edición de otras columnas (excepto 'activo' y 'Funciones')
    $('#mytable tbody').on('click', 'td', function() {
        var table = $('#mytable').DataTable();
        var cell = table.cell(this);
        var row = table.row($(this).parents('tr'));
        var rowData = row.data();
        var columnIndex = cell.index().column;
        var columnName = table.column(columnIndex).dataSrc();

        // Evitar edición en las columnas 'activo' y 'Funciones'
        if (columnName === 'activo' || columnName === 'id_usuario') {
            return;
        }
        var originalValue = cell.data();
    
    // Si el valor original es NULL, establecerlo como una cadena vacía
    var inputValue = (originalValue === null) ? '' : originalValue;


        // Convertir la celda a un input para edición
        //  $(this).html('<input type="text" class="form-control" value="' + originalValue + '" />');
        //  $(this).html('<input type="text" class="form-control" value="' + originalValue.toUpperCase() + '" />');
// Convertir la celda a un input para edición
var formattedValue = (columnIndex === 1) ? inputValue.toLowerCase() : inputValue.toUpperCase();
$(this).html('<input type="text" class="form-control" value="' + formattedValue + '" />');


        $(this).find('input').focus();

        // Captura eventos 'blur' y 'keypress'
        $(this).find('input').on('keypress blur', function(e) {
            if (e.type === 'blur' || (e.type === 'keypress' && e.which === 13)) {
                var newValue = $(this).val();

                // Actualizar la celda en la tabla
                cell.data(newValue).draw();

                // Construir el objeto con el campo modificado y el id_usuario
                var updatedData = {
                    id_usuario: rowData.id_usuario,
                    campo_modificado: columnName,
                    nuevo_valor: columnIndex === 1 ? newValue.toLowerCase() : newValue.toUpperCase()

                };

                // Enviar la actualización al servidor
                $.ajax({
                    url: 'update_usuario.php',
                    method: 'POST',
                    data: updatedData,
                    dataType: 'json',
                    success: function(response) {
                        if (!response.success) {
                            alert('Error al actualizar los datos: ' + (response
                                .error || 'Desconocido'));
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        alert('Ocurrió un error al enviar los datos: ' +
                            textStatus);
                    }
                });
            }
        });
    });
});






function editar(id) {
    window.location.href = "usuarios_ac.php?id=" + id;
}

function nuevo_usuario() {
    var emailField = document.getElementById('email');
    var emailValue = emailField.value;

    // Validar el campo de email
    if (emailField.checkValidity()) {
        // Si es válido, continúa con el envío del valor del email vía AJAX
        $.ajax({
            url: 'nuevo_usuario.php',  // Archivo al que enviarás el email
            method: 'POST',
            data: { email: emailValue },  // Envía el valor del email
            success: function(response) {
                // Manejo del resultado exitoso
                alert('Usuario creado: ' + response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                // Manejo de errores
                alert('Ocurrió un error: ' + textStatus);
            }
        });
    } else {
        // Si no es válido, muestra el mensaje de error
        emailField.classList.add('is-invalid');
    }
}
</script>