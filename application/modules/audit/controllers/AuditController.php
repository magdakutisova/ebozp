<?php
class Audit_AuditController extends Zend_Controller_Action {
	
	/**
	 * radek s uzivatelem
	 * @var Application_Model_User
	 */
	protected $_user;
	
	/**
	 * radek auditu, pokud je nejaky nacten
	 * 
	 * @var Audit_Model_Row_Audit
	 */
	protected $_audit = null;
	
	/**
	 * identifikacni cislo auditu
	 * 
	 * @var int
	 */
	protected $_auditId = 0;
	
	public function init() {
		// zapsani helperu
		$this->view->addHelperPath(APPLICATION_PATH . "/views/helpers");
		
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
	}
	
	public function clientlistAction() {
		// nacteni seznamu pobocek
		$subsidiaries = $this->_user->getUserSubsidiaries();
		$subsidiaries[] = 0;
		
		$adapter = Zend_Db_Table_Abstract::getDefaultAdapter();
		
		// podminky pro nacteni otevrenych a uzavrenych auditu
		$open = array(
				"0"
		);
		
		// nactnei uzavrenych auditu
		$closed = array(
				$adapter->quoteInto("subsidiary_id in (?)", $subsidiaries)
		);
		
		$this->_loadList($open, $closed);
		
		$this->view->openRoute = "audit-review";
		$this->view->closedRoute = "audit-get";
	}
	
	public function coordlistAction() {
		// nacteni seznamu pobocek
		$subsidiaries = $this->_user->getUserSubsidiaries();
		$subsidiaries[] = 0;
	
		$adapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	
		// podminky pro nacteni otevrenych a uzavrenych auditu
		$open = array(
				"coordinator_id = " . $this->_user->getIdUser(),
				"coordinator_confirmed_at = 0",
				"auditor_confirmed_at > 0"
		);
	
		// nactnei uzavrenych auditu
		$closed = array(
				"coordinator_id = " . $this->_user->getIdUser(),
				"coordinator_confirmed_at > 0 OR auditor_confirmed_at = 0"
		);
	
		$this->_loadList($open, $closed);
		
		// nacteni neshod tykajicich se pobocky
	
		$this->view->openRoute = "audit-review";
		$this->view->closedRoute = "audit-get";
	}
	
	/*
	 * odeslani auditu ze strany koordinatora
	 */
	public function coordsubmitAction() {
		// kontrola auditu a opravneni k pristupu
		if (!$this->_audit) throw new Zend_Exception("Audit #$this->_auditId is not found");
		
		if ($this->_audit->coordinator_id != $this->_user->getIdUser()) throw new Zend_Exception("You are not coordinator for this audit");
		
		// parametry routy
		$params = array(
				"clientId" => $this->_audit->client_id,
				"subsidiaryId" => $this->_audit->subsidiary_id,
				"auditId" => $this->_audit->id
		);
		
		// kontrola dat
		$form = new Audit_Form_AuditAuditorSubmit();
		if (!$form->isValid($_REQUEST)) {
			$this->_forward("review");
			return;
		}
		
		// odstraneni neodeslanych neshod
		$tableAssocs = new Audit_Model_AuditsMistakes();
		$nameAssocs = $tableAssocs->info("name");
		$auditId = $this->_audit->id;
		
		// vygenerovani podminky smazani
		$where = array(
				"audit_id = " . $auditId,
				"submit_status = 0"
		);
		
		$tableAssocs->delete($where);
		
		// odstraneni osyrelych neshod
		$tableMistakes = new Audit_Model_AuditsRecordsMistakes();
		$where = array(
				"audit_id = " . $auditId,
				"id not in (select mistake_id from $nameAssocs where audit_id = $auditId)"
		);
		
		$tableMistakes->delete($where);
		
		// oznaceni auditu jako odeslaneho
		$this->_audit->coordinator_confirmed_at = new Zend_Db_Expr("NOW()");
		$this->_audit->is_closed = 1;
		$this->_audit->save();
		
		// oznaceni neshod, ktere jsou pouzity vice nez dvakrat
		$where = array(
				"audit_id != " . $this->_audit->id,
				"id in (select mistake_id from `$nameAssocs` where audit_id = " . $this->_audit->id . ")"
		);
		
		$tableMistakes->update(array("is_marked" => 1), $where);
		
		// smazani neshod. ktere nakonec nebyly pouzity
		$where = array(
				"audit_id = " . $this->_audit->id,
				"id not in (select mistake_id from `$nameAssocs` where audit_id = " . $this->_audit->id . ")"
		);
		
		$tableMistakes->delete($where);
		
		// presmerovani na get
		$this->_redirect($this->view->url($params, "audit-get"));
	}
	
