<?php
class Audit_Form_FormInstanceCreate extends Zend_Form {
	
	public function init() {
		$this->setName("form-instance-craeate");
		$this->setMethod(Zend_Form::METHOD_POST);
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
		
		$lastDecorator = array(
				"ViewHelper",
				array(array("data" => "HtmlTag"), array("tag" => "td", "class" => "element", "colspan" => 2)),
				array(array("row" => "HtmlTag"), array("tag" => "tr"))
		);
		
		$this->addElement("select", "id", array(
				"label" => "Formulář",
				"decorators" => $elementDecorator
		));
		
		$this->addElement("submit", "submit", array(
				"label" => "Vytvořit",
				"decorators" => $lastDecorator
		));
	}
	
	public function loadUnused(Audit_Model_Rowset_AuditsForms $usedForms) {
		$ids = array(0);
		
		foreach ($usedForms as $form) {
			$ids[] = $form->form_id;
		}
		
		// nacteni dat
		$tableForms = new Audit_Model_Forms();
		$forms = $tableForms->fetchAll($tableForms->getAdapter()->quoteInto("id not in (?)", $ids), "name");
		
		$element = $this->getElement("id");
		
		foreach ($forms as $form) {
			$element->addMultiOption($form->id, $form->name);
		}
		
		return $this;
	}
}