<?php

function getSelectPlanCuentas($conn, $id_condominio) {
    $stmt = $conn->prepare("SELECT id_plan, codigo, nombre FROM plan_cuenta WHERE id_condominio = :id_condominio ORDER BY codigo ASC");
    $stmt->bindParam(':id_condominio', $id_condominio, PDO::PARAM_INT);
    $stmt->execute();
    $opciones = '';
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $label = htmlspecialchars("{$row['codigo']} - {$row['nombre']}");
        $opciones .= "<option value='{$row['id_plan']}'>{$label}</option>";
    }
    return $opciones;
}
?>

<!-- Modal Nuevo/Editar Inmueble -->
<div class="modal fade" id="modalNuevoInmueble" tabindex="-1" role="dialog" aria-labelledby="nuevoInmuebleLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <form id="formNuevoInmueble" method="POST">
        <div class="modal-header">
          <h5 class="modal-title" id="nuevoInmuebleLabel">Nuevo Inmueble</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label for="identificacion" class="form-label">Identificación</label>
              <input type="text" class="form-control" name="identificacion" required>
            </div>
            <div class="col-md-6">
              <label for="tipo" class="form-label">Tipo de Inmueble</label>
              <select class="form-select" name="tipo" id="tipo" required>
                <option value="1">Apartamento</option>
                <option value="2">Casa</option>
                <option value="3">Local</option>
              </select>
            </div>

            <div class="col-md-4 grupo-apartamento">
              <label for="torre" class="form-label">Torre</label>
              <input type="text" class="form-control" name="torre">
            </div>
            <div class="col-md-4 grupo-apartamento">
              <label for="piso" class="form-label">Piso</label>
              <input type="text" class="form-control" name="piso">
            </div>

            <div class="col-md-4 grupo-casa-local d-none">
              <label for="calle" class="form-label">Calle</label>
              <input type="text" class="form-control" name="calle">
            </div>
            <div class="col-md-4 grupo-casa-local d-none">
              <label for="manzana" class="form-label">Manzana</label>
              <input type="text" class="form-control" name="manzana">
            </div>

            <div class="col-md-4">
              <label for="alicuota" class="form-label">Alicuota (%)</label>
              <input type="text" class="form-control" name="alicuota" id="alicuota" required
                maxlength="12" placeholder="Ej: 0,00000056"
                oninput="this.value = this.value.replace(/[^0-9.,]/g, '')">
            </div>

            <input type="hidden" name="id_condominio" value="<?= $row_condo['id_condominio']; ?>">
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

<script>
document.addEventListener('DOMContentLoaded', function () {
  const tipoSelect = document.getElementById('tipo');
  if (tipoSelect) {
    tipoSelect.addEventListener('change', function () {
      actualizarCamposPorTipo(this.value);
    });
    actualizarCamposPorTipo(tipoSelect.value);
  }
});

function actualizarCamposPorTipo(tipo) {
  const grupoApartamento = document.querySelectorAll('.grupo-apartamento');
  const grupoCasaLocal = document.querySelectorAll('.grupo-casa-local');
  if (tipo === '1' || tipo === 1) {
    grupoApartamento.forEach(el => el.classList.remove('d-none'));
    grupoCasaLocal.forEach(el => el.classList.add('d-none'));
  } else {
    grupoApartamento.forEach(el => el.classList.add('d-none'));
    grupoCasaLocal.forEach(el => el.classList.remove('d-none'));
  }
}

$(document).ready(function () {
  $('#formNuevoInmueble').on('submit', function(e) {
    e.preventDefault();

    let valor = $('#alicuota').val().replace(',', '.');
    $('#alicuota').val(valor);

    let formData = $(this).serialize();

    // Añadir id_condominio manualmente al formData
    formData += '&id_condominio=' + encodeURIComponent($('input[name="id_condominio"]').val());

    $.ajax({
      url: 'inmueble_insert.php',
      type: 'POST',
      data: formData,
      dataType: 'json',
      success: function(response) {
      if (response.status === 'ok') {
        $('#modalNuevoInmueble').modal('hide');
        $('#formNuevoInmueble')[0].reset();
        $('#tablaInmuebles').DataTable().ajax.reload();
        Swal.fire('Guardado', 'Inmueble guardado correctamente.', 'success');
      } else {
        Swal.fire('Error', response.message, 'error');
      }
      },
      error: function(xhr) {
      Swal.fire('Error', 'Error del servidor: ' + xhr.responseText, 'error');
      }
    });

    return false;
  });
});
</script>
