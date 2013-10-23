<?php
class Audit_Form_AuditFill extends Audit_Form_Audit {
	
	public function init() {
		parent::init();
		
		$this->removeElement("submit");
		
		// nastaveni dat
		$this->setName("audit-fill");
		$this->setMethod(Zend_Form::METHOD_POST);
		$this->setElementsBelongTo("audit");
		$this->setAction("/audit/audit/put");
		
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
		
		// poznamka
		$this->addElement("textarea", "progress_note", array(
				"label" => "Cíle a průběh auditu",
				"decorators" => $elementDecorator
		));
		
		// shrnuti
		$this->addElement("textarea", "summary", array(
				"label" => "Zjištění z auditu*",
				"decorators" => $elementDecorator,
				"validators" => array("NotEmpty"),
				"required" => true
		));
		
		$this->getElement("close");
		
		// skryte hodnoty
		$this->addElement("hidden", "id", array(
				"decorators" => $lastDecoratorOpen,
				"requied" => true
		));
		
		$this->addElement("hidden", "content", array(
				"decorators" => array(array("ViewHelper"))
		));
		
		// odeslani
		$this->addElement("submit", "submit", array(
				"label" => "Uložit",
				"decorators" => $lastDecoratorClose
		));
	}
}