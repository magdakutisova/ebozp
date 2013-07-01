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
	
	public function getByPosition($positionId){
		$select = $this->select()
			->from('employee')
			->where('position_id = ?', $positionId);
		$result = $this->fetchAll($select);
		return $this->process($result);
	}
	
	/****************************************************************************
	 * Vrací seznam ID - zaměstnanec.
	 */
	public function getEmployees($clientId){
		$select = $this->select()
			->from('employee')
			->where('client_id = ?', $clientId)
			->order('employee.surname', 'employee.first_name')
			->group('employee.id_employee');
		$results = $this->fetchAll($select);
		$employees = array();
		if(count($results) > 0){
			foreach ($results as $result){
				$key = $result->id_employee;
				$employees[$key] = $result->surname . ', ' . $result->first_name;
			}
		}
		return $employees;
	}
	
	/*************************************
	 * Seznam ID - zaměstnanec pro odpovědné zaměnstnace.
	 */
	public function getResponsibleEmployees($clientId){
		$select = $this->select()
			->from('employee')
			->joinLeft('position', 'employee.position_id = position.id_position')
			->where('client_id = ?', $clientId)
			->order('employee.surname', 'employee.first_name')
			->group('employee.id_employee');
		$select->setIntegrityCheck(false);
		$results = $this->fetchAll($select);
		$employees = array();
		if(count($results) > 0){
			foreach($results as $result){
				$key = $result->id_employee;
				$employees[key] = $result->surname . ', ' . $result->first_name;
				if($employees->phone != ''){
					$employees[key] .= ', telefon: ' . $employees->phone;
				}
				if($employees->email != ''){
					$employees[key] .= ', email: ' . $employees->email;
				}
				if($employees->position_id != null){
					$employees[key] .= ', pracovní pozice: ' . $employees->position;
				}
			}
		}
		return $employees;
	}
	
	private function process($result){
		if($result->count()){
			$employees = array();
			foreach($result as $employee){
				$employee = $result->current();
				$employees[] = $this->processEmployee($employee);
			}
			return $employees;
		}
	}
	
	private function processEmployee($employee){
		$data = $employee->toArray();
		return new Application_Model_Employee($data);
	}
	
}