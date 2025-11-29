<?php
@session_start();
require_once 'core/PDO.class.php';

/**
 *  modal_pago.php
 *  ▸ Versión original funcional + ajustes mínimos 2025‑05‑02
 *    1) Se eliminó el scroll horizontal de los contenedores de tablas.
 *    2) Se añadió un margen inferior a cada sección (.step-section)
 *       para evitar que la tabla superior se encime sobre la inferior
 *       en pantallas pequeñas cuando ambas están visibles.
 */

$conn = DB::getInstance();
$id_usuario = $_SESSION['userid'];

// ─────────── Condominio y moneda base ───────────
$sql = "SELECT a.id_condominio, c.nombre
        FROM administradores a
        JOIN condominio c ON a.id_condominio = c.id_condominio
        WHERE a.id_usuario = :id_usuario AND a.estatus = TRUE";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
$stmt->execute();
$row_condo = $stmt->fetch(PDO::FETCH_ASSOC);
$id_condominio = (int)$row_condo['id_condominio'];

$stmt = $conn->prepare("SELECT id_moneda FROM condominio WHERE id_condominio = :id");
$stmt->bindParam(':id', $id_condominio, PDO::PARAM_INT);
$stmt->execute();
$moneda_base_id = (int)$stmt->fetchColumn();

// ─────────── Listado de cuentas ───────────
$sql = "SELECT c.id_cuenta, c.nombre, c.tipo, m.codigo AS moneda, m.id_moneda, c.banco
        FROM cuenta c
        JOIN moneda m ON c.id_moneda = m.id_moneda
        WHERE c.id_condominio = :id
        ORDER BY c.banco, c.nombre";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':id', $id_condominio, PDO::PARAM_INT);
$stmt->execute();
$cuentas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!-- ═════════════ ESTILOS ═════════════ -->
<style>



  .modal-body { overflow-y: auto; }

  /* Contenedor con 5 filas visibles ─ solo scroll vertical */
  .table-responsive-wrapper {
    max-height: 13rem;     /* ≈ 5 filas */
    overflow-y: auto;
    /* overflow-x eliminado para quitar la barra horizontal */
  }

  /* Ajustes de celdas como en la versión original */
  #modalCargarPago table.table th,
  #modalCargarPago table.table td {
    padding: 0.25rem 0.4rem;
    line-height: 1.1;
    font-size: 0.85rem;
    white-space: nowrap;
  }

  /* Evita que las tablas se encimen en móvil */
  .step-section { margin-bottom: 1rem; }

  /* Step‑mode sin cambios */
  @media (max-width: 767.98px) {
    .step-mode .step-section { display: none; }
    .step-mode .step-section.active { display: block; }
    #modalCargarPago .modal-dialog { margin: 0; height: 100vh; flex-direction: column; }
    #modalCargarPago .modal-content { flex: 1 1 auto; height: 100%; max-height: 100%; }
  }

  #scrollArea{
    overflow-y: auto;
    /* 100vh – alto cabecera – alto pie – 2 rem de margen */
    max-height: calc(100vh - 180px);   /* ajusta 180 px si tu header/footer son más altos */
    overscroll-behavior: contain;      /* evita mover el fondo cuando llegas al tope */

/* Formas de Pago – filas compactas */
#tablaFormasPago th,
#tablaFormasPago td {
    padding: 0.1rem 0.1rem;
    line-height: 0.95;
}

/* Notificaciones Pendientes – un poco más altas */
#tablaPendientes th,
#tablaPendientes td {
    padding: 0.1rem 0.1rem;
    line-height: 0.95;
}


}

</style>

