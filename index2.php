<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>

<?php
require_once("core/PDO.class.php") ; 
$conn=  DB::getInstance();
require_once("layouts/vars.php") ; 
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
 <?php include 'layouts/dashboard.php'; ?>
            <!-- container-fluid -->
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
<?php include 'layouts/vendor-scripts.php'; ?>
<!-- apexcharts -->
<script src="assets/libs/apexcharts/apexcharts.min.js"></script>
<script src="assets/js/pages/dashboard.init.js"></script>
<script src="assets/libs/admin-resources/jquery.vectormap/jquery-jvectormap-1.2.2.min.js"></script>
<script src="assets/libs/admin-resources/jquery.vectormap/maps/jquery-jvectormap-world-mill-en.js"></script>
<script src="assets/js/app.js"></script>
