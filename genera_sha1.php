<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generar SHA1</title>
</head>
<body>
    <h1>Generador de SHA1</h1>
    <form method="post" action="">
        <label for="texto">Texto:</label>
        <input type="text" id="texto" name="texto" required>
        <br><br>
        <label for="resultado">SHA1:</label>
        <input type="text" id="resultado" name="resultado" readonly value="<?php echo isset($_POST['texto']) ? sha1($_POST['texto']) : ''; ?>">
        <br><br>
        <button type="submit">Generar SHA1</button>
    </form>
</body>
</html>