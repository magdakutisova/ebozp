<?php
class QuestController extends Zend_Controller_Action {

	public function init() {
		$this->view->layout()->setLayout("client-layout");
	}

	/*
	 * seznam dotazniku pro editaci a podobne
	 */
	public function adminAction() {

	}

	public function assignAction() {
		// nacteni dat
		$clientId = $this->_request->getParam("clientId");
		$data = $this->_request->getParam("questionary", array());

		// smazani starych dat
		$tableAssign = new Application_Model_DbTable_QuestAssignments();
		$tableAssign->delete(array("client_id = ?" => $clientId));

		// zapis dat novych
		foreach ($data as $qid => $type) {
			if ($type) {
				$tableAssign->insert(array(
					"client_id" => $clientId,
					"questionary_id" => $qid,
					"assign_type" => $type
				));
			}
		}

		$this->view->clientId = $clientId;
	}

	/*
	 * nastaveni dotazniku klientovi
	 */
	public function clientAction() {
		// nacteni klienta
		$tableClients = new Application_Model_DbTable_Client();
		$clientId = $this->_request->getParam("clientId");
		$client = $tableClients->find($clientId)->current();

		// nacteni seznamu dotazniku
		$tableAssign = new Application_Model_DbTable_QuestAssignments();
		$assignments = $tableAssign->findByClient($clientId);

		$this->view->client = $client;
		$this->view->assignments = $assignments;
	}

	/*
	 * seznam klientu pro nastaveni
	 */
	public function clientsAction() {
		// nacteni seznamu klientu
		$tableClients = new Application_Model_DbTable_Client();
		$clients = $tableClients->fetchAll(array("!deleted", "!archived"), array("company_name"));

		$this->view->clients = $clients;
	}

	/*
	 * editace dotazniku
	 */
	public function editAction() {

	}

	/*
	 * zobrazi vypis dotazniku dostupnych klientovi
	 */
	public function indexAction() {
		$clientId = $this->_request->getParam("clientId");
		$qIndex = $this->_findAssignments($clientId);
		$fIndex = $this->_findFilleds($clientId);

		// nacteni pobocek a jejich indexace dle id pobocky
		$tableSubsidiaries = new Application_Model_DbTable_Subsidiary();
		$subsidiaries = $tableSubsidiaries->fetchAll(array("client_id = ?" => $clientId));

		$this->view->clientId = $clientId;
		$this->view->assigned = $qIndex;
		$this->view->filleds = $fIndex;
		$this->view->subsidiaries = $subsidiaries;
	}

	public function subsidiaryAction() {
		// nacteni dat
		$clientId = $this->_request->getParam("clientId", 0);
		$subsidiaryId = $this->_request->getParam("subsidiaryId", 0);

		$tableClients = new Application_Model_DbTable_Client();
		$tableSubsidiaries = new Application_Model_DbTable_Subsidiary();
		$tableEmployees = new Application_Model_DbTable_Employee();
		$tableWorkplaces = new Application_Model_DbTable_Workplace();
		$tablePositions = new Application_Model_DbTable_Position();

		$client = $tableClients->find($clientId)->current();
		$subsidiary = $tableSubsidiaries->find($subsidiaryId)->current();

		// kontrola nactenych dat
		if (!$client || !$subsidiary) throw new Zend_Exception("Invalid data was sent");
		if ($subsidiary->client_id != $client->id_client) throw new Zend_Exception("Subsidiary does not belong to client");

		// nacteni zamestnancu
		$employees = $tableEmployees->fetchAll(array("subsidiary_id = ?" => $subsidiaryId), array("surname", "first_name"));

		// nacteni pracovist
		$workplaces = $tableWorkplaces->fetchAll(array("subsidiary_id = ?" => $subsidiaryId), "name");

		// nacteni pracovnich pozic
		$positions = $tablePositions->fetchAll(array("subsidiary_id = ?" => $subsidiaryId), "position");

		$this->view->client = $client;
		$this->view->subsidiary = $subsidiary;
		$this->view->employees = $employees;
		$this->view->workplaces = $workplaces;
		$this->view->positions = $positions;
		$this->view->assigned = $this->_findAssignments($clientId);
		$this->view->filleds = $this->_findFilleds($clientId);
	}

	/**
	 * nacte prirazeni dotazniku ke klientovi a vraci index
	 * @param int $clientId identifikacni cislo klienta
	 * @return array
	 */
	public function _findAssignments($clientId) {
		// nacteni pridelenych a vytvorenych dotazniku
		$tableAssign = new Application_Model_DbTable_QuestAssignments();
		$assigned = $tableAssign->findByClient($clientId);

		// rozrazeni dle typu
		$qIndex = array();

		foreach ($assigned as $item) {
			$assignedType = $item->assign_type;

			// kontrola, zda je dotaznik povolen
			if ($assignedType) {
				// zapis objektu
				$qIndex[$assignedType] = $item;
			}
		}

		return $qIndex;
	}

	protected function _findFilleds($clientId, $type=null) {
		// nalezeni vyplnenych dotazniku
		$tableClients = new Application_Model_DbTable_QuestClients();
		$filleds = $tableClients->findByClient($clientId);

		$fIndex = array();

		foreach ($filleds as $filled) {
			if (!isset($fIndex[$filled->assign_type])) $fIndex[$filled->assign_type] = array();

			$fIndex[$filled->assign_type][] = $filled;
		}

		return $fIndex;
	}
}