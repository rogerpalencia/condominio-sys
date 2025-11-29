<?php session_start(); ?>

<?php
require_once("core/PDO.class.php");
$conn = DB::getInstance();

$_SESSION['userlogin'];
$userid= $_SESSION['userid'] ;
?>

<header id="page-topbar">
    <div class="navbar-header">
        <div class="d-flex">
            <!-- LOGO -->

            <div class="navbar-brand-box">
                <a href="index.php" class="logo logo-dark">
                    <span class="logo-sm">
                        <img src="assets/images/LOGO_SEMATPC_300.fw.png" alt="" height="25">
                    </span>
                    <style>
                    .navbar-brand-box {
                        padding-top: 25px;
                    }
                    </style>
                    <span class="logo-lg">

                        <img src="assets/images/LOGO_SEMATPC_300.fw.png" alt="" height="80"> <span
                            class="logo-txt"></span>
                    </span>
                </a>

                <a href="index.php" class="logo logo-light">
                    <span class="logo-sm">
                        <img src="assets/images/logo-sm.svg" alt="" height="24">
                    </span>
                    <span class="logo-lg">
                        <img src="assets/images/logo-sm.svg" alt="" height="24"> <span class="logo-txt">semat-pc</span>
                    </span>
                </a>
            </div>

            <button type="button" class="btn btn-sm px-3 font-size-16 header-item" id="vertical-menu-btn">
                <i class="fa fa-fw fa-bars"></i>
            </button>

            <!-- App Search-->
            <form class="app-search d-none d-lg-block">
                <div class="position-relative">

                </div>
            </form>
        </div>

        <div class="d-flex">

            <div class="dropdown d-inline-block d-lg-none ms-2">
                <button type="button" class="btn header-item" id="page-header-search-dropdown" data-bs-toggle="dropdown"
                    aria-haspopup="true" aria-expanded="false">
                    <i data-feather="search" class="icon-lg"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0"
                    aria-labelledby="page-header-search-dropdown">

                    <form class="p-3">
                        <div class="form-group m-0">
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="<?php echo $language["Search"]; ?>"
                                    aria-label="Search Result">

                                <button class="btn btn-primary" type="submit"><i class="mdi mdi-magnify"></i></button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="dropdown d-none d-sm-inline-block">
                <button type="button" class="btn header-item" id="mode-setting-btn">
                    <i data-feather="moon" class="icon-lg layout-mode-dark"></i>
                    <i data-feather="sun" class="icon-lg layout-mode-light"></i>
                </button>
            </div>

            <div class="dropdown d-inline-block">
                <button type="button" class="btn header-item bg-soft-light border-start border-end"
                    id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <img class="rounded-circle header-profile-user" src="assets/images/users/user.png"
                        alt="Header Avatar">
                    <span
                        class="d-none d-xl-inline-block ms-1 fw-medium"><?php echo strtoupper($_SESSION['username']); ?></span>
                    <i class="mdi mdi-chevron-down d-none d-xl-inline-block"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end">
                    <!-- item-->


                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="logout.php"><i
                            class="mdi mdi-logout font-size-16 align-middle me-1"></i>
                        <?php echo $language["Logout"]; ?></a>
                </div>
            </div>
            <!-- END USUARIO SESSION -->


        </div>
    </div>
</header>


<div class="vertical-menu">

    <div data-simplebar class="h-100">

        <!--- Sidemenu -->





        <div id="sidebar-menu">
            <!-- Left Menu Start -->
            <ul class="metismenu list-unstyled" id="side-menu">
                <li class="menu-title" data-key="t-menu"><?php echo $language["Menu"]; ?></li>

                <li>
                    <a href="index.php">
                        <i data-feather="home"></i>
                        <span data-key="t-dashboard">Inicio V2</span>
                    </a>
                </li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow">
                        <i class="fas fa-money-bill-wave"></i>
                        <span data-key="t-pages">Pagos</span>
                    </a>
                    <ul class="sub-menu mm-collapse" aria-expanded="false">
                        <li><a href="revisionpagos.php" data-key="t-register">Validación de Pagos</a></li>
                    </ul>
                </li>


                <?php
// Obtener el ID del usuario actual
$id_usuario = $userid; // Reemplazar con el ID del usuario actual

$sql_modulos = 'SELECT DISTINCT m.*, COUNT(p.id_programa) as num_programas FROM modulos m LEFT JOIN programas p ON m.id_modulo = p.id_modulo WHERE p.id_programa IN (SELECT id_programa FROM detalles_perfiles WHERE id_perfil = ' . $id_usuario . ') GROUP BY m.id_modulo ORDER BY m.orden';
$rs_modulos = $conn->query($sql_modulos);

foreach ($rs_modulos as $key_modulos => $row_modulos) {
    if ($row_modulos['num_programas'] > 0) {
        echo '<li>';
        echo '<a href="javascript: void(0);" class="has-arrow">';
        echo '<i class="' . $row_modulos['icono'] . '"></i>';
        echo '<span data-key="t-pages">' . $row_modulos['nombre'] . '</span>';
        echo '</a>';
        echo '<ul class="sub-menu mm-collapse" aria-expanded="false">';

        $sql_programas = 'SELECT * FROM programas WHERE id_modulo = ' . $row_modulos['id_modulo'] . ' AND id_programa IN (SELECT id_programa FROM detalles_perfiles WHERE id_perfil = ' . $id_usuario . ') ORDER BY orden';
        $rs_programas = $conn->query($sql_programas);

        foreach ($rs_programas as $key_programas => $row_programas) {
            echo '<li>';
            echo '<a href="' . $row_programas['accion'] . '" data-key="t-register">';
            echo '<span data-key="t-pages">' . $row_programas['nombre'] . '</span>';
            echo '</a>';
            echo '</li>';
        }

        echo '</ul>';
        echo '</li>';
    }
}
?>



<li>

<a href="logout.php" data-key="t-lock-screen" data-key="t-register">
    <i data-feather="log-out"></i>
    <span data-key="t-pages">Salir</span>
</a>
</li>

            </ul>





        </div>

    </div>
</div>




</div>
</div>