<?php
class Audit_CheckController extends Zend_Controller_Action {
	
	const CHECK_FOR_TECHNIC = 1;
	const CHECK_FOR_COORDINATOR = 2;
	const CHECK_FOR_CLIENT = 4;
	
	/**
	 * 
	 * @var Application_Model_User
	 */
	protected $_user;
	
	public function init() {
		// registrace view helperu
		$this->view->addBasePath(APPLICATION_PATH . "/views");
		
		// nacteni uzivatele
		$username = Zend_Auth::getInstance()->getIdentity()->username;
		$tableUsers = new Application_Model_DbTable_User();
		
		$this->_user = $tableUsers->getByUsername($username);
		
		// kontrola auditu
		$auditId = $this->getRequest()->getParam("auditId", 0);
		
		if ($auditId)  {
			$tableAudits = new Audit_Model_Audits();
			$this->_audit = $tableAudits->getById($auditId);
			$this->_auditId = $auditId;
		}
		
		$this->view->layout()->setLayout("client-layout");
	}
	
	public function actionAction() {
		// nacteni dat
		$mistakeId = $this->getRequest()->getParam("mistakeId", 0);
		$checkId = $this->getRequest()->getParam("checkId", 0);
		
		$tableAssocs = new Audit_Model_ChecksMistakes();
		$assoc = $tableAssocs->find($checkId, $mistakeId)->current();
		
		if (!$assoc) throw new Zend_Exception("hovno");
		
		// kontrola dat
		$form = new Audit_Form_CheckAction();
		$form->populate($this->getRequest()->getParams());
		
		$assoc->action = $form->getElement("action")->getValue();
		$assoc->save();
		
		// presmerovani zpet
		$params = array(
				"clientId" => $this->getRequest()->getParam("clientId"),
				"subsidiaryId" => $this->getRequest()->getParam("subsidiaryId"),
				"checkId" => $checkId,
				"mistakeId" => $mistakeId
		);
		
		$url = $this->view->url($params, "audit-mistake-checkedit");
		$this->_redirect($url);
	}
	
	public function coordsubmitAction() {
		// nacteni dat
		$check = $this->_loadDataFromDb(null, self::CHECK_FOR_COORDINATOR, true, $client, $subsidiary);
		
		// kontrola jestli je audit uzavren
		if ($check->checker_confirmed_at == "0000-00-00 00:00:00" || $check->coordinator_confirmed_at != "0000-00-00 00:00:00") throw new Zend_Exception("Check #$check->id can not be closed by coordinator");
		
		// odstraneni neakcnich neshod
		$tableAssocs = new Audit_Model_ChecksMistakes();
		$where = array(
				"check_id = " . $check->id,
				"!submit_status",
				"`action` != " . Audit_Model_ChecksMistakes::DO_NEW
		);
		
		$tableAssocs->delete($where);
		
		// samazani novych neshod, ktere nebyly potvrzeny
		$tableMistakes = new Audit_Model_AuditsRecordsMistakes();
		$nameAssocs = $tableAssocs->info("name");
		
		$where = array(
				"check_id = " . $check->id,
				"id in (select mistake_id from `$nameAssocs` where `check_id` = $check->id and !`submit_status` and `action` = " . Audit_Model_ChecksMistakes::DO_NEW . ")"
		);
		
		$tableMistakes->delete($where);
		
		$check->coordinator_confirmed_at = new Zend_Db_Expr("NOW()");
		$check->save();
		
		// presmerovani na get
		$params = array(
				"checkId" => $check->id,
				"subsidiaryId" => $check->subsidiary_id,
				"clientId" => $check->client_id
		);
		
		$url = $this->view->url($params, "audit-check-get");
		
		$this->_redirect($url);
	}
	
	public function createAction() {
		// nacteni dat
		$clientId = $this->getRequest()->getParam("clientId", 0);
		$subsidiaryId = $this->getRequest()->getParam("subsidiaryId", 0);
		
		// nacteni radku z databaze
		$tableClients = new Application_Model_DbTable_Client();
		$tableSubsidiaries = new Application_Model_DbTable_Subsidiary();
		
		$client = $tableClients->find($clientId)->current();
		$subsidiary = $tableSubsidiaries->find($subsidiaryId)->current();
		
		// kontrola nactenych dat
		if (!$client || !$subsidiary) throw new Zend_Exception("Client or subsidiary has not been found");
		if ($client->id_client != $subsidiary->client_id) throw new Zend_Exception("Subsidiary #$subsidiaryId is not belongs to client #$clientId");
		
		// nastaveni formulare
		$form = new Audit_Form_CheckCreate();
		$form->fillSelects();
		$form->isValidPartial($_REQUEST);
		
		// nastaveni adresy
		$params = array("clientId" => $clientId, "subsidiaryId" => $subsidiaryId);
		$url = $this->view->url($params, "audit-check-post");
		$form->setAction($url);
		$form->setMethod(Zend_Form::METHOD_POST);
		
		$this->view->form = $form;
		$this->view->client = $client;
		$this->view->subsidiary = $subsidiary;
	}
	
