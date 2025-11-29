<?php
// config_condominio.php — Configuración del condominio (Datos + Email)
// Usa esquema real: public.condominio y email.config
@session_start();
include 'layouts/session.php';
include 'layouts/head-main.php';
require_once 'core/funciones.php';
require_once 'layouts/vars.php';
require_once 'core/PDO.class.php';

if (!isset($_SESSION['userid'])) { header("Location: login.php"); exit; }

// === Resolver id_condominio desde sesión ===
$id_condominio = (int)($_SESSION['id_condominio'] ?? 0);
if ($id_condominio <= 0) { die('No hay condominio seleccionado.'); }

$conn = DB::getInstance();

// ====== Cargar datos del condominio ======
$sqlCondo = "SELECT id_condominio, nombre, direccion, linea_1, linea_2, linea_3,
                    id_moneda, url_logo_izquierda, url_logo_derecha
             FROM public.condominio
             WHERE id_condominio = :id
             LIMIT 1";
$st = $conn->prepare($sqlCondo);
$st->execute([':id'=>$id_condominio]);
$condo = $st->fetch(PDO::FETCH_ASSOC) ?: [];

// ====== Cargar parámetros de email (tomar SIEMPRE la última versión) ======
$sqlEmail = "SELECT id_email_config, id_condominio, host, puerto, seguridad, usuario,
                    from_email, from_name, reply_to_email, reply_to_name,
                    rate_limit_por_min, activo
             FROM email.config
             WHERE id_condominio = :id
             ORDER BY updated_at DESC, id_email_config DESC
             LIMIT 1";
$st = $conn->prepare($sqlEmail);
$st->execute([':id'=>$id_condominio]);
$email = $st->fetch(PDO::FETCH_ASSOC) ?: [];

