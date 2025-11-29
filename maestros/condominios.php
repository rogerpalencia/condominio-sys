<?php 
@session_start();
# Revisado jodocha, 16/07/2024

include 'layouts/session.php';
include 'layouts/head-main.php'; 

require_once("core/funciones.php") ; 
$func= new Funciones();
require_once("layouts/vars.php") ; 
$userid = $_SESSION['userid'] ;
?>

<head>

    <title>Declaracion de Aduanas | <?php echo NOMBREAPP ?></title>
    <?php include 'layouts/head.php'; ?>

    <!-- DataTables -->
    <link href="assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/libs/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css" rel="stylesheet" type="text/css" />

    <!-- Responsive datatable examples -->
    <link href="assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css" rel="stylesheet" type="text/css" />

    <?php include 'layouts/head-style.php'; ?>

</head>

<?php include 'layouts/body.php'; ?>

<input type="hidden" name="parserJsn" id="parserJsn" value="<?php echo PARSERJSN ?> ">
<input type="hidden" name="mensaje" id="mensaje" value="<?php echo $mensaje ?> ">

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
                            <!--h4 class="mb-sm-0 font-size-18">DataTables</h4-->

                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="javascript: void(0);">Declaraciones</a></li>
                                    <li class="breadcrumb-item active">Aduana</li>
                                </ol>
                            </div>

                        </div>
                    </div>
                </div>
                <!-- end page title -->
                
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <!--h4 class="card-title">Buttons example</h4-->
                                <p class="card-title-desc"><button type="button" class="btn btn-success waves-effect waves-light w-sm" onclick="ndeclaracionaduana()" title="Agregar Item">Nueva Declaración</button></p>
                            </div>
                            <div class="card-body">
                                <table id="mytable" class="table table-nowrap align-middle table-edits">
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th>No.DUA/Fecha</th>
                                            <th>Origen</th>                                            
                                            <th style="width:6px;">Monto DUA</th>
                                            <th style="width:6px;">Monto Pagar</th>
                                            <th style="width:6px;">Monto Pte.</th>
                                            <th>Estatus</th>
                                            <th style="width:2px;"></th>
                                            <th style="width:2px;"></th>
                                            <th style="width:2px;"></th>
                                            <th style="width:2px;"></th>
                                            <th style="width:2px;"></th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                        <!-- end cardaa -->
                    </div> <!-- end col -->
                </div> <!-- end row -->
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

<!-- Required datatable js -->
<script src="assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>
<!-- Buttons examples -->
<script src="assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js"></script>
<script src="assets/libs/datatables.net-buttons-bs4/js/buttons.bootstrap4.min.js"></script>
<script src="assets/libs/jszip/jszip.min.js"></script>
<script src="assets/libs/pdfmake/build/pdfmake.min.js"></script>
<script src="assets/libs/pdfmake/build/vfs_fonts.js"></script>
<script src="assets/libs/datatables.net-buttons/js/buttons.html5.min.js"></script>
<script src="assets/libs/datatables.net-buttons/js/buttons.print.min.js"></script>
<script src="assets/libs/datatables.net-buttons/js/buttons.colVis.min.js"></script>

<!-- Responsive examples -->
<script src="assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
<script src="assets/libs/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js"></script>

<!-- Datatable init js -->
<script src="assets/js/pages/datatables.init.js"></script>

<script src="assets/js/app.js"></script>
<script src="assets/js/funciones.js"></script>

</body>

</html>


