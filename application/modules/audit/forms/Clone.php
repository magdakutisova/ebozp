<?php
class Audit_Form_Clone extends Zend_Form {
	
	public function init() {
		// nastaveni dat
		$this->setName("audit-clone");
		$this->setMethod(Zend_Form::METHOD_POST);
		$this->setElementsBelongTo("audit");
		
		// nastaveni dekoratoru
		$this->setDecorators(array(
				'FormElements',
				array('HtmlTag', array('tag' => 'table')),
				'Form',
		));
		
		$lastDecorator = array(
				"ViewHelper",
				array(array("data" => "HtmlTag"), array("tag" => "td", "class" => "element", "colspan" => 2)),
				array(array("row" => "HtmlTag"), array("tag" => "tr"))
		);
		
		$this->addElement("submit", "submit", array(
				"label" => "Klonovat",
				"decorators" => $lastDecorator
		));
	}
}