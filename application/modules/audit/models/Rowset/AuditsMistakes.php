<?php
class Audit_Model_Rowset_AuditsMistakes extends Zend_Db_Table_Rowset_Abstract {
	
	/**
	 * vraci seznam auditu v seznamu
	 * 
	 * @return Audit_Model_Rowset_Audits
	 */
	public function getAudits() {
		// sestaveni seznamu id auditu
		$auditIds = array(0);
		
		foreach ($this as $item) {
			$auditIds[] = $item->audit_id;
		}
		
		$tableAudit = new Audit_Model_Audits();
		
		return $tableAudit->find($auditIds);
	}
	
	/**
	 * vraci seznam neshod v seznamu
	 * 
	 * @return Audit_Model_Rowset_AuditsRecordsMistakes
	 */
	public function getMistakes() {
		// sestaveni seznamu id auditu
		$mistakeIds = array(0);
		
		foreach ($this as $item) {
			$mistakeIds[] = $item->mistake_id;
		}
		
		$tableMistakes = new Audit_Model_AuditsRecordsMistakes();
		
		return $tableMistakes->find($mistakeIds);
	}
	
}