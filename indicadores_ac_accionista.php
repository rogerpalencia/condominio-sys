<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; 

$userid= $_SESSION['userid'] ;
require_once("core/PDO.class.php") ; 
$conn=  DB::getInstance();
require_once("core/funciones.php") ; 
$func= new Funciones();
require_once("layouts/vars.php") ; 
  $id_empresa=intval($_GET['id_em']) ;
  
  $id=intval($_GET['id']) ;
if ( (isset($_GET['id'])) && (intval($_GET['id'])!==0) ) 
{    
  
    $sql = "select * from accionistas where id_accionista= '$id'" ;
    $stmt= $conn->prepare($sql) ;
    $stmt->execute();
    $row= $stmt->fetch();

    $nombre= $row['nombre'] ?? null ;
    $cedula= $row['numero'] ?? null ;
    $correo= $row['correo'] ?? null ;
    $porcentaje= $row['porcentaje'] ?? null ;
    $tipo_doc= $row['tipodoc'] ?? null ;
}  



if ($tipo_doc=="V")
$chk_tipodoc_v="checked" ;
if ($tipo_doc=="E")
$chk_tipodoc_e="checked" ;
if ($tipo_doc=="P")
$chk_tipodoc_p="checked" ;


?>


<head>
    <title>Registro de Empresas | <?php echo NOMBREAPP ?></title>
    <?php include 'layouts/head.php'; ?>
    <!-- twitter-bootstrap-wizard css -->
    <link rel="stylesheet" href="assets/libs/twitter-bootstrap-wizard/prettify.css">
    <link href="assets/libs/dropzone/min/dropzone.min.css" rel="stylesheet" type="text/css" />
    <?php include 'layouts/head-style.php'; ?>
</head>

