<?php
/* clase PDO preparada por Jorge Dominguez Chavez
// jodocha version 1.1
// Venezuela,2019
*/

require_once("PDO.Log.class.php");

class DB {
	protected static $instance;
    protected $pdo;
	private $Host;
	private $DBName;
	private $DBUser;
	private $DBPassword;
	private $DBPort;
	private $Driver;
	private $sQuery;
	private $bConnected = false;
	private $log;
	private $dsn;
	private $cred;
	private $parameters;
	public  $rowCount   = 0;
	public  $columnCount   = 0;
	public  $querycount = 0;
	private static $_instance;

	public function __construct($file = 'db.php.ini') {
		/* Leer credenciales desde el  archivo ini */
		$this->cred = parse_ini_file($file);
		$this->log        = new Log();
		$this->Host       = $this->cred["host"];
		$this->DBName     = $this->cred["dbnombre"];
		$this->DBUser     = $this->cred["usuario"];
		$this->DBPassword = $this->cred["clave"];
		$this->DBPort	  = $this->cred["port"];
		$this->Driver	  = $this->cred["driver"];
		$this->Connect();
		$this->parameters = array();
	}

	private function Connect() {
		try {
			$this->dsn = $this->Driver.":host=". $this->Host . ";port=" .$this->DBPort .";dbname=" . $this->DBName;
			$this->pdo = new PDO($this->dsn, $this->DBUser, $this->DBPassword);
			$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
			$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            if($this->Driver == 'mysql')
			    $this->pdo->exec("SET CHARACTER SET utf8");
			$this->bConnected = true;
		} catch (PDOException $e) {
			echo $this->ExceptionLog($e->getMessage());
			die();
		}
	}

   	/*Evitamos el clonaje del objeto. Patrón Singleton*/
   	private function __clone(){ }

   	private function __wakeup(){ }

	public function CloseConnection() {
		$this->pdo = null;
	}

	// un método estatico clasico universalmente disponible
	public static function instance() {
		if (self::$instance === null) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	/*Función encargada de crear, si es necesario, el objeto. 
	/* Esta es la función a llamar desde fuera de la clase para instanciar el objeto, y así, poder utilizar sus métodos*/
	public static function getInstance(){
		if (!(self::$_instance instanceof self)){
		   self::$_instance=new self();
		}
		return self::$_instance;
	 }
  
	// a proxy to native PDO methods
	public function __call($method, $args) {
		return call_user_func_array(array($this->pdo, $method), $args);
	}

	private function Init($query, $parameters = "") {
		if (!$this->bConnected) {
			$this->Connect();
		}
		try {
			$this->parameters = $parameters;
			$this->sQuery     = $this->pdo->prepare($this->BuildParams($query, $this->parameters));
			if (!empty($this->parameters)) {
				if (array_key_exists(0, $parameters)) {
					$parametersType = true;
					array_unshift($this->parameters, "");
					unset($this->parameters[0]);
				} else {
					$parametersType = false;
				}
				foreach ($this->parameters as $column => $value) {
					$this->sQuery->bindParam($parametersType ? intval($column) : ":" . $column, $this->parameters[$column]); 
					//It would be query after loop end(before 'sQuery->execute()').It is wrong to use $value.
				}
			}
			$this->succes = $this->sQuery->execute();
			$this->querycount++;
		} catch (PDOException $e) {
			echo $this->ExceptionLog($e->getMessage(), $this->BuildParams($query));
			die();
		}

		$this->parameters = array();
	}

	private function BuildParams($query, $params = array()){
		if (!empty($params)) {
			$array_parameter_found = false;
			foreach ($params as $parameter_key => $parameter) {
				if (is_array($parameter)){
					$array_parameter_found = true;
					$in = "";
					foreach ($parameter as $key => $value){
						$name_placeholder = $parameter_key."_".$key;
						// concatenates params as named placeholders
					    	$in .= ":".$name_placeholder.", ";
						// adds each single parameter to $params
						$params[$name_placeholder] = $value;
					}
					$in = rtrim($in, ", ");
					$query = preg_replace("/:".$parameter_key."/", $in, $query);
					// removes array form $params
					unset($params[$parameter_key]);
				}
			}
			// updates $this->params if $params and $query have changed
			if ($array_parameter_found) $this->parameters = $params;
		}
		return $query;
	}

	public function query($query, $params = null, $fetchmode = PDO::FETCH_ASSOC) {
		$query        = trim($query);
		$rawStatement = explode(" ", $query);
		$this->Init($query, $params);
		$statement = strtolower($rawStatement[0]);
		if ($statement === 'select' || $statement === 'show') {
			return $this->sQuery->fetchAll($fetchmode);
		} elseif ($statement === 'insert' || $statement === 'update' || $statement === 'delete') {
			return $this->sQuery->rowCount();
		} else {
			return NULL;
		}
	}

	public function lastInsertId() {
		return $this->pdo->lastInsertId();
	}

	public function column($query, $params = null) {
		$this->Init($query, $params);
		$resultColumn = $this->sQuery->fetchAll(PDO::FETCH_COLUMN);
		$this->rowCount = $this->sQuery->rowCount();
		$this->columnCount = $this->sQuery->columnCount();
		$this->sQuery->closeCursor();
		return $resultColumn;
	}

	public function row($query, $params = null, $fetchmode = PDO::FETCH_ASSOC) {
		$this->Init($query, $params);
		$resultRow = $this->sQuery->fetch($fetchmode);
		$this->rowCount = $this->sQuery->rowCount();
		$this->columnCount = $this->sQuery->columnCount();
		$this->sQuery->closeCursor();
		return $resultRow;
	}

	public function single($query, $params = null) {
		$this->Init($query, $params);
		return $this->sQuery->fetchColumn();
	}

	private function ExceptionLog($message, $sql = "") {
		$exception = 'Unhandled Exception. <br />';
		$exception .= $message;
		$exception .= "<br /> You can find the error back in the log.";

		if (!empty($sql)) {
			$message .= "\r\nRaw SQL : " . $sql;
		}
		$this->log->write($message, $this->DBName . md5($this->DBPassword));
		//Prevent search engines to crawl
		header("HTTP/1.1 500 Internal Server Error");
		header("Status: 500 Internal Server Error");
		return $exception;
	}
}
