<?php
require_once __DIR__ . "/IndexController.php";

class Deadline_DeadlineController extends Zend_Controller_Action {
	
	public function init() {
		$this->view->addHelperPath(APPLICATION_PATH . "/views/helpers");
	}
	
	/**
	 * zobrazi formular pro vytvoreni nove lhuty
	 */
	public function createAction() {
		$form = new Deadline_Form_Deadline();
		$form->populate($this->_request->getParams());
		self::prepareDeadlineForm($form, $this->_request->getParam("clientId"), $this->_request->getParam("subsidiaryId"));
		
		// nastaveni id klienta
		$clientId = $this->_request->getParam("clientId", null);
		$form->setAction("/deadline/deadline/post?clientId=$clientId");
		
		$this->view->form = $form;
	}
	
	/**
	 * odstrani lhutu
	 */
	public function deleteAction() {
		// nacteni lhuty
		$deadline = self::loadDeadline($this->_request->getParam("deadlineId", 0));
		$deadline->delete();
	}
	
	/**
	 * zobrazi lhutu pro editaci
	 */
	public function editAction() {
		// nacteni lhuty
		$deadline = self::loadDeadline($this->_request->getParam("deadlineId"))->extendData();
		
		// vyhodnoceni typu objektu
		$rowData = self::prepareDeadlineRowData($deadline);
		
		$form = new Deadline_Form_Deadline();
		$requestData = $this->_request->getParam("deadline", array());
		$rowData = array_merge($rowData, $requestData);
		
		$form->populate($rowData);
		self::prepareDeadlineForm($form, $this->_request->getParam("clientId"));
		$form->populate($this->_request->getParam("deadline", array()));
		
		$form->isValidPartial($this->_request->getParams());
		self::disableEditInputs($form);
		$form->setAction(sprintf("%s?clientId=%s&deadlineId=%d", "/deadline/deadline/put", $this->_request->getParam("clientId", 0), $deadline->id));
		
		// formular odeslani lhuty
		$formSubmit = new Deadline_Form_Done();
		$formSubmit->setAction(sprintf("/deadline/deadline/submit?clientId=%s&deadlineId=%s", $this->_request->getParam("clientId"), $deadline->id));
		
		// zaznamy o splneni
		$logs = $deadline->findLogs();
		
		// formular smazani
		$deleteForm = new Deadline_Form_Delete();
		$url = $this->view->url(array("clientId" => $deadline->client_id, "deadlineId" => $deadline->id), "deadline-delete");
		$deleteForm->setAction($url);
		
		$this->view->form = $form;
		$this->view->deadline = $deadline;
		$this->view->formSubmit = $formSubmit;
		$this->view->logs = $logs;
		$this->view->deleteForm = $deleteForm;
	}
	
	/**
	 * zobrazi lhutu pro cteni
	 */
	public function getAction() {
		// nacteni lhuty
		$tableDeadlines = new Deadline_Model_Deadlines();
		
		$deadline = self::loadDeadline($this->_request->getParam("deadlineId"));
		
		$formSubmit = new Deadline_Form_Done();
		$formSubmit->setAction(sprintf("/deadline/deadline/submit?clientId=%s&deadlineId=%s", $this->_request->getParam("clientId"), $deadline->id));
		
		// nacteni historie
		$tableHistory = new Deadline_Model_Logs();
		$history = $tableHistory->findByDeadline($deadline->id);
		
		$this->view->deadline = $deadline;
		$this->view->formSubmit = $formSubmit;
		$this->view->logs = $history;
	}
	
