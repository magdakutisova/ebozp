<?php
class Zend_View_Helper_AuditInfo extends Zend_View_Helper_Abstract {
	
	public function auditInfo() {
		return $this;
	}
	
	public function header(Audit_Model_Row_Audit $audit, Zend_Db_Table_Row_Abstract $client, Zend_Db_Table_Row_Abstract $subsidiary) {
		$retVal = "<h1>";
		$retVal .= $audit->is_check ? "Prověrka" : "Audit";
		$retVal .= "</h1><h2>" . $client->company_name . " - " . $subsidiary->subsidiary_name . "</h2>";
		return $retVal;
	}
}