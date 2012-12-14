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
				"coordinator_confirmed_at = 0"
		);
	
		// nactnei uzavrenych auditu
		$closed = array(
				"coordinator_id = " . $this->_user->getIdUser(),
				"coordinator_confirmed_at > 0"
		);
	
		$this->_loadList($open, $closed);
		
		// nacteni neshod tykajicich se pobocky
	
		$this->view->openRoute = "audit-review";
		$this->view->closedRoute = "audit-get";
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
		// kontrola dat
		if (!$this->_audit) throw new Zend_Exception("Audit not found");
		
		// vytvoreni formulare
		$form = new Audit_Form_Audit();
		$form->fillSelects();
		$form->populate(array("audit" => $this->_audit->toArray()));
		
		// naplneni zodpovednych osob
		$form->getElement("responsibile_name")->setValue($this->_audit->getResponsibiles());
		
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
		
		$this->view->subsidiary = $this->_audit->getSubsidiary();
		$this->view->client = $this->_audit->getClient();
		$this->view->form = $form;
		$this->view->instanceForm = $instanceForm;
		$this->view->formInstances = $formInstances;
		$this->view->audit = $this->_audit;
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
		
		// nacteni zaznamu a skupin a indexace zaznamu dle skupin
		$groups = $audit->getGroups();
		
		// nacteni zaznamu a indexace dle skupin
		$records = $audit->getRecords();
		
		$recordIndex = array();
		
		// vytvoreni mist dle skupin
		foreach ($groups as $group) {
			$recordIndex[$group->id] = array();
		}
		
		// nacteni doprovodnych informaci
		$responsibiles = $audit->getResponsibiles();
		$auditor = $audit->getAuditor();
		$client = $audit->getClient();
		$coordinator = $audit->getCoordinator();
		$subsidiary = $audit->getSubsidiary();
		
		// zapis zaznamu
		foreach ($records as $record) {
			$recordIndex[$record->group_id][] = $record;
		}
		
		$this->view->layout()->setLayout("client-layout");
		
		$this->view->audit = $this->_audit;
		$this->view->groups = $groups;
		$this->view->recordIndex = $recordIndex;
		$this->view->subsidiary = $subsidiary;
		$this->view->client = $client;
		$this->view->coordinator = $coordinator;
		$this->view->auditor = $auditor;
		$this->view->responsibiles = $responsibiles;
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
		
		$this->view->openRoute = "audit-fill";
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
		$audit = $tableAudits->createAudit($auditor, $coordinator, $subsidiary, $doneAt, explode(",", $form->getValue("responsibile_name")));
		
		$this->_redirect(
				$this->view->url(array("clientId" => $subsidiary->client_id, "auditId" => $audit->id), "audit-edit")
		);
	}
	
	public function putAction() {
		// nacteni dat
		$data = $this->getRequest()->getParam("audit");
		
		$form = new Audit_Form_AuditFill();
		
		// kontrola konecneho odeslani
		if (!$data["close"])
			$form->getElement("summary")->setRequired(false);
		
		// kontrola validity
		$form->populate($data);
		if (!$form->isValidPartial($data)) $this->_forward("fill");
		
		// nacteni auditu
		$tableAudits = new Audit_Model_Audits();
		$audit = $tableAudits->getById($form->getValue("id"));
		
		// nacteni formulare
		$filled = $audit->findParentRow("Questionary_Model_Filleds");
		$questionary = $filled->toClass();
		
		// ulozeni novych dat do formulare
		$formData = Zend_Json::decode($data["content"]);
		
		foreach ($formData as $name => $value) {
			$questionary->getByName($name)->fill($value);
		}
		
		$filled->saveFilledData($questionary);
		
		// zapis poznamek a shrnuti
		$audit->summary = $form->getValue("summary");
		$audit->progress_note = $form->getValue("progress_note");
		
		// kontrola uzavreni ze strany technika
		if ($data["close"]) {
			$audit->setDone();
			
			// zapis dat do vypisu auditu
			$tableRecords = new Audit_Model_AuditsRecords();
			$tableRecords->createRecords($audit);
			
			$audit->save();
			
			// presmerovani na vypis
			$this->_redirect(
				$this->view->url(array("clientId" => $audit->client_id, "auditId" => $audit->id), "audit-get")
			);
			
			return;
		}
		
		$audit->save();
		
		// presmerovani na fill
		$this->_redirect(
				$this->view->url(array("clientId" => $audit->client_id, "auditId" => $audit->id), "audit-fill")
		);
	}
	
	public function reviewAction() {
		$this->getAction();
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
		
		// nacteni seznamu zodpovednych osob za otevrene audity
		$openResp = self::loadResponsibiles($open);
		
		// nactnei uzavrenych auditu
		$closed = $tableAudits->fetchAll($closedCond, "done_at desc");
		
		$closedResp = self::loadResponsibiles($closed);
		
		// nacteni seznamu pobocek
		$subIndex = self::loadSubdiaryIndex($client);
		
		$this->view->layout()->setLayout("client-layout");
		
		$this->view->client = $client;
		$this->view->open = $open;
		$this->view->openResp = $openResp;
		$this->view->closed = $closed;
		$this->view->closedResp = $closedResp;
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
	
	/**
	 * sestavi rowsety zodpovednych lidi do setu
	 * 
	 * @param Audit_Model_Rowset_Audits $audits zkoumane audity
	 * @return array<Audit_Model_Rowset_AuditsResponsibiles>
	 */
	public static function loadResponsibiles(Audit_Model_Rowset_Audits $audits) {
		// sestaveni seznamu id a priprava pomocneho pole
		$idList = array(0);
		$resList = array();
		$retVal = array();
		
		foreach ($audits as $audit) {
			$idList[] = $audit->id;
			$respList[$audit->id] = array();
			$retVal[$audit->id] = array();
		}
		
		// nactnei dat
		$tableResp = new Audit_Model_AuditsResponsibiles();
		$responsibiles = $tableResp->fetchAll($tableResp->getAdapter()->quoteInto("audit_id in (?)", $idList), "audit_id")->toArray();
		
		// zapis dat do pomocne promenne
		foreach ($responsibiles as $item) {
			$resList[$item["audit_id"]][] = $item;
		}
		
		// vygenerovani rowsetu a jejich ulozeni do pole navratove hodnoty
		foreach ($resList as $auditId => $item) {
			$retVal[$auditId] = new Audit_Model_Rowset_AuditsResponsibiles(array(
					"data" => $item,
					"table" => $tableResp
			));
		}
		
		return $retVal;
	}
}