	/**
	 * importuje lhuty do systemu
	 */
	public function importAction() {
		// kontrola formulare
		$form = new Deadline_Form_Import();
		Deadline_IndexController::prepareImportForm($this->_request->getParam("clientId"), $this->_request->getParam("subsidiaryId"), $form);
		
		if (!$form->isValid($this->_request->getParams())) {
			$this->_forward("index", "index");
			return;
		}
		
		// nacteni souboru do pameti
		$rows = array();
		$form->getElement("import_file")->receive();
		$fp = fopen($form->getElement("import_file")->getFileName(), "r");
		
		// preskoceni prvniho radku
		fgets($fp, 4096);
		
		// nacteni zbytku dat
		while (!feof($fp)) {
			$rows[] = fgetcsv($fp, 8192);
		}
		
		// vyhodnoceni typu lhut
		self::_import($form, $rows);
		
		$this->view->form = $form;
	}
	
	/**
	 * vytvori novou lhutu
	 */
	public function postAction() {
		// vytvoreni instance formulare
		$form = new Deadline_Form_Deadline();
		$form->populate($this->_request->getParams());
		self::prepareDeadlineForm($form, $this->_request->getParam("clientId"), $this->_request->getParam("subsidiaryId"));
		
		// kontrola validity
		if (!$form->isValid($this->_request->getParams())) {
			$this->_forward("create");
			return;
		}
		
		// vytvoreni radku a priprava dat
		$tableDeadlines = new Deadline_Model_Deadlines();
		$row = $tableDeadlines->createRow(array("client_id" => $this->_request->getParam("clientId")));
		
		$data = $form->getValues(true);
		
		// nastaveni dat radku
		$row->updateAll($data);
		$row->save();
		
		$this->view->row = $row;
	}
	
	/**
	 * upravi obecne informace o lhute
	 */
	public function putAction() {
		// nacteni a kontrola dat
		$form = new Deadline_Form_Deadline();
		self::disableEditInputs($form, true);
		$deadline = self::loadDeadline($this->_request->getParam("deadlineId"), false);
		
		// nastaveni pobocky do requestu
		$requestData = $this->_request->getParams();
		$requestData["deadline"]["subsidiary_id"] = $deadline->subsidiary_id;
		
		$rowData = self::prepareDeadlineRowData($deadline);
		$requestData = array_merge($rowData, $requestData);
		
		$requestData["deadline"]["object_id"] = $rowData["object_id"];
		$requestData["deadline"]["deadline_type"] = $rowData["deadline_type"];
		
		$form->populate($requestData);
		
		self::prepareDeadlineForm($form, $this->_request->getParam("clientId"));
		
		// zapis dat z requestu a jejich validace
		if (!$form->isValid($requestData)) {
			// nejaka hodnota neni validni
			$this->_forward("edit");
			return;
		}
		
		// update dat
		$data = $form->getValues(true);
		$deadline->updateCommons($data);
		$deadline->updatePeriod($data);
		$deadline->updateResponsible($data);

		$deadline->save();
		
		$this->view->deadline = $deadline;
		$this->view->form = $form;
	}
	
	public function submitAction() {
		// nacteni dat
		$deadline = self::loadDeadline($this->_request->getParam("deadlineId"), false);
		
		// vylidace dat
		$form = new Deadline_Form_Done();
		
		if (!$form->isValid($this->_request->getParams())) {
			$this->_forward("edit");
			return;
		}
		
		// zjisteni uzivatele
		$user = Zend_Auth::getInstance()->getIdentity()->id_user;
		
		$deadline->submit(
				$user, 
				$form->getValue("note"),
				$form->getValue("done_at"));
		$deadline->save();
		
		$this->view->deadline = $deadline;
	}
	
	/**
	 * nacte lhutu dle id
	 * @param int $deadlineId id lhuty
	 * @return Deadline_Model_Row_Deadline
	 * @throws Zend_Db_Table_Exception
	 */
	public static function loadDeadline($deadlineId, $extended = true) {
		$tableDeadlines = new Deadline_Model_Deadlines();
		$deadline = $tableDeadlines->findById($deadlineId, $extended);
		
		if (!$deadline) throw new Zend_Db_Table_Exception("Deadline #$deadlineId not found");
		
		return $deadline;
	}
	
