<?php
class My_Questionary_Callback_Get extends My_Questionary_Callback_Abstract {

	public function callback($questionary, $row=null, array $params = array()) {
		// nactnei asociacniho zaznamu
		$assoc = $this->getQuestionaryType2($row, $params["clientId"]);

		// pokud neni asociacni zaznam nalezen, nic se provadet nebude
		if (is_null($assoc)) return;

		// vyhodnoceni typu asociace
		switch ($assoc->assign_type) {
		case Application_Model_DbTable_QuestAssignments::TYPE_CLIENT:
			// prirazeni klienta
			$this->_writeToAssocTable($row->id, $params["clientId"], Application_Model_DbTable_QuestAssignments::TYPE_CLIENT, $params["clientId"]);
			$this->_initClient($questionary, $row, $params);
			break;

		case Application_Model_DbTable_QuestAssignments::TYPE_SUBSIDIARY:
			$this->_writeToAssocTable($row->id, $params["clientId"], Application_Model_DbTable_QuestAssignments::TYPE_SUBSIDIARY, $params["subsidiaryId"]);
			$this->_initSubsidiary($questionary, $row, $params);
			break;

		case Application_Model_DbTable_QuestAssignments::TYPE_EMPLOYEE:
			$this->_writeToAssocTable($row->id, $params["clientId"], Application_Model_DbTable_QuestAssignments::TYPE_EMPLOYEE, $params["employeeId"]);
			$this->_initEmployee($questionary, $row, $params);
			break;

		case Application_Model_DbTable_QuestAssignments::TYPE_WORKPLACE:
			$this->_writeToAssocTable($row->id, $params["clientId"], Application_Model_DbTable_QuestAssignments::TYPE_WORKPLACE, $params["workplaceId"]);
			$this->_initWorkplace($questionary, $row, $params);
			break;

		case Application_Model_DbTable_QuestAssignments::TYPE_POSITION:
			$this->_writeToAssocTable($row->id, $params["clientId"], Application_Model_DbTable_QuestAssignments::TYPE_POSITION, $params["positionId"]);
			$this->_initPosition($questionary, $row, $params);
			break;
		}
	}

	/**
	 * inicializuje dotaznik klienta
	 * provede prednastaveni dat a prednastaveni informaci o vytvorenych pobockach
	 * @param mixed $questionary dotaznik
	 * @param Zend_Db_Table_Row $row radek tabulky
	 * @param array $params dodatecne parametry
	 */
	private function _initClient($questionary, $row, $params) {
		// nacteni radku klienta
		$tableClients = new Application_Model_DbTable_Client();
		$client = $tableClients->find($params["clientId"])->current();

		// nacteni vazanych pobocek
		$tableSubsidiaries = new Application_Model_DbTable_Subsidiary();
		$subsidiaries = $tableSubsidiaries->fetchAll(array("client_id = ?" => $params["clientId"]));

		// ziskani prvku a nastaveni klientskych informaci z radku
		$items = $questionary->getItems();
		$subItem = null;

		foreach ($items as $item) {
			// ziskani jmena
			$name = $item->getName();

			if ($name == "subsidiaries") {
				$subItem = $item;
				continue;
			}

			// kontrola, zda se jedna o podporovanou hodnotu
			if (strpos($name, "client-") !== 0) continue;

			// rozlozeni jmena
			list($trash, $rowName) = explode("-", $name);

			// pokud tato hodnota v radku existuje, nastavi se
			if (isset($client[$rowName])) {
				$val = $client[$rowName];
				$item->setValue($val);
			}
		}

		// kontrola, zda bylo nalezeno pole pro pobocky
		if (is_null($subItem)) return;

		// zapis pobocek do dotazniku
		$subData = array();

		foreach ($subsidiaries as $subsidiary) {
			$subData[] = array(
				"name" => $subsidiary->subsidiary_name,
				"town" => $subsidiary->subsidiary_town,
				"street" => $subsidiary->subsidiary_street,
				"code" => $subsidiary->subsidiary_code,
				"id_subsidiary" => $subsidiary->id_subsidiary
			);
		}

		// zapis dat
		$subItem->setValue($subData);
	}

	/**
	 * inicializuje dotaznik pobocky
	 */
	protected function _initSubsidiary($questionary, $row, $params) {
		// nacteni klienta a pobocky
		$tableClients = new Application_Model_DbTable_Client();
		$tableSubsidiaries = new Application_Model_DbTable_Subsidiary();

		$client = $tableClients->find($params["clientId"])->current();
		$subsidiary = $tableSubsidiaries->find($params["subsidiaryId"])->current();

		foreach ($subsidiary as $key => $val) {
			// sestaveni jmena
			$name = "subsidiary-" . $key;

			try {
				$item = $questionary->getByName($name);
			} catch (Exception $e) {
				continue;
			}

			$item->setValue($val);
		}
	}

