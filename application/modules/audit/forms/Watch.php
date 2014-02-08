<?php
class Audit_Form_Watch extends Zend_Form {
	
	public function init() {
		// nastaveni dat
		$this->setName("watch_create");
		$this->setMethod(Zend_Form::METHOD_POST);
		$this->setElementsBelongTo("watch");
		$this->setAction("/audit/watch/post");
		
		// nastaveni dekoratoru
		$this->setDecorators(array(
				'FormElements',
				array('HtmlTag', array('tag' => 'table')),
				'Form',
		));
		
		$elementDecorator = array(
				'ViewHelper',
				array('Errors'),
				array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element')),
				array('Label', array('tag' => 'td')),
				array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
		);
		
		$lastDecorator = array(
				"ViewHelper",
				array(array("data" => "HtmlTag"), array("tag" => "td", "class" => "element", "colspan" => 2)),
				array(array("row" => "HtmlTag"), array("tag" => "tr"))
		);
		
		// pole pro datum
		$this->addElement("text", "watched_at", array(
				"label" => "Provedeno dne",
				"decorators" => $elementDecorator,
				"validators" => array(
						array(
								"Regex",
								false,
								array(
										"pattern" => My_Filter_Date::PATTERN_CZ,
										"message" => "Špatný formát datumu"
										)
								)
				),
				"required" => true,
				"value" => Zend_Date::now()->get("dd. MM. y")
		));
		
		$this->addElement("select", "contactperson_id", array(
				"label" => "Zástupce klienta",
				"decorators" => $elementDecorator
		));
		
		// pole pro zacatek dohlidky
		$this->addElement("text", "time_from", array(
				"label" => "Začátek (čas)",
				"decorators" => $elementDecorator,
				"validators" => array(
						array(
								"Regex",
								false,
								array(
										"pattern" => "/^[0-9]{2}(:[0-9]{2}){1,2}$/",
										"messages" => "Špatný formát času"
								)
						)),
				"pattern" => "^[0-9]{2}(:[0-9]{2}){1,2}$"
		));
		
		// pole pro konec dohlidky
		$this->addElement("text", "time_to", array(
				"label" => "Konec (čas)",
				"decorators" => $elementDecorator,
				"validators" => array(
						array(
								"Regex",
								false,
								array(
										"pattern" => "/^[0-9]{2}(:[0-9]{2}){1,2}$/",
										"messages" => "Špatný formát času"
								)
						)),
				"pattern" => "^[0-9]{2}(:[0-9]{2}){1,2}$"
		));
		
		// pole pro jmeno dohlizitele
		$this->addElement("textarea", "guard_person", array(
				"label" => "Technik",
				"decorators" => $elementDecorator
		));
		
		// pole pro jmeno pobocky
		$this->addElement("text", "client_description", array(
				"label" => "Text u podpisu klienta",
				"decorators" => $elementDecorator
		));
		
		// další zástupce guardu
		$this->addElement("textarea", "other_guard", array(
				"label" => "Další zástupce G U A R D 7, v.o.s.",
				"decorators" => $elementDecorator
		));
		
		// další zástupce klienta
		$this->addElement("textarea", "other_client", array(
				"label" => "Další zástupce klienta",
				"decorators" => $elementDecorator
		));
		
		// zobrazeni neshod
		$this->addElement("checkbox", "display_mistakes", array(
				"label" => "Stávající neshody v protokolu",
				"decorators" => $elementDecorator
		));
        
        $this->addElement("checkbox", "display_mistakes_removed", array(
				"label" => "Odstraněné neshody v protokolu",
				"decorators" => $elementDecorator
		));
		
		// zobrazeni lhut
		$this->addElement("checkbox", "display_deadlines", array(
				"label" => "Propadlé hůlty v protokolu",
				"decorators" => $elementDecorator
		));
        
        $this->addElement("checkbox", "display_deadlines_close", array(
				"label" => "Lhůty před propadnutím v protokolu",
				"decorators" => $elementDecorator
		));
		
		$this->addElement("submit", "submit", array(
				"decorators" => $lastDecorator,
				"label" => "Uložit"
		));
	}
	
	public function setClientData($clientId, $subsidiaryId, $watchId = null) {
		// vytvoreni dotazovaciho retezce
		$query = "?clientId=$clientId&subsidiaryId=$subsidiaryId";
		
		if (!is_null($watchId)) $query .= "&watchId=" . $watchId;
		
		$this->setAction(
				$this->getAction() . $query
		);
		
		return $this;
	}
	
	public function setContacts($contacts) {
		$values = array("--- VYBERTE --");
		
		foreach ($contacts as $contact) {
			$values[$contact->id_contact_person] = $contact->name;
		}
		
		$this->_elements["contactperson_id"]->setMultiOptions($values);
		
		return $this;
	}
}