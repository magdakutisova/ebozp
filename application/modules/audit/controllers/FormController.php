<?php
class Audit_FormController extends Zend_Controller_Action {
	
	public function init() {
		$this->view->addHelperPath(APPLICATION_PATH . "/views/helpers/", "Zend_View_Helper");
	}
	
	public function createAction() {
		// vytvoreni formulare
		$form = new Audit_Form_Form();
		
		// nacteni dat, pokud jsou nejaka odeslana
		$data = $this->getRequest()->getParam("form", array());
		$form->isValid($data);
		
		$this->view->form = $form;
	}
	
	/*
	 * smaze formular
	 */
	public function deleteAction() {
		// nacteni dat
		$data = $this->getRequest()->getParam("form", array());
		$data = array_merge(array("id" => 0), $data);
		
		// kontrola dat
		try {
			// nacteni formulare
			$tableForms = new Audit_Model_Forms();
			$form = $tableForms->findById($data["id"]);
			
			if (!$form) throw new Zend_Exception("Form #" . $data["id"] . " not found");
		} catch (Zend_Exception $e) {
			$this->_forward("index");
			return;
		}
		
		// nacteni puvodniho dotazniku
		$questionary = $form->getQuestionary();
		
		// smazani dat
		$form->delete();
		$questionary->delete();
		
		// presmerovani na index
		$this->_redirect("/audit/form/index");
	}
	
	/*
	 * zobrazi formular pro editaci
	 */
	public function editAction() {
		// nacteni dat
		$data = $this->getRequest()->getParam("form", array());
		$data = array_merge(array("id" => 0), $data);
		
		$tableForms = new Audit_Model_Forms();
		$form = $tableForms->findById($data["id"]);
		
		if (!$form) {
			// formular nebyl nalezen
			$this->_forward("index");
			return;
		}
		
		// nalezeni dotazniku a prevod do tridy
		$questionaryRow = $form->getQuestionary();
		$questionary = $questionaryRow->toClass();
		
		// vytvoreni HTML formulare
		$editForm = new Audit_Form_Form();
		$editForm->populate($questionaryRow->toArray());
		
		$this->view->form = $form;
		$this->view->questionary = $questionary;
		$this->view->editForm = $editForm;
	}
	
	public function fillAction() {
		$this->view->layout()->setLayout("client-layout");
		
		// nacteni dat
		$auditId = $this->getRequest()->getParam("auditId", 0);
		$formId = $this->getRequest()->getParam("formId", 0);
		$clientId = $this->getRequest()->getParam("clientId", 0);
		$subsidiaryId = $this->getRequest()->getParam("subsidiaryId", 0);
		
		// nacteni formularu
		$tableForms = new Audit_Model_AuditsForms();
		$form = $tableForms->getById($formId);
		
		if (!$form) throw new Zend_Exception("Form #" . $formId . " not found");
		
		// kontrola orpavneni
		if ($form->audit_id != $auditId) throw new Zend_Exception("Form id #$formId is not belongs to audit #$auditId");
		
		// nacteni stranky
		$pageIndex = $this->getRequest()->getParam("page", 1) - 1;
		
		// nacteni stran
		$groups = $form->getGroups();
		
		// nacteni zaznamu ze skupiny
		$actualGroup = $groups[$pageIndex];
		
		// nacteni zaznmu
		$records = $actualGroup->getRecords();
		
		// nacteni informaci o klientovi a podobne
		$tableClients = new Application_Model_DbTable_Client();
		$tableSubsidiary = new Application_Model_DbTable_Subsidiary();
		
		$client = $tableClients->find($clientId)->current();
		$subsidiary = $tableSubsidiary->find($subsidiaryId)->current();
		
		// mastaven view
		$this->view->groups = $groups;
		$this->view->pageIndex = $pageIndex;
		$this->view->actualGroup = $actualGroup;
		$this->view->records = $records;
		$this->view->client = $client;
		$this->view->subsidiary = $subsidiary;
		$this->view->auditId = $auditId;
		$this->view->form = $form;
	}
	
