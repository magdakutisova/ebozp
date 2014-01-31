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

        if (strpos($this->_request->getActionName(), "create") === false)
            $form->isValidPartial($this->_request->getParams());

		// nacteni kategorii
		$tableCategories = new Deadline_Model_Categories();
		
		$this->view->form = $form;
		$this->view->categories = $tableCategories->findAll();
	}
	
	public function createHtmlAction() {
		$this->createAction();
		
		$clientId = $this->_request->getParam("clientId");
		
		$this->view->form->setAction("/deadline/deadline/post.html?clientId=$clientId");
	}
	
	/**
	 * odstrani lhutu
	 */
	public function deleteAction() {
		// nacteni lhuty
		$deadline = self::loadDeadline($this->_request->getParam("deadlineId", 0));
        
        // zapis do denniku
        if ($deadline->employee_id || $deadline->anonymous_obj_emp) {
            $route = "deadline-employees";
        } elseif ($deadline->employee_id || $deadline->anonymous_obj_emp) {
            $route = "deadline-devices";
        } else {
            $route = "deadline-others";
        }
        
        $this->_helper->diaryRecord->insertMessage("odstranil lhůtu", array("subsidiaryId" => $deadline->subsidiary_id, "clientId" => $deadline->client_id), $route, $deadline->name ? $deadline->name : "Jiná lhůta", $deadline->subsidiary_id);
		
        
		$deadline->delete();
		
		$this->_helper->FlashMessenger("Lhůta byla smazána");
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
        
        $tableCategories = new Deadline_Model_Categories();
		
		$this->view->form = $form;
		$this->view->deadline = $deadline;
		$this->view->formSubmit = $formSubmit;
		$this->view->logs = $logs;
		$this->view->deleteForm = $deleteForm;
        $this->view->categories = $tableCategories->findAll();
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
        
        $ext = $tableDeadlines->findById($row->id);
		
		self::checkDeadlineDate($row);
        
        // zapis do denniku
        if ($row->employee_id || $row->anonymous_obj_emp) {
            $route = "deadline-employees";
        } elseif ($row->employee_id || $row->anonymous_obj_emp) {
            $route = "deadline-devices";
        } else {
            $route = "deadline-others";
        }
        $this->_helper->diaryRecord->insertMessage("přidal novou lhůtu", array("subsidiaryId" => $row->subsidiary_id, "clientId" => $row->client_id), $route, $ext->name ? $ext->name : "Jiná lhůta", $row->subsidiary_id);
		
		$this->view->row = $row;
		$this->_helper->FlashMessenger("Lhůta byla vytvořena");
	}
	
	public function postHtmlAction() {
        // vytvoreni instance formulare
		$form = new Deadline_Form_Deadline();
		$form->populate($this->_request->getParams());
		self::prepareDeadlineForm($form, $this->_request->getParam("clientId"), $this->_request->getParam("subsidiaryId"));
		
		// kontrola validity
		if (!$form->isValid($this->_request->getParams())) {
			$this->_forward("create.html");
			return;
		}
        
		$this->postAction();
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
		
        if (!isset($requestData["deadline"]["object_id"])) {
            $requestData["deadline"]["object_id"] = $rowData["object_id"];
            $requestData["deadline"]["deadline_type"] = $rowData["deadline_type"];
        }
        
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
        $deadline->updateObjectId($data);

		$deadline->save();
		
		self::checkDeadlineDate($deadline);
        
        $row = self::loadDeadline($deadline->id);
        
        // zapis do denniku
        if ($row->employee_id || $row->anonymous_obj_emp) {
            $route = "deadline-employees";
        } elseif ($row->employee_id || $row->anonymous_obj_emp) {
            $route = "deadline-devices";
        } else {
            $route = "deadline-others";
        }
        
        $this->_helper->diaryRecord->insertMessage("upravil lhůtu", array("subsidiaryId" => $row->subsidiary_id, "clientId" => $row->client_id), $route, $row->name ? $row->name : "Jiná lhůta", $row->subsidiary_id);
		
		
		$this->_helper->FlashMessenger("Změny byly uloženy");
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
		$this->_helper->FlashMessenger("Lhůta byla označena jako splněná");
	}
    
    /**
     * odesle vybrane lhuty najednou
     */
    public function submitsAction() {
        // nacteni odeslanych informaci
        $clientId = $this->_request->getParam("clientId", 0);
        $deadIds = (array) $this->_request->getParam("selected", array());
        
        // pokud je seznam prazdny, nema cenu pokracovat
        if (empty($deadIds)) return;
        
        // kontrola odesilaciho formulare
        $form = new Deadline_Form_Done();
        
        if (!$form->isValid($this->_request->getParams())) {
            // nespravna data - odeslani zpravy a return
            $this->_helper->FlashMessenger("Nesprávně zadaná data (pravděpodobně špatné datum splnění)");
            return;
        }
        
        // aktualizace data lhuty
        $where = array(
            "id in (?)" => $deadIds
        );
        
        $data = array(
            "last_done" => $form->getValue("done_at")
        );
        
        $tableDeadlines = new Deadline_Model_Deadlines();
        $tableDeadlines->update($data, $where);
        
        // posunuti period
        $tableDeadlines->update(array(
            "next_date" => new Zend_Db_Expr("ADDDATE(last_done, INTERVAL period MONTH)")
        ), $where);
        
        // zapis do logu
        $tableLogs = new Deadline_Model_Logs();
        $userId = Zend_Auth::getInstance()->getIdentity()->id_user;
        $adapter = $tableLogs->getAdapter();
        
        $select = new Zend_Db_Select($adapter);
        
        $select->from($tableDeadlines->info("name"), array(
            "id",
            new Zend_Db_Expr($adapter->quote($userId)),
            new Zend_Db_Expr($adapter->quote($form->getValue("done_at")))
        ));
        
        // vygenerovani vkladaciho sql dotazu
        $sql = "insert into %s (deadline_id, user_id, done_at) %s";
        $adapter->query(sprintf($sql, $tableLogs->info("name"), $select->assemble()));
        
        // nacteni lhut a kontrola datumu
        $deadlines = $tableDeadlines->find($deadIds);
        
        foreach ($deadlines as $item) {
            self::checkDeadlineDate($item);
        }
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

		// nastaveni druhu a specifikace
		$specificEl = $form->getElement("specific");
		$kindEl = $form->getElement("kind");

		// vlozeni hodnot do multioptions
		$specVal = $specificEl->getValue();

		if (!is_null($specVal))
			$specificEl->addMultiOption($specVal, $specVal);

		$kindVal = $kindEl->getValue();

		if (!is_null($kindVal))
			$kindEl->setMultiOptions(array($kindVal => $kindVal));

		// vyhodnoceni, ktery typ zodpovedne osoby pouzit
		$respType = $form->getValue("resp_type");
		
		if ($respType == Deadline_Form_Deadline::RESP_EXTERNAL) {
			// jedna se o externistu - odebere se vyber uzivatele
			$form->removeElement("responsible_id");
		} else {
			if ($respType == Deadline_Form_Deadline::RESP_GUARD) {
				// nacteni pracovniku G7
				$tableUsers = new Application_Model_DbTable_User();
                $select = $tableUsers->select(true);
                
                $select->reset(Zend_Db_Table_Select::COLUMNS);
                $select->columns(array(
                    "id_user",
                    "username" => new Zend_Db_Expr("name")
                ));
                
                $select->where("role in (?)", array(
						My_Role::ROLE_ADMIN, 
						My_Role::ROLE_COORDINATOR, 
						My_Role::ROLE_TECHNICIAN
						));
                
				$users = $select->query()->fetchAll(Zend_Db::FETCH_OBJ);
                
                $users[] = (object) array("id_user" => "0", "username" => "--NEZNÁMÝ--");
                
			} else {
				// nacteni zamestnancu
				$tableEmployee = new Application_Model_DbTable_Employee();
				$empSelect = $tableEmployee->select(false);
				$empSelect->from($tableEmployee->info("name"), array("id_user" => "id_employee", "username" => new Zend_Db_Expr("CONCAT(first_name, ' ', surname)")));
				$empSelect->where("client_id = ?", $clientId);
				
                /*
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
                 * 
                 * pro jistotu nechavam
				*/
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
					
					$select->where("client_id = ?", $clientId);
					
					break;
					
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
					$tableAssocs = new Application_Model_DbTable_ClientHasTechnicalDevice();
					$nameDevs = $tableDevs->info("name");
					$nameAssocs = $tableAssocs->info("name");
					
					$select = new Zend_Db_Select(Zend_Db_Table_Abstract::getDefaultAdapter());
					
					// zapis do selectu
					$select->from($nameAssocs, array())->where("id_client = ?", $clientId)->order("name")->group("$nameDevs.id_technical_device");
					$select->joinInner($nameDevs, "$nameAssocs.id_technical_device = $nameDevs.id_technical_device", array("id" => "id_technical_device", "name" => new Zend_Db_Expr("CONCAT(IFNULL(`sort`, ''), ' ', IFNULL(`type`, ''))")));
					
					break;
					
				case Deadline_Form_Deadline::TARGET_UNDEFINED:
					$form->getElement("object_id")->setRequired(false);
					$select = null;
					break;
			}
			
			if ($select) {
				// nacteni a zapis dat
				$objs = $select->query()->fetchAll();
				$objList = array("0" => "--JINÉ--");
				
				foreach ($objs as $obj) {
					$objList[$obj["id"]] = $obj["name"];
				}
                
                // pridani vyberoveho policka
                $objList["-1"] = "-VYTVOŘIT NOVÝ OBJEKT-";
				
				$form->getElement("object_id")->setMultiOptions($objList);
				$form->getElement("object_id")->setAttrib("disabled", null);
                
                if ($form->getValue("object_id") == "-1")  {
                    $form->getElement("object_id")->setValue("0");
                }
			}
		}
	}
	
	public static function disableEditInputs(Deadline_Form_Deadline $form) {
		$form->getElement("subsidiary_id")->setAttrib("disabled", "disabled");
	}
	
	/**
	 * pripravi data radku (nastavi spravny typ a id objektu)
	 * 
	 * @param Deadline_Model_Row_Deadline $row radek
	 * @return array
	 */
	public static function prepareDeadlineRowData(Deadline_Model_Row_Deadline $row) {
		$retVal = $row->toArray();
		
		if (!is_null($retVal["employee_id"]) || $retVal["anonymous_obj_emp"]) {
			
			$retVal["object_id"] = $retVal["employee_id"] ? $retVal["employee_id"] : 0;
			$retVal["deadline_type"] = Deadline_Form_Deadline::TARGET_EMPLOYEE;
			
		} elseif (!is_null($retVal["chemical_id"]) || $retVal["anonymous_obj_chem"]) {
			
			$retVal["object_id"] = $retVal["chemical_id"] ? $retVal["chemical_id"] : 0;
			$retVal["deadline_type"] = Deadline_Form_Deadline::TARGET_CHEMICAL;
			
		} elseif ($retVal["technical_device_id"] || $retVal["anonymous_obj_tech"]) {
			$retVal["object_id"] = $retVal["technical_device_id"] ? $retVal["technical_device_id"] : 0;
			$retVal["deadline_type"] = Deadline_Form_Deadline::TARGET_DEVICE;
		
		} else {
			
			// jedna se o obecnou lhutu
			$retVal["object_id"] = null;
			$retVal["deadline_type"] = Deadline_Form_Deadline::TARGET_UNDEFINED;
		}
		
		// vyhodnoceni zodpovedne osoby
		if (!is_null($retVal["responsible_user_id"]) || $retVal["anonymous_guard"]) {
			
			$retVal["responsible_id"] = $retVal["responsible_user_id"];
			$retVal["resp_type"] = Deadline_Form_Deadline::RESP_GUARD;
            
            if ($retVal["anonymous_guard"]) $retVal["responsible_id"] = 0;
			
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
        
        // nacteni maximalniho id
        $tableDeadlines = new Deadline_Model_Deadlines();
        $nameDeadlines = $tableDeadlines->info("name");
        
        $sql = "select id from $nameDeadlines order by id desc limit 0,1";
        $maxId = $adapter->query($sql)->fetchColumn();
		
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
				
				if ((int) substr($item[7], 0, 4) < 1910) $item[7] = "0000-00-00";
				
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
        
        // kontrola datumu deadline
        $deadlines = $tableDeadlines->fetchAll(array("id > ?" => $maxId));
        
        foreach ($deadlines as $deadline) {
            self::checkDeadlineDate($deadline);
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
		$baseSql = "update tmp_emp_import, %s, %s set obj_id = $nameDevices.id_technical_device where $nameAssocs.id_client = %s and $nameAssocs.id_technical_device = $nameDevices.id_technical_device and tmp_emp_import.`name1` like $nameDevices.sort and obj_id is null";
		$sqlUpdate = sprintf($baseSql, $nameDevices, $nameAssocs, $this->_request->getParam("clientId"));
		
		$adapter->query($sqlUpdate);
		
		// nacteni nejvyssiho incrementu zarizeni
		$sql = "select max(id_technical_device) from $nameDevices";
		$maxId = $adapter->query($sql)->fetchColumn();
		
		// ti zamestnanci, kteri nebyli nelezeni se vytvori
		$sql = "insert into $nameDevices (`sort`) select `name1` from tmp_emp_import where obj_id is null and LENGTH(TRIM(name1)) > 0 group by `name1`";
		$adapter->query($sql);
		
        if (!$maxId) $maxId = 0;
        
		// prirazeni novych zarizeni klientovi
		$sql = "insert into $nameAssocs (id_client, id_technical_device) select $clientId, id_technical_device from $nameDevices where id_technical_device > $maxId";
        $adapter->query($sql);
		
		// novy update dat
		$adapter->query($sqlUpdate);
		
		// vlozeni lhut do tabulky lhut
		$sql = "insert into $nameDeads (client_id, subsidiary_id, `kind`, `specific`, type, period, last_done, next_date, note, responsible_external_name, technical_device_id, anonymous_obj_tech) ";
		$sql .= "select $clientId, $subsidiaryId, `kind`, `specific`, " . Deadline_Form_Deadline::TYPE_OTHER . ", period, last_done, DATE_ADD(last_done, interval period month), note, responsible_name, obj_id, ISNULL(obj_id) from tmp_emp_import";
		
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
	
	public static function checkDeadlineDate($deadline) {
		// kontrola datumu propadnuti
		$validTo = new Zend_Date($deadline->next_date, "y-MM-dd");
		$reserve = new Zend_Date($validTo);
		
		$tableWatches = new Audit_Model_Watches();
		$tableAudits = new Audit_Model_Audits();
		
		$nameWatches = $tableWatches->info("name");
		$nameAudits = $tableAudits->info("name");
		
		$select = new Zend_Db_Select(Zend_Db_Table_Abstract::getDefaultAdapter());
		
		// kazdopadne se smazou stara data
		$select->from($nameWatches, array("id"))->where("!is_closed");
			
		// odebrani dat
		$tableAssocs = new Audit_Model_WatchesDeadlines();
		$tableAssocs->delete(array(
				"deadline_id = ?" => $deadline->id,
				"watch_id in (?)" => new Zend_Db_Expr($select)
		));
			
		//prepis auditu
		$select->reset(Zend_Db_Select::FROM)->reset(Zend_Db_Select::COLUMNS);
		$select->from($nameAudits, array("id"))->where("!is_closed");
		
		// odebrani dat
		$tableAssocs = new Audit_Model_AuditsDeadlines();
		$tableAssocs->delete(array(
				"deadline_id = ?" => $deadline->id,
				"audit_id in (?)" => new Zend_Db_Expr($select)
		));
		
		if ($reserve->isEarlier(Zend_Date::now())) {
			// lhuta se zahrne do dohlidek s novymi informacemi
			$adapter = Zend_Db_Table_Abstract::getDefaultAdapter();
			$select = new Zend_Db_Select($adapter);
				
			// poddotaz nacte dohlidky spolecne s id lhuty
			$select->from($nameWatches, array(
					"id",
					new Zend_Db_Expr($deadline->id),
					new Zend_Db_Expr($adapter->quote($deadline->next_date)),
					new Zend_Db_Expr($validTo->isEarlier(Zend_Date::now()) ? 1 : 0)
			));
				
			$select->where("!is_closed")->where("subsidiary_id = ?", $deadline->subsidiary_id);
				
			// prepis dat dohlidek
			$tableAssocs = new Audit_Model_WatchesDeadlines();
			$sql = "insert ignore into %s (watch_id, deadline_id, valid_to, is_over) %s";
			$adapter->query(sprintf($sql, $tableAssocs->info("name"), $select));
				
			// prepis dat auditu
			// poddotaz nacte dohlidky spolecne s id lhuty
			$select->reset(Zend_Db_Select::FROM);
			$select->reset(Zend_Db_Select::COLUMNS);
				
			$select->from($nameAudits, array(
					"id",
					new Zend_Db_Expr($deadline->id),
					new Zend_Db_Expr($adapter->quote($deadline->next_date)),
					new Zend_Db_Expr($validTo->isEarlier(Zend_Date::now()) ? 1 : 0)
			));
				
			$tableAssocs = new Audit_Model_AuditsDeadlines();
			$sql = "insert ignore into %s (audit_id, deadline_id, valid_to, is_over) %s";
				
			$adapter->query(sprintf($sql, $tableAssocs->info("name"), $select));
				
		}
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
