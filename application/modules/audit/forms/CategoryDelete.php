<?php
class Audit_Form_CategoryDelete extends Zend_Form {
	
	public function init() {
		// nastaveni dat
		$this->setName("category-edit");
		$this->setMethod(Zend_Form::METHOD_POST);
		$this->setElementsBelongTo("category");
		$this->setAction("/audit/category/delete");
		
		$this->setAttrib("onsubmit", "return confirm('Skutečně smazat kategorii neshod?')");
		
		// nastaveni dekoratoru
		$this->setDecorators(array(
				'FormElements',
				array('HtmlTag', array('tag' => 'table')),
				'Form',
		));
		
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
		
		// zapis elementu
		$this->addElement("hidden", "id", array("decorators" => $lastDecoratorOpen));
		$this->addElement("submit", "Smazat", array("decorators" => $lastDecoratorClose));
	}
}