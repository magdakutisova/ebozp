<?php
class Audit_Form_Category extends Zend_Form {
	
	public function init() {
		
		// nastaveni dat
		$this->setName("category-edit");
		$this->setMethod(Zend_Form::METHOD_POST);
		$this->setElementsBelongTo("category");
		
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
		
		// jmeno kagegorie
		$options = array(
				"label" => "Jméno kategorie",
				"required" => true,
				"decorators" => $elementDecorator
		);
		
		$this->addElement("text", "name", array_merge(array("validators" => array("NotEmpty"), "filters" => array("StringTrim")), $options));
		
		// skryte hodnoty
		unset($options["label"], $options["decorators"], $options["required"]);
		
		// reset
		$options["label"] = "Obnovit hodnoty";
		$options["decorators"] = $lastDecoratorOpen;
		
		$this->addElement("reset", "category-reset", $options);
		
		// odeslat
		$options["label"] = "Uložit";
		$options["decorators"] = array("ViewHelper");
		$this->addElement("submit", "category-submit", $options);
		
		$this->addElement("hidden", "id", array("decorators" => array("ViewHelper")));
		
		// id predka
		$this->addElement("hidden", "parent_id", array("decorators" => $lastDecoratorClose));
		
	}
}