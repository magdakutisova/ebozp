<?php
class Zend_View_Helper_SqlDate extends Zend_View_Helper_Abstract {
	
	public function sqlDate($date) {
		$parts = explode(" ", $date);
		
		list ($year, $month, $day) = explode("-", $parts[0]);
		
		return "$day. $month. $year";
	}
}