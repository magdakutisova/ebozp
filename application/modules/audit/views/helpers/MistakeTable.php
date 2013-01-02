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
	public function header() {
		return "<thead><tr style=\"border: 1px solid black;\"><th>Kategorie</th><th>Podkategorie</th><th>Upřesnění</th><th>Pracoviště</th><th rowspan=\"2\">Akce</th></tr><tr><td colspan=\"2\">Neshoda</td><td colspan=\"2\">Návrh</td></tr></thead>";
	}
	
	public function mistake(Audit_Model_Row_AuditRecordMistake $mistake, array $actions, array $classes = null, $submitStatus = null) {
		// vygenerovani obsahu prvniho radku
		$buttons = array();
		
		if (is_null($classes)) {
			$classes = array();
		}
		
		// vyhodnoceni submit stavu
		if (is_null($submitStatus)) $submitStatus = $mistake->submit_status;
		
		foreach ($actions as $name => $label) {
			$buttons[] = $this->view->formButton($name, $label);
		}
		
		$button = implode("", $buttons) . $this->view->formHidden("mistakeId", $mistake->id) . $this->view->formHidden("submitStatus", $submitStatus);
		
		$columns = array(
				$this->_wrapToTd($mistake->category),
				$this->_wrapToTd($mistake->subcategory),
				$this->_wrapToTd($mistake->concretisation),
				$this->_wrapToTd($mistake->workplace_id ? (isset($this->workIndex[$mistake->workplace_id]) ? $this->workIndex[$mistake->workplace_id]->name : "?") : "-"),
				$this->_wrapToTag($button, "td", array("rowspan" => 2, "width" => "50px"))
		);
		
		$row1 = $this->_wrapToTag(implode("", $columns), "tr", array());
		
		// vygenerovani obsahu druheho radku
		$columns = array(
				$this->_wrapToTd($mistake->mistake, 2, true),
				$this->_wrapToTd($mistake->suggestion, 2, true)
		);
		
		$row2 = $this->_wrapToTag(implode("", $columns), "tr", array());
		
		// slouceni a vraceni vysledku
		$content = $row1 . $row2;
		
		return $this->_wrapToTag($content, "tbody", array("class" => implode(" ", $classes)));
	}
	
	private function _wrapToTd($content, $colspan = 1, $pre = false) {
		// sestaveni parametru
		$params = array();
		
		if ($colspan > 1) {
			$params["colspan"] = $colspan;
		}
		
		if ($pre) {
			$params["style"] = "word-wrapping: pre";
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