	public static function prepareDeadlineForm(Deadline_Form_Deadline $form, $clientId, $subsidiaryId = null) {
		
		// prepsani id pobocky z formulare
		if ($form->getValue("subsidiary_id"))
			$subsidiaryId = $form->getValue("subsidiary_id");
		else
			$form->getElement("subsidiary_id")->setValue($subsidiaryId);
		
		// nacteni pobocek
		self::setSubsidiaries($form, $clientId);
		
		// vyhodnoceni, ktery typ zodpovedne osoby pouzit
		$respType = $form->getValue("resp_type");
		
		if ($respType == Deadline_Form_Deadline::RESP_EXTERNAL) {
			// jedna se o externistu - odebere se vyber uzivatele
			$form->removeElement("responsible_id");
		} else {
			if ($respType == Deadline_Form_Deadline::RESP_GUARD) {
				// nacteni pracovniku G7
				$tableUsers = new Application_Model_DbTable_User();
				$users = $tableUsers->fetchAll(array("role in (?)" => array(
						My_Role::ROLE_ADMIN, 
						My_Role::ROLE_COORDINATOR, 
						My_Role::ROLE_TECHNICIAN
						)));
			} else {
				// nacteni zamestnancu
				$tableEmployee = new Application_Model_DbTable_Employee();
				$empSelect = $tableEmployee->select(false);
				$empSelect->from($tableEmployee->info("name"), array("id_user" => "id_employee", "username" => new Zend_Db_Expr("CONCAT(first_name, ' ', surname)")));
				$empSelect->where("client_id = ?", $clientId);
				
				// vyhodnoceni pobocky
				if ($subsidiaryId) {
					// pro nacteni zodpovednych osob klienta musi byt vybrana pobocka
					$select = new Zend_Db_Select(Zend_Db_Table_Abstract::getDefaultAdapter());
					
					// filtrace zodpovednych osob dle pobocky
					$tableResponsibles = new Application_Model_DbTable_Responsible();
					$nameResponsibles = $tableResponsibles->info("name");
					
					$select->from($nameResponsibles, array("id_employee"))->where("id_subsidiary = ?", $subsidiaryId);
					$empSelect->where("id_employee in (?)", new Zend_Db_Expr($select->assemble()));
				}
				
				$users = $empSelect->query()->fetchAll(Zend_Db::FETCH_OBJ);
				$users = array_merge(array((object) array("id_user" => 0, "username" => "--NEZNÁMÝ--")), $users);
				
				// odebrani textoveho pole externisty
				$form->removeElement("responsible_external_name");
			}
			
			// odebrani textoveho pole externisty
			$form->removeElement("responsible_external_name");
		
			$userList = array();
			
			foreach ($users as $user) {
				$userList[$user->id_user] = $user->username;
			}
			
			$form->getElement("responsible_id")->setMultiOptions($userList);
		}
		
		// pokud je nastavena pobocka, nactou se pracoviste
		if (!is_null($subsidiaryId)) {
			$tableWorkplaces = new Application_Model_DbTable_Workplace();
			$workplaces = $tableWorkplaces->fetchAll(array("subsidiary_id = ?" => $subsidiaryId));
			
			$workList = array("" => "---VYBERTE---");
			
			foreach ($workplaces as $workplace) {
				$workList[$workplace->id_workplace] = $workplace->name;
			}
			
			$form->getElement("workplace_id")->setMultiOptions($workList);
		}
		
		// vyhodnoceni, jestli byl vybran typ objektu
		$deadType = $form->getValue("deadline_type");
		
		if ($deadType) {
			// priprava zakladniho dotazu pro filtraci pracovnich pozic
			$select = new Zend_Db_Select(Zend_Db_Table_Abstract::getDefaultAdapter());
			$tablePositions = new Application_Model_DbTable_Position();
			$namePositions = $tablePositions->info("name");
			
			$select->from($namePositions, array())->order("name");
			$select->where("$namePositions.client_id = ?", $clientId);
			
			if ($subsidiaryId)
				$select->where("$namePositions.subsidiary_id = ?", $subsidiaryId);
			
			// vyhodnoceni typu objektu a nacteni dat
			switch ($deadType) {
				case Deadline_Form_Deadline::TARGET_EMPLOYEE:
					// byl vybrán zaměstnanec - select se musi kompletne prestavet
					
					// sestaveni zacatku zakladniho selekctu
					$tableEmployee = new Application_Model_DbTable_Employee();
					$nameEmployee = $tableEmployee->info("name");
						
					$select = new Zend_Db_Select(Zend_Db_Table_Abstract::getDefaultAdapter());
					$select->from($nameEmployee, array("id" => "id_employee", "name" => new Zend_Db_Expr("CONCAT(first_name, ' ', surname)")));
					
					// sestaveni pomocneho selectu pro provazani s pozicemi
					$helperSelect1 = new Zend_Db_Select(Zend_Db_Table_Abstract::getDefaultAdapter());
					$helperSelect1->from($namePositions, array("id_position"))
									->where("client_id = ?", $clientId);
					
					// kontrola, jeslti je nastaveno id pobocky
					if (!is_null($subsidiaryId)) {
						// provazani na zodpovedne osoby
						$tableResponsibles = new Application_Model_DbTable_Responsible();
						$nameResponsibles = $tableResponsibles->info("name");
						
						$helperSelect2 = new Zend_Db_Select(Zend_Db_Table_Abstract::getDefaultAdapter());
						$helperSelect2->from($nameResponsibles, array("id_employee"))
										->where("id_subsidiary = ?", $subsidiaryId);
						
						$select->where("id_employee in (?)", new Zend_Db_Expr($helperSelect2));
						
						// pridani omezeni pobocky k pracovnim pozicim
						$helperSelect1->where("subsidiary_id = ?", $subsidiaryId);
						// pridani prvniho poconeho dotazu
						$select->orWhere("position_id in (?)", new Zend_Db_Expr($helperSelect1));
					} else {
						// pridani prvniho poconeho dotazu
						$select->where("position_id in (?)", new Zend_Db_Expr($helperSelect1));
					}
					
					break;
					
				case Deadline_Form_Deadline::TARGET_CHEMICAL:
					// byla vybrána chemická látka
					$tableChems = new Application_Model_DbTable_Chemical();
					$tableAssocs = new Application_Model_DbTable_PositionHasChemical();
					$nameChems = $tableChems->info("name");
					$nameAssocs = $tableAssocs->info("name");
					
					// zapis do selectu
					$select->joinLeft($nameAssocs, "$nameAssocs.id_position = $namePositions.id_position", array());
					$select->joinLeft($nameChems, "$nameAssocs.id_chemical = $nameChems.id_chemical", array("name" => "chemical", "id" => "id_chemical"));
					break;
					
				case Deadline_Form_Deadline::TARGET_DEVICE:
					// bylo vybráno technické zařízení
					$tableDevs = new Application_Model_DbTable_TechnicalDevice();
					$tableAssocs = new Application_Model_DbTable_PositionHasTechnicalDevice();
					$nameDevs = $tableDevs->info("name");
					$nameAssocs = $tableAssocs->info("name");
					
					// zapis do selectu
					$select->joinLeft($nameAssocs, "$nameAssocs.id_position = $namePositions.id_position", array());
					$select->joinLeft($nameDevs, "$nameAssocs.id_technical_device = $nameDevs.id_technical_device", array("id" => "id_technical_device", "name" => new Zend_Db_Expr("CONCAT(`sort`, ' ', `type`)")));
					break;
					
				case Deadline_Form_Deadline::TARGET_UNDEFINED:
					$form->getElement("object_id")->setRequired(false);
					$select = null;
					break;
			}
			
			if ($select) {
				// nacteni a zapis dat
				$objs = $select->query()->fetchAll();
				$objList = array();
				
				foreach ($objs as $obj) {
					$objList[$obj["id"]] = $obj["name"];
				}
				
				$form->getElement("object_id")->setMultiOptions($objList);
				$form->getElement("object_id")->setAttrib("disabled", null);
			}
		}
	}
	
