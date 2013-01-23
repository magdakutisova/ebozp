<?php
class Audit_MistakeController extends Zend_Controller_Action {
	
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
		$this->view->addHelperPath(APPLICATION_PATH . "/../library/My/View/Helper", "My_View_Helper");
	
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
	
	public function attachAction() {
		// nactnei parametru
		$data = $this->getRequest()->getParam("mistake", array());
		$data = array_merge(array("id" => 0), $data);
		$recordId = $this->getRequest()->getParam("recordId", 0);
		
		// ziskani informaci z databaze
		$tableRecords = new Audit_Model_AuditsRecords();
		$tableMistakes = new Audit_Model_AuditsRecordsMistakes();
		$tableAssocs = new Audit_Model_AuditsMistakes();
		
		// nacteni neshody
		$mistake = $tableMistakes->getById($data["id"]);
		if (!$mistake) throw new Zend_Exception("Mistake #" . $data["id"] . " has not been found");
		
		// nacteni zaznamu
		$record = $tableRecords->getById($recordId);
		if (!$record) throw new Zend_Exception("Record #$recordId has not been found");
		
		// smazani stavajici asociace
		$where = array(
				"audit_id = " . $this->_audit->id,
				"record_id = " . $record->id
		);
		
		$tableAssocs->delete($where);
		
		// zapis nove asociace
		$tableAssocs->insert(array(
				"audit_id" => $this->_audit->id,
				"mistake_id" => $mistake->id,
				"record_id" => $record->id
		));
		
		// nacteni puvodni neshody
		$originMistake = $record->getMistake();
		
		// presmerovani zpatky na edit
		$params = array(
				"auditId" => $this->_audit->id,
				"clientId" => $this->_audit->client_id,
				"mistakeId" => $originMistake->id
		);
		
		$this->_redirect($this->view->url($params, "audit-mistake-edit-html"));
	}
	
	public function auditlistAction() {
		if (!$this->_audit) throw new Zend_Exception("Item not found");
		
		// nacteni neshod
		$tableMistakes = new Audit_Model_AuditsRecordsMistakes();
		$mistakes = $tableMistakes->getUngrouped($this->_audit, Audit_Model_AuditsRecordsMistakes::SUBMITED_UNSUBMITED);
		
		// vyhodnoceni backTo url
		$params = array("auditId" => $this->_audit->id, "clientId" => $this->_audit->client_id);
		
		if ($this->_audit->auditor_confirmed_at == "0000-00-00 00:00:00") {
			$route = "audit-fill";
			$backCaption = "Zpět na dotazník";	
		} else {
			
		}
		
		$backTo = $this->view->url($params, $route);
		
		$this->view->mistakes = $mistakes;
		$this->view->audit = $this->_audit;
		$this->view->client = $this->_audit->getClient();
		$this->view->subsidiary = $this->_audit->getSubsidiary();
		$this->view->backTo = $backTo;
		$this->view->backCaption = $backCaption;
	}
	
	public function createAction() {
		// nacteni zaznamu
		$recordId = $this->getRequest()->getParam("recordId", 0);
		$tableRecords = new Audit_Model_AuditsRecords();
		
		$record = $tableRecords->getById($recordId);
		
		// kontrola dat (nalezeni zaznamu, prislusnost k auditu a jeslti je uz zaznamu prirazena neshoda)
		if (!$record || $record->audit_id != $this->_audit->id || $record->mistake_id) throw new Zend_Exception("Record not found");
		
		// nacteni klienta a pobocky
		$client = $this->_audit->getClient();
		$subdiary = $this->_audit->getSubsidiary();
		
		// nalezeni zaznamu neshod, ktere je mozne pouzit
		$tableMistakes = new Audit_Model_AuditsRecordsMistakes();

		$mistakes = $tableMistakes->getByItem($record->questionary_item_id, $client, $subdiary);
		
		// vytvoreni formulare a nastaveni dat
		$createForm = new Audit_Form_MistakeCreate();
		
		// nastaveni akce
		$createForm->setAction($this->view->url(array(
				"clientId" => $client->id_client,
				"recordId" => $record->id,
				"auditId" => $this->_audit->id
		), "audit-mistake-post"));
		
		// $data = $this->getRequest()->getParam("mistake", array());
		
		$createForm->populate($_REQUEST);
		$createForm->isValidPartial($_REQUEST);
		
		$this->_loadCategories();
		
		// zapis do view
		$this->view->record = $record;
		$this->view->audit = $this->_audit;
		$this->view->mistakes = $mistakes;
		$this->view->client = $client;
		$this->view->subsidiary = $subdiary;
		$this->view->createForm = $createForm;
	}
	
