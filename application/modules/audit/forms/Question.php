<?php
class Audit_Form_Question extends Zend_Form {
	
	public function init() {
		
		$this->setMethod(Zend_Form::METHOD_POST);
		$this->setName("form-post");
		$this->setElementsBelongTo("q");
		
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
		
		$lastDecorator = array(
				"ViewHelper",
				array(array("data" => "HtmlTag"), array("tag" => "td", "class" => "element", "colspan" => 2)),
				array(array("row" => "HtmlTag"), array("tag" => "tr"))
		);
		
		$this->addElement("textarea", "question", array("label" => "Otázka", "decorators" => $elementDecorator, "required" => true));
		$this->addElement("textarea", "farplan_text", array("label" => "Text pro farplan", "decorators" => $elementDecorator, "required" => true));
		$this->addElement("select", "weight", array("label" => "Závažnost", "multiOptions" => array(1 => 1, 2 => 2, 3 => 3), "decorators" => $elementDecorator));
		$this->addElement("text", "category", array("label" => "Kategorie", "required" => true, "decorators" => $elementDecorator));
		$this->addElement("text", "subcategory", array("label" => "Podkategorie", "required" => true, "decorators" => $elementDecorator));
		$this->addElement("text", "concretisation", array("label" => "Upřesnění", "decorators" => $elementDecorator));
		$this->addElement("textarea", "mistake", array("label" => "Neshoda", "decorators" => $elementDecorator, "required" => true));
		$this->addElement("textarea", "suggestion", array("label" => "Návrh řešení", "decorators" => $elementDecorator, "required" => true));
		$this->addElement("textarea", "mistake_comment", array("label" => "Komentář k neshodě", "decorators" => $elementDecorator, "required" => true));
		$this->addElement("submit", "submit", array("label" => "Uložit", "decorators" => $lastDecorator));
	}
}