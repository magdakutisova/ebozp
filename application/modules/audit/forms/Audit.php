<?php
class Audit_Form_Audit extends Zend_Form {
	
	/**
	 * (non-PHPdoc)
	 * @see Zend_Form::setDefaults()
	 * @return Audit_Form_Audit
	 */
	public function setDefaults($defaults) {
		// kontrola a prepis datumu
		if (isset($defaults["done_at"])) {
			if (strpos($defaults["done_at"], "-")) {
				list($year, $month, $day) = explode("-", $defaults["done_at"]);
				$defaults["done_at"] = "$day. $month. $year";
			}
		}
		
		parent::setDefaults($defaults);
		
		return $this;
	}
	
	public function init() {
		// nastaveni dat
		$this->setName("audit-edit");
		$this->setMethod(Zend_Form::METHOD_POST);
		$this->setElementsBelongTo("audit");
		$this->setAction("/audit/audit/post");
		
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
		
		$lastDecoratorOpen = array(
				"ViewHelper",
				array(array("data" => "HtmlTag"), array("tag" => "td", "class" => "element", "colspan" => 2, "openOnly" => true)),
				array(array("row" => "HtmlTag"), array("tag" => "tr", "openOnly" => true))
		);
		
		$lastDecoratorClose = array(
				"ViewHelper",
				array(array("data" => "HtmlTag"), array("tag" => "td", "class" => "element", "colspan" => 2, "closeOnly" => true)),
				array(array("row" => "HtmlTag"), array("tag" => "tr", "closeOnly" => true))
		);
		
		// zodpovedne osoby
		$this->addElement("select", "contactperson_id", array(
				"required" => false,
				"decorators" => $elementDecorator,
				"label" => "Zástupce klienta"
		));
		
		// datum provedeni auditu
		$this->addElement("text", "done_at", array(
				"decorators" => $elementDecorator,
				"label" => "Datum provedení auditu",
				"required" => true,
				"value" => Zend_Date::now()->get("dd. MM. y"),
				"validators" => array(
						array(
								"Regex",
								false,
								array(
										"pattern" => "/^([0-9]{2}\. ){2}[0-9]{4}$/",
										"messages" => "Špatný formát datumu"
								)
						)
				)
		));
		
		// audit nebo proverka
		$this->addElement("select", "is_check", array(
				"decorators" => $elementDecorator,
				"label" => "Audit / Prověrka",
				"multiOptions" => array("Audit", "Prověrka")
		));
		
		// skryta pole, tlacitka a tak
		$this->addElement("hidden", "subsidiary_id", array(
				"decorators" => $lastDecoratorOpen
		));
		
		$this->addElement("submit", "submit", array(
				"decorators" => $lastDecoratorClose,
				"label" => "Provést audit"
		));
	}
	
	public function setContacts($contacts) {
		$items = array("" => "---Vyberte---");
		
		foreach ($contacts as $contact) {
			$items[$contact->id_contact_person] = $contact->name;
		}
		
		$this->_elements["contactperson_id"]->setMultiOptions($items);
	}
    
    public function enableCloning() {
        $this->addElement("checkbox", "copy_old", array(
            "label" => "Klonovat minulý audit/prověrku",
            "value" => 0,
            "order" => 3,
            "decorators" => array(
				'ViewHelper',
				array('Errors'),
				array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element')),
				array('Label', array('tag' => 'td')),
				array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            )
        ));
    }
}