<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php';

require_once("core/PDO.class.php");
$conn =  DB::getInstance();
require_once("layouts/vars.php");
$userid = $_SESSION['userid'];


?>

<head>

    <title>Registro de Accionistas | <?php echo NOMBREAPP ?></title>
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
                                    <li class="breadcrumb-item"><a href="javascript: void(0);">Indicadores Económicos</a></li>

                                </ol>
                            </div>

                        </div>
                    </div>
                </div>
                <!-- end page title -->

                <div class="row">
                    <div class="col-12">


                        <div>
                            <div class="mb-4">
                                <button type="button" class="btn btn-primary  active btn-block" onclick="regresar()" title="Regresar">Regresar</button>
                             </div>
                        </div>
                        <div class="card">
                            <div class="row">


                                <div class="col-5">
                                    <table id="mytable" class="table table-nowrap align-middle table-edits">
                                        <thead>
                                            <tr>
                                                <th></th>
                                                <th>Fecha / Hora</th>
                                                <th>TCMMV</th>
                                                <th>Dólar</th>

                                            </tr>
                                        </thead>
                                    </table>
                                </div>




                                <div class="col-7">

                                    <div class="card mt-5 ms-4 me-4">
                                        <div class="row  ">
                                            <h4 class="text-center mt-2"> Actualizar Indicadores</h4>
                                            <h5 class="text-center mt-2">* TCMMV : Tipo de cambio de la moneda de mayor valor publicado por el BCV.</h5>
                                        </div>
                                        <div class="row  ">
                                            <div class="col-8 ">
                                                <form>
                                                    <div class="col-8 ms-3">
                                                        <div class="form-group mb-2">
                                                            <label>TCMMV:</label>
                                                            <input type="number" id='petro' name='petro' class="form-control" />
                                                        </div>
                                                    </div>

                                                    <div class="col-8 ms-3">
                                                        <div class="form-group mb-4">
                                                            <label>Dólar:</label>
                                                            <input type="number" id='dolar' name='dolar' class="form-control" />

                                                        </div>
                                                    </div>

                                                </form>
                                            </div>


                                            <div class="col-4 mt-5 ms-31>
                                    <div class=" row ">
                                    <button type=" button" class="btn btn-primary  active btn-block" onclick="ingresar()" title="Regresar">Ingresar</button>

                                            </div>

                                        </div>



                                        </div>

                                       
                                        <div class="card mt-5 ms-4 me-4">
                                          <div class="row  ">   
                                             
                                                <div id="chart"></div>
                                           
                                        

                                            </div>
                                        </div>


                                </div>
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
<script src="assets/libs/apexcharts/apexcharts.min.js"></script>

<script src="assets/js/app.js"></script>
<script src="assets/js/funciones.js"></script>











<script type="text/javascript">
    function ingresar() {
        var dolar = $("#dolar").val();
        var petro = $("#petro").val();
        var reg = true;

        if (!(dolar)) {
            alerta("Indique la cotización del Dólar");
            reg = false;
            return;
        };
        if (!(petro)) {
            alerta("Indique la cotización del Petro");
            reg = false;
            return;
        };

        if (reg) {
            Swal.fire({
                background: 'transparent',
                html: '<img src="assets/images/loading.svg">',
                allowOutsideClick: false,
                showConfirmButton: false,
            })
            $.ajax({
                url: "indicadores_mod.php",
                type: "POST",
                data: {
                    dolar: dolar,
                    petro: petro
                },
                datatype: 'json',
                success: function(data) {
                    data = JSON.parse(data);
                    $('#mytable').DataTable().draw();

                    $('#dolar').val('');
                    $('#petro').val('');
          

                    $.getJSON('indicadores_gra.php', function(response) {
        chart.updateSeries([{
          name: 'Sales',
          data: response
        }])
      });

                    
                    swal.close();

                },
                error: function(data) {
                    alerta(data.respuesta);

                }
            })
        }
    }




    $(document).ready(function() {
        $('#mytable').DataTable({
            "info": false,

            "columnDefs": [{
                    "targets": [0],
                    "visible": false,
                    "searchable": false,

                },

                {
                    "targets": [1],
                    data: 'id_pagos',
                    "render": function(data, type, row, meta) {
                        return '<p align="left">' + row.fecha + '</p>'
                    },
                    "searchable": false,
                    "width": '50%'
                },
                {
                    "targets": [2],
                    data: 'id_pagos',
                    "render": function(data, type, row, meta) {
                        return '<p align="right">' + row.petro + '</p>'
                    },
                    "searchable": false,
                    "width": '25%'
                },
                {
                    "targets": [3],
                    data: 'id_pagos',
                    "render": function(data, type, row, meta) {
                        return '<p align="right">' + row.dolar + '</p>'
                    },
                    "searchable": false,
                    "width": '25%'
                }



            ],
            "language": {
                "url": "Spanish.json"
            },
            'processing': true,
            'serverSide': true,
            'serverMethod': 'post',
            'bFilter': false, // oculta el buscador
            'bLengthChange': false, // oculta la cantidad de registros por pagina queda en 10
            'ajax': {
                'url': 'indicadores_data.php'
            },
            'columns': [{
                    data: 'id'
                },
                {
                    data: 'fecha'
                },
                {
                    data: 'petro'
                },
                {
                    data: 'dolar'
                }
            ],
        });
    });


    function regresar() {
        // var id = $('#id_empresa').val();
        window.location.href = "index.php";
    };


    var options = {
  series: [
    {
      name: "Petro",
      type: "line",
      data: []
    },
    {
      name: "Dolar",
      type: "line",
      data: []
    }
  ],
  chart: {
    height: 350,
    type: 'line',
  },
  dataLabels: {
    enabled: false
  },
  title: {
    text: 'Cotizaciones del Petro y Dolar',
  },
  noData: {
    text: 'Cargando...'
  },
  xaxis: {
    type: 'category',
    tickPlacement: 'on',
    labels: {
      rotate: -45,
      rotateAlways: true
    }
  },
  yaxis: [
    {
      seriesName: 'Petro',
      title: {
        text: 'Petro',
      },
      labels: {
        style: {
          colors: '#008FFB'
        }
      }
    },
    {
      seriesName: 'Dolar',
      opposite: true,
      title: {
        text: 'Dolar',
      },
      labels: {
        style: {
          colors: '#00E396'
        }
      }
    }
  ],
  colors: ['#008FFB', '#00E396']
};

var chart = new ApexCharts(document.querySelector("#chart"), options);
chart.render();

$.getJSON('indicadores_gra.php', function(response) {
  var dataPetro = response.petro;
  var dataDolar = response.dolar;

  chart.updateSeries([
    {
      name: 'Petro',
      data: dataPetro,
      yaxis: 0
    },
    {
      name: 'Dolar',
      data: dataDolar,
      yaxis: 1
    }
  ]);
});











</script>



</body>

</html>