	/*
	 * zobrazi formular pro nahled
	 */
	public function getAction() {
		// nastaveni layoutu
		$this->view->layout()->setLayout("client-layout");
		
		// nacteni formulare
		$formId = $this->getRequest()->getParam("formId", 0);
		$tableForms = new Audit_Model_AuditsForms();
		$form = $tableForms->getById($formId);
		
		if (!$form) throw new Zend_Exception("Form id $formId not found");
		
		// nacteni skupin a zaznamu
		$groups = $form->getGroups();
		$records = $form->getRecords();
		
		// indexace zaznamu dle id skupiny
		$recordIndex = array();
		
		foreach ($records as $record) {
			$groupId = $record->group_id;
			
			// kontrola existence slotu
			if (!isset($recordIndex[$groupId])) {
				$recordIndex[$groupId] = array();
			}
			
			$recordIndex[$groupId][] = $record;
		}
		
		// nacteni rodicovskych prvku
		$audit = $form->getAudit();
		$client = $audit->getClient();
		$subsidiary = $audit->getSubsidiary();
		
		$this->view->form = $form;
		$this->view->client = $client;
		$this->view->subsidiary = $subsidiary;
		$this->view->audit = $audit;
		
		$this->view->groups = $groups;
		$this->view->recordIndex = $recordIndex;
	}
	
	/*
	 * uvodni strana
	 * zobrazi seznam formularu
	 * zobrazi formular pro vytvoreni noveho formulare (ehm)
	 */
	public function indexAction() {
		
	}
	
	public function instanceAction() {
		// nastaveni layoutu
		$this->view->layout()->setLayout("client-layout");
		
		// vygenerovani url na vyplneni
		$params = array(
				"auditId" => $this->getRequest()->getParam("auditId", 0),
				"clientId" => $this->getRequest()->getParam("clientId", 0),
				"subsidiaryId" => $this->getRequest()->getParam("subsidiaryId", 0)
		);
		
		// nacteni auditu
		$auditId = $this->getRequest()->getParam("auditId", 0);
		$tableAudits = new Audit_Model_Audits();
		$audit = $tableAudits->getById($auditId);
		
		if (!$audit) throw new Zend_Exception("Audit #$auditId not found");
		
		// nacteni dat
		$formForm = new Audit_Form_FormInstanceCreate();
		$formForm->populate($_REQUEST);
		
		// nacteni dotazniku
		$tableForms = new Audit_Model_Forms();
		$form = $tableForms->findById($formForm->getValue("questionary_id"));
		
		if (!$form) throw new Zend_Exception("Invalid form id");
		
		// kontrola existence instance dotazniku
		$tableAuditForms = new Audit_Model_AuditsForms();
		$auditForm = $tableAuditForms->getByAuditAndForm($audit, $form);
		
		if ($auditForm) {
			$params["formId"] = $auditForm->id;
			
			$url = $this->view->url($params, "audit-form-fill");
			$this->_redirect($url);
			return;
		}
		
		// vytvoreni instance
		$auditForm = $tableAuditForms->createForm($audit, $form);
		
		$params["formId"] = $auditForm->id;
			
		$url = $this->view->url($params, "audit-form-fill");
		$this->_redirect($url);
	}
	
	/*
	 * zobrazi seznm formularu
	 */
	public function listAction() {
		// nacteni formularu
		$tableForms = new Audit_Model_Forms();
		$forms = $tableForms->fetchAll(null, "name");
		
		$this->view->forms = $forms;
	}
	
	/*
	 * vytvori novy formular
	 */
	public function postAction() {
		// nactnei a kontrola dat
		$data = $this->getRequest()->getParam("form", array());
		$form = new Audit_Form_Form();
		
		if (!$form->isValid($data)) {
			$this->_forward("create", null, null, array("form" => $data));
			return;
		}
		
		// vytvoreni zakladniho dotazniku
		$tableQuestionaries = new Questionary_Model_Questionaries();
		$questionary = $tableQuestionaries->createQuestionary($form->getValue("name"));
		
		// vytvoreni reprezentace formulare
		$tableForms = new Audit_Model_Forms();
		$form = $tableForms->createForm($form->getValue("name"), $questionary);
		
		// presmerovani na editaci
		$this->_redirect("/audit/form/edit?form[id]=" . $questionary->id);
	}
	
	/*
	 * ulozi zmeny ve formulari
	 */
	public function putJsonAction() {
		$this->view->layout()->disableLayout();
		$this->view->response = false;
		
		// nacteni dat
		$data = $this->getRequest()->getParam("form", array());
		$data = array_merge(array("id" => 0), $data);
		
		// nacteni lokalnich dat
		$tableForms = new Audit_Model_Forms();
		$formRow = $tableForms->findById($data["id"]);
		
		if (!$formRow) return;
		
		// nacteni dotazniku
		$questionaryRow = $formRow->getQuestionary();
		
		// kontrola dat odeslanych z klienta
		$form = new Audit_Form_Form();
		
		if (!$form->isValid($data)){
			return;
		}
		
		// nastaveni jmena
		$formRow->name = $form->getElement("name")->getValue();
		$formRow->save();
		
		// naprasovani dat z dotazniku
		$qData = Zend_Json::decode($data["def"]);
		
		// nastaveni dotazniku
		$questionary = new Questionary_Questionary();
		$questionary->setFromArray($qData);
		
		// zapis do dotazniku
		$questionaryRow->saveClass($questionary);
		
		// zapis do formulare
		$formRow->writeQuestionary($questionary);
		
		// nastaveni view
		$this->view->response = true;
	}
	
