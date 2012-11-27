<?php
class Audit_Model_AuditsResponsibiles extends Zend_Db_Table_Abstract {
	
	protected $_name = "audit_audits_responsibiles";
	
	protected $_primary = "id";
	
	protected $_sequence = true;
	
	protected $_referenceMap = array(
			"audit" => array(
					"columns" => "audit_id",
					"refTableClass" => "Audit_Model_Audits",
					"refColumns" => "id"
			),
			
			"user" => array(
					"columns" => "user_id",
					"refTableClass" => "Application_Model_User",
					"refColumns" => "id_user"
			)
	);
	
	protected $_rowClass = "Audit_Model_Row_AuditResponsibile";
	
	protected $_rowsetClass = "Audit_Model_Rowset_AuditsResponsibiles";
	
	/**
	 * vraci seznam zodpovednych osob podle auditu
	 * 
	 * @param Audit_Model_Row_Audit $audit
	 * @return Audit_Model_Rowset_AuditsResponsibiles
	 */
	public function getByAudit(Audit_Model_Row_Audit $audit) {
		// sestaveni podminky
		$where = "audit_id = " . $this->getAdapter()->quote($audit->id);
		
		return $this->fetchAll($where);
	}
	
	/**
	 * nastavi nove zodpovedne osoby a vraci set s nimi
	 * 
	 * @param Audit_Model_Row_Audit $audit audit do ktereho se bude vkladat
	 * @param array $records nove zaznamy
	 * @return Audit_Model_Rowset_AuditsResponsibiles
	 */
	public function insertRecords(Audit_Model_Row_Audit $audit, array $records) {
		// smazani starych dat
		$adapter = $this->getAdapter();
		$auditId = $adapter->quote($audit->id);
		$where = "`audit_id` = " . $auditId;
		
		$this->delete($where);
		
		// kotrnoa, jeslti jsou nejake data k provedeni
		if (!$records) return;
		
		// priprava pro zapis dat
		$inserts = array();
		$template = array("audit_id" => $auditId, "user_id" => null, "name" => null);
		
		// prochazeni dat
		foreach ($records as $item) {
			$template["name"] = $adapter->quote($item["name"]);
			$template["user_id"] = isset($item["user_id"]) ? $item["user_id"] : new Zend_Db_Expr("NULL");
			
			// zapis radku
			$inserts[] = "(" . implode(",", $template) . ")";
		}
		
		// bazovy SQL prikaz
		$sql = "insert into " . $adapter->quoteIdentifier($this->_name) . " (`audit_id`, `user_id`, `name`) values " . implode(",", $inserts);
		$adapter->query($sql);
		
		return $this;
	}
}