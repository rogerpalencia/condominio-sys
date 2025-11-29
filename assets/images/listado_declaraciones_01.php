<?php
if(session_status() !== PHP_SESSION_ACTIVE) session_start();

include "assets/libs/phpqrcode/qrlib.php";
include "core/PDO.class.php";
$conn = DB::getInstance();
include "fpdf.php"; 
include "core/funciones.php";
$func= new Funciones();
$f3 = $date ="";
$id = $_GET['id'];
$userid =  $_SESSION['userid'];

$sql = "SELECT * FROM aseo.inmuebles WHERE id_inmueble=$id";
$rs  = $conn->row($sql);

class PDF extends FPDF {
    function centrarTexto($pdf, $texto) {
        $pdf->Cell(0, 10, $texto, 0, 1, 'C');
    }
    function Header() {
        global $pdf, $texto;
        $x=10;
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetY($x+17);  
        $pdf->Image('assets/images/header.png', 10, 10, 200, 50);
        $pdf->SetY($x+48);  
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 10, 'A la fecha : ' . date('d/m/Y'), 0, 1, 'C');
        $pdf->SetFillColor(200, 220, 255);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
    }

    function cabeceraHorizontal($cabecera, $columnas, $t) {
        $ultimaPosicionY = $this->GetY();
        $this->SetXY(30, $ultimaPosicionY);

        $this->SetFont('Arial','B',11);
        $i = 0;
        $w = array_sum($columnas);
        $this->SetX(($this->GetPageWidth() - $w) / 2);
        $this->SetFillColor(200, 220, 255);
        foreach ($cabecera as $key => $texto) {
            $this->Cell($columnas[$i], 6, $texto, 0, 0, $t[$i], true);
            $i++;
        }
        $this->Ln();
        $this->SetX(($this->GetPageWidth() - $w) / 2);
        foreach ($columnas as $columna) {
            $this->Cell($columna, 0, '', 'B');
        }
        $this->Ln();
    }
    function datosHorizontal($datos, $columnas,$t) {
        $this->SetFont('Arial','',10);
        $i = 0;
        $w = array_sum($columnas);
        $this->SetX(($this->GetPageWidth() - $w) / 2);
        foreach($datos as $fila) {
            $this->Cell($columnas[$i], 6, $fila,0,0, $t[$i]);
            $i++;
        }
        $this->Ln();
    }
    function ResumenHorizontal($datos, $columnas,$t) {
        $w = array_sum($columnas);
        $this->SetX(($this->GetPageWidth() - $w) / 2);
        $this->SetFont('Arial','B',10);
        $this->SetFillColor(200, 220, 255);
        for($i=0;$i<count($datos);$i++)
            $this->Cell($columnas[$i], 10, $datos[$i], 'T', 0, $t[$i], true);
    }
}

$pdf = new PDF();
$pdf->AddPage('P');
$pdf->AliasNbPages();
$ancho_pagina = $pdf->GetPageWidth();
$pdf->SetAutoPageBreak(true, 20);
$pdf->SetFont("Arial", "",9);
$pdf->SetXY(85,70);
$pdf->Cell(40, 10, 'DATOS DEL INMUEBLE', 1, 2);
$pdf->SetFont("Arial", "",8);
$pdf->SetXY(75,40);   $pdf->Cell(40, 8,$func->properText ("ID INMUEBLE: ")); $pdf->Cell(20, 8, $id);
$pdf->SetXY(77,40);   $pdf->Cell(40, 8,$func->properText ("DIRECCIÓN: ")); $pdf->Cell(20, 8, $func->properText($rs['av_calle']));
$pdf->SetXY(79,40);   $pdf->Cell(20, 8,$func->properText ("No.      : ")); $pdf->Cell(20, 8, $func->properText($rs['no_inmueble']));
$pdf->SetXY(81,40);  $pdf->Cell(10, 8,$func->properText ("PISO     : ")); $pdf->Cell(20, 8, $func->properText($rs['piso']));

$miCabecera = array('Declaración','Contribuyente','Desde','Hasta','Estatus','Meses','Recargo','Desechos','Pagado');
$columnas = array(10,10,10,10,10,10,10,10,10);
$t = array('L','L','L','L','L','L','C','C','R');
$pdf->cabeceraHorizontal($miCabecera,$columnas,$t);
$ultimaPosicionY = $pdf->GetY();
$pdf->SetXY(30, $ultimaPosicionY);

$sql = "SELECT * FROM aseo.listado_declaraciones WHERE id_inmueble = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$id]);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

$datosPDF = array();
$fill = false;
foreach ($result as $row) {
    $v1 = $func->properText($row['Declaración']);
    $v1 .= $row['Contribuyente'];
    $v1 .= $row['Desde'];
    $v1 .= $row['Hasta'];
    $v1 .= $row['Estatus'];
    $v1 .= $row['Meses'];
    $v1 .= $row['Recargo'];
    $v1 .= $row['Desechos'];
    $v1 .= $row['Pagado'];
    $datosPDF[] = array($v1);
    if ($fill) {
        $pdf->SetFillColor(230, 230, 230);
    } else {
        $pdf->SetFillColor(255, 255, 255);
    }
    $fill = !$fill;
    $pdf->datosHorizontal($datos, $columnas, $t);
}

$txt = "Listado_declaraciones_pagadas_aseo_urbano";
$titulo_reporte = str_replace(' ','_', $txt. date('Y')).'_impreso_'.date('Y-m-d').'_'.time(); 
$pdf->Output('I', $titulo_reporte . '.pdf', '_blank');
