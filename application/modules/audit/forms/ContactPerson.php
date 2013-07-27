<?php
class Audit_Form_ContactPerson extends Zend_Form {
	
	public function init() {
		// nastaveni dat
		$this->setName("contact_create");
		$this->setMethod(Zend_Form::METHOD_POST);
		$this->setElementsBelongTo("newcontact");
		$this->setAction("/audit/watch/newcontact");
		
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
		
		$this->addElement("text", "name", array(
				"required" => true,
				"decorators" => $elementDecorator,
				"label" => "Jméno"
				));
		
		$this->addElement("text", "phone", array(
				"decorators" => $elementDecorator,
				"label" => "Telefon"
				));
		
		$this->addElement("text", "email", array(
				"decorators" => $elementDecorator,
				"label" => "E-mail",
				"validators" => array(new Zend_Validate_EmailAddress())
				));
		
		$this->addElement("submit", "submit", array(
				"label" => "Uložit",
				"decorators" => $lastDecorator
				));
	}
	
	public function setActionParams($watchId) {
		$query = "?watchId=$watchId";
		
		$this->setAction($this->getAction() . $query);
		
		return $this;
	}
}