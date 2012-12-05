<?php
class Audit_Form_MistakeDelete extends Zend_Form {
	
	public function init() {
		// nastaveni dat
		$this->setName("mistake-delete");
		$this->setMethod(Zend_Form::METHOD_POST);
		$this->setElementsBelongTo("mistake");
		
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
		
		$this->setOptions(array(
				"onsubmit" => "return confirm('Skutečně smazat neshodu?');"
		));
		
		$this->addElement("submit", "submit", array(
				"label" => "Smazat neshodu",
				"decorators" => $lastDecorator
		));
	}
}