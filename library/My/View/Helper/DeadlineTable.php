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
		
		$retVal = $this->wrap($this->_wrapper, $content, array("class" => "multirow-table", "id" => "deadlinetable"));
		
		$configs = array_merge(array("form" => array()), $configs);
		
		if ($configs["form"]) {
			$retVal = $this->deadlineForm($retVal, $configs["form"]);
		}
		
		return $retVal;
	}
	
	/**
	 * vygeneruje jeden radek
	 * 
	 * @param unknown_type $deadline
	 * @param array $config
	 */
	public function deadline($deadline, array $config = array()) {
		$config = array_merge(array("noAction" => false, "subsidiaryRow" => true), $config);
		
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
		$buttons = $this->_generateButtons($deadline, $config) . sprintf("<input type='hidden' name='hiddenId' value='%s'>", $deadline["id"]);
		$w = $this->view;
        
        // vygenerovani jmena
        $name = "";
        
        if ($deadline["anonymous_obj_emp"]) {
            $name = "-";
        } elseif ($deadline["anonymous_obj_tech"]) {
            $name = "-";
        } elseif ($deadline["anonymous_obj_chem"]) {
            $name = "-";
        } else {
            $name = $deadline["name"];
        }
		
		$row = array(
				$this->wrap("td", $name . $this->_hidden("name", $name), array("rowspan" => 2)),
				$this->wrap("td", $deadline["kind"]. $this->_hidden("kind", $deadline["kind"])),
				$this->wrap("td", $deadline["specific"] . $this->_hidden("specific", $deadline["specific"])),
				$this->wrap("td", $type . $this->_hidden("type", $type)),
				$this->wrap("td", $deadline["period"] . $this->_hidden("period", $deadline["period"]))
		);
		
		if (!$config["noAction"]) {
			$row[] = $this->wrap("td", $buttons, array("rowspan" => 3));
		}
		
		$rowStr1 = $this->wrap("tr", implode("", $row));
		
        // vyhodnoceni zodpovedne osoby
        if ($deadline["anonymous_employee"]) {
            $resp = "Neurčený zaměstnanec";
        } elseif ($deadline["anonymous_guard"]) {
            $resp = "GUARD7";
        } else {
            $resp = $deadline["responsible_name"];
        }
        
		// druhy radek
		$row = array(
				$this->wrap("td", $this->_sqlDate($deadline["last_done"])),
				$this->wrap("td", $this->_sqlDate($deadline["next_date"])),
				$this->wrap("td", $deadline["note"]),
				$this->wrap("td", $resp),
		);
		
		$rowStr2 = $this->wrap("tr", implode("", $row));
        $content = $rowStr1 . $rowStr2;
        
        // pokud se ma zobrazit nazev pobocky, tak se zobrazi
        if ($config["subsidiaryRow"])
            $content .= sprintf("<tr><td colspan=\"5\">%s, %s</td></tr>", $deadline["subsidiary_town"], $deadline["subsidiary_street"]);
		
		// vyhodnoceni jestli je lhuta propadla
		if (@$deadline["is_done"]) {
			$opts = array("class" => "mistake-removed");
		} elseif (!$deadline["is_valid"]) {
			$opts = array("class" => "mistake-marked");
		} elseif ($deadline["invalid_close"]) {
			$opts = array("class" => "deadline-yellow");
		} else {
			$opts = array("class" => "deadline-ok");
		}
		
		return $this->wrap("tbody", $content, $opts);
	}
	
	public function deadlineForm($content, array $config = array()) {
		$config = array_merge(array("action" => ""), $config);
		
		$rows = array(
				sprintf("<tr><td>%s</td><td>%s hromadné zadání u vybraných lhůt</td></tr>", $this->view->formLabel("deadline[done_at]", "Naposledy provedeno"), $this->view->formText("deadline[done_at]")),
				sprintf("<tr><td colspan='2' style='display:none'>%s</td></tr>", $this->view->formLabel("deadline[comment]", "Komentář")),
				sprintf("<tr><td colspan='2' style='display:none'>%s</td></tr>", $this->view->formTextarea("deadline[comment]")),
				sprintf("<tr><td colspan='2'>%s</td></tr>", $this->view->formSubmit("deadline[submit]", "Uložit"))
				);
		
		$form = sprintf("<table>%s</table>", implode("", $rows));
		
		return sprintf("<form action='%s' method='post'>%s%s</form>", $config["action"], $content, $form);
	}
	
	private function _sqlDate($date) {
		if ($date == "0000-00-00" || !$date) {
			return "?";
		}
		
		return sprintf("%s. %s. %s", substr($date, 8, 2), substr($date, 5, 2), substr($date, 0, 4));
	}
	
	private function _generateButtons($deadline, array $config) {
		$config = array_merge(array("buttons" => array()), $config);
		$btnList = array();
		
		foreach ($config["buttons"] as $name => $c) {
			switch ($c["type"]) {
				case "checkbox":
					$btnList[] = sprintf("<label><input type='checkbox' name='%s' value='%s' />%s</label>", $name, $deadline["id"], $c["caption"]);
					break;
					
				case "link":
					// sestaveni url
					$url = $c["url"];
					$url = str_replace("%clientId", $deadline["client_id"], $url);
					$url = str_replace("%deadlineId", $deadline["id"], $url);
					
					$btnList[] = sprintf("<a href='%s'>%s</a>", $url, $c["caption"]);
					
					break;
				
				default:
                    if (!isset($c["url"])) {
                        $c["url"] = "";
                    } else {
                        // sestaveni url
                        $url = $c["url"];
                    	$url = str_replace("%clientId", $deadline["client_id"], $url);
                        $url = str_replace("%deadlineId", $deadline["id"], $url);
                        
                        $c["url"] = $url;
                    }
                    
					$btnList[] = sprintf("<button type='%s' name='%s' g7:url='%s'>%s</button>", $c["type"], $name, $c["url"], $c["caption"]);
			}
		}
		
		return implode("<br />", $btnList);
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
				"responsible_name" => "Provádí",
				"noAction" => false,
				"headBg" => ""
				), $config);
		
		// vygenerovani prvniho radku
		$row = array(
				$this->wrap("th", $config["name"], array("rowspan" => 2, "align" => "center", "valign" => "middle")),
				$this->wrap("th", $config["kind"], array("align" => "center", "valign" => "middle")),
				$this->wrap("th", $config["specific"], array("align" => "center", "valign" => "middle")),
				$this->wrap("th", $config["type"], array("align" => "center", "valign" => "middle")),
				$this->wrap("th", $config["period"], array("align" => "center", "valign" => "middle"))
				);
		
		if (!$config["noAction"]) {
			$row[] = $this->wrap("th", $config["actions"], array("rowspan" => 2, "align" => "center", "valign" => "middle"));
		}
		
		$rowStr1 = $this->wrap("tr", implode("", $row), array("bgcolor" => $config["headBg"]));
		
		// druhy radek
		$row = array(
				$this->wrap("th", $config["last_done"], array("align" => "center", "valign" => "middle")),
				$this->wrap("th", $config["next_date"], array("align" => "center", "valign" => "middle")),
				$this->wrap("th", $config["note"], array("align" => "center", "valign" => "middle")),
				$this->wrap("th", $config["responsible_name"], array("align" => "center", "valign" => "middle")),
		);
		
		$rowStr2 = $this->wrap("tr", implode("", $row), array("bgcolor" => $config["headBg"]));
		
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
	
	/**
	 * vygeneruje skryte pole
	 * 
	 * @param string $name jmeno
	 * @param string $value hodnota
	 * @return string
	 */
	private function _hidden($name, $value) {
		return sprintf("<input type='hidden' name='%s' value='%s' />", $name, $value);
	}
}