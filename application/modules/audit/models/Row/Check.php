<?php
class Audit_Model_Row_Check extends Zend_Db_Table_Row_Abstract {
	
	/**
	 * vraci radek klienta
	 * 
	 * @return Zend_Db_Table_Row_Abstract
	 */
	public function getClient() {
		return $this->findParentRow("Application_Model_DbTable_Client", "client");
	}
	
	/**
	 * vraci koordinatora
	 * 
	 * @return Zend_Db_Table_Row_Abstract
	 */
	public function getCoordinator() {
		return $this->findParentRow("Application_Model_DbTable_User", "coordinator");
	}
	
	/**
	 * vraci technika
	 * 
	 * @return Zend_Db_Table_Row_Abstract
	 */
	public function getChecker() {
		return $this->findParentRow("Application_Model_DbTable_User", "checker");
	}
	
	/**
	 * vraci seznam neshod navazanych na proverku
	 * 
	 * @param bool $removed odstranene neshody
	 * @param bool $new nove neshody
	 * @param bool $marked oznacene jako kriticke
	 * @param string $order razeni vysledku
	 * @return Audit_Model_Rowset_AuditsRecordsMistakes
	 */
	public function getMistakes($removed = null, $new = null, $marked = null, $order = null) {
		// vytvoreni potrebych objektu
		$tableMistakes = new Audit_Model_AuditsRecordsMistakes();
		$tableAssocs = new Audit_Model_ChecksMistakes();
		
		$nameMistakes = $tableMistakes->info("name");
		$nameAssocs = $tableAssocs->info("name");
		
		// sestaveni podminky
		$where = array(
				"`$nameAssocs`.check_id = " . $this->id,
				"`$nameAssocs`.mistake_id = `$nameMistakes`.id"
		);
		
		$adapter = $tableAssocs->getAdapter();
		
		$orWhere = array();
		
		if (!is_null($removed)) $orWhere[] = "`action` = " . $removed;
		if (!is_null($new)) $orWhere[] = "`action` = " . $new;
		if (!is_null($marked)) $orWhere[] = "`action` = " . $order;
		
		if ($orWhere) {
			$where[] = "(" . implode(" or ", $orWhere) . ")";
		}
		
		// sestaveni a provedeni dotazu
		if (!$order) $order = "workplace_id";
		
		$sql = "select `$nameMistakes`.* from `$nameMistakes`, `$nameAssocs` where " . implode(" and ", $where) . " order by `$nameMistakes`.`$order`";
		$result = $adapter->query($sql)->fetchAll();
		
		// vygenerovani navratove hodnoty
		$retVal = new Audit_Model_Rowset_AuditsRecordsMistakes(array(
				"table" => $tableMistakes,
				"data" => $result,
				"rowClass" => "Audit_Model_Row_AuditRecordMistake"
		));
		
		return $retVal;
	}
	
	public function getSubsidiary() {
		return $this->findParentRow("Application_Model_DbTable_Subsidiary", "subsidiary");
	}
}