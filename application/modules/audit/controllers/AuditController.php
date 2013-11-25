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
			
			$this->_request->setParam("subsidiaryId", $this->_audit->subsidiary_id);
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
		
		// nacteni seznamu zodpovednych osob
		$tableContacts = new Application_Model_DbTable_ContactPerson();
		$contacts = $diary->findDependentRowset($tableContacts, "Subsidiary", $tableContacts->select(false)->order("name"));
		
		$form->setContacts($contacts);
		
		$this->view->form = $form;
	}
	
	public function cloneAction() {
		// ziskani adapteru a zapocnuti transakce
		$adapter = Zend_Db_Table::getDefaultAdapter();
		$adapter->beginTransaction();
		
		// protoze je klonovani omezeno na povoleni, musi se toto povoleni zkontrolovat
		$tableCopyables = new Audit_Model_Copyables();
		if (!$tableCopyables->isCopyable($this->_user)) throw new Zend_Exception("You do not have permision to clone audits");
		
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
				"client_id" => $audit->client_id,
				"subsidiary_id" => $audit->subsidiary_id,
				"responsibile_name" => $audit->responsibile_name,
				"progress_note" => $audit->progress_note,
				"summary" => $audit->summary
		);
		
		$newAudit = $tableAudits->createRow($auditData);
		$newAudit->save();
		
		/*
		 * nasleduje kopirovani dat auditu
		 * 
		 * klonovat se musi:
		 * - neshody (zkopiruje se pouze asociace, nikoliv cela neshoda)
		 *     - neshody z formularu
		 *     - neshody z pracovist
		 *     - volne neshody
		 * - formulare
		 *     - otazky ktere se nezmenili se kopiruji normalne
		 *     - otazky ktere se zmenili se kopiruje jejich nova verze
		 *     - otazky ktere byly smazany se nekopiruji
		 *     - otazky ktere byly pridany se nakopiruji prazdne
		 * - komentare pracovist
		 */
		
		// prekopirovani formularu
		$tableAuditForms = new Audit_Model_AuditsForms();
		$tableForms = new Audit_Model_Forms();
		
		// nacteni id formularu, ktere byly v puvodnim auditu a jejich serazeni
		$oldForms = $audit->getForms();
		$oldFormIds = array(0);
		
		foreach ($oldForms as $form) $oldFormIds[] = $form->form_id;
		sort($oldFormIds);
		
		// prekopirovani starych formularu
		$nameAuditsForms = $tableAuditForms->info("name");
		$nameForms = $tableForms->info("name");
		
		$sql = "insert into `$nameAuditsForms` (form_id, audit_id, name) select id, $newAudit->id, name from `$nameForms` where " . $adapter->quoteInto("id in (?)", $oldFormIds) . " order by id";
		$adapter->query($sql);
		
		// nacteni id formularu, ktere se skutecne prekopirovaly a serazeni podle id
		$newFormIds = array();
		$newForms = $newAudit->getForms();
		
		foreach ($newForms as $form) $newFormIndex[] = $form;
		
		// nakopirovani instanci otazek, ktere jsou ve starem auditu (zatim bez aktualizaci)
		$tableQuestions = new Audit_Model_FormsCategoriesQuestions();
		$tableRecords = new Audit_Model_AuditsRecords();
		$tableCategories = new Audit_Model_FormsCategories();
		
		$nameQuestions = $tableQuestions->info("name");
		$nameRecords = $tableRecords->info("name");
		$nameCategories = $tableCategories->info("name");
		
		// kopirovani musi probihat postupne, kvuli ruznym audit_form_id
		foreach ($newFormIndex as $form) {
			$select = new Zend_Db_Select($adapter);
			
			// select z tabulky otazek
			$select->from($nameQuestions, array(new Zend_Db_Expr($newAudit->id), new Zend_Db_Expr($form->id), "$nameQuestions.id"))
						->where("!$nameQuestions.is_deleted");
			
			// spojeni s tabulkou kategorii
			$select->joinInner($nameCategories, "group_id = $nameCategories.id", array());
			
			// spojeni s tabulkou formularu
			$select->joinInner($nameForms, "$nameForms.id = form_id", array())->where("$nameForms.id = ?", $form->form_id);
			
			$sql = "insert into $nameRecords (audit_id, audit_form_id, question_id) " . $select->assemble();
			$adapter->query($sql);
		}
		
		// nastaveni poznamek a identifikatoru neshod u prvku, ktere se nezmenily
		$sql = "update $nameRecords as r1, $nameRecords as r2 set r1.note = r2.note, r1.mistake_id = r2.mistake_id, r1.score = r2.score where r1.audit_id = $newAudit->id and r2.audit_id = $audit->id and r1.question_id = r2.question_id";
		$adapter->query($sql);
		
		// nastaveni poznamek a identifikatoru neshod u prvku, ktere se zmenili
		$sql = "update $nameRecords as r1, $nameRecords as r2, $nameQuestions as q set r1.note = r2.note, r1.mistake_id = r2.mistake_id, r1.score = r2.score where r1.audit_id = $newAudit->id and r2.audit_id = $audit->id and r1.question_id = q.new_id and q.id = r2.question_id";
		$adapter->query($sql);
		
		// zapis neshod pro zaznamy, ktere neshodu nemaji
		$select = new Zend_Db_Select($adapter);
		$select->from($nameQuestions, array(
				new Zend_Db_Expr($newAudit->id),
				new Zend_Db_Expr($newAudit->client_id),
				new Zend_Db_Expr($newAudit->subsidiary_id),
				"weight",
				"question",
				"category",
				"subcategory",
				"concretisation",
				"mistake",
				"suggestion",
				new Zend_Db_Expr("''"),			// comment
				new Zend_Db_Expr("NOW()"),		// notified_at
				new Zend_Db_Expr("NOW()"),		// will_be_removed_at
				new Zend_Db_Expr("''")			// responsible name
		));
		
		// sjednoceni s tabulkou zaznamu
		$tableMistakes = new Audit_Model_AuditsRecordsMistakes();
		$nameMistakes = $tableMistakes->info("name");
		$select->joinInner($nameRecords, "question_id = $nameQuestions.id", array("$nameRecords.id"));
		
		// omezeni na audit a na zaznamy, ktere nemaji pridelenou neshodu a provedeni operace
		$select->where("audit_id = ?", $newAudit->id)->where("mistake_id is null");
		$sql = "insert into $nameMistakes (audit_id, client_id, subsidiary_id, weight, question, category, subcategory, concretisation, mistake, suggestion, comment, notified_at, will_be_removed_at, responsibile_name, record_id) " . $select->assemble();
		
		$adapter->query($sql);
		
		// krizove navazani id neshod na zaznamy
		$sql = "update $nameRecords, $nameMistakes set mistake_id = $nameMistakes.id where $nameRecords.id = $nameMistakes.record_id and $nameMistakes.audit_id = $newAudit->id and $nameRecords.audit_id = $newAudit->id and mistake_id is null";
		$adapter->query($sql);
		
		// prekopirovani komentaru pracovist
		$select = new Zend_Db_Select($adapter);
		$tableComments = new Audit_Model_AuditsWorkcomments();
		$nameComments = $tableComments->info("name");
		
		$select->from($nameComments, array(new Zend_Db_Expr($newAudit->id), "workplace_id", "comment"))
				->where("audit_id = ?", $audit->id);
		
		$sql = "insert into $nameComments (audit_id, workplace_id, comment) " . $select->assemble();
		$adapter->query($sql);
		
		// prekopirovani asociaci neshod
		$tableAssocs = new Audit_Model_AuditsMistakes();
		$nameAssocs = $tableAssocs->info("name");
		
		// neshody, ktere nejsou vazany na formular
		$select = new Zend_Db_Select($adapter);
		$select->from($nameAssocs, array(new Zend_Db_Expr($newAudit->id), "mistake_id", "status"));
		$select->where("audit_id = ?", $audit->id)->where("status <> 2")->where("record_id is null");
		
		$sql = "insert into $nameAssocs (audit_id, mistake_id, status) " . $select->assemble();
		$adapter->query($sql);
		
		// neshody, ktere jsou vazany na formular
		$select = new Zend_Db_Select($adapter);
		$select->from($nameAssocs, array(new Zend_Db_Expr($newAudit->id), "mistake_id", "status"))
				->joinInner($nameRecords, "$nameRecords.mistake_id = $nameAssocs.mistake_id", array("id"));
		
		// podminka stareho auditu v Assocs a noveho auditu v Records
		$select->where("$nameAssocs.audit_id = ?", $audit->id)->where("$nameRecords.audit_id = ?", $newAudit->id);
		
		// podminka nenullovosti neshod
		$select->where("$nameAssocs.mistake_id is not null")->where("$nameRecords.mistake_id is not null");
		
		// odeslani dotazu
		$sql = "insert into $nameAssocs (audit_id, mistake_id, status, record_id) " . $select->assemble();
		$adapter->query($sql);
		
		// potvrzeni transkace
		$adapter->commit();
		
		$this->view->audit = $newAudit;
	}
	
	public function deadlistHtmlAction() {
		// nacteni dohlidky
		$audit = $this->_audit;
	
		// selekce lhut, ktere jeste nejsou v dohlidce
		$tableAssocs = new Audit_Model_AuditsDeadlines();
		$subSelect = new Zend_Db_Select(Zend_Db_Table_Abstract::getDefaultAdapter());
		$subSelect->from($tableAssocs->info("name"), "deadline_id")->where("audit_id = ?", $audit->id);
	
		// nacteni lhut
		$tableDeadlines = new Deadline_Model_Deadlines();
		$select = $tableDeadlines->_prepareSelect();
		$select->where("subsidiary_id = ?", $audit->subsidiary_id)->where("id not in (?)", new Zend_Db_Expr($subSelect));
	
		$data = $select->query()->fetchAll();
	
		$this->view->deadlines = $data;
		$this->view->audit = $audit;
	}
	
	public function editAction() {
		$this->view->layout()->setLayout("client-layout");
		
		// kontrola dat
		if (!$this->_audit) throw new Zend_Exception("Audit not found");
		
		// kotrnola pristupu
		$zeroDate = "0000-00-00 00:00:00";
		
		// kontrola pristupu
		$userId = $this->_user->getIdUser();
		if (($this->_audit->auditor_confirmed_at[0] != '0' && $this->_audit->auditor_id == $userId 
				|| $this->_audit->auditor_id != $userId) && $this->_user->getRole() != My_Role::ROLE_ADMIN) throw new Zend_Exception("Audit #" . $this->_audit->id . " was closed for this action");
		
		// vytvoreni formulare
		$form = new Audit_Form_AuditFill();
		
		// nastaveni kontaktnich osob
		$tableContacts = new Application_Model_DbTable_ContactPerson();
		$contacts = $tableContacts->fetchAll(array("subsidiary_id = ?" => $this->_audit->subsidiary_id), "name");
		$form->setContacts($contacts);
		
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
		
		// vyhodnoceni zda se jedna o audit nebo proverku
		if ($this->_audit->is_check) {
			// jedna se o proverku - zobrazi se farplany
			
			// nacteni existujicich farplanu
			$formInstances = $this->_audit->getFarplans();
			
			// nactei formularu, ktere jeste mohou byt vyplneny
			$instanceForm = new Audit_Form_FormInstanceCreate();
			$instanceForm->loadUnused($formInstances);
			
			// vytvoreni odkazu pro novy formular
			$url = sprintf("/audit/farplan/clone?auditId=%s", $this->_audit->id);
				
			$instanceForm->setAction($url);
		} else {
			// jedna se o audit - zobrazi se formulare
			
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
		}
		
		// nacteni neshod tykajicich se auditu
		$auditMistakes = $this->_audit->getMistakes();
		$mistakeAssocs = $this->_audit->getMistakeAssocs();
		
		$mistakeAssocsIndex = array();
		
		foreach ($mistakeAssocs as $assoc) {
			$mistakeAssocsIndex[$assoc->mistake_id] = $assoc;
		}
		
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
		
		$this->_initWorkForms();
		
		// nastaveni formulare pro kontaktni osobou
		$contactForm = new Audit_Form_ContactPerson();
		$contactForm->populate($this->_audit->toArray());
		$contactForm->setAction(sprintf("/audit/audit/newcontact?auditId=%s", $this->_audit->id));
		
		// nacteni dat o lhutach z databaze
		$tableDeadlines = new Audit_Model_AuditsDeadlines();
		$deadlines = $tableDeadlines->findExtendedByAudit($this->_audit);
		
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
		$this->view->commentForms = $commentForms;
		$this->view->userId = $userId;
		$this->view->contactForm = $contactForm;
		$this->view->mistakeAssocIndex = $mistakeAssocsIndex;
		$this->view->deadlines = $deadlines;
	}
	
	public function getAction() {
		// kontrola auditu
		if (!$this->_audit) throw new Zend_Exception("Audit #" . $this->_auditId . " has not been found");
		
		$audit = $this->_audit;
		$auditId = $audit->id;
		
		// nacteni doprovodnych informaci
		$auditor = $audit->getAuditor();
		$client = $audit->getClient();
		$subsidiary = $audit->getSubsidiary();
		
		// nacteni formularu
		if ($audit->is_check) {
			$forms = $audit->getFarplans();
		} else {
			$forms = $audit->getForms();
		}
		
		$this->view->layout()->setLayout("client-layout");
		
		// nacteni neshod z dotazniku
		$tableMistakes = new Audit_Model_AuditsRecordsMistakes();
		$tableAssocs = new Audit_Model_AuditsMistakes();
		
		// sestaveni podminek
		$nameAssocs = $tableAssocs->info("name");
		$mistakes = $tableMistakes->fetchAll(array("is_submited", "id in (select mistake_id from $nameAssocs where audit_id = $audit->id and status != 2)"));
		
		// nacteni pracovist
		$tableWorkplaces = new Application_Model_DbTable_Workplace();
		$workplaces = $tableWorkplaces->fetchAll("client_id = " . $this->_audit->client_id, "name");
		
		// indexace pracovist dle ud
		$workplaceIndex = array();
		
		foreach ($workplaces as $item) {
			$workplaceIndex[$item->id_workplace] = $item;
		}
		
		// nacteni lhut
		$tableDeadlines = new Audit_Model_AuditsDeadlines();
		$deadlines = $tableDeadlines->findExtendedByAudit($audit);
		
		$this->view->audit = $this->_audit;
		$this->view->subsidiary = $subsidiary;
		$this->view->client = $client;
		$this->view->auditor = $auditor;
		$this->view->forms = $forms;
		$this->view->deadlines = $deadlines;
		
		if ($this->_audit->contactperson_id) {
			$tableContacts = new Application_Model_DbTable_ContactPerson();
			
			$this->view->contact = $tableContacts->find($this->_audit->contactperson_id)->current();
		}
		
		// zapis dat neshod
		$this->view->workplaceIndex = $workplaceIndex;
		$this->view->mistakes = $mistakes;
	}
	
	public function getdeadHtmlAction() {
		// nacteni dohlidky
		$audit = $this->_audit;
	
		// nacteni asociace
		$tableAssocs = new Audit_Model_AuditsDeadlines();
		$deadlineId = $this->_request->getParam("deadlineId");
		$assoc = $tableAssocs->findByAuditDeadline($audit->id, $deadlineId);
	
		if (!$assoc) throw new Zend_Db_Table_Exception("Combination of deadline and audit not found");
	
		// nacteni lhuty
		$tableDeadlines = new Deadline_Model_Deadlines();
		$deadline = $tableDeadlines->findById($deadlineId, true);
	
		// formular pro splneni lhuty
		if ($audit->is_closed) {
			$formDone = null;
		} else {
			$formDone = new Audit_Form_Deadline();
			$url = sprintf("/audit/audit/subdead.html?deadlineId=%s&auditId=%s&clientId=%s", $deadline->id, $audit->id, $audit->client_id);
			$formDone->populate($assoc->toArray());
			$formDone->setAction($url);
		}
	
		$this->view->audit = $audit;
		$this->view->deadline = $deadline;
		$this->view->assoc = $assoc;
	
		$this->view->formDone = $formDone;
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
		
		$subsidiaryId = $this->_request->getParam("subsidiaryId", null);
		
		$tableAudits = new Audit_Model_Audits();
		$role = Zend_Auth::getInstance()->getIdentity()->role;
		$audits = $tableAudits->findAudits($clientId, $subsidiaryId, $role == My_Role::ROLE_CLIENT);
		
		$this->view->audits = $audits;
	}
	
	public function newcontactAction() {
		// kontrola dat
		if (!$this->_audit) throw new Zend_Db_Exception("Audit not found");
		
		$this->_request->setParam("clientId", $this->_audit->client_id)->setParam("subsidiaryId", $this->_audit->subsidiary_id);
		
		$form = new Audit_Form_ContactPerson();
		
		if (!$form->isValid($this->_request->getParams())) {
			$this->_forward("edit");
			return;
		}
		
		// nastaveni dat a ulozeni
		$audit = $this->_audit;
		
		$audit->contactperson_id = null;
		
		$audit->setFromArray($form->getValues(true));
		$audit->save();
		
		$this->_helper->FlashMessenger("Kontaktní osoba byla vytvořena");
		
		$this->view->audit = $audit;
	}
	
	public function postAction() {
		// nacteni dat
		$data = $this->getRequest()->getParam("audit", array());
		
		// nacteni a validace formulare
		$form = new Audit_Form_Audit();
		
		$tableContacts = new Application_Model_DbTable_ContactPerson();
		$contacts = $tableContacts->fetchAll(array("subsidiary_id = ?" => $this->_request->getParam("subsidiaryId", null)), "name");
		$form->setContacts($contacts);
		
		if (!$form->isValid($data)) {
			$this->_forward("create");
			return;
		}
		
		// nacteni dat
		$tableUser = new Application_Model_DbTable_User();
		$tableSubsidiaries = new Application_Model_DbTable_Subsidiary();
		$tableAudits = new Audit_Model_Audits();
		
		$subsidiary = $tableSubsidiaries->find($form->getValue("subsidiary_id"))->current();
		$auditor = $tableUser->find($this->_user->getIdUser())->current();
		
		// datum provedeni
		$doneAt = new Zend_Date($form->getValue("done_at"), "dd. MM. y");
		
		// vytvoreni zaznamu
		$contactId = $form->getValue("contactperson_id");
		
		if (!$contactId) $contactId = null;
		
		$audit = $tableAudits->createAudit($auditor, $subsidiary, $doneAt, $form->getValue("is_check"), $contactId);
		
		// prirazeni existujicich neshod
		$tableAssocs = new Audit_Model_AuditsMistakes();
		$tableMistakes = new Audit_Model_AuditsRecordsMistakes();
		
		$nameAssocs = $tableAssocs->info("name");
		$nameMistakes = $tableMistakes->info("name");
		
		$sql = "insert into $nameAssocs (audit_id, mistake_id, record_id, is_submited, status) select $audit->id, id, null, 0, 0 from $nameMistakes where subsidiary_id = $audit->subsidiary_id and !is_removed and is_submited";
		$tableAssocs->getAdapter()->query($sql);
		
		// navazeni propadlych lhut k auditu
		$tableDeadlines = new Audit_Model_AuditsDeadlines();
		$tableDeadlines->createByAudit($audit);
		
		$this->_helper->FlashMessenger("Audit vytvořen");
		
		$this->_redirect(
				$this->view->url(array("clientId" => $subsidiary->client_id, "auditId" => $audit->id, "subsidiaryId" => $audit->subsidiary_id), "audit-edit")
		);
	}
	
	public function putAction() {
		// nacteni dat
		$data = $this->getRequest()->getParam("audit");
		
		$form = new Audit_Form_AuditFill();
		$form->getElement("summary")->setRequired(false);
		
		$tableContacts = new Application_Model_DbTable_ContactPerson();
		$contacts = $tableContacts->fetchAll(array("subsidiary_id = ?" => $this->_request->getParam("subsidiaryId", null)), "name");
		$form->setContacts($contacts);
		
		// kontrola validity
		$form->populate($data);
		if (!$form->isValidPartial($data)) $this->_forward("fill");
		
		// nacteni auditu
		$tableAudits = new Audit_Model_Audits();
		$audit = $tableAudits->getById($form->getValue("id"));
		
		// datum je ve spatnem formatu - musi se prepsat na SQL standard
		list($day, $month, $year) = explode(". ", $form->getValue("done_at"));
		$form->getElement("done_at")->setValue("$year-$month-$day");
		
		// pokud je id kontaktni osoby nulove, nahrani se hodnotou null
		$data = $form->getValues(true);
		
		if ($data["contactperson_id"]) {
			$data["contact_name"] = null;
			$data["contact_email"] = null;
			$data["contact_phone"] = null;
		} else {
			$data["contactperson_id"] = null;
		}
		
		// zapis poznamek a shrnuti
		$audit->setFromArray($data);
		
		// nastaveni ostanich dat
		
		$audit->save();
		
		// presmerovani na fill nebo review, dle role
		$url = $this->view->url(array("clientId" => $audit->client_id, "auditId" => $audit->id, "subsidiaryId" => $audit->subsidiary_id), "audit-edit");
		
		$this->_helper->FlashMessenger("Změny byly uloženy");
		
		$this->_redirect($url);
	}
	
	public function subdeadAction() {
		// nacteni dat
		$watchId = $this->_request->getParam("auditId", 0);
		$tableAudits = new Audit_Model_Audits();
		$audit = $tableAudits->find($watchId)->current();
		
		if (!$audit) throw new Zend_Db_Table_Exception("audit not found");
	
		$this->_request->setParam("clientId", $audit->client_id);
	
		// nacteni zaskrtnutych policek
		$selected = (array) $this->_request->getParam("selected", array());
		$selectedIds = array_merge(array(0), $selected);
	
		// nacteni dat o splneni
		$data = $this->_request->getParam("deadline", array());
		$data = array_merge(array("done_at" => "", "comment" => ""), $data);
	
		// vytvoreni updatovacich a filtracnich poli
		$where = array(
				"deadline_id in (?)" => $selectedIds,
				"audit_id = ?" => $audit->id
		);
		
		$updateData = array(
				"note" => $data["comment"],
				"done_at" => $data["done_at"],
				"is_done" => 1
		);
		
		$this->_helper->FlashMessenger("Lhůty splněny");
	
		$tableAssocs = new Audit_Model_AuditsDeadlines();
		$tableAssocs->update($updateData, $where);
	}
	
	public function subdeadHtmlAction() {
		// nacteni dat z formulare
		$form = new Audit_Form_Deadline();
	
		$form->populate($this->_request->getParams());
	
		if (!$form->isValid($this->_request->getParams())) {
			$this->_forward("getdead.html");
			return;
		}
	
		// nacteni dat z databaze
		$audit = $this->_audit;
		$tableAssocs = new Audit_Model_AuditsDeadlines();
		$assoc = $tableAssocs->findByAuditDeadline($audit->id, $this->_request->getParam("deadlineId"));
	
		if (!$assoc) throw new Zend_Db_Table_Exception("Association not found");
	
		// nastaveni dat
		$assoc->setFromArray($form->getValues(true));
		$assoc->save();
		
		$this->_helper->FlashMessenger("Lhůta splněna");
	
		$this->view->assoc = $assoc;
		$this->view->audit = $audit;
	}
	
	public function submitAction() {
		// kontrola dat
		$form = new Audit_Form_AuditAuditorSubmit();
		
		$tableContacts = new Application_Model_DbTable_ContactPerson();
		$contacts = $tableContacts->fetchAll(array("subsidiary_id = ?" => $this->_audit->subsidiary_id), "name");
		
		if (!$form->isValid($this->getRequest()->getParams())) {
			// neni zaskrtnuto potvrzovaci policko
			$this->_forward("edit");
			
			return;
		}
		
		// kontrola opravneni pristupu
		$userId = $this->_user->getIdUser();
		$roleId = $this->_user->getRoleId();
		$audit = $this->_audit;
		
		// kontrola pristupnosti vzhledem k rolim
		if ($roleId != My_Role::ROLE_ADMIN && $roleId != My_Role::ROLE_SUPERADMIN) {
			// uzivatel neni administrator - musime zkontrolovat pristup k akcim
			
			if ($audit->auditor_confirmed_at == "0000-00-00 00:00:00" && ($roleId != My_Role::ROLE_TECHNICIAN && $audit->auditor_id != $userId)) {
				throw new Zend_Exception("Invalid user or audit status - unsubmited by technic try submit non technic user");
			}
		}
		
		// provedeni akci dle typu uzivatele, ktery odeslal audit
		if ($audit->auditor_confirmed_at == "0000-00-00 00:00:00") {
			// odeslal to technik - jen se to podepise jinak se nic nedeje
			$this->_audit->auditor_confirmed_at = new Zend_Db_Expr("NOW()");
			
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
			$sql1 = "$begin is_removed = 1 where `$nameAssocs`.`status` = 2 and id = mistake_id";
			$tableMistakes->getAdapter()->query("$sql1");
				
			// odeslani neshod, ktere se maji odeslat
			$sql = "update `$nameMistakes` set is_submited = 1 where id in (select mistake_id from `$nameAssocs` where audit_id = $auditId)";
			$tableMistakes->getAdapter()->query($sql);
			
			// smazani neshod z formularu, ktere se nakonec nepouzily
			$tableMistakes->delete(array(
					"audit_id = ?" => $audit->id,
					"id not in (?)" => new Zend_Db_Expr("select mistake_id from $nameAssocs where audit_id = " . $audit->id)
					));
				
			// nastaveni odeslani neshod v asociacni tabulce auditu
			$tableAssocs->update(array("is_submited" => 1), array("audit_id = ?" => $auditId));
				
			// potvrdi se audit
			$this->_audit->is_closed = 1;
			$this->_audit->save();
			
			// oznaceni lhut, ktere byly vybrany jako splnenych
			$tableDeadlinesAssocs = new Audit_Model_AuditsDeadlines();
			$tableDeadlines = new Deadline_Model_Deadlines();
			
			$nameDeadlinesAssocs = $tableDeadlinesAssocs->info("name");
			$nameDeadlines = $tableDeadlines->info("name");
			
			// sestaveni updatovaciho dotazu
			$sql = sprintf("update %s as deads, %s as assocs set deads.last_done = assocs.done_at, deads.next_date = ADDDATE(assocs.done_at, INTERVAL deads.period MONTH) WHERE deads.id = assocs.deadline_id and is_done and audit_id = %s",
					$nameDeadlines, $nameDeadlinesAssocs, $audit->id);
			
			Zend_Db_Table_Abstract::getDefaultAdapter()->query($sql);
			
			// zapis do logu
			$tableLogs = new Deadline_Model_Logs();
			$nameLogs = $tableLogs->info("name");
			
			$sql = sprintf("insert into %s (deadline_id, done_at, note) select deadline_id, done_at, note from %s where is_done and audit_id = %s",
					$nameLogs, $nameDeadlinesAssocs, $audit->id);
			
			Zend_Db_Table_Abstract::getDefaultAdapter()->query($sql);
			
			// odstraneni farplanu, ktere nejsou zaskrtle
			$this->_helper->FlashMessenger("Audit uzavřen");
			
			// presmerovani na get
			$url = $this->view->url(array("auditId" => $this->_audit->id, "clientId" => $this->_audit->client_id, "subsidiaryId" => $audit->subsidiary_id), "audit-get");
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
		$placeForm->setAction($this->view->url(array("auditId" => $this->_audit->id, "clientId" => $this->_audit->client_id, "subsidiaryId" => $this->_audit->subsidiary_id), "audit-workplace-post"));
		
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