<?php
require_once (APPLICATION_PATH . "/modules/deadline/controllers/DeadlineController.php");

class Audit_WatchController extends Zend_Controller_Action {
	
    protected $_standardDiscused = array(
        "Byla provedena roční prověrka v oblasti BOZP a PO. Z prověrky bude pořízen samostatný zápis včetně výčtu zjištěných závad.",
        "Byla provedena kontrola odstraněných závad z minulých kontrol.",
        "Byla provedena kontrola pracoviště, zda jsou dodržovány předpisy v oblasti BOZP a PO.",
        "Byla provedena kontrola zavedené dokumentace BOZP a PO.",
        "Byla provedena kontrola lhůt školení zaměstnanců a revizí a kontrol.",
        "Proveden zápis do požární knihy o provedené preventivní požární prohlídce v rámci roční prověrky BOZP a PO.",
        "Bylo provedeno školení vedoucích zaměstnanců BOZP a PO.",
        "Byla zavedena kniha kontrol BOZP a PO."
    );
    
	public function init() {
		$this->view->layout()->setLayout("client-layout");
		$this->view->addHelperPath(APPLICATION_PATH . "/views/helpers");
	}
	
	public function adddeadHtmlAction() {
		// nacteni dohlidky
		$watch = self::loadWatch($this->_request->getParam("watchId"));
		$this->_request->setParam("clientId", $watch->client_id);
		// nacteni seznamu identifikatoru
		$deadIds =  $this->_request->getParam("deadlineId", array());
		
		// vytvoreni filtracniho selectu
		$tableDeadlines = new Deadline_Model_Deadlines();
		
		$select = new Zend_Db_Select(Zend_Db_Table_Abstract::getDefaultAdapter());
		$select->from($tableDeadlines->info("name"), array(
				new Zend_Db_Expr($watch->id),
				"id",
				new Zend_Db_Expr("next_date < NOW()"),
				"next_date"
				));
		
		$select->where("subsidiary_id = ?", $watch->subsidiary_id)->where("id in (?)", array_merge(array("0"), $deadIds));
		
		// vlozeni dat do tabulky
		$tableAssocs = new Audit_Model_WatchesDeadlines();
		$sql = sprintf("insert ignore into %s (watch_id, deadline_id, is_over, done_at) %s", $tableAssocs->info("name"), $select->assemble());
		Zend_Db_Table_Abstract::getDefaultAdapter()->query($sql);
		
		$this->view->watch = $watch;
		
		$this->_helper->FlashMessenger("Lhůty přidány");
	}
	
	public function createAction() {
		// vytvoreni formulare a nastaveni hodnot
		$form = new Audit_Form_WatchCreate();
		$this->prepareWatchForm($form);
		
		// prednastaveni technika
		$form->getElement("guard_person")->setValue(Zend_Auth::getInstance()->getIdentity()->name);
		
		// nacteni klienta a pobocky
		$tableClients = new Application_Model_DbTable_Client();
		$tableSubsidiary = new Application_Model_DbTable_Subsidiary();
		
		$client = $tableClients->find($this->_request->getParams("clientId", 0))->current();
		$subsidiary = $tableSubsidiary->find($this->_request->getParam("subsidiaryId", 0))->current();
		
		$clientData = "Za " . $client->company_name;
		
		if ($subsidiary) {
			$clientData .= " - " . $subsidiary->subsidiary_town . ", " . $subsidiary->subsidiary_street;
		}
		
		$clientData .= " převzal";
		$form->getElement("client_description")->setValue($clientData);
		
		$form->isValidPartial($this->_request->getParams());
		
		$this->view->form = $form;
	}
	
