<?php
class Database {
    public static function StartUp($file = 'db.php.ini') {
		/* Leer credenciales desde el  archivo ini */
		$cred = parse_ini_file($file);
		$dsn = $cred["driver"] . ":host=" . $cred["host"] . ";port=" . $cred["port"] . ";dbname=" . $cred["dbnombre"];
		$pdo = new PDO($dsn, $cred["usuario"], $cred["clave"]);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);	
        return $pdo;
    }
}
