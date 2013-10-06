<?php
class Audit_Model_WatchesDeadlines extends Zend_Db_Table_Abstract {
	
	protected $_name = "audit_watches_deadlines";
	
	protected $_primary = array("id");
	
	protected $_sequence = true;
	
	protected $_referenceMap = array(
			"watch" => array(
					"columns" => "watch_id",
					"refTableClass" => "Audit_Model_Watches",
					"refColumns" => "id"
					),
			
			"deadline" => array(
					"columns" => "deadline_id",
					"refTableClass" => "Deadline_Model_Deadlines",
					"refColumns" => "id"
					)
			);
	
	protected $_rowClass = "Audit_Model_Row_WatchDeadline";
	
	protected $_rowsetClass = "Audit_Model_Rowset_WatchesDeadlines";
	
	/**
	 * vytvori asociacni zaznamy vztahujici se k dohlidce
	 * 
	 * @param Audit_Model_Row_Watch $watch radek dohlidky
	 * @return int
	 */
	public function createByWatch(Audit_Model_Row_Watch $watch) {
		// vytvoreni tabulky lhut a nacteni jejiho jmena
		$tableDeadlines = new Deadline_Model_Deadlines();
		$nameDeadlines = $tableDeadlines->info("name");
		
		// sesta eni vyhledavaciho dotazu
		$select = $tableDeadlines->select(true);
		$select->reset(Zend_Db_Select::COLUMNS);
		
		$select->columns(array(new Zend_Db_Expr($watch->id), "id", "next_date", "is_over" => new Zend_Db_Expr("next_date < NOW()")));
		
		$select->where("subsidiary_id = ?", $watch->subsidiary_id)
					->where("next_date < NOW()");
		
		// sestaveni insertniho dotazu
		$adapter = $this->getAdapter();
		$sql = sprintf("insert into %s (watch_id, deadline_id, valid_to, is_over) %s", 
				$adapter->quoteIdentifier($this->_name),
				$select);
		
		// odeslani dat
		return $adapter->query($sql)->rowCount();
	}
	
	public function findByWatchDeadline($watchId, $deadlineId) {
		return $this->fetchRow(array(
				"watch_id = ?" => $watchId,
				"deadline_id = ?" => $deadlineId
				));
	}
	
	/**
	 * najde informace o lhutach zahrnutych v dohlidce
	 * informace jsou rozsireny o data z asociacni tabulky
	 * 
	 * @param Audit_Model_Row_Watch $watch
	 * @return Zend_Db_Table_Rowset_Abstract
	 */
	public function findExtendedByWatch(Audit_Model_Row_Watch $watch) {
		// sestaveni dotazu
		$tableDeadlines = new Deadline_Model_Deadlines();
		$select = $tableDeadlines->_prepareSelect();
		
		// vlozeni omezeni na asociovane lhuty
		$subSelect = new Zend_Db_Select($this->getAdapter());
		$subSelect->from($this->_name, array("deadline_id"))->where("watch_id = ?", $watch->id);
		
		$select->where("id in ?", $subSelect);
		
		$data = $select->query()->fetchAll();
		
		return new Zend_Db_Table_Rowset(array("data" => $data, "readOnly" => true));
	}
}