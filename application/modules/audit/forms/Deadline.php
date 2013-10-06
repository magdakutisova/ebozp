<?php
class Audit_Form_Deadline extends Deadline_Form_Done {
	
	public function init() {
		parent::init();
		
		// pridani zaskrtavaciho policka pro oznaceni lhuty jako splnene
		$elementDecorator = array(
				'ViewHelper',
				array('Errors'),
				array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element')),
				array('Label', array('tag' => 'td')),
				array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
		);
		
		// zapis aktivacniho policka
		$this->addElement("checkbox", "is_done", array(
				"label" => "Splnit lhůtu",
				"decorators" => $elementDecorator,
				"order" => 0
				));
		
		// deaktivace ostatnich poli
		$this->setActivated(false);
		
		// prejmenovani tlacitka odeslani
		$this->_elements["submit"]->setLabel("Uložit");
	}
	
	public function setActivated($activated = true) {
		foreach ($this->_elements as $name => $value) {
			if ($name != "is_done" && $name != "submit") {
				$value->setAttrib("disabled", $activated);
			}
		}
	}
}