<?php
session_start();

include_once "../config.php";
require('fpdf.php');
$lang_code_global = "Spanish";
include_once(ROOT_PATH.'language/'.$lang_code_global.'/lang_owner_list.php');
//$branch_id = (int)$_SESSION['objLogin']['branch_id'];
$branch_id = 1;

class PDF extends FPDF {
    var $widths;
    var $aligns;
    var $actual;

    function SetWidths($w) {
        $this->widths=$w;
    }

    function SetAligns($a) {
        $this->aligns=$a;
    }

    function Row($data) {
        $nb=0;
        for($i=0;$i<count($data);$i++)
            $nb=max($nb,$this->NbLines($this->widths[$i],$data[$i]));
        $h=8*$nb;
        $this->CheckPageBreak($h);
        for($i=0;$i<count($data);$i++) {
            $w=$this->widths[$i];
            $a=isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
            $x=$this->GetX();
            $y=$this->GetY();
            $this->Rect($x,$y,$w,$h);
            $this->MultiCell($w,8,$data[$i],0,$a,'true');
            $this->SetXY($x+$w,$y);
        }
        $this->Ln($h);
    }

    function CheckPageBreak($h) {
        if($this->GetY()+$h>$this->PageBreakTrigger)
            $this->AddPage($this->CurOrientation);
    }

    function NbLines($w,$txt) {

        $cw=&$this->CurrentFont['cw'];
        if($w==0)
            $w=$this->w-$this->rMargin-$this->x;
        $wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
        $s=str_replace("\r",'',$txt);
        $nb=strlen($s);
        if($nb>0 and $s[$nb-1]=="\n")
            $nb--;
        $sep=-1;
        $i = $j = $l = 0;
        $nl=1;
        while($i<$nb) {
            $c=$s[$i];
            if($c=="\n") {
                $i++;
                $sep=-1;
                $j=$i;
                $l=0;
                $nl++;
                continue;
            }
            if($c==' ')
                $sep=$i;
            $l+=$cw[$c];
            if($l>$wmax) {
                if($sep==-1) {
                    if($i==$j)
                    $i++;
                } else
                    $i=$sep+1;
                $sep=-1;
                $j=$i;
                $l=0;
                $nl++;
            } else
                $i++;
        }
        return $nl;
    }

    function Header() {
        $this->actual = date('D/M/Y');
        $title = 'SISTEMA DE CONDOMINIO';
        $subtitle = utf8_decode ('Urb. Fundación Maracay II, Primera Etapa') . $this->actual;
        global $subsubtitle;
        $this->Image('../img/logo.png',10,8,33);
        $this->SetFont('Arial','B',12);
        $w = $this->GetStringWidth($title)+6;
        $this->SetX((210-$w)/2);
        $this->Cell($w,9,$title,1,1,'C',true);
        $w = $this->GetStringWidth($subtitle)+6;
        $this->SetX((210-$w)/2);
        $this->Cell($w,9,$subtitle,1,1,'C',true);
        $w = $this->GetStringWidth($subsubtitle)+6;
        $this->SetX((210-$w)/2);
        $this->Cell($w,9,$subsubtitle,1,1,'C',true);
     }

     function Footer() {
        $this->SetY(-10);
        $this->SetFont('Arial','I',8);
        $this->Cell(0,10,utf8_decode ('Santa Rosa, Maracay, Aragua, Venezuela.'),1,0,'C');
        $this->Cell(0,10,utf8_decode ('Teléfonos: 58-412-8982851 Correo: fundacionmaracayetapa1@gmail.com'),1,0,'C');
     }
  
}

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->SetFont('Arial','',12);
$pdf->AddPage();
echo "holasss"; exit;
$pdf->SetMargins(20,20,20);
$pdf->Ln(10);
echo "aqui 4"; exit;
$pdf->SetWidths(array(40, 40, 40, 40, 40));
$pdf->SetFont('Arial','B',10);
$pdf->SetFillColor(29,29,29);
$pdf->SetTextColor(255);
$pdf->Row(array('#',$_data['add_new_form_field_text_1'], $_data['add_new_form_field_text_2'], $_data['add_new_form_field_text_4'], $_data['add_new_form_field_text_8']));
$result = $pdo->query("Select * from tbl_add_owner where branch_id = " . $branch_id . " order by o_apto asc"); 
$i = $n = 0;
foreach ($result as $row) {
    $n++;
    $pdf->SetFont('Arial','',9);
    if($i%2 == 1){
        $pdf->SetFillColor(181,175,173);
        $pdf->SetTextColor(0);
        $pdf->Row(array($n,$row['o_name'], $row['o_email'], $row['o_contact'], $row['o_apto']));
        $i++;
    } else {
        $pdf->SetFillColor(212,204,202);
        $pdf->SetTextColor(0);
        $pdf->Row(array($n,$row['o_name'], $row['o_email'], $row['o_contact'], $row['o_apto']));
        $i--;
    }
}
$pdf->Output('reporte.pdf','I');
?>
