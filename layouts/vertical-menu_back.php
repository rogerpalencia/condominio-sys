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
                        .navbar-brand-box { padding-top: 25px; }
                    </style>
                    <span class="logo-lg" >
                      
                        <img src="assets/images/LOGO_SEMATPC_300.fw.png" alt="" height="80" > <span class="logo-txt"></span>
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
                <button type="button" class="btn header-item" id="page-header-search-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i data-feather="search" class="icon-lg"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0" aria-labelledby="page-header-search-dropdown">

                    <form class="p-3">
                        <div class="form-group m-0">
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="<?php echo $language["Search"]; ?>" aria-label="Search Result">

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

            <!--div class="dropdown d-none d-lg-inline-block ms-1">
                <button type="button" class="btn header-item"
                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i data-feather="grid" class="icon-lg"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                    <div class="p-2">
                        <div class="row g-0">
                            <div class="col">
                                <a class="dropdown-icon-item" href="#">
                                    <img src="assets/images/brands/github.png" alt="Github">
                                    <span>GitHub</span>
                                </a>
                            </div>
                            <div class="col">
                                <a class="dropdown-icon-item" href="#">
                                    <img src="assets/images/brands/bitbucket.png" alt="bitbucket">
                                    <span>Bitbucket</span>
                                </a>
                            </div>
                            <div class="col">
                                <a class="dropdown-icon-item" href="#">
                                    <img src="assets/images/brands/dribbble.png" alt="dribbble">
                                    <span>Dribbble</span>
                                </a>
                            </div>
                        </div-->

            <!--div class="row g-0">
                            <div class="col">
                                <a class="dropdown-icon-item" href="#">
                                    <img src="assets/images/brands/dropbox.png" alt="dropbox">
                                    <span>Dropbox</span>
                                </a>
                            </div>
                            <div class="col">
                                <a class="dropdown-icon-item" href="#">
                                    <img src="assets/images/brands/mail_chimp.png" alt="mail_chimp">
                                    <span>Mail Chimp</span>
                                </a>
                            </div>
                            <div class="col">
                                <a class="dropdown-icon-item" href="#">
                                    <img src="assets/images/brands/slack.png" alt="slack">
                                    <span>Slack</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div

            <div class="dropdown d-inline-block">
                <button type="button" class="btn header-item noti-icon position-relative" id="page-header-notifications-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i data-feather="bell" class="icon-lg"></i>
                    <span class="badge bg-danger rounded-pill">5</span>
                </button>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0" aria-labelledby="page-header-notifications-dropdown">
                    <div class="p-3">
                        <div class="row align-items-center">
                            <div class="col">
                                <h6 class="m-0"> <?php echo $language["Notifications"]; ?> </h6>
                            </div>
                            <div class="col-auto">
                                <a href="#!" class="small text-reset text-decoration-underline"> <?php echo $language["Unread"]; ?> (3)</a>
                            </div>
                        </div>
                    </div>
                    <div data-simplebar style="max-height: 230px;">
                        <a href="#!" class="text-reset notification-item">
                            <div class="d-flex">
                                <div class="flex-shrink-0 me-3">
                                    <img src="assets/images/users/user.png" class="rounded-circle avatar-sm" alt="user-pic">
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1"><?php echo $language["Gobermnador"]; ?></h6>
                                    <div class="font-size-13 text-muted">
                                        <p class="mb-1"><?php echo 'Mensaje institucional'; ?>.</p>
                                        <p class="mb-0"><i class="mdi mdi-clock-outline"></i> <span><?php echo $language["1_hours_ago"]; ?></span></p>
                                    </div>
                                </div>
                            </div>
                        </a>
                        <a href="#!" class="text-reset notification-item">
                            <div class="d-flex">
                                <div class="flex-shrink-0 avatar-sm me-3">
                                    <span class="avatar-title bg-primary rounded-circle font-size-16">
                                        <i class="bx bx-cart"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1"><?php echo 'Alcalde'; ?></h6>
                                    <div class="font-size-13 text-muted">
                                        <p class="mb-1"><?php echo 'Recomendaciones al Semat-PC'; ?></p>
                                        <p class="mb-0"><i class="mdi mdi-clock-outline"></i> <span><?php echo $language["3_min_ago"]; ?></span></p>
                                    </div>
                                </div>
                            </div>
                        </a>
                        <a href="#!" class="text-reset notification-item">
                            <div class="d-flex">
                                <div class="flex-shrink-0 avatar-sm me-3">
                                    <span class="avatar-title bg-success rounded-circle font-size-16">
                                        <i class="bx bx-badge-check"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1"><?php echo 'Superintendente'; ?></h6>
                                    <div class="font-size-13 text-muted">
                                        <p class="mb-1"><?php echo 'Nuevas Instrucciones'; ?></p>
                                        <p class="mb-0"><i class="mdi mdi-clock-outline"></i> <span><?php echo $language["3_min_ago"]; ?></span></p>
                                    </div>
                                </div>
                            </div>
                        </a>

                        <a href="#!" class="text-reset notification-item">
                            <div class="d-flex">
                                <div class="flex-shrink-0 me-3">
                                    <img src="assets/images/users/user.png" class="rounded-circle avatar-sm" alt="user-pic">
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1"><?php echo $language["Salena_Layfield"]; ?></h6>
                                    <div class="font-size-13 text-muted">
                                        <p class="mb-1"><?php echo $language["As_a_skeptical_Cambridge_friend_of_mine_occidental"]; ?>.</p>
                                        <p class="mb-0"><i class="mdi mdi-clock-outline"></i> <span><?php echo $language["1_hours_ago"]; ?></span></p>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="p-2 border-top d-grid">
                        <a class="btn btn-sm btn-link font-size-14 text-center" href="javascript:void(0)">
                            <i class="mdi mdi-arrow-right-circle me-1"></i> <span><?php echo $language["View_More"]; ?></span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="dropdown d-inline-block">
                <button type="button" class="btn header-item right-bar-toggle me-2">
                    <i data-feather="settings" class="icon-lg"></i>
                </button>
            </div>
