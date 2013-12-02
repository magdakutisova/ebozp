<?php
class Audit_FormController extends Zend_Controller_Action {
	
	public function init() {
		$this->view->addHelperPath(APPLICATION_PATH . "/views/helpers/", "Zend_View_Helper");
	}
	
	/**
	 * provede klonovani formulare predaneho z prohlizece
	 */
	public function cloneAction() {
		// nacteni formulare
		$form = self::loadForm($this->_request->getParam("formId"));
		
		$newForm = $form->cloneForm($form->name . " - kopie");
		
		$this->view->oldForm = $form;
		$this->view->newForm = $newForm;
		
		$this->_helper->FlashMessenger("Formulář byl zkopírován");
	}
	
	public function createAction() {
		// vytvoreni formulare
		$form = new Audit_Form_Form();
		
		// nacteni dat, pokud jsou nejaka odeslana
		$data = $this->getRequest()->getParam("form", array());
		$form->isValidPartial($data);
		
		$this->view->form = $form;
	}
	
	/*
	 * smaze formular
	 */
	public function deleteAction() {
		// nacteni dat
		$form = self::loadForm($this->_request->getParam("formId"));
		$delForm = new Audit_Form_Delete();
		
		if (!$delForm->isValid($this->_request->getParams())) {
			$this->_forward("edit");
			return;
		}
		
		// oznaceni formulare jako smazaneho
		$form->is_deleted = 1;
		$form->save();
		
		$this->_helper->FlashMessenger("formulář smazán");
		
		// presmerovani na index
		$this->_redirect("/audit/form/index");
	}
    
    /**
     * odebere formular z auditu
     */
    public function dettachAction() {
        // nacteni dat
        $formId = $this->_request->getParam("formId");
        $auditId = $this->_request->getParam("auditId");
        
        $tableAudits = new Audit_Model_Audits();
        $tableForms = new Audit_Model_AuditsForms();
        
        // nacteni auditu
        $audit = $tableAudits->getById($auditId);
        if (!$audit) throw new Zend_Db_Table_Exception(sprintf("audit #%s not found", $auditId));
        
        // nacteni formulare
        $form = $tableForms->fetchRow(array("id = ?" => $formId, "audit_id = ?" => $auditId));
        if (!$form) throw new Zend_Db_Table_Exception("form #%s not found in audit #%s", $formId, $auditId);
        
        // vytvoreni dodatecnych tabulek
        $tableRecords = new Audit_Model_AuditsRecords();
        $tableMistakes = new Audit_Model_AuditsRecordsMistakes();
        
        // smazani neshod
        
        // vytvoreni vyhledavaciho dotazu
        $adapter = $tableAudits->getAdapter();
        $select = new Zend_Db_Select($adapter);
        
        $select->from($tableRecords->info("name"), array("id"));
        $select->where("audit_form_id = ?", $formId);
        
        $tableMistakes->delete(array(
            "audit_id = ?" => $auditId,
            "record_id in (?)" => new Zend_Db_Expr($select->assemble())
        ));
        
        // smazani zaznamu
        $tableRecords->delete(array(
            "audit_form_id = ?" => $formId
        ));
        
        // smazani formulare
        $form->delete();
        
        $this->view->audit = $audit;
    }
	
	/*
	 * zobrazi formular pro editaci
	 */
	public function editAction() {
		// nacteni dat
		$formId = $this->getRequest()->getParam("formId", 0);
		$form = self::loadForm($formId);
		
		// vytvoreni HTML formulare pro zmenu jmena
		$editForm = new Audit_Form_Form();
		$editForm->populate($form->toArray());
		$editForm->setAction($this->view->url(array("formId" => $formId), "audit-form-put"));
		
		// nacteni kategorii 
		$categories = $form->findCategories();
		
		// formular tvorby kategorii
		$categoryForm = new Audit_Form_Section();
		$categoryForm->isValidPartial($this->_request->getParams());
		
		// formular smazani
		$formDelete = new Audit_Form_Delete();
		$formDelete->setAction("/audit/form/delete?formId=" . $form->id);
		
		$this->view->form = $form;
		$this->view->editForm = $editForm;
		$this->view->categories = $categories;
		$this->view->categoryForm = $categoryForm;
		$this->view->formDelete = $formDelete;
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
		$records = $form->getRecords($actualGroup);
		
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
		
		// nacteni formulare
		$tableForms = new Audit_Model_Forms();
		$form = $tableForms->findById($formForm->getValue("id"));
		
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
		$forms = $tableForms->fetchAll("!is_deleted", "name");
		
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
		
		// vytvoreni reprezentace formulare
		$tableForms = new Audit_Model_Forms();
		$form = $tableForms->createForm($form->getValue("name"));
		
		$this->_helper->FlashMessenger("Formulář vytvořen");
		
		// presmerovani na editaci
		$this->_redirect("/audit/form/edit?formId=" . $form->id);
	}
	
