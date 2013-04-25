<?php
class Audit_ReportController extends Zend_Controller_Action {
	
	public function init() {
		$this->view->addHelperPath(APPLICATION_PATH . "/views/helpers");
		
		$this->view->headScript()->appendFile("/js/audit/report/report.js");
	}
	
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
		$this->view->client = $client;
		$this->view->coordinator = $coordinator;
		$this->view->auditor = $auditor;
		$this->view->subsidiary = $subsidiary;
	}
	
	public function loadAudit() {
		$auditId = $this->getRequest()->getParam("auditId", 0);
		$tableAudits = new Audit_Model_Audits();
		
		$audit = $tableAudits->getById($auditId);
		if (!$audit) throw new Zend_Exception("Audit #$auditId has not been found");
		
		return $audit;
	}
}