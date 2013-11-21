<?php
class Audit_Model_WatchesOrders extends Zend_Db_Table_Abstract {
	
	protected $_name = "audit_watches_orders";
	
	protected $_primary = array("id");
	
	protected $_sequence = true;
	
	protected $_referenceMap = array(
			"watch" => array(
					"columns" => "watch_id",
					"refTableClass" => "Audit_Model_Watches",
					"refColumns" => "id"
					)
	);
	
	public function findOrder($id) {
		$select = $this->prepareSelect();
		
		$select->where("o.id = ?", $id);
		
		$data = $select->query()->fetch();
		$cls = $this->_rowClass;
		
		return new $cls(array("data" => $data, "table" => $this));
	}
	
	public function findOrders($selectDone = false, $selectUndone = true) {
		// vytvoreni dotazu
		$select = $this->prepareSelect();
		
		// pridani podminek
		if (!$selectDone) {
			$select->where("o.finished_at IS NULL");
		}
		
		if (!$selectUndone) {
			$select->where("o.finished_at IS NOT NULL");
		}
		
		$select->order(new Zend_Db_Expr("o.finished_at IS NOT NULL"))->order(new Zend_Db_Expr("w.watched_at DESC"));
		
		$result = $select->query()->fetchAll();
		
		return new Zend_Db_Table_Rowset(array(
				"data" => $result,
				"rowClass" => $this->_rowClass
				));
	}
	
	public function prepareSelect() {
		// sestaveni dotazu
		$tableSubsidiary = new Application_Model_DbTable_Subsidiary();
		$tableWatches = new Audit_Model_Watches();
		
		$nameWatches = $tableWatches->info("name");
		$nameSubsidiary = $tableSubsidiary->info("name");
		
		$tableUser = new Application_Model_DbTable_User();
		$nameUser = $tableUser->info("name");
		
		$select = new Zend_Db_Select($this->getAdapter());
		
		$select->from(array("o" => $this->_name));
		
		// napojeni dohlidky
		$select->joinInner(array("w" => $nameWatches), "watch_id = w.id", array());
		
		// napojeni pobocky
		$select->joinInner(array("subs" => $nameSubsidiary), "subs.id_subsidiary = w.subsidiary_id", array(
				"subsidiary_name" => new Zend_Db_Expr("CONCAT(subsidiary_name, ' - ', subsidiary_town, ', ', subsidiary_street)")
				));
		
		// napojeni uzivatele
		$select->joinLeft(array("u" => $nameUser), "o.finished_by = u.id_user", array("login" => "username", "username" => "name"));
		
		$select->where("w.is_closed");
		
		return $select;
	}
}