<?php
class Audit_View_Helper_Farplan extends Zend_View_Helper_Abstract {
	
	/**
	 * 
	 * @param array $items data k zobrazeni
	 * @param array $config konfigurace
	 * @return string
	 */
	public function farplan(array $items = null, array $config = array()) {
		if ($items === null) return $this;
		
		$config = array_merge(array("form" => false), $config);
		
		$rendered = array();
		
		foreach ($items as $item) {
			$rendered[] = $this->category($item, $config);
		}
		
		$renderedStr = implode("", $rendered);
		
		// vyhodnoceni, jestli se ma vystup obalit do formulare
		if ($config["form"]) {
			$retVal = $this->form($renderedStr, $config);
		} else {
			$retVal = $renderedStr;
		}
		
		return $retVal;
	}
	
	public function category(array $data, array $config = array()) {
		$config = array_merge(array(
				"checkbox" => false
				), $config);
		
		// vygenerovani nadpisu kategorie
		if ($config["checkbox"]) {
			// vygenerovani jmena checkboxu
			$name = sprintf("category[%s]", $data["category"]->id);
			
			$head = sprintf("<h3>%s%s</h3>", $this->view->formCheckbox($name, 1, $data["category"]->is_selected ? array("checked" => "checked") : array()), $this->view->formLabel($name, $data["category"]->name));
		} else {
			$head = sprintf("<h3>%s</h3>", $data["category"]->name);
		}
		
		// vygenerovani textu
		$texts = $this->texts($data["texts"], $config);
		
		$retVal = $head . $texts;
		
		return $retVal;
	}
	
	public function form($content, array $config = array()) {
		$config = array_merge(array("action" => ""), $config);
		
		$retVal = sprintf("<form method='post' action='%s'>%s<p>%s</p></form>", $config["action"], $content, $this->view->formSubmit("farplan-submit", "Uložit"));
		
		return $retVal;
	}
	
	public function texts(array $data, array $config = array()) {
		$texts = array();
		
		foreach ($data as $item) {
			$texts[] = sprintf("<li>%s</li>", $item->text);
		}
		
		return sprintf("<ul>%s</ul>", implode("", $texts));
	}
}