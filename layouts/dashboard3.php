<?php
require_once "./core/config.php";


$conn = pg_connect("host=$Servidor dbname=$BaseDeDatos user=$Usuario password=$Password");
if (!$conn) {
    die("Conexion Fallida");
    exit();
}




$query = "SELECT
	( SELECT COUNT ( DISTINCT id_contribuyente ) FROM empresas ) AS total_empresas,
	( SELECT COUNT ( DISTINCT id_contribuyente ) FROM empresas_decl_definitiva ) AS total_empresas_activas,
	( SELECT COUNT ( DISTINCT id_contribuyente ) FROM empresas WHERE ( activa = 's' OR activa = 'S' ) AND actividad_economica = 159 ) AS total_aduaneros,
	( SELECT COUNT ( DISTINCT id_contribuyente ) FROM declaraciones_aduana WHERE id_estatus <> 0 ) AS aduaneros_activos
    ";


$result = pg_query($query);
$row = pg_fetch_row($result);

$total_empresas = $row[0];
$total_empresas_activas = $row[1];
$total_aduaneros = $row[2];
$aduaneros_activos = $row[3];




$query = "
SELECT
    total_vehiculos,
    vehiculos_solventes,
    ROUND((vehiculos_solventes::NUMERIC / total_vehiculos * 100), 2) AS porcentaje_solventes
FROM (
    SELECT
        (SELECT COUNT(DISTINCT id_vehiculo)
         FROM vehiculos) AS total_vehiculos,
        (SELECT COUNT(DISTINCT id_declaracion_vehiculo)
         FROM declaraciones_vehiculos
         WHERE hasta = DATE_TRUNC('year', CURRENT_DATE) + INTERVAL '1 year' - INTERVAL '1 day' and monto_pendiente=0) AS vehiculos_solventes
    ) AS conteos;
 ";

$result = pg_query($query);
$row = pg_fetch_row($result);

$total_vehiculos        = $row['total_vehiculos'];
$total_solventes        = $row['vehiculos_solventes'];
$porcentaje_solventes   = $row['porcentaje_solventes'];








"SELECT count(*) c  FROM  declaraciones_aduana WHERE (rev_aduana_estatus='1') and  
EXTRACT(MONTH FROM declaraciones_aduana.fecha_declaracion) = EXTRACT(MONTH FROM now() ) ";

$cant_decla_objetada = $row['c'];


$query = "SELECT count(*) c  FROM  declaraciones_aduana WHERE (rev_aduana_estatus='2') and  
EXTRACT(MONTH FROM declaraciones_aduana.fecha_declaracion) = EXTRACT(MONTH FROM now() ) ";
$result = pg_query($query);
$row = pg_fetch_assoc($result);
$cant_decla_corregida = $row['c'];



// Obtener el primer día del mes en curso
$fecha_i = date('Y-m-01'); // Devuelve el primer día del mes actual en formato 'YYYY-MM-01'

// Obtener el último día del mes en curso
$fecha_f = date('Y-m-t');


$sql = "
SELECT
    fecha_pago,
    SUM(CASE WHEN tipo_pago = 'CREDITO' THEN monto_pagado ELSE 0 END) AS total_pagos_credito,
    COUNT(CASE WHEN tipo_pago = 'CREDITO' THEN 1 END) AS cant_declaraciones_credito,
    SUM(CASE WHEN tipo_pago = 'BANCO' THEN monto_pagado ELSE 0 END) AS total_pagos_bancos,
    COUNT(CASE WHEN tipo_pago = 'BANCO' THEN 1 END) AS cant_declaraciones_bancos
