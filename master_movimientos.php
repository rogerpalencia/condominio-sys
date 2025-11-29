<?php
// Habilitar reporte de errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


include 'layouts/session.php';
include 'layouts/head-main.php';
require_once("core/funciones.php");
require_once("layouts/vars.php");
require_once("core/PDO.class.php");

$id_usuario = $_SESSION['userid'];
$func = new Funciones();
$conn = DB::getInstance();

if ($conn === null) {
    error_log("Error: No se pudo establecer la conexión a la base de datos.");
    die("Error: No se pudo conectar a la base de datos.");
}

$userid = (int)($_SESSION['userid'] ?? 0);
if ($userid === 0) {
    error_log("Error: Usuario no autenticado para ID: $userid");
    die("Error: Usuario no autenticado.");
}

// Obtener condominio
$sql = "SELECT a.id_condominio, c.nombre
        FROM administradores a
        INNER JOIN condominio c ON a.id_condominio = c.id_condominio
        WHERE a.id_usuario = :id_usuario AND a.estatus = true";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':id_usuario', $userid, PDO::PARAM_INT);
$stmt->execute();
$row_condo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row_condo) {
    error_log("Error: No se encontró condominio para usuario ID: $userid");
    die("Error: No se encontró un condominio asociado al usuario.");
}

$anio = date('Y');
$mes = date('m');
$id_condominio = $row_condo['id_condominio'];
?>

<head>
    <title>Notificaciones CxC | <?= NOMBREAPP ?></title>
    <?php include 'layouts/head.php'; ?>
    <link href="assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css" rel="stylesheet" />
    <link href="assets/libs/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css" rel="stylesheet" />
    <link href="assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css" rel="stylesheet" />
    <?php include 'layouts/head-style.php'; ?>
</head>

<body>
    <div id="layout-wrapper">
        <?php include 'layouts/menu.php'; ?>

        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">
                    <div class="d-flex justify-content-between mb-2">
                        <h4 class="card-title">Movimientos - <?php echo htmlspecialchars($row_condo['nombre']); ?></h4>
                        <div>
                            <button type="button" class="btn btn-success btn-sm mb-2" id="btnNuevoMovimiento">
                                <i class="mdi mdi-plus"></i> Nuevo movimiento
                            </button>
                            <button class="btn btn-danger" id="btnCierre">Cierre Contable</button>
                            <button type="button" class="btn btn-primary btn-sm mb-2" id="btnPruebaModal">
                                <i class="mdi mdi-test-tube"></i> Prueba Modal
                            </button>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-2">
                            <label for="filtroMes">Mes</label>
                            <select id="filtroMes" class="form-control">
                                <?php for ($m = 1; $m <= 12; $m++): ?>
                                    <option value="<?php echo $m; ?>" <?php echo ($m == $mes ? 'selected' : ''); ?>>
                                        <?php echo str_pad($m, 2, '0', STR_PAD_LEFT); ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="filtroAnio">Año</label>
                            <input type="number" id="filtroAnio" class="form-control" value="<?php echo $anio; ?>" min="2000" max="2100">
                        </div>
                    </div>

                    <div id="mensajeError" class="alert alert-danger d-none" role="alert"></div>
                    <table id="tablaMovimientos" class="table table-bordered dt-responsive nowrap" style="width:100%">
                        <thead class="thead-light">
                            <tr>
                                <th>Fecha</th>
                                <th>Tipo</th>
                                <th>Monto</th>
                                <th>Moneda</th>
                                <th>Tasa</th>
                                <th>Monto Base</th>
                                <th>Cuenta</th>
                                <th>Estado</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th colspan="2" style="text-align:right">Total:</th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th colspan="3"></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <?php include 'layouts/footer.php'; ?>
        </div>
    </div>

    <!-- Modal de prueba -->
    <div class="modal fade" id="modalPrueba" tabindex="-1" aria-labelledby="modalPruebaLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalPruebaLabel">Modal de Prueba</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>¡Esto es una prueba de modal! Si lo ves, las dependencias están funcionando.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Nuevo Movimiento -->
    <div class="modal fade" id="modalMovimiento" tabindex="-1" aria-labelledby="modalMovimientoLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalMovimientoLabel">Nuevo Movimiento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formMovimiento">
                        <input type="hidden" id="id_movimiento">
                        <div class="mb-3">
                            <label for="id_cuenta" class="form-label">Cuenta</label>
                            <select class="form-control" id="id_cuenta" required>
                                <!-- Opciones dinámicas -->
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="tipo_movimiento" class="form-label">Tipo</label>
                            <select class="form-control" id="tipo_movimiento" required>
                                <option value="ingreso">Ingreso</option>
                                <option value="egreso">Egreso</option>
                            </select>
                        </div>
                                                <div class="mb-3" id="id_moneda_group">
                            <label for="id_moneda" class="form-label">Moneda <span class="text-danger">*</span></label>
                            <select class="form-control" id="id_moneda" required>
                                <option value="">Seleccione una moneda...</option>
                                <!-- Opciones dinámicas -->
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="monto" class="form-label">Monto</label>
                            <input type="number" class="form-control" id="monto" step="0.01" required>
                        </div>
                        <div class="mb-3" id="grupo_tasa" style="display: none;">
                            <label for="tasa" class="form-label">Tasa</label>
                            <input type="number" class="form-control" id="tasa" step="0.000001" value="1.0" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="fecha_movimiento" class="form-label">Fecha</label>
                            <input type="date" class="form-control" id="fecha_movimiento" required>
                        </div>
                        <div class="mb-3">
                            <label for="estado" class="form-label">Estado</label>
                            <select class="form-control" id="estado" required>
                                <option value="pendiente">Pendiente</option>
                                <option value="completado">Completado</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Observación</label>
                            <textarea class="form-control" id="descripcion"></textarea>
                        </div>

                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" id="btnGuardarMovimiento">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <?php include 'layouts/footer-scripts.php'; ?>
    <?php include 'layouts/right-sidebar.php'; ?>
    <?php include 'layouts/vendor-scripts.php'; ?>

    <!-- Forzar carga de dependencias vía CDN -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>
    <script src="assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
    <script src="assets/libs/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js"></script>
    <!-- Desactivar app.js temporalmente -->
    <!-- <script src="assets/js/app.js"></script> -->