	public function createmistakeHtmlAction() {
		// nacteni dat dohlidky
		$watchId = $this->_request->getParam("watchId", 0);
		$watch = self::loadWatch($watchId);
		
		// nastaveni hodnot requestu
		$this->_request->setParam("clientId", $watch->client_id);
		$this->_request->setParam("subsidiaryId", $watch->subsidiary_id);
		
		// sestaveni URL
		$url = "/audit/watch/postmistake?watchId=" . $watch->id . "&clientId=" . $watch->client_id . "&subsidiaryId=" . $watch->subsidiary_id;
		
		// nacteni a priprava pracovist
		$tableWorkplaces = new Application_Model_DbTable_Workplace();
		$workplaces = $tableWorkplaces->fetchAll(array(
			"subsidiary_id = ?" => $watch->subsidiary_id
		), "name");
                
                // indexace dat pro zarazeni do vyberu
        $workIndex = array("0" => "--MIMO PRACOVIŠTĚ--");
        
        foreach ($workplaces as $workplace) {
            $workIndex[$workplace->id_workplace] = $workplace->name;
        }
		
		// priprava formulare a nastaveni akce
		$form = new Audit_Form_MistakeCreateAlone();
		$form->setAction($url);
		$form->isValidPartial($this->_request->getParams());
		$form->removeElement("is_removed");
        
		// prenastaveni vyberu pracovisted
		$form->removeElement("workplace_id");
		$form->addElement("select", "workplace_id", array(
				"label" => "Pracoviště",
				"decorators" => $form->getElement("category")->getDecorators(),
				"multiOptions" => $workIndex,
                                "order" => 0
		));
		
		$this->view->form = $form;
		$this->view->watch = $watch;
	}
	
	public function deleteAction() {
		$watch = self::loadWatch($this->_request->getParam("watchId"));
        
        // kontrola radku dohlidky
        if (!$watch) {
            throw new Zend_Db_Table_Exception("Watch not found");
        }
        
        if ($watch->is_closed) {
            throw new Zend_Db_Table_Row_Exception("Watch is closed");
        }
        
        // kontrola opravneni
        $user = Zend_Auth::getInstance()->getIdentity();
        
        if ($watch->user_id != $user->id_user && !in_array($user->role, array(My_Role::ROLE_COORDINATOR, My_Role::ROLE_ADMIN, My_Role::ROLE_SUPERADMIN))) {
            throw new Zend_Acl_Exception("You have not permision to delete this audit");
        }
        
        // smazani neshod
        $tableMistakes = new Audit_Model_AuditsRecordsMistakes();
        $tableMistakes->delete(array("watch_id = ?" => $watch->id));
        
        $this->view->clientId = $watch->client_id;
        $this->view->subsidiaryId = $watch->subsidiary_id;
        
        $watch->delete();
	}
	
	public function discussAction() {
		// nacteni dohlidky
		$watchId = $this->_request->getParam("watchId", 0);
		$watch = self::loadWatch($watchId);
		
		// nacteni dat a rpiprava tabulky
		$tableDiscuss = new Audit_Model_WatchesDiscussed();
		$contents = (array) $this->_request->getParam("discussed", array());
		$contents = array_merge(array("content" => array()), $contents);
		
		self::_writeListItems($tableDiscuss, $watch, $contents["content"]);
		
		$this->_helper->FlashMessenger("Změny byly uloženy");
		
		$this->view->watch = $watch;
	}
	
	public function deadlistHtmlAction() {
		// nacteni dohlidky
		$watch = self::loadWatch($this->_request->getParam("watchId"));
        
		// selekce lhut, ktere jeste nejsou v dohlidce
		$tableAssocs = new Audit_Model_WatchesDeadlines();
		$subSelect = new Zend_Db_Select(Zend_Db_Table_Abstract::getDefaultAdapter());
		$subSelect->from($tableAssocs->info("name"), "deadline_id")->where("watch_id = ?", $watch->id);
		
		// nacteni lhut
		$tableDeadlines = new Deadline_Model_Deadlines();
		$select = $tableDeadlines->_prepareSelect();
		$select->where("d.subsidiary_id = ?", $watch->subsidiary_id)->where("id not in (?)", new Zend_Db_Expr($subSelect));
		
		$data = $select->query()->fetchAll();
		
		$this->view->deadlines = $data;
		$this->view->watch = $watch;
	}
	
