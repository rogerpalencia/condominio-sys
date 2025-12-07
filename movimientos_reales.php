<?php
//movimientos_reales.php
@session_start();
include 'layouts/session.php';
include 'layouts/head-main.php';
require_once 'core/funciones.php';
require_once 'layouts/vars.php';
require_once 'core/PDO.class.php';

try {
    $func = new Funciones();
    $conn = DB::getInstance();
    if (!$conn) throw new Exception('No se pudo conectar a la base de datos');
    $id_usuario = (int)($_SESSION['userid'] ?? 0);
    if ($id_usuario <= 0) throw new Exception('Usuario no autenticado');

    $sql = "SELECT c.id_condominio, c.nombre
            FROM administradores a
            JOIN condominio c ON c.id_condominio = a.id_condominio
            WHERE a.id_usuario = :u AND a.estatus = TRUE
            LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':u' => $id_usuario]);
    $row_condo = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row_condo) throw new Exception('Sin permisos o sin condominio.');
    $id_condominio = (int)$row_condo['id_condominio'];
    $nombre_condo = $row_condo['nombre'];
    $_SESSION['id_condominio'] = $id_condominio; // Almacenar en sesión como respaldo
} catch (Exception $e) {
    die('Error: ' . htmlspecialchars($e->getMessage()));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <title>Movimientos Reales | <?= NOMBREAPP ?></title>
    <?php include 'layouts/head.php'; ?>
    <link href="assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css" rel="stylesheet"/>
    <link href="assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css" rel="stylesheet"/>
    <?php include 'layouts/head-style.php'; ?>
</head>
<?php include 'layouts/body.php'; ?>
<input type="hidden" id="id_condominio" value="<?= $id_condominio ?>">
<div id="layout-wrapper">
    <?php include 'layouts/menu.php'; ?>
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row align-items-center mb-3">
                    <div class="col-6">
                        <h1 class="display-6 mb-0">Cargar ingresos/egresos</h1>
                        <p class="text-muted mb-0"><?= htmlspecialchars($nombre_condo) ?></p>
                    </div>
                    <div class="col-6 text-end">
                        <button class="btn btn-success me-1" data-bs-toggle="modal" data-bs-target="#modalMovimiento">
                            + Nuevo Movimiento
                        </button>
                        <button class="btn btn-primary" onclick="window.history.back()">Volver</button>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <table id="tablaMovimientos" class="table table-bordered dt-responsive nowrap w-100">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Fecha</th>
                                    <th>Descripción</th>
                                    <th>Cuenta</th>
                                    <th>Moneda</th>
                                    <th>Monto Base</th>
                                    <th>Mes Contable</th>
                                    <th>Año Contable</th>
                                    <th>Tipo</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
            <?php include 'layouts/footer.php'; ?>
        </div>
    </div>
    <?php include 'layouts/right-sidebar.php'; ?>
</div>
<div class="modal fade" id="modalMovimiento" tabindex="-1" aria-labelledby="modalMovimientoLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content" id="modalMovimientoContent"></div>
    </div>
</div>
<?php include 'layouts/vendor-scripts.php'; ?>
<script src="assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
<script src="assets/libs/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js"></script>
<script>
$(document).ready(function() {
    function estadoBadge(d) {
        const s = d.toLowerCase();
        if (s === 'conciliado') return '<span class="badge bg-success">Conciliado</span>';
        if (s === 'pendiente') return '<span class="badge bg-warning text-dark">Pendiente</span>';
        if (s === 'cancelado') return '<span class="badge bg-secondary">Cancelado</span>';
        return d;
    }

    function tipoBadge(d) {
        const s = d.toLowerCase();
        if (s === 'ingreso') return '<span class="badge bg-primary">Ingreso</span>';
        if (s === 'egreso') return '<span class="badge bg-danger">Egreso</span>';
        return d;
    }

    function renderBtns(row) {
        let deleteBtn = row.estado.toLowerCase() !== 'conciliado' ? `
            <button class="btn btn-sm btn-danger me-1 eliminar-movimiento" data-id="${row.id_movimiento}">
                <i class="fas fa-trash"></i>
            </button>` : '';
        let conciliarBtn = row.estado.toLowerCase() !== 'conciliado' ? `
            <button class="btn btn-sm btn-success me-1 conciliar-movimiento" data-id="${row.id_movimiento}">
                <i class="fas fa-check"></i>
            </button>` : '';
        return `
            <button class="btn btn-sm btn-primary me-1 editar-movimiento" data-id="${row.id_movimiento}">
                <i class="fas fa-edit"></i>
            </button>
            ${conciliarBtn}
            ${deleteBtn}
        `;
    }

    const tabla = $('#tablaMovimientos').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: 'movimientos_reales_data.php',
            type: 'POST',
            data: function(d) {
                d.id_condominio = $('#id_condominio').val();
            }
        },
        columns: [
            { data: 'id_movimiento' },
            { data: 'fecha_movimiento' },
            { data: 'descripcion', render: function(d) { return d.toUpperCase(); } },
            { data: 'cuenta' },
            { data: 'moneda' },
            { data: 'monto_base_total', render: $.fn.dataTable.render.number('.', ',', 2, '') },
            {
                data: 'mes_contable',
                render: function(data, type, row) {
                    if (type === 'display') {
                        let html = '<select class="form-control mes-contable" data-id="' + row.id_movimiento + '">';
                        html += '<option value="">-- Seleccione --</option>';
                        for (let i = 1; i <= 12; i++) {
                            const mes = String(i).padStart(2, '0');
                            const selected = (data === mes) ? 'selected' : '';
                            html += `<option value="${mes}" ${selected}>${mes}</option>`;
                        }
                        html += '</select>';
                        return html;
                    }
                    return data;
                }
            },
            {
                data: 'anio_contable',
                render: function(data, type, row) {
                    if (type === 'display') {
                        let html = '<select class="form-control anio-contable" data-id="' + row.id_movimiento + '">';
                        html += '<option value="">-- Seleccione --</option>';
                        for (let i = 2020; i <= 2030; i++) {
                            const selected = (data === i.toString()) ? 'selected' : '';
                            html += `<option value="${i}" ${selected}>${i}</option>`;
                        }
                        html += '</select>';
                        return html;
                    }
                    return data;
                }
            },
            { data: 'tipo_movimiento', render: tipoBadge },
            { data: 'estado', render: estadoBadge },
            { data: null, render: renderBtns, orderable: false, searchable: false }
        ],
        order: [[0, 'desc']],
        language: { url: 'Spanish.json' },
        rowCallback: function(row, data) {
            if (data.estado.toLowerCase() === 'conciliado') {
                $(row).addClass('table-success');
            }
        }
    });

    $('#tablaMovimientos').on('click', '.editar-movimiento', function() {
        const idMovimiento = $(this).data('id');
        const idCondominio = $('#id_condominio').val();
        console.log('Iniciando edición para ID:', idMovimiento);
        $.ajax({
            url: 'editar_movimiento.php',
            method: 'GET',
            data: { id: idMovimiento },
            dataType: 'json',
            success: function(data) {
                console.log('Respuesta de editar_movimiento.php:', data);
                if (data.status === 'error') {
                    Swal.fire('Error', data.message, 'error');
                    return;
                }
                $.ajax({
                    url: 'movimiento_real_nuevo.php',
                    type: 'POST',
                    data: {
                        id_movimiento: idMovimiento,
                        id_condominio: idCondominio,
                        data: JSON.stringify(data)
                    },
                    success: function(html) {
                        console.log('Respuesta de movimiento_real_nuevo.php (editar):', html);
                        $('#modalMovimientoContent').html(html);
                        $('#modalMovimiento').modal('show');
                    },
                    error: function(xhr) {
                        console.log('Error en movimiento_real_nuevo.php (editar):', xhr.responseText);
                        Swal.fire('Error', 'No se pudo cargar el formulario', 'error');
                    }
                });
            },
            error: function(xhr) {
                console.log('Error en editar_movimiento.php:', xhr.responseText);
                Swal.fire('Error', 'No se pudo obtener los datos del movimiento', 'error');
            }
        });
    });

    $('#tablaMovimientos').on('click', '.eliminar-movimiento', function() {
        const idMovimiento = $(this).data('id');
        Swal.fire({
            title: '¿Estás seguro?',
            text: 'Esta acción eliminará el movimiento #' + idMovimiento,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'movimiento_delete.php',
                    type: 'POST',
                    data: {
                        id_movimiento: idMovimiento,
                        id_condominio: $('#id_condominio').val()
                    },
                    success: function(response) {
                        if (response.status === 'ok') {
                            Swal.fire('Eliminado', 'Movimiento eliminado correctamente', 'success');
                            tabla.ajax.reload();
                        } else {
                            Swal.fire('Error', response.message || 'No se pudo eliminar el movimiento', 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Error al eliminar el movimiento', 'error');
                    }
                });
            }
        });
    });

    $('[data-bs-target="#modalMovimiento"]').on('click', function() {
        $('#modalMovimientoContent').html('');
        const idCondominio = $('#id_condominio').val();
        console.log('Iniciando nuevo movimiento para condominio:', idCondominio);
        $.ajax({
            url: 'movimiento_real_nuevo.php',
            type: 'POST',
            data: {
                id_movimiento: 0,
                id_condominio: idCondominio,
                data: '{}'
            },
            success: function(html) {
                console.log('Respuesta de movimiento_real_nuevo.php (nuevo):', html);
                if (html.trim() === '' || html.includes('<div class="alert alert-danger">')) {
                    console.error('Respuesta vacía o con error:', html);
                    Swal.fire('Error', 'El servidor devolvió una respuesta inválida', 'error');
                    return;
                }
                $('#modalMovimientoContent').html(html);
                $('#modalMovimiento').modal('show');
            },
            error: function(xhr) {
                console.log('Error en movimiento_real_nuevo.php (nuevo):', xhr.responseText);
                Swal.fire('Error', 'No se pudo cargar el formulario de nuevo movimiento', 'error');
            }
        });
    });

    $('#tablaMovimientos').on('change', '.mes-contable, .anio-contable', function() {
        const id = $(this).data('id');
        const mes = $(this).hasClass('mes-contable') ? $(this).val() : $(this).closest('tr').find('.mes-contable').val();
        const anio = $(this).hasClass('anio-contable') ? $(this).val() : $(this).closest('tr').find('.anio-contable').val();

        $.ajax({
            url: 'actualizar_periodo.php',
            type: 'POST',
            data: { id: id, mes_contable: mes, anio_contable: anio },
            success: function(response) {
                if (response.status !== 'ok') {
                    Swal.fire('Error', 'Error al actualizar el período', 'error');
                }
            }
        });
    });

    $('#tablaMovimientos').on('click', '.conciliar-movimiento', function() {
        const id = $(this).data('id');
        const mes = $(this).closest('tr').find('.mes-contable').val();
        const anio = $(this).closest('tr').find('.anio-contable').val();

        if (!mes || !anio) {
            Swal.fire('Error', 'Debes seleccionar Mes y Año Contable antes de conciliar', 'error');
            return;
        }

        Swal.fire({
            title: '¿Estás seguro?',
            text: 'Esto marcará el movimiento #' + id + ' como conciliado',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, conciliar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'conciliar_movimiento.php',
                    type: 'POST',
                    data: { id: id, mes_contable: mes, anio_contable: anio },
                    success: function(response) {
                        if (response.status === 'ok') {
                            Swal.fire('Éxito', 'Movimiento conciliado correctamente', 'success');
                            tabla.ajax.reload();
                        } else {
                            Swal.fire('Error', response.message || 'No se pudo conciliar el movimiento', 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Error al conciliar el movimiento', 'error');
                    }
                });
            }
        });
    });
});
</script>
</body>
</html>