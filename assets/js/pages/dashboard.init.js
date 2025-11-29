









$(document).ready(function() {

    $('.aduana2').on('click', function() {
        //Añadimos la imagen de carga en el contenedor
        $('#content_1').html('<div class="loading"><img src="loader.gif"/><br/>Un momento, por favor...</div>');

        $.ajax({
            type: "POST",
            url: "ajax.php",
            success: function(data) {
                //Cargamos finalmente el contenido deseado
                $('#content_1').fadeIn(1000).html(data);
            }
        });
        return false;
    });



    $('.aduana2_dia').on('click', function() {
        //Añadimos la imagen de carga en el contenedor
        $('#content_1').html('<div class="loading"><img src="loader.gif"/><br/>Un momento, por favor...</div>');

        $.ajax({
            type: "POST",
            url: "./witgets/witget_aduana4.php",
            success: function(data) {
                //Cargamos finalmente el contenido deseado
                $('#content_1').fadeIn(1000).html(data);
            }
        });
        return false;
    });





    $('.witget_aduana1').on('click', function() {
        //Añadimos la imagen de carga en el contenedor
        $('#witget_aduana1').html('<div class="loading"><img src="loader.gif"/><br/>Un momento, por favor...</div>');

        $.ajax({
            type: "POST",
            url: "./witgets/witget_aduana1.php",
            success: function(data) {
                //Cargamos finalmente el contenido deseado
                $('#witget_aduana1').fadeIn(1000).html(data);
            }
        });
        return false;
    });


    $('.witget_aduana_anterior').on('click', function() {
        //Añadimos la imagen de carga en el contenedor
        $('#witget_aduana1').html('<div class="loading"><img src="loader.gif"/><br/>Un momento, por favor...</div>');

        $.ajax({
            type: "POST",
            url: "./witgets/witget_aduana_anterior.php",
            success: function(data) {
                //Cargamos finalmente el contenido deseado
                $('#witget_aduana1').fadeIn(1000).html(data);
            }
        });
        return false;
    });










    $('.witget_aduana1_dia').on('click', function() {
        //Añadimos la imagen de carga en el contenedor
        $('#witget_aduana1').html('<div class="loading"><img src="loader.gif"/><br/>Un momento, por favor...</div>');

        $.ajax({
            type: "POST",
            url: "./witgets/witget_aduana1_dia.php",
            success: function(data) {
                //Cargamos finalmente el contenido deseado
                $('#witget_aduana1').fadeIn(1000).html(data);
            }
        });
        return false;
    });



});





var options1 = {
    series: [{
        name: "",
        data: [],
    }],
    chart: {
        height: 250,
        type: "bar",
        zoom: {
            enabled: false
        },
        toolbar: {
            show: false
        }
    },

    plotOptions: {
      bar: {
        dataLabels: {
          position: 'top', // top, center, bottom
        },
      }
    },
    dataLabels: {
      enabled: true,
      formatter: function (val) {
        return val + "%";
      },
      offsetY: -20,
      style: {
        fontSize: '12px',
        colors: ["#304758"]
      }
    },



    markers: {
        show: true,
        size: 6
    },
    dataLabels: {
        enabled: false
    },
    legend: {
        show: true,
        showForSingleSeries: true,
        position: "top",
        horizontalAlign: "right"
    },
    stroke: {
        curve: "smooth",
        linecap: "round"
    },
    grid: {
        row: {
            colors: ["#f3f3f3"], // takes an array which will be repeated on columns
            opacity: 0.5
        }
    },
    xaxis: {
        categories: ['Declaradas', 'Pagos y Aduana', 'Pagos', 'Aduana', 'Taquilla']
    },
    noData: {
        text: 'Cargando...'
    },
    title: {
        text: "",
        align: 'left',
        margin: 10,
        offsetX: 0,
        offsetY: 0,
        floating: false,
        style: {
            fontSize: '15px',
            fontWeight: 'bold',
            fontFamily: undefined,
            color: '#000000 '
        },
    }

};


    var chart1 = new ApexCharts(document.querySelector("#aduana1"), options1);
    chart1.render();




////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    
function generarGrafico(seriesA, seriesB,titulo,seriey,seriex) {
    var currentDate = new Date();
    var currentMonth = currentDate.getMonth(); // Devuelve el número del mes (0 - enero, 1 - febrero, etc.)
    var currentYear = currentDate.getFullYear(); // Devuelve el año en curso (por ejemplo, 2023)
  
    function daysInMonth(month, year) {
      return new Date(year, month + 1, 0).getDate();
    }
  
    var daysOfMonth = daysInMonth(currentMonth, currentYear);
    //var daysOfMonth = 28;
    // Rellenar los valores de las series con ceros o nulos para los días que faltan.
    for (var i = seriesA.length; i < daysOfMonth; i++) {
      seriesA.push(0);
      seriesB.push(0);
    }
  
    var options = {
      series: [{
        name: 'Aporte por Estimada',
        data: seriesA
      }, {
        name: 'Bancos',
        data: seriesB
      }],
      
      chart: {
        height: 250,
        type: "bar",
        zoom: {
          enabled: false
        },
        toolbar: {
          show: false
        }
      },


      plotOptions: {
        bar: {
          dataLabels: {
            position: 'top', // top, center, bottom
          },
        }
      },


      dataLabels: {
        enabled: false,
        formatter: function (val) {
          return val + "";
        },
        offsetY: -20,
        style: {
          fontSize: '7px',
          colors: ["#304758"]
        }
      },
      

      markers: {
        show: true,
        size: 6
      },
 
      legend: {
        show: true,
        showForSingleSeries: true,
        position: "top",
        horizontalAlign: "right"
      },
      stroke: {
        curve: "smooth",
        linecap: "round"
      },
      grid: {
        row: {
          colors: ["#f3f3f3"],
          opacity: 0.5
        }
      },


      yaxis: {
     //   logarithmic: true,
        
        title: {
            text: seriey, // Texto del título del eje Y
            offsetX: -10, // Ajusta la posición horizontal del título (opcional)
            rotate: -90, // Rota el título del eje Y (opcional)
            style: {
              fontSize: '12px' // Tamaño de fuente del título (opcional)
            }},
      },


      
      xaxis: {
        categories: Array.from({ length: daysOfMonth }, (_, i) => i + 1).map(String),
        
        tickPlacement: 'on',

        
        title: {
          text: seriex,
          offsetX: 50,
          rotate: -90,
          style: {
            fontSize: '12px'
          }
        },
      },
      
      noData: {
        text: 'Cargando...'
      },
      title: {
        text: titulo,
        align: 'left',
        margin: 10,
        offsetX: 0,
        offsetY: 0,
        floating: false,
        style: {
          fontSize: '15px',
          fontWeight: 'bold',
          fontFamily: 'arial',
          color: '#000000 '
        },
      }
    };
  
    var chart = new ApexCharts(document.querySelector("#consolidado"), options);
    chart.render();
  }

  