	public function editAction() {
		// nacteni odeslanych dat
		$clientId = $this->getRequest()->getParam("clientId", 0);
		$subsidiaryId = $this->getRequest()->getParam("subsidiaryId", 0);
		$checkId = $this->getRequest()->getParam("checkId", 0);
		
		// nacteni z databaze a kontrola shody dat
		$tableChecks = new Audit_Model_Checks();
		$check = $tableChecks->getById($checkId);
		
		if ($check->client_id != $clientId) throw new Zend_Exception("Check #$checkId is not belongs to client #$clientId");
		if ($check->subsidiary_id != $subsidiaryId) throw new Zend_Exception("Check #$checkId is not belongs to subsidiary #$subsidiaryId");
		
		// nactnei klienta a pobocky
		$client = $check->getClient();
		$subsidiary = $check->getSubsidiary();
		
		// vytvoreni formulare a jeho nastaveni
		$form = new Audit_Form_CheckEdit();
		$form->fillSelects();
		$data = $check->toArray();
		
		list($year, $month, $day) = explode("-", $data["done_at"]);
		$data["done_at"] = "$day. $month. $year";
		
		$data["responsibile_name"] = $data["responsibiles"];
		$form->populate($data);
		$form->isValidPartial($this->getRequest()->getParams());
		$form->getElement("submit")->setLabel("Uložit");
		
		$params = array(
				"clientId" => $clientId,
				"subsidiaryId" => $subsidiaryId,
				"checkId" => $checkId
		);
		
		$url = $this->view->url($params, "audit-check-put");
		$form->setAction($url);
		
		// nacteni neodstranenych neshod pobocky
		$mistakes = $check->getMistakes();
		
		// nacteni asociaci a indexace dle neshody
		$assocs = $check->findDependentRowset("Audit_Model_ChecksMistakes");
		$assocIndex = array();
		
		foreach ($assocs as $item) {
			$assocIndex[$item->mistake_id] = $item;
		}
		//die(var_dump(array_keys($assocIndex)));
		// nacteni a indexace pracovist na pobocce
		$tableWorkplaces = new Application_Model_DbTable_Workplace();
		$workplaces = $tableWorkplaces->fetchAll("subsidiary_id = " . $subsidiary->id_subsidiary);
		
		$workplaceIndex = array();
		$workplaceList = array();
		
		foreach ($workplaces as $item) {
			$workplaceList[$item->id_workplace] = $item->name;
			$workplaceIndex[$item->id_workplace] = $item;
		}
		
		// formular nove neshody
		$newMistakeForm = new Audit_Form_MistakeCreateSubsidiarySelect();
		$newMistakeForm->getElement("workplace_id")->setMultiOptions($workplaceList);
		$newMistakeForm->setAction(
				$this->view->url($params, "audit-check-createmistake")
		);
		
		// vyhodnoceni moznosti uzavrit audit
		if ($form->getValue("summary")) {
			$formSubmit = new Audit_Form_CheckCheckerSubmit();
			$formSubmit->populate($this->getRequest()->getParams());
			
			$url = $this->view->url($params, "audit-check-techsubmit");
			$formSubmit->setAction($url);
			$this->view->formSubmit = $formSubmit;
		}
		
		$this->view->check = $check;
		$this->view->client = $client;
		$this->view->subsidiary = $subsidiary;
		$this->view->editForm = $form;
		$this->view->mistakes = $mistakes;
		$this->view->assocIndex = $assocIndex;
		$this->view->newMistakeForm = $newMistakeForm;
		$this->view->workplaceIndex = $workplaceIndex;
	}
	
	public function getAction() {
		// nacteni informaci
		$check = $this->_loadDataFromDb(null, self::CHECK_FOR_CLIENT | self::CHECK_FOR_COORDINATOR | self::CHECK_FOR_TECHNIC, true, $client, $subsidiary);
		
		// nacteni neshod
		$mistakes = $check->getMistakes();
		$assocs = $check->findDependentRowset("Audit_Model_ChecksMistakes", "check");
		$assocIndex = array();
		
		foreach ($assocs as $item) {
			$assocIndex[$item->mistake_id] = $item;
		}
		
		// nacteni technika a koordinatora
		$tableUsers = new Application_Model_DbTable_User();
		$users = $tableUsers->find(array($check->coordinator_id, $check->checker_id));
		$userIndex = array();
		
		foreach ($users as $user) {
			$userIndex[$user->id_user] = $user;
		}
		
		$this->view->client = $client;
		$this->view->subsidiary = $subsidiary;
		$this->view->mistakes = $mistakes;
		$this->view->assocIndex = $assocIndex;
		$this->view->check = $check;
		$this->view->userIndex = $userIndex;
	}
	
