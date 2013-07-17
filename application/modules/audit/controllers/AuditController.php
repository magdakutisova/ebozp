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
		
		$this->view->layout()->setLayout("client-layout");
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
		if (!$assoc && $this->_user->getRoleId() != My_Role::ROLE_ADMIN) throw new Zend_Exception("Pobočka není přístupná");
		
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
		
		// kontrola, jeslti je uzivatel opravnen kopirovat audity
		$tableCopyables = new Audit_Model_Copyables();
		
		if ($tableCopyables->isCopyable($this->_user)) {
			// kontrola, jeslti uzivatel uz vytvoril na pobocce audit
			$tableAudits = new Audit_Model_Audits();
			$audit = $tableAudits->fetchRow(
					array(
						"subsidiary_id = " . $subsidiaryId, 
						"auditor_id = " . $this->_user->getIdUser()), 
					"done_at desc");
			
			if ($audit) {
				// protoze je uzivatel opravnen kopirovat a predchozi audit existuje, vytvori se kopirovaci formular
				$cloneForm = new Audit_Form_Clone();
				$cloneForm->setAction($this->view->url(array(
						"clientId" => $diary->client_id,
						"subsidiaryId" => $subsidiaryId,
						"auditId" => $audit->id
						), "audit-clone"));
				
				$this->view->cloneForm = $cloneForm;
				$this->view->oldAudit = $audit;
			}
		}
		
		$this->view->form = $form;
	}
	
	public function cloneAction() {
		// protoze je klonovani omezeno na povoleni, musi se toto povoleni zkontrolovat
		$tableCopyables = new Audit_Model_Copyables();
		if (!$tableCopyables->isCopyable($this->_user)) throw new Zend_Exception("You are have not permision to clone audits");
		
		// nacteni auditu
		$tableAudits = new Audit_Model_Audits();
		$audit = $tableAudits->getById($this->getRequest()->getParam("auditId", 0));
		if (!$audit) throw new Zend_Exception("Audit #" . $this->getRequest()->getParam("auditId") . " has not been found");
		if ($audit->auditor_id != $this->_user->getIdUser()) throw new Zend_Exception("Audit #$audit->id is not belongs to you");
		if (!$audit->is_closed) throw new Zend_Exception("Audit #$audit->id has not been closed yet");
		
		// prevedeni dat do pole a zapis nove pollozky do databaze
		$auditData = array(
				"done_at" => new Zend_Db_Expr("CURRENT_TIMESTAMP"),
				"auditor_id" => $audit->auditor_id,
				"coordinator_id" => $audit->coordinator_id,
				"client_id" => $audit->client_id,
				"subsidiary_id" => $audit->subsidiary_id,
				"responsibile_name" => $audit->responsibile_name,
				"progress_note" => $audit->progress_note,
				"summary" => $audit->summary
		);
		
		$newAudit = $tableAudits->createRow($auditData);
		$newAudit->save();
		
		// prekopirovani formularu
		$tableForms = new Audit_Model_AuditsForms();
		
		// nacteni id formularu stareho auditu
		$oldForms = $audit->getForms();
		$formIds = array(0);
		$formIndex = array();
		
		// musi se zjistit vsechny id formularu a naindexovat se podle id definice
		foreach ($oldForms as $form) {
			if ($form->form_id) {
				$formIds[] = $form->form_id;
			}
			
			$formIndex[$form->form_id] = $form;
		}
		
		// nacteni jmen tabulek
		$tableMistakes = new Audit_Model_AuditsRecordsMistakes();
		$tableAuditsMistake = new Audit_Model_AuditsMistakes();
		$tableRecords = new Audit_Model_AuditsRecords();
		$nameMistakes = $tableMistakes->info("name");
		$nameRecords = $tableRecords->info("name");
		$nameAuditsMistake = $tableAuditsMistake->info("name");
		
		// nacteni radku formularu a prekopirovani dat
		$tableFormsDefs = new Audit_Model_Forms();
		$forms = $tableFormsDefs->find($formIds);
		
		$adapter = $tableAudits->getAdapter();
		
		// zapis formularu a zapis neshod
		foreach ($forms as $form) {
			$instance = $tableForms->createForm($newAudit, $form);
			$oldFormId = $formIndex[$form->questionary_id]->id;
			
			// zjisteni offsetu stare a nove prvni neshody
			$offset = $this->_getMistakeOffset($nameMistakes, $nameRecords, $oldFormId, $instance->id, $adapter);
			$sql = "update `$nameAuditsMistake` as t1, `$nameRecords` as t2 set t1.mistake_id = t2.mistake_id where t2.mistake_id = t1.mistake_id - $offset and t1.audit_id = $newAudit->id and t2.audit_form_id = $oldFormId";
			$adapter->query($sql);
			
			// update skore
			$offset = $this->_getRecordOffset($nameRecords, $oldFormId, $instance->id, $adapter);
			$sql = "update $nameRecords as t1, $nameRecords as t2 set t1.score = t2.score where t2.id = t1.id - $offset";
			$adapter->query($sql);
		}
		
		// presmerovani na get
		$url = $this->view->url(array(
				"clientId" => $newAudit->client_id,
				"subsidiaryId" => $newAudit->subsidiary_id,
				"auditId" => $newAudit->id
		), "audit-edit");
		
		$this->_redirect($url);
	}
	
	public function editAction() {
		$this->view->layout()->setLayout("client-layout");
		
		// kontrola dat
		if (!$this->_audit) throw new Zend_Exception("Audit not found");
		
		// kotrnola pristupu
		$zeroDate = "0000-00-00 00:00:00";
		
		// kontrola pristupu
		$userId = $this->_user->getIdUser();
		if ($this->_audit->auditor_confirmed_at[0] != '0' && $this->_audit->auditor_id == $userId 
				|| ($this->_audit->auditor_confirmed_at[0] == '0' || $this->_audit->coordinator_confirmed_at[0] != '0') && $this->_audit->coordinator_id == $userId
				|| $this->_audit->coordinator_id != $userId && $this->_audit->auditor_id != $userId) throw new Zend_Exception("Audit #" . $this->_audit->id . " was closed for this action");
		
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
		$auditMistakes = $this->_audit->getMistakes();
		$otherMistakes = $this->_audit->getSupplementMistakes();
		
		// nacteni seznamu pracovist na pobocce
		$tableWorkplaces = new Application_Model_DbTable_Workplace();
		
		$workplaces = $tableWorkplaces->fetchAll("subsidiary_id = " . $this->_audit->subsidiary_id, "name");
		$workIndex = array();
		$workSelect = array();
		$selectWorkplace = new Audit_Form_MistakeCreateSubsidiarySelect();
		
		foreach ($workplaces as $item) {
			$workIndex[$item->id_workplace] = $item;
			$workSelect[$item->id_workplace] = $item->name;
		}
		
		$selectWorkplace->getElement("workplace_id")->setMultiOptions(array_merge(array("0" => "---MIMO PRACOVIŠTĚ---"), $workSelect));
		$selectWorkplace->setAction($this->view->url($params, "audit-mistake-createalone2"));
		
		// nacteni zaznamu o komentarich k pracovistim a vygenerovani seznamu formularu
		$tableComments = new Audit_Model_AuditsWorkcomments();
		$nameComments = $tableComments->info("name");
		$nameWorkplaces = $tableWorkplaces->info("name");
		$sql = "select name, id_workplace as workplace_id, comment, comment is null as `create` from `$nameWorkplaces` left join `$nameComments` on (id_workplace = workplace_id and audit_id = " . $this->_audit->id . ") where subsidiary_id = " . $this->_audit->subsidiary_id . " order by name";
		$result = Zend_Db_Table_Abstract::getDefaultAdapter()->query($sql);
		$commentForms = array();
		$url = $this->view->url(array("clientId" => $this->_audit->client_id, "auditId" => $this->_audit->id), "audit-post-wcomment");
		
		while ($record = $result->fetch()) {
			$commentForm = new Audit_Form_WorkplaceComment();
			$commentForm->populate($record)->setAction($url);
			$commentForms[] = $commentForm;
		}
		
		// formular uzavreni auditu
		$submitForm =new Audit_Form_AuditAuditorSubmit();
		$submitForm->setAction($this->view->url($params, "audit-submit"));
		$submitForm->populate($_REQUEST);
		
		if ($this->_user->getRoleId() == My_Role::ROLE_COORDINATOR) {
			$submitForm->getElement("confirm")->setLabel("Uzavřít audit");
		}
		
		$this->_initWorkForms();
		
		$this->view->subsidiary = $this->_audit->getSubsidiary();
		$this->view->client = $this->_audit->getClient();
		$this->view->form = $form;
		$this->view->instanceForm = $instanceForm;
		$this->view->formInstances = $formInstances;
		$this->view->audit = $this->_audit;
		$this->view->workIndex = $workIndex;
		$this->view->submitForm = $submitForm;
		$this->view->selectWorkplace = $selectWorkplace;
		$this->view->workSelect = $workSelect;
		$this->view->auditMistakes = $auditMistakes;
		$this->view->otherMistakes = $otherMistakes;
		$this->view->commentForms = $commentForms;
		$this->view->userId = $userId;
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
		
		// sestaveni podminek
		$nameAssocs = $tableAssocs->info("name");
		$mistakes = $tableMistakes->fetchAll(array("is_submited", "id in (select mistake_id from $nameAssocs where audit_id = $audit->id)"));
		
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
		$this->view->mistakes = $mistakes;
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
		
		$this->view->layout()->setLayout("layout");
	}
	
	public function listAction() {
		// nacteni klienta
		$clientId = $this->getRequest()->getParam("clientId", 0);
		$tableClients = new Application_Model_DbTable_Client();
		$client = $tableClients->find($clientId)->current();
		
		// nacteni pobocek
		$subsidiaries = $client->findDependentRowset("Application_Model_DbTable_Subsidiary");
		$subIndex = array();
		
		foreach ($subsidiaries as $item) {
			$subIndex[$item->id_subsidiary] = $item;
		}
		
		// nacteni auditu, ktere ma uzivatel k dispozici
		$userId = $this->_user->getIdUser();
		
		$tableAudits = new Audit_Model_Audits();
		$audits = $tableAudits->fetchAll("auditor_id = $userId or coordinator_id = $userId", "done_at");
		
		$this->view->client = $client;
		$this->view->audits = $audits;
		$this->view->subIndex = $subIndex;
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
		$audit = $tableAudits->createAudit($auditor, $coordinator, $subsidiary, $doneAt, $form->getValue("is_check"), $form->getValue("responsibile_name"));
		
		// prirazeni existujicich neshod
		$tableAssocs = new Audit_Model_AuditsMistakes();
		$tableMistakes = new Audit_Model_AuditsRecordsMistakes();
		
		$nameAssocs = $tableAssocs->info("name");
		$nameMistakes = $tableMistakes->info("name");
		
		$sql = "insert into $nameAssocs (audit_id, mistake_id, record_id, is_submited, status) select $audit->id, id, null, 0, 0 from $nameMistakes where subsidiary_id = $audit->subsidiary_id and !is_removed";
		$tableAssocs->getAdapter()->query($sql);
		
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
		
		// datum je ve spatnem formatu - musi se prepsat na SQL standard
		list($day, $month, $year) = explode(". ", $form->getValue("done_at"));
		$form->getElement("done_at")->setValue("$year-$month-$day");
		
		// zapis poznamek a shrnuti
		$audit->setFromArray($form->getValues(true));
		
		// nastaveni ostanich dat
		
		$audit->save();
		
		// presmerovani na fill nebo review, dle role
		if ($this->_user->getRoleId() == My_Role::ROLE_TECHNICIAN) {
			$url = $this->view->url(array("clientId" => $audit->client_id, "auditId" => $audit->id), "audit-edit");
		} else {
			$url = $this->view->url(array("clientId" => $audit->client_id, "auditId" => $audit->id), "audit-review");
		}
		
		$this->_redirect($url);
	}
	
	public function submitAction() {
		// kontrola dat
		$form = new Audit_Form_AuditAuditorSubmit();
		
		if (!$form->isValid($this->getRequest()->getParams())) {
			// neni zaskrtnuto potvrzovaci policko
			$this->_forward("edit");
			
			return;
		}
		
		// kontrola opravneni pristupu
		$userId = $this->_user->getIdUser();
		$roleId = $this->_user->getRoleId();
		
		// provedeni akci dle typu uzivatele, ktery odeslal audit
		if ($roleId == My_Role::ROLE_TECHNICIAN && $this->_audit->auditor_id == $userId && $this->_audit->auditor_confirmed_at[0] == '0') {
			// odeslal to technik - jen se to podepise jinak se nic nedeje
			$this->_audit->auditor_confirmed_at = new Zend_Db_Expr("NOW()");
			$this->_audit->save();
			
			// presmerovani na get
			$url = $this->view->url(array("auditId" => $this->_audit->id, "clientId" => $this->_audit->client_id), "audit-get");
			$this->_redirect($url);
			
			return;
		} else if ($roleId == My_Role::ROLE_COORDINATOR && $this->_audit->coordinator_id == $userId && $this->_audit->auditor_confirmed_at[0] != '0' && $this->_audit->coordinator_confirmed_at[0] == '0') {
			// odstrani se neshody, ktere nakonec nebyly pouzity
			$tableMistakes = new Audit_Model_AuditsRecordsMistakes();
			$tableAssocs = new Audit_Model_AuditsMistakes();
			$nameMistakes = $tableMistakes->info("name");
			$nameAssocs = $tableAssocs->info("name");
			$auditId = $this->_audit->id;
			
			$where = array(
					"audit_id = $auditId",
					"id not in (select mistake_id from `$nameAssocs` where audit_id = $auditId)"
			);
			
			$tableMistakes->delete($where);
			
			// aktualizace stavu neshod
			$begin = "update `$nameAssocs`, `$nameMistakes` set ";
			$sql1 = "$begin is_removed = 1 where `$nameAssocs`.`status` = 1 and id = mistake_id";
			$tableMistakes->getAdapter()->query("$sql1");
			
			// odeslani neshod, ktere se maji odeslat
			$sql = "update `$nameMistakes` set is_submited = 1 where id in (select mistake_id from `$nameAssocs` where audit_id = $auditId)";
			$tableMistakes->getAdapter()->query($sql);
			
			// potvrdi se audit
			$this->_audit->coordinator_confirmed_at = new Zend_Db_Expr("NOW()");
			$this->_audit->is_closed = 1;
			$this->_audit->save();
			
			// presmerovani na get
			$url = $this->view->url(array("auditId" => $this->_audit->id, "clientId" => $this->_audit->client_id), "audit-get");
			$this->_redirect($url);
				
			return;
		}
		
		throw new Zend_Exception("Unknouwn action for this role and audit");
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
	
	protected function _getMistakeOffset($nameMistakes, $nameRecords, $oldFormId, $newFormId, $adapter) {
		// nacteni prvniho id stare neshody
		$sql = "select id from `$nameMistakes` where id in (select mistake_id from `$nameRecords` where audit_form_id = $oldFormId and mistake_id is not null order by mistake_id) order by id limit 0, 1";
		$oldId = $adapter->query($sql)->fetchColumn();
		
		// nacteni prvniho id nove neshody
		$sql = "select id from `$nameMistakes` where id in (select mistake_id from `$nameRecords` where audit_form_id = $newFormId order by mistake_id) order by id limit 0, 1";
		$newId = $adapter->query($sql)->fetchColumn();
		
		return $newId - $oldId;
	}
	
	protected function _getRecordOffset($nameRecords, $oldFormId, $newFormId, $adapter) {
		$sql = "select id from `$nameRecords` where audit_form_id = $oldFormId order by id limit 0, 1";
		$oldId = $adapter->query($sql)->fetchColumn();
		
		$sql = "select id from `$nameRecords` where audit_form_id = $newFormId order by id limit 0, 1";
		$newId = $adapter->query($sql)->fetchColumn();
		
		return $newId - $oldId;
	}
	
	protected function _initWorkForms() {
		$placeForm = new Application_Form_Workplace();
		$this->view->placeForm = $placeForm;
		$placeForm->setAction($this->view->url(array("auditId" => $this->_audit->id, "clientId" => $this->_audit->client_id), "audit-workplace-post"));
		
		// nacteni seznamu pobocek
		$tableSubsidiary = new Application_Model_DbTable_Subsidiary();
		$subsidiaries = $tableSubsidiary->fetchAll("client_id = " . $this->_audit->client_id);
		$placeForm->getElement("subsidiary_id")->setMultiOptions($this->generateIndex($subsidiaries, "subsidiary_name", "id_subsidiary"));
		$placeForm->save->setLabel("Uložit");
		
		// nacteni pracovnich pozic
		$tablePositions = new Application_Model_DbTable_Position();
		$placeForm->getElement("positionList")->setMultiOptions($tablePositions->getPositions($this->_audit->client_id));
		
		// nacteni cinnosti
		$tableWorks = new Application_Model_DbTable_Work();
		$placeForm->getElement("workList")->setMultiOptions($tableWorks->getWorks($this->_audit->client_id));
		
		// technicke prostredky
		$tableDevices = new Application_Model_DbTable_TechnicalDevice();
		$placeForm->getElement("technicaldeviceList")->setOptions($tableDevices->getTechnicalDevices($this->_audit->client_id));
		
		// chemicke latky
		$tableChems = new Application_Model_DbTable_Chemical();
		$placeForm->getElement("chemicalList")->setMultiOptions($tableChems->getChemicals($this->_audit->client_id));
		
		$postForm = new Application_Form_Position();
		$this->view->postForm = $postForm;
		$postForm->removeElement("new_workplace");
		$postForm->removeElement("workplaces");
		$postForm->removeElement("workplaceList");
		$workForm = new Application_Form_Work();
		$this->view->workForm = $workForm;
		$techForm = new Application_Form_TechnicalDevice();
		$this->view->techForm = $techForm;
		$chemForm = new Application_Form_Chemical();
		$this->view->chemForm = $chemForm;
		$folderForm = new Application_Form_Folder();
		$this->view->folderForm = $folderForm;
		
	}
	
	public function generateIndex($rowset, $name, $id) {
		$retVal = array();
		foreach ($rowset as $item) {
			$retVal[$item[$id]] = $item[$name];
		}
		return $retVal;
	}
}