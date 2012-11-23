<?php
class Zend_View_Helper_Tree extends Zend_View_Helper_Abstract {
	
	private function _buildSpan($text, $class = null) {
		$retVal = "<span";
		
		// vyhodnoceni tridy
		if (is_string($class)) {
			$retVal .= " class='$class'";
		}
		
		$retVal .= ">" . $text . "</span>";
		
		return $retVal;
	}
	
	public function folderTitle($text, array $params = null) {
		// sestaveni parametru
		$params = (array) $params;
		
		$defaults = array(
				"open" => true,
				"rollerClass" => "tree-roller",
				"folderClass" => "tree-folder"
		);
		
		$params = array_merge($defaults, $params);
		
		// vygenerovani spanu se znakem otevreni, zavreni
		if ($params["open"])
			$rollerSpan = $this->folderOpen($params["rollerClass"]);
		else
			$rollerSpan = $this->folderClosed($params["rollerClass"]);
		
		$retVal = "<span class='" . $params["folderClass"] . "'>" . $rollerSpan . $text . "</span>";
		
		return $retVal;
	}
	
	public function folderClosed($class = null) {
		return $this->_buildSpan("+", $class);
	}
	
	public function folderOpen($class = null) {
		return $this->_buildSpan("-", $class);
	}
	
	public function item($text, array $options = null) {
		// nastaveni defaultnich parametru
		$options = (array) $options;
		$defaults = array(
				"itemClass" => ""
		);
		
		$options = array_merge($defaults, $options);
		
		// vygenerovani itemu
		$retVal = "<li class='" . $options["itemClass"] . "'>" . $text . "</li>";
		
		return $retVal;
	}
	
	public function items(array $items, array $options = null) {
		// vygenerovani vracene hodnoty
		$retVal = "";
		
		foreach ($items as $item) {
			$retVal .= $this->item($item, $options);
		}
		
		return $retVal;
	}
	
	public function tree($options = null) {
		if (is_null($options))
			return $this;
	}
}