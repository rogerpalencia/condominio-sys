<!-- ========== MODAL · CARGAR NOTIFICACIÓN MAESTRA ========== -->
<div class="modal fade" id="modalCargarCobroMaster" tabindex="-1"
     aria-labelledby="modalCargarCobroMasterLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl"><div class="modal-content">
    <form id="formCargarCobroMaster">

      <div class="modal-header">
        <h5 class="modal-title mb-2" id="modalCargarCobroMasterLabel">
          Cargar Notificación MAESTRA (Condominio:xxxxxxxxxxxxxxxxxxxx
          <span class="fw-bold"><?= htmlspecialchars($row_condo['nombre']); ?></span>)
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">

        <!--  hidden: condominio / moneda base  -->
        <input type="hidden" name="id_condominio" value="<?= $row_condo['id_condominio']; ?>">
        <input type="hidden" name="id_moneda"    value="<?= $row_condo['id_moneda']; ?>">

        <div class="row g-3">

          <div class="col-md-2">
            <label class="form-label">Año</label>
            <input type="number" name="anio" class="form-control"
                   value="<?= date('Y'); ?>" min="2000" max="2100" required>
          </div>

          <div class="col-md-2">
            <label class="form-label">Mes</label>
            <select name="mes" class="form-select" required>
              <option value="0">— Extraordinaria —</option>
              <?php foreach([
                1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',
                7=>'Julio',8=>'Agosto',9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre'] as $num=>$nom): ?>
                <option value="<?= $num ?>"><?= $nom ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-md-4">
            <label class="form-label">Fecha de Emisión</label>
            <input type="date" name="fecha_emision" class="form-control"
                   value="<?= date('Y-m-d'); ?>" required>
          </div>

          <div class="col-md-4">
            <label class="form-label">Fecha Vencimiento</label>
            <input type="date" name="fecha_vencimiento" class="form-control"
                   value="<?= date('Y-m-d',strtotime('+30 days')); ?>" required>
          </div>

          <div class="col-md-12">
            <label class="form-label">Descripción (cabecera)</label>
            <input type="text" name="descripcion_cab"
                   class="form-control text-uppercase" maxlength="150" required
                   oninput="this.value=this.value.toUpperCase();">
          </div>

          <div class="col-md-4">
            <label class="form-label">Tipo de Notificación</label>
            <select name="id_tipo" class="form-select" required>
              <option value="1" selected>Presupuesto (genera hijas)</option>
              <option value="2">Relación de Ingresos/Egresos</option>
            </select>
          </div>

          <div class="col-md-4">
            <label class="form-label">Moneda Base</label>
            <input type="text" class="form-control"
                   value="<?= htmlspecialchars($row_condo['codigo_moneda']); ?>"
                   readonly>
          </div>

          <div class="col-md-4">
            <label class="form-label">Activa</label>
            <select name="activa" class="form-select">
              <option value="false" selected>No</option>
              <option value="true">Sí</option>
            </select>
          </div>

        </div><!-- .row -->

        <hr class="my-4">

        <h5>Detalle del Presupuesto</h5>
        <table class="table table-bordered" id="tablaDetalleMaster">
          <thead>
            <tr>
              <th>Cuenta</th>
              <th>Descripción</th>
              <th>Monto</th>
              <th>
                <button type="button" class="btn btn-sm btn-success"
                        onclick="agregarFilaDetalleMaster()">+ Agregar Concepto</button>
              </th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>

        <div class="row g-3 mt-3">
          <div class="col-md-6">
            <label class="form-label">Total Presupuesto (<?= $row_condo['codigo_moneda']; ?>)</label>
            <input type="text" name="monto_total" class="form-control" readonly>
          </div>
        </div>
      </div><!-- .modal-body -->

      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Guardar</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
      </div>
    </form>
  </div></div>
</div><!-- /modal -->

<?php
/* ===== Helper: combo de plan de cuenta ingreso ===== */
function selectPlanIngreso($conn,$id_condo){
  $q="SELECT id_plan,codigo,nombre
      FROM plan_cuenta
      WHERE id_condominio=:c AND tipo='ingreso'
      ORDER BY codigo";
  $st=$conn->prepare($q); $st->execute([':c'=>$id_condo]);
  $out='<option value="">Seleccione</option>';
  while($r=$st->fetch(PDO::FETCH_ASSOC)){
     $out.='<option value="'.$r['id_plan'].'">'.
           htmlspecialchars($r['codigo'].' - '.$r['nombre']).'</option>';
  }
  return $out;
}
?>

<script>
function agregarFilaDetalleMaster(){
  const fila=`<tr>
    <td><select name="id_plan_cuenta[]" class="form-select" required>
      <?= selectPlanIngreso($conn,$row_condo['id_condominio']); ?>
    </select></td>
    <td><input type="text" name="descripcion[]" class="form-control text-uppercase"
               required oninput="this.value=this.value.toUpperCase();"></td>
    <td><input type="text" name="monto[]" class="form-control"
               required oninput="recalcularTotalMaster()"></td>
    <td><button type="button" class="btn btn-sm btn-danger"
                onclick="this.closest('tr').remove();recalcularTotalMaster()">x</button></td>
  </tr>`;
  document.querySelector('#tablaDetalleMaster tbody')
          .insertAdjacentHTML('beforeend',fila);
  recalcularTotalMaster();
}
function recalcularTotalMaster(){
  let tot=0;
  document.querySelectorAll('#tablaDetalleMaster input[name="monto[]"]').forEach(i=>{
     const v=parseFloat(i.value.replace(',','.')); if(!isNaN(v)) tot+=v;
  });
  document.querySelector('input[name="monto_total"]').value=tot.toFixed(2);
}
$(function(){
  agregarFilaDetalleMaster();   // fila inicial

  $('#formCargarCobroMaster').on('submit',e=>{
    e.preventDefault();
    // Añadir id_condominio manualmente si es necesario
    const data = $(e.target).serialize() + '&id_condominio=' + encodeURIComponent($row_condo['id_condominio']);
    $.post('notificacion_cobro_insert.php',
           data,
           resp=>{
             if(resp.status==='ok'){
               $('#modalCargarCobroMaster').modal('hide');
               Swal.fire('Guardado','Notificación maestra registrada','success');
               location.reload();  // o refresca tu tabla
             }else Swal.fire('Error',resp.message,'error');
           },'json')
     .fail(xhr=>Swal.fire('Error','Red: '+xhr.responseText,'error'));
  });
});
</script>
