<?php
class Funciones
{
	public function fechahoy()
	{ 
		$curDate = date("Y-m-d");
		return($curDate)  ;
	}

	public function anno()
	{ 
		$Today = getdate();
		$year = $Today['year'];
		return($year)  ; 
	}

	public function fmtbs($i)
	{
		return number_format($i, 2, ',', '.');
	}


	public function fmtbspre($i)
	{
		return "Bs." . number_format($i, 2, ',', '.');
	}


	public function fmtus($i)
	{
		return number_format($i, 2, '.', ',');
	}

	public function hora()
	{
		$hora = getdate(time());
		return $hora["hours"] . ":" . $hora["minutes"] ; 
	} 

	public function formatfecha($fecha)
	{ 
		$newdate = date("d/m/Y", strtotime($fecha));
		return $newdate; 
	} 

	public function fechaymd($fecha)
	{ 
		$newdate = date("Y-m-d", strtotime($fecha));
		return $newdate; 
	} 


	public function explfecha($fecha)
	{ 
		$orderdate = explode("/", $fecha);
		$newdate = $orderdate[2]."-".$orderdate[1]."-".$orderdate[0];
		return $newdate; 
	}

	public function timest()
	{ 
		$fechat = new DateTime();
		$timestamp= $fechat->getTimestamp();
		return $timestamp; 
	} 

	function isnum($valor)
	{
		return (is_numeric($valor)) ? 1 : 0;
	}

	function istext($valor)
	{
		return (is_numeric($valor)) ? 0 : 1;
	}

	function isdate($valor)
	{
		$d = strtotime($valor);
		return ($d>=1) ? 1 : 0;
	}

	function validateDate($date, $format = 'Y-m-d H:i:s')
	{
		$d = DateTime::createFromFormat($format, $date);
		return $d && $d->format($format) == $date;
	}

	function evaluar($valor)
	{
		$nopermitido = array("'",'\\','<','>',"\"");
		$valor = str_replace($nopermitido, "", $valor);
		return $valor;
	}

	function get_fecha_actual($formato)
	{
		setlocale(LC_TIME, 'es_ES.UTF-8');
		$formato_fecha = array(
			"largo" => "l, j \d\e F \d\e Y",
			"corto" => "d/m/Y",
			"ISO" => "Y-m-d",
		);
		if (array_key_exists($formato, $formato_fecha)) {
			$fecha = date($formato_fecha[$formato]);
		}
		return $fecha;
	}

	function _formatear($fecha) 
	{
		return strtotime(substr($fecha, 6, 4)."-".substr($fecha, 3, 2)."-".substr($fecha, 0, 2)." " .substr($fecha, 10, 6)) * 1000;
	}

	function getCurrURL()
	{
		$currURL='http://'.$_SERVER['HTTP_Host'].'/'.ltrim($_SERVER['REQUEST_URI'],'/').'';
		return $currURL;
	}

	function diff_dias( $f1, $f2 )
	{
		if($f1 == "" || $f2 == "") { return "0"; }
		$matf1=explode("/",$f1);
		$matf2=explode("/",$f2);
		$nf1=$matf1[1]."/".$matf1[0]."/".$matf1[2];
		$nf2=$matf2[1]."/".$matf2[0]."/".$matf2[2];
		$segundos  = strtotime($nf2)-strtotime($nf1);
		$dias      = intval($segundos/86400);
		$sl_retorna = $dias ;
		return $sl_retorna;
	}

	function fecha24a12($hora)
	{
		if(intval($hora) <= 12) {
			return array($hora, "AM");
		} else {
			return array(($hora - 12), "PM");
		}
	}

	function fecha12a24($hora, $siglas)
	{
		if ($siglas == "AM") {
			return $hora;
		} else {
			if ($hora == "12") { return "00"; }
			return 12 + $hora;
		}
	}


	function charset_decode_utf_8 ($string)
	{
	  return html_entity_decode($string);
	} 

	function FechaSis($pValor)
	{
		$Fecha = date("d").'/'.date("m").'/'.date("Y");
		$Hora = date("h").':'.date("i").':'.date("s").' '.date("a");
		switch (strtoupper($pValor)) {
			case "F":
				return($Fecha);
				break;
			case "FH":
				return($Fecha.' '.$Hora);
				break;
			default:
				return('funci�n FechaSis: parametro no v�lido');
				break;
		}
	}

