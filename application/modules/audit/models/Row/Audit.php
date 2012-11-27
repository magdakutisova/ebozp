<?php
class Audit_Model_Row_Audit extends Zend_Db_Table_Row_Abstract {
	
	/**
	 * vraci radek s auditorem
	 * 
	 * @return Zend_Db_Table_Row_Abstract
	 */
	public function getAuditor() {
		return $this->findParentRow("Application_Model_User", "auditor");
	}
	
	/**
	 * vraci radek s klientem
	 * 
	 * @return Zend_Db_Table_Row_Abstract
	 */
	public function getClient() {
		return $this->findParentRow("Application_Model_Client", "client");
	}
	
	/**
	 * vraci radek s koordinatorem
	 * 
	 * @return Zend_Db_Table_Row_Abstract
	 */
	public function getCoordinator() {
		return $this->findParentRow("Application_Model_User", "coordinator");
	}
	
	/**
	 * vraci seznam zodpovednych ze strany klienta
	 * 
	 * @return Audit_Model_Rowset_AuditsResponsibiles
	 */
	public function getResponsibiles() {
		return $this->findDependentRowset("Audit_Model_Rowset_AuditsResponsibiles", "audit");
	}
	
	/**
	 * vraci pobocku
	 * 
	 * @return Zend_Db_Table_Row_Abstract
	 */
	public function getSubsidiary() {
		return $this->findParentRow("Application_Model_Subsidiary", "subsidiary");
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