	public static function disableEditInputs(Deadline_Form_Deadline $form) {
		$form->getElement("subsidiary_id")->setAttrib("disabled", "disabled");
		$form->getElement("deadline_type")->setAttrib("disabled", "disabled");
		$form->getElement("object_id")->setAttrib("disabled", "disabled");
	}
	
	/**
	 * pripravi data radku (nastavi spravny typ a id objektu)
	 * 
	 * @param Deadline_Model_Row_Deadline $row radek
	 * @return array
	 */
	public static function prepareDeadlineRowData(Deadline_Model_Row_Deadline $row) {
		$retVal = $row->toArray();
		
		if (!is_null($retVal["employee_id"])) {
			
			$retVal["object_id"] = $retVal["employee_id"];
			$retVal["deadline_type"] = Deadline_Form_Deadline::TARGET_EMPLOYEE;
			
		} elseif (!is_null($retVal["chemical_id"])) {
			
			$retVal["object_id"] = $retVal["chemical_id"];
			$retVal["deadline_type"] = Deadline_Form_Deadline::TARGET_CHEMICAL;
			
		} elseif (!is_null($retVal["technical_device_id"])) {
			
			$retVal["object_id"] = $retVal["technical_device_id"];
			$retVal["deadline_type"] = Deadline_Form_Deadline::TARGET_DEVICE;
		
		} else {
			
			// jedna se o obecnou lhutu
			$retVal["object_id"] = null;
			$retVal["deadline_type"] = Deadline_Form_Deadline::TARGET_UNDEFINED;
		}
		
		// vyhodnoceni zodpovedne osoby
		if ($retVal["responsible_user_id"]) {
			
			$retVal["responsible_id"] = $retVal["responsible_user_id"];
			$retVal["resp_type"] = Deadline_Form_Deadline::RESP_GUARD;
			
		} elseif (!is_null($retVal["responsible_external_name"])) {
			
			$retVal["resp_type"] = Deadline_Form_Deadline::RESP_EXTERNAL;
			
		} else {
			$retVal["resp_type"] = Deadline_Form_Deadline::RESP_CLIENT;
		}
		
		return $retVal;
	}
	