	public function indexAction() {
		// nacteni klienta
		$clientId = $this->getRequest()->getParam("clientId", 0);
		$tableClients = new Application_Model_DbTable_Client();
		
		$client = $tableClients->find($clientId)->current();
		if (!$client) throw new Zend_Exception("Client #$clientId has not been found");
		
		// nacteni proverek
		$tableChecks = new Audit_Model_Checks();
		$checks = $tableChecks->fetchAll(array("client_id = " . $client->id_client), "created_at desc");
		
		// nactnei id pobocek
		$subsidiaryIds = array(0);
		
		foreach ($checks as $item) {
			$subsidiaryIds[] = $item->subsidiary_id;
		}
		
		// nctnei pobocek a jejich indexace
		$tableSubsidiaries = new Application_Model_DbTable_Subsidiary();
		$subsidiaries = $tableSubsidiaries->find($subsidiaryIds);
		$subsidiaryIndex = array();
		
		foreach ($subsidiaries as $item) {
			$subsidiaryIndex[$item->id_subsidiary] = $item;
		}
		
		$this->view->client = $client;
		$this->view->subsidiaryIndex = $subsidiaryIndex;
		$this->view->checks = $checks;
		$this->view->user = $this->_user;
	}
	
	public function postAction() {
		// nacteni odeslanych dat
		$clientId = $this->getRequest()->getParam("clientId", 0);
		$subsidiaryId = $this->getRequest()->getParam("subsidiaryId", 0);
		
		// nacteni a kontrola dat z databaze
		$tableClients = new Application_Model_DbTable_Client();
		$tableSubsidiaries = new Application_Model_DbTable_Subsidiary();
		
		$client = $tableClients->find($clientId)->current();
		$subsidiary = $tableSubsidiaries->find($subsidiaryId)->current();
		
		// kontrola nactenych dat
		if (!$client || !$subsidiary) throw new Zend_Exception("Client or subsidiary has not been found");
		if ($client->id_client != $subsidiary->client_id) throw new Zend_Exception("Subsidiary #$subsidiaryId is not belongs to client #$clientId");
		
		// nastaveni formulare
		$form = new Audit_Form_CheckCreate();
		$form->fillSelects();
		
		// kontrola validy
		if (!$form->isValid($_REQUEST)) {
			$this->_forward("create");
			return;
		}
		
		// nacteni uzivatelu
		$tableUsers = new Application_Model_DbTable_User();
		$technic = $tableUsers->find($this->_user->getIdUser())->current();
		$coordinator = $tableUsers->find($form->getElement("coordinator_id")->getValue())->current();
		
		// vytvoreni data
		$doneAt = new Zend_Date($form->getValue("done_at"), "dd. MM. y");
		
		// vytvoreni proverky
		$tableChecks = new Audit_Model_Checks();
		$check = $tableChecks->createCheck($subsidiary, $technic, $coordinator, $form->getValue("responsibile_name"), $doneAt);
		
		// nacteni neshod pobocky a vytvoreni prvotnich asociaci
		$tableMistakes = new Audit_Model_AuditsRecordsMistakes();
		$where = array("subsidiary_id = " . $subsidiary->id_subsidiary, "!is_removed");
		$mistakes = $tableMistakes->fetchAll($where);
		$toInsert = array();
		
		foreach ($mistakes as $mistake) {
			$toInsert[] = "($check->id, $mistake->id, " . Audit_Model_ChecksMistakes::DO_NOTHING . ")";
		}
		
		// pokud je co vlozit, vlozi se to
		if ($toInsert) {
			$tableAssocs = new Audit_Model_ChecksMistakes();
			$nameAssocs = $tableAssocs->info("name");
			
			$sql = "insert into `$nameAssocs` (check_id, mistake_id, `action`) values " . implode(",", $toInsert);
			$tableAssocs->getAdapter()->query($sql);
		}
		
		// presmerovani na edit
		$params = array(
				"clientId" => $subsidiary->client_id,
				"subsidiaryId" => $subsidiary->id_subsidiary,
				"checkId" => $check->id
		);
		
		$url = $this->view->url($params, "audit-check-edit");
		
		$this->_redirect($url);
	}
	
