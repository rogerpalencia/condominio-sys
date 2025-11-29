<?php
require_once("layouts/mainfile.php")  ;
$model= new Mainfile();
$conn= $model->get_connection();
$msg=null ;
?>
<?php include 'layouts/head-main.php'; ?>

<head>

    <title>Recuperar Su Clave | <?php echo NOMBREAPP ?></title>
    <?php include 'layouts/head.php'; ?>
    <?php include 'layouts/head-style.php'; ?>

</head>

<?php include 'layouts/body.php'; ?>
<div class="auth-page">
    <div class="container-fluid p-0">
        <div class="row g-0">
            <div class="col-xxl-3 col-lg-4 col-md-5">
                <div class="auth-full-page-content d-flex p-sm-5 p-4">
                    <div class="w-100">
                        <div class="d-flex flex-column h-100">
                            <div class="mb-4 mb-md-5 text-center">
                                <a href="index.php" class="d-block auth-logo">
                                    <img src="assets/images/logomedium.png" alt=""> <span class="logo-txt"></span>
                                </a>
                            </div>
                            <div class="auth-content my-auto">
                                <div class="text-center">
                                    <h5 class="mb-0">Recuperar Su Clave</h5>
                                    <p class="text-muted mt-2"></p>
                                </div>
                                <?php if ($msg) { ?>
                                    <div class="alert alert-success text-center mb-4 mt-4 pt-2" role="alert">
                                        <?php echo $msg; ?>
                                    </div>
                                <?php } ?>

                                <form class="custom-form mt-4">
                                    <div class="mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="text" class="form-control" id="email" placeholder="Enter email">
                                        <span class="text-danger"></span>
                                    </div>
                                    <div class="mb-3 mt-4">
                                        <button class="btn btn-primary w-100 waves-effect waves-light" type='button' onclick="dorecovery()">Recuperar</button>
                                    </div>
                                </form>

                                <div class="mt-5 text-center">
                                    <p class="text-muted mb-0"><a href="auth-login.php" class="text-primary fw-semibold">Acceder</a> </p>
                                </div>

                            </div>
                            <div class="mt-4 mt-md-5 text-center">
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


<!-- JAVASCRIPT -->
<?php include 'layouts/vendor-scripts.php'; ?>
</body>
</html>

<script src="assets/js/funciones.js"></script>

