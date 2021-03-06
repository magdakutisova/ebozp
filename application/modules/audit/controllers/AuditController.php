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
    
    protected $_progresCheck = array(
			"Kontrola funkčnosti systému BOZP a PO.",
			"Prověření přetrvávajících neshod.",
			"Fyzická kontrola všech pracovišť.",
			"Doplnění a úprava registru neshod.",
			"Návrh konkrétních opatření k odstranění BOZP a PO."
	);
    
    protected $_progresAudit = array(
            "Kontrola systému BOZP a PO - dokumentace, záznamy, výcvik a školení, technická bezpečnost, atd.",
			"Fyzická kontrola pracovišť.",
			"Zpracování registru neshod.",
			"Návrh konkrétních opatření k dosažení požadované úrovně BOZP a PO."
    );
    
    protected $_targetCheck = "Zhodnocení současného stavu bezpečnosti práce  (BOZP) a požární ochrany (PO) v organizaci a podle Zákona 262/2006 Sb. Zákoník práce, § 108, odst. 5.";
    
    protected $_targetAudit = "Zhodnocení současného stavu bezpečnosti práce  (BOZP) a požární ochrany (PO) v organizaci a jeho shody  s požadavky právních a dalších předpisů České republiky. 
Audit byl proveden podle ISO 19011 auditory G U A R D 7, v.o.s.";
	
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
		
        // kontrola, jeslti uzivatel uz vytvoril na pobocce audit
        $tableAudits = new Audit_Model_Audits();
        $audit = $tableAudits->fetchRow(
                array(
                    "subsidiary_id = " . $subsidiaryId, 
                    "auditor_id = " . $this->_user->getIdUser()), 
                "done_at desc");

        $form->enableCloning();

        $form->getElement("copy_old")->setValue(1);
		
		// nacteni seznamu zodpovednych osob
		$tableContacts = new Application_Model_DbTable_ContactPerson();
		$contacts = $diary->findDependentRowset($tableContacts, "Subsidiary", $tableContacts->select(false)->where("!is_deleted")->order("name"));
		
		$form->setContacts($contacts);
		
		$this->view->form = $form;
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
		$select->where("d.subsidiary_id = ?", $audit->subsidiary_id)->where("id not in (?)", new Zend_Db_Expr($subSelect));
		
		$data = $select->query()->fetchAll();
		
		$this->view->deadlines = $data;
		$this->view->audit = $audit;
	}
	
    public function deleteAction() {
        // kontrola auditu
        if (is_null($this->_audit)) {
            throw new Zend_Db_Table_Row_Exception("Audit was not set");
        }
        
        // kontrola opravneni audit smazat
        $audit = $this->_audit;
        
        if ($audit->is_closed) {
            throw new Zend_Db_Table_Row_Exception("Closed audit is forbidden for this action");
        }
        
        $user = Zend_Auth::getInstance()->getIdentity();
        
        if ($audit->auditor_id != $user->id_user && !in_array($user->role, array(My_Role::ROLE_COORDINATOR, My_Role::ROLE_ADMIN, My_Role::ROLE_SUPERADMIN))) {
            throw new Zend_Acl_Exception("You have not permision to delete this audit");
        }
        
        // smazani pridruzenych radku
        $tableRecords = new Audit_Model_AuditsRecords();
        $tableMistakes = new Audit_Model_AuditsRecordsMistakes();
        $tableForms = new Audit_Model_AuditsForms();
        
        $where = array("audit_id = ?" => $audit->id);
        $tableMistakes->delete($where);
        $tableRecords->delete($where);
        $tableForms->delete($where);
        
        if (!is_null($audit->report_id)) {
            $tableReports = new Audit_Model_AuditsReports();
            $tableReports->delete(array("id = ?" => $audit->report_id));
        }
        
        $this->view->clientId = $audit->client_id;
        $this->view->subsidiaryId = $audit->subsidiary_id;
        
        $audit->delete();
        $this->_helper->FlashMessenger("Audit byl smazán");
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
		$contacts = $tableContacts->fetchAll(array("subsidiary_id = ?" => $this->_audit->subsidiary_id, "!is_deleted"), "name");
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
		$deadlines = $tableDeadlines->findExtendedByAudit($this->_audit, true, false);
        
        // nactnei polozek prubehu
        $progres = $this->_audit->getProgres();
		
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
        $this->view->progres = $progres;
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
		
		$forms = $audit->getForms();
		
		$this->view->layout()->setLayout("client-layout");
		
		// nacteni neshod z dotazniku
		$tableMistakes = new Audit_Model_AuditsRecordsMistakes();
		$tableAssocs = new Audit_Model_AuditsMistakes();
		
		// sestaveni podminek
        $mistakes = $audit->getMistakes(true, true);
		
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
		$form->enableCloning();
        
		$tableContacts = new Application_Model_DbTable_ContactPerson();
		$contacts = $tableContacts->fetchAll(array("subsidiary_id = ?" => $this->_request->getParam("subsidiaryId", null)), "name");
		$form->setContacts($contacts);
		
		if (!$form->isValid($data)) {
			$this->_forward("create");
			return;
		}
		
        // zacatek transakce
        try {
            $adapter = Zend_Db_Table_Abstract::getDefaultAdapter();
            $adapter->beginTransaction();
            
            // kontrola klonovani
            $oldAudit = null;
            $tableAudits = new Audit_Model_Audits();
            
            if ($form->getValue("copy_old")) {
                $oldAudit = $tableAudits->fetchRow(array(
                    "subsidiary_id = ?" => $form->getValue("subsidiary_id"),
                    "is_closed"
                ), "auditor_confirmed_at desc");
            }
        
            // nacteni dat
            $tableUser = new Application_Model_DbTable_User();
            $tableSubsidiaries = new Application_Model_DbTable_Subsidiary();

            $subsidiary = $tableSubsidiaries->find($form->getValue("subsidiary_id"))->current();
            $auditor = $tableUser->find($this->_user->getIdUser())->current();

            // datum provedeni
            $doneAt = new Zend_Date($form->getValue("done_at"), "dd. MM. y");

            // vytvoreni zaznamu
            $contactId = $form->getValue("contactperson_id");

            if (!$contactId) $contactId = null;

            $audit = $tableAudits->createAudit($auditor, $subsidiary, $doneAt, $form->getValue("is_check"), $contactId);

            if ($audit->is_check) {
                $items = $this->_progresCheck;
                $audit->progress_note = $this->_targetCheck;
            } else {
                $items = $this->_progresAudit;
                $audit->progress_note = $this->_targetAudit;
            }

            $audit->save();

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

            // zapis zaznamu prubehu
            $tableProgres = new Audit_Model_AuditsProgresitems();

            foreach ($items as $item) {
                $tableProgres->insert(array(
                    "audit_id" => $audit->id,
                    "content" => $item
                ));
            }
            
            // naklonovani dat, pokud je treba
            if ($oldAudit) {
                $this->_copyForms($oldAudit, $audit);
            }
        
        } catch(Exception $e) {
            // stornovani transakce a propagace vyjimky
            $adapter->rollBack();
            throw $e;
        }
        
        // potvrzeni transkace
        $adapter->commit();
		
		$this->_helper->FlashMessenger("Audit vytvořen");
		
		$this->_redirect(
				$this->view->url(array("clientId" => $subsidiary->client_id, "auditId" => $audit->id, "subsidiaryId" => $audit->subsidiary_id), "audit-edit")
		);
	}
	
    public function progresAction() {
        // nacteni dat
        $data = (array) $this->_request->getParam("item", array());
        $tableProgres = new Audit_Model_AuditsProgresitems();
        
        // smazani starych dat
        $tableProgres->delete(array("audit_id = ?" => $this->_audit->id));
        
        // zapis novych prvku
        foreach ($data as $item) {
            $tableProgres->insert(array(
                "audit_id" => $this->_audit->id,
                "content" => $item
            ));
        }
        
        $this->view->audit = $this->_audit;
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
			$audit->auditor_confirmed_at = new Zend_Db_Expr("NOW()");
			
			// odstrani se neshody, ktere nakonec nebyly pouzity
			$tableMistakes = new Audit_Model_AuditsRecordsMistakes();
			$tableAssocs = new Audit_Model_AuditsMistakes();
			$nameMistakes = $tableMistakes->info("name");
			$nameAssocs = $tableAssocs->info("name");
			$auditId = $audit->id;
				
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

			// nahrazeni kontaktni osoby
			if ($audit->contactperson_id) {
				$tableContacts = new Application_Model_DbTable_ContactPerson();

				$contact = $tableContacts->find($audit->contactperson_id)->current();
				$audit->contactperson_id = null;
				$audit->contact_name = $contact->name;
				$audit->contact_phone = $contact->phone;
				$audit->contact_email = $contact->email;
			}
				
			// potvrdi se audit
			$audit->is_closed = 1;
			$audit->save();
			
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
			
			$sql = sprintf("insert into %s (deadline_id, done_at, note, user_id) select deadline_id, done_at, note, %s from %s where is_done and audit_id = %s",
					$nameLogs, $userId, $nameDeadlinesAssocs, $audit->id);
			
			Zend_Db_Table_Abstract::getDefaultAdapter()->query($sql);
			
			// odstraneni farplanu, ktere nejsou zaskrtle
			$this->_helper->FlashMessenger("Audit uzavřen");
			
			// presmerovani na get a zapis do denniku
			$url = $this->view->url(array("auditId" => $audit->id, "clientId" => $audit->client_id, "subsidiaryId" => $audit->subsidiary_id), "audit-get");
            
            if ($audit->is_check) {
                $label = "provedl roční prověrku BOZP a PO";
                $link = "zpráva o prověrce";
            } else {
                $label = "provedl audit BOZP a PO";
                $link = "zpráva o auditu";
            }
            
            $this->_helper->diaryRecord->insertMessage($label, null, null, sprintf("<a href='%s'>%s</a>", $url, $link), $audit->subsidiary_id);
            $this->_helper->diaryRecord->save();
            
			$this->_helper->redirector->gotoUrlAndExit($url);
			
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
    
    /**
     * zkopiruje formulare stareho auditu do noveho
     * 
     * @param Audit_Model_Row_Audit $old
     * @param Audit_Model_Row_Audit $new
     */
    protected function _copyForms(Audit_Model_Row_Audit $old, Audit_Model_Row_Audit $new) {
        // nacteni starych formularu
        $forms = $old->getForms();
        $formIds = array();
        
        foreach ($forms as $form) {
            $formIds[] = $form->form_id;
        }
        
        if (!$formIds) return;
        
        // nacteni definicnich radku
        $tableForms = new Audit_Model_Forms();
        $defForms = $tableForms->find($formIds);
        
        // vytvoreni instanci
        $tableInsts = new Audit_Model_AuditsForms();
        
        foreach ($defForms as $form) {
            $tableInsts->createForm($new, $form, false);
        }
        
        // nacteni id neshod z minuleho auditu a rozrazeni na odstranene a neodstranene
        $tableMistakes = new Audit_Model_AuditsRecordsMistakes();
        $mistakes = $tableMistakes->fetchAll(array(
            "audit_id = ?" => $old->id,
            "record_id is not null"
        ));
        
        $removed = array(0);
        $notRemoved = array(0);
        
        foreach ($mistakes as $mistake) {
            if ($mistake->is_removed){
                $removed[] = $mistake->id;
            } else {
                $notRemoved[] = $mistake->id;
            }
        }
        
        // priprava patterny pro dotaz
        $tableRecords = new Audit_Model_AuditsRecords();
        $nameRecords = $tableRecords->info("name");
        $adapter = $tableRecords->getAdapter();
        
        // vyhledavahucu podminka
        $where = " WHERE o.audit_id = " . $old->id . " and n.audit_id = " . $new->id . " and o.question_id = n.question_id and o.mistake_id in (%s)";
        $update = sprintf("UPDATE %s AS o, %s AS n SET ", $nameRecords, $nameRecords);
        
        // update odstranenych neshod
        $sqlBaseRem = sprintf("%s n.score = %%d %s", $update, $where);
        $sql = sprintf($sqlBaseRem, Audit_Model_AuditsRecords::SCORE_A, implode(", ", $removed));
        $adapter->query($sql);
        
        // update neodstranenych neshod
        $sqlBaseNot = sprintf("%s n.score = %%d, n.note = o.note, n.mistake_id = o.mistake_id %s", $update, $where);
        $sql = sprintf($sqlBaseNot, Audit_Model_AuditsRecords::SCORE_N, implode(", ", $notRemoved));
        $adapter->query($sql);
        
        // update dat, ktere byly oznaceny jako v poradku
        $sql = $update . "n.score = o.score, n.note = o.note WHERE o.audit_id = " . $old->id . " and n.audit_id = " . $new->id . " and o.question_id = n.question_id and o.score = " . Audit_Model_AuditsRecords::SCORE_A;
        $adapter->query($sql);
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
        
        // vedouci
        $formBoss = new Application_Form_ResponsibleEmployee();
    	$formBoss->clientId->setValue($this->_audit->client_id);
    	$formBoss->removeElement('save_responsible_employee');
    	$elementDecorator2 = array(
    			'ViewHelper',
    			array('Errors'),
    			array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element')),
    			array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
    	);
    	$formBoss->addElement('button', 'save_boss', array(
				'decorators' => $elementDecorator2,
				'label' => 'Uložit zaměstnance',
    			));
    	$formBoss->save_boss->setAttrib('class', array('boss', 'workplace', 'ajaxSave'));
    	$formBoss->setName('boss');
        
		$workForm = new Application_Form_Work();
		$this->view->workForm = $workForm;
		$techForm = new Application_Form_TechnicalDevice();
		$this->view->techForm = $techForm;
		$chemForm = new Application_Form_Chemical();
		$this->view->chemForm = $chemForm;
		$folderForm = new Application_Form_Folder();
		$this->view->folderForm = $folderForm;
        $this->view->bossForm = $formBoss;
		
	}
	
	public function generateIndex($rowset, $name, $id) {
		$retVal = array();
		foreach ($rowset as $item) {
			$retVal[$item[$id]] = $item[$name];
		}
		return $retVal;
	}
}