	protected function _import($form, array $data) {
		// ziskani adapteru a start relace
		$adapter = Zend_Db_Table_Abstract::getDefaultAdapter();
		$adapter->beginTransaction();
		
		try {
			// vytvoreni docasne tabulky pro ulozeni dat
			$columns = array(
					"`id` int not null primary key auto_increment",
					"`kind` varchar(128) not null",
					"`specific` varchar(128) null",
					"`period` tinyint",
					"`last_done` date",
					"`responsible_name` varchar(128)",
					"`name1` varchar(128)",
					"`name2` varchar(128)",
					"`birth_date` date",
					"`note` text",
					"`obj_id` int"
					);
			
			$colSpecs = implode(",", $columns);
			$sql = sprintf("create temporary table `tmp_emp_import` (%s) collate=utf8_czech_ci", $colSpecs);
			
			$adapter->query($sql);
			
			// naplneni dat
			$rows = array();
			
			foreach ($data as $item) {
				if (count($item) < 13) continue;
				
				// konverze dat do UTF-8
				foreach ($item as $key => $c) {
					$item[$key] = iconv("CP1250", "UTF-8", $c);
				}
				
				// prevod datumu
				$item[7] = self::_transferDate($item[7]);
				$item[11] = self::_transferDate($item[11]);
				
				// vyhodnoceni jmena
				if (strpos($item[10], " ") && $form->getValue("import_type") == Deadline_Form_Import::TYPE_EMPLOYEE) {
					// v prvku jsou minimalne dve slova
					list($name, $surname) = explode(" ", $item[10], 2);
				} else {
					// v prvku neni zadne nebo jedno slovo
					$name = $item[10];
					$surname = "";
				}
				
				// zapis do radku
				$rows[] = sprintf("(%s, %s, %s, %s, %s, %s, %s, %s, %s)",
						$adapter->quote($item[4]),
						$adapter->quote($item[5]),
						$adapter->quote($item[6]),
						$adapter->quote($item[7]),
						$adapter->quote($item[9]),
						$adapter->quote(trim($name)),
						$adapter->quote(trim($surname)),
						$adapter->quote($item[11]),
						$adapter->quote($item[13]));
				
			}
			
			// zapis do databaze, pokud je co zapisovat
			if ($rows) {
				$sql = sprintf("insert into tmp_emp_import (`kind`, `specific`, `period`, `last_done`, `responsible_name`, `name1`, `name2`, `birth_date`, `note`) values %s", implode(",", $rows));
				$adapter->query($sql);
			}
			
			// dalsi postup je zavisly na typu objektu
			switch ($form->getValue("import_type")) {
				case Deadline_Form_Import::TYPE_EMPLOYEE:
					$this->_importEmployees($adapter, $form->getValue("subsidiary_id"));
					break;

				case Deadline_Form_Import::TYPE_DEVICE:
					$this->_importDevices($adapter, $form->getValue("subsidiary_id"));
					break;
					
				default:
					throw new Zend_Exception("Invalid object type");
			}
		} catch (Zend_Exception $e) {
			die($e->getMessage());
			$adapter->rollBack();
			return;
		}
		
		$adapter->commit();
	}
	
