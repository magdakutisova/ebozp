<?php
require_once __DIR__ . "/WatchController.php";

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
			$sql .= "select $auditId, id, record_id, 1  from `$nameMistakes` where id in ($in) and id not in (select mistake_id from `$nameAssocs` where audit_id = $auditId)";
			
			$adapter->query($sql);
		}
		
		$this->_helper->FlashMessenger("Neshoda přiřazena");
		
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
        $form->removeElement("is_removed");
		$form->setFilledCategory(@$_REQUEST["mistake"]["category"], @$_REQUEST["mistake"]["subcategory"]);
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
	
	/**
	 * vytvoreni nove neshody z registru neshod
	 */
	public function createAction() {
		// vytvoreni formulare
		$form = new Audit_Form_MistakeCreateAlone();
		$form->setAction("/audit/mistake/post?clientId=" . $this->_request->getParam("clientId", 0));
		$form->removeElement("is_removed");
        
		$this->_preprareCreateForm($form);
		$form->setFilledCategory(@$_REQUEST["mistake"]["category"], @$_REQUEST["mistake"]["subcategory"]);
		
		$form->isValidPartial($this->_request->getParams());
		
		$this->view->form = $form;
	}
	
	/*
	 * vytvoreni neshody primo z registru z dialogu
	 */
	public function createHtmlAction() {
		$this->createAction();
	}

	public function deleteAction($redirect = true) {
		// nacteni neshody
		$mistakeId = $this->getRequest()->getParam("mistakeId", 0);

		$mistake = self::getMistake($mistakeId);
		
		// vyhodnoceni prislusnosti k auditu
		if (!$this->_audit || $mistake->audit_id == @$this->_audit->id) {
			// nejsme v zadnem auditu nebo v auditu, kde byla neshoda definovana
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
		
		$this->_helper->FlashMessenger("Neshoda smazána");

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
			
			/*
			 * UPRAVA AKCE -> NAKONEC SE TO POUZIVA I PRO ZMENU ODSTRANENI / NEODSTRANENI
			 */
			
			$where = "audit_id = $auditId and mistake_id in ($in) and mistake_id not in (select id from `$nameMistakes` where id in ($in) and audit_id = $auditId)";
			
			if ($this->_request->getParam("submit-solve", 0)) {
				// oznaceni jako odtranene
				$sql = "update `$nameAssocs` set status = 2 where " . $where;
			} elseif ($this->_request->getParam("submit-unsolve", 0)) {
				// oznaceni jako neodstranene
				// oznaceni jako odtranene
				$sql = "update `$nameAssocs` set status = 1 where " . $where;
			} else {
				// odebrani z auditu
				$sql = "delete from `$nameAssocs` where " . $where;
			}
			
			// odeslani dotazu
			$adapter->query($sql);
		}
		
		$this->_helper->FlashMessenger("Neshoda odebrána");
		
		// presmerovani
		$url = $this->view->url(array("clientId" => $this->_audit->client_id, "auditId" => $this->_audit->id, "subsidiaryId" => $this->_audit->subsidiary_id), "audit-edit") . "#mistakes";
		$this->_redirect($url);
	}
	
	public function editAction() {
		// nacteni neshody
		$mistake = self::getMistake($this->_request->getParam("mistakeId", 0));

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
		} elseif ($mistake->record_id) {
			$form = new Audit_Form_MistakeCreate();
				
			$deleteForm = null;
				
			// nacteni aktivni asociace a zaznamu
			$where = array(
					"audit_id = " . $this->_audit->id,
					"mistake_id = " . $mistake->id
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
				elseif($mistake->isMarked(Zend_Date::now()->get("yMMdd")))
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
		} else {
			$form = new Audit_Form_MistakeCreateAlone();
		}

		$form->setFilledCategory($mistake->category, $mistake->subcategory);
		$form->populate(array("mistake" => $data));
		$form->getElement("submit")->setLabel("Uložit");
		
		// vyhodnoceni auditu a parametru pro url
		if ($this->_audit) {
			$route = "audit-mistake-put";
			
			$params = array(
				"clientId" => $mistake->client_id,
				"auditId" => $this->_audit->id,
				"mistakeId" => $mistake->id
			);
		} else {
			$route = "audit-mistake-put-html";
			
			$params = array(
					"clientId" => $mistake->client_id,
					"mistakeId" => $mistake->id
			);
		}
		
		$form->setAction($this->view->url($params, $route));

		// nastaveni dat z requestu, pokud neco je k dispozici
		$form->isValidPartial($_REQUEST);

		$params = array(
				"clientId" => $mistake->client_id,
				"auditId" => $mistake->audit_id,
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
		$this->view->deleteForm = $formDelete;
		$this->view->similars = $similars;
		$this->view->audit = $this->_audit;
		$this->view->record = isset($record) ? $record : null;
	}

	public function editHtmlAction() {
		// provedeni akce a vypnuti layoutu
		$this->editAction();
		$this->view->layout()->setLayout("floating-layout");

		// nastaveni zemenne routy formulare
		$params = array(
				"clientId" => $this->view->mistake->client_id,
				"auditId" => $this->view->mistake->audit_id,
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
		$mistake = self::getMistake($this->_request->getParam("mistakeId", 0));

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
		
		// pokud je puvodni soubor v jinem kodovani nez UTF8 - prekoduje se
		$encoding = $this->_request->getParam("encoding", "UTF8");
		if ($encoding != "UTF8") {
			$content = file_get_contents($_FILES["importfile"]["tmp_name"]);
			$content = iconv($encoding, "utf8", $content);
			file_put_contents($_FILES["importfile"]["tmp_name"], $content);
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
			$stateStr = strtolower($item[11]);
			
			// pokud je stav NT, pak se radek uplne preskoci
			if ($stateStr == "nt" || substr($stateStr, 0, 3) == "net") continue;
			
			$state = (substr($stateStr, 0, 7) == "odstran") ? 1 : 0;
			
			$notifiedAt = self::_toSQLDate($item[10]);
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
					. "," . $adapter->quote($item[12])	// zodpovedna osoba
					. "," . $adapter->quote($removedAt)
					. "," . $adapter->quote(@$item[14]) . ", 1)";	// skryta poznamka a hodnota is_submited
		}
		
		// smazani starych dat
		$tableMistakes = new Audit_Model_AuditsRecordsMistakes();
		$tableMistakes->delete(array("subsidiary_id = ?" => $subsidiary->id_subsidiary, "audit_id is null"));
		
		// sestaveni zapisovaciho dotazu
		$sql = "insert into `" . $tableMistakes->info("name") . "` (client_id, subsidiary_id, weight, category, subcategory, concretisation, mistake, suggestion, comment, notified_at, is_removed, responsibile_name, will_be_removed_at, hidden_comment, is_submited) values ";
		$sql .= implode(",", $insert);
		
		$adapter->query($sql);
		
		$this->_helper->FlashMessenger("Neshody importovány");
		
		$url = $this->view->url(array("clientId" => $clientId, "subsidiaryId" => $subsidiaryId), "audit-mistakes-index-subs");
		$this->_redirect($url);
	}

	public function indexAction() {
		// nacteni dat
		$clientId = $this->getRequest()->getParam("clientId", 0);
		$subsidiaryId = $this->_request->getParam("subsidiaryId", null);
		
		// kontrola, jestli byla odeslana pobocka pomoci filtrace
		$filterArr = (array) $this->_request->getParam("mistake", array());
		$filterArr = array_merge(array("subsidiary_id" => null), $filterArr);
		
		if (!is_null($filterArr["subsidiary_id"])) {
			$subsidiaryId = $filterArr["subsidiary_id"];
			$this->_request->setParam("subsidiaryId", $subsidiaryId ? $subsidiaryId : null);
		} elseif (is_null($filterArr["subsidiary_id"])) {
			$filterArr["subsidiary_id"] = $subsidiaryId;
			$this->_request->setParam("mistake", $filterArr);
		}
		
		// nacteni klienta
		$tableClients = new Application_Model_DbTable_Client();
		$client = $tableClients->find($clientId)->current();
		
		// nacteni pobocek, ke kterym ma uzivatel pristup
		$user = Zend_Auth::getInstance()->getIdentity();
		$where = array("client_id = ?" => $clientId, "active", "!deleted");
		
		if ($user->role == My_Role::ROLE_CLIENT || $user->role == My_Role::ROLE_TECHNICIAN) {
			// uzivatel ma omezeny pristup k pobockam
			$tableAssocs = new Application_Model_DbTable_UserHasSubsidiary();
			
			// vytvoreni fitlranicho selectu
			$select = $tableAssocs->select(true);
			$select->reset(Zend_Db_Table_Select::COLUMNS);
			$select->columns("id_subsidiary")->where("id_user = ?", $user->id_user);
			
			// vytvoreni podminky
			$where["id_subsidiary in (?)"] = new Zend_Db_Expr($select->assemble());
		}
		
		// nacteni pobocek
		$tableSubsidiaries = new Application_Model_DbTable_Subsidiary();
		$subsidiaries = $tableSubsidiaries->fetchAll($where, array("subsidiary_name", "subsidiary_town", "subsidiary_street"));
		$subsidiary = $tableSubsidiaries->find($subsidiaryId)->current();
		
		// priprava filtracniho formulare
		$formFilter = new Audit_Form_MistakeIndex();
		$formFilter->populate($this->_request->getParams());
		
		// naplneni dat pobocek
		$formFilter->addSubsidiaries($subsidiaries);
		
		// nacteni neshod
		$tableMistakes = new Audit_Model_AuditsRecordsMistakes();
        
        // vyhodnoceni dalsich filtru
        $filterVals = $formFilter->getValues(true);
        $otherFilters = array();
        
        foreach ($filterVals as $key => $val) {
            if ($key != "filter" && $val !== '0' && !is_null($val)) {
                
                // vyhodnoceni typu
                if ($key == "category" || $key == "subcategory") {
                    $otherFilters["$key like ?"] = $val;
                } else {
                    if ($key == "subsidiary_id") $key = "audit_audits_records_mistakes.$key";
                    
                    $otherFilters["$key = ?"] = $val;
                }
            }
        }
		
		if ($subsidiary) {
			$mistakes = $tableMistakes->getBySubsidiary($subsidiary, $formFilter->getValue("filter"), $otherFilters);
		} else {
			$mistakes = $tableMistakes->getByClient($client, $formFilter->getValue("filter"), $otherFilters);
		}
		
		// nastaveni hodnot pro vyber filtracniho formulare
		$workplaces = array("---");
		$categories = array("---");
		$subcategories = array("---");
		
		foreach ($mistakes as $mistake) {
			if ($mistake->workplace_name)
				$workplaces[$mistake->id_workplace] = $mistake->workplace_name;
			
			$categories[$mistake->category] = $mistake->category;
			
			$subcategories[$mistake->subcategory] = $mistake->subcategory;
		}
		
		$workplaces = array_unique($workplaces);
		$categories = array_unique($categories);
		$subcategories = array_unique($subcategories);
        
		foreach ($subcategories as &$list) {
			if (is_array($list))
				$list = array_unique($list);
		}
		
		$formFilter->getElement("workplace_id")->setMultiOptions($workplaces);
		$formFilter->getElement("category")->setMultiOptions($categories);
		$formFilter->getElement("subcategory")->setMultiOptions($subcategories);
		
		$this->view->formFilter = $formFilter;
		$this->view->mistakes = $mistakes;
		$this->view->client = $client;
	}
	
	public function indexJsonAction() {
		$this->indexAction();
	}
	
	public function indexXmlAction() {
		$this->indexAction();
	}
	
	public function postAction() {
		// priprava dat
		$form = new Audit_Form_MistakeCreateAlone();
		
		$this->_preprareCreateForm($form);
		
		if (!$form->isValid($this->_request->getParams())) {
			$this->_forward("create");
			return;
		}
		
		$clientId = $this->_request->getParam("clientId", 0);
		$data = $form->getValues(true);
		$data["client_id"] = $clientId;
		$data["is_submited"] = 1;
		
		// kontrola id_subsidiary
		if (!$data["subsidiary_id"]) {
			$data["subsidiary_id"] = null;
		} else {
			if (!$data["workplace_id"]) {
				$data["workplace_id"] = null;
			}
		}
		
		// prepis datumu
		list($day, $month, $year) = explode(". ", trim($data["notified_at"]));
		$data["notified_at"] = "$year-$month-$day";
		list($day, $month, $year) = explode(". ", trim($data["will_be_removed_at"]));
		$data["will_be_removed_at"] = "$year-$month-$day";
		
		unset($data["record_id"]);
		
		$tableMistakes = new Audit_Model_AuditsRecordsMistakes();
		$tableMistakes->insert($data);
		
		$this->_helper->FlashMessenger("Neshoda vytvořena");
		
		$this->view->clientId = $clientId;
		$this->view->mistake = $data;
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

		$this->_helper->FlashMessenger("Neshoda vytvořena");
		
		$url = $this->view->url($params, "audit-edit");

		$this->_redirect($url);
	}

	public function putAction($redirect = true, $forwardOnError = null) {
		// nacteni neshody
		$mistake = self::getMistake($this->_request->getParam("mistakeId", 0));

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
		$params = array("clientId" => $mistake->client_id, "auditId" => $mistake->audit_id, "mistakeId" => $mistake->id);

		$this->_helper->FlashMessenger("Změny byly uloženy");
		
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
	
	public function removeAction() {
		// nacteni id neshody a klienta
		$clientId = $this->_request->getParam("clientId", 0);
		
		// nactnei identity
		$user = Zend_Auth::getInstance()->getIdentity();
		
		// nacteni dat neshody a pobocky
		$mistake = self::getMistake($this->_request->getParam("mistakeId", 0));
		$subsidiary = $mistake->getSubsidiary();
		$tableSubsidiaries = new Application_Model_DbTable_Subsidiary();
		
		// pokud je pobocka NULL, pak musi mit uzivatel pod palcem centralu
		if (is_null($subsidiary)) {
			$subsidiary = $tableSubsidiaries->fetchAll(array("client_id = ?" => $clientId, "hq"));
		}
		
		// kontrola ACL
		$acl = new My_Controller_Helper_Acl();
		
		if ($acl->isAllowed($user->role, "audit:mistake", "removeall")) {
			// uzivatel nema opravneni manipulovat se vsema neshodama - zkontroluje se prislusnost ke klientovi
			$tableAssocs = new Application_Model_DbTable_UserHasSubsidiary();
			$nameAssocs = $tableAssocs->info("name");
			$nameSubsidiaries = $tableSubsidiaries->info("name");
			
			$select = new Zend_Db_Select($tableAssocs->getAdapter());
			$select->from($nameSubsidiaries, array("subsidiary_name"))
					->joinInner($nameAssocs, "$nameAssocs.id_subsidiary = $nameSubsidiaries.id_subsidiary", array("id_subsidiary"))
					->where("id_user = ?", $user->id_user);
			die(var_dump($tableAssocs->getAdapter()->query($select)->fetch()));
			if (!$tableAssocs->getAdapter()->query($select)->fetch()) throw new Zend_Exception("Invalid user");
		}
		die;
	}
	
	public function removeHtmlAction() {
		$this->removeAction();
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
    
    /*
     * hromadne odesle neshody
     */
    public function submitsAction() {
        // nacteni dat
        $clientId = $this->_request->getParam("clientId", 0);
        $data = (array) $this->_request->getParam("mistake", array());
        
        // pokud zadna data nebyla odeslana, pak se nic delat nebude
        if (!$data) return;
        
        $mistakeIds = array(0);
        
        foreach ($data as $key => $item) {
            if ($item["select"]) {
                $mistakeIds[] = $key;
            }
        }
        
        // nastaveni vsech oznacenych neshod jako odstranenych
        $tableMistakes = new Audit_Model_AuditsRecordsMistakes();
        
        $where = array(
            "client_id = ?" => $clientId,
            "id in (?)" => $mistakeIds
        );
        
        $updateData = array(
            "removed_at" => new Zend_Db_Expr("NOW()"),
            "is_removed" => 1
        );
        
        $tableMistakes->update($updateData,  $where);
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
		$date = Zend_Date::now()->get("yMMdd");
		if ($mistake->is_removed) $status = 0; elseif ($mistake->isMarked($date)) $status = 2;
		
		
		// smazani aktualni aktualizace
		$tableAssocs->delete(array("audit_id = " . $this->_audit->id, "record_id = " . $record->id));
		
		// zapis nove asociace
		$sql = "insert into " . $tableAssocs->info("name") . " (audit_id, mistake_id, record_id, status) values (" . $this->_audit->id . ","
					. $data["id"] . ","
					. $record->id . ","
					. $status . ") on duplicate key update record_id = values(record_id), status = values(status)";
		
		$tableMistakes->getAdapter()->query($sql);
		
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
	
	/**
	 * nacte neshodu podle id
	 * 
	 * @param int $mistakeId
	 * @return Audit_Model_Row_AuditRecordMistake
	 */
	public static function getMistake($mistakeId) {
		$tableMistakes = new Audit_Model_AuditsRecordsMistakes();
		$mistake = $tableMistakes->getById($mistakeId);
		
		if (!$mistake) throw new Zend_Db_Table_Exception("Mistake #$mistakeId not found");
			
		return $mistake;
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
		// kontrola prazdneho auditu
		if (!$mistake->audit_id) return new Audit_Model_Rowset_AuditsRecordsMistakes(array("data" => array()));
		
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
			if ($mistake->item_id) {
				$where[] = "item_id = " . $mistake->item_id;
			} else {
				$where[] = "item_id = 0";
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
		if ($this->_audit->auditor_id != $userId && $this->_user->getRoleId() != My_Role::ROLE_ADMIN) throw new Zend_Exception("This action is not allowed to yout");
		
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
		$date = trim($date);
		
		if (!$date) return 0;
		
		$exploded = explode(".", $date);
		
		if (strlen($exploded[0]) == 1) $exploded[0] = "0" . $exploded[0];
		if (strlen($exploded[1]) == 1) $exploded[1] = "0" . $exploded[1];
		
		return $exploded[2] . "-" . $exploded[1] . "-" . $exploded[0];
	}
	
	private function _preprareCreateForm($form) {
		// modifikace formulare - pridani vyberu pobocky
		$decorator = array(
				'ViewHelper',
				array('Errors'),
				array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element')),
				array('Label', array('tag' => 'td')),
				array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
		);
		
		$form->addElement("select", "subsidiary_id", array(
				"decorators" => $decorator,
				"label" => "Pobočka",
				"order" => 3
		));
		
		// naplneni pobocek
		$tableSubsidiaries = new Application_Model_DbTable_Subsidiary();
		$subsidiaries = $tableSubsidiaries->fetchAll(array("client_id = ?" => $this->_request->getParam("clientId", 0)), "subsidiary_name");
		$subList = array("0" => "--OBECNÁ NESHODA--");
		
		foreach ($subsidiaries as $item) $subList[$item->id_subsidiary] = $item->subsidiary_name . "(" . $item->subsidiary_town . ", " . $item->subsidiary_street . ")";
		
		$form->getElement("subsidiary_id")->setMultiOptions($subList);
		
		// odstraneni skryteho pracoviste a vlozeni noveho vyberu
		$form->removeElement("workplace_id");
		$form->addElement("select", "workplace_id", array(
				"decorators" => $decorator,
				"label" => "Pracoviště",
				"order" => 4,
				"disabled" => true,
				"multiOptions" => array("--OBECNÁ NESHODA--")
		));
		
		// kontrola nacteni workplaces
		$form->populate($this->_request->getParams());
		
		$subsidiaryId = $form->getValue("subsidiary_id");
		
		if ($subsidiaryId) {
			// nacteni dat
			$tableWorkplaces = new Application_Model_DbTable_Workplace();
			$workplaces = $tableWorkplaces->fetchAll(array("subsidiary_id = ?" => $subsidiaryId));
			$workList = array("0" => "--OBECNÁ NESHODA--");
			
			foreach ($workplaces as $workplace) $workList[$workplace["id_workplace"]] = $workplace["name"];
			
			$element = $form->getElement("workplace_id");
			$element->setMultiOptions($workList)->setAttrib("disabled", null);
			$form->populate($this->_request->getParams());
		}
		
		// pridani polozky "poprve zjistena"
		$form->addElement("text", "notified_at", array(
				"decorators" => $decorator,
				"label" => "Poprvé zjištěna",
				"order" => 5,
				"multiOptions" => array("--OBECNÁ NESHODA--"),
				"value" => Zend_Date::now()->get("dd. MM. y"),
				"required" => true
		));
	}
}