FROM
    (
        SELECT
            TO_CHAR(PAG.fecha_pago, 'DD-MM-YYYY') AS fecha_pago,
            ROUND(PAG.monto, 2) AS monto_pagado,
            'BANCO' AS tipo_pago
        FROM
            pagos AS PAG
            INNER JOIN empresas_decl_definitiva AS EST ON PAG.id_declaracion_definitiva = EST.id_declaracion_def
            INNER JOIN empresas AS EMP ON EST.id_contribuyente = EMP.id_contribuyente
        WHERE
            EST.id_estatus > 3 
            AND PAG.fecha_pago BETWEEN '$fecha_i  00:00:00.00' AND '$fecha_f  23:59:59.99'
            AND PAG.id_declaracion_definitiva > 0 
            AND PAG.id_estatus = 2

        UNION ALL

        SELECT
            TO_CHAR(PAG.fecha_creado, 'DD-MM-YYYY') AS fecha_pago,
            (ROUND(PAG.credito_fiscal, 2) * -1) AS monto_pagado,
            'CREDITO' AS tipo_pago
        FROM
            credito_empresas AS PAG
            INNER JOIN empresas_decl_definitiva AS def ON PAG.id_declaracion = def.id_declaracion_def
            INNER JOIN empresas AS EMP ON def.id_contribuyente = EMP.id_contribuyente
        WHERE
            def.id_declaracion_def IN (SELECT id_declaracion FROM credito_empresas WHERE operacion LIKE '%Egreso%') 
            AND PAG.fecha_creado BETWEEN '$fecha_i  00:00:00.00' AND '$fecha_f  23:59:59.99'
    ) AS pagos_consolidados
GROUP BY
    fecha_pago
ORDER BY
    fecha_pago;
";

// Ejecutar la consulta
$resultado = pg_query($conn, $sql);

// Verificar si hubo errores en la consulta
if (!$resultado) {
    echo "Error en la consulta: " . pg_last_error($conexion);
    exit();
}

