<?php
class Deadline_Form_Filter extends Zend_Form {
	
	public function init() {
		
		$this->setName("deadlinefilter");
		
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
		
		$this->addElement("select", "kind", array(
				"label" => "Druh",
				"decorators" => $elementDecorator
				));
		
		$this->addElement("select", "specific", array(
				"label" => "Specifikace",
				"decorators" => $elementDecorator
				));
		
		$this->addElement("select", "type", array(
				"label" => "Forma",
				"decorators" => $elementDecorator,
				"multiOptions" => array(
						"---",
						Deadline_Form_Deadline::TYPE_OTHER => "Jiná",
						Deadline_Form_Deadline::TYPE_PRESENT => "Prezenční",
						Deadline_Form_Deadline::TYPE_ELEARNING => "Elearning"
						)
				));
		
		$this->addElement("select", "period", array(
				"label" => "Perioda",
				"decorators" => $elementDecorator
				));
		
		$this->addElement("select", "name", array(
				"label" => "Jméno",
				"decorators" => $elementDecorator
		));
		
		$this->addElement("submit", "submit", array(
				"label" => "Filtrovat",
				"decorators" => $submitDecorator
				));
	}
}