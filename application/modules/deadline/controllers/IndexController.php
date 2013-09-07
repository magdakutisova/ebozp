<?php
class Deadline_IndexController extends Zend_Controller_Action {
	
	public function init() {
		$this->view->layout()->setLayout("client-layout");
		$this->view->addHelperPath(APPLICATION_PATH . "/views/helpers");
	}
	
	public function deviceAction() {
		// nacteni dat
		$filter = $this->_request->getParam("filter", array());
		$filter["clientId"] = $this->_request->getParam("clientId", 0);
	
		$deadlines = self::filterDeadlines(Deadline_Form_Deadline::TARGET_DEVICE, $filter);
	
		$this->view->deadlines = $deadlines;
		$this->view->filterSet = $this->_request->getParam("filter", array());
	}
	
	/**
	 * zobrazi seznam lhut tykajicich se zamestnancu
	 */
	public function employeeAction() {
		// nacteni dat
		$filter = $this->_request->getParam("filter", array());
		$filter["clientId"] = $this->_request->getParam("clientId", 0);
		
		$deadlines = self::filterDeadlines(Deadline_Form_Deadline::TARGET_EMPLOYEE, $filter);
		
		$this->view->deadlines = $deadlines;
		$this->view->filterSet = $this->_request->getParam("filter", array());
	}
	
	public function chemicalAction() {
		// nacteni dat
		$filter = $this->_request->getParam("filter", array());
		$filter["clientId"] = $this->_request->getParam("clientId", 0);
		
		$deadlines = self::filterDeadlines(Deadline_Form_Deadline::TARGET_CHEMICAL, $filter);
		
		$this->view->deadlines = $deadlines;
		$this->view->filterSet = $this->_request->getParam("filter", array());
	}
	
	/**
	 * zobrazi seznam lhut k danemu klientovi / pobocce
	 * podporuje filtrovani
	 */
	public function indexAction() {
		
	}
	
	/**
	 * profiltruje lhuty dle zadanych parametru
	 * 
	 * @param array $filerSet parametry filtrace
	 * @return Deadline_Model_Rowset_Deadlines
	 */
	public static function filterDeadlines($objType, array $filterSet) {
		$tableDeadlines = new Deadline_Model_Deadlines();
		$select = new Zend_Db_Select(Zend_Db_Table_Abstract::getDefaultAdapter());
		$nameDead = $tableDeadlines->info("name");
		$select->from($nameDead, array(
				new Zend_Db_Expr("$nameDead.*"),
				"is_valid" => new Zend_Db_Expr("CURRENT_DATE() < next_date"),
				"resp_emp" => new Zend_Db_Expr("CONCAT(emp.first_name, ' ', emp.surname)"),
				"resp_guard" => new Zend_Db_Expr("username")
				));
		
		// zapis filtru dle klienta
		$select->where("$nameDead.client_id = ?", $filterSet["clientId"]);
		
		// vyhledani zodpovedne osoby
		$tableEmployees = new Application_Model_DbTable_Employee();
		$tableUsers = new Application_Model_DbTable_User();
		$nameEmployees = $tableEmployees->info("name");
		$nameUsers = $tableUsers->info("name");
		
		// sjednoceni s tabulkami
		$select->joinLeft(array("emp" => $nameEmployees), "$nameDead.responsible_id = emp.id_employee", array());
		$select->joinLeft($nameUsers, "id_user = responsible_user_id", array());
		
		// vyfiltrovani typu
		switch ($objType) {
			case Deadline_Form_Deadline::TARGET_CHEMICAL:
				$select->where("chemical_id IS NOT NULL");
				
				$tableChemicals = new Application_Model_DbTable_Chemical();
				$nameChemicals = $tableChemicals->info("name");
				
				$select->joinInner($nameChemicals, "chemical_id = id_chemical", array("name" => "chemical"));
				break;
				
			case Deadline_Form_Deadline::TARGET_DEVICE:
				$select->where("technical_device_id IS NOT NULL");
				
				$tableDevices = new Application_Model_DbTable_TechnicalDevice();
				$nameDevices = $tableDevices->info("name");
				
				$select->joinInner($nameDevices, "technical_device_id = id_technical_device", array("name" => new Zend_Db_Expr("CONCAT(`sort`, ' (', $nameDevices.`type` , ')')")));
				break;
				
			case Deadline_Form_Deadline::TARGET_EMPLOYEE:
				$select->where("employee_id IS NOT NULL");
				
				$tableEmployees = new Application_Model_DbTable_Employee();
				$nameEmployees = $tableEmployees->info("name");
				
				$select->joinInner($nameEmployees, "employee_id = id_employee", array("name" => new Zend_Db_Expr("CONCAT(first_name, ' ', surname)")));
				break;
				
			default:
				throw new Zend_Db_Table_Exception("Invalid type of filter");
		}
		
		// nastaveni pobocky, pokud je potreba
		if (isset($filterSet["subsidiaryId"])) {
			// kontrola hodnoty subsidiary
			if ($filterSet["subsidiary_id"] == 0) {
				// filtruji se pouze globalni terminy
				$select->where("subsidiary_id IS NULL");
			} else {
				// filtruje se konkretni pobocka
				$select->where("subsidiary_id = ?", $filterSet["subsidiary_id"]);
			}
		}
		
		return $select->query()->fetchAll(Zend_Db::FETCH_OBJ);
	}
}