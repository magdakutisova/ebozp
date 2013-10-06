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
	public function findById($deadlineId, $extended= true) {
		if ($extended) {
			$select = $this->_prepareSelect();
			$select->where("$this->_name.id = ?", $deadlineId);
			
			return new $this->_rowClass(array("data" => $select->query()->fetch(), "table" => $this));
		} else {
			return $this->find($deadlineId)->current();
		}
	}
	
	/**
	 * vraci lhuty, ktere jsou propadle
	 * 
	 * @param int $clientId id klienta
	 * @param int $subsidiaryId id pobocky
	 * @return Deadline_Model_Rowset_Deadlines
	 */
	public function findInvalids($clientId, $subsidiaryId = null) {
		// vytvoreni selektu
		$select = $this->_prepareSelect();
		$thisName = $this->_name;
		$select->where("$thisName.client_id = ?", $clientId);
		
		if (!is_null($subsidiaryId)) {
			$select->where("$thisName.subsidiary_id = ?", $subsidiaryId);
		}
		
		$select->where("$thisName.next_date < ADDDATE(CURRENT_DATE(), INTERVAL 1 MONTH)");
		$data = $select->query()->fetchAll();
		
		return new Deadline_Model_Rowset_Deadlines(array("data" => $data, "table" => $this, "rowClass" => $this->_rowClass));
	}
	
	/**
	 * pripravi selekt pro filtraci vcetne spojeni k ostatnim tabulkam
	 * 
	 * @return Zend_Db_Select
	 */
	public function _prepareSelect() {
		$select = new Zend_Db_Select($this->getAdapter());
		$name = $this->_name;
		
		// spojeni poli pro jednotlive typy lhut
		$tableDevices = new Application_Model_DbTable_TechnicalDevice();
		$nameDevices = $tableDevices->info("name");
		$tableEmployees = new Application_Model_DbTable_Employee();
		$nameEmployees = $tableEmployees->info("name");
		
		$devName = "CONCAT($nameDevices.`sort`, ' (', $nameDevices.`type`, ')')";
		$chemName = "chemical";
		$empName = "CONCAT($nameEmployees.first_name, ' ', $nameEmployees.surname)";
		
		// zakladni select
		$select->from($name, array(
				new Zend_Db_Expr("$name.*"),
				"is_valid" => new Zend_Db_Expr("CURRENT_DATE() < next_date"),
				"invalid_close" => new Zend_Db_Expr("ADDDATE(CURRENT_DATE(), INTERVAL 1 MONTH) > next_date"),
				"responsible_name" => new Zend_Db_Expr("TRIM(CONCAT(IFNULL(respemp.first_name, ''), ' ', IFNULL(respemp.surname, ''), IFNULL(user.name, ''), IFNULL(responsible_external_name, '')))"),
				"name" => new Zend_Db_Expr("CONCAT(IFNULL($empName, ''), IFNULL($chemName, ''), IFNULL($devName, ''))")
				));
		
		// propojeni s osobou z rad zamestnancu
		$select->joinLeft(array("respemp" => $nameEmployees), "responsible_id = respemp.id_employee", array());
		
		// propojeni s tabulkou uzivatelu
		$tableUsers = new Application_Model_DbTable_User();
		$nameUsers = $tableUsers->info("name");
		
		$select->joinLeft($nameUsers, "responsible_user_id = user.id_user", array());
		
		// pripojeni reference na zamestnance
		$select->joinLeft($nameEmployees, "$nameEmployees.id_employee = employee_id", array("employee_name" => new Zend_Db_Expr("$nameEmployees.first_name, ' ', $nameEmployees.surname")));
		
		// pripojeni reference na chemickou latku
		$tableChemicals = new Application_Model_DbTable_Chemical();
		$nameChemicals = $tableChemicals->info("name");
		
		$select->joinLeft($nameChemicals, "id_chemical = chemical_id", array("chemical_name" => "chemical"));
		
		// pripojeni reference na technicke zarizeni
		$select->joinLeft($nameDevices, "id_technical_device = technical_device_id", array("device_name" => new Zend_Db_Expr("CONCAT($nameDevices.sort, ' (', $nameDevices.`type`, ')')")));
		
		return $select;
	}
}