<?php
class Deadline_Form_Category extends Zend_Form {
	
	public function init() {
		
		$this->setMethod(self::METHOD_POST);
		
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
		
		$this->addElement("text", "name", array(
				"label" => "Jméno",
				"required" => true,
				"decorators" => $elementDecorator
				));
		
		$this->addElement("text", "period", array(
				"label" => "Perioda",
				"required" => false,
				"decorators" => $elementDecorator,
				"filters" => array(
						array("Null")
						)
				));
		
		$this->addElement("text", "value", array(
				"label" => "Speciální hodnota",
				"required" => false,
				"decorators" => $elementDecorator,
				"filters" => array(
						array("Null")
						),
				"validators" => array(
						array("Digits")
						)
				));
		
		$this->addElement("submit", "Uložit", array(
				"label" => "Uložit",
				"decorators" => $submitDecorator
				));
	}
}