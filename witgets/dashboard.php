<?php
require_once "./core/config.php";


$conn = pg_connect("host=$Servidor dbname=$BaseDeDatos user=$Usuario password=$Password");
if (!$conn) {
    die("Conexion Fallida");
    exit();
}

$query = "SELECT count(distinct rif) c  FROM  declaraciones_aduana WHERE id_estatus <> 0";
$result = pg_query($query);
$row = pg_fetch_assoc($result);
$cant_empre_activas = $row['c'];

$query = "SELECT count(*) d FROM  empresas WHERE activa='s' or activa='S' and actividad_economica=159";
$result = pg_query($query);
$row = pg_fetch_assoc($result);
$cant_empre_registradas = $row['d'];




$query = "SELECT count(*) c  FROM  declaraciones_aduana WHERE (rev_aduana_estatus='1') and  
EXTRACT(MONTH FROM declaraciones_aduana.fecha_declaracion) = EXTRACT(MONTH FROM now() ) ";
$result = pg_query($query);
$row = pg_fetch_assoc($result);
$cant_decla_objetada = $row['c'];


$query = "SELECT count(*) c  FROM  declaraciones_aduana WHERE (rev_aduana_estatus='2') and  
EXTRACT(MONTH FROM declaraciones_aduana.fecha_declaracion) = EXTRACT(MONTH FROM now() ) ";
$result = pg_query($query);
$row = pg_fetch_assoc($result);
$cant_decla_corregida = $row['c'];






?>


