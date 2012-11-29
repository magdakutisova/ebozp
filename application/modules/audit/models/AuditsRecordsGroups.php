<?php
class Audit_Model_AuditsRecordsGroups extends Zend_Db_Table_Abstract {
	
	protected $_name = "audit_audits_records_groups";
	
	protected $_primary = "id";
	
	protected $_sequence = true;
	
	protected $_referenceMap = array(
			"audit" => array(
					"columns" => "audit_id",
					"refTableClass" => "Audit_Model_Audits",
					"refColumns" => "id"
			)
	);
	
	protected $_rowClass = "Audit_Model_Row_AuditRecordGroup";
	
	protected $_rowsetClass = "Audit_Model_Rowset_AuditsRecordsGroups";
	
	/**
	 * vytvori novou skupinu
	 * 
	 * @param string $name jmeno skupiny
	 * @return Audit_Model_Row_AuditRecordGroup
	 */
	public function createGroup($name, Audit_Model_Row_Audit $audit) {
		$retVal = $this->createRow(array("name" => $name, "audit_id" => $audit->id));
		
		$retVal->save();
		
		return $retVal;
	}
	
	/**
	 * vraci zaznam podle id
	 * 
	 * @param int $id identifikacni cislo zaznamu
	 * @return Audit_Model_Row_AuditRecordGroup
	 */
	public function getById($id) {
		return $this->find($id)->current();
	}
}