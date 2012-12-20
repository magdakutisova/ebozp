<?php
class Application_Model_DbTable_Employee extends Zend_Db_Table_Abstract{
	
	protected $_name = 'employee';
	
	public function getEmployee($id){
		$id = (int)$id;
		$row = $this->fetchRow('id_employee = ' . $id);
		if(!$row){
			throw new Exception("Zaměstnanec $id nebyl nalezen");
		}
		$employee = $row->toArray();
		return new Application_Model_Employee($employee);
	}
	
	public function addEmployee(Application_Model_Employee $employee){
		$data = $employee->toArray();
		$employeeId = $this->insert($data);
		return $employeeId;
	}
	
	public function updateEmployee(Application_Model_Employee $employee){
		$data = $employee->toArray();
		$this->update($data, 'id_employee = ' . $employee->getIdEmployee());
	}
	
	public function deleteEmployee($id){
		$this->delete('id_employee = ' . (int)$id);
	}
	
}