	public function editAction() {
		// nacteni dat
		$watchId = $this->_request->getParam("watchId", 0);
		$watch = self::loadWatch($watchId);
        
		// kontrola uzavreni
		if ($watch->is_closed) throw new Zend_Db_Table_Row_Exception("Watch is closed");
		
		// prevedeni na pole a revedeni datumu
		$data = $watch->toArray();
		
		// formular editace
		$form = new Audit_Form_Watch();
		$form->setAction("/audit/watch/put");
		self::prepareWatchForm($form);
		self::fillForm($data, $form);
		
		$form->isValidPartial($this->_request->getParams());
		
		// formular pridani kontaktni osoby
		$contactForm = new Audit_Form_ContactPerson();
		$contactForm->setActionParams($watch->id);
		$contactForm->populate($watch->toArray());
		$contactForm->isValidPartial($this->_request->getParams());
		
		// nacteni probranych polozek
		$discussed = $watch->findDiscussed();
		
		// nacteni zmen
		$changes = $watch->findChanges();
		
		// nacteni objednavek
		$order = $watch->findOrder();
		
		// pokud nebyl zaroven provaden audit/proverka, pak se nactou lhuty a neshody
		if (!$watch->also_audit) {
			// neshody vazane k dohlidce
			$mistakes = $watch->findMistakes();
			
			// nacteni lhut
            $tableDeadlines = new Audit_Model_WatchesDeadlines();
			$deadlines = $tableDeadlines->findExtendedByWatch($watch, true, false);
			
			$this->view->deadlines = $deadlines;
			$this->view->mistakes = $mistakes;
		} else {
			$form->removeElement("display_mistakes");
			$form->removeElement("display_deadlines");
		}
		
		// nacteni pracovist pobocky
		$tableWorkplaces = new Application_Model_DbTable_Workplace();
		$workplaces = $tableWorkplaces->fetchAll(array("subsidiary_id = ?" => $watch->subsidiary_id), "name");
		
		$this->view->form = $form;
		$this->view->contactForm = $contactForm;
		$this->view->watch = $watch;
		$this->view->discussed = $discussed;
		$this->view->changes = $changes;
		$this->view->order = $order;
		$this->view->workplaces = $workplaces;
	}
	
	public function getAction() {
		// nacteni dohlidky
		$watchId = $this->_request->getParam("watchId", 0);
		$watch = self::loadWatch($watchId);
		
		// nacteni dat
		$user = $watch->findParentRow("Application_Model_DbTable_User", "user");
		$changes = $watch->findChanges();
		$discussed = $watch->findDiscussed();
		$order = $watch->findOrder();
		
		// nacteni kontaktni osoby
		if ($watch->contactperson_id) {
			$person = $watch->findParentRow("Application_Model_DbTable_ContactPerson", "contact");
		} else {
			$person = (object) array("name" => $watch->contact_name, "phone" => $watch->contact_phone, "email" => $watch->contact_email);
		}
		
		// pokud nebyl zaroven provaden audit/proverka pak se nactou neshody a lhuty
		if (!$watch->also_audit) {
			// nacteni lhut
			$tableDeadlines = new Audit_Model_WatchesDeadlines();
			$deadlines = $tableDeadlines->findExtendedByWatch($watch, true, false);
			$mistakes = $watch->findMistakes(true);
			
			$this->view->deadlines = $deadlines;
			$this->view->mistakes = $mistakes;
		}
		
		$this->view->user = $user;
		$this->view->watch = $watch;
		$this->view->changes = $changes;
		$this->view->discussed = $discussed;
		$this->view->order = $order;
		$this->view->person = $person;
	}
	
	public function getdeadHtmlAction() {
		// nacteni dohlidky
		$watch = self::loadWatch($this->_request->getParam("watchId", 0));
		
		// nacteni asociace
		$tableAssocs = new Audit_Model_WatchesDeadlines();
		$deadlineId = $this->_request->getParam("deadlineId");
		$assoc = $tableAssocs->findByWatchDeadline($watch->id, $deadlineId);
		
		if (!$assoc) throw new Zend_Db_Table_Exception("Combination of deadline and watch not found");
		
		// nacteni lhuty
		$tableDeadlines = new Deadline_Model_Deadlines();
		$deadline = $tableDeadlines->findById($deadlineId, true);
		
		// formular pro splneni lhuty
		if ($watch->is_closed) {
			$formDone = null;
		} else {
			$formDone = new Audit_Form_Deadline();
			$url = sprintf("/audit/watch/subdead.html?deadlineId=%s&watchId=%s&clientId=%s", $deadline->id, $watch->id, $watch->client_id);
			$formDone->populate($assoc->toArray());
			$formDone->setAction($url);
		}
		
		$this->view->watch = $watch;
		$this->view->deadline = $deadline;
		$this->view->assoc = $assoc;
		
		$this->view->formDone = $formDone;
	}
	
	public function changesAction() {
		// nacteni dohlidky
		$watchId = $this->_request->getParam("watchId", 0);
		$watch = self::loadWatch($watchId);
		
		// nacteni dat
        $changes = $this->_request->getParam("changes", null);
        
        if (!$changes) {
            $changes = null;
        }
        
        $watch->changes = $changes;
        $watch->save();
		
		$this->_helper->FlashMessenger("Změny byly uloženy");
		
		$this->view->watch = $watch;
	}
	