	public function createAction() {
		// nacteni dat a naplneni formulare
		$subsidiaryId = $this->getRequest()->getParam("subsidiaryId", 0);
		
		$data = $this->getRequest()->getParam("audit", array());
		$data = array_merge(array("subsidiary_id" => $subsidiaryId), $data);
		
		// nacteni dat
		$tableSubsidiaries = new Application_Model_DbTable_Subsidiary();
		$diary = $tableSubsidiaries->find($subsidiaryId)->current();
		
		// pokud nebylo nic nalezeno, vraci se na index
		if (!$diary) throw new Zend_Exception("Pobočka nenalezena");
		
		// kontrola uzivatele
		$tableAssocs = new Application_Model_DbTable_UserHasSubsidiary();
		$assoc = $tableAssocs->fetchRow(array(
				"id_user = " . $this->_user->getIdUser(),
				"id_subsidiary = " . $diary->id_subsidiary
		));
		
		// kontrola validity asociace
		if (!$assoc) throw new Zend_Exception("Pobočka není přístupná");
		
		// nastaveni formulare
		$form = new Audit_Form_Audit();
		$form->fillSelects();
		
		// castecna validace dat, pokud je potreba
		$form->isValidPartial($data);
		$form->populate($data);
		
		// nastavei action
		$form->setAction($this->view->url(array(
				"clientId" => $diary->client_id,
				"subsidiaryId" => $subsidiaryId
				), "audit-post"));
		
		$this->view->form = $form;
	}
	
	public function editAction() {
		$this->view->layout()->setLayout("client-layout");
		
		// kontrola dat
		if (!$this->_audit) throw new Zend_Exception("Audit not found");
		
		// kotrnola pristupu
		$zeroDate = "0000-00-00 00:00:00";
		
		if ($this->_audit->auditor_confirmed_at != $zeroDate) throw new Zend_Exception("Audit #" . $this->_audit->id . " was closed for this action");
		
		// vytvoreni formulare
		$form = new Audit_Form_AuditFill();
		$form->fillSelects();
		$form->populate(array("audit" => $this->_audit->toArray()));
		
		// uprava datumu
		$doneAt = $this->_audit->done_at;
		list($year, $month, $day) = explode("-", $doneAt);
		$form->getElement("done_at")->setValue("$day. $month. $year");
		
		// uprava akce a tlacitka odeslat
		$form->getElement("submit")->setLabel("Uložit");
		
		$params = array(
				"clientId" => $this->_audit->client_id,
				"subsidiaryId" => $this->_audit->subsidiary_id,
				"auditId" => $this->_audit->id
		);
		
		$form->setAction($this->view->url($params, "audit-put"));
		
		// nacteni instanci formularu
		$formInstances = $this->_audit->getForms();
		
		// nactei formularu, ktere jeste mohou byt vyplneny
		$instanceForm = new Audit_Form_FormInstanceCreate();
		$instanceForm->loadUnused($formInstances);
		
		// vytvoreni odkazu pro novy formular
		$url = $this->view->url(array(
				"clientId" => $this->_audit->client_id,
				"subsidiaryId" => $this->_audit->subsidiary_id,
				"auditId" => $this->_auditId
		), "audit-form-instance");
		
		$instanceForm->setAction($url);
		
		// nacteni neshod tykajicich se auditu
		$mistakes = $this->_audit->getMistakes();
		
		// nacteni seznamu pracovist na pobocce
		$tableWorkplaces = new Application_Model_DbTable_Workplace();
		
		$workplaces = $tableWorkplaces->fetchAll("subsidiary_id = " . $this->_audit->subsidiary_id, "name");
		$workIndex = array();
		
		foreach ($workplaces as $item) {
			$workIndex[$item->id_workplace] = $item;
		}
		
		// formular uzavreni auditu
		$submitForm = new Audit_Form_AuditAuditorSubmit();
		$submitForm->setAction($this->view->url($params, "audit-technic-submit"));
		$submitForm->populate($_REQUEST);

		$this->view->subsidiary = $this->_audit->getSubsidiary();
		$this->view->client = $this->_audit->getClient();
		$this->view->form = $form;
		$this->view->instanceForm = $instanceForm;
		$this->view->formInstances = $formInstances;
		$this->view->audit = $this->_audit;
		$this->view->mistakes = $mistakes;
		$this->view->workIndex = $workIndex;
		$this->view->submitForm = $submitForm;
	}
	
