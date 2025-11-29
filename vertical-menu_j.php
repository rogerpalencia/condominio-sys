<?//php session_start(); ?>

<?php
require_once 'core/PDO.class.php';
$conn = DB::getInstance();

$userid = $_SESSION['userid'];
$perfil = $_SESSION['perfil'];
$userid= 2 ;
$perfil= 2 ;

?>

<ul>
<li>

                    <?php

$sql = "SELECT * FROM public.vista_perfil_detalles_perfiles_programa_modulo 
WHERE id_perfil = $perfil ";
$rs = $conn->query($sql);
echo $sql ;



$cond = '';
foreach ($rs as $row) {
    if ($cond != $row['id_modulo']) {
        $tx = '<i class=" fas '.$row['icono'].'"></i>';
        echo '<a href="javascript: void(0);" class="has-arrow">'.$tx.'
       <span data-key="t-pages">'.$row['modulo'].'</span></a>';
        $cond = $row['id_modulo'];
    }
    echo '<ul class="sub-menu" aria-expanded="false">';
    echo '<li><a href="'.$row['accion'].'" data-key="t-register">'.$row['nombre_programa'].'</a></li>';
    echo '</ul>';
}
?>
</li>
</ul>

