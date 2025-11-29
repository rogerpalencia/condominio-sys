<?php
session_start();
require_once("core/PDO.class.php");

// Validación de sesión
if (!isset($_SESSION['userid']) || !isset($_SESSION['username'])) {
    die("Error: Usuario no autenticado o sesión no configurada.");
}
$userid = (int)$_SESSION['userid'];

// Conexión a la base de datos
try {
    $conn = DB::getInstance();
} catch (Exception $e) {
    die("Error: Conexión a la base de datos fallida - " . $e->getMessage());
}

// Fallback para $language si no está definido
if (!isset($language)) {
    $language = [
        "Search"       => "Buscar",
        "Logout"       => "Cerrar sesión",
        "Menu"         => "Menú",
        "SelectCondo"  => "Condominio",
        "ChangeSaved"  => "Condominio cambiado",
    ];
}

/**
 * Cargar condominios disponibles para el usuario por cualquiera de las dos vías:
 * - Rol por condominio:    menu_login.usuario_rol (ur.id_condominio)
 * - Administrador directo: public.administradores (a.id_condominio)
 * Nota: Usamos UNION para cubrir ambos escenarios; DISTINCT elimina duplicados.
 */
$sql_condos = '
    SELECT DISTINCT c.id_condominio, c.nombre
    FROM public.condominio c
    JOIN "menu_login".usuario_rol ur
      ON ur.id_condominio = c.id_condominio
    WHERE ur.id_usuario = :id_usuario

    UNION

    SELECT DISTINCT c.id_condominio, c.nombre
    FROM public.condominio c
    JOIN public.administradores a
      ON a.id_condominio = c.id_condominio
    WHERE a.id_usuario = :id_usuario
      AND COALESCE(a.estatus, true) = true

    ORDER BY nombre
';

try {
    $stmt_condos = $conn->prepare($sql_condos);
    $stmt_condos->bindParam(':id_usuario', $userid, PDO::PARAM_INT);
    $stmt_condos->execute();
    $condominios = $stmt_condos->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al cargar condominios: " . $e->getMessage());
}

// Resolver id_condominio actual en sesión
$currentCondoId = isset($_SESSION['id_condominio']) ? (int)$_SESSION['id_condominio'] : null;
$idsValidos     = array_map('intval', array_column($condominios, 'id_condominio'));
$hasMultiple    = count($condominios) > 1;

if (!$hasMultiple) {
    if (count($condominios) === 1) {
        $currentCondoId = (int)$condominios[0]['id_condominio'];
        $_SESSION['id_condominio'] = $currentCondoId;
    } else {
        $currentCondoId = null;
        unset($_SESSION['id_condominio']);
    }
} else {
    if ($currentCondoId === null || !in_array((int)$currentCondoId, $idsValidos, true)) {
        $currentCondoId = (int)$condominios[0]['id_condominio'];
        $_SESSION['id_condominio'] = $currentCondoId;
    }
}

// Helper para obtener nombre del condominio actual
function nombreCondominioActual(array $condominios, $idActual) {
    foreach ($condominios as $c) {
        if ((int)$c['id_condominio'] === (int)$idActual) return $c['nombre'];
    }
    return null;
}
$nombreCondoActual = $currentCondoId ? nombreCondominioActual($condominios, $currentCondoId) : null;

/* ===== Logo izquierdo dinámico por condominio ===== */
$logoLeftDefault = 'assets/images/default.png';
$logoLeftUrl     = $logoLeftDefault;

if ($currentCondoId) {
    try {
        $stmtLogo = $conn->prepare('SELECT url_logo_izquierda FROM public.condominio WHERE id_condominio = :cid LIMIT 1');
        $stmtLogo->execute([':cid' => $currentCondoId]);
        $rowLogo = $stmtLogo->fetch(PDO::FETCH_ASSOC);
        if ($rowLogo && !empty($rowLogo['url_logo_izquierda'])) {
            $logoLeftUrl = $rowLogo['url_logo_izquierda'];
        }
    } catch (PDOException $e) {
        // No bloquear por el logo; usar fallback
        $logoLeftUrl = $logoLeftDefault;
    }
}

