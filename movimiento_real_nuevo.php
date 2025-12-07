<?php
@session_start();
require_once 'core/PDO.class.php';
require_once 'core/funciones.php';

header('Content-Type: text/html; charset=utf-8');

try {
    $conn = DB::getInstance();
    if (!$conn) throw new Exception('No se pudo conectar a la base de datos');

    $id_movimiento = (int)($_POST['id_movimiento'] ?? 0);
    $id_condominio = (int)($_POST['id_condominio'] ?? 0);
    $data_json = $_POST['data'] ?? '{}';
    $data = json_decode($data_json, true);

    if ($id_condominio <= 0) throw new Exception('ID de condominio no proporcionado');

    // Datos del movimiento (nuevo o edición)
    $movimiento = [];
    if ($id_movimiento > 0 && $data && !empty($data)) {
        $movimiento = $data;
    } else {
        $movimiento = [
            'id_movimiento' => 0,
            'id_condominio' => $id_condominio,
            'fecha_movimiento' => date('Y-m-d'),
            'tipo_movimiento' => 'ingreso',
            'descripcion' => '',
            'estado' => 'pendiente',
            'mes_contable' => date('m'),
            'anio_contable' => date('Y'),
            'monto_base_total' => 0,
            'cuenta' => '',
            'moneda' => '',
            'id_cuenta' => '',
            'id_moneda' => '',
            'detalles' => []
        ];
    }

    // Depuración: Registrar el POST recibido
    file_put_contents('debug_form_post.log', print_r($_POST, true) . PHP_EOL, FILE_APPEND);

    // Consultar cuentas disponibles
    $sql = "SELECT id_cuenta, nombre FROM cuenta WHERE id_condominio = :id_condominio";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':id_condominio' => $id_condominio]);
    $cuentas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!$cuentas) {
        throw new Exception('No se encontraron cuentas para este condominio.');
    }

    // Consultar monedas disponibles
    $sql = "SELECT id_moneda, codigo FROM moneda";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $monedas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!$monedas) {
        throw new Exception('No se encontraron monedas.');
    }

    // Consultar cuentas contables disponibles (con valor predeterminado si falla)
    $sql = "SELECT id_plan as id_plan_cuenta, codigo, nombre, tipo 
            FROM plan_cuenta 
            WHERE id_condominio = :id_condominio AND estado = TRUE AND nivel > 1 
            ORDER BY codigo";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':id_condominio' => $id_condominio]);
    $cuentas_contables = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (empty($cuentas_contables)) {
        $cuentas_contables = [['id_plan_cuenta' => 1, 'codigo' => '001', 'nombre' => 'Verificar Configuración', 'tipo' => 'ingreso']]; // Valor temporal
    }

    ob_start();
