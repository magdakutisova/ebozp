<?php
class Audit_CheckController extends Zend_Controller_Action {
	
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
		$form = new Audit_Form_CheckCreate();
		$form->fillSelects();
		$data = $check->toArray();
		$data["responsibile_name"] = $data["responsibiles"];
		$form->populate($data);
		$form->getElement("submit")->setLabel("Uložit");
		
		$params = array(
				"clientId" => $clientId,
				"subsidiaryId" => $subsidiaryId,
				"checkId" => $checkId
		);
		
		$url = $this->view->url($params, "audit-check-put");
		$form->setAction($url);
		
		$this->view->check = $check;
		$this->view->client = $client;
		$this->view->subsidiary = $subsidiary;
		$this->view->editForm = $form;
	}
	
	public function indexAction() {
		// nacteni klienta
		$clientId = $this->getRequest()->getParam("clientId", 0);
		$tableClients = new Application_Model_DbTable_Client();
		
		$client = $tableClients->find($clientId)->current();
		if (!$client) throw new Zend_Exception("Client #$clientId has not been found");
		
		// nacteni klientu a pobocek, ktere jsou k dispozici
		$subSiDiariesIds = $this->_user->getUserSubsidiaries();
		
		// nacteni pobocek
		$tableSubsidiaries = new Application_Model_DbTable_Subsidiary();
		$subSiDiariesIds[] = 0;
		
		$where = array(
				$tableSubsidiaries->getAdapter()->quoteInto("id_subsidiary in (?)", $subSiDiariesIds),
				"client_id = " . $client->id_client
		);
		
		$subsidiaries = $tableSubsidiaries->fetchAll(
				$where,
				array("subsidiary_name")
		);
		
		$this->view->client = $client;
		$this->view->subsidiaries = $subsidiaries;
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
		
		// presmerovani na edit
		$params = array(
				"clientId" => $subsidiary->client_id,
				"subsidiaryId" => $subsidiary->id_subsidiary,
				"checkId" => $check->id
		);
		
		$url = $this->view->url($params, "audit-check-edit");
		
		$this->_redirect($url);
	}
}