	/*
	 * ulozi zmeny ve formulari
	 */
	public function putAction() {
		// nacteni dat
		$formId = $this->_request->getParam("formId");
		
		try {
			$formRow = self::loadForm($formId);
		} catch (Zend_Exception $e) {
			return;
		}
		
		// kontrola dat odeslanych z klienta
		$form = new Audit_Form_Form();
		
		if (!$form->isValid($this->_request->getParams())){
			return;
		}
		
		// nastaveni jmena
		$formRow->setFromArray($form->getValues(true));
		$formRow->save();
		
		$this->_helper->FlashMessenger("Změny byly uloženy");
		
		$this->view->form = $formRow;
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
				"formId" => $formId,
				"page" => $page + 1
		), "audit-form-fill");
		
		$this->_helper->FlashMessenger("Změny byly uloženy");
		
		$this->_redirect($url);
	}
	
	public function saveoneJsonAction() {
		// nacteni dat
		$recordId = $this->_request->getParam("recordId", 0);
		
		$tableRecords = new Audit_Model_AuditsRecords();
		$record = $tableRecords->find($recordId)->current();
		
		if (!$record) throw new Zend_Db_Exception(sprintf("Record #%s not found", $recordId));
		
		// update dat
		$comment = $this->_request->getParam("comment", null);
		
		if (!is_null($comment)) {
			$record->note = $comment;
		}
		
		$score = $this->_request->getParam("score", null);
		
		if (!is_null($score)) {
			$record->score = $score;
			
			// nacteni dat
			$tableAssocs = new Audit_Model_AuditsMistakes();
			$where = array(
					"audit_id = ?" => $record->audit_id,
					"mistake_id = ?" => $record->mistake_id,
					"record_id = ?" => $record->id
			);
			
			// vyhodnoceni skore
			if ($score == Audit_Model_AuditsRecords::SCORE_N) {
				// vytvoreni asociace, pokud neexistuje
				if (!$tableAssocs->fetchRow($where)) {
					$tableAssocs->insert(array(
							"audit_id" => $record->audit_id,
							"mistake_id" => $record->mistake_id,
							"record_id" => $record->id
							));
				}
				
			} else {
				// smazani dat z asociace
				$tableAssocs->delete($where);
			}
		}
		
		$record->save();
	}
	
	public function sortAction() {
		// nacteni dat
		$formId = $this->_request->getParam("formId", 0);
		$form = self::loadForm($formId);
		
		// vytvoreni instance tabulky a priprava podminky
		$tableCategories = new Audit_Model_FormsCategories();
		$where = array("form_id = ?" => $form->id, "id = ?" => 0);
		$data = array("position" => 0);
		$pos = 1;
		
		// prochazeni dat a zapis do databaze
		$sort = (array) $this->_request->getParam("category", null);
		$sort = array_merge(array("sort" => array()), $sort);
		
		// update dat
		foreach ($sort["sort"] as $categoryId) {
			$where["id = ?"] = $categoryId;
			$data["position"] = $pos++;
			
			$tableCategories->update($data, $where);
		}
		
		$this->_helper->FlashMessenger("Změny byly uloženy");
		
		$this->view->form = $form;
	}
	
	public static function loadForm($id) {
		$tableForms = new Audit_Model_Forms();
		$form = $tableForms->findById($id);
		
		if (!$form) throw new Zend_Db_Table_Exception("Form #$id not found");
		
		return $form;
	}
}