<?php
class Deadline_Form_Done extends Zend_Form {
	
	public function init() {
		// nastaveni dat
		$this->setName("deadline-done");
		$this->setMethod(Zend_Form::METHOD_POST);
		$this->setElementsBelongTo("deadline");
		$this->setAction("/deadline/deadline/submit");
		
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
		
		$submitDecorator = array(
				"ViewHelper",
				array(array("data" => "HtmlTag"), array("tag" => "td", "class" => "element", "colspan" => 2)),
				array(array("row" => "HtmlTag"), array("tag" => "tr"))
		);
		
		$this->addElement("textarea", "note", array(
				"label" => "Poznámka",
				"required" => false,
				"decorators" => $elementDecorator
				));
		
		$this->addElement("submit", "submit", array(
				"label" => "Splnit lhůtu",
				"decorators" => $submitDecorator
				));
	}
}