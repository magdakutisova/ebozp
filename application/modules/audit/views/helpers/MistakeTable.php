<?php
class Zend_View_Helper_MistakeTable extends Zend_View_Helper_Abstract {
	
	public $workIndex = array();
	
	/**
	 * vraci instanci helperu
	 * 
	 * @return Zend_View_Helper_MistakeTable
	 */
	public function mistakeTable() {
		return $this;
	}
	
	/**
	 * vraci hlavicku tabulky
	 */
	public function header(array $config = array()) {
		$config = array_merge(array("bgColor" => "#ffffff"), $config);
		
		$bgColor = $config["bgColor"];
		
		return "<thead><tr style=\"border: 1px solid black;\" bgcolor=\"$bgColor\"><th>Kategorie</th><th>Podkategorie</th><th>Upřesnění</th><th>Pracoviště</th><th rowspan=\"2\">Akce</th></tr><tr><td colspan=\"2\">Neshoda</td><td colspan=\"2\">Návrh</td></tr></thead>";
	}
	
	public function mistake(Audit_Model_Row_AuditRecordMistake $mistake, array $config = array()) {
		// vytvoreni zakladni konfigurace a slouceni s predanou
		$baseConfig = array("classes" => array(), 
            "submitStatus" => $mistake->is_submited, 
            "actions" => array(), 
            "semaphore" => false, 
            "selector" => false, 
            "selected" => false, 
            "hashTable" => null,
            "subsidiaryRow" => true);
        
		$config = array_merge($baseConfig, (array) $config);
		
		// vygenerovani obsahu prvniho radku
		$buttons = array();
		
		foreach ($config["actions"] as $name => $label) {
			$buttons[] = $this->view->formButton($name, $label);
		}
		
		if ($config["selector"]) {
			$checked = $config["selected"] ? array("checked" => "checked") : array();
			
			$chBox = sprintf("<label style='white-space:pre' for='%s'>Odstraněno %s</label>", sprintf("mistake-%s-select", $mistake->id), $this->view->formCheckbox("mistake[$mistake->id][select]", 1, $checked));
			
			$buttons[] = $chBox;
		}
		
		// zapis tridy pracoviste
		$config["classes"][] = "filter-weight-" . $mistake->weight;
		
		// vyhodnoceni hash tabulky
		if ($config["hashTable"]) {
			// hash tabulka existuje, zapiseme hash tridy
			$config["classes"][] = $config["hashTable"]["categories"][$mistake->category];
			$config["classes"][] = $config["hashTable"]["subcategories"][$mistake->category][$mistake->subcategory];
			
			if ($mistake->workplace_id)
				$config["classes"][] = $config["hashTable"]["workplaces"][$mistake->workplace_name];
			else
				$config["classes"][] = "work-nowork";
		}
		
		// vygenerovani semaforu
		if ($config["semaphore"] !== false) {
			switch ($config["semaphore"]) {
				case 0:
					$colorId = 0;
					break;
					
				case 1:
					$colorId = 1;
					break;
					
				case 2:
				default:
					$colorId = 2;
			}
			
			$semaphore = "<div class='semaphore'></div><input type='hidden' name='semaphore_val' value='$colorId'>";
		} else {
			$semaphore = "";
		}
		
		$button = implode("", $buttons) . $this->view->formHidden("mistakeId", $mistake->id) . $this->view->formHidden("submitStatus", $config["submitStatus"]);
		$button .= sprintf("<input type='hidden' name='weight', value='%s'>", $mistake->weight);
		$button .= sprintf("<input type='hidden' name='subsidiary_id' value='%s'>", $mistake->subsidiary_id);
		
		// vyhodnoceni jmena pracoviste, pokud existuje
		if ($mistake->workplace_id) {
			if (isset($mistake->workplace_name)) {
				$workplaceName = $mistake->workplace_name;
			} else {
				$workplaceName = isset($this->workIndex[$mistake->workplace_id]) ? $this->workIndex[$mistake->workplace_id]->name : "?";
			}
		} else {
			$workplaceName = "-";
		}
		
		$columns = array(
				$this->_wrapToTd($mistake->category, "category"),
				$this->_wrapToTd($mistake->subcategory, "subcategory"),
				$this->_wrapToTd($mistake->concretisation),
				$this->_wrapToTd($workplaceName),
				$this->_wrapToTag($button . $semaphore, "td", array("rowspan" => 3, "width" => "50px"))
		);
		
		$row1 = $this->_wrapToTag(implode("", $columns), "tr", array());
		
		// vygenerovani obsahu druheho radku
		$columns = array(
				$this->_wrapToTd($mistake->mistake, null, 2, true),
				$this->_wrapToTd($mistake->suggestion, null, 2, true)
		);
		
		$row2 = $this->_wrapToTag(implode("", $columns), "tr", array());
        
        // slouceni a vraceni vysledku
		$content = $row1 . $row2;
        
        // pokud se ma zobrazit i nazev pobocky, pak se zobrazi
        if ($config["subsidiaryRow"])
            $content .= sprintf("<tr><td colspan='4'>%s, %s</td></tr>", $mistake->subsidiary_town, $mistake->subsidiary_street);
		
		return $this->_wrapToTag($content, "tbody", array("class" => implode(" ", $config["classes"])));
	}
	
	private function _wrapToTd($content, $inputName = null, $colspan = 1, $pre = false) {
		// sestaveni parametru
		$params = array();
		
		if ($colspan > 1) {
			$params["colspan"] = $colspan;
		}
		
		if ($pre) {
			$params["style"] = "word-wrapping: pre";
		}
		
		if (!is_null($inputName)) {
			$content .= sprintf("<input type='hidden' name='%s' value='%s'>", $inputName, htmlspecialchars($content));
		}
		
		return $this->_wrapToTag($content, "td", $params);
	}
	
	private function _wrapToTag($content, $tag, array $attrs) {
		$retVal = "<" . $tag;
		
		// zpracovani atributu
		$attrVals = array();
		
		foreach ($attrs as $name => $val) {
			$attrVals[] = $name . "=\"" . $val . "\"";
		}
		
		if ($attrVals) {
			$retVal .= " " . implode(" ", $attrVals);
		}
		
		$retVal .= ">$content</$tag>";
		
		return $retVal;
	}
}