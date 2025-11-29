<?php 
@session_start();
include 'layouts/session.php';
include 'layouts/head-main.php';
require_once("core/funciones.php");
require_once("layouts/vars.php");
require_once("core/PDO.class.php");

if (!isset($_SESSION['userid'])) {
    header("Location: login.php");
    exit;
}

$func   = new Funciones();
$conn   = DB::getInstance();
$userid = (int)$_SESSION['userid'];

$sql = "SELECT a.id_condominio as id_condominio, c.nombre, c.esquema_cuota
        FROM administradores a
        INNER JOIN condominio c ON a.id_condominio = c.id_condominio
        WHERE a.id_usuario = :id_usuario AND a.estatus = true";

$stmt = $conn->prepare($sql);
$stmt->bindParam(':id_usuario', $userid, PDO::PARAM_INT);
$stmt->execute();
$row_condo = $stmt->fetch();
$id_condominio = $row_condo['id_condominio'] ?? null;
$nombre_condominio = $row_condo['nombre'] ?? null;  
$esquema_cuota = $row_condo['esquema_cuota'] ?? 'fija';

$sql = "SELECT DISTINCT anio FROM notificacion_cobro WHERE id_condominio = :id_condominio ORDER BY anio DESC";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':id_condominio', $id_condominio, PDO::PARAM_INT);
$stmt->execute();
$anios_disponibles = $stmt->fetchAll(PDO::FETCH_COLUMN);

$anio_actual = date('Y');
$anio_seleccionado = isset($_GET['anio']) && in_array($_GET['anio'], $anios_disponibles) ? (int)$_GET['anio'] : ($anios_disponibles[0] ?? $anio_actual);

function obtenerEstados($conn, $id_condominio, $anio, $mes) {
    $estados = ['presupuesto' => 'pendiente', 'notificaciones' => 'pendiente', 'ejecucion' => 'pendiente'];

    $stmt = $conn->prepare("SELECT id_tipo FROM notificacion_cobro WHERE id_condominio = :id AND anio = :anio AND mes = :mes AND id_inmueble IS NULL LIMIT 1");
    $stmt->execute([':id' => $id_condominio, ':anio' => $anio, ':mes' => $mes]);
    $tipo = $stmt->fetchColumn();

    if ($tipo == 1) $estados['presupuesto'] = 'En proceso';
    if ($tipo == 2) $estados['presupuesto'] = 'Borrador';
    if ($tipo == 3) $estados['presupuesto'] = 'Emitida';

    $stmt = $conn->prepare("SELECT COUNT(*) FROM notificacion_cobro WHERE id_condominio = :id AND anio = :anio AND mes = :mes AND id_inmueble IS NOT NULL");
    $stmt->execute([':id' => $id_condominio, ':anio' => $anio, ':mes' => $mes]);
    $estados['notificaciones'] = $stmt->fetchColumn() > 0 ? 'Generadas' : 'Pendientes';

    $stmt = $conn->prepare("SELECT estado FROM notificacion_cobro WHERE id_condominio = :id AND anio = :anio AND mes = :mes AND id_tipo = 2 LIMIT 1");
    $stmt->execute([':id' => $id_condominio, ':anio' => $anio, ':mes' => $mes]);
    $estados['ejecucion'] = $stmt->fetchColumn() ?: 'Pendiente';

    return $estados;
}

function btnEstado($icon, $label, $estado, $action = '#') {
    $badgeClass = match ($estado) {
        'En proceso' => 'bg-warning text-dark',
        'Borrador' => 'bg-secondary text-light',
        'Emitida' => 'bg-success text-light',
        'Generadas' => 'bg-primary text-light',
        'Pendientes' => 'bg-danger text-light',
        default => 'bg-light border text-dark',
    };

    return "<a href=\"$action\" class=\"btn btn-outline-secondary w-100 text-start d-flex align-items-center mb-1\">
        <i class=\"me-2 bi bi-$icon\"></i>
        <span class=\"flex-grow-1\">$label</span>
        <span class=\"badge $badgeClass ms-auto\">$estado</span>
    </a>";
}
?>

<head>
  <title>Gestión de Presupuestos | <?= NOMBREAPP ?></title>
  <?php include 'layouts/head.php'; ?>
  <?php include 'layouts/head-style.php'; ?>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>

