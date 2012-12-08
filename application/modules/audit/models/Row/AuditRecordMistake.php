<?php
class Audit_Model_Row_AuditRecordMistake extends Zend_Db_Table_Row_Abstract {
	
	/**
	 * vraci seznam zaznamu prirazenych k zavade
	 * 
	 * @return Audit_Model_Rowset_AuditsRecords
	 */
	public function getAttachedRecords() {
		return $this->findDependentRowset("Audit_Model_AuditsRecords", "mistake");
	}
	
	/**
	 * vraci klienta
	 *
	 * @return Zend_Db_Table_Row_Abstract
	 */
	public function getClient() {
		return $this->findParentRow("Application_Model_DbTable_Client", "client");
	}
	
	/**
	 * vraci zaznam, ze ktereho neshoda vznikla
	 *
	 * @return Audit_Model_Row_AuditRecord
	 */	
	public function getRecord() {
		return $this->findParentRow("Audit_Model_AuditsRecords", "record");
	}
	
	/**
	 * vraci pobocku nebo NULL
	 *
	 * @return Zend_Db_Table_Row_Abstract
	 */
	public function getSubsidiary() {
		if ($this->subsidiary_id)
			return $this->findParentRow("Application_Model_DbTable_Subsidiary", "subsidiary");
		
		return null;
	}
	
	/**
	 * vraci pracoviste nebo NULL
	 * 
	 * @return Zend_Db_Table_Row_Abstract
	 */
	public function getWorkplace() {
		if ($this->workplace_id)
			return $this->findParentRow("Application_Model_DbTable_Workplace", "workplace");
		
		return null;
	}
	
	/**
	 * vraci nastaveni oznaceni neshody
	 * 
	 * @return bool
	 */
	public function isMarked() {
		return $this->is_marked;
	}
	
	/**
	 * vraci nastaveni odstraneni neshody
	 * 
	 * @return bool
	 */
	public function isRemoved() {
		return $this->is_removed;
	}
	
	/**
	 * nastavi oznaceni neshody
	 *
	 * @param bool $marked nove nastaveni
	 * @return Audit_Model_Row_AuditRecordMistake
	 */
	public function setMarked($marked) {
		$this->is_marked = $marked;
	}
	
	/**
	 * nastavi odstraneni neshody
	 * 
	 * @param bool $removed nove nastaveni
	 * @return Audit_Model_Row_AuditRecordMistake
	 */
	public function setRemoved($removed) {
		$this->is_removed = $removed;
	}
}