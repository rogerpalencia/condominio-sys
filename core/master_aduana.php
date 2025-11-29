<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; 

require_once("core/funciones_sys.php") ; 
$func= new Funciones();
require_once("layouts/vars.php") ; 
$userid = $_SESSION['userid'] ;
require_once("core/PDO.class.php") ; 
$conn=  DB::getInstance();


// ACTUALIZAR PENDIENTE DE DUAS EN CCURSO
// SOLO DUAS HASTA PENDIENTES POR SELLAR 
$monto_pendiente=0;
foreach($conn->query("SELECT id_declaracion,monto_a_pagar FROM declaraciones_aduana WHERE id_contribuyente= '$userid' AND (id_estatus=1 OR id_estatus=2 OR id_estatus=3)") as $row)
{
    $id_declaracion=$row['id_declaracion'] ;
    $monto_pendiente =$row['monto_a_pagar'] ;

    $sql="SELECT sum(monto) AS pagado FROM pagos WHERE id_contribuyente= '$userid' AND id_declaracion='$id_declaracion' AND id_estatus=2" ;
    $stmt= $conn->prepare($sql) ;
    $stmt->execute();
    $row= $stmt->fetch();
    $monto_pendiente=$monto_pendiente - $row['pagado'] ;

    $sql= "SELECT sum(credito_fiscal)*-1 AS pagado FROM credito_contribuyente WHERE id_declaracion='$id_declaracion' AND id_contribuyente= '$userid' AND substr(codigo,1,2)= 'AD' AND operacion= 'Egreso'";
    $stmt= $conn->prepare($sql) ;
    $stmt->execute();
    $row=$stmt->fetch();
    if ($row['pagado'] > 0) 
        $monto_pendiente= $monto_pendiente - $row['pagado'] ;

    if ($monto_pendiente < 0)
        $monto_pendiente= 0 ;

    $sql="UPDATE declaraciones_aduana SET monto_pendiente= '$monto_pendiente' WHERE id_contribuyente= '$userid' AND id_declaracion='$id_declaracion'" ;
    $stmt= $conn->prepare($sql) ;
    $stmt->execute();
    
} 

$sql="SELECT sum(credito_fiscal) AS credito_fiscal FROM credito_contribuyente WHERE id_contribuyente= '$userid' AND substr(codigo,1,2)= 'AD' GROUP by rif" ;
$stmt= $conn->prepare($sql) ;
$stmt->execute();
$row= $stmt->fetch();
$credito_fiscal=$row['credito_fiscal'] ?? 0 ;
if ($credito_fiscal > 0) 
{
    $sql="UPDATE declaraciones_aduana SET usarcredito_fiscal= 's' WHERE id_contribuyente= '$userid' AND monto_pendiente > 0" ;
    $stmt= $conn->prepare($sql) ;
    $stmt->execute();
} else {
    $sql="UPDATE declaraciones_aduana SET usarcredito_fiscal= 'n' WHERE id_contribuyente= '$userid' AND monto_pendiente > 0" ;
    $stmt= $conn->prepare($sql) ;
    $stmt->execute();

}
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

<style>
  .bolden{font-family:"Arial Black"}
