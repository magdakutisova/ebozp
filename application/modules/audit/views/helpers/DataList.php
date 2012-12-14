<?php
class Zend_View_Helper_DataList extends Zend_View_Helper_Abstract {
	
	public function dataList($name, array $items) {
		$values = array();
		
		foreach ($items as $item) {
			$values[] = "<option value='$item' />";
		}
		
		$retVal = "<datalist id='$name'>" . implode("", $values) . "</datalist>";
		
		return $retVal;
	}
}