	public function fillAction() {
		$data = $this->getRequest()->getParam("audit", array());
		$data = array_merge(array("id" => $this->_auditId), $data);
		
		// nacteni auditu
		$audit = $this->_audit;
		
		if (!$audit) throw new Zend_Exception("Audit #" . $this->_auditId . " has not been found");
		
		// nacteni dotazniku
		$tableQuestionaries = new Questionary_Model_Filleds();
		$questionaryRow = $tableQuestionaries->getById($audit->form_filled_id);
		
		$questionary = $questionaryRow->toClass();
		$form = new Audit_Form_AuditFill();
		
		$form->populate($audit->toArray());
		$form->populate($data)->isValidPartial($data);
		
		// nacteni klienta a pobocky
		$subsidiary = $audit->findParentRow("Application_Model_DbTable_Subsidiary");
		$client = $subsidiary->findParentRow("Application_Model_DbTable_Client");
		
		$this->view->layout()->setLayout("client-layout");
		
		$this->view->questionary = $questionary;
		$this->view->audit = $audit;
		$this->view->form = $form;
		$this->view->client = $client;
		$this->view->subsidiary = $subsidiary;
	}
	
	public function getAction() {
		// kontrola auditu
		if (!$this->_audit) throw new Zend_Exception("Audit #" . $this->_auditId . " has not been found");
		
		$audit = $this->_audit;
		$auditId = $audit->id;
		
		// nacteni doprovodnych informaci
		$auditor = $audit->getAuditor();
		$client = $audit->getClient();
		$coordinator = $audit->getCoordinator();
		$subsidiary = $audit->getSubsidiary();
		
		// nacteni formularu
		$forms = $audit->getForms();
		
		$this->view->layout()->setLayout("client-layout");
		
		// nacteni neshod z dotazniku
		$tableMistakes = new Audit_Model_AuditsRecordsMistakes();
		$tableAssocs = new Audit_Model_AuditsMistakes();
		$nameAssocs = $tableAssocs->info("name");
		
		// sestaveni podminek
		$where = array(
				"id in (select mistake_id from `$nameAssocs` where audit_id = $auditId and record_id is not null)"
		);
		
		// nacteni neshod z formularu
		$formMistakes = $tableMistakes->fetchAll($where);
		
		// nacteni neshod mimo formulare
		$where = array(
				"id in (select mistake_id from `$nameAssocs` where audit_id = $auditId and record_id is null)"
		);
		
		$otherMistakes = $tableMistakes->fetchAll($where);
		
		// nacteni pracovist
		$tableWorkplaces = new Application_Model_DbTable_Workplace();
		$workplaces = $tableWorkplaces->fetchAll("client_id = " . $this->_auditId, "name");
		
		// indexace pracovist dle ud
		$workplaceIndex = array();
		
		foreach ($workplaces as $item) {
			$workplaceIndex[$item->id_workplace] = $item;
		}
		
		$this->view->audit = $this->_audit;
		$this->view->subsidiary = $subsidiary;
		$this->view->client = $client;
		$this->view->coordinator = $coordinator;
		$this->view->auditor = $auditor;
		$this->view->forms = $forms;
		
		// zapis dat neshod
		$this->view->workplaceIndex = $workplaceIndex;
		$this->view->formMistakes = $formMistakes;
		$this->view->otherMistakes = $otherMistakes;
	}
	
