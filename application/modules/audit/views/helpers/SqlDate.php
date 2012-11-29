<?php
class Zend_View_Helper_SqlDate extends Zend_View_Helper_Abstract {
	
	public function sqlDate($date) {
		list ($year, $month, $day) = explode("-", $date);
		
		return "$day. $month. $year";
	}
}