	public function saveAction() {
		// nacteni dat
		$auditId = $this->getRequest()->getParam("auditId", 0);
		$subsidiaryId = $this->getRequest()->getParam("subsidiaryId", 0);
		$clientId = $this->getRequest()->getParam("clientId", 0);
		$formId = $this->getRequest()->getParam("formId", 0);
		$page = $this->getRequest()->getParam("page", 0) - 1;
		
		// nacteni dat z databaze a kontrola dat
		$tableAudits = new Audit_Model_Audits();
		$tableForms = new Audit_Model_AuditsForms();
		
		// kontrola auditu
		$audit = $tableAudits->getById($auditId);
		if (!$audit) throw new Zend_Exception("Audit #$auditId not found");
		
		// nacteni formulare
		$form = $tableForms->getById($formId);
		
		// kontrola formulare
		if (!$form) throw new Zend_Exception("Form #$formId not found");
		if ($form->audit_id != $audit->id) throw new Zend_Exception("Form #$formId is not belongs to audit #$auditId");
		
		// nacteni skupiny
		$groups = $form->getGroups();
		$currentGroup = $groups[$page];
		
		// priprava tabulky
		$tableRecords = new Audit_Model_AuditsRecords();
		
		// sestaveni zakladniho SQL
		$sqlBase = "update `" . $tableRecords->info("name") . "` set ";
		
		// data z requestu
		$data = (array) $this->getRequest()->getParam("record", array());
		
		// zapis dat
		$toUpdate = array();
		$activatedMistakeIds = array(0);
		$activatedMistakesPairs = array();
		$recordIds = array(0);
		
		// prochazeni zaznamu a tvorba dotazu
		$adapter = $tableRecords->getAdapter();
		
		foreach ($data as $index => $item) {
			$item = array_merge(array("comment" => "", "note" => Audit_Model_AuditsRecords::SCORE_NT, "mistake_id" => 0), $item);
			
			$sql = $sqlBase;
			
			// cast updatu
			$sql .= "`note` = " . $adapter->quote($item["note"]) . ", `score` = " . $item["score"];
			
			// case podminky
			$sql .= " where audit_id = " . $audit->id . " and id = " . $adapter->quote($index) . " and audit_form_id = " . $adapter->quote($formId);
			
			$toUpdate[] = $sql;
			$recordIds[] = $index;
			
			// vyhodnoceni aktivace neshody
			if ($item["score"] == Audit_Model_AuditsRecords::SCORE_N) {
				$activatedMistakeIds[] = $item["mistake_id"];
				$activatedMistakesPairs[] = array($item["mistake_id"], $index);
			}
		}
		
		// zaneseni zmen
		if ($toUpdate) {
			$sql = implode(";", $toUpdate) . ";";
			$adapter->query($sql);
		}
		
		// vygenerovani SQL pro nastaveni prirazeni neshod
		$tableAssocs = new Audit_Model_AuditsMistakes();
		$nameAssocs = $tableAssocs->info("name");
		
		$quotedRecords = $adapter->quote($recordIds);
		$quotedMistakes = $adapter->quote($activatedMistakeIds);
		
		// smazani nepouzitych neshod
		$sql = "delete from `$nameAssocs` where audit_id = $audit->id and record_id in ($quotedRecords) and mistake_id not in ($quotedMistakes)";
		$adapter->query($sql);
		
		// vlozeni novych neshod
		$toInsert = array();
		
		foreach ($activatedMistakesPairs as $item) {
			$toInsert[] = "($auditId, " . $adapter->quote($item[0]) . "," . $adapter->quote($item[1]) . ")";
		}
		
		if ($toInsert) {
			$sql = "insert ignore into `$nameAssocs` (audit_id, mistake_id, record_id) values " . implode(",", $toInsert) . "";
			
			$adapter->query($sql);
		}
		
		// presmerovani zpet na editaci
		$url = $this->view->url(array(
				"auditId" => $auditId,
				"clientId" => $clientId,
				"subsidiaryId" => $subsidiaryId,
				"formId" => $formId
		), "audit-form-fill");
		
		$this->_redirect($url);
	}
}