<script>
$(document).ready(function () {
    console.log('jQuery versión:', $.fn.jquery);
    console.log('Bootstrap cargado:', typeof bootstrap !== 'undefined');

    let tabla = $('#tablaMovimientos').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: 'master_movimientos_data.php',
            type: 'POST',
            data: function (d) {
                d.anio = $('#filtroAnio').val();
                d.mes = $('#filtroMes').val();
            },
            error: function (xhr, status, error) {
                $('#mensajeError').text('Error al cargar los datos: ' + (xhr.responseJSON?.error || error)).removeClass('d-none');
                console.error('Error AJAX DataTable:', error);
            }
        },
        footerCallback: function (row, data, start, end, display) {
            var api = this.api();
            let total = api.column(5, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return (parseFloat(a) || 0) + (parseFloat(b) || 0);
                }, 0);
            $(api.column(5).footer()).html(total.toFixed(2));
        },
        columns: [
            { data: 'fecha_movimiento' },
            { data: 'tipo_movimiento' },
            { data: 'monto' },
            { data: 'moneda' },
            { data: 'tasa' },
            { data: 'monto_base' },
            { data: 'cuenta' },
            { data: 'estado' },
            { data: null, defaultContent: '<button class="btn btn-info btn-sm edit-btn">Editar</button>' }
        ]
    });

    // Evento para el botón de prueba
    console.log('Buscando botón #btnPruebaModal');
    if ($('#btnPruebaModal').length) {
        console.log('Botón de prueba encontrado, asignando evento');
        $('#btnPruebaModal').on('click', function () {
            console.log('Botón Prueba Modal clicado');
            $('#modalPrueba').modal('show');
        });
    } else {
        console.log('Error: Botón #btnPruebaModal no encontrado en el DOM');
    }

    // Evento para el botón Nuevo Movimiento
    console.log('Buscando botón #btnNuevoMovimiento');
    if ($('#btnNuevoMovimiento').length) {
        console.log('Botón encontrado, asignando evento');
        $('#btnNuevoMovimiento').on('click', function () {
            console.log('Botón Nuevo Movimiento clicado');
            $('#formMovimiento')[0].reset();
            $('#modalMovimientoLabel').text('Nuevo Movimiento');
            $('#id_movimiento').val('');
            $('#modalMovimiento').modal('show');
            cargarCuentas(); // Cargar cuentas al abrir el modal
            cargarMonedas(); // Cargar monedas
        });
    } else {
        console.log('Error: Botón #btnNuevoMovimiento no encontrado en el DOM');
    }

    $('#btnCierre').click(function () {
        const anio = $('#filtroAnio').val();
        const mes = $('#filtroMes').val();
        if (confirm(`¿Deseas cerrar el periodo ${mes}/${anio}?`)) {
            $.post('cerrar_periodo.php', { anio: anio, mes: mes }, function (resp) {
                if (resp.success) {
                    alert(resp.mensaje);
                    tabla.ajax.reload();
                } else {
                    alert('Error: ' + resp.mensaje);
                }
            }, 'json').fail(function () {
                alert('Error al cerrar el periodo.');
            });
        }
    });

    $('#tablaMovimientos').on('click', '.edit-btn', function () {
        console.log('Botón Editar clicado');
        let data = tabla.row($(this).parents('tr')).data();
        $('#id_movimiento').val(data.id_movimiento);
        $('#id_cuenta').val(data.id_cuenta);
        $('#tipo_movimiento').val(data.tipo_movimiento);
        $('#monto').val(data.monto.replace(/[^0-9.-]+/g,''));
        $('#fecha_movimiento').val(data.fecha_movimiento);
        $('#estado').val(data.estado);
        $('#observacion').val(data.observacion);
        $('#tasa').val(data.tasa);
        $('#id_moneda').val(data.id_moneda); // Usar id_moneda para pre-seleccionar
        $('#modalMovimientoLabel').text('Editar Movimiento');
        $('#modalMovimiento').modal('show');
        cargarCuentas(data.id_cuenta); // Cargar cuentas con el valor seleccionado
        cargarMonedas(data.id_moneda); // Cargar monedas con el valor seleccionado
    });

    // Función para cargar monedas
    function cargarMonedas(selectedId = '') {
        console.log('Intentando cargar monedas desde get_moneda.php');
        $.get('get_moneda.php', function (data) {
            console.log('Respuesta de get_moneda.php:', data);
            let select = $('#id_moneda');
            select.empty().append('<option value="">Seleccione una moneda...</option>');
            data.forEach(moneda => {
                select.append(`<option value="${moneda.id_moneda}" ${moneda.id_moneda == selectedId ? 'selected' : ''}>${moneda.codigo} - ${moneda.nombre}</option>`);
            });
            actualizarTasa();
        }, 'json').fail(function (jqXHR, textStatus, errorThrown) {
            console.error('Error al cargar monedas:', textStatus, errorThrown, jqXHR.responseText);
            $('#mensajeError').text('Error al cargar monedas').removeClass('d-none');
        });
    }

    // Función para cargar cuentas desde plan_cuenta
    function cargarCuentas(selectedId = '') {
        console.log('Intentando cargar cuentas desde get_cuentas.php');
        $.get('get_cuentas.php', function (data) {
            console.log('Respuesta de get_cuentas.php:', data);
            let select = $('#id_cuenta');
            select.empty().append('<option value="">Seleccione...</option>');
            data.forEach(cuenta => {
                select.append(`<option value="${cuenta.id_cuenta}" ${cuenta.id_cuenta == selectedId ? 'selected' : ''}>${cuenta.nombre}</option>`);
            });
        }, 'json').fail(function (jqXHR, textStatus, errorThrown) {
            console.error('Error al cargar cuentas:', textStatus, errorThrown, jqXHR.responseText);
            $('#mensajeError').text('Error al cargar cuentas').removeClass('d-none');
        });
    }

    // Función para actualizar la tasa
    function actualizarTasa() {
        const idCuentaMoneda = $('#id_cuenta option:selected').data('moneda');
        const idMovimientoMoneda = $('#id_moneda').val();
        if (idCuentaMoneda && idMovimientoMoneda && idCuentaMoneda !== idMovimientoMoneda) {
            $.get('get_tasa_cambio.php', { origen: idMovimientoMoneda, destino: idCuentaMoneda }, function (data) {
                $('#tasa').val(data.tasa || 1.0);
                $('#grupo_tasa').show();
            }, 'json').fail(function () {
                $('#tasa').val(1.0);
                $('#grupo_tasa').hide();
            });
        } else {
            $('#tasa').val(1.0);
            $('#grupo_tasa').hide();
        }
    }

    // Evento para el botón Guardar
    $('#btnGuardarMovimiento').on('click', function () {
        console.log('Botón Guardar clicado');

        // Recolectar datos del formulario
        let data = {
            id_movimiento: $('#id_movimiento').val(),
            id_cuenta: $('#id_cuenta').val(),
            tipo_movimiento: $('#tipo_movimiento').val(),
            monto: $('#monto').val(),
            fecha_movimiento: $('#fecha_movimiento').val(),
            estado: $('#estado').val(),
            observacion: $('#observacion').val(),
            tasa: $('#tasa').val(),
            id_moneda: $('#id_moneda').val() // Asegurar que se envíe la moneda seleccionada
        };

        // Validación básica
        if (!data.id_cuenta || !data.tipo_movimiento || !data.monto || !data.fecha_movimiento || !data.id_moneda) {
            alert('Por favor, complete todos los campos obligatorios, incluyendo la moneda.');
            return;
        }

        // Enviar datos al servidor
        $.post('guardar_movimiento.php', data, function (resp) {
            if (resp.success) {
                alert(resp.mensaje);
                $('#modalMovimiento').modal('hide');
                tabla.ajax.reload(); // Recargar la tabla
            } else {
                alert('Error: ' + resp.mensaje);
            }
        }, 'json').fail(function (jqXHR, textStatus, errorThrown) {
            console.error('Error al guardar:', textStatus, errorThrown, jqXHR.responseText);
            alert('Error al guardar el movimiento.');
        });
    });

    $('#id_cuenta').on('change', function () {
        const idCuenta = $(this).val();
        if (idCuenta) {
            $.get('get_cuenta_moneda.php', { id_cuenta: idCuenta }, function (data) {
                $('#id_moneda').val(data.id_moneda);
                actualizarTasa();
            }, 'json').fail(function () {
                $('#mensajeError').text('Error al obtener moneda de la cuenta').removeClass('d-none');
            });
        }
    });

    $('#id_moneda').on('change', actualizarTasa);
});
</script>

