<?php
class Audit_Model_Row_AuditReport extends Zend_Db_Table_Row_Abstract {
	
	public function getItems() {
		$tableItems = new Audit_Model_AuditsReportsProgresitems();
		return $tableItems->fetchAll("report_id = " . $this->_data["id"], "id");
	}
}