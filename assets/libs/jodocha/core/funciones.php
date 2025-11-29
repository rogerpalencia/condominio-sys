<?php

	function evaluar($valor) {
		$nopermitido = array("'",'\\','<','>',"\"");
		$valor = str_replace($nopermitido, "", $valor);
		return $valor;
	}

	function get_fecha_actual($formato) {
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
		
	function _formatear($fecha) {
		return strtotime(substr($fecha, 6, 4)."-".substr($fecha, 3, 2)."-".substr($fecha, 0, 2)." " .substr($fecha, 10, 6)) * 1000;
	}

	function getCurrURL() {
            $currURL='http://'.$_SERVER['HTTP_Host'].'/'.ltrim($_SERVER['REQUEST_URI'],'/').'';
            return $currURL;
     }

	function getComboBoxList($tabla, $c1, $c2){
		$a = array();
		$i = 0;
		foreach($this->listarTabla($tabla, $c1, $c2) as $ro) {
			  $a[$ro->id] = '<option value="' . $ro->id . '">'. $ro->nombre . '</option>';
			  $i++;
		}
	    $a[$i] = '</select>';
		return $a;			
	}
	
	function diff_dias( $f1, $f2 ) {
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

	function fecha24a12($hora) {
		if(intval($hora) <= 12) {
			return array($hora, "AM");
		} else {
			return array(($hora - 12), "PM");
		}
	}

	function fecha12a24($hora, $siglas) {
		if ($siglas == "AM") {
			return $hora;
		} else {
			if ($hora == "12") { return "00"; }
			return 12 + $hora;
		}
	}

	function FechaSis($pValor){
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

	function ConvierteFecha($accion,$fecha) {
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

	function ordena_fecha($fech, $accion="") {	
		$pos=strpos($fech, "-");
		if ($pos!= false) {  $f=explode("-", $fech); $sep="-"; } else { $f=explode("/", $fech); $sep="-"; }
		return $f[2].$sep.$f[1].$sep.$f[0];  //}
	}

	function FechaOrdenar($pFecha, $pSeparador, $accion) {	
		$pFecha=trim($pFecha);

		if (empty($pFecha)) { return ""; }

		if (strpos($pFecha,"/")!==false) { $pSeparador="/"; } else { $pSeparador="-"; }
		if (trim($pFecha)!="") 	{
			if ($accion=='M') {   
				if (strlen($pFecha)>10) {
					$vFecha = explode(" ", $pFecha);
					$f = explode('-', $vFecha[0]);
					return "$f[2]/$f[1]/$f[0]";
				} else {
					$f = explode('-', $pFecha);
					return "$f[2]/$f[1]/$f[0]";
				}
			} else {
				$Hora = "";
				if (strlen($pFecha)>10) {
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

	function numDias($fecha_actual,$fecha_final){

		$int_nodias = floor(abs(strtotime($fecha_actual) - strtotime($fecha_final))/86400);
		return $int_nodias;
	}

	function datediff($interval, $datefrom, $dateto, $using_timestamps = false) {
		if (!$using_timestamps) {
			$datefrom = strtotime($datefrom, 0);
			$dateto = strtotime($dateto, 0);
		}
		$difference = $dateto - $datefrom; // Difference in seconds
		switch($interval) {
			case 'yyyy': // Number of full years
				$years_difference = floor($difference / 31536000);
				if (mktime(date("H", $datefrom), date("i", $datefrom), date("s", $datefrom), date("n", $datefrom), date("j", $datefrom), date("Y", $datefrom)+$years_difference) > $dateto) {
					$years_difference--;
				}
				if (mktime(date("H", $dateto), date("i", $dateto), date("s", $dateto), date("n", $dateto), date("j", $dateto), date("Y", $dateto)-($years_difference+1)) > $datefrom) {
					$years_difference++;
				}
				$datediff = $years_difference;
				break;
			case "q": // Number of full quarters
				$quarters_difference = floor($difference / 8035200);
				while (mktime(date("H", $datefrom), date("i", $datefrom), date("s", $datefrom), date("n", $datefrom)+($quarters_difference*3), date("j", $dateto), date("Y", $datefrom)) < $dateto) {
					$months_difference++;
				}
				$quarters_difference--;
				$datediff = $quarters_difference;
				break;
			case "m": // Number of full months
				$months_difference = floor($difference / 2678400);
				while (mktime(date("H", $datefrom), date("i", $datefrom), date("s", $datefrom), date("n", $datefrom)+($months_difference), date("j", $dateto), date("Y", $datefrom)) < $dateto) {
					$months_difference++;
				}
				$months_difference--;
				$datediff = $months_difference;
				break;
			case 'y': // Difference between day numbers
				$datediff = date("z", $dateto) - date("z", $datefrom);
				break;
			case "d": // Number of full days
				$datediff = floor($difference / 86400);
				break;
			case "w": // Number of full weekdays
				$days_difference = floor($difference / 86400);
				$weeks_difference = floor($days_difference / 7); // Complete weeks
				$first_day = date("w", $datefrom);
				$days_remainder = floor($days_difference % 7);
				$odd_days = $first_day + $days_remainder; // Do we have a Saturday or Sunday in the remainder?
				if ($odd_days > 7) { // Sunday
					$days_remainder--;
				}
				if ($odd_days > 6) { // Saturday
					$days_remainder--;
				}
				$datediff = ($weeks_difference * 5) + $days_remainder;
				break;
			case "ww": // Number of full weeks
				$datediff = floor($difference / 604800);
				break;
			case "h": // Number of full hours
				$datediff = floor($difference / 3600);
				break;
			case "n": // Number of full minutes
				$datediff = floor($difference / 60);
				break;
			default: // Number of full seconds (default)
				$datediff = $difference;
				break;
		}
		return $datediff;
	}

	function DevuelveDiaSemana($fecha) {
		$aDias = array("Domingo","Lunes", "Martes", "Miercoles","Jueves", "Viernes", "Sabado");
		$e =explode("-", $fecha);
		$nDiaSemana= date( "w", mktime(0,0,0,$e[1],$e[2],$e[0]) );
		return $aDias[$nDiaSemana];
	}

	function ToFecha() {
		$aDias = array("Domingo","Lunes", "Martes", "Miercoles","Jueves", "Viernes", "Sabado");
		$aMeses= array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
		$nDiaSemana= date("w");
		$nDia = date("j");
		$nMes = (date("n")-1);
		$nAnio = date("Y");
		return  $aDias[$nDiaSemana] . ", " . $nDia . " de " . $aMeses[$nMes] . " de " . $nAnio ;
	}

	function DiaFecha() {
		$aDias = array("Domingo","Lunes", "Martes", "Miercoles","Jueves", "Viernes", "Sabado");
		$nDiaSemana= date("w");
		$nDia = date("j");
		$nMes = date("n");
		$nAnio = date("Y");
		return  $aDias[$nDiaSemana] . ", " . $nDia . "/" .$nMes. "/" . $nAnio ;
	}

	function Tohora() {
			$Hora = date("G");
			if ($Hora>=12) {  $AmPm=" p.m." ;} else { $AmPm=" a.m.";}
			if ($Hora>12) { $Hora= $Hora -12; }
			if ($Hora==0) {$Hora=12;}
			$Minutos = date("i");
			return "   Hora:  $Hora  :   $Minutos  $AmPm ";
	}

	function busca_ano_presup() {
		$sql="SELECT ano FROM periodo_ano where estatus='1' order by ano desc limit 1";
		$rs=dregistro(abredatabase(g_BDSeguridad,$sql));
		if ($rs!="")
			{	$anoppto = $rs['ano'];	}
		else
			{	$anoppto= date("Y");	}

		return $anoppto;
	}

	function dateAdd($interval,$number,$dateTime) {
		$dateTime = (strtotime($dateTime) != -1) ? strtotime($dateTime) : $dateTime;
		$dateTimeArr=getdate($dateTime);
		$yr=$dateTimeArr[@year];
		$mon=$dateTimeArr[@mon];
		$day=$dateTimeArr[@mday];
		$hr=$dateTimeArr[@hours];
		$min=$dateTimeArr[@minutes];
		$sec=$dateTimeArr[@seconds];
		switch($interval) {
			case "s"://seconds
				$sec += $number;
				break;
			case "n"://minutes
				$min += $number;
				break;
			case "h"://hours
				$hr += $number;
				break;
			case "d"://days
				$day += $number;
				break;
			case "ww"://Week
				$day += ($number * 7);
				break;
			case "m": //similar result "m" dateDiff Microsoft
				$mon += $number;
				break;
			case "yyyy": //similar result "yyyy" dateDiff Microsoft
				$yr += $number;
				break;
			default:
				$day += $number;
		}

		$dateTime = mktime($hr,$min,$sec,$mon,$day,$yr);
		$dateTimeArr=getdate($dateTime);
		$nosecmin = 0;
		$min=$dateTimeArr[@minutes];
		$sec=$dateTimeArr[@seconds];
		if ($hr==0){$nosecmin += 1;}
		if ($min==0){$nosecmin += 1;}
		if ($sec==0){$nosecmin += 1;}
		if ($nosecmin>2){    return(date("Y-m-d",$dateTime));} else {    return(date("Y-m-d G:i:s",$dateTime));}
	}

	function intervalosemana($fecha) {
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

	function modfecha($tipovar, $cant, $fecha, $tipoop="S") {
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

	function retornaIntervaloFechas($desde, $hasta) {
		while($desde!=$hasta) {
			$arr_fechas[] = $desde;
			$desde = dateAdd("d",1,$desde);
		}
		$arr_fechas[] = $desde;
		return $arr_fechas;
	}

	function formatea_hora_agenda($hora) {
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

	function formatea_hora_agenda24($hora) {
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

	function ultimoDiaMes($mes,$ano) {
		$ultimo_dia=28;
		while (checkdate($mes,$ultimo_dia + 1,$ano)) {
			$ultimo_dia++;
		}
		return $ultimo_dia;
	}

	function retornaMes($mes){
		$meses = array('1'=>'ENERO','2'=>'FEBRERO','3'=>'MARZO','4'=>'ABRIL','5'=>'MAYO','6'=>'JUNIO','7'=>'JULIO','8'=>'AGOSTO','9'=>'SEPTIEMBRE','10'=>'OCTUBRE','11'=>'NOVIEMBRE','12'=>'DICIEMBRE');
		return $meses[$mes];
	}

	function retornaAntiguedad($fecha_ingreso){
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