function h($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <title>Configuración del Condominio | <?= NOMBREAPP ?></title>
  <?php include 'layouts/head.php'; ?>
  <?php include 'layouts/head-style.php'; ?>

  <style>
    .config-card{border-radius:10px;box-shadow:0 6px 20px rgba(0,0,0,.06)}
    .logo-preview{max-height:64px;margin-top:6px;display:block;border-radius:6px}
    .membrete-preview{border:1px dashed #ced4da;border-radius:8px;padding:12px 16px;background:#fafafa}
    .membrete-header{display:flex;align-items:center;justify-content:space-between}
    .membrete-logos img{max-height:40px;border-radius:4px}
    .membrete-text{margin-top:8px;line-height:1.2}
    .muted{color:#6c757d}
  </style>
</head>
<?php include 'layouts/body.php'; ?>

<input type="hidden" id="id_condominio" value="<?= (int)$id_condominio ?>">

<div id="layout-wrapper">
<?php include 'layouts/menu.php'; ?>

<div class="main-content">
  <div class="page-content">
    <div class="container-fluid">

      <div class="d-flex align-items-baseline justify-content-between mb-3">
        <div>
          <h2 class="mb-0">Configuración del Condominio</h2>
          <div class="text-muted">Gestione datos generales, membrete y parámetros de correo</div>
        </div>
        <button class="btn btn-outline-primary" onclick="history.back()">
          <i class="mdi mdi-arrow-left"></i> Volver
        </button>
      </div>

      <div class="card config-card">
        <div class="card-body">
          <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tabDatos" role="tab">Datos Generales</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabEmail" role="tab">Parámetros Email</a></li>
          </ul>

          <div class="tab-content pt-3">
            <!-- ====== TAB: Datos Generales ====== -->
            <div class="tab-pane fade show active" id="tabDatos" role="tabpanel">
              <form id="formGenerales" enctype="multipart/form-data" autocomplete="off">
                <input type="hidden" name="id_condominio" value="<?= (int)$id_condominio ?>">

                <div class="row g-3">
                  <div class="col-lg-6">
                    <label class="form-label">Nombre del Condominio</label>
                    <input type="text" class="form-control" name="nombre" required value="<?= h($condo['nombre']) ?>">
                  </div>
                  <div class="col-lg-6">
                    <label class="form-label">Dirección</label>
                    <input type="text" class="form-control" name="direccion" value="<?= h($condo['direccion']) ?>">
                  </div>

                  <div class="col-lg-4">
                    <label class="form-label">Línea 1 del membrete</label>
                    <input type="text" class="form-control" name="linea_1" value="<?= h($condo['linea_1']) ?>" placeholder="Ej: RIF: J-12345678-9">
                  </div>
                  <div class="col-lg-4">
                    <label class="form-label">Línea 2 del membrete</label>
                    <input type="text" class="form-control" name="linea_2" value="<?= h($condo['linea_2']) ?>" placeholder="Ej: Av. Principal, Torre X">
                  </div>
                  <div class="col-lg-4">
                    <label class="form-label">Línea 3 del membrete</label>
                    <input type="text" class="form-control" name="linea_3" value="<?= h($condo['linea_3']) ?>" placeholder="Ej: Tel. +58 ...">
                  </div>

                  <div class="col-lg-6">
                    <label class="form-label">Logo Izquierdo</label>
                    <input type="file" class="form-control" name="logo_izquierdo" accept="image/*">
                    <?php if (!empty($condo['url_logo_izquierda'])): ?>
                      <img src="<?= h($condo['url_logo_izquierda']) ?>" class="logo-preview" id="prev_logo_izq">
                    <?php else: ?>
                      <img src="" class="logo-preview d-none" id="prev_logo_izq">
                    <?php endif; ?>
                  </div>
                  <div class="col-lg-6">
                    <label class="form-label">Logo Derecho</label>
                    <input type="file" class="form-control" name="logo_derecho" accept="image/*">
                    <?php if (!empty($condo['url_logo_derecha'])): ?>
                      <img src="<?= h($condo['url_logo_derecha']) ?>" class="logo-preview" id="prev_logo_der">
                    <?php else: ?>
                      <img src="" class="logo-preview d-none" id="prev_logo_der">
                    <?php endif; ?>
                  </div>

                  <div class="col-lg-4">
                    <label class="form-label">Moneda base</label>
                    <select class="form-select" name="id_moneda" required>
                      <option value="">Seleccione</option>
                      <option value="1" <?= (int)($condo['id_moneda'] ?? 0)===1 ? 'selected':'' ?>>Bolívar</option>
                      <option value="2" <?= (int)($condo['id_moneda'] ?? 0)===2 ? 'selected':'' ?>>Dólar</option>
                    </select>
                    <div class="form-text">Se usa como moneda base para cálculos y reportes</div>
                  </div>

                  <div class="col-12">
                    <label class="form-label">Vista previa de membrete</label>
                    <div class="membrete-preview">
                      <div class="membrete-header">
                        <div class="membrete-logos">
                          <?php if (!empty($condo['url_logo_izquierda'])): ?>
                            <img id="previewMiniIzq" src="<?= h($condo['url_logo_izquierda']) ?>">
                          <?php else: ?>
                            <img id="previewMiniIzq" src="" class="d-none">
                          <?php endif; ?>
                        </div>
                        <div class="membrete-logos">
                          <?php if (!empty($condo['url_logo_derecha'])): ?>
                            <img id="previewMiniDer" src="<?= h($condo['url_logo_derecha']) ?>">
                          <?php else: ?>
                            <img id="previewMiniDer" src="" class="d-none">
                          <?php endif; ?>
                        </div>
                      </div>
                      <div class="membrete-text mt-2">
                        <div><strong id="pv_nombre"><?= h($condo['nombre']) ?></strong></div>
                        <div class="muted" id="pv_l1"><?= h($condo['linea_1']) ?></div>
                        <div class="muted" id="pv_l2"><?= h($condo['linea_2']) ?></div>
                        <div class="muted" id="pv_l3"><?= h($condo['linea_3']) ?></div>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="mt-3 text-end">
                  <button class="btn btn-primary" type="submit">Guardar datos</button>
                </div>
              </form>
            </div>

            <!-- ====== TAB: Email ====== -->
            <div class="tab-pane fade" id="tabEmail" role="tabpanel">
              <form id="formEmail" autocomplete="off">
                <input type="hidden" name="id_condominio" value="<?= (int)$id_condominio ?>">
                <div class="row g-3">
                  <div class="col-lg-6">
                    <label class="form-label">Host SMTP</label>
                    <input type="text" class="form-control" name="host" required value="<?= h($email['host'] ?? '') ?>" autocomplete="off">
                  </div>
                  <div class="col-lg-2">
                    <label class="form-label">Puerto</label>
                    <input type="number" class="form-control" name="puerto" required value="<?= h($email['puerto'] ?? '') ?>" autocomplete="off">
                  </div>
                  <div class="col-lg-4">
                    <label class="form-label">Seguridad</label>
                    <select name="seguridad" class="form-select" required>
                      <?php
                        $seg = strtolower((string)($email['seguridad'] ?? 'ninguna'));
                        $opts = ['ninguna'=>'Ninguna','ssl'=>'SSL','tls'=>'TLS'];
                        foreach($opts as $v=>$lbl){
                          $sel = ($seg===$v)?'selected':'';
                          echo "<option value=\"$v\" $sel>$lbl</option>";
                        }
                      ?>
                    </select>
                  </div>

                  <div class="col-lg-6">
                    <label class="form-label">Usuario SMTP</label>
                    <input type="text" class="form-control" name="usuario" required value="<?= h($email['usuario'] ?? '') ?>" autocomplete="off">
                  </div>
                  <div class="col-lg-6">
                    <label class="form-label">Clave (nueva)</label>
                    <!-- Nunca mostrar la clave actual; este campo solo establece una nueva -->
                    <input type="password" class="form-control" name="contrasena" placeholder="Dejar en blanco para mantener la actual" autocomplete="new-password">
                    <div class="form-text">Se almacena ofuscada (Base64). Solo se usa si escribes una nueva.</div>
                  </div>

                  <div class="col-lg-6">
                    <label class="form-label">From (email remitente)</label>
                    <input type="email" class="form-control" name="from_email" required value="<?= h($email['from_email'] ?? '') ?>" autocomplete="off">
                  </div>
                  <div class="col-lg-6">
                    <label class="form-label">From (nombre)</label>
                    <input type="text" class="form-control" name="from_name" required value="<?= h($email['from_name'] ?? '') ?>" autocomplete="off">
                  </div>

                  <div class="col-lg-6">
                    <label class="form-label">Reply-To (email)</label>
                    <input type="email" class="form-control" name="reply_to_email" value="<?= h($email['reply_to_email'] ?? '') ?>" autocomplete="off">
                  </div>
                  <div class="col-lg-6">
                    <label class="form-label">Reply-To (nombre)</label>
                    <input type="text" class="form-control" name="reply_to_name" value="<?= h($email['reply_to_name'] ?? '') ?>" autocomplete="off">
                  </div>

                  <div class="col-lg-3">
                    <label class="form-label">Rate limit / min</label>
                    <input type="number" class="form-control" name="rate_limit_por_min" min="1" max="1000"
                           value="<?= h($email['rate_limit_por_min'] ?? 30) ?>" autocomplete="off">
                  </div>
                  <div class="col-lg-3 d-flex align-items-end">
                    <div class="form-check form-switch">
                      <input class="form-check-input" type="checkbox" name="activo" value="1" <?= !empty($email) && !empty($email['activo']) ? 'checked':'' ?>>
                      <label class="form-check-label">Activo</label>
                    </div>
                  </div>
                </div>

<div class="mt-3 d-flex justify-content-end gap-2">
  <button class="btn btn-outline-secondary" type="button" id="btnProbarEmail">
    Probar envío
  </button>
  <button class="btn btn-primary" type="submit">Guardar email</button>
</div>

              </form>
            </div>

          </div> <!-- tab-content -->
        </div>
      </div>

    </div>
  </div>
  <?php include 'layouts/footer.php'; ?>
</div>

<?php include 'layouts/right-sidebar.php'; ?>
</div>

<?php include 'layouts/vendor-scripts.php'; ?>
<script src="assets/libs/jquery/jquery.min.js"></script>
<script src="assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/libs/sweetalert2/sweetalert2.min.js"></script>

<script>
(function(){
  // ====== Vista previa de membrete ======
  $('input[name="nombre"]').on('input', function(){ $('#pv_nombre').text(this.value); });
  $('input[name="linea_1"]').on('input', function(){ $('#pv_l1').text(this.value); });
  $('input[name="linea_2"]').on('input', function(){ $('#pv_l2').text(this.value); });
  $('input[name="linea_3"]').on('input', function(){ $('#pv_l3').text(this.value); });

  // ====== Preview logos local ======
  const izq = document.querySelector('input[name="logo_izquierdo"]');
  if (izq) izq.addEventListener('change', function(e){
    const f = e.target.files?.[0]; if(!f) return;
    const url = URL.createObjectURL(f);
    $('#prev_logo_izq').attr('src', url).removeClass('d-none');
    $('#previewMiniIzq').attr('src', url).removeClass('d-none');
  });
  const der = document.querySelector('input[name="logo_derecho"]');
  if (der) der.addEventListener('change', function(e){
    const f = e.target.files?.[0]; if(!f) return;
    const url = URL.createObjectURL(f);
    $('#prev_logo_der').attr('src', url).removeClass('d-none');
    $('#previewMiniDer').attr('src', url).removeClass('d-none');
  });

  // ====== Guardar Datos Generales ======
  $('#formGenerales').on('submit', function(e){
    e.preventDefault();
    const fd = new FormData(this);
    $.ajax({
      url:'config_condominio_save.php', type:'POST', data:fd,
      processData:false, contentType:false, dataType:'json',
      success:function(r){
        if(r && r.status==='ok') Swal.fire('Guardado','Datos generales actualizados.','success');
        else Swal.fire('Error', (r&&r.message)||'No se pudo guardar','error');
      }, error:function(xhr){ Swal.fire('Error', xhr.responseText||'No se pudo guardar','error'); }
    });
  });

  // ====== Guardar Email ======
  $('#formEmail').on('submit', function(e){
    e.preventDefault();
    $.ajax({
      url:'guardar_config_email.php', type:'POST', data: $(this).serialize(), dataType:'json',
      success:function(r){
        if(r && r.status==='ok') Swal.fire('Guardado','Parámetros de email actualizados.','success');
        else Swal.fire('Error', (r&&r.message)||'No se pudo guardar','error');
      }, error:function(xhr){ Swal.fire('Error', xhr.responseText||'No se pudo guardar','error'); }
    });
  });
})();


$(function(){
  $('#btnProbarEmail').on('click', function(){
    const idCondo = $('#id_condominio').val();

    Swal.fire({
      title: 'Prueba de Email',
      input: 'email',
      inputLabel: 'Correo destino',
      inputPlaceholder: 'ejemplo@correo.com',
      showCancelButton: true,
      confirmButtonText: 'Enviar',
      cancelButtonText: 'Cancelar',
      preConfirm: (email) => {
        if (!email) return 'Debes indicar un correo';
        return $.ajax({
          url: 'probar_config_email.php',
          method: 'POST',
          dataType: 'json',
          data: { id_condominio: idCondo, destino: email }
        }).then(resp => {
          if (resp.status === 'ok') {
            Swal.fire('Éxito', resp.message, 'success');
          } else {
            Swal.fire('Error', resp.message || 'Error desconocido', 'error');
          }
        }).catch(err => {
          Swal.fire('Error', 'No se pudo contactar el servidor', 'error');
        });
      }
    });
  });
});



</script>



</body>
</html>