	/**
	 * zobrazi seznam dohlidek u klienta / pobocky
	 */
	public function indexAction() {
		// nacteni dat
		$clientId = $this->_request->getParam("clientId", 0);
		$subsidiaryId = $this->_request->getParam("subsidiaryId", 0);
		
		// pokud je pobocka rovna nule, pak se nacte id vychoti pobocky
		if ($subsidiaryId == 0) {
			$tableSubsidiaries = new Application_Model_DbTable_Subsidiary();
			$subsidiary = $tableSubsidiaries->fetchRow(array("client_id = ?" => $clientId, "hq"));
			$subsidiaryId = $subsidiary->id_subsidiary;
		}
		
		// vygenerovani vyhledavaciho dotazu
		$acl = new My_Controller_Helper_Acl();
		$user = Zend_Auth::getInstance()->getInstance()->getIdentity();
		
		$tableWatches = new Audit_Model_Watches();
		$onlyClosed = !$acl->isAllowed($user->role, "audit:watch", "edit");
		$watches = $tableWatches->findWatches($clientId, $subsidiaryId, $onlyClosed);
		
		$this->view->watches = $watches;
		$this->view->clientId = $clientId;
		$this->view->subsidiaryId = $subsidiaryId;
		$this->view->onlyClosed = $onlyClosed;
	}
	
	public function mistakesAction() {
		// nacteni dohlidky
		$watchId = $this->_request->getParam("watchId", 0);
		$watch = self::loadWatch($watchId);
		
		// nacteni id neshod
		$mistakes = (array) $this->_request->getParam("mistake", array());
		
		$selecteds = array(0);
		
		foreach ($mistakes as $id => $val) {
			if ($val["select"]) {
				$selecteds[] = $id;
			}
		}
		
		// zapis odstranenych neshod
		$where = array(
				"watch_id = ?" => $watch->id,
				"mistake_id in (?)" => $selecteds
		);
		
		if ($this->_request->getParam("mistake-remove", 0)) {
			$data = array("set_removed" => 1);
		} else {
			$data = array("set_removed" => 0);
		}
		
		$tableAssocs = new Audit_Model_WatchesMistakes();
		$tableAssocs->update($data, $where);
		
		$this->_helper->FlashMessenger("Změny byly uloženy");
		
		$this->view->watch = $watch;
	}
	
	/**
	 * zapise jinou kontaktni osobou nez je v seznamu
	 */
	public function newcontactAction() {
		// nacteni dat
		$watchId = $this->_request->getParam("watchId", 0);
		$watch = self::loadWatch($watchId);
		
		// nastaveni dalsich parametru request
		$this->_request->setParam("clientId", $watch->client_id)->setParam("subsidiaryId", $watch->subsidiary_id);
		
		// naplneni dat
		$form = new Audit_Form_ContactPerson();
		
		if (!$form->isValid($this->_request->getParams())) {
			$this->_forward("edit");
			return;
		}
		
		// nastaveni dat a zapis radku
		$watch->setFromArray($form->getValues(true));
		$watch->contactperson_id = null;
		$watch->save();
		
		$this->_helper->FlashMessenger("Kontaktní osoba byla vytvořena");
		
		$this->view->watch = $watch;
	}
	
	public function ordersAction() {
		// nacteni dohlidky
		$watchId = $this->_request->getParam("watchId", 0);
		$watch = self::loadWatch($watchId);
	
		// nacteni dat
        $orders = trim($this->_request->getParam("orders", null));
        
        if (!$orders) {
            $orders = null;
        }
        
        $order = $watch->findOrder();
        $order->content = $orders;
        $order->save();
        
		$this->_helper->FlashMessenger("Změny byly uloženy");
	
		$this->view->watch = $watch;
	}
	
	public function outputsAction() {
		// nacteni dohlidky
		$watchId = $this->_request->getParam("watchId", 0);
		$watch = self::loadWatch($watchId);
	
		// nacteni dat a rpiprava tabulky
		$watch->outputs = $this->_request->getParam("outputs");
		$watch->save();
		
		$this->_helper->FlashMessenger("Změny byly uloženy");
	
		$this->view->watch = $watch;
	}
	
