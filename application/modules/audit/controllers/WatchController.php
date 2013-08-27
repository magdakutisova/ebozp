<?php
class Audit_WatchController extends Zend_Controller_Action {
	
	public function init() {
		$this->view->layout()->setLayout("client-layout");
		$this->view->addHelperPath(APPLICATION_PATH . "/views/helpers");
	}
	
	public function createAction() {
		// vytvoreni formulare a nastaveni hodnot
		$form = new Audit_Form_Watch();
		$this->prepareWatchForm($form);
		
		// prednastaveni technika
		$form->getElement("guard_person")->setValue(Zend_Auth::getInstance()->getIdentity()->username);
		
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
	
	public function createmistakeAction() {
		// nacteni dat dohlidky
		$watchId = $this->_request->getParam("watchId", 0);
		$watch = self::loadWatch($watchId);
		
		// nastaveni hodnot requestu
		$this->_request->setParam("clientId", $watch->client_id);
		$this->_request->setParam("subsidiaryId", $watch->subsidiary_id);
		
		// nacteni id pracoviste, pokud je treba
		$workplaceId = $this->_request->getParam("workplaceId", 0);
		$workplace = null;
		
		// sestaveni URL
		$url = "/audit/watch/postmistake?watchId=" . $watch->id . "&clientId=" . $watch->client_id . "&subsidiaryId=" . $watch->subsidiary_id;
		
		if ($workplaceId) {
			$tableWorkplaces = new Application_Model_DbTable_Workplace();
			$workplace = $tableWorkplaces->find($workplaceId)->current();
		}
		
		// priprava formulare a nastaveni akce
		$form = new Audit_Form_MistakeCreateAlone();
		$form->setAction($url);
		$form->isValidPartial($this->_request->getParams());
		$form->getElement("workplace_id")->setValue($workplaceId);
		
		$this->view->form = $form;
		$this->view->workplace = $workplace;
		$this->view->watch = $watch;
	}
	
	public function deleteAction() {
		
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
		
		$this->view->watch = $watch;
	}
	
	public function editAction() {
		// nacteni dat
		$watchId = $this->_request->getParam("watchId", 0);
		$watch = self::loadWatch($watchId);
		
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
		$orders = $watch->findOrders();
		
		// realizacni vystup
		$outputs = $watch->findOutputs();
		
		// neshody vazane k dohlidce
		$mistakes = $watch->findMistakes();
		
		// nacteni pracovist pobocky
		$tableWorkplaces = new Application_Model_DbTable_Workplace();
		$workplaces = $tableWorkplaces->fetchAll(array("subsidiary_id = ?" => $watch->subsidiary_id), "name");
		
		$this->view->form = $form;
		$this->view->contactForm = $contactForm;
		$this->view->watch = $watch;
		$this->view->discussed = $discussed;
		$this->view->changes = $changes;
		$this->view->orders = $orders;
		$this->view->outputs = $outputs;
		$this->view->mistakes = $mistakes;
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
		$mistakes = $watch->findMistakes();
		$orders = $watch->findOrders();
		$outputs = $watch->findOutputs();
		
		// nacteni kontaktni osoby
		if ($watch->contactperson_id) {
			$person = $watch->findParentRow("Application_Model_DbTable_ContactPerson", "contact");
		} else {
			$person = (object) array("name" => $watch->contact_name, "phone" => $watch->contact_phone, "email" => $watch->contact_email);
		}
		
		$this->view->user = $user;
		$this->view->watch = $watch;
		$this->view->changes = $changes;
		$this->view->discussed = $discussed;
		$this->view->mistakes = $mistakes;
		$this->view->orders = $orders;
		$this->view->outputs = $outputs;
		$this->view->person = $person;
	}
	
	public function changesAction() {
		// nacteni dohlidky
		$watchId = $this->_request->getParam("watchId", 0);
		$watch = self::loadWatch($watchId);
		
		// tvorba tabulky a nacteni dat
		$tableChanges = new Audit_Model_WatchesChanges();
		$contents = (array) $this->_request->getParam("change", array());
		$contents = array_merge(array("content" => array()), $contents);
		
		self::_writeListItems($tableChanges, $watch, $contents["content"]);
		
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
		$tableWatches = new Audit_Model_Watches();
		$watches = $tableWatches->findWatches($clientId, $subsidiaryId);
		
		$this->view->watches = $watches;
		$this->view->clientId = $clientId;
		$this->view->subsidiaryId = $subsidiaryId;
	}
	
	public function mistakesAction() {
		// nacteni dohlidky
		$watchId = $this->_request->getParam("watchId", 0);
		$watch = self::loadWatch($watchId);
		
		// nacteni id neshod
		$mistakes = (array) $this->_request->getParam("mistake", array());
		
		$removed = array(0);
		$notRemoved = array(0);
		
		foreach ($mistakes as $id => $val) {
			if ($val["select"]) {
				$removed[] = $id;
			} else {
				$notRemoved[] = $id;
			}
		}
		
		// zapis odstranenych neshod
		$where = array(
				"watch_id = ?" => $watch->id,
				"mistake_id in (?)" => $removed
		);
		
		$tableAssocs = new Audit_Model_WatchesMistakes();
		$tableAssocs->update(array("set_removed" => 1), $where);
		
		// zapis neodstranenych neshod
		$where["mistake_id in (?)"] = $notRemoved;
		
		$tableAssocs->update(array("set_removed" => 0), $where);
		
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
		
		$this->view->watch = $watch;
	}
	
	public function ordersAction() {
		// nacteni dohlidky
		$watchId = $this->_request->getParam("watchId", 0);
		$watch = self::loadWatch($watchId);
	
		// nacteni dat a rpiprava tabulky
		$tableOrders = new Audit_Model_WatchesOrders();
		$contents = (array) $this->_request->getParam("order", array());
		$contents = array_merge(array("content" => array()), $contents);
	
		self::_writeListItems($tableOrders, $watch, $contents["content"]);
	
		$this->view->watch = $watch;
	}
	
	public function outputsAction() {
		// nacteni dohlidky
		$watchId = $this->_request->getParam("watchId", 0);
		$watch = self::loadWatch($watchId);
	
		// nacteni dat a rpiprava tabulky
		$tableOutputs = new Audit_Model_WatchesOutputs();
		$contents = (array) $this->_request->getParam("output", array());
		$contents = array_merge(array("content" => array()), $contents);
	
		self::_writeListItems($tableOutputs, $watch, $contents["content"]);
	
		$this->view->watch = $watch;
	}
	
	public function postAction() {
		// nacteni a kontrola dat
		$form = new Audit_Form_Watch();
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
		
		// vlozeni neshod, ktere jsou vazany k pobocce
		$tableAssocs = new Audit_Model_WatchesMistakes();
		$tableAssocs->insertByWatch($watch);
		
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
		
		// zapis asociace
		$tableAssocs = new Audit_Model_WatchesMistakes();
		$tableAssocs->insert(array(
				"watch_id" => $watch->id,
				"mistake_id" => $mistake->id
				));
		
		$this->view->watch = $watch;
		$this->view->mistake = $mistake;
	}
	
	public function protocolPdfAction() {
		// nacteni dat
		$watchId = $this->_request->getParam("watchId", 0);
		$watch = self::loadWatch($watchId);
		
		$mistakes = $watch->findMistakes();
		$outputs = $watch->findOutputs();
		$orders = $watch->findOrders();
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
		
		$this->view->watch = $watch;
		$this->view->mistakes = $mistakes;
		$this->view->outputs = $outputs;
		$this->view->orders = $orders;
		$this->view->discussed = $discussed;
		$this->view->changes = $changes;
		$this->view->client = $client;
		$this->view->subsidiary = $subsidiary;
		$this->view->user = $user;
		$this->view->person = $person;
		$this->view->logo = __DIR__ . "/../resources/logo.png";
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
		$this->view->watch = $watch;
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
		
		if ($user->id_user != $watch->user_id && $user->role != My_Role::ROLE_ADMIN) {
			// chyba opravneni
			throw new Zend_Auth_Exception("Invalid user for submit watch #$watch->id");
		}
		
		if ($watch->is_closed) throw new Zend_Db_Table_Row_Exception("Watch #$watch->id is closed");
		
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
		
		$this->view->watch = $watch;
	}
	
	public function prepareWatchForm($form) {
		// nacteni id klienta a pobocky
		$clientId = $this->_request->getParam("clientId", 0);
		$subsidiaryId = $this->_request->getParam("subsidiaryId", 0);
		$watchId = $this->_request->getParam("watchId", null);
		$form->setClientData($clientId, $subsidiaryId, $watchId);
		
		// nacteni kontaktnich osob
		$tableContacts = new Application_Model_DbTable_ContactPerson();
		$contacts = $tableContacts->fetchAll(array("subsidiary_id = ?" => $subsidiaryId));
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
}