	public function indexAction() {
		// nacteni klientu a pobocek, ktere jsou k dispozici
		$subSiDiariesIds = $this->_user->getUserSubsidiaries();
		
		// nacteni pobocek
		$tableSubsidiaries = new Application_Model_DbTable_Subsidiary();
		$subSiDiariesIds[] = 0;
		
		$subSiDiaries = $tableSubsidiaries->fetchAll(
				$tableSubsidiaries->getAdapter()->quoteInto("id_subsidiary in (?)", $subSiDiariesIds),
				array("client_id", "subsidiary_name")
		);
		
		// nacteni id klientu a indexace deniku podle klienta
		$clientIds = array(0);
		$subSiDiaryIndex = array();
		
		foreach ($subSiDiaries as $diary) {
			if (!isset($subSiDiaryIndex[$diary->client_id]))
				$subSiDiaryIndex[$diary->client_id] = array();
			
			$clientIds[] = $diary->client_id;
			$subSiDiaryIndex[$diary->client_id][] = $diary;
		}
		
		// nactnei klientu
		$tableClients = new Application_Model_DbTable_Client();
		$clients = $tableClients->fetchAll(
				$tableClients->getAdapter()->quoteInto("id_client in (?)", $clientIds),
				"company_name"
		);
		
		// zapis do view
		$this->view->clients = $clients;
		$this->view->subSiDiaryIndex = $subSiDiaryIndex;
	}
	
	public function techlistAction() {
		// podminky pro nacteni otevrenych a uzavrenych auditu
		$open = array(
				"auditor_id = " . $this->_user->getIdUser(),
				"auditor_confirmed_at = 0"
		);

		// nactnei uzavrenych auditu
		$closed = array(
				"auditor_id = " . $this->_user->getIdUser(),
				"auditor_confirmed_at > 0"
		);
		
		$this->_loadList($open, $closed);
		
		$this->view->openRoute = "audit-edit";
		$this->view->closedRoute = "audit-get";
	}
	
	public function postAction() {
		// nacteni dat
		$data = $this->getRequest()->getParam("audit", array());
		
		// nacteni a validace formulare
		$form = new Audit_Form_Audit();
		$form->fillSelects();
		
		if (!$form->isValid($data)) {
			$this->_forward("create");
			return;
		}
		
		// nacteni dat
		$tableUser = new Application_Model_DbTable_User();
		$tableSubsidiaries = new Application_Model_DbTable_Subsidiary();
		$tableAudits = new Audit_Model_Audits();
		
		$subsidiary = $tableSubsidiaries->find($form->getValue("subsidiary_id"))->current();
		$coordinator = $tableUser->find($form->getValue("coordinator_id"))->current();
		$auditor = $tableUser->find($this->_user->getIdUser())->current();
		
		// datum provedeni
		$doneAt = new Zend_Date($form->getValue("done_at"), "MM. dd. y");
		
		// vytvoreni zaznamu
		$audit = $tableAudits->createAudit($auditor, $coordinator, $subsidiary, $doneAt, $form->getValue("responsibile_name"));
		
		$this->_redirect(
				$this->view->url(array("clientId" => $subsidiary->client_id, "auditId" => $audit->id), "audit-edit")
		);
	}
	