	public function postAction() {
		// nacteni a kontrola dat
		$form = new Audit_Form_WatchCreate();
		$this->prepareWatchForm($form);
		
		if (!$form->isValid($this->_request->getParams())) {
			$this->_forward("create");
			return;
		}
		
		// vytvoreni zaznamu
		$tableWatches = new Audit_Model_Watches();
		$data = self::prepareForSave($form);
		$data["subsidiary_id"] = $this->_request->getParam("subsidiaryId");
		$data["client_id"] = $this->_request->getParam("clientId");
		$data["user_id"] = Zend_Auth::getInstance()->getIdentity()->id_user;
		
		$watch = $tableWatches->createRow($data);
		$watch->save();
        
        // zapis standardnich prodiskutovanych
        $tableDiscusseds = new Audit_Model_WatchesDiscussed();
        
        // nacteni naposledy provedene dohlidky
        $lastWatch = $tableWatches->fetchRow(array(
            "subsidiary_id = ?" => $watch->subsidiary_id,
            "is_closed"
        ), array("watched_at desc", "id desc"));
        
        // pokud byla nalezena posledni dohlidka, pak se prekopiruji veci z ni, jinak se nastavi vychozi
        if ($lastWatch) {
            $sqlBase = "insert into %s (watch_id, content) select %s, content from %s where watch_id = %s order by id";
            $sql = sprintf($sqlBase, $tableDiscusseds->info("name"), $watch->id, $tableDiscusseds->info("name"), $lastWatch->id);
            
            $tableDiscusseds->getAdapter()->query($sql);
        } else {
            foreach ($this->_standardDiscused as $content) {
                $tableDiscusseds->insert(array(
                    "content" => $content,
                    "watch_id" => $watch->id
                ));
            }
        }
		
		// vyhodnoceni, zda se jedna o dohlidku spolecne s proverkou/auditem
		if (!$watch->also_audit) {
			// vlozeni neshod, ktere jsou vazany k pobocce
			$tableAssocs = new Audit_Model_WatchesMistakes();
			$tableAssocs->insertByWatch($watch);
			
			// zapsani asociaci lhut
			$tableDeadlines = new Audit_Model_WatchesDeadlines();
			$tableDeadlines->createByWatch($watch);
		}
        
		$this->_helper->FlashMessenger("Dohldíka vytvořena");
		
		$this->view->watch = $watch;
	}
	
	public function postmistakeAction() {
		// nacteni dat
		$watchId = $this->_request->getParam("watchId", 0);
		$workplaceId = $this->_request->getParam("workplaceId", 0);
		
		$watch = self::loadWatch($watchId);
		
		// kontrola validity
		$form = new Audit_Form_MistakeCreateAlone();
		$form->removeElement("record_id");
		
		if (!$form->isValid($this->_request->getParams())) {
			$this->_forward("createmistake");
			return;
		}
		
		// zapis neshody a presun na editaci dohlidky
		$tableMistakes = new Audit_Model_AuditsRecordsMistakes();
		$data = $form->getValues(true);
		
		$removedAt = new Zend_Date($data["will_be_removed_at"], "MM. dd. y");
		
		$mistake = $tableMistakes->createMistake(
				null, 
				$removedAt, 
				$form->getValue("mistake"), 
				$form->getValue("suggestion"), 
				$data["comment"], 
				$data["hidden_comment"], 
				$data["category"], 
				$data["subcategory"], 
				$data["concretisation"], 
				null, 
				$data["weight"], 
				$watch);
		
		$mistake->responsibile_name = $form->getValue("responsibile_name");
		
		// nastaveni pracoviste
		$workplaceId = $form->getValue("workplace_id");
		if ($workplaceId) $mistake->workplace_id = $workplaceId;
		
		$mistake->save();
		
		// zapis asociace
		$tableAssocs = new Audit_Model_WatchesMistakes();
		$tableAssocs->insert(array(
				"watch_id" => $watch->id,
				"mistake_id" => $mistake->id
				));
		
		$this->view->watch = $watch;
		$this->view->mistake = $mistake;
		
		$this->_helper->FlashMessenger("Neshoda byla vytvořena");
	}
	
