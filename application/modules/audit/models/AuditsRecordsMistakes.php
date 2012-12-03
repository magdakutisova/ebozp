<?php
class Audit_Model_AuditsRecordsMistakes extends Zend_Db_Table_Abstract {
	
	protected $_name = "audit_audits_records_mistakes";
	
	protected $_sequence = true;
	
	protected $_primary = "id";
	
	protected $_referenceMap = array(
			"client" => array(
					"columns" => "client_id",
					"refTableClass" => "Application_Model_DbTable_Client",
					"refColumns" => "id_client"
			),
			
			"workpace" => array(
					"columns" => "workplace_id",
					"refTableClass" => "Application_Model_DbTable_Workplace",
					"refColumns" => "id_workplace"
			),
			
			"record" => array(
					"columns" => "record_id",
					"refTableClass" => "Audit_Model_AuditsRecords",
					"refColumns" => "id"
			),
			
			"audit" => array(
					"columns" => "audit_id",
					"refTableClass" => "Audit_Model_Audits",
					"refColumns" => "id"
			)
	);
	
	protected $_rowClass = "Audit_Model_Row_AuditRecordMistake";
	
	protected $_rowsetClass = "Audit_Model_Rowset_AuditsRecordsMistakes";
	
	public function createMistake(
			Audit_Model_Row_AuditRecord $record,
			Zend_Date $willBeRemoved,
			$mistake,
			$suggestion,
			$comment,
			$hiddenComment,
			$category,
			$subcategory,
			$concretization = null,
			Audit_Model_Row_Audit $audit = null
			) 
	{	
		// kontrola auditu a pripadne jeho nacteni
		if (is_null($audit)) {
			$audit = $record->getAudit();
		}
		
		// vytvoreni zaznamu o neshode
		$retVal = $this->createRow(array(
				"record_id" => $record->id,
				"audit_id" => $audit->id,
				"client_id" => $audit->client_id,
				"subsidiary_id" => $audit->subsidiary_id,
				"questionary_item_id" => $record->questionary_item_id,
				"weight" => $record->weight,
				"question" => $record->question,
				"category" => $category,
				"subcategory" => $subcategory,
				"concretisation" => $concretization,
				"mistake" => $mistake,
				"suggestion" => $suggestion,
				"comment" => $comment,
				"hidden_comment" => $hiddenComment,
				"notified_at" => $audit->done_at,
				"will_be_removed_at" => $willBeRemoved->get("y-MM-dd"),
				"responsibile_name" => ""
				
		));
		
		$retVal->save();
		
		return $retVal;
	}
	
	public function getByAudit(Audit_Model_Row_Audit $audit) {
		return $this->fetchAll("audit_id = " . $audit->id);
	}
	
	public function getByClient(Zend_Db_Table_Row_Abstract $client) {
		return $this->fetchAll("client_id = " . $client->id_client);
	}
	
	public function getById($id) {
		return $this->find($id)->current();
	}
	
	public function getByItem($itemId, Zend_Db_Table_Row_Abstract $client, Zend_Db_Table_Row_Abstract $subsidiary = null) {
		// vygenerovani podminky
		$where = array(
				"questionary_item_id = " . $itemId,
				"client_id = " . $client->id_client
		);
		
		if ($subsidiary) {
			$where[] = "subsidiary_id = " . $subsidiary->id_subsidiary;
		}
		
		return $this->fetchAll($where);
	}
	
	public function getBySubsidiary(Zend_Db_Table_Row_Abstract $subsidiary, $order, $actualsOnly = true) {
		$where = "subsidiary_id = " . $subsidiary->id_subsidiary;
		
		if ($actualsOnly) $where .= " !is_removed";
		
		return $this->fetchAll($where, $order);
	}
}