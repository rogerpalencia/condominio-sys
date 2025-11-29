<?php
$archivo = 'error_log';
$num_lineas = 20;

// Leer las últimas líneas del archivo
function leer_ultimas_lineas($filename, $lines = 20) {
    if (!file_exists($filename)) return ["[Archivo no encontrado]"];
    if (!is_readable($filename)) return ["[Archivo sin permisos de lectura]"];
    
    $data = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    return $data ? array_slice($data, -$lines) : ["[Archivo vacío]"];
}

// Obtener el último error
function obtener_ultimo_error($filename) {
    if (!file_exists($filename)) return "[Archivo no encontrado]";
    if (!is_readable($filename)) return "[Archivo sin permisos de lectura]";
    
    $data = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    return $data ? end($data) : "[No hay errores]";
}

// Manejo de acciones POST
$mensaje = "";
$tipo_mensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['limpiar'])) {
        // Limpiar el archivo
        if (file_exists($archivo)) {
            if (is_writable($archivo)) {
                file_put_contents($archivo, '');
                $mensaje = "✓ Archivo limpiado correctamente";
                $tipo_mensaje = "exito";
            } else {
                $mensaje = "✗ Error: No hay permisos de escritura";
                $tipo_mensaje = "error";
            }
        } else {
            $mensaje = "✗ Error: El archivo no existe";
            $tipo_mensaje = "error";
        }
    }
}

$ultimo_error = obtener_ultimo_error($archivo);
$lineas = leer_ultimas_lineas($archivo, $num_lineas);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Visor de Error Log</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, sans-serif; margin: 20px; background: #f5f5f5; }
        .contenedor { max-width: 1200px; margin: 0 auto; }
        .panel {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        .log { 
            background: #111; 
            color: #f0f0f0; 
            padding: 15px; 
            border-radius: 5px;
            max-height: 60vh;
            overflow-y: auto;
            white-space: pre-wrap;
            font-family: monospace;
        }
        .ultimo-error {
            background: #fff8e6;
            border-left: 4px solid #ff9800;
            padding: 15px;
            margin: 15px 0;
            font-family: monospace;
            white-space: pre-wrap;
            border-radius: 4px;
        }
        .linea { display: block; margin: 2px 0; }
        .boton {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            margin: 5px;
            cursor: pointer;
            border-radius: 4px;
            font-size: 14px;
            transition: all 0.3s;
        }
        .boton:hover { background: #45a049; transform: translateY(-2px); }
        .boton-limpiar { background: #f44336; }
        .boton-limpiar:hover { background: #d32f2f; }
        .botones { margin: 15px 0; }
        .mensaje {
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
            font-weight: bold;
        }
        .exito { background: #dff0d8; color: #3c763d; border-left: 4px solid #3c763d; }
        .error { background: #f2dede; color: #a94442; border-left: 4px solid #a94442; }
        h1 { color: #333; }
        h2 { color: #ff9800; font-size: 1.2em; }
        .titulo-seccion { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-bottom: 10px; 
        }
    </style>
</head>
<body>
    <div class="contenedor">
        <h1>Monitor de Errores</h1>
        
        <?php if ($mensaje): ?>
            <div class="mensaje <?= $tipo_mensaje ?>"><?= $mensaje ?></div>
        <?php endif; ?>
        
        <div class="panel">
            <div class="titulo-seccion">
                <h2>Último Error Registrado</h2>
                <small><?= date('Y-m-d H:i:s') ?></small>
            </div>
            <div class="ultimo-error"><?= htmlspecialchars($ultimo_error) ?></div>
        </div>
        
        <div class="panel">
            <div class="titulo-seccion">
                <h2>Últimas <?= $num_lineas ?> líneas del error_log</h2>
                <div class="botones">
                    <form method="post" style="display:inline;">
                        <button type="submit" name="actualizar" class="boton">Actualizar</button>
                    </form>
                    <form method="post" style="display:inline;" onsubmit="return confirm('¿Estás seguro de limpiar el archivo? Esta acción no se puede deshacer.');">
                        <button type="submit" name="limpiar" class="boton boton-limpiar">Limpiar Archivo</button>
                    </form>
                </div>
            </div>
            
            <div class="log">
                <?php
                if (empty($lineas)) {
                    echo '<div style="color:#aaa; text-align:center;">[El archivo está vacío]</div>';
                } else {
                    foreach ($lineas as $linea) {
                        echo '<span class="linea">' . htmlspecialchars($linea) . '</span>';
                    }
                }
                ?>
            </div>
        </div>
    </div>
    
    <script>
        // Función para confirmar la limpieza
        function confirmarLimpieza(e) {
            if (!confirm('¿Estás seguro de limpiar el archivo?\nEsta acción no se puede deshacer.')) {
                e.preventDefault();
            }
        }
        
        // Asignar evento al botón de limpiar
        document.querySelector('.boton-limpiar').addEventListener('click', confirmarLimpieza);
    </script>
</body>
</html>