	public function putAction() {
		// nacteni dat
		$data = $this->getRequest()->getParam("audit");
		
		$form = new Audit_Form_AuditFill();
		$form->getElement("summary")->setRequired(false);
		
		// kontrola validity
		$form->populate($data);
		if (!$form->isValidPartial($data)) $this->_forward("fill");
		
		// nacteni auditu
		$tableAudits = new Audit_Model_Audits();
		$audit = $tableAudits->getById($form->getValue("id"));
		
		// zapis poznamek a shrnuti
		$audit->summary = $form->getValue("summary");
		$audit->progress_note = $form->getValue("progress_note");
		
		// nastaveni ostanich dat
		
		$audit->save();
		
		// presmerovani na fill
		$this->_redirect(
				$this->view->url(array("clientId" => $audit->client_id, "auditId" => $audit->id), "audit-edit")
		);
	}
	
	public function reviewAction() {
		// kontrola auditu
		if (!$this->_audit) throw new Zend_Exception("Audit #" . $this->_auditId . " has not been found");
		
		$audit = $this->_audit;
		
		// nacteni doprovodnych informaci
		$auditor = $audit->getAuditor();
		$client = $audit->getClient();
		$coordinator = $audit->getCoordinator();
		$subsidiary = $audit->getSubsidiary();
		
		// nacteni formularu
		$forms = $audit->getForms();
		
		$this->view->layout()->setLayout("client-layout");
		
		// nacteni neshod
		$tableMistakes = new Audit_Model_AuditsRecordsMistakes();
		$tableRecords = new Audit_Model_AuditsRecords();
		$tableAssocs = new Audit_Model_AuditsMistakes();
		
		$nameRecords = $tableRecords->info("name");
		$nameAssocs = $tableAssocs->info("name");
		
		// sestaveni podminek
		$where = array(
				"id in (select mistake_id from `$nameRecords` where audit_id = $audit->id)",
				"workplace_id is null",
				"submit_status = " . Audit_Model_AuditsRecordsMistakes::SUBMITED_VAL_UNSUBMITED
		);
		
		// nacteni neshod z formularu
		$formMistakes = $tableMistakes->fetchAll($where);
		
		// nacteni asociaci z formularu a indexace dle id zaznamu
		$where = array(
				"audit_id = " . $this->_audit->id,
				"record_id is not null"
		);
		
		$assocs = $tableAssocs->fetchAll($where);
		$assocRecordIndex = array();
		
		foreach ($assocs as $item) {
			$assocRecordIndex[$item->record_id] = $item;
		}
		
		// nacteni neshod mimo formulare
		$where = array(
				"id in (select mistake_id from `$nameAssocs` where audit_id = $audit->id)",
				"workplace_id is not null"
		);
		
		$otherMistakes = $tableMistakes->fetchAll($where);
		
		// nacteni pracovist
		$tableWorkplaces = new Application_Model_DbTable_Workplace();
		$workplaces = $tableWorkplaces->fetchAll("client_id = " . $this->_auditId, "name");
		
		// indexace pracovist dle ud
		$workplaceIndex = array();
		
		foreach ($workplaces as $item) {
			$workplaceIndex[$item->id_workplace] = $item;
		}
		
		// nacteni a indexace asociaci
		$assocs = $tableAssocs->getByAudit($audit);
		$assocIndex = array();
		
		foreach ($assocs as $item) {
			$assocIndex[$item->mistake_id] = $item;
		}
		
		// vytvoreni formulare pro uplne uzavreni auditu
		$submitForm = new Audit_Form_AuditAuditorSubmit();
		$submitForm->setName("auditcoordsubmit");
		
		$url = $this->view->url(array("clientId" => $this->_audit->client_id, "subsidiaryId" => $this->_audit->subsidiary_id, "auditId" => $this->_audit->id), "audit-coordinator-submit");
		$submitForm->setAction($url);
		
		$submitForm->getElement("confirm")->setLabel("Uzavřít audit");
		
		$this->view->audit = $this->_audit;
		$this->view->subsidiary = $subsidiary;
		$this->view->client = $client;
		$this->view->coordinator = $coordinator;
		$this->view->auditor = $auditor;
		$this->view->forms = $forms;
		$this->view->submitForm = $submitForm;
		
		// zapis dat neshod
		$this->view->workplaceIndex = $workplaceIndex;
		$this->view->formMistakes = $formMistakes;
		$this->view->otherMistakes = $otherMistakes;
		$this->view->assocIndex = $assocIndex;
		$this->view->assocRecordIndex = $assocRecordIndex;
	}
	
