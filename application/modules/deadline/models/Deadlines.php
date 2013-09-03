<?php
class Deadline_Model_Deadlines extends Zend_Db_Table_Abstract {
	
	protected $_name = "deadline_deadlines";
	
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
			
			"responsible" => array(
					"columns" => "responsible_id",
					"refTableClass" => "Application_Model_DbTable_User",
					"refColumns" => "id_user"
					),
			
			"workplace" => array(
					"columns" => "workplace_id",
					"refTableClass" => "Application_Model_DbTable_Workplace",
					"refColumns" => "id_workplace"
					),
			
			"employee" => array(
					"columns" => "employee_id",
					"refTableClass" => "Application_Model_DbTable_Employee",
					"refColumns" => "id_employee"
					),
			
			"chemical" => array(
					"columns" => "chemical_id",
					"refTableClass" => "Application_Model_DbTable_Chemical",
					"refColumns" => "id_chemical"
					),
			
			"technicalDevice" => array(
					"columns" => "technical_device_id",
					"refTableClass" => "Application_Model_DbTable_TechnicalDevice",
					"refColumns" => "id_technical_device"
					)
			);
	
	protected $_rowClass = "Deadline_Model_Row_Deadline";
	
	protected $_rowsetClass = "Deadline_Model_Rowset_Deadlines";
	
	/**
	 * vraci lhutu dle id
	 * 
	 * @param int $deadlineId id lhuty
	 * @return Deadline_Model_Row_Deadline
	 */
	public function findById($deadlineId) {
		return $this->find($deadlineId)->current();
	}
}