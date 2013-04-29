<?php
class Audit_Model_AuditsReports extends Zend_Db_Table_Abstract {
	
	public $_name = "audit_audits_reports";
	
	public $_sequence = true;
	
	public $_primary = "id";
	
	protected $_rowClass = "Audit_Model_Row_AuditReport";
}