<?php include 'layouts/body.php'; ?>
<div id="layout-wrapper">
<?php include 'layouts/menu.php'; ?>
  <div class="main-content">
    <div class="page-content">
      <div class="container-fluid">
        <div class="row mb-3 align-items-end">
          <div class="col-md-6">
            <h4 class="card-title">Presupuestos y Relación de Gastos - <?= htmlspecialchars($nombre_condominio); ?> | <strong><?= $anio_seleccionado ?></strong></h4>
          </div>
          <div class="col-md-3">
            <form method="GET" id="formAnios">
              <label for="anio" class="form-label">Año:</label>
              <select class="form-select" name="anio" id="anio" onchange="document.getElementById('formAnios').submit()">
                <?php foreach ($anios_disponibles as $anio): ?>
                <option value="<?= $anio ?>" <?= ($anio == $anio_seleccionado ? 'selected' : '') ?>><?= $anio ?></option>
                <?php endforeach; ?>
              </select>
            </form>
          </div>
        </div>

        <div class="row">
          <?php
          $meses = [1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio',
                    7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'];

          foreach ($meses as $num => $nombre):
            $estados = obtenerEstados($conn, $id_condominio, $anio_seleccionado, $num);
          ?>
            <div class="col-sm-6 col-md-4 col-xl-3">
              <div class="card shadow-sm border">
                <h5 class="card-header bg-light text-primary fw-bold text-uppercase border-bottom"><?= $nombre ?></h5>
                <div class="card-body">
                  <?= btnEstado('clipboard-check-fill', 'Presupuesto', $estados['presupuesto'], "javascript:abrirPresupuesto($num, '$esquema_cuota')") ?>
                  <?= btnEstado('bell', 'Notificaciones', $estados['notificaciones'], "javascript:alert('Notificaciones mes $num')") ?>
                  <?= btnEstado('file-earmark-text', 'Ejecución', $estados['ejecucion'], "javascript:alert('Ejecución mes $num')") ?>

                  <a href="presupuesto_mes.php?mes=<?= $num ?>&anio=<?= $anio_seleccionado ?>" class="btn btn-outline-primary w-100 mt-2">
                    Gestionar más
                  </a>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    <?php include 'layouts/footer.php'; ?>
  </div>
</div>

<?php include 'layouts/right-sidebar.php'; ?>
<?php include 'layouts/vendor-scripts.php'; ?>
<?php include 'modal_presupuesto.php'; ?>
<script src="assets/js/app.js"></script>


<script>

document.addEventListener('DOMContentLoaded', () => {
  window.abrirPresupuesto = function (mes, esquema) {
    const modalEl = document.getElementById('modalPresupuesto');
    const modal = new bootstrap.Modal(modalEl); // ✅ reemplazo compatible

    const anio = <?= $anio_seleccionado ?>;
    const idCondominio = <?= $id_condominio ?>;
    const tbody = modalEl.querySelector('#tablaDetallePresupuesto tbody');

    modalEl.querySelector('select[name="mes"]').value = mes;
    modalEl.querySelector('input[name="anio"]').value = anio;
    modalEl.querySelector('#id_condominio_presupuesto').value = idCondominio;

    tbody.innerHTML = '';

    fetch(`get_presupuesto_existente.php?condominio=${idCondominio}&anio=${anio}&mes=${mes}`)
      .then(response => response.json())
      .then(data => {
        const tipo = data.status === 'ok' ? data.tipo : esquema;

        modalEl.querySelectorAll('input[name="tipo_cuota"]').forEach(r => {
          r.checked = (r.value === tipo);
        });

        document.getElementById('columnaAccion').style.display = (tipo === 'alicuota') ? '' : 'none';

        if (tipo === 'fija') {
          const descripcion = `CUOTA DEL MES DE ${new Date(2000, mes - 1).toLocaleString('es-ES', { month: 'long' }).toUpperCase()} ${anio}`;
          fetch('get_cuota_fija.php')
            .then(res => res.json())
            .then(cuota => {
              const fila = `
                <tr>
                  <td><input type="text" name="cuenta[]" class="form-control" value=46 readonly></td>
                  <td><input type="text" name="descripcion[]" class="form-control" value="${descripcion}" readonly></td>
                  <td><input type="text" name="monto[]" class="form-control" value="${parseFloat(cuota.monto).toFixed(2)}" readonly></td>
                  <td></td>
                </tr>`;
              tbody.innerHTML = fila;
              recalcularPresupuesto();
              modal.show();
            });
        } else if (data.status === 'ok') {
          fetch('get_select_plan_cuentas.php?id_condominio=' + idCondominio)
            .then(r => r.text())
            .then(opciones => {
              data.detalle.forEach(d => {
                const fila = document.createElement('tr');
                fila.innerHTML = `
                  <td><select name="cuenta[]" class="form-select">${opciones}</select></td>
                  <td><input type="text" name="descripcion[]" class="form-control text-uppercase" value="${d.descripcion}" oninput="this.value = this.value.toUpperCase();" required></td>
                  <td><input type="text" name="monto[]" class="form-control" value="${parseFloat(d.monto).toFixed(2)}" required oninput="recalcularPresupuesto()"></td>
                  <td><button type="button" class="btn btn-sm btn-danger" onclick="this.closest('tr').remove(); recalcularPresupuesto()">x</button></td>
                `;
                fila.querySelector('select').value = d.id_plan_cuenta;
                tbody.appendChild(fila);
              });
              recalcularPresupuesto();
              modal.show();
            });
        } else {
          fetch('get_select_plan_cuentas.php?id_condominio=' + idCondominio)
            .then(r => r.text())
            .then(opciones => {
                const fila = `
  <tr>
    <td>
      <input type="hidden" name="id_plan_cuenta[]" value="46">
      <input type="text" class="form-control" value="4.1" readonly>
    </td>
    <td><input type="text" name="descripcion[]" class="form-control" value="${descripcion}" readonly></td>
    <td><input type="text" name="monto[]" class="form-control" value="${parseFloat(cuota.monto).toFixed(2)}" readonly></td>
    <td></td>
  </tr>`;
              tbody.innerHTML = fila;
              recalcularPresupuesto();
              modal.show();
            });
        }
      });
  };
});


</script>


