<?php
class Audit_Model_Checks extends Zend_Db_Table_Abstract {
	
	protected $_name = "audit_checks";
	
	protected $_primary = "id";
	
	protected $_sequence = true;
	
	protected $_referenceMap = array(
			"client" => array(
					"columns" => "client_id",
					"refTableClass" => "Application_Model_DbTable_Client",
					"columns" => "id_client"
			),
			
			"subsidiary" => array(
					"columns" => "subsidiary_id",
					"refTableClass" => "Application_Model_DbTable_Subsidiary",
					"refColumns" => "id_subsidiary"
			),
			
			"checker" => array(
					"columns" => "checker_id",
					"refTableClass" => "Applicatino_Model_DbTable_User",
					"refColumns" => "id_user"
			),
			
			"coordinator" => array(
					"columns" => "coordinator_id",
					"refTableClass" => "Application_Model_DbTable_User",
					"refcolumns" => "id_user"
			)
	);
	
	protected $_rowClass = "Audit_Model_Row_Check";
	
	protected $_rowsetClass = "Audit_Model_Rowset_Checks";
	
	/**
	 * vytvori novou proverku
	 * 
	 * @param Zend_Db_Table_Row_Abstract $subsidiary pobocka
	 * @param Zend_Db_Table_Row_Abstract $checker technik
	 * @param Zend_Db_Table_Row_Abstract $coordinator koordinator
	 * @param Zend_Date $doneAt datum provedeni proverky
	 * @return Audit_Model_Row_Check
	 */
	public function createCheck(Zend_Db_Table_Row_Abstract $subsidiary, Zend_Db_Table_Row_Abstract $checker, Zend_Db_Table_Row_Abstract $coordinator, Zend_Date $doneAt) {
		$retVal = $this->createRow(array(
				"checker_id" => $checker->id_user,
				"coordinator_id" => $coordinator->id_user,
				"subsidiary_id" => $subsidiary->id_subsidiary,
				"client_id" => $subsidiary->client_id,
				"summary" => "",
				"progerss_note" => "",
				"done_at" => $doneAt->get("y-MM-dd")
		));
		
		$retVal->save();
		
		return $retVal;
	}
	
	/**
	 * vraci seznam proverek dle koordinatora
	 * 
	 * @param Zend_Db_Table_Row_Abstract $coordinator koordinator
	 * @param string $order razeni
	 * @return Audit_Model_Rowset_Checks
	 */
	public function getByCoordinator(Zend_Db_Table_Row_Abstract $coordinator, $order) {
		// sestaveni dotazu
		$where = "coordinator_id = " . $checker->id_user;
		
		return $this->fetchAll($where, $order);
	}
	
	/**
	 * vraci seznam proverek dle technika
	 * 
	 * @param Zend_Db_Table_Row_Abstract $checker technik
	 * @param string $order razeni
	 * @return Audit_Model_Rowset_Checks
	 */
	public function getByChecker(Zend_Db_Table_Row_Abstract $checker, $order = null) {
		// sestaveni dotazu
		$where = "checker_id = " . $checker->id_user;
		
		return $this->fetchAll($where, $order);
	}
	
	/**
	 * vraci proverku dle jejiho id
	 * 
	 * @param int $id id proverky
	 * @return Audit_Model_Row_Check
	 */
	public function getById($id) {
		return $this->find($id)->current();
	}
}