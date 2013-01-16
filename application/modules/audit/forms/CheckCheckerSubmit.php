<?php
class Audit_Form_CheckCheckerSubmit extends Audit_Form_AuditAuditorSubmit {
	
	public function init() {
		parent::init();
		
		$this->getElement("confirm")->setLabel("Uzavřit prověrku a odeslat koordinátorovi");
		$this->getElement("submit")->setLabel("Uzavřít prověrku");
	}
}