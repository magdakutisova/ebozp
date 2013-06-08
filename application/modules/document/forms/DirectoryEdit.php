<?php
class Document_Form_DirectoryEdit extends Zend_Form {
	
	public function init() {
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
				"decorators" => $elementDecorator,
				"required" => true
		));
		
		$this->addElement("select", "subsidiary_id", array(
				"label" => "Pobočka",
				"decorators" => $elementDecorator
		));
		
		$this->addElement("submit", "submit", array(
				"label" => "Uložit",
				"decorators" => $submitDecorator
		));
	}
	
	public function fillSelect($subsidiaries) {
		$values = array("0" => "----");
		
		foreach ($subsidiaries as $sub) {
			$values[$sub->id_subsidiary] = $sub->subsidiary_name;
		}
		
		$this->_elements["subsidiary_id"]->setMultiOptions($values);
		
		return $this;
	}
}