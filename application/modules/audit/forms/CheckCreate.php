<?php
class Audit_Form_CheckCreate extends Audit_Form_Audit {
	
	public function init() {
		parent::init();
		
		// prenastaveni parametru formulare
		$this->getElement("done_at")->setLabel("Datum provedení prověrky");
		$this->setElementsBelongTo(array("check"));
		$this->setName("check");
	}
}