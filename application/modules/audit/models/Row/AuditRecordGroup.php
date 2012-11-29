<?php
class Audit_Model_Row_AuditRecordGroup extends Zend_Db_Table_Row_Abstract {
	
	/**
	 * vraci seznam zaznamu vazanych ke skupine
	 * 
	 * @return Audit_Model_Rowset_AuditsRecords
	 */
	public function getRecords() {
		return $this->findDependentRowset("Audit_Model_AuditsRecords", "group");
	}
}