<?php
class Deadline_Model_Row_Deadline extends Zend_Db_Table_Row_Abstract {
	
	/**
	 * vraci novy radek obsahujici rozsirena data
	 * 
	 */
	public function extendData() {
		$select = new Zend_Db_Select($this->_table->getAdapter());
		$tableName = $this->_table->info("name");
		
		$select->from($tableName, array(new Zend_Db_Expr("*"), "is_valid" => new Zend_Db_Expr("next_date > CURRENT_DATE()")));
		$select->where("$tableName.id = ?", $this->_data["id"]);
		
		return new self(array("data" => $select->query()->fetch(), "table" => $this->_table));
	}
	
	/**
	 * vraci historii
	 * 
	 * @return Deadline_Model_Rowset_Logs
	 */
	public function findLogs() {
		$tableLogs = new Deadline_Model_Logs();
		
		return $tableLogs->fetchAll(array("deadline_id = ?" => $this->_data["id"]), "id desc");
	}
	
	/**
	 * pricte 
	 */
	public function submit($userId, $note) {
		// zapis do logu
		$tableLogs = new Deadline_Model_Logs();
		$tableLogs->insert(array(
				"deadline_id" => $this->_data["id"],
				"user_id" => $userId,
				"note" => $note
				));
		
		// vypocet dalsiho data
		$nextDate = new Zend_Date();
		$nextDate->addMonth($this->_data["period"]);
		
		// nastaveni dnesniho data
		$this->last_done = new Zend_Db_Expr("CURRENT_DATE()");
		$this->next_date = $nextDate->get(sprintf("%s-%s-%s", Zend_Date::YEAR, Zend_Date::MONTH, Zend_Date::DAY));
	}
	
	/**
	 * provede vsechny mozne updaty
	 * @param array $data data pro update
	 * @return Deadline_Model_Row_Deadline
	 */
	public function updateAll(array $data) {
		$this->updateCommons($data);
		$this->updateObjectId($data);
		$this->updatePeriod($data);
		$this->updateResponsible($data);
		
		return $this;
	}
	
	/**
	 * nastavi obecne informace
	 * 
	 * @param array $data data k nastaveni
	 */
	public function updateCommons(array $data) {
		// sestaveni dat
		$data = array_merge($this->toArray(), $data);
		
		// nastaveni dat
		$target = & $this->_data;
		
		$this->kind = $data["kind"];
		$this->specific = $data["specific"];
		$this->type = $data["type"];
		$this->period = $data["period"];
		$this->note = $data["note"];
		$this->subsidiary_id = $data["subsidiary_id"] ? $data["subsidiary_id"] : null;
		$this->next_date = $data["next_date"];
		
		return $this;
	}
	
	/**
	 * nastavi periodu lhuty
	 * 
	 * @param array $data data obsahujici periodu
	 * @return Deadline_Model_Row_Deadline
	 */
	public function updatePeriod(array $data) {
		// vychozi data
		if (is_null($this->_data["period"])) {
			$defaults = array("is_period" => 0, "period" => null);
		} else {
			$defaults = array("is_period" => 1, "period" => $this->_data["period"]);
		}
		
		// nahrazeni vychozich dat daty z parametru
		$data = array_merge($defaults, $data);
		
		// vyhodnoceni a nastaveni informaci
		if ($data["is_period"]) {
			$this->period = $data["period"];
		} else {
			$this->period = null;
		}
		
		return $this;
	}
	
	/**
	 * nastavi informace o zodpovedne osobe
	 * 
	 * @param array $data
	 * @return Deadline_Model_Row_Deadline
	 */
	public function updateResponsible(array $data) {
		// priprava dat
		$defaults = array(
				"resp_from_guard" => 0,
				"responsible_id" => null
				);
		
		$data = array_merge($defaults, $data);
		
		// vyhodnoceni, jestli je zodpovedna osoba z G7 nebo od klienta
		if ($data["resp_from_guard"]) {
			// je z G7
			$this->responsible_id = null;
			$this->responsible_user_id = $data["responsible_id"];
			
			// nacteni uzivatele a zapis jeho jmena
			$tableUsers = new Application_Model_DbTable_User();
			$user = $tableUsers->find($data["responsible_id"])->current();
			$this->responsible_name = $user->name ? $user->name : "?";
		} else {
			// je od klienta
			$this->responsible_id = $data["responsible_id"];
			$this->responsible_user_id = null;
			
			// nacteni zamestnance a update jmena
			$tableEmployees = new Application_Model_DbTable_Employee();
			$employee = $tableEmployees->find($data["responsible_id"])->current();
			$this->responsible_name = sprintf("%s %s", $employee->first_name, $employee->surname);
		}
	}
	
	/**
	 * updatuje objekt, ktereho se tyka lhuta
	 * 
	 * @param unknown_type $data
	 * @throws Zend_Db_Table_Exception
	 */
	public function updateObjectId($data) {
		// inicializace dat
		$data = array_merge(array("deadline_type" => 0, "object_id" => null), $data);
		
		// anulace dat
		$this->employee_id = null;
		$this->chemical_id = null;
		$this->technical_device_id = null;
		
		// vyhodnoceni typu lhuty
		switch ($data["deadline_type"]) {
			case Deadline_Form_Deadline::TARGET_EMPLOYEE:
				$this->employee_id = $data["object_id"];
				break;
				
			case Deadline_Form_Deadline::TARGET_CHEMICAL:
				$this->chemical_id = $data["object_id"];
				break;
				
			case Deadline_Form_Deadline::TARGET_DEVICE:
				$this->technical_device_id = $data["object_id"];
				break;
				
			default:
				throw new Zend_Db_Table_Exception("Unknown object type");
		}
	}
}