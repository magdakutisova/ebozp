<?php

class PositionController extends Zend_Controller_Action{
	
	private $_headquarters = null;
    private $_clientId = null;
    private $_acl = null;
    private $_user = null;
    private $_username = null;
    
    public function init(){
    	//globální nastavení view
    	$this->view->title = 'Pracovní pozice';
    	$this->view->headTitle($this->view->title);
    	$this->_helper->layout()->setLayout('clientLayout');
    	$this->view->addHelperPath('My/View/Helper', 'My_View_Helper');
    	
    	//získání odkazu na centrálu - instance Application_Model_Subsidiary
    	$action = $this->getRequest()->getActionName();
    	$this->_acl = new My_Controller_Helper_Acl();
    	$this->_clientId = $this->getRequest()->getParam('clientId');
    	$subsidiaries = new Application_Model_DbTable_Subsidiary();
    	$this->_headquarters = $subsidiaries->getHeadquarters($this->_clientId);
    	
    	//přístupová práva
    	$this->_username = Zend_Auth::getInstance()->getIdentity()->username;
    	$users = new Application_Model_DbTable_User();
    	$this->_user = $users->getByUsername($this->_username);
    	
    	//soukromá poznámka
    	$this->view->canViewPrivate = $this->_acl->isAllowed($this->_user, 'private');
    }
    
    public function newAction(){
    	$defaultNamespace = new Zend_Session_Namespace();
    	$this->view->subtitle = "Zadat pracovní pozici";
    	$form = $this->loadOrCreateForm($defaultNamespace);
    	
    	//získání parametrů ID klienta a pobočky
    	$clientId = $this->getRequest()->getParam('clientId');
    	$subsidiaryId = $this->getRequest()->getParam('subsidiaryId');
    	 
    	$form->client_id->setValue($clientId);
    	 
    	//naplnění multiselectu pobočkami
    	$subsidiaries = new Application_Model_DbTable_Subsidiary ();
    	$formContent = $subsidiaries->getSubsidiaries ( $this->_clientId, 0, 1 );
    	if ($formContent != 0){
    		$formContent = $this->filterSubsidiarySelect($formContent);
    		$form->subsidiary_id->setMultiOptions ( $formContent );
    	}
    	$form->subsidiary_id->setValue($subsidiaryId);
    	
    	//TODO odtud dále
    	
    	$this->view->form = $form;
    }
    
    public function listAction(){
    	$defaultNamespace = new Zend_Session_Namespace();
    	if (isset($defaultNamespace->form)){
    		unset($defaultNamespace->form);
    	}
    	if (isset($defaultNamespace->formData)){
    		unset($defaultNamespace->formData);
    	}
    	
    	$clients = new Application_Model_DbTable_Client();
    	$client = $clients->getClient($this->_clientId);
    	
    	$this->view->subtitle = "Databáze pracovních pozic - " . $client->getCompanyName();
    	$this->view->clientId = $this->_clientId;
    	$filter = $this->getRequest()->getParam('filter');
    	$this->view->filter = $filter;
    	
    	//výběr poboček
    	$subsidiaries = new Application_Model_DbTable_Subsidiary();
    	$formContent = $subsidiaries->getSubsidiaries ( $this->_clientId, 0, 1 );
    	
    	if ($formContent != 0){
    		$formContent = $this->filterSubsidiarySelect($formContent);
    	}
    	
    	$subsidiaryId = null;
    	 
    	if ($formContent != 0) {
    		$subsidiaryId = $this->initSubsidiarySwitch($formContent, $subsidiaryId);
    	}
    	else{
    		$selectForm = "<p>Klient nemá žádné pobočky nebo k nim nemáte přístup.</p>";
    		$this->view->selectForm = $selectForm;
    	}
    	
    	//TODO vypisování pracovních pozic
    }
    
    private function filterSubsidiarySelect($formContent){
    	$subsidiaries = new Application_Model_DbTable_Subsidiary();
    	foreach ($formContent as $key => $subsidiary){
    		if (!$this->_acl->isAllowed($this->_user, $subsidiaries->getSubsidiary($key))){
    			unset($formContent[$key]);
    		}
    	}
    	return $formContent;
    }
    
    private function initSubsidiarySwitch($formContent, $subsidiaryId){
    	$selectForm = new Application_Form_Select ();
    	$selectForm->select->setMultiOptions ( $formContent );
    	$selectForm->select->setLabel('Vyberte pobočku:');
    	$selectForm->submit->setLabel('Vybrat');
    	$this->view->selectForm = $selectForm;
    	$subsidiaryId = array_shift(array_keys($formContent));
    		
    	if ($this->getRequest ()->isPost () && in_array('Vybrat', $this->getRequest()->getPost())) {
    		$formData = $this->getRequest ()->getPost ();
    		$subsidiaryId = $formData['select'];
    		$this->_helper->redirector->gotoRoute(array('clientId' => $this->_clientId, 'subsidiaryId' => $subsidiaryId, 'filter' => $this->getRequest()->getParam('filter')), 'positionList');
    	}
    	else{
    		$subsidiaryId = $this->getRequest()->getParam('subsidiaryId');
    		$selectForm->select->setValue($subsidiaryId);
    	}
    	$this->view->subsidiaryId = $subsidiaryId;
    	return $subsidiaryId;
    }
    
    private function loadOrCreateForm($defaultNamespace){
    	//pokud předtím selhalo odeslání, tak se načte aktuální formulář se všemi dodatečně vloženými elementy
    	if (isset ( $defaultNamespace->form )) {
    		$form = $defaultNamespace->form;
    		unset ( $defaultNamespace->form );
    	}
    	//jinak se vytvoří nový
    	else{
    		$form = new Application_Form_Position();
    	}
    	return $form;
    }
	
}