// Crear un array para almacenar los datos
$data = array();
$serieA = array();
$serieB = array();
// Obtener los datos y almacenarlos en el array
while ($fila = pg_fetch_assoc($resultado)) {
    $serieA[] = $fila['cant_declaraciones_credito'];
    $serieB[] = $fila['cant_declaraciones_bancos'];

    $serieC[] = $fila['total_pagos_credito'];
    $serieD[] = $fila['total_pagos_bancos'];
    
}



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
                        <div class="col-12">



                            <div class="row">
                                <div class="col-10 d-flex align-items-left">
                                    <span class="text-muted mb-3 lh-1 d-block">Empresas Registradas</span>
                                </div>
                                <div class="col-2 d-flex justify-content-end align-items-right">
                                    <i class="fas fa-industry text-muted" style="font-size: 40px;"></i>
                                </div>
                            </div>

                            <h4 class="mb-1">
                                <span class="counter-value"
                                    data-target="<?php echo number_format($total_empresas, 0, ",", "."); ?>">0</span>
                            </h4>
                        </div>


                    </div>
                    <div class="text-nowrap">
                        <span class="badge bg-soft-success text-success">
                            <?php echo number_format($total_empresas_activas, 0, ",", "."); ?>
                        </span>
                        <span class="ms-1 text-muted font-size-13">Empresas Activas </span>
                        <span class="badge bg-soft-success text-success">
                            <?php echo number_format((($total_empresas_activas / $total_empresas) * 100), 0, ",", "."); ?>
                            %
                        </span>
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
                        <div class="col-12">
                            <div class="row">
                                <div class="col-10 d-flex align-items-left">
                                    <span class="text-muted mb-3 lh-1 d-block">Aduaneras Registradas</span>
                                </div>
                                <div class="col-2 d-flex justify-content-end align-items-right">
                                    <i class="fas fa-ship text-muted" style="font-size: 40px;"></i>
                                </div>
                            </div>


                            <h4 class="mb-1">
                                <span class="counter-value"
                                    data-target="<?php echo number_format($total_aduaneros, 0, ",", "."); ?>">0</span>
                            </h4>
                        </div>


                    </div>
                    <div class="text-nowrap">
                        <span class="badge bg-soft-success text-success">
                            <?php echo number_format($aduaneros_activos, 0, ",", "."); ?>
                        </span>
                        <span class="ms-1 text-muted font-size-13">Empresas Activas </span>
                        <span class="badge bg-soft-success text-success">
                            <?php echo number_format((($aduaneros_activos / $total_aduaneros) * 100), 0, ",", "."); ?>
                            %
                        </span>
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
                        <div class="col-12">



                            <div class="row">
                                <div class="col-10 d-flex align-items-left">
                                    <span class="text-muted mb-3 lh-1 d-block">Inmuebles</span>
                                </div>
                                <div class="col-2 d-flex justify-content-end align-items-right">
                                    <i class="far fa-building text-muted" style="font-size: 40px;"></i>
                                </div>
                            </div>

                            <h4 class="mb-1">
                                <span class="counter-value"
                                    data-target="<?php echo number_format($total_empresas, 0, ",", "."); ?>">0</span>
                            </h4>
                        </div>


                    </div>
                    <div class="text-nowrap">
                        <span class="badge bg-soft-success text-success">
                            <?php echo number_format($total_empresas_activas, 0, ",", "."); ?>
                        </span>
                        <span class="ms-1 text-muted font-size-13">Inmuebles Solventes</span>
                        <span class="badge bg-soft-success text-success">
                            <?php echo number_format((($total_empresas_activas / $total_empresas) * 100), 0, ",", "."); ?>
                            %
                        </span>
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
                        <div class="col-12">


                        <div class="col-xl-3 col-md-6">
            <!-- card -->
            <div class="card card-h-100">
                <!-- card body -->
                <div class="card-body">

                    <div class="row align-items-center">
                        <div class="col-12">
   


                            <div class="row">
                                <div class="col-10 d-flex align-items-left">
                                    <span class="text-muted mb-3 lh-1 d-block">Vehículos</span>
                                </div>
                                <div class="col-2 d-flex justify-content-end align-items-right">
                                    <i class="fas fa-car text-muted" style="font-size: 40px;"></i>
                                </div>
                            </div>

                            <h4 class="mb-1">
                                <span class="counter-value"
                                    data-target="<?php echo number_format($total_vehiculos    , 0, ",", "."); ?>">0</span>
                            </h4>
                        </div>


                    </div>
                    <div class="text-nowrap">
                        <span class="badge bg-soft-success text-success">
                            <?php echo number_format($total_solventes  , 0, ",", "."); ?>
                        </span>
                        <span class="ms-1 text-muted font-size-13">Vehículos Solventes </span>
                        <span class="badge bg-soft-success text-success">
                            <?php echo number_format($porcentaje_solventes  , 0, ",", "."); ?>
                            %
                        </span>
                    </div>
                </div><!-- end card body -->
            </div><!-- end card -->
        </div><!-- end col -->


















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

                                <button type="button" class="btn btn-soft-secondary btn-sm witget_aduana_anterior">
                                    Mes Anterior
                                </button>
                                <button type="button" class="btn btn-soft-secondary btn-sm witget_aduana1_dia">
                                    Hoy
                                </button>
                                <button type="button" id="aduanav" class="btn btn-soft-primary btn-sm witget_aduana1">
                                    Mes en curso
                                </button>
                                <script>
                                    document.addEventListener('DOMContentLoaded', function () {
                                        var myButton = document.getElementById('myButton');

                                        setTimeout(function () {
                                            aduanav.click();
                                            console.log('disparo');
                                        }, 200); // 2000 milisegundos = 2 segundos
                                    });
                                </script>


                            </div>
                        </div>
                    </div>
                    <div class="row mb-1">
                        <div class="col-xl-6">
                            <div id='aduana1' class='apex-charts'></div>
                        </div>

                        <div class="col-xl-6">
                            <div class="col-sm " id="witget_aduana1">

                               
                            </div>
                        </div>
                    </div>
                </div>
            </div><!-- end card body -->
        </div><!-- end card -->
    </div><!-- end col -->
</div><!-- end row-->








<div class="row">
    <div class="col-xl-12">
        <div class="card card-h-100">
            <!-- card body -->
            <div class="card-body">
                <!-- card -->

                <div class="card-body">

                    <div class="row mb-1">
                    <div class="col-xl-12">
  <div id='consolidado' class='apex-charts'></div>
  <?php // include('./witgets/witget_consolidado.php'); ?>

  <script>
    function generarGrafico(seriesA, seriesB, titulo,seriey,seriex) {}
  
    document.addEventListener("DOMContentLoaded", function () {
     var serieA = <?php echo json_encode($serieA); ?>;
     var serieB = <?php echo json_encode($serieB); ?>;

      generarGrafico(serieA, serieB, 'Comportamiento de las Delcaraciones Definitivas por Día Vs. Origen de los Fondos', 'Declaraciones','Días del Mes en Curso');
    });
  </script>
</div>

                    </div>
                </div><!-- end card body -->
            </div><!-- end card -->
        </div><!-- end col -->
    </div><!-- end row-->














    





    <div class="row">
        <div class="col-xl-6">











                        </div>

                    </div>
                </div>





            </div>
            <!-- end card -->
        </div>

    </div>

</div>