<?php
class Application_Model_DbTable_Row_ClientRow extends Zend_Db_Table_Row_Abstract{
	
	public function getSubsidiaries(){
		$select = $this->select()->where('deleted = 0')->where('hq = 0');
		$result = $this->findDependentRowset('Application_Model_DbTable_Subsidiary', 'Client', $select);
		return $this->process($result);
	}
	
	public function getAllSubsidiaries(){
		$select = $this->select()->where('deleted = 0');
		$result = $this->findDependentRowset('Application_Model_DbTable_Subsidiary', 'Client', $select);
		return $this->process($result);
	}
	
	private function process($result){
		if ($result->count()){
			$subsidiaries = array();
			foreach($result as $subsidiary){
				$subsidiary = $result->current();
				$subsidiaries[] = $this->processSubsidiary($subsidiary);
			}
			return $subsidiaries;
		}
	}
	
	private function processSubsidiary($subsidiary){
		$data = $subsidiary->toArray();
		return new Application_Model_Subsidiary($data);
	}
}