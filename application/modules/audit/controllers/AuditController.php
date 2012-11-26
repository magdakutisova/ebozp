<?php
class Audit_AuditController extends Zend_Controller_Action {
	
	/**
	 * radek s uzivatelem
	 * @var Application_Model_User
	 */
	protected $_user;
	
	public function init() {
		// zapsani helperu
		$this->view->addHelperPath(APPLICATION_PATH . "/views/helpers");
		
		// nacteni uzivatele
		$username = Zend_Auth::getInstance()->getIdentity()->username;
		$tableUsers = new Application_Model_DbTable_User();
		
		$this->_user = $tableUsers->getByUsername($username);
	}
	
	public function createAction() {
		// nacteni dat a naplneni formulare
		$data = $this->getRequest()->getParam("audit", array());
		$data = array_merge(array("subsidiary_id" => 0), $data);
		
		// nacteni dat
		$tableSubsidiaries = new Application_Model_DbTable_Subsidiary();
		$diary = $tableSubsidiaries->find($data["subsidiary_id"])->current();
		
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
		
		$this->view->form = $form;
	}
	
	public function fillAction() {
		$data = $this->getRequest()->getParam("audit");
		$data = array_merge(array("id" => 0), $data);
		
		// nacteni auditu
		$tableAudits = new Audit_Model_Audits();
		$audit = $tableAudits->getById($data["id"]);
		
		if (!$audit) throw new Zend_Exception("Audit #" . $data["id"] . " has not been found");
		
		// nacteni dotazniku
		$tableQuestionaries = new Questionary_Model_Filleds();
		$questionaryRow = $tableQuestionaries->getById($audit->form_filled_id);
		
		$questionary = $questionaryRow->toClass();
		$form = new Audit_Form_AuditFill();
		
		$form->populate($audit->toArray());
		$form->populate($data)->isValidPartial($data);
		
		$this->view->questionary = $questionary;
		$this->view->audit = $audit;
		$this->view->form = $form;
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
	
	public function postAction() {
		// nacteni dat
		$data = $this->getRequest()->getParam("audit");
		
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
		
		// nacteni radku fomrulare
		$tableForms = new Audit_Model_Forms();
		$formRow = $tableForms->find($form->getValue("form_id"))->current();
		
		// vytvoreni zaznamu
		$audit = $tableAudits->createAudit($auditor, $coordinator, $formRow, $subsidiary, $doneAt, array());
		
		$this->_redirect("/audit/audit/fill?audit[id]=" . $audit->id);
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
		
		// nacteni formulare
		$filled = $audit->findParentRow("Questionary_Model_Filleds");
		$questionary = $filled->toClass();
		
		// ulozeni formulare
		$questionary->setFromArray(Zend_Json::decode($data["content"]));
		$filled->saveFilledData($questionary);
		
		// zapis poznamek a shrnuti
		$audit->summary = $form->getValue("summary");
		$audit->progress_note = $form->getValue("progress_note");
		
		$audit->save();
		
		// presmerovani na fill
		$this->_redirect("/audit/audit/fill?audit[id]=" . $audit->id);
	}
	
	protected function submit(Audit_Form_AuditFill $form, Audit_Model_Row_Audit $audit) {
		
	}
}