<!-- ═════════════ MODAL HTML (sin cambios de estructura) ═════════════ -->
<div class="modal fade" id="modalCargarPago" tabindex="-1" aria-labelledby="modalCargarPagoLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable modal-fullscreen-sm-down">

    <div class="modal-content">
      <form id="formCargarPago">
        <!-- ───── CABECERA ───── -->
        <div class="modal-header flex-wrap gap-2">
          <div class="row w-100 text-center">
            <div class="col-12 col-md-2">
              <label class="form-label">Pago del inmueble:</label>
              <h5 class="modal-title fw-bold" id="modalCargarPagoLabel"><span id="identificacion_pago"></span></h5>
            </div>
            <div class="col-12 col-md-3">
              <label class="form-label">Fecha de pago:</label>
              <input type="date" class="form-control mx-auto" name="fecha_pago" required value="<?= date('Y-m-d'); ?>">
            </div>
            <div class="col-12 col-md-6">
              <label class="form-label">Observaciones:</label>
              <input type="text" class="form-control text-uppercase mx-auto" name="observacion_pago" maxlength="100" required oninput="this.value = this.value.toUpperCase();">
            </div>
            <div class="col-12 col-md-1">
              <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal"></button>
            </div>
          </div>
        </div>

        <!-- ───── CUERPO ───── -->
        <div class="modal-body">

          <div id="scrollArea">
          <input type="hidden" name="id_inmueble" id="id_inmueble_pago">

          <!-- Formas de Pago -->
          <div class="step-section step-1 active">
            <h5>Formas de pago (Bancos y Créditos)</h5>
        
              <table class="table table-bordered align-middle mb-1" id="tablaFormasPago">
                <thead class="table-light">
                  <tr>
                    <th>Banco</th><th>Cuenta</th><th>Moneda</th><th>Referencia</th><th>Monto</th><th>Tasa</th><th>Base</th>
                  </tr>
                </thead>
                <tbody id="tablaFormasPagoBody">
                  <!-- Se cargan dinámicamente por JS -->
                </tbody>
              </table>
          
            <div id="resumenMontosPorMoneda" class="alert alert-secondary p-2 mb-2 d-none"></div>
            <div class="text-center mt-4 d-block d-md-none">
              <button type="button" class="btn btn-outline-primary" onclick="mostrarStep(2)">Siguiente</button>
            </div>
          </div>

          <!-- Notificaciones Pendientes -->
          <div class="step-section step-2">
            <h5>Notificaciones Pendientes</h5>
            <div class="table-responsive-wrapper mb-2" id="pendientesWrapper">
              <table class="table table-sm table-bordered mb-0" id="tablaPendientes">
                <thead class="table-light">
                  <tr>
                    <th>F. Emisión</th><th>Descripción</th><th>Moneda</th><th class="text-end">Por pagar</th><th class="text-end">Tasa</th><th class="text-end">Base</th><th class="text-center">Pagar</th><th>Abono</th>
                  </tr>
                </thead>
                <tbody></tbody>
              </table>
            </div>
            <div id="resumenPendientes" class="alert alert-secondary p-2 mb-2 d-none"></div>
            <div id="creditosDiv" class="alert alert-info py-2 d-none"></div>
            <div class="text-center d-block d-md-none">
              <button type="button" class="btn btn-outline-secondary" onclick="mostrarStep(1)">Volver</button>
            </div>
          </div>
        </div>
       
        <!-- ───── PIE ───── -->
        <div class="modal-footer">
          <div class="d-flex justify-content-between w-100">
            <button type="submit" class="btn btn-primary ms-auto">Guardar Pago</button>
          </div>
        </div>
      </form>
      
    </div>
  </div>
</div>
</div>

<!-- ═══════════════  SCRIPTS  ═══════════════ -->
<script src="assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
<script src="assets/libs/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js"></script>

<script>
/* ── Utilidades numéricas ─────────────────────── */
const fmtDisp=(n,d=2)=>{let p=(+n).toFixed(d).split('.');p[0]=p[0].replace(/\B(?=(\d{3})+(?!\d))/g,'.');return p.join(',');};
const fmt    =(n,d=2)=>(+n).toFixed(d);

/* ── DataTables init sin scrollX ──────────────── */
function initDT(sel){
  if($.fn.DataTable.isDataTable(sel)) $(sel).DataTable().destroy();
  $(sel).DataTable({
    paging:false,info:false,searching:false,ordering:false,
    autoWidth:false,scrollY:'13rem',responsive:true,
    language:{url:'assets/libs/datatables.net/i18n/es-ES.json'}
  });
}

/* ── Variables globales ───────────────────────── */
let abonosMod=false;
window.totMon={},window.totBase={},window.monMay=null;

/* ── Recalcular resumen ───────────────────────── */
function recalcular(){
  window.totMon={},window.totBase={};let totalB=0;
  const proc=(inp,isCred)=>{const mon=inp.dataset.moneda,
    monto=parseFloat(inp.value||0),
    sel=isCred?`[data-id-moneda="${inp.dataset.idMoneda}"]`:`[data-id="${inp.dataset.id}"]`,
    tasa=parseFloat(document.querySelector('.tasa-cambio'+sel)?.value||1),
    base=monto*tasa;
    window.totMon[mon]=(window.totMon[mon]||0)+monto;
    window.totBase[mon]=(window.totBase[mon]||0)+base;
    totalB+=base;
    const baseInp=document.querySelector('.monto-base'+sel);
    if(baseInp) baseInp.value=fmtDisp(base);
  };
  document.querySelectorAll('.monto-por-cuenta').forEach(i=>proc(i,false));
  document.querySelectorAll('.monto-por-credito').forEach(i=>proc(i,true));
  window.monMay=Object.keys(window.totBase).sort((a,b)=>window.totBase[b]-window.totBase[a])[0]||null;
  let h='<div class="alert alert-secondary p-2 mb-0">';
  Object.entries(window.totMon).forEach(([m,v])=>h+=`<span class="me-3">${m}: ${fmtDisp(v)}</span>`);
  h+=`<strong class="ms-3">Total base: ${fmtDisp(totalB)}</strong></div>`;
  $('#resumenMontosPorMoneda').html(h).removeClass('d-none');
  return totalB;
}

