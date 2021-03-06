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
		if($clientId == null){
			$select = $this->select()
				->from('employee')
				->joinLeft('position', 'employee.position_id = position.id_position')
				->where('employee.client_id IS NULL')
				->order('employee.surname', 'employee.first_name')
				->group('employee.id_employee');
		}
		else{
			$select = $this->select()
				->from('employee')
				->joinLeft('position', 'employee.position_id = position.id_position')
				->where('employee.client_id = ?', $clientId)
				->order('employee.surname', 'employee.first_name')
				->group('employee.id_employee');
		}		
		$select->setIntegrityCheck(false);
		$results = $this->fetchAll($select);
		$employees = array();
		if(count($results) > 0){
			foreach($results as $result){
				$key = $result->id_employee;
				$employees[$key] = $result->surname . ', ' . $result->first_name;
				if($result->phone != ''){
					$employees[$key] .= ', telefon: ' . $result->phone;
				}
				if($result->email != ''){
					$employees[$key] .= ', email: ' . $result->email;
				}
				if($result->position_id != null){
					$employees[$key] .= ', pracovní pozice: ' . $result->position;
				}
			}
		}
		return $employees;
	}
	
	public function getBySubsidiaryAndPositions($subsidiaryId){
		$select = $this->select()
			->from('employee')
			->join('position', 'employee.position_id = position.id_position')->where("employee.subsidiary_id = ?", $subsidiaryId)
			->order(array('position.position', 'employee.surname'));
		$select->setIntegrityCheck(false);
		$result = $this->fetchAll($select);
		if(count($result) > 0){
			$employees = array();
			foreach($result as $employee){
				if($employee->surname != ''){
					$employees[$employee->position][$employee->id_employee] = $employee->first_name . ' ' . $employee->surname;
				}
				else{
					$employees[$employee->position] = null;
				}
			}
			return $employees;
		}
		else{
			return null;
		}
	}
	
	public function getUnassignedEmployees($clientId, $subsidiaryId=null){
		$select = $this->select()
			->from('employee')
			->where('client_id = ?', $clientId)
			->where('position_id IS NULL')
			->order('employee.surname');
        
        if ($subsidiaryId) $select->where("employee.subsidiary_id = ?", $subsidiaryId);
        
		$result = $this->fetchAll($select);
		if(count($result) > 0){
			return $this->process($result);
		}
		else{
			return null;
		}
	}
	
	public function assignToClient($clientId){
		$select = $this->select()
			->from('employee')
			->where('client_id IS NULL');
		$results = $this->fetchAll($select);
		if(count($results) > 0){
			foreach ($results as $result){
				$result->client_id = $clientId;
				$result->save();
			}
		}
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