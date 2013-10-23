<?php
class Audit_Form_AuditAuditorSubmit extends Zend_Form {
	
	public function init() {
		// nastaveni dat
		$this->setName("audit-auditor-submit");
		$this->setMethod(Zend_Form::METHOD_POST);
		$this->setElementsBelongTo("audit");
		
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
		
		// potvrzeni
		$this->addElement("checkbox", "confirm", array(
				"required" => true,
				"label" => "Uzavřít audit",
				"decorators" => $elementDecorator,
				"validators" => array(new Zend_Validate_InArray(array(1)))
		));
		
		// tlacitko odeslani
		$this->addElement("submit", "submit", array(
				"label" => "Uzavřít audit",
				"decorators" => $lastDecorator
		));
	}
}