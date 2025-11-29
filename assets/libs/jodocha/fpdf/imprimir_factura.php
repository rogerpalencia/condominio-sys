<?php

define('FPDF_FONTPATH','font/');
require('mysql_table.php');
include("comunes.php");
include ("../conectar.php");
include ("../funciones/fechas.php"); 
include ("Numbers/Words.php");


$pdf=new PDF();
$pdf->Open();
$pdf->AddPage();
if ($fondofac=="SI") $pdf->Header($imagenfac,0,0,205,295);

$codfactura=$_GET["codfactura"];
  
$consulta = "Select * from facturas,clientes where facturas.codfactura='$codfactura' and facturas.codcliente=clientes.codcliente";
$resultado = mysql_query($consulta, $conexion);
$lafila=mysql_fetch_array($resultado);

   $fecha = implota($lafila["fecha"]);
	$dia1=substr($fecha,0,2);
	$mes1=substr($fecha,3,2);
	$mes1=mes($mes1);
	$ano1=substr($fecha,6,4);
	
	$pdf->SetFillColor(255,255,255);
   $pdf->SetTextColor(18,18,239);
   $pdf->SetDrawColor(0,0,0);
   $pdf->SetLineWidth(.2);
   $pdf->SetFont('Arial','B',11);	
   $pdf->Ln(30);
	$pdf->Cell(115);
	$pdf->Cell(10,4,$codfactura,0,0,'L',0);
		
	$pdf->Ln(4);
	$pdf->Cell(20);
//	$pdf->Cell(42,4,$CiudadActual,0,0,'L',0);
	$pdf->Cell(12,4,$dia1,0,0,'L',0);
	$pdf->Cell(19,4,$mes1,0,0,'L',0);
	$pdf->Cell(10,4,$ano1,0,0,'L',0);
	
	$pdf->Ln(8);
	
	$pdf->Cell(30);
   $pdf->Cell(125,4,$lafila["nombre"],0,0,'L',0);
   $fecha = implota($lafila["fecha"]);
	$pdf->Ln(6);
    	
	$codigoprovincia=$lafila["codprovincia"];
	$consulta="select * from provincias where codprovincia='$codigoprovincia'";
	$query=mysql_query($consulta);
	$row=mysql_fetch_array($query);
	$pdf->Cell(40);
	$pdf->Cell(125,4,$lafila["codpostal"] . " - " . $lafila["direccion"] . " - " . $row["nombreprovincia"],0,0,'L',0);
   $pdf->Cell(40,4,$lafila["localidad"],0,0,'L',0);
   $pdf->Ln(6);
   $pdf->Cell(100);
   $pdf->Cell(125,4,$lafila["telefono"] . "            " . $lafila["nif"],0,0,'L',0);
   $pdf->Ln(7);		
	
   $pdf->Cell(140);
//	$pdf->Ln(6);
	
	// Consultamos las condiciones de pago
	$codfpago=$lafila["codformapago"]; // Esta es la forma de pago del cliente en la tabla de clientes
	$consulta="select * from formapago where codformapago='$codfpago'";
	$query2=mysql_query($consulta, $conexion);
	$row2=mysql_fetch_array($query2);
	$pdf->Cell(28);
//	$pdf->Cell(30,4,$row2["nombrefp"],0,0,'L',0);
	$pdf->Ln(10);
	
    //ahora mostramos las lneas de la factura
				
	$consulta2 = "Select * from factulinea where codfactura='$codfactura' order by numlinea";
   $resultado2 = mysql_query($consulta2, $conexion);
    
	$contador=1;
	while ($row=mysql_fetch_array($resultado2)) {
	  $pdf->Cell(1);
	  $contador++;
	  
	  $codarticulo=mysql_result($resultado2,$lineas,"codigo");
	  $codfamilia=mysql_result($resultado2,$lineas,"codfamilia");
	  $cantidad = sprintf("%d",mysql_result($resultado2,$lineas,"cantidad"));

	  $pdf->Cell(5);	  
	  $pdf->Cell(05,4,$cantidad,0,0,'R');	
	  	  
	  $sel_articulos="SELECT * FROM articulos WHERE codarticulo='$codarticulo' AND codfamilia='$codfamilia'";
	  $rs_articulos=mysql_query($sel_articulos);
	  	  
	  $acotado = substr(mysql_result($rs_articulos,0,"descripcion"), 0, 45);
	  $pdf->Cell(10);
	  $pdf->Cell(134,4,$acotado,0,0,'L');
	  	  
	  $precio2= number_format(mysql_result($resultado2,$lineas,"precio"),2,".",",");	  
	  $pdf->Cell(20);
	  $pdf->Cell(17,4,$precio2,0,0,'R');
	  
	  if (mysql_result($resultado2,$lineas,"dcto")==0) {
	  		$pdf->Cell(10,4,"",0,0,'C');
	  } else { 
			$pdf->Cell(15,4,mysql_result($resultado2,$lineas,"dcto") . " %",0,0,'C');
	  }
	  
	  $importe2= number_format(mysql_result($resultado2,$lineas,"importe"),2,".",",");	  
	  
	  $pdf->Cell(20,4,$importe2,0,0,'R');
	  $pdf->Ln(5);	

	  //vamos acumulando el importe
	  $importe=$importe + mysql_result($resultado2,$lineas,"importe");
	  $contador=$contador + 1;
	  $lineas=$lineas + 1;	  
	};
	
	// Parametro global Cantidad de filas en el detalle
	while ($contador<$FilasDetalleFactura) {
	  $pdf->Cell(1);
      $pdf->Cell(25,4,"",0,0,'C');
      $pdf->Ln(5);	
	  $contador=$contador +1;
	}
		  
	//Calculamos los valores del final de la factura
   $importe4=number_format($importe,2,".",",");	

	$ivai=$lafila["iva"];
	$impo=$importe*($ivai/100);
	$impo=sprintf("%01.2f", $impo); 
	$total=$importe+$impo; 
	$total=sprintf("%01.2f", $total);
	$impo=number_format($impo,2,".",",");	

   $total=sprintf("%01.2f", $total);
	$total2= number_format($total,2,".",",");	

	//Calculamos de numero a palabras
	$nw = new Numbers_Words();
		
	$pdf->SetY(135);
	$pdf->Cell(33);
	
	$decimales = explode(".",$total);	
	$pdf->Cell(35,4,strtoupper($nw->toWords($decimales[0], "es") . " con " . $decimales[1] . "/100 Bs. F."),0,0,'L',0);
	
	$pdf->Ln(2);		
	$pdf->Cell(160);
   $pdf->Cell(32,4,$importe4,0,0,'R',0);
	$pdf->Ln(8);

	$pdf->Cell(153);
	$pdf->Cell(04,4,$lafila["iva"] ,0,0,'R',0);
	$pdf->Cell(35,4,$impo,0,0,'R',0);	
	$pdf->Ln(8);
		
	$pdf->Cell(160);
	$pdf->Cell(32,4,$total2,0,0,'R',0);
	$pdf->Ln(4);
	
   @mysql_free_result($resultado); 
   @mysql_free_result($query);
	@mysql_free_result($resultado2); 
	@mysql_free_result($query3);
	  
	header('Content-type: application/pdf');
	$pdf->Output();
?>