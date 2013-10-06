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
		return $tableLogs->findByDeadline($this->id);
	}
	
	/**
	 * pricte 
	 */
	public function submit($userId, $note, $date) {
		// zapis do logu
		$tableLogs = new Deadline_Model_Logs();
		$tableLogs->insert(array(
				"deadline_id" => $this->_data["id"],
				"user_id" => $userId,
				"note" => $note,
				"done_at" => $date
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
		$this->kind = $data["kind"];
		$this->specific = $data["specific"];
		$this->type = $data["type"];
		$this->period = $data["period"];
		$this->note = $data["note"];
		$this->subsidiary_id = $data["subsidiary_id"] ? $data["subsidiary_id"] : null;
		$this->last_done = $data["last_done"];
		$this->next_date = new Zend_Db_Expr(sprintf("DATE_ADD('%s', INTERVAL %s MONTH)", $this->last_done, $this->period));
		
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
		$this->responsible_id = null;
		$this->responsible_user_id = null;
		$this->responsible_external_name = null;
		$this->anonymous_employee;
		
		switch ($data["resp_type"]) {
			case Deadline_Form_Deadline::RESP_CLIENT:
				// kontrola, jestli je zadano id
				if ($data["responsible_id"])
					$this->responsible_id = $data["responsible_id"];
				else
					$this->anonymous_employee = 1;
				
				break;
			
			case Deadline_Form_Deadline::RESP_GUARD:
				$this->responsible_user_id = $data["responsible_id"];
				break;
				
			default:
				$this->responsible_external_name = $data["responsible_external_name"];
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
			
			case Deadline_Form_Deadline::TARGET_UNDEFINED:
				// lhuta se tyka neceho uplne jineho
				break;
			
			default:
				throw new Zend_Db_Table_Exception("Unknown object type");
		}
	}
}