	function ConvierteFecha($accion,$fecha)
	{
		if($fecha==""){ return; }
		switch($accion) {
			case 'M':
				$f_arr = explode(" ",$fecha);
				$fecha = $f_arr[0];
				if (strpos($fecha,"/")!==false) { $f=explode("/",$fecha); } else { $f=explode("-",$fecha); }
				return "$f[2]/$f[1]/$f[0]";
			break;

			case 'M2':
				if (strpos($fecha,"/") !== false) { $f = explode("/",$fecha); } else { $f = explode("-",$fecha); }
				return "$f[2]-$f[1]-$f[0]";
			break;

			case 'FH':
				if (strpos($fecha,"/")!==false) { $h=explode(" ",$fecha); $f=explode("/",$h[0]); } else { $h=explode(" ",$fecha); $f=explode("-",$h[0]); }
				return "$f[2]-$f[1]-$f[0] " . $h[1];
			break;

			case 'G':
				if (strpos($fecha,"/")!==false) { $f=explode("/",$fecha); } else { $f=explode("-",$fecha); }
				return date("Y-m-d",mktime(0,0,0,$f[1],$f[0],$f[2]));
			break;

			case 'AFH':
				if (strpos($fecha,"/")!==false) { $f=explode("/",$fecha); } else { $f=explode("-",$fecha); }
				$fecha= "$f[2]-$f[1]-$f[0]";
				$hora = date("h").':'.date("i").':'.date("s").' '.date("a");
				return($fecha.' '.$hora);
			break;

			case 'AAAAIMDD':
				if (strpos($fecha,"/")!==false) { $f=explode("/",$fecha); } else { $f=explode("-",$fecha); }
				$aMeses= array("JAN", "FEB", "MAR", "ABR", "MAY", "JUN", "JUL", "AGO", "SEP", "OCT", "NOV", "DEC");
				$nDia = $f[0];
				$nMes = ($f[1]-1);
				$nAno = $f[2];
				return $nAno."-".$aMeses[$nMes]."-".$nDia ;
			break;

			case 'DDIMAAAA':
				if (strpos($fecha,"/")!==false) { $f=explode("/",$fecha); } else { $f=explode("-",$fecha); }
				$aMeses= array("JAN", "FEB", "MAR", "ABR", "MAY", "JUN", "JUL", "AGO", "SEP", "OCT", "NOV", "DEC");
				$nDia = $f[0];
				$nMes = ($f[1]-1);
				$nAno = $f[2];
				return $nDia."-".$aMeses[$nMes]."-".$nAno ;
			break;
		}
	}

	function FechaLarga() {
		$aDias = array("Domingo","Lunes", "Martes", "Miercoles","Jueves", "Viernes", "Sabado");
		$aMeses= array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
		$nDiaSemana= date("w");
		$nDia = date("j");
		$nMes = (date("n")-1);
		$nAnio = date("Y");
		return  $aDias[$nDiaSemana] . ", " . $nDia . " de " . $aMeses[$nMes] . " de " . $nAnio ;
	}

	function FechaMes($fecha) {	
		$pos=strpos($fecha, "-");
		if ($pos!= false) {  $f=explode("-", $fecha); } else { $f=explode("/", $fecha); }
		$aMeses= array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
		$nDia = $f[0];
		$nMes = ($f[1]-1);
		$nAno = $f[2];
		return  $aMeses[$nMes] . " - " . $nAno ;
	}


	function HoraLarga() {
		$Hora = date("g");
		if ($Hora>=12) {  $AmPm=" p.m." ;} else { $AmPm=" a.m.";}
		if ($Hora>12) { $Hora= $Hora -12; }
		if ($Hora==0) {$Hora=12;}
		$Minutos = date("i");
		return "   Hora:  $Hora  :   $Minutos  $AmPm ";
	}

	function ordena_fecha($fech, $accion="") 
	{	
		$pos=strpos($fech, "-");
		if ($pos!= false)
		{  
			$f=explode("-", $fech); 
			$sep="-"; 
		} else { 
			$f=explode("/", $fech); 
			$sep="-"; 
		}
		return $f[2].$sep.$f[1].$sep.$f[0];  
	}

