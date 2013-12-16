<?php
class Audit_Model_AuditsProgresitems extends Zend_Db_Table_Abstract {
	
	protected $_name = "audit_audits_progresitems";
	
	protected $_primary = "id";
	
	protected $_sequence = true;
	
	protected $_referenceMap = array(
			"audit" => array(
					"columns" => "audit_id",
					"refTableClass" => "Audit_Model_Audits",
					"refColumns" => "id"
			)
	);
	
	protected $_rowClass = "Audit_Model_Row_AuditProgresitem";
	
	protected $_rowsetClass = "Audit_Model_Rowset_AuditsProgresitems";
}