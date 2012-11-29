<?php
class Audit_Model_Row_AuditRecord extends Zend_Db_Table_Row_Abstract {
	
	/**
	 * vraci chybu generovanou ze zaznamu
	 * pokud chyba neexistuje, vraci NULL
	 * 
	 * @return Audit_Model_Row_AuditRecordMistake
	 */
	public function getMistake() {
		return $this->findDependentRowset("Audit_Model_AuditsRecordsMistakes", "record")->current();
	}
}