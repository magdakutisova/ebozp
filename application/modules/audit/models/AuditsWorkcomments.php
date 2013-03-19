<?php
class Audit_Model_AuditsWorkcomments extends Zend_Db_Table_Abstract {
	
	protected $_name = "audit_audits_workcomments";
	
	protected $_sequence = false;
	
	protected $_primary = array("audit_id", "workplace_id");
	
	protected $_referenceMap = array(
			"audit" => array(
					"columns" => "audit_id",
					"refTableClass" => "Audit_Model_Audits",
					"refColumns" => "id"
			),
			
			"workplace" => array(
					"columns" => "workplace_id",
					"refTableClass" => "Application_Model_DbTable_Workplace",
					"refColumns" => "id_workplace"
			)
	);
	
	protected $_rowsetClass = "Audit_Model_Rowset_AuditsWorkcomments";
	
	protected $_rowClass = "Audit_Model_Row_AuditWorkcomment";
	
	public function getByAudit($audit) {
		if ($audit instanceof Audit_Model_Row_Audit) $audit = $audit->id;
		
		return $this->fetchAll("audit_id = " . $this->getAdapter()->quote($audit));
	}
}