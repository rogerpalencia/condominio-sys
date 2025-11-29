<!-- MODAL DE CARGA DE COBRO -->
<div class="modal fade" id="modalCargarCobro" tabindex="-1" aria-labelledby="modalCargarCobroLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <form id="formCargarCobro">
        <div class="modal-header">
          <h5 class="modal-title mb-2" id="modalCargarCobroLabel">
            Cargar Notificación de Cobro para el Inmueble:
            <span id="identificacion_cobro" class="fw-bold"></span>
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>

        <div class="modal-body">
          <input type="hidden" name="id_inmueble" id="id_inmueble_cobro">
          <!-- AÑADIDO: id_condominio oculto -->
          <input type="hidden" name="id_condominio" id="id_condominio_cobro" value="<?php echo (int)$row_condo['id_condominio']; ?>">

          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label">Fecha de Emisión</label>
              <input type="date" class="form-control" name="fecha_emision" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Fecha Límite de Pago</label>
              <input type="date" class="form-control" name="fecha_limite_pago" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Moneda</label>
              <select class="form-select" name="id_moneda" required>
                <option value="">Seleccione</option>
                <option value="1">Bolívar</option>
                <option value="2">Dólar</option>
              </select>
            </div>
            <div class="col-md-12">
              <label class="form-label">Descripción de la Notificación de Cobro:</label>
              <input type="text" class="form-control text-uppercase" name="descripcion_cab" maxlength="100" required oninput="this.value = this.value.toUpperCase();">
            </div>
          </div>

          <hr class="my-4">

          <h5>Detalle del Cobro</h5>
          <table class="table table-bordered" id="tablaDetalleCobro">
            <thead>
              <tr>
                <th>Cuenta</th>
                <th>Descripción</th>
                <th>Monto</th>
                <th>
                  <button type="button" class="btn btn-sm btn-success" onclick="agregarFilaDetalle()">+ Agregar Concepto</button>
                </th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>

          <div class="row g-3 mt-3">
            <div class="col-md-4">
              <label class="form-label">Monto en Gastos:</label>
              <input type="text" class="form-control" name="monto_en_gastos" value="" readonly>
            </div>
            <div class="col-md-4">
              <label class="form-label">Descuento por Pronto Pago:</label>
              <input type="text" class="form-control" name="monto_descuento_pronto_pago" value="" oninput="recalcularTotales()">
            </div>
            <div class="col-md-4">
              <label class="form-label">Total Notificación:</label>
              <input type="text" class="form-control" name="monto_notificacion" readonly>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Guardar</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php
function getSelectPlanCuentasIngreso($conn, $id_condominio) {
  $stmt = $conn->prepare("SELECT id_plan, codigo, nombre
                          FROM plan_cuenta
                          WHERE id_condominio = :id_condominio
                            AND tipo = 'ingreso'
                          ORDER BY codigo ASC");
  $stmt->bindParam(':id_condominio', $id_condominio, PDO::PARAM_INT);
  $stmt->execute();
  $opciones = "<option value=\"\">Seleccione cuenta</option>";
  while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $label = htmlspecialchars("{$row['codigo']} - {$row['nombre']}");
    $opciones .= "<option value=\"{$row['id_plan']}\">{$label}</option>";
  }
  return $opciones;
}
?>

<script>
function agregarFilaDetalle() {
  const fila = `
    <tr>
      <td>
        <select name="id_plan_cuenta[]" class="form-select" required>
          <?php echo getSelectPlanCuentasIngreso($conn, $row_condo['id_condominio']); ?>
        </select>
      </td>
      <td><input type="text" name="descripcion[]" class="form-control text-uppercase" required oninput="this.value = this.value.toUpperCase();"></td>
      <td><input type="text" name="monto[]" class="form-control" required oninput="recalcularTotales()"></td>
      <td><button type="button" class="btn btn-sm btn-danger" onclick="this.closest('tr').remove(); recalcularTotales()">x</button></td>
    </tr>`;
  document.querySelector('#tablaDetalleCobro tbody').insertAdjacentHTML('beforeend', fila);
  recalcularTotales();
}

$(document).ready(function () {
  agregarFilaDetalle();

  $('#formCargarCobro').on('submit', function(e) {
    e.preventDefault();

    // Validación simple en cliente
    const gastos = parseFloat((document.querySelector('input[name="monto_en_gastos"]').value || '0').replace(',', '.')) || 0;
    const pp = parseFloat((document.querySelector('input[name="monto_descuento_pronto_pago"]').value || '0').replace(',', '.')) || 0;
    if (pp < 0) return Swal.fire('Atención','El pronto pago no puede ser negativo.','warning');
    if (pp > gastos) return Swal.fire('Atención','El pronto pago no puede exceder el total de gastos.','warning');

    const formData = $(this).serialize();

    $.ajax({
      url: 'notificacion_cobro_insert.php',
      type: 'POST',
      data: formData,
      dataType: 'json',
      success: function(resp) {
        if (resp.status === 'ok') {
          $('#modalCargarCobro').modal('hide');
          $('#formCargarCobro')[0].reset();
          $('#tablaDetalleCobro tbody').empty();
          Swal.fire('Guardado', 'La notificación fue registrada correctamente.', 'success');
          // TODO: refrescar DataTable si aplica
        } else {
          Swal.fire('Error', resp.message || 'No se pudo registrar.', 'error');
        }
      },
      error: function(xhr) {
        Swal.fire('Error', 'Error de red: ' + (xhr.responseText || xhr.statusText), 'error');
      }
    });
  });
});

function recalcularTotales() {
  let totalGastos = 0;
  document.querySelectorAll('input[name="monto[]"]').forEach(input => {
    let valor = parseFloat((input.value || '').replace(',', '.'));
    if (!isNaN(valor)) totalGastos += valor;
  });

  document.querySelector('input[name="monto_en_gastos"]').value = totalGastos.toFixed(2);

  const prontoPago = parseFloat((document.querySelector('input[name="monto_descuento_pronto_pago"]').value || '0').replace(',', '.')) || 0;
  const totalNotificacion = totalGastos - prontoPago;
  document.querySelector('input[name="monto_notificacion"]').value = totalNotificacion.toFixed(2);
}
</script>