	public function createalone1Action() {
		$params = array(
				"clientId" => $this->_audit->client_id,
				"subsidiaryId" => $this->_audit->subsidiary_id,
				"auditId" => $this->_audit->id
		);
		
		$form = new Audit_Form_MistakeCreateSubsidiarySelect();
		$form->setAction($this->view->url($params, "audit-mistake-createalone2"));
		
		$this->_fillAloneMistake($form);
		
		// formular pro vytvoreni noveho pracoviste
		$formWork = new Application_Form_Workplace();
		
		$this->view->form = $form;
		$this->view->formWork = $formWork;
		
		$this->view->layout()->setLayout("client-layout");
	}
	
	public function createalone2Action() {
		$form = new Audit_Form_MistakeCreateAlone();
		
		// nastaveni akce
		$form->setAction(
				$this->view->url(array(
						"clientId" => $this->_audit->client_id,
						"auditId" => $this->_audit->id
				), "audit-mistake-postalone")
		);
		
		$form->isValidPartial($_REQUEST);
		$this->_loadCategories();
		
		// vyhodnoceni jestli je pracoviste validni
		$element = $form->getElement("workplace_id");
		$workplaceId = $element->getValue();
		
		if (!$element->isValid($workplaceId)) {
			// zpatky na vyber pracoviste
			$this->_forward("createalone1");
			return;
		}
		
		// kontrola jestli pracoviste existuje
		$tableWorkplaces = new Application_Model_DbTable_Workplace();
		$workplace = $tableWorkplaces->find($workplaceId)->current();
		
		if (!$workplace) {
			// pracoviste nebylo nalezeno - vraceni zpatky
			$this->_forward("createalone1");
			return;
		}
		
		// nacteni podobnych neshod
		$tableMistakes = new Audit_Model_AuditsRecordsMistakes();
		
		$where = array(
				"workplace_id = " . $workplace->id_workplace,
				"audit_id != " . $this->_audit->id,
				"!is_removed"
		);
		
		$similars = $tableMistakes->fetchAll($where);
		
		$this->view->form = $form;
		$this->view->audit = $this->_audit;
		$this->view->client = $this->_audit->getClient();
		$this->view->subsidiary = $this->_audit->getSubsidiary();
		$this->view->workplace = $workplace;
		$this->view->similars = $similars;
	}
	
	public function deleteAction($redirect = true) {
		// nacteni neshody
		$mistakeId = $this->getRequest()->getParam("mistakeId", 0);
		
		$tableMistakes = new Audit_Model_AuditsRecordsMistakes();
		$mistake = $tableMistakes->getById($mistakeId);
		
		if (!$mistake) throw new Zend_Db_Table_Exception("Mistake id #$mistakeId not found");
		
		// vyhodnoceni prislusnosti k auditu
		if ($mistake->audit_id == $this->_audit->id) {
			$mistake->delete();
		} else {
			// odstraneni probehne pouze z asociacni tabulky
			$tableAssocs = new Audit_Model_AuditsMistakes();
			$tableAssocs->delete(
					array(
							"audit_id = " . $this->_audit->id,
							"mistake_id = " . $mistake->id
			));
		}
		
		// presmerovani na vypis
		$params = array("clientId" => $mistake->client_id);
		
		// kontrola auditu
		if ($this->_audit) {
			$params["auditId"] = $this->_audit->id;
			$route = "audit-mistakes-auditlist";
		} else {
			
		}
		
		if ($redirect) $this->_redirect($this->view->url($params, $route));
	}
	
	public function deleteHtmlAction() {
		$this->deleteAction(false);
		
		$this->view->layout()->setLayout("floating-layout");
	}
	
