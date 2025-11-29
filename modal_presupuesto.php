<!-- MODAL DE PRESUPUESTO CON SELECT DE CUENTAS CONTABLES -->
<?php
// helper para llenar select solo si aún no existe
if (!function_exists('getSelectPlanCuentasIngreso')) {
    function getSelectPlanCuentasIngreso($conn, $id_condominio) {
        $stmt = $conn->prepare("SELECT id_plan, codigo, nombre FROM plan_cuenta WHERE id_condominio = :id_condominio AND tipo = 'ingreso' ORDER BY codigo ASC");
        $stmt->bindParam(':id_condominio', $id_condominio, PDO::PARAM_INT);
        $stmt->execute();
        $opciones = "<option value=\"\">Seleccione cuenta</option>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $label = htmlspecialchars("{$row['codigo']} - {$row['nombre']}");
            $opciones .= "<option value=\"{$row['id_plan']}\">{$label}</option>";
        }
        return $opciones;
    }
}

if (!function_exists('getSelectMonedas')) {
    function getSelectMonedas($conn) {
        $stmt = $conn->prepare("SELECT id_moneda, nombre FROM moneda ORDER BY nombre ASC");
        $stmt->execute();
        $opciones = "<option value=\"\">Seleccione moneda</option>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $opciones .= "<option value=\"{$row['id_moneda']}\">{$row['nombre']}</option>";
        }
        return $opciones;
    }
}
?>
<div class="modal fade" id="modalPresupuesto" tabindex="-1" aria-labelledby="modalPresupuestoLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <form id="formPresupuesto">
        <div class="modal-header">
          <h5 class="modal-title" id="modalPresupuestoLabel">Crear o Editar Presupuesto Mensual</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id_condominio" id="id_condominio_presupuesto">

          <div class="row g-3">
            <div class="col-md-3">
              <label class="form-label">Mes</label>
              <select class="form-select" name="mes" required >
                <?php 
                setlocale(LC_TIME, 'es_ES.UTF-8'); // Establecer el idioma a español
                for ($i = 1; $i <= 12; $i++): ?>
                  <option value="<?= $i ?>"><?= ucfirst(strftime('%B', mktime(0, 0, 0, $i, 10))) ?></option>
                <?php endfor; ?>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">Año</label>
              <input type="number" class="form-control" name="anio" value="<?= date('Y') ?>" required>
            </div>
            <div class="col-md-3">
              <label class="form-label">Tipo de Esquema</label>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="tipo_cuota" id="cuotaFija" value="fija">
                <label class="form-check-label" for="cuotaFija">Cuota Fija</label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="tipo_cuota" id="cuotaVariable" value="alicuota">
                <label class="form-check-label" for="cuotaVariable">Cuota Variable</label>
              </div>
            </div>
            <div class="col-md-3">
              <label class="form-label">Moneda</label>
              <select class="form-select" name="id_moneda" required>
                <?php echo getSelectMonedas($conn); ?>
              </select>
            </div>
          </div>

          <hr class="my-4">

          <h5>Detalle del Presupuesto</h5>
          <table class="table table-bordered" id="tablaDetallePresupuesto">
            <thead>
              <tr>
                <th>Cuenta</th>
                <th>Descripción</th>
                <th>Monto</th>
                <th id="columnaAccion"><button type="button" class="btn btn-sm btn-success" onclick="agregarFilaPresupuesto()">+ Agregar Concepto</button></th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>

          <div class="row g-3 mt-3">
            <div class="col-md-4 offset-md-8">
              <label class="form-label">Total Presupuesto:</label>
              <input type="text" class="form-control" name="total_presupuesto" readonly>
            </div>
          </div>

        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Guardar Presupuesto</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>



document.addEventListener('DOMContentLoaded', () => {
  const formPresupuesto = document.getElementById('formPresupuesto');

  formPresupuesto.addEventListener('submit', function (e) {
    e.preventDefault();

    const formData = new FormData(this);

    fetch('guardar_presupuesto.php', {
      method: 'POST',
      body: formData
    })
    .then(res => res.json())
    .then(data => {
      if (data.status === 'ok') {
        Swal.fire({
          icon: 'success',
          title: 'Presupuesto guardado',
          text: 'El presupuesto se registró correctamente.',
          timer: 2000,
          showConfirmButton: false
        }).then(() => {
          location.reload(); // Opcional: recarga la página para ver los cambios
        });
      } else {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: data.message || 'No se pudo guardar el presupuesto.'
        });
      }
    })
    .catch(err => {
      console.error('Error en la solicitud:', err);
      Swal.fire({
        icon: 'error',
        title: 'Error de red',
        text: 'No se pudo conectar con el servidor.'
      });
    });
  });
});










function agregarFilaPresupuesto() {
  const fila = `\
    <tr>\
      <td>\
        <select name=\"id_plan_cuenta[]\" class=\"form-select\" required>\
          <?php echo getSelectPlanCuentasIngreso($conn, $id_condominio); ?>\
        </select>\
      </td>\
      <td><input type=\"text\" name=\"descripcion[]\" class=\"form-control text-uppercase\" required oninput=\"this.value = this.value.toUpperCase();\"></td>\
      <td><input type=\"text\" name=\"monto[]\" class=\"form-control\" required oninput=\"recalcularPresupuesto()\"></td>\
      <td><button type=\"button\" class=\"btn btn-sm btn-danger\" onclick=\"this.closest('tr').remove(); recalcularPresupuesto()\">x</button></td>\
    </tr>`;
  document.querySelector('#tablaDetallePresupuesto tbody').insertAdjacentHTML('beforeend', fila);
  recalcularPresupuesto();
}

function recalcularPresupuesto() {
  let total = 0;
  document.querySelectorAll('input[name="monto[]"]').forEach(input => {
    const valor = parseFloat(input.value.replace(',', '.'));
    if (!isNaN(valor)) total += valor;
  });
  document.querySelector('input[name="total_presupuesto"]').value = total.toFixed(2);
}

function aplicarEsquema(esquema) {
  const cuerpo = document.querySelector('#tablaDetallePresupuesto tbody');
  cuerpo.innerHTML = '';
  document.getElementById('columnaAccion').style.display = esquema === 'alicuota' ? '' : 'none';

  if (esquema === 'fija') {
    const mes = parseInt(document.querySelector('select[name="mes"]').value);
    const anio = document.querySelector('input[name="anio"]').value;
    const descripcion = `CUOTA DEL MES DE ${new Date(2000, mes - 1).toLocaleString('es-ES', { month: 'long' }).toUpperCase()} ${anio}`;

    fetch('get_cuota_fija.php')
      .then(r => r.json())
      .then(d => {
        const fila = `\
          <tr>\
            <td>
  <input type="hidden" name="id_plan_cuenta[]" value="46">  <input type="text" class="form-control" value="4.1" readonly>
</td>
            <td><input type=\"text\" name=\"descripcion[]\" class=\"form-control\" value=\"${descripcion}\" readonly></td>\
            <td><input type=\"text\" name=\"monto[]\" class=\"form-control\" value=\"${parseFloat(d.monto).toFixed(2)}\" readonly></td>\
            <td></td>\
          </tr>`;
        cuerpo.innerHTML = fila;
        recalcularPresupuesto();
      });
  } else {
    agregarFilaPresupuesto();
  }
}

// esquema change listener
$(document).on('change', 'input[name="tipo_cuota"]', function(){
  aplicarEsquema(this.value);
});
</script>