	public function protocolPdfAction() {
		// nacteni dat
		$watchId = $this->_request->getParam("watchId", 0);
		$watch = self::loadWatch($watchId);

        $mistakes = $watch->findMistakes();
		$order = $watch->findOrder();
		$discussed = $watch->findDiscussed();
		$changes = $watch->findChanges();
		
		// nacteni dat o klientovi
		$client = $watch->getClient();
		$subsidiary = $watch->getSubsidiary();
		$person = $watch->getContactPerson();
		$user = $watch->getUser();
		
		// vyhodnoceni zastupce
		if (!$person) {
			$person = (object) array(
					"name" => $watch->contact_name,
					"phone" => $watch->contact_phone,
					"email" => $watch->contact_email);
		}
		
		// nacteni lhut
        $tableDeadlines = new Audit_Model_WatchesDeadlines();
		$deadlines = $tableDeadlines->findExtendedByWatch($watch, true, $watch->display_deadlines_close);
        
		$this->view->watch = $watch;
		$this->view->mistakes = $mistakes;
		$this->view->order = $order;
		$this->view->discussed = $discussed;
		$this->view->changes = $changes;
		$this->view->client = $client;
		$this->view->subsidiary = $subsidiary;
		$this->view->user = $user;
		$this->view->person = $person;
		$this->view->deadlines = $deadlines;
		$this->view->logo = __DIR__ . "/../resources/logo.png";
		$this->view->disableHeaders = $this->_request->getParam("disableHeaders", 0);
	}
	
	public function putAction() {
		// nacteni a validace dat
		$form = new Audit_Form_Watch();
		$this->prepareWatchForm($form);
		
		if (!$form->isValid($this->_request->getParams())) {
			$this->_forward("edit");
			return;
		}
		
		// nacteni dat
		$watch = self::loadWatch($this->_request->getParam("watchId", 0));
		$data = self::prepareForSave($form);
		
		$watch->setFromArray($data)->save();
		
		$this->_helper->FlashMessenger("Změny byly uloženy");
		
		$this->view->watch = $watch;
	}
	
