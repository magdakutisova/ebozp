<?php
class Audit_Model_Row_Audit extends Zend_Db_Table_Row_Abstract {
	
	/**
	 * vraci radek s auditorem
	 * 
	 * @return Zend_Db_Table_Row_Abstract
	 */
	public function getAuditor() {
		return $this->findParentRow("Application_Model_DbTable_User", "auditor");
	}
	
	/**
	 * vraci radek s klientem
	 * 
	 * @return Zend_Db_Table_Row_Abstract
	 */
	public function getClient() {
		return $this->findParentRow("Application_Model_DbTable_Client", "client");
	}
	
	/**
	 * vraci radek s koordinatorem
	 * 
	 * @return Zend_Db_Table_Row_Abstract
	 */
	public function getCoordinator() {
		return $this->findParentRow("Application_Model_DbTable_User", "coordinator");
	}
	
	/**
	 * vraci existujici farplany
	 * 
	 * @return Zend_Db_Table_Rowset_Abstract
	 */
	public function getFarplans() {
		return $this->findDependentRowset("Audit_Model_Farplans", "audit");
	}
	
	/**
	 * vraci instance formularu
	 * 
	 * @return Audit_Model_Rowset_AuditsForms
	 */
	public function getForms() {
		return $this->findDependentRowset("Audit_Model_AuditsForms", "audit");
	}
	
	/**
	 * vraci seznam neshod z auditu
	 * 
	 * @return Audit_Model_Rowset_AuditsRecordsMistakes
	 */
	public function getMistakes() {
		// sestaveni podminky a nacteni dat
		$tableMistakes = new Audit_Model_AuditsRecordsMistakes();
		$tableAssocs = new Audit_Model_AuditsMistakes();
		
		$nameAssocs = $tableAssocs->info("name");
		
		$subSelect = new Zend_Db_Select(Zend_Db_Table_Abstract::getDefaultAdapter());
		$subSelect->from($nameAssocs, array("mistake_id"))->where("audit_id = ?", $this->id);
		
		$where = array(
				"id in (?)" => new Zend_Db_Expr($subSelect->assemble())
				);
		
		return $tableMistakes->_findMistakes($where);
	}
	
	public function getMistakeAssocs() {
		return $this->findDependentRowset(new Audit_Model_AuditsMistakes(), "audit");
	}
	
	/**
	 * vraci neshody, ktere nejsou v auditu zahrnuty
	 */
	public function getSupplementMistakes() {
		// sestaveni podminky a nacteni dat
		$tableMistakes = new Audit_Model_AuditsRecordsMistakes();
		$tableAssocs = new Audit_Model_AuditsMistakes();
		
		$nameAssocs = $tableAssocs->info("name");
		
		$subSelect = new Zend_Db_Select(Zend_Db_Table_Abstract::getDefaultAdapter());
		$subSelect->from($nameAssocs, array("mistake_id"))->where("audit_id = ?", $this->id);
		
		$where = array(
				"id not in (?)" => new Zend_Db_Expr($subSelect->assemble()),
				"audit_audits_records_mistakes.subsidiary_id = ?" => $this->subsidiary_id,
				"audit_audits_records_mistakes.is_submited",
				"!audit_audits_records_mistakes.is_removed"
				);
		
		return $tableMistakes->_findMistakes($where);
	}
	
	/**
	 * vraci seznam zaznamu auditu
	 */
	public function getRecords() {
		return $this->findDependentRowset("Audit_Model_AuditsRecords", "audit");
	}
	
	/**
	 * vraci pobocku
	 * 
	 * @return Zend_Db_Table_Row_Abstract
	 */
	public function getSubsidiary() {
		return $this->findParentRow("Application_Model_DbTable_Subsidiary", "subsidiary");
	}
	
	/**
	 * nastavi nove zodpovedne a vraci novy set
	 * 
	 * @param array $people seznam novych zodpovednych
	 * @return Audit_Model_Rowset_AuditsResponsibiles
	 */
	public function setResponsibiles(array $people) {
		$tableResponsibiles = new Audit_Model_AuditsResponsibiles();
		
		return $tableResponsibiles->insertRecords($this, $people);
	}
	
	/**
	 * nastavi TS modified_at na aktualni TS
	 * 
	 * @return Audit_Model_Row_Audit
	 */
	public function touch() {
		$this->modified_at = new Zend_Db_Expr("NOW()");
		
		return $this;
	}
	
	/**
	 * oznaci audit jako potvrzeny technikem
	 * 
	 * @return Audit_Model_Row_Audit
	 */
	public function setDone() {
		$this->auditor_confirmed_at = new Zend_Db_Expr("NOW()");
		
		return $this;
	}
	
	/**
	 * nastavi datum a cas zpracovani auditu
	 * 
	 * @return Audit_Model_Row_Audit
	 */
	public function setProcessed() {
		$this->processed_at = new Zend_Db_Expr("NOW()");
		
		return $this;
	}
	
	/**
	 * nastavi TS a potvrzeni auditu a oznaci audit jako zamceny
	 * 
	 * @return Audit_Model_Row_Audit
	 */
	public function setConfirmed() {
		$this->confirmed_at = new Zend_Db_Expr("NOW()");
		$this->is_locked = 1;
		
		return $this;
	}
}