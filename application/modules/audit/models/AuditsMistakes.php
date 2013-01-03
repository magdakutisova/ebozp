<?php
class Audit_Model_AuditsMistakes extends Zend_Db_Table_Abstract {
	
	protected $_name = "audit_audits_mistakes";
	
	protected $_primary = array("audit_id", "mistake_id");
	
	protected $_sequence = false;
	
	protected $_referenceMap = array(
			"audit" => array(
					"columns" => "audit_id",
					"refTableClass" => "Audit_Model_Audits",
					"refColumns" => "id"
			),
			
			"mistake" => array(
					"columns" => "mistake_id",
					"refTableClass" => "Audit_Model_AuditsRecordsMistakes",
					"refColumns" => "id"
			)
	);
	
	protected $_rowClass = "Audit_Model_Row_AuditMistake";
	
	protected $_rowsetClass = "Audit_Model_Rowset_AuditsMistakes";
	
	/**
	 * vytvori novy zaznam v tabulce
	 * 
	 * @param Audit_Model_Row_Audit $audit audit
	 * @param Audit_Model_Row_AuditRecordMistake $mistake neshoda
	 * @param unknown_type $status vychozi stav
	 * @return Audit_Model_Row_AuditMistake
	 */
	public function createAssoc(Audit_Model_Row_Audit $audit, Audit_Model_Row_AuditRecordMistake $mistake, $status = 0) {
		$retVal = $this->createRow(array(
				"audit_id" => $audit->id,
				"mistake_id" => $mistake->id,
				"status" => $status
		));
		
		$retVal->save();
		
		return $retVal;
	}
	
	/**
	 * smaze neodeslane neshody z auditu
	 * 
	 * @param Audit_Model_Row_Audit $audit audit
	 * @return Audit_Model_AuditsMistakes
	 */
	public function deleteUnsubmited(Audit_Model_Row_Audit $audit) {
		// sestaveni podminky
		$where = array(
				"audit_id = " . $audit->id,
				"submit_status = 0"
		);
		
		$this->delete($where);
		
		return $this;
	}
	
	/**
	 * vraci seznam asociaci dle auditu
	 * 
	 * @param Audit_Model_Row_Audit $audit audit
	 * @return Audit_Model_Rowset_AuditsMistakes
	 */
	public function getByAudit(Audit_Model_Row_Audit $audit) {
		return $this->fetchAll("audit_id = " . $audit->id);
	}
	
	/**
	 * vraci seznam asociaci dle neshody
	 * 
	 * @param Audit_Model_Row_AuditRecordMistake $mistake neshoda
	 * @return Audit_Model_Rowset_AuditsMistakes
	 */
	public function getByMistake(Audit_Model_Row_AuditRecordMistake $mistake) {
		return $this->fetchAll("mistake_id = " . $mistake->id);
	}
}