<?php include 'layouts/body.php'; ?>
<!-- Begin page -->
<input type="hidden" name="parserJsn" id="parserJsn" value="<?php echo PARSERJSN ?>">
<input type="hidden" name="id_empresa" id="id_empresa" value="<?php echo $id_empresa ?>">
<input type="hidden" name="mensaje" id="mensaje" value="<?php echo $mensaje ?>">

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
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0 font-size-18">Accionistas</h4>

                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="javascript: void(0);">Contribuciones</a></li>
                                    <li class="breadcrumb-item active">Empresas</li>
                                    <li class="breadcrumb-item active">Accionistas</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- end page title -->

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <!--div class="card-header">
                                <h4 class="card-title mb-0">Wizard with Progressbar</h4>
                            </div-->
                            <div class="card-body">
                                <?php if ((trim($correccion)!=='')||($correccion!==null)){ ?>
                                    <div class="alert alert-warning" role="alert">
                                        <h6><?php echo $correccion ?></h6>
                                    </div>                                
                                <?php } ?>

                                <div id="progrss-wizard" class="twitter-bs-wizard">
                                    <ul class="twitter-bs-wizard-nav nav nav-pills nav-justified">
                                        <li class="nav-item">
                                            <a href="#progress-seller-details" class="nav-link" data-toggle="tab">
                                                <div class="step-icon" data-bs-toggle="tooltip" data-bs-placement="top" title="Seller Details">
                                                    <i class="bx bx-list-ul"></i>
                                                </div>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="#progress-company-document" class="nav-link" data-toggle="tab">
                                                <div class="step-icon" data-bs-toggle="tooltip" data-bs-placement="top" title="Company Document">
                                                    <i class="bx bx-book-bookmark"></i>
                                                </div>
                                            </a>
                                        </li>
                                    </ul>
                                    <!-- wizard-nav -->                                                                     

                                    <div id="bar" class="progress mt-4">
                                        <div class="progress-bar bg-success progress-bar-striped progress-bar-animated"></div>
                                    </div>
                                    <div class="tab-content twitter-bs-wizard-tab-content">

                                        <div class="tab-pane" id="progress-seller-details">
                                            <div class="text-center mb-4">
                                                <h5>Datos del Accionista</h5>
                                                <!--p class="card-title-desc"></p-->
                                                <?php echo $ver ; ?>
                                            </div>
                                            <form>
                                                <div class="row">
                                                    <div class="col-xl-4 col-md-4">
                                                        <div class="form-group mb-4">
                                                            <label>Nombres:</label>
                                                            <input type="text" id='nombres' name='nombres' onkeypress="return /[ a-zA-Z0-9!@#\$%\^\&*\)\(+=._-]/i.test(event.key)" style="text-transform: uppercase" required data-pristine-required-message="Indique el Nombre"  class="form-control" value="<?php echo $nombre ?>" />
                                                        </div>
                                                    </div>

                                                    <div class="col-xl-2 col-md-1">
                                                        <label class="form-label">Documento</label>
                                                        <div>
                                                            <div class="form-check mb-2">
                                                                <label class="form-check-label" for="tipo_doc1">
                                                                    V<input class="form-check-input" type="radio" name="tipo_doc" id="tipo_doc1" value="V" <?php echo $chk_tipodoc_v ?>>  
                                                                </label>

                                                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                                <label class="form-check-label" for="tipo_doc2">
                                                                    E<input class="form-check-input" type="radio" name="tipo_doc" id="tipo_doc2" value="E"<?php echo $chk_tipodoc_e ?>>
                                                                </label>

                                                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                                <label class="form-check-label" for="tipo_doc3">
                                                                    P<input class="form-check-input" type="radio" name="tipo_doc" id="tipo_doc3" value="P"<?php echo $chk_tipodoc_p ?>>
                                                                </label>

                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div id="divcedula" class="col-xl-2 col-md-2">
                                                        <div class="form-group mb-2">
                                                            <label>Cédula: </label>
                                                            <input type="number" id="cedula" name="cedula"  onkeypress="return /[0-9]/i.test(event.key)" required data-pristine-required-message="Indique su Nro de Cedula" class="form-control" value= "<?php echo $cedula ?>"/>
                                                        </div>
                                                    </div>





                                                    <div class="col-xl-2 col-md-2">
                                                        <div class="form-group mb-2">
                                                            <label>Correo:</label>
                                                            <input type="text" id='correo' name='correo'class="form-control" value="<?php echo $correo ?>" />
                                                        </div>
                                                    </div>

                                                    <div class="col-xl-4 col-md-4">
                                                        <div class="form-group mb-4">
                                                            <label class="form-label" for="porcentaje">Porcentaje:</label>
                                                            <input type="text" id='porcentaje' name='porcentaje' class="form-control" onkeypress="return /[ a-zA-Z0-9!@#\$%\^\&*\)\(+=._-]/i.test(event.key)" value="<?php echo $porcentaje ?>" />
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                            <ul class="pager wizard twitter-bs-wizard-pager-link">
                                                <a href="javascript: void(0);" class="btn btn-success" onclick="regresar()"><i class="bx bx-chevron-left me-1"></i>Regresar</a>
                                                <li class="next"><a href="javascript: void(0);" class="btn btn-primary" onclick="nextTab()">Siguiente <i class="bx bx-chevron-right ms-1"></i></a></li>
                                            </ul>
                                        </div>
                                        
                                        <div class="tab-pane" id="progress-company-document">
                                            <div>
                                                <div class="text-center mb-4">
                                                    <p class="card-title-desc">Complete toda la información a continuación:</p>
                                                    <ul>
                                                        <li>Cedula</li>
                                                        <li>RID</li>
                                                    </ul>
                                                </div>

                                                <form action="#" class="dropzone" id="frmupload">
                                                    <div class="fallback">
                                                        <input name="file" type="file" multiple="multiple">
                                                    </div>
                                                    <div class="dz-message needsclick">
                                                        <div class="mb-2">
                                                            <i class="display-4 text-muted bx bx-cloud-upload"></i>
                                                        </div>
                                                        <h5>Arrastre y suelte aqui los Archivos de Documentación en formato JPG o PDF</h5>
                                                    </div>
                                                </form>
                                                <ul class="pager wizard twitter-bs-wizard-pager-link">
                                                    <li class="previous"><a href="javascript: void(0);" class="btn btn-primary" onclick="nextTab()"><i class="bx bx-chevron-left me-1"></i> Anterior</a></li>
                                                    <li class="next"><a href="javascript: giniciar()" class="btn btn-success w-md" onclick="nextTab()">Enviar Datos</a></li>
                                                    <button type="button" id="btnimg" value="btnAux" style="display: none;"></button>
                                                </ul>
                                            </div>                                            
                                        </div>
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

