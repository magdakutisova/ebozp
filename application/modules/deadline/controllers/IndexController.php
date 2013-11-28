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
		$filter["subsidiaryId"] = $this->_request->getParam("subsidiaryId", 0);
	
        $filterForm = new Deadline_Form_Filter();
        $filterForm->populate($this->_request->getParams());
        
        if (is_null($filterForm->getValue("subsidiary_id"))) {
            $filterForm->getElement("subsidiary_id")->setValue($this->_request->getParam("subsidiaryId"));
        }
        
		$deadlines = self::filterDeadlines(Deadline_Form_Deadline::TARGET_DEVICE, $filter, $filterForm);
	
		$this->view->deadlines = $deadlines;
		$this->view->filterSet = $this->_request->getParam("filter", array());
		$this->_prepareFilterForm($deadlines, $filterForm);
	}
	
	/**
	 * zobrazi seznam lhut tykajicich se zamestnancu
	 */
	public function employeeAction() {
		// nacteni dat
		$filter = $this->_request->getParam("filter", array());
		$filter["clientId"] = $this->_request->getParam("clientId", 0);
		$filter["subsidiaryId"] = $this->_request->getParam("subsidiaryId", 0);
		
        $filterForm = new Deadline_Form_Filter();
        $filterForm->populate($this->_request->getParams());
        
        if (is_null($filterForm->getValue("subsidiary_id"))) {
            $filterForm->getElement("subsidiary_id")->setValue($this->_request->getParam("subsidiaryId"));
        }
        
		$deadlines = self::filterDeadlines(Deadline_Form_Deadline::TARGET_EMPLOYEE, $filter, $filterForm);
		
		$this->view->deadlines = $deadlines;
		$this->view->filterSet = $this->_request->getParam("filter", array());
		
		$this->_prepareFilterForm($deadlines, $filterForm);
	}
	
	public function chemicalAction() {
		// nacteni dat
		$filter = $this->_request->getParam("filter", array());
		$filter["clientId"] = $this->_request->getParam("clientId", 0);
		$filter["subsidiaryId"] = $this->_request->getParam("subsidiaryId", 0);
		
        $filterForm = new Deadline_Form_Filter();
        $filterForm->populate($this->_request->getParams());
        
        if (is_null($filterForm->getValue("subsidiary_id"))) {
            $filterForm->getElement("subsidiary_id")->setValue($this->_request->getParam("subsidiaryId"));
        }
        
		$deadlines = self::filterDeadlines(Deadline_Form_Deadline::TARGET_CHEMICAL, $filter, $filterForm);
		
		$this->view->deadlines = $deadlines;
		$this->view->filterSet = $this->_request->getParam("filter", array());
		$this->_prepareFilterForm($deadlines, $filterForm);
	}
	
	/**
	 * zobrazi seznam lhut k danemu klientovi / pobocce
	 * podporuje filtrovani
	 */
	public function indexAction() {
		// nacteni pobocky
		$tableSubsidiaries = new Application_Model_DbTable_Subsidiary();
		$subsidiary = $tableSubsidiaries->find($this->_request->getParam("subsidiaryId", 0))->current();
		
		// vytvoreni improtovaciho formulare
		$form = new Deadline_Form_Import();
		self::prepareImportForm($this->_request->getParam("clientId"), $this->_request->getParam("subsidiaryId"), $form);
		
		$this->view->importForm = $form;
		$this->view->subsidiary = $subsidiary;
	}
	
	public function otherAction() {
		// nacteni dat
		$filter = $this->_request->getParam("filter", array());
		$filter["clientId"] = $this->_request->getParam("clientId", 0);
		$filter["subsidiaryId"] = $this->_request->getParam("subsidiaryId", 0);
		
        $filterForm = new Deadline_Form_Filter();
        $filterForm->populate($this->_request->getParams());
        
        if (is_null($filterForm->getValue("subsidiary_id"))) {
            $filterForm->getElement("subsidiary_id")->setValue($this->_request->getParam("subsidiaryId"));
        }
        
		$deadlines = self::filterDeadlines(Deadline_Form_Deadline::TARGET_UNDEFINED, $filter, $filterForm);
		
		$this->view->deadlines = $deadlines;
		$this->view->filterSet = $this->_request->getParam("filter", array());
		$this->_prepareFilterForm($deadlines, $filterForm);
	}
	
	/**
	 * profiltruje lhuty dle zadanych parametru
	 * 
	 * @param array $filerSet parametry filtrace
	 * @return Deadline_Model_Rowset_Deadlines
	 */
	public static function filterDeadlines($objType, array $filterSet, $filterForm) {
		$select = self::prepareFilterSelect($objType, $filterSet);
		
        // nastaveni filtru z formulare
        $filterVals = $filterForm->getValues(true);
        
        foreach ($filterVals as $key => $val) {
            if ($val && $key[0] != 'c') {
                switch ($key) {
                    case "specific":
                    case "kind":
                        $select->where("`$key` like ?", $val);
                        
                    default:
                        $select->where("`$key` = ?", $val);
                }
            }
        }
        
        // vyhodnoceni casovych omezeni
        if (!$filterVals["clsok"]) {
            $select->where("next_date < NOW()");
        }
        
        if (!$filterVals["clsinvalid"]) {
            $select->where("next_date >= NOW()");
        }
        
        if (!$filterVals["clsclose"]) {
            $select->where("NOT (next_date BETWEEN NOW() and ADDDATE(NOW(), INTERVAL 1 MONTH))", $val);
        }
        
		return $select->query()->fetchAll(Zend_Db::FETCH_OBJ);
	}
	
	/**
	 * vyfiltruje seznam jmen
	 * 
	 * @param int $objType typ objektu
	 * @param array $filterSet seznam filtracnich podminek
	 * @return array
	 */
	public static function filterNames($objType, array $filterSet) {
		$select = self::prepareFilterSelect($objType, $filterSet);
		$select->columns(array("name"))->group("name");
		
		return $select->query()->fetchAll(Zend_Db::FETCH_OBJ);
	}
	
	/**
	 * pripravi filtracni select pro dany typ objektu a s danymi podminkami
	 * @param int $objType typ objektu
	 * @param array $filterSet filtracni podminky
	 * @return Zend_Db_Select
	 * @throws Zend_Db_Table_Exception
	 */
	public static function prepareFilterSelect($objType, array $filterSet) {
		$tableDeadlines = new Deadline_Model_Deadlines();
		$select = new Zend_Db_Select(Zend_Db_Table_Abstract::getDefaultAdapter());
		$nameDead = $tableDeadlines->info("name");
		
		$tableDevices = new Application_Model_DbTable_TechnicalDevice();
		$nameDevices = $tableDevices->info("name");
		
		$tableEmployees = new Application_Model_DbTable_Employee();
		$nameEmployees = $tableEmployees->info("name");
		
		$devName = "CONCAT($nameDevices.`sort`, ' (', $nameDevices.`type`, ')')";
		$chemName = "chemical";
		$empName = "CONCAT($nameEmployees.first_name, ' ', $nameEmployees.surname)";
		
		$select->from($nameDead, array(
				new Zend_Db_Expr("$nameDead.*"),
				"is_valid" => new Zend_Db_Expr("CURRENT_DATE() < next_date"),
				"responsible_name" => new Zend_Db_Expr("TRIM(CONCAT(IFNULL(respemp.first_name, ''), ' ', IFNULL(respemp.surname, ''), IFNULL(user.name, ''), IFNULL(responsible_external_name, '')))"),
				"invalid_close" => new Zend_Db_Expr("ADDDATE(CURRENT_DATE(), INTERVAL 1 MONTH) > next_date"),
		));
		
		// zapis filtru dle klienta
		$select->where("$nameDead.client_id = ?", $filterSet["clientId"]);
		
		// vyhledani zodpovedne osoby
		$tableUsers = new Application_Model_DbTable_User();
		$nameUsers = $tableUsers->info("name");
		
		// sjednoceni s tabulkami
		$select->joinLeft(array("respemp" => $nameEmployees), "$nameDead.responsible_id = respemp.id_employee", array());
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
		
				$select->joinInner($nameDevices, "technical_device_id = id_technical_device", array("name" => new Zend_Db_Expr("CONCAT(IFNULL(`sort`, ''), ' (', IFNULL($nameDevices.`type`, '') , ')')")));
				break;
		
			case Deadline_Form_Deadline::TARGET_EMPLOYEE:
				$select->where("employee_id IS NOT NULL");
		
				$tableEmployees = new Application_Model_DbTable_Employee();
				$nameEmployees = $tableEmployees->info("name");
		
				$select->joinInner($nameEmployees, "employee_id = $nameEmployees.id_employee", array("name" => new Zend_Db_Expr("CONCAT($nameEmployees.first_name, ' ', $nameEmployees.surname)")));
				break;
				
			case Deadline_Form_Deadline::TARGET_UNDEFINED:
				$select->where("employee_id IS NULL")->where("technical_device_id IS NULL")->where("chemical_id IS NULL");
				$select->columns(array("name" => new Zend_Db_Expr("''")));
				break;
		
			default:
				throw new Zend_Db_Table_Exception("Invalid type of filter");
		}
		
		return $select;
	}
	
	public static function prepareImportForm($clientId, $subsidiaryId, Deadline_Form_Import $form) {
		$form->setClientId($clientId);
		
		// nacteni pobocek a sestaveni jejich seznamu
		$tableSubsidiaries = new Application_Model_DbTable_Subsidiary();
		$select = $tableSubsidiaries->select(false);
		$select->where("client_id = ?", $clientId)
						->where("active")->where("!deleted")->order("name");
		
		$select->from($tableSubsidiaries, array("id_subsidiary", "name" => new Zend_Db_Expr("CONCAT(subsidiary_town, ' ', subsidiary_street)")));
		
		$items = $tableSubsidiaries->fetchAll($select);
		$subIndex = array("0" => "---VYBERTE---");
		
		foreach ($items as $item) {
			$subIndex[$item->id_subsidiary] = $item->name;
		}
		
		$form->getElement("subsidiary_id")->setMultiOptions($subIndex);
		$form->getElement("subsidiary_id")->setValue($subsidiaryId);
	}
	
	/**
	 * pripravi fitlracni formular pro 
	 * 
	 * @param Deadline_Model_Rowset_Deadlines $deadlines seznam lhut
	 */
	private function _prepareFilterForm($deadlines, $form) {
		// prochazani dat a priprava filtru
		$periods = array("---");
		$kinds = array("---");
		$names = array("---");
		$specifics = array("---");
		
		// iterace nad daty a zapis polozek
		foreach ($deadlines as $item) {
			$periods[$item->period] = $item->period;
			$kinds[$item->kind] = $item->kind;
			$names[$item->name] = $item->name;
			$specifics[$item->specific] = $item->specific;
		}
		
		// serazeni polozek
		$periods = array_unique($periods);
		$kinds = array_unique($kinds);
		$names = array_unique($names);
		$specifics = array_unique($specifics);
		
		$form->getElement("period")->setMultiOptions($periods);
		$form->getElement("kind")->setMultiOptions($kinds);
		//$form->getElement("name")->setMultiOptions($names);
		$form->getElement("specific")->setMultiOptions($specifics);
        
        // naplneni filtracniho formulare pobockami
        $user = Zend_Auth::getInstance()->getIdentity();
        $where = array(
            "client_id = ?" => $this->_request->getParam("clientId", 0)
        );
        
        if ($user->role != My_Role::ROLE_ADMIN) {
            $userId = Zend_Auth::getInstance()->getIdentity()->id_user;

            $tableAssocs = new Application_Model_DbTable_UserHasSubsidiary();
            $nameAssocs = $tableAssocs->info("name");

            $subSelect = new Zend_Db_Select($tableAssocs->getAdapter());
            $subSelect->from($nameAssocs, array("id_subsidiary"));
            $subSelect->where("id_user = ?", $userId);
            
            $where["id_subsidiary in (?)"] = new Zend_Db_Expr($subSelect);
        }
        
        $tableSubsidiaries = new Application_Model_DbTable_Subsidiary();
        $subsidiaries = $tableSubsidiaries->fetchAll($where, array("subsidiary_town", "subsidiary_street"));
        
        $subsidiaryIndex = array("0" => "---VŠECHNY POBOČKY---");
        
        foreach ($subsidiaries as $item) {
            $subsidiaryIndex[$item->id_subsidiary] = sprintf("%s, %s", $item->subsidiary_town, $item->subsidiary_street);
        }
        
        $form->getElement("subsidiary_id")->setMultiOptions($subsidiaryIndex);
        
        $form->populate($this->_request->getParams());
        
		$this->view->filterForm = $form;
	}
}