<?php
class Audit_Form_Form extends Zend_Form {
	
	public function init() {
		
		$this->setAction("/audit/form/post");
		$this->setMethod(Zend_Form::METHOD_POST);
		$this->setName("form-post");
		$this->setElementsBelongTo("form");
		
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
				"label" => "Jméno formuláře:",
				"required" => true,
				"decorators" => $elementDecorator
		);
		
		$this->addElement("text", "name", array_merge(array("validators" => array("NotEmpty"), "filters" => array("StringTrim")), $options));
		
		// skryte hodnoty
		unset($options["label"], $options["decorators"], $options["required"]);
		
		// reset
		$options["label"] = "Obnovit hodnoty";
		$options["decorators"] = $lastDecoratorOpen;
		
		// odeslat
		$options["label"] = "Uložit";
		$this->addElement("submit", "form-submit", $options);
		
		$this->addElement("hidden", "id", array("decorators" => $lastDecoratorClose));
	}
}