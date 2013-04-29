<?php
class Audit_Model_AuditsReportsProgresitems extends Zend_Db_Table_Abstract {
	
	protected $_name = "audit_audits_reports_progresitems";
	
	protected $_primary = "id";
	
	protected $_sequence = true;
	
	protected $_refereceMap = array(
			"report" => array(
					"columns" => "report_id",
					"refTableClass" => "Audit_Model_AuditsReports",
					"refColumns" => "id"
			)
	);
	
	protected $_rowClass = "Audit_Model_Row_AuditReportProgresitem";
	
	protected $_rowsetClass = "Audit_Model_Rowset_AuditsReportsProgresitems";
}