	function FechaOrdenar($pFecha, $pSeparador, $accion) 
	{	
		$pFecha=trim($pFecha);

		if (empty($pFecha))
		{ 
			return ""; 
		}

		if (strpos($pFecha,"/")!==false)
		{ 
			$pSeparador="/"; 
		} else { 
			$pSeparador="-"; 
		}
		if (trim($pFecha)!="")
		{
			if ($accion=='M')
			{   
				if (strlen($pFecha)>10)
				{
					$vFecha = explode(" ", $pFecha);
					$f = explode('-', $vFecha[0]);
					return "$f[2]/$f[1]/$f[0]";
				} else {
					$f = explode('-', $pFecha);
					return "$f[2]/$f[1]/$f[0]";
				}
			} else {
				$Hora = "";
				if (strlen($pFecha)>10)
				{
					$vFecha = explode(" ", $pFecha);
					$Fecha = $vFecha[0];
					$Hora = " ".$vFecha[1]." ".$vFecha[2];
				} else { 
					$Fecha = $pFecha;	
				}
				$FechaOrd = explode($pSeparador, $Fecha);
				$FechaOrd = "$FechaOrd[2]/$FechaOrd[1]/$FechaOrd[0]";
				return($FechaOrd.$Hora);
			}
		}
	}

	function numDias($fecha_actual,$fecha_final)
	{
		$int_nodias = floor(abs(strtotime($fecha_actual) - strtotime($fecha_final))/86400);
		return $int_nodias;
	}


	function DevuelveDiaSemana($fecha) 
	{
		$aDias = array("Domingo","Lunes", "Martes", "Miercoles","Jueves", "Viernes", "Sabado");
		$e =explode("-", $fecha);
		$nDiaSemana= date( "w", mktime(0,0,0,$e[1],$e[2],$e[0]) );
		return $aDias[$nDiaSemana];
	}

	function ToFecha()
	{
		$aDias = array("Domingo","Lunes", "Martes", "Miercoles","Jueves", "Viernes", "Sabado");
		$aMeses= array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
		$nDiaSemana= date("w");
		$nDia = date("j");
		$nMes = (date("n")-1);
		$nAnio = date("Y");
		return  $aDias[$nDiaSemana] . ", " . $nDia . " de " . $aMeses[$nMes] . " de " . $nAnio ;
	}

	function DiaFecha()
	{
		$aDias = array("Domingo","Lunes", "Martes", "Miercoles","Jueves", "Viernes", "Sabado");
		$nDiaSemana= date("w");
		$nDia = date("j");
		$nMes = date("n");
		$nAnio = date("Y");
		return  $aDias[$nDiaSemana] . ", " . $nDia . "/" .$nMes. "/" . $nAnio ;
	}

	function Tohora()
	{
			$Hora = date("G");
			if ($Hora>=12) {  $AmPm=" p.m." ;} else { $AmPm=" a.m.";}
			if ($Hora>12) { $Hora= $Hora -12; }
			if ($Hora==0) {$Hora=12;}
			$Minutos = date("i");
			return "   Hora:  $Hora  :   $Minutos  $AmPm ";
	}

	function intervalosemana($fecha)
	{
		$matfecha =explode("/",$fecha);
		$ndia = intval($matfecha[0]);
		$nmes = intval($matfecha[1]);
		$nano = intval($matfecha[2]);
		$numdia= intval(date("w",mktime(0,0,0,$nmes,$ndia,$nano)));
		//Ultimo dia de la semana
		if ($numdia<6) {
			$diasf=6-$numdia;
			$fin=modfecha("D",$diasf,$fecha);
		} else { 
			$fin=$fecha; 
		}
		//Primer dia de la semana
		if ($numdia>0) {
			$diasf=$numdia;
			$inicio=modfecha("D",$diasf,$fecha,"R");
		} else { 
			$inicio=$fecha; 
		}

		return array("fecha_inicio" => $inicio, "fecha_fin" => $fin);
	}


	function modfecha($tipovar, $cant, $fecha, $tipoop="S")
	{
		$matfecha =explode("/",$fecha);
		$ndia = intval($matfecha[0]);
		$nmes = intval($matfecha[1]);
		$nano = intval($matfecha[2]);
		switch ($tipovar) {
			case 'A':   // Agregar Ano
				if($tipoop=="S") {$nano+=$cant;} else {$nano-=$cant;}
				break;
			case 'M':    // Agregar Mes
				if($tipoop=="S") {$nmes+=$cant;} else {$nmes-=$cant;}
				break;
			case 'D':    // Agregar Dias
				if($tipoop=="S") {$ndia+=$cant;} else {$ndia-=$cant;}
				break;
		}
		$nfecha= date("d/m/Y",mktime(0,0,0,$nmes,$ndia,$nano));
		return $nfecha;
	}


