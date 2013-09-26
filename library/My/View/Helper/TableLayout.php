<?php
class My_View_Helper_TableLayout extends Zend_View_Helper_Abstract {
	
	public function tableLayout() {
		return $this;
	}
	
	public function row($name, $value, array $config = array()) {
		$config = array_merge(array(
				"first" => array(), 
				"second" => array(), 
				"row" => array()
				), $config);
		
		// sestaveni parcialnich dat
		$first = $this->cell($name, $config["first"]);
		$second = $this->cell($value, $config["second"]);
		
		// sestaveni radku
		$params = $this->_assembleParams($config["row"]);
		
		return sprintf("<tr %s>%s%s</tr>", $params, $first, $second);
	}
	
	public function cell($content, array $config = array()) {
		// sestaveni parametru
		$params = $this->_assembleParams($config);
		
		// navrat bunky
		return sprintf("<td %s>%s</td>", $params, $content);
	}
	
	private function _assembleParams(array $paramList) {
		// sestaveni parametru
		$params = array();
		
		foreach ($paramList as $key => $val) {
			$params[] = sprintf("%s=\"%s\"", $key, addslashes($val));
		}
		
		$retVal = implode(" ", $params);
		
		return $retVal;
	}
}