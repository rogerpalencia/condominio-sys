<?php
// master_propietarios.php — CRUD Propietarios (por condominio) + vínculo a menu_login.usuario (correo/clave)
@session_start();
include 'layouts/session.php';
include 'layouts/head-main.php';
require_once 'core/funciones.php';
require_once 'layouts/vars.php';
require_once 'core/PDO.class.php';

if (!isset($_SESSION['userid'])) { header("Location: login.php"); exit; }

// === Resolver id_condominio ===
$id_condominio = 0;
if (isset($_POST['id_condominio']) && $_POST['id_condominio']!=='')      $id_condominio=(int)$_POST['id_condominio'];
elseif (isset($_SESSION['id_condominio']))                                $id_condominio=(int)$_SESSION['id_condominio'];
elseif (isset($_GET['id_condominio']))                                    $id_condominio=(int)$_GET['id_condominio'];
if ($id_condominio<=0) die('No hay condominio seleccionado.');

$conn = DB::getInstance();
$nombre_condo = 'Condominio';
try{
  $st=$conn->prepare('SELECT nombre FROM public.condominio WHERE id_condominio=:c LIMIT 1');
  $st->execute([':c'=>$id_condominio]);
  if($r=$st->fetch(PDO::FETCH_ASSOC)) $nombre_condo=$r['nombre'];
}catch(Throwable $e){}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <title>Propietarios | <?= NOMBREAPP ?></title>
  <?php include 'layouts/head.php'; ?>
  <link href="assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css" rel="stylesheet"/>
  <link href="assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css" rel="stylesheet"/>
  <?php include 'layouts/head-style.php'; ?>
  <style>.card{border-radius:8px}.table th,.table td{vertical-align:middle}.badge{font-weight:500}</style>
</head>
<?php include 'layouts/body.php'; ?>

<input type="hidden" id="id_condominio" value="<?= (int)$id_condominio ?>">

<div id="layout-wrapper">
<?php include 'layouts/menu.php'; ?>

<div class="main-content"><div class="page-content"><div class="container-fluid">

  <div class="row align-items-center mb-3">
    <div class="col-12 col-md-6">
      <h1 class="display-6 mb-0">Propietarios</h1>
      <p class="text-muted mb-0"><?= htmlspecialchars($nombre_condo) ?></p>
    </div>
    <div class="col-12 col-md-6 text-md-end mt-2 mt-md-0">
      <button id="btnNuevo" class="btn btn-success me-1">+ Nuevo propietario</button>
      <button class="btn btn-primary" type="button" onclick="window.history.back()">Volver</button>
    </div>
  </div>

  <div class="card"><div class="card-body">
    <div class="table-responsive">
      <table id="tablaPropietarios" class="table table-bordered dt-responsive nowrap w-100">
        <thead class="table-light">
          <tr>
            <th>ID</th>
            <th>Nombres</th>
            <th>Apellidos</th>
            <th>Correo (usuario)</th>
            <th>Cédula</th>
            <th>RIF</th>
            <th>Celular</th>
            <th>Verificado</th>
            <th>Fecha registro</th>
            <th>Acciones</th>
          </tr>
        </thead>
      </table>
    </div>
  </div></div>

</div><?php include 'layouts/footer.php'; ?></div></div>
<?php include 'layouts/right-sidebar.php'; ?>
</div>

<?php include 'layouts/vendor-scripts.php'; ?>
<script src="assets/libs/jquery/dist/jquery.min.js"></script>
<script src="assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/libs/sweetalert2/dist/sweetalert2.min.js"></script>
<script src="assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
<script src="assets/libs/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js"></script>

<script>
$(function(){
  const idCondo = $('#id_condominio').val();

  const tabla = $('#tablaPropietarios').DataTable({
    processing:true, serverSide:true,
    ajax:{ url:'propietario_data.php', type:'POST', data:d=>{ d.id_condominio=idCondo; } },
    columns:[
      { data:'id_propietario' },
      { data:null, render:(d,t,r)=>(r.nombre1||'')+' '+(r.nombre2||'') },
      { data:null, render:(d,t,r)=>(r.apellido1||'')+' '+(r.apellido2||'') },
      { data:'correo', defaultContent:'' },
      { data:null, render:(d,t,r)=>(r.t_cedula||'')+'-'+(r.cedula||'') },
      { data:null, render:(d,t,r)=>(r.t_rif||'')+'-'+(r.rif||'') },
      { data:'celular', defaultContent:'' },
      { data:'verificado', render:v=> (v?'<span class="badge bg-success">Sí</span>':'<span class="badge bg-secondary">No</span>') },
      { data:'fecha_registro', defaultContent:'' },
      { data:null, orderable:false, searchable:false, render:(row)=>`
        <div class="d-flex flex-wrap gap-1">
          <button class="btn btn-sm btn-secondary ver" data-id="${row.id_propietario}" title="Ver"><i class="fas fa-eye"></i></button>
          <button class="btn btn-sm btn-primary editar" data-id="${row.id_propietario}" title="Editar"><i class="fas fa-edit"></i></button>
          <button class="btn btn-sm btn-danger eliminar" data-id="${row.id_propietario}" title="Eliminar"><i class="fas fa-trash"></i></button>
        </div>` }
    ],
    order:[[2,'ASC'],[1,'ASC']],
    language:{ url:'Spanish.json' }
  });

  // Nuevo
  $('#btnNuevo').on('click', function(){
    openPropietarioModalNuevo(idCondo);
  });

  // Ver (solo lectura)
  $('#tablaPropietarios').on('click','.ver', function(){
    const id=$(this).data('id');
    openPropietarioModalEditar(id, idCondo, { readOnly:true });
  });

  // Editar
  $('#tablaPropietarios').on('click','.editar', function(){
    const id=$(this).data('id');
    openPropietarioModalEditar(id, idCondo);
  });

  // Eliminar
  $('#tablaPropietarios').on('click','.eliminar', function(){
    const id=$(this).data('id');
    Swal.fire({title:'¿Eliminar propietario #'+id+'?', text:'Acción irreversible.', icon:'warning', showCancelButton:true,
      confirmButtonText:'Sí, eliminar', cancelButtonText:'Cancelar'
    }).then(res=>{
      if(!res.isConfirmed) return;
      $.post('propietario_delete.php', { id_propietario:id, id_condominio:idCondo }, function(resp){
        let r=(typeof resp==='string')?(function(){try{return JSON.parse(resp)}catch(e){return null}})():resp;
        if(r && r.status==='ok'){ Swal.fire('Eliminado', r.message||'Registro eliminado','success'); tabla.ajax.reload(null,false); }
        else Swal.fire('Error',(r&&r.message)||'No se pudo eliminar','error');
      });
    });
  });

  // Cuando el modal guarda, recargar la tabla
  $(document).on('propietario:saved', function(e, payload){
    tabla.ajax.reload(null,false);
  });

  // Limpieza al cerrar cualquier modal para evitar backdrop huérfano
  $(document).on('hidden.bs.modal', '#modalPropietario', function(){
    $('body').removeClass('modal-open'); $('.modal-backdrop').remove();
  });

});
</script>

<!-- Incluye el modal reutilizable al final de la página -->
<?php include 'propietario_modal.php'; ?>
</body>
</html>