/* ── Distribución automática ───────────────────── */
function distribuirAuto(){
  if(abonosMod) return;
  let saldo=recalcular();
  $('#tablaPendientes tbody tr').each(function(){
    const $r=$(this),por=parseFloat($r.find('td:eq(3)').text()),
          tasa=parseFloat($r.data('tasa')),chk=$r.find('.chk-sel'),ab=$r.find('.abono');
    if(saldo>0){
      chk.prop('checked',true);ab.prop('disabled',false);
      const abPos=Math.min(por,saldo/tasa);ab.val(fmt(abPos));saldo-=abPos*tasa;
    }else{chk.prop('checked',false);ab.val('').prop('disabled',true);}
  });
  if(saldo>0&&window.monMay){
    const tMay=window.totBase[window.monMay]/window.totMon[window.monMay],
          cred=fmt(saldo/tMay);
    $('#creditosDiv').removeClass('d-none')
      .html(`Crédito a favor: <strong>${cred}</strong> (${window.monMay})`);
  }else $('#creditosDiv').addClass('d-none').empty();
}

/* ── Carga AJAX tablas ────────────────────────── */
function cargarFormas(id){
  const tbody=$('#tablaFormasPago tbody').html('<tr><td colspan="7" class="text-center">Cargando…</td></tr>');
  $.post('creditos_disponibles.php',{id_inmueble:id},res=>{
    tbody.empty();
    res.cuentas.forEach(c=>tbody.append(`
      <tr>
        <td>${c.banco||''}</td><td>${c.nombre}</td><td>${c.moneda}</td>
        <td><input type="text" class="form-control referencia-pago"
             name="referencia[${c.id_cuenta}]" ${c.tipo.toLowerCase()!=='banco'?'readonly':''}></td>
        <td><input type="number" class="form-control monto-por-cuenta"
             name="monto_cuenta[${c.id_cuenta}]" step="0.01" min="0"
             data-moneda="${c.moneda}" data-id="${c.id_cuenta}"></td>
        <td><input type="number" class="form-control tasa-cambio"
             name="tasa_cambio[${c.id_cuenta}]" step="0.0001"
             value="${parseFloat(c.tasa).toFixed(4)}"
             ${c.moneda_base?'readonly':''} data-id="${c.id_cuenta}"></td>
        <td><input type="text" class="form-control monto-base" readonly data-id="${c.id_cuenta}"></td>
      </tr>`));
    res.creditos.forEach(c=>tbody.append(`
      <tr class="credito-row">
        <td>Crédito</td><td>Crédito a Favor</td><td>${c.moneda}</td>
        <td><input type="text" class="form-control" value="${parseFloat(c.saldo).toFixed(2)}" readonly></td>
        <td><input type="number" class="form-control monto-por-credito"
             name="monto_credito[${c.id_moneda}]" step="0.01" min="0" max="${c.saldo}"
             data-moneda="${c.moneda}" data-id-moneda="${c.id_moneda}"></td>
        <td><input type="number" class="form-control tasa-cambio"
             name="tasa_credito[${c.id_moneda}]" step="0.0001"
             value="${parseFloat(c.tasa).toFixed(4)}"
             ${c.moneda_base?'readonly':''} data-id-moneda="${c.id_moneda}"></td>
        <td><input type="text" class="form-control monto-base" readonly data-id-moneda="${c.id_moneda}"></td>
      </tr>`));
    if(!res.cuentas.length&&!res.creditos.length)
      tbody.append('<tr><td colspan="7" class="text-center">Sin formas de pago.</td></tr>');
    initDT('#tablaFormasPago');recalcular();
  },'json');
}

