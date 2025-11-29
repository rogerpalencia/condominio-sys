<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <script type="text/javascript">
    function sumarMesesAFecha() {
        let fecha = new Date(document.getElementById('fecha').value);
        let meses = parseInt(document.getElementById('meses').value);
        fecha.setMonth(fecha.getMonth() + meses); // Sumar meses a la nueva fecha
        document.getElementById('fechaNueva').value = fecha.toISOString().substring(0, 10);
    }
    </script>
</head>
<body>
    <input type="date" name="fecha" id="fecha">
    <input type="number" name="" id="meses" onblur="sumarMesesAFecha();">
    <input type="date" name="fechaNueva" id="fechaNueva">
</body>
</html>
