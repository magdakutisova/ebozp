<?php
class Audit_Form_MistakeCreateSubsidiarySelect extends Zend_Form {
	
	public function init() {
		
		// nastaveni dat
		$this->setName("mistake-post");
		$this->setMethod(Zend_Form::METHOD_POST);
		$this->setElementsBelongTo("mistake");
		
		// nastaveni dekoratoru
		$this->setDecorators(array(
				'FormElements',
				array('HtmlTag', array('tag' => 'table')),
				'Form',
		));
		
		// pridani polozky na vyplneni pracoviste
		$elementDecorator = array(
				'ViewHelper',
				array('Errors'),
				array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element')),
				array('Label', array('tag' => 'td')),
				array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
		);
		
		$lastDecorator = array(
				'ViewHelper',
				array('Errors'),
				array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element', "colspan" => 2)),
				array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
		);
		
		$this->addElement("select", "workplace_id", array(
				"label" => "Pracoviště",
				"decorators" => $elementDecorator,
				"required" => true
		));
		
		$this->addElement("submit", "submit", array(
				"label" => "Pokračovat",
				"decorators" => $lastDecorator
		));
	}
}