-->
            <!-- USUARIO SESSION -->
            <div class="dropdown d-inline-block">
                <button type="button" class="btn header-item bg-soft-light border-start border-end" id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <img class="rounded-circle header-profile-user" src="assets/images/users/user.png" alt="Header Avatar">
                    <span class="d-none d-xl-inline-block ms-1 fw-medium"><?php echo strtoupper($_SESSION['username']); ?></span>
                    <i class="mdi mdi-chevron-down d-none d-xl-inline-block"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end">
                    <!-- item-->
                   
                    
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="logout.php"><i class="mdi mdi-logout font-size-16 align-middle me-1"></i> <?php echo $language["Logout"]; ?></a>
                </div>
            </div>
            <!-- END USUARIO SESSION -->


        </div>
    </div>
</header>

<!-- ========== Left Sidebar Start ========== -->
<!-- ========== Left Sidebar Start ========== -->
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
                        <span data-key="t-dashboard">Inicio</span>
                    </a>
                </li>

          
                <li>
                    <a href="javascript: void(0);" class="has-arrow">
                        <i data-feather="book"></i>
                        <span data-key="t-pages">Registros</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="contribuyentes.php" data-key="t-register">Contribuyentes</a></li>
                        <li><a href="empresas.php" data-key="t-register">Empresas</a></li>
                        <li><a href="inmuebles.php" data-key="t-register">Inmuebles</a></li>
                        <li><a href="vehiculos.php" data-key="t-register">Vehículos</a></li>
                    </ul>
                </li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow">
                         <i class=" fas fa-ship"></i> 
                        <span data-key="t-pages">Aduana</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="revisionduas.php" data-key="t-register">Validación de Aduana</a></li>
                    </ul>
                </li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow">
                         <i class="fas fa-money-bill-wave"></i> 
                        <span data-key="t-pages">Pagos</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="revisionpagos.php" data-key="t-register">Validación de Pagos</a></li>
                    </ul>
                </li>

                    <li>
                        <a href="javascript: void(0);" class="has-arrow"> 
                            <i class="fas fa-file-signature "></i> 
                            <span data-key="t-pages">Taquilla</span>
                        </a>
                        <ul class="sub-menu" aria-expanded="false">
                            <li><a href="revisionduastaq.php" data-key="t-register">Sellado de Planillas</a></li>
                            <li><a href="pago_x_punto_menu.php" data-key="t-register">Pagos por Punto</a></li>
                        </ul>
   
                    </li>

                    <li>

                  
                        <?php
                    $sql = "SELECT DISTINCT p.id_programa as codigo,p.grupo as grupo, p.nombre as nombre, p.accion as accion, orden
                    FROM programas p 
                    WHERE p.grupo=5   ORDER BY p.orden"; // GRUPO ADUANA

                    
                    $rs = $conn->query($sql);
                    if (empty($rs)) {  } else {
                        //echo $sql;
                        echo  ' <a href="javascript: void(0);" class="has-arrow"> 
                    <i class="fas fa-chart-bar "></i> 
                    <span data-key="t-pages">Reportes</span>
                    </a>';
                        echo '<ul class="sub-menu" aria-expanded="false">';
                        foreach ($rs as $row) {
                            echo '<li><a href="' . $row['accion'] . '" data-key="t-register">' . $row["nombre"] . '</a></li>';
                        }
                        echo '</ul>';
                    }
                    ?>
              
   
                    </li>


                <!--li>
                    <a href="javascript: void(0);" class="has-arrow">
                        <i data-feather="pie-chart"></i>
                        <span data-key="t-pages">Tributos</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="aduana.php" data-key="t-register">Aduana</a></li>
                        <li><a href="estimada.php" data-key="t-register">Estimada</a></li>
                    </ul>
                </li-->

                <li>
                    <a href="javascript: void(0);" class="has-arrow">
                        <i data-feather="sliders"></i>
                        <span data-key="t-pages">Sistema</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">

                        <li><a href="javascript:void(0)" data-key="t-recover-password">Cambiar Clave</a></li>
                        <?php if ($_SESSION['perfil']===1){ ?>
                            <li><a href="javascript:void(0)" data-key="t-recover-password">Usuarios</a></li>
                            <li><a href="javascript:void(0)" data-key="t-recover-password">Perfiles y Roles</a></li>
                        <?php } ?>
                      <!--  <li><a href="auth-lock-screen.php" data-key="t-lock-screen">Bloquear Pantalla</a></li> -->
                        <li><a href="logout.php" data-key="t-lock-screen">Salir <span data-key="t-pages">Sistema</span>
                        </a>
                </li>


            </ul>
            </li>

            <li>

                <a href="logout.php" data-key="t-lock-screen" data-key="t-register">
                    <i data-feather="log-out"></i>
                    <span data-key="t-pages">Salir</span>
                </a>
            </li>

            <!--li class="menu-title mt-2" data-key="t-components"><?php echo $language["Elements"]; ?></li-->

        </div>
        <!-- Sidebar -->
    </div>
</div>
<!-- Left Sidebar End -->