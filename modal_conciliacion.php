<?php
session_start();
require_once 'core/PDO.class.php';

/**
 * modal_conciliacion.php
 * Muestra un modal para conciliar los orígenes de fondos y notificaciones asociadas a un recibo.
 * - Los abonos a notificaciones son de solo lectura, reflejando los valores de recibo_destino_fondos.
 * - No redistribuye abonos automáticamente, respeta los valores originales.
 * - Calcula totales de orígenes y destinos para mostrar en el resumen.
 * - Permite aprobar/rechazar orígenes de fondos y enviar datos a aprobar_fondos.php.
 * - Incluye un botón para rechazar el recibo, actualizando su estado a 'anulado'.
 */

ini_set('display_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', 'logs/modal_conciliacion.log');

// Validar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log('Error: Método no permitido. Método recibido: ' . $_SERVER['REQUEST_METHOD']);
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
    exit;
}

// Validar parámetros
$id_recibo = isset($_POST['id_recibo']) ? (int)$_POST['id_recibo'] : 0;
$id_condominio = isset($_POST['id_condominio']) ? (int)$_POST['id_condominio'] : 0;
$id_usuario = isset($_POST['id_usuario']) ? (int)$_POST['id_usuario'] : 0;

if ($id_recibo <= 0 || $id_condominio <= 0 || $id_usuario <= 0) {
    error_log('Error: Parámetros inválidos - id_recibo: ' . $id_recibo . ', id_condominio: ' . $id_condominio . ', id_usuario: ' . $id_usuario);
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Parámetros inválidos']);
    exit;
}

