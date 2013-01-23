<?php
class Audit_Model_AuditsRecordsMistakes extends Zend_Db_Table_Abstract {
	
	const SUBMITED_UNUSED = 0;
	const SUBMITED_SUBMITED = 1;
	const SUBMITED_UNSUBMITED = 2;
	const SUBMITED_ALL = 4;
	
	const SUBMITED_VAL_UNUSED = 0;
	const SUBMITED_VAL_UNSUBMITED = 1;
	const SUBMITED_VAL_SUBMITED = 2;
	
	protected $_name = "audit_audits_records_mistakes";
	
	protected $_sequence = true;
	
	protected $_primary = "id";
	
	protected $_referenceMap = array(
			"client" => array(
					"columns" => "client_id",
					"refTableClass" => "Application_Model_DbTable_Client",
					"refColumns" => "id_client"
			),
			
			"workplace" => array(
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
			),
			
			"subsidiary" => array(
					"columns" => "subsidiary_id",
					"refTableClass" => "Application_Model_DbTable_Subsidiary",
					"refcolumns" => "subsidiary_id"
			)
	);
	
	protected $_rowClass = "Audit_Model_Row_AuditRecordMistake";
	
	protected $_rowsetClass = "Audit_Model_Rowset_AuditsRecordsMistakes";
	
	public function createMistake(
			Audit_Model_Row_AuditRecord $record = null,
			Zend_Date $willBeRemoved,
			$mistake,
			$suggestion,
			$comment,
			$hiddenComment,
			$category,
			$subcategory,
			$concretization = null,
			Audit_Model_Row_Audit $audit = null,
			$weight = null,
			Audit_Model_Row_Check $check = null
			) 
	{	
		// kontrola auditu a recordu
		if (is_null($check) && is_null($audit) && is_null($record)) throw new Zend_Db_Table_Exception("Audit, check and Record can not be null both");
		
		// kontrola auditu a pripadne jeho nacteni
		if (is_null($audit) && is_null($check)) {
			$audit = $record->getAudit();
		}
		
		// vyhodnoceni otazky a zavaznosti
		$question = $record ? $record->question : null;
		$weight = $weight ? $weight : $record->weight;
		
		// vytvoreni zaznamu o neshode
		$retVal = $this->createRow(array(
				"record_id" => $record ? $record->id : null,
				"audit_id" => $audit ? $audit->id : null,
				"check_id" => $check ? $check->id : null,
				"client_id" => $audit ? $audit->client_id : $check->client_id,
				"subsidiary_id" => $audit ? $audit->subsidiary_id : $check->subsidiary_id,
				"questionary_item_id" => $record ? $record->questionary_item_id : null,
				"weight" => $weight,
				"question" => $question,
				"category" => $category,
				"subcategory" => $subcategory,
				"concretisation" => $concretization,
				"mistake" => $mistake,
				"suggestion" => $suggestion,
				"comment" => $comment,
				"hidden_comment" => $hiddenComment,
				"notified_at" => $audit ? $audit->done_at : $check->done_at,
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
	
	/**
	 * vraci neshod dle id
	 * 
	 * @param int $id id neshody
	 * @return Audit_Model_Row_AuditRecordMistake
	 */
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
	
	/**
	 * vraci seznam vsech nezarazenych neshod
	 * 
	 * @param Audit_Model_Row_Audit $audit audit ke kteremu se vazou
	 * @param int $submitFilter filtrace
	 * @return Audit_Model_Rowset_AuditsRecordsMistakes
	 */
	public function getUngrouped(Audit_Model_Row_Audit $audit, $submitFilter = self::SUBMITED_ALL) {
		$where = array("audit_id = " . $audit->id);
		
		// vyhodnoceni filtrace
		if ($submitFilter) {
			switch ($submitFilter) {
				case self::SUBMITED_SUBMITED:
					$where[] = "is_submited";
					break;
					
				case self::SUBMITED_UNSUBMITED:
					$where[] = "!is_submited";
					break;
					
				default:
			}
		}
		
		return $this->fetchAll($where);
	}
}