	/**
	 * inicializuje dotaznik zamestnance
	 */
	public function _initEmployee($questionary, $row, $params) {
		// nacteni klienta a zamestnance
		$tableClients = new Application_Model_DbTable_Client();
		$tableEmployees = new Application_Model_DbTable_Employee();

		$client = $tableClients->find($params["clientId"])->current();
		$employee = $tableEmployees->find($params["employeeId"])->current();

		// nastaveni dat
		foreach ($employee as $key => $val) {
			$name = "employee-" . $key;

			try {
				$item = $questionary->getByName($name);
			} catch (Exception $e) {
				continue;
			}

			$item->setValue($val);
		}
	}

	/**
	 * inicializuje dotaznik pracoviste
	 */
	public function _initWorkplace($questionary, $row, $params) {
		// nacteni klienta a zamestnance
		$tableClients = new Application_Model_DbTable_Client();
		$tableWorkplaces = new Application_Model_DbTable_Workplace();

		$client = $tableClients->find($params["clientId"])->current();
		$workplace = $tableWorkplaces->find($params["workplaceId"])->current();

		// nastaveni dat
		foreach ($workplace as $key => $val) {
			$name = "workplace-" . $key;

			try {
				$item = $questionary->getByName($name);
			} catch (Exception $e) {
				continue;
			}

			$item->setValue($val);
		}
	}

	private function _initPosition($questionary, $row, $params) {
		// nacteni dat
		$tableClients = new Application_Model_DbTable_Client();
		$tablePositions = new Application_Model_DbTable_Position();

		$client = $tableClients->find($params["clientId"])->current();
		$position = $tablePositions->find($params["positionId"])->current();

		// naplneni dat dotazniku
		foreach ($position as $key => $val) {
			$name = "position-" . $key;

			try {
				$item = $questionary->getByName($name);
			} catch (Exception $e) {
				continue;
			}

			$item->setValue($val);
		}

		// naplneni zamestnancu
		$tableEmployees = new Application_Model_DbTable_Employee();
		$tableAssocsEmps = new Application_Model_DbTable_PositionHasEmployee();

		$select = new Zend_Db_Select($tableEmployees->getAdapter());
		$select->from(array("e" => $tableEmployees->info("name")));
		$select->joinLeft(array("a" => $tableAssocsEmps->info("name")), "e.id_employee = a.id_employee", array());
		$select->where("e.subsidiary_id = ?", $position->subsidiary_id);

		$data = $select->query()->fetchAll();

		// sestaveni datove tabulky
		$empData = array();

		foreach ($data as $row) {
			$empData[] = array(
				"emp_name" => $row["first_name"] . " " . $row["surname"],
				"id_employee" => $row["id_employee"],
				"use" => 0
			);
		}

		$questionary->getByName("employees")->setValue($empData);

		// nacteni technickych zarizeni
		$tableDevices = new Application_Model_DbTable_TechnicalDevice();
		$tableAssocsTechs = new Application_Model_DbTable_PositionHasTechnicalDevice();

		$select = new Zend_Db_Select($tableDevices->getAdapter());
		$select->from(array("t" => $tableDevices->info("name")));
		$select->joinLeft(array("a" => $tableAssocsTechs->info("name")), "t.id_technical_device = a.id_technical_device", array());
		$select->where("t.subsidiary_id = ?", $position->subsidiary_id);

		$data = $select->query()->fetchAll();

		// sestaveni datove tabulky
		$techData = array();

		foreach ($data as $row) {
			$techData[] = array(
				"tech_name" => $row["type"] . " (" . $row["kind"] . ")",
				"id_technical_device" => $row["id_technical_device"],
				"use" => 0
			);
		}

		$questionary->getByName("technical_devices")->setValue($techData);
	}

	/**
	 * zapise zaznam do asociacni tabulky
	 * @param int $filledId identifikacni cislo vyplneneho dotazniku
	 * @param int $clientId identifikacni cislo klienta, ke kteremu dotaznik nalezi
	 * @param int $type prepinac typu objektu, ke kteremu data nalezi
	 * @param int $objId identifikacni cislo objektu
	 */
	private function _writeToAssocTable($filledId, $clientId, $type, $objId) {
		// priprava pole
		$data = array("filled_id" => $filledId, "client_id" => $clientId, "assign_type" => $type);

		switch ($type) {
		case Application_Model_DbTable_QuestAssignments::TYPE_CLIENT:
			// s typem klienta se nic nedela
			break;

		case Application_Model_DbTable_QuestAssignments::TYPE_SUBSIDIARY:
			// pobocka
			$data["subsidiary_id"] = $objId;
			break;

		case Application_Model_DbTable_QuestAssignments::TYPE_EMPLOYEE:
			$data["employee_id"] = $objId;
			break;

		case Application_Model_DbTable_QuestAssignments::TYPE_WORKPLACE:
			$data["workplace_id"] = $objId;
			break;

		case Application_Model_DbTable_QuestAssignments::TYPE_POSITION:
			$data["position_id"] = $objId;
			break;

		default:
			return;
		}

		// zapus dat do databaze
		$tableClients = new Application_Model_DbTable_QuestClients();
		$tableClients->insert($data);
	}
}
?>