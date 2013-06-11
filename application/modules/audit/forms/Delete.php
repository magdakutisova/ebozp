<?php
class Audit_Form_Delete extends Zend_Form {
	
	public function init() {
		// nastaveni dat
		$this->setMethod(Zend_Form::METHOD_POST);
		$this->setElementsBelongTo("delete");
	
		$this->setAttrib("onsubmit", "return confirm('Skutečně smazat?')");
	
		// nastaveni dekoratoru
		$this->setDecorators(array(
				'FormElements',
				array('HtmlTag', array('tag' => 'table')),
				'Form',
		));
	
		$elementDecorator = array(
				'ViewHelper',
				array('Errors'),
				array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element', "openOnly" => true)),
				array('Label', array('tag' => 'td')),
				array(array('row' => 'HtmlTag'), array('tag' => 'tr', "openOnly" => true)),
		);
	
		$lastDecoratorClose = array(
				"ViewHelper",
				array(array("data" => "HtmlTag"), array("tag" => "td", "class" => "element", "closeOnly" => true)),
				array(array("row" => "HtmlTag"), array("tag" => "tr", "closeOnly" => true))
		);
	
		// zapis elementu
		$this->addElement("checkbox", "confirm", array("decorators" => $elementDecorator, "label" => "Skutečně smazat?", "required" => true, "value" => 0, "validators" => array(
				new Zend_Validate_Identical("1")
		)));
		
		$this->addElement("submit", "Smazat", array("decorators" => $lastDecoratorClose));
	}
}