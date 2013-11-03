<?php
class Application_Model_DbTable_Task extends Zend_Db_Table_Abstract {
	
	protected $_name = "task";
	
	protected $_primary = "id";
	
	protected $_sequence = true;
	
	protected $_referenceMap = array(
			"client" => array(
					"columns" => "client_id",
					"refTableClass" => "Application_Model_DbTable_Client",
					"refColumns" => "id_client"
					),
			
			"subsidiary" => array(
					"columns" => "subsidiary_id",
					"refTableClass" => "Application_Model_DbTable_Subsidiary",
					"refColumns" => "id_subsidiary"
					),
			
			"manager" => array(
					"columns" => "created_by",
					"refTableClass" => "Application_Model_DbTable_User",
					"refColumns" => "id_user"
					),
			
			"worker" => array(
					"columns" => "completed_by",
					"refTableClass" => "Application_Model_DbTable_User",
					"refColumns" => "id_user"
					)
			);
	
	protected $_rowClass = "Application_Model_DbTable_Row_Task";
	
	/**
	 * najde informace o ukolech vztahujicich se k dane pobocce a klientovi
	 * 
	 * @param unknown_type $subsidiaryId
	 * @param unknown_type $clientId
	 * @return Zend_Db_Table_Rowset_Abstract
	 */
	public function findTasks($subsidiaryId, $clientId, $activeOnly) {
		// sestaveni dotazu
		$adapter = $this->getAdapter();
		$select = new Zend_Db_Select($adapter);
		$select->from($this->_name);
		
		// propojeni na jmeno zadavatele
		$tableUsers = new Application_Model_DbTable_User();
		$nameUsers = $tableUsers->info("name");
		
		$select->joinInner(array("us_creator" => $nameUsers), "created_by = us_creator.id_user", array("creator_name" => "name"));
		$select->joinLeft(array("us_completer" => $nameUsers), "completed_by = us_completer.id_user", array("completer_name" => "name"));
		
		// sestaveni podminky pro klienta a pobocku
		$where = sprintf("($this->_name.subsidiary_id = %s or $this->_name.client_id = %s)", $adapter->quote($subsidiaryId), $adapter->quote($clientId));
		$select->where($where);
		
		// doplneni podminky aktivity, pokud je treba
		if ($activeOnly) {
			$select->where("completed_by IS NULL");
		}
		
		return new Zend_Db_Table_Rowset(array("data" => $select->query()->fetchAll()));
	}
}