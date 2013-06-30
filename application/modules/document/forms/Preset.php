<?php
class Document_Form_Preset extends Document_Form_Documentation {
	
	public function init() {
		parent::init();
		
		$this->setName("preset");
		
		$elementDecorator = array(
				'ViewHelper',
				array('Errors'),
				array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element')),
				array('Label', array('tag' => 'td')),
				array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
		);
		
		$this->removeElement("subsidiary_id");
		
		$this->addElement("checkbox", "is_general", array(
				"decorators" => $elementDecorator,
				"label" => "Obecná dokuemntace",
				"order" => 1
		));
	}
}