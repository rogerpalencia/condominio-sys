<?php
// propietario_modal.php — Modal reutilizable (Nuevo / Editar / Asignar Propietario)
// Requisitos en la página contenedora: jQuery, Bootstrap 5 (modal+tabs), SweetAlert2 y stack Minia.
// Endpoints utilizados (ya existentes en tu app):
//   - propietario_buscar.php
//   - propietario_fetch.php
//   - propietario_insert.php
//   - propietario_update.php
//   - propietario_inmueble_upsert.php
//
// API pública (desde otras páginas):
//   openPropietarioModalNuevo(idCondo, { linkInmueble })
//   openPropietarioModalEditar(idProp, idCondo, { linkInmueble })
?>
<div class="modal fade" id="modalPropietario" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg"><div class="modal-content">
    <form id="formPropietario">
      <input type="hidden" name="id_propietario" id="pm_id_propietario">
      <input type="hidden" name="id_condominio" id="pm_id_condominio">
      <!-- contexto para asociación automática -->
      <input type="hidden" id="pm_link_inmueble">
      <!-- fallback genérico por si abren el modal de otro lugar -->
      <input type="hidden" id="id_inmueble_contexto" name="id_inmueble_contexto">

      <div class="modal-header">
        <h5 class="modal-title" id="pm_modal_title">Nuevo Propietario</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <ul class="nav nav-tabs" id="pmTabs" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" id="pm-tab-identificar" data-bs-toggle="tab" data-bs-target="#pmIdentificar" type="button" role="tab">
              Identificar
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="pm-tab-datos" data-bs-toggle="tab" data-bs-target="#pmDatos" type="button" role="tab" disabled>
              Datos
            </button>
          </li>
        </ul>

        <div class="tab-content mt-3">
          <!-- ===== Paso 1: Identificar ===== -->
          <div class="tab-pane fade show active" id="pmIdentificar" role="tabpanel" aria-labelledby="pm-tab-identificar">
            <div class="row g-3 align-items-end">
              <div class="col-md-2">
                <label class="form-label">T. Cédula</label>
                <input type="text" class="form-control text-uppercase" id="pm_t_cedula" maxlength="1" placeholder="V/E/P"
                       oninput="this.value=this.value.toUpperCase();" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Cédula</label>
                <input type="number" class="form-control" id="pm_cedula" required>
              </div>
              <div class="col-md-4">
                <button class="btn btn-primary w-100" id="pm_btn_buscar">Buscar / Continuar</button>
              </div>
            </div>

            <!-- Resultado de búsqueda -->
            <div id="pm_found_wrap" class="mt-3 d-none">
              <div class="card border">
                <div class="card-body d-flex justify-content-between align-items-center">
                  <div>
                    <div id="pm_found_nombre" class="fw-bold"></div>
                    <small id="pm_found_info" class="text-muted"></small>
                  </div>
                  <div>
                    <button type="button" class="btn btn-outline-primary btn-sm me-2" id="pm_btn_editar_found">Editar datos</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="pm_btn_cambiar_busqueda">Cambiar cédula</button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Sugerencia de asignación cuando se encuentra OTRO propietario -->
            <div id="pm_asignar_wrap" class="alert alert-info d-none mt-2">
              <div class="d-flex align-items-center justify-content-between">
                <div>
                  <strong>Propietario encontrado.</strong>
                  Puede asignarlo a este inmueble sin editar el registro actual.
                </div>
                <button type="button" class="btn btn-sm btn-success" id="pm_btn_asignar" data-propietario="">
                  Asignar a este inmueble
                </button>
              </div>
            </div>
          </div>

          <!-- ===== Paso 2: Datos ===== -->
          <div class="tab-pane fade" id="pmDatos" role="tabpanel" aria-labelledby="pm-tab-datos">
            <div class="row g-3">
              <div class="col-md-3">
                <label class="form-label">Nombre 1</label>
                <input type="text" name="nombre1" id="pm_nombre1" class="form-control text-uppercase" required
                       oninput="this.value=this.value.toUpperCase();">
              </div>
              <div class="col-md-3">
                <label class="form-label">Nombre 2</label>
                <input type="text" name="nombre2" id="pm_nombre2" class="form-control text-uppercase"
                       oninput="this.value=this.value.toUpperCase();">
              </div>
              <div class="col-md-3">
                <label class="form-label">Apellido 1</label>
                <input type="text" name="apellido1" id="pm_apellido1" class="form-control text-uppercase" required
                       oninput="this.value=this.value.toUpperCase();">
              </div>
              <div class="col-md-3">
                <label class="form-label">Apellido 2</label>
                <input type="text" name="apellido2" id="pm_apellido2" class="form-control text-uppercase"
                       oninput="this.value=this.value.toUpperCase();">
              </div>

              <div class="col-md-6">
                <label class="form-label">Correo (usuario)</label>
                <input type="email" name="correo_usuario" id="pm_correo" class="form-control" required>
                <small class="text-muted">Se creará o vinculará un usuario. Clave inicial: <code>sha1(cédula)</code>.</small>
              </div>

              <div class="col-md-2">
                <label class="form-label">T. Cédula</label>
                <input type="text" name="t_cedula" id="pm_t_cedula_datos" maxlength="1" class="form-control text-uppercase" required
                       oninput="this.value=this.value.toUpperCase();">
              </div>
              <div class="col-md-4">
                <label class="form-label">Cédula</label>
                <input type="number" name="cedula" id="pm_cedula_datos" class="form-control" required>
              </div>

              <div class="col-md-2">
                <label class="form-label">T. RIF</label>
                <input type="text" name="t_rif" id="pm_t_rif" class="form-control text-uppercase" placeholder="V/J/G"
                       oninput="this.value=this.value.toUpperCase();">
              </div>
              <div class="col-md-4">
                <label class="form-label">RIF</label>
                <input type="text" name="rif" id="pm_rif" class="form-control text-uppercase"
                       oninput="this.value=this.value.toUpperCase();">
              </div>

              <div class="col-md-3">
                <label class="form-label">Celular</label>
                <input type="text" name="celular" id="pm_celular" class="form-control">
              </div>
              <div class="col-md-3">
                <label class="form-label">Tratamiento</label>
                <input type="text" name="tratamiento" id="pm_tratamiento" class="form-control text-uppercase"
                       oninput="this.value=this.value.toUpperCase();">
              </div>
              <div class="col-md-4">
                <label class="form-label">Fecha registro</label>
                <input type="datetime-local" name="fecha_registro" id="pm_fecha_registro" class="form-control">
              </div>

              <div class="col-12">
                <input type="hidden" name="verificado" value="0">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="verificado" id="pm_verificado" value="1">
                  <label class="form-check-label" for="pm_verificado">Verificado</label>
                </div>
              </div>
            </div>
          </div><!-- /tab Datos -->
        </div><!-- /tab-content -->
      </div>

      <div class="modal-footer">
        <button class="btn btn-primary" type="submit" id="pm_btn_guardar">Guardar</button>
        <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancelar</button>
      </div>
    </form>
  </div></div>
