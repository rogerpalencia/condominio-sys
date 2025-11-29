<?php
sleep(1);
echo 'Hola. Soy el nuevo contenido que viene del servidor!';
?>
<div id="aduana_2"  class="apex-charts"></div>
<script>

    
var options = {
    series: [{
            name: 'EMITIDAS',
            data: [234, 55]
        }, {
            name: 'POR ADUANA',
            data: [13, 23]
        }, {
            name: 'POR PAGOS',
            data: [11, 17]
        }, {
            name: 'POR SELLAR',
            data: [144, 17]
        }, {
            name: 'SELLADAS',
            data: [1312, 17]
        }



    ],
    chart: {
        type: 'bar',
        height: 500,
        stacked: true,
        stackType: '100%'
    },
    responsive: [{
        breakpoint: 480,
        options: {
            legend: {
                position: 'bottom',
                offsetX: -10,
                offsetY: 0
            }
        }
    }],
    xaxis: {
        categories: ['Mes', 'Act. Económica'],
    },
    fill: {
        opacity: 1
    },
    legend: {
        position: 'right',
        offsetX: 0,
        offsetY: 50
    },
};


var chart = new ApexCharts(document.querySelector("#aduana_2"), options);
chart.render();


</script>