	public function editAction() {
		// nacteni neshody
		$mistakeId = $this->getRequest()->getParam("mistakeId", 0);
		$tableMistakes = new Audit_Model_AuditsRecordsMistakes();
		$mistake = $tableMistakes->getById($mistakeId);
		
		if (!$mistake) throw new Zend_Exception("Mistake id #$mistakeId not found");
		
		// formular a jeho naplneni
		$data = $mistake->toArray();
		$data["will_be_removed_at"] = $this->view->sqlDate($data["will_be_removed_at"]);
		
		// vyhodnoceni vyplneni pracoviste
		if ($mistake->workplace_id) {
			$form = new Audit_Form_MistakeCreateAlone();
			
			$deleteForm = new Audit_Form_MistakeDelete();
			$deleteForm->populate(array("mistake" => $mistake->toArray()));
			
			// nacteni pracoviste
			$this->view->workplace = $mistake->getWorkplace();
		} else {
			$form = new Audit_Form_MistakeCreate();
			
			$deleteForm = null;
			
			// nacteni aktivni asociace
			$where = array(
					"audit_id = " . $this->_audit->id,
					"record_id = " . $mistake->record_id
			);
			
			$tableAssocs = new Audit_Model_AuditsMistakes();
			$this->view->activeAssoc = $tableAssocs->fetchRow($where);
		}
		
		$form->populate(array("mistake" => $data));
		$form->getElement("submit")->setLabel("Uložit");
		$form->setAction($this->view->url(array(
				"clientId" => $this->_audit->client_id,
				"auditId" => $this->_audit->id,
				"mistakeId" => $mistake->id
		), "audit-mistake-put"));
		
		// nastaveni dat z requestu, pokud neco je k dispozici
		$form->isValidPartial($_REQUEST);
		
		$params = array(
				"clientId" => $this->_audit->client_id,
				"auditId" => $this->_audit->id,
				"mistakeId" => $mistake->id
		);
		
		// nacteni podobnych neshod
		$similars = $this->_loadSimilarMistakes($mistake);
		
		// formular mazani
		$formDelete = new Audit_Form_MistakeDelete();
		$formDelete->setAction($this->view->url($params, "audit-mistake-delete"));
		
		// vyhodnoceni kam se vratit
		$params = array("clientId" => $mistake->client_id);
		
		if ($this->_audit) {
			$params["auditId"] = $this->_audit->id;
			$route = "audit-mistakes-auditlist";
		}
		
		// nacteni seznamu kategorii
		$this->_loadCategories($mistake->category);
		
		$backTo = $this->view->url($params, $route);
		
		// nacteni zaznamu
		$record = $mistake->findParentRow("Audit_Model_AuditsRecords", "record");
		
		$this->view->form = $form;
		$this->view->formDelete = $formDelete;
		$this->view->backTo = $backTo;
		$this->view->client = $mistake->getClient();
		$this->view->subsidiary = $mistake->getSubsidiary();
		$this->view->mistake = $mistake;
		$this->view->deleteForm = $deleteForm;
		$this->view->similars = $similars;
		$this->view->audit = $this->_audit;
		$this->view->record = $record;
	}
	
	public function editHtmlAction() {
		// provedeni akce a vypnuti layoutu
		$this->editAction();
		$this->view->layout()->setLayout("floating-layout");
		
		// nastaveni zemenne routy formulare
		$params = array(
				"clientId" => $this->_audit->client_id,
				"auditId" => $this->_audit->id,
				"mistakeId" => $this->view->mistake->id
		);
		
		$this->view->form->setAction(
				$this->view->url($params, "audit-mistake-put-html")
		);
		
		if ($this->view->deleteForm) {
			$this->view->deleteForm->setAction(
					$this->view->url($params, "audit-mistake-delete-html")
			);
		}
	}
	
