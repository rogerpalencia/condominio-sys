<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title></title>
</head>
<body>


<?php echo $_SERVER['PHP_SELF'] . "<br>" ;

echo "ID: ". $_POST['id'] . "<br>" ;
echo "APELLIDOS: ". $_POST['apellidos'] . "<br>" ;
echo "NOMBRES: ". $_POST['nombres'] . "<br>" ;
echo "EDAD: ". $_POST['edad'] . "<br>" ; 

?>

<input type="button" value="regresar" onclick="reg()">

</body>
</html>


<script type="text/javascript">
  
    function reg()  
    {
        window.location= "test.php" ;
    }
</script>