?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Control</title>

    <!-- Estilos -->
    <link href="assets/libs/metismenu/metisMenu.min.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/icons.min.css">
    <link rel="stylesheet" href="assets/css/app.min.css">
    <style>

  .navbar-brand-box { padding-top: 25px; }

  /* === Logos a 50px === */
  .logo .logo-sm img,
  .logo .logo-lg img,
  .navbar-brand-box img {
    height: 50px !important;
    width: auto !important;
  }

  /* Bloque del selector */
  .condo-selector-wrap {
    display: flex; align-items: center; gap: .75rem; padding: 0 .85rem;
  }
  .condo-selector-wrap .mdi { font-size: 1.05rem; opacity: .9; }
  .condo-badge { font-size: .8rem; opacity: .8; margin-left: .35rem; }
  #selectCondominio { min-width: 260px; }
  @media (max-width: 576px) {
    .condo-selector-wrap { padding: .5rem; gap: .6rem; }
    .condo-badge { display: none; }
  }
  .condo-icon { margin-right: .15rem; }
  .condo-id   { margin-left: .35rem; }

  /* === Quitar el “ángulo” (caret) de MetisMenu en items con submenú === */
  .vertical-menu a.has-arrow::after {
    content: none !important;
  }

  /* === Botón hamburguesa: oculto en >= lg === */
  @media (min-width: 992px) {
    #vertical-menu-btn {
      display: none !important;
    }
  }

/* Estado colapsado del sidebar */
body.sidebar-collapsed .vertical-menu {
  width: 72px;
}

body.sidebar-collapsed .vertical-menu .has-arrow:after {
  display: none !important;
}

body.sidebar-collapsed .vertical-menu #side-menu li a span {
  display: none;
}

body.sidebar-collapsed .vertical-menu #side-menu li a i {
  margin-right: 0;
  text-align: center;
  width: 100%;
}

body.sidebar-collapsed .main-content {
  margin-left: 72px;
}






</style>

    
</head>

<body>