	public function getAction() {
		// nacteni neshdoy
		$mistakeId = $this->getRequest()->getParam("mistakeId", 0);
		
		$tableMistakes = new Audit_Model_AuditsRecordsMistakes();
		$mistake = $tableMistakes->getById($mistakeId);
		
		if (!$mistake) throw new Zend_Exception("Mistake not found");
		
		// nacteni pracoviste
		if ($mistake->workplace_id) {
			$workplace = $mistake->getWorkplace();
			$this->view->workplaceName = $workplace->name;
		} else {
			$this->view->workplaceName = "-";
		}
		
		// nacteni rodicovskeho auditu nebo proverky
		$tableAudits = new Audit_Model_Audits();
		$tableChecks = new Audit_Model_Checks();
		if ($mistake->audit_id) {
			$masterAudit = $tableAudits->getById($mistake->audit_id);
			$masterCheck = null;
		} else {
			$masterCheck = $tableChecks->getById($mistake->check_id);
			$masterAudit = null;
		}
		
		// nacteni dalsich auditu
		$audits = $mistake->findManyToManyRowset($tableAudits, "Audit_Model_AuditsMistakes", "mistake", "audit", $tableAudits->select(false)->order("done_at"));
		
		/** @todo slouceni s proverkami a serazeni dle datumu */
		$found = $audits;
		
		$this->view->mistake = $mistake;
		$this->view->masterAudit = $masterAudit;
		$this->view->masterCheck = $masterCheck;
		$this->view->found = $found;
	}
	
	public function getHtmlAction() {
		$this->getAction();
		$this->view->layout()->setLayout("floating-layout");
	}
	
	public function indexAction() {
		// nacteni dat
		$clientId = $this->getRequest()->getParam("clientId", 0);
	
		// sestaveni klienta
		$tableClients = new Application_Model_DbTable_Client();
		$client = $tableClients->find($clientId)->current();
	
		if (!$client) throw new Zend_Exception("Client #$clientId has not been found");
	
		// sestaveni vyhledavaciho dotazu pro seznam neshod
		$tableAudits = new Audit_Model_Audits();
		$nameAudits = $tableAudits->info("name");
		
		$where = array(
				"client_id = " . $clientId,
				"audit_id in (select id from `$nameAudits` where client_id = $clientId and is_closed)"
		);
	
		// nacteni filtracniho formulare
		$formFilter = new Audit_Form_MistakeIndex();
		
		// naplneni pobocek
		$tableSubsidiaries = new Application_Model_DbTable_Subsidiary();
		$subsidiaries = $tableSubsidiaries->fetchAll("client_id = " . $client->id_client);
		
		$formFilter->addSubsidiaries($subsidiaries);
		$formFilter->populate($_REQUEST);
		
		// nastaveni dodatecnych filtracnich parametru
		$subsidiaryId = $formFilter->getValue("subsidiary_id");
		$filter = $formFilter->getValue("filter");
		
		// vyhodnoceni skryti pracoviste
		if (!$subsidiaryId) {
			// srkyti vyberu pracoviste
			$formFilter->removeElement("workplace_id");
		} else {
			// nacteni a zapis pracovist
			$tableWorkplaces = new Application_Model_DbTable_Workplace();
			$workplaces = $tableWorkplaces->fetchAll("subsidiary_id = " . $subsidiaryId, "name");
			
			$formFilter->addWorkplaces($workplaces);
		}
		
		$workplaceId = $formFilter->getValue("workplace_id");
		
		// zapis filtraci do dotazu
		if ($subsidiaryId) {
			$where[] = "subsidiary_id = $subsidiaryId";
		}
		
		if ($workplaceId) {
			$where[] = "workplace_id = $workplaceId";
		}
		
		switch ($filter) {
			case 1:
				$where[] = "!is_removed";
				break;
				
			case 2:
				$where[] = "is_removed";
				break;
		}
		
		// nacteni neshod
		$tableMistakes = new Audit_Model_AuditsRecordsMistakes();
		$mistakes = $tableMistakes->fetchAll($where);
		
		$this->view->formFilter = $formFilter;
		$this->view->mistakes = $mistakes;
		$this->view->client = $client;
	}
	
