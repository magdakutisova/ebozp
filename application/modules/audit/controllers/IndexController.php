<?php
class Audit_IndexController extends Zend_Controller_Action {
	
	public function init() {
		$this->view->addHelperPath(APPLICATION_PATH . "/views/helpers/", "Zend_View_Helper");
	}
	
	public function indexAction() {
		// nacteni klientu
		$tableClients = new Application_Model_DbTable_Client();
		$clients = $tableClients->fetchAll(null, "company_name");
	}
}