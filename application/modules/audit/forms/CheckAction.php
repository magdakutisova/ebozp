<?php
class Audit_Form_CheckAction extends Zend_Form {
	public function init() {
		// nastaveni dat
		$this->setName("mistakeaction");
		$this->setMethod(Zend_Form::METHOD_POST);
		$this->setElementsBelongTo("mistake");
		$this->setAction("/audit/audit/post");
		
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
		
		$this->addElement("select", "action", array(
				"decorators" => $elementDecorator,
				"label" => "Akce",
				"multiOptions" => array(
						Audit_Model_ChecksMistakes::DO_NOTHING => "Žádná akce",
						Audit_Model_ChecksMistakes::DO_REMOVE => "Označit jako odstraněné",
						Audit_Model_ChecksMistakes::DO_MARK => "Označit jako kritické"
				)
		));
		
		$this->addElement("submit", "submit", array(
				"decorators" => $lastDecorator,
				"label" => "Uložit"
		));
	}
}