<?php
class Application_Model_DbTable_Row_ClientRow extends Zend_Db_Table_Row_Abstract{
	
	public function getSubsidiaries(){
		return $this->findDependentRowset('Application_Model_DbTable_Subsidiary');
	}
	
}