<?php
$userid = $_SESSION['userid'];

include "../core/config.php";
include "../core/funciones.php";

$conn = pg_connect("host=$Servidor dbname=$BaseDeDatos user=$Usuario password=$Password");
if (!$conn) {
    die("Conexion Fallida");
    exit();
}
$mes_actual = date('m')-1;
//$mes_actual = $_POST['mesSeleccionado'];



$sql = "SELECT
id_estatus, 
count(*),
sum(monto_a_pagar)
FROM
declaraciones_aduana
WHERE
EXTRACT(MONTH FROM declaraciones_aduana.fecha_declaracion) = $mes_actual   and id_estatus>0
GROUP BY
declaraciones_aduana.id_estatus
ORDER BY
declaraciones_aduana.id_estatus";


$result = pg_query($conn, $sql);
if (!$result) {
  echo "Ocurrió un error con la BD.\n";
  exit;
}

while ($row = pg_fetch_assoc($result)) {




    if ($row["id_estatus"] == 1) {
        $decla_cant = $row["count"];
        $decla_sum =  $row["sum"];
    };

    if ($row["id_estatus"] == 2) {
        $por_rev_pag_adu_cant = $row["count"];
        $por_rev_pag_adu_sum =  $row["sum"];
    };


    if ($row["id_estatus"] == 3) {
        $por_pago_cant = $row["count"];
        $por_pago_sum =  $row["sum"];
    };

    if ($row["id_estatus"] == 4) {
        $por_adu_cant = $row["count"];
        $por_adu_sum =  $row["sum"];
    };


    if ($row["id_estatus"] == 5) {
        $por_sello_cant = $row["count"];
        $por_sello_sum =  $row["sum"];
    };

    if ($row["id_estatus"] == 6) {
        $selladas_cant = $row["count"];
        $selladas_sum =  $row["sum"];
    };
};



?>


<?PHP


$cantidades_aduana_1 = "[" . " 
   $decla_cant,
   $por_rev_pag_adu_cant,
   $por_pago_cant,
   $por_adu_cant,
   $por_sello_cant" . "]";

$montos_aduana_1 = "[" . " 
  $decla_sum,
   $por_rev_pag_adu_sum,
   $por_pago_sum,
   $por_adu_sum,
   $por_sello_sum" . "]";

?>


<script>
    // Function to get the month name from the month number
    function getMonthName(monthNumber) {
        const months = [
            "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio",
            "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"
        ];
        return months[monthNumber - 1];
    }

    chart1.updateSeries([{
        data: <?php echo $cantidades_aduana_1; ?>,
        labels: ['Declaradas', 'Pagos y Aduana', 'Pagos', 'Aduana', 'Taquilla'],
        animate: true
    }]);

    var mes_actual = $("#mes_actual").val();
    var monthName = getMonthName(parseInt(mes_actual));

    chart1.updateOptions({
        title: { text: 'Declaraciones Aduanales de ' + monthName+ ' en cola '},
    });
</script>






<div class="card-body">
    <div class="table-responsive">
        <table class="table table-sm m-0">
        <input type="hidden" name="mes_actual" id="mes_actual" value="<?php echo $mes_actual ?> ">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Estatus</th>
                    <th>Declaraciones</th>
                    <th>Monto en Bs.</th>
                </tr>
            </thead>
            <tbody>


                <tr>
                    <th scope="row">1</th>
                    <td>Declaradas</td>
                    <td><?php echo (number_format($decla_cant, 0, ',', '.')); ?> </td>
                    <td><?php echo (number_format($decla_sum, 2, ',', '.')); ?> </td>
                </tr>
                <tr>
                    <th scope="row">2</th>
                    <td>Por Pagos y Aduana</td>
                    <td><?php echo (number_format($por_rev_pag_adu_cant, 0, ',', '.')); ?> </td>
                    <td><?php echo (number_format($por_rev_pag_adu_sum, 2, ',', '.')); ?> </td>
                </tr>
                <tr>
                    <th scope="row">3</th>
                    <td>Por Pagos</td>
                    <td><?php echo (number_format($por_pago_cant, 0, ',', '.')); ?> </td>
                    <td><?php echo (number_format($por_pago_sum, 2, ',', '.')); ?></td>
                </tr>
                <tr>
                    <th scope="row">4</th>
                    <td>Por Aduana</td>
                    <td><?php echo (number_format($por_adu_cant, 0, ',', '.')); ?> </td>
                    <td><?php echo (number_format($por_adu_sum, 2, ',', '.')); ?></td>
                </tr>

                <tr>
                    <th scope="row">5</th>
                    <td>Por Sellar</td>
                    <td><?php echo (number_format($por_sello_cant, 0, ',', '.')); ?> </td>
                    <td><?php echo (number_format($por_sello_sum, 2, ',', '.')); ?></td>
                </tr>
                <tr>
                    <th scope="row">6</th>
                    <td>Selladas</td>
                    <td><?php echo (number_format($selladas_cant, 0, ',', '.')); ?> </td>
                    <td><?php echo (number_format($selladas_sum, 2, ',', '.')); ?> </td>
                </tr>


            </tbody>
        </table>

    </div>
</div>