</style>

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
                    <div class="col-3">
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
                    <div class="col-4">
                  
                    </div>
                    <div class="col-4 ">

                    <div class="btn-group">
                                 

                                    <p class="card-title-desc"><button type="button" class="btn btn-primary waves-effect waves-light w-sm" 
                                        onclick="ver_selladas()" title="Agregar Item">Selladas y Nulas</button></p>
                                      &nbsp;&nbsp;&nbsp;&nbsp;
                                        <p class="card-title-desc"><button type="button" class="btn btn-success waves-effect waves-light w-sm" 
                                        onclick="ndeclaracionaduana()" title="Agregar Item">Nueva Declaración</button></p>
                                    



                        </div>


                    </div>








                </div>
                <!-- end page title -->
                
                <div class="row">
                    <div class="col-12">
                        <div class="card">

                            <div class="card-header">
                                <!--h4 class="card-title">Buttons example</h4-->
                                <h1 class="display-6 mb-0">Master de Aduanas</h1>
                            </div>

                            <div class="card-body">
                                <table id="mytable" class="table table-nowrap align-middle table-edits">
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th>DUA/Planilla/Fecha</th>
                                            <th>Origen</th>                                            
                                            <th style="width:6px;">Monto DUA</th>
                                            <th style="width:6px;">Tributos</th>
                                            <th style="width:6px;">Monto Pte.</th>
                                            <th></th>
                                            <th style="width:2px;"></th>
                                            <th style="width:2px;"></th>
                                            <th style="width:2px;"></th>
                                            <th style="width:2px;"></th>
                                            <th style="width:2px;"></th>
                                            <th style="width:2px;">fecha</th>
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
        //dom: "QBfrtip",
        
          "columnDefs": [
              {
                "targets": [0],
                "visible": false,
                "searchable": false
              },
              {
                "targets": [5],
                "visible": false,
                "searchable": false
              },
              {
                "targets": [3],
                "visible": false,
                "searchable": false
              },

            
              
              {
                "targets": [4],
                data:'id_declaracion', 
                 "visible": false,
                "render": 
                    function ( data, type, row, meta )
                    {
 
                            return ''
                       
                    },
                "searchable": false
              },



              {
                "targets": [2],
                data:'id_declaracion',
                "render": 
                    function ( data, type, row, meta )
                    {

                          
                     return'<b><small>Aduanero:&nbsp</b> '+stringTruncate(row.razon_social, 35)+'</small><br>'+
                            '<b><small>Domicilio:&nbsp</b> '+stringTruncate(row.domicilio_fiscal, 35) +'</small><br>'+
                            '<b><small>Rif:&nbsp</b> '+stringTruncate(row.rif, 20) +'</small><br>'+
                            '<b><small>Origen:&nbsp</b> '+stringTruncate(row.origen, 20) +'</small><br>'+
                            '<small><b>Para:</b> '+ stringTruncate(row.consignatario, 35) +'</small> <br>' +
                            '<b><small>Monto DUA:&nbsp</small></b> '+row.monto_dua+'<br>'+
                            '<b><small>Total:&nbsp</small></b> '+row.monto_a_pagar +'<br>'+
                            '<b><small>Saldo:&nbsp</small></b> '+row.monto_pendiente;



                    },
                "searchable": false
              }, 







              {
                "targets": [6],
                data:'id_declaracion',
                "render": 
                    function ( data, type, row, meta )
                    {







              return       '<div class="alert alert-primary alert-dismissible fade show px-0 mb-0 pb-0 text-center" role="alert">'+
                           ' <h6 class="mt-0 mb-0 text-primary style="text-transform:uppercase">'+row.estatus+'</h6>'+
                           ' <p> '+'<span class="col-11 badge bg-primary"title="" >   Conciliado : '+ row.monto_conciliado + '</span>'+
                           '<br><span class="col-11 badge bg-primary "title="">            En Revisión: '+ row.monto_en_revision + '</span>'+
                           '<br><span class="col-11    badge bg-primary"title="" >           Rechazado  : '+ row.monto_rechazado + '</span><br>'+
                           '&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;'
                     
                            
                        





            
                    },
                "searchable": false
              },



              {
                "targets": [7],
                data:'id_declaracion',
                "render": 
                    function ( data, type, row, meta )
                    {


const inicio =    '<div class="container">'+ '<div class="row mb-3">'+ '<div class="col"> '

const verdua =              '<button type="button" onclick="planilladua('+row.id_declaracion+')" class="btn btn-primary btn-sm waves-effect waves-light"'+
                            ' title="Ver DUA"><i class=" fas fa-ship  font-size-8"></i>&nbsp Ver DUA</button>';

const pagar  =              '&nbsp;<button type="button" onclick="pagar('+row.id_declaracion+')" class="btn btn-primary btn-sm waves-effect waves-light" '+
                            'title="Pagar Item"><i class="far fa-money-bill-alt  font-size-8"></i>&nbsp; Pagar&nbsp;&nbsp;&nbsp;</button>'

const editar  =              '&nbsp;<button type="button" onclick="edit('+row.id_declaracion+')" class="btn btn-primary btn-sm waves-effect waves-light" '+
                            'title="Editar Item"><i class="fas fa-edit font-size-8"></i>&nbsp; Editar&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;</button><br>'


const segundalinea  =       '</div>'+ '</div>'+ '<div class="row">'+ '<div class="col">'

const anular  =             '&nbsp;<button type="button" onclick="anular(' +row.id_declaracion+','+ row.id_contribuyente +','+ row.monto_conciliado+')"class="btn btn-primary btn-sm waves-effect waves-light" '+
                             'title="Anular Item"><i class="fas fa-times font-size-8"></i>&nbsp;Anular &nbsp; &nbsp; &nbsp;</button>'


const verpdf2  =           '&nbsp;<button type="button" onclick="verpdf2('+row.id_declaracion+')" class="btn btn-primary btn-sm waves-effect waves-light" '+
                            'title="Imprimir Cálculo"><i class="far fa-file-pdf font-size-8">&nbsp;</i>Imprimir</button>'

const verpdf  =              '&nbsp;<button type="button" onclick="verpdf('+row.id_declaracion+')" class="btn btn-primary btn-sm waves-effect waves-light" '+
                            'title="Imprimir Planilla 914"><i class="far fa-file-pdf font-size-8">&nbsp;</i>Imprimir</button>'

const fin =                 '</div>'+ '</div>'+ '</div> <br><small>Al anular una declaración el monto pagado se abona <br> como crédito fiscal a favor del contribuyente</small>'


    const vercaptura1  =        '&nbsp;<button type="button" onclick="vercaptura('+row.id_declaracion+')" class="btn btn-primary btn-sm waves-effect waves-light" '+
                            'title="Editar Item"><i class="fas fa-edit font-size-8"></i>&nbsp; Ver Pagos </button><br>'

return inicio+verdua+pagar+editar+segundalinea+verpdf2+anular+vercaptura1+fin 



                    },
                "searchable": false
              },


              {
                "targets": [8],
                data:'id_declaracion',
                "render": 
                    function ( data, type, row, meta )
                    {
                                                   return '';
                 
                    },
                "searchable": false
              },
              {
                "targets": [9],
                data:'id_declaracion',
                "render": 
                    function ( data, type, row, meta )
                    {
                        return '';
                    },
                "searchable": false
              },
              {
                "targets": [10],
                data:'numero_dua',
                "render": 
                    function ( data, type, row, meta )
                    {
                        return '';
                    },
                "searchable": false
              }, 
              {
                "targets": [11],
                data:'id_declaracion',
                "render": 
                    function ( data, type, row, meta )
                    {
                        return '';
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
                'url':'master_aduana_data.php'
            },

            'columns': [
                { data: 'id_declaracion'},
                { data: 'numero_dua', 
                    render: function ( data, type, row ) {
                        return ('<small><B>  DUA&nbsp;&nbsp;&nbsp; :&nbsp; </B> </small>'+ row.numero_dua + '<br>'+ 
                                '<small><B>  Nro&nbsp;&nbsp;&nbsp; :&nbsp; </B> </small>'+row.id_declaracion + '<br>'+  
                                '<small><B>  Fecha:&nbsp; </B> </small>'+row.fecha_declaracion );
                    }                    
                },

                { data: 'origen' },
                { data: 'monto_dua' },
                { data: 'monto_a_pagar' },
             
                { data: 'estatus' },
                { data: 'fecha_declaracion' },
            ],
      });
  });

    function notas(id){
       let params = `scrollbars=no,resizable=no,status=no,location=no,toolbar=no,menubar=no,width=600,height=800,left=100,top=100`;
        var parserJsn= $("#parserJsn").val() ;
        $.ajax({
            type: "post",
            datatype:'json',
            url: "master_aduana_mod_dua_nota.php",
            data: {id:id},
            success: function(data) {
                if (parserJsn==1)
                    data= JSON.parse(data);
                if (data.estatus==1){
                    //var newWin = open('','Notas de Revision de la DUA',params);
                    var nota = data.correccion ;
                   // newWin.document.write(nota); 


        Swal.fire({
        title: 'OBJECIONES',
        text: nota,
        imageUrl: 'assets/images/LOGO_SEMATPC_300.fw.png',
        imageHeight: 48,
        confirmButtonColor: "#5156be",
        animation: true
          })










                } else { 
                    alerta("No hay datos") ;
                }
            }
        })
    }
    
    function ndeclaracionaduana(){
        window.location.href="master_aduana_ac.php" ;
    }
    function ver_selladas(){
        window.location.href="master_aduana_selladas.php" ;
    }
    function pagar(id){
        window.location.href="master_aduana_pa.php?id=" + id ;
    }


    function edit(id){
        confirmar('Editar Declaracion ? ', "", "master_aduana_ac.php?id=" + id) ;
    }

    function anular(id_declaracion,id_contribuyente,credito_fiscal){
        var bool = false ;
        var tabla = $('#mytable').DataTable() ;
        var parserJsn= $("#parserJsn").val() ;
        
        Swal.fire({
          title: 'ANULAR DECLARACIÓN ',
          text: '¿Ud. está seguro de anular esta declaración?',
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
                url: "master_aduana_rem.php",
                type: "POST",
                data: {id_declaracion:id_declaracion,
                    id_contribuyente:id_contribuyente,
                    credito_fiscal:credito_fiscal
               
                },
                datatype:'json',
                success: function(data) {
                    if (parserJsn==1)
                        data= JSON.parse(data);
                    if (data.estatus==1) {
                        //tips(data.respuesta);
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


    function aplicarcredito(id_declaracion){
        var bool = false ;
        var tabla = $('#mytable').DataTable() ;
        var parserJsn= $("#parserJsn").val() ;
        Swal.fire({
          title: 'Aplicar Credito Fiscal a esta Planilla ? ',
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
                url: "aduana_credito_mod.php",
                type: "POST",
                data: {id_declaracion:id_declaracion},
                datatype:'json',
                success: function(data) {
                    if (parserJsn==1)
                        data= JSON.parse(data);
                    if (data.estatus==1) {
                        tips(data.respuesta);
                        tabla.ajax.reload();
                        $.post("aduana_act_credito.php",  function(data){
                            $("#credito").html(data);
                        });

                    } else {
                        alerta(data.respuesta);
                        tabla.ajax.reload();    
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
            url: "master_mod_dua.php",
            data: {id:id},
            success: function(data) {
                if (parserJsn==1)
                    data= JSON.parse(data);
                if (data.estatus==1){
                   // open('../sys/'+ data.ruta_arch, "Planilla DUA", params);

                    Swal.fire({
  imageUrl: '../sys/'+ data.ruta_arch,
  
  imageAlt: 'DUA'
})





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

  //  $(document).ready(function(){
  //      $.post("aduana_act_credito.php",  function(data){
  //          console.log(data) 
  //          $("#credito").html(data);
  //      });         
  //  });
//

function vercaptura(id){
        let params = `scrollbars=no,resizable=no,status=no,location=no,toolbar=no,menubar=no,width=600,height=800,left=100,top=100`;        
        let url = "revisionpagostaq_mod_referencias.php?id=" + id ;
        open(url, "Información del Pago", params);
        alerta(data.respuesta);
    }





var stringTruncate = function(str, length){
    if ((str!=null)&& (length>0)){ 
  var dots = str.length > length ? '...' : '';
  return str.substring(0, length)+dots;}
  else{ return '' }
};


</script>





