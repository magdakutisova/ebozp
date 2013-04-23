<?php
class Audit_ReportController extends Zend_Controller_Action {
	
	/**
	 * prvnotni vytvoreni z auditu
	 */
	public function createAction() {
		$audit = $this->loadAudit();
		
		// nacteni nacionalii
		$client = $audit->getClient();
		$subsidiary = $audit->getSubsidiary();
		$coordinator = $audit->getCoordinator();
		$auditor = $audit->getAuditor();
		
		$this->view->audit = $audit;
	}
	
	public function loadAudit() {
		$auditId = $this->getRequest()->getParam("auditId", 0);
		$tableAudits = new Audit_Model_Audits();
		
		$audit = $tableAudits->getById($auditId);
		if (!$audit) throw new Zend_Exception("Audit #$auditId has not been found");
		
		return $audit;
	}
}