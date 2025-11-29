<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>

<head>

    <title>Semat-PC</title>
    <?php include 'layouts/head.php'; ?>
    <?php include 'layouts/head-style.php'; ?>
<style>

.row {
    display: flex;
}
.row [class*="col-"] {
    display: table-cell;
    /* Propiedades de estilo, no afectan al funcionamiento */
    
}
</style>

</head>

<?php include 'layouts/body.php'; 



$ok=      "<i class='bx bx-message-square-check widget-box-1-icon text-success'></i>";
$no=      "<i class='bx bx-message-square-x widget-box-1-icon text-danger'></i>";
$proceso= "<i class='bx bx-message-square-dots widget-box-1-icon text-warning'></i>";

$paso1=$ok; 
$paso2=$no; 
$paso3=$no; 
$paso4=$ok; 
$paso5=$proceso; 
$paso6=$proceso; 
?>

<!-- Begin page -->
<div id="layout-wrapper">

    <?php include 'layouts/menu.php'; ?>

    <!-- ============================================================== -->
    <!-- Start right Content here -->
    <!-- ============================================================== -->
    <div class="main-content">

        <div class="page-content">
            <div class="container-fluid">

                                <!-- start page title -->
                                <div class="row">
                    <div class="col-3 ">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <!--h4 class="mb-sm-0 font-size-18">DataTables</h4-->
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="javascript: void(0);">Empresas</a></li>
                                    <li class="breadcrumb-item active">Actividad Económica</li>
                                </ol>
                            </div>
                        </div>
                    </div>

                <div class="row">
                    <div class="col-lg-12 box ">
                        <div class="card" >
                            <div class="card-body">
                                <div class="row justify-content-center mt-3">
                                    <div class="col-xl-8 col-lg-8">
                                        <div class="text-center">
                                            <h5>Ahora es muy Fácil ser un Contribuyente Habilitado en el Municipio
                                                Puerto Cabello</h5>
                                            <p class="text-muted">Estimado Contribuyente, sólo con mantener en verde los siguientes recuadros Ud.
                                                estará listo para declarar y pagar sus tributos. Recuerde que cada proceso será validado en el lapso perentorio por 
                                                el funcionario competente en el SEMAT-PC</p>
                                                &nbsp; <i class='bx bx-message-square-check  text-success font-size-20'>Aprobado</i>&nbsp;&nbsp;&nbsp;&nbsp;
                                                <i class='bx bx-message-square-x  text-danger font-size-20'> Requerido</i>&nbsp;&nbsp;&nbsp;&nbsp;
                                                <i class='bx bx-message-square-dots  text-warning font-size-20'>En Revisión</i>&nbsp;
                                               
                                            <div>

                                            </div>


                                        </div>
                                    </div>
                                    <!-- end col -->
                                </div>
                                <!-- end row -->



                                
                                <div class="row mt-3 " >
                                    <div class="col-xl-4 col-sm-6 well">
                                        <div class="card "style="height: 230px">
                                            <div class="card-body overflow-hidden position-relative ">
                                                <div>
                                                    <i
                                                     <?php echo $paso1  ?>
                                                </div>
                                                <div class="faq-count">
                                                    <h5 class="text-primary">PASO 1.</h5>
                                                </div>
                                                <h5 class="mt-3">Formulario de Datos de la Empresa</h5>
                                                <p class="text-muted mt-3 mb-0">el contribuyente deberá completar todos los datos solicitados en el formulario.
                                                </p>

                                                <div class="row mt-3 ">
                                                    
                                                    <div class="col-xl-6 col-sm-6 col-md-offset-2">
                                                        <button type="button" onclick="verpdf('+row.id_declaracion+')"
                                                            class="btn btn-primary btn-sm waves-effect waves-light"
                                                            title="Imprimir Planilla 914"><i
                                                                class="fab fa-wpforms font-size-8">&nbsp;</i>Datos de la Empresa</button>
                                                    </div>
                                                </div>

                                            </div>
                                            <!-- end card body -->
                                        </div>
                                        <!-- end card -->
                                    </div>
                                    <!-- end col -->
                                    <div class="col-xl-4 col-sm-6">
                                        <div class="card" style="height: 230px">
                                            <div class="card-body overflow-hidden position-relative">
                                                <div>
                                                <?php echo $paso2  ?>
                                                </div>
                                                <div class="faq-count">
                                                    <h5 class="text-primary">PASO 2.</h5>
                                                </div>
                                                <h5 class="mt-3">Imagen del RIF</h5>
                                                <p class="text-muted mt-3 mb-0">Subir la imagen nítida del registro de Información Fiscal RIF vigente, emitido por el SENIAT</p>



                                        <div class="row mt-3 ">
                                                    
                                                    <div class="col-xl-6 col-sm-6 col-md-offset-2">
                                                        <button type="button" onclick="verpdf('+row.id_declaracion+')"
                                                            class="btn btn-primary btn-sm waves-effect waves-light"
                                                            title="Imprimir Planilla 914"><i
                                                                class="fab fa-resolving font-size-8">&nbsp;</i>Subir RIF</button>
                                                    </div>
                                                </div>

                                            </div>
                                            <!-- end card body -->
                                        </div>
                                        <!-- end card -->
                                    </div>
                                    <!-- end col -->

                                    <div class="col-xl-4 col-sm-6">
                                        <div class="card" style="height: 230px">
                                            <div class="card-body overflow-hidden position-relative">
                                                <div>
                                                <?php echo $paso3  ?>
                                                </div>
                                                <div class="faq-count">
                                                    <h5 class="text-primary">PASO 3.</h5>
                                                </div>
                                                <h5 class="mt-3">Registro Mercantil Actualizado</h5>
                                                <p class="text-muted mt-3 mb-0">Un archivo PDF con el registro mercantil vigente y sus últimas modificaciones</p>
                                           
                                                <div class="row mt-3 ">
                                                    
                                                    <div class="col-xl-6 col-sm-6 col-md-offset-2">
                                                        <button type="button" onclick="verpdf('+row.id_declaracion+')"
                                                            class="btn btn-primary btn-sm waves-effect waves-light"
                                                            title="Imprimir Planilla 914"><i
                                                                class="fas fa-folder-open font-size-8">&nbsp;</i>Registro Mercantil</button>
                                                    </div>
                                                </div>
                                           
                                            </div>
                                            <!-- end card body -->
                                        </div>
                                        <!-- end card -->
                                    </div>
                                    <!-- end col -->

                                    <div class="col-xl-4 col-sm-6">
                                        <div class="card" style="height: 230px">
                                            <div class="card-body overflow-hidden position-relative">
                                                <div>
                                                <?php echo $paso4 ?>
                                                </div>
                                                <div class="faq-count">
                                                    <h5 class="text-primary">PASO 4.</h5>
                                                </div>
                                                <h5 class="mt-3">Registro de los Accionistas
                                                </h5>
                                                <p class="text-muted mt-3 mb-0">El contribuyente debe registrar  a cada uno de los accionistas de la empresa.</p>

                                                <div class="row mt-3 ">
                                                    
                                                    <div class="col-xl-6 col-sm-6 col-md-offset-2">
                                                        <button type="button" onclick="verpdf('+row.id_declaracion+')"
                                                            class="btn btn-primary btn-sm waves-effect waves-light"
                                                            title="Imprimir Planilla 914"><i
                                                                class="fas fa-id-badge font-size-8">&nbsp;</i>Registro de Accionistas</button>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- end card body -->
                                        </div>
                                        <!-- end card -->
                                    </div>
                                    <!-- end col -->

                                    <div class="col-xl-4 col-sm-6">
                                        <div class="card" style="height: 230px">
                                            <div class="card-body overflow-hidden position-relative">
                                                <div>
                                                <?php echo $paso5  ?>
                                                </div>
                                                <div class="faq-count">
                                                    <h5 class="text-primary">PASO 5.</h5>
                                                </div>
                                                <h5 class="mt-3">Códigos de Actividad Económica </h5>
                                                <p class="text-muted mt-3 mb-0">Declarar cada una de las actividades económicas que la empresa ejerce en el municipio</p>

                                                    <div class="row mt-3 ">
                                                    
                                                    <div class="col-xl-6 col-sm-6 col-md-offset-2">
                                                        <button type="button" onclick="verpdf('+row.id_declaracion+')"
                                                            class="btn btn-primary btn-sm waves-effect waves-light"
                                                            title="Imprimir Planilla 914"><i
                                                                class="fas fa-cogs font-size-8">&nbsp;</i>Declarar Actividades</button>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- end card body -->
                                        </div>
                                        <!-- end card -->
                                    </div>
                                    <!-- end col -->

                                    <div class="col-xl-4 col-sm-6">
                                        <div class="card" style="height: 230px">
                                            <div class="card-body overflow-hidden position-relative">
                                                <div>
                                                <?php echo $paso6  ?>
                                                </div>
                                                <div class="faq-count">
                                                    <h5 class="text-primary">PASO 6.</h5>
                                                </div>
                                                <h5 class="mt-3">Solicitud de Licencia</h5>
                                                <p class="text-muted mt-3 mb-0">Solicitud formal de la patente de Industria y/o Comercio ante el SEMAT-PC</p>

                                                    <div class="row mt-3 ">
                                                    
                                                    <div class="col-xl-6 col-sm-6 col-md-offset-2">
                                                        <button type="button" onclick="verpdf('+row.id_declaracion+')"
                                                            class="btn btn-primary btn-sm waves-effect waves-light"
                                                            title="Imprimir Planilla 914"><i
                                                                class="fas fa-industry font-size-8">&nbsp;</i>Solicitar Patente</button>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- end card body -->
                                        </div>
                                        <!-- end card -->
                                    </div>
                                    <!-- end col -->
                                </div>
                                <!-- end row -->
                            </div>
                            <!-- end  card body -->
                        </div>
                        <!-- end card -->
                    </div>
                    <!-- end col -->
                </div>
                <!-- end row -->

            </div> <!-- container-fluid -->
        </div>
        <!-- End Page-content -->

        <?php include 'layouts/footer.php'; ?>

    </div>
    <!-- end main content-->

</div>
<!-- END layout-wrapper -->


<!-- Right Sidebar -->
<?php include 'layouts/right-sidebar.php'; ?>
<!-- /Right-bar -->

<!-- JAVASCRIPT -->

<?php include 'layouts/vendor-scripts.php'; ?>

<script src="assets/js/app.js"></script>
<script>
$(document).ready(function() {
    var heights = $(".well").map(function() {
        return $(this).height();
    }).get(),
 
    maxHeight = Math.max.apply(null, heights);
 
    $(".well").height(maxHeight);
});


</script>



</body>

</html>