function cargarPendientes(id){
  const tbody=$('#tablaPendientes tbody').html('<tr><td colspan="8" class="text-center">Cargando…</td></tr>');
  $.post('notificaciones_pendientes.php',{id_inmueble:id},res=>{
    tbody.empty();let tot={},base=0;
    res.forEach(n=>{
      const m=parseFloat(n.monto_x_pagar),b=m*parseFloat(n.tasa);
      tot[n.codigo_moneda]=(tot[n.codigo_moneda]||0)+m;base+=b;
      tbody.append(`
        <tr data-id="${n.id_notificacion}" data-tasa="${n.tasa}" data-id-moneda="${n.id_moneda}">
          <td>${n.fecha_emision}</td><td>${n.descripcion}</td><td>${n.codigo_moneda}</td>
          <td class="text-end">${fmt(n.monto_x_pagar)}</td><td class="text-end">${fmt(n.tasa,4)}</td>
          <td class="text-end">${fmt(b)}</td>
          <td class="text-center"><input type="checkbox" class="form-check-input chk-sel"></td>
          <td><input type="number" class="form-control abono" step="0.01" disabled max="${n.monto_x_pagar}"></td>
        </tr>`);});
    if(!res.length) tbody.append('<tr><td colspan="8" class="text-center">Sin pendientes.</td></tr>');
    let h='';Object.entries(tot).forEach(([m,v])=>h+=`${m}: ${fmtDisp(v)} `);
    h+=`Total base: ${fmtDisp(base)}`;$('#resumenPendientes').text(h).removeClass('d-none');
    initDT('#tablaPendientes');distribuirAuto();
  },'json');
}

/* ── Navegación pasos (solo móviles) ───────────── */
function mostrarStep(x){if(window.innerWidth<768){document.getElementById('modalCargarPago').style.setProperty('--step-mode','1');$('.step-section').removeClass('active');$('.step-'+x).addClass('active');}}
function resetSteps(){document.getElementById('modalCargarPago').style.removeProperty('--step-mode');}

/* ── Main ─────────────────────────────────────── */
$(function(){
  /* Abrir modal --------------------------------------------------------- */
  $('#tablaInmuebles tbody').on('click','.cargar-pago',function(){
    const id=$(this).data('id'),ide=$(this).data('identificacion');
    $('#id_inmueble_pago').val(id);$('#identificacion_pago').text(ide);
    abonosMod=false;$('#resumenMontosPorMoneda,#resumenPendientes,#creditosDiv').addClass('d-none').empty();
    cargarFormas(id);cargarPendientes(id);
    $('#modalCargarPago').modal('show');resetSteps();
  });

  /* Listeners ----------------------------------------------------------- */
  $(document).on('input','.monto-por-cuenta,.tasa-cambio,.monto-por-credito',()=>{if(!abonosMod)distribuirAuto();});
  $(document).on('change','.chk-sel',function(){const $a=$(this).closest('tr').find('.abono');$a.prop('disabled',!this.checked);if(!this.checked)$a.val('');});
  $(document).on('input','.abono',function(){
    abonosMod=true;let pagB=0;
    $('#tablaPendientes tbody tr').each(function(){
      pagB+=((parseFloat($(this).find('.abono').val())||0)*parseFloat($(this).data('tasa')));
    });
    const saldo=recalcular()-pagB;
    if(saldo>0&&window.monMay){
      const tMay=window.totBase[window.monMay]/window.totMon[window.monMay],cred=fmt(saldo/tMay);
      $('#creditosDiv').removeClass('d-none').html(`Crédito a favor: <strong>${cred}</strong> (${window.monMay})`);
    }else $('#creditosDiv').addClass('d-none').empty();
  });

  /* Submit -------------------------------------------------------------- */
  $('#formCargarPago').on('submit',function(e){
    e.preventDefault();const data=$(this).serializeArray(),notif=[];
    $('#tablaPendientes tbody tr').each(function(){
      const ab=parseFloat($(this).find('.abono').val())||0;
      if($(this).find('.chk-sel').is(':checked')&&ab>0){notif.push({
        id_notificacion:$(this).data('id'),abono:ab,tasa:$(this).data('tasa'),id_moneda:$(this).data('id-moneda')
      });}});
    data.push({name:'notificaciones',value:JSON.stringify(notif)});
    if(window.monMay&&$('#creditosDiv').is(':visible')){
      const cred=$('#creditosDiv strong').text().replace('.','').replace(',','.'); /* simple parse */
      data.push({name:'credito',value:cred},{name:'moneda_credito',value:window.monMay});
    }
    $.post('guardar_pago.php',data,r=>{
      Swal.fire({icon:r.status,title:r.status==='success'?'Éxito':'Error',text:r.message}).then(()=>{
        if(r.status==='success'){$('#modalCargarPago').modal('hide');location.reload();}
      });
    },'json').fail(x=>Swal.fire('Error','No se pudo guardar el pago.','error'));
  });
});
</script>
