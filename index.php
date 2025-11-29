<?php 
@session_start();
# Revisado 12/07/2024

include 'layouts/session.php';
include 'layouts/head-main.php';

require_once "core/PDO.class.php"; 
$conn=  DB::getInstance();
require_once "layouts/vars.php"; 
$userid= $_SESSION['userid'] ;

?>


<!DOCTYPE html>
<html lang="es_VE">
<head>
    <title><?php echo NOMBREAPP ?></title>
    <?php include 'layouts/head.php'; ?>
    <link href="assets/libs/admin-resources/jquery.vectormap/jquery-jvectormap-1.2.2.css" rel="stylesheet" type="text/css" />
    <?php include 'layouts/head-style.php'; ?>
</head>

<?php include 'layouts/body.php'; ?>

<!-- Begin page -->
<div id="layout-wrapper">
    <?php include 'layouts/menu.php'; ?>
    <!-- ============================================================== -->
    <!-- Start right Content here -->
    <!-- ============================================================== -->
    <div class="main-content">

        <div class="page-content">


 <?php 
  
 try {
    $sql = "SELECT escritorio FROM usuarios WHERE id_usuario = :userid";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $escritorio = $row ? $row['escritorio'] : 1;
} catch (PDOException $e) {
    $escritorio = 1;
}

if ($escritorio == 1) {
    include 'layouts/dashboard.php';
} else if ($escritorio == 2) {
    include 'layouts/dashboard2.php';
} else if ($escritorio == 3) {
    include 'layouts/dashboard3.php';
} else {
    // Si $escritorio contiene un valor inesperado, se incluirá dashboard.php por defecto.
    include 'layouts/dashboard.php';
}








 
 ?>
           
        
        
        
        
        
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
</body>
</html>


<!-- JAVASCRIPT -->
<?php include 'layouts/vendor-scripts.php';
 ?>
<!-- apexcharts -->
<script src="assets/libs/apexcharts/apexcharts.min.js"></script>
<!-- Plugins js-->
<script src="assets/libs/admin-resources/jquery.vectormap/jquery-jvectormap-1.2.2.min.js"></script>
<script src="assets/libs/admin-resources/jquery.vectormap/maps/jquery-jvectormap-world-mill-en.js"></script>
<script src="assets/js/bootstrap.bundle.min.js"></script>
<!-- dashboard init -->
<script src="assets/js/pages/dashboard.init.js"></script>
<!-- App js -->
<script src="assets/js/app.js"></script>