	function retornaIntervaloFechas($desde, $hasta)
	{
		while($desde!=$hasta) {
			$arr_fechas[] = $desde;
			$desde = dateAdd("d",1,$desde);
		}
		$arr_fechas[] = $desde;
		return $arr_fechas;
	}

	function formatea_hora_agenda($hora)
	{
		$a = explode(".", $hora);
		if(intval(@$a[1]) > 0) {
			$tm = number_format( intval($hora), 0 ) . ":30";
		} else {
			$tm = number_format( intval($hora), 0 ) . ":00";
		}
		$e =explode(":", $tm); // Saco los minutos
		if(intval($e[0]) >= 12) {
			$t = $e[0] - 12;
			if($t == 0) { $t = 12; }
			$tm = $t . ":" . $e[1] . " p.m.";
		} else {
			$t = $e[0];
			if($t == 0) { $t = 12; }
			$tm = $t . ":" . $e[1] . " a.m.";
		}
		return $tm;
	}

	function formatea_hora_agenda24($hora)
	{
		$a = explode(".", $hora);
		if(intval(@$a[1]) > 0) {
			$tm = number_format( intval($hora), 0 ) . ":30";
		} else {
			$tm = number_format( intval($hora), 0 ) . ":00";
		}
		$e =explode(":", $tm); // Saco los minutos
		if(intval($e[0]) >= 12) {
			$t = $e[0];
			if($t == 0) { $t = 12; }
			$tm = $t . ":" . $e[1] . ":00";
		} else {
			$t = $e[0];
			if($t == 0) { $t = "00"; } elseif($t <= 9) { $t = "0" . $t; }
			$tm = $t . ":" . $e[1] . ":00";
		}
		return $tm;
	}

	function ultimoDiaMes($mes,$ano)
	{
		$ultimo_dia=28;
		while (checkdate($mes,$ultimo_dia + 1,$ano)) {
			$ultimo_dia++;
		}
		return $ultimo_dia;
	}

	function retornaMes($mes)
	{
		$meses = array('1'=>'ENERO','2'=>'FEBRERO','3'=>'MARZO','4'=>'ABRIL','5'=>'MAYO','6'=>'JUNIO','7'=>'JULIO','8'=>'AGOSTO','9'=>'SEPTIEMBRE','10'=>'OCTUBRE','11'=>'NOVIEMBRE','12'=>'DICIEMBRE');
		return $meses[$mes];
	}

	function retornaAntiguedad($fecha_ingreso)
	{
		if($fecha_ingreso == ""){
			return "0";
		} else if(count(explode("/",$fecha_ingreso)) < 3){
			return "0";
		} else {
			$ano = 0;
			$fecha_in =explode("/",$fecha_ingreso);
			if($fecha_in[0] == ""){
				$ano = 0;
			} else if($fecha_in[1] == ""){
				$ano = 0;
			} else if($fecha_in[2] == ""){
				$ano = 0;
			} else {
				$dia_in = $fecha_in[0];
				$mes_in = $fecha_in[1];
				$ano_in = $fecha_in[2];
				$fecha_ac =explode("/",date("d/m/Y"));
				$dia_ac = $fecha_ac[0];
				$mes_ac = $fecha_ac[1];
				$ano_ac = $fecha_ac[2];
				$ano = $ano_ac - $ano_in;
				if($mes_ac < $mes_in) {
					$ano = $ano - 1;
				}
				if($mes_ac == $mes_in && $dia_ac < $dia_in) {
					$ano = $ano - 1;
				}
				if($ano < 0) {
					$ano = 0;
				}
			}
			return $ano;
		}
	}

	function nombremes($mes) 
	{
		if ($mes=="01") { $mes="Enero"; } else
		if ($mes=="02") { $mes="Febrero"; } else
		if ($mes=="03") { $mes="Marzo"; } else
		if ($mes=="04") { $mes="Abril"; } else
		if ($mes=="05") { $mes="Mayo"; } else
		if ($mes=="06") { $mes="Junio"; } else
		if ($mes=="07") { $mes="Julio"; } else
		if ($mes=="08") { $mes="Agosto"; } else
		if ($mes=="09") { $mes="Septiembre"; } else
		if ($mes=="10") { $mes="Octubre"; } else
		if ($mes=="11") { $mes="Noviembre"; } else
		if ($mes=="12") { $mes="Diciembre"; };
		return($mes);
	}




}	
?>