	public function postAction() {
		// nacteni a kontrola dat
		$data = $this->getRequest()->getParam("mistake", array());
		
		$form = new Audit_Form_MistakeCreate();
		
		if (!$form->isValid($_REQUEST)) {
			$this->_forward("create");
			return;
		}
		
		// naplneni dat a zpracovani dat formularem
		$form->populate($_REQUEST);
		
		// nacteni a kontrola zaznamu
		$recordId = $this->getRequest()->getParam("recordId", 0);
		$tableRecords = new Audit_Model_AuditsRecords();
		$record = $tableRecords->getById($recordId);
		
		// kontrola zaznamu
		if (!$record || $record->audit_id != $this->_audit->id || $record->mistake_id) {
			throw new Zend_Exception("Invalid audit record");
		}
		
		// vytvoreni neshody
		$tableMistakes = new Audit_Model_AuditsRecordsMistakes();
		$removed = new Zend_Date($form->getValue("will_be_removed_at"), "dd. MM. y");
		
		$mistake = $tableMistakes->createMistake(
				$record, 
				$removed, 
				$form->getValue("mistake"), 
				$form->getValue("suggestion"), 
				$form->getValue("comment"), 
				$form->getValue("hidden_comment"), 
				$form->getvalue("category"), 
				$form->getValue("subcategory"),
				$form->getValue("concretization"),
				$this->_audit);
		
		// nastaveni zodpovedne osoby
		$mistake->responsibile_name = $form->getValue("responsibile_name");
		$mistake->save();
		
		// nastaveni zaznamu
		$record->mistake_id = $mistake->id;
		$record->save();
		
		// kontrola kategorii
		$this->_postCategoriesIfNotExists($mistake->category, $mistake->subcategory);
		
		// presmerovani na audit
		$this->_redirect($this->view->url(array(
				"clientId" => $this->_audit->client_id,
				"auditId" => $this->_auditId
				), "audit-review"));
	}
	
	public function postaloneAction() {
		
		// kontrola dat
		$form = new Audit_Form_MistakeCreateAlone();
		
		if (!$form->isValidPartial($_REQUEST)) {
			$this->_forward("createalone");
			return;
		}
		
		// vytvoreni a ulozeni neshody
		$tableMistakes = new Audit_Model_AuditsRecordsMistakes();
		$removed = new Zend_Date($form->getValue("will_be_removed_at"), "dd. MM. y");
		
		$mistake = $tableMistakes->createMistake(
				null,
				$removed,
				$form->getValue("mistake"),
				$form->getValue("suggestion"),
				$form->getValue("comment"),
				$form->getValue("hidden_comment"),
				$form->getvalue("category"),
				$form->getValue("subcategory"),
				$form->getValue("concretization"),
				$this->_audit,
				$form->getvalue("weight"));
		
		// nastaveni id pracoviste
		$mistake->workplace_id = $form->getValue("workplace_id");
		$mistake->submit_status = Audit_Model_AuditsRecordsMistakes::SUBMITED_VAL_UNSUBMITED;
		
		// nastaveni zodpovedne osoby
		$mistake->responsibile_name = $form->getValue("responsibile_name");
		$mistake->save();
		
		// zaneseni zaznamu o asociaci
		$tableAssocs = new Audit_Model_AuditsMistakes();
		$tableAssocs->createAssoc($this->_audit, $mistake);
		
		// kontrola kategorii
		$this->_postCategoriesIfNotExists($mistake->category, $mistake->subcategory);
		
		$this->_redirect($this->view->url(
				array(
						"clientId" => $this->_audit->client_id,
						"auditId" => $this->_audit->id,
						"subsidiaryId" => $this->_audit->subsidiary_id
				), "audit-edit"
		));
	}
	