	public function putAction() {
		// nacteni dat
		$checkId = $this->getRequest()->getParam("checkId", 0);
		$tableChecks = new Audit_Model_Checks();
		$check = $tableChecks->getById($checkId);
		
		if (!$check) throw new Zend_Exception("Check #$checkId not found");
		
		// kontrola dat
		$form = new Audit_Form_CheckEdit();
		$form->getElement("summary")->setRequired(false);
		$form->fillSelects();
		
		if (!$form->isValid($this->getRequest()->getParams())) {
			$this->_forward("edit");
			return;
		}
		
		// nastaveni novych hodnot
		$data = $form->getValues(true);
		list($day, $month, $year) = explode(". ", $data["done_at"]);
		$data["done_at"] = "$year-$month-$day";
		
		$check->setFromArray($data)->save();
		
		// presmerovani na editaci
		$params = array(
				"clientId" => $this->getRequest()->getParam("clientId", 0),
				"subsidiaryId" => $this->getRequest()->getParam("subsidiaryId", 0),
				"checkId" => $this->getRequest()->getParam("checkId", 0)
		);
		
		$url = $this->view->url($params, "audit-check-edit");
		$this->_redirect($url);
	}
	
	public function reviewAction() {
		$this->editAction();
		
		if ($this->view->formSubmit) {
			$this->view->formSubmit->setName("checkcoordsubmit");
			
			$params = array(
					"clientId" => $this->getRequest()->getParam("clientId", 0),
					"subsidiaryId" => $this->getRequest()->getParam("subsidiaryId", 0),
					"checkId" => $this->getRequest()->getParam("checkId", 0)
			);
			
			$url = $this->view->url($params, "audit-check-coordsubmit");
			
			$this->view->formSubmit->setAction($url);
		}
	}
	
	public function techsubmitAction() {
		// nacteni formulare a kontrola dat
		$form = new Audit_Form_CheckCheckerSubmit();
		
		if (!$form->isValid($this->getRequest()->getParams())) {
			$this->_forward("edit");
			return;
		}
		
		// nacteni proverky
		$checkId = $this->getRequest()->getParam("checkId", 0);
		$clientId = $this->getRequest()->getParam("clientId", 0);
		$subsidiaryId = $this->getRequest()->getParam("subsidiaryId", 0);
		
		$tableChecks = new Audit_Model_Checks();
		$check = $tableChecks->getById($checkId);
		
		if (!$check) throw new Zend_Exception("Check #$checkId has not been found");
		if ($check->checker_id != $this->_user->getIdUser()) throw new Zend_Exception();
		
		$check->checker_confirmed_at = new Zend_Db_Expr("NOW()");
		$check->save();
		
		// presmerovani na get
		$params = array(
				"checkId" => $checkId,
				"clientId" => $clientId,
				"subsidiaryId" => $subsidiaryId
		);
		
		$url = $this->view->url($params, "audit-check-get");
		$this->_redirect($url);
	}
	
	/**
	 * nacte proverku
	 * 
	 * @param int $checkId idnetifikacni cislo proverky
	 * @param bool $doCheck prepinac, jeslti se maji provest kontroly vlastnictvi klienta a pobocky
	 * @param Zend_Db_Table_Row_Abstract $client klient
	 * @param Zend_Db_Table_Row_Abstract $subsidiary pobocka
	 */
	protected function _loadDataFromDb($checkId = null, $checkAction = 0, $doChecks = true, &$client = null, &$subsidiary = null) {
		if (!$checkId) $checkId = $this->getRequest()->getParam("checkId");
		$tableChecks = new Audit_Model_Checks();
		$check = $tableChecks->getById($checkId);
		
		if (!$check) throw new Zend_Exception("Check #$checkId not found");
		
		if ($doChecks) {
			$clientId = $this->getRequest()->getParam("clientId", 0);
			$subsidiaryId = $this->getRequest()->getParam("subsidiaryId", 0);
			
			if ($check->client_id != $clientId) throw new Zend_Exception("Check #$checkId is not belongs to client #$clientId");
			if ($check->subsidiary_id != $subsidiaryId) throw new Zend_Exception("Check #$checkId is not belongs to subsidiary #$subsidiaryId");
		}
		
		// kontrola pristupudle role
		if ($checkAction) {
			$ok = false;
			
			if ($checkAction & self::CHECK_FOR_TECHNIC) {
				if ($check->checker_id == $this->_user->getIdUser()) $ok = true;
			}
			
			if ($checkAction & self::CHECK_FOR_COORDINATOR) {
				if ($check->coordinator_id == $this->_user->getIdUser()) $ok = true;
			}
			
			if (!$ok) throw new Zend_Exception("You have not permisions for access to this check");
		}
		
		// nacteni dalsich dat
		$client = $check->getClient();
		$subsidiary = $check->getSubsidiary();
		
		return $check;
	}
}