	protected function _importEmployees(Zend_Db_Adapter_Abstract $adapter, $subsidiaryId) {
		// odstraneni vsech lhut tykajicich se zamestnancu dane pobocky
		$tableDeads = new Deadline_Model_Deadlines();
		$nameDeads = $tableDeads->info("name");
		$clientId = $this->_request->getParam("clientId");
		
		$tableDeads->delete(array(
				"subsidiary_id = ?" => $subsidiaryId,
				"client_id = ?" => $clientId,
				"employee_id is not null"));
		
		// v prvni fazi se najdou a oznaci ti pracovnici, kteri byli uz zaneseni do databaze
		$tableEmployees = new Application_Model_DbTable_Employee();
		$nameEmployees = $tableEmployees->info("name");
		
		// vytvoreni dotazu
		$baseSql = "update tmp_emp_import, %s set obj_id = id_employee where client_id = %s and name1 like first_name and name2 like surname and obj_id is null";
		$sqlUpdate = sprintf($baseSql, $nameEmployees, $this->_request->getParam("clientId"));
		
		$adapter->query($sqlUpdate);
		
		// ti zamestnanci, kteri nebyli nelezeni se vytvori
		$sql = "insert into $nameEmployees (client_id, first_name, surname, year_of_birth) select $clientId, name1, name2, birth_date from tmp_emp_import where obj_id is null group by concat(name1, ' ', name2)";
		$adapter->query($sql);
		
		// novy update dat
		$adapter->query($sqlUpdate);
		
		// vlozeni lhut do tabulky lhut
		$sql = "insert into $nameDeads (client_id, subsidiary_id, `kind`, `specific`, type, period, last_done, next_date, note, responsible_external_name, employee_id) ";
		$sql .= "select $clientId, $subsidiaryId, `kind`, `specific`, " . Deadline_Form_Deadline::TYPE_OTHER . ", period, last_done, DATE_ADD(last_done, interval period month), note, responsible_name, obj_id from tmp_emp_import";
		
		$adapter->query($sql);
	}
	
