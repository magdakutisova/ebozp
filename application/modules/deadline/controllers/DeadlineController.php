<?php
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
		
		$this->view->form = $form;
		$this->view->deadline = $deadline;
		$this->view->formSubmit = $formSubmit;
		$this->view->logs = $logs;
	}
	
	/**
	 * zobrazi lhutu pro cteni
	 */
	public function getAction() {
		
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
		$deadline = self::loadDeadline($this->_request->getParam("deadlineId"));
		
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
		$deadline = self::loadDeadline($this->_request->getParam("deadlineId"));
		
		// vylidace dat
		$form = new Deadline_Form_Done();
		
		if (!$form->isValid($this->_request->getParams())) {
			$this->_forward("edit");
			return;
		}
		
		// zjisteni uzivatele
		$user = Zend_Auth::getInstance()->getIdentity()->id_user;
		
		$deadline->submit($user, $form->getValue("note"));
		$deadline->save();
		
		$this->view->deadline = $deadline;
	}
	
	/**
	 * nacte lhutu dle id
	 * @param int $deadlineId id lhuty
	 * @return Deadline_Model_Row_Deadline
	 * @throws Zend_Db_Table_Exception
	 */
	public static function loadDeadline($deadlineId) {
		$tableDeadlines = new Deadline_Model_Deadlines();
		$deadline = $tableDeadlines->findById($deadlineId);
		
		if (!$deadline) throw new Zend_Db_Table_Exception("Deadline #$deadlineId not found");
		
		return $deadline;
	}
	
	public static function prepareDeadlineForm(Deadline_Form_Deadline $form, $clientId, $subsidiaryId = null) {
		
		// prepsani id pobocky z formulare
		if ($form->getValue("subsidiary_id"))
			$subsidiaryId = $form->getValue("subsidiary_id");
		
		// nacteni pobocek
		$tableSubsidiaries = new Application_Model_DbTable_Subsidiary();
		$subsidiaries = $tableSubsidiaries->fetchAll(array("client_id = ?" => $clientId), "subsidiary_name");
		
		$subs = array("0" => "---VYBERTE---");
		
		foreach ($subsidiaries as $subsidiary) {
			$subs[$subsidiary->id_subsidiary] = $subsidiary->subsidiary_name;
		}
		
		$form->getElement("subsidiary_id")->setMultiOptions($subs);
		
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
			}
			
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
}