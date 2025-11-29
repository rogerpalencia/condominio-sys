<?php
/* clase preparada por Jorge Dominguez Chavez,
// jodocha version 1.0
// Venezuela,2010
// Revisada Venezuela, 2024
*/

class NumeroALetras {

    private $unidades = array(
        '',
        'UNO ',
        'DOS ',
        'TRES ',
        'CUATRO ',
        'CINCO ',
        'SEIS ',
        'SIETE ',
        'OCHO ',
        'NUEVE ',
        'DIEZ ',
        'ONCE ',
        'DOCE ',
        'TRECE ',
        'CATORCE ',
        'QUINCE ',
        'DIECISEIS ',
        'DIECISIETE ',
        'DIECIOCHO ',
        'DIECINUEVE ',
        'VEINTE '
    );

    private $decenas = array(
        'VEINTI',
        'TREINTA ',
        'CUARENTA ',
        'CINCUENTA ',
        'SESENTA ',
        'SETENTA ',
        'OCHENTA ',
        'NOVENTA ',
        'CIEN '
    );

    private $centenas = array(
        'CIENTO ',
        'DOSCIENTOS ',
        'TRESCIENTOS ',
        'CUATROCIENTOS ',
        'QUINIENTOS ',
        'SEISCIENTOS ',
        'SETECIENTOS ',
        'OCHOCIENTOS ',
        'NOVECIENTOS '
    );

    public $conector = 'CON';

    public $apocope = false;

    public function toWords($number, $decimals = 2) {
        $this->checkApocope();
        $number = number_format($number, $decimals, '.', '');
        $splitNumber = explode('.', $number);
        $splitNumber[0] = $this->wholeNumber($splitNumber[0]);
        if (!empty($splitNumber[1])) {
            $splitNumber[1] = $this->convertNumber($splitNumber[1]);
        }
        return $this->glue($splitNumber);
    }

    public function toMoney($number, $decimals = 2, $currency = '', $cents = '') {
        $this->checkApocope();
        $number = number_format($number, $decimals, '.', '');
        $splitNumber = explode('.', $number);
        $splitNumber[0] = $this->wholeNumber($splitNumber[0]) . ' ' . mb_strtoupper($currency, 'UTF-8');
        if (!empty($splitNumber[1])) {
            $splitNumber[1] = $this->convertNumber($splitNumber[1]);
        }
        if (!empty($splitNumber[1])) {
            $splitNumber[1] .= ' ' . mb_strtoupper($cents, 'UTF-8');
        }
        return $this->glue($splitNumber);
    }

    public function toString($number, $decimals = 2, $whole_str = '', $decimal_str = '') {
        return $this->toMoney($number, $decimals, $whole_str, $decimal_str);
    }

    public function toInvoice($number, $decimals = 2,  $currency = '') {
        $this->checkApocope();
        $number = number_format($number, $decimals, '.', '');
        $splitNumber = explode('.', $number);
        $splitNumber[0] = $this->wholeNumber($splitNumber[0]);
        if (!empty($splitNumber[1])) {
            $splitNumber[1] .= '/100 ';
        } else {
            $splitNumber[1] = '00/100 ';
        }
        return $this->glue($splitNumber) . mb_strtoupper($currency, 'UTF-8');
    }

    private function checkApocope() {
        if ($this->apocope === true) {
            $this->unidades[1] = 'UN ';
        }
    }

    private function wholeNumber($number) {
        if ($number == '0') {
            $number = 'CERO ';
        } else {
            $number = $this->convertNumber($number);
        }
        return $number;
    }

    private function glue($splitNumber) {
        return implode(' ' . mb_strtoupper($this->conector, 'UTF-8') . ' ', array_filter($splitNumber));
    }

    private function convertNumber($number) {
        $converted = '';
        if (($number < 0) || ($number > 999999999)) {
            throw new ParseError("Wrong parameter number");
        }
        $numberStrFill = str_pad($number, 9, '0', STR_PAD_LEFT);
        $millones = substr($numberStrFill, 0, 3);
        $miles = substr($numberStrFill, 3, 3);
        $cientos = substr($numberStrFill, 6);
		if (intval($millones) > 0) {
            if ($millones == '001') {
                $converted .= 'UN MILLON ';
            } else if (intval($millones) > 0) {
                $converted .= sprintf('%sMILLONES ', $this->convertGroup($millones));
            }
        }
        if (intval($miles) > 0) {
            if ($miles == '001') {
                $converted .= 'MIL ';
            } else if (intval($miles) > 0) {
                $converted .= sprintf('%sMIL ', $this->convertGroup($miles));
            }
        }
        if (intval($cientos) > 0) {
            if ($cientos == '001') {
                $this->apocope === true ? $converted .= 'UN ' : $converted .= 'UNO ';
            } else if (intval($cientos) > 0) {
                $converted .= sprintf('%s ', $this->convertGroup($cientos));
            }
        }
        return trim($converted);
    }

    private function convertGroup($n) {
        $output = '';
        if ($n == '100') {
            $output = "CIEN ";
        } else if ($n[0] !== '0') {
            $output = $this->centenas[$n[0] - 1];
        }
        $k = intval(substr($n, 1));
        if ($k <= 20) {
            $output .= $this->unidades[$k];
        } else {
            if (($k > 30) && ($n[2] !== '0')) {
                $output .= sprintf('%sY %s', $this->decenas[intval($n[1]) - 2], $this->unidades[intval($n[2])]);
            } else {
                $output .= sprintf('%s%s', $this->decenas[intval($n[1]) - 2], $this->unidades[intval($n[2])]);
            }
        }
        return $output;
    }
}