	protected function _importDevices(Zend_Db_Adapter_Abstract $adapter, $subsidiaryId) {
		// odstraneni vsech lhut tykajicich se zamestnancu dane pobocky
		$tableDeads = new Deadline_Model_Deadlines();
		$nameDeads = $tableDeads->info("name");
		$clientId = $this->_request->getParam("clientId");
		
		$tableDeads->delete(array(
				"subsidiary_id = ?" => $subsidiaryId,
				"client_id = ?" => $clientId,
				"technical_device_id is not null"));
		
		// v prvni fazi se najdou a oznaci ta zarizeni, ktera byla uz zanesena do databaze
		$tableDevices = new Application_Model_DbTable_TechnicalDevice();
		$tableAssocs = new Application_Model_DbTable_ClientHasTechnicalDevice();
		$nameDevices = $tableDevices->info("name");
		$nameAssocs = $tableAssocs->info("name");
		
		// vytvoreni dotazu
		$baseSql = "update tmp_emp_import, %s, %s set obj_id = $nameDevices.id_technical_device where $nameAssocs.id_client = %s and $nameAssocs.id_technical_device = $nameDevices.id_technical_device and tmp_emp_import.`specific` like $nameDevices.sort and obj_id is null";
		$sqlUpdate = sprintf($baseSql, $nameDevices, $nameAssocs, $this->_request->getParam("clientId"));
		
		$adapter->query($sqlUpdate);
		
		// nacteni nejvyssiho incrementu zarizeni
		$sql = "select max(id_technical_device) from $nameDevices";
		$maxId = $adapter->query($sql)->fetchColumn();
		
		// ti zamestnanci, kteri nebyli nelezeni se vytvori
		$sql = "insert into $nameDevices (`sort`) select `specific` from tmp_emp_import where obj_id is null group by `specific`";
		$adapter->query($sql);
		
		// prirazeni novych zarizeni klientovi
		$sql = "insert into $nameAssocs (id_client, id_technical_device) select $clientId, id_technical_device from $nameDevices where id_technical_device > $maxId";
		$adapter->query($sql);
		
		// novy update dat
		$adapter->query($sqlUpdate);
		
		// vlozeni lhut do tabulky lhut
		$sql = "insert into $nameDeads (client_id, subsidiary_id, `kind`, `specific`, type, period, last_done, next_date, note, responsible_external_name, technical_device_id) ";
		$sql .= "select $clientId, $subsidiaryId, `kind`, `specific`, " . Deadline_Form_Deadline::TYPE_OTHER . ", period, last_done, DATE_ADD(last_done, interval period month), note, responsible_name, obj_id from tmp_emp_import";
		
		$adapter->query($sql);
	}
	
	public static function setSubsidiaries(Zend_Form $form, $clientId) {
		// nacteni seznamu pobocek
		$tableSubsidiaries = new Application_Model_DbTable_Subsidiary();
		$subsidiaries = $tableSubsidiaries->fetchAll(array("client_id = ?" => $clientId, "active", "!deleted", "!hq_only"), array("hq desc", "subsidiary_town", "subsidiary_street"));
		
		$subs = array("0" => "---VYBERTE---");
		
		foreach ($subsidiaries as $subsidiary) {
			$subs[$subsidiary->id_subsidiary] = sprintf("%s, %s", $subsidiary->subsidiary_town, $subsidiary->subsidiary_street);
		}
		
		$form->getElement("subsidiary_id")->setMultiOptions($subs);
	}
	
	/**
	 * prevede datum pri improtu do formatu SQL
	 * 
	 * @param string $date datum ze souboru
	 * @return string
	 */
	private static function _transferDate($date) {
		$date = trim($date);
		
		if (!$date) {
			return "1900-01-01";
		}
		
		// rozlozeni na segmenty
		list($day, $month, $year) = explode(".", $date);
		
		return sprintf("%s-%s-%s", $year, $month, $day);
	}
}