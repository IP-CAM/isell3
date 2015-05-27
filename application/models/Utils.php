<?php

class Utils extends CI_Model {

    public function spellNumber($number, $units=null) {
	$hundreds_i = $this->getNumberPosition($number, 100, 1);
	$tens_i = $this->getNumberPosition($number, 10, 1);
	$ones_i = $this->getNumberPosition($number, 1, 1);
	if (!($hundreds_i || $tens_i || $ones_i)){
	    return '';
	}
	if( $units ){
	    if ($ones_i === 1 && $tens_i != 1){
		$unit = $units[0];
	    } else 
	    if ($ones_i > 1 && $ones_i < 5){
		$unit = $units[1];
	    } else {
		$unit = $units[2];
	    }
	} else {
	    $unit='';
	}

	$ones = array("", "одна", "дві", "три", "чотири", "п'ять", "шість", "сім", "вісім", "дев'ять");
	$tens = array("", "десять", "двадцять", "тридцять", "сорок", "п'ятдесят", "шістдесят", "сімдесят", "вісімдесят", "дев'яносто");
	$teens = array("", "одинадцять", "дванадцять", "тринадцять", "чотирнадцять", "п'ятнадцять", "шістнадцять", "сімнадцять", "вісімнадцять", "дев'ятнадцять");
	$hundreds = array("", "сто", "двісті", "триста", "чотириста", "п'ятсот", "шістсот", "сімсот", "вісімсот", "дев'ятсот");

	if ($tens_i == 1){
	    return "$hundreds[$hundreds_i] $teens[$ones_i] $unit";
	}
	else{
	    return "$hundreds[$hundreds_i] $tens[$tens_i] $ones[$ones_i] $unit";
	}
    }

    private function getNumberPosition($number, $position, $range = 1) {//DEPRECATED
	$number-=$position * 10 * $range * floor($number / $position / 10 / $range);
	return floor($number / $position);
    }

}