<!-- HTML del Modal -->
<div class="modal fade" id="modalMovimiento" tabindex="-1" aria-labelledby="modalMovimientoLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalMovimientoLabel">Nuevo Movimiento XXXXXXXXXX</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formMovimiento">
                    <input type="hidden" id="id_movimiento">
                    <div class="mb-3">
                        <label for="id_cuenta" class="form-label">Cuenta</label>
                        <select id="id_cuenta" class="form-select" required></select>
                    </div>
                    <div class="mb-3">
                        <label for="tipo_movimiento" class="form-label">Tipo de Movimiento</label>
                        <select id="tipo_movimiento" class="form-select" required>
                            <option value="ingreso">Ingreso</option>
                            <option value="egreso">Egreso</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="monto" class="form-label">Monto</label>
                        <input type="number" id="monto" class="form-control" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label for="fecha_movimiento" class="form-label">Fecha</label>
                        <input type="date" id="fecha_movimiento" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="estado" class="form-label">Estado</label>
                        <select id="estado" class="form-select" required>
                            <option value="pendiente">Pendiente</option>
                            <option value="completado">Completado</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="observacion" class="form-label">Observación</label>
                        <textarea id="observacion" class="form-control"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="id_moneda" class="form-label">Moneda <span class="text-danger">*</span></label>
                        <select id="id_moneda" class="form-select" required>
                            <option value="">Seleccione una moneda...</option>
                        </select>
                    </div>
                    <div class="mb-3" id="grupo_tasa" style="display:none;">
                        <label for="tasa" class="form-label">Tasa de Cambio</label>
                        <input type="number" id="tasa" class="form-control" step="0.000001" value="1.0">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnGuardarMovimiento">Guardar</button>
            </div>
        </div>
    </div>
</div>