?>
<div class="modal-header">
    <h5 class="modal-title" id="modalMovimientoLabel"><?= $id_movimiento > 0 ? 'Editar Movimiento' : 'Movimiento Real - Nuevo' ?></h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    <form id="movimientoForm" method="post" action="guardar_movimiento_real.php">
        <input type="hidden" name="id_movimiento" value="<?= htmlspecialchars($movimiento['id_movimiento']) ?>">
        <input type="hidden" name="id_condominio" value="<?= htmlspecialchars($id_condominio) ?>">
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Fecha</label>
                <input type="date" name="fecha_movimiento" class="form-control" value="<?= htmlspecialchars($movimiento['fecha_movimiento'] ?? '') ?>" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Tipo</label>
                <select name="tipo_movimiento" class="form-control" required onchange="toggleFormFields()">
                    <option value="ingreso" <?= isset($movimiento['tipo_movimiento']) && $movimiento['tipo_movimiento'] === 'ingreso' ? 'selected' : '' ?>>Ingreso</option>
                    <option value="egreso" <?= isset($movimiento['tipo_movimiento']) && $movimiento['tipo_movimiento'] === 'egreso' ? 'selected' : '' ?>>Egreso</option>
                </select>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-12">
                <label class="form-label">Descripción</label>
                <input type="text" name="descripcion" class="form-control" value="<?= htmlspecialchars($movimiento['descripcion'] ?? '') ?>" required>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-12">
                <label class="form-label">Fuentes de Fondos</label>
                <table class="table table-striped table-bordered" id="tablaDetalles">
                    <thead>
                        <tr>
                            <th>Cuenta</th>
                            <th>Moneda</th>
                            <th>Monto</th>
                            <th>Tasa</th>
                            <th>Base</th>
                            <th>Referencia</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($movimiento['detalles'])): ?>
                            <?php foreach ($movimiento['detalles'] as $detalle): ?>
                                <tr>
                                    <td>
                                        <select class="form-control id_cuenta_detalle" name="detalles[id_cuenta][]" required>
                                            <?php foreach ($cuentas as $cuenta): ?>
                                                <option value="<?= htmlspecialchars($cuenta['id_cuenta']) ?>" <?= isset($detalle['id_cuenta']) && $detalle['id_cuenta'] == $cuenta['id_cuenta'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($cuenta['nombre']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td>
                                        <select class="form-control id_moneda_detalle" name="detalles[id_moneda][]" required>
                                            <?php foreach ($monedas as $moneda): ?>
                                                <option value="<?= htmlspecialchars($moneda['id_moneda']) ?>" <?= isset($detalle['id_moneda']) && $detalle['id_moneda'] == $moneda['id_moneda'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($moneda['codigo']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td><input type="number" step="0.01" class="form-control monto" name="detalles[monto][]" value="<?= htmlspecialchars($detalle['monto'] ?? 0) ?>" required></td>
                                    <td><input type="number" step="0.01" class="form-control tasa" name="detalles[tasa][]" value="<?= htmlspecialchars($detalle['tasa'] ?? 1) ?>" required></td>
                                    <td><input type="number" step="0.01" class="form-control monto_base" name="detalles[monto_base][]" value="<?= htmlspecialchars($detalle['monto_base'] ?? 0) ?>" required></td>
                                    <td><input type="text" class="form-control referencia" name="detalles[referencia][]" value="<?= htmlspecialchars($detalle['referencia'] ?? '') ?>"></td>
                                    <td><button type="button" class="btn btn-danger btn-sm remove-row">Eliminar</button></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td>
                                    <select class="form-control id_cuenta_detalle" name="detalles[id_cuenta][]" required>
                                        <?php foreach ($cuentas as $cuenta): ?>
                                            <option value="<?= htmlspecialchars($cuenta['id_cuenta']) ?>"><?= htmlspecialchars($cuenta['nombre']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td>
                                    <select class="form-control id_moneda_detalle" name="detalles[id_moneda][]" required>
                                        <?php foreach ($monedas as $moneda): ?>
                                            <option value="<?= htmlspecialchars($moneda['id_moneda']) ?>"><?= htmlspecialchars($moneda['codigo']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td><input type="number" step="0.01" class="form-control monto" name="detalles[monto][]" value="0" required></td>
                                <td><input type="number" step="0.01" class="form-control tasa" name="detalles[tasa][]" value="1" required></td>
                                <td><input type="number" step="0.01" class="form-control monto_base" name="detalles[monto_base][]" value="0" required></td>
                                <td><input type="text" class="form-control referencia" name="detalles[referencia][]" value=""></td>
                                <td><button type="button" class="btn btn-danger btn-sm remove-row">Eliminar</button></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="7">
                                <button type="button" class="btn btn-primary btn-sm add-row">Agregar Fuente/Destino</button>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label">Cuenta Contable</label>
                <select name="id_plan_cuenta" class="form-control" required>
                    <option value="">Seleccione</option>
                    <?php foreach ($cuentas_contables as $cuenta_contable): ?>
                        <option value="<?= htmlspecialchars($cuenta_contable['id_plan_cuenta']) ?>" <?= isset($movimiento['id_plan_cuenta']) && $movimiento['id_plan_cuenta'] == $cuenta_contable['id_plan_cuenta'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cuenta_contable['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Mes Contable</label>
                <select name="mes_contable" class="form-control" required>
                    <option value="">-- Seleccione --</option>
                    <?php for ($i = 1; $i <= 12; $i++): ?>
                        <option value="<?= sprintf('%02d', $i) ?>" <?= isset($movimiento['mes_contable']) && $movimiento['mes_contable'] === sprintf('%02d', $i) ? 'selected' : '' ?>>
                            <?= sprintf('%02d', $i) ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Año Contable</label>
                <select name="anio_contable" class="form-control" required>
                    <option value="">-- Seleccione --</option>
                    <?php for ($i = 2020; $i <= 2030; $i++): ?>
                        <option value="<?= $i ?>" <?= isset($movimiento['anio_contable']) && $movimiento['anio_contable'] == $i ? 'selected' : '' ?>>
                            <?= $i ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-12">
                <label class="form-label">Estado</label>
                <select name="estado" class="form-control" required>
                    <option value="pendiente" <?= isset($movimiento['estado']) && $movimiento['estado'] === 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                    <option value="conciliado" <?= isset($movimiento['estado']) && $movimiento['estado'] === 'conciliado' ? 'selected' : '' ?>>Conciliado</option>
                    <option value="cancelado" <?= isset($movimiento['estado']) && $movimiento['estado'] === 'cancelado' ? 'selected' : '' ?>>Cancelado</option>
                </select>
            </div>
        </div>
    </form>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
    <button type="submit" class="btn btn-primary" form="movimientoForm">Guardar</button>
</div>
<script>
$(document).ready(function() {
    $('.add-row').on('click', function() {
        const row = `
            <tr>
                <td>
                    <select class="form-control id_cuenta_detalle" name="detalles[id_cuenta][]" required>
                        <?php foreach ($cuentas as $cuenta): ?>
                            <option value="<?= htmlspecialchars($cuenta['id_cuenta']) ?>"><?= htmlspecialchars($cuenta['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td>
                    <select class="form-control id_moneda_detalle" name="detalles[id_moneda][]" required>
                        <?php foreach ($monedas as $moneda): ?>
                            <option value="<?= htmlspecialchars($moneda['id_moneda']) ?>"><?= htmlspecialchars($moneda['codigo']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td><input type="number" step="0.01" class="form-control monto" name="detalles[monto][]" value="0" required></td>
                <td><input type="number" step="0.01" class="form-control tasa" name="detalles[tasa][]" value="1" required></td>
                <td><input type="number" step="0.01" class="form-control monto_base" name="detalles[monto_base][]" value="0" required></td>
                <td><input type="text" class="form-control referencia" name="detalles[referencia][]" value=""></td>
                <td><button type="button" class="btn btn-danger btn-sm remove-row">Eliminar</button></td>
            </tr>`;
        $('#tablaDetalles tbody').append(row);
    });

    $(document).on('click', '.remove-row', function() {
        $(this).closest('tr').remove();
    });

    $('#movimientoForm').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: 'guardar_movimiento_real.php',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.status === 'ok') {
                    Swal.fire('Éxito', 'Movimiento guardado correctamente', 'success');
                    $('#modalMovimiento').modal('hide');
                    tabla.ajax.reload();
                } else {
                    Swal.fire('Error', response.message || 'No se pudo guardar el movimiento', 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'Error al guardar el movimiento', 'error');
            }
        });
    });

    function toggleFormFields() {
        const tipo = $('select[name="tipo_movimiento"]').val();
        // Aquí puedes agregar lógica para habilitar/deshabilitar campos según el tipo si es necesario
    }
});
</script>
<?php
    $html = ob_get_clean();
    echo $html;
} catch (Exception $e) {
    http_response_code(500);
    echo '<div class="alert alert-danger">' . htmlspecialchars($e->getMessage()) . '</div>';
}