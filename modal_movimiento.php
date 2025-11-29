<!-- Modal para nuevo/editar movimiento -->
<div class="modal fade" id="modalMovimiento" tabindex="-1" role="dialog" aria-labelledby="modalMovimientoLabel" aria-hidden="true">
  <div class="modal-dialog modal-md" role="document">
    <form id="formMovimiento" method="POST" action="guardar_movimiento.php">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalMovimientoLabel">Registrar Movimiento</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id_movimiento" id="id_movimiento">

          <div class="mb-3">
            <label for="id_cuenta" class="form-label">Cuenta</label>
            <select class="form-select" name="id_cuenta" id="id_cuenta" required>
              <option value="">Seleccione...</option>
              <?php
              $sqlCuentas = "SELECT c.id_cuenta, c.nombre, c.numero, c.saldo_actual, m.codigo AS moneda
                             FROM cuenta c
                             JOIN moneda m ON c.id_moneda = m.id_moneda
                             WHERE c.id_condominio = :condo AND c.estatus = true";
              $stmtCuentas = $conn->prepare($sqlCuentas);
              $stmtCuentas->execute([':condo' => $id_condominio]);
              while ($cuenta = $stmtCuentas->fetch(PDO::FETCH_ASSOC)) {
                  echo "<option value='{$cuenta['id_cuenta']}' data-moneda='{$cuenta['moneda']}'>"
                      . htmlspecialchars("{$cuenta['nombre']} - {$cuenta['numero']} (Saldo: " . number_format($cuenta['saldo_actual'], 2) . " {$cuenta['moneda']})")
                      . "</option>";
              }
              ?>
            </select>
          </div>

          <div class="mb-3">
            <label for="id_moneda" class="form-label">Moneda del Movimiento</label>
            <select class="form-select" name="id_moneda" id="id_moneda" required>
              <option value="">Seleccione...</option>
              <!-- Se cargará dinámicamente via AJAX -->
            </select>
          </div>

          <div class="mb-3">
            <label for="tipo_movimiento" class="form-label">Tipo</label>
            <select class="form-select" name="tipo_movimiento" id="tipo_movimiento" required>
              <option value="ingreso">Ingreso</option>
              <option value="egreso">Egreso</option>
            </select>
          </div>

          <div class="mb-3">
            <label for="monto" class="form-label">Monto</label>
            <input type="number" step="0.01" name="monto" id="monto" class="form-control" required>
          </div>

          <div class="mb-3">
            <label for="fecha_movimiento" class="form-label">Fecha</label>
            <input type="date" name="fecha_movimiento" id="fecha_movimiento" class="form-control" value="<?= date('Y-m-d') ?>" required>
          </div>

          <div class="mb-3" id="grupo_tasa" style="display:none;">
            <label for="tasa" class="form-label">Tasa de Cambio</label>
            <input type="number" step="0.0001" name="tasa" id="tasa" class="form-control" readonly>
          </div>

          <div class="mb-3">
            <label for="estado" class="form-label">Estado</label>
            <select name="estado" id="estado" class="form-select" required>
              <option value="pendiente">Pendiente</option>
              <option value="conciliado">Conciliado</option>
              <option value="cancelado">Cancelado</option>
            </select>
          </div>

          <div class="mb-3">
            <label for="observacion" class="form-label">Observación</label>
            <textarea class="form-control" name="observacion" id="observacion" rows="2"></textarea>
          </div>

        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Guardar</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
document.getElementById('id_cuenta').addEventListener('change', function () {
    const idCuenta = this.value;
    if (idCuenta) {
        $.get('get_cuenta_moneda.php', { id_cuenta: idCuenta }, function (data) {
            $('#id_moneda').val(data.id_moneda); // Establecer moneda por defecto
            actualizarTasa();
        }, 'json');
    }
});

document.getElementById('id_moneda').addEventListener('change', actualizarTasa);

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

$(document).ready(function () {
    $.get('get_monedas.php', function (data) {
        let select = $('#id_moneda');
        select.empty().append('<option value="">Seleccione...</option>');
        data.forEach(moneda => {
            select.append(`<option value="${moneda.id_moneda}">${moneda.codigo} - ${moneda.nombre}</option>`);
        });
    }, 'json');
});
</script>