<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visor de Error Log Mejorado</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .log-container, .latest-event {
            width: 80%;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #ccc;
            background-color: #f9f9f9;
            overflow-y: auto;
            height: 300px;
        }
        .update-button, .export-button {
            display: block;
            width: 120px;
            margin: 20px auto;
            padding: 10px;
            background-color: #007bff;
            color: white;
            text-align: center;
            border: none;
            cursor: pointer;
        }
        .update-button:hover, .export-button:hover {
            background-color: #0056b3;
        }
        .search-bar {
            width: 80%;
            margin: 20px auto;
            display: flex;
            justify-content: center;
        }
        .search-bar input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
        }
    </style>
</head>
<body>

<h1 style="text-align: center;">Visor de Error</h1>

<div class="search-bar">
    <input type="text" id="search-term" placeholder="Buscar en el log..." oninput="updateLog()">
</div>

<div class="log-container" id="log-container">
    <!-- Aquí se cargará el log completo -->
</div>

<div class="latest-event" id="latest-event">
    <!-- Aquí se cargará el último evento -->
</div>

<button class="update-button" onclick="updateLog()">Actualizar</button>
<button class="export-button" onclick="exportLog()">Exportar</button>

<script>
    function updateLog() {
        const searchTerm = document.getElementById('search-term').value;

        fetch(`visor_error.php?search=${encodeURIComponent(searchTerm)}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                document.getElementById('log-container').innerHTML = data.fullLog;
                document.getElementById('latest-event').innerHTML = data.latestEvent;
            })
            .catch(error => console.error('Error al actualizar el log:', error));
    }

    function exportLog() {
        window.open('export_log.php');
    }

    // Auto-actualización cada 60 segundos
    setInterval(updateLog, 60000);

    // Llamar a updateLog al cargar la página para mostrar el log inicial
    window.onload = updateLog;
</script>

</body>
</html>