	/**
	 * odeslani auditu ze strany technika
	 */
	public function techsubmitAction() {
		// kontrola auditu
		if (!$this->_audit) throw new Zend_Exception("Audit not found");
		
		if ($this->_audit->auditor_id != $this->_user->getIdUser()) throw new Zend_Exception("Invalid auditor");
		
		// parametry routy
		$params = array(
				"clientId" => $this->_audit->client_id,
				"subsidiaryId" => $this->_audit->subsidiary_id,
				"auditId" => $this->_audit->id
		);
		
		// kontrola dat
		$form = new Audit_Form_AuditAuditorSubmit();
		if (!$form->isValid($_REQUEST)) {
			$this->_forward("edit");
			return;
		}
		
		// odstraneni nepouzitych neshod
		$where = array(
				"audit_id = " . $this->_audit->id,
				"submit_status = " . Audit_Model_AuditsRecordsMistakes::SUBMITED_VAL_UNUSED
		);
		
		$tableMistakes = new Audit_Model_AuditsRecordsMistakes();
		$tableMistakes->delete($where);
		
		// zapis auditu jako uzavreneho ze strany technika
		$this->_audit->auditor_confirmed_at = new Zend_Db_Expr("NOW()");
		$this->_audit->save();
		
		
		// presmerovani na cteni auditu
		$this->_redirect($this->view->url($params, "audit-get"));
	}
	
	/**
	 * nacte seznamy auditu a zapise je do view
	 * @param array $openCond
	 * @param array $closedCont
	 * @throws Zend_Exception
	 */
	protected function _loadList(array $openCond, array $closedCond) {
		// nacteni klienta
		$tableClients = new Application_Model_DbTable_Client();
		$client = $tableClients->find($this->getRequest()->clientId)->current();
		
		// doplneni informaci o klientovi do podminek
		$openCond[] = "client_id = " . $client->id_client;
		$closedCond[] = "client_id = " . $client->id_client;
		
		if (!$client) throw new Zend_Exception("Client #" . $this->getRequest()->clientId . " has not been found");
		
		// nacteni auditu, kde je uzivatel auditorem a patri ke klientovi
		$tableAudits = new Audit_Model_Audits();
		
		// nejprve nacteni probihajicich auditu, ktere nebyly uzavreny technikem nebo byly znovu utevreny
		$open = $tableAudits->fetchAll($openCond, "done_at desc");
		
		// nactnei uzavrenych auditu
		$closed = $tableAudits->fetchAll($closedCond, "done_at desc");
		
		// nacteni seznamu pobocek
		$subIndex = self::loadSubdiaryIndex($client);
		
		$this->view->layout()->setLayout("client-layout");
		
		$this->view->client = $client;
		$this->view->open = $open;
		$this->view->closed = $closed;
		$this->view->subIndex = $subIndex;
	}
	
	/**
	 * vraci index pobocek
	 * @param Zend_Db_Table_Row_Abstract $client
	 */
	public static function loadSubdiaryIndex(Zend_Db_Table_Row_Abstract $client) {
		$tableSubsidiaries = new Application_Model_DbTable_Subsidiary();
		$subsidiaries = $tableSubsidiaries->fetchAll("client_id = " . $client->id_client);
		
		$subIndex = array();
		
		foreach ($subsidiaries as $sub) {
			$subIndex[$sub->id_subsidiary] = $sub;
		}
		
		return $subIndex;
	}
}