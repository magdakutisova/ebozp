<?php
class Audit_Model_Row_AuditMistake extends Zend_Db_Table_Row_Abstract {
	
	/**
	 * vraci audit
	 * 
	 * @return Audit_Model_Row_Audit
	 */
	public function getAudit() {
		return $this->findParentRow("Audit_Model_Audits", "audit");
	}
	
	/**
	 * vraci neshodu
	 * 
	 * @return Audit_Model_Row_AuditRecordMistake
	 */
	public function getMistake() {
		return $this->findParentRow("Audit_Model_AuditsRecordsMistakes", "mistake");
	}
}