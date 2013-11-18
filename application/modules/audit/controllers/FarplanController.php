<?php
class Audit_FarplanController extends Zend_Controller_Action {
	
	/**
	 * vytvori farplan z formulare a presune se na jeho editaci
	 */
	public function cloneAction() {
		// nacteni informaci
		$formId = $this->_request->getParam("formId", 0);
		$auditId = $this->_request->getParam("auditId", 0);
		
		// nacteni dat z databaze
		$tableForms = new Audit_Model_Forms();
		$tableAudits = new Audit_Model_Audits();
		
		$form = $tableForms->findById($formId);
		$audit = $tableAudits->getById($auditId);
		
		// vytvoreni farplanu
		$tableFars = new Audit_Model_Farplans();
		$farplan = $tableFars->cloneForm($form, $audit);
		
		$this->view->farplan = $farplan;
	}
	
	public function deleteAction() {
		
	}
	
	public function editAction() {
		
	}
	
	public function getAction() {
		
	}
	
	public function saveAction() {
		
	}
	
}