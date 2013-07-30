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
		
		$form->isValidPartial($this->_request->getParams());
		
		$this->view->form = $form;
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
		$contactForm->isValidPartial($this->_request->getParams());
		
		// nacteni probranych polozek
		$discussed = $watch->findDiscussed();
		
		// nacteni zmen
		$changes = $watch->findChanges();
		
		// nacteni objednavek
		$orders = $watch->findOrders();
		
		// realizacni vystup
		$outputs = $watch->findOutputs();
		
		$this->view->form = $form;
		$this->view->contactForm = $contactForm;
		$this->view->watch = $watch;
		$this->view->discussed = $discussed;
		$this->view->changes = $changes;
		$this->view->orders = $orders;
		$this->view->outputs = $outputs;
	}
	
	public function getAction() {
		
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
		
	}
	
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
		$tableContacts = new Application_Model_DbTable_ContactPerson();
		$contactPerson = new Application_Model_ContactPerson();
		$contactPerson->populate($form->getValues(true));
		$contactPerson->setSubsidiaryId($watch->subsidiary_id);
		$watch->contactperson_id = $tableContacts->addContactPerson($contactPerson);
		$watch->save();
		
		$this->view->watch = $watch;
		$this->view->contactPerson = $contactPerson;
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
		
		$this->view->watch = $watch;
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
		if (!$data["contactperson_id"]) $data["contactperson_id"] = null;
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