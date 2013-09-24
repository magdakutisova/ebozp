<?php
class My_View_Helper_DeadlineTable extends Zend_View_Helper_Abstract {
	
	private $_wrapper = "table";
	
	public function deadlineTable($deadlines = null, array $configs = array()) {
		if (is_null($deadlines)) {
			return $this;
		}
		
		// vygenerovani hlavicky
		$header = $this->header($configs);
		
		// vygenerovani tela
		$deadlines = $this->deadlines($deadlines, $configs);
		
		$content = $header . implode("", $deadlines);
		
		return $this->wrap($this->_wrapper, $content, array("class" => "multirow-table", "id" => "deadlinetable"));
	}
	
	/**
	 * vygeneruje jeden radek
	 * 
	 * @param unknown_type $deadline
	 * @param array $config
	 */
	public function deadline($deadline, array $config = array()) {
		// vyhodnoceni typu
		switch ($deadline["type"]) {
			case Deadline_Form_Deadline::TYPE_ELEARNING:
				$type = "Elearning";
				break;
				
			case Deadline_Form_Deadline::TYPE_PRESENT:
				$type = "Prezenční";
				break;
				
			default:
				$type = "Jiná";
		}
		
		// vygenerovani prvniho radku
		$buttons = $this->_generateButtons($config) . sprintf("<input type='hidden' name='hiddenId' value='%s'>", $deadline["id"]);
		
		$row = array(
				$this->wrap("td", $deadline["name"], array("rowspan" => 2)),
				$this->wrap("td", $deadline["kind"]),
				$this->wrap("td", $deadline["specific"]),
				$this->wrap("td", $type),
				$this->wrap("td", $deadline["period"]),
				$this->wrap("td", $buttons, array("rowspan" => 2))
		);
		
		$rowStr1 = $this->wrap("tr", implode("", $row));
		
		// druhy radek
		$row = array(
				$this->wrap("td", $deadline["last_done"]),
				$this->wrap("td", $deadline["next_date"]),
				$this->wrap("td", $deadline["note"]),
				$this->wrap("td", $deadline["responsible_name"]),
		);
		
		$rowStr2 = $this->wrap("tr", implode("", $row));
		
		$content = $rowStr1 . $rowStr2;
		
		// vyhodnoceni jestli je lhuta propadla
		if (!$deadline["is_valid"]) {
			$opts = array("class" => "mistake-marked");
		} elseif ($deadline["invalid_close"]) {
			$opts = array("class" => "deadline-yellow");
		} else {
			$opts = array();
		}
		
		return $this->wrap("tbody", $content, $opts);
	}
	
	private function _generateButtons(array $config) {
		$config = array_merge(array("buttons" => array()), $config);
		$btnList = array();
		
		foreach ($config["buttons"] as $name => $c) {
			$btnList[] = sprintf("<button type='%s' name='%s'>%s</button>", $c["type"], $name, $c["caption"]);
		}
		
		return implode("", $btnList);
	}
	
	/**
	 * vygeneruje obsah tela tabulky lhut
	 * 
	 * @param array|Deadline_Model_Rowset_Deadlines $deadlines
	 * @param array $config
	 */
	public function deadlines($deadlines, array $config = array()) {
		// vygenerovani jednotlivych radku
		$rows = array();
		
		foreach ($deadlines as $item) {
			// kontrola typu lhuty
			if ($item instanceof stdClass) {
				$item = (array) $item;
			} elseif ($item instanceof Zend_Db_Table_Row_Abstract) {
				$item = $item->toArray();
			}
			
			$rows[] = $this->deadline($item, $config);
		}
		
		return $rows;
	}
	
	/**
	 * vraci header
	 */
	public function header(array $config = array()) {
		$config = array_merge(array(
				"name" => "Jméno",
				"kind" => "Druh",
				"specific" => "Specifikace",
				"type" => "Forma",
				"period" => "Perioda",
				"actions" => "Akce",
				"last_done" => "Naposledy provedeno",
				"next_date" => "Další provedení",
				"note" => "Poznámka",
				"responsible_name" => "Zodpovědná osoba"
				), $config);
		
		// vygenerovani prvniho radku
		$row = array(
				$this->wrap("th", $config["name"], array("rowspan" => 2)),
				$this->wrap("th", $config["kind"]),
				$this->wrap("th", $config["specific"]),
				$this->wrap("th", $config["type"]),
				$this->wrap("th", $config["period"]),
				$this->wrap("th", $config["actions"], array("rowspan" => 2))
				);
		
		$rowStr1 = $this->wrap("tr", implode("", $row));
		
		// druhy radek
		$row = array(
				$this->wrap("th", $config["last_done"]),
				$this->wrap("th", $config["next_date"]),
				$this->wrap("th", $config["note"]),
				$this->wrap("th", $config["responsible_name"]),
		);
		
		$rowStr2 = $this->wrap("tr", implode("", $row));
		
		$header = $rowStr1 . $rowStr2;
		
		return $this->wrap("thead", $header);
	}
	
	/**
	 * zabali obsah do wrapperu
	 * 
	 * @param string $content obsah k zabaleni
	 * @return string
	 */
	public function wrap($tag, $content, array $params = array()) {
		return sprintf("%s%s%s", $this->wrapper($tag, $params), $content, $this->wrapper($tag, array(), true));
	}
	
	/**
	 * vraci wrapper nebo uzavreni wrapperu
	 * 
	 * @param bool $close prepinac uzavreni
	 * @return string
	 */
	public function wrapper($tag, array $options = array(), $close = false) {
		// vyhodnoceni, zda se jedna o closeTag
		if ($close) {
			$retVal = sprintf("%s%s%s", "</", $tag, ">");
		} else {
			// vygenerovani seznamu parametru
			$parts = array($tag);
			
			foreach ($options as $name => $value) {
				$parts[] = sprintf("%s=\"%s\"", $name, $value);
			}
			
			$strParts = implode(" ", $parts);
			
			$retVal = sprintf("%s%s%s", "<", $strParts, ">");
		}
		
		return $retVal;
	}
}