<?php
class Audit_Form_MistakeCreateAlone extends Audit_Form_MistakeCreate {
	
	public function init() {
		// pridani polozky na vyplneni pracoviste
		$elementDecorator = array(
				'ViewHelper'
		);
		
		$this->addElement("hidden", "workplace_id", array(
				"label" => "Pracoviště",
				"decorators" => $elementDecorator,
				"required" => true,
				"validators" => array(new Zend_Validate_Digits())
		));
		
		parent::init();
	}
}