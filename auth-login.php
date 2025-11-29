<?php 
@session_start();

require_once "core/PDO.class.php" ; 
$conn=  DB::getInstance();
require_once "layouts/vars.php" ; 
?>

<?php include 'layouts/head-main.php'; ?>
<head>
    <title>Acceso | <?php echo NOMBREAPP ?></title>
    <?php include 'layouts/head.php'; ?>
    <?php include 'layouts/head-style.php'; ?>
</head>

<?php include 'layouts/body.php'; ?>
<input type="hidden" name="parserJsn" id="parserJsn" value="<?php echo PARSERJSN ?>">
<input type="hidden" name="mensaje" id="mensaje" value="<?php echo $mensaje ?>">

<div class="auth-page">
    <div class="container-fluid p-0">
        <div class="row g-0">
            <div class="col-xxl-3 col-lg-4 col-md-5">
                <div class="auth-full-page-content d-flex p-sm-5 p-4">
                    <div class="w-100">
                        <div class="d-flex flex-column h-100">
                            <div class="mb-2 mb-md-2 text-center">
                                <a href="index.php" class="d-block auth-logo">
                                    <img src="assets/images/LOGO_SEMATPC_300.fw.png" alt="" height="100" > <span class="logo-txt"></span>
                                </a>
                            </div>
                            <div class="auth-content my-auto">
                                <div class="text-center">
                                 <h5 class="mb-0" style="color:#FF0000">Acceso Para Administradores de Condominios</h5>

                                </div>
                                <form class="custom-form mt-4 pt-2">
                                    <div class="mb-1">
                                        <label class="form-label" for="username">Usuario</label>
                                        <input type="text" class="form-control" id="username" placeholder="Su Usuario" name="username">
                                    </div>
                                    <div class="mb-1">
                                        <div class="d-flex align-items-start">
                                            <div class="flex-grow-1">
                                                <label class="form-label" for="userpass">Clave</label>
                                            </div>
                                            <div class="flex-shrink-0">
                                                <div class="">
                                                    <a href="auth-recoverpw.php" class="text-muted">Olvido su Clave?</a>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="input-group auth-pass-inputgroup">
                                            <input type="password" class="form-control" placeholder="Indique su Clave" name="userpass" id="userpass" aria-label="Clave" aria-describedby="password-addon">
                                            <span class="text-danger"></span>
                                            <button class="btn btn-light ms-0" type="button" id="password-addon"><i class="mdi mdi-eye-outline"></i></button>
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col">
                                            <div class="form-check">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-2">
                                        <button class="btn btn-primary w-100 waves-effect waves-light" type="button" onclick="dologin()">Acceder</button>
                                    </div>
                                </form>

            
                            </div>
                            <div class="mt-4 mt-md-3 text-center">

                            <h5 class="mb-0" style="color:#FF0000">ADVERTENCIA</h5>
<p > "Toda persona que sin la debida autorización o
excediendo la que hubiere obtenido, acceda, intercepte, interfiera o use un sistema
que utilice tecnologías de información, será penado con prisión de uno a cinco años
y multa.."<br>
 <STRONG> (Artículo 6 Ley Especial Contra Los Delitos Informáticos,30 de octubre de 2001 Gaceta Oficial Nº 37.313) </STRONG> </p>



                                <p class="mb-0">©<script>
                                        document.write(new Date().getFullYear())
                                    </script>&nbsp;<?php echo NOMBREAPP ?>&nbsp;Desarrollado por:&nbsp;<i class="mdi mdi-heart text-danger"></i><?php echo " " . DERECHOS ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- end auth full page content -->
            </div>
            <!-- end col -->
            <div class="col-xxl-9 col-lg-8 col-md-7">
                <div class="auth-bg pt-md-5 p-4 d-flex">
                    <div class="bg-overlay bg-primary"></div>
                    <ul class="bg-bubbles">
                        <li></li>
                        <li></li>
                        <li></li>
                        <li></li>
                        <li></li>
                        <li></li>
                        <li></li>
                        <li></li>
                        <li></li>
                        <li></li>
                    </ul>
                    <!-- end bubble effect -->
                    <div class="row justify-content-center align-items-center">
                        <div class="col-xl-7">
                            <div class="p-0 p-sm-4 px-xl-0">
                                <?php include("carousel.php") ?>
                                <!-- end review carousel -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end col -->
        </div>
        <!-- end row -->
    </div>
    <!-- end container fluid -->
</div>
<!-- password addon init -->
</body>
</html>

<?php include 'layouts/vendor-scripts.php'; ?>

<script src="assets/js/pages/pass-addon.init.js"></script>
<script src="assets/js/funciones.js"></script>



