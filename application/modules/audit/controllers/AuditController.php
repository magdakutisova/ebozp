<?php
class Audit_AuditController extends Zend_Controller_Action {
	
	/**
	 * radek s uzivatelem
	 * @var Application_Model_User
	 */
	protected $_user;
	
	public function init() {
		// zapsani helperu
		$this->view->addHelperPath(APPLICATION_PATH . "/views/helpers");
		
		// nacteni uzivatele
		$username = Zend_Auth::getInstance()->getIdentity()->username;
		$tableUsers = new Application_Model_DbTable_User();
		
		$this->_user = $tableUsers->getByUsername($username);
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
	}
}