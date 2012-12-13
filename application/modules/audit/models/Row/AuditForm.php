<?php
class Audit_Model_Row_AuditForm extends Zend_Db_Table_Row_Abstract {
	
	/**
	 * vraci audit 
	 * 
	 * @return Audit_Model_Row_Audit
	 */
	public function getAudit() {
		return $this->findParentRow("Audit_Model_Audits", "audit");
	}
	
	/**
	 * vraci formular
	 * 
	 * @return Audit_Model_Row_Form
	 */
	public function getForm() {
		return $this->findParentRow("Audit_Model_Forms", "form");
	}
	
	/**
	 * vraci seznam prirazenych zaznamu
	 * 
	 * @return Audit_Model_Rowset_AuditsRecords
	 */
	public function getRecords() {
		return $this->findDependentRowset("Audit_Model_AuditsRecords");
	}
}