<script type="text/javascript">
    $(document).ready(function() {
      $('#mytable').DataTable( {
        "order": [ 0, 'desc' ],
          "columnDefs": [
              {
                "targets": [0],
                "visible": false,
                "searchable": false
              },
              {
                "targets": [3],
                data:'id_declaracion',
                "render": 
                    function ( data, type, row, meta )
                    {
                        if (parseInt(row.rev_aduana_estatus)==1) {
                            return row.monto_dua + '<br><a href= "javascript:notas('+row.id_declaracion+')"><span class="badge bg-danger"title="Su Declaración tiene Objeciones">Objetado</span></a>';
                        } else if (parseInt(row.rev_aduana_estatus)==2)  {
                            return row.monto_dua + '<br><a href= "javascript:notas('+row.id_declaracion+')"><span class="badge bg-success"title="Corregido">Corregido</span></a>';
                        } else {                           
                            return row.monto_dua ;
                        }
                    },
                "searchable": true
              },
              {
                "targets": [6],
                data:'id_declaracion',
                "render": 
                    function ( data, type, row, meta )
                    {
                        if (parseInt(row.rev_pagos_estatus)==1) {
                            return '<span class="badge bg-warning"title="">Comprbante Rechazado</span>';
                        } else {                           
                            return row.estatus ;
                        }
                    },
                "searchable": true
              },
              {
                "targets": [7],
                data:'id_declaracion',
                "render": 
                    function ( data, type, row, meta )
                    {
                        if (parseInt(row.id_estatus) !== 0) {
                            return '<button type="button" onclick="planilladua('+row.id_declaracion+')" class="btn btn-info btn-sm waves-effect waves-light" title="Ver DUA"><i class=" fas fa-ship  font-size-8"></i></button>';
                        } else { 
                            return '<button type="button" disabled class="btn btn-info btn-sm waves-effect waves-light" title="Ver Planilla DUA"><i class="far fa-file-image  font-size-8"></i></button>';
                        }

                    },
                "searchable": false
              },
              {
                "targets": [8],
                data:'id_declaracion',
                "render": 
                    function ( data, type, row, meta )
                    {
                        if (parseInt(row.id_estatus) !== 0) {
                            return '<button type="button" onclick="pagar('+row.id_declaracion+')" class="btn btn-success btn-sm waves-effect waves-light" title="Pagar Item"><i class="far fa-money-bill-alt  font-size-8"></i></button>';
                        } else { 
                            return '<button type="button" disabled class="btn btn-success btn-sm waves-effect waves-light" title="Pagar Item"><i class="far fa-money-bill-alt  font-size-8"></i></button>';
                        }
                    },
                "searchable": false
              },
              {
                "targets": [9],
                data:'id_declaracion',
                "render": 
                    function ( data, type, row, meta )
                    {
                        if ((parseInt(row.id_estatus)===1)||(parseInt(row.rev_aduana_estatus)===1)){
                            return '<button type="button" onclick="edit('+row.id_declaracion+')" class="btn btn-primary btn-sm waves-effect waves-light" title="Editar Item"><i class="fas fa-edit font-size-8"></i></button>';
                        } else { 
                            return '<button type="button" disabled class="btn btn-primary btn-sm waves-effect waves-light" title="Editar Item"><i class="fas fa-edit font-size-8"></i></button>';
                        }
                    },
                "searchable": false
              },
              {
                "targets": [10],
                data:'numero_dua',
                "render": 
                    function ( data, type, row, meta )
                    {
                        if (parseInt(row.id_estatus)===1){
                            return '<button type="button" onclick="anular(' + row.id_declaracion + ')" class="btn btn-danger btn-sm waves-effect waves-light" title="Anular Item"><i class="fas fa-edit font-size-8"></i></button>';
                        } else { 
                            return '<button type="button" disabled class="btn btn-danger btn-sm waves-effect waves-light" title="Anular Item"><i class="fas fa-edit font-size-8"></i></button>';
                        }
                    },
                "searchable": false
              }, 
              {
                "targets": [11],
                data:'id_declaracion',
                "render": 
                    function ( data, type, row, meta )
                    {
                        if (parseInt(row.id_estatus) !== 0) {
                            if ((parseInt(row.id_estatus) == 5) || (parseInt(row.id_estatus) == 6) ) {
                            return '<button type="button" onclick="verpdf('+row.id_declaracion+')" class="btn btn-info btn-sm waves-effect waves-light" title="Imprimir Planilla 914QR"><i class="far fa-file-pdf font-size-8"></i></button>';
                            } else{
                                return '<button type="button" onclick="verpdf2('+row.id_declaracion+')" class="btn btn-info btn-sm waves-effect waves-light" title="Imprimir Cálculo"><i class="far fa-file-pdf font-size-8"></i></button>';
                            }                
                        } else {                            
                            return '<button type="button" disabled class="btn btn-info btn-sm waves-effect waves-light" title="Imprimir PDF"><i class="far fa-file-pdf font-size-8"></i></button>';
                        }
                    },
                "searchable": false
              }
          ],
          "language": 
            {
              "url": "Spanish.json"
            },
            'processing': true,
            'serverSide': true,
            'serverMethod': 'post',
            'ajax': {
                'url':'aduana_data.php'
            },
            'columns': [
                { data: 'id_declaracion'},
                { data: 'numero_dua', 
                    render: function ( data, type, row ) {
                        return row.numero_dua + '<br>(' + row.fecha_declaracion + ')';
                    }                    
                },
                { data: 'origen' },
                { data: 'monto_dua' },
                { data: 'monto_a_pagar' },
                { data: 'monto_pendiente' },
                { data: 'estatus' }
            ],
      });
  });

    function notas(id){
       let params = `scrollbars=no,resizable=no,status=no,location=no,toolbar=no,menubar=no,width=600,height=800,left=100,top=100`;
        var parserJsn= $("#parserJsn").val() ;
        $.ajax({
            type: "post",
            datatype:'json',
            url: "aduana_mod_dua_nota.php",
            data: {id:id},
            success: function(data) {
                if (parserJsn==1)
                    data= JSON.parse(data);
                if (data.estatus==1){
                    var newWin = open('','Notas de Revision de la DUA',params);
                    var nota = data.correccion ;
                    newWin.document.write(nota); 
                } else { 
                    alerta("No hay datos") ;
                }
            }
        })
    }
    
    function ndeclaracionaduana(){
        window.location.href="aduana_ac.php" ;
    }

    function pagar(id){
        window.location.href="aduana_pa.php?id=" + id ;
    }


    function edit(id){
        confirmar('Editar Declaracion ? ', "", "aduana_ac.php?id=" + id) ;
    }

    function anular(id_declaracion){
        var bool = false ;
        var tabla = $('#mytable').DataTable() ;
        var parserJsn= $("#parserJsn").val() ;
        Swal.fire({
          title: 'Anular Item ? ',
          text: 'Observacion: ',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#3085d6',
          cancelButtonColor: '#d33',
          confirmButtonText: 'Aceptar'
        }).then((result) => {
          if (result.isConfirmed) {
            Swal.fire({
                background: 'transparent',
                html: '<img src="./assets/images/loading.svg">',
                allowOutsideClick: false,
                showConfirmButton: false,
            });
            $.ajax({
                url: "aduana_rem.php",
                type: "POST",
                data: {id_declaracion:id_declaracion},
                datatype:'json',
                success: function(data) {
                    if (parserJsn==1)
                        data= JSON.parse(data);
                    if (data.estatus==1) {
                        tips(data.respuesta);
                        tabla.ajax.reload();                            
                    } else {
                        alerta(data.respuesta);
                    }
                },
                error: function(data) {
                    alerta(data.respuesta)
                }
            })
          }
        })
    }


    function planilladua(id){
       let params = `scrollbars=no,resizable=no,status=no,location=no,toolbar=no,menubar=no,width=600,height=800,left=100,top=100`;        
        var parserJsn= $("#parserJsn").val() ;
        $.ajax({
            type: "post",
            datatype:'json',
            url: "aduana_mod_dua.php",
            data: {id:id},
            success: function(data) {
                if (parserJsn==1)
                    data= JSON.parse(data);
                if (data.estatus==1){
                    open(data.ruta_arch, "Planilla DUA", params);
                } else { 
                    alerta("No hay datos") ;
                }
            }
        })
    }

    function verpdf(id){
        // METER EN FUNCIONES.JS
        var parserJsn= $("#parserJsn").val() ;
        $.ajax({
            type: "post",
            datatype:'json',
            url: "aduana_mod_reg.php",
            data: {id:id},
            success: function(data) {
                if (parserJsn==1)
                    data= JSON.parse(data);
                if (data.estatus==1){
                    var a = document.createElement('a');
                    a.href='aduana_pdf_planilla.php?id_declaracion=' + data.idc;
                    a.target = '_blank';
                    document.body.appendChild(a);
                    a.click();        
                } else { 
                    alerta("No hay datos") ;
                }
            }
        })
    }






    function verpdf2(id){
        // METER EN FUNCIONES.JS
        var parserJsn= $("#parserJsn").val() ;
        $.ajax({
            type: "post",
            datatype:'json',
            url: "aduana_mod_reg.php",
            data: {id:id},
            success: function(data) {
                if (parserJsn==1)
                    data= JSON.parse(data);
                if (data.estatus==1){
                    var a = document.createElement('a');
                    a.href='aduana_pdf_planilla2.php?id_declaracion=' + data.idc;
                    a.target = '_blank';
                    document.body.appendChild(a);
                    a.click();        
                } else { 
                    alerta("No hay datos") ;
                }
            }
        })
    }
</script>





