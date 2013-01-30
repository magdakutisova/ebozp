<?php
class Audit_Form_Audit extends Zend_Form {
	
	public function fillSelects() {
		// nactei koordinatoru
		$tableUsers = new Application_Model_DbTable_User();
		$coordinators = $tableUsers->fetchAll("role = " . My_Role::ROLE_COORDINATOR, "username");
		
		// zapis do formulare
		foreach ($coordinators as $coord) {
			$this->getElement("coordinator_id")->addMultiOption($coord->id_user, $coord->username);
		}
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
		$this->addElement("text", "responsibile_name", array(
				"required" => true,
				"decorators" => $elementDecorator,
				"label" => "Zodpovědná osoba*"
		));
		
		// datum provedeni auditu
		$this->addElement("text", "done_at", array(
				"decorators" => $elementDecorator,
				"label" => "Datum provedení auditu",
				"required" => true,
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
		
		// koordinator
		$this->addElement("select", "coordinator_id", array(
				"decorators" => $elementDecorator,
				"label" => "Koordinátor auditu"
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
}