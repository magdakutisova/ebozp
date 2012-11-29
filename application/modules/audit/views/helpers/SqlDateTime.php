<?php
class Zend_View_Helper_SqlDateTime extends Zend_View_Helper_Abstract {
	
	public function sqlDateTime($sqlDateTime) {
		list($date, $time) = explode(" ", $sqlDateTime);
		
		$retVal = $this->view->sqlDate($date) . " " . $time;
		
		return $retVal;
	}
}