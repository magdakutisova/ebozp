<?php
class Audit_Form_Order extends Zend_Form {
	
	public function init() {
		
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
		
		$this->addElement("textarea", "comment", array(
				"label" => "Komentář",
				"required" => false,
				"decorators" => $elementDecorator
				));
		
		$this->addElement("checkbox", "is_finished", array(
				"label" => "Vyřízeno",
				"required" => false,
				"decorators" => $elementDecorator
				));
		
		$this->addElement("submit", "submit", array(
				"label" => "Uložit",
				"required" => false,
				"decorators" => $elementDecorator
				));
	}
}