</div>

<script>
(function(){
  // ===== Estado del modal =====
  const State = {
    mode: 'new',          // 'new' | 'edit' | 'assign'
    currentId: null,      // id del propietario en edición (si aplica)
    foundId: null,        // id del propietario hallado por cédula
    linkInmueble: null,   // id_inmueble si se abrió desde master_inmuebles
    idCondo: null         // id_condominio
  };

  // ===== Helpers de UI =====
  function setTitle(txt){ $('#pm_modal_title').text(txt); }
  function switchToTab(tabId) {
    const t = new bootstrap.Tab(document.querySelector(`[data-bs-target="${tabId}"]`));
    t.show();
  }
  function enableDatosTab(enable) { $('#pm-tab-datos').prop('disabled', !enable); }
  function resetIdentificar() {
    $('#pm_t_cedula').val('');
    $('#pm_cedula').val('');
    $('#pm_found_wrap').addClass('d-none');
    $('#pm_asignar_wrap').addClass('d-none');
    $('#pm_btn_asignar').data('propietario','');
    State.foundId = null;
  }
  function resetDatos() {
    $('#pm_nombre1, #pm_nombre2, #pm_apellido1, #pm_apellido2, #pm_correo, #pm_t_rif, #pm_rif, #pm_celular, #pm_tratamiento').val('');
    $('#pm_t_cedula_datos').val('');
    $('#pm_cedula_datos').val('');
    $('#pm_fecha_registro').val('');
    $('#pm_verificado').prop('checked', false);
  }
  function fillDatos(d){
    $('#pm_nombre1').val(d.nombre1||'');
    $('#pm_nombre2').val(d.nombre2||'');
    $('#pm_apellido1').val(d.apellido1||'');
    $('#pm_apellido2').val(d.apellido2||'');
    $('#pm_correo').val(d.correo||'');
    $('#pm_t_cedula_datos').val(d.t_cedula||'');
    $('#pm_cedula_datos').val(d.cedula||'');
    $('#pm_t_rif').val(d.t_rif||'');
    $('#pm_rif').val(d.rif||'');
    $('#pm_celular').val(d.celular||'');
    $('#pm_tratamiento').val(d.tratamiento||'');
    $('#pm_fecha_registro').val(d.fecha_registro ? d.fecha_registro.replace(' ','T') : '');
    $('#pm_verificado').prop('checked', !!d.verificado);
  }
  function showFound(found){
    $('#pm_found_wrap').removeClass('d-none');
    const nom = [found.nombre1, found.nombre2, found.apellido1, found.apellido2].filter(Boolean).join(' ');
    $('#pm_found_nombre').text(nom || '(Sin nombre)');
    const info = `Cédula: ${found.t_cedula||''}-${found.cedula||''} | ${found.correo||''}`;
    $('#pm_found_info').text(info);
  }
  function getInmuebleContext(){
    // Prioriza el link explícito que enviamos al abrir el modal; si no, usa el hidden de respaldo
    return State.linkInmueble || $('#pm_link_inmueble').val() || $('#id_inmueble_contexto').val() || '';
  }

  // ===== API pública =====
  window.openPropietarioModalNuevo = function(idCondo, opts) {
    State.mode = 'new';
    State.currentId = null;
    State.foundId = null;
    State.linkInmueble = (opts && opts.linkInmueble) ? String(opts.linkInmueble) : null;
    State.idCondo = idCondo;

    $('#pm_id_propietario').val('');
    $('#pm_id_condominio').val(idCondo);
    $('#pm_link_inmueble').val(State.linkInmueble || '');
    $('#id_inmueble_contexto').val(State.linkInmueble || ''); // fallback

    setTitle('Nuevo Propietario');
    resetIdentificar();
    resetDatos();
    enableDatosTab(false);
    $('#pm_btn_guardar').prop('disabled', false);

    switchToTab('#pmIdentificar');
    $('#modalPropietario').modal('show');
  };

  window.openPropietarioModalEditar = function(idProp, idCondo, opts) {
    State.mode = 'edit';
    State.currentId = Number(idProp);
    State.foundId = null;
    State.linkInmueble = (opts && opts.linkInmueble) ? String(opts.linkInmueble) : null;
    State.idCondo = idCondo;

    $('#pm_id_propietario').val(idProp);
    $('#pm_id_condominio').val(idCondo);
    $('#pm_link_inmueble').val(State.linkInmueble || '');
    $('#id_inmueble_contexto').val(State.linkInmueble || ''); // fallback

    setTitle('Editar Propietario #' + idProp);
    resetIdentificar();
    resetDatos();
    enableDatosTab(true);
    $('#pm_btn_guardar').prop('disabled', false);

    $.post('propietario_fetch.php', { id_propietario:idProp, id_condominio:idCondo }, function(resp){
      let r = (typeof resp==='string') ? (function(){try{return JSON.parse(resp)}catch(e){return null}})() : resp;
      if (!r || r.status!=='ok' || !r.data) { Swal.fire('Error',(r&&r.message)||'No se pudo cargar','error'); return; }
      const d = r.data;
      $('#pm_t_cedula').val(d.t_cedula||'');
      $('#pm_cedula').val(d.cedula||'');
      fillDatos(d);
      switchToTab('#pmIdentificar'); // inicia en Identificar para permitir buscar otra cédula
      $('#modalPropietario').modal('show');
    });
  };

  // ===== Buscar / Continuar =====
  $('#pm_btn_buscar').on('click', function(e){
    e.preventDefault();
    const t = $('#pm_t_cedula').val().trim().toUpperCase();
    const c = $('#pm_cedula').val().trim();
    const idCondo = State.idCondo;

    if (!t || !c) { Swal.fire('Atención','Indique tipo y número de cédula.','info'); return; }

    $.post('propietario_buscar.php', { t_cedula:t, cedula:c, id_condominio:idCondo }, function(resp){
      let r = (typeof resp==='string') ? (function(){try{return JSON.parse(resp)}catch(e){return null}})() : resp;
      if (!r) { Swal.fire('Error','Respuesta inválida','error'); return; }

      if (r.status === 'not_found') {
        // No existe → ir a Datos para crear
        State.mode = State.currentId ? 'edit' : 'new';
        enableDatosTab(true);
        $('#pm_t_cedula_datos').val(t);
        $('#pm_cedula_datos').val(c);
        $('#pm_found_wrap').addClass('d-none');
        $('#pm_asignar_wrap').addClass('d-none');
        $('#pm_btn_asignar').data('propietario','');
        $('#pm_btn_guardar').prop('disabled', false);
        switchToTab('#pmDatos');
        return;
      }

      if (r.status === 'ok' && r.data && r.data.id_propietario) {
        const found = r.data;
        State.foundId = Number(found.id_propietario);
        showFound(found);

        // Si estoy editando y encontré OTRO → modo ASIGNAR
        if (State.currentId && State.currentId !== State.foundId) {
          State.mode = 'assign';
          enableDatosTab(false);
          $('#pm_asignar_wrap').removeClass('d-none');
          $('#pm_btn_asignar').data('propietario', State.foundId);
          $('#pm_btn_guardar').prop('disabled', true);
          return;
        }

        // Si es el mismo propietario (o venía a crear pero ya existe)
        if (!State.currentId) {
          // Nuevo pero ya existe → si hay inmueble en contexto, ofrecer asignar
          State.mode = 'assign';
          enableDatosTab(false);
          const iid = getInmuebleContext();
          if (iid) {
            $('#pm_asignar_wrap').removeClass('d-none');
            $('#pm_btn_asignar').data('propietario', State.foundId);
            $('#pm_btn_guardar').prop('disabled', true);
          } else {
            // sin inmueble en contexto, bloquear guardado para evitar duplicar
            $('#pm_btn_guardar').prop('disabled', true);
          }
        } else {
          // Es el mismo registro → permitir editar
          State.mode = 'edit';
          enableDatosTab(true);
          fillDatos(found);
          const iid = getInmuebleContext();
          $('#pm_asignar_wrap').toggleClass('d-none', !iid);
          $('#pm_btn_asignar').data('propietario', State.foundId);
          $('#pm_btn_guardar').prop('disabled', false);
          switchToTab('#pmDatos');
        }
        return;
      }

      Swal.fire('Error', r.message || 'No se pudo buscar', 'error');
    }, 'json');
  });

  // Cambiar cédula (reinicia sección encontrada)
  $('#pm_btn_cambiar_busqueda').on('click', function(){
    $('#pm_found_wrap').addClass('d-none');
    $('#pm_asignar_wrap').addClass('d-none');
    $('#pm_btn_asignar').data('propietario','');
    $('#pm_btn_guardar').prop('disabled', false);
    State.mode = State.currentId ? 'edit' : 'new';
  });

  // Editar datos del encontrado (si es el mismo)
  $('#pm_btn_editar_found').on('click', function(){
    if (!State.foundId) return;
    if (State.currentId && State.currentId !== State.foundId) return; // es otro → asignar
    enableDatosTab(true);
    switchToTab('#pmDatos');
    $('#pm_btn_guardar').prop('disabled', false);
  });

  // ===== Asignar propietario encontrado al inmueble =====
  $('#pm_btn_asignar').on('click', function(){
    const pid = $(this).data('propietario');
    const iid = getInmuebleContext();
    const idCondo = State.idCondo;

    if (!pid) { Swal.fire('Atención','No hay propietario a asignar.','warning'); return; }
    if (!iid) { Swal.fire('Atención','No hay inmueble en contexto para asignar.','warning'); return; }

    $.post('propietario_inmueble_upsert.php', {
      id_inmueble: iid,
      id_propietario: pid,
      id_condominio: idCondo
    }, function(resp){
      if (resp && resp.status==='ok'){
        Swal.fire('Vinculado','Propietario asociado al inmueble.','success');
        $('#modalPropietario').modal('hide');
        $(document).trigger('propietario:saved', [{ id_propietario: pid }]);
      } else {
        Swal.fire('Error', (resp && resp.message) || 'No se pudo asociar', 'error');
      }
    }, 'json');
  });

  // ===== Guardar (insert/update) =====
  $('#formPropietario').on('submit', function(e){
    // En modo "assign" no se guarda, se usa el botón Asignar
    if (State.mode === 'assign') {
      e.preventDefault();
      Swal.fire('Atención','Use el botón "Asignar" para cambiar el titular.','info');
      return;
    }

    e.preventDefault();
    const id = $('#pm_id_propietario').val();
    const url = id ? 'propietario_update.php' : 'propietario_insert.php';
    const fd  = new FormData(this);

    fd.set('id_condominio', State.idCondo);

    $.ajax({
      url: url, type:'POST', data:fd, processData:false, contentType:false,
      success: function(resp){
        let r = (typeof resp==='string') ? (function(){try{return JSON.parse(resp)}catch(e){return null}})() : resp;
        if (r && r.status==='ok'){
          const newId = r.id || id;

          // Si venimos desde un inmueble y era INSERT (nuevo), asociar automáticamente
          const iid = getInmuebleContext();
          if (!id && iid) {
            $.post('propietario_inmueble_upsert.php', {
              id_inmueble: iid,
              id_propietario: newId,
              id_condominio: State.idCondo
            }, function(x){
              if (x && x.status==='ok'){
                Swal.fire('Guardado','Propietario creado y asociado al inmueble.','success');
                $('#modalPropietario').modal('hide');
                $(document).trigger('propietario:saved', [{ id_propietario: newId }]);
              } else {
                Swal.fire('Aviso','Propietario creado, pero no se pudo asociar al inmueble.','warning');
              }
            }, 'json');
          } else {
            Swal.fire('Guardado','Operación exitosa.','success');
            $('#modalPropietario').modal('hide');
            $(document).trigger('propietario:saved', [{ id_propietario: newId }]);
          }
        } else {
          Swal.fire('Error', (r&&r.message)||'No se pudo guardar', 'error');
        }
      },
      error: function(xhr){
        Swal.fire('Error', xhr.responseText || 'No se pudo guardar', 'error');
      }
    });
  });

  // Limpieza visual al cerrar
  $('#modalPropietario').on('hidden.bs.modal', function(){
    $('body').removeClass('modal-open'); $('.modal-backdrop').remove();
  });
})();
</script>