<!-- twitter-bootstrap-wizard js -->
<script src="assets/libs/twitter-bootstrap-wizard/jquery.bootstrap.wizard.min.js"></script>
<script src="assets/libs/twitter-bootstrap-wizard/prettify.js"></script>
<!-- form wizard init -->
<script src="assets/js/pages/form-wizard.init.js"></script>
<script src="assets/libs/dropzone/min/dropzone.min.js"></script>
<script src="assets/js/funciones.js"></script>
<script src="assets/js/app.js"></script>
<script src="assets/js/jquery.mask.min.js"></script> 

</body>
</html>

<script>

    function giniciar(){
        var parserJsn = $("#parserJsn").val(); 
        var id_empresa= $("#id_empresa").val();
        var id_accionista= $("#id_accionista").val();

        var nombre= $("#nombres").val();
        var correo= $("#correo").val();
        var porcentaje= $("#porcentaje").val();
        var cedula=  $('#cedula').val();
        var tipodoc=  $('#tipo_doc').val();
        if (document.getElementById("tipo_doc1").checked == true)   {var tipodoc= 'V'};
        if (document.getElementById("tipo_doc2").checked = true)    {var tipodoc= 'E'};
        if(document.getElementById("tipo_doc3").checked = true)     {var tipodoc= 'P'};
    


        var reg = true ;
        if(!(nombres)){alerta("Indique Nombres");reg=false;return;}
        if(!(porcentaje)){alerta("Indique Porcentaje");reg=false;return;}
        if(!(correo)){alerta("Indique Correo");reg=false;return;}
        if(!(cedula)){alerta("Indique el número de cédula");reg=false;return;}

        if (reg){
            Swal.fire
            ({
                background: 'transparent',
                html: '<img src="assets/images/loading.svg">',
                allowOutsideClick: false,
                showConfirmButton: false,
            })
            $.ajax({
                url: "empresas_accionistas_mod.php",
                type: "POST",
                data: {id_empresa:id_empresa,
                           nombre:nombre,
                       porcentaje:porcentaje,
                           correo:correo,
                           cedula:cedula,
                          tipodoc:tipodoc,
                    },
                datatype:'json',
                success: function(data){
                    if (parserJsn==1)
                        data= JSON.parse(data);
                    if (data.estatus==1){
                        let btnimg = document.getElementById('btnimg');
                        btnimg.click();
                        delay(3000).then(() => { 
                            tips(data.respuesta) ;
                            regresar();
                        });
                                                alerta(data.respuesta+ ' '+ 'ac_acc');

                    } else {
                        alerta(data.respuesta+ ' '+ 'ac_acc2');
                    }
                },
                error: function(data) {
                    alerta(data.respuesta);
                }
            })
        }
    }

    Dropzone.options.frmupload = {
     
        maxFiles: 4,    
        addRemoveLinks: true,
        clickable: true,
        maxFilesize: 8388608, 
        parallelUploads: 10,
        acceptedFiles: `image/*,application/pdf`,
        autoProcessQueue: false,
        url: 'empresas_accionistas_upload.php',
        init: function() 
        {
            
            var myDropzone = this;
            $("#btnimg").click(function (e) {
                e.preventDefault();
                myDropzone.processQueue();
            });
            this.on('sending', function(file, xhr, formData) {
                formData.append('cedula',  $('#cedula').val());      
                formData.append('tipodoc', $('#tipodoc').val());      
                var data = $('#frmupload').serializeArray();
                $.each(data, function(key, el) {
                    formData.append(el.name, el.value);
                });
            });
        }
    };
    
    
    function regresar(){
        window.location="empresas_accionistas.php" ;
    }

    $(document).ready(function(){
        $('input[name="rif"]').mask('J-00000000000');
    });


</script>