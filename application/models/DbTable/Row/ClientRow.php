<?php
class Application_Model_DbTable_Row_ClientRow extends Zend_Db_Table_Row_Abstract{
	
	public function getSubsidiaries(){
		$select = $this->select()->where('deleted = 0')->where('hq = 0');
		return $this->findDependentRowset('Application_Model_DbTable_Subsidiary', 'Client', $select);
	}
	
	public function getAllSubsidiaries(){
		$select = $this->select()->where('deleted = 0');
		return $this->findDependentRowset('Application_Model_DbTable_Subsidiary', 'Client', $select);
	}
	
}