<!-- Header -->
<header id="page-topbar">
    <div class="navbar-header">
        <div class="d-flex">
            <!-- LOGO (usa logo izquierdo del condominio activo; fallback al default) -->
            <div class="navbar-brand-box">
                <a href="index.php" class="logo logo-dark">
                    <span class="logo-sm">
                        <img src="<?php echo htmlspecialchars($logoLeftUrl); ?>" alt="Logo" height="25">
                    </span>
                    <span class="logo-lg">
                        <img src="<?php echo htmlspecialchars($logoLeftUrl); ?>" alt="Logo" height="50">
                        <span class="logo-txt"></span>
                    </span>
                </a>
                <a href="index.php" class="logo logo-light">
                    <span class="logo-sm">
                        <img src="<?php echo htmlspecialchars($logoLeftUrl); ?>" alt="Logo" height="25">
                    </span>
                    <span class="logo-lg">
                        <img src="<?php echo htmlspecialchars($logoLeftUrl); ?>" alt="Logo" height="50">
                        <span class="logo-txt">semat-pc</span>
                    </span>
                </a>
            </div>

            <button type="button" class="btn btn-sm px-3 font-size-16 header-item" id="vertical-menu-btn">
                <i class="fa fa-fw fa-bars"></i>
            </button>

            <!-- Selector de Condominio -->
            <?php if ($hasMultiple): ?>
            <div class="condo-selector-wrap">
                <label for="selectCondominio" class="form-label mb-0 condo-icon">
                    <i class="mdi mdi-office-building"></i>
                </label>
                <select id="selectCondominio" class="form-select form-select-sm">
                    <?php foreach ($condominios as $c): ?>
                        <option value="<?php echo (int)$c['id_condominio']; ?>"
                            <?php echo ((int)$c['id_condominio'] === (int)$currentCondoId ? 'selected' : ''); ?>>
                            <?php echo htmlspecialchars($c['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <span class="text-muted condo-badge condo-id d-none d-sm-inline">
                    ID: <?php echo (int)$currentCondoId; ?>
                </span>
            </div>
            <?php else: ?>
                <?php if ($nombreCondoActual): ?>
                    <div class="condo-selector-wrap">
                        <i class="mdi mdi-office-building condo-icon"></i>
                        <span class="badge bg-secondary">
                            <?php echo htmlspecialchars($nombreCondoActual); ?>
                        </span>
                        <span class="text-muted condo-badge condo-id d-none d-sm-inline">
                            ID: <?php echo (int)$currentCondoId; ?>
                        </span>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

        </div>

        <div class="d-flex">
            <div class="dropdown d-inline-block">
                <button type="button" class="btn header-item bg-soft-light border-start border-end"
                    id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <img class="rounded-circle header-profile-user" src="assets/images/users/user.png" alt="Header Avatar">
                    <span class="d-none d-xl-inline-block ms-1 fw-medium"><?php echo strtoupper($_SESSION['username']); ?></span>
                    <i class="mdi mdi-chevron-down d-none d-xl-inline-block"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end">
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="logout.php">
                        <i class="mdi mdi-logout font-size-16 align-middle me-1"></i>
                        <?php echo $language["Logout"]; ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- Sidebar/Menu -->
<div class="vertical-menu">
    <div data-simplebar class="h-100">
        <div id="sidebar-menu">
            <!-- Menú de la izquierda -->
            <ul class="list-unstyled" id="side-menu">
                <li class="menu-title" data-key="t-menu"><?php echo $language["Menu"]; ?></li>

                <li>
                    <a href="index.php">
                        <i class="fas fa-home"></i>
                        <span data-key="t-dashboard">Inicio</span>
                    </a>
                </li>

                <?php
                $id_usuario = $userid;

                $sql_modulos = '
                    SELECT DISTINCT m.*, COUNT(p.id_programa) as num_programas
                    FROM "menu_login".modulos m
                    LEFT JOIN "menu_login".programas p ON m.id_modulo = p.id_modulo
                    WHERE p.id_programa IN (
                        SELECT pr.id_programa
                        FROM "menu_login".programas_rol pr
                        JOIN "menu_login".usuario_rol ur ON pr.id_rol = ur.id_rol
                        WHERE ur.id_usuario = :id_usuario
                    )
                    GROUP BY m.id_modulo
                    ORDER BY m.orden
                ';

                try {
                    $stmt_modulos = $conn->prepare($sql_modulos);
                    $stmt_modulos->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
                    $stmt_modulos->execute();
                    $rs_modulos = $stmt_modulos->fetchAll(PDO::FETCH_ASSOC);
                } catch (PDOException $e) {
                    die("Error: Consulta de módulos fallida - " . $e->getMessage());
                }

                foreach ($rs_modulos as $row_modulos) {
                    if ($row_modulos['num_programas'] > 0) {
                        echo '<li>';
                        echo '<a href="javascript:void(0);" class="has-arrow">';
                        echo '<i class="' . htmlspecialchars($row_modulos['icono']) . '"></i>';
                        echo '<span>' . htmlspecialchars($row_modulos['nombre']) . '</span>';
                        echo '</a>';
                        echo '<ul class="sub-menu mm-collapse" aria-expanded="false">';

                        $sql_programas = '
                            SELECT p.*
                            FROM "menu_login".programas p
                            WHERE p.id_modulo = :id_modulo
                            AND p.id_programa IN (
                                SELECT pr.id_programa
                                FROM "menu_login".programas_rol pr
                                JOIN "menu_login".usuario_rol ur ON pr.id_rol = ur.id_rol
                                WHERE ur.id_usuario = :id_usuario
                            )
                            ORDER BY p.orden
                        ';
                        try {
                            $stmt_programas = $conn->prepare($sql_programas);
                            $stmt_programas->bindParam(':id_modulo', $row_modulos['id_modulo'], PDO::PARAM_INT);
                            $stmt_programas->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
                            $stmt_programas->execute();
                            $rs_programas = $stmt_programas->fetchAll(PDO::FETCH_ASSOC);

                            foreach ($rs_programas as $row_programas) {
                                echo '<li>';
                                echo '<a href="' . htmlspecialchars($row_programas['accion']) . '">';
                                echo '<span><small>' . htmlspecialchars($row_programas['nombre']) . '</small></span>';
                                echo '</a>';
                                echo '</li>';
                            }
                        } catch (PDOException $e) {
                            echo "<li>Error en la consulta de programas: " . $e->getMessage() . "</li>";
                        }
                        echo '</ul>';
                        echo '</li>';
                    }
                }
                ?>

                <li>
                    <a href="logout.php">
                        <i class="fas fa-sign-out-alt"></i>
                        <span data-key="t-pages">Salir</span>
                    </a>
                </li>
            </ul>
        </div>
        <div style="text-align: center;">
            <small><small>V-1.2</small></small>
        </div>
    </div>
</div>

<!-- Scripts Necesarios -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="assets/libs/metismenu/metisMenu.min.js"></script>
<script src="assets/js/bootstrap.bundle.min.js"></script>

<!-- Inicialización de MetisMenu + Cambio de condominio -->
<script>
    $(document).ready(function () {
        $('#side-menu').metisMenu();

        $('#selectCondominio').on('change', function () {
            const id_condominio = $(this).val();
            $.ajax({
                url: 'set_condominio.php',
                type: 'POST',
                dataType: 'json',
                data: { id_condominio: id_condominio },
                success: function (resp) {
                    if (resp.status === 'ok') {
                        location.reload();
                    } else {
                        alert('Error: ' + (resp.message || 'No se pudo cambiar el condominio'));
                    }
                },
                error: function (xhr) {
                    alert('Error de red: ' + (xhr.responseText || ''));
                }
            });
        });
    });
</script>

</body>
</html>
