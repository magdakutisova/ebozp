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
			Audit_Model_Row_Watch $watch = null
			) 
	{	
		// kontrola auditu a recordu
		if (is_null($watch) && is_null($audit) && is_null($record)) throw new Zend_Db_Table_Exception("Audit, check and Record can not be null both");
		
		// kontrola auditu a pripadne jeho nacteni
		if (is_null($audit) && is_null($watch)) {
			$audit = $record->getAudit();
		}
		
		// vyhodnoceni otazky a zavaznosti
		$question = $record ? $record->question : null;
		$weight = $weight ? $weight : $record->weight;
		
		// vytvoreni zaznamu o neshode
		$retVal = $this->createRow(array(
				"record_id" => $record ? $record->id : null,
				"audit_id" => $audit ? $audit->id : null,
				"watch_id" => $watch ? $watch->id : null,
				"client_id" => $audit ? $audit->client_id : $watch->client_id,
				"subsidiary_id" => $audit ? $audit->subsidiary_id : $watch->subsidiary_id,
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
				"notified_at" => $audit ? $audit->done_at : $watch->watched_at,
				"will_be_removed_at" => $willBeRemoved->get("y-MM-dd"),
				"responsibile_name" => ""
				
		));
		
		$retVal->save();
		
		return $retVal;
	}
	
	public function getByAudit(Audit_Model_Row_Audit $audit) {
		return $this->fetchAll(array("audit_id = " . $audit->id, "is_submited"));
	}
	
	public function getByClient(Zend_Db_Table_Row_Abstract $client, $filter, array $otherFilter = array()) {
		$where = array(
				"$this->_name.is_submited", 
				"$this->_name.client_id = ?" => $client->id_client
				);
		
		switch ($filter) {
			case 1:
				// pouze aktualni
				$where[] = "!is_removed";
				break;
		
			case 2:
				$where[] = "is_removed";
				break;
		}
        
        $where = array_merge($where, $otherFilter);
		
		return $this->_findMistakes($where);
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
				"client_id = " . $client->id_client,
				"is_submited"
		);
		
		if ($subsidiary) {
			$where[] = "subsidiary_id = " . $subsidiary->id_subsidiary;
		}
		
		return $this->fetchAll($where);
	}
	
	public function getByWatch(Audit_Model_Row_Watch $watch) {
		$tableAssocs = new Audit_Model_WatchesMistakes();
		$nameAssocs = $tableAssocs->info("name");
		
		return $this->_findMistakes(
				array("id in (select mistake_id from $nameAssocs where watch_id = ?)" => $watch->id),
				array("$nameAssocs.set_removed")
				);
	}
	
	public function getBySubsidiary(Zend_Db_Table_Row_Abstract $subsidiary, $filter, array $otherFilters = array()) {
		$where = array("$this->_name.subsidiary_id = ?" => $subsidiary->id_subsidiary, "$this->_name.is_submited");
		
		switch ($filter) {
			case 1:
				// pouze aktualni
				$where[] = "!is_removed";
				break;
				
			case 2:
				$where[] = "is_removed";
				break;
		}
        
        $where = array_merge($where, $otherFilters);
		
		return $this->_findMistakes($where);
	}
	
	public function getBySubsidiaries($subsidiaries, $type) {
		$subIds = array(0);
		
		foreach ($subsidiaries as $item) {
			$subIds[] = $item->id_subsidiary;
		}
		
		$where = array("$this->_name.subsidiary_id in (?)" => $subIds, "$this->_name.is_submited");
		
		// vyhodnoceni pozadovaneho typu
		switch ($type) {
			case 1:
				$where[] = "!is_removed";
				break;
				
			case 2:
				$where[] = "is_removed";
				break;
		}
		
		return $this->_findMistakes($where);
	}
	
	/**
	 * vraci hodnotu rizika pobocky
	 * 
	 * @param int $subsidiaryId id pobocky
	 * @return int
	 */
	public function getScore($subsidiaryId) {
		// sestaveni vyhledavaciho dotazu
		$select = new Zend_Db_Select($this->getAdapter());
		
		$select->from($this->_name, array(
				new Zend_Db_Expr("SUM(weight)")
				));
		
		$select->where("subsidiary_id = ?", $subsidiaryId)
					->where("!is_removed")->where("is_submited")->group("subsidiary_id");
		
		return $select->query()->fetchColumn();
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
	
	public function getCategories($clientId = null) {
		// sestaveni dotazu
		$select = new Zend_Db_Select($this->getAdapter());
		
		$select->from($this->_name, array("category"))->group("category")->order("category");
		
		// vyhodnoceni klienta
		if ($clientId) $select->where("client_id = ?", $clientId);
		
		// nacteni dat
		$result = $this->getAdapter()->query($select)->fetchAll();
		$retVal = array();
		
		foreach ($result as $item) {
			$retVal[$item["category"]] = $item["category"];
		}
		
		return $retVal;
	}
	
	public function getSubcategories($category, $clientId = null) {
		// sestaveni dotazu
		$select = new Zend_Db_Select($this->getAdapter());
		
		$select->from($this->_name, array("subcategory"))
				->where("category like ?", $category)
				->group("subcategory")
				->order("subcategory");
		
		if ($clientId) $select->where("client_id = ?", $clientId);
		
		$result = $this->getAdapter()->query($select)->fetchAll();
		$retVal = array();
		
		foreach ($result as $item) {
			$retVal[$item["subcategory"]] = $item["subcategory"];
		}
		
		return $retVal;
	}
	
	public function _findMistakes(array $where, array $columns = array()) {
		// nacteni asociacnich tabulek
		$tableAWatches = new Audit_Model_WatchesMistakes();
		$tableAAudits = new Audit_Model_AuditsMistakes();
		$tableWorkplaces = new Application_Model_DbTable_Workplace();
        $tableSubsidiaries = new Application_Model_DbTable_Subsidiary();
		$nameAWatches = $tableAWatches->info("name");
		$nameAAudits = $tableAAudits->info("name");
        $nameSubsidiary = $tableSubsidiaries->info("name");
		
		// sestaveni vyhledavaciho dotazu
		$select = new Zend_Db_Select($this->getAdapter());
		$select->from($this->_name, array_merge(array(
				new Zend_Db_Expr("$this->_name.*"),
				new Zend_Db_Expr("(SUM($nameAAudits.is_submited) + SUM($nameAWatches.is_submited) - 1) > 0 AS is_marked")
				), $columns));
		
		// spojeni s asociacemi
		$select->joinLeft($nameAAudits, "$nameAAudits.mistake_id = id and $nameAAudits.is_submited", array())
				->joinLeft($nameAWatches, "$nameAWatches.mistake_id = id and $nameAWatches.is_submited", array());
		
        // propojeni s tabulkou pobocek
        $select->joinInner($nameSubsidiary, "id_subsidiary = $this->_name.subsidiary_id", array(
            "subsidiary_name",
            "subsidiary_town",
            "subsidiary_street"
        ));
        
		// vlozeni omezeni z parametru
		foreach ($where as $cond => $val) {
			if (is_numeric($cond)) {
				$select->where($val);
			} else {
				$select->where($cond, $val);
			}
		}
        
        // zarazeni podminky pro vyhledani pobocek, ke kterym ma uzivatel pristup
        $user = Zend_Auth::getInstance()->getIdentity();
        
        if (!in_array($user->role, array(My_Role::ROLE_ADMIN, My_Role::ROLE_COORDINATOR))) {
            $tableAssocs = new Application_Model_DbTable_UserHasSubsidiary();
            $nameAssocs = $tableAssocs->info("name");
            $select->where("$nameSubsidiary.id_subsidiary in (select id_subsidiary from $nameAssocs where id_user = ?)", $user->id_user);
        }
		
		// spojeni s pracovisti
		$tableWorkplaces = new Application_Model_DbTable_Workplace();
		$nameWorkplaces = $tableWorkplaces->info("name");
		
		$select->joinLeft(
				$nameWorkplaces, sprintf("%s.workplace_id = %s.id_workplace", 
						$this->_name, 
						$nameWorkplaces), 
				array("workplace_name" => "name", "id_workplace"));
		
		// nastaveni seskupovani
		$select->group("id");

		// nacteni dat a sestaveni vysledku
		$data = $select->query()->fetchAll();
		
		return new Audit_Model_Rowset_AuditsRecordsMistakes(array("data" => $data, "table" => $this, "rowClass" => $this->_rowClass));
	}
}