<div class="container-fluid">

    <!-- start page title -->
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <!-- card -->
            <div class="card card-h-100">
                <!-- card body -->
                <div class="card-body">

                    <div class="row align-items-center">
                        <div class="col-9">
                            <span class="text-muted mb-3 lh-1 d-block ">Agentes Aduanales</span>
                            <h4 class="mb-1">
                                <span class="counter-value" data-target="<?php echo number_format($cant_empre_registradas, 0, ",", "."); ?>">0</span>
                            </h4>
                        </div>

                        <div class="col-6">

                        </div>
                    </div>
                    <div class="text-nowrap">
                        <span class="badge bg-soft-success text-success"> <?php echo number_format($cant_empre_activas, 0, ",", "."); ?></span>
                        <span class="ms-1 text-muted font-size-13">Empresas Activas </span>
                        <span class="badge bg-soft-success text-success"> <?php echo number_format((($cant_empre_activas / $cant_empre_registradas) * 100), 0, ",", "."); ?> %</span>
                    </div>
                </div><!-- end card body -->
            </div><!-- end card -->
        </div><!-- end col -->




        <div class="col-xl-3 col-md-6">
            <!-- card -->
            <div class="card card-h-100">
                <!-- card body -->
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-9">
                            <span class="mb-3 lh-1 d-block ">Declaraciones Objetadas</span>
                            <h4 class="mb-1">
                                <span class="counter-value" data-target="<?php echo number_format($cant_decla_objetada, 0, ",", "."); ?>">0</span>
                            </h4>
                        </div>
                        <div class="col-6">

                        </div>
                    </div>
                    <div class="text-nowrap">
                        <span class="badge bg-soft-success text-success"> <?php echo number_format($cant_decla_corregida, 0, ",", "."); ?></span>
                        <span class="ms-1 text-muted font-size-13">Declaraciones Corregidas </span>
                        <span class="badge bg-soft-success text-success"> <?php echo number_format((( $cant_decla_objetada/$cant_decla_corregida) * 100), 0, ",", "."); ?> %</span>

                    </div>
                </div><!-- end card body -->
            </div><!-- end card -->
        </div><!-- end col-->

        <div class="col-xl-3 col-md-6">
            <!-- card -->
            <div class="card card-h-100">
                <!-- card body -->
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-7">
                            <span class="text-muted mb-3 lh-1 d-block text-truncate">Inmuebles</span>
                            <h4 class="mb-1">
                                $<span class="counter-value" data-target=" <?php echo number_format($cant_empre_registradas, 0, ",", "."); ?>    ">0</span>K
                            </h4>
                        </div>
                        <div class="col-6">

                        </div>
                    </div>
                    <div class="text-nowrap">
                        <span class="badge bg-soft-success text-success">+ $2.8k</span>
                        <span class="ms-1 text-muted font-size-13">Última Semana</span>
                    </div>
                </div><!-- end card body -->
            </div><!-- end card -->
        </div><!-- end col -->

        <div class="col-xl-3 col-md-6">
            <!-- card -->
            <div class="card card-h-100">
                <!-- card body -->



                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-7">
                            <span class="text-muted mb-3 lh-1 d-block text-truncate">Vehículos</span>
                            <h4 class="mb-1">
                                <span class="counter-value" data-target="9730">0</span>
                            </h4>
                        </div>
                        <div class="col-6">

                        </div>
                    </div>
                    <div class="text-nowrap">
                        <span class="badge bg-soft-success text-success">+8,23</span>
                        <span class="ms-1 text-muted font-size-13">Última Semana</span>
                    </div>
                </div><!-- end card body -->
            </div><!-- end card -->
        </div><!-- end col -->
    </div><!-- end row-->
    <!-- inicio de cajas grandes-->





    <div class="row">
        <div class="col-xl-12">
            <div class="card card-h-100">
                <!-- card body -->
                <div class="card-body">
                    <!-- card -->

                    <div class="card-body">
                        <div class="d-flex flex-wrap align-items-center mb-0">
                            <h5 class="card-title me-2"></h5>
                            <div class="ms-auto">
                                <div>
                                    <!--     <button type="button" class="btn btn-soft-secondary btn-sm">
                                                        Hoy
                                                    </button>
                                                    <button type="button" class="btn btn-soft-primary btn-sm">
                                                        Semana
                                                    </button>
                                                    <button type="button" class="btn btn-soft-secondary btn-sm">
                                                        Mes
                                                    </button>-->
                                    <button type="button" class="btn btn-soft-secondary btn-sm witget_aduana1_dia">
                                        Hoy
                                    </button>
                                    <button type="button" id=boton_aduana class="btn btn-soft-secondary btn-sm active witget_aduana1">
                                        Mes en curso

                                </div>
                            </div>
                        </div>
                        <div class="row mb-1">
                            <div class="col-xl-6">
                                <div id='aduana1' class='apex-charts'></div>
                            </div>

                            <div class="col-xl-6">
                                <div class="col-sm " id="witget_aduana1">

                                    <?php include('./witgets/witget_aduana1.php'); ?>

                                </div>
                            </div>
                        </div>
                        <script>
                            setTimeout(function() {

                                $("#boton_aduana").click();
                            }, 1200);
                        </script>


                    </div>




















                </div><!-- end card body -->
            </div><!-- end card -->
        </div><!-- end col -->
    </div><!-- end row-->


    <div class="row">
        <div class="col-xl-6">




            <div class="card card-h-100">
                <!-- card body -->

            </div>

        </div>





        <div class="col-xl-6">
            <!-- card -->
            <div class="card card-h-100">
                <!-- card body -->


                <div class="card-body">
                    <div class="d-flex flex-wrap align-items-center mb-1">
                        <h5 class="card-title me-2">Industria y Comercio</h5>
                        <div class="ms-auto">
                            <div>
                                <button type="button" class="btn btn-soft-primary btn-sm">
                                    ALL
                                </button>
                                <button type="button" class="btn btn-soft-secondary btn-sm aduanax">
                                    1 Mes
                                </button>
                                <button type="button" class="btn btn-soft-secondary btn-sm">
                                    6M
                                </button>
                                <button type="button" class="btn btn-soft-secondary btn-sm active">
                                    1Y
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="row align-items-center" id="content_1">
                        <div class="col-xl-8">

                           Cargando...


                        </div>

                    </div>
                </div>





            </div>
            <!-- end card -->
        </div>

    </div>

</div>