<?php
class Audit_Model_Copyables extends Zend_Db_Table_Abstract {
	
	protected $_name = "audit_copyables";
	
	protected $_primary = "user_id";
	
	protected $_sequence = false;
	
	protected $_copyables = null;
	
	protected $_referenceMap = array(
			"user" => array(
					"columns" => "user_id",
					"refTableClass" => "Application_Model_DbTable_User",
					"refColumns" => "user_id"
			)
	);
	
	protected $_rowClass = "Audit_Model_Row_Copyable";
	
	protected $_rowsetClass = "Audit_Model_Rowset_Copyables";
	
	public function getCopyables() {
		$copyables = $this->fetchAll();
		$retVal = array();
		
		foreach ($copyables as $item) {
			$retVal[] = $item->user_id;
		}
		
		$this->_copyables = $retVal;
		
		return $retVal;
	}
	
	public function isCopyable(Application_Model_User $user) {
		if (!is_null($this->_copyables)) {
			return in_array($user->getIdUser(), $this->_copyables);
		}
		
		// nacteni dat
		$copyable = $this->find($user->getIdUser());
		
		return (bool) $copyable->count();
	}
	
	public function setCopyable(Application_Model_User $user, $copyable) {
		if ($copyable) {
			$sql = "insert ignore into `$this->_name` (" . $user->getIdUser() . ")";
			$this->getAdapter()->query($sql);
		} else {
			$this->delete("user_id = " . $user->getIdUser());
		}
	}
	
	public function setCopyables(array $users) {
		$this->delete("1");
		
		// zapis uzivatelu
		$adapter = $this->getAdapter();
		$list = array();
		
		foreach ($users as $userId) {
			$list[] = $adapter->quote($userId);
		}
		$sql = "insert into `$this->_name` values (" . implode("),(", $list) . ")";
		$adapter->query($sql);
	}
}