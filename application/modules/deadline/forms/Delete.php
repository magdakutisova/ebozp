<?php 
class Deadline_Form_Delete extends Zend_Form {
	
	public function init() {
		// nastaveni dat
		$this->setName("deadlinedelete");
		$this->setMethod(Zend_Form::METHOD_POST);
		$this->setElementsBelongTo("deadline");
		
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
				"onsubmit" => "return confirm('Skutečně smazat lhůtu?');"
		));
		
		$this->addElement("submit", "submit", array(
				"label" => "Smazat lhůtu",
				"decorators" => $lastDecorator
		));
	}
}