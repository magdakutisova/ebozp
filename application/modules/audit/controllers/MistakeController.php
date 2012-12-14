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
		// nacteni zaznamu
		$recordId = $this->getRequest()->getParam("recordId", 0);
		$tableRecords = new Audit_Model_AuditsRecords();
		$record = $tableRecords->getById($recordId);
		
		// kontrola zaznamu
		if (!$record || $record->audit_id != $this->_audit->id || $record->mistake_id) throw new Zend_Exception("Invalid audit record");
		
		// nacteni neshody
		$data = $this->getRequest()->getParam("mistake", array());
		$data = array_merge(array("id" => 0), $data);
		
		$tableMistakes = new Audit_Model_AuditsRecordsMistakes();
		$mistake = $tableMistakes->getById($data["id"]);
		
		if (!$mistake || $mistake->client_id != $this->_audit->client_id) throw new Zend_Exception("Invalid mistake");
		
		// nastaveni dat
		$record->mistake_id = $mistake->id;
		$record->save();
		
		// presmerovani na audit
		$this->_redirect($this->view->url(array(
				"clientId" => $this->_audit->client_id,
				"auditId" => $this->_auditId
				), "audit-review"));
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
	
	public function createaloneAction() {
		$form = new Audit_Form_MistakeCreateAlone();
		
		// nastaveni akce
		$form->setAction(
				$this->view->url(array(
						"clientId" => $this->_audit->client_id,
						"auditId" => $this->_audit->id
				), "audit-mistake-postalone")
		);
		
		$this->_fillAloneMistake($form);
		
		$form->isValidPartial($_REQUEST);
		$this->_loadCategories();
		
		$this->view->form = $form;
		$this->view->audit = $this->_audit;
		$this->view->client = $this->_audit->getClient();
		$this->view->subsidiary = $this->_audit->getSubsidiary();
	}
	
	public function deleteAction($redirect = true) {
		// nacteni neshody
		$mistakeId = $this->getRequest()->getParam("mistakeId", 0);
		
		$tableMistakes = new Audit_Model_AuditsRecordsMistakes();
		$mistake = $tableMistakes->getById($mistakeId);
		
		if (!$mistake) throw new Zend_Db_Table_Exception("Mistake id #$mistakeId not found");
		
		$mistake->delete();
		
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
			$this->_fillAloneMistake($form);
			
			$deleteForm = new Audit_Form_MistakeDelete();
			$deleteForm->populate(array("mistake" => $mistake->toArray()));
		} else {
			$form = new Audit_Form_MistakeCreate();
			
			$deleteForm = null;
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
		$this->_loadCategories();
		
		$backTo = $this->view->url($params, $route);
		
		$this->view->form = $form;
		$this->view->formDelete = $formDelete;
		$this->view->backTo = $backTo;
		$this->view->client = $mistake->getClient();
		$this->view->subsidiary = $mistake->getSubsidiary();
		$this->view->mistake = $mistake;
		$this->view->deleteForm = $deleteForm;
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
		
		// nacteni zaznamu a auditu, kde byla chyba zjisteni
		$records = $mistake->findDependentRowset("Audit_Model_AuditsRecords", "mistake");
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
		
		// presmerovani na audit
		$this->_redirect($this->view->url(array(
				"clientId" => $this->_audit->client_id,
				"auditId" => $this->_auditId
				), "audit-review"));
	}
	
	public function postaloneAction() {
		
		// kontrola dat
		$form = new Audit_Form_MistakeCreateAlone();
		
		$this->_fillAloneMistake($form);
		
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
	
	/**
	 * naplni formular pracovisti a podobne
	 * 
	 * @param Audit_Form_MistakeCreateAlone $form formular
	 * @return Audit_Form_MistakeCreateAlone
	 */
	protected function _fillAloneMistake(Audit_Form_MistakeCreateAlone $form) {
		// naplneni pracovist
		$tableWorkplaces = new Application_Model_DbTable_Workplace();
		
		$workplaces = $tableWorkplaces->fetchAll("client_id = " . $this->_audit->client_id, "name");
		$workplaceList = array();
		
		foreach ($workplaces as $item) {
			$workplaceList[$item->id_workplace] = $item->name;
		}
		
		$form->getElement("workplace_id")->setMultiOptions($workplaceList);
		
		return $form;
	}
	
	protected function _loadCategories() {
		// nacteni kategorii a zapis do seznamu
		$tableCategories = new Audit_Model_Categories();
		$categories = $tableCategories->getRoots("name");
		
		$list = array();
		
		foreach ($categories as $item) {
			$list[] = $item->name;
		}
		
		$this->view->categories = $list;
	}
}