try {
    $db = DB::getInstance();
    if ($db === null) {
        error_log('Error: No se pudo conectar a la base de datos.');
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'No se pudo conectar a la base de datos']);
        exit;
    }

    // Obtener datos del recibo
    $sql_recibo = "SELECT rc.numero_recibo, rc.fecha_emision, rc.monto_total, rc.observaciones, 
                          p.nombre1 AS propietario, rc.id_inmueble, c.id_moneda, m.codigo AS moneda_base_codigo
                   FROM recibo_cabecera rc
                   JOIN propietario p ON rc.id_propietario = p.id_propietario
                   JOIN condominio c ON rc.id_condominio = c.id_condominio
                   JOIN moneda m ON c.id_moneda = m.id_moneda
                   WHERE rc.id_recibo = :id_recibo AND rc.id_condominio = :id_condominio";
    $recibo = $db->row($sql_recibo, ['id_recibo' => $id_recibo, 'id_condominio' => $id_condominio]);
    if ($recibo === false || $recibo === null) {
        error_log('Error: Recibo no encontrado para id_recibo: ' . $id_recibo);
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Recibo no encontrado']);
        exit;
    }

    // Obtener moneda base
    $moneda_base_id = (int)$recibo['id_moneda'];
    $moneda_base_codigo = $recibo['moneda_base_codigo'];

    // Obtener orígenes de fondos
    $sql_fondos = "SELECT rof.id_origen_fondos, rof.tipo_origen, rof.monto, rof.tasa, rof.monto_base, 
                          rof.referencia, 
                          COALESCE(c.nombre, '—') AS cuenta,
                          COALESCE(c.banco, 'Crédito a favor') AS banco,
                          m.codigo AS moneda, 
                          m.id_moneda
                   FROM recibo_origen_fondos rof
                   LEFT JOIN cuenta c ON rof.id_cuenta = c.id_cuenta
                   JOIN moneda m ON rof.id_moneda = m.id_moneda
                   WHERE rof.id_recibo = :id_recibo AND rof.estado = 'en_revision'";
    $fondos = $db->query($sql_fondos, ['id_recibo' => $id_recibo]);
    error_log('Orígenes de fondos encontrados para id_recibo ' . $id_recibo . ': ' . count($fondos));

    // Obtener destinos (notificaciones) y calcular tasas dinámicamente
    $sql_destinos = "SELECT rdf.id_notificacion, nc.fecha_emision, nc.descripcion, nc.monto_x_pagar, 
                            nc.monto_pagado, m.codigo AS moneda, m.id_moneda, rdf.monto_aplicado, 
                            COALESCE(rdf.tasa, 
                                     (SELECT tasa FROM tipo_cambio 
                                      WHERE id_moneda_origen = nc.id_moneda 
                                      AND id_moneda_destino = :moneda_base_id 
                                      ORDER BY fecha_vigencia DESC LIMIT 1), 
                                     1) AS tasa, 
                            rdf.monto_base
                     FROM recibo_destino_fondos rdf
                     JOIN notificacion_cobro nc ON rdf.id_notificacion = nc.id_notificacion
                     JOIN moneda m ON nc.id_moneda = m.id_moneda
                     WHERE rdf.id_recibo = :id_recibo";
    $destinos = $db->query($sql_destinos, ['id_recibo' => $id_recibo, 'moneda_base_id' => $moneda_base_id]);
    error_log('Destinos encontrados para id_recibo ' . $id_recibo . ': ' . count($destinos));

    ?>
    <style>
        .modal-body { overflow-y: auto; }
        .scroll-y { max-height: 300px; overflow-y: auto; }
        .scroll-destinos { max-height: 200px; overflow-y: auto; }
        #modalConciliacion table.table th,
        #modalConciliacion table.table td {
            padding: 0.25rem 0.4rem;
            line-height: 1.1;
            font-size: 0.85rem;
            white-space: nowrap;
        }
        .table-responsive-wrapper {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        #modalConciliacion .modal-dialog {
            max-height: 95vh;
            display: flex;
            flex-direction: column;
        }
        #modalConciliacion .modal-content {
            display: flex;
            flex-direction: column;
            height: 100%;
            max-height: 100%;
        }
        #modalConciliacion .modal-body {
            overflow-y: auto;
            flex: 1 1 auto;
            padding: 1rem;
        }
        #modalConciliacion .modal-footer {
            position: sticky;
            bottom: 0;
            background-color: #fff;
            z-index: 100;
            padding: 1rem;
            border-top: 1px solid #dee2e6;
        }
        @media (max-width: 767.98px) {
            .step-mode .step-section { display: none; }
            .step-mode .step-section.active { display: block; }
            .scroll-y, .scroll-destinos { max-height: none; overflow-y: visible; }
            #modalConciliacion .modal-dialog { margin: 0; }
            #modalConciliacion .modal-content {
                border-radius: 0;
                height: 100vh;
            }
        }
    </style>

    <div class="modal fade" id="modalConciliacion" tabindex="-1" aria-labelledby="modalConciliacionLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable modal-fullscreen-sm-down">
            <div class="modal-content">
                <form id="formConciliarFondos">
                    <div class="modal-header flex-wrap gap-2">
                        <div class="row w-100 text-center">
                            <div class="col-12 col-md-4">
                                <label class="form-label">Recibo:</label>
                                <h5 class="modal-title fw-bold" id="modalConciliacionLabel"><?= htmlspecialchars($recibo['numero_recibo']) ?></h5>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label">Propietario:</label>
                                <p><?= htmlspecialchars($recibo['propietario']) ?></p>
                            </div>
                            <div class="col-12 col-md-3">
                                <label class="form-label">Fecha Emisión:</label>
                                <p><?= htmlspecialchars($recibo['fecha_emision']) ?></p>
                            </div>
                            <div class="col-12 col-md-1">
                                <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal"></button>
                                <small class="d-block text-center"><small>Cerrar</small></small>
                            </div>
                        </div>
                    </div>

                    <div class="modal-body">
                        <input type="hidden" name="id_recibo" value="<?= $id_recibo ?>">
                        <input type="hidden" name="id_condominio" value="<?= $id_condominio ?>">
                        <input type="hidden" name="id_usuario" value="<?= $id_usuario ?>">
                        <input type="hidden" name="id_inmueble" value="<?= $recibo['id_inmueble'] ?>">

                        <div class="step-section step-1 active">
                            <h5>Orígenes de Fondos</h5>
                            <div class="table-responsive-wrapper scroll-y mb-3">
                                <table class="table table-bordered align-middle mb-1">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Banco</th>
                                            <th>Cuenta</th>
                                            <th>Moneda</th>
                                            <th>Referencia</th>
                                            <th>Monto</th>
                                            <th>Tasa</th>
                                            <th>En Base</th>
                                            <th>Aprobar</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ($fondos): foreach ($fondos as $fondo): ?>
                                            <tr data-id="<?= $fondo['id_origen_fondos'] ?>">
                                                <td><?= htmlspecialchars($fondo['banco']) ?></td>
                                                <td><?= htmlspecialchars($fondo['cuenta']) ?></td>
                                                <td><?= htmlspecialchars($fondo['moneda']) ?></td>
                                                <td><?= htmlspecialchars($fondo['referencia'] ?: '') ?></td>
                                                <td><?= number_format($fondo['monto'], 2) ?></td>
                                                <td><?= number_format($fondo['tasa'], 4) ?></td>
                                                <td><?= number_format($fondo['monto_base'], 2) ?></td>
                                                <td>
                                                    <input type="checkbox" class="origen-checkbox" 
                                                           data-id="<?= $fondo['id_origen_fondos'] ?>" 
                                                           data-monto="<?= $fondo['monto'] ?>" 
                                                           data-tasa="<?= $fondo['tasa'] ?>" 
                                                           data-monto-base="<?= $fondo['monto_base'] ?>" 
                                                           data-moneda="<?= $fondo['moneda'] ?>" 
                                                           data-id-moneda="<?= $fondo['id_moneda'] ?>" 
                                                           checked>
                                                </td>
                                            </tr>
                                        <?php endforeach; else: ?>
                                            <tr><td colspan="8" class="text-center">No hay orígenes de fondos en revisión.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div id="resumenMontosPorMoneda" class="mt-3 fw-bold d-none mb-3"></div>
                            <div class="text-center mt-4 d-block d-md-none">
                                <button type="button" class="btn btn-outline-primary" onclick="mostrarStep(2)">Siguiente</button>
                            </div>
                        </div>

                        <div class="step-section step-2 active">
                            <h5 class="mt-4">Notificaciones Cubiertas</h5>
                            <div class="table-responsive-wrapper scroll-destinos mb-2">
                                <table class="table table-sm table-bordered mb-0" id="tablaDestinos">
                                    <thead class="table-light">
                                        <tr>
                                            <th>F. Emisión</th>
                                            <th>Descripción</th>
                                            <th>Moneda</th>
                                            <th class="text-end">Por Pagar</th>
                                            <th class="text-end">Tasa</th>
                                            <th class="text-end">Base</th>
                                            <th class="text-end">Abono</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ($destinos): foreach ($destinos as $destino): ?>
                                            <tr data-id="<?= $destino['id_notificacion'] ?>" data-tasa="<?= $destino['tasa'] ?>">
                                                <td><?= htmlspecialchars($destino['fecha_emision']) ?></td>
                                                <td><?= htmlspecialchars($destino['descripcion']) ?></td>
                                                <td><?= htmlspecialchars($destino['moneda']) ?></td>
                                                <td class="text-end"><?= number_format($destino['monto_x_pagar'], 2) ?></td>
                                                <td class="text-end"><?= number_format($destino['tasa'], 4) ?></td>
                                                <td class="text-end base-destino"><?= number_format($destino['monto_base'], 2) ?></td>
                                                <td class="text-end">
                                                    <input type="number" class="form-control abono" 
                                                           step="0.01" max="<?= $destino['monto_x_pagar'] ?>" 
                                                           value="<?= number_format($destino['monto_aplicado'], 2) ?>" readonly>
                                                </td>
                                            </tr>
                                        <?php endforeach; else: ?>
                                            <tr><td colspan="7" class="text-center">No hay notificaciones asociadas.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div id="resumenDestinos" class="alert alert-secondary p-2 mb-2 d-none"></div>
                            <div id="creditosDiv" class="alert alert-info py-2 d-none"></div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <div class="d-flex justify-content-between w-100">
                            <button type="button" class="btn btn-outline-secondary d-block d-md-none" onclick="mostrarStep(1)">Volver</button>
                            <div>
                                <button type="button" class="btn btn-danger me-2" id="btnRechazar">Rechazar</button>
                                <button type="submit" class="btn btn-primary">Aprobar y Guardar</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    const fmtDisp = (n, d = 2) => {
        let p = (+n).toFixed(d).split('.');
        p[0] = p[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        return p.join(',');
    };
    const fmt = (n, d = 2) => (+n).toFixed(d);

    function calcularTotalesOrigenes() {
        window.totalesPorMoneda = {};
        window.totalesPorMonedaBase = {};
        let totalBaseOrigenes = 0;
        document.querySelectorAll('.origen-checkbox:checked').forEach(chk => {
            const monto = parseFloat(chk.dataset.monto || 0);
            const tasa = parseFloat(chk.dataset.tasa || 1);
            const moneda = chk.dataset.moneda;
            const base = parseFloat(chk.dataset.montoBase || 0);
            window.totalesPorMoneda[moneda] = (window.totalesPorMoneda[moneda] || 0) + monto;
            window.totalesPorMonedaBase[moneda] = (window.totalesPorMonedaBase[moneda] || 0) + base;
            totalBaseOrigenes += base;
        });
        window.monedaMayorAporte = Object.keys(window.totalesPorMonedaBase)
            .sort((a, b) => window.totalesPorMonedaBase[b] - window.totalesPorMonedaBase[a])[0] || null;

        let htmlOrigenes = '<div class="alert alert-secondary p-2 mb-0">';
        Object.entries(window.totalesPorMoneda).forEach(([m, v]) => htmlOrigenes += `<span class="me-3">${m}: ${fmtDisp(v)}</span>`);
        htmlOrigenes += `<strong class="ms-3">Total base: ${fmtDisp(totalBaseOrigenes)}</strong></div>`;
        $('#resumenMontosPorMoneda').html(htmlOrigenes).removeClass('d-none');

        return totalBaseOrigenes;
    }

    function calcularTotalesDestinos() {
        let totDest = {}, baseDest = 0;
        $('#tablaDestinos tbody tr').each(function () {
            const $r = $(this);
            const abono = parseFloat($r.find('.abono').val() || 0);
            const tasa = parseFloat($r.data('tasa') || 1);
            const moneda = $r.find('td:eq(2)').text();
            const base = abono * tasa;
            totDest[moneda] = (totDest[moneda] || 0) + abono;
            baseDest += base;
            $r.find('.base-destino').text(fmtDisp(base));
        });

        let htmlDest = '';
        Object.entries(totDest).forEach(([mon, val]) => { htmlDest += `${mon}: ${fmtDisp(val)} `; });
        htmlDest += `Total base: ${fmtDisp(baseDest)}`;
        $('#resumenDestinos').text(htmlDest).removeClass('d-none');

        return baseDest;
    }

    function distribuirExcedentes() {
        let saldoBase = calcularTotalesOrigenes();
        let baseDestOriginal = 0;
        $('#tablaDestinos tbody tr').each(function () {
            const $r = $(this);
            const abono = parseFloat($r.find('.abono').val() || 0);
            const tasa = parseFloat($r.data('tasa') || 1);
            baseDestOriginal += abono * tasa;
        });

        let excedenteBase = saldoBase - baseDestOriginal;
        if (excedenteBase <= 0) {
            $('#creditosDiv').addClass('d-none').empty();
            calcularTotalesDestinos();
            return;
        }

        $('#tablaDestinos tbody tr').each(function () {
            const $r = $(this);
            const abonoActual = parseFloat($r.find('.abono').val() || 0);
            if (abonoActual > 0) return;

            const porPagar = parseFloat($r.find('td:eq(3)').text().replace(/[,.]/g, '') / 100);
            const tasa = parseFloat($r.data('tasa') || 1);
            const porPagarBase = porPagar * tasa;
            const abonoInput = $r.find('.abono');

            if (excedenteBase >= porPagarBase) {
                abonoInput.val(fmt(porPagar));
                excedenteBase -= porPagarBase;
            } else if (excedenteBase > 0) {
                const abono = excedenteBase / tasa;
                abonoInput.val(fmt(abono));
                excedenteBase = 0;
            }
        });

        const baseDestFinal = calcularTotalesDestinos();
        const saldoBaseFinal = saldoBase - baseDestFinal;
        if (saldoBaseFinal > 0 && window.monedaMayorAporte) {
            const tasaMayor = window.totalesPorMonedaBase[window.monedaMayorAporte] / window.totalesPorMoneda[window.monedaMayorAporte];
            const credito = fmt(saldoBaseFinal / tasaMayor);
            $('#creditosDiv').removeClass('d-none')
                .html(`Crédito a favor: <strong>${credito}</strong> (${window.monedaMayorAporte})`);
        } else {
            $('#creditosDiv').addClass('d-none').empty();
        }
    }

    function mostrarStep(n) {
        if (window.innerWidth < 768) {
            $('#modalConciliacion').addClass('step-mode');
            $('.step-section').removeClass('active');
            $('.step-' + n).addClass('active');
        }
    }

    function resetSteps() {
        if (window.innerWidth >= 768) {
            $('#modalConciliacion').removeClass('step-mode');
            $('.step-section').addClass('active');
        } else {
            $('#modalConciliacion').addClass('step-mode');
            $('.step-section').removeClass('active');
            $('.step-1').addClass('active');
        }
    }

    $(document).ready(function () {
        distribuirExcedentes();
        $(document).on('change', '.origen-checkbox', distribuirExcedentes);

        $('#formConciliarFondos').on('submit', function (e) {
            e.preventDefault();
            const formData = $(this).serializeArray();
            const origenes = [];
            const notificaciones = [];

            $('.origen-checkbox').each(function () {
                const id = $(this).data('id');
                origenes.push({
                    id_origen_fondos: id,
                    estado: $(this).is(':checked') ? 'aprobado' : 'rechazado'
                });
            });

            $('#tablaDestinos tbody tr').each(function () {
                const $row = $(this);
                const abono = parseFloat($row.find('.abono').val() || 0);
                if (abono > 0) {
                    notificaciones.push({
                        id_notificacion: $row.data('id'),
                        abono: abono
                    });
                }
            });

            formData.push({ name: 'origenes', value: JSON.stringify(origenes) });
            formData.push({ name: 'notificaciones', value: JSON.stringify(notificaciones) });

            if (window.monedaMayorAporte && $('#creditosDiv').is(':visible')) {
                const credito = $('#creditosDiv strong').text().match(/[\d,.]+/)[0].replace(/,/g, '');
                formData.push({ name: 'credito', value: credito });
                formData.push({ name: 'moneda_credito', value: window.monedaMayorAporte });
            }

            Swal.fire({
                title: '¿Confirmar conciliación?',
                text: 'Esta acción aprobará o rechazará los orígenes seleccionados y actualizará las notificaciones.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, guardar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'aprobar_fondos.php',
                        type: 'POST',
                        data: formData,
                        dataType: 'json',
                        success: function (response) {
                            Swal.fire({
                                icon: response.status,
                                title: response.status === 'success' ? 'Éxito' : 'Error',
                                text: response.message,
                                confirmButtonText: 'Aceptar'
                            }).then(() => {
                                if (response.status === 'success') {
                                    $('#modalConciliacion').modal('hide');
                                    $('#tablaConciliacion').DataTable().ajax.reload();
                                }
                            });
                        },
                        error: function (xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Error al conectar con el servidor: ' + (xhr.responseText || 'Desconocido'),
                                confirmButtonText: 'Aceptar'
                            });
                        }
                    });
                }
            });
        });

        $('#btnRechazar').on('click', function () {
            Swal.fire({
                title: '¿Rechazar recibo?',
                text: 'Esta acción anulará el recibo y no se podrá deshacer.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, rechazar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = {
                        id_recibo: <?= $id_recibo ?>,
                        id_condominio: <?= $id_condominio ?>,
                        id_usuario: <?= $id_usuario ?>
                    };

                    $.ajax({
                        url: 'rechazar_fondos.php',
                        type: 'POST',
                        data: formData,
                        dataType: 'json',
                        success: function (response) {
                            Swal.fire({
                                icon: response.status,
                                title: response.status === 'success' ? 'Éxito' : 'Error',
                                text: response.message,
                                confirmButtonText: 'Aceptar'
                            }).then(() => {
                                if (response.status === 'success') {
                                    $('#modalConciliacion').modal('hide');
                                    $('#tablaConciliacion').DataTable().ajax.reload();
                                }
                            });
                        },
                        error: function (xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Error al conectar con el servidor: ' + (xhr.responseText || 'Desconocido'),
                                confirmButtonText: 'Aceptar'
                            });
                        }
                    });
                }
            });
        });

        $(window).on('resize', resetSteps);
        resetSteps();
    });
</script>
<?php
} catch (Exception $e) {
    error_log('Error en modal_conciliacion.php: ' . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Error en el servidor: ' . $e->getMessage()]);
    exit;
}
?>