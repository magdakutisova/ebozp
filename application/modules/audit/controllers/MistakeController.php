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
		$toInsert = $this->_prepareAttachDetach();
		
		// pokud neco bylo nalezeno, zapise se to do databaze
		if ($toInsert) {
			// vygenerovani kodu pro vlozeni do databaze - kontrola integrity je na strane databaze
			$tableMistakes = new Audit_Model_AuditsRecordsMistakes();
			$tableAssocs = new Audit_Model_AuditsMistakes();
			$nameMistakes = $tableMistakes->info("name");
			$nameAssocs = $tableAssocs->info("name");
			
			$adapter = Zend_Db_Table_Abstract::getDefaultAdapter();
			$in = $adapter->quote($toInsert);
			$auditId = $this->_audit->id;
			
			$sql = "insert into `$nameAssocs` (audit_id, mistake_id, record_id, status) ";
			$sql .= "select $auditId, id, record_id, (is_removed * 0 + (!is_removed and !is_marked) * 1 + (!is_removed and is_marked) * 2) as status  from `$nameMistakes` where id in ($in) and id not in (select mistake_id from `$nameAssocs` where audit_id = $auditId)";
			
			$adapter->query($sql);
		}
		
		// premserovani na audit
		$url = $this->view->url(array("clientId" => $this->_audit->client_id, "auditId" => $this->_audit->id), "audit-edit") . "#mistakes";
		$this->_redirect($url);
	}
	
	public function createalone2Action() {
		$form = new Audit_Form_MistakeCreateAlone();

		$request = $this->getRequest();
		$clientId = $request->getParam("clientId", 0);
		$auditId = $request->getParam("auditId", 0);

		// nastaveni akce formulare
		$url = $this->view->url(array(
				"clientId" => $clientId,
				"auditId" => $auditId), "audit-mistake-postalone");
			
		$client = $this->_audit->getClient();
		$subsidiary = $this->_audit->getSubsidiary();

		// nastaveni akce
		$form->setAction($url);

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

		if (!$workplace && $workplaceId != 0) {
			// pracoviste nebylo nalezeno - vraceni zpatky
			$this->_forward("edit", "audit");
			return;
		}
		
		// nacteni neshod, ktere nejsou pripojeny k auditu a ktere nalezi k pracovisti a nebyly jeste odstraneny
		if ($workplace) {
			$tableMistakes = new Audit_Model_AuditsRecordsMistakes();
			$tableAssocs = new Audit_Model_AuditsMistakes();
			$nameAssocs = $tableAssocs->info("name");
			$mistakes = $tableMistakes->fetchAll(array("is_submited", "!is_removed", "workplace_id = " . $workplace->id_workplace, "id not in (select mistake_id from `$nameAssocs` where audit_id = " . $this->_audit->id . ")"));
		} else {
			$mistakes = new Zend_Db_Table_Rowset(array("data" => array()));
		}
		
		$this->view->form = $form;
		$this->view->audit = $this->_audit;
		$this->view->client = $client;
		$this->view->subsidiary = $subsidiary;
		$this->view->workplace = $workplace;
		$this->view->mistakes = $mistakes;
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

	public function detachAction() {
		$toDelete = $this->_prepareAttachDetach();
		
		if ($toDelete) {
			// vygenerovani mazaciho dotazu
			$tableAssocs = new Audit_Model_AuditsMistakes();
			$tableMistakes = new Audit_Model_AuditsRecordsMistakes();
			$nameAssocs = $tableAssocs->info("name");
			$nameMistakes = $tableMistakes->info("name");
			$adapter = Zend_Db_Table_Abstract::getDefaultAdapter();
			$in = $adapter->quote($toDelete);
			$auditId = $this->_audit->id;
			
			$sql = "delete from `$nameAssocs` where audit_id = $auditId and mistake_id in ($in) and mistake_id not in (select id from `$nameMistakes` where id in ($in) and audit_id = $auditId)";
			$adapter->query($sql);
		}
		
		// presmerovani
		$url = $this->view->url(array("clientId" => $this->_audit->client_id, "auditId" => $this->_audit->id), "audit-edit") . "#mistakes";
		$this->_redirect($url);
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
			$record = null;
		} else {
			$form = new Audit_Form_MistakeCreate();
				
			$deleteForm = null;
				
			// nacteni aktivni asociace a zaznamu
			$where = array(
					"audit_id = " . $this->_audit->id,
					"record_id = " . $mistake->record_id
			);
			
			$tableAssocs = new Audit_Model_AuditsMistakes();
			$activeAssoc = $tableAssocs->fetchRow($where);
			$record = $mistake->findParentRow("Audit_Model_AuditsRecords", "record");
			
			// kontrola nastaveni zaznamu record
			if ($record->score != Audit_Model_AuditsRecords::SCORE_N) {
				$record->score = Audit_Model_AuditsRecords::SCORE_N;
				$record->save();
			}
			
			// kontrola existence asociace
			if (!$activeAssoc) {
				// vyhodnoceni stavu
				$status = 1;
				if ($mistake->is_removed) 
					$status = 0;
				elseif($mistake->is_marked)
					$status = 2;
				
				$tableAssocs->insert(array(
						"audit_id" => $record->audit_id, 
						"mistake_id" => $mistake->id,
						"record_id" => $record->id,
						"status" => $status
				));
				
				$activeAssoc = (object) array("mistake_id" => $mistake->id);
			}
			
			$this->view->activeAssoc = $activeAssoc; 
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

		$this->view->form = $form;
		$this->view->formDelete = $formDelete;
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

		$masterAudit = $tableAudits->getById($mistake->audit_id);
		$masterCheck = null;

		// nacteni dalsich auditu
		$audits = $mistake->findManyToManyRowset($tableAudits, "Audit_Model_AuditsMistakes", "mistake", "audit", $tableAudits->select(false)->order("done_at"));

		// nacteni historie neshody
		$found = self::getMistakeHistory($mistake);

		// nacteni lidi
		$userIds = array(0);

		foreach ($found as $item) {
			$userIds[] = $item["coordinator_id"];
			$userIds[] = $item["auditor_id"];
		}

		$tableUsers = new Application_Model_DbTable_User();
		$users = $tableUsers->find($userIds);
		$userIndex = array();

		foreach ($users as $user) {
			$userIndex[$user->id_user] = $user;
		}

		$this->view->mistake = $mistake;
		$this->view->masterAudit = $masterAudit;
		$this->view->found = $found;
		$this->view->userIndex = $userIndex;
	}

	public function getHtmlAction() {
		$this->getAction();
		$this->view->layout()->setLayout("floating-layout");
	}
	
	public function importAction() {
		// kontrola dat
		$clientId = $this->_request->getParam("clientId", 0);
		$subsidiaryId = $this->_request->getParam("subsidiaryId", 0);
		
		// nacteni pobocky
		$tableSubsidiaries = new Application_Model_DbTable_Subsidiary();
		$subsidiary = $tableSubsidiaries->fetchRow(array("id_subsidiary = ?" => $subsidiaryId, "client_id = ?" => $clientId));
		
		if (!$subsidiary) throw new Zend_Exception("Subsidiary not found");
		
		// kontrola odeslani souboru
		if (!is_file($_FILES["importfile"]["tmp_name"])) {
			// soubor nebyl nalezen
			throw new Zend_Exception("File not found");
		}
		
		// otevreni souboru a nacitani dat
		$fp = fopen($_FILES["importfile"]["tmp_name"], "r");
		
		// preskoceni prvniho radku
		fgetcsv($fp);
		
		// nacitani dat a vygenerovani dotazu pro vlozeni
		$insert = array();
		$adapter = $tableSubsidiaries->getAdapter();
		
		while(!feof($fp)) {
			$item = fgetcsv($fp);
			
			if (count($item) < 5) continue;
			
			// vyhodnocení stavu
			$state = ($item[11] == "odstraněno") ? 1 : 0;
			
			$motifiedAt = self::_toSQLDate($item[10]);
			$removedAt = self::_toSQLDate($item[13]);
			
			$insert[] = "("
					. $subsidiary->client_id
					. "," . $subsidiary->id_subsidiary
					. "," . $adapter->quote($item[3]) // zavaznost
					. "," . $adapter->quote($item[4])	// kategorie
					. "," . $adapter->quote($item[5])	// podkategorie
					. "," . $adapter->quote($item[6])	// upresneni
					. "," . $adapter->quote($item[7])	// neshoda
					. "," . $adapter->quote($item[8])	// navrh opatreni
					. "," . $adapter->quote($item[9])	// kometar
					. "," . $adapter->quote($notifiedAt)
					. "," . $adapter->quote($state)
					. "," . $adapter->quote($item[13])	// zodpovedna osoba
					. "," . $adapter->quote($removedAt)
					. "," . $adapter->quote($item[15]) . ", 1)";	// skryta poznamka a hodnota is_submited
		}
		
		// smazani starych dat
		$tableMistakes = new Audit_Model_AuditsRecordsMistakes();
		$tableMistakes->delete(array("subsidiary_id = ?" => $subsidiary->id_subsidiary, "audit_id is null"));
		
		// sestaveni zapisovaciho dotazu
		$sql = "insert into `" . $tableMistakes->info("name") . "` (client_id, subsidiary_id, weight, question, category, subcategory, concretisation, mistake, suggestion, comment, notified_at, is_removed, responsibile_name, will_be_removed_at, is_submited) values ";
		$sql .= implode(",", $insert);
		
		$adapter->query($sql);
		
		$url = $this->view->url(array("clientId" => $clientId), "audit-mistakes-index");
		$this->_redirect($url);
	}

	public function indexAction() {
		// nacteni dat
		$clientId = $this->getRequest()->getParam("clientId", 0);

		// sestaveni klienta
		$tableClients = new Application_Model_DbTable_Client();
		$client = $tableClients->find($clientId)->current();

		if (!$client) throw new Zend_Exception("Client #$clientId has not been found");

		// sestaveni vyhledavaciho dotazu pro seznam neshod
		$where = array(
				"client_id = " . $clientId,
				"is_submited"
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
		
		// nacteni kategorii
		$tableCategories = new Audit_Model_Categories();
		$categories = $tableCategories->fetchAll("parent_id IS NULL", "name");
		$category = $formFilter->category->getValue();
		$adapter = Zend_Db_Table_Abstract::getDefaultAdapter();
		
		foreach ($categories as $item) {
			$formFilter->getElement("category")->addMultiOption($item->id, $item->name);
			if ($item->id == $category) $where[] = "category like " . $adapter->quote($item->name);
		}
		
		if (!$category) {
			$formFilter->removeElement("subcategory");
		} else {
			// nacteni podkategorii
			$categories = $tableCategories->fetchAll("parent_id = " . $adapter->quote($category), "name");
			$subcategory = $formFilter->getElement("subcategory")->getValue();
			
			foreach ($categories as $item) {
				$formFilter->getElement("subcategory")->addMultiOption($item->id, $item->name);
				if ($item->id == $subcategory) $where[] = "subcategory like " . $adapter->quote($item->name);
			}
		}

		$workplaceId = $formFilter->getValue("workplace_id");

		// zapis filtraci do dotazu
		if ($subsidiaryId) {
			$where[] = "subsidiary_id = $subsidiaryId";
		}

		if ($workplaceId) {
			$where[] = "workplace_id = $workplaceId";
		}
		
		if ($weight = $formFilter->weight->getValue()) {
			$where[] = "weight = " . $adapter->quote($weight);
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
				$form->getvalue("weight"),
				null);

		// nastaveni id pracoviste
		$mistake->workplace_id = ($workpalceId = $form->getValue("workplace_id")) ? $form->getValue("workplace_id") : null;

		// nastaveni zodpovedne osoby
		$mistake->responsibile_name = $form->getValue("responsibile_name");
		$mistake->save();

		// zaneseni zaznamu o asociaci
		$tableAssocs = new Audit_Model_AuditsMistakes();
		$tableAssocs->createAssoc($this->_audit, $mistake);

		// kontrola kategorii
		$this->_postCategoriesIfNotExists($mistake->category, $mistake->subcategory);

		// protoze se muzeme na akci dostat z vice mist, rozhodneme, ze ktereho jsme se sem dostali
		$params = array(
				"clientId" => $this->getRequest()->getParam("clientId", 0),
				"auditId" => $this->_auditId,
				"subsidiaryId" => $this->_audit->subsidiary_id,
				"checkId" => $checkId
		);

		$url = $this->view->url($params, "audit-edit") . "#workcomments";

		$this->_redirect($url);
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

		if (!$data["record_id"]) $data["record_id"] = new Zend_Db_Expr("NULL");

		$mistake->setFromArray($data);

		// prepis datumu
		list($day, $month, $year) = explode(". ", $data["will_be_removed_at"]);
		$mistake->will_be_removed_at = $year . "-" . $month . "-" . $day;
		$mistake->save();

		// kontrola kategorii
		$this->_postCategoriesIfNotExists($mistake->category, $mistake->subcategory);

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

	public function setstatusJsonAction() {
		// nacteni dat
		$data = (array) $this->getRequest()->getParam("status", array());

		// rozdeleni neshod dle noveho stavu
		$ok = array();		// neshody, ktere byly oznaceny jako v poradku
		$new = array();		// neshody, ktere jsou nove (nebo je jeste cas na odstraneni)
		$fail = array();	// neshody, ktere byly oznaceny jako nevyhovujici

		foreach ($data as $mistakeId => $status) {
			switch ($status) {
				case 0:
					$ok[] = $mistakeId;
					break;
						
				case 1:
					$new[] = $mistakeId;
					break;
						
				case 2:
					$fail[] = $mistakeId;
			}
		}

		// priprava zakladniho SQL dotazu
		$tableAssocs = new Audit_Model_AuditsMistakes();
		$nameAssocs = $tableAssocs->info("name");

		$sqlBegin = "update `$nameAssocs` set ";
		$sqlEnd = " where `$nameAssocs`.audit_id = $this->_auditId and mistake_id in (";

		// vygenerovani specifickych dotazu pro jednotlive seznamy a jejich odeslani
		// pokud neni potreba odesilat, nic se neprovede
		$adapter = Zend_Db_Table_Abstract::getDefaultAdapter();

		if ($new) {
			$sql = $sqlBegin . "`status` = 1 " . $sqlEnd . $adapter->quote($new) . ")";
			$adapter->query($sql);
		}

		if ($ok) {
			$sql = $sqlBegin . "`status` = 0 " . $sqlEnd . $adapter->quote($ok) . ")";
			$adapter->query($sql);
		}
		
		if ($fail) {
			$sql = $sqlBegin . "`status` = 2 " . $sqlEnd . $adapter->quote($fail) . ")";
			$adapter->query($sql);
		}
		
		$this->view->response = true;
	}
	
	public function switchAction() {
		// nacteni zaznamu
		$recordId = $this->getRequest()->getParam("recordId", 0);
		$tableRecords = new Audit_Model_AuditsRecords();
		$record = $tableRecords->getById($recordId);
		
		if ($record->audit_id != $this->_audit->id) throw new Zend_Exception("Record #$recordId is not belongs to audit");
		
		// nacteni pozadovane neshody
		$tableMistakes = new Audit_Model_AuditsRecordsMistakes();
		$tableAssocs = new Audit_Model_AuditsMistakes();
		$nameMistakes = $tableMistakes->info("name");
		$nameAssocs = $tableAssocs->info("name");
		
		// nacteni neshody a vyhodnoceni stavu
		$data = (array) $this->getRequest()->getParam("mistake", array());
		$data = array_merge(array("id" => 0), $data);
		$mistake = $tableMistakes->getById($data["id"]);
		if (!$mistake) throw new Zend_Exception("Mistake not found");
		
		$status = 1;
		if ($mistake->is_removed) $status = 0; elseif ($mistake->is_marked) $status = 2;
		
		
		// smazani aktualni aktualizace
		$tableAssocs->delete(array("audit_id = " . $this->_audit->id, "record_id = " . $record->id));
		
		// zapis nove asociace
		$tableAssocs->insert(array(
				"audit_id" => $this->_audit->id,
				"mistake_id" => $data["id"],
				"record_id" => $record->id,
				"status" => $status
		));
		
		// nacteni puvodni neshody a presmerovani na ni
		$originalMistake = $tableMistakes->fetchRow(array("record_id = " . $record->id));
		$url = $this->view->url(array("clientId" => $this->_audit->client_id, "auditId" => $this->_audit->id, "mistakeId" => $originalMistake->id), "audit-mistake-edit-html");
		$this->_redirect($url);
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

		// nacteni neshody
		$tableMistakes = new Audit_Model_AuditsRecordsMistakes();
		$mistakeId = $this->getRequest()->getParam("mistakeId", 0);

		$mistake = $tableMistakes->getById($mistakeId);

		if (!$mistake) throw new Zend_Exception("Mistake #$mistakeId has not been found");

		// nacteni asociace
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

		// kontrola prislusnosti k auditu
		if (!$assoc) throw new Zend_Exception("Invalid combination of audit and mistake");

		// kontrola pristupnosti k akci
		if ($this->_audit->coordinator_id != $this->_user->getIdUser()) throw new Zend_Exception("Invalid user");

		// vygenerovani navratove hodnoty
		$retVal = (object) array("mistake" => $mistake, "assoc" => $assoc);

		return $retVal;
	}

	public static function getMistakeHistory(Audit_Model_Row_AuditRecordMistake $mistake) {
		// nacteni zaznamu auditu a proverek
		$audits = $mistake->getAudits();

		// prevod na pole, slouceni a prevedeni datumu na cislo
		$arrData = $audits->toArray();
		$maxI = count($arrData);

		for ($i = 0; $i < $maxI; $i++) {
			$numDate = (int) implode("", explode("-", $arrData[$i]["done_at"]));
			$arrData[$i]["done_at_int"] = $numDate;
		}

		// definice anonymni funkce callbacku
		$callback = "self::SORT_CALLBACK";

		usort($arrData, $callback);

		return $arrData;
	}
	
	protected function _prepareAttachDetach() {
		// kontrola auditu
		if (!$this->_audit) throw new Zend_Exception("Audit not found");
		$userId = $this->_user->getIdUser();
		if ($this->_audit->coordinator_id != $userId && $this->_audit->auditor_id != $userId && $this->_user->getRoleId() != My_Role::ROLE_ADMIN) throw new Zend_Exception("This action is not allowed to yout");
		
		// nacteni dat a jejich zpracovani
		$data = (array) $this->getRequest()->getParam("mistake", array());
		
		// sestaveni seznamu pro insert
		$checked = array();
		
		foreach ($data as $id => $insert) {
			if ($insert["select"]) $checked[] = $id;
		}
		
		return $checked;
	}

	private static function SORT_CALLBACK ($item1, $item2) {
		if ($item1["done_at_int"] == $item2["done_at_int"]) return 0;

		return ($item1["done_at_int"] > $item2["done_at_int"]) ? 1 : -1;
	}
	
	/**
	 * prevede datum na SQL format
	 * 
	 * @param string $date puvodni datum z excelu
	 * @return string
	 */
	private static function _toSQLDate($date) {
		list($day, $month, $year) = explode(".", $date);
		
		if (strlen($month) == 1) $month = "0" . $month;
		if (strlen($day) == 1) $day = "0" . $day;
		
		return $year . "-" . $month . "-" . $day;
	}
}
