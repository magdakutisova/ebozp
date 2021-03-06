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
				"label" => "Cíle",
				"decorators" => $elementDecorator
		));
		
		// shrnuti
		$this->addElement("textarea", "summary", array(
				"label" => "Zhodnocení prověrky/auditu",
				"decorators" => $elementDecorator,
				"validators" => array("NotEmpty"),
				"required" => true
		));
		
		$this->getElement("close");
		
		$this->addElement("hidden", "content", array(
				"decorators" => array(array("ViewHelper"))
		));
		
		// zobrazeni neshod
		$this->addElement("checkbox", "display_mistakes", array(
				"label" => "Stávající neshody ve zprávě",
				"decorators" => $elementDecorator
		));
        
        $this->addElement("checkbox", "display_mistakes_removed", array(
				"label" => "Odstraněné neshody ve zprávě",
				"decorators" => $elementDecorator
		));
		
		// zobrazeni lhut
		$this->addElement("checkbox", "display_deadlines", array(
				"label" => "Propadlé lhůty ve vzprávě",
				"decorators" => $elementDecorator
		));
        
        $this->addElement("checkbox", "display_deadlines_close", array(
				"label" => "Lhůty před propadnutím ve zprávě",
				"decorators" => $elementDecorator
		));
		
		// skryte hodnoty
		$this->addElement("hidden", "id", array(
				"decorators" => $lastDecoratorOpen,
				"requied" => true
		));
		
		// odeslani
		$this->addElement("submit", "submit", array(
				"label" => "Uložit",
				"decorators" => $lastDecoratorClose
		));
	}
}