	public function sendAction() {
		// kontrolni nacteni 
		$watch = self::loadWatch($this->_request->getParam("watchId"));
		
		$preview = (!$watch->is_closed) ? " návrh protokolu" : " protokol";
		
		// vygenerovani protokolu
		$pdfProt = $this->view->action("protocol.pdf", "watch", "audit", array("watchId" => $watch->id, "disableHeaders" => 1));
		
		// vyhodnoceni kontaktni osoby
		if ($watch->contactperson_id) {
			$tableContacts = new Application_Model_DbTable_ContactPerson();
			$contact = $tableContacts->find($watch->contactperson_id)->current();
			
			$email = $contact->email;
			$name = $contact->name;
		} else {
			$email = $watch->contact_email;
			$name = $watch->contact_name;
		}
        
        $subsidiary = $watch->getSubsidiary();
        $fileName = sprintf("%s, %s, %s - %s.pdf", $subsidiary->subsidiary_name, $subsidiary->subsidiary_town, $subsidiary->subsidiary_street, $watch->watched_at);

        $mailer = new Zend_Mail("UTF-8");
        $mailer->setSubject('=?UTF-8?B?' . base64_encode("Protokol z kontrolní dohlídky") . '?=');
        $mailer->setBodyText("Dobrý den,

v příloze zasíláme protokol z kontrolní dohlídky BOZP a PO na Vašem pracovišti.

S pozdravem

GUARD7, v.o.s.");
        
        $mailer->createAttachment($pdfProt, "application/pdf", "attachment", Zend_Mime::ENCODING_BASE64, $fileName);
        
        $transport = new Zend_Mail_Transport_Smtp("smtp.gmail.com", array(
				'ssl' => 'ssl',
				'port' => 465,
				'auth' => 'login',
				'username' => 'guardian@guard7.cz',
				'password' => 'guardianG7',
				));
        
        $mailer->addTo($email, $name);
        $mailer->addTo("podklady@guard7.cz");
        $mailer->send($transport);
        
		$this->view->watch = $watch;
		
		$this->_helper->FlashMessenger("Protokol odeslán");
	}
	
	public function subdeadAction() {
		// nacteni dat
		$watchId = $this->_request->getParam("watchId", 0);
		$watch = self::loadWatch($watchId);
		
		$this->_request->setParam("clientId", $watch->client_id);
		
		// nacteni zaskrtnutych policek
		$selected = (array) $this->_request->getParam("selected", array());
		$selectedIds = array_merge(array(0), $selected);
		
		// nacteni dat o splneni
		$data = $this->_request->getParam("deadline", array());
		$data = array_merge(array("done_at" => "", "comment" => ""), $data);
		
		// vytvoreni updatovacich a filtracnich poli
		$where = array(
				"deadline_id in (?)" => $selectedIds,
				"watch_id = ?" => $watch->id
				);
		
		$updateData = array(
				"note" => $data["comment"],
				"done_at" => $data["done_at"],
				"is_done" => 1
				);
		
		$tableAssocs = new Audit_Model_WatchesDeadlines();
		$tableAssocs->update($updateData, $where);
		
		$this->_helper->FlashMessenger("Změny byly uloženy");
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
		$watch = self::loadWatch($this->_request->getParam("watchId"));
		$tableAssocs = new Audit_Model_WatchesDeadlines();
		$assoc = $tableAssocs->findByWatchDeadline($watch->id, $this->_request->getParam("deadlineId"));
		
		if (!$assoc) throw new Zend_Db_Table_Exception("Association not found");
		
		// nastaveni dat
		$assoc->setFromArray($form->getValues(true));
		$assoc->save();
		
		$this->view->assoc = $assoc;
		$this->view->watch = $watch;
		
		$this->_helper->FlashMessenger("Změny byly uloženy");
	}
	
	/**
	 * uzavre dohlidku
	 */
	public function submitAction() {
		// nacteni dat
		$watchId = $this->_request->getParam("watchId", 0);
		$watch = self::loadWatch($watchId);
		
		// kontrola uzivatele
		$user = Zend_Auth::getInstance()->getIdentity();
		$this->_request->setParam("clientId", $watch->client_id);
		$this->view->layout()->setLayout("floating-layout");
		
		if ($user->id_user != $watch->user_id && $user->role != My_Role::ROLE_ADMIN) {
			// chyba opravneni
			throw new Zend_Auth_Exception("Invalid user for submit watch #$watch->id");
		}
		
		if ($watch->is_closed) throw new Zend_Db_Table_Row_Exception("Watch #$watch->id is closed");

		// kontrola kontaktni osoby
		if ($watch->contactperson_id) {
			$tableContacts = new Application_Model_DbTable_ContactPerson();

			$contact = $tableContacts->find($watch->contactperson_id)->current();
			$watch->contactperson_id = null;
			$watch->contact_name = $contact->name;
			$watch->contact_phone = $contact->phone;
			$watch->contact_email = $contact->email;
		}
		
		$watch->is_closed = 1;
		$watch->save();
		
		// odeslani neshod v asociaci
		$tableAssocs = new Audit_Model_WatchesMistakes();
		$tableAssocs->update(array("is_submited" => 1), array("watch_id = ?" => $watch->id));
		
		// odeslani novych neshod v neshodach
		$tableMistakes = new Audit_Model_AuditsRecordsMistakes();
		$tableMistakes->update(array("is_submited" => 1), array("watch_id = ?" => $watch->id));
		
		// oznaceni neshod jako odebranych
		$select = new Zend_Db_Select($tableAssocs->getAdapter());
		$select->from($tableAssocs->info("name"), array("mistake_id"));
		$select->where("set_removed")->where("watch_id = ?", $watch->id);
		
		$tableMistakes->update(array("is_removed" => 1), array("id in (?)" => new Zend_Db_Expr($select->assemble())));
		
		// oznaceni lhut, ktere byly vybrany jako splnenych
		$tableDeadlinesAssocs = new Audit_Model_WatchesDeadlines();
		$tableDeadlines = new Deadline_Model_Deadlines();
		
		$nameDeadlinesAssocs = $tableDeadlinesAssocs->info("name");
		$nameDeadlines = $tableDeadlines->info("name");
		
		// sestaveni updatovaciho dotazu
		$sql = sprintf("update %s as deads, %s as assocs set deads.last_done = assocs.done_at, deads.next_date = ADDDATE(assocs.done_at, INTERVAL deads.period MONTH) WHERE deads.id = assocs.deadline_id and is_done and watch_id = %s", 
				$nameDeadlines, $nameDeadlinesAssocs, $watch->id);
		
		Zend_Db_Table_Abstract::getDefaultAdapter()->query($sql);
		
		// zapis do logu
		$tableLogs = new Deadline_Model_Logs();
		$nameLogs = $tableLogs->info("name");
		
		$sql = sprintf("insert into %s (deadline_id, done_at, note, user_id) select deadline_id, done_at, note, %s from %s where is_done and watch_id = %s", 
				$nameLogs, $user->id_user, $nameDeadlinesAssocs, $watch->id);
		
		Zend_Db_Table_Abstract::getDefaultAdapter()->query($sql);
        
		$this->view->watch = $watch;
        
        // zapis do denniku
        $this->_helper->diaryRecord->insertMessage("provedl dohlídku na pracovišti", null, null, sprintf("<a href='/audit/watch/protocol.pdf?watchId=%d'>protokol</a>", $watch->id), $watch->subsidiary_id);
		
		$this->_helper->FlashMessenger("Dohlídka uzavřena");
	}
	
	public function prepareWatchForm($form) {
		// nacteni id klienta a pobocky
		$clientId = $this->_request->getParam("clientId", 0);
		$subsidiaryId = $this->_request->getParam("subsidiaryId", 0);
		$watchId = $this->_request->getParam("watchId", null);
		$form->setClientData($clientId, $subsidiaryId, $watchId);
		
		// nacteni kontaktnich osob
		$tableContacts = new Application_Model_DbTable_ContactPerson();
		$contacts = $tableContacts->fetchAll(array("subsidiary_id = ?" => $subsidiaryId, "!is_deleted"));
		$form->setContacts($contacts);
		
		return $form;
	}
	
	public static function loadWatch($id) {
		$tableWatches = new Audit_Model_Watches();
		$watch = $tableWatches->findById($id);
		
		if (!$watch) throw new Zend_Db_Table_Exception("Watch #$id not found");
		
		return $watch;
	}
	
	public static function fillForm($data, $form) {
		// prevedeni datumu
		$data["watched_at"] = My_Filter_Date::revert($data["watched_at"]);
		
		$form->populate($data);
		
		return $form;
	}
	
	/**
	 * prirpavi data formulare pro ulozeni
	 * @param unknown_type $form
	 */
	public static function prepareForSave($form) {
		$data = $form->getValues(true);
		
		// kontrola nevybrane kontaktni osoby a casu
		if ($data["contactperson_id"]) {
			// anulace osoby v radku
			$data["contact_name"] = null;
			$data["contact_phone"] = null;
			$data["contact_email"] = null;
		} else {
			$data["contactperson_id"] = null;
		}
		if (!$data["time_from"]) $data["time_from"] = null;
		if (!$data["time_to"]) $data["time_to"] = null;
		
		// prevedeni na SQL format
		$filter = new My_Filter_Date();
		$data["watched_at"] = $filter->filter($data["watched_at"]);
		
		return $data;
	}
	
	private static function _writeListItems($table, $watch, $items) {
		// smazani starych dat
		$table->delete(array("watch_id = ?" => $watch->id));
		
		foreach ($items as $item) {
			$table->insert(array(
					"watch_id" => $watch->id,
					"content" => $item
			));
		}
	}
	
	private static function generateMail($messageContent, $pdf, $from, $to, $subsidiary, $watch) {
		// vygenerovani hranice
		$boundary = uniqid('np');
		$boundary2 = $boundary . "2";
        
        // vygenerovani jmena souboru
        $fileName = sprintf("%s, %s, %s - %s", $subsidiary->subsidiary_name, $subsidiary->subsidiary_town, $subsidiary->subsidiary_street, $watch->watched_at);
        $nameHeader = "=protokol.pdf";
        //$nameHeader = "*=UTF-8''" . str_replace("+", "%20", urlencode($fileName));
		
		// hlavicky
		$headers = "MIME-Version: 1.0\r\n";
		$headers .= "From: $from \r\n";
		$headers .= "To: $to \r\n";
        $headers .= "Cc: podklady@guard7.cz\r\n";
		$headers .= "Content-Type: multipart/mixed;boundary=" . $boundary . "\r\n";
		
		$message .= "\r\n\r\n--" . $boundary . "\r\n";
		$message .= "Content-Type: text/plain; charset=utf-8\r\n";
		$message .= "Content-Disposition: inline\r\n";
		$message .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
		$message .= $messageContent;

		$message .= "\r\n\r\n--$boundary\r\n";
		$message .= "Content-Transfer-Encoding: base64\r\n";
		$message .= "Content-Disposition: attachment; filename$nameHeader\r\n";
		$message .= "Content-type: application/pdf; name$nameHeader\r\n\r\n";
		
		$message .= base64_encode($pdf);
		$message .= "\r\n\r\n--" . $boundary . "--";
		
		return array("message" => $message, "headers" => $headers);
	}
}