<?php
class Audit_Model_Row_Watch extends Zend_Db_Table_Row_Abstract {
	
	public function findDiscussed() {
		$tableDiscussed = new Audit_Model_WatchesDiscussed();
		
		return $tableDiscussed->fetchAll(array("watch_id = ?" => $this->_data["id"]), "id");
	}
	
	public function findChanges() {
		$tableChanges = new Audit_Model_WatchesChanges();
	
		return $tableChanges->fetchAll(array("watch_id = ?" => $this->_data["id"]), "id");
	}
	
	public function findOrders() {
		$tableOrders = new Audit_Model_WatchesOrders();
	
		return $tableOrders->fetchAll(array("watch_id = ?" => $this->_data["id"]), "id");
	}
	
	public function findOutputs() {
		$tableOutputs = new Audit_Model_WatchesOutputs();
	
		return $tableOutputs->fetchAll(array("watch_id = ?" => $this->_data["id"]), "id");
	}
}