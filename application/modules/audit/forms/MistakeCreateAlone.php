<?php
class Audit_Form_MistakeCreateAlone extends Audit_Form_MistakeCreate {
	
	public function init() {
		// pridani polozky na vyplneni pracoviste
		$elementDecorator = array(
				'ViewHelper',
				array('Errors'),
				array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element')),
				array('Label', array('tag' => 'td')),
				array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
		);
		
		$this->addElement("select", "workplace_id", array(
				"label" => "Pracoviště",
				"decorators" => $elementDecorator,
				"required" => true
		));
		
		parent::init();
	}
}