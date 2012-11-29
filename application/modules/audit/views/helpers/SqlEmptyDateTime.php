<?php
class Zend_View_Helper_SqlEmptyDatetime extends Zend_View_Helper_Abstract {
	
	public function sqlEmptyDateTime($date) {
		return !strcmp("0000-00-00 00:00:00", $date);
	}
}