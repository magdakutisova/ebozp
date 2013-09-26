<?php
class Deadline_Model_Logs extends Zend_Db_Table_Abstract {
	
	protected $_name = "deadline_logs";
	
	protected $_sequence = true;
	
	protected $_primary = "id";
	
	protected $_referenceMap = array(
			"deadline" => array(
					"columns" => "deadline_id",
					"refTableClass" => "Deadline_Model_Deadlines",
					"refColumns" => "id"
					)
			);
	
	protected $_rowClass = "Deadline_Model_Row_Log";
	
	protected $_rowsetClass = "Deadline_Model_Rowset_Logs";
	
	/**
	 * vraci historii dle lhuty
	 * 
	 * @param int $deadlineId id lhuty
	 * @param string $order razeni
	 * @return Deadline_Model_Rowset_Logs
	 */
	public function findByDeadline($deadlineId, $order = "done_at desc") {
		$tableUsers = new Application_Model_DbTable_User();
		$nameUsers = $tableUsers->info("name");
		
		$select = new Zend_Db_Select($this->getAdapter());
		$select->from($this->_name)->where("deadline_id = ?", $deadlineId);
		$select->order($order);
		$select->joinLeft($nameUsers, "id_user = user_id" ,array("name"));
		
		$data = $select->query()->fetchAll();
		
		return new $this->_rowsetClass(array(
				"rowClass" => $this->_rowClass,
				"data" => $data,
				"table" => $this
				));
	}
}