	public function putAction($redirect = true, $forwardOnError = null) {
		// nacteni neshody
		$mistakeId = $this->getRequest()->getParam("mistakeId", 0);
		$tableMistakes = new Audit_Model_AuditsRecordsMistakes();
		$mistake = $tableMistakes->getById($mistakeId);
		
		if (!$mistake) throw new Zend_Db_Table_Exception("Mistake id #$mistakeId not found");
		
		$form = new Audit_Form_MistakeCreate();
		
		// validace dat
		if (!$form->isValid($_REQUEST)) {
			if ($forwardOnError) {
				$this->_forward($forwardOnError);
			} else {
				$this->_forward("edit");
			}
			
			return false;
		}
		
		// zapis dat
		$data = $form->getValues(true);
		
		$mistake->setFromArray($data);
		
		// prepis datumu
		list($day, $month, $year) = explode(". ", $data["will_be_removed_at"]);
		$mistake->will_be_removed_at = $year . "-" . $month . "-" . $day;
		$mistake->save();
		
		// kontrola kategorii
		$this->_postCategoriesIfNotExists($mistake->category, $mistake->subcategory);
		
		// zapis asociace
		if ($mistake->record_id) {
			$tableAssocs = new Audit_Model_AuditsMistakes();
			$tableAssocs->delete(array(
					"audit_id = " . $this->_audit->id,
					"record_id = " . $mistake->record_id
			));
			
			// zapis nove asociace
			$tableAssocs->insert(array(
					"audit_id" => $this->_audit->id,
					"mistake_id" => $mistake->id,
					"record_id" => $mistake->record_id
			));
		}
		
		// presmerovani zpet na vypis
		$params = array("clientId" => $this->_audit->client_id, "auditId" => $this->_audit->id, "mistakeId" => $mistake->id);
		
		if ($redirect) $this->_redirect($this->view->url($params, "audit-mistake-edit"));
		
		return $params;
	}
	
	public function putHtmlAction() {
		// zavolani akce bez rediractu
		$params = $this->putAction(false, "edit.html");
		
		if (!$params) return;
		
		// redirect na spravnou adresu
		$this->_redirect($this->view->url($params, "audit-mistake-edit-html"));
	}
	
	public function submitAction() {
		// nacteni neshody
		$mistake = $this->_loadMistakeAndCheck();
		
		$mistake->assoc->submit_status = 1;
		$mistake->assoc->save();
		
		$this->view->mistake = $mistake;
	}
	
	public function submitJsonAction() {
		$this->submitAction();
	}
	
	public function unsubmitAction() {
		// nacteni neshody
		$mistake = $this->_loadMistakeAndCheck();
		
		$mistake->assoc->submit_status = 0;
		$mistake->assoc->save();
		
		$this->view->mistake = $mistake;
	}
	
	public function unsubmitJsonAction() {
		$this->unsubmitAction();
	}
	
	/**
	 * naplni formular pracovisti a podobne
	 * 
	 * @param Audit_Form_MistakeCreateAlone $form formular
	 * @return Audit_Form_MistakeCreateAlone
	 */
	protected function _fillAloneMistake(Zend_Form $form) {
		// naplneni pracovist
		$tableWorkplaces = new Application_Model_DbTable_Workplace();
		
		$workplaces = $tableWorkplaces->fetchAll("client_id = " . $this->_audit->client_id, "name");
		
		foreach ($workplaces as $item) {
			$workplaceList[$item->id_workplace] = $item->name;
		}
		
		$form->getElement("workplace_id")->setMultiOptions($workplaceList);
		
		return $form;
	}
	
	protected function _loadCategories($actualBase = null) {
		// nacteni kategorii a zapis do seznamu
		$tableCategories = new Audit_Model_Categories();
		$categories = $tableCategories->getRoots("name");
		
		$list = array();
		
		foreach ($categories as $item) {
			$list[] = $item->name;
		}
		
		$this->view->categories = $list;
		$this->view->subcategories = array();
		
		// kontrola kategorie
		if (!is_null($actualBase)) {
			// nacteni zakladni kategorie
			$base = $tableCategories->fetchRow(array("parent_id is null and name like " . $tableCategories->getAdapter()->quote($actualBase)));
			
			// kontrola, jeslti byla kategorie nalezena
			if ($base) {
				$subcategories = $base->getChildren();
				$subList = array();
				
				foreach ($subcategories as $item) {
					$subList[] = $item->name;
				}
				
				$this->view->subcategories = $subList;
			}
		}
	}
	
	/**
	 * vlozi kategorie do databaze, pokud neexistuji
	 * 
	 * @param array $categories seznam zkoumanych kategorii
	 */
	protected function _postCategoriesIfNotExists($category, $subcategory) {
		// tabulka a adapter
		$tableCategories = new Audit_Model_Categories();
		$adapter = $tableCategories->getAdapter();
		
		// nacteni kategorie a podkategorie
		$categoryRow = $tableCategories->fetchRow("parent_id is null and name like " . $adapter->quote($category));
		
		if (!$categoryRow) {
			$categoryRow = $tableCategories->createCategory($category);
		}
		
		// nacteni a kontrola podkategorie
		$subcategoryRow = $tableCategories->fetchRow(array(
				"name like " . $adapter->quote($subcategory),
				"parent_id = " . $categoryRow->id
		));
		
		if (!$subcategoryRow) {
			$tableCategories->createCategory($subcategory, $categoryRow);
		}
	}
	
	/**
	 * nacteni neshody podobne dane neshode
	 * 
	 * @param Audit_Model_Row_AuditRecordMistake $mistake
	 */
	public function _loadSimilarMistakes(Audit_Model_Row_AuditRecordMistake $mistake) {
		// ziskani tabulky
		$tableMistakes = $mistake->getTable();
		$tableAudits = new Audit_Model_Audits();
		
		$nameAudits = $tableAudits->info("name");
		
		// sestaveni dotazu pro where
		$where = array(
				"client_id = " . $mistake->client_id,
				"audit_id != " . $mistake->audit_id,
				"!is_removed",
				"audit_id in (select id from `$nameAudits` where subsidiary_id = $mistake->subsidiary_id and is_closed)"
		);
		
		// doplneni posledni podminky dle povahy neshody
		if ($mistake->workplace_id) {
			$where[] = "workplace_id = " . $mistake->workplace_id;
		} else {
			// vyhodnoceni nullovosti id itemu z dotazniku
			if ($mistake->questionary_item_id) {
				$where[] = "questionary_item_id = " . $mistake->questionary_item_id;
			} else {
				$where[] = "questionary_item_id = 0";
			}
		}
		
		return $tableMistakes->fetchAll($where, "created_at");
	}
	
	/**
	 * jednoucelova funkce pro nacteni a overeni nehsody
	 * 
	 * @return Audit_Model_Row_AuditRecordMistake
	 */
	private function _loadMistakeAndCheck() {
		// vyhodnoceni jeslti se jedna o neshodu z auditu nebo z proverky
		$auditId = $this->getRequest()->getParam("auditId", 0);
		$checkId = $this->getRequest()->getParam("checkId", 0);
		
		// nacteni neshody
		$tableMistakes = new Audit_Model_AuditsRecordsMistakes();
		$mistakeId = $this->getRequest()->getParam("mistakeId", 0);
		
		$mistake = $tableMistakes->getById($mistakeId);
		
		if (!$mistake) throw new Zend_Exception("Mistake #$mistakeId has not been found");
		
		// nacteni asociace
		if ($auditId) {
			$tableAssocs = new Audit_Model_AuditsMistakes();
			
			if ($mistake->record_id) {
				$where = array(
						"audit_id = " . $this->_audit->id,
						"record_id = " . $mistake->record_id
				);
				
				$assoc = $tableAssocs->fetchRow($where);
			} else {
				$assoc = $tableAssocs->find($this->_audit->id, $mistake->id)->current();
			}
		} else {
			$tableAssocs = new Audit_Model_ChecksMistakes();
			
			$assoc = $tableAssocs->find($checkId, $mistakeId)->current();
			
			$tableChecks = new Audit_Model_Checks();
			$check = $tableChecks->getById($checkId);
		}
		
		// kontrola prislusnosti k auditu
		if (!$assoc) throw new Zend_Exception("Invalid combination of audit and mistake");
		
		// kontrola pristupnosti k akci
		if ($auditId) {
			if ($this->_audit->coordinator_id != $this->_user->getIdUser()) throw new Zend_Exception("Invalid user");
		} else {
			if ($check->coordinator_id != $this->_user->getIdUser()) throw new Zend_Exception("Invalid user");
		}
		
		// vygenerovani navratove hodnoty
		$retVal = (object) array("mistake